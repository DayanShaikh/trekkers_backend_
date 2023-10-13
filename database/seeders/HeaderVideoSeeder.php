<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\HeaderVideo;
use Illuminate\Support\Facades\Storage;

class HeaderVideoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $backup = DB::connection('mysql_backup');
        $records = $backup->table('header_video')->select('*')->get();
        HeaderVideo::truncate();
        foreach($records as $record){
            HeaderVideo::create([
                "id" => $record->id,
                "title" => $record->title,
                "webm_format" => $record->webm_format,
                "mp4_format" => $record->mp4_format,
                "creater_id" => 1,
                "status" => $record->status,
                "created_at" => $record->ts,
            ]);
        }

        $headerVideos = headerVideo::where("webm_format", "not like", "public/%")->orWhere("mp4_format", "not like", "public/%")->get();
        foreach($headerVideos as $headerVideo){
            if(strpos($headerVideo->webm_format, 'public/')===false){
                $url = "https://www.simi-reizen.nl/uploads/header_video/webm/".$headerVideo->webm_format;
                if($headerVideo->webm_format!=="") {
                    $contents = file_get_contents($url);
                    $destination = "public/header_videos/webm_format/" . $headerVideo->webm_format;
                    Storage::put($destination, $contents);
                    $headerVideo->webm_format = $destination;
                }
            }
            if(strpos($headerVideo->mp4_format, 'public/')===false){
                $url = "https://www.simi-reizen.nl/uploads/header_video/mp4/".$headerVideo->mp4_format;
                if($headerVideo->mp4_format!=="") {
                    $contents = file_get_contents($url);
                    $destination = "public/header_videos/mp4_format/" . $headerVideo->mp4_format;
                    Storage::put($destination, $contents);
                    $headerVideo->mp4_format = $destination;
                }
            }
            $headerVideo->save();
        }
    }
}
