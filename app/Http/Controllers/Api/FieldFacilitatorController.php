<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use DB;
use Carbon\Carbon;
use App\FieldFacilitator;
use App\Employee;
use App\MainPivot;
use App\FFWorkingArea;

class FieldFacilitatorController extends Controller
{

    /**
     * @SWG\Get(
     *   path="/api/GetFieldFacilitatorAllWeb",
     *   tags={"FieldFacilitator"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Field Facilitator All Web Admin",
     *   operationId="GetFieldFacilitatorAllWeb",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="fc_no",in="query", type="string"),
     * )
     */
    public function GetFieldFacilitatorAllWeb(Request $request){
        $getAll = false;
        $getfcno = $request->fc_no;
        if($getfcno ){$fc_no = (explode(",",$getfcno));}
        else{ $fc_no='%%';$getAll = true;}
        try{
            // first query
            $getffall= DB::table('field_facilitators')
                ->leftjoin('managementunits', 'managementunits.mu_no', '=', 'field_facilitators.mu_no')
                ->leftjoin('target_areas', 'target_areas.area_code', '=', 'field_facilitators.target_area')
                ->leftjoin('desas', 'desas.kode_desa', '=', 'field_facilitators.working_area')
                ->leftjoin('main_pivots', 'main_pivots.key2', '=', 'field_facilitators.ff_no')
                ->leftjoin('employees', 'employees.nik', '=', 'main_pivots.key1')
                ->leftJoin('users', 'users.employee_no', 'field_facilitators.ff_no')
                ->select(
                    'field_facilitators.id', 
                    'field_facilitators.ff_no',
                    'main_pivots.key1 as fc_no',
                    'employees.name as namaFC', 
                    'field_facilitators.name as namaFF', 
                    'field_facilitators.gender', 
                    'field_facilitators.address', 
                    'field_facilitators.village',
                    'field_facilitators.kecamatan', 
                    'field_facilitators.mu_no',
                    'field_facilitators.target_area',
                    'field_facilitators.working_area',
                    'field_facilitators.active',
                    'users.email',
                    'managementunits.name as mu_name',
                    'target_areas.name as ta_name',
                    'desas.name as village_name'
                )
                ->where([
                    'main_pivots.type' => 'fc_ff'    
                ])->groupBy('field_facilitators.ff_no'); 
            if ($request->program_year) if ($request->program_year != 'Semua') {
                $getffall = $getffall->where([
                    ['main_pivots.program_year', 'like', "%$request->program_year%"]
                ]);
            }
            
            // Set new structure
            // if ($request->program_year) {
            //     $ffs = FieldFacilitator::get();
            //     foreach ($ffs as $ff) {
            //         FFWorkingArea::where('ff_no', $ff->ff_no)->delete();
            //         FFWorkingArea::create([
            //                 'ff_no' => $ff->ff_no,
            //                 'mu_no' => $ff->mu_no,
            //                 'area_code' => $ff->target_area,
            //                 'kode_desa' => $ff->working_area,
            //                 'program_year' => '2021,2022'
            //             ]);
            //     }
            // }
           
            // second query
            if($getAll == false){   
                // var_dump($fc_no);
                $getffall= $getffall
                    ->wherein('main_pivots.key1', $fc_no);
            }
            
            // third query
            $getffall = $getffall
                    ->orderBy('field_facilitators.created_at', 'DESC');
            
            $getffall = $getffall->get();
            if ($request->program_year) if ($request->program_year != 'Semua') {
                foreach ($getffall as $val) {
                    $working_area = DB::table('ff_working_areas')
                    ->select(
                        'managementunits.name as mu_name',
                        'target_areas.name as ta_name',
                        'desas.name as village_name'
                    )
                    ->join('managementunits', 'managementunits.mu_no', 'ff_working_areas.mu_no')
                    ->join('target_areas', 'target_areas.area_code', 'ff_working_areas.area_code')
                    ->join('desas', 'desas.kode_desa', 'ff_working_areas.kode_desa')
                    ->where([
                        'ff_working_areas.ff_no' => $val->ff_no,
                        ['ff_working_areas.program_year', 'like', "%$request->program_year%"],
                    ])->first();
                    if ($working_area) {
                        $val->mu_name = $working_area->mu_name;
                        $val->ta_name = $working_area->ta_name;
                        $val->village_name = $working_area->village_name;
                    }
                }
            }
            // var_dump($GetFieldFacilitator);
            if(count($getffall) != 0) {
                $data = ['count'=>count($getffall), 'data'=>$getffall];
                $rslt =  $this->ResultReturn(200, 'success', $data);
                return response()->json($rslt, 200);  
            }
            else{
                $rslt =  $this->ResultReturn(404, 'doesnt match data', 'doesnt match data');
                return response()->json($rslt, 404);
            }
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }
    
    /**
     * @SWG\Get(
     *   path="/api/GetFieldFacilitatorAll",
     *   tags={"FieldFacilitator"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Field Facilitator All Admin",
     *   operationId="GetFieldFacilitatorAll",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="name",in="query", type="string"),
     *      @SWG\Parameter(name="fc_no",in="query", type="string"),
     * )
     */
    public function GetFieldFacilitatorAll(Request $request){
        $getname = $request->name;
        if($getname){$name='%'.$getname.'%';}
        else{$name='%%';}

        $getAll = false;
        $getfcno = $request->fc_no;
        if($getfcno ){$fc_no = (explode(",",$getfcno));}
        else{ $fc_no='%%';$getAll = true;}
        try{
           
            if($getAll == true){
                // var_dump($fc_no);
                $GetFieldFacilitator = FieldFacilitator::select('id', 'ff_no','fc_no', 'name', 'gender', 'ktp_no','address', 'mu_no', 'target_area', 'working_area','active', 'created_at')->where('fc_no', 'Like', $fc_no)->where('name', 'Like', $name)->orderBy('name', 'ASC')->get();           
            }else{
                // var_dump($fc_no);
                $GetFieldFacilitator = FieldFacilitator::select('id', 'ff_no','fc_no', 'name', 'gender', 'ktp_no','address','mu_no', 'target_area', 'working_area','active', 'created_at')->wherein('fc_no', $fc_no)->where('name', 'Like', $name)->orderBy('name', 'ASC')->get();
            }
            // var_dump($GetFieldFacilitator);
            if(count($GetFieldFacilitator)!=0){
                if($getAll == true){
                    $count = FieldFacilitator::where('fc_no', 'Like', $fc_no)->where('name', 'Like', $name)->count();
                }else{
                    $count = FieldFacilitator::wherein('fc_no', $fc_no)->where('name', 'Like', $name)->count();
                }
                $data = ['count'=>$count, 'data'=>$GetFieldFacilitator];
                $rslt =  $this->ResultReturn(200, 'success', $data);
                return response()->json($rslt, 200);  
            }
            else{
                $rslt =  $this->ResultReturn(404, 'doesnt match data', 'doesnt match data');
                return response()->json($rslt, 404);
            }
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }

    /**
     * @SWG\Get(
     *   path="/api/GetFieldFacilitator",
     *   tags={"FieldFacilitator"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Field Facilitator",
     *   operationId="GetFieldFacilitator",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="name",in="query", type="string"),
     *      @SWG\Parameter(name="limit",in="query", type="integer"),
     *      @SWG\Parameter(name="offset",in="query", type="integer"),
     * )
     */
    public function GetFieldFacilitator(Request $request){
        $limit = $this->limitcheck($request->limit);
        $offset =  $this->offsetcheck($limit, $request->offset);
        $getname = $request->name;
        if($getname){$name='%'.$getname.'%';}
        else{$name='%%';}
        try{
            $GetFieldFacilitator = FieldFacilitator::select('id', 'ff_no', 'name', 'gender', 'ktp_no','address','active', 'created_at')->where('name', 'Like', $name)->orderBy('name', 'ASC')->limit($limit)->offset($offset)->get();
            // var_dump($GetFieldFacilitator);
            if(count($GetFieldFacilitator)!=0){
                $count = FieldFacilitator::count();
                $data = ['count'=>$count, 'data'=>$GetFieldFacilitator];
                $rslt =  $this->ResultReturn(200, 'success', $data);
                return response()->json($rslt, 200);  
            }
            else{
                $rslt =  $this->ResultReturn(404, 'doesnt match data', 'doesnt match data');
                return response()->json($rslt, 404);
            }
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }
    /**
     * @SWG\Get(
     *   path="/api/GetFieldFacilitatorDetail",
     *   tags={"FieldFacilitator"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Field Facilitator Detail",
     *   operationId="GetFieldFacilitatorDetail",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="id",in="query", type="string")
     * )
     */
    public function GetFieldFacilitatorDetail(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'id' => 'required'
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
            $GetFieldFacilitatorDetail = 
            DB::table('field_facilitators')
            ->select('field_facilitators.id','field_facilitators.ff_no','field_facilitators.fc_no',
            'field_facilitators.name','field_facilitators.birthday','field_facilitators.religion',
            'field_facilitators.gender','field_facilitators.marrital','field_facilitators.join_date',
            'field_facilitators.ktp_no','field_facilitators.phone','field_facilitators.address',
            'field_facilitators.village','field_facilitators.kecamatan','field_facilitators.city',
            'field_facilitators.province','field_facilitators.post_code','field_facilitators.mu_no',
            'field_facilitators.working_area','field_facilitators.target_area','field_facilitators.bank_account',
            'field_facilitators.bank_branch','field_facilitators.bank_name','field_facilitators.ff_photo',
            'field_facilitators.ff_photo_path','field_facilitators.active','field_facilitators.user_id',
            'desas.name as namaWorkingArea','target_areas.name as namaTA','managementunits.name as namaMU',
            'employees.name as fc_name')
            ->leftjoin('desas', 'desas.kode_desa', '=', 'field_facilitators.working_area')
            ->leftjoin('target_areas', 'target_areas.area_code', '=', 'field_facilitators.target_area')
            ->leftjoin('managementunits', 'managementunits.mu_no', '=', 'field_facilitators.mu_no')
            ->leftJoin('employees', 'employees.nik', 'field_facilitators.fc_no');
            
            if (isset($request->ff_no)) {
                $GetFieldFacilitatorDetail = $GetFieldFacilitatorDetail
                    ->where('field_facilitators.ff_no', '=', $request->ff_no);
            } else {
                $GetFieldFacilitatorDetail = $GetFieldFacilitatorDetail
                    ->where('field_facilitators.id', '=', $request->id);
            }
            $GetFieldFacilitatorDetail = $GetFieldFacilitatorDetail->first();
            $GetFieldFacilitatorDetail->fc_ff = MainPivot::where([
                    'type' => 'fc_ff',
                    'key2' => $GetFieldFacilitatorDetail->ff_no
                ])->get();
            $GetFieldFacilitatorDetail->working_areas = FFWorkingArea::where(['ff_no' => $GetFieldFacilitatorDetail->ff_no])->get();
            if($GetFieldFacilitatorDetail){
                $rslt =  $this->ResultReturn(200, 'success', $GetFieldFacilitatorDetail);
                return response()->json($rslt, 200);  
            }
            else{
                $rslt =  $this->ResultReturn(404, 'doesnt match data', 'doesnt match data');
                return response()->json($rslt, 404);
            } 
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }
    
    /**
     * @SWG\Post(
     *   path="/api/AddFieldFacilitator",
	 *   tags={"FieldFacilitator"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Add Field Facilitator",
     *   operationId="AddFieldFacilitator",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Add Field Facilitator",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="fc_no", type="string", example="11_111"),
     *              @SWG\Property(property="name", type="string", example="Mangga"),
     *              @SWG\Property(property="birthday", type="date", example="1990-01-30"),
     *              @SWG\Property(property="religion", type="string", example="islam"),
     *              @SWG\Property(property="gender", type="string", example="male/female"),
     *              @SWG\Property(property="ktp_no", type="string", example="33101700020001"),
     *              @SWG\Property(property="address", type="string", example="Jl Cemara 11"),
     *              @SWG\Property(property="village", type="string", example="32.04.30.01"),
     *              @SWG\Property(property="kecamatan", type="string", example="32.04.30.01"),
     *              @SWG\Property(property="city", type="string", example="32.04"),
     *              @SWG\Property(property="province", type="string", example="JT"),
     *              @SWG\Property(property="working_area", type="string", example="390302"),
     *              @SWG\Property(property="mu_no", type="string", example="023"),
     *              @SWG\Property(property="target_area", type="string", example="120200100000"),
     *              @SWG\Property(property="active", type="string", example="1"),
     *              @SWG\Property(property="user_id", type="string", example="020"),
     *              @SWG\Property(property="marrital", type="string", example="Nullable"),
     *              @SWG\Property(property="join_date", type="date", example="Nullable"),
     *              @SWG\Property(property="phone", type="string", example="Nullable"),
     *              @SWG\Property(property="post_code", type="string", example="Nullable"),
     *              @SWG\Property(property="bank_account", type="string", example="Nullable"),
     *              @SWG\Property(property="bank_branch", type="date", example="Nullable"),
     *              @SWG\Property(property="bank_name", type="string", example="Nullable"),
     *              @SWG\Property(property="ff_photo", type="string", example="Nullable"),
     *              @SWG\Property(property="ff_photo_path", type="string", example="Nullable")
     *          ),
     *      )
     * )
     *
     */
    public function AddFieldFacilitator(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'fc_no' => 'required|max:255',
                'name' => 'required|max:255',
                'birthday' => 'required|max:255',
                'religion' => 'required|max:255',
                'gender' => 'required|max:255',
                'ktp_no' => 'required|max:255|unique:field_facilitators,ktp_no',
                'address' => 'required|max:255',
                'village' => 'required|max:255',
                'kecamatan' => 'required',
                'city' => 'required',
                'province' => 'required',
                'working_area' => 'required',
                'mu_no' => 'required',
                'target_area' => 'required',
                'active' => 'required',
                'user_id' => 'required'
            ]);
    
            if($validator->fails()){
                return response()->json($validator->errors()->first(), 400);
            }

            $getLastIdFieldFacilitator = FieldFacilitator::where('ff_no', 'Like', 'F%')
                                        ->orderBy('ff_no','desc')->first(); 

            $getYearNow = Carbon::now()->format('Y');
            if($getLastIdFieldFacilitator){
                $ff_no = 'FF'.str_pad(((int)substr($getLastIdFieldFacilitator->ff_no,-8) + 1), 8, '0', STR_PAD_LEFT);
            }else{
                $ff_no = 'FF00000001';
            }
            
    
            $createFF = FieldFacilitator::create([
                'ff_no' => $ff_no,
                'fc_no' => $request->fc_no,
                'name' => $request->name,
                'birthday' => $request->birthday,
                'religion' => $request->religion,
                'ktp_no' => $request->ktp_no,
                'address' => $request->address,
                'village' => $request->village,
                'kecamatan' => $request->kecamatan,
                'city' => $request->city,
                'province' => $request->province,
                'working_area' => $request->working_area,
                'mu_no' => $request->mu_no,
                'target_area' => $request->target_area,
                'active' => $request->active,
                'user_id' => $request->user_id,
                

                'marrital' => $this->ReplaceNull($request->marrital, 'string'),
                'join_date' => $this->ReplaceNull($request->join_date, 'date'),
                'phone' => $this->ReplaceNull($request->phone, 'string'),
                'post_code' => $this->ReplaceNull($request->post_code, 'string'),
                'bank_account' => $this->ReplaceNull($request->bank_account, 'string'),
                'bank_branch' => $this->ReplaceNull($request->bank_branch, 'string'),
                'bank_name' => $this->ReplaceNull($request->bank_name, 'string'),
                'ff_photo' => $this->ReplaceNull($request->ff_photo, 'string'),
                'ff_photo_path' => $this->ReplaceNull($request->ff_photo_path, 'string'),

                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now()
            ]);
            MainPivot::where([
                'type' => 'fc_ff',
                'key2' => $ff_no
            ])->delete();
            MainPivot::create([
                'type' => 'fc_ff',
                'key1' => $request->fc_no,
                'key2' => $ff_no,
                'program_year' => $request->program_year,
                'active' => $request->active
            ]);
            FFWorkingArea::create([
                'ff_no' => $ff_no,
                'mu_no' => $request->mu_no,
                'area_code' => $request->target_area,
                'kode_desa' => $request->working_area,
                'program_year' => $request->program_year
            ]);
            $rslt =  $this->ResultReturn(200, 'success', [
                    'message' => 'success',
                    'ff_id' => $createFF->id,
                    'ff_no' => $ff_no
                ]);
            return response()->json($rslt, 200);
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }

    /**
     * @SWG\Post(
     *   path="/api/UpdateFieldFacilitator",
	 *   tags={"FieldFacilitator"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Update Field Facilitator",
     *   operationId="UpdateFieldFacilitator",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Update Field Facilitator",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="id", type="integer", example=1),
     *              @SWG\Property(property="fc_no", type="string", example="11_111"),
     *              @SWG\Property(property="name", type="string", example="Mangga"),
     *              @SWG\Property(property="birthday", type="date", example="1990-01-30"),
     *              @SWG\Property(property="religion", type="string", example="islam"),
     *              @SWG\Property(property="gender", type="string", example="male/female"),
     *              @SWG\Property(property="ktp_no", type="string", example="33101700020001"),
     *              @SWG\Property(property="address", type="string", example="Jl Cemara 11"),
     *              @SWG\Property(property="village", type="string", example="32.04.30.01"),
     *              @SWG\Property(property="kecamatan", type="string", example="32.04.30.01"),
     *              @SWG\Property(property="city", type="string", example="32.04"),
     *              @SWG\Property(property="province", type="string", example="JT"),
     *              @SWG\Property(property="working_area", type="string", example="390302"),
     *              @SWG\Property(property="mu_no", type="string", example="023"),
     *              @SWG\Property(property="target_area", type="string", example="120200100000"),
     *              @SWG\Property(property="active", type="string", example="1"),
     *              @SWG\Property(property="user_id", type="string", example="023"),
     *              @SWG\Property(property="marrital", type="string", example="Nullable"),
     *              @SWG\Property(property="join_date", type="date", example="Nullable"),
     *              @SWG\Property(property="phone", type="string", example="Nullable"),
     *              @SWG\Property(property="post_code", type="string", example="Nullable"),
     *              @SWG\Property(property="bank_account", type="string", example="Nullable"),
     *              @SWG\Property(property="bank_branch", type="date", example="Nullable"),
     *              @SWG\Property(property="bank_name", type="string", example="Nullable"),
     *              @SWG\Property(property="ff_photo", type="string", example="Nullable"),
     *              @SWG\Property(property="ff_photo_path", type="string", example="Nullable")
     *          ),
     *      )
     * )
     *
     */
    public function UpdateFieldFacilitator(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:field_facilitators,id',
            'ff_no' => 'required|exists:field_facilitators,ff_no',
            'name' => 'required|max:255',
            'ktp_no' => 'required|max:255',
            'active' => 'required',
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        }

        FieldFacilitator::where('id', '=', $request->id)
        ->update([
            'active' => $request->active,
            'address' => $request->address,
            'bank_account' => $this->ReplaceNull($request->bank_account, 'string'),
            'bank_branch' => $this->ReplaceNull($request->bank_branch, 'string'),
            'bank_name' => $this->ReplaceNull($request->bank_name, 'string'),
            'birthday' => $request->birthday,
            'city' => $request->city,
            'gender' => $request->gender,
            'join_date' => $this->ReplaceNull($request->join_date, 'date'),
            'kecamatan' => $request->kecamatan,
            'ktp_no' => $request->ktp_no,
            'marrital' => $this->ReplaceNull($request->marrital, 'string'),
            'name' => $request->name,
            'phone' => $this->ReplaceNull($request->phone, 'string'),
            'post_code' => $this->ReplaceNull($request->post_code, 'string'),
            'province' => $request->province,
            'religion' => $request->religion,
            'village' => $request->village,
            'updated_at'=>Carbon::now()
        ]);
        // fc_ff
        foreach ($request->fc_ff as $fc_ff) {
            $data = [
                'type' => 'fc_ff',
                'key1' => $fc_ff['key1'],
                'key2' => $fc_ff['key2'],
                'program_year' => $fc_ff['program_year'], 
                'active' => 1,
                'updated_at'=>Carbon::now()
            ];
            unset($data['id']);
            if ($fc_ff['id']) {
                MainPivot::find($fc_ff['id'])->update($data);
            } else MainPivot::create($data);
        }
        // working_areas
        foreach ($request->working_areas as $working_area) {
            $data = [
                'ff_no' => $working_area['ff_no'],  
                'mu_no' => $working_area['mu_no'],
                'area_code' => $working_area['target_area'],
                'kode_desa' => $working_area['working_area'],
                'program_year' => $working_area['program_year'],
                'updated_at'=>Carbon::now()
            ];
            if ($working_area['id']) {
                FFWorkingArea::find($working_area['id'])->update($data);
            } else FFWorkingArea::create($data);
        }
        $rslt =  $this->ResultReturn(200, 'success', 'success');
        return response()->json($rslt, 200);
    }

    /**
     * @SWG\Post(
     *   path="/api/DeleteFieldFacilitator",
	 *   tags={"FieldFacilitator"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Delete Field Facilitator",
     *   operationId="DeleteFieldFacilitator",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Delete Field Facilitator",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="id", type="integer", example=1)
     *          ),
     *      )
     * )
     *
     */
    public function DeleteFieldFacilitator(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'id' => 'required'
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
    
            DB::table('field_facilitators')->where('id', $request->id)->delete();
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200);
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }
    
    // Nonactivate FF
    public function NonactivateFieldFacilitator(Request $req) {
            $validator = Validator::make($req->all(), [
                'ff_no' => 'required|exists:field_facilitators,ff_no',
                'active' => 'required'
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
            
            $ff = FieldFacilitator::where('ff_no', $req->ff_no)->first();
            if ($ff) {
                // Create Logs
                $this->createLog([
                    'ff_no' => $req->ff_no,
                    'status' => $req->active ? 'Activated' : 'Nonactivated'
                ]);
                // Nonactivate
                $ff->update(['active' => $req->active]);
                
                $rslt =  $this->ResultReturn(200, 'success', 'success');
                return response()->json($rslt, 200);
            } else {
                $rslt =  $this->ResultReturn(404, 'not found', 'FF Not Found');
                return response()->json($rslt, 404);
            }
        
    }
    
    // Change FC
    public function ChangeFCFieldFacilitator(Request $req) {
        $validator = Validator::make($req->all(), [
            'ff_no' => 'required|exists:field_facilitators,ff_no',
            'fc_no' => 'required|exists:employees,nik'
        ]);

        if($validator->fails()) return response()->json($validator->errors()->first(), 400);
        else {
            $data = FieldFacilitator::where('ff_no', $req->ff_no)->first();
            $fcOld = Employee::where('nik', $data->fc_no)->first();
            if ($data->fc_no != $req->fc_no) {
                $update = FieldFacilitator::where('ff_no', $req->ff_no)->update(['fc_no' => $req->fc_no]);
                $fcNew = Employee::where('nik', $req->fc_no)->first();
                if ($update) {
                    // create logs
                    $this->createLog([
                        'ff_no' => $req->ff_no,
                        'message' => " (" . ($fcOld->name ?? '?') . " => " . ($fcNew->name ?? '?') . ") in ",
                        'status' => 'Change FC'
                    ]);
                    return response()->json('Change FF success!', 200);
                }
                else return response()->json('Change FF failed!', 400);
            } else return response()->json('Nothing Changed!', 200);
        }
    }
    
    // Create Logs
    private function createLog($logData) {
        // get main data
        $ff = DB::table('field_facilitators')->where('ff_no', $logData['ff_no'])->first();
        // get fc data
        if (isset($ff->fc_no)) {
            $fc = DB::table('employees')->where('nik', $ff->fc_no)->first();
        }
        // user auth data
        $user = Auth::user();
        
        // set message
        $message =  $logData['status'] . ' ' . 
                    ($ff->ff_no ?? '-') . 
                    ($logData['message'] ?? '') .
                    '[ff name = ' . 
                    ($ff->name ?? '-') .
                    ', fc = ' . 
                    ($fc->name ?? '-') .
                    '] ' .
                    'by ' .
                    ($user->email ?? '-');
                    
        $log = Log::channel('field_facilitators');
        
        if ($logData['status'] == 'Updated' || $logData['status'] == 'Created' || $logData['status'] == 'Verification') {
            $log->notice($message);
        } else if ($logData['status'] == 'Soft Deleted' || $logData['status'] == 'Unverification' || $logData['status' ] == 'Nonactivated') {
            $log->warning($message);
        } else if ($logData['status'] == 'Deleted') {
            $log->alert($message);
        } else {
            $log->info($message);
        }
        
        
        
        return true;
    }
}
