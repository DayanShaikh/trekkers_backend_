<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\PageDag;
use Illuminate\Support\Facades\Storage;

class PageDagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $backup = DB::connection('mysql_backup');
        $records = $backup->table('page_dag')->select('*')->get();
        PageDag::truncate();
        foreach($records as $record){
            PageDag::create([
                "id" => $record->id,
                "page_id" => $record->pages_id,
                "title" => $record->title,
                "image" => $record->image,
                "description" => $record->description,
                "sortorder" => $record->sortorder,
                "status" => $record->status,
                "created_at" => $record->ts,
            ]);
        }

        $images = PageDag::where("image", "not like", "public/%")->get();
        foreach($images as $image){
            if(strpos($image->image, 'public/')===false){
                $url = "https://www.simi-reizen.nl/uploads/page_images/dag/".$image->image;
                if($image->image!=="") {
                    $contents = file_get_contents($url);
                    $destination = "public/page_images/dag/" . $image->image;
                    Storage::put($destination, $contents);
                    $image->image = $destination;
                }
            }
            $image->save();
        }
    }
}
