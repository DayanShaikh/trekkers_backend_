<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Location;

class LocationSeeder extends Seeder
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
        $records = $backup->table('location')->select('*')->get();
        Location::truncate();
        foreach($records as $record){
            Location::create([
                "id" => $record->id,
                "region_id" => $record->country_id,
                "title" => $record->title,
                "travel_time" => $record->travel_time,
                "upsell_email_title" => $record->upsell_email_title,
                "upsell_email_content" => $record->upsell_email_content,
                "upsell_email_title1" => $record->upsell_email_title1,
                "upsell_email_content1" => $record->upsell_email_content1,
                "has_flight" => $record->has_flight,
                "icons" => $record->icons,
                "commission" => $record->commission,
                "commission_type" => $record->commission_type,
                "sortorder" => $record->sortorder,
                "require_passport_details" => $record->require_passport_details,
                "combined_age_group" => $record->combined_age_group,
                "creater_id" => 1,
                "status" => $record->status,
                "created_at" => $record->ts,
            ]);
        }

        $types = $backup->table('location_2_trip_type')->select('*')->get();
        $conn->table('location_trip_type')->truncate();
        foreach($types as $type){
            $conn->insert('insert into location_trip_type values(?,?)', collect($type)->values()->toArray());
        }
    }
}
