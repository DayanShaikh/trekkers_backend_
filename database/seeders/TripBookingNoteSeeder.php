<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\TripBookingNote;

class TripBookingNoteSeeder extends Seeder
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
        $notes = $backup->table('trip_booking_notes')->select('*')->get();
        TripBookingNote::truncate();
		foreach($notes as $note){
			TripBookingNote::create([
				"trip_booking_id" => $note->trip_booking_id,
				"notes" => $this->clean($note->notes),
				"status" => $note->status,
				"creater_id" => 1,
				"created_at" => $note->ts,
				"updated_at" => $note->ts,
			]);
		}
    }
}
