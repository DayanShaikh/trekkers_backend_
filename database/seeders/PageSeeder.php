<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Page;
use Illuminate\Support\Facades\Storage;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $backup = DB::connection('mysql_backup');
        $records = $backup->table('age_group')->select('*')->get();
        Page::truncate();
        foreach($records as $record){
            Page::create([
                "pageable_type" => 'App\Models\AgeGroup',
                "pageable_id" => $record->id,
                "page_name" => $record->seo_url,
                "title" => $record->title,
                "image" => $record->header_image,
                "content" => $record->details,
                "meta_title" => $record->meta_title,
                "meta_description" => $record->meta_description,
                "meta_keywords" => $record->meta_keywords,
                "creater_id" => 1,
                "status" => $record->status,
                "created_at" => $record->ts,
            ]);
        }

        $pages = Page::where("image", "not like", "public/%")->get();
        foreach($pages as $page){
            if(strpos($page->image, 'public/')===false){
                $url = "https://www.simi-reizen.nl/uploads/age_group/header_images/".$page->image;
                if($page->image!=="") {
                    $contents = file_get_contents($url);
                    $destination = "public/page_images/" . $page->image;
                    Storage::put($destination, $contents);
                    $page->image = $destination;
                }
            }
            $page->save();
        }
    }
}
