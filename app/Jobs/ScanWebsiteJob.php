<?php

namespace App\Jobs;

use App\Models\Company;
use App\Models\Scan;
use App\Models\User;
use App\Services\WebsiteScanner;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

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

        $scan = Scan::where('company_id', $this->companyId)
            ->whereIn('status', ['pending', 'running'])
            ->latest()
            ->first();

        if (!$scan) {
            return;
        }

        $scan->update(['status' => 'running']);

        try {
            $user    = User::find($this->userId);
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
