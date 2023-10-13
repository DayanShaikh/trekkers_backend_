<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\QuizQuestionAnswer;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class QuizQuestionAnswerController extends Controller
{
    /**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->authorizeResource(QuizQuestionAnswer::class, 'quiz_question_answer');
	}

   /**
	* Display a listing of the resource.
	*
	* @return \Illuminate\Http\Response
	*/
   public function index(Request $request)
   {
	   return QuizQuestionAnswer::query()
	   ->with('quizQuestionOption')
	   ->when($request->get("trash"), function($query) use ($request){
		   if($request->get('trash')==1){
			   $query->onlyTrashed();
		   }
	   })
	   ->when($request->get('quiz_question_id'), function ($query) use($request){
		$query->where("quiz_question_id", $request->get('quiz_question_id'));
		})
	   ->filter($request->only('search'))
	   ->orderBy($request->order_by ?? 'question_id', $request->order ?? 'desc')
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
			'quiz_question_id'   			=>  ['required'],
		   	'quiz_question_option_id' 	=>  [''],
	   ]);
	   $data['creater_id'] = auth()->user()->id;
	   $quizQuestionAnswer = QuizQuestionAnswer::create($data);
	   return response()->json([
		   'status'    =>  true,
		   'quizQuestionAnswer' => $quizQuestionAnswer,
	   ]);
   }

   /**
	* Display the specified resource.
	*
	* @param  \App\Models\QuizQuestionAnswer  $quizQuestionAnswer
	* @return \Illuminate\Http\Response
	*/
   public function show(QuizQuestionAnswer $quizQuestionAnswer)
   {
	   $quizQuestionAnswer = $quizQuestionAnswer->with('quizQuestionOption')->where('id', $quizQuestionAnswer->id)->first();
	   return response()->json(['status' => true, 'quizQuestionAnswer' => $quizQuestionAnswer]);
   }

   /**
	* Update the specified resource in storage.
	*
	* @param  \Illuminate\Http\Request  $request
	* @param  \App\Models\QuizQuestionAnswer  $quizQuestionAnswer
	* @return \Illuminate\Http\Response
	*/
   public function update(Request $request, QuizQuestionAnswer $quizQuestionAnswer)
   {
		$data = $request->validate([
			'quiz_question_id'   			=>  ['required'],
		   	'quiz_question_option_id' 	=>  [''],
		]);
	   $quizQuestionAnswer->update($data);
	   $quizQuestionAnswer->save();
	   return response()->json([
		   'status'    =>  true,
		   'quizQuestionAnswer' => $quizQuestionAnswer,
	   ]);
   }

   /**
	* Update the Lock Status.
	*
	* @param  \Illuminate\Http\Request  $request
	* @param  \App\Models\QuizQuestionAnswer  $quizQuestionAnswer
	* @return \Illuminate\Http\Response
	*/
   public function updateActiveStatus(Request $request, QuizQuestionAnswer $quizQuestionAnswer)
   {
	   $validated = $request->validate([
		   'status' => ['required'],
	   ]);
	   $quizQuestionAnswer->update(['status' => $request->boolean('status')]);
	   $quizQuestionAnswer->save();
	   return response()->json([
		   'status' => true,
		   'message'   =>  'Status has been updated'
	   ]);
   }

   /**
	* Remove the specified resource from storage.
	*
	* @param  \App\Models\QuizQuestionAnswer  $quizQuestionAnswer
	* @return \Illuminate\Http\Response
	*/
   public function destroy(QuizQuestionAnswer $quizQuestionAnswer)
   {
	   $quizQuestionAnswer->delete();
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
		   $quizQuestionAnswer = QuizQuestionAnswer::find($id);
		   if(Auth::user()->can('delete', $quizQuestionAnswer)) {
			   $quizQuestionAnswer->delete();
			   $count++;
		   }
		   else{
			   $errors[] = $id;
		   }
	   }
	   return response()->json([
		   "status" => true,
		   "count" => $count,
		   "errors" => $errors ? "You do not have permission to delete these quizQuestionAnswers ID: [ ". implode(",",$errors)." ]": "",
		   "message" => 'Selected record has been deleted'
	   ]);
   }

   public function restore($id)
   {
	   $quizQuestionAnswer = QuizQuestionAnswer::withTrashed()->find($id);
	   if(Auth::user()->can('restore', $quizQuestionAnswer)) {
		   $quizQuestionAnswer->restore();
		   return response()->json([
			   'status' => true,
			   "message" => 'Record has been restored'
		   ]);
	   }
	   else {
		   return response([
			   'status' => false,
			   'error' => 'You do not have permission to restore this quizQuestionAnswer ID: '.$id,
		   ], 403);
	   }
   }

   public function forceDelete($id)
   {
	   $quizQuestionAnswer = QuizQuestionAnswer::withTrashed()->find($id);
	   if(Auth::user()->can('forceDelete', $quizQuestionAnswer)) {
		   $quizQuestionAnswer->forceDelete();
		   return response()->json([
			   'status' => true,
			   "message" => 'Record has been permanent deleted'
		   ]);
	   }
	   else {
		   return response([
			   'status' => false,
			   'error' =>  'You do not have permission to permanent delete this quizQuestionAnswer ID: '.$id], 403);
	   }
   }

   public function massRestore(Request $request)
   {
	   $count = 0;
	   $errors = [];
	   foreach($request->get('ids') as $id) {
		   $quizQuestionAnswer = QuizQuestionAnswer::withTrashed()->find($id);
		   if(Auth::user()->can('restore', $quizQuestionAnswer)) {
			   $quizQuestionAnswer->restore();
			   $count++;
		   }
		   else{
			   $errors[] = $id;
		   }
	   }
	   return response()->json([
		   "status" => true,
		   "count" => $count,
		   "errors" => $errors ? "You do not have permission to restore these quizQuestionAnswers ID: [ ". implode(",",$errors)." ]": "",
		   "message" => 'Selected record has been restored'
	   ]);
   }

   public function massForceDelete(Request $request)
   {
	   $count = 0;
	   $errors = [];
	   foreach($request->get('ids') as $id) {
		   $quizQuestionAnswer = QuizQuestionAnswer::withTrashed()->find($id);
		   if(Auth::user()->can('forceDelete', $quizQuestionAnswer)) {
			   $quizQuestionAnswer->forceDelete();
			   $count++;
		   }
		   else{
			   $errors[] = $id;
		   }
	   }
	   return response()->json([
		   "status" => true,
		   "count" => $count,
		   "errors" => $errors ? "You do not have permission to permanent delete these quizQuestionAnswers ID: [ ". implode(",",$errors)." ]": "",
		   "message" => 'Selected record has been permanent deleted'
	   ]);
   }
}
