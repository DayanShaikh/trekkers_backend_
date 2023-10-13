<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use App\Models\LandingPage;
use Illuminate\Http\Request;
use App\Models\FrontMenu;
use App\Models\ConfigVariable;
use App\Models\TripBooking;
use App\Models\AgeGroup;
use App\Models\Page;
use App\Models\Location;
use App\Models\Blog;
use App\Models\PageRedirect;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Mail;
use Utility;

class HelperController extends Controller
{

	/**
	 * Get Site Settings
	 *
	 * This endpoint will provide you static setting arrays.
	 */
    public function getSetting(Request $request)
	{
        $response = [];
		$helpersReflection = new \ReflectionClass('App\Utility');
		$staticMembers = $helpersReflection->getStaticProperties();

		foreach($staticMembers as $field => &$value) {
			$response[$field] = $value;
		}
		return response()->json($response);
	}
	public function getGeneralConfigurations(Request $request)
    {
        $response = [];

        $class = new \ReflectionClass('App\Utility');
        $staticMembers = $class->getStaticProperties();

        foreach($staticMembers as $field => $value) {
            $response[$field] = $value;
        }

        $ageGroups = AgeGroup::where('status', 1)->get();

        $frontMenu = FrontMenu::with(['frontSubMenu'], function ($q){
            $q->where('parent_id', '!=', 0)->get();
        })->where(['position' => 0, 'parent_id' => 0])->orderBy('sortorder')
            ->get();
        $config = ConfigVariable::select(["config_key", "value"])->where('autoload', 1)->get()->keyBy('config_key');
		$attributes = Attribute::where('status',1)->get();
		$homePage = LandingPage::where("type", 0)->first()->page;
        return response()->json(["setting" => $response, "homePage" => $homePage, "ageGroups" => $ageGroups, "frontMenus" => $frontMenu, "config" => $config, "attributes" => $attributes]);
    }
    public function depositReminder($id, Request $request)
    {
		$tripBooking = TripBooking::with('trip')->find($id);
		$emailTo = $tripBooking->email;
		$subject = \App\Utility::getConfig('deposit_reminder_email_subject')->value;
		$body = \App\Utility::getConfig('deposit_reminder_email_body')->value;
		//return $body;
		$deposit_percent = \App\Utility::getConfig("deposit_percent")->value;
		//return $deposit_percent;
		$available_upfront_fee = round(($tripBooking->trip_fee * $deposit_percent) / 100 + $tripBooking->cancellation_insurance + $tripBooking->travel_insurance + $tripBooking->survival_adventure_insurance + $tripBooking->nature_disaster_insurance + $tripBooking->insurance_admin_charges,2);
		$upfront_fee = round($tripBooking->trip_fee + $tripBooking->cancellation_insurance + $tripBooking->travel_insurance + $tripBooking->survival_adventure_insurance + $tripBooking->nature_disaster_insurance + $tripBooking->insurance_admin_charges,2);
		$start_date = Carbon::createFromDate($tripBooking->trip->start_date);
		$now = Carbon::now();
		$diff = $start_date->diffInDays($now);
		//return $testdate;
		if($diff > 42){
			$deposit_amount = $available_upfront_fee;
		}
		else{
			$deposit_amount = $upfront_fee;
		}
		$body=str_replace(array(
			'[$client_firstname]',
			'[$client_lastname]',
			'trip_book_date',
			'{trip_destination}',
			'{deposit_amount}',
			'{booking_number}'
		), array(
			$tripBooking->child_firstname,
			$tripBooking->child_lastname,
			date('d-m-Y', strtotime($tripBooking->date_added)),
			date('d-m-Y', strtotime($tripBooking->trip->start_date)),
			$deposit_amount,
			$tripBooking->id
		), $body);
		$tripBooking->update(["deposit_reminder_email_sent" => 1]);
		//return $subject;
        return \App\Utility::send_email($emailTo, $subject, $body, $tripBooking->id);
		
    }
	public function passwordEmail($id, Request $request)
    {
		$tripBooking = TripBooking::find($id);
		$emailTo = $tripBooking->email;
		$subject = \App\Utility::getConfig('logininstruction_email_subject')->value;
		$body = \App\Utility::getConfig('logininstruction_email_body')->value;
		$body=str_replace(array(
			'[$email]',
		), array(
			$tripBooking->email,
		), $body);
		$tripBooking->update(["email_sent" => 1]);
        return \App\Utility::send_email($emailTo, $subject, $body, $tripBooking->id);
		
    }
	public function passportReminder($id, Request $request)
    {
		$tripBooking = TripBooking::with('trip', 'trip.location')->find($id);
		$emailTo = $tripBooking->email;
		$subject = \App\Utility::getConfig('passport_reminder_subject')->value;
		$body = \App\Utility::getConfig('passport_reminder_body')->value;
		$body = str_replace(array(
			'[$child_firstname]',
			'[$child_lastname]',
			'[$tripname]',
		), array(
			$tripBooking->child_firstname,
			$tripBooking->child_lastname,
			$tripBooking->trip->location->title
		), $body);
        return \App\Utility::send_email($emailTo, $subject, $body, $tripBooking->id);
		
    }
	public function sitemap(Request $request)
	{
        $location = Location::with('page')->where('status', true)->get();
		$blog = Blog::where('status', true)->orderBy('published_at', 'desc')->get();
		return response()->json(['locations' => $location, 'blogs' => $blog]);
	}
	public static function sitemapXml(Request $request)
	{
		$pages = Page::select(DB::raw("pageable_type, id, pageable_id, page_name"))->where("status", true)->get();
		$blogs = Blog::get();
		
		$pages->map(function($page){
			$page->type = strtolower(str_replace('App\\Models\\', '', $page->pageable_type));
			unset($page->pageable_type);
			if($page->type === 'landingpage'){
				$page->extra = LandingPage::select("type", "month")->find($page->pageable_id);
			}
			
			
			return $page;
		});
		$pageRedirect = '';
		foreach($pages as $page){
			$url = 'https://beta.simi-reizen.nl/'.$page->page_name.'.html';
			$pageRedirect = PageRedirect::where('redirect_url', $url)->first();
		}
		
		$headers = header("Content-Type: text/xml");
        return response()->view('sitemap.xml', compact('pages', 'blogs', 'pageRedirect')) ->withHeaders([
			'Content-Type' => 'text/xml'
		]);
	}
	public function bookingPDF(TripBooking $tripBooking)
	{
		//return "test";
		$booking = TripBooking::with('trip', 'locationPickup')->find($tripBooking->id);
		//return $booking;
		if($booking){
			$deposit_percent = \App\Utility::getConfig("deposit_percent")->value;
			$client_address = $booking->address." ".$booking->house_number." ".$booking->additional_address." ".$booking->postcode." ".$booking->city;	
			$start_date = Carbon::createFromDate($booking->trip->start_date);
			$now = Carbon::now();
			$diff = $start_date->diffInDays($now);
			if($diff > 42){
				$total = $booking->trip_fee;
				$check_fee_flag=1;
			}
			else{
				$total = $booking->trip_fee*$deposit_percent/100;
				$check_fee_flag=0;
			}
			$available_upfront_fee = round($total + $booking->cancellation_insurance + $booking->travel_insurance + $booking->survival_adventure_insurance + $booking->nature_disaster_insurance + $booking->insurance_admin_charges,2);
			$deposite_payment_url = "https://www.simi-reizen.nl/deposite_payment.php?booking_id=".$booking->id;
			$subject = \App\Utility::getConfig("booking_email_subject")->value;
			$booking_email_subject=str_replace('[$trip_startdate]', $booking->trip->id, $subject);
			$booking_email_body = \App\Utility::getConfig("booking_email_body")->value;
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
					$booking->location_pickup ? $booking->location_pickup->place : '',
					$booking->location_pickup ? $booking->location_pickup->time : '',
					round($booking->total_amount, 2),
					$booking->partial_payment_amount,
					date('d-m-Y', strtotime($booking->created_at)),
					date('d-m-Y', strtotime($booking->trip->start_date)),
					date("d-m-Y", strtotime($booking->trip->start_date)+(($booking->trip->duration-1)*86400)),
					'',
					$deposite_payment_url,
					$booking->trip->id,
				),
				$booking_email_body
			);
			return \App\Utility::get_email_formatted($booking_email_body);
		}
		
	}

	public function downloadBookingPDF(TripBooking $tripBooking)
	{
		$booking_email_body;
		$booking = TripBooking::with('trip', 'trip.location', 'locationPickup')->find($tripBooking->id);
		if($booking){
			$deposit_percent = \App\Utility::getConfig("deposit_percent")->value;
			$client_address = $booking->address." ".$booking->house_number." ".$booking->additional_address." ".$booking->postcode." ".$booking->city;	
			$start_date = Carbon::createFromDate($booking->trip->start_date);
			$now = Carbon::now();
			$diff = $start_date->diffInDays($now);
			if($diff > 42){
				$total = $booking->trip_fee;
				$check_fee_flag=1;
			}
			else{
				$total = $booking->trip_fee*$deposit_percent/100;
				$check_fee_flag=0;
			}
			$available_upfront_fee = round($total + $booking->cancellation_insurance + $booking->travel_insurance + $booking->survival_adventure_insurance + $booking->nature_disaster_insurance + $booking->insurance_admin_charges,2);
			$deposite_payment_url = "https://www.simi-reizen.nl/deposite_payment.php?booking_id=".$booking->id;
			$subject = \App\Utility::getConfig("booking_email_subject")->value;

			$data = (object)[
                "booking_id" => $booking->id,
                "client_firstname" => $booking->child_firstname,
                "client_lastname" => $booking->child_lastname,
				"contact_person_name" => $booking->contact_person_name,
                "client_dob" => date('d-m-Y', strtotime($booking->child_dob)),
                "client_gender" => $booking->gender=="0"?"Man":"Vrouw",
                "client_parent_name" => $booking->parent_name,
                "client_parent_email" => $booking->email,
                "client_address" => $client_address,
                "client_phone" => $booking->telephone,
                "client_mobile" => $booking->cellphone,
                "client_diet" => $booking->child_diet,
                "client_medication" => $booking->child_medication,
                "client_about_child" => $booking->about_child,
                "booking_client_insurance" => $booking->insurance,
			    "pickup_place" => $booking->location_pickup ? $booking->location_pickup->place : '',
		  	    "pickup_time" => $booking->location_pickup ? $booking->location_pickup->time : '',
		   	    "booking_total_amount" => round($booking->total_amount, 2),
		        "deposite_amount" => $booking->partial_payment_amount,
			    "booking_date" => date('d-m-Y', strtotime($booking->created_at)),
			    "trip_startdate" => date('d-m-Y', strtotime($booking->trip->start_date)),
		   	    "trip_enddate" => date("d-m-Y", strtotime($booking->trip->start_date)+(($booking->trip->duration-1)*86400)),
			    "ideal_payment_url" => '',
			    "deposite_payment_url" => $deposite_payment_url,
			    "trip_name" => $booking->trip->location->title,
            ];

			return \App\Utility::downloadBookingPDF($data);
		}
	}
}
