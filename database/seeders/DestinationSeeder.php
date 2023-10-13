<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Destination;
use App\Models\Page;

class DestinationSeeder extends Seeder
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
		$conn = DB::connection(config('database.default'));
		//$destinations = $backup->table('location_country')->select(DB::raw('location_country.*, trip_country.travel_insurance_fees, trip_country.is_survival_adventure_insurance_active'))->leftJoin("location", "location.location_country_id", "=", "location_country.id")->leftJoin("trip_country", "location.country_id", "=", "trip_country.id")->groupBy("location_country.id")->orderBy("location_country.id")->get();
		$i = 1;
		$destinations = Destination::with('page')->get();
		DB::table('destination_trip')->truncate();
		foreach($destinations as $destination){
			$page = Page::where('pageable_id', $destination->id)->get();
			if($page){
				$destinationTrips = $backup->table('page_country_trips')->where(['type' => 0, 'page_id' => $page->id])->select('*')->get();
				if($destinationTrips){
					foreach($destinationTrips as $destinationTrip){
						var_dump($destinationTrip);
						DB::table('destination_trip')->insert([
							'destination_id' => $destination->id,
							'trip_id' => $destinationTrip->trips_id,
							'type' => 0,
						]);
					}
					
				}
			}
		
		}
    }
}
