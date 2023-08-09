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
use App\FormMinat;
use App\FormMinatFarmer;

class FormMinatController extends Controller
{
    /**
     * @SWG\Get(
     *   path="/api/GetFormMinatAllAdmin",
     *   tags={"FormMinat"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Form Minat Admin",
     *   operationId="GetFormMinatAllAdmin",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="name",in="query", type="string"),
     *       @SWG\Parameter(name="typegetdata",in="query", type="string"),
     *      @SWG\Parameter(name="fc",in="query", type="string"),
     * )
     */
    public function GetFormMinatAllAdmin(Request $request){
        $typegetdata = $request->typegetdata;
        $fc = $request->fc;
        $getname = $request->name;
        if($getname){$name='%'.$getname.'%';}
        else{$name='%%';}
        try{
            if($typegetdata == 'all' || $typegetdata == 'several'){
                $fcdecode = (explode(",",$fc));
                
                if($typegetdata == 'all'){
                    $GetFormMinatAll = DB::table('form_minats')->select('form_minats.id',\DB::raw('SUBSTRING(form_minats.form_date, 1, 4) as form_date'),'form_minats.form_date as form_date_all','form_minats.name','form_minats.alamat',
                    'form_minats.respond_to_programs','form_minats.village','desas.name as namaDesa','form_minats.tree1',
                    'form_minats.tree2','form_minats.tree3','form_minats.tree4','form_minats.tree5')
                    ->leftjoin('desas', 'desas.village', '=', 'form_minats.village')
                    ->where('form_minats.name', 'Like', $name)->orderBy('form_minats.name', 'ASC')->get();
                }else{
                    
                    $GetFormMinatAll = DB::table('form_minats')->select('form_minats.id',\DB::raw('SUBSTRING(form_minats.form_date, 1, 4) as form_date'),'form_minats.form_date as form_date_all','form_minats.name','form_minats.alamat',
                    'form_minats.respond_to_programs','form_minats.village','desas.name as namaDesa','form_minats.tree1',
                    'form_minats.tree2','form_minats.tree3','form_minats.tree4','form_minats.tree5')
                    ->leftjoin('desas', 'desas.village', '=', 'form_minats.village')
                    ->wherein('form_minats.user_id', $fcdecode)
                    ->where('form_minats.name', 'Like', $name)->orderBy('form_minats.name', 'ASC')->get();
                }

                if(count($GetFormMinatAll)!=0){
                    if($typegetdata == 'all'){
                        $count = DB::table('form_minats')->where('name', 'Like', $name)->count();
                    }else{
                        $count = DB::table('form_minats')->where('name', 'Like', $name)
                        ->wherein('user_id', $fcdecode)->count();
                    }
                    $data = ['count'=>$count, 'data'=>$GetFormMinatAll];
                    $rslt =  $this->ResultReturn(200, 'success', $data);
                    return response()->json($rslt, 200);  
                }
                else{
                    $rslt =  $this->ResultReturn(404, 'doesnt match data', 'doesnt match data');
                    return response()->json($rslt, 404);
                }
            }else{
                $rslt =  $this->ResultReturn(404, 'doesnt match data', 'doesnt match data');
                return response()->json($rslt, 404);
            }
             
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }

    /**
     * @SWG\Get(
     *   path="/api/GetFormMinatAll",
     *   tags={"FormMinat"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Form Minat",
     *   operationId="GetFormMinatAll",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="name",in="query", type="string"),
     *      @SWG\Parameter(name="user_id",in="query", required=true, type="string"),
     * )
     */
    public function GetFormMinatAll(Request $request)
    {
        $userID = $request->user_id;
        $mu = $request->mu_no;
        
        $GetFormMinatAll = DB::table('form_minats')
                ->select('form_minats.id',
                         'form_minats.form_date', 
                         'form_minats.user_id', 
                         'form_minats.form_no', 
                         'form_minats.mu_no',
                         'form_minats.village', 
                         'desas.name as namaDesa', 
                         'form_minats.is_verified', 
                         'form_minats.verified_by',
                         DB::raw('COUNT(form_minat_farmers.id) as total_farmer'),
                         'users.name as pic_name',
                         'employees.name as pic_manager')
        ->leftjoin('desas', 'desas.kode_desa', '=', 'form_minats.village')
        ->leftjoin('form_minat_farmers', 'form_minat_farmers.form_no', '=', 'form_minats.form_no')
        ->join('users', 'users.email', 'form_minats.user_id')
        ->join('employee_structure', 'employee_structure.nik', 'users.employee_no')
        ->leftjoin('employees', 'employees.nik', 'employee_structure.manager_code')
        //->where('form_minats.mu_no', '=', $request->mu_no)
        ->orderBy('form_minats.created_at', 'desc')
        ->groupBy('form_no');
        
        if ($request->program_year) {
            $GetFormMinatAll = $GetFormMinatAll->where('form_minats.program_year', $request->program_year);
        }
        
        if($userID){
            $GetFormMinatAll = $GetFormMinatAll->whereIn('form_minats.user_id', explode(',',$userID));
        }
        $GetFormMinatAll = $GetFormMinatAll->get();
        
        if(count($GetFormMinatAll)!=0){
            $count = count($GetFormMinatAll);
            $data = ['count'=>$count, 'data'=>$GetFormMinatAll];
            $rslt =  $this->ResultReturn(200, 'success', $data);
            return response()->json($rslt, 200);  
        }
        else{
            $rslt =  $this->ResultReturn(404, 'doesnt match data', 'doesnt match data');
            return response()->json($rslt, 404);
        }
    }

    /**
     * @SWG\Get(
     *   path="/api/GetFormMinatDetail",
     *   tags={"FormMinat"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Form Minat Detail",
     *   operationId="GetFormMinatDetail",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="id",in="query", required=true, type="string"),
     * )
     */
    public function GetFormMinatDetail(Request $request)
    {
        $id = $request->form_no;
        $GetFormMinatAll = DB::table('form_minats')->select('form_minats.id', 'form_minats.form_date', 'form_minats.province', 'form_minats.form_no', 'form_minats.city', 'form_minats.district', 'form_minats.village','desas.name as namaDesa','form_minats.mu_no', 'form_minats.target_area','form_minats.program_year', 'form_minats.user_id', 'form_minats.is_verified', 'form_minats.verified_by', 'target_areas.name as namaTA', 'managementunits.name as namaMU')
        ->leftjoin('desas', 'desas.kode_desa', '=', 'form_minats.village')
        ->leftjoin('managementunits', 'managementunits.mu_no', '=', 'form_minats.mu_no')
        ->leftjoin('target_areas', 'target_areas.area_code', '=', 'form_minats.target_area')
        ->where('form_minats.form_no', '=', $id)->first();
        
        $GetFormMinatAll->ListFarmer = DB::table('form_minat_farmers')
            ->select('form_minat_farmers.*', 'training_materials.material_name as namaTraining')
            
            ->leftjoin('training_materials', 'training_materials.material_no', '=', 'form_minat_farmers.training')
            ->where('form_no', '=', $id)->get();
            
        if($GetFormMinatAll){
            $namaPohon  = ""; $n=1;
            
            foreach ($GetFormMinatAll->ListFarmer as $val) {                    
                if($val->tree1){
                    $val->tree1 = DB::table('trees')->where('tree_code', '=', $val->tree1)->first();
                }if($val->tree2){
                    $val->tree2 = DB::table('trees')->where('tree_code', '=', $val->tree2)->first();
                }if($val->tree3){
                    $val->tree3 = DB::table('trees')->where('tree_code', '=', $val->tree3)->first();
                }
            }
            
            $rslt =  $this->ResultReturn(200, 'success', $GetFormMinatAll);
            return response()->json($rslt, 200);  
        }
        else{
            $rslt =  $this->ResultReturn(404, 'doesnt match data', 'doesnt match data');
            return response()->json($rslt, 404);
        } 
        
    }

    /**
     * @SWG\Post(
     *   path="/api/AddFormMinat",
	 *   tags={"FormMinat"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Add Form Minat",
     *   operationId="AddFormMinat",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Add Form Minat",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="form_date", type="string", example="2021-03-20"),
     *              @SWG\Property(property="name", type="string", example="Budi Indra"),
     *              @SWG\Property(property="alamat", type="date", example="Jl Cemara No 22, Kemiri, Salatiga"),
     *              @SWG\Property(property="village", type="string", example="33.05.10.18"),
     *              @SWG\Property(property="respond_to_programs", type="string", example="berminat/ragu-ragu/belum_berminat"),
     *              @SWG\Property(property="tree1", type="string", example="nullable"),
     *              @SWG\Property(property="tree2", type="string", example="nullable"),
     *              @SWG\Property(property="tree3", type="string", example="nullable"),
     *              @SWG\Property(property="tree4", type="string", example="nullable"),
     *              @SWG\Property(property="tree5", type="string", example="nullable"),
     *              @SWG\Property(property="user_id", type="string", example="U0002"),
     *          ),
     *      )
     * )
     *
     */
    public function AddFormMinat(Request $request)
    {
        // date_default_timezone_set("Asia/Bangkok");

        $validator = Validator::make($request->all(), [
            'form_date' => 'required',    
            // 'village' => 'required|unique:form_minats,village'
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        }
        // $farmercount = Farmer::count();
        // $farmerno = 'F'.str_pad($farmercount+1, 8, '0', STR_PAD_LEFT);
        
        $form_no = FormMinat::Maxno();
        
        $minat = FormMinat::create([
           'form_no' => $form_no,
            'form_date' => $request->form_date,
           'province' => $request->province,
           'city' => $request->city,
           'district' => $request->district,
           'village' => $request->village,
           'mu_no' => $request->mu_no,
           'target_area' => $request->target_area,
           'program_year' => $request->program_year,
           'user_id' => $request->user_id ?? Auth::user()->email ?? '-',
           'created_at'=>Carbon::now(),
           'updated_at'=>Carbon::now(),
        ]);
        
        $list_farmer = $request->list_farmer;
        
        foreach($list_farmer as $value){
            FormMinatFarmer::create([
                'name' => $value['name'],
                'form_no' => $form_no,
                'status_program' => $value['status_program'],
                'tree1' => $value['tree1'],
                'tree2' => $value['tree2'],
                'tree3' => $value['tree3'],
                'training' => $value['training'],
                'photo' => $value['photo'] ?? '',
                'pattern' => $value['pattern'],
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ]);   
        }
        
        $rslt =  $this->ResultReturn(200, 'success', $minat);
        return response()->json($rslt, 200); 
    }

    /**
     * @SWG\Post(
     *   path="/api/UpdateFormMinat",
	 *   tags={"FormMinat"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Update Form Minat",
     *   operationId="UpdateFormMinat",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Update Form Minat",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="id", type="string", example="1"),
     *              @SWG\Property(property="form_date", type="string", example="2021-03-20"),
     *              @SWG\Property(property="name", type="string", example="Budi Indra"),
     *              @SWG\Property(property="alamat", type="date", example="Jl Cemara No 22, Kemiri, Salatiga"),
     *              @SWG\Property(property="kode_desa", type="string", example="33.05.10.18"),
     *              @SWG\Property(property="respond_to_programs", type="string", example="berminat/ragu-ragu/belum_berminat"),
     *              @SWG\Property(property="tree1", type="string", example="nullable"),
     *              @SWG\Property(property="tree2", type="string", example="nullable"),
     *              @SWG\Property(property="tree3", type="string", example="nullable"),
     *              @SWG\Property(property="tree4", type="string", example="nullable"),
     *              @SWG\Property(property="tree5", type="string", example="nullable"),
     *              @SWG\Property(property="user_id", type="string", example="U0002"),
     *          ),
     *      )
     * )
     *
     */
    public function UpdateFormMinat(Request $request)
    {
        // date_default_timezone_set("Asia/Bangkok");

        $validator = Validator::make($request->all(), [
            'village' => 'required|max:255',           
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        }
        // $farmercount = Farmer::count();
        // $farmerno = 'F'.str_pad($farmercount+1, 8, '0', STR_PAD_LEFT);
        
        $minat = FormMinat::where('form_no', '=', $request->form_no)
        ->update([
            'form_date' => $request->form_date,
            'province' => $request->province,
            'city' => $request->city,
            'district' => $request->district,
            'village' => $request->village,
            'mu_no' => $request->mu_no,
            'target_area' => $request->target_area,
            'program_year' => $request->program_year,
            'user_id' => $request->user_id,
            'updated_at'=>Carbon::now(),
        ]);
        
        $list_farmer = $request->list_farmer;
    
        FormMinatFarmer::where('form_no', '=', $request->form_no)->delete();
        
        foreach($list_farmer as $value){
            FormMinatFarmer::create([
                'name' => $value['name'],
                'form_no' => $request->form_no,
                'status_program' => $value['status_program'],
                'tree1' => $value['tree1'],
                'tree2' => $value['tree2'],
                'tree3' => $value['tree3'],
                'training' => $value['training'],
                'photo' => $value['photo'],
                'pattern' => $value['pattern'],
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ]);   
        }
        
        $rslt =  $this->ResultReturn(200, 'success', $minat);
        return response()->json($rslt, 200);
    }

    /**
     * @SWG\Post(
     *   path="/api/DeleteFormMinat",
	 *   tags={"FormMinat"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Delete Form Minat",
     *   operationId="DeleteFormMinat",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Delete Form Minat",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="id", type="integer", example=1)
     *          ),
     *      )
     * )
     *
     */
    public function DeleteFormMinat(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer|max:255'
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
    
            DB::table('form_minats')->where('id', $request->id)->delete();
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200);
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }
    
    public function VerificationFormMinat(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'form_no' => 'required',
        ]);
        
        if($validator->fails()){
            $rslt = $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        }
        
        $form_data_no = $request->form_no;
        $form_minat = DB::table('form_minats')->where('form_no', '=', $form_data_no)->first();
        
        if($form_minat) {
            FormMinat::where('form_no', '=', $form_data_no)
            ->update([
                'verified_at'=>Carbon::now(),
                'verified_by'=> Auth::user()->email,
                'is_verified'=> 1,
            ]);
            
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200); 
        }else{
            $rslt =  $this->ResultReturn(400, 'doesnt match data', 'doesnt match data');
            return response()->json($rslt, 400);
        }
    }
    
    public function UnverificationFormMinat(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'form_no' => 'required',
        ]);
        
        if($validator->fails()){
            $rslt = $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        }
        
        $form_data_no = $request->form_no;
        $form_minat = DB::table('form_minats')->where('form_no', '=', $form_data_no)->first();
        
        if($form_minat) {
            FormMinat::where('form_no', '=', $form_data_no)
            ->update([
                'verified_at'=>Carbon::now(),
                'verified_by'=> '-',
                'is_verified'=> 0,
            ]);
            
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200); 
        }else{
            $rslt =  $this->ResultReturn(400, 'doesnt match data', 'doesnt match data');
            return response()->json($rslt, 400);
        }
    }
}
