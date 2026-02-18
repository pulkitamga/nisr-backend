<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Controller;
use App\Models\AboutHeroSection;
use App\Models\AboutWhoWeAreSection;
use App\Models\AboutProductSection;
use App\Models\AboutMissionSection;
use App\Models\AboutTimelineSection;
use App\Models\AboutDealerSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Contracts\Repositories\TranslationRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Traits\CommonTrait;
use App\Traits\PaginatorTrait;
use Illuminate\Support\Facades\Storage;

class AboutController extends Controller
{
    use PaginatorTrait;
    use CommonTrait;

    public function __construct(
        private readonly ProductRepositoryInterface     $productRepo,
        private readonly TranslationRepositoryInterface     $translationRepo,

    ) {}

    private $modelMap = [
        'hero' => AboutHeroSection::class,
        'who_we_are' => AboutWhoWeAreSection::class,
        'products' => AboutProductSection::class,
        'mission' => AboutMissionSection::class,
        'timeline' => AboutTimelineSection::class,
        'dealers' => AboutDealerSection::class,
    ];

    public function index($section = 'hero')
    {
        if (!array_key_exists($section, $this->modelMap)) {
            $section = 'hero';
        }

        $model = $this->modelMap[$section];
        $data = $model::all();
        $items = $model::latest()->paginate(10);
        return view('admin-views.content-management.about.index', compact('section', 'data', 'items'));
    }
    public function pages($section = 'hero')
    {


        if (!array_key_exists($section, $this->modelMap)) {
            $section = 'hero';
        }

        $model = $this->modelMap[$section];
        $data = $model::all();
        $items = $model::latest()->paginate(10);
        return view('admin-views.content-management.about.index', compact('section', 'data', 'items'));
    }

    public function create(Request $request)
    {
        $section = $request->get('section', 'hero'); // default: hero

        $viewPath = "admin-views.content-management.about.sections.create." . $section;


        return view($viewPath, compact('section'));
    }

    public function store(Request $request, $section)
    {
        $modelMap = [
            'hero' => AboutHeroSection::class,
            'who_we_are' => AboutWhoWeAreSection::class,
            'products' => AboutProductSection::class,
            'mission' => AboutMissionSection::class,
            'timeline' => AboutTimelineSection::class,
            'dealers' => AboutDealerSection::class,
        ];

        if (!isset($modelMap[$section])) {
            return back()->withErrors(['Invalid section']);
        }

        $modelClass = $modelMap[$section];

        $defaultLangIndex = array_search(config('app.locale'), $request->lang);

        $input = $request->except('_token', 'lang');

        foreach ($input as $key => $value) {
            if (is_array($value) && isset($value[$defaultLangIndex])) {
                $input[$key] = $value[$defaultLangIndex];
            }
        }

        if ($request->hasFile('image')) {
            $input['image'] = $request->file('image')->store('about', 'public');
        }

        $model = new $modelClass;
        $model->fill($input);
        $model->save();

        $this->translationRepo->add($request, $modelClass, $model->id);

        return redirect()->route('admin.content-management.about-us.pages', ['section' => $section])
            ->with('success', translate('Data added successfully.'));
    }

    public function destroy($section, $id)
    {
        $modelMap = [
            'hero' => AboutHeroSection::class,
            'who_we_are' => AboutWhoWeAreSection::class,
            'products' => AboutProductSection::class,
            'mission' => AboutMissionSection::class,
            'timeline' => AboutTimelineSection::class,
            'dealers' => AboutDealerSection::class,
        ];

        if (!isset($modelMap[$section])) {
            return redirect()->route('admin.content-management.about-us.index')
                ->withErrors(['Invalid section']);
        }
        $model = $modelMap[$section]::findOrFail($id);
        $model->delete();

        return redirect()->route('admin.content-management.about-us.pages', ['section' => $section])
            ->with('success', 'Item deleted successfully');
    }

    public function edit($section, $id)
    {
        $modelMap = [
            'hero' => AboutHeroSection::class,
            'who_we_are' => AboutWhoWeAreSection::class,
            'products' => AboutProductSection::class,
            'mission' => AboutMissionSection::class,
            'timeline' => AboutTimelineSection::class,
            'dealers' => AboutDealerSection::class,
        ];

        if (!isset($modelMap[$section])) {
            return redirect()->route('admin.content-management.about-us.index')
                ->withErrors(['Invalid section']);
        }

        $model = $modelMap[$section]::with('translations')->findOrFail($id);

        return view("admin-views.content-management.about.sections.edit.$section", compact('model', 'section'));
    }

    public function update(Request $request, $section, $id)
    {

        $modelMap = [
            'hero' => AboutHeroSection::class,
            'who_we_are' => AboutWhoWeAreSection::class,
            'products' => AboutProductSection::class,
            'mission' => AboutMissionSection::class,
            'timeline' => AboutTimelineSection::class,
            'dealers' => AboutDealerSection::class,
        ];

        if (!isset($modelMap[$section])) {
            return back()->withErrors(['Invalid section']);
        }

        $modelClass = $modelMap[$section];
        $model = $modelClass::findOrFail($id);


        $defaultLangIndex = array_search('en', $request->input('lang', []));

        $data = $request->except('_token', '_method', 'lang', 'heading', 'subheading', 'title', 'content', 'description', 'section');

        if ($defaultLangIndex !== false) {
            if ($request->has('heading')) {
                $data['heading'] = $request->input('heading')[$defaultLangIndex];
            }
            if ($request->has('subheading')) {
                $data['subheading'] = $request->input('subheading')[$defaultLangIndex];
            }
            if ($request->has('title')) {
                $data['title'] = $request->input('title')[$defaultLangIndex];
            }
            if ($request->has('content')) {
                $data['content'] = $request->input('content')[$defaultLangIndex];
            }
            if ($request->has('description')) {
                $data['description'] = $request->input('description')[$defaultLangIndex];
            }
            if ($request->has('dealer_name')) {
                $data['dealer_name'] = $request->input('dealer_name')[$defaultLangIndex];
            }
            if ($request->has('location')) {
                $data['location'] = $request->input('location')[$defaultLangIndex];
            }
        }

        if ($request->remove_image == 1) {
            if ($model->image) {
                Storage::disk('public')->delete($model->image);
            }

            $data['image'] = null; // set column to null
        }

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('about', 'public');
        }

        $model->fill($data);
        $model->save();


        $this->translationRepo->update($request, $modelClass, $id);

        return redirect()->back()
            ->with('success', 'Data updated successfully.');
    }


    public function toggleStatus(Request $request)
    {

        $request->validate([
            'id' => 'required|integer',
            'section' => 'required|string',
        ]);

        $section = $request->input('section');
        $id = $request->input('id');

        $modelMap = [
            'hero' => AboutHeroSection::class,
            'who_we_are' => AboutWhoWeAreSection::class,
            'products' => AboutProductSection::class,
            'mission' => AboutMissionSection::class,
            'timeline' => AboutTimelineSection::class,
            'dealers' => AboutDealerSection::class,
        ];

        if (!isset($modelMap[$section])) {
            return response()->json(['message' => 'Invalid section.'], 400);
        }

        $modelClass = $modelMap[$section];
        $item = $modelClass::find($id);

        if (!$item) {
            return response()->json(['message' => 'Item not found.'], 404);
        }

        $item->is_active = $item->is_active ? 0 : 1;
        $item->save();

        return response()->json(['message' => 'Status updated successfully.']);
    }
}
