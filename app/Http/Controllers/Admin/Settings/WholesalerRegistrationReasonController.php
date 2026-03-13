<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Enums\ViewPaths\Admin\WholesalerRegistrationReason;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\WholesalerRegistrationReasonRequest;
use App\Repositories\WholesalerRegistrationReasonRepository;
use App\Services\WholesalerRegistrationSettingService;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\WholesalerRegistrationReason as WholesalerRegistrationReasonModel;
use App\Contracts\Repositories\TranslationRepositoryInterface;


class WholesalerRegistrationReasonController extends BaseController
{
    public function __construct(

        private readonly WholesalerRegistrationReasonRepository $wholesalerRegistrationReasonRepo,
        private readonly WholesalerRegistrationSettingService $wholesalerRegistrationSettingService,
        private readonly TranslationRepositoryInterface     $translationRepo,

    ) {}
    public function index(?Request $request, string $type = null): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse
    {
        // TODO: Implement index() method.
    }
    public function add(WholesalerRegistrationReasonRequest $request): RedirectResponse
    {

        $data = $this->wholesalerRegistrationSettingService->getVendorRegistrationReasonData(request: $request);

        $reason = $this->wholesalerRegistrationReasonRepo->add($data);

        $this->translationRepo->add($request, WholesalerRegistrationReasonModel::class, $reason->id);

        Toastr::success(translate('wholesaler_registration_reason_added_successfully'));
        return redirect()->back();
    }
    public function getUpdateView(Request $request): JsonResponse
    {
        $wholesalerRegistrationReason = $this->wholesalerRegistrationReasonRepo->getFirstWhere(
            ['id' => $request['id']],
            ['translations'] // 👈 yeh zaroori hai
        );

        return response()->json(['view' => view(WholesalerRegistrationReason::UPDATE[VIEW], compact('wholesalerRegistrationReason'))->render()]);
    }
    public function update(WholesalerRegistrationReasonRequest $request): RedirectResponse
    {
        $this->wholesalerRegistrationReasonRepo->update(
            id: $request['id'],
            data: $this->wholesalerRegistrationSettingService->getVendorRegistrationReasonData(request: $request)
        );

        $id = $request['id'];
        $this->translationRepo->update($request, WholesalerRegistrationReasonModel::class, $id);

        Toastr::success(translate('wholesaler_registration_reason_update_successfully'));
        return redirect()->back();
    }

    public function delete(Request $request): RedirectResponse
    {
        $this->wholesalerRegistrationReasonRepo->delete(params: ['id' => $request['id']]);
        Toastr::success(translate('wholesaler_registration_reason_deleted_successfully'));
        return redirect()->back();
    }

    public function updateStatus(Request $request): JsonResponse
    {
        $this->wholesalerRegistrationReasonRepo->update(id: $request['id'], data: ['status' => $request->get('status', 0)]);
        return response()->json(['message' => translate('wholesaler_registration_reason_status_changed_successfully')]);
    }
}
