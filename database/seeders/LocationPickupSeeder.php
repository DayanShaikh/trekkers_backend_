<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\LocationPickup;

class LocationPickupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $backup = DB::connection('mysql_backup');
        $records = $backup->table('location_pickup')->select('*')->get();
        LocationPickup::truncate();
        foreach($records as $record){
            LocationPickup::create([
                "id" => $record->id,
                "location_id" => $record->location_id,
                "place" => $record->place,
                "time" => $record->time,
                "spot" => $record->spot,
                "sortorder" => $record->sortorder,
                "status" => $record->status,
                "created_at" => $record->ts,
            ]);
        }
    }
}
