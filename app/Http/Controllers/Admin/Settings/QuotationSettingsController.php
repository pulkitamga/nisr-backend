<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Enums\ViewPaths\Admin\QuotationSettings;
use App\Http\Controllers\Controller;
use App\Services\BusinessSettingService;
use App\Traits\FileManagerTrait;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\BusinessSetting;

class QuotationSettingsController extends Controller
{
    use FileManagerTrait {
        delete as deleteFile;
        update as updateFile;
    }
    public function __construct(
        private readonly BusinessSettingRepositoryInterface $businessSettingRepo,
        private readonly BusinessSettingService $businessSettingService,
    ) {}
    public function index(Request|null $request, string $type = null): View
    {
        return $this->getView();
    }

    public function getView(): View
    {
        $invoiceSettings = json_decode($this->businessSettingRepo->getFirstWhere(params: ['type' => 'quotation_settings']));
        if (!isset($invoiceSettings)) {
            $invoiceSettings = json_encode($this->businessSettingService->getInvoiceSettingsData(request: null, imageArray: null));
            $this->businessSettingRepo->updateOrInsert(type: 'invoice_settings', value: $invoiceSettings);
            clearWebConfigCacheKeys();
        } else {
            $invoiceSettings = $invoiceSettings->value;
        }

        $type = BusinessSetting::where('type', 'quotation_settings')->first();

        $invoiceSettings = json_decode($invoiceSettings);
        return view(QuotationSettings::VIEW[VIEW], compact('invoiceSettings', 'type'));
    }
    public function update(Request $request)
    {
        $invoiceSettings = json_decode(
            $this->businessSettingRepo->getFirstWhere(['type' => 'quotation_settings'])->value,
            true
        );

        $imageFields = ['image_header', 'image_bg', 'image_footer'];

        $imageArray = [];

        foreach ($imageFields as $field) {
            if ($request->hasFile($field)) {
                $oldImageName = isset($invoiceSettings[$field])
                    ? (is_array($invoiceSettings[$field]) ? $invoiceSettings[$field]['image_name'] : $invoiceSettings[$field])
                    : null;

                $newImage = $this->updateFile(
                    dir: 'company/',
                    oldImage: $oldImageName,
                    format: 'webp',
                    image: $request->file($field)
                );

                $imageArray[$field] = [
                    'image_name' => $newImage,
                    'storage' => config('filesystems.disks.default') ?? 'public'
                ];
            } elseif (isset($invoiceSettings[$field])) {
                $imageArray[$field] = $invoiceSettings[$field];
            }
        }

        $value = $this->businessSettingService->getQuotationSettingsData(
            request: $request,
            imageArray: $imageArray
        );

        $this->businessSettingRepo->updateOrInsert(
            type: 'quotation_settings',
            value: json_encode($value)
        );

        clearWebConfigCacheKeys();

        return response()->json([
            'message' => translate('quotation_settings_update_successfully')
        ]);
    }

    public function toggleActiveStatus(Request $request)
    {

        $setting = BusinessSetting::where('type', $request->type)->first();
        $setting->is_active = $request->is_active;
        $setting->save();


        return response()->json(['message' => 'Status updated successfully.']);
    }
}
