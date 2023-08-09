<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use App\User;
use App\Lahan;
use App\LahanTutupan;
use App\LahanTutupanChangeRequest;
use App\Trees as Tree;
use App\TreeLocation;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class LahanTutupanController extends Controller
{
    //GET MAIN CONTROLLER {
        public function GetLahanTutupanRequestAllAdmin(Request $request) {
            $program_year = $request->program_year;
            $getcr = $request->user_id;
            
            if($getcr){$cr='%'.$getcr.'%';}
            else{$cr='%%';}
            
            $GetTutupanLahanAll = DB::table('lahan_tutupan_change_requests')
                ->select('lahan_tutupan_change_requests.*')
                ->leftjoin('employees', 'employees.nik', '=', 'lahan_tutupan_change_requests.user_id')
                ->leftjoin('managementunits', 'managementunits.mu_no', '=', 'lahan_tutupan_change_requests.mu_no')
                ->where('lahan_tutupan_change_requests.is_dell','=',0)
                ->where('lahan_tutupan_change_requests.program_year','=',$program_year)
                ->where('lahan_tutupan_change_requests.user_id', 'LIKE', $cr)
                ->orderBy('lahan_tutupan_change_requests.created_at', 'desc')
                ->get();
                
            $rslt =  $this->ResultReturn(200, 'success', $GetTutupanLahanAll);
            return response()->json($rslt, 200);  
        }
        
        public function GetDetailTutupanLahanRequest(Request $request) {
            $form_no = $request->form_no;
            
            $getDetail = DB::table('lahan_tutupan_change_requests')
                ->select('lahan_tutupan_change_requests.*',
                        'managementunits.name as namaMU',
                        'target_areas.name as namaTA',
                        'employees.name as namaEmployee')
                ->leftjoin('employees', 'employees.nik', '=', 'lahan_umums.employee_no')
                ->leftjoin('managementunits', 'managementunits.mu_no', '=', 'lahan_umums.mu_no')
                ->where('lahan_tutupan_change_requests.is_dell', '=', 0)
                ->where('lahan_tutupan_change_requests.form_no', '=', $form_no)
                ->first();
        }
    //END GET MAIN CONTROLLER }
    
    //ADD & UPDATE CONTROLLER {
        public function AddLahanTutupanRequest(Request $request) {
            $validator = Validator::make($request->all(), [                
                // 'form_no' => 'required|max:255',
                'farmer_no' => 'required',
                'land_area' => 'required',
                // 'tutupan_lahan_now' => 'required',
                // 'tutupan_lahan_new' => 'required',
                // 'reason' => 'required|max:255',
                // 'lahan_no' => 'required|max:255',
                // 'year_active' => 'required|max:255',
                // 'program_year' => 'required|max:255',
                // 'tutupan_photo1' => 'required',
                // 'tutupan_photo2' => 'required',
                // 'tutupan_photo3' => 'required|max:255',
                'mu_no' => 'required|max:255',
                'target_area' => 'required',
            ]);

            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
            
            $form_no = LahanTutupanChangeRequest::Maxno();
            
            LahanTutupanChangeRequest::create([
                'form_no' => $form_no,
                'farmer_no' => $request->farmer_no, 
                'land_area' => $request->land_area,
                'tutupan_lahan_now' => $request->tutupan_lahan_now,
                'tutupan_lahan_new' => $request->tutupan_lahan_new,
                'reason' => $request->reason,
                'lahan_no' => $request->lahan_no,
                'year_active' => $request->year_active,
                'program_year' => $request->program_year,
                'submit_date_ff' => Carbon::now()->format('Y-m-d'),
                'submit_date_fc' => Carbon::now()->format('Y-m-d'),
                'is_verified' => 0,
                'verified_by' => '-',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'user_id' => $request->user_id,
                'mu_no' => $request->mu_no,
                'target_area' => $request->target_area,
                'is_dell' => 0,
                'tutupan_photo1' => $request->tutupan_photo1,
                'tutupan_photo2' => $request->tutupan_photo2,
                'tutupan_photo3' => $request->tutupan_photo3,
            ]);
            
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200);
        }
        
        public function UpdateLahanTutupanRequest(Request $request) {
            $validator = Validator::make($request->all(), [                
                // 'form_no' => 'required|max:255',
                'farmer_no' => 'required',
                'land_area' => 'required',
                // 'tutupan_lahan_now' => 'required',
                // 'tutupan_lahan_new' => 'required',
                // 'reason' => 'required|max:255',
                // 'lahan_no' => 'required|max:255',
                // 'year_active' => 'required|max:255',
                // 'program_year' => 'required|max:255',
                // 'tutupan_photo1' => 'required',
                // 'tutupan_photo2' => 'required',
                // 'tutupan_photo3' => 'required|max:255',
                'mu_no' => 'required|max:255',
                'target_area' => 'required',
            ]);

            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
            
            $form_no = LahanTutupanChangeRequest::Maxno();
            
            LahanTutupanChangeRequest::update([
                'farmer_no' => $request->farmer_no, 
                'land_area' => $request->land_area,
                'tutupan_lahan_now' => $request->tutupan_lahan_now,
                'tutupan_lahan_new' => $request->tutupan_lahan_new,
                'reason' => $request->reason,
                'lahan_no' => $request->lahan_no,
                'year_active' => $request->year_active,
                'program_year' => $request->program_year,
                'submit_date_ff' => Carbon::now()->format('Y-m-d'),
                'submit_date_fc' => Carbon::now()->format('Y-m-d'),
                'updated_at' => Carbon::now(),
                'user_id' => $request->user_id,
                'mu_no' => $request->mu_no,
                'target_area' => $request->target_area,
                'is_dell' => 0,
                'tutupan_photo1' => $request->tutupan_photo1,
                'tutupan_photo2' => $request->tutupan_photo2,
                'tutupan_photo3' => $request->tutupan_photo3,
            ]);
            
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200);
        }
    //END ADD CONTROLLER }
    
    //VERIFICATION CONTROLLER {
        public function VerificationLahanTutupanFC(Request $request) {
            $validator = Validator::make($request->all(), [
                'form_no' => 'required',
                'verified_by' => 'required',
            ]);
            
            if($validator->fails()){
                $rslt = $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
            
            $form_no = $request->form_no;
            $tutupan = DB::table('lahan_tutupan_change_requests')->where('form_no', '=', $form_no)->first();
            
            if($tutupan) {
                LahanTutupanChangeRequest::where('form_no', '=', $form_no)
                ->update([
                    'updated_at'=>Carbon::now(),
                    'verified_by'=>$request->verified_by,
                    'is_verified'=> 1
                ]);
                
                $rslt =  $this->ResultReturn(200, 'success', 'success');
                return response()->json($rslt, 200); 
            }else{
                $rslt =  $this->ResultReturn(400, 'terminate data', 'doesnt match data');
                return response()->json($rslt, 400);
            }
        }
        
        public function VerificationLahanTutupanUM(Request $request) {
            $validator = Validator::make($request->all(), [
                'form_no' => 'required',
                'verified_by' => 'required',
            ]);
            
            if($validator->fails()){
                $rslt = $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
            
            $form_no = $request->form_no;
            $tutupan = DB::table('lahan_tutupan_change_requests')->where('form_no', '=', $form_no)->first();
            
            if($tutupan) {
                LahanTutupanChangeRequest::where('form_no', '=', $form_no)
                ->update([
                    'updated_at'=>Carbon::now(),
                    'verified_by'=>$request->verified_by,
                    'is_verified'=> 2
                ]);
                
                Lahan::where('lahan_no', '=', $tutupan->lahan_no)
                ->update([
                    'updated_at'=>Carbon::now(),
                    'status_perubahan_tutupan' => 1,
                    'tutupan_lahan' => $tutupan->tutupan_lahan_new
                ]);
                
                $lahan = Lahan::where('lahan_no', '=', $tutupan->lahan_no)->first();
                
                LahanTutupan::create([
                    'lahan_no' => $tutupan->lahan_no,
                    'land_area' => $tutupan->land_area,
                    'planting_area' => ((100 - $tutupan->tutupan_lahan_new) /100) * $tutupan->land_area,
                    'program_year' => $tutupan->program_year,
                    'tutupan_lahan' => $tutupan->tutupan_lahan_new,
                    'pattern' => '-',
                    'created_at' => Carbon::now()
                ]);
                
                $rslt =  $this->ResultReturn(200, 'success', 'success');
                return response()->json($rslt, 200); 
            }else{
                $rslt =  $this->ResultReturn(400, 'terminate data', 'doesnt match data');
                return response()->json($rslt, 400);
            }
        }
        
        public function UnverificationLahanTutupan(Request $request) {
            $validator = Validator::make($request->all(), [
                'form_no' => 'required',
                'verified_by' => 'required',
            ]);
            
            if($validator->fails()){
                $rslt = $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
            
            $form_no = $request->form_no;
            $tutupan = DB::table('lahan_tutupan_change_requests')->where('form_no', '=', $form_no)->first();
            
            if($tutupan) {
                LahanTutupanChangeRequest::where('form_no', '=', $form_no)
                ->update([
                    'updated_at'=> Carbon::now(),
                    'verified_by'=> '-',
                    'is_verified'=> 1
                ]);
                
                $rslt =  $this->ResultReturn(200, 'success', 'success');
                return response()->json($rslt, 200); 
            }else{
                $rslt =  $this->ResultReturn(400, 'terminate data', 'doesnt match data');
                return response()->json($rslt, 400);
            }
        }
    //END VERIFICATION CONTROLLER }
    
    //EXTENDED CONTROLLER
    
}
