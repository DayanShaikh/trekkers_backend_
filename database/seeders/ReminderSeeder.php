<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Reminder;
use App\Models\Trip;

class ReminderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tripCount = 3000;
        $backup = DB::connection('mysql_backup');
        //$records = $backup->table('reminder')->select('*')->get();
        $dependantTrips = [];
        $dependant_trips = $backup->table('dependent_trips')->select('*')->get();
        Reminder::truncate();
        $backup->table('reminder')->select(DB::raw('reminder.*, trips.location_id, trips.start_date'))->join("trips", "reminder.trip_id", "=", "trips.id")->orderBy("start_date")->chunk(500, function ($reminders) use(&$dependantTrips, &$tripCount) {
            foreach ($reminders as $reminder) {
                if(!isset($dependantTrips[$reminder->location_id.$reminder->start_date])){
					$reminder->trip_id = $tripCount;
					$tripCount++;
					echo $reminder->trip_id." = Trip = ".$reminder->location_id.PHP_EOL;
					$dependantTrips[$reminder->location_id.$reminder->start_date] = Trip::create([
						"id" => $reminder->trip_id,
						"location_id" => $reminder->location_id,
						"total_space" => 30,
						"male_female_important" => false,
						"show_client_detail" => false,
						"start_date" => $reminder->start_date,
						"duration" => 10,
						"trip_fee" => 0,
						"trip_seats_status" => false,
						"is_not_bookable" => false,
						"archive" => true,
						"is_full" => true,
						"status" => false,
						"creater_id" => 1
					]);
				}
				else{
					echo $reminder->trip_id." = DTrip = ".($dependantTrips[$reminder->location_id.$reminder->start_date]->id).PHP_EOL;
				}
                Reminder::create([
                    "trip_id" => $dependantTrips[$reminder->location_id.$reminder->start_date]->id,
                    "email" => $reminder->email,
                    "created_at" => $reminder->ts,
                    "updated_at" => $reminder->ts,
                ]);
            }
        });
    }
}
