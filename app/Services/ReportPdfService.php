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

    // public function download(
    //     string $view,
    //     array $data,
    //     string $fileName,
    //     string $orientation = 'portrait'
    // ): Response {
    //     $html = $this->viewFactory->make($view, $data)->render();

    //     $mpdf = new Mpdf([
    //         'mode' => 'utf-8',
    //         'format' => $this->resolveFormat($orientation),
    //         'default_font' => 'dejavusans',
    //         'autoScriptToLang' => true,
    //         'autoLangToFont' => true,
    //     ]);

    //     if ($this->resolveRtl($data)) {
    //         $mpdf->SetDirectionality('rtl');
    //     }

    //     $mpdf->WriteHTML($html);
    //     $pdfContent = $mpdf->Output('', Destination::STRING_RETURN);

    //     return response($pdfContent, 200, [
    //         'Content-Type' => 'application/pdf',
    //         'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
    //     ]);
    // }
public function download(
        string $view,
        array $data,
        string $fileName,
        string $orientation = 'portrait',

    ): Response {

        $html = $this->viewFactory->make($view, $data)->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => $this->resolveFormat($orientation),
            'default_font' => 'dejavusans',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
            'margin_bottom' => 35, 
        ]);

        $isRtl = $this->resolveRtl($data);

        if ($isRtl) {
            $mpdf->SetDirectionality('rtl');
        }
        $reportTitle = $data['report_title'] ?? 'Report';
        $userName = ucfirst(auth()->user()->name ?? 'system');

        $generatedOn = translate('generated_on');
        $generatedBy = translate('generated_by');
        $mpdf->SetHTMLFooter('
<table width="100%" style="padding-top:6px; font-size:8px; color:#6b7280; border-collapse:collapse; border:none;">
    <tr>

        <td width="20%" style="text-align:' . ($isRtl ? 'right' : 'left') . '; vertical-align:top; border:none;">
            Page {PAGENO} of {nbpg}
        </td>

        <td width="60%" style="text-align:center; vertical-align:top; line-height:1.4; border:none;">
            ' . $generatedOn . ': ' . now()->translatedFormat('j F Y, h:i A') . '<br>
            ' . $reportTitle . '<br>
            ' . $generatedBy . ': ' . $userName . '<br>
            <span style="color:red;">' . config('app.name') . '</span>
        </td>

        <td width="20%" style="border:none;"></td>

    </tr>
</table>
');

        $mpdf->WriteHTML($html);

        return response(
            $mpdf->Output('', Destination::STRING_RETURN),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ]
        );
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
