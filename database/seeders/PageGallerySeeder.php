<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\PageGallery;

class PageGallerySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $backup = DB::connection('mysql_backup');
        $records = $backup->table('page_gallery')->select('*')->get();
        PageGallery::truncate();
        foreach($records as $record){
            PageGallery::create([
                "id" => $record->id,
                "page_id" => $record->pages_id,
                "title" => $record->title,
                "image" => $record->image,
                "sortorder" => $record->sortorder,
                "status" => $record->status,
                "created_at" => $record->ts,
            ]);
        }

        $pageGalleries = PageGallery::where("image", "not like", "public/%")->get();
        foreach($pageGalleries as $pageGallery){
            if(strpos($pageGallery->image, 'public/')===false){
                $url = "https://www.simi-reizen.nl/uploads/page_images/gallery/".$pageGallery->image;
                if($pageGallery->image!=="") {
                    $contents = file_get_contents($url);
                    $destination = "public/page_images/gallery/" . $pageGallery->image;
                    Storage::put($destination, $contents);
                    $pageGallery->image = $destination;
                }
            }
            $pageGallery->save();
        }
    }
}
