<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Session;
use DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\User;
use App\FieldFacilitator;
use App\Desa;
use App\Kecamatan;
use App\Kabupaten;
use App\Province;
use App\ManagementUnit;
use App\TargetArea;
use App\Employee;
use App\EmployeeStructure;
use App\EmployeePosition;
use App\MenuParent;
use App\MenuAccess;
use App\MainPivot;
use App\FFWorkingArea;

class UserController extends Controller
{
    /**
     * @SWG\Post(
     *   path="/api/Regist",
	 *   tags={"Users"},
     *   summary="Regist User",
     *   operationId="UsersRegist",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Regist User",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="employee_no", type="string", example="FF0001"),
     *              @SWG\Property(property="name", type="string", example="nama"),
     *              @SWG\Property(property="email", type="string", example="nama@mail.com"),
	 *				@SWG\Property(property="password", type="string", example="nama123"),
     *              @SWG\Property(property="role", type="string", example="fc/ff/um")
     *          ),
     *      )
     * )
     *
     */

    public function Regist(Request $request)
    {   
        $validator = Validator::make($request->all(), [
            'employee_no' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|string',
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors(), $validator->errors());
            return response()->json($rslt, 400);
        }

        // $getLastIdUser = User::orderBy('user_no','desc')->first(); 
        //     if($getLastIdUser){
        //         $user_no = 'U'.str_pad(((int)substr($getLastIdUser->user_no,-4) + 1), 4, '0', STR_PAD_LEFT);
        //     }else{
        //         $user_no = 'U0001';
        //     }

        User::create([
            'employee_no' => $request->employee_no,
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role,
        ]);
        $rslt =  $this->ResultReturn(200, 'success', 'success');
        return response()->json($rslt, 200);
    
    }

    /**
     * @SWG\Post(
     *   path="/api/Login",
	 *   tags={"Users"},
     *   summary="Login User",
     *   operationId="UsersLogin",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Login User",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="email", type="string", example="nama@mail.com"),
	 *				@SWG\Property(property="password", type="string", example="nama123")
     *          ),
     *      )
     * )
     *
     */

    public function Login (Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email|max:255',
                'password' => 'required|string|min:6'
            ]);

            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors(), $validator->errors());
                return response()->json($rslt, 400);
            }

            // var_dump('test');
            $credentials = request(['email', 'password']);
            if (! $token = auth()->attempt($credentials)) {                
                $rslt =  $this->ResultReturn(401, 'Incorrect Email & password', 'Unauthorized');
                return response()->json($rslt, 401);
            }else{
                $getUser = User::where('email', '=', $request->email)->first();
                
                // if($request->program_year == null && substr($request->email,-14) == "FFPati@t4t.org"){
                //     $request->program_year = "2022";
                // }
                // var_dump($getUser);
                $objUser = ['id'=>$getUser->id,'employee_no'=>$getUser->employee_no,'role'=>$getUser->role,'name'=>$getUser->name,
                'email'=>$getUser->email,'email_verified_at'=>$getUser->email_verified_at];
                if($getUser->role == 'ff' && $request->program_year){
                    $getff = FFWorkingArea::select('kode_desa as working_area','area_code as target_area','mu_no')->where([
                        ['ff_no','=',$getUser->employee_no],
                        ['program_year', 'like', "%$request->program_year%"]
                    ])->first();
                    $fc_ff = MainPivot::select('key1 as fc_no', 'key2 as ff_no', 'program_year', 'active', 'employees.name as fc_name')
                        ->join('employees', 'employees.nik', 'main_pivots.key1')
                        ->where(['type' => 'fc_ff', 'key2' => $getUser->employee_no, ['program_year', 'like', "%$request->program_year%"]])->first();
                    // return response()->json($getff);
                    
                // var_dump($getff->working_area);
                    if($getff && $fc_ff){
                        // var_dump($getff->working_area);
                        // var_dump($getff->target_area);
                        // var_dump($getff->mu_no);
                        $getDesa = Desa::select('kode_desa','name','kode_kecamatan')->where('kode_desa','=',$getff->working_area)->first(); 
                        
                        $getTA = TargetArea::select('area_code','name')->where('area_code','=',$getff->target_area)->first(); 
                        
                        $getMU = ManagementUnit::select('mu_no','name')->where('mu_no','=',$getff->mu_no)->first();
                        // var_dump($getMU);
                        
                        $objUser = ['id'=>$getUser->id,'employee_no'=>$getUser->employee_no,'role'=>$getUser->role,'name'=>$getUser->name,
                        'email'=>$getUser->email,'email_verified_at'=>$getUser->email_verified_at,
                        'kode_desa'=>$getff->working_area,'nama_desa'=>$getDesa->name,
                        'mu_no'=>$getff->mu_no,'nama_mu'=>$getMU->name,
                        'target_area'=>$getff->target_area,'nama_ta'=>$getTA->name,
                        'program_year' => $request->program_year, 'fc_ff' => $fc_ff];

                        // var_dump($objUser);
                        $usernew = ['User'=>$objUser, 'access_token' => $token, 'token_type' => 'bearer', 'expires_in' => auth()->factory()->getTTL() * 60 ];
                        $rslt =  $this->ResultReturn(200, 'success', $usernew);
                        return response()->json($rslt, 200);
                    }  else{
                    $rslt =  $this->ResultReturn(401, 'FF doesnt active in this year', 'FF doesnt active in this year');
                    return response()->json($rslt, 401);
                }               
                
                }else{
                    $rslt =  $this->ResultReturn(404, 'doesnt match data', 'doesnt match data');
                    return response()->json($rslt, 404);
                }
                                
            }
        } catch (\Exception $ex) {
            return response()->json($ex);
        }
    }

    /**
     * @SWG\Post(
     *   path="/api/LoginWeb",
	 *   tags={"Users"},
     *   summary="Login Web",
     *   operationId="UsersLoginWeb",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Login Web",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="email", type="string", example="nama@mail.com"),
	 *				@SWG\Property(property="password", type="string", example="nama123")
     *          ),
     *      )
     * )
     *
     */

    public function LoginWeb (Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email|max:255',
                'password' => 'required|string|min:6',
            ]);

            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors(), $validator->errors());
                return response()->json($rslt, 400);
            }
            
            $py = $request->program_year;

            // var_dump('test');
            $credentials = request(['email', 'password']);
            if (! $token = auth()->attempt($credentials)) {                
                $rslt =  $this->ResultReturn(401, 'Incorrect Email & password', 'Unauthorized');
                return response()->json($rslt, 401);
            }else{
                $getUser = User::where('email', '=', $request->email)->first();  
                // var_dump($getUser);
                $objUser = ['id'=>$getUser->id,'employee_no'=>$getUser->employee_no,'role'=>$getUser->role,'name'=>$getUser->name,
                'email'=>$getUser->email,'email_verified_at'=>$getUser->email_verified_at];

                $Employee = Employee::where('nik','=',$getUser->employee_no)->first();          
                // var_dump($getff);
                // var_dump($getff->working_area);
                if($Employee){
                    
                    $EmployeeStructure = EmployeeStructure::where('nik','=',$getUser->employee_no)->first(); 
                    $EmployeePosition = EmployeePosition::where('position_no','=',$Employee->position_no)->first();
                    
                    $menuaccess = $EmployeeStructure->menu_access;
                    $arraymn = json_decode($menuaccess);

                    // $menuaccess = $getMenuUser->menu;
                    // $arraymn = json_decode($menuaccess);
                    // var_dump(count($arraymn));
                    $dataval = [];
                    $listval=array();
                    $listvalmenu=array();
                    $getmenuparent = DB::table('menu_access_parent')->get();
                    foreach ($getmenuparent as $val){
                        // var_d112ump($val->id);
                        $getmenu = DB::table('menu_access')
                        ->select('menu_access.name as title', 'menu_access.path as to')
                        ->join('menu_access_parent', 'menu_access_parent.id', '=', 'menu_access.parent_code')
                        ->where('menu_access_parent.id','=',$val->id)
                        ->wherein('menu_access.id',$arraymn)
                        ->orderby('menu_access.id','ASC')
                        ->orderby('menu_access_parent.id','DESC')
                        ->get();
                        if(count($getmenu) > 0){
                            $dataval = [ 'title'=>  $val->name, 'items'=>$getmenu, 'icon'=> $val->icon];
                            array_push($listval,$dataval);
                        }
                    }

                    
                    $fc = [];
                    $ff = [];
                    $um = [];
                    // var_dump($EmployeePosition->position_group);
                    // var_dump($EmployeePosition->position_no);
                    if($EmployeePosition->position_group == 'FIELD') {
                        if($EmployeePosition->position_no == '19'){
                            $FieldFacilitator = DB::table('main_pivots')->where([
                                'type' => 'fc_ff',
                                'key1' => $getUser->employee_no
                            ])->pluck('key2');
                            if($py){
                                $FieldFacilitator = $this->getFFListByUserPY($py);   
                            }
                            $ff = ['value_data'=>'several','ff'=>$FieldFacilitator];
                            $fc = ['value_data'=>'several','fc'=>$getUser->employee_no];
                            $um = ['value_data'=>'several','um'=>$EmployeeStructure->manager_code];
                        }else if($EmployeePosition->position_no == '20'){
                            // var_dump($getUser->employee_no);
                            $EmployeeFC = EmployeeStructure::where('manager_code','=',$getUser->employee_no)->pluck('nik')->toArray();
                            
                            $EmployeeFC = array_merge($EmployeeFC, [$getUser->employee_no]);
                            $FieldFacilitator = DB::table('main_pivots')->where([
                                'type' => 'fc_ff'
                            ])->whereIn('key1', $EmployeeFC)->pluck('key2')->toArray();
                            if($py){
                                $FieldFacilitator = $this->getFFListByUserPY($py);   
                            }
                            $FieldFacilitatorDewe = DB::table('main_pivots')->where([
                                'type' => 'fc_ff',
                                'key1' => $getUser->employee_no
                            ])->pluck('key2')->toArray();
                            $ff = ['value_data'=>'several','ff'=> array_merge($FieldFacilitator, $FieldFacilitatorDewe)];
                            $fc = ['value_data'=>'several','fc'=> $EmployeeFC];
                            $um = ['value_data'=>'several','um'=>$getUser->employee_no];
                        }else{
                            // $EmployeeUM= Employee::where('position_no','=',20)->pluck('nik');
                            // $EmployeeFC = Employee::where('position_no','=',19)->pluck('nik');
                            // $FieldFacilitator = FieldFacilitator::pluck('ff_no');  
                            $ff = ['value_data'=>'all','ff'=>[]];
                            $fc = ['value_data'=>'all','fc'=>[]];
                            $um = ['value_data'=>'all','um'=>[]];
                        }
                    }else if( $EmployeePosition->position_group == 'IT'){
                        $fc = ['value_data'=>'all','fc'=>[]];
                        $um = ['value_data'=>'all','um'=>[]];
                        $ff = ['value_data'=>'all','ff'=>[]];
                    }else{
                        $ff = ['value_data'=>'-','ff'=>[]];
                        $fc = ['value_data'=>'-','fc'=>[]];
                        $um = ['value_data'=>'-','um'=>[]];
                    }

                    $getmenulist = DB::table('menu_access')
                        ->select('menu_access.path as to')
                        ->wherein('menu_access.id',$arraymn)
                        ->orderby('menu_access.name','ASC')
                        ->get();

                    foreach( $getmenulist as $list){
                        array_push($listvalmenu,str_replace("/","",$list->to));
                    }

                    array_push($listvalmenu,'ExportDataStock');

                    array_push($listvalmenu,'GantiPassword');
                    
                    // var_dump($getff->working_area);
                    // $getDesa = Desa::select('kode_desa','name','kode_kecamatan')->where('kode_desa','=',$getff->working_area)->first(); 
                    // $getDesaList = Desa::select('kode_desa','name','kode_kecamatan','post_code')->where('kode_ta','=',$getff->target_area)->get();
                    // $dataval = [];
                    // $listval=array(); 
                    // foreach ($getDesaList as $val) { 
                    //     $getKec = Kecamatan::select('kode_kecamatan','name','kabupaten_no')->where('kode_kecamatan','=',$val->kode_kecamatan)->first();
                    //     $dataval = ['kode_desa'=>$val->kode_desa,'namaDesa'=>$val->name,'kode_kecamatan'=>$val->kode_kecamatan, 'namaKecamatan'=>$getKec->name,'namaDesaKecamatan'=>$val->name."-".$getKec->name,'post_code'=>$val->post_code];
                    //     array_push($listvaldesa, $dataval);
                    // }
                    // $getTA = TargetArea::select('area_code','name')->where('area_code','=',$getff->target_area)->first(); 
                    // $getMU = ManagementUnit::select('mu_no','name')->where('mu_no','=',$getff->mu_no)->first();

                    
                    $objUser = ['id'=>$getUser->id,'employee_no'=>$getUser->employee_no,'name'=>$getUser->name,
                    'email'=>$getUser->email,'role_group'=>$EmployeePosition->position_group, 'role'=>$Employee->position_no,'role_name'=>$EmployeePosition->name,
                    'EmployeeStructure'=>$EmployeeStructure, 'list_menu' => $listval,'list_val_menu'=> $listvalmenu,
                    // 'kode_kecamatan'=>$getKec->kode_kecamatan,'nama_kec'=>$getKec->name,
                    // 'kabupaten_no'=>$getKab->kabupaten_no,'nama_kab'=>$getKab->name,
                    // 'province_code'=>$getProv->province_code,'nama_prov'=>$getProv->name,
                    'ff'=>$ff,'fc'=>$fc,'um'=>$um,];
                }                 
              
                $usernew = ['User'=>$objUser, 'access_token' => $token, 'token_type' => 'bearer', 'expires_in' => auth()->factory()->getTTL() * 60 ];
                $rslt =  $this->ResultReturn(200, 'success', $usernew);
                return response()->json($rslt, 200);                
            }
        } catch (\Exception $ex) {
            return response()->json($ex);
        }
    }

    /**
     * @SWG\Post(
     *   path="/api/Logout",
	 *   tags={"Users"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Logout User",
     *   operationId="UsersLogout",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     * )
     *
     */

    public function Logout()
    {
        auth()->logout();
        $rslt =  $this->ResultReturn(200, 'Successfully logged out', 'Successfully logged out');
        return response()->json($rslt, 200);
    }

    /**
     * @SWG\Post(
     *   path="/api/ForgotPassword",
	 *   tags={"Users"},
     *   summary="Forgot Password User",
     *   operationId="ForgotPassword",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Forgot Password User",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="email", type="string", example="nama@mail.com")
     *          ),
     *      )
     * )
     *
     */
    public function ForgotPassword (Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email|max:255',
            ]);

            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors(), $validator->errors());
                return response()->json($rslt, 400);
            }

            $getUser = User::where('email', '=', $request->email)->first();

            if ($getUser) {                
                $newPass = $this->RandomPassword();
                User::where('email', '=', $request->email)
                    ->update(['password' => bcrypt($newPass)]);                
                $usernew = ['User'=>$getUser, 'new_password' => $newPass];
                $rslt =  $this->ResultReturn(200, 'success', $usernew);
                return response()->json($rslt, 200);
            }else{
                $rslt =  $this->ResultReturn(401, 'Incorrect Email', 'Unauthorized');
                return response()->json($rslt, 401);  
            }
            
        } catch (\Exception $ex) {
            return response()->json($ex);
        }
    }

    public function ResetPasswordUser (Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|max:255',
                'employee_no' => 'required',
            ]);

            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors(), $validator->errors());
                return response()->json($rslt, 400);
            }

            $getUser = User::where('email', '=', $request->email)->where('employee_no', '=', $request->employee_no)->first();

            if ($getUser) {                
                $newPass = '123456';
                User::where('email', '=', $request->email)->where('employee_no', '=', $request->employee_no)
                    ->update(['password' => bcrypt($newPass)]);                
                // $usernew = ['User'=>$getUser, 'new_password' => $newPass];
                $rslt =  $this->ResultReturn(200, 'success', 'success');
                return response()->json($rslt, 200);
            }else{
                $rslt =  $this->ResultReturn(401, 'Incorrect Email', 'Unauthorized');
                return response()->json($rslt, 401);  
            }
            
        } catch (\Exception $ex) {
            return response()->json($ex);
        }
    }

    /**
     * @SWG\Post(
     *   path="/api/EditProfile",
	 *   tags={"Users"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Edit Profile User",
     *   operationId="EditProfile",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Edit Profile User",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="name", type="string", example="nama"),
	 *				@SWG\Property(property="email", type="string", example="nama@mail.com"),
     *              @SWG\Property(property="password", type="string", example="1QwOp@"),
	 *				@SWG\Property(property="new_password", type="string", example="1QwOp@"),
     *              @SWG\Property(property="confirm_password", type="string", example="1QwOp@")
     *          ),
     *      )
     * )
     *
     */
    public function EditProfile (Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255',
                'password' => 'required|string|min:6',
                'new_password' => 'required|string|min:6',
                'confirm_password' => 'required|string|min:6',
            ]);

            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors(), $validator->errors());
                return response()->json($rslt, 400);
            }

            $credentials = request(['email', 'password']);
            if (! $token = auth()->attempt($credentials)) {                
                $rslt =  $this->ResultReturn(401, 'Incorrect Email & password', 'Unauthorized');
                return response()->json($rslt, 401);
            }elseif($request->new_password != $request->confirm_password){
                $rslt =  $this->ResultReturn(401, 'Incorrect New Password & Confirm password', 'Unauthorized');
                return response()->json($rslt, 401);
            }else{
                User::where('email', '=', $request->email)
                    ->update([
                        'name' => $request->name,
                        'email' => $request->email,
                        'password' => bcrypt($request->new_password)
                        ]);

                $rslt =  $this->ResultReturn(200, 'Success Update Profile', 'success');
                return response()->json($rslt, 200);                
            }
        } catch (\Exception $ex) {
            return response()->json($ex);
        }
    }

    /**
     * @SWG\Get(
     *   path="/api/GetUser",
     *   tags={"Users"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Users",
     *   operationId="GetUser",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="role",in="query",  type="string"),
     * )
     */
    public function GetUser(Request $request){
        $getmu = $request->role;
        if($getmu){$role='%'.$getmu.'%';}
        else{$role='%%';}
        try{
            // var_dump(count($GetLahanNotComplete));
            $GetUser = DB::table('users')
            ->where('users.role','like',$role)
            ->get();
            if(count($GetUser)!=0){ 
                $count = DB::table('users')
                ->where('users.role','like',$role)
                ->count();
                $data = ['count'=>$count, 'data'=>$GetUser];
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

    function RandomPassword() {
        $maxLengthPass=8;
        $pass = array(); 
        
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $char='!@#$*';
        $number='1234567890';
        
        $getNumberRandChar = rand(1,$maxLengthPass-1);
        $getNumberRandNumber = rand(1,$maxLengthPass-1);
        if($getNumberRandChar == $getNumberRandNumber){
            $getNumberRandChar = 2;
            $getNumberRandNumber = 5;
        }

        $alphaLength = strlen($alphabet) - 1;
        $charLength = strlen($char) - 1;
        $numberLength = strlen($number) - 1; 

        for ($i = 0; $i < $maxLengthPass; $i++) {
            $varPass='';
            if($getNumberRandChar == $i){
                $n = rand(0, $charLength);
                $varPass=$char[$n];
            }elseif($getNumberRandNumber == $i){
                $n = rand(0, $numberLength);
                $varPass=$number[$n];
            }else{
                $n = rand(0, $alphaLength); 
                $varPass=$alphabet[$n]; 
            }           
            $pass[] = $varPass;
        }
        return implode($pass); 
    }

    protected function RespondWithToken($token, $data)
    {
        return response()->json([
            'success'=>true,
            'data'=>$data,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ], 200);
    }
}
