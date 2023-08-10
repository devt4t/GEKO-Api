<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Image;

class UploadController extends Controller
{
    public function Uploads(Request $request) {
        $validator = Validator::make($request->all(), [
            'file' => 'required',
            'file_name' => 'required',
        ]);
        $prefixPath = 'Uploads/';
        $path = $prefixPath . $request->directory ?? 'Uploads';

        if($validator->fails()) return response()->json($validator->errors()->first(), 400);
        $fileExt = $request->file->extension();
        $fileName = $request->file_name.'.'.$request->file->extension();  

        $imgExt = ['jpg','jpeg','JPG','JPEG','GIF','gif','png','PNG','HEIC'];
        if (in_array($fileExt, $imgExt) && $request->max_size) {
            $validator = Validator::make($request->all(), [
                'max_size' => 'required|integer',
            ]);
            if($validator->fails()) return response()->json($validator->errors()->first(), 400);
            $image = $request->file('file');
        
            $destinationPath = public_path($path);
            $img = Image::make($image->getRealPath());
            $img->resize($request->max_size, $request->max_size, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destinationPath.'/'.$fileName);
        } else {
            $request->file->move(public_path($path), $fileName);
        }
   
        return response()->json((object)[
            'status' => 'success upload',
            'name' => $fileName
        ], 200);
    }
}