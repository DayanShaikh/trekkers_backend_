<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\TripBooking;
use App\Models\TripBookingNote;
use App\Models\Role;
use App\Models\Trip;
use App\Models\User;

class TripBookingSeeder extends Seeder
{
    public function clean($text){
		$text = utf8_decode($text);
		$text = stripslashes($text);
		return str_replace("https://beta.simi-reizen.nl/uploads/page_images/dag/", "https://d1n5x7e4cmsf36.cloudfront.net/public/uploads/", str_replace("https://beta.simi-reizen.nl/uploads/page_images/gallery/", "https://d1n5x7e4cmsf36.cloudfront.net/public/uploads/", str_replace("https://beta.simi-reizen.nl/uploads/upload_files/", "https://d1n5x7e4cmsf36.cloudfront.net/public/uploads/", str_replace("www.", "", str_replace("simi-reizen.nl", "beta.simi-reizen.nl", $text)))));
	}
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $backup = DB::connection('mysql_backup');
        TripBooking::truncate();
        $tripCount = Trip::select(DB::raw('max(id) as id'))->first()->id+1;
        $clients = [];
        $dependantTrips = [];
        $client = Role::where('name', 'Client')->first();
		$backup->table('trip_booking')->select(DB::raw('trip_booking.*, trips.location_id, trips.start_date, clients.display_name as client_name, clients.email as client_email, clients.password as client_password'))->join("trips", "trip_booking.trip_id", "=", "trips.id")->leftJoin('clients', 'trip_booking.client_id', '=', 'clients.id')->orderBy("date_added")->chunk(500, function ($bookings) use(&$dependantTrips, &$clients, &$client, &$tripCount) {
			foreach ($bookings as $booking) {
                if(!isset($dependantTrips[$booking->location_id.$booking->start_date])){
                    $trip = Trip::where("location_id", $booking->location_id)->where("start_date", $booking->start_date)->first();
                    if($trip){
                        $dependantTrips[$booking->location_id.$booking->start_date] = $trip;
                    }
                    else{
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
				}
				else{
					echo $booking->trip_id." = DTrip = ".($dependantTrips[$booking->location_id.$booking->start_date]->id).PHP_EOL;
				}
				if(!isset($clients[$booking->client_id])){
					$user = User::where("email", $this->clean(empty($booking->client_email) ? $booking->email : $booking->client_email))->first();
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
    }
}
