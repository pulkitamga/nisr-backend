<?php

namespace App\Http\Controllers\Admin\ThirdParty;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Enums\ViewPaths\Admin\Mail;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\UcmConfigUpdateRequest;
use App\Services\MailService;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UcmConfigController extends BaseController
{

    public function __construct(
        private readonly BusinessSettingRepositoryInterface $businessSettingRepo,
        private readonly MailService $mailService,
    )
    {
    }

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View Index function is the starting point of a controller
     * Index function is the starting point of a controller
     */
    public function index(Request|null $request, string $type = null): View
    {
        return $this->getView();
    }

    public function getView(): View
    {
        return view('admin-views.business-settings.ucm-config');
    }

    public function update(UcmConfigUpdateRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['status'] = $data['status'] ?? 0;
        $data['digest'] = $data['digest'] ?? 0;
        $data['verify_tls'] = $data['verify_tls'] ?? 0;
        $data['api_version'] = $data['api_version'] ?? '1.0';
        $data['report_url'] = $data['report_url'] ?? '';
        $data['webhook_token'] = $data['webhook_token'] ?? '';
        $data['ca_path'] = $data['ca_path'] ?? '';

        \App\Models\BusinessSetting::updateOrCreate(
            ['type' => 'ucm_api_config'],
            ['value' => json_encode($data)]
        );

        Toastr::success(translate('UCM_API_Configuration_updated_successfully'));
        return back();
    }
}
