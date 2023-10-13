<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Airline;

class AirlineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $backup = DB::connection('mysql_backup');
        $records = $backup->table('airline')->select('*')->get();
        Airline::truncate();
        foreach($records as $record){
            Airline::create([
                "id" => $record->id,
                "title" => $record->title,
                "details" => $record->details,
                "status" => $record->status,
                "created_at" => $record->ts,
            ]);
        }
    }
}
