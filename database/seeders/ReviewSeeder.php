<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Review;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $backup = DB::connection('mysql_backup');
        $records = $backup->table('review')->select('*')->get();
        Review::truncate();
        foreach($records as $record){
            Review::create([
                "id" => $record->id,
                "trip_booking_id" => $record->booking_id,
                "fake_trip_booking_id" => $record->fake_booking_id,
                "review_date" => $record->review_date,
                "tour_guide_points" => $record->tour_guide_points,
                "quality_price_points" => $record->quality_price_points,
                "activities_points" => $record->activities_points,
                "review_text" => $record->review_text,
                "feedback_text" => $record->feedback_text,
                "review_picture" => $record->review_picture,
                "show_client_details" => $record->show_client_details,
                "status" => $record->status,
                "created_at" => $record->ts,
            ]);
        }
    }
}
