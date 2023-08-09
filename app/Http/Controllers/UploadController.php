<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
        if (in_array($fileExt, $imgExt)) {
            $imageTmpName   = $_FILES["file"]["tmp_name"];
            $imageSize = getImageSize($imageTmpName);
            $imageWidth = $imageSize[0];
            $imageHeight = $imageSize[1];
            $maxWidth = $req->max_size ?? 700;
            
            $tempName = public_path($path).$fileName.'.'.$fileExt;
            if ($imageWidth > $maxWidth) $DESIRED_WIDTH = $maxWidth;
            else $DESIRED_WIDTH = $imageWidth;
            
            
            $proportionalHeight = round(($DESIRED_WIDTH * $imageHeight) / $imageWidth);
            
            $originalImage = imageCreateFromString( file_get_contents($imageTmpName) );

            $resizedImage = imageCreateTrueColor($DESIRED_WIDTH, $proportionalHeight);
            
            imagesavealpha($resizedImage,true);
            $trans_colour   = imagecolorallocatealpha($resizedImage,0,0,0,127);
            imagefill($resizedImage,0,0,$trans_colour);
            unset($trans_colour);
            
            imageCopyResampled($resizedImage, $originalImage, 0, 0, 0, 0, $DESIRED_WIDTH+1, $proportionalHeight+1, $imageWidth, $imageHeight);
            
            if ($fileExt == 'png') {
                imagePNG($resizedImage, $tempName);
            } else {
                imageJPEG($resizedImage, $tempName);
            }
            
            imageDestroy($originalImage);
            imageDestroy($resizedImage);
        } else {
            $request->file->move(public_path($path), $fileName);
        }
   
        return response()->json((object)[
            'status' => 'success upload',
            'name' => $fileName
        ], 200);
    }
}