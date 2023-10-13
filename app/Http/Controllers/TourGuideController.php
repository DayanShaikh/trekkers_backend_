<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TripTourGuide;
use App\Models\TripBooking;
use App\Models\TripTicket;
use App\Models\Trip;
use App\Models\User;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\UserDropboxLink;
use Carbon\Carbon;
use Mail;
use App\Utility;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Hash;

class TourGuideController extends Controller
{
    public function dashboard(Request $request){
		$date = Carbon::now();
		$user_id = null;
		if(in_array('view-myaccount', auth()->user()->given_permissions) || auth()->user()->id == 1){
			$user_id = $request->session()->get('authUserId');
			if($user_id){
				$user = User::find($user_id);
			}
		}
		if(!$user_id){
			$user = auth()->user();
		}
		if($user){
			$user->fields = $user->userFields()->get();
			$is_partner = $user->fields->where('field_key', 'is_partner')->where('field_value', '1')->first();
			// return $is_partner->field_value;
			if(!empty($is_partner) && $is_partner->field_value==1){
				$user->trips = User::with(['locations', 'locations.trips' => function($query) use($request, $date){
					$query->where("start_date", ">=", $date->format("Y-m-d"))->where('status', true);
				}, 'locations.trips.location'])->whereHas('locations.trips', function($q) use($date){
					// $q->select(DB::raw("DATE_ADD(start_date, INTERVAL duration+2 DAY) >= '".date("Y-m-d")."'"));
					$q->where("start_date", ">=", $date->format("Y-m-d"))->where('status', true);
				})->where("id", $user->id)->get();
			}
			else{
				$user->trips = TripTourGuide::with('trip', 'trip.location')->whereHas('trip', function($q){
					// $q->select(DB::raw("DATE_ADD(start_date, INTERVAL duration+2 DAY) >= '".date("Y-m-d")."'"));
					$q->where("start_date", ">=", date("Y-m-d"));
				})->where("user_id", $user->id)->get();
			}
			return response()->json(["user" => $user]);
		}
		return response()->json(['error' => 'No Record Found'], 422);
	}
	public function dashboardDetails(Trip $trip, Request $request){
		$user_id = null;
		if(in_array('view-myaccount', auth()->user()->given_permissions) || auth()->user()->id == 1){
			$user_id = $request->session()->get('authUserId');
			if($user_id){
				$user = User::find($user_id);
			}
		}
		if(!$user_id){
			$user = auth()->user();
		}
		if($user){
			$date = Carbon::now();
			$tr = Trip::with('location')->where(['id' => $trip->id])->first();
			if($tr){
				$tr->tickets = $tr->tripTickets()->with('tripTicketUsers')->where("connecting_flight", 0)->orderBy('sortorder')->get();
				$tr->bookings = TripBooking::with(['locationPickup', 'passportDetails', 'notes' => function($query) use($request){
					$query->where('is_publish', true);
				}])->where(['trip_id' => $tr->id, 'status' => true, 'deleted' => 0])->orderBy('child_firstname')->get();
				foreach($tr->tickets as $ticket){
					$ticket->child = TripTicket::where("connecting_flight", $ticket->id)->orderBy('sortorder')->get();
				}
				return response()->json(["trip" => $tr]);
			}
		}
		return response()->json(['error' => 'No Record Found'], 422);
	}
	public function myGuides(Request $request){
		$user_id = null;
		if(in_array('view-myaccount', auth()->user()->given_permissions) || auth()->user()->id == 1){
			$user_id = $request->session()->get('authUserId');
			if($user_id){
				$user = User::find($user_id);
			}
		}
		if(!$user_id){
			$user = auth()->user();
		}
		if($user){
			$guides = User::with('userFields', 'userTrips', 'userTrips.trip', 'userTrips.trip.location')->whereHas('userFields', function (Builder $query) use($user){
				$query->where("field_key", 'partner')->where("field_value", $user->id)
				;
			})
			->get();
			return response()->json(["guides" => $guides]);
		}
		return response()->json(['error' => 'No Record Found'], 422);
	}
	public function showMyGuide($id)
    {
		$guide = User::with('userFields')->where('id', $id)->first();
        return response()->json(['status' => true, 'guide' => $guide]);
    }
	public function storeGuide(Request $request)
    {
        $data = $request->validate([
			'name' 	=>  [''],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'  	=>  [''],
        ]);
        $user_id = null;
		if(in_array('view-myaccount', auth()->user()->given_permissions) || auth()->user()->id == 1){
			$user_id = $request->session()->get('authUserId');
			if($user_id){
				$user = User::find($user_id);
			}
		}
		if(!$user_id){
			$user = auth()->user();
		}
        $guide = User::create($data);
		//$data1 = ['key' => 'partner', 'value' => auth()->user()->id];
		if (!empty($request->get('user_metas'))) {
			$data1 = [];
			$data1[] = ['field_key' => 'partner', 'field_value' => $user->id];
			$data1[] = ['field_key' => 'is_partner', 'field_value' => 0];
			$data1[] = ['field_key' => 'gender', 'field_value' => $request->gender];
			// foreach ($request->get('user_metas') as $key => $meta) {
				
			// 		$data1[] = ['field_key' => $meta['key'], 'field_value' => $meta['value']];
			// 		$data1[] = ['field_key' => 'partner', 'field_value' => auth()->user()->id];
			// }
			$guide->userFields()->createMany($data1);
		}
		
		$guide->save();
        return response()->json([
            'status'    =>  true,
            'guide' => $guide,
        ]);
    }
	public function updateGuide(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users')->ignore($request->id)],
        ]);

        if(isset($request->password)){
            
            $data['password'] = Hash::make($request->password);
        }
        $user->update($data);
        $user->save();
		if (!empty($request->get('user_metas'))) {
			foreach ($request->user_metas as $meta) {
					$doesExist = $user->userFields()
						->where('field_key', $meta['key'])
						->update(['field_value' => $meta['value']]);
					if (!$doesExist) {
						$user->userFields()->create(['field_key' => $meta['key'], 'field_value' => $meta['value']]);
					}
					
				
			}
			
		}
		$guide = User::with('roles', 'userFields', 'locations')->where('id', $user->id)->first();
        return response()->json([
            'status'	=>  true,
            'guide'		=> $guide,
        ]);
    }
	public function updateGuideTrip(Request $request)
    {
        $data = $request->validate([
			'user_id' 	=>  [''],
            'trip_id'  	=>  [''],
        ]);
		$TripTourGuide = TripTourGuide::where('id', $request->id)->first();
		if(!$TripTourGuide){
			$trip = TripTourGuide::create($data);
		}
        else{
			$trip = $TripTourGuide->update($data);
			
		}
        $trip->save();
        return response()->json([
            'status'	=>  true,
            'trip'		=> $trip,
        ]);
    }
	public function course(Request $request){
		$user_id = null;
		if(in_array('view-myaccount', auth()->user()->given_permissions) || auth()->user()->id == 1){
			$user_id = $request->session()->get('authUserId');
			if($user_id){
				$user = User::find($user_id);
			}
		}
		if(!$user_id){
			$user = auth()->user();
		}
		if($user){
			$courses = Course::orderBy('sortorder', 'asc')->paginate($request->get("limit", 10));
			if($courses){
				return response()->json($courses);
			}
		}
		return response()->json(['error' => 'No Record Found'], 422);
	}
	public function lesson(Course $course, Request $request){
		$user_id = null;
		if(in_array('view-myaccount', auth()->user()->given_permissions) || auth()->user()->id == 1){
			$user_id = $request->session()->get('authUserId');
			if($user_id){
				$user = User::find($user_id);
			}
		}
		if(!$user_id){
			$user = auth()->user();
		}
		if($user){
			$lessons = Lesson::where('course_id', $course->id)->orderBy('title')->paginate($request->get("limit", 10));
			if($lessons){
				return response()->json(["lessons" => $lessons, "course" => $course]);
			}
		}
		return response()->json(['error' => 'No Record Found'], 422);
	}
	public function lessonIntro(Lesson $lesson, Request $request){
		$user_id = null;
		if(in_array('view-myaccount', auth()->user()->given_permissions) || auth()->user()->id == 1){
			$user_id = $request->session()->get('authUserId');
			if($user_id){
				$user = User::find($user_id);
			}
		}
		if(!$user_id){
			$user = auth()->user();
		}
		if($user){
			$lesson = Lesson::with('course')->find($lesson->id);
			if($lesson){
				return response()->json($lesson);
			}
		}
		return response()->json(['error' => 'No Record Found'], 422);
	}
	public function lessonQuestion(Lesson $lesson, Request $request){
		$user_id = null;
		if(in_array('view-myaccount', auth()->user()->given_permissions) || auth()->user()->id == 1){
			$user_id = $request->session()->get('authUserId');
			if($user_id){
				$user = User::find($user_id);
			}
		}
		if(!$user_id){
			$user = auth()->user();
		}
		if($user){
			$lesson = Lesson::find($lesson->id);
			if($lesson){
				$lesson->questions = $lesson->quizQuestions()->with('quizQuestionOptions')->get();
				//$lesson->questions->options = $lesson->questions()->quizQuestionOptions()->get();
				return response()->json($lesson);
			}
		}
		return response()->json(['error' => 'No Record Found'], 422);
	}
	public function previousTrip(Request $request){
		$user_id = null;
		if(in_array('view-myaccount', auth()->user()->given_permissions) || auth()->user()->id == 1){
			$user_id = $request->session()->get('authUserId');
			if($user_id){
				$user = User::find($user_id);
			}
		}
		if(!$user_id){
			$user = auth()->user();
		}
		if($user){
			$user->fields = $user->userFields();
			$user->trips = TripTourGuide::with('trip', 'trip.location')->whereHas('trip', function($q){
				// $q->whereRaw("DATE_SUB(start_date, INTERVAL 3 MONTH) <= '".date("Y-m-d")."' and start_date < CURDATE()");
				$q->whereBetween('start_date', [Carbon::now()->subMonth(3), Carbon::now()]);
			})->where("user_id", $user->id)->get();
			foreach($user->trips as $trip){
				$user->dropbox = UserDropboxLink::where(['user_id' => $user->id, 'trip_id' => $trip->trip->id])->first();
			}
			return response()->json(["user" => $user]);
		}
		return response()->json(['error' => 'No Record Found'], 422);
	}
	public function dropboxTrip(Request $request)
    {
        $data = $request->validate([
			'user_id' 	=>  [''],
            'trip_id'  	=>  [''],
        ]);
		$dropbox = UserDropboxLink::where(['trip_id' => $request->trip_id, 'user_id' => $request->user_id])->first();
		if(!$dropbox){
			$data = UserDropboxLink::create($data);
		}
        else{
			$data = $dropbox->update($data);
			
		}
        $data->save();
        return response()->json([
            'status'	=>  true,
            'data'		=> $data,
        ]);
    }
}
