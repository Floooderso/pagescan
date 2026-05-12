<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Lead;
use App\Services\PdfReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class LeadController extends Controller
{
    public function index()
    {
        return view('leads.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'place_id'    => 'required|string',
            'name'        => 'required|string|max:255',
            'adresse'     => 'nullable|string|max:500',
            'plz'         => 'nullable|string|max:20',
            'stadt'       => 'nullable|string|max:100',
            'branche'     => 'nullable|string|max:100',
            'telefon'     => 'nullable|string|max:50',
            'website_url' => 'nullable|string|max:500',
            'has_website' => 'boolean',
            'rating'      => 'nullable|numeric',
            'bewertungen' => 'nullable|integer',
            'scan'        => 'nullable|array',
            'photo_url'   => 'nullable|string|max:1000',
        ]);

        $company = Company::updateOrCreate(
            ['place_id' => $request->place_id],
            $request->only(['name', 'adresse', 'plz', 'stadt', 'branche', 'telefon', 'website_url', 'has_website', 'rating', 'bewertungen'])
        );

        // Screenshot speichern (nur wenn noch keiner vorhanden)
        if (!$company->screenshot_path) {
            try {
                $imgUrl = $request->filled('photo_url')
                    ? $request->photo_url
                    : ($request->filled('website_url')
                        ? 'https://image.thum.io/get/width/600/crop/400/noanimate/' . $request->website_url
                        : null);

                if ($imgUrl) {
                    $response = Http::timeout(10)->get($imgUrl);
                    if ($response->successful()) {
                        $filename = 'screenshots/' . $company->id . '.jpg';
                        Storage::disk('public')->put($filename, $response->body());
                        $company->update(['screenshot_path' => $filename]);
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Screenshot download failed', ['error' => $e->getMessage()]);
            }
        }

        $scanId = null;
        if ($request->filled('scan')) {
            $s = $request->scan;
            $scan = $company->scans()->create([
                'scanned_at'       => now(),
                'status'           => 'done',
                'http_status'      => $s['http_status']      ?? null,
                'response_ms'      => $s['response_ms']      ?? null,
                'has_ssl'          => $s['has_ssl']           ?? null,
                'is_mobile'        => $s['is_mobile']         ?? null,
                'has_contact'      => $s['has_contact']       ?? null,
                'has_impressum'    => $s['has_impressum']     ?? null,
                'has_tracking'     => $s['has_tracking']      ?? null,
                'has_favicon'      => $s['has_favicon']       ?? null,
                'cms'              => $s['cms']               ?? null,
                'page_title'       => $s['page_title']        ?? null,
                'meta_description' => $s['meta_description']  ?? null,
                'score'            => $s['score']             ?? null,
            ]);
            $scanId = $scan->id;
        }

        $existing = Lead::where('user_id', auth()->id())
            ->where('company_id', $company->id)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Lead bereits vorhanden'], 409);
        }

        $lead = Lead::create([
            'user_id'    => auth()->id(),
            'company_id' => $company->id,
            'scan_id'    => $scanId,
            'status'     => 'neu',
            'priority'   => 'mittel',
        ]);

        return response()->json($lead->load(['company', 'scan']), 201);
    }

    public function apiIndex(Request $request)
    {
        $user  = auth()->user();
        $query = Lead::with(['company', 'scan', 'contactAttempts', 'assignedTo']);

        if ($user->isAdmin()) {
            $query->with('user');
        } else {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('min_score') || $request->filled('max_score')) {
            $query->whereHas('scan', function ($q) use ($request) {
                if ($request->filled('min_score')) $q->where('score', '>=', (int) $request->min_score);
                if ($request->filled('max_score'))  $q->where('score', '<=', (int) $request->max_score);
            });
        }
        if ($request->filled('cms')) {
            $query->whereHas('scan', fn($q) => $q->where('cms', $request->cms));
        }

        $leads = $query->latest()->get()
            ->map(function ($lead) {
                $scan = $lead->scan;
                return [
                    'id'          => $lead->id,
                    'status'      => $lead->status,
                    'priority'    => $lead->priority,
                    'notes'       => $lead->notes,
                    'assigned_to' => $lead->assigned_to,
                    'assigned_to_name' => $lead->assignedTo?->name,
                    'owner_name'  => $lead->user?->name,
                    'company'  => [
                        'id'              => $lead->company->id,
                        'name'            => $lead->company->name,
                        'branche'         => $lead->company->branche,
                        'stadt'           => $lead->company->stadt,
                        'telefon'         => $lead->company->telefon,
                        'website_url'     => $lead->company->website_url,
                        'has_website'     => $lead->company->has_website,
                        'screenshot_path' => $lead->company->screenshot_path
                            ? asset('storage/' . $lead->company->screenshot_path)
                            : null,
                        'rating'      => $lead->company->rating,
                        'bewertungen' => $lead->company->bewertungen,
                    ],
                    'scan' => $scan ? [
                        'score'              => $scan->score,
                        'http_status'        => $scan->http_status,
                        'response_ms'        => $scan->response_ms,
                        'has_ssl'            => $scan->has_ssl,
                        'is_mobile'          => $scan->is_mobile,
                        'has_contact'        => $scan->has_contact,
                        'has_impressum'      => $scan->has_impressum,
                        'has_tracking'       => $scan->has_tracking,
                        'has_favicon'        => $scan->has_favicon,
                        'has_og_tags'        => $scan->has_og_tags,
                        'has_google_maps'    => $scan->has_google_maps,
                        'has_cookie_banner'  => $scan->has_cookie_banner,
                        'alt_tag_ratio'      => $scan->alt_tag_ratio,
                        'broken_links_count' => $scan->broken_links_count,
                        'speed_rating'       => $scan->speed_rating,
                        'cms'                => $scan->cms,
                        'page_title'         => $scan->page_title,
                        'meta_description'   => $scan->meta_description,
                        // Advanced fields
                        'risk_badge'              => $scan->risk_badge,
                        'google_fonts_source'     => $scan->google_fonts_source,
                        'cookie_banner_tool'      => $scan->cookie_banner_tool,
                        'cookie_banner_status'    => $scan->cookie_banner_status,
                        'impressum_location'      => $scan->impressum_location,
                        'datenschutz_location'    => $scan->datenschutz_location,
                        'security_headers_score'  => $scan->security_headers_score,
                        'security_headers_detail' => $scan->security_headers_detail,
                        'outdated_tech'           => $scan->outdated_tech,
                        'ttfb_score'              => $scan->ttfb_score,
                        'lcp_score'               => $scan->lcp_score,
                        'cls_score'               => $scan->cls_score,
                        'has_dsgvo_issues'        => $scan->has_dsgvo_issues,
                        'has_performance_issues'  => $scan->has_performance_issues,
                    ] : null,
                    'contact_attempts' => $lead->contactAttempts->map(fn($a) => [
                        'id'             => $a->id,
                        'kanal'          => $a->kanal,
                        'notiz'          => $a->notiz,
                        'kontaktiert_am' => $a->kontaktiert_am->format('Y-m-d'),
                    ])->values(),
                ];
            });

        return response()->json($leads);
    }

    public function destroy(Lead $lead)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && $lead->user_id !== $user->id) {
            return response()->json(['message' => 'Nicht erlaubt'], 403);
        }

        $lead->delete();

        return response()->json(['message' => 'Gelöscht'], 200);
    }

    public function update(Request $request, Lead $lead)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && $lead->user_id !== $user->id) {
            return response()->json(['message' => 'Nicht erlaubt'], 403);
        }

        $data = $request->validate([
            'status'   => 'nullable|in:neu,kontaktiert,angebot,gewonnen,verloren',
            'priority' => 'nullable|in:hoch,mittel,niedrig',
            'notes'    => 'nullable|string|max:5000',
        ]);

        $lead->update(array_filter($data, fn($v) => $v !== null));

        return response()->json($lead);
    }

    public function assign(Request $request, Lead $lead)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Nur Admins dürfen Leads zuweisen'], 403);
        }

        $data = $request->validate([
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $lead->update(['assigned_to' => $data['assigned_to']]);

        return response()->json([
            'assigned_to'      => $lead->assigned_to,
            'assigned_to_name' => $lead->assignedTo?->name,
        ]);
    }

    // ────────────────────────────────────────────────────────────────────────────
    //  PDF Export
    // ────────────────────────────────────────────────────────────────────────────

    /**
     * Download a single lead as PDF.
     * Route: GET /leads/{lead}/pdf
     */
    public function exportPdf(Lead $lead, PdfReportService $pdfService)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && $lead->user_id !== $user->id) {
            abort(403, 'Nicht erlaubt');
        }

        $pdf      = $pdfService->generateForLead($lead);
        $filename = 'Lead_' . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $lead->company?->name ?? $lead->id) . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Download multiple leads as ZIP of PDFs.
     * Route: POST /leads/export-pdf-batch
     * Body: { ids: [1,2,3] } — leave empty to export all user leads
     */
    public function exportPdfBatch(Request $request, PdfReportService $pdfService)
    {
        $request->validate([
            'ids'   => 'nullable|array',
            'ids.*' => 'integer|exists:leads,id',
        ]);

        $user  = auth()->user();
        $query = Lead::with(['company', 'scan', 'contactAttempts']);

        if (!$user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('ids')) {
            $query->whereIn('id', $request->ids);
        }

        $leads = $query->latest()->get();

        if ($leads->isEmpty()) {
            abort(404, 'Keine Leads gefunden');
        }

        $zipPath = $pdfService->generateZipForLeads($leads);

        return response()->download($zipPath, 'leads_' . now()->format('Y-m-d') . '.zip', [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    // ────────────────────────────────────────────────────────────────────────────
    //  CSV Export (extended)
    // ────────────────────────────────────────────────────────────────────────────

    public function exportCsv(Request $request)
    {
        $user  = auth()->user();
        $query = Lead::with(['company', 'scan']);

        if (!$user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        // Support filtering by specific IDs for batch CSV
        if ($request->filled('ids')) {
            $ids = array_map('intval', explode(',', $request->ids));
            $query->whereIn('id', $ids);
        }

        $leads = $query->latest()->get();

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="leads_' . now()->format('Y-m-d') . '.csv"',
        ];

        return response()->stream(function () use ($leads) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                'ID', 'Firmenname', 'Stadt', 'Branche', 'Website', 'Telefon',
                'Score', 'Risiko', 'SSL', 'Mobil', 'CMS',
                'Impressum', 'Datenschutz', 'Cookie-Banner', 'Cookie-Tool',
                'Google Fonts', 'Sicherheits-Score',
                'TTFB', 'LCP', 'CLS',
                'Veraltete Technologien',
                'Lead-Status', 'Priorität', 'Notizen', 'Erstellt am',
            ], ';');

            foreach ($leads as $lead) {
                $scan    = $lead->scan;
                $company = $lead->company;

                fputcsv($handle, [
                    $lead->id,
                    $company->name ?? '',
                    $company->stadt ?? '',
                    $company->branche ?? '',
                    $company->website_url ?? '',
                    $company->telefon ?? '',
                    $scan?->score ?? '',
                    $scan?->risk_badge ?? '',
                    $scan ? ($scan->has_ssl ? 'Ja' : 'Nein') : '',
                    $scan ? ($scan->is_mobile ? 'Ja' : 'Nein') : '',
                    $scan?->cms ?? '',
                    $scan?->impressum_location ?? '',
                    $scan?->datenschutz_location ?? '',
                    $scan?->cookie_banner_status ?? '',
                    $scan?->cookie_banner_tool ?? '',
                    $scan?->google_fonts_source ?? '',
                    $scan?->security_headers_score ?? '',
                    $scan?->ttfb_score ?? '',
                    $scan?->lcp_score ?? '',
                    $scan?->cls_score ?? '',
                    $scan?->outdated_tech ? implode(', ', $scan->outdated_tech) : '',
                    $lead->status,
                    $lead->priority,
                    $lead->notes ?? '',
                    $lead->created_at->format('d.m.Y'),
                ], ';');
            }

            fclose($handle);
        }, 200, $headers);
    }
}
