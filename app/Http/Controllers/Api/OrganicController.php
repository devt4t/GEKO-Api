<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use App\Employee;
use App\Farmer;
use App\FieldFacilitator;
use App\Organic;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OrganicController extends Controller
{
    public function GetOrganicAll(Request $request)
    {
        $userId = $request->created_by;
        $getvillage = $request->village;
        $limit = $this->limitcheck($request->limit);
        $offset =  $this->offsetcheck($limit, $request->offset);
        try{
            //$GetFarmerTrainingAll = Organic::select('id as organic_id','organic_no', 'organic_name')->where('is_dell', '=', 0)->get();
            //$GetFarmerTrainingAll = Organic::all()->where('is_dell', '=', 0);
            $GetFarmerTrainingAll = DB::table('organics')
                    ->select('organics.id',
                             'organics.organic_no',
                             'organics.organic_name',
                             'organics.uom',
                             'organics.organic_amount', 
                             'organics.organic_type', 
                             'organics.farmer_no', 
                             'organics.farmer_signature',
                             'organics.organic_photo',
                             'organics.status', 
                             'organics.verified_by',  
                             'organics.created_by as ff_no',
                             'organics.is_dell', 
                             'organics.deleted_by', 
                             'organics.created_at', 
                             'organics.updated_at',
                             'farmers.name as farmer_name',
                             'field_facilitators.name as ff_name')
                    ->leftjoin('farmers', 'farmers.farmer_no', '=', 'organics.farmer_no')
                    ->leftjoin('field_facilitators', 'field_facilitators.ff_no', '=', 'organics.created_by')
                    ->leftjoin('users', 'users.email', '=', 'organics.verified_by')
                    ->where('organics.is_dell','=',0)
                    ->get();
            $rslt =  $this->ResultReturn(200, 'success', $GetFarmerTrainingAll);
            return response()->json($rslt, 200);
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }
    
    public function GetOrganicAllAdmin(Request $request)
    {
        $py             = $request->program_year ?? '%';
        $type           = $request->type;
        $typegetdata    = $request->typegetdata;
        $ff             = $request->ff_no ? explode(",",$request->ff_no) : [];
        $getmu          = $request->mu_no;
        $getta          = $request->ta_no;
        $getvillage     = $request->village_no;
        
        if ($getmu) $mu='%'.$getmu.'%';
        else $mu='%%';
        
        if ($getta) $ta='%'.$getta.'%';
        else $ta='%%';
        
        if ($getvillage) $village='%'.$getvillage.'%';
        else $village='%%';
        
        if ($type) {
            $type = $type == 'Pupuk'  ? 'PP' : 'PT';
        } else {
            $type = '%';
        }
        
        $pyOrganicNo = 'ORG-'. $type . '-'. $py . '%';
        
        try{
            $datas = Organic::
                leftjoin('farmers', 'farmers.farmer_no', '=', 'organics.farmer_no')
                ->leftjoin('field_facilitators', 'field_facilitators.ff_no', '=', 'organics.created_by')
                ->leftjoin('users', 'users.email', '=', 'organics.verified_by')
                ->select('organics.id',
                         'organics.organic_no',
                         'organics.organic_name',
                         'organics.uom',
                         'organics.organic_amount', 
                         'organics.organic_type', 
                         'organics.farmer_no', 
                         'organics.farmer_signature',
                         'organics.organic_photo',
                         'organics.status', 
                         'organics.verified_by',  
                         'organics.created_by as ff_no',
                         'organics.is_dell', 
                         'organics.deleted_by', 
                         'organics.created_at', 
                         'organics.updated_at',
                         'farmers.name as farmer_name',
                         'field_facilitators.name as ff_name')
                ->where([
                    ['organics.is_dell','=',0],
                    ['organics.organic_no', 'LIKE', $pyOrganicNo],
                    ['field_facilitators.mu_no', 'LIKE', $mu],
                    ['field_facilitators.target_area', 'LIKE', $ta],
                    ['field_facilitators.village', 'LIKE', $village],
                ]);
                
            if (count($ff) > 0) {
                $datas = $datas->whereIn('organics.created_by', $ff);
            }
            
            $datas = $datas->orderBy('organics.created_at', 'DESC');
            
            $rslt =  $this->ResultReturn(200, 'success', $datas->get());
            return response()->json($rslt, 200);
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }
    
    public function GetOrganicFF(Request $request){
        $ff_no = $request->ff_no;
        try{
                $GetPH = DB::table('organics')
                    ->select('organics.id','organics.organic_no',
                    'organics.organic_name','organics.uom','organics.organic_amount', 
                    'organics.organic_type', 'organics.farmer_no', 'organics.farmer_signature',
                    'organics.organic_photo',b'organics.status', 'organics.verified_by',  
                    'organics.created_by as ff_no','organics.is_dell', 'organics.deleted_by', 'organics.created_at', 'organics.updated_at')
                    ->leftjoin('farmers', 'farmers.farmer_no', '=', 'organics.farmer_no')
                    ->leftjoin('field_facilitators', 'field_facilitators.ff_no', '=', 'organics.verified_by')
                    ->where('organics.is_dell','=',0)
                    ->where('organics.created_by','=',$ff_no)
                    ->get();

                if(count($GetPH)!=0){ 
                    $count = DB::table('organics')
                        ->leftjoin('farmers', 'farmers.farmer_no', '=', 'organics.farmer_no')
                        ->leftjoin('field_facilitators', 'field_facilitators.ff_no', '=', 'organics.created_by')
                        ->where('organics.is_dell','=',0)
                        ->where('organics.created_by','=',$ff_no)
                        ->count();
                    
                    $data = ['count'=>$count, 'data'=>$GetPH];
                    $rslt =  $this->ResultReturn(200, 'success', $data);
                    return response()->json($rslt, 200); 
                }else{
                    $rslt =  $this->ResultReturn(404, 'doesnt match data', 'doesnt match data');
                    return response()->json($rslt, 404);
                }
            // var_dump(count($GetLahanNotComplete));
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }

    public function AddOrganic(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'organic_no' => 'required|unique:organics',
                'organic_name' => 'required',
                'uom' => 'required|max:255',
                'organic_amount' => 'required|max:255',
            ]);

            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }

            $day = Carbon::now()->format('d');
            $month = Carbon::now()->format('m');
            $year = Carbon::now()->format('Y');

            //$organic_no = Organic::Maxno();
            //$farmer_no = $request->farmer_no;
            
            //$organic_amount = Organic::select('counter_hole_standard')->where('farmer_no', '=', $farmer_no)->first();

            Organic::create([
                'organic_no' => $request->organic_no,
                'organic_name' => $request->organic_name,
                'uom' => $request->uom,
                'organic_amount' => $request->organic_amount,
                'organic_type' => $request->organic_type,
                'farmer_no' => $request->farmer_no,
                'farmer_signature' => $request->farmer_signature,
                'organic_photo' => $request->organic_photo,
                'status' => 0,
                'created_by' => $request->created_by,
                'verified_by' => '-',
                'is_dell' => 0,
                'deleted_by' => '-',
        
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ]);
                
            // Log Record
            $this->createLogs([
                'status' => 'Created',
                'organic_no' => $request->organic_no
            ]);

            $rslt =  $this->ResultReturn(200, 'success', 'success');
                return response()->json($rslt, 200); 
            }catch (\Exception $ex){
                return response()->json($ex);
            }
    }

    public function UpdateOrganic(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'organic_name' => 'required',
                'uom' => 'required|max:255',
                'organic_amount' => 'required|max:255',     
            ]);

            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }

            
                
            // Log Record
            $this->createLogs([
                'status' => 'Updated',
                'organic_no' => $request->organic_no,
            ]);
            
            $day = Carbon::now()->format('d');
            $month = Carbon::now()->format('m');
            $year = Carbon::now()->format('Y');

            Organic::where('organic_no', '=', $request->organic_no)->update
            ([
                'organic_name' => $request->organic_name,
                'uom' => $request->uom,
                'organic_amount' => $request->organic_amount,
                'organic_type' => $request->organic_type,
                'farmer_no' => $request->farmer_no,
                'farmer_signature' => $request->farmer_signature,
                'organic_photo' => $request->organic_photo,
                'created_by' => $request->created_by,
                'verified_by' => $request->verified_by,
                'is_dell' => 0,
                'status' => 0,
        
                'updated_at'=>Carbon::now(),
            ]);
            

            $rslt =  $this->ResultReturn(200, 'success', 'success');
                return response()->json($rslt, 200); 
            }catch (\Exception $ex){
                return response()->json($ex);
            }
    }
    
    public function ValidateOrganic(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'organic_no' => 'required',
                'verified_by' => 'required',
            ]);

            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }  
            
            $form_no_old = $request->organic_no;
            $organics = DB::table('organics')->where('organic_no','=',$form_no_old)->first();
            
            if($organics){

                Organic::where('organic_no', '=', $form_no_old)
                ->update([    
                    'updated_at'=>Carbon::now(),
                    'verified_by' => $request->verified_by,    
                    'status' => 1
                ]);
                
                // Log Record
                $this->createLogs([
                    'status' => 'Verified',
                    'organic_no' => $form_no_old
                ]);
    
                $rslt =  $this->ResultReturn(200, 'success', 'success');
                return response()->json($rslt, 200); 
            }else{
                $rslt =  $this->ResultReturn(400, 'doesnt match data', 'doesnt match data');
                return response()->json($rslt, 400);
            }

            
        }catch (\Exception $ex){
            $rslt =  $this->ResultReturn(400, 'gagal',$ex);
            return response()->json($rslt, 400);
        }
    }
    
    public function UnvalidateOrganic(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'organic_no' => 'required',
                'verified_by' => 'required',
            ]);

            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }  
            
            $form_no_old = $request->organic_no;
            $organics = DB::table('organics')->where('organic_no','=',$form_no_old)->first();
            
            if($organics){

                Organic::where('organic_no', '=', $form_no_old)
                ->update([    
                    'updated_at'=>Carbon::now(),
                    'verified_by' => '-',    
                    'status' => 0
                ]);
                
                // Log Record
                $this->createLogs([
                    'status' => 'Unverified',
                    'organic_no' => $form_no_old
                ]);
    
                $rslt =  $this->ResultReturn(200, 'success', 'success');
                return response()->json($rslt, 200); 
            }else{
                $rslt =  $this->ResultReturn(400, 'doesnt match data', 'doesnt match data');
                return response()->json($rslt, 400);
            }

            
        }catch (\Exception $ex){
            $rslt =  $this->ResultReturn(400, 'gagal',$ex);
            return response()->json($rslt, 400);
        }
    }

    public function SoftDeleteOrganic(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [    
                'id' => 'required|exists:organics,id',
                'deleted_by' =>'required|exists:employee,nik'
            ]);

            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
            Organic::where('id', '=', $request->id)
                    ->update
                    ([
                        'deleted_by' => $request->user_id,
                        'is_dell' => 1
                    ]);
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200); 
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }

    public function DeleteOrganic(Request $request)
    {
        $validator = Validator::make($request->all(), [    
            'organic_no' => 'required|exists:organics,organic_no'
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        }
        $data = Organic::where('organic_no', $request->organic_no)->first();
        if ($data) {
            // Log Record
            $this->createLogs([
                'status' => 'Deleted',
                'organic_no' => $data->organic_no
            ]);
            // Delete Data
            $data->delete();
            // Response Success
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200); 
        } else {
            // Response Not Found
            $rslt =  $this->ResultReturn(404, 'not found', 'Organic data not found.');
            return response()->json($rslt, 404);
        }
    }
    
    public function ExportMaterialOrganic(Request $req) {
        // validator {
        $validator = Validator::make($req->all(), [
            'token' => 'required',
            'program_year' => 'required',
            'land_program' => 'required|in:Petani,Umum',
            'organic_type' => 'required|in:Pupuk,Pestisida'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->first(), 400);
        }
        // end: validator }
        // authenticate {
        $token = $req->token;
        $user = Auth::guard('api')->authenticate($token);
        if (!$user) {
            return response()->json('Invalid token!', 401);
        } 
        // end: auth }
        // get material data 
        $type_code = $req->organic_type == 'Pupuk' ? 'PP' : 'PT';
        $filter = [
            ['organics.organic_no', "LIKE", "ORG-$type_code-$req->program_year-%"],
            'organics.organic_name' => $req->organic_type
        ];
        $datas = DB::table('organics')
            ->join('farmers', 'farmers.farmer_no', 'organics.farmer_no')
            ->join('managementunits', 'managementunits.mu_no', 'farmers.mu_no')
            ->join('target_areas', 'target_areas.area_code', 'farmers.target_area')
            ->join('desas', 'desas.kode_desa', 'farmers.village')
            ->join('field_facilitators', 'field_facilitators.ff_no', 'farmers.user_id')
            ->join('employees', 'employees.nik', 'field_facilitators.fc_no')
            ->leftJoin('users', 'users.email', 'organics.verified_by')
            ->select(
                'organics.*',
                'farmers.name as farmer_name',
                'managementunits.name as mu_name',
                'target_areas.name as ta_name',
                'desas.name as village_name',
                'field_facilitators.name as ff_name',
                'farmers.name as farmer_name',
                'employees.name as fc_name',
                'users.name as verified_by_name'
            )
            ->where($filter);
        
        if ($req->ff) {
            $ff = explode(",", $req->ff);
            $datas = $datas->whereIn('field_facilitators.ff_no', $ff);
        }
        
        $datas = $datas->get();
        
        $rslt = [
            'program_year' => $req->program_year,
            'organic_type' => $req->organic_type,
            'filter' =>$filter,
            'user' => $user,
            'datas' => $datas
        ];    
        // return response()->json($rslt, 200);
        return view('material_organic.petani.export', $rslt);
    }
    
    // Create Logs
    private function createLogs($logData) {
        // get main data
        $main = Organic::where('organic_no', $logData['organic_no'])->first();
        
        // get Petani Data
        if (isset($main->farmer_no)) {
            $farmer = Farmer::where('farmer_no', $main->farmer_no)->first();
        }
        
        // get ff data
        if(isset($main->created_by)) {
            $ff = FieldFacilitator::where('ff_no', $main->created_by)->first();
        }
        // get fc data
        if (isset($ff->fc_no)) {
            $fc = Employee::where('nik', $ff->fc_no)->first();
        }
        // user auth data
        $user = Auth::user();
        
        // set message
        $message =  $logData['status'] . ' ' . 
                    ($main['organic_no'] ?? '-') . 
                    '[petani = ' . 
                    ($farmer['farmer_no'] ?? '-') . '_' . ($farmer['name'] ?? '-') . '_' . ($farmer['nickname'] ?? '-') .
                    ', ff = ' . 
                    ($ff->ff_no ?? '-') . '_' . ($ff->name ?? '-') .
                    ', fc = ' . 
                    ($fc->name ?? '-') .
                    '] ' .
                    'by ' .
                    ($user['email'] ?? '-');
                    
        $log = Log::channel('material_organics');
        
        if ($logData['status'] == 'Updated' || $logData['status'] == 'Created') {
            $log->notice($message);
        } else if ($logData['status'] == 'Soft Deleted') {
            $log->warning($message);
        } else if ($logData['status'] == 'Deleted') {
            $log->alert($message);
        } else {
            $log->info($message);
        }
        
        
        
        return true;
    }
}
