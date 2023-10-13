<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Page;
use App\Models\Blog;
use App\Models\Trip;

class PageController extends Controller
{
    public function getPage(Request $request)
    {
		//return $request;
        $page = Page::with('pageable', 'pageGalleries', 'pageable.location', 'pageable.location.locationDays', 'pageable.location.trips')->where("page_name", $request->get("pageName"))->first();
        if($page){
            return response()->json($page);
        }
        return response()->json(['status' => false, 'error' => 'No Record Found'], 422);

    }
	public function getCountries($page){
        $pageCountries = $page->with(['pageCountries', 'pageCountries.headerVideo'])->first();
        if($pageCountries){
			$offset = 0;
			$ids = [];
			while($index = strpos($pageCountries->content,'[blogpost id="', $offset)){
				$index += strlen( '[blogpost id="' );
				$endIndex = strpos($pageCountries->content,'"]', $index);
				$strIds = substr($pageCountries->content, $index, $endIndex-$index);
				array_push($ids, ...explode(',', $strIds));
				$pageCountries->content = str_replace( '[blogpost id="'.$strIds.'"]', '<app-blog-post blogs="['.$strIds.']"></app-trip-slider>', $page->content );
				$offset = $endIndex;
			}
            //$pageCountries->blogpost = Blog::whereIn('id', collect($ids)->unique()->values())->orderBy('created_at')->get(['id','title','seo_url','image']);
            //return $pageCountries->pageCountries[0]["trip_ids"];
            $pageCountries->trip = Trip::with('ageGroup:id,short_title', 'location:id,icons')->whereIn('id', $pageCountries->pageCountries[0]["trip_ids"])->get(['id','location_id','age_group_id','trip_name','duration','short_details','trip_main_image','trip_fee']);
            return response()->json($pageCountries);
        }
        return response()->json(['error' => 'No Record Found'], 422);
    }
}
