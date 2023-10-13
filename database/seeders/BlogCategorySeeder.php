<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\BlogCategory;

class BlogCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $backup = DB::connection('mysql_backup');
        $records = $backup->table('blog_categories')->select('*')->get();
        BlogCategory::truncate();
        foreach($records as $record){
            BlogCategory::create([
                "id" => $record->id,
                "title" => $record->title,
                "seo_url" => $record->seo_url,
                "meta_title" => $record->meta_title,
                "meta_description" => $record->meta_description,
                "status" => $record->status,
                "created_at" => $record->ts,
            ]);
        }
    }
}
