<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\LocationAddon;

class LocationAddonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $backup = DB::connection('mysql_backup');
        $records = $backup->table('trip_addon')->select('*')->get();
        LocationAddon::truncate();
        foreach($records as $record){
            LocationAddon::create([
                "id" => $record->id,
                "location_id" => $record->location_id,
                "title" => $record->title,
                "image" => $record->image,
                "mobile_image" => $record->mobile_image,
                "description" => $record->description,
                "price" => $record->price,
                "is_public" => $record->is_public,
                "hide_payment" => $record->hide_payment,
                "sortorder" => $record->sortorder,
                "extra_field_1" => $record->extra_field_1,
                "extra_field_2" => $record->extra_field_2,
                "extra_field_3" => $record->extra_field_3,
                "status" => $record->status,
                "created_at" => $record->ts,
            ]);
        }

        $images = LocationAddon::where("image", "not like", "public/%")->get();
        foreach($images as $image){
            if(strpos($image->image, 'public/')===false){
                $url = "https://www.simi-reizen.nl/uploads/trip_addon/".$image->image;
                if($image->image!==""){
                    $contents = file_get_contents($url);
                    $destination = "public/location_addon/image/".$image->image;
                    Storage::put($destination, $contents);
                    $image->image = $destination;
                }
            }
            $image->save();
        }
    }
}
