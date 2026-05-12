<?php

namespace App\Services;

use App\Models\Lead;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;

class PdfReportService
{
    /**
     * Generate a PDF for a single lead and return the PDF instance.
     * Usage: $pdf = $service->generateForLead($lead);
     *        return $pdf->download('report.pdf');
     */
    public function generateForLead(Lead $lead): \Barryvdh\DomPDF\PDF
    {
        $lead->loadMissing(['company', 'scan', 'contactAttempts']);

        return Pdf::loadView('pdf.lead-report', [
            'lead'    => $lead,
            'company' => $lead->company,
            'scan'    => $lead->scan,
        ])->setPaper('a4', 'portrait');
    }

    /**
     * Generate PDFs for multiple leads, zip them and return a ZipArchive path.
     * The caller is responsible for deleting the temp file after the response.
     *
     * @param Collection<Lead> $leads
     * @return string Absolute path to the temp ZIP file
     */
    public function generateZipForLeads(Collection $leads): string
    {
        $leads->loadMissing(['company', 'scan', 'contactAttempts']);

        $tmpDir  = sys_get_temp_dir();
        $zipPath = $tmpDir . '/leads_export_' . time() . '.zip';

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Konnte ZIP-Datei nicht erstellen: ' . $zipPath);
        }

        foreach ($leads as $lead) {
            $lead->loadMissing(['company', 'scan', 'contactAttempts']);

            $pdf  = Pdf::loadView('pdf.lead-report', [
                'lead'    => $lead,
                'company' => $lead->company,
                'scan'    => $lead->scan,
            ])->setPaper('a4', 'portrait');

            // Safe filename: company name sanitized
            $name     = $lead->company->name ?? 'Lead_' . $lead->id;
            $filename = preg_replace('/[^a-zA-Z0-9_\-äöüÄÖÜß ]/', '', $name);
            $filename = trim(str_replace(' ', '_', $filename));
            $filename = ($filename ?: 'Lead_' . $lead->id) . '_' . $lead->id . '.pdf';

            $zip->addFromString($filename, $pdf->output());
        }

        $zip->close();

        return $zipPath;
    }
}
