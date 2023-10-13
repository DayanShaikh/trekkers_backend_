<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\ForumReply;

class ForumReplySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $backup = DB::connection('mysql_backup');
        $records = $backup->table('forum_replies')->select('*')->get();
        ForumReply::truncate();
        foreach($records as $record){
            ForumReply::create([
                "id" => $record->id,
                "forum_topic_id" => $record->forum_topic_id,
                "content" => $record->content,
                "datetime_added" => $record->datetime_added,
                "status" => $record->status,
                "created_at" => $record->ts,
            ]);
        }
    }
}
