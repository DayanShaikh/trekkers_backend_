<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\ForumTopic;

class ForumTopicSeeder extends Seeder
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
        $records = $backup->table('forum_topics')->select('*')->get();
        ForumTopic::truncate();
        foreach($records as $record){
            ForumTopic::create([
                "id" => $record->id,
                "user_id" => $record->client_id,
                "forum_category_id" => $record->forum_category_id,
                "title" => $record->title,
                "content" => $record->content,
                "announcement" => $record->announcement,
                "view" => $record->view,
                "status" => $record->status,
                "created_at" => $record->ts,
            ]);
        }

        
        $favorites = $backup->table('forum_topics_favorites')->select('*')->get();
        $conn->table('forum_topic_favorites')->truncate();
        foreach($favorites as $favorite){
            $conn->insert('insert into forum_topic_favorites values(?,?)', collect($favorite)->values()->toArray());
        }

        $reads = $backup->table('forum_topics_read')->select('*')->get();
        $conn->table('forum_topic_reads')->truncate();
        foreach($reads as $read){
            $conn->insert('insert into forum_topic_reads values(?,?)', collect($read)->values()->toArray());
        }
    }
}
