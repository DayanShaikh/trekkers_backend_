<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\LandingPage;

class BlogController extends Controller
{
    public function getBlog(Request $request)
    {
		$page = LandingPage::where("type", 3)->first();
		if($page){
			$page = $page->page;			
		}
		if($request->seo_url){
            $blogs = Blog::with('blogCategories')->whereHas('blogCategories', function($q) use($request){
                $q->where('seo_url', $request->get('seo_url'));
            })->orderBy('published_at', 'desc')->paginate($request->get("limit", 10));
			
			$related_blogs = Blog::whereHas('blogCategories', function($q) use($request){
                $q->where('seo_url', $request->get('seo_url'));
            })->orderBy('published_at', 'desc')->take(5)->get();
        }
		else{
			$blogs = Blog::with('blogCategories')->when($request->get('search'), function ($query) use($request){
				$query->where('title', 'ilike', '%'.$request->search.'%')
				->orWhere('excerpt', 'ilike', '%'.$request->search.'%');
			})
			->orderBy('published_at', 'desc')
			->paginate($request->get("limit", 10));
			$related_blogs = Blog::where('status', 1)->orderBy('published_at', 'desc')->take(5)->get();
		}
		$categories= BlogCategory::where('status', 1)->orderBy('title')->get();
		$response = ['blogs' => $blogs, 'related_blogs' => $related_blogs, 'categories' => $categories, 'page' => $page]; 
		return $response;
        
    }

    public function getSingleblog(Request $request)
    {
		$blog = Blog::with('blogCategories')->where('seo_url', $request->get('pagename'))->first();
		$blog->blogs = Blog::whereHas('blogCategories', function($q) use($blog){
                //$q->where('blog_id', $blog->id);
            })->orderBy('published_at', 'desc')->take(3)->get();
		$blog->categories = BlogCategory::where('status', 1)->orderBy('title')->get();
		$blog->related_blogs = $blog->relatedBlogs()->orderBy('published_at', 'desc')->take(5)->get();
        //$blog->with('blogCategories')->first();
        return $blog; 
    }
	
	public function blogPost(Request $request)
	{
		$blog = Blog::whereIn('id', collect($request->ids)->unique()->values())->orderBy('created_at')->first(['id','title', 'seo_url', 'image']);
		if($blog){
			return response()->json(["blog" => $blog]);
		}
		return response()->json(['error' => 'No Record Found'], 422);
	}
}