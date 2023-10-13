<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\TripBookingPayment;

class TripBookingPaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $backup = DB::connection('mysql_backup');
        $records = $backup->table('trip_booking_payments')->select('*')->get();
        TripBookingPayment::truncate();
        foreach($records as $record){
            TripBookingPayment::create([
                "id" => $record->id,
                "trip_booking_id" => $record->trip_booking_id,
                "payment_type" => $record->payment_type,
                "payment_date" => $record->payment_date,
                "amount" => $record->amount,
                "transaction_reference" => $record->transaction_reference,
                "details" => $record->details,
                "status" => $record->status,
                "created_at" => $record->ts,
            ]);
        }
    }
}
