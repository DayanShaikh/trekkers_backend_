<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\TripBooking;
use App\Models\Reservation;
use App\Models\TripBookingAddon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\GamilMail;
use Symfony\Component\Mime\Part\TextPart;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportTripBooking;
use Response;
use File;

class TripBookingController extends Controller
{
     /**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	 public function __construct()
	 {
	 	$this->authorizeResource(TripBooking::class, 'trip_booking');
	 }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return TripBooking::query()
		->with(['user','trip', 'trip.location', 'notes', 'passportDetails'])
		->withCount('notes')
        
		->whereHas('trip', function($query) use($request){
			$query->when($request->get('archive_trip'), function ($query) use($request){
				$query->where("archive", $request->get('archive_trip'));
			})
            ->when($request->get('date'), function ($q) use($request){
                if($request->date) {
                    $q->where('start_date', $request->date );
                }
            });
           
		})
		->whereHas('trip.location', function($query) use($request){
			$query->when($request->get('location_id'), function ($query) use($request){
				$query->where("location_id", $request->get('location_id'));
			});
		})
		->when($request->get("trash"), function($query) use ($request){
			if($request->get('trash')==1){
				$query->onlyTrashed();
			}
		})
        ->when($request->get("status") != null, function($query) use ($request){
            $query->where('status', $request->status );
            // if($request->get("status")==0){
            //     $query->orWhere('deleted', true);    
            // }
            // elseif($request->get("status")==1){
            //     $query->orWhere('deleted', false);
            // }
        })
        ->when($request->get('user_id'), function ($q) use($request){
            if($request->user_id) {
                $q->where('user_id', $request->user_id );
            }
        })
        ->when($request->get('trip_id'), function ($q) use($request){
            if($request->trip_id) {
                $q->where('trip_id', $request->trip_id );
            }
        })
        ->when($request->get('date_added')  != '', function ($query) use($request){
			$query->where("created_at", $request->get('date_added'));
		})
        ->when($request->get('password_email_filter') != '', function ($query) use($request){
			$query->where("email_sent", $request->get('password_email_filter'));
		})
        ->when($request->get('covid_option'), function ($query) use($request){
			$query->where("covid_option", $request->get('covid_option'));
		})
        ->when($request->get('payment_filter') != '', function ($query) use($request){
			
            if($request->get('payment_filter')==1){
                $extra.=' and a.payment_reminder_email_sent=0';
            }
            if($request->get('payment_filter')==2){
                $query->where("payment_reminder_email_sent", 1);
            }
		})
        ->when($request->get('can_drive'), function ($query) use($request){
            if($request->get('can_drive')==1){
			    $query->where("can_drive", $request->get('can_drive'));
            }
		})
        ->filter($request->only('search'))
        ->orderBy($request->order_by ?? 'created_at', $request->order ?? 'desc')
        ->paginate($request->per_page ?? 10)
        ;
    }
    public function bookingUser(Request $request)
    {
        return TripBooking::query()
		->with('user')
        ->when($request->get('trip_ticket_users_bookings'), function ($q) use($request){
                $q->whereDoesntHave('tripTicketUsers')->get();
        })
        ->when($request->get('trip_id'), function ($q) use($request){
            if($request->trip_id) {
                $q->where('trip_id', $request->trip_id );
            }
        })
        
        ->filter($request->only('search'))
        ->orderBy($request->order_by ?? 'created_at', $request->order ?? 'desc')
        ->paginate($request->per_page ?? 10)
        ;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['int','required'],
            'trip_id' => ['int','required'],
            'travel_agent_id' =>  [''],
            'travel_brand_id'   =>  [''],
            'child_firstname'  =>  [''],
            'child_lastname'  =>  [''],
            'gender'  =>  [''],
            'child_dob'  =>  [''],
			'parent_name'  =>  [''],
            'parent_email'  =>  ['email'],
            'email'  =>  ['email','required'],
            'address'  =>  [''],
            'house_number'  =>  [''],
            'city'  =>  [''],
            'postcode'  =>  [''],
            'telephone'  =>  [''],
            'cellphone'  =>  [''],
            'whatsapp_number'  =>  [''],
            'location_pickup_id'  =>  [''],
            'child_diet'  =>  [''],
            'child_medication'  =>  [''],
            'about_child'  =>  [''],
            'date_added'  =>  [''],
            'can_drive'  =>  [''],
            'have_driving_license'  =>  [''],
            'have_creditcard'  =>  [''],
			'trip_fee'  =>  ['numeric'],
            'insurance'  =>  ['numeric'],
            'cancellation_insurance'  =>  ['numeric'],
            'travel_insurance'  =>  ['numeric'],
            'cancellation_policy_number'  =>  ['int'],
            'travel_policy_number'  =>  ['int'],
            'survival_adventure_insurance'  =>  ['numeric'],
            'insurance_admin_charges'  =>  ['numeric'],
            'nature_disaster_insurance'  =>  ['numeric'],
            'sgr_contribution'  =>  ['numeric'],
            'insurnace_question_1'  =>  [''],
            'insurnace_question_2'  =>  [''],
            'total_amount'  =>  ['numeric'],
            'paid_amount'  =>  ['numeric'],
            'deleted'  =>  [''],
            'payment_reminder_email_sent'  =>  [''],
            'total_reminder_sent'  =>  [''],
            'email_sent'  =>  [''],
            'login_reminder_email_sent'  =>  [''],
            'upsell_email_sent'  =>  [''],
            'deposit_reminder_email_sent'  =>  [''],
            'passport_reminder_email_sent'  =>  [''],
            'display_name'  =>  [''],
            'additional_address'  =>  [''],
            'contact_person_name'  =>  [''],
            'contact_person_extra_name'  =>  [''],
            'contact_person_extra_cellphone'  =>  [''],
            'travel_agent_email'  =>  ['email'],
            'commission'  =>  ['numeric'],
            'covid_option'  =>  [''],
            'account_name'  =>  [''],
            'account_number'  =>  [''],
            'phone_reminder_email_sent'  =>  [''],
			'country'  =>  [''],
			'invoice_number'  =>  [''],
        ]);
        $data['creater_id'] =   auth()->user()->id;
        $tripBooking = TripBooking::create($data);
        return response()->json([
            'status'    =>  true,
            'tripBooking' => $tripBooking,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TripBooking  $tripBooking
     * @return \Illuminate\Http\Response
     */
    public function show(TripBooking $tripBooking)
    {
        //return $tripBooking;
		$tripBooking = $tripBooking->with('user','trip', 'trip.location', 'trip.location.destination', 'trip.location.attributes')->where('id', $tripBooking->id)->first();
        return response()->json(['status' => true, 'tripBooking' => $tripBooking]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TripBooking  $tripBooking
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TripBooking $tripBooking)
    {
        $data = $request->validate([
            'user_id' => ['int','required'],
            'trip_id' => ['int','required'],
            'travel_agent_id' =>  [''],
            'travel_brand_id'   =>  [''],
            'child_firstname'  =>  [''],
            'child_lastname'  =>  [''],
            'gender'  =>  [''],
            'child_dob'  =>  [''],
			'parent_name'  =>  [''],
            'parent_email'  =>  [''],
            'email'  =>  ['email','required'],
            'address'  =>  [''],
            'house_number'  =>  [''],
            'city'  =>  [''],
            'postcode'  =>  [''],
            'telephone'  =>  [''],
            'cellphone'  =>  [''],
            'whatsapp_number'  =>  [''],
            'location_pickup_id'  =>  [''],
            'child_diet'  =>  [''],
            'child_medication'  =>  [''],
            'about_child'  =>  [''],
            'date_added'  =>  [''],
            'can_drive'  =>  [''],
            'have_driving_license'  =>  [''],
            'have_creditcard'  =>  [''],
            'trip_fee'  =>  ['numeric'],
            'insurance'  =>  [''],
            'cancellation_insurance'  =>  ['numeric'],
            'travel_insurance'  =>  ['numeric'],
            'cancellation_policy_number'  =>  [''],
            'travel_policy_number'  =>  [''],
            'survival_adventure_insurance'  =>  ['numeric'],
            'insurance_admin_charges'  =>  ['numeric'],
            'nature_disaster_insurance'  =>  ['numeric'],
            'sgr_contribution'  =>  ['numeric'],
            'insurnace_question_1'  =>  [''],
            'insurnace_question_2'  =>  [''],
            'total_amount'  =>  ['numeric'],
            'paid_amount'  =>  ['numeric'],
            'deleted'  =>  [''],
            'payment_reminder_email_sent'  =>  [''],
            'total_reminder_sent'  =>  [''],
            'email_sent'  =>  [''],
            'login_reminder_email_sent'  =>  [''],
            'upsell_email_sent'  =>  [''],
            'deposit_reminder_email_sent'  =>  [''],
            'passport_reminder_email_sent'  =>  [''],
            'display_name'  =>  [''],
            'additional_address'  =>  [''],
            'contact_person_name'  =>  [''],
            'contact_person_extra_name'  =>  [''],
            'contact_person_extra_cellphone'  =>  [''],
            'travel_agent_email'  =>  [''],
            'commission'  =>  ['numeric'],
            'covid_option'  =>  [''],
            'account_name'  =>  [''],
            'account_number'  =>  [''],
            'phone_reminder_email_sent'  =>  [''],
			'country'  =>  [''],
			'invoice_number'  =>  [''],
        ]);
        $tripBooking->update($data);
        return response()->json([
            'status'    =>  true,
            'tripBooking' => $tripBooking,
        ]);
    }

    /**
     * Update the Lock Status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TripBooking  $tripBooking
     * @return \Illuminate\Http\Response
     */
    public function updateActiveStatus(Request $request, TripBooking $tripBooking)
	{
		$validated = $request->validate([
			'status' => ['required'],
		]);
        $tripBooking->update(['status' => $request->boolean('status')]);
        $tripBooking->save();
		return response()->json([
            'status' => true,
            'message'   =>  'Status has been updated'
        ]);
	}
	
	public function updatePolicyNumber(Request $request, TripBooking $tripBooking)
	{
		$validated = $request->validate([
			'cancellation_policy_number'  =>  [''],
            'travel_policy_number'  =>  [''],
		]);
        $tripBooking->update([
			'cancellation_policy_number' => $request->cancellation_policy_number,
			'travel_policy_number' => $request->travel_policy_number
		]);
        $tripBooking->save();
		return response()->json([
            'status' => true,
            'message'   =>  'Status has been updated'
        ]);
	}

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TripBooking  $tripBooking
     * @return \Illuminate\Http\Response
     */
    public function destroy(TripBooking $tripBooking)
    {
        $tripBooking->delete();
		return response()->json([
            'status'    =>  true,
            'message'   =>  'Record has been deleted'
        ]);
    }

    public function massDestroy(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $tripBooking = TripBooking::find($id);
            if(Auth::user()->can('delete', $tripBooking)) {
                $tripBooking->delete();
                $count++;
            }
            else{
                $errors[] = ["You do not have permission to delete Trip Booking ID: ".$id.""];
            }
        }
		return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to delete these Trip Booking ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been deleted'
        ]);
    }

    public function restore($id)
    {
        $tripBooking = TripBooking::withTrashed()->find($id);
		if(Auth::user()->can('restore', $tripBooking)) {
			$tripBooking->restore();
			return response()->json([
                'status' => true,
                "message" => 'Record has been restored'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' => 'You do not have permission to restore Trip Booking ID: '.$id,
            ], 403);
		}
    }

    public function forceDelete($id)
    {
        $tripBooking = TripBooking::withTrashed()->find($id);
		if(Auth::user()->can('forceDelete', $tripBooking)) {
			$user->forceDelete();
			return response()->json([
                'status' => true,
                "message" => 'Record has been permanent deleted'
            ]);
		}
		else {
			return response(['status' => 'You do not have permission to Permanent Delete Trip Booking ID: '.$id], 403);
		}
    }

    public function massRestore(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $tripBooking = TripBooking::withTrashed()->find($id);
            if(Auth::user()->can('restore', $tripBooking)) {
                $tripBooking->restore();
                $count++;
            }
            else{
                $errors[] = ["You do not have permission to Restore Trip Booking ID: ".$id.""];
            }
        }
		return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to restore these Trip Booking ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been restored'
        ]);
	}

    public function massForceDelete(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $tripBooking = TripBooking::withTrashed()->find($id);
            if(Auth::user()->can('forceDelete', $tripBooking)) {
                $tripBooking->forceDelete();
                $count++;
            }
            else{
                $errors[] = ["You do not have permission to Permanent Delete Trip Booking ID: ".$id.""];
            }
        }
		return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to permanent delete these Trip Booking ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been permanent deleted'
        ]);
	}

	public function downloadEmails(Request $request){
		$headers = [
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',   
                'Content-type'        => 'text/csv',
                'Content-Disposition' => 'attachment; filename=email.csv',
                'Expires'             => '0',
                'Pragma'              => 'public'
        ];

        $list = TripBooking::where('deleted', 0)->when($request->get('user_id'), function ($query) use($request){
            if($request->user_id) {
                $query->where('user_id', $request->user_id );
            }
        })
        ->when($request->get('trip_id'), function ($query) use($request){
            if($request->trip_id) {
                $query->where('trip_id', $request->trip_id );
            }
        })
        ->when($request->get('date_added'), function ($query) use($request){
			$query->where("date_added", $request->get('date_added'));
		})
        ->when($request->get('email_sent'), function ($query) use($request){
			$query->where("email_sent", $request->get('email_sent'));
		})
        ->when($request->get('covid_option'), function ($query) use($request){
			$query->where("covid_option", $request->get('covid_option'));
		})
        ->when($request->get("trash"), function($query) use ($request){
            if($request->get('trash')==1){
                $query->onlyTrashed();
            }
        })->with('passportDetails')->get();
		//return $list;
        $callback = function() use ($list) 
        {
			
            $FH = fopen('php://output', 'w');
			$sn=1;
			fputcsv($FH, [
				"ID",
				"Email"
			]);
            foreach ($list as $row) {
				
                fputcsv($FH, [
                    $row->id,
                    $row->email
                ]);
            }
            fclose($FH);
        };
        return response()->stream($callback, 200, $headers);
    }
	public function downloadPassport(Request $request){
		$headers = [
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',   
                'Content-type'        => 'text/csv',
                'Content-Disposition' => 'attachment; filename=passport.csv',
                'Expires'             => '0',
                'Pragma'              => 'public'
        ];

        $list = TripBooking::where('deleted', 0)->when($request->get('user_id'), function ($query) use($request){
            if($request->user_id) {
                $query->where('user_id', $request->user_id );
            }
        })
        ->when($request->get('trip_id'), function ($query) use($request){
            if($request->trip_id) {
                $query->where('trip_id', $request->trip_id );
            }
        })
        ->when($request->get('date_added'), function ($query) use($request){
			$query->where("date_added", $request->get('date_added'));
		})
        ->when($request->get('email_sent'), function ($query) use($request){
			$query->where("email_sent", $request->get('email_sent'));
		})
        ->when($request->get('covid_option'), function ($query) use($request){
			$query->where("covid_option", $request->get('covid_option'));
		})
        ->when($request->get("trash"), function($query) use ($request){
            if($request->get('trash')==1){
                $query->onlyTrashed();
            }
        })->with('passportDetails')->get();
		//return $list;
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
				"Country of issuance (3-letter)",
				"Passport number",
				"Nationality (3-letter)",
				"Date of expiration",
				"Dietary requirements*",
				"Medical requirements*",
				"Frequent Flyer No. without punctuation marks*",
				"Frequent Flyer Airline Program*"
			]);
            foreach ($list as $row) {
				if($row->gender==1){
					$gender = "MRS";
				}
				else{
					$gender = "MR";
				}
				//return $row;
				if( $row->passport_details && $row->passport_details[0] ) {
					$p1 = $row->passport_details[0]->document_number;
					$p2 = $row->passport_details[0]->issue_date;
					$p3 = $row->passport_details[0]->expiry_date;
				}
				else{
					$p1 = $p2 = $p3 = '';
				}
                fputcsv($FH, [
					$sn++,
                    $row->child_lastname,
                    $row->child_firstname,
					$gender,
					$row->child_dob->format("d-m-Y"),
					'NLD',
					$p1,
					'NLD',
					$p3,
					$row->child_diet,
					$row->child_medication,
					'',
					''
                ]);
            }
            fclose($FH);
        };
        return response()->stream($callback, 200, $headers);
		
    }
	public function downloadTicket(Request $request){
		$headers = [
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',   
                'Content-type'        => 'text/csv',
                'Content-Disposition' => 'attachment; filename=ticket.csv',
                'Expires'             => '0',
                'Pragma'              => 'public'
        ];

        $list = TripBooking::where('deleted', 0)->when($request->get('user_id'), function ($query) use($request){
            if($request->user_id) {
                $query->where('user_id', $request->user_id );
            }
        })
        ->when($request->get('trip_id'), function ($query) use($request){
            if($request->trip_id) {
                $query->where('trip_id', $request->trip_id );
            }
        })
        ->when($request->get('date_added'), function ($query) use($request){
			$query->where("date_added", $request->get('date_added'));
		})
        ->when($request->get('email_sent'), function ($query) use($request){
			$query->where("email_sent", $request->get('email_sent'));
		})
        ->when($request->get('covid_option'), function ($query) use($request){
			$query->where("covid_option", $request->get('covid_option'));
		})
        ->when($request->get("trash"), function($query) use ($request){
            if($request->get('trash')==1){
                $query->onlyTrashed();
            }
        })->get();
        $callback = function() use ($list) 
        {
			
            $FH = fopen('php://output', 'w');
			$sn=1;
			fputcsv($FH, [
				"Last name",
				"First name",
				"Gender (MR or MRS)",
				"Date of birth",
				"diet info",
			]);
            foreach ($list as $row) {
				if($row->gender==1){
					$gender = "MRS";
				}
				else{
					$gender = "MR";
				}
                fputcsv($FH, [
					$row->child_lastname,
					$row->child_firstname,
					$gender,
					$row->child_dob->format("d-m-Y"),
					$row->child_diet,
                ]);
            }
            fclose($FH);
        };
        return response()->stream($callback, 200, $headers);
		
    }
	public function downloadCsv(Request $request){
		$headers = [
			'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',   
			'Content-type'        => 'text/csv',
			'Content-Disposition' => 'attachment; filename=booking.csv',
			'Expires'             => '0',
			'Pragma'              => 'public'
        ];

        $list = TripBooking::where('deleted', 0)->when($request->get('user_id'), function ($query) use($request){
            if($request->user_id) {
                $query->where('user_id', $request->user_id );
            }
        })
        ->when($request->get('trip_id'), function ($query) use($request){
            if($request->trip_id) {
                $query->where('trip_id', $request->trip_id );
            }
        })
        ->when($request->get('date_added'), function ($query) use($request){
			$query->where("date_added", $request->get('date_added'));
		})
        ->when($request->get('email_sent'), function ($query) use($request){
			$query->where("email_sent", $request->get('email_sent'));
		})
        ->when($request->get('covid_option'), function ($query) use($request){
			$query->where("covid_option", $request->get('covid_option'));
		})
        ->when($request->get("trash"), function($query) use ($request){
            if($request->get('trash')==1){
                $query->onlyTrashed();
            }
        })->get()->toArray();
		//return $list;
        //array_unshift($list, array_keys($list[0]));
        $callback = function() use ($list) 
        {
			
            $FH = fopen('php://output', 'w');
			$sn=0;
            
            foreach ($list as $row) {
                if($sn==0){
					fputcsv($FH, array_keys($row));
				}
				fputcsv($FH, array_values($row));
                $sn++;
            }
            fclose($FH);
        };
        return response()->stream($callback, 200, $headers);
		
    }
	public function downloadInsurance(Request $request){
		$headers = [
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',   
                'Content-type'        => 'text/csv',
                'Content-Disposition' => 'attachment; filename=insurance.csv',
                'Expires'             => '0',
                'Pragma'              => 'public'
        ];

        $list = TripBooking::where('deleted', 0)->when($request->get('user_id'), function ($query) use($request){
            if($request->user_id) {
                $query->where('user_id', $request->user_id );
            }
        })
        ->when($request->get('trip_id'), function ($query) use($request){
            if($request->trip_id) {
                $query->where('trip_id', $request->trip_id );
            }
        })
        ->when($request->get('date_added'), function ($query) use($request){
			$query->where("date_added", $request->get('date_added'));
		})
        ->when($request->get('email_sent'), function ($query) use($request){
			$query->where("email_sent", $request->get('email_sent'));
		})
        ->when($request->get('covid_option'), function ($query) use($request){
			$query->where("covid_option", $request->get('covid_option'));
		})
        ->when($request->get("trash"), function($query) use ($request){
            if($request->get('trash')==1){
                $query->onlyTrashed();
            }
        })->get();
        $callback = function() use ($list) 
        {
			
            $FH = fopen('php://output', 'w');
			$sn=1;
			fputcsv($FH, [
				"Bookingsnumber",
				"First Name",
				"Last Name",
				"Sex",
				"Date of birth",
				"Outdoor & Survival insurance",
				"Cancelation insurance",
				"Administration cost",
				"Amount of Days"
			]);
            foreach ($list as $row) {
				if($row->gender==1){
					$gender = "MRS";
				}
				else{
					$gender = "MR";
				}
                fputcsv($FH, [
					$row->id,
					$row->child_firstname,
					$row->child_lastname,
					$gender,
					$row->child_dob->format("d-m-Y"),
					$row->travel_insurance,
					$row->cancellation_insurance,
					$row->insurance_admin_charges,
					$row->duration,
                ]);
            }
            fclose($FH);
        };
        return response()->stream($callback, 200, $headers);
		
    }
	public function downloadAddon(Request $request){
		$headers = [
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',   
                'Content-type'        => 'text/csv',
                'Content-Disposition' => 'attachment; filename=trip-booking-addon.csv',
                'Expires'             => '0',
                'Pragma'              => 'public'
        ];
        $list = TripBookingAddon::with(['locationAddon', 'tripBooking' => function($q){
			$q->select('id', 'trip_id', 'child_firstname', 'child_lastname', 'email');
		}, 'tripBooking.trip', 'tripBooking.trip.location' =>function($q){
			$q->select('id', 'title');
		}])->select('id', 'location_addon_id', 'trip_booking_id', 'booking_date', 'payment_date', 'amount', 'created_at')->get()->toArray();
    //    return $list;
        $callback = function() use ($list) 
        {
			
            $FH = fopen('php://output', 'w');
			$sn=1;
			fputcsv($FH, [
				"Order#",
				"Addon",
				"Trip",
				"Booking",
				"Booking Date",
				"Payment Date",
				"Amount",
				"Ts",
			]);
            foreach ($list as $row) {
                
                fputcsv($FH, [
					$row["id"],
					($row["location_addon_id"] && $row["location_addon"]) ? utf8_decode(stripslashes($row["location_addon"]["title"])) : '',
					($row["trip_booking_id"] && $row["trip_booking"] && $row["trip_booking"]["trip"] && $row["trip_booking"]["trip"]["location"]) ? utf8_decode(stripslashes($row["trip_booking"]["trip"]["location"]["title"])) : '',
                    ($row["trip_booking_id"] && $row["trip_booking"]) ? utf8_decode(stripslashes($row["trip_booking"]["child_firstname"]." ".$row["trip_booking"]["child_lastname"]." ".$row["trip_booking"]["email"])) : '',
                    ($row["booking_date"]) ? date("d-m-Y", strtotime($row["booking_date"])) : '--',
                    ($row["payment_date"]) ? date("d-m-Y", strtotime($row["payment_date"])) : '--',
					$row["amount"],
					$row["created_at"],
                ]);
            }
            fclose($FH);
        };
        return response()->stream($callback, 200, $headers);
    }
	public function downloadReservation(Request $request){
		$headers = [
			'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',   
			'Content-type'        => 'text/csv',
			'Content-Disposition' => 'attachment; filename=reservation.csv',
			'Expires'             => '0',
			'Pragma'              => 'public'
        ];

        $list = Reservation::get()->toArray();
		//return $list;
        $callback = function() use ($list) 
        {
			
            $FH = fopen('php://output', 'w');
			$sn=0;
            
            foreach ($list as $row) {
                if($sn==0){
					fputcsv($FH, array_keys($row));
				}
				fputcsv($FH, array_values($row));
                $sn++;
            }
            fclose($FH);
        };
        return response()->stream($callback, 200, $headers);
		
    }
}
