<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Blog;
use Illuminate\Support\Facades\Storage;

class BlogSeeder extends Seeder
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
        $records = $backup->table('blog')->select('*')->get();
        Blog::truncate();
        foreach($records as $record){
            Blog::create([
                "id" => $record->id,
                "title" => $record->title,
                "seo_url" => $record->seo_url,
                "excerpt" => $record->excerpt,
                "content" => $record->content,
                "image" => $record->image,
                "date" => $record->date,
                "meta_title" => $record->meta_title,
                "meta_description" => $record->meta_description,
                "meta_keywords" => $record->meta_keywords,
                "related_post" => $record->related_post,
                "status" => $record->status,
                "created_at" => $record->ts,
            ]);
        }
        $conn->table('blog_blog_category')->truncate();
        $blogtoCats = $backup->table('blog_blog_category')->select('*')->get();
        foreach($blogtoCats as $blogtoCat){
            $conn->insert('insert into blog_blog_category values(?,?)', collect($blogtoCat)->values()->toArray());
        }

        $images = Blog::where("image", "not like", "public/%")->get();
        foreach($images as $image){
            if(strpos($image->image, 'public/')===false){
                $url = "https://www.simi-reizen.nl/uploads/blog/".$image->image;
                if($image->image!==""){
                    $contents = file_get_contents($url);
                    $destination = "public/blog/images/".$image->image;
                    Storage::put($destination, $contents);
                    $image->image = $destination;
                }
            }
            $image->save();
        }
    }
}
