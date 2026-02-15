<?php

namespace App\Services\Pdf;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class PdfService
{
    /**
     * Generate PDF from view.
     */
    public function generate(string $view, array $data, string $filename): Response
    {
        $pdf = Pdf::loadView($view, $data);

        return $pdf->download($filename);
    }

    /**
     * Stream PDF in browser.
     */
    public function stream(string $view, array $data, string $filename): Response
    {
        $pdf = Pdf::loadView($view, $data);

        return $pdf->stream($filename);
    }

    /**
     * Generate customer statement PDF.
     */
    public function customerStatement(array $statementData): Response
    {
        $filename = 'customer-statement-' . $statementData['entity']['id'] . '-' . now()->format('Ymd') . '.pdf';

        return $this->stream('pdf.statement', [
            'statement' => $statementData,
            'title' => 'Customer Account Statement',
        ], $filename);
    }

    /**
     * Generate vendor statement PDF.
     */
    public function vendorStatement(array $statementData): Response
    {
        $filename = 'vendor-statement-' . $statementData['entity']['id'] . '-' . now()->format('Ymd') . '.pdf';

        return $this->stream('pdf.statement', [
            'statement' => $statementData,
            'title' => 'Vendor Account Statement',
        ], $filename);
    }
}
