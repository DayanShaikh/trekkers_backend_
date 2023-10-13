<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

use App\Models\Role;
use App\Models\TripBooking;
use App\Models\Reservation;
use App\Models\Location;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{

    /**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->authorizeResource(Role::class, 'report');
    }

    public function getBookingNumbers($year){
        if(!empty($year)){
            $start_date = ($year-1)."-10-01";
            $end_date = $year."-09-30 23:59:59";
            $bookings = [
                "totals" => TripBooking::select(
                    DB::raw(
                        "count(*) as total, count(*) FILTER (WHERE gender = false) as male, count(*) FILTER (WHERE gender = true) as female, count(*) FILTER (WHERE returning_user_id is not NULL) as returning_users"
                        )
                    )->leftJoin(
                        DB::raw(
                            "(select distinct(user_id) as returning_user_id from trip_bookings inner join trips on trip_bookings.trip_id = trips.id where trips.start_date <= '".$start_date."' ) AS returning_users"
                        ),
                        'trip_bookings.user_id', '=', 'returning_users.returning_user_id'
                    )
                    ->join("trips", 'trip_bookings.trip_id', '=', 'trips.id')
                    // ->whereBetween("trip_bookings.created_at", [$start_date, $end_date])
                    ->where("deleted", false)
                    ->whereBetween("start_date", [$start_date, $end_date])
                    ->first(),
                "totalReservations" => Reservation::select(
                    DB::raw(
                        "count(*) as total, count(*) FILTER (WHERE gender = false) as male, count(*) FILTER (WHERE gender = true) as female, count(*) FILTER (WHERE returning_user_id is not NULL) as returning_users"
                        )
                    )->leftJoin(
                        DB::raw(
                            "(select distinct(user_id) as returning_user_id from reservations inner join trips on reservations.trip_id = trips.id where trips.start_date <= '".$start_date."' ) AS returning_users"
                        ),
                        'reservations.user_id', '=', 'returning_users.returning_user_id'
                    )
                    ->join("trips", 'reservations.trip_id', '=', 'trips.id')
                    // ->whereBetween("reservations.created_at", [$start_date, $end_date])
                    ->where("deleted", false)
                    ->whereBetween("start_date", [$start_date, $end_date])
                    ->first(),
                "dates" => TripBooking::select(
                    DB::raw(
                        "count(*) as total, count(*) FILTER (WHERE gender = false) as male, count(*) FILTER (WHERE gender = true) as female, count(*) FILTER (WHERE returning_user_id is not NULL) as returning_users, trips.id, trips.start_date, trips.location_id, locations.title"
                        )
                    )->leftJoin(
                        DB::raw(
                            "(select distinct(user_id) as returning_user_id from trip_bookings inner join trips on trip_bookings.trip_id = trips.id where trips.start_date <= '".$start_date."' ) AS returning_users"
                        ),
                        'trip_bookings.user_id', '=', 'returning_users.returning_user_id'
                    )
                    ->join("trips", 'trip_bookings.trip_id', '=', 'trips.id')
                    ->join("locations", 'trips.location_id', '=', 'locations.id')
                    // ->whereBetween("trip_bookings.created_at", [$start_date, $end_date])
                    ->whereBetween("start_date", [$start_date, $end_date])
                    ->where("deleted", false)
                    ->groupBy("trips.id", "locations.title")
                    ->orderBy("start_date")
                    ->get()
            ];
            $i = 0;
            foreach($bookings["dates"] as $date){
                $bookings["dates"][$i]["reservations"] = Reservation::select(
                    DB::raw(
                        "count(*) as total, count(*) FILTER (WHERE gender = false) as male, count(*) FILTER (WHERE gender = true) as female, count(*) FILTER (WHERE returning_user_id is not NULL) as returning_users"
                        )
                    )->leftJoin(
                        DB::raw(
                            "(select distinct(user_id) as returning_user_id from reservations inner join trips on reservations.trip_id = trips.id where trips.start_date <= '".$start_date."' ) AS returning_users"
                        ),
                        'reservations.user_id', '=', 'returning_users.returning_user_id'
                    )
                    ->join("trips", 'reservations.trip_id', '=', 'trips.id')
                    ->join("locations", 'trips.location_id', '=', 'locations.id')
                    ->whereBetween("start_date", [$start_date, $end_date])
                    ->where("deleted", false)
                    ->where("trips.id", $date->id)
                    ->first();
                $i++;
            }
            return $bookings;
        }
    }

    public function getBookingChartData(){
        $lastYear = 0;
        $lastWeek = 1;
        $data = [];
        foreach(TripBooking::select(DB::raw("count(1) as total, cast(concat(DATE_PART('isoyear', created_at), TO_CHAR(DATE_PART('week', created_at), 'fm00')) as integer) as weeknumber"))->groupBy(DB::raw("weeknumber"))->orderBy("weeknumber")->get() as $yearWeek){
            $year = substr($yearWeek->weeknumber, 0, 4);
            $week = (int)substr($yearWeek->weeknumber, 4);
            if($lastYear != $year){
                if($lastYear != 0){
                    for($i = $lastWeek; $i <= 52; $i++){
                        $data[$lastYear]["data"][] = 0;
                    }
                }
                $lastYear = $year;
                $lastWeek = 1;
                $data[$year] = [
                    "name" => $year,
                    "data" => []
                ];
            }
            if( $week - $lastWeek > 1 ){
                //return ($week." > ".$lastWeek)."   ".($week - $lastWeek);
                for( $j = 1; $j < $week - $lastWeek; $j++ ){
                    $data[$year]["data"][] = 0;
                }
            }
            $data[$year]["data"][] = $yearWeek->total;
            $lastWeek = $week;
        }
        for($i = $lastWeek; $i <= 52; $i++){
            $data[$year]["data"][] = 0;
        }
        return array_values($data);
        $firstBooking = TripBooking::oldest()->first();
        $data = [];
        for($i = $firstBooking->created_at->format("Y"); $i <= date("Y"); $i++){
            $yearData = [
                "name" => $i,
                "data" => []
            ];
            $last_week = -1;
            
            if( $last_week < 52 ) {
                for( $j = $last_week+1; $j <= 52; $j++ ){
                    $yearData["data"][] = 0;
                }
            }
            $data[] = $yearData;
        }
        return $data;
    }
    public function downloadReports(Location $location, $year, $date = null){
        // return $date;
		$headers = [
			'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',   
			'Content-type'        => 'text/csv',
			'Content-Disposition' => 'attachment; filename=booking.csv',
			'Expires'             => '0',
			'Pragma'              => 'public'
        ];
        $start_date = ($year-1)."-10-01";
        $end_date = $year."-09-30 23:59:59";
        $list = TripBooking::with('trip', 'trip.location')->whereHas('trip', function($query) use($location, $year, $date){
				$query->where("location_id", $location->id)
                ->whereBetween("start_date", [$year."-01-01", $year."-12-31"]);
                if ($date !== null) {
                    $query->where("start_date", $date);
                }
		})->where(['deleted' => false, 'status' => true])->whereBetween("created_at", [$start_date, $end_date])->get();
        $callback = function() use ($list) 
        {
			
            $FH = fopen('php://output', 'w');
			$sn=1;
            fputcsv($FH, [
				"",
				"Last name",
				"First name",
				"Gender (MR or MRS)",
				"Date of birth",
				"Telefoonnummer",
				"Email",
			]);
			
            foreach ($list as $row) {
                $row->telephone = (int)$row->telephone;
				if($row->gender==1){
					$gender = "MRS";
				}
				else{
					$gender = "MR";
				}
                fputcsv($FH, [
					$sn++,
                    $row->child_lastname,
                    $row->child_firstname,
					$gender,
					$row->child_dob->format("d-m-Y"),
					stripslashes(utf8_decode($row->telephone)),
					$row->email,
                ]);
            }
            fclose($FH);
        };
        return response()->stream($callback, 200, $headers);
		
    }
    public function downloadReportsReservation(Location $location, $year, $date = null){
        // return $date;
		$headers = [
			'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',   
			'Content-type'        => 'text/csv',
			'Content-Disposition' => 'attachment; filename=reservation.csv',
			'Expires'             => '0',
			'Pragma'              => 'public'
        ];
        $start_date = ($year-1)."-10-01";
        $end_date = $year."-09-30 23:59:59";
        $list = Reservation::with('trip', 'trip.location')->whereHas('trip', function($query) use($location, $year, $date){
				$query->where("location_id", $location->id)
                ->whereBetween("start_date", [$year."-01-01", $year."-12-31"]);
                if ($date !== null) {
                    $query->where("start_date", $date);
                }
		})->where(['deleted' => false, 'status' => true])->whereBetween("created_at", [$start_date, $end_date])->get();
        $callback = function() use ($list) 
        {
			
            $FH = fopen('php://output', 'w');
			$sn=1;
            fputcsv($FH, [
				"",
				"Last name",
				"First name",
				"Gender (MR or MRS)",
				"Date of birth",
				"Telefoonnummer",
				"Email",
			]);
			
            foreach ($list as $row) {
                $row->telephone = (int)$row->telephone;
				if($row->gender==1){
					$gender = "MRS";
				}
				else{
					$gender = "MR";
				}
                fputcsv($FH, [
					$sn++,
                    $row->child_lastname,
                    $row->child_firstname,
					$gender,
					$row->child_dob->format("d-m-Y"),
					stripslashes(utf8_decode($row->telephone)),
					$row->email,
                ]);
            }
            fclose($FH);
        };
        return response()->stream($callback, 200, $headers);
		
    }
    public function sendEmailReport(Request $request){
        // $request->data;
        $data = $request->data;
        $bookings = TripBooking::whereHas('trip', function($query) use($data){
            $query->where('location_id', $data['location_id'])
            ->where('start_date', $data['date']);
        })->get();
        foreach($bookings as $booking){
            $emailTo = $booking->email;
		    $subject = $data['subject'];
		    $body = $data['body'];
		    $body = str_replace(array(
                '[$child_firstname]',
                '[$child_lastname]',
                '[$email]',
                '[$telefoonnummer]',
                '[$DOB]',
            ), array(
                $booking->child_firstname,
                $booking->child_lastname,
                $booking->email,
                $booking->telephone,
                $booking->child_dob
            ), $body);
            return \App\Utility::send_email('s.raheelshaikh@gmail.com', $subject, $body, $booking->id);
        }
        
    }
}
