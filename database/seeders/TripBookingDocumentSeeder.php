<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\TripBookingDocument;

class TripBookingDocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $backup = DB::connection('mysql_backup');
        $records = $backup->table('trip_booking_document')->select('*')->get();
        TripBookingDocument::truncate();
        foreach($records as $record){
            TripBookingDocument::create([
                "id" => $record->id,
                "trip_booking_id" => $record->trip_booking_id,
                "title" => $record->title,
                "document_url" => $record->document_url,
                "sortorder" => $record->sortorder,
                "status" => $record->status,
                "created_at" => $record->ts,
            ]);
        }
    }
}
