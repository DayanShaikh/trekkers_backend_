<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\PageRedirect;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class PageRedirectController extends Controller
{
/**
	 * Create the controller instance.
	 *
	 * @return void
	 */
    public function __construct()
    {
        $this->authorizeResource(PageRedirect::class, 'page_redirect');
    }

   /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
   public function index(Request $request)
   {
       return PageRedirect::query()       
       ->when($request->get("trash"), function($query) use ($request){
           if($request->get('trash')==1){
               $query->onlyTrashed();
           }
       })
       ->when($request->get("status") != null, function($query) use ($request){
        $query->where('status', $request->status );    
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
           'page_url'           =>  ['required'],
           'redirect_url'  =>  ['required'],
       ]);
       $data['creater_id'] = auth()->user()->id;
       $pageRedirect = PageRedirect::create($data);
      
       return response()->json([
           'status'    =>  true,
           'pageRedirect' => $pageRedirect,
       ]);
   }

   /**
    * Display the specified resource.
    *
    * @param  \App\Models\PageRedirect  $pageRedirect
    * @return \Illuminate\Http\Response
    */
   public function show(PageRedirect $pageRedirect)
   {
       return response()->json(['status' => true, 'pageRedirect' => $pageRedirect]);
   }

   /**
    * Update the specified resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  \App\Models\PageRedirect  $pageRedirect
    * @return \Illuminate\Http\Response
    */
   public function update(Request $request, PageRedirect $pageRedirect)
   {
       $data = $request->validate([
            'page_url'          =>  ['required'],
            'redirect_url' =>  ['required'],
       ]);
       $pageRedirect->update($data);
      
       return response()->json([
           'status'    =>  true,
           'pageRedirect' => $pageRedirect,
       ]);
   }

    /**
    * Update the Lock Status.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  \App\Models\PageRedirect  $pageRedirect
    * @return \Illuminate\Http\Response
    */
   public function updateActiveStatus(Request $request, PageRedirect $pageRedirect)
   {
       $validated = $request->validate([
           'status' => ['required'],
       ]);       
           $pageRedirect->update(['status' => $request->boolean('status')]);
           $pageRedirect->save();
           return response()->json([
               'status' => true,
               'message'   =>  'Status has been updated'
           ]);
   }

   /**
    * Remove the specified resource from storage.
    *
    * @param  \App\Models\PageRedirect  $pageRedirect
    * @return \Illuminate\Http\Response
    */
   public function destroy(PageRedirect $pageRedirect)
   {
       $pageRedirect->delete();
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
           $pageRedirect = PageRedirect::find($id);
           if(Auth::user()->can('delete', $pageRedirect)) {
               $pageRedirect->delete();
               $count++;
           }
           else{
               $errors[] = $id;
           }
       }
       return response()->json([
           "status" => true,
           "count" => $count,
           "errors" => $errors ? "You do not have permission to delete these page redirects ID: [ ". implode(",",$errors)." ]": "",
           "message" => 'Selected record has been deleted'
       ]);
   }

   public function restore($id)
   {
       $pageRedirect = PageRedirect::withTrashed()->find($id);
       if(Auth::user()->can('restore', $pageRedirect)) {
           $pageRedirect->restore();
           return response()->json([
               'status' => true,
               "message" => 'Record has been restored'
           ]);
       }
       else {
           return response([
               'status' => false,
               'error' => 'You do not have permission to restore this page redirect ID: '.$id,
           ], 403);
       }
   }

   public function forceDelete($id)
   {
       $pageRedirect = PageRedirect::withTrashed()->find($id);
       if(Auth::user()->can('forceDelete', $pageRedirect)) {
           $pageRedirect->forceDelete();
           return response()->json([
               'status' => true,
               "message" => 'Record has been permenent deleted'
           ]);
       }
       else {
           return response([
               'status' => false,
               'error' =>  'You do not have permission to permanent delete this page redirect ID: '.$id], 403);
       }
   }

   public function massRestore(Request $request)
   {
       $count = 0;
       $errors = [];
       foreach($request->get('ids') as $id) {
           $pageRedirect = PageRedirect::withTrashed()->find($id);
           if(Auth::user()->can('restore', $pageRedirect)) {
               $pageRedirect->restore();
               $count++;
           }
           else{
               $errors[] = $id;
           }
       }
       return response()->json([
           "status" => true,
           "count" => $count,
           "errors" => $errors ? "You do not have permission to restore these page redirects ID: [ ". implode(",",$errors)." ]": "",
           "message" => 'Selected record has been restored'
       ]);
   }

   public function massForceDelete(Request $request)
   {
       $count = 0;
       $errors = [];
       foreach($request->get('ids') as $id) {
           $pageRedirect = PageRedirect::withTrashed()->find($id);
           if(Auth::user()->can('forceDelete', $pageRedirect)) {
               $pageRedirect->forceDelete();
               $count++;
           }
           else{
               $errors[] = $id;
           }
       }
       return response()->json([
           "status" => true,
           "count" => $count,
           "errors" => $errors ? "You do not have permission to permanent delete these page redirects ID: [ ". implode(",",$errors)." ]": "",
           "message" => 'Selected record has been permanent deleted'
       ]);
   }

   public function checkRedirectUrl(Request $request){
        $pageRedirect = PageRedirect::where('page_url', $request->get('url'))->first();
        if($pageRedirect){
            return [
                'pageRedirect' => $pageRedirect,
                'status' => true

            ];
        }
        return ['status' => false];
   }
}
