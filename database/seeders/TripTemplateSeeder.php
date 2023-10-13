<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\TripTemplate;

class TripTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $backup = DB::connection('mysql_backup');
        $records = $backup->table('trip_templates')->select('*')->get();
        TripTemplate::truncate();
        foreach($records as $record){
            TripTemplate::create([
                "id" => $record->id,
                "location_id" => $record->location_id,
                "name" => $record->name,
                "content" => $record->content,
                "creater_id" => 1,
                "status" => $record->status,
                "created_at" => $record->ts,
            ]);
        }
    }
}
