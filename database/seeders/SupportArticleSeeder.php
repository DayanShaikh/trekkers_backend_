<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\SupportArticle;

class SupportArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $backup = DB::connection('mysql_backup');
        $records = $backup->table('support_article')->select('*')->get();
        SupportArticle::truncate();
        foreach($records as $record){
            SupportArticle::create([
                "id" => $record->id,
                "support_category_id" => $record->support_category_id,
                "title" => $record->title,
                "excerpt" => $record->excerpt,
                "date" => $record->date,
                "time" => $record->time,
                "sortorder" => $record->sortorder,
                "status" => $record->status,
                "created_at" => $record->ts,
            ]);
        }
    }
}
