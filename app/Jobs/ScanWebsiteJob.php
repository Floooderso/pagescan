<?php

namespace App\Jobs;

use App\Models\Company;
use App\Models\Scan;
use App\Models\User;
use App\Services\WebsiteScanner;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class ScanWebsiteJob implements ShouldQueue
{
    use Queueable;

    public int $tries   = 2;
    public int $timeout = 60;

    public function __construct(
        public readonly int $companyId,
        public readonly int $userId,
    ) {}

    public function handle(): void
    {
        $company = Company::find($this->companyId);
        if (!$company || !$company->website_url) {
            return;
        }

        $user = User::find($this->userId);

        // Atomically claim the pending scan: lock the row and immediately mark it
        // as 'running' inside one transaction so no other job can pick it up.
        $scan = DB::transaction(function () use ($user) {
            $s = Scan::where('company_id', $this->companyId)
                ->whereIn('status', ['pending', 'running'])
                ->lockForUpdate()
                ->latest()
                ->first();

            if (!$s) {
                return null;
            }

            // Defense-in-depth quota guard inside the lock so concurrent jobs
            // cannot both pass the quota check for the same user.
            if ($user) {
                $limit = User::effectiveLimit($user);
                if ($limit !== null && $user->scans_this_month >= $limit) {
                    $s->update(['status' => 'failed', 'error' => 'quota_exceeded']);
                    return null;
                }
            }

            $s->update(['status' => 'running']);
            return $s;
        });

        if (!$scan) {
            return;
        }

        try {
            $weights = $user?->getScoreWeights() ?? [];
            $result  = (new WebsiteScanner($weights))->scan($company->website_url);

            $scan->update($result);

            if ($user) {
                $user->increment('scans_this_month');
            }
        } catch (\Throwable $e) {
            $scan->update([
                'status' => 'failed',
                'error'  => $e->getMessage(),
            ]);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Scan::where('company_id', $this->companyId)
            ->whereIn('status', ['pending', 'running'])
            ->latest()
            ->first()
            ?->update(['status' => 'failed', 'error' => $exception->getMessage()]);
    }
}
