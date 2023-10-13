<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{

	    /**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->authorizeResource(Course::class, 'course');
	}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return Course::query()
        ->when($request->get("trash"), function($query) use ($request){
            if($request->get('trash')==1){
                $query->onlyTrashed();
            }
        })
        ->filter($request->only('search'))
        ->orderBy($request->order_by ?? 'title', $request->order ?? 'desc')
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
            'title' =>  ['required','unique:courses'],
            'description'  =>  [''],
			'details'  =>  [''],
            'sortorder'  =>  [''],

        ]);
        $data['creater_id'] = auth()->user()->id;
        $course = Course::create($data);
        if ($request->hasFile('image')){
			if(!empty($course->image)){
				Storage::delete($course->image);
			}
			$course->image = Storage::putFile('public/courses', $request->file('image'));
            $course->save();
		}
        return response()->json([
            'status'    =>  true,
            'course' => $course,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\Response
     */
    public function show(Course $course)
    {
        return response()->json(['status' => true, 'course' => $course]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Course $course)
    {
        $data = $request->validate([
            'title' =>  ['required', Rule::unique('courses')->ignore($course)],
            'description'  =>  [''],
			'details'  =>  [''],
            'sortorder'  =>  [''],
        ]);
        $course->update($data);
        $course->save();
        if ($request->hasFile('image')){
			if(!empty($course->image)){
				Storage::delete($course->image);
			}
			$course->image = Storage::putFile('public/courses', $request->file('image'));
            $course->save();
		}
        return response()->json([
            'status'    =>  true,
            'course' => $course,
        ]);
    }

    /**
     * Update the Lock Status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\Response
     */
    public function updateActiveStatus(Request $request, Course $course)
	{
        $validated = $request->validate([
			'status' => ['required'],
		]);
        $course->update(['status' => $request->boolean('status')]);
        $course->save();
        return response()->json([
            'status' => true,
            'message'   =>  'Status has been updated'
        ]);
	}

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\Response
     */
    public function destroy(Course $course)
    {
        $course->delete();
        return response()->json([
			'status' => true,
            "message" => 'Record has been deleted'
		]);
    }

    public function massDestroy(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $course = Course::find($id);
            if(Auth::user()->can('delete', $course)) {
                $course->delete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to delete these courses ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been deleted'
        ]);
    }

    public function restore($id)
    {
        $course = Course::withTrashed()->find($id);
		if(Auth::user()->can('restore', $course)) {
			$course->restore();
			return response()->json([
                'status' => true,
                "message" => 'Record has been restored'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' => 'You do not have permission to restore this course ID: '.$id,
            ], 403);
		}
    }

    public function forceDelete($id)
    {
        $course = Course::withTrashed()->find($id);
		if(Auth::user()->can('forceDelete', $course)) {
            if(!empty($course->image)){
                Storage::delete($course->image);
            }
			$course->forceDelete();
			return response()->json([
                'status' => true,
                "message" => 'Record has been permanent deleted'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' =>  'You do not have permission to permanent delete this course ID: '.$id], 403);
		}
    }

    public function massRestore(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $course = Course::withTrashed()->find($id);
            if(Auth::user()->can('restore', $course)) {
                $course->restore();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to restore these courses ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been restored'
        ]);
	}

    public function massForceDelete(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $course = Course::withTrashed()->find($id);
            if(Auth::user()->can('forceDelete', $course)) {
                if(!empty($course->image)){
                    Storage::delete($course->image);
                }
				$course->forceDelete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to permanent delete these courses ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been permanent deleted'
        ]);
	}
}
