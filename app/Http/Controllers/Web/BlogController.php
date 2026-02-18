<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Blog;
use App\Contracts\Repositories\RobotsMetaContentRepositoryInterface;


class BlogController extends Controller
{

    public function __construct(
        private readonly RobotsMetaContentRepositoryInterface $robotsMetaContentRepo,
    ) {}

    public function show($id)
    {

        
        $blog = Blog::where('id', $id)->where('status', 1)->firstOrFail();

        $featuredPosts = Blog::where('status', 1)
            ->where('blog_type', 'Featured Posts')
            ->where('id', '!=', $blog->id)
            ->take(4)
            ->get();


        $themeName = theme_root_path(); // Automatically gets theme like 'default', etc.

        return match ($themeName) {
            'default' => view('default.web-views.pages.blog-details', compact('blog', 'featuredPosts')),
            'theme_aster' => view('theme_aster.web-views.pages.blog-details', compact('blog', 'featuredPosts')),
            'theme_fashion' => view('theme_fashion.web-views.pages.blog-details', compact('blog', 'featuredPosts')),
            default => abort(404),
        };
    }

    public function blogsByCategory($category)
    {
        // Valid category check (optional but recommended)
        $validCategories = ['Technology', 'Food', 'Travel', 'Health', 'Social Media', 'Business'];
        if (!in_array($category, $validCategories)) {
            abort(404); // Invalid category to 404
        }

        // Category ke blogs get karo (only active)
        $blogs = Blog::where('status', 1)
            ->where('category', $category)
            ->latest()
            ->paginate(9); // pagination bhi daal diya bhai

        // Theme path jaisa show me kiya tha
        $themeName = theme_root_path();

        return match ($themeName) {
            'default' => view('default.web-views.pages.blogs-by-category', compact('blogs', 'category')),
            'theme_aster' => view('theme_aster.web-views.pages.blog-by-category', compact('blogs', 'category')),
            'theme_fashion' => view('theme_fashion.web-views.pages.blog-by-category', compact('blogs', 'category')),
            default => abort(404),
        };
    }
}
