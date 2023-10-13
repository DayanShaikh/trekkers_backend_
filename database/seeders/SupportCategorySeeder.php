<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\SupportCategory;

class SupportCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $backup = DB::connection('mysql_backup');
        $records = $backup->table('support_category')->select('*')->get();
        SupportCategory::truncate();
        foreach($records as $record){
            SupportCategory::create([
                "id" => $record->id,
                "title" => $record->title,
                "icon" => $record->icon,
                "sortorder" => $record->sortorder,
                "status" => $record->status,
                "created_at" => $record->ts,
            ]);
        }
    }
}
