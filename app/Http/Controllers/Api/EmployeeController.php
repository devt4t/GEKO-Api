<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use App\User;
use App\Employee;
use App\FieldFacilitator;
use App\EmployeeFamily;
use App\EmployeePosition;
use App\EmployeeStructure;

class EmployeeController extends Controller
{
    /**
     * @SWG\Get(
     *   path="/api/GetEmployeeAll",
     *   tags={"Employee"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Employee All",
     *   operationId="GetEmployeeAll",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="name",in="query", type="string"),
     *      @SWG\Parameter(name="position_no",in="query", type="string"),
     *      @SWG\Parameter(name="parent_no",in="query", type="string"),
     * )
     */
    public function GetEmployeeAll(Request $request){
        $getname = $request->name;
        $positionno = $request->position_no;
        $parentno = $request->parent_no;
        if($getname){$name='%'.$getname.'%';}
        else{$name='%%';}
        if($positionno){$position_no='%'.$positionno.'%';}
        else{$position_no='%%';}
        if($parentno){$parent_no='%'.$parentno.'%';}
        else{$parent_no='%%';}
        try{
            $GetEmployeeAll = DB::table('employees')
                                ->select('employees.id','employees.nik','employees.parent_no','employees.name',
                                'employees.alias','employees.ktp_no','employees.kk_no','employees.email',
                                'employees.address','employees.city','employees.kelurahan','employees.kecamatan',
                                'employees.province','employees.birthday','employees.birthplace','employees.phone',
                                'employees.marrital','employees.blood_type','employees.religion','employees.zipcode',
                                'employees.gender','employees.npwp','employees.bank_account','employees.bank_branch',
                                'employees.bank_name','employees.job_status','employees.job_start','employees.job_end',
                                'employees.bpjs_kesehatan_no','employees.bpjs_tenagakerja_no','employees.mother_name','employees.employee_photo',
                                'employees.position_no','employee_positions.name as emp_position',
                                'employee_structure.manager_code')
                                ->leftjoin('employee_positions', 'employee_positions.position_no', '=', 'employees.position_no')
                                ->leftJoin('employee_structure', 'employee_structure.nik', 'employees.nik')
                                ->where('employees.parent_no', 'Like', $parent_no)
                                ->where('employees.name', 'Like', $name)
                                ->where('employees.position_no', 'Like', $position_no)
                                ->orderBy('employees.name', 'ASC')->get();
            if(count($GetEmployeeAll)!=0){
                $count = DB::table('employees')
                        ->leftjoin('employee_positions', 'employee_positions.position_no', '=', 'employees.position_no')
                        ->where('employees.parent_no', 'Like', $parent_no)
                        ->where('employees.name', 'Like', $name)
                        ->where('employees.position_no', 'Like', $position_no)
                        ->count();
                $data = ['count'=>$count, 'data'=>$GetEmployeeAll];
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
     *   path="/api/GetEmployeebyManager",
     *   tags={"Employee"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Employee by Manager",
     *   operationId="GetEmployeebyManager",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="manager_code",in="query", type="string"),
     *      @SWG\Parameter(name="position",in="query", type="integer"),
     * )
     */
    public function GetEmployeebyManager(Request $request){
        $manager_code = $request->manager_code;
        $getposition = $request->position;
        if($getposition){$position='%'.$getposition.'%';}
        else{$position='%%';}
        try{
            $GetEmployeeAll = DB::table('employees')
            ->select(
                'employees.name', 
                'employees.ktp_no', 
                'employees.nik', 
                'employees.id', 
                'users.email'
            )
            ->join('employee_structure', 'employee_structure.nik', '=', 'employees.nik')
            ->join('users', 'users.employee_no', '=', 'employees.nik')
            ->where('employee_structure.manager_code','=',$manager_code)
            ->where('employees.position_no','Like',$position)
            ->groupBy('employees.nik')
            ->get();
            if(count($GetEmployeeAll)!=0){
                $count = DB::table('employees')
                ->join('employee_structure', 'employee_structure.manager_code', '=', 'employees.nik')
                ->where('employee_structure.manager_code','=',$manager_code)
                ->where('employees.position_no','Like',$position)
                ->count();
                $data = ['count'=>$count, 'data'=>$GetEmployeeAll];
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
     *   path="/api/GetEmployeebyPosition",
     *   tags={"Employee"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Employee by Position",
     *   operationId="GetEmployeebyPosition",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="position_code",in="query", type="string"),
     * )
     */
    public function GetEmployeebyPosition(Request $request){
        $position_code = $request->position_code;
        try{
            $GetEmployeeAll = DB::table('employees')
            ->select('employees.name', 'employees.nik', 'employees.id')
            ->where('employees.position_no','=',$position_code)
            ->get();
            if(count($GetEmployeeAll)!=0){
                $count = DB::table('employees')
                ->select('employees.name', 'employees.nik', 'employees.id')
                ->where('employees.position_no','=',$position_code)
                ->count();
                $data = ['count'=>$count, 'data'=>$GetEmployeeAll];
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
     *   path="/api/GetFFbyUMandFC",
     *   tags={"Employee"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get FF by UM and FC",
     *   operationId="GetFFbyUMandFC",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="position",in="query", type="string"),
     *      @SWG\Parameter(name="code",in="query", type="string"),
     * )
     */
    public function GetFFbyUMandFC(Request $request){
        $position = $request->position;
        $code = $request->code;
        $py = $request->program_year;
        try{
            if($position == 'UM' || $position == 'FC'){
                if($position == 'FC'){
                    $FieldFacilitator = FieldFacilitator::where('fc_no','=',$code);
                    if($py) {
                        $FieldFacilitator = DB::table('main_pivots')
                        ->where('type', '=', 'fc_ff')
                        ->where('program_year', 'LIKE', "%$py%")
                        ->where('key1', '=', $code);   
                    }
                }else{
                    $EmployeeFC = EmployeeStructure::where('manager_code','=',$code)->pluck('nik');
                    $FieldFacilitator = FieldFacilitator::whereIn('fc_no',$EmployeeFC);
                    if($py) {
                        $FieldFacilitator = DB::table('main_pivots')
                        ->where('type', '=', 'fc_ff')
                        ->where('program_year', 'LIKE', "%$py%")
                        ->whereIn('key1', $EmployeeFC);
                    }
                }
                if($py){
                    $FieldFacilitator = $FieldFacilitator->pluck('key2');
                }
                else $FieldFacilitator = $FieldFacilitator->where('active', 1)->pluck('ff_no');
                // var_dump('test');
                if(count($FieldFacilitator)!=0){
                    // $count = DB::table('employees')
                    // ->select('employees.name', 'employees.nik', 'employees.id')
                    // ->where('employees.position_no','=',$position_code)
                    // ->count();
                    $data = [ 'data'=>$FieldFacilitator];
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
     *   path="/api/GetEmployeeManagePosition",
     *   tags={"Employee"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Employee Manage Position",
     *   operationId="GetEmployeeManagePosition",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="position_code",in="query", type="string"),
     * )
     */
    public function GetEmployeeManagePosition(Request $request){
        // $position_code = $request->position_code;
        try{
            $GetEmployeeManagePosition = DB::table('employees')
            ->select('employees.name', 'employees.nik', 'employees.position_no', 'employees.id','employee_positions.name as namaPosition','employee_positions.position_group')
            ->join('employee_positions', 'employee_positions.position_no', '=', 'employees.position_no')
            ->get();
            if(count($GetEmployeeManagePosition)!=0){
                $count = DB::table('employees')
                ->join('employee_positions', 'employee_positions.position_no', '=', 'employees.position_no')
                ->count();
                $data = ['count'=>$count, 'data'=>$GetEmployeeManagePosition];
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
     *   path="/api/GetJobPosition",
     *   tags={"Employee"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Job Position",
     *   operationId="GetJobPosition",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     * )
     */
    public function GetJobPosition(Request $request){
        // $position_code = $request->position_code;
        try{
            $GetJobPosition = DB::table('employee_positions')
            ->get();
            if(count($GetJobPosition)!=0){
                $count = DB::table('employee_positions')
                ->count();
                $data = ['count'=>$count, 'data'=>$GetJobPosition];
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
     * @SWG\Post(
     *   path="/api/EditPositionEmp",
	 *   tags={"Employee"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="EditPositionEmp",
     *   operationId="EditPositionEmp",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="EditPositionEmp",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="nik", type="string", example="nama"),
	 *				@SWG\Property(property="position_no", type="string", example="1"),
     *          ),
     *      )
     * )
     *
     */
    public function EditPositionEmp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nik' => 'required',
                'position_no' => 'required',
            ]);

            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors(), $validator->errors());
                return response()->json($rslt, 400);
            }

            Employee::where('nik', '=', $request->nik)
                    ->update([
                        'position_no' => $request->position_no,
                        ]);
            $getUser= DB::table('users')->where('employee_no', '=', $request->nik)->first();
            if($getUser){
                User::where('employee_no', '=', $request->nik)
                    ->update([
                        'role' => $request->position_no,
                        ]);
            }

                $rslt =  $this->ResultReturn(200, 'Success Update Profile', 'success');
                return response()->json($rslt, 200);
        } catch (\Exception $ex) {
            return response()->json($ex);
        }
    }

    /**
     * @SWG\Get(
     *   path="/api/GetEmployeeManageManager",
     *   tags={"Employee"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Employee Manage Manager",
     *   operationId="GetEmployeeManageManager",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="position_code",in="query", type="string"),
     * )
     */
    public function GetEmployeeManageManager(Request $request){
        // $position_code = $request->position_code;
        try{
            $GetEmployeeManageManager = DB::table('employees')
            ->select('employees.name', 'employees.nik', 'employees.id', 'employees.id')
            ->join('employee_structure', 'employee_structure.manager_code', '=', 'employees.nik')
            ->get();
            if(count($GetEmployeeManageManager)!=0){
                $count = DB::table('employees')
                ->count();
                $data = ['count'=>$count, 'data'=>$GetEmployeeManageManager];
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
     *   path="/api/GetEmployeeMenuAccess",
     *   tags={"Employee"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="GetEmployeeMenuAccess",
     *   operationId="GetEmployeeMenuAccess",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     * )
     */
    public function GetEmployeeMenuAccess(Request $request){
        // $position_code = $request->position_code;
            $GetEmployeeMenuAccess = DB::table('employees')
            ->select('employees.name', 'employees.nik')
            ->get();

            $datavalempmenu = [];
            $listval=array();

            foreach ($GetEmployeeMenuAccess as $val){
                        // var_d112ump($val->id);
                    $EmployeeStructure = EmployeeStructure::where('nik','=',$val->nik)->first();
                    if ($EmployeeStructure) {
                        $menuaccess = $EmployeeStructure->menu_access ?? [];
                        $arraymn = json_decode($menuaccess);
    
                        // var_dump($arraymn);
                        $menu = '';
                        $i = 1;
                        $datavalemenu = [];
                        $listvalmenu=array();
                        foreach ($arraymn as $valemp){
                            
                            $getmenu = DB::table('menu_access')
                            ->select('menu_access.name as title')
                            ->where('menu_access.id','=',$valemp)
                            ->first();
                            // var_dump($getmenu);
                            if($i == 1){
                               $menu = $getmenu->title;
                            }else{
                               $menu = $menu. ', '.$getmenu->title; 
                            }                        
                            $i+=1;
    
                            $datavalemenu = [ 'MenuCode'=>  $valemp, 'MenuName'=>$getmenu->title];
                            array_push($listvalmenu,$datavalemenu);
                        }
                        $managerCode='-';
                        $managerName='-';
                        if($EmployeeStructure->manager_code != '-'){
                           $GetEmployeeManager = DB::table('employees')
                            ->select('employees.name', 'employees.nik')
                            ->where('nik','=',$EmployeeStructure->manager_code)
                            ->first(); 
                            $managerCode=$GetEmployeeManager->nik ?? '-';
                            $managerName=$GetEmployeeManager->name ?? '-';
                        }
                        
                        // var_dump($menu);
                        $datavalempmenu = [ 'IdEmp'=>  $val->nik, 'NamaEmp'=>$val->name, 'IdManager'=> $managerCode, 'NamaManager'=> $managerName, 'Menu'=> $menu, 'MenuCode'=> $arraymn, 'MenuTable'=> $listvalmenu];
                        array_push($listval,$datavalempmenu);
                    }
                }

            
            if(count($GetEmployeeMenuAccess)!=0){
                $count = DB::table('employees')
                ->count();
                $data = ['count'=>$count, 'data'=>$listval];
                $rslt =  $this->ResultReturn(200, 'success', $data);
                return response()->json($rslt, 200);  
            }
            else{
                $rslt =  $this->ResultReturn(404, 'doesnt match data', 'doesnt match data');
                return response()->json($rslt, 404);
            }
    }

    /**
     * @SWG\Post(
     *   path="/api/EditMenuAccessEmp",
	 *   tags={"Employee"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="EditMenuAccessEmp",
     *   operationId="EditMenuAccessEmp",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="EditMenuAccessEmp",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="nik", type="string", example="11_011"),
     *              @SWG\Property(property="manager_code", type="string", example="11_011"),
	 *				@SWG\Property(property="menu_access", type="string", example="[1]"),
     *          ),
     *      )
     * )
     *
     */
    public function EditMenuAccessEmp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nik' => 'required',
                'manager_code' => 'required',
                'menu_access' => 'required',
            ]);

            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors(), $validator->errors());
                return response()->json($rslt, 400);
            }

            EmployeeStructure::where('nik', '=', $request->nik)
                ->update([
                        'manager_code' => $request->manager_code,
                        'menu_access' => $request->menu_access,
                ]);

                $rslt =  $this->ResultReturn(200, 'Success Update Profile', 'success');
                return response()->json($rslt, 200);
        } catch (\Exception $ex) {
            return response()->json($ex);
        }
    }

    

    public function AddEmployee(Request $request)
    {

        $validator = Validator::make($request->all(), [
            //Employee
            'nik'                       => 'required|string|max:255|unique:employees',
            'position_no'               => 'required|string|max:255|nullable',
            'name'                      => 'required|string|max:255',
            'alias'                      => 'required|string|max:255',
            'ktp_no'                    => 'required|string|max:255',
            'kk_no'                     => 'string|max:255',
            'gender'                    => 'required|in:male,female',
            'religion'                  => 'required|in:-,islam,kristen,khatolik,hindu,buddha,konghuchu,others',
            'blood_type'                => 'required|in:-,A,AB,B,O',
            'birthplace'                => 'string|max:255',
            'birthday'                  => 'date|nullable',
            'phone'                     => 'string|max:255|nullable',
            'email'                     => 'string|max:255|nullable',
            'address'                   => 'string|max:255|nullable',
            'city'                      => 'string|max:255|nullable',
            'kelurahan'                 => 'string|max:255|nullable',
            'kecamatan'                 => 'string|max:255|nullable',
            'province'                  => 'string|max:255|nullable',
            'marrital'                  => 'string|max:255|nullable',
            'zipcode'                   => 'string|max:255|nullable',
            'npwp'                      => 'string|max:255|nullable',
            'bank_account'              => 'string|max:255|nullable',
            'bank_branch'               => 'string|max:255|nullable',
            'bank_name'                 => 'string|max:255|nullable',
            'bpjs_kesehatan_no'         => 'string|max:255|nullable',
            'bpjs_tenagakerja_no'       => 'string|max:255|nullable',
        ]);

        if ($validator->fails()) {
            $rslt =  $this->ResultReturn(400, $validator->errors(), $validator->errors());
            return response()->json($rslt, 400);
        }


        try {

            DB::beginTransaction();

            try {
                Employee::create([
                    'nik' => $request->nik,
                    'name' => $request->name,
                    'alias' => $request->alias,
                    'ktp_no' => $request->ktp_no,
                    'gender' => $request->gender,
                    'religion' => $request->religion,
                    'blood_type' => $request->blood_type,
                    'position_no' => $request->position_no,
                    'kk_no' => $this->ReplaceNull($request->kk_no, 'string'),
                    'birthplace' => $this->ReplaceNull($request->birthplace, 'string'),
                    'birthday' => $this->ReplaceNull($request->birthday, 'date'),
                    'phone' => $this->ReplaceNull($request->phone, 'string'),
                    'email' => $this->ReplaceNull($request->email, 'string'),
                    'address' => $this->ReplaceNull($request->address, 'string'),
                    'city' => $this->ReplaceNull($request->city, 'string'),
                    'kelurahan' => $this->ReplaceNull($request->kelurahan, 'string'),
                    'kecamatan' => $this->ReplaceNull($request->kecamatan, 'string'),
                    'province' => $this->ReplaceNull($request->province, 'string'),
                    'marrital' => $this->ReplaceNull($request->marrital, 'string'),
                    'zipcode' => $this->ReplaceNull($request->zipcode, 'string'),
                    'npwp' => $this->ReplaceNull($request->npwp, 'string'),
                    'bank_account' => $this->ReplaceNull($request->bank_account, 'string'),
                    'bank_branch' => $this->ReplaceNull($request->bank_branch, 'string'),
                    'bank_name' => $this->ReplaceNull($request->bank_name, 'string'),
                    'bpjs_kesehatan_no' => $this->ReplaceNull($request->bpjs_kesehatan_no, 'string'),
                    'bpjs_tenagakerja_no' => $this->ReplaceNull($request->bpjs_tenagakerja_no, 'string'),
                    'parent_no' => $this->ReplaceNull($request->parent_no, 'string'),
                    'mother_name' => $this->ReplaceNull($request->mother_name, 'string'),
                    'employee_photo' => $this->ReplaceNull($request->employee_photo, 'string'),
                    'is_user' => 1,
                    'job_status' => 'Active',
                    'job_start' => $this->ReplaceNull($request->job_start, 'date'),
                    'job_end' => $this->ReplaceNull($request->job_end, 'date'),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

                EmployeeStructure::create([
                        'nik' => $request->nik,
                        'manager_code' => '01-0001',
                        'menu_access' => '[3,4,5,20,21]',
                ]);

                DB::commit();
                
                $rslt =  $this->ResultReturn(200, 'success', 'success');
                return response()->json($rslt, 200);
            } catch (\Exception $e) {
                DB::rollback();
                $success = false;
                throw $e;
            }
        } catch (\Exception $ex) {
            $rslt =  $this->ResultReturn(500, 'error', $ex);
            return response()->json($rslt, 500);
        }
    }

    public function EditEmployee(Request $request)
    {

        $validator = Validator::make($request->all(), [
            //Employee
            'id'                       => 'required',
            'nik'                       => 'required|string|max:255',
            'position_no'               => 'required|string|max:255|nullable',
            'name'                      => 'required|string|max:255',
            'alias'                      => 'required|string|max:255',
            'ktp_no'                    => 'required|string|max:255',
            'kk_no'                     => 'string|max:255',
            'gender'                    => 'required|in:male,female',
            'religion'                  => 'required|in:-,islam,kristen,khatolik,hindu,buddha,konghuchu,others',
            'blood_type'                => 'required|in:-,A,AB,B,O',
            'birthplace'                => 'string|max:255',
            'birthday'                  => 'date|nullable',
            'phone'                     => 'string|max:255|nullable',
            'email'                     => 'string|max:255|nullable',
            'address'                   => 'string|max:255|nullable',
            'city'                      => 'string|max:255|nullable',
            'kelurahan'                 => 'string|max:255|nullable',
            'kecamatan'                 => 'string|max:255|nullable',
            'province'                  => 'string|max:255|nullable',
            'marrital'                  => 'string|max:255|nullable',
            'zipcode'                   => 'string|max:255|nullable',
            'npwp'                      => 'string|max:255|nullable',
            'bank_account'              => 'string|max:255|nullable',
            'bank_branch'               => 'string|max:255|nullable',
            'bank_name'                 => 'string|max:255|nullable',
            'bpjs_kesehatan_no'         => 'string|max:255|nullable',
            'bpjs_tenagakerja_no'       => 'string|max:255|nullable',
        ]);

        if ($validator->fails()) {
            $rslt =  $this->ResultReturn(400, $validator->errors(), $validator->errors());
            return response()->json($rslt, 400);
        }


        try {

            DB::beginTransaction();
            
            try {
                Employee::where('id', '=', $request->id)
                ->update([
                    'nik' => $request->nik,
                    'name' => $request->name,
                    'alias' => $request->alias,
                    'ktp_no' => $request->ktp_no,
                    'gender' => $request->gender,
                    'religion' => $request->religion,
                    'blood_type' => $request->blood_type,
                    'position_no' => $request->position_no,
                    'kk_no' => $this->ReplaceNull($request->kk_no, 'string'),
                    'birthplace' => $this->ReplaceNull($request->birthplace, 'string'),
                    'birthday' => $this->ReplaceNull($request->birthday, 'date'),
                    'phone' => $this->ReplaceNull($request->phone, 'string'),
                    'email' => $this->ReplaceNull($request->email, 'string'),
                    'address' => $this->ReplaceNull($request->address, 'string'),
                    'city' => $this->ReplaceNull($request->city, 'string'),
                    'kelurahan' => $this->ReplaceNull($request->kelurahan, 'string'),
                    'kecamatan' => $this->ReplaceNull($request->kecamatan, 'string'),
                    'province' => $this->ReplaceNull($request->province, 'string'),
                    'marrital' => $this->ReplaceNull($request->marrital, 'string'),
                    'zipcode' => $this->ReplaceNull($request->zipcode, 'string'),
                    'npwp' => $this->ReplaceNull($request->npwp, 'string'),
                    'bank_account' => $this->ReplaceNull($request->bank_account, 'string'),
                    'bank_branch' => $this->ReplaceNull($request->bank_branch, 'string'),
                    'bank_name' => $this->ReplaceNull($request->bank_name, 'string'),
                    'bpjs_kesehatan_no' => $this->ReplaceNull($request->bpjs_kesehatan_no, 'string'),
                    'bpjs_tenagakerja_no' => $this->ReplaceNull($request->bpjs_tenagakerja_no, 'string'),
                    'parent_no' => $this->ReplaceNull($request->parent_no, 'string'),
                    'mother_name' => $this->ReplaceNull($request->mother_name, 'string'),
                    'employee_photo' => $this->ReplaceNull($request->employee_photo, 'string'),
                    'is_user' => 1,
                    'job_status' => 'Active',
                    'job_start' => $this->ReplaceNull($request->job_start, 'date'),
                    'job_end' => $this->ReplaceNull($request->job_end, 'date'),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

                
                DB::commit();

                $rslt =  $this->ResultReturn(200, 'success', 'success');
                return response()->json($rslt, 200);
            } catch (\Exception $e) {
                DB::rollback();
                $success = false;
                throw $e;
            }
        } catch (\Exception $ex) {
            $rslt =  $this->ResultReturn(500, 'error', $ex);
            return response()->json($rslt, 500);
        }
    }

    public function DeleteEmployee(Request $request)
    {

        $validator = Validator::make($request->all(), [
            //Employee
            'id' => 'required',
        ]);

        if ($validator->fails()) {
            $rslt =  $this->ResultReturn(400, $validator->errors(), $validator->errors());
            return response()->json($rslt, 400);
        }


        try {

            DB::beginTransaction();
            
            try {
                
                DB::table('employees')->where('id', '=', $request->id)->delete();

                
                DB::commit();

                $rslt =  $this->ResultReturn(200, 'success', 'success');
                return response()->json($rslt, 200);
            } catch (\Exception $e) {
                DB::rollback();
                $success = false;
                throw $e;
            }
        } catch (\Exception $ex) {
            $rslt =  $this->ResultReturn(500, 'error', $ex);
            return response()->json($rslt, 500);
        }
    }
}
