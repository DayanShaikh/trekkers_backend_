<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\ConfigVariable;
use Illuminate\Support\Facades\Storage;
use App\Utility;

class ConfigVariableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $backup = DB::connection('mysql_backup');
        $records = $backup->table('config_variables')->select('*')->get();
            ConfigVariable::create([
                "id" => 305,
                "config_page_id" => 2,
                "input_type" => 0,
                "name" => "Partner Subject",
                "notes" => "",
                "options" => "",
                "config_key" => "partner_email_subject",
                "value" => "New tour date added",
                "creater_id" => 1
            ]);
            ConfigVariable::create([
                "id" => 306,
                "config_page_id" => 2,
                "input_type" => 4,
                "name" => "Partner Body",
                "notes" => "",
                "options" => "",
                "config_key" => "partner_email_body",
                "value" => "",
                "creater_id" => 1
            ]);
        // ConfigVariable::truncate();
        // foreach($records as $record){
        //     $type = array_search(strtolower($record->type), array_map("strtolower", Utility::$input_types));
        //     ConfigVariable::create([
        //         "id" => $record->id,
        //         "config_page_id" => $record->typeid,
        //         "input_type" => $type ? $type : 0,
        //         "name" => $record->title,
        //         "notes" => $record->notes,
        //         "options" => $record->default_values,
        //         "config_key" => $record->key,
        //         "value" => $record->value,
        //         "creater_id" => 1
        //     ]);
        // }

        // $configImages = ConfigVariable::where("input_type", 5)->where("value", "not like", "public/%")->get();
        // foreach($configImages as $configImage){
        //     if(strpos($configImage->value, 'public/')===false){
        //         $url = "https://www.simi-reizen.nl/uploads/config/".$configImage->value;
        //         if($configImage->value!==""){
        //             $contents = file_get_contents($url);
        //             $destination = "public/config/".$configImage->value;
        //             Storage::put($destination, $contents);
        //             $configImage->value = $destination;
        //         }
        //     }
        //     $configImage->save();
        // }
    }
}
