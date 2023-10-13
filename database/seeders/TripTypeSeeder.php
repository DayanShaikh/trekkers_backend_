<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\TripType;

class TripTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $backup = DB::connection('mysql_backup');
        $records = $backup->table('trip_type')->select('*')->get();
        TripType::truncate();
        foreach($records as $record){
            TripType::create([
                "id" => $record->id,
                "title" => $record->title,
                "image" => $record->image,
                "show_on_homepage" => $record->show_on_homepage,
                "sortorder" => $record->sortorder,
                "description" => $record->description,
                "status" => $record->status,
                "created_at" => $record->ts,
            ]);
        }

        $tripTypes = TripType::where("image", "not like", "public/%")->get();
        foreach($tripTypes as $tripType){
            if(strpos($tripType->image, 'public/')===false){
                $url = "https://www.simi-reizen.nl/uploads/trip_type/".$tripType->image;
                if($tripType->image!==""){
                    $contents = file_get_contents($url);
                    $destination = "public/trip_types/".$tripType->image;
                    Storage::put($destination, $contents);
                    $tripType->image = $destination;
                }
            }
            $tripType->save();
        }
    }
}
