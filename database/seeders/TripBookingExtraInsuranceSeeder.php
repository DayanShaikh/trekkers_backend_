<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\TripBookingExtraInsurance;

class TripBookingExtraInsuranceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $backup = DB::connection('mysql_backup');
        $records = $backup->table('trip_booking_extra_insurance')->select('*')->get();
        TripBookingExtraInsurance::truncate();
        foreach($records as $record){
			if($record->payment_date=="0000-00-00"){
                 $record->payment_date = Null;
            }
            TripBookingExtraInsurance::create([
                "id" => $record->id,
                "trip_booking_id" => $record->booking_id,
                "date" => $record->date,
                "insurance" => $record->insurance,
                "survival_adventure_insurance" => $record->survival_adventure_insurance,
                "travel_insurance" => $record->travel_insurance,
                "insurance_admin_charges" => $record->insurance_admin_charges,
                "is_completed" => $record->is_completed,
                "payment_date" => $record->payment_date,
                "status" => 1,
                "created_at" => $record->ts,
            ]);
        }
    }
}
