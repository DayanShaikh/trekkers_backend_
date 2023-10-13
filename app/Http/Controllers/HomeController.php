<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\Attribute;
use App\Models\LandingPage;
use Illuminate\Http\Request;
use App\Models\FrontMenu;
use App\Models\ConfigVariable;
use App\Models\Blog;
use App\Models\TripBooking;
use App\Models\AgeGroup;
use App\Models\Page;
use App\Models\Location;
use App\Models\Trip;
use App\Models\TripTicket;
use App\Models\TripTicketUser;
use App\Models\User;
use App\Models\ForumTopic;
use App\Models\ForumReply;
use App\Models\TripBookingAddon;
use App\Models\LocationAddon;
use App\Models\PassportDetail;
use App\Models\TripBookingExtraInsurance;
use App\Models\LocationPickup;
use App\Models\Reservation;
use App\Services\PaymentService;
use Carbon\Carbon;
use Mail;
use App\Utility;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Hash;

class HomeController extends Controller
{

	public function getGeneralConfigurations(Request $request)
    {
        $settings = [];

        $class = new \ReflectionClass('App\Utility');
        $staticMembers = $class->getStaticProperties();

        foreach($staticMembers as $field => $value) {
            $settings[$field] = $value;
        }

        //$ageGroups = AgeGroup::where('status', 1)->get();

        $frontMenu = FrontMenu::with(['frontSubMenu' => function($q){
			$q->orderBy("sortorder")
			->where('status', true);
		}, 'frontSubMenu.frontSubMenu' => function($q){
			$q->orderBy("sortorder");
		}], function ($q){
            $q->where('parent_id', '!=', 0)->get()->where('status', true);
        })->where(['position' => 0, 'parent_id' => 0])->where('status', true)->orderBy('sortorder')
            ->get();
        $config = ConfigVariable::select(["config_key", "value"])->where('autoload', 1)->get()->keyBy('config_key');
		$pages = Page::select(DB::raw("pageable_type, id, pageable_id, page_name, meta_title, meta_description"))->where("status", true)->get();
		$pages->map(function($page){
			$page->type = strtolower(str_replace('App\\Models\\', '', $page->pageable_type));
			unset($page->pageable_type);
			if($page->type === 'landingpage'){
				$page->extra = LandingPage::select("type", "month")->find($page->pageable_id);
			}
			return $page;
		});
        return response()->json(["settings" => $settings, "frontMenus" => $frontMenu, "config" => $config, "pages" => $pages]);
    }
    public function getMonths(Request $request)
	{
		$now = Carbon::now()->locale("de");
		$date = $now->copy();
		$months = [];
		for($i = 0; $i < 15; $i++){
			$month_start = $date->startOfMonth();
			$month_end = $date->copy()->endOfMonth();
			$month_label = Utility::getMonthName($date->format("n")-1)." ".$date->isoFormat("Y");
			$month_value = Str::slug($date->isoFormat('MMM'));
			//return $month_value;
			$count = Location::when($request->has('attribute_id'), function($q) use($request){
                    $q->whereHas("attributes", function($q) use($request){
                        $q->where('attribute_id', '=', $request->get('attribute_id'));
                    });
				})
				->whereHas("trips", function($q) use($month_start, $month_end, $request){
					$q->whereBetween('start_date', [$month_start->format('Y-m-d'), $month_end->format('Y-m-d')]);
				})
				->count();
			if($count > 0){
				$monthDb = LandingPage::where("type", 2)->with("page")->where("month", $date->format("n")-1)->first();
				$months[] = [
					'label' => $month_label,
					'value' => $date->format("Ym"),
					'path' => $monthDb->page->page_name,
					'count' => $count,
				];
			}
			$date->addMonth();
		}

		return response()->json($months, 200);
    }
    public function getHomePageData(Request $request){
		$date = Carbon::now();
		$page = LandingPage::where("type", 0)->first()->page;
		if($request->has('month')){
			$month = Carbon::createFromFormat('Ymd', $request->get("month")."01");
			$monthPage = LandingPage::where("type", 2)->where("month", $month->format("n")-1)->first()->page;
			if($monthPage){
				$page->content = $monthPage->content;
				$page->meta_title = $monthPage->meta_title;
				$page->meta_description = $monthPage->meta_description;
				$page->page_name = $monthPage->page_name;
				$page->title = $monthPage->title;
			}
		}
		$response = [
			"page" => $page,
			"trips" => Attribute::with([
				"locations" => function($query) use($request){
					$query->select("locations.id", "locations.title", "listing_image", "listing_text", "travel_time", "icons", "trip_fee", "pages.page_name")->when($request->has('attrbiuteId'), function($q) use($request){
						$q->whereHas("attributes", function($query) use($request){
							$query->where('attribute_id', '=', $request->get('attrbiuteId'));
						});
					})->when($request->has('month'), function($q) use($request){
						$month_start = Carbon::createFromFormat('Ymd', $request->get("month")."01");
						$month_end = $month_start->copy()->endOfMonth();
						$q->whereHas("trips", function($q) use($month_start, $month_end, $request){
							$q->whereBetween('start_date', [$month_start->format('Y-m-d'), $month_end->format('Y-m-d')]);
						});
					})->when($request->has('date'), function($q) use($request){
						$q->whereHas("trips", function($q) use($request){
							$q->where('start_date', '=', $request->get('date'));
						});
					})->join("pages", function ($join) {
						$join->on('locations.id', '=', 'pages.pageable_id')->where("pages.pageable_type", "App\Models\Location");
					})->where("locations.status", 1)->orderBy("sortorder");
				},
			"locations.trips" => function($query) use($request, $date){
				$query->where("status", true)
				->when($request->has('month'), function($q) use($request){
					$month_start = Carbon::createFromFormat('Ymd', $request->get("month")."01");
					$month_end = $month_start->copy()->endOfMonth();
					
						$q->whereBetween('start_date', [$month_start->format('Y-m-d'), $month_end->format('Y-m-d')]);
					
				})
				->where("start_date", ">=", $date->format("Y-m-d"))
				->orderBy('start_date')
				->select('id', 'location_id', 'start_date', 'duration');
			}])->orderBy("sortorder")->get(),
			"attributes" => Attribute::where('status',1)->orderBy("sortorder")->get(),
			"destinations" => Destination::select('id', 'title', 'thumb_image')->where('status',1)->with([
				"page" => function($query){
					$query->select("pageable_type", "pageable_id", "page_name", "image");
				}])->get(),
			"blogs" => Blog::where("status", 1)->orderBy("published_at", "desc")->take(3)->get()
		];
		
		return $response;
	}
	public function page(Page $page){
		$date = Carbon::now();
		$page = Page::with("pageable")->withCount("pageGalleries")->find($page->id);
		if($page->pageable_type ===  null || $page->pageable_type ===  0){
			$offset = 0;
			$ids = [];
			while($index = strpos($page->content,'[trip_slider ids="', $offset)){
				$index += strlen( '[trip_slider ids="' );
				$endIndex = strpos($page->content,'"]', $index);
				$strIds = substr($page->content, $index, $endIndex-$index);
				array_push($ids, ...explode(',', $strIds));
				$page->content = str_replace( '[trip_slider ids="'.$strIds.'"]', '<app-trip-slider trips="['.$strIds.']"></app-trip-slider>', $page->content );
				$offset = $endIndex;
			}
			//$page->trips = Trip::whereIn('id', collect($ids)->unique()->values())->orderBy('created_at')->get(['id','start_date']);
			if( strpos( $page->content, '[upcoming_trips]' ) !== false ) {
				$page->content = str_replace( '[upcoming_trips]', '<app-upcoming-trips></app-upcoming-trips>', $page->content );
			}
			if( strpos( $page->content, '[upcoming_trips_spot]' ) !== false ) {
				$page->content = str_replace( '[upcoming_trips_spot]', '<app-upcoming-trips-spot></app-upcoming-trips-spot>', $page->content );
			}
			$page->attribures = Attribute::where('status',1)->get();
			$page->popular_trips = Trip::whereHas('location', function ($query){
				$query->where("status", true);
			})->with(['location' => function($query){
				$query->select('id', 'title',);
			}, 'location.page' => function($query){
				$query->select('id', 'pageable_type', 'pageable_id', 'page_name');
			}])->where("start_date", ">=", $date->format("Y-m-d"))->orWhere("archive", "=", 0)->inRandomOrder()->take(5)->get();
			
		}
		if($page->pageable_type ===  'App\\Models\\Location'){
			$chart_labels = [];
			$chart_values = [];
			$chart_total_count = 0;
			foreach(TripBooking::select(DB::raw(config("database.default") === 'pgsql' ? "date_part('year', AGE(CURRENT_DATE, child_dob)) as child_age, count(1) as age_count" : "(YEAR(CURDATE())-YEAR(child_dob)) as child_age, count(1) as age_count"))->where("status", true)->whereIn("trip_id", Trip::select("id")->where("location_id", $page->pageable->id))->whereYear("created_at", ">=", date("Y")-2)->groupBy('child_age')->orderBy("child_age", "asc")->get() as $age){
				if($age->child_age >= 18 && $age->child_age <= 32){
					$chart_labels[] = (int)$age->child_age;
					$chart_values[] = (int)$age->age_count;
					$chart_total_count += $age->age_count;
				}
			}
			$page->pageable->chart_labels = $chart_labels;
			$page->pageable->chart_values = $chart_values;
			$page->pageable->chart_total_count = $chart_total_count;
			//$page->pageable->chart_labels = [18,19,20,21,22,23,24,25];
			//$page->pageable->chart_values = [2,11,18,38,53,57,39,33];
			//$page->pageable->chart_total_count = 251;
			$page->pageable->days = $page->pageable->locationDays()->orderBy("sortorder")->get();
			$page->pageable->trips = $page->pageable->trips()->with('tripTickets')->where("start_date", ">=", $date->format("Y-m-d"))->where('status', true)->orderBy("start_date")->get();
			
			//$page->pageable->trips->trip_tickets = $page->pageable->trips->tripTickets()->get();
			$page->pageable->minimum_price = $page->pageable->trips->min('trip_fee');
			//return $page->pageable->destination_id;
			$page->related_trips = Location::whereHas('trips', function ($query) use($date){
				$query->where("start_date", ">=", $date->format("Y-m-d"))->orWhere("archive", "=", 0);
			})->with(['page' => function($query){
				$query->select('id', 'pageable_type', 'pageable_id', 'page_name');
			}])->where('id', '!=', $page->pageable->id)->where('destination_id', $page->pageable->destination_id)->inRandomOrder()->take(3)->get();
			/*$page->related_trips = Trip::whereHas('location', function ($query) use($page){
				$query->where('destination_id', $page->pageable->destination_id);
			})->with(['location' => function($query) use($page){
				$query->select('id', 'title', 'listing_image', 'listing_text', 'destination_id')
				;
			}, 'location.page' => function($query){
				$query->select('id', 'pageable_type', 'pageable_id', 'page_name');
			}])->where("start_date", ">=", $date->format("Y-m-d"))->orWhere("archive", "=", 0)->inRandomOrder()->take(3)->get();*/
		}
		if($page->pageable_type ===  'App\\Models\\Destination'){
			$page->pageable->header_video = $page->pageable->headerVideo()->first();
			$page->pageable->trips = $page->pageable->trips()->with('page')->get();
			if(count($page->pageable->trips)===0){
				$page->pageable->trips = $page->pageable->trips()->with('page')->take(1)->get();
			}
			$page->pageable->other_trips = $page->pageable->otherTrips()->with('page')->get();
			
			$offset = 0;
			$ids = [];
			while($index = strpos($page->content,'[blogpost id="', $offset)){
				$index += strlen( '[blogpost id="' );
				$endIndex = strpos($page->content,'"]', $index);
				$strIds = substr($page->content, $index, $endIndex-$index);
				//array_push($ids, ...explode(',', $strIds));
				$page->content = str_replace( '[blogpost id="'.$strIds.'"]', '<app-blog-post blogs="'.$strIds.'"></app-blog-post>', $page->content );
				
				$offset = $endIndex;
				//return $page->content;
			}
			
			
		}
		return $page;
	}

	public function trip(Page $page, Request $request){
		//return $request;
		$date = Carbon::now();
		$page = Page::with("pageable", "pageable.attributes", "pageable.destination", "pageable.locationPickups")->find($page->id);
		if($page->pageable_type ===  'App\\Models\\Location'){
			$trip = $page->pageable->trips()->where("start_date", "=", $request->get("date"))->active()->first();
			if($trip){
				$trip->reservation_expiry = Utility::get_reservation_expiry(['trip_id' => $trip->id, 'date_added' => date("Y-m-d")]);
				if($request->get('reservation_id') && $request->get('key')){
					$reservation = Reservation::find($request->get('reservation_id'));
					if(md5($reservation->email.$reservation->child_firstname)==$request->get('key')){
						$res = $reservation;
					}
					else{
						$res = [];
					}
				}
				else{
					$res = [];
				}
				if($trip && $trip->space->remaining > 0){
					return [
						"page" => $page,
						"trip" => $trip,
						"reservation" => $res
					];
				}
			}
		}
		return response()->json(['error' => true, 'data' => []], 404);
	}
	
	public function saveBooking(Request $request, PaymentService $paymentService){
		//return $request;
		$trip = Trip::with('location')->find($request->get("trip_id"));
		$locationPickup = LocationPickup::find($request->get("location_pickup_id"));
		if($trip->space->remaining <= 0){
			//return response()->json(["status" => false]);
		}
		$date = Carbon::now();
		$data = $request->validate([
			'trip_id'  =>  ['required'],
			'child_firstname'  =>  ['required'],
            'child_lastname'  =>  ['required'],
            'gender'  =>  [''],
            'child_dob'  =>  ['required'],
			'parent_name'  =>  [''],
            'parent_email'  =>  [''],
            //'email'  =>  $request->reserve ? ['required', 'string', 'unique:reservations', 'email', 'max:255'] : ['required', 'string', 'email', 'max:255'],
            'email' => ['required',
Rule::unique('trip_bookings')->where(function ($query) use($request) {
      $query->where('child_firstname', $request->get('child_firstname'))
	  ->where('trip_id', $request->get('trip_id'))->whereNull('deleted_at');
})],
            'address'  =>  ['required'],
            'house_number'  =>  ['required'],
            'city'  =>  ['required'],
            'postcode'  =>  ['required'],
            'telephone'  =>  ['required'],
            'cellphone'  =>  ['required'],
            'whatsapp_number'  =>  [''],
            'location_pickup_id'  =>  [''],
            'child_diet'  =>  [''],
            'child_medication'  =>  [''],
            'about_child'  =>  [''],
            'date_added'  =>  [''],
            'can_drive'  =>  [''],
            'have_driving_license'  =>  [''],
            'have_creditcard'  =>  [''],
			'trip_fee'  =>  ['required'],
            'insurance'  =>  [''],
            'cancellation_insurance'  =>  [''],
            'travel_insurance'  =>  [''],
            'cancellation_policy_number'  =>  [''],
            'travel_policy_number'  =>  [''],
            'survival_adventure_insurance'  =>  [''],
            'insurance_admin_charges'  =>  [''],
            'nature_disaster_insurance'  =>  [''],
            'sgr_contribution'  =>  [''],
            'insurnace_question_1'  =>  [''],
            'insurnace_question_2'  =>  [''],
            'total_amount'  =>  [''],
            'paid_amount'  =>  [''],
            'deleted'  =>  [''],
            'payment_reminder_email_sent'  =>  [''],
            'total_reminder_sent'  =>  [''],
            'email_sent'  =>  [''],
            'login_reminder_email_sent'  =>  [''],
            'upsell_email_sent'  =>  [''],
            'deposit_reminder_email_sent'  =>  [''],
            'passport_reminder_email_sent'  =>  [''],
            'display_name'  =>  [''],
            'additional_address'  =>  [''],
            'contact_person_name'  =>  [''],
            'contact_person_extra_name'  =>  [''],
            'contact_person_extra_cellphone'  =>  [''],
            'travel_agent_email'  =>  [''],
            'commission'  =>  ['numeric'],
            'covid_option'  =>  [''],
            'account_name'  =>  [''],
            'account_number'  =>  [''],
            'phone_reminder_email_sent'  =>  [''],
			'country' => ['']
        ]);
		$user = User::where(['email' => $data["email"]])->first();
        if(!$user){
            $random = str_shuffle('abcdefghjklmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ234567890!$%^&!$%^&');
            $password = substr($random, 0, 8);
            $user = User::create([
                'name' => $data["display_name"],
                'email' => $data["email"],
                'gender' => $data["gender"],
                'password' => Hash::make($password)
            ]);
        }
		$data['user_id'] =  $user->id;
        $data['date_added'] = $date->format("Y-m-d");
		if($request->reserve){
			$booking = Reservation::create($data);
			if($booking){
				$ideal_payment_url = "https://www.simi-reizen.nl/ideal_payment.php?booking_id=".$booking->id;
				$deposite_payment_url = "https://www.simi-reizen.nl/deposite_payment.php?booking_id=".$booking->id;
				$client_address = $booking->address." ".$booking->house_number." ".$booking->additional_address." ".$booking->postcode." ".$booking->city;
				$subject = Utility::getConfig('reservation_email_subject')->value;
				$reserve_price = Utility::getConfig('reserve_price')->value;
				$reservation_expiry = Utility::get_reservation_expiry(['trip_id' => $booking->trip_id, 'date_added' => $booking->date_added]);
				$url1 = Utility::get_reservation_url($booking->id, 0);
				$url2 = Utility::get_reservation_url($booking->id, 1);
				$reservation_email_body = Utility::getConfig("reservation_email_body")->value;
				$reservation_email_subject = str_replace('[$trip_startdate]', $trip->location->title, $subject);
				$reservation_email_body = str_replace(
					array(
						'[$booking_id]',
						'[$client_firstname]',
						'[$client_lastname]',
						'[$client_dob]',
						'[$client_gender]',
						'[$client_parent_name]',
						'[$client_parent_email]',
						'[$client_address]',
						'[$client_phone]',
						'[$contact_person_name]',
						'[$client_mobile]',
						'[$client_diet]',
						'[$client_medication]',
						'[$client_about_child]',
						'[$booking_client_insurance]',
						'[$pickup_place]',
						'[$pickup_time]',
						'[$booking_total_amount]',
						'[$deposite_amount]',
						'[$booking_date]',
						'[$trip_startdate]',
						'[$trip_enddate]',
						'[$ideal_payment_url]',
						'[$deposite_payment_url]',
						'[$trip_name]',
						'[$date]',
						'[$url1]',
						'[$url2]',
					),
					array(
						$booking->id,
						$booking->child_firstname,
						$booking->child_lastname,
						date('d-m-Y', strtotime($booking->child_dob)),
						$booking->gender=="0"?"Man":"Vrouw",
						$booking->parent_name,
						$booking->email,
						$client_address,
						$booking->telephone,
						$booking->contact_person_name,
						$booking->cellphone,
						$booking->child_diet,
						$booking->child_medication,
						$booking->about_child,
						$booking->insurance,
						$locationPickup? $locationPickup->place : '',
						$locationPickup ? $locationPickup->time : '',
						round($booking->total_amount, 2),
						$reserve_price,
						date('d-m-Y', strtotime($booking->date_added)),
						date('d-m-Y', strtotime($trip->start_date)),
						date("d-m-Y", strtotime($trip->start_date)+(($trip->duration-1)*86400)),
						$ideal_payment_url,
						$deposite_payment_url,
						$trip->trip_name,
						$reservation_expiry,
						$url1,
						$url2
					),
					$reservation_email_body
				);
				Utility::send_email($booking->email, $reservation_email_subject, $reservation_email_body, $booking->id);
				//Utility::send_email('boeking@simi-reizen.nl', $reservation_email_subject, $reservation_email_body, $booking->id);
			}
			$paymentLinks = [];
			$partial = '';
			$order = $booking->orders()->create([
				'type' => 'full',
				'amount' => $booking->total_amount
			]);
			$paymentLinks = $paymentService->createUrl($order);

			return response()->json([
				"status" => true,
				"paymentLinks" => $paymentLinks,
				"booking" => $booking
			]);
		}
		else{
			$booked = TripBooking::where(["user_id" => $user->id, "child_firstname" => $data["child_firstname"], "deleted" => 0, "trip_id" => $trip->id])->first();
			$myString = implode(',', $data['insurance']);
			$data['insurance'] = $myString;
			if(!$booked){
				$booking = TripBooking::create($data);
			}
			else{
				$booking = $booked;
			}
			if($booking){
				if($request->get('reservation_id') && !empty($request->get('reservation_id'))){
					$reservation = Reservation::find($request->get('reservation_id'));
					$reservation->update(["trip_booking_id" => $booking->id]);
				}
				$ideal_payment_url = "https://www.simi-reizen.nl/ideal_payment.php?booking_id=".$booking->id;
				$deposite_payment_url = "https://www.simi-reizen.nl/deposite_payment.php?booking_id=".$booking->id;
				$client_address = $booking->address." ".$booking->house_number." ".$booking->additional_address." ".$booking->postcode." ".$booking->city;
				$sgr_bijdrage = Utility::getConfig("sgr_bijdrage")->value;
				$deposit_percent = Utility::getConfig("deposit_percent")->value;
				//$available_upfront_fee = ($booking->total_amount*$deposit_percent)/100;
				$subject = Utility::getConfig('booking_email_subject')->value;
				$booking_email_body = Utility::getConfig('booking_email_body')->value;
				$booking_email_subject=str_replace('[$trip_startdate]', $trip->location->title, $subject);
				$booking_email_body=str_replace(
					array(
						'[$booking_id]',
						'[$client_firstname]',
						'[$client_lastname]',
						'[$client_dob]',
						'[$client_gender]',
						'[$client_parent_name]',
						'[$client_parent_email]',
						'[$client_address]',
						'[$client_phone]',
						'[$contact_person_name]',
						'[$client_mobile]',
						'[$client_diet]',
						'[$client_medication]',
						'[$client_about_child]',
						'[$booking_client_insurance]',
						'[$pickup_place]',
						'[$pickup_time]',
						'[$booking_total_amount]',
						'[$deposite_amount]',
						'[$booking_date]',
						'[$trip_startdate]',
						'[$trip_enddate]',
						'[$ideal_payment_url]',
						'[$deposite_payment_url]',
						'[$trip_name]',
						'[$sgr_bijdrage]'
					),
					array(
						$booking->id,
						$booking->child_firstname,
						$booking->child_lastname,
						date('d-m-Y', strtotime($booking->child_dob)),
						$booking->gender=="0"?"Man":"Vrouw",
						$booking->parent_name,
						$booking->email,
						$client_address,
						$booking->telephone,
						$booking->contact_person_name,
						$booking->cellphone,
						$booking->child_diet,
						$booking->child_medication,
						$booking->about_child,
						$booking->insurance,
						$locationPickup? $locationPickup->place : '',
						$locationPickup ? $locationPickup->time : '',
						round($booking->total_amount, 2),
						$booking->partial_payment_amount,
						date('d-m-Y', strtotime($booking->date_added)),
						date('d-m-Y', strtotime($trip->start_date)),
						date("d-m-Y", strtotime($trip->start_date)+(($trip->duration-1)*86400)),
						$ideal_payment_url,
						$deposite_payment_url,
						$trip->trip_name,
						$sgr_bijdrage
					),
					$booking_email_body
				);
				Utility::send_email($booking->email, $booking_email_subject, $booking_email_body, $booking->id);
				//Utility::send_email('boeking@simi-reizen.nl', $booking_email_subject, $booking_email_body, $booking->id);
				if(!empty($booking->cancellation_insurance) && !empty($booking->travel_insurance)){
					//All Insurance Email
					$all_insurance_email_subject = Utility::getConfig('all_insurance_email_subject')->value;
					$all_insurance_email_body = Utility::getConfig("all_insurance_email_body")->value;
					$all_insurance_email_body=str_replace(
						array(
							'[$booking_id]',
							'[$client_firstname]',
							'[$client_lastname]',
							'[$client_dob]',
							'[$client_gender]',
							'[$client_parent_name]',
							'[$client_parent_email]',
							'[$client_address]',
							'[$client_phone]',
							'[$client_mobile]',
							'[$client_diet]',
							'[$client_medication]',
							'[$client_about_child]',
							'[$booking_client_insurance]',
							'[$pickup_place]',
							'[$pickup_time]',
							'[$booking_total_amount]',
							'[$deposite_amount]',
							'[$booking_date]',
							'[$trip_startdate]',
							'[$trip_enddate]',
							'[$ideal_payment_url]',
							'[$deposite_payment_url]',
							'[$trip_name]'
						),
						array(
							$booking->id,
							$booking->child_firstname,
							$booking->child_lastname,
							date('d-m-Y', strtotime($booking->child_dob)),
							$booking->gender=="0"?"Man":"Vrouw",
							$booking->parent_name,
							$booking->email,
							$client_address,
							$booking->telephone,
							$booking->cellphone,
							$booking->child_diet,
							$booking->child_medication,
							$booking->about_child,
							$booking->insurance,
							$locationPickup? $locationPickup->place : '',
							$locationPickup ? $locationPickup->time : '',
							round($booking->total_amount, 2),
							$booking->partial_payment_amount,
							date('d-m-Y', strtotime($booking->date_added)),
							date('d-m-Y', strtotime($trip->start_date)),
							date("d-m-Y", strtotime($trip->start_date)+(($trip->duration-1)*86400)),
							$ideal_payment_url,
							$deposite_payment_url,
							$trip->trip_name,
						),
						$all_insurance_email_body
					);
					Utility::send_email($booking->email, $all_insurance_email_subject, $all_insurance_email_body, $booking->id);
				}
				else{
					if(!empty($booking->cancellation_insurance)){
						//Cancellation Insurance Email
						$canellation_insurance_email_subject = Utility::getConfig("cancellation_insurance_email_subject")->value;
						$canellation_insurance_email_body = Utility::getConfig("cancellation_insurance_email_body")->value;
						$canellation_insurance_email_body=str_replace(
							array(
								'[$booking_id]',
								'[$client_firstname]',
								'[$client_lastname]',
								'[$client_dob]',
								'[$client_gender]',
								'[$client_parent_name]',
								'[$client_parent_email]',
								'[$client_address]',
								'[$client_phone]',
								'[$client_mobile]',
								'[$client_diet]',
								'[$client_medication]',
								'[$client_about_child]',
								'[$booking_client_insurance]',
								'[$pickup_place]',
								'[$pickup_time]',
								'[$booking_total_amount]',
								'[$deposite_amount]',
								'[$booking_date]',
								'[$trip_startdate]',
								'[$trip_enddate]',
								'[$ideal_payment_url]',
								'[$deposite_payment_url]',
								'[$trip_name]'
							),
							array(
								$booking->id,
								$booking->child_firstname,
								$booking->child_lastname,
								date('d-m-Y', strtotime($booking->child_dob)),
								$booking->gender=="0"?"Man":"Vrouw",
								$booking->parent_name,
								$booking->email,
								$client_address,
								$booking->telephone,
								$booking->cellphone,
								$booking->child_diet,
								$booking->child_medication,
								$booking->about_child,
								$booking->insurance,
								$locationPickup? $locationPickup->place : '',
								$locationPickup ? $locationPickup->time : '',
								round($booking->total_amount, 2),
								$booking->partial_payment_amount,
								date('d-m-Y', strtotime($booking->date_added)),
								date('d-m-Y', strtotime($trip->start_date)),
								date("d-m-Y", strtotime($trip->start_date)+(($trip->duration-1)*86400)),
								$ideal_payment_url,
								$deposite_payment_url,
								$trip->trip_name,
							),
							$canellation_insurance_email_body
						);
						Utility::send_email($booking->email, $canellation_insurance_email_subject, $canellation_insurance_email_body, $booking->id);
					}
					if(!empty($booking->travel_insurance)){
						//Travel Insurance Email
						$travel_insurance_email_subject = Utility::getConfig("travel_insurance_email_subject")->value;
						$travel_insurance_email_body = Utility::getConfig("travel_insurance_email_body")->value;
						$travel_insurance_email_body=str_replace(
							array(
								'[$booking_id]',
								'[$client_firstname]',
								'[$client_lastname]',
								'[$client_dob]',
								'[$client_gender]',
								'[$client_parent_name]',
								'[$client_parent_email]',
								'[$client_address]',
								'[$client_phone]',
								'[$client_mobile]',
								'[$client_diet]',
								'[$client_medication]',
								'[$client_about_child]',
								'[$booking_client_insurance]',
								'[$pickup_place]',
								'[$pickup_time]',
								'[$booking_total_amount]',
								'[$deposite_amount]',
								'[$booking_date]',
								'[$trip_startdate]',
								'[$trip_enddate]',
								'[$ideal_payment_url]',
								'[$deposite_payment_url]',
								'[$trip_name]'
							),
							array(
								$booking->id,
								$booking->child_firstname,
								$booking->child_lastname,
								date('d-m-Y', strtotime($booking->child_dob)),
								$booking->gender=="0"?"Man":"Vrouw",
								$booking->parent_name,
								$booking->email,
								$client_address,
								$booking->telephone,
								$booking->cellphone,
								$booking->child_diet,
								$booking->child_medication,
								$booking->about_child,
								$booking->insurance,
								$locationPickup? $locationPickup->place : '',
								$locationPickup ? $locationPickup->time : '',
								round($booking->total_amount, 2),
								$booking->partial_payment_amount,
								date('d-m-Y', strtotime($booking->date_added)),
								date('d-m-Y', strtotime($trip->start_date)),
								date("d-m-Y", strtotime($trip->start_date)+(($trip->duration-1)*86400)),
								$ideal_payment_url,
								$deposite_payment_url,
								$trip->trip_name,
							),
							$travel_insurance_email_body
						);
						Utility::send_email($booking->email, $travel_insurance_email_subject, $travel_insurance_email_body, $booking->id);
					}
				}
				$paymentLinks = [];
				$order = $booking->orders()->where("type", "part")->first();
				$partial = '';
				if($order){
					$order->amount = $booking->partial_payment_amount;
					$order->save();
				}
				else{
					$order = $booking->orders()->create([
						'type' => 'full',
						'amount' => $booking->total_amount
					]);
					if((Utility::calculateDiff($trip->start_date) >= 42)){
						$partial = $booking->orders()->create([
							'type' => 'part',
							'amount' => $booking->partial_payment_amount
						]);
					}
				}
				$paymentLinks = $paymentService->createUrl($order);
				if($partial){
					$partialPaymentLinks = $paymentService->createUrl($partial);
				}
				return response()->json([
					"status" => true,
					"paymentLinks" => $paymentLinks,
					"partialPaymentLinks" => $partialPaymentLinks ?? 0,
					"booking" => $booking
				]);
			}			
		}
        return response()->json(['error' => 'No Record Found'], 422);
    }
	public function rabobankResponse(Request $request)
    {
        return response()->json([
				"status" => true,
		]);
    }
	public function dashboard(Request $request){
		$user_id = null;
		if(in_array('view-myaccount', auth()->user()->given_permissions) || auth()->user()->id == 1){
			$user_id = $request->session()->get('authUserId');
			if($user_id){
				$user = User::find($user_id);
			}
		}
		if(!$user_id){
			$user = auth()->user();
		}
		$booking = TripBooking::select('id', 'email', 'trip_id', 'user_id')->with('trip', 'trip.location')->where(['user_id' => $user->id, 'status' => 1, 'deleted' => 0])->get();
		if($booking){
            return response()->json(["booking" => $booking]);
        }
		return response()->json(['error' => 'No Record Found'], 422);
	}
	public function dashboardBooking(TripBooking $tripBooking, Request $request, PaymentService $paymentService){
		$user_id = null;
		if(in_array('view-myaccount', auth()->user()->given_permissions) || auth()->user()->id == 1){
			$user_id = $request->session()->get('authUserId');
			if($user_id){
				$user = User::find($user_id);
			}
		}
		if(!$user_id){
			$user = auth()->user();
		}
		if($user){
			$date = Carbon::now();
			$booking = TripBooking::with('trip', 'trip.location', 'passportDetails')->where(['id' => $tripBooking->id, 'user_id' => $user->id, 'status' => 1, 'deleted' => 0])->first();
			
			if($booking){
				$booking->customers = TripBooking::whereHas('trip', function($q) use($booking){
						$q->where('start_date', $booking->trip->start_date);
					})->where(['trip_id' => $booking->trip_id, 'status' => 1, 'deleted' => 0])->get();
				$booking->booked_addons = TripBookingAddon::with('locationAddon')->where('trip_booking_id', $booking->id)->get();
                //return $booking->booked_addons;
				foreach($booking->booked_addons as $addon){
					//return $addon;
					$paymentLinksAddon = [];
					if($addon){
						$addonOrder = $addon->orders()->where("type", "part")->first();
						if($addonOrder){
							$addonOrder->amount = $addon->amount;
							$addonOrder->save();
						}
						else{
							$addonOrder = $addon->orders()->create([
								'type' => 'part',
								'amount' => $addon->amount
							]);
						}
						$addon->links = $paymentService->createUrl($addonOrder);
					}
				}
				$booking->addons = LocationAddon::whereIn('id', $booking->booked_addons->pluck('id'))->where('location_id', $booking->trip->location->id)->get();
				$ticketUser = TripTicketUser::where('trip_booking_id', $booking->id)->first();
				if($ticketUser){
					$booking->tickets = TripTicket::with('airline')->where('id', $ticketUser->trip_ticket_id)->orWhere('connecting_flight', $ticketUser->trip_ticket_id)->orderBy('sortorder')->get();
				}
				else{
					$booking->tickets = TripTicket::with('airline')->whereHas('trip', function($q) use($booking){
						$q->where('location_id', $booking->trip->location_id)->where('start_date', $booking->trip->start_date);
					})->where('type', 0)->orderBy('sortorder')->get();
				}
				$trip_travel_ins_fee = Utility::get_trip_travel_ins_fee($booking->trip->id);
				//$trip_survival_adventure_insurance = Utility::getConfig('survival_adventure_insurance')['value'];
				$trip_insurance_administration_charges = Utility::getConfig('insurance_administration_charges')['value'];
				$travel_insurance=($trip_travel_ins_fee*$booking->trip->duration);
				$ti_taxed=($travel_insurance*1.44)/100;
				$ti_tax=(($ti_taxed*21)/100);
				$travel_insurance=$travel_insurance+$ti_tax+$trip_insurance_administration_charges;
				//$SpecialInsurance_insurance=($trip_survival_adventure_insurance*$booking->trip->duration);
				//$SI_taxed=($SpecialInsurance_insurance*0.07)/100;
				//$SI_tax=($SI_taxed*21)/100;
				//$SpecialInsurance_insurance=$SpecialInsurance_insurance+$SI_tax;
				if(strpos($booking->insurance, "Reisverzekering")===false){
					$booking->travelInsurance = $travel_insurance;
					/*$booking->survivalAdventureInsurance = false;
					if(Utility::is_survival_adventure_insurance_active($booking->trip->id) && 0){
						$booking->survivalAdventureInsurance = true;
						$booking->specialInsurance = $SpecialInsurance_insurance;

					}*/
				}
				$paymentLinks = [];
				if($booking->total_amount > $booking->paid_amount){
					$order = $booking->orders()->where("type", "part")->first();
					if($order){
						$order->amount = $booking->total_amount;
						$order->save();
					}
					else{
						$order = $booking->orders()->create([
							'type' => 'part',
							'amount' => $booking->total_amount
						]);
					}
					//return $order;
					$paymentLinks = $paymentService->createUrl($order);
				}
				return response()->json(["booking" => $booking, "paymentLinks" => $paymentLinks]);
			}
		}
		return response()->json(['error' => 'No Record Found'], 422);
	}
	public function saveWhatsappNumber(Request $request)
    {
		$user_id = null;
        if(in_array('view-myaccount', auth()->user()->given_permissions) || auth()->user()->id == 1){
			$user_id = $request->session()->get('authUserId');
			if($user_id){
				$user = User::find($user_id);
			}
		}
		if(!$user_id){
			$user = auth()->user();
		}
        if($user) {
            $booking = TripBooking::where(['user_id' => $user->id, 'id' => $request->booking_id])->first();
			if($booking){
				$data["whatsapp_number"] = $request->whatsapp_number;
				$booking->update($data);
				return response()->json(['status' => 1, 'message' => 'Whatsapp Number Saved successfully'], 200);
			}
        }
		else{
			return response()->json([
				'status' => false
			]);
		}
    }
	public function savePassportDetails(Request $request){
		$user_id = null;
        if(in_array('view-myaccount', auth()->user()->given_permissions) || auth()->user()->id == 1){
			$user_id = $request->session()->get('authUserId');
			if($user_id){
				$user = User::find($user_id);
			}
		}
		if(!$user_id){
			$user = auth()->user();
		}
        if($user){
            $booking = TripBooking::where(['user_id' => $user->id, 'id' => $request->booking_id])->first();
            if($booking){
                $passport_details = PassportDetail::where('trip_booking_id', $booking->id)->first();
                if(!empty($passport_details)){
                    $passport_details->update([
						"document_number" => $request->document_number,
						"issue_date" => $request->issue_date,
						"expiry_date" => $request->expiry_date
					]);
                    return response()->json(["status" => true, "data" => $passport_details]);
                }
                else{
                    $saveDetails = PassportDetail::create([
						"trip_booking_id" => $booking->id,
						"document_number" => $request->document_number,
						"issue_date" => $request->issue_date,
						"expiry_date" => $request->expiry_date,
						"creater_id" => $user->id
					]);
                    return response()->json(["status" => true, "data" => $saveDetails]);
                }

            }
        }
		else{
			return response()->json([
				'status' => false
			]);
		}
    }
	public function saveInsurance(Request $request){
        $user_id = null;
		if(in_array('view-myaccount', auth()->user()->given_permissions) || auth()->user()->id == 1){
			$user_id = $request->session()->get('authUserId');
			if($user_id){
				$user = User::find($user_id);
			}
		}
		if(!$user_id){
			$user = auth()->user();
		}
        if($user){
            $date = Carbon::now();
            $booking = TripBooking::where(['user_id' => $user->id, 'id' => $request->booking_id])->first();
            if($booking){
                $trip = Trip::where('id', $booking->trip_id)->first(['id', 'start_date', 'duration']);
                $trip_travel_ins_fee = Utility::get_trip_travel_ins_fee($trip->id);
                //$trip_survival_adventure_insurance = Utility::getConfig('survival_adventure_insurance')['value'];
                $trip_insurance_administration_charges = Utility::getConfig('insurance_administration_charges')['value'];
                $total_amount=$booking->total_amount;
                $insurance_arr=array();
                if(!empty($booking->insurance))
                    $insurance_arr=explode(", ", $booking->insurance);
                if(in_array("Reisverzekering", $request->insurance)){
                    $travel_insurance=($trip_travel_ins_fee*$trip->duration);
                    $ti_taxed=($travel_insurance*1.44)/100;
                    $ti_tax=(($ti_taxed*21)/100);
                    $travel_insurance=$travel_insurance+$ti_tax;
                    $total_amount+=$travel_insurance;
                    $insurance_arr[]="Reisverzekering";
                }
                else{
                    $travel_insurance=0;
                }
                /*if(in_array("Survival en Adventure verzekering", $request->insurance)){
                    $SpecialInsurance_insurance=($trip_survival_adventure_insurance*$trip->duration);
                    $SI_taxed=($SpecialInsurance_insurance*0.07)/100;
                    $SI_tax=($SI_taxed*21)/100;
                    $survival_adventure_insurance=$SpecialInsurance_insurance+$SI_tax;
                    $total_amount+=$survival_adventure_insurance;
                    $insurance_arr[]="Survival en Adventure verzekering";
                }
                else{
                    $survival_adventure_insurance=0;
                }*/
                if(!empty($travel_insurance)){
                    $insurance_admin_charges=$trip_insurance_administration_charges;
                    if($booking->insurance_admin_charges==0)
                        $total_amount+=$insurance_admin_charges;
                }
                else{
                    $insurance_admin_charges=0;
                }
                $booking->update([
                    'insurance' => implode(", ", $insurance_arr),
                    'travel_insurance' => $travel_insurance,
                    'insurance_admin_charges' => $insurance_admin_charges,
                    'total_amount' => $total_amount,
                ]);
                $extraInsurance = TripBookingExtraInsurance::create([
                    'trip_booking_id' => $request->booking_id,
                    'date' => $date,
                    'insurance' => implode(", ", $insurance_arr),
                    'travel_insurance' => $travel_insurance,
                    'survival_adventure_insurance' => $survival_adventure_insurance,
                    'insurance_admin_charges' => $insurance_admin_charges,

                ]);
                return response()->json(['booking' => $booking, 'extraInsurance' => $extraInsurance, 'status' => true]);
            }
            else{
                return response()->json([
                    'status' => false,
                ]);
            }
        }
    }
	public function updateProfile(Request $request)
    {
		$user_id = null;
        if(in_array('view-myaccount', auth()->user()->given_permissions) || auth()->user()->id == 1){
			$user_id = $request->session()->get('authUserId');
			if($user_id){
				$user = User::find($user_id);
			}
		}
		if(!$user_id){
			$user = auth()->user();
		}
		if($user){
			$data = $request->all();
			$data["password"] = \Hash::make($data["password"]);
			$user->update($data);
			return response()->json(['message' => 'Profile Saved successfully'], 200);
		}
    }
	public function forumTopics(Request $request)
    {
        $forumTopics = ForumTopic::when($request->get('search'), function($q) use($request){
            $q->where('title', 'LIKE', '%'.$request->search.'%');
        })->with(['forumCategory:id,title', 'user:id,name,email'])->withCount('forumReplies')->orderBy('created_at', 'desc')->paginate($request->get("limit", 25));
        if($forumTopics){
            return response()->json($forumTopics);
        }
        return response()->json(['error' => 'No Record Found'], 422);
    }
	public function singleTopic(ForumTopic $forumTopic, Request $request){
		$topic = ForumTopic::with('forumReplies', 'forumReplies.user', 'forumCategory', 'user')->where(['id' => $forumTopic->id])->first();
		if($topic){
            return response()->json($topic);
        }
		return response()->json(['error' => 'No Record Found'], 422);
	}
	public function saveAddon(Request $request, PaymentService $paymentService){
		//return $request;
        $user_id = null;
		$date = Carbon::now();
		if(in_array('view-myaccount', auth()->user()->given_permissions) || auth()->user()->id == 1){
			$user_id = $request->session()->get('authUserId');
			if($user_id){
				$user = User::find($user_id);
			}
		}
		if(!$user_id){
			$user = auth()->user();
		}
        if($user){
			$addon = LocationAddon::find($request->get('addon_id'));
			//return $addon;
			if($request->is_confirm){
				$bookedAddon = TripBookingAddon::create([
                    'trip_booking_id' => $request->booking_id,
                    'location_addon_id' => $addon->id,
					'booking_date' => $date,
                    'amount' => $addon->price,
                    'extra_field_1' => $request->extra_field_1,
                    'extra_field_2' => $request->extra_field_2,
                    'extra_field_3' => $request->extra_field_3,

                ]);
				$paymentLinks = [];
				if($bookedAddon){
					$order = $bookedAddon->orders()->where("type", "part")->first();
					if($order){
						$order->amount = $bookedAddon->amount;
						$order->save();
					}
					else{
						$order = $bookedAddon->orders()->create([
							'type' => 'part',
							'amount' => $bookedAddon->amount
						]);
					}
					$paymentLinks = $paymentService->createUrl($order);
					
				}
                return response()->json(['bookedAddon' => $bookedAddon, "paymentLinks" => $paymentLinks, 'status' => true]);
			}
		}
	}
	public function getInsurance(Request $request){
		$user_id = null;
		$date = Carbon::now();
		if(in_array('view-myaccount', auth()->user()->given_permissions) || auth()->user()->id == 1){
			$user_id = $request->session()->get('authUserId');
			if($user_id){
				$user = User::find($user_id);
			}
		}
		if(!$user_id){
			$user = auth()->user();
		}
        if($user){
			$booking = TripBooking::where(['user_id' => $user->id, 'status' => 1, 'deleted' => 0])->orderBy('created_at', 'desc')->first();
			return response()->json(["booking" => $booking]);
		}
	}
	public function saveComment(Request $request)
    {
		$user_id = null;
        if(in_array('view-myaccount', auth()->user()->given_permissions) || auth()->user()->id == 1){
			$user_id = $request->session()->get('authUserId');
			if($user_id){
				$user = User::find($user_id);
			}
		}
		if(!$user_id){
			$user = auth()->user();
		}
        if($user) {
            $topic = ForumTopic::where(['id' => $request->topic_id])->first();
			if($topic){
				$reply = ForumReply::create([
                    'forum_topic_id' => $topic->id,
                    'user_id' => $user->id,
					'content' => $request->content

                ]);
				$replyRes = ForumReply::with('user')->find($reply->id);
				return response()->json([
					'status' => 1, 
					'message' => 'Reply has been done',
					'reply' => $replyRes
				], 200);
			}
        }
		else{
			return response()->json([
				'status' => false
			]);
		}
    }
}
