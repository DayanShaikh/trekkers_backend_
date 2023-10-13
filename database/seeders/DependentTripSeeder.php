<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\DependentTrip;
use App\Models\DependentTripDocument;
use App\Models\DependentTripTicket;
use App\Models\DependentTripTicketUser;

class DependentTripSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $backup = DB::connection('mysql_backup');
        $records = $backup->table('dependent_trips')->select('*')->get();
        DependentTrip::truncate();
        foreach($records as $record){
            DependentTrip::create([
                "id" => $record->id,
                "date" => $record->date,
                "location_id" => $record->location_id,
                "total_space" => $record->total_space,
                "male_female_important" => $record->male_female_important,
                "male_space" => $record->male_space,
                "female_space" => $record->female_space,
                "tour_leader" => $record->tour_leader,
                "is_grouped" => $record->is_grouped,
                "show_client_detail" => $record->show_client_detail,
                "creater_id" => 1,
                "status" => $record->status,
                "created_at" => $record->ts,
            ]);
        }

        $documents = $backup->table('dependent_trips_document')->select('*')->get();
        DependentTripDocument::truncate();
        foreach($documents as $document){
            DependentTripDocument::create([
                "id" => $document->id,
                "dependent_trip_id" => $document->dependent_trip_id,
                "title" => $document->title,
                "document_url" => $document->document_url,
                "sortorder" => $document->sortorder,
                "status" => $document->status,
                "created_at" => $document->ts,
            ]);
        }

        $tickets = $backup->table('trip_ticket')->select('*')->get();
        DependentTripTicket::truncate();
        foreach($tickets as $ticket){
            DependentTripTicket::create([
                "id" => $ticket->id,
                "dependent_trip_id" => $ticket->dependent_trip_id,
                "airline_id" => $ticket->airline_id,
                "connecting_flight" => $ticket->connecting_flight,
                "type" => $ticket->type,
                "datum" => $ticket->datum,
                "vluchtnummer" => $ticket->vluchtnummer,
                "van" => $ticket->van,
                "naar" => $ticket->naar,
                "vertrek" => $ticket->vertrek,
                "ankomst" => $ticket->ankomst,
                "ankomst" => $ticket->ankomst,
                "sortorder" => $ticket->sortorder,
            ]);
        }

        $ticketUsers = $backup->table('trip_ticket_users')->select('*')->get();
        DependentTripTicketUser::truncate();
        foreach($ticketUsers as $ticketUser){
            DependentTripTicketUser::create([
                "id" => $ticketUser->id,
                "dependent_trip_ticket_id" => $ticketUser->trip_ticket_id,
                "trip_booking_id" => $ticketUser->booking_id,
                "ticket_number" => $ticketUser->ticket_number,
                "notes" => $ticketUser->notes,
                "status" => $ticketUser->status,
                "created_at" => $ticketUser->ts,
            ]);
        }
    }
}
