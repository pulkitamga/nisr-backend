<?php

namespace App\Http\Controllers\Admin\HelpAndSupport;

use App\Contracts\Repositories\HelpTopicRepositoryInterface;
use App\Enums\ViewPaths\Admin\HelpTopic;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\HelpTopicAddRequest;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Contracts\Repositories\TranslationRepositoryInterface;
use App\Models\HelpTopic as HelpTopicModel;


class HelpTopicController extends BaseController
{

    public function __construct(
        private readonly HelpTopicRepositoryInterface $helpTopicRepo,
        private readonly TranslationRepositoryInterface     $translationRepo,


    ) {}

    public function index(?Request $request, string $type = null): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse
    {
        return $this->getListView();
    }

    public function getListView(): View
    {
        $helps = $this->helpTopicRepo->getListWhere(orderBy: ['id' => 'desc'],filters: ['type' => 'default'],dataLimit: 'all');
        $translations = [];

        foreach ($helps as $help) {
            $help->load('translations');
            foreach ($help->translations as $trans) {
                $translations[$help->id][$trans->locale][$trans->key] = $trans->value;
            }
        }
        return view(HelpTopic::LIST[VIEW], compact('helps' ,'translations'));
    }

    public function add(HelpTopicAddRequest $request): RedirectResponse
    {
        $defaultLangIndex = getDefaultLanguageIndex($request);

        // 1. Insert Default Language Data
        $helpTopic = $this->helpTopicRepo->add(data: [
            'type'     => $request->get('type', 'default'),
            'question' => $request->question[$defaultLangIndex],
            'answer'   => $request->answer[$defaultLangIndex],
            'status'   => $request->get('status', 0),
            'ranking'  => $request->ranking,
        ]);

        // 2. Send Translation Data to TranslationRepo
        if ($helpTopic) {
            $this->translationRepo->update(
                request: $request,
                model: HelpTopicModel::class,
                id: $helpTopic->id
            );
        }

        Toastr::success(translate('FAQ_added_successfully'));
        return back();
    }


    public function updateStatus($id): JsonResponse
    {
        $helpTopic = $this->helpTopicRepo->getFirstWhere(params: ['id' => $id]);
        $this->helpTopicRepo->update(id: $id, data: [
            'status' => $helpTopic['status'] ? 0 : 1,
        ]);
        return response()->json(['success' => translate('status_change_successfully')]);
    }

    public function getUpdateResponse($id): JsonResponse
    {
        $helpTopic = $this->helpTopicRepo->getFirstWhere(params: ['id' => $id]);
        return response()->json($helpTopic);
    }

    public function update(HelpTopicAddRequest $request, $id): RedirectResponse
    {
        $defaultLangIndex = getDefaultLanguageIndex($request);
        $this->helpTopicRepo->update(id: $id, data: [
            'question' => $request['question'][$defaultLangIndex],
            'answer' => $request['answer'][$defaultLangIndex],
            'ranking' => $request['ranking'],
            'status' => $request->get('status', 0),
        ]);

        $this->translationRepo->update($request, HelpTopicModel::class, $id);
        Toastr::success(translate('FAQ_Update_successfully'));
        return back();
    }

    public function delete(Request $request): JsonResponse
    {
        $this->helpTopicRepo->delete(params: ['id' => $request['id']]);
        return response()->json();
    }
}
