<?php

namespace App\Services;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Http\Response;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class ReportPdfService
{
    public function __construct(
        private readonly ViewFactory $viewFactory
    ) {}

    public function download(
        string $view,
        array $data,
        string $fileName,
        string $orientation = 'portrait'
    ): Response {
        $html = $this->viewFactory->make($view, $data)->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => $this->resolveFormat($orientation),
            'default_font' => 'dejavusans',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
        ]);

        if ($this->resolveRtl($data)) {
            $mpdf->SetDirectionality('rtl');
        }

        $mpdf->WriteHTML($html);
        $pdfContent = $mpdf->Output('', Destination::STRING_RETURN);

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    private function resolveRtl(array $data): bool
    {
        if (array_key_exists('isRtl', $data)) {
            return (bool)$data['isRtl'];
        }

        return app()->getLocale() === 'ar' || session('direction') === 'rtl';
    }

    private function resolveFormat(string $orientation): string
    {
        return strtolower($orientation) === 'landscape' ? 'A4-L' : 'A4';
    }
}
