<?php

namespace Database\Seeders;
use App\Models\LocationDay;
use App\Models\PageDag;
use App\Models\PageGallery;
use App\Models\SupportArticle;
use App\Models\SupportCategory;
use App\Models\Page;
use App\Models\Trip;
use App\Models\TripBooking;
use App\Models\TripBookingAddon;
use App\Models\TripBookingDocument;
use App\Models\TripBookingExtraInsurance;
use App\Models\TripBookingNote;
use App\Models\PassportDetail;
use App\Models\TripTemplate;
use App\Models\TripType;
use App\Models\TripTicket;
use App\Models\TripTicketUser;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\AgeGroup;
use App\Models\AgeGroupMonthMeta;
use App\Models\Airline;
use App\Models\Attribute;
use App\Models\ConfigPage;
use App\Models\ConfigVariable;
use App\Models\BlogCategory;
use App\Models\Blog;
use App\Models\FrontMenu;
use App\Models\HeaderVideo;
use App\Models\LocationAddon;
use App\Models\Location;
use App\Models\LocationAgeGroup;
use App\Models\LocationPickup;
use App\Models\Destination;
use App\Models\LandingPage;
use App\Models\ForumTopic;
use App\Models\ForumReply;
use App\Models\ForumCategory;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\QuizQuestion;
use App\Models\QuizQuestionOption;
use App\Models\QuizQuestionAnswer;
use App\Models\Reservation;
use App\Models\TripTourGuide;
use App\Models\UserField;
use App\Utility;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NewDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function clean($text){
		$text = utf8_decode($text);
		$text = stripslashes($text);
		return str_replace("https://beta.simi-reizen.nl/uploads/page_images/dag/", "https://d1n5x7e4cmsf36.cloudfront.net/public/uploads/", str_replace("https://beta.simi-reizen.nl/uploads/page_images/gallery/", "https://d1n5x7e4cmsf36.cloudfront.net/public/uploads/", str_replace("https://beta.simi-reizen.nl/uploads/upload_files/", "https://d1n5x7e4cmsf36.cloudfront.net/public/uploads/", str_replace("www.", "", str_replace("simi-reizen.nl", "beta.simi-reizen.nl", $text)))));
	}
	public function cleanMenuUrl($text){
		$text = utf8_decode($text);
		$text = stripslashes($text);
		return str_replace("https://www.simi-reizen.nl/", "", $text);
	}
	public function copyImage($url, $path, $skip=true){
		if($skip){
			return $path;
		}
		if(!Storage::disk("s3")->exists($path)){
			$url = "https://www.simi-reizen.nl/uploads/".$url;
			if(is_array(getimagesize($url))){
				Storage::disk("s3")->put($path, file_get_contents($url));
			}
			else{
				return '';
			}
		}
		return $path;
	}
    public function run()
    {
        $pageIds = [];
		$backup = DB::connection(env('DB_BACKUP_DB', 'mysql_backup'));
		$conn = DB::connection(config('database.default'));
        Role::truncate();
        User::truncate();
        Page::truncate();
        LandingPage::truncate();
        $role = Role::create([
            'name' => 'Admin',
			"creater_id" => 1,
        ]);
		$client = Role::create([
            'name' => 'Client',
        ]);
		$tourGuide = Role::create([
            'name' => 'Tour Guide',
        ]);
		$travelAdmin = Role::create([
            'name' => 'Travel Admin',
        ]);
		$travelBrand = Role::create([
            'name' => 'Travel Brand',
        ]);

        $user = User::create([
            'name' => 'admin',
            'email' => 'admin@simi-reizen.nl',
            'password' => bcrypt('secret')
        ]);

        $user = User::create([
            'name' => 'user',
            'email' => 'user@simi-reizen.nl',
            'password' => bcrypt('secret')
        ]);

        $user->roles()->attach($role);

        /*********Age Group***********/
        $ageGroups = $backup->table('age_group')->select('*')->get();
        AgeGroup::truncate();
        foreach($ageGroups as $ageGroup){
            $ageGroupDb = AgeGroup::create([
                "id" => $ageGroup->id,
                "title" => $this->clean($ageGroup->title),
                "sortorder" => $ageGroup->sortorder,
                "creater_id" => 1,
                "status" => $ageGroup->status,
                "created_at" => $ageGroup->ts,
				"updated_at" => $ageGroup->ts,
            ]);
		}
		
		//Home Pages
		$ageGroup = $ageGroups[0];
		$landingPage = LandingPage::create([
			"type" => 0,
			"creater_id" => 1,
			"created_at" => Carbon::now(),
			"updated_at" => Carbon::now(),
		]);
		$landingPage->page()->create([
			"page_name" => $this->clean($ageGroup->seo_url),
			"title" => $this->clean($ageGroup->title),
			"content" =>  "",
			"highlights" => $this->clean($ageGroup->details),
			"image" => $this->copyImage("age_group/header_images/".$ageGroup->header_image, "public/pages/".$ageGroup->header_image),
			"meta_title" => $this->clean($ageGroup->meta_title),
			"meta_description" => $this->clean($ageGroup->meta_description),
			"meta_keywords" => $this->clean($ageGroup->meta_keywords),
			"creater_id" => 1,
			"created_at" => $ageGroup->ts,
			"updated_at" => $ageGroup->ts,
		]);
		$ageGroupMonths = $backup->table('age_group_months_meta')->select('*')->where("age_group_id", $ageGroup->id)->get();
		foreach($ageGroupMonths as $ageGroupMonth){
			$landingPage = LandingPage::create([
				"type" => 2,
				"month" => $ageGroupMonth->month,
				"creater_id" => 1,
				"created_at" => Carbon::now(),
				"updated_at" => Carbon::now(),
			]);
			$landingPage->page()->create([
				"page_name" => "reizen-vertrek-".Utility::getMonthShortName($ageGroupMonth->month),
				"title" => "Reizen met vertrek in ".Utility::getMonthName($ageGroupMonth->month),
				"content" => $this->clean($ageGroupMonth->text),
				"meta_title" => $this->clean($ageGroupMonth->meta_title),
				"meta_description" => $ageGroupMonth->meta_description,
				"meta_keywords" => $this->clean($ageGroupMonth->meta_keywords),
				"creater_id" => 1,
				"created_at" => Carbon::now(),
				"updated_at" => Carbon::now(),
			]);
		}

		/*********Destination***********/
		$destinations = $backup->table('location_country')->select(DB::raw('location_country.*, trip_country.travel_insurance_fees, trip_country.is_survival_adventure_insurance_active'))->leftJoin("location", "location.location_country_id", "=", "location_country.id")->leftJoin("trip_country", "location.country_id", "=", "trip_country.id")->groupBy("location_country.id")->orderBy("location_country.id")->get();
		$i = 1;
        Destination::truncate();
		foreach($destinations as $destination){
			$destinationDb = Destination::create([
				"id" => $destination->id,
				"title" => $this->clean($destination->title),
				"iso_code" => $destination->iso_code,
				"travel_insurance_fees" => $destination->travel_insurance_fees,
				"status" => $destination->status,
				"sortorder" => $i++,
				"creater_id" => 1,
				"created_at" => $destination->ts,
				"updated_at" => $destination->ts
			]);
			$page = $backup->table('location')->select(DB::raw("pages.id as page_id, pages.*, page_country.*"))->join("pages", "location.country_page_id", "=", "pages.id")->join("page_country", "pages.id", "=", "page_country.page_id")->where('location_country_id', "=", $destination->id)->where("country_page_id", "!=", "0")->where("location.status", "=", 1)->where("pages.status", "=", 1)->first();
			if($page) {
				$destinationDb->intro_title = $this->clean($page->intro_title);
				$destinationDb->intro_text = $this->clean($page->intro_text);
				$destinationDb->intro_video = $this->clean($page->intro_video);
				$destinationDb->video_text = $this->clean($page->video_text);
				$destinationDb->header_video_id = $this->clean($page->header_video_id);
				$destinationDb->trip_title = $this->clean($page->trips_title);
				$destinationDb->other_trip_title = $this->clean($page->other_trips_title);
				$destinationDb->trip_toggle = $page->trip_toggle==1;
				$destinationDb->thumb_image = $page->thumb_image ? $this->copyImage("country_thumb/".$page->thumb_image, "public/destinations/".$page->thumb_image) : '';
				$destinationDb->save();

				$pageDb = $destinationDb->page()->create([
					"page_name" => ($page->seo_url_path ? $this->clean($page->seo_url_path)."/": '').$this->clean($page->seo_url),
					"title" => $this->clean($page->title),
					"content" => $this->clean($page->body),
					"highlights" => $this->clean($page->highlights),
					"image" => $page->image ? $this->copyImage("page_images/".$page->image, "public/pages/".$page->image) : '',
					"meta_title" => $this->clean($page->meta_title),
					"meta_description" => $this->clean($page->meta_description),
					"meta_keywords" => $this->clean($page->meta_keywords),
					"sitemap_title" => $this->clean($page->sitemap_title),
					"sitemap_details" => $this->clean($page->sitemap_details),
					"header_details" => $page->show_default_header_details!=1 ? $this->clean($page->header_details) : '',
					"show_schema_markup" => $page->show_schema_markup==1,
					"schema_title" => $this->clean($page->schema_title),
					"show_search_box" => $page->show_search_box,
					"creater_id" => 1,
					"created_at" => $page->ts,
					"updated_at" => $page->ts,
				]);
				$pageIds[$page->id] = $pageDb->id;
				// $destinationTrips = $backup->table('page_country_trips')->where(['type' => 0, 'page_id' => $pageDb->id])->select('*')->get();
				// if($destinationTrips){
				// 	$tripIds = [];
				// 	foreach($destinationTrips as $destinationTrip){
				// 		$tripIds[] = ["trip_id" => $destinationTrip->trips_id, "type" => 0];
				// 	}
				// 	$destinationDb->trips()->sync($tripIds);
				// }
				/*foreach($destinationTrips as $destinationTrip){
					$conn->insert('insert into destination_trip values(null,?,?,?)',[
		                $destination->id,
		                $destinationTrip->trips_id,
		                0
		            ]);
				}*/
			}
			else{
				echo $destination->id."\n";
			}
		}

		/*********Attributes***********/
		$attributeIds = [
			"13" => "13",
			"11" => "14",
			"4" => "16",
			"7" => "15",
			"12" => "20",
			"8" => "18",
			"10" => "19",
			"14" => "21"
		];
		$attributes = $backup->table('trip_country')->select('*')->get();
        Attribute::truncate();
		foreach($attributes as $attribute){
			
			$attrbiuteDb = Attribute::create([
				"id" => $attributeIds[$attribute->id],
				"title" => $this->clean($attribute->country),
				"seo_url_path" => Str::slug($this->clean($attribute->country)),
				"sortorder" => $attribute->sortorder,
				"status" => $attribute->status,
				"creater_id" => 1,
				"created_at" => $attribute->ts,
				"updated_at" => $attribute->ts,
			]);
		}
		
		/*********Location***********/
		$locations = $backup->table('location')->get();
        Location::truncate();
		foreach($locations as $location){
			$locationAgeGroup = $backup->table("location_2_age_group")->where("location_id", $location->id)->where("age_group_id", $ageGroup->id)->first();
			$trips = $backup->table("trips")->where("location_id", $location->id)->where("age_group_id", $ageGroup->id)->first();
			if(!$locationAgeGroup){
				$locationAgeGroup = $backup->table("location_2_age_group")->where("location_id", $location->id)->first();
				$trips = $backup->table("trips")->where("location_id", $location->id)->first();
			}
			if(!$locationAgeGroup){
				echo $location->title.PHP_EOL;
				continue;
			}
			$locationDb = Location::create([
				"id" => $location->id,
				"destination_id" => $location->location_country_id,
				"title" => $this->clean($location->title),
				"trip_fee" => $location->trip_fee,
				"travel_time" => $location->travel_time,
				"upsell_email_title" => $location->upsell_email_title,
				"upsell_email_content" => $this->clean($location->upsell_email_content),
				"upsell_email_title2" => $location->upsell_email_title1,
				"upsell_email_content2" => $this->clean($location->upsell_email_content1),
				"has_flight" => $location->has_flight,
				"icons" => explode(",", $location->icons),
				"require_passport_details" => $location->require_passport_details,
				"trip_level" => $locationAgeGroup->trip_level,
				"included" => $locationAgeGroup->included,
				"travel_information" => $this->clean($locationAgeGroup->travel_information),
				"program_details" => $locationAgeGroup->program_details,
				"packing_list" => $this->clean($locationAgeGroup->packing_list),
				"faqs" => $this->clean($locationAgeGroup->faqs),
				"faqs_new" => $this->clean($locationAgeGroup->faqs_new),
				"reviews" => $this->clean($locationAgeGroup->reviews),
				"review_text" => $this->clean($locationAgeGroup->review_text),
				"listing_title" => $this->clean($locationAgeGroup->listing_title),
				"listing_text" => $this->clean($locationAgeGroup->listing_text),
				"listing_image" => $locationAgeGroup->listing_image ? $this->copyImage("location_2_age_group_images/listing_images/".$locationAgeGroup->listing_image, "public/locations/".$locationAgeGroup->listing_image) : '',
				"marketing_text" => $trips ? $this->clean($trips->marketing_text) : '',
				"excursions" => $this->clean($locationAgeGroup->excursions),
				"combination" => $locationAgeGroup->combination,
				"flight" => $locationAgeGroup->flight,
				"meals" => $locationAgeGroup->meals,
				"min_people" => $locationAgeGroup->min_people,
				"baggage" => $locationAgeGroup->baggage,
				"sortorder" => $location->sortorder,
				"status" => $location->status == 1,
				"creater_id" => 1,
				"created_at" => $location->ts,
				"updated_at" => $location->ts,
			]);
			$attributes = $backup->table("location")->select(DB::raw("DISTINCT(attributes_id) as attribute_id"))->join("trips", "location.id", "=", "trips.location_id")->join("trips_2_attributes", "trips.id",  "=", "trips_2_attributes.trip_id")->where("trips.status", "=", 1)->where("trips.archive", "=", 0)->where("location_id", "=", $location->id)->get();
			if($attributes){
				$attributeIds = [];
				foreach($attributes as $attribute){
					$attributeIds[] = $attribute->attribute_id;
				}
				$locationDb->attributes()->sync($attributeIds);
			}
			if($locationAgeGroup->page_id){
				$page = $backup->table('pages')->where("id", $locationAgeGroup->page_id)->select("*")->first();
				if(!$page){
					echo $locationAgeGroup->page_id." page does not exists".PHP_EOL;
					$locationDb->page()->create([
						"page_name" => "location-".$locationDb->id,
						"title" => $this->clean($location->title),
						"creater_id" => 1,
						"created_at" => $locationAgeGroup->ts,
						"updated_at" => $locationAgeGroup->ts,
					]);
				}
				else{
					$pageDb = $locationDb->page()->create([
						"page_name" => $page->seo_url_path . ($page->seo_url_path ? '/' : '') . $page->seo_url,
						"title" => $this->clean($page->title),
						"content" => $this->clean($page->body),
						"highlights" => $this->clean($page->highlights),
						"header_details" => $page->show_default_header_details!=1 ? $this->clean($page->header_details) : '',
						"image" => $page->image ? $this->copyImage("page_images/".$page->image, "public/pages/".$page->image) : '',
						"meta_title" => $this->clean($page->meta_title),
						"meta_description" => $this->clean($page->meta_description),
						"meta_keywords" => $this->clean($page->meta_keywords),
						"sitemap_title" => $this->clean($locationAgeGroup->sitemap_title),
						"sitemap_details" => $this->clean($locationAgeGroup->sitemap_details),
						"creater_id" => 1,
						"created_at" => $locationAgeGroup->ts,
						"updated_at" => $locationAgeGroup->ts,
					]);
					$pageIds[$page->id] = $pageDb->id;
				}
			}
		}
		//print_r($pageIds);die;
		/*********Page Dags***********/
		$pageDags = $backup->table('page_dag')->select('*')->get();
		foreach($pageDags as $pageDag){
			if(!isset($pageIds[$pageDag->pages_id])){

				continue;
			}
			$location = $backup->table('location_2_age_group')->where("page_id", $pageDag->pages_id)->where("age_group_id", $ageGroup->id)->select("*")->first();
			if($location) {
				$locationDays[] = $location->location_id;
				LocationDay::create([
					"location_id" => $location->location_id,
					"title" => $this->clean($pageDag->title),
					"image" => $pageDag->image ? $this->copyImage("page_images/dag/".$pageDag->image, "public/location-days/".$pageDag->image) : '',
					"description" => $pageDag->description,
					"sortorder" => $pageDag->sortorder,
					"status" => $pageDag->status,
					"creater_id" => 1,
					"created_at" => $pageDag->ts,
					"updated_at" => $pageDag->ts,
				]);
			}
			else{
				print_r($pageDag);
			}
		}

		//Info Pages
		$pages = $backup->table("pages")->where("page_type", 1)->orderBy("id", "desc")->get();
		foreach($pages as $page){
			Page::create([
				"page_name" => $page->seo_url_path . ($page->seo_url_path ? '/' : '') . $page->seo_url,
				"title" => $this->clean($page->title),
				"content" => $this->clean($page->body),
				"highlights" => $this->clean($page->highlights),
				"header_details" => $page->show_default_header_details!=1 ? $this->clean($page->header_details) : '',
				"image" => $page->image ? $this->copyImage("page_images/".$page->image, "public/pages/".$page->image) : '',
				"meta_title" => $this->clean($page->meta_title),
				"meta_description" => $this->clean($page->meta_description),
				"meta_keywords" => $this->clean($page->meta_keywords),
				"sitemap_title" => $this->clean($page->sitemap_title),
				"sitemap_details" => $this->clean($page->sitemap_details),
				'show_schema_markup' =>  $page->show_schema_markup,
				'schema_title'    =>  $page->schema_title,
				'show_search_box' => $page->show_search_box,
				"creater_id" => 1,
				"created_at" => $page->ts,
				"updated_at" => $page->ts,
			]);
		}
		/*********Country Pages********/
		//$countryPages = [315, 151, 337];
		
		/*********Airlines***********/
        $airlines = $backup->table('airline')->select('*')->get();
        Airline::truncate();
        foreach($airlines as $airline){
            Airline::create([
                "id" => $airline->id,
                "title" => $this->clean($airline->title),
                "details" => $this->clean($airline->details),
                "status" => $airline->status,
				"creater_id" => 1,
                "created_at" => $airline->ts,
				"updated_at" => $airline->ts,
            ]);
        }

       
        /*********Blog Categories***********/
        $blogCats = $backup->table('blog_categories')->select('*')->get();
        BlogCategory::truncate();
        foreach($blogCats as $blogCat){
            BlogCategory::create([
                "id" => $blogCat->id,
                "title" => $this->clean($blogCat->title),
                "seo_url" => $this->clean($blogCat->seo_url),
                "meta_title" => $this->clean($blogCat->meta_title),
                "meta_description" => $this->clean($blogCat->meta_description),
				"creater_id" => 1,
                "status" => $blogCat->status,
                "created_at" => $blogCat->ts,
				"updated_at" => $blogCat->ts,
            ]);
        }

        /*********Blogs***********/
        $blogs = $backup->table('blog')->select('*')->get();
        Blog::truncate();
        foreach($blogs as $blog){
			echo $blog->date." ".$blog->time.PHP_EOL;
            Blog::create([
                "id" => $blog->id,
                "title" => $this->clean($blog->title),
                "seo_url" => $this->clean($blog->seo_url),
                "excerpt" => $this->clean($blog->excerpt),
                "content" => $this->clean($blog->content),
				"image" => $blog->image ? $this->copyImage("blog/".$blog->image, "public/blogs/".$blog->image) : '',
                "published_at" => Carbon::createFromFormat("Y-m-d Hi", $blog->date." ".str_replace(":", "", $blog->time))->format("Y-m-d H:i:s"),
                "meta_title" => $this->clean($blog->meta_title),
                "meta_description" => $this->clean($blog->meta_description),
                "meta_keywords" => $this->clean($blog->meta_keywords),
				"creater_id" => 1,
                "status" => $blog->status,
                "created_at" => $blog->ts,
				"updated_at" => $blog->ts,
            ]);
        }
        $conn->table('blog_blog_category')->truncate();
        $blogtoCats = $backup->table('blog_2_categories')->select('*')->get();
        foreach($blogtoCats as $blogtoCat){
            $conn->insert('insert into blog_blog_category values(?,?)', collect($blogtoCat)->values()->toArray());
        }

        /*********Front Menus***********/
        $frontMenus = $backup->table('frontmenus')->select('*')->get();
        FrontMenu::truncate();
        foreach($frontMenus as $frontMenu){
			if(empty($image)){}
            FrontMenu::create([
                "id" => $frontMenu->id,
                "position" => $frontMenu->position == "top"? 0: ($frontMenu->position == "bottom"? 1: 2),
                "title" => $this->clean($frontMenu->title),
				"url" => $this->cleanMenuUrl($frontMenu->url),
				"image" => $frontMenu->image ? $this->copyImage("frontmenu/".$frontMenu->image, "public/front-menus/".$frontMenu->image) : '',
                "parent_id" => $frontMenu->parentid,
                "sortorder" => $frontMenu->sortorder,
                "is_default" => $frontMenu->isdefault,
                "creater_id" => 1,
                "status" => $frontMenu->status,
                "created_at" => $frontMenu->ts,
				"updated_at" => $frontMenu->ts,
            ]);
        }

        /*********Header Videos***********/
        $videos = $backup->table('header_video')->select('*')->get();
        HeaderVideo::truncate();
        foreach($videos as $video){
            HeaderVideo::create([
                "id" => $video->id,
                "title" => $this->clean($video->title),
				"webm_format" => $video->webm_format ? $this->copyImage("header_video/webm/".$video->webm_format, "public/header-videos/".$video->webm_format) : '',
				"mp4_format" => $video->mp4_format ? $this->copyImage("header_video/mp4/".$video->mp4_format, "public/header-videos/".$video->mp4_format) : '',
                "creater_id" => 1,
                "status" => $video->status,
                "created_at" => $video->ts,
				"updated_at" => $video->ts,
            ]);
        }

        /*********Location Addon***********/
        $locationAddons = $backup->table('trip_addon')->select('*')->get();
        LocationAddon::truncate();
        foreach($locationAddons as $locationAddon){
            LocationAddon::create([
                "id" => $locationAddon->id,
                "location_id" => $locationAddon->location_id,
                "title" => $this->clean($locationAddon->title),
				"image" => $locationAddon->image ? $this->copyImage("trip_addon/".$locationAddon->image, "public/location-addons/image/".$locationAddon->image) : '',
				"mobile_image" => $locationAddon->mobile_image ? $this->copyImage("trip_addon/mobile_image/".$locationAddon->mobile_image, "public/location-addons/mobile-image/".$locationAddon->mobile_image) : '',
                "description" => $this->clean($locationAddon->description),
                "price" => $locationAddon->price,
                "is_public" => $locationAddon->is_public,
                "hide_payment" => $locationAddon->hide_payment,
                "sortorder" => $locationAddon->sortorder,
                "extra_field_1" => $locationAddon->extra_field_1,
                "extra_field_2" => $locationAddon->extra_field_2,
                "extra_field_3" => $locationAddon->extra_field_3,
				"creater_id" => 1,
                "status" => $locationAddon->status,
                "created_at" => $locationAddon->ts,
				"updated_at" => $locationAddon->ts,
            ]);
        }

        $conn->table('location_trip_type')->truncate();
        $types = $backup->table('location_2_trip_type')->select('*')->get();
        foreach($types as $type){
            $conn->insert('insert into location_trip_type values(?,?)', collect($type)->values()->toArray());
        }

        /*********Location Pickup***********/
        $locationPickups = $backup->table('location_pickup')->select('*')->get();
        LocationPickup::truncate();
        foreach($locationPickups as $locationPickup){
            LocationPickup::create([
                "id" => $locationPickup->id,
                "location_id" => $locationPickup->location_id,
                "place" => $locationPickup->place,
                "time" => $locationPickup->time,
                "spot" => $locationPickup->spot,
                "sortorder" => $locationPickup->sortorder,
                "status" => $locationPickup->status,
				"creater_id" => 1,
                "created_at" => $locationPickup->ts,
				"updated_at" => $locationPickup->ts,
            ]);
        }

		/*********Trips***********/
		$dependantTrips = [];
		$dependant_trips = $backup->table('dependent_trips')->select('*')->get();
        Trip::truncate();
        foreach($dependant_trips as $dependant_trip){
			if(isset($dependantTrips[$dependant_trip->location_id.$dependant_trip->date])){
				continue;
			}
			$tripsDb = $backup->table('trips')->select('*')->where("start_date", $dependant_trip->date)->where("location_id", $dependant_trip->location_id)->get();
			if($tripsDb->count() > 0) {
				$dependantTrips[$dependant_trip->location_id.$dependant_trip->date] = Trip::create([
					"id" => $dependant_trip->id,
					"location_id" => $dependant_trip->location_id,
					"total_space" => $dependant_trip->total_space,
					"male_female_important" => $dependant_trip->male_female_important,
					"show_client_detail" => $dependant_trip->show_client_detail,
					"start_date" => $dependant_trip->date,
					"duration" => $tripsDb[0]->duration,
					"trip_fee" => $tripsDb[0]->trip_fee,
					"trip_seats_status" => $tripsDb[0]->trip_seats_status,
					"is_not_bookable" => $tripsDb[0]->is_not_bookable,
					"archive" => $tripsDb[0]->archive,
					"is_full" => $tripsDb[0]->is_full,
					"status" => $dependant_trip->status,
					"creater_id" => 1,
					"created_at" => $dependant_trip->ts,
					"updated_at" => $dependant_trip->ts,
				]);
				
			}
			else{
				echo $dependant_trip->id." Trips not found\n";
			}
		}
		//Trip Ticket
		$tripTickets = $backup->table('trip_ticket')->select('*')->get();
        TripTicket::truncate();
        foreach($tripTickets as $tripTicket){
            TripTicket::create([
                "id" => $tripTicket->id,
                "trip_id" => $tripTicket->dependent_trip_id,
                "airline_id" => $tripTicket->airline_id,
                "connecting_flight" => $tripTicket->connecting_flight,
                "type" => $tripTicket->type,
                "datum" => $tripTicket->datum,
				"vluchtnummer" => $tripTicket->vluchtnummer,
				"van" => $tripTicket->van,
				"naar" => $tripTicket->naar,
				"vertrek" => $tripTicket->vertrek,
				"ankomst" => $tripTicket->ankomst,
				"sortorder" => $tripTicket->sortorder,
				"creater_id" => 1,
                "created_at" => Carbon::now(),
				"updated_at" => Carbon::now(),
            ]);
        }
		//Trip Ticket User
		$tripTicketUsers = $backup->table('trip_ticket_users')->select('*')->get();
        TripTicketUser::truncate();
        foreach($tripTicketUsers as $tripTicketUser){
            TripTicketUser::create([
                "id" => $tripTicketUser->id,
                "trip_ticket_id" => $tripTicketUser->trip_ticket_id,
                "trip_booking_id" => $tripTicketUser->booking_id,
                "ticket_number" => $tripTicketUser->ticket_number,
                "notes" => $tripTicketUser->notes,
				"creater_id" => 1,
                "created_at" => $tripTicketUser->ts,
				"updated_at" => $tripTicketUser->ts,
            ]);
        }
		//Bookings
		$tripCount = 2000;
		$clients = [];
        TripBooking::truncate();
		$backup->table('trip_booking')->select(DB::raw('trip_booking.*, trips.location_id, trips.start_date, clients.display_name as client_name, clients.email as client_email, clients.password as client_password'))->join("trips", "trip_booking.trip_id", "=", "trips.id")->leftJoin('clients', 'trip_booking.client_id', '=', 'clients.id')->orderBy("date_added")->chunk(500, function ($bookings) use(&$dependantTrips, &$clients, &$client, &$tripCount) {
			foreach ($bookings as $booking) {
				if(!isset($dependantTrips[$booking->location_id.$booking->start_date])){
					$booking->trip_id = $tripCount;
					$tripCount++;
					echo $booking->trip_id." = Trip = ".$booking->location_id.PHP_EOL;
					$dependantTrips[$booking->location_id.$booking->start_date] = Trip::create([
						"id" => $booking->trip_id,
						"location_id" => $booking->location_id,
						"total_space" => 30,
						"male_female_important" => false,
						"show_client_detail" => false,
						"start_date" => $booking->start_date,
						"duration" => 10,
						"trip_fee" => $booking->trip_fee,
						"trip_seats_status" => false,
						"is_not_bookable" => false,
						"archive" => true,
						"is_full" => true,
						"status" => false,
						"creater_id" => 1
					]);
				}
				else{
					echo $booking->trip_id." = DTrip = ".($dependantTrips[$booking->location_id.$booking->start_date]->id).PHP_EOL;
				}
				if(!isset($clients[$booking->client_id])){
					$user = User::where("email", empty($booking->client_email) ? $booking->email : $booking->client_email)->first();
						if($user){
							$clients[$booking->client_id] = $user;
						}
						else{
							$clients[$booking->client_id] = User::create([
								'name' => $this->clean(empty($booking->client_email) ? $booking->display_name : $booking->client_name),
								'email' => $this->clean(empty($booking->client_email) ? $booking->email : $booking->client_email),
								'password' => empty($booking->client_password) ? bcrypt( 'secret' ) : $booking->client_password
							]);
							$clients[$booking->client_id]->roles()->attach($client);
						}
				}
				TripBooking::create([
					"id" => $booking->id,
					"trip_id" => $dependantTrips[$booking->location_id.$booking->start_date]->id,
					"user_id" =>  $clients[$booking->client_id]->id,
					"child_firstname" => $this->clean($booking->child_firstname),
					"child_lastname" => $this->clean($booking->child_lastname),
					"gender" => $booking->gender == 'female',
					"child_dob" => in_array($booking->child_dob, ['0000-00-00','2000-01-00']) ? '1970-01-01' : $booking->child_dob,
					"parent_name" => $this->clean($booking->parent_name),
					"parent_email" => $this->clean($booking->parent_email),
					"email" => $this->clean($booking->email),
					"address" => $this->clean($booking->address),
					"house_number" => $this->clean($booking->house_number),
					"city" => $this->clean($booking->city),
					"postcode" => $this->clean($booking->postcode),
					"telephone" => $this->clean($booking->telephone),
					"cellphone" => $this->clean($booking->cellphone),
					"whatsapp_number" => $this->clean($booking->whatsapp_number),
					"location_pickup_id" => $booking->location_pickup_id,
					"child_diet" => $this->clean($booking->child_diet),
					"child_medication" => $this->clean($booking->child_medication),
					"about_child" => $this->clean($booking->about_child),
					"can_drive" => $booking->can_drive,
					"have_driving_license" => $booking->have_driving_license,
					"have_creditcard" => $booking->have_creditcard,
					"trip_fee" => $booking->trip_fee,
					"insurance" => $booking->insurance,
					"cancellation_insurance" => $booking->cancellation_insurance,
					"travel_insurance" => $booking->travel_insurance,
					"cancellation_policy_number" => $this->clean($booking->cancellation_policy_number),
					"travel_policy_number" => $this->clean($booking->travel_policy_number),
					"survival_adventure_insurance" => $booking->survival_adventure_insurance,
					"insurance_admin_charges" => $booking->insurance_admin_charges,
					"nature_disaster_insurance" => $booking->nature_disaster_insurance,
					"sgr_contribution" => $booking->sgr_contribution,
					"insurnace_question_1" => $booking->insurnace_question_1,
					"insurnace_question_2" => $booking->insurnace_question_2,
					"total_amount" => $booking->total_amount,
					"paid_amount" => $booking->paid_amount,
					"deleted" => $booking->deleted,
					"payment_reminder_email_sent" => $booking->payment_reminder_email_sent,
					"total_reminder_sent" => $booking->total_reminder_sent,
					"email_sent" => $booking->email_sent,
					"login_reminder_email_sent" => $booking->login_reminder_email_sent,
					"upsell_email_sent" => $booking->upsell_email_sent,
					"deposit_reminder_email_sent" => $booking->deposit_reminder_email_sent,
					"passport_reminder_email_sent" => $booking->passport_reminder_email_sent,
					"display_name" => $this->clean($booking->display_name),
					"additional_address" => $this->clean($booking->additional_address),
					"contact_person_name" => $this->clean($booking->contact_person_name),
					"contact_person_extra_name" => $this->clean($booking->contact_person_extra_name),
					"contact_person_extra_cellphone" => $this->clean($booking->contact_person_extra_cellphone),
					"travel_agent_email" => $this->clean($booking->travel_agent_email),
					"commission" => $booking->commission,
					"covid_option" => $booking->covid_option,
					"account_name" => $this->clean($booking->account_name),
					"account_number" => $this->clean($booking->account_number),
					"phone_reminder_email_sent" => $booking->phone_reminder_email_sent,
					"country" => $booking->country,
					"invoice_number" => $this->clean($booking->invoice_number),
					"creater_id" => 1,
					"created_at" => $booking->date_added,
				]);
			}
		});
		Reservation::truncate();
		$backup->table('reservation')->select(DB::raw('reservation.*, trips.location_id, trips.start_date, clients.display_name as client_name, clients.email as client_email, clients.password as client_password'))->join("trips", "reservation.trip_id", "=", "trips.id")->leftJoin('clients', 'reservation.client_id', '=', 'clients.id')->orderBy("date_added")->chunk(500, function ($reservations) use(&$dependantTrips, &$clients, &$client, &$tripCount) {
			foreach ($reservations as $reservation) {
				$gender = $reservation->gender=='male'?0:1;
				if($reservation->expiry_date=="0000-00-00"){
					$reservation->expiry_date = Null;
				}
				if(!isset($dependantTrips[$reservation->location_id.$reservation->start_date])){
					$reservation->trip_id = $tripCount;
					$tripCount++;
					echo $reservation->trip_id." = Trip = ".$reservation->location_id.PHP_EOL;
					$dependantTrips[$reservation->location_id.$reservation->start_date] = Trip::create([
						"id" => $reservation->trip_id,
						"location_id" => $reservation->location_id,
						"total_space" => 30,
						"male_female_important" => false,
						"show_client_detail" => false,
						"start_date" => $reservation->start_date,
						"duration" => 10,
						"trip_fee" => $reservation->trip_fee,
						"trip_seats_status" => false,
						"is_not_bookable" => false,
						"archive" => true,
						"is_full" => true,
						"status" => false,
						"creater_id" => 1
					]);
				}
				else{
					echo $reservation->trip_id." = DTrip = ".($dependantTrips[$reservation->location_id.$reservation->start_date]->id).PHP_EOL;
				}
				if(!isset($clients[$reservation->client_id])){
					$user = User::where("email", empty($reservation->client_email) ? $reservation->email : $reservation->client_email)->first();
						if($user){
							$clients[$reservation->client_id] = $user;
						}
						else{
							$clients[$reservation->client_id] = User::create([
								'name' => $this->clean(empty($reservation->client_email) ? $reservation->display_name : $reservation->client_name),
								'email' => $this->clean(empty($reservation->client_email) ? $reservation->email : $reservation->client_email),
								'password' => empty($reservation->client_password) ? bcrypt( 'secret' ) : $reservation->client_password
							]);
							$clients[$reservation->client_id]->roles()->attach($client);
						}
				}
				Reservation::create([
					"id" => $reservation->id,
					"trip_id" => $dependantTrips[$reservation->location_id.$reservation->start_date]->id,
					"user_id" =>  $clients[$reservation->client_id]->id,
					"trip_booking_id" => $reservation->booking_id,
					"child_firstname" => $this->clean($reservation->child_firstname),
					"child_lastname" => $this->clean($reservation->child_lastname),
					"gender" => $gender,
					"child_dob" => $reservation->child_dob,
					"parent_name" => $this->clean($reservation->parent_name),
					"parent_email" => $this->clean($reservation->parent_email),
					"email" => $this->clean($reservation->email),
					"address" => $this->clean($reservation->address),
					"house_number" => $this->clean($reservation->house_number),
					"city" => $this->clean($reservation->city),
					"postcode" => $this->clean($reservation->postcode),
					"telephone" => $this->clean($reservation->telephone),
					"cellphone" => $this->clean($reservation->cellphone),
					"whatsapp_number" => $this->clean($reservation->whatsapp_number),
					"location_pickup_id" => $reservation->location_pickup_id,
					"child_diet" => $this->clean($reservation->child_diet),
					"child_medication" => $this->clean($reservation->child_medication),
					"about_child" => $this->clean($reservation->about_child),
					"date_added" => $reservation->date_added,
					"can_drive" => $reservation->can_drive,
					"have_driving_license" => $reservation->have_driving_license,
					"have_creditcard" => $reservation->have_creditcard,
					"trip_fee" => $reservation->trip_fee,
					"total_amount" => $reservation->total_amount,
					"paid_amount" => $reservation->paid_amount,
					"deleted" => $reservation->deleted,
					"payment_reminder_email_sent" => $reservation->payment_reminder_email_sent,
					"email_sent" => $reservation->email_sent,
					"login_reminder_email_sent" => $reservation->login_reminder_email_sent,
					"upsell_email_sent" => $reservation->upsell_email_sent,
					"deposit_reminder_email_sent" => $reservation->deposit_reminder_email_sent,
					"display_name" => $this->clean($reservation->display_name),
					"additional_address" => $this->clean($reservation->additional_address),
					"contact_person_name" => $this->clean($reservation->contact_person_name),
					"contact_person_extra_name" => $this->clean($reservation->contact_person_extra_name),
					"contact_person_extra_cellphone" => $this->clean($reservation->contact_person_extra_cellphone),
					"reservation_fees" => $reservation->reservation_fees,
					"reservation_fees_paid_at" => $reservation->reservation_fees_paid_at,
					"reservation_fees_payment_type" => $reservation->reservation_fees_payment_type,
					"expiry_date" => $reservation->expiry_date,
					"status" => $reservation->status,
					"created_at" => $reservation->ts,
					"updated_at" => $reservation->ts,
				]);
			}
		});


		/*********Trip Templates***********/
		$tripTemplates = $backup->table('trip_templates')->select('*')->get();
        TripTemplate::truncate();
		foreach($tripTemplates as $tripTemplate){
			TripTemplate::create([
				"id" => $tripTemplate->id,
				"location_id" => $tripTemplate->location_id,
				"name" => $this->clean($tripTemplate->name),
				"content" => $this->clean($tripTemplate->content),
				"creater_id" => 1,
				"status" => $tripTemplate->status,
				"created_at" => $tripTemplate->ts,
				"updated_at" => $tripTemplate->ts,
			]);
		}

		/*********Trip Types***********/
		$tripTypes = $backup->table('trip_type')->select('*')->get();
        TripType::truncate();
		foreach($tripTypes as $tripType){
			TripType::create([
				"id" => $tripType->id,
				"title" => $this->clean($tripType->title),
				"image" => $tripType->image ? $this->copyImage("trip_type/".$tripType->image, "public/trip-types/".$tripType->image) : '',
				"show_on_homepage" => $tripType->show_on_homepage,
				"sortorder" => $tripType->sortorder,
				"description" => $this->clean($tripType->description),
				"status" => $tripType->status,
				"creater_id" => 1,
				"created_at" => $tripType->ts,
				"updated_at" => $tripType->ts,
			]);
		}

		/*********Page Gallery***********/
		$pagesGallery = $backup->table('page_gallery')->select('*')->get();
        PageGallery::truncate();
		foreach($pagesGallery as $pageGallery){
			if(isset($pageIds[$pageGallery->pages_id])){
				PageGallery::create([
					"id" => $pageGallery->id,
					"page_id" =>  $pageIds[$pageGallery->pages_id],
					"title" => $this->clean($pageGallery->title),
					"image" => $pageGallery->image ? $this->copyImage("page_images/gallery/".$pageGallery->image, "public/page-galleries/".$pageGallery->image) : '',
					"sortorder" => $pageGallery->sortorder,
					"status" => $pageGallery->status,
					"creater_id" => 1,
					"created_at" => $pageGallery->ts,
					"updated_at" => $pageGallery->ts,
				]);
			}
		}

		/*********Page Dags***********/
		$pageDags = $backup->table('page_dag')->select('*')->get();
        LocationDay::truncate();
		foreach($pageDags as $pageDag){
			if(!isset($pageIds[$pageDag->pages_id])){
				continue;
			}
			$location = $backup->table('location_2_age_group')->where("page_id", $pageDag->pages_id)->select("*")->first();
			if($location) {
				$locationDays[] = $location->location_id;
				LocationDay::create([
					"id" => $pageDag->id,
					"location_id" => $location->location_id,
					"title" => $this->clean($pageDag->title),
					"image" => $pageDag->image ? $this->copyImage("page_images/dag/".$pageDag->image, "public/location-days/".$pageDag->image) : '',
					"description" => $pageDag->description,
					"sortorder" => $pageDag->sortorder,
					"status" => $pageDag->status,
					"creater_id" => 1,
					"created_at" => $pageDag->ts,
					"updated_at" => $pageDag->ts,
				]);
			}
		}
		/*********Support Categories***********/
		$supportCategories = $backup->table('support_category')->select('*')->get();
        SupportCategory::truncate();
		foreach($supportCategories as $supportCategory){
			$supportCategoryDb = SupportCategory::create([
                "id" => $supportCategory->id,
				"title" => $this->clean($supportCategory->title),
				"icon" => $supportCategory->icon ? $this->copyImage("support_category/".$supportCategory->icon, "public/support-categories/".$supportCategory->icon) : '',
				"sortorder" => $supportCategory->sortorder,
				"status" => $supportCategory->status,
				"creater_id" => 1,
				"created_at" => $supportCategory->ts,
				"updated_at" => $supportCategory->ts,
            ]);
			$supportCategoryDb->page()->create([
				"page_name" => $this->clean($supportCategory->seo_url),
				"title" => $this->clean($supportCategory->title),
				"content" =>  "",
				"highlights" => "",
				"image" => "",
				"meta_title" => "",
				"meta_description" => "",
				"meta_keywords" => "",
				"creater_id" => 1,
				"created_at" => $supportCategory->ts,
				"updated_at" => $supportCategory->ts,
			]);
		}
		/*********Support Articles***********/
		$supportArticles = $backup->table('support_article')->select('*')->get();
        SupportArticle::truncate();
		foreach($supportArticles as $supportArticle){
			$supportArticleDb = SupportArticle::create([
                "id" => $supportArticle->id,
				"support_category_id" => $supportArticle->support_category_id,
				"title" => $this->clean($supportArticle->title),
				"excerpt" => $this->clean($supportArticle->excerpt),
				"date" => $supportArticle->date,
				"time" => $supportArticle->time,
				"sortorder" => $supportArticle->sortorder,
				"creater_id" => 1,
				"status" => $supportArticle->status,
				"created_at" => $supportArticle->ts,
				"updated_at" => $supportArticle->ts,
            ]);
			$supportArticleDb->page()->create([
				"page_name" => $this->clean($supportArticle->seo_url),
				"title" => $this->clean($supportArticle->title),
				"content" =>  $this->clean($supportArticle->content),
				"highlights" => "",
				"image" => "",
				"meta_title" => $this->clean($supportArticle->meta_title),
				"meta_description" => $this->clean($supportArticle->meta_description),
				"meta_keywords" => $this->clean($supportArticle->meta_keywords),
				"creater_id" => 1,
				"created_at" => $supportArticle->ts,
				"updated_at" => $supportArticle->ts,
			]);
		}
		
		
		/*********Course***********/
		$courses = $backup->table('courses')->select('*')->get();
        Course::truncate();
		foreach($courses as $course){
			Course::create([
				"id" => $course->id,
				"title" => $this->clean($course->title),
				"image" => $course->image ? $this->copyImage("courses/".$course->image, "public/courses/".$course->image) : '',
				"description" => $this->clean($course->description),
				"sortorder" => $course->sortorder,
				"details" => $this->clean($course->details),
				"creater_id" => 1,
				"created_at" => $course->ts,
				"updated_at" => $course->ts,
			]);
		}
		/*********Lesson***********/
		$lessons = $backup->table('lessons')->select('*')->get();
        Lesson::truncate();
		foreach($lessons as $lesson){
			Lesson::create([
				"id" => $lesson->id,
				"course_id" => $lesson->course_id,
				"title" => $this->clean($lesson->title),
				"small_description" => $this->clean($lesson->small_description),
				"duration" => $lesson->duration,
				"details" => $this->clean($lesson->details),
				"intro_video" => $lesson->intro_video,
				"creater_id" => 1,
				"created_at" => $lesson->ts,
				"updated_at" => $lesson->ts,
			]);
		}
		/*********Quiz Question***********/
		$questions = $backup->table('quiz_questions')->select('*')->get();
        QuizQuestion::truncate();
		foreach($questions as $question){
			QuizQuestion::create([
				"id" => $question->id,
				"lesson_id" => $question->lesson_id,
				"question" => $this->clean($question->question),
				"description" => $this->clean($question->description),
				"sortorder" => $question->sortorder,
				"is_multiple" => $question->is_multiple,
				"creater_id" => 1,
				"created_at" => $question->ts,
				"updated_at" => $question->ts,
			]);
		}
		/*********Quiz Question Option***********/
		$question_options = $backup->table('quiz_question_options')->select('*')->get();
        QuizQuestionOption::truncate();
		foreach($question_options as $question_option){
			QuizQuestionOption::create([
				"id" => $question_option->id,
				"quiz_question_id" => $question_option->question_id,
				"option" => $this->clean($question_option->option),
				"is_correct" => $question_option->is_correct,
				"created_at" => $question_option->ts,
				"updated_at" => $question_option->ts,
			]);
		}
		/*********Trip Booking Documents***********/
		$bookingDocuments = $backup->table('trip_booking_document')->select('*')->get();
        TripBookingDocument::truncate();
		foreach($bookingDocuments as $bookingDocument){
			TripBookingDocument::create([
				"id" => $bookingDocument->id,
				"trip_booking_id" => $bookingDocument->trip_booking_id,
				"title" => $this->clean($bookingDocument->title),
				"document_url" => $this->clean($bookingDocument->document_url),
				"sortorder" => $bookingDocument->sortorder,
				"status" => $bookingDocument->status,
				"creater_id" => 1,
				"created_at" => $bookingDocument->ts,
				"updated_at" => $bookingDocument->ts,
			]);
		}

		/*********Tour Guides***********/
		TripTourGuide::truncate();
		UserField::truncate();
		$backup->table('tour_guides')->select(DB::raw('tour_guides.*, trips.location_id, trips.start_date'))->join("tour_guides_2_dependent_trip", "tour_guides.id", "=", "tour_guides_2_dependent_trip.tour_guide_id")->join("dependent_trips", "tour_guides_2_dependent_trip.dependent_trip_id", "=", "dependent_trips.id")->join("trips", "dependent_trips.location_id", "=", "trips.location_id")->groupBy("tour_guides.id")->orderBy("trips.start_date")->chunk(500, function ($tour_guides) use(&$dependantTrips, &$tripCount, &$tourGuide) {
			//$tour_guides = $backup->table('tour_guides')->select('*')->get();
			foreach($tour_guides as $tour_guide){
				$user = User::where("email", $tour_guide->email)->first();
				if(!$user){
					$guide = User::create([
						"name" => $this->clean($tour_guide->name),
						"email" => $tour_guide->email,
						"password" => $tour_guide->password,
						"creater_id" => 1,
						"created_at" => $tour_guide->ts,
						"updated_at" => $tour_guide->ts,
					]);
					$guide->roles()->attach($tourGuide);
					$guide->userFields()->create([
						"field_key" => 'partner',
						"field_value" => $tour_guide->partner_id ? $tour_guide->partner_id : 0,
					]);
					$guide->userFields()->create([
						"field_key" => 'gender',
						"field_value" => $tour_guide->gender,
					]);
					$guide->userFields()->create([
						"field_key" => 'is_partner',
						"field_value" => $tour_guide->is_partner,
					]);
					$trip = TripTourGuide::create([
						"trip_id" => $dependantTrips[$tour_guide->location_id.$tour_guide->start_date]->id,
						"user_id" => $guide->id,
						"creater_id" => 1,
						"created_at" => $tour_guide->ts,
						"updated_at" => $tour_guide->ts,
					]);
				}
				else{
					$user->roles()->attach($tourGuide);
				}
				
				
			}
		});

		
    }
}
