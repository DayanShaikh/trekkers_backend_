<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\TripBookingAddon;

class TripBookingAddonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $backup = DB::connection('mysql_backup');
        $records = $backup->table('trip_booking_addon')->select('*')->get();
        TripBookingAddon::truncate();
        foreach($records as $record){
            TripBookingAddon::create([
                "id" => $record->id,
                "trip_booking_id" => $record->booking_id,
                "location_addon_id" => $record->trip_addon_id,
                "booking_date" => $record->booking_date,
                "amount" => $record->amount,
                "amount_paid" => $record->amount_paid,
                "payment_date" => $record->payment_date,
                "processed" => $record->processed,
                "notes" => $record->notes,
                "extra_field_1" => $record->extra_field_1,
                "extra_field_2" => $record->extra_field_2,
                "extra_field_3" => $record->extra_field_3,
                "status" => $record->status,
                "created_at" => $record->ts,
            ]);
        }
    }
}
