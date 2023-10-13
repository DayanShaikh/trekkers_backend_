<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Trip;

class TripSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $backup = DB::connection('mysql_backup');
        $conn = DB::connection('mysql');
        $records = $backup->table('trips')->select('*')->get();
        Trip::truncate();
        foreach($records as $record){
            Trip::create([
                "id" => $record->id,
                "location_id" => $record->location_id,
                "start_date" => $record->start_date,
                "duration" => $record->duration,
                "trip_fee" => $record->trip_fee,
                "trip_discount" => $record->trip_discount,
                "original_fee" => $record->original_fee,
                "trip_seats_status" => $record->trip_seats_status,
                "is_not_bookable" => $record->is_not_bookable,
                "archive" => $record->archive,
                "is_full" => $record->is_full,
                "status" => $record->status,
                "created_at" => $record->ts,
            ]);
        }
    }
}
