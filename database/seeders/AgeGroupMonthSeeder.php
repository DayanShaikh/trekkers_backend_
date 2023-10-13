<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AgeGroupMonthMeta;
use Illuminate\Support\Facades\DB;

class AgeGroupMonthSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $backup = DB::connection('mysql_backup');
        $records = $backup->table('age_group_months_meta')->select('*')->get();
        AgeGroupMonthMeta::truncate();
        foreach($records as $record){
            AgeGroupMonthMeta::create([
                "id" => $record->id,
                "age_group_id" => $record->age_group_id,
                "month" => $record->month,
                "text" => $record->text,
                "meta_title" => $record->meta_title,
                "meta_description" => $record->meta_description,
                "meta_keywords" => $record->meta_keywords,
                "creater_id" => 1,
            ]);
        }
    }
}
