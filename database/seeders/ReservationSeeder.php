<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Reservation;
use App\Models\Role;
use App\Models\Trip;
use App\Models\User;
use App\Models\ReservationNote;

class ReservationSeeder extends Seeder
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
    public function run()
    {
        $backup = DB::connection('mysql_backup');
        $records = $backup->table('reservation')->select('*')->get();
        //$dependantTrips = [];
		$reservations = $backup->table('reservation')->select('*')->get();
        foreach ($reservations as $reservation) {
            if($reservation->gender=='male'){
                $gender = false;
            }
            else{
                $gender = true;
            }
            $updRes = Reservation::where('id', $reservation->id);
            $updRes->update(['gender' => $gender]);

        }
        // $tripCount = 3000;
		// $clients = [];
        // $client = Role::where('name', 'Client')->first();
        // Reservation::truncate();
		// $backup->table('reservation')->select(DB::raw('reservation.*, trips.location_id, trips.start_date, clients.display_name as client_name, clients.email as client_email, clients.password as client_password'))->join("trips", "reservation.trip_id", "=", "trips.id")->leftJoin('clients', 'reservation.client_id', '=', 'clients.id')->orderBy("date_added")->chunk(500, function ($reservations) use(&$dependantTrips, &$clients, &$client, &$tripCount) {
		// 	foreach ($reservations as $reservation) {
		// 		$gender = $reservation->gender=='male'?0:1;
		// 		if($reservation->expiry_date=="0000-00-00"){
		// 			$reservation->expiry_date = Null;
		// 		}
		// 		if(!isset($dependantTrips[$reservation->location_id.$reservation->start_date])){
		// 			$reservation->trip_id = $tripCount;
		// 			$tripCount++;
		// 			echo $reservation->trip_id." = Trip = ".$reservation->location_id.PHP_EOL;
		// 			$dependantTrips[$reservation->location_id.$reservation->start_date] = Trip::create([
		// 				"id" => $reservation->trip_id,
		// 				"location_id" => $reservation->location_id,
		// 				"total_space" => 30,
		// 				"male_female_important" => false,
		// 				"show_client_detail" => false,
		// 				"start_date" => $reservation->start_date,
		// 				"duration" => 10,
		// 				"trip_fee" => $reservation->trip_fee,
		// 				"trip_seats_status" => false,
		// 				"is_not_bookable" => false,
		// 				"archive" => true,
		// 				"is_full" => true,
		// 				"status" => false,
		// 				"creater_id" => 1
		// 			]);
		// 		}
		// 		else{
		// 			echo $reservation->trip_id." = DTrip = ".($dependantTrips[$reservation->location_id.$reservation->start_date]->id).PHP_EOL;
		// 		}
		// 		if(!isset($clients[$reservation->client_id])){
		// 			$user = User::where("email", empty($reservation->client_email) ? $reservation->email : $reservation->client_email)->first();
		// 				if($user){
		// 					$clients[$reservation->client_id] = $user;
		// 				}
		// 				else{
		// 					$clients[$reservation->client_id] = User::create([
		// 						'name' => $this->clean(empty($reservation->client_email) ? $reservation->display_name : $reservation->client_name),
		// 						'email' => $this->clean(empty($reservation->client_email) ? $reservation->email : $reservation->client_email),
		// 						'password' => empty($reservation->client_password) ? bcrypt( 'secret' ) : $reservation->client_password
		// 					]);
		// 					$clients[$reservation->client_id]->roles()->attach($client);
		// 				}
		// 		}
		// 		Reservation::create([
		// 			"id" => $reservation->id,
		// 			"trip_id" => $dependantTrips[$reservation->location_id.$reservation->start_date]->id,
		// 			"user_id" =>  $clients[$reservation->client_id]->id,
		// 			"trip_booking_id" => $reservation->booking_id,
		// 			"child_firstname" => $this->clean($reservation->child_firstname),
		// 			"child_lastname" => $this->clean($reservation->child_lastname),
		// 			"gender" => $gender,
		// 			"child_dob" => $reservation->child_dob,
		// 			"parent_name" => $this->clean($reservation->parent_name),
		// 			"parent_email" => $this->clean($reservation->parent_email),
		// 			"email" => $this->clean($reservation->email),
		// 			"address" => $this->clean($reservation->address),
		// 			"house_number" => $this->clean($reservation->house_number),
		// 			"city" => $this->clean($reservation->city),
		// 			"postcode" => $this->clean($reservation->postcode),
		// 			"telephone" => $this->clean($reservation->telephone),
		// 			"cellphone" => $this->clean($reservation->cellphone),
		// 			"whatsapp_number" => $this->clean($reservation->whatsapp_number),
		// 			"location_pickup_id" => $reservation->location_pickup_id,
		// 			"child_diet" => $this->clean($reservation->child_diet),
		// 			"child_medication" => $this->clean($reservation->child_medication),
		// 			"about_child" => $this->clean($reservation->about_child),
		// 			"date_added" => $reservation->date_added,
		// 			"can_drive" => $reservation->can_drive,
		// 			"have_driving_license" => $reservation->have_driving_license,
		// 			"have_creditcard" => $reservation->have_creditcard,
		// 			"trip_fee" => $reservation->trip_fee,
		// 			"total_amount" => $reservation->total_amount,
		// 			"paid_amount" => $reservation->paid_amount,
		// 			"deleted" => $reservation->deleted,
		// 			"payment_reminder_email_sent" => $reservation->payment_reminder_email_sent,
		// 			"email_sent" => $reservation->email_sent,
		// 			"login_reminder_email_sent" => $reservation->login_reminder_email_sent,
		// 			"upsell_email_sent" => $reservation->upsell_email_sent,
		// 			"deposit_reminder_email_sent" => $reservation->deposit_reminder_email_sent,
		// 			"display_name" => $this->clean($reservation->display_name),
		// 			"additional_address" => $this->clean($reservation->additional_address),
		// 			"contact_person_name" => $this->clean($reservation->contact_person_name),
		// 			"contact_person_extra_name" => $this->clean($reservation->contact_person_extra_name),
		// 			"contact_person_extra_cellphone" => $this->clean($reservation->contact_person_extra_cellphone),
		// 			"reservation_fees" => $reservation->reservation_fees,
		// 			"reservation_fees_paid_at" => $reservation->reservation_fees_paid_at,
		// 			"reservation_fees_payment_type" => $reservation->reservation_fees_payment_type,
		// 			"expiry_date" => $reservation->expiry_date,
		// 			"status" => $reservation->status,
		// 			"created_at" => $reservation->ts,
		// 			"updated_at" => $reservation->ts,
		// 		]);
		// 	}
		// });
    }
}
