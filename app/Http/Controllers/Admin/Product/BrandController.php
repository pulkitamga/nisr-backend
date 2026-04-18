<?php

namespace App\Http\Controllers\Admin\Product;

use App\Contracts\Repositories\BrandRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\TranslationRepositoryInterface;
use App\Enums\ExportFileNames\Admin\Brand as BrandExport;
use App\Enums\ViewPaths\Admin\Brand;
use App\Exports\FormattedTableExport;
use App\Exports\BrandListExport;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\BrandAddRequest;
use App\Http\Requests\Admin\BrandUpdateRequest;
use App\Services\BrandService;
use App\Support\LocalizedExport;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BrandController extends BaseController
{
    public function __construct(
        private readonly BrandRepositoryInterface           $brandRepo,
        private readonly ProductRepositoryInterface           $productRepo,
        private readonly TranslationRepositoryInterface     $translationRepo,
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
        return $this->getList($request);
    }

    public function getList(Request $request): Application|Factory|View
    {
        $brands = $this->brandRepo->getListWhere(
            orderBy:['id'=>'desc'],
            searchValue:$request->get('searchValue'),
            relations: ['storage'],
            dataLimit: getWebConfig(name: 'pagination_limit'));
        return view(Brand::LIST[VIEW], compact('brands'));
    }

    public function getAddView(): View
    {
        $language = getWebConfig(name: 'pnc_language') ?? null;
        $defaultLanguage = $language[0];
        return view(Brand::ADD[VIEW], compact( 'language', 'defaultLanguage'));
    }

    public function getUpdateView(string|int $id): View|RedirectResponse
    {
        $brand = $this->brandRepo->getFirstWhere(params:['id'=>$id], relations: ['translations','storage']);
        $language = getWebConfig(name: 'pnc_language') ?? null;
        $defaultLanguage = $language[0];
        return view(Brand::UPDATE[VIEW], compact('brand', 'language', 'defaultLanguage'));
    }

    public function updateStatus(Request $request): JsonResponse
    {
        $data = [
          'status' => $request->get('status', 0),
        ];
        $this->brandRepo->update(id:$request['id'], data:$data);
        return response()->json(['success' => 1, 'message' => translate('Status_updated_successfully')], 200);
    }

    public function delete(Request $request, BrandService $brandService): RedirectResponse
    {
        $this->productRepo->updateByParams(params:['brand_id'=>$request['id']],data:['brand_id' =>$request['brand_id'],'sub_category_id'=>null,'sub_sub_category_id'=>null]);
        $brand = $this->brandRepo->getFirstWhere(params:['id'=>$request['id']]);
        $brandService->deleteImage(data:$brand);
        $this->translationRepo->delete(model:'App\Models\Brand', id:$request['id']);
        $this->brandRepo->delete(params: ['id'=>$request['id']]);
        Toastr::success(translate('brand_deleted_successfully'));
        return redirect()->back();
    }


    public function add(BrandAddRequest $request, BrandService $brandService): RedirectResponse
    {
        $dataArray = $brandService->getAddData(request:$request);
        $savedAttributes = $this->brandRepo->add(data:$dataArray);
        $this->translationRepo->add(request:$request, model:'App\Models\Brand', id:$savedAttributes->id);

        Toastr::success(translate('brand_added_successfully'));
        return redirect()->route('admin.brand.list');
    }

    public function update(BrandUpdateRequest $request, $id, BrandService $brandService): RedirectResponse
    {
        $brand = $this->brandRepo->getFirstWhere(params:['id'=>$request['id']],relations: ['storage']);
        $dataArray = $brandService->getUpdateData(request: $request, data:$brand);
        $this->brandRepo->update(id:$request['id'], data:$dataArray);
        $this->translationRepo->update(request:$request, model:'App\Models\Brand', id:$request['id']);

        Toastr::success(translate('brand_updated_successfully'));
        return redirect()->route('admin.brand.list');
    }

    public function exportList(Request $request): BinaryFileResponse
    {
        $brands = $this->brandRepo->getListWhere(searchValue:$request->get('searchValue'), dataLimit: 'all');
        $active = $this->brandRepo->getListWhere(filters:['status'=>1], dataLimit: 'all')->count();
        $inactive = $this->brandRepo->getListWhere(filters:['status'=>0], dataLimit: 'all')->count();
        $rows = $brands->map(function ($brand) {
            return [
                (string) ($brand->defaultName ?? $brand->name ?? '-'),
                (int) ($brand->brand_all_products_count ?? 0),
                (int) ($brand->brandAllProducts?->sum('order_details_count') ?? 0),
                $brand->status == 1 ? translate('Active') : translate('Inactive'),
            ];
        })->values()->all();

        return Excel::download(
            new FormattedTableExport(
                rows: $rows,
                headings: [
                    translate('Name'),
                    translate('total_Product'),
                    translate('total_Order'),
                    translate('Status'),
                ],
                title: translate('brand_List'),
                locale: LocalizedExport::locale(),
                isRtl: LocalizedExport::isRtl(),
                metaPairs: [
                    ['label' => translate('exported_at'), 'value' => LocalizedExport::exportedAtLabel()],
                    ['label' => translate('count'), 'value' => (string) count($rows)],
                    ['label' => translate('Active'), 'value' => (string) $active],
                    ['label' => translate('Inactive'), 'value' => (string) $inactive],
                ],
                filterSummary: translate('Search') . ': ' . (trim((string) $request->input('searchValue', '')) ?: translate('All')),
                columnWidths: ['A' => 28, 'B' => 16, 'C' => 16, 'D' => 14],
                centerColumns: ['B', 'C', 'D'],
                sumColumns: ['B', 'C']
            ),
            LocalizedExport::fileName(translate('brand_List'))
        );
    }
}
