<?php

namespace App\Http\Controllers;

use App\Jobs\ScanWebsiteJob;
use App\Models\Company;
use App\Models\Scan;
use App\Services\WebsiteScanner;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index()
    {
        return view('companies.index');
    }

    public function create()
    {
        return view('companies.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'branche'     => 'required|string|max:255',
            'stadt'       => 'required|string|max:255',
            'plz'         => 'nullable|string|max:20',
            'telefon'     => 'nullable|string|max:50',
            'email'       => 'nullable|email|max:255',
            'website_url' => 'nullable|url|max:500',
        ]);

        $data['has_website'] = !empty($data['website_url']);
        $company = Company::create($data);

        if ($company->has_website) {
            $result = (new WebsiteScanner)->scan($company->website_url);
            Scan::create(array_merge(['company_id' => $company->id], $result));
        }

        return redirect("/companies/{$company->id}");
    }

    public function show(Company $company)
    {
        $scan = $company->latestScan();
        $lead = $company->lead()->where('user_id', auth()->id())->first();
        return view('companies.show', compact('company', 'scan', 'lead'));
    }

    public function scan(Company $company)
    {
        if (!$company->website_url) {
            return back()->with('error', 'Keine Website-URL vorhanden.');
        }

        Scan::create(['company_id' => $company->id, 'status' => 'pending']);
        ScanWebsiteJob::dispatch($company->id, auth()->id());

        return back()->with('success', 'Scan gestartet. Das Ergebnis erscheint in Kürze.');
    }

    public function exportCsv(Request $request)
    {
        $companies = Company::with(['scans' => fn($q) => $q->latest()->limit(1)])
            ->latest()
            ->get();

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="unternehmen_' . now()->format('Y-m-d') . '.csv"',
        ];

        return response()->stream(function () use ($companies) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, ['ID', 'Name', 'Stadt', 'Branche', 'PLZ', 'Telefon', 'Website', 'Hat Website', 'Score', 'SSL', 'Mobil', 'CMS', 'Tracking', 'Erstellt am'], ';');

            foreach ($companies as $company) {
                $scan = $company->scans->first();
                fputcsv($handle, [
                    $company->id,
                    $company->name,
                    $company->stadt ?? '',
                    $company->branche ?? '',
                    $company->plz ?? '',
                    $company->telefon ?? '',
                    $company->website_url ?? '',
                    $company->has_website ? 'Ja' : 'Nein',
                    $scan?->score ?? '',
                    $scan ? ($scan->has_ssl ? 'Ja' : 'Nein') : '',
                    $scan ? ($scan->is_mobile ? 'Ja' : 'Nein') : '',
                    $scan?->cms ?? '',
                    $scan ? ($scan->has_tracking ? 'Ja' : 'Nein') : '',
                    $company->created_at->format('d.m.Y'),
                ], ';');
            }

            fclose($handle);
        }, 200, $headers);
    }
}
