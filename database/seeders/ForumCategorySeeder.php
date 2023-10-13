<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\ForumCategory;

class ForumCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $backup = DB::connection('mysql_backup');
        $records = $backup->table('forum_categories')->select('*')->get();
        ForumCategory::truncate();
        foreach($records as $record){
            ForumCategory::create([
                "id" => $record->id,
                "title" => $record->title,
                "status" => $record->status,
                "created_at" => $record->ts,
            ]);
        }
    }
}
