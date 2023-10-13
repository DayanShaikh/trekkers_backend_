<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuizQuestion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class QuizQuestionController extends Controller
{
    /**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->authorizeResource(QuizQuestion::class, 'quiz_question');
	}

   /**
	* Display a listing of the resource.
	*
	* @return \Illuminate\Http\Response
	*/
   public function index(Request $request)
   {
	   return QuizQuestion::query()
	   ->with('lesson')
	   ->when($request->get("trash"), function($query) use ($request){
		   if($request->get('trash')==1){
			   $query->onlyTrashed();
		   }
	   })
	   ->when($request->get('lesson_id'), function ($query) use($request){
			$query->where("lesson_id", $request->get('lesson_id'));
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
			'lesson_id'   				=>  ['required'],
		   	'question'    				=>  ['required', 'unique:quiz_questions'],
			'description'   			=>  [''],
			'is_multiple'   			=>  [''],
			'sortorder'   				=>  [''],
			'quiz_question_options' 	=> 	['array', 'min:2'],
	   ]);
	   $data['creater_id'] = auth()->user()->id;
	   $quizQuestion = QuizQuestion::create($data);
	   if($request->get('quiz_question_options')){
			$quizQuestion->quizQuestionOptions()->createMany($request->get('quiz_question_options'));
	   }
	   return response()->json([
		   'status'    =>  true,
		   'quizQuestion' => $quizQuestion,
	   ]);
   }

   /**
	* Display the specified resource.
	*
	* @param  \App\Models\QuizQuestion  $quizQuestion
	* @return \Illuminate\Http\Response
	*/
   public function show(QuizQuestion $quizQuestion)
   {
	   $quizQuestion = $quizQuestion->with('quizQuestionOptions', 'lesson')->where('id', $quizQuestion->id)->first();
	   return response()->json(['status' => true, 'quizQuestion' => $quizQuestion]);
   }

   /**
	* Update the specified resource in storage.
	*
	* @param  \Illuminate\Http\Request  $request
	* @param  \App\Models\QuizQuestion  $quizQuestion
	* @return \Illuminate\Http\Response
	*/
   public function update(Request $request, QuizQuestion $quizQuestion)
   {
		$data = $request->validate([
			'lesson_id'   				=>  ['required'],
		   	'question'    				=>  ['required', Rule::unique('quiz_questions')->ignore($request->id)],
			'description'   			=>  [''],
			'is_multiple'   			=>  [''],
			'sortorder'   				=>  [''],
			'quiz_question_options' 	=> 	['array', 'min:2'],
		]);
	   $quizQuestion->update($data);
	   	if($request->get('quiz_question_options')){
			$quizQuestion->quizQuestionOptions()->delete();
			$quizQuestion->quizQuestionOptions()->createMany($request->get('quiz_question_options'));
		}
	   $quizQuestion->save();
	   return response()->json([
		   'status'    =>  true,
		   'quizQuestion' => $quizQuestion,
	   ]);
   }

   /**
	* Update the Lock Status.
	*
	* @param  \Illuminate\Http\Request  $request
	* @param  \App\Models\QuizQuestion  $quizQuestion
	* @return \Illuminate\Http\Response
	*/
   public function updateActiveStatus(Request $request, QuizQuestion $quizQuestion)
   {
	   $validated = $request->validate([
		   'status' => ['required'],
	   ]);
	   $quizQuestion->update(['status' => $request->boolean('status')]);
	   $quizQuestion->save();
	   return response()->json([
		   'status' => true,
		   'message'   =>  'Status has been updated'
	   ]);
   }

   /**
	* Remove the specified resource from storage.
	*
	* @param  \App\Models\QuizQuestion  $quizQuestion
	* @return \Illuminate\Http\Response
	*/
   public function destroy(QuizQuestion $quizQuestion)
   {
	   $quizQuestion->delete();
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
		   $quizQuestion = QuizQuestion::find($id);
		   if(Auth::user()->can('delete', $quizQuestion)) {
			   $quizQuestion->delete();
			   $count++;
		   }
		   else{
			   $errors[] = $id;
		   }
	   }
	   return response()->json([
		   "status" => true,
		   "count" => $count,
		   "errors" => $errors ? "You do not have permission to delete these quizQuestions ID: [ ". implode(",",$errors)." ]": "",
		   "message" => 'Selected record has been deleted'
	   ]);
   }

   public function restore($id)
   {
	   $quizQuestion = QuizQuestion::withTrashed()->find($id);
	   if(Auth::user()->can('restore', $quizQuestion)) {
		   $quizQuestion->restore();
		   return response()->json([
			   'status' => true,
			   "message" => 'Record has been restored'
		   ]);
	   }
	   else {
		   return response([
			   'status' => false,
			   'error' => 'You do not have permission to restore this quizQuestion ID: '.$id,
		   ], 403);
	   }
   }

   public function forceDelete($id)
   {
	   $quizQuestion = QuizQuestion::withTrashed()->find($id);
	   if(Auth::user()->can('forceDelete', $quizQuestion)) {
		   $quizQuestion->forceDelete();
		   return response()->json([
			   'status' => true,
			   "message" => 'Record has been permanent deleted'
		   ]);
	   }
	   else {
		   return response([
			   'status' => false,
			   'error' =>  'You do not have permission to permanent delete this quizQuestion ID: '.$id], 403);
	   }
   }

   public function massRestore(Request $request)
   {
	   $count = 0;
	   $errors = [];
	   foreach($request->get('ids') as $id) {
		   $quizQuestion = QuizQuestion::withTrashed()->find($id);
		   if(Auth::user()->can('restore', $quizQuestion)) {
			   $quizQuestion->restore();
			   $count++;
		   }
		   else{
			   $errors[] = $id;
		   }
	   }
	   return response()->json([
		   "status" => true,
		   "count" => $count,
		   "errors" => $errors ? "You do not have permission to restore these quizQuestions ID: [ ". implode(",",$errors)." ]": "",
		   "message" => 'Selected record has been restored'
	   ]);
   }

   public function massForceDelete(Request $request)
   {
	   $count = 0;
	   $errors = [];
	   foreach($request->get('ids') as $id) {
		   $quizQuestion = QuizQuestion::withTrashed()->find($id);
		   if(Auth::user()->can('forceDelete', $quizQuestion)) {
			   $quizQuestion->forceDelete();
			   $count++;
		   }
		   else{
			   $errors[] = $id;
		   }
	   }
	   return response()->json([
		   "status" => true,
		   "count" => $count,
		   "errors" => $errors ? "You do not have permission to permanent delete these quizQuestions ID: [ ". implode(",",$errors)." ]": "",
		   "message" => 'Selected record has been permanent deleted'
	   ]);
   }
}
