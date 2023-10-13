<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\PageCountry;
use Illuminate\Support\Facades\Storage;

class PageCountrySeeder extends Seeder
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
        $records = $backup->table('page_country')->select('*')->get();
        PageCountry::truncate();
        foreach($records as $record){
            PageCountry::create([
                "id" => $record->id,
                "page_id" => $record->page_id,
                "intro_title" => $record->intro_title,
                "intro_text" => $record->intro_text,
                "intro_video" => $record->intro_video,
                "video_text" => $record->video_text,
                "header_video_id" => $record->header_video_id,
                "trip_title" => $record->trips_title,
                "other_trip_title" => $record->other_trips_title,
                "trip_toggle" => $record->trip_toggle,
                "thumb_image" => $record->thumb_image,
            ]);
        }

        $types = $backup->table('page_country_trips')->select('*')->get();
        $conn->table('page_country_trips')->truncate();
        foreach($types as $type){
            $conn->insert('insert into page_country_trips values(?,?,?)', collect($type)->values()->toArray());
        }

        $pageCountries = PageCountry::where("thumb_image", "not like", "public/%")->get();
        foreach($pageCountries as $pageCountry){
            if(strpos($pageCountry->thumb_image, 'public/')===false){
                $url = "https://www.simi-reizen.nl/uploads/country_thumb/".$pageCountry->thumb_image;
                if($pageCountry->thumb_image!==""){
                    $contents = file_get_contents($url);
                    $destination = "public/page_country/".$pageCountry->thumb_image;
                    Storage::put($destination, $contents);
                    $pageCountry->thumb_image = $destination;
                }
            }
            $pageCountry->save();
        }
    }
}
