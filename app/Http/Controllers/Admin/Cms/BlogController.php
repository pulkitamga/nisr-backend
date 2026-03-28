<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;
use App\Contracts\Repositories\TranslationRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Traits\AuthorizesCmsSection;
use App\Traits\CommonTrait;
use App\Traits\PaginatorTrait;
use App\Support\CmsContentSanitizer;
use App\Utils\ImageManager;

class BlogController extends Controller
{


    use PaginatorTrait;
    use CommonTrait;
    use AuthorizesCmsSection;

    public function __construct(
        private readonly ProductRepositoryInterface     $productRepo,
        private readonly TranslationRepositoryInterface     $translationRepo,

    ) {
        $this->middleware($this->cmsPermissionMiddleware('cms_section.read'))->only(['index', 'create', 'edit']);
        $this->middleware($this->cmsPermissionMiddleware('cms_section.create'))->only(['store']);
        $this->middleware($this->cmsPermissionMiddleware('cms_section.update'))->only(['update', 'toggleStatus']);
        $this->middleware($this->cmsPermissionMiddleware('cms_section.delete'))->only(['destroy']);
    }


    public function index(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string|max:255',
            'category' => 'nullable|string',
        ]);

        // Fetching blog posts with search and filter functionality
        $query = Blog::query();

        // Apply search filter (by heading or description)
        if ($request->has('search') && $request->search != '') {
            $searchTerm = addcslashes($request->search, '\\%_');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('heading', 'like', '%' . $searchTerm . '%')
                    ->orWhere('description', 'like', '%' . $searchTerm . '%');
            });
        }

        // Apply category filter
        if ($request->has('category') && $request->category != '') {
            $query->where('category', $request->category);
        }

        // Pagination
        $blogs = $query->with('translations')->paginate(10);


        // ✅ Get distinct categories from blog table
        $categories = Blog::select('category')->distinct()->pluck('category');

        return view('admin-views.content-management.blog.index', compact('blogs', 'categories'));
    }
    public function create()
    {
        $categories = ['Technology', 'Food', 'Travel', 'Health', 'Social Media', 'Business'];
        $blogTypes = ['Featured Posts', 'Latest Blog', 'Trending Blog'];

        return view('admin-views.content-management.blog.create', compact('categories', 'blogTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'heading' => 'required|array',
            'heading.*' => 'required|string|max:255',

            'description' => 'required|array',
            'description.*' => 'required|string',

            'image' => 'required|image|mimes:jpg,jpeg,png|max:2048',

            'blog_type' => 'required|in:Featured Posts,Latest Blog,Trending Blog',

            'category' => 'required|in:Technology,Food,Travel,Health,Social Media,Business',

            'lang' => 'required|array',
            'lang.*' => 'required|string',
        ]);

        $sanitizedDescriptions = CmsContentSanitizer::sanitizeRichTextArray($request->input('description', []));
        $request->merge([
            'description' => $sanitizedDescriptions,
        ]);

        $imageName = ImageManager::upload('blog/', 'webp', $request->file('image'));
        $imagePath = 'blog/' . $imageName;

        // ✅ Default language ka index nikaalo
        $defaultLangIndex = getDefaultLanguageIndex($request);

        // ✅ Jo fields multi-lang hain unka sirf default language ka value nikalo
        $input = $request->except('_token', 'lang');

        foreach ($input as $key => $value) {
            if (is_array($value) && isset($value[$defaultLangIndex])) {
                $input[$key] = $value[$defaultLangIndex]; // Default language value
            }
        }

        $heading = is_array($request->heading) ? ($request->heading[$defaultLangIndex] ?? null) : $request->heading;
        $description = is_array($request->description) ? ($request->description[$defaultLangIndex] ?? null) : $request->description;

        $blog = new Blog();

        $blog->heading = $heading;
        $blog->description = $description;
        $blog->image = $imagePath ?? null;
        $blog->blog_type = $request->blog_type;
        $blog->category = $request->category;

        $blog->save();


        $this->translationRepo->add($request, Blog::class, $blog->id);


        return redirect()->route('admin.content-management.blog')->with('success', 'Blog created successfully!');
    }

    public function edit($id)
    {
        $blog = Blog::with('translations')->findOrFail($id);
        $categories = ['Technology', 'Food', 'Travel', 'Health', 'Social Media', 'Business'];
        $blogTypes = ['Featured Posts', 'Latest Blog', 'Trending Blog'];

        return view('admin-views.content-management.blog.edit', compact('blog', 'categories', 'blogTypes'));
    }
    public function destroy($id)
    {
        $blog = Blog::findOrFail($id);

        if (!empty($blog->image)) {
            ImageManager::delete($blog->image);
        }

        // Delete the blog
        $blog->delete();

        return redirect()->route('admin.content-management.blog')->with('success', 'Blog deleted successfully!');
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'heading' => 'required|array',
            'heading.*' => 'string|max:255',
            'description' => 'required|array',
            'description.*' => 'string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'blog_type' => 'required|in:Featured Posts,Latest Blog,Trending Blog',
            'category' => 'required|in:Technology,Food,Travel,Health,Social Media,Business',
            'lang' => 'required|array'
        ]);

        $sanitizedDescriptions = CmsContentSanitizer::sanitizeRichTextArray($request->input('description', []));
        $request->merge([
            'description' => $sanitizedDescriptions,
        ]);

        $blog = Blog::findOrFail($id);

        // Set default language fields to blog model
        $defaultLangIndex = getDefaultLanguageIndex($request);
        if ($defaultLangIndex !== false) {
            $blog->heading = $request->heading[$defaultLangIndex];
            $blog->description = $request->description[$defaultLangIndex];
        }

        $blog->blog_type = $request->blog_type;
        $blog->category = $request->category;

        // Image handling
        if ($request->hasFile('image')) {
            if (!empty($blog->image)) {
                ImageManager::delete($blog->image);
            }
            $imageName = ImageManager::upload('blog/', 'webp', $request->file('image'));
            $blog->image = 'blog/' . $imageName;
        }

        $blog->save();

        // Update translations
        $this->translationRepo->update(
            request: $request,
            model: Blog::class,
            id: $id
        );

        return redirect()->route('admin.content-management.blog')->with('success', 'Blog updated successfully!');
    }

    public function toggleStatus($id)
    {
        $blog = Blog::findOrFail($id);

        // Toggle the status
        $blog->status = $blog->status == 1 ? 0 : 1;
        $blog->save();

        return response()->json([
            'status' => true,
            'message' => 'Blog status updated successfully!',
            'new_status' => $blog->status
        ]);
    }
}
