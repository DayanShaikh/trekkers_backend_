<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class UploadController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return Upload::query()
        ->filter($request->only('search'))
        ->orderBy($request->order_by ?? 'file_name', $request->order ?? 'desc')
        ->paginate($request->per_page ?? 25)
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
            'file_name' => ['string', 'required'],
            'file_location' =>  [''],
        ]);
        $data['creater_id'] =   auth()->user()->id;
        $upload = Upload::create($data);
        if ($request->hasFile('file_location')){
			if(!empty($upload->file_location)){
				Storage::delete($upload->file_location);
			}
			$upload->file_location = Storage::putFile('public/upload_center', $request->file('file_location'));
            $upload->save();
		}
        return response()->json([
            'status'    =>  true,
            'upload' => $upload,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Upload  $upload
     * @return \Illuminate\Http\Response
     */
    public function show(Upload $upload)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Upload  $upload
     * @return \Illuminate\Http\Response
     */
    public function edit(Upload $upload)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Upload  $upload
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Upload $upload)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Upload  $upload
     * @return \Illuminate\Http\Response
     */
    public function destroy(Upload $upload)
    {
		if(!empty($upload->file_location)){
			Storage::delete($upload->file_location);
		}
		$upload->delete();
        return response()->json([
			'status' => true,
            'message' => 'Image has been deleted'
		]);
    }
}
