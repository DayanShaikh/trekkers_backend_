<?php

namespace App;

use App\Models\ConfigVariable;
use App\Models\Reservation;
use App\Models\Trip;
use Carbon\Carbon;
use App\Models\TripBookingNote;
use Storage;
use Mail;
use PDF;

class Utility{
    public static $has_flights = ['No', 'Individual', 'Group'];
    public static $commission_types = ['Fixed', 'Percent'];
    public static $activity_icons = ['Hotel', 'Camping', 'Hostel', 'Apartment', 'Flight', 'No flight', 'Bus', 'No bus', 'Activity', 'Luggage', 'Food', 'No food', 'Breakfast only'];
    public static $page_types = ['Normal Info Page', 'Trip Info Page', 'Country Info Page', 'Trip Info All-in Page', 'Trip Info Page Two Programs'];
    public static $input_types = ['Text', 'Checkbox(s)', 'Radio Buttons', 'Textarea', 'Editor', 'File', 'Select Box'];
    public static $genders = ['S/o', 'D/o', 'W/o'];
    public static $months = ['Januari','Februari','Maart','April','Mei','Juni','Juli','Augustus','September','Oktober','November','December'];
	public static $month_short_name = ['jan','feb','maa','apr','mei','jun','jul','aug','sep','okt','nov','dec'];
    public static $trip_letter_types = ['No', 'Yes', 'keep hidden'];
    public static $trip_seat_status = ['Normal', 'Less than 10 spots remaining', 'Almost Full'];
    public static $position_types = ['Top', 'Bottom', 'User Dashboard'];
    public static $trip_levels = ['Eenvoudig', 'Normale Moeilijkheid', 'Uitdagend/Intensief'];
    public static $location_includes = ['Activiteiten', 'Vervoer', 'Verblijf', 'Maaltijden'];
    public static $covid_options = ['Select Covid Option', 'Andere bestemming', 'Reis verplaatsen', 'Mijn geld terug'];
	public static $landing_pages = ['Home', 'Contact', 'Month', 'Blog', 'Bestemmingen', 'Vakanties'];
	public static function getMonthShortName($month){
		return isset(Utility::$month_short_name[$month])?Utility::$month_short_name[$month]:'--';
	}
	public static function getMonthName($month){
		return isset(Utility::$months[$month])?Utility::$months[$month]:'--';
	}
	public static function getCommissionType($commission_type){
        return isset(Utility::$commission_types[$commission_type])?Utility::$commission_types[$commission_type]:'--';
    }
    public static function getHasFlight($has_flight){
        return isset(Utility::$has_flights[$has_flight])?Utility::$has_flights[$has_flight]:'--';
    }
    public static function getActivityIcon($activity_icon){
        return isset(Utility::$activity_icons[$activity_icon])?Utility::$activity_icons[$activity_icon]:'--';
    }
    public static function getInputType($input_type){
        return isset(Utility::$input_types[$input_type])?Utility::$input_types[$input_type]:'--';
    }
    public static function getPageType($page_type){
        return isset(Utility::$page_types[$page_type])?Utility::$page_types[$page_type]:'--';
    }
    public static function getGender($gender){
        return isset(Utility::$genders[$gender])?Utility::$genders[$gender]:'--';
    }
    public static function getPositionType($position_type){
        return isset(Utility::$position_types[$position_type])?Utility::$position_types[$position_type]:'--';
    }
    public static function getTripLevel($trip_level){
        return isset(Utility::$trip_levels[$trip_level])?Utility::$trip_levels[$trip_level]:'--';
    }
    public static function getLocationInclude($location_include){
        return isset(Utility::$location_includes[$location_include])?Utility::$location_includes[$location_include]:'--';
    }
    public static function getConfig($key){
        $config = ConfigVariable::where('config_key', $key)->first(['id','config_key','value']);
        return $config;
    }
    public static function get_trip_travel_ins_fee($trip_id){
        $fees = \App\Models\Trip::with(['location:id,destination_id', 'location.destination:id,travel_insurance_fees'])->where('id', $trip_id)->first(['id','location_id']);
        return $fees->location->destination["travel_insurance_fees"];
    }
    public static function is_survival_adventure_insurance_active($trip_id){
        $fees = \App\Models\Trip::with(['location:id,destination_id', 'location.destination:id,is_survival_adventure_insurance_active'])->where('id', $trip_id)->first(['id','location_id']);
        return $fees->location->destination["is_survival_adventure_insurance_active"];
    }
	public static function get_reservation_expiry($reservation){
        if(!is_array($reservation)){
			$reservation = Reservation::find($reservation);
			if($reservation){
				$reservation = $reservation;
			}
			else{
				return "Expired";
			}
		}
		$trip = Trip::where('id', $reservation["trip_id"])->first();
		$days = round( (strtotime($trip->start_date) - time() )/ (60 * 60 * 24));
		if(!empty($reservation->expiry_date)){
			return $reservation->expiry_date->format('d-m-Y');
		}
		else if($days > 14){
			if($trip->space->remaining < 3){
				return "Expired";
			}
			else{
				$daysUntilReserve = $days - 14;
				$daysUntilReserve = $daysUntilReserve > 60 ? 60 : ($daysUntilReserve > 14 ? 14 : $daysUntilReserve);
				return date("d/m/Y", strtotime("+".$daysUntilReserve." days", strtotime($reservation["date_added"])));
			}
		}
		else{
			return "Expired";
		}
    }
	public static function get_reservation_url($reservation, $cancel_url=0){
		if(!is_array($reservation)){
			$reservation = Reservation::where('id', $reservation)->first();
			if($reservation){
				$reservation = $reservation;
			}
			else{
				return "Expired";
			}
		}
		$site_url = Utility::getConfig("site_url")->value;
		$trip = Trip::with('location', 'location.page')->where('id', $reservation["trip_id"])->first();
		return $site_url."/boek/".$trip->start_date->format('Y-m-d')."/".$trip->location->page->page_name.".html?reservation_id=".$reservation["id"]."&key=".md5($reservation["email"].$reservation["child_firstname"]).($cancel_url==1?"&cancel":"");
	}
    public static function calculateDiff($start_date)
	{
        $currentDate = Carbon::now();
        $date = Carbon::parse($start_date);
        $days = $currentDate->diffInDays($date);
        return $days;
	}
	public static function get_email_formatted($body){
		$logo = \App\Utility::getConfig("admin_logo")->value;
		$site_title = \App\Utility::getConfig("site_title")->value;
		$site_url = \App\Utility::getConfig("site_url")->value;
		//return $logo;
		return '<table style="width:100%; background-color:#eeeeee;" cellpadding="10" cellspacing="0" align="center" class="sep">
			<tr>
				<td align="center"><table style="max-width:100%;width:800px;background-color:#ffffff" cellpadding="10" cellspacing="0" align="center">
					<thead>
						<tr>
							<td style="text-align: left; border-bottom: solid 1px #dddddd; background-color:#ffffff;"><a href="'.$site_url.'"><img src="https://d1n5x7e4cmsf36.cloudfront.net/public/config/upload-20210831131111-6-thumbnail.png" alt="'.$site_title.'" /></a></td>
							<td style="text-align: right; border-bottom: solid 1px #dddddd; background-color:#ffffff;"><strong>055 737 00 14</strong></td>
						</tr>
					</thead>
					<tr>
						<td colspan="2" class="maintd" style="padding: 50px 20px; background-color:#ffffff;">'.str_replace( "<table", '<table border="1" cellpadding="7", cellspacing="0" style="border-collapse: collapse"', $body ).'</td>
					</tr>
					<tfoot>
						<tr>
							<td style="text-align: left; background-color:#00AEEF; color:#ffffff;">Actieve groepsreizen voor jongeren. Jongerenreizen, singlereizen en meer!<br><a href="'.$site_url.'/simi/privacy-cookies.html" style="color:#ffffff;">Privacybeleid</a> | <a href="'.$site_url.'/simi/privacy-cookies.html" style="color:#ffffff;">Cookie-beleid</a> | <a href="'.$site_url.'/sitemap" style="color:#ffffff;">Sitemap</a></td>
							<td style="text-align: right; background-color:#00AEEF; color:#ffffff;">&copy; '.date( "Y" ).' Simi Reizen</td>
						</tr>
					</tfoot>
				</table></td>
			</tr>
		</table>';
	}

    public static function downloadBookingPDF($body){
		$logo = \App\Utility::getConfig("admin_logo")->value;
		$site_title = \App\Utility::getConfig("site_title")->value;
		$site_url = \App\Utility::getConfig("site_url")->value;

        $pdf = PDF::loadView('booking', ['logo' => $logo, 'site_title' => $site_title, 'site_url' => $site_url, 'booking' => $body]);
    	return $pdf->download('boekingsbevestiging.pdf');
    }

	public static function send_email($emailTo, $subject, $body, $bookingId){
		//return "Test";
		$body = \App\Utility::get_email_formatted( $body );
		$booking_email = \App\Utility::getConfig("booking_email")->value;
		Mail::send([],[],function($msg) use ($emailTo, $subject, $body)
        {
           try {
            $msg->to($emailTo)
            ->subject($subject)
			->html($body);
			$msg->from('info@simi-reizen.nl','Simi');
           } catch (\Throwable $th) {
                throw $th;
           }
        });
		$notes = [
			'trip_booking_id'	=>  $bookingId,
            'notes'   =>  date("d-M h:i")." - ".$subject." verzonden"
		];
		$tripBookingNote = TripBookingNote::create($notes);
		return response()->json([
			'status' => true,
			"message" => 'Email has been sent'
		]);
	}
    //public static $permissionAccess = ['viewAny', 'view', 'create', 'updateAny', 'update', 'deleteAny', 'delete', 'restoreAny', 'restore', 'forceDeleteAny', 'forceDelete'];
    public static $permissionAccess = ['Read', 'Add', 'Update', 'Delete'];
	public static $permissionModels = [
	  	['key'=> 'user', 'name' => 'Users'],
	  	['key'=> 'role', 'name' => 'Roles'],
        ['key'=> 'header_video', 'name' => 'Header Videos'],
        ['key'=> 'attribute', 'name' => 'Attributes'],
        ['key'=> 'front_menu', 'name' => 'Front Menus'],
        ['key'=> 'page', 'name' => 'Pages'],
        ['key'=> 'location_day', 'name' => 'Location Days'],
        ['key'=> 'page_gallery', 'name' => 'Page Galleries'],
        ['key'=> 'age_group', 'name' => 'Age Groups'],
        ['key'=> 'age_group_month_meta', 'name' => 'Age Group Month Metas'],
        ['key'=> 'trip_type', 'name' => 'Trip Types'],
        ['key'=> 'location', 'name' => 'Locations'],
        ['key'=> 'location_age_group', 'name' => 'Location Age Group'],
        ['key'=> 'location_pickup', 'name' => 'Location Pickup'],
        ['key'=> 'location_addon', 'name' => 'Location Addons'],
        ['key'=> 'trip', 'name' => 'Trips'],
        ['key'=> 'trip_ticket', 'name' => 'Trip Tickets'],
        ['key'=> 'trip_tour_guide', 'name' => 'Trip Tour Guides'],
        ['key'=> 'trip_document', 'name' => 'Trip Documents'],
        ['key'=> 'trip_template', 'name' => 'Trip Templates'],
        ['key'=> 'tour_guide_info', 'name' => 'Tour Guide Infos'],
        ['key'=> 'travel_agent', 'name' => 'Travel Agents'],
        ['key'=> 'travel_admin', 'name' => 'Travel Admin'],
        ['key'=> 'travel_brand', 'name' => 'Travel Brands'],
        ['key'=> 'trip_booking', 'name' => 'Trip Bookings'],
        ['key'=> 'trip_booking_note', 'name' => 'Trip Booking Notes'],
        ['key'=> 'trip_booking_document', 'name' => 'Trip Booking Documents'],
        ['key'=> 'trip_booking_addon', 'name' => 'Trip Booking Addons'],
        ['key'=> 'trip_booking_payment', 'name' => 'Trip Booking Payments'],
        ['key'=> 'trip_booking_extra_insurance', 'name' => 'Trip Booking Extra Insurances'],
        ['key'=> 'review', 'name' => 'Reviews'],
        ['key'=> 'airline', 'name' => 'Airlines'],
        ['key'=> 'reservation', 'name' => 'Reservations'],
        ['key'=> 'blog_category', 'name' => 'Blog Categories'],
        ['key'=> 'blog', 'name' => 'Blogs'],
        ['key'=> 'support_category', 'name' => 'Support Categories'],
        ['key'=> 'support_article', 'name' => 'Support Articles'],
        ['key'=> 'forum_category', 'name' => 'Forum Categories'],
        ['key'=> 'forum_topic', 'name' => 'Forum Topics'],
        ['key'=> 'forum_reply', 'name' => 'Forum Replies'],
        ['key'=> 'config_page', 'name' => 'Config Pages'],
		['key'=> 'config_variable', 'name' => 'Config Variables'],
        ['key'=> 'destination', 'name' => 'Destinations'],
		['key'=> 'course', 'name' => 'Course'],
		['key'=> 'lesson', 'name' => 'Lesson'],
		['key'=> 'quiz_question', 'name' => 'Quiz Question'],
		['key'=> 'reminder', 'name' => 'Reminder'],
        ['key'=> 'landing_page', 'name' => 'Landing Page'],
        ['key'=> 'myaccount', 'name' => 'My Account'],
        ['key'=> 'page_redirect', 'name' => 'Page Redirect'],
    ];
}
