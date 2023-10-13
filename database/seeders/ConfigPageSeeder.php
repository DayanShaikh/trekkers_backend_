<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\ConfigPage;

class ConfigPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $backup = DB::connection('mysql_backup');
        $records = $backup->table('config_types')->select('*')->get();
        ConfigPage::truncate();
        foreach($records as $record){
            ConfigPage::create([
                "id" => $record->id,
                "title" => $record->title,
                "sortorder" => $record->sortorder,
                "creater_id" => 1
            ]);
        }
    }
}
