<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Attribute;
use Illuminate\Support\Facades\Storage;

class AttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $backup = DB::connection('mysql_backup');
        $records = $backup->table('attributes')->select('*')->get();
        Attribute::truncate();
        foreach($records as $record){
            Attribute::create([
                "id" => $record->id,
                "title" => $record->title,
                "image" => $record->image,
                "linked_id" => $record->linked_id,
                "main_filter" => $record->main_filter,
                "status" => $record->status,
                "creater_id" => 1,
                "created_at" => $record->ts,
            ]);
        }

        $images = Attribute::where("image", "not like", "public/%")->get();
        foreach($images as $image){
            if(strpos($image->image, 'public/')===false){
                $url = "https://www.simi-reizen.nl/uploads/attributes/".$image->image;
                if($image->image!==""){
                    $contents = file_get_contents($url);
                    $destination = "public/attribute/images/".$image->image;
                    Storage::put($destination, $contents);
                    $image->image = $destination;
                }
            }
            $image->save();
        }
    }
}
