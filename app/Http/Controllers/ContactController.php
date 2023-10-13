<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SupportCategory;
use App\Models\SupportArticle;
use App\Models\Blog;
use App\Models\Page;
use App\Models\LandingPage;

class ContactController extends Controller
{
    public function getSupportArticle()
    {
        $page = LandingPage::where("type", 1)->first();
        if($page){
			$page = $page->page;			
		}
        $articles = SupportCategory::with('supportArticles', 'supportArticles.page', 'page')->where('status', 1)->orderBy('sortorder', 'asc')->get();
        if($articles){
            return response()->json(["articles" => $articles, "page" => $page]);
        }
        return response()->json(['error' => 'No Record Found'], 422);
    }
	public function getSingleArticle(Request $request)
    {
		if($request->get('type') == "search"){
			$articles = SupportArticle::whereHas('page', function($query) use($request){
				$query->where('title', 'ilike', '%'.$request->pagename.'%');
			})->where('title', 'ilike', '%'.$request->pagename.'%')->with('page', 'SupportCategory', 'SupportCategory.page')->get();
			//$articles->pageable->support_articles = $articles->pageable()->supportArticles()->get();
			//return $articles;
			//$articles->pageable()->support_articles = $articles->pageable()->with('page', 'supportCategory', 'supportCategory.page')->get();
            $article = Page::with('pageable')->where('page_name', $request->get('pagename'))->first();
			$blog = $this->getBlogPost();
            return [
                'is_search' => true,
                'searcharticles' => $articles,
				'article' => $article,
                'blog' => $blog
            ];
		}
		elseif($request->get('type') == "article"){
            $article = Page::with('pageable', 'pageable.supportCategory')->where('page_name', $request->get('pagename'))->first();
            $blog = $this->getBlogPost();
            if($article){
                return [
                    'article' => $article,
                    'blog' => $blog
                ];
            }
			
        }
		elseif($request->get('type') == "category"){
            $articles = Page::with('pageable', 'pageable.supportArticles', 'pageable.supportArticles.page')->when($request->get('type') == "category", function ($query) use($request){
				$query->where('page_name', $request->get('pagename'));
			})->first();
            $blog = $this->getBlogPost();
            return [
                'is_category' => true,
                'articles' => $articles,
                'blog' => $blog
            ];
        }
        /*if($request->get('type') == "search"){
			$search = $request->get('value');
            $articles = SupportArticle::with('page', 'supportCategory', 'supportCategory.page')->where('title', 'LIKE', '%'.$search.'%')->get();
            $blog = $this->getBlogPost();
            return [
                'is_search' => true,
                'articles' => $articles,
                'blog' => $blog
            ];
        }
        elseif($request->get('type') == "article"){
            $article = Page::with('pageable', 'pageable.supportCategory')->where('page_name', $request->get('pagename'))->first();
            $blog = $this->getBlogPost();
            if($article){
                return [
                    'article' => $article,
                    'blog' => $blog
                ];
            }
        }
		elseif($request->get('type') == "category"){
            $articles = Page::with('pageable', 'pageable.supportArticles', 'pageable.supportArticles.page')->where('page_name', $request->get('pagename'))->first();
            $blog = $this->getBlogPost();
            return [
                'is_category' => true,
                'articles' => $articles,
                'blog' => $blog
            ];
        }*/
        return response()->json(['error' => 'No Record Found'], 422);
    }
    public function getBlogPost(){
        $post = Blog::where('status', 1)->orderBy('created_at', 'desc')->first();
        return $post;
    }
}
