<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\AgeGroup;
use Illuminate\Support\Facades\Storage;

class AgeGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $backup = DB::connection('mysql_backup');

        $ageGroups = $backup->table('age_group')->select('*')->get();
        AgeGroup::truncate();
        foreach($ageGroups as $ageGroup){
            AgeGroup::create([
                "id" => $ageGroup->id,
                "title" => $ageGroup->title,
                "short_title" => $ageGroup->short_title,
                // "seo_url" => $ageGroup->seo_url,
                "tour_guide_points" => $ageGroup->tour_guide_points,
                "quality_price_points" => $ageGroup->quality_price_points,
                "activities_points" => $ageGroup->activities_points,
                "total_reviews" => $ageGroup->total_reviews,
                //"header_image" => $ageGroup->header_image,
                // "meta_title" => $ageGroup->meta_title,
                // "meta_description" => $ageGroup->meta_description,
                // "meta_keywords" => $ageGroup->meta_keywords,
                "extra_links" => $ageGroup->extra_links,
                // "details" => $ageGroup->details,
                "sortorder" => $ageGroup->sortorder,
                "creater_id" => 1,
                "status" => $ageGroup->status,
                "created_at" => $ageGroup->ts,
            ]);
        }

        // $images = AgeGroup::where("header_image", "not like", "public/%")->get();
        // foreach($images as $image){
        //     if(strpos($image->header_image, 'public/')===false){
        //         $url = "https://www.simi-reizen.nl/uploads/age_group/header_images/".$image->header_image;
        //         if($image->header_image!==""){
        //             $contents = file_get_contents($url);
        //             $destination = "public/age_group/header_images/".$image->header_image;
        //             Storage::put($destination, $contents);
        //             $image->header_image = $destination;
        //         }
        //     }
        //     $image->save();
        // }
    }
}
