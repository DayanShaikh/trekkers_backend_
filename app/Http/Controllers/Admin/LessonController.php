<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use Illuminate\Http\Request;
use App\Models\Course;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class LessonController extends Controller
{
/**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->authorizeResource(Lesson::class, 'lesson');
	}

   /**
	* Display a listing of the resource.
	*
	* @return \Illuminate\Http\Response
	*/
   public function index(Request $request)
   {
	   return Lesson::query()
	   ->when($request->get("trash"), function($query) use ($request){
		   if($request->get('trash')==1){
			   $query->onlyTrashed();
		   }
	   })
	   ->when($request->get('course_id'), function ($query) use($request){
			$query->where("course_id", $request->get('course_id'));
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
		   'course_id'  =>  [''],
		   'title' =>  ['required','unique:lessons'],
		   'small_description'  =>  [''],
		   'details'  =>  [''],
		   'duration'  =>  [''],
		   'intro_video'  =>  [''],
	   ]);
	   $data['creater_id'] = auth()->user()->id;
	   $lesson = Lesson::create($data);
	   return response()->json([
		   'status'    =>  true,
		   'lesson' => $lesson,
	   ]);
   }

   /**
	* Display the specified resource.
	*
	* @param  \App\Models\Lesson  $lesson
	* @return \Illuminate\Http\Response
	*/
   public function show(Lesson $lesson)
   {
	   $lesson = $lesson->with('course')->where('id', $lesson->id)->first();
	   return response()->json(['status' => true, 'lesson' => $lesson]);
   }

   /**
	* Update the specified resource in storage.
	*
	* @param  \Illuminate\Http\Request  $request
	* @param  \App\Models\Lesson  $lesson
	* @return \Illuminate\Http\Response
	*/
   public function update(Request $request, Lesson $lesson)
   {
	   $data = $request->validate([
		'course_id'  =>  [''],
		'title' =>  ['required', Rule::unique('lessons')->ignore($lesson)],
		'small_description'  =>  [''],
		'details'  =>  [''],
		'duration'  =>  [''],
		'intro_video'  =>  [''],
	   ]);
	   $lesson->update($data);
	   $lesson->save();
	   return response()->json([
		   'status'    =>  true,
		   'lesson' => $lesson,
	   ]);
   }

   /**
	* Update the Lock Status.
	*
	* @param  \Illuminate\Http\Request  $request
	* @param  \App\Models\Lesson  $lesson
	* @return \Illuminate\Http\Response
	*/
   public function updateActiveStatus(Request $request, Lesson $lesson)
   {
	   $validated = $request->validate([
		   'status' => ['required'],
	   ]);
	   $lesson->update(['status' => $request->boolean('status')]);
	   $lesson->save();
	   return response()->json([
		   'status' => true,
		   'message'   =>  'Status has been updated'
	   ]);
   }

   /**
	* Remove the specified resource from storage.
	*
	* @param  \App\Models\Lesson  $lesson
	* @return \Illuminate\Http\Response
	*/
   public function destroy(Lesson $lesson)
   {
	   $lesson->delete();
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
		   $lesson = Lesson::find($id);
		   if(Auth::user()->can('delete', $lesson)) {
			   $lesson->delete();
			   $count++;
		   }
		   else{
			   $errors[] = $id;
		   }
	   }
	   return response()->json([
		   "status" => true,
		   "count" => $count,
		   "errors" => $errors ? "You do not have permission to delete these lessons ID: [ ". implode(",",$errors)." ]": "",
		   "message" => 'Selected record has been deleted'
	   ]);
   }

   public function restore($id)
   {
	   $lesson = Lesson::withTrashed()->find($id);
	   if(Auth::user()->can('restore', $lesson)) {
		   $lesson->restore();
		   return response()->json([
			   'status' => true,
			   "message" => 'Record has been restored'
		   ]);
	   }
	   else {
		   return response([
			   'status' => false,
			   'error' => 'You do not have permission to restore this lesson ID: '.$id,
		   ], 403);
	   }
   }

   public function forceDelete($id)
   {
	   $lesson = Lesson::withTrashed()->find($id);
	   if(Auth::user()->can('forceDelete', $lesson)) {
		   $lesson->forceDelete();
		   return response()->json([
			   'status' => true,
			   "message" => 'Record has been permanent deleted'
		   ]);
	   }
	   else {
		   return response([
			   'status' => false,
			   'error' =>  'You do not have permission to permanent delete this lesson ID: '.$id], 403);
	   }
   }

   public function massRestore(Request $request)
   {
	   $count = 0;
	   $errors = [];
	   foreach($request->get('ids') as $id) {
		   $lesson = Lesson::withTrashed()->find($id);
		   if(Auth::user()->can('restore', $lesson)) {
			   $lesson->restore();
			   $count++;
		   }
		   else{
			   $errors[] = $id;
		   }
	   }
	   return response()->json([
		   "status" => true,
		   "count" => $count,
		   "errors" => $errors ? "You do not have permission to restore these lessons ID: [ ". implode(",",$errors)." ]": "",
		   "message" => 'Selected record has been restored'
	   ]);
   }

   public function massForceDelete(Request $request)
   {
	   $count = 0;
	   $errors = [];
	   foreach($request->get('ids') as $id) {
		   $lesson = Lesson::withTrashed()->find($id);
		   if(Auth::user()->can('forceDelete', $lesson)) {
			   $lesson->forceDelete();
			   $count++;
		   }
		   else{
			   $errors[] = $id;
		   }
	   }
	   return response()->json([
		   "status" => true,
		   "count" => $count,
		   "errors" => $errors ? "You do not have permission to permanent delete these lessons ID: [ ". implode(",",$errors)." ]": "",
		   "message" => 'Selected record has been permanent deleted'
	   ]);
   }
}
