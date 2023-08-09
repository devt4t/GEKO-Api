<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Desa;
use App\Employee;
use App\FarmerTraining;
use App\FarmerTrainingDetail;
use App\FieldFacilitator;
use App\Kecamatan;
use App\ManagementUnit;
use App\Organic;
use App\TargetArea;
use App\TrainingMaterial;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class FarmerTrainingController extends Controller
{
    public function GetFarmerTrainingAllAdmin(Request $request) {

        $validator = Validator::make($request->all(), [
            'typegetdata' => 'required|in:all,several',
            'program_year' => 'required'
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        }

        $typegetdata = $request->typegetdata;
        $ff = $request->ff;
        $getmu = $request->mu;
        $getta = $request->ta;
        $getvillage = $request->village;
        if($getmu){$mu='%'.$getmu.'%';}
        else{$mu='%%';}
        if($getta){$ta='%'.$getta.'%';}
        else{$ta='%%';}
        if($getvillage){$village='%'.$getvillage.'%';}
        else{$village='%%';}
        try {
            $datas = DB::table('farmer_trainings')
                ->leftjoin('field_facilitators', 'field_facilitators.ff_no', '=', 'farmer_trainings.ff_no')
                ->leftjoin('managementunits', 'managementunits.mu_no', '=', 'field_facilitators.mu_no')
                ->leftjoin('employees', 'employees.nik', '=', 'farmer_trainings.field_coordinator')
                ->leftjoin('users', 'users.email', '=', 'farmer_trainings.user_id')
                ->leftjoin('desas', 'desas.kode_desa', '=', 'farmer_trainings.village')
                ->select('farmer_trainings.id as ft_id', 
                    'farmer_trainings.training_no',
                    'farmer_trainings.training_date',
                    'employees.name as fc_name',
                    'field_facilitators.name as ff_name',
                    'managementunits.name as mu_name',
                    'farmer_trainings.program_year',
                    'desas.name as desa',
                    'farmer_trainings.absent as absensi_img',
                    'users.name as created_by')
                ->where('farmer_trainings.mu_no','like',$mu)
                ->where('farmer_trainings.target_area','like',$ta)
                ->where('farmer_trainings.village','like',$village)
                ->where('farmer_trainings.program_year', $request->program_year);
            
            if ($ff) {
                $ffdecode = (explode(",",$ff));
                $datas = $datas
                    ->wherein('farmer_trainings.ff_no',$ffdecode);
            }
            
            $datas = $datas
                ->where('is_dell', 0)
                ->orderBy('farmer_trainings.created_at', 'desc')
                ->groupBy('farmer_trainings.training_no')
                ->get();
            
            $rslt =  $this->ResultReturn(200, 'success', $datas);
            return response()->json($rslt, 200); 
            
        } catch (\Exception $ex){
            return response()->json($ex);
        }
    }
    /**
     * @SWG\Get(
     *   path="/api/GetFarmerTrainingAll",
     *   tags={"FarmerTrainings"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Farmer Trainings All",
     *   operationId="GetFarmerTrainingAll",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="village",in="query", type="string"),
     *      @SWG\Parameter(name="user_id",in="query", required=true, type="string"),
     *      @SWG\Parameter(name="limit",in="query", type="integer"),
     *      @SWG\Parameter(name="offset",in="query", type="integer"),
     * )
     */
    public function GetFarmerTrainingAll(Request $request){
        $userId = $request->user_id;
        $getvillage = $request->village;
        $limit = $this->limitcheck($request->limit);
        $offset =  $this->offsetcheck($limit, $request->offset);
        if($getvillage){$village='%'.$getvillage.'%';}
        else{$village='%%';}
        try{
            $GetFarmerTrainingAll = FarmerTraining::where('user_id', '=', $userId)->where('village', 'Like', $village)->where('is_dell', '=', 0)->orderBy('village', 'ASC')->get();
            if(count($GetFarmerTrainingAll)!=0){
                $count = FarmerTraining::where('user_id', '=', $userId)->where('is_dell', '=', 0)->count();
                $data = ['count'=>$count, 'data'=>$GetFarmerTrainingAll];
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

    public function GetFarmerTrainingAllTempDelete(Request $request){
        // $userId = $request->user_id;
        // $getvillage = $request->village;
        // $limit = $this->limitcheck($request->limit);
        // $offset =  $this->offsetcheck($limit, $request->offset);
        // if($getvillage){$village='%'.$getvillage.'%';}
        // else{$name='%%';}
        try{
            $GetFarmerTrainingAll = FarmerTraining::where('is_dell', '=', 1)->where('absent', '=', '-')->orderBy('village', 'ASC')->get();
            if(count($GetFarmerTrainingAll)!=0){
                $count = FarmerTraining::where('is_dell', '=', 1)->where('absent', '=', '-')->count();
                $data = ['count'=>$count, 'data'=>$GetFarmerTrainingAll];
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
    
    public function DetailFarmerTraining(Request $request) {
        $validator = Validator::make($request->all(), [
            'training_no' => 'required|exists:farmer_trainings,training_no',
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        }
        
        try {
            
            $data = DB::table('farmer_trainings')
                ->leftjoin('field_facilitators', 'field_facilitators.ff_no', '=', 'farmer_trainings.ff_no')
                ->leftjoin('employees', 'employees.nik', '=', 'farmer_trainings.field_coordinator')
                ->leftjoin('employee_structure', 'employee_structure.nik', '=', 'farmer_trainings.field_coordinator')
                ->leftjoin('users', 'users.email', '=', 'farmer_trainings.user_id')
                ->leftjoin('desas', 'desas.kode_desa', '=', 'farmer_trainings.village')
                ->leftjoin('target_areas', 'target_areas.area_code', '=', 'farmer_trainings.target_area')
                ->leftjoin('managementunits', 'managementunits.mu_no', '=', 'farmer_trainings.mu_no')
                ->select('farmer_trainings.id as ft_id', 
                    'farmer_trainings.training_no',
                    'farmer_trainings.training_date',
                    'employees.name as fc_name',
                    'field_facilitators.name as ff_name',
                    'employee_structure.manager_code as um_name',
                    'farmer_trainings.program_year',
                    'desas.name as desa',
                    'target_areas.name as target_area',
                    'managementunits.name as management_unit',
                    'farmer_trainings.first_material as materi_1',
                    'farmer_trainings.second_material as materi_2',
                    'farmer_trainings.absent',
                    'farmer_trainings.documentation_photo')
                ->where('farmer_trainings.training_no', $request->training_no)
                ->first();
            if ($data) {
                // set unit manager name
                $umName = DB::table('employees')->select('name')->where('nik', $data->um_name)->first();
                if ($umName) $data->um_name = $umName->name;
                
                // set list farmers
                $data->farmers = FarmerTrainingDetail::where('training_no', $request->training_no)->get();
                if(count($data->farmers) > 0) {
                    foreach($data->farmers as $fIndex => $farmer) {
                        $data->farmers[$fIndex] = DB::table('farmers')
                            ->select('farmer_no', 'name', 'nickname', 'birthday', 'gender', 'join_date', 'ktp_no as nik', 'mou_no', 'farmer_profile')
                            ->where('farmer_no', $farmer->farmer_no)
                            ->first();
                    }
                }
                
                // set materi pelatihan
                $data->materi_1 = DB::table('training_materials')->where('material_no', $data->materi_1)->first()->material_name ?? '';
                if ($data->materi_2 != '-') {
                    $data->materi_2 = DB::table('training_materials')->where('material_no', $data->materi_2)->first()->material_name ?? '';
                }
                
                // set absensi image
                $data->absent = explode(",", $data->absent);
            }
            $rslt =  $this->ResultReturn(200, 'success', $data);
            return response()->json($rslt, 200); 
        } catch (\Exception $ex){
            return response()->json($ex);
        }
    }
    
    /**
     * @SWG\Post(
     *   path="/api/AddFarmerTraining",
	 *   tags={"FarmerTrainings"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Add Farmer Training",
     *   operationId="AddFarmerTraining",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Add Farmer Training",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="training_no", type="string", example="0909090909"),
     *              @SWG\Property(property="training_date", type="date", example="2021-03-20"),
     *              @SWG\Property(property="1st_material", type="string", example="Pelatihan Pertama"),
     *              @SWG\Property(property="2nd_material", type="date", example="Pelatihan kedua"),
     *              @SWG\Property(property="organic_material", type="string", example="Pelatihan Organik"),
     *              @SWG\Property(property="program_year", type="date", example="2022"),
     *              @SWG\Property(property="absent", type="integer", example="diisi gambar ya nanti"),
     *              @SWG\Property(property="address", type="string", example="Jl Cemara No 22, Kemiri, Salatiga"),
     *              @SWG\Property(property="village", type="string", example="33.05.10.18"),
     *              @SWG\Property(property="field_coordinator", type="string", example="Nama FC"),
     *              @SWG\Property(property="ff_no", type="string", example="FF0000001"),
     *              @SWG\Property(property="mu_no", type="string", example="022"),   
     *              @SWG\Property(property="origin", type="string", example="lokal"),
     *              @SWG\Property(property="gender", type="string", example="male"),
     *              @SWG\Property(property="number_family_member", type="int", example="2"), 
     *              @SWG\Property(property="target_area", type="string", example="test"),
     *              @SWG\Property(property="status", type="int", example="1"),
     *              @SWG\Property(property="user_id", type="string", example="U0002"),
     *          ),
     *      )
     * )
     *
     */
    public function AddFarmerTraining(Request $request){
        try{
            // date_default_timezone_set("Asia/Bangkok");

            $validator = Validator::make($request->all(), [
                'training_date' => 'required',
                'first_material' => 'required|max:255',
                'second_material' => 'required|max:255',
                'program_year' => 'required',
                'absent' => 'required|image:jpg,jpeg,png',
                'absent2' => 'image:jpg,jpeg,png',
                'dokumentasi' => 'required|image:jpg,jpeg,png',
                'village' => 'required|max:255',
                'field_coordinator' => 'required|max:255',
                'ff_no' => 'required|exists:field_facilitators,ff_no|unique:farmer_trainings,ff_no',             
                'mu_no' => 'required|max:255',
                'target_area' => 'required|max:255',
                'status' => 'required|max:1',
                'user_id' => 'required|exists:users,email',
                'farmers' => 'required|array'
            ]);

            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }

            $day = Carbon::now()->format('d');
            $month = Carbon::now()->format('m');
            $year = Carbon::now()->format('Y');
            
            $training_no = FarmerTraining::Maxno();
            $time = Carbon::now();
            
            // upload photo
            $absentFileNames = [];
            if (isset($request->absent)) {
                $upload = $this->UploadPhotoExternal($request->absent, ($training_no.'_abs1'));
                array_push($absentFileNames, $upload);
            }
            if (isset($request->absent2)) {
                $upload = $this->UploadPhotoExternal($request->absent2, ($training_no.'_abs2'));
                array_push($absentFileNames, $upload);
            }
            if (isset($request->dokumentasi)) {
                $fotoDokumentasi = $this->UploadPhotoExternal($request->dokumentasi, ('documentation-photos/,'.$training_no.'_doc'));
            }
            
            // var_dump('test');
            FarmerTraining::create([
                'training_no' => $training_no,
                'training_date' => $request->training_date,
                'first_material' => $request->first_material,
                'second_material' => $request->second_material,
                'organic_material' => 'ORG22090001,ORG22090002',
                'program_year' => $request->program_year,
                'absent' => implode(',', $absentFileNames),
                'documentation_photo' => $fotoDokumentasi ?? '-',
                'mu_no' => $request->mu_no,
                'target_area' => $request->target_area,
                'village' => $request->village,
                'field_coordinator' => $request->field_coordinator,
                'ff_no' => $request->ff_no,
                'user_id' => $request->user_id,
                'is_dell' => 0,
                'deleted_by' => '-',
                'verified_by' => '-',
                'status' => 0,
          
                'created_at'=>$time,
                'updated_at'=>$time,
            ]);
            
            foreach ($request->farmers as $farmer_no){
                $ftd = new FarmerTrainingDetail();
                $ftd->create([
                    'training_no' => $training_no,
                    'training_date' => $request->training_date,
                    'farmer_no' => $farmer_no,
                    'created_at' => $time,
                    'updated_at' => $time
                ]);
            }
            
            
            $rslt =  $this->ResultReturn(200, 'success', 'Berhasil menambahkan data pelatihan petani.');
            return response()->json($rslt, 200); 
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }

    /**
     * @SWG\Post(
     *   path="/api/UpdateFarmerTraining",
	 *   tags={"FarmerTrainings"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Update Farmer Training",
     *   operationId="UpdateFarmerTraining",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Update Farmer Training",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="farmer_no", type="string", example="F000001"),
     *              @SWG\Property(property="ktp_no", type="string", example="0909090909"),
     *              @SWG\Property(property="name", type="string", example="Budi Indra"),
     *              @SWG\Property(property="birthday", type="date", example="2000-10-20"),
     *              @SWG\Property(property="religion", type="string", example="Islam"),
     *               @SWG\Property(property="rt", type="integer", example="01"),
     *               @SWG\Property(property="rw", type="integer", example="02"),
     *              @SWG\Property(property="address", type="string", example="Jl Cemara No 22, Kemiri, Salatiga"),
     *              @SWG\Property(property="village", type="string", example="33.05.10.18"),
     *              @SWG\Property(property="marrital_status", type="string", example="Kawin"),
     *              @SWG\Property(property="phone", type="string", example="085777771111"),
     *              @SWG\Property(property="ethnic", type="string", example="Jawa"),   
     *              @SWG\Property(property="origin", type="string", example="lokal"),
     *              @SWG\Property(property="gender", type="string", example="male"),
     *              @SWG\Property(property="number_family_member", type="int", example="2"),   
     *              @SWG\Property(property="mu_no", type="string", example="024"),
     *              @SWG\Property(property="target_area", type="string", example="test"),
     *              @SWG\Property(property="active", type="int", example="1"),
     *              @SWG\Property(property="user_id", type="string", example="U0002"),
     *              @SWG\Property(property="ktp_document", type="int", example="test"),              
     *              @SWG\Property(property="post_code", type="string", example="nullable"),
     *              @SWG\Property(property="group_no", type="string", example="nullable"),
     *              @SWG\Property(property="mou_no", type="string", example="nullable"),   
     *              @SWG\Property(property="main_income", type="int", example="nullable"),
     *              @SWG\Property(property="side_income", type="int", example="nullable"),
     *              @SWG\Property(property="main_job", type="string", example="nullable"),
     *              @SWG\Property(property="side_job", type="string", example="nullable"),
     *              @SWG\Property(property="education", type="string", example="nullable"),
     *              @SWG\Property(property="non_formal_education", type="string", example="nullable"),
     *              @SWG\Property(property="farmer_profile", type="string", example="nullable") 
     *          ),
     *      )
     * )
     *
     */
    public function UpdateFarmerTraining(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'training_no' => 'required|max:255|unique:farmer_trainings',
                'training_date' => 'required',
                'first_material' => 'required|max:255',
                'second_material' => 'required|max:255',
                'organic_material' => 'required|max:255',
                'program_year' => 'required',
                'absent' => 'required',
                'village' => 'required|max:255',
                'field_coordinator' => 'required|max:255',
                'ff_no' => 'required|max:255',             
                'mu_no' => 'required|max:255',
                'target_area' => 'required|max:255',
                'status' => 'required|max:1',
                'user_id' => 'required|max:11'              
            ]);

            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }

            FarmerTraining::where('training_no', '=', $request->training_no)
            ->update
            ([
                'training_no' => $trainingno,
                'training_date' => $request->training_date,
                'first_material' => $request->first_material,
                'second_material' => $request->second_material,
                'organic_material' => $request->organic_material,
                'program_year' => $request->program_year,
                'absent' => $request->absent,
                'mu_no' => $request->mu_no,
                'target_area' => $request->target_area,
                'village' => $request->village,
                'field_coordinator' => $request->field_coordinator,
                'ff_no' => $request->ff_no,
                'user_id' => $request->user_id,
                'deleted_by' => '-',
                'verified_by' => $request->verified_by,        
                
                'updated_at'=>Carbon::now(),

                'is_dell' => 0
            ]);
            if($group_no != "-" && $main_job != "-" && $side_job != "-" && $education != "-" && $non_formal_education != "-" && $farmer_profile != "-" )
            {
                FarmerTraining::where('farmer_no', '=', $request->farmer_no)
                ->update
                (['status' => 1]);
            }
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200); 
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }

    public function AddDetailFarmerTraining(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'training_no' => 'required',
                'date_training' => 'required', 
                'farmer_no' => 'required',
            ]);

            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }

            // var_dump($coordinate);
            // 'lahan_no', 'tree_code', 'amount', 'detail_year', 'user_id','created_at', 'updated_at'
            FarmerTrainingDetaill::create([
                'training_no' => $request->training_no,
                'date_training' => $request->date_training,
                'farmer_no' => $request->farmer_no,

                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now()
            ]);
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200); 
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }

    /**
     * @SWG\Post(
     *   path="/api/DeleteFarmerTrainingDetail",
	 *   tags={"FarmerTrainings"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Delete Farmer Training Detail",
     *   operationId="DeleteFarmerTrainingDetail",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Delete Farmer Training Detail",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="id", type="string", example="1")
     *          ),
     *      )
     * )
     *
     */
    public function DeleteFarmerTrainingDetail(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'id' => 'required'
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
    
            DB::table('farmer_training_details')->where('id', $request->id)->delete();
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200);
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }

        /**
     * @SWG\Post(
     *   path="/api/SoftDeleteFarmerTraining",
	 *   tags={"FarmerTrainings"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Soft Delete Farmer Training",
     *   operationId="SoftDeleteFarmer",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Soft Delete Farmer",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="id", type="string", example="2")
     *          ),
     *      )
     * )
     *
     */
    public function SoftDeleteFarmerTraining(Request $request){
        try{
            $validator = Validator::make($request->all(), [    
                'id' => 'required|exists:farmer_trainings,id'
            ]);

            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
            FarmerTraining::where('id', '=', $request->id)
                    ->update
                    ([
                        'is_dell' => 1
                    ]);
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200); 
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }

        /**
     * @SWG\Post(
     *   path="/api/DeleteFarmerTraining",
	 *   tags={"FarmerTrainings"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Delete Farmer Training",
     *   operationId="DeleteFarmer",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Delete Farmer Training",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="id", type="string", example="2")
     *          ),
     *      )
     * )
     *
     */
    public function DeleteFarmerTraining(Request $request){
        try{
            $validator = Validator::make($request->all(), [    
                'id' => 'required|exists:farmer_trainings,id',
                'user_email' => 'required|exists:users,email'
            ]);

            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
            $training = FarmerTraining::find($request->id);
            // delete farmers
            $farmers = DB::table('farmer_training_details')->where('training_no', $training->training_no)->delete();
            // delete photo
            if (isset($training->absent)) {
                $absentImages = explode(",", $training->absent);
                foreach($absentImages as $absImages) {
                    $this->DeletePhotoExternal($absImages);
                }
            }
            if (isset($training->documentation_photo)) {
                    $this->DeletePhotoExternal('documentation-photos/,'.$training->documentation_photo);
            }
            
            // create delete log
            $logData = [
                'deleted_by' => $request->user_email,
                'training_no' => $training->training_no,
                'fc_no' => $training->field_coordinator,
                'ff_no' => $training->ff_no,
                'created_by' => $training->user_id,
            ];
            $this->createFarmerTrainingLogs($logData);
            
            // delete farmer training
            $training->delete();
            
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200); 
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }
    
    // Training Materials
    public function GetTrainingMaterials() {
        try {
            $datas = DB::table('training_materials')
            ->select('material_no', 'material_name')
            ->orderBy('material_no')
            ->get();    
            
            $rslt =  $this->ResultReturn(200, 'success', $datas);
            return response()->json($rslt, 200); 
        } catch (\Exception $ex){
            return response()->json($ex);
        }
        
    }
    
    // Create Log for Deleted Farmer Training
    public function createFarmerTrainingLogs($logData) {
        // get fc 
        $fc = Employee::where('nik', $logData['fc_no'])->first();
        // get ff 
        $ff = FieldFacilitator::where('ff_no', $logData['ff_no'])->first();
        
        // set message
        $message = 'Deleted ' . 
                    ($logData['training_no'] ?? '-') . 
                    '[created by = ' . 
                    ($logData['created_by'] ?? '-') .
                    ', fc = ' .
                    ($fc->name ?? '-') .
                    ', ff = ' . 
                    ($ff->name ?? '-') .
                    '] by ' . 
                    ($logData['deleted_by'] ?? '-');
        
        
        $log = Log::channel('farmer_training');
        $createLog = $log->info($message);
        
        return true;
    }
    
    public function TrialUploadPhotoExternal(Request $req) {
        $upload = $this->UploadPhotoExternal($req->absent, $req->name);
        
        $rslt =  $this->ResultReturn(200, 'success', $upload);
        return response()->json($rslt, 200);
    }
    
    public function ExportFarmerTraining(Request $req) {
        // validator {
        $validator = Validator::make($req->all(), [
            'token' => 'required',
            'program_year' => 'required',
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
        // get datas 
        $filter = [
            'farmer_trainings.program_year' => $req->program_year
        ];
        $datas = DB::table('farmer_trainings')
            ->join('employees', 'employees.nik', 'farmer_trainings.field_coordinator')
            ->join('field_facilitators', 'field_facilitators.ff_no', 'farmer_trainings.ff_no')
            ->join('managementunits', 'managementunits.mu_no', 'farmer_trainings.mu_no')
            ->join('target_areas', 'target_areas.area_code', 'farmer_trainings.target_area')
            ->join('desas', 'desas.kode_desa', 'farmer_trainings.village')
            ->select(
                'farmer_trainings.*', 
                'employees.name as fc_name', 
                'field_facilitators.name as ff_name',
                'managementunits.name as mu_name', 
                'target_areas.name as ta_name', 
                'desas.name as village_name')
            ->where($filter);
        
        if ($req->ff) {
            $ff = explode(",", $req->ff);
            $datas = $datas->whereIn('field_facilitators.ff_no', $ff);
        }
        
        $datas = $datas->get();
        
        $capFarmer = 0;
        foreach ($datas as $data) {
            $data->materi1 = DB::table('training_materials')->where('material_no', $data->first_material)->first()->material_name ?? '-';
            $data->materi2 = DB::table('training_materials')->where('material_no', $data->second_material)->first()->material_name ?? '-';
            $data->farmers = DB::table('farmer_training_details')
                ->join('farmers', 'farmers.farmer_no', 'farmer_training_details.farmer_no')
                ->where('training_no', $data->training_no)->orderBy('farmers.name')->pluck('farmers.name')->toArray();
            if ($capFarmer < count($data->farmers)) $capFarmer = count($data->farmers);
        }
        
        $rslt = [
            'program_year' => $req->program_year,
            'cap_farmer' => $capFarmer,
            'user' => $user,
            'datas' => $datas
        ];    
        // return response()->json($rslt, 200);
        return view('farmer_training.export', $rslt);
    }
    
    // upload external training absent photo
    private function UploadPhotoExternal($file, $name) {
        if (isset($file) && isset($name)) {
            $dataToPost = [
                'nama' => $name
            ];
            
            $toArray = explode(',', $name);
            if (count($toArray) > 1) {
                $dataToPost['dir'] = $toArray[0];
                $dataToPost['nama'] = $toArray[1];
            }
            
            $url = "https://t4tadmin.kolaborasikproject.com/farmer-training/upload.php";
            $response = Http::attach('image', file_get_contents($file), 'gambar.' . $file->extension())
                ->post($url, $dataToPost);
            
            $content = $response->json();
            
            if ($content['code'] == 200) {
                return $content['data']['new_name'];
            } else return false;
        } else return false;
        
    }
    // delete external training absent photo
    private function DeletePhotoExternal($name) {
        if (isset($name)) {
            $dataToPost = [
                'nama' => $name
            ];
            
            $toArray = explode(',', $name);
            if (count($toArray) > 1) {
                $dataToPost['dir'] = $toArray[0];
                $dataToPost['nama'] = $toArray[1];
            }
            
            $url = "https://t4tadmin.kolaborasikproject.com/farmer-training/delete.php";
            $response = Http::asForm()->post($url, $dataToPost);
            
            $content = $response->json();
            
            if ($content['code'] == 200) {
                return true;
            } else return false;
        } else return false;
        
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}
