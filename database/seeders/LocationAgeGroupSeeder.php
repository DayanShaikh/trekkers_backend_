<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\LocationAgeGroup;
use Illuminate\Support\Facades\Storage;

class LocationAgeGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $backup = DB::connection('mysql_backup');
        $records = $backup->table('location_2_age_group')->select('*')->get();
        LocationAgeGroup::truncate();
        foreach($records as $record){
            LocationAgeGroup::create([
                "id" => $record->id,
                "location_id" => $record->location_id,
                "age_group_id" => $record->age_group_id,
                "trip_level" => $record->trip_level,
                "description" => $record->description,
                "image" => $record->header_image,
                "included" => $record->included,
                "travel_information" => $record->travel_information,
                "program_details" => $record->program_details,
                "packing_list" => $record->packing_list,
                "faqs" => $record->faqs,
                "faqs_new" => $record->faqs_new,
                "reviews" => $record->reviews,
                "review_text" => $record->review_text,
                "title" => $record->listing_title,
                "listing_title" => $record->listing_title,
                "listing_text" => $record->listing_text,
                "listing_image" => $record->listing_image,
                // "meta_title" => $record->meta_title,
                // "meta_description" => $record->meta_description,
                // "meta_keywords" => $record->meta_keywords,
                // "sitemap_title" => $record->sitemap_title,
                // "sitemap_details" => $record->sitemap_details,
                // "page_id" => $record->page_id,
                "excursions" => $record->excursions,
                "combination" => $record->combination,
                "flight" => $record->flight,
                "meals" => $record->meals,
                "min_people" => $record->min_people,
                "baggage" => $record->baggage,
                "status" => $record->status,
                "created_at" => $record->ts,
            ]);
        }

        $locationAgeGroups = LocationAgeGroup::where("listing_image", "not like", "public/%")->get();
        foreach($locationAgeGroups as $locationAgeGroup){
            if(strpos($locationAgeGroup->listing_image, 'public/')===false){
                $url = "https://www.simi-reizen.nl/uploads/location_2_age_group_images/listing_images/".$locationAgeGroup->listing_image;
                if($locationAgeGroup->listing_image!==""){
                    $contents = file_get_contents($url);
                    $destination = "public/location_age_group/listing_image/".$locationAgeGroup->listing_image;
                    Storage::put($destination, $contents);
                    $locationAgeGroup->listing_image = $destination;
                }
            }
            // if(strpos($locationAgeGroup->header_image, 'public/')===false){
            //     $url = "https://www.simi-reizen.nl/uploads/location_2_age_group_images/".$locationAgeGroup->header_image;
            //     if($locationAgeGroup->header_image!=="") {
            //         $contents = file_get_contents($url);
            //         $destination = "public/location_age_group/header_image/" . $locationAgeGroup->header_image;
            //         Storage::put($destination, $contents);
            //         $locationAgeGroup->header_image = $destination;
            //     }
            // }
            $locationAgeGroup->save();
        }
    }
}
