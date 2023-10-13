<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\FrontMenu;

class FrontMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $backup = DB::connection('mysql_backup');
        $records = $backup->table('frontmenus')->select('*')->get();
        FrontMenu::truncate();
        foreach($records as $record){
            FrontMenu::create([
                "id" => $record->id,
                "position" => $record->position == "top"? 0: ($record->position == "bottom"? 1: 2),
                "title" => $record->title,
                "url" => $record->url,
                "parent_id" => $record->parentid,
                "sortorder" => $record->sortorder,
                "is_default" => $record->isdefault,
                "creater_id" => 1,
                "status" => $record->status,
                "created_at" => $record->ts,
            ]);
        }
        $frontMenus = FrontMenu::get();
        foreach($frontMenus as $frontMenu){
            if(!empty($frontMenu->url)){
                $url =  str_replace('https://www.simi-reizen.nl/', '', $frontMenu->url);
                $frontMenu->url = $url;
                $frontMenu->save();
            }

        }
    }
}
