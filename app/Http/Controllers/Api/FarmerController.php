<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use App\Desa;
use App\Kecamatan;
use App\Kabupaten;
use App\Province;
use App\User;
use App\Distribution;
use App\Employee;
use App\Farmer;
use App\FarmerGroups;
use App\FarmerDetail;
use App\FieldFacilitator;
use App\Lahan;
use App\LahanDetail;
use App\LogFarmerActivity;
use App\MainPivot;
use App\PlantingSocializations;
use App\PlantingHoleSurviellance;
use App\Monitoring;
use App\Monitoring2;

class FarmerController extends Controller
{
    /**
     * @SWG\Get(
     *   path="/api/GetFarmerAllAdmin",
     *   tags={"Farmers"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Farmers All Admin",
     *   operationId="GetFarmerAllAdmin",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="typegetdata",in="query", type="string"),
     *      @SWG\Parameter(name="ff",in="query", type="string"),
     *      @SWG\Parameter(name="name",in="query", type="string"),
     *      @SWG\Parameter(name="mu",in="query",  type="string"),
     *      @SWG\Parameter(name="ta",in="query", type="string"),
     *      @SWG\Parameter(name="village",in="query",  type="string"),
     *      @SWG\Parameter(name="limit",in="query", type="integer"),
     *      @SWG\Parameter(name="offset",in="query", type="integer"),
     * )
     */
    public function GetFarmerAllAdmin(Request $request){
        $typegetdata = $request->typegetdata;
        $ff = $request->ff;

        $nik = $request->nik;
        $role = $request->role;
        $py = $request->program_year;
        $getmu = $request->mu;
        $getta = $request->ta;
        $getvillage = $request->village;        
        $getname = $request->name;
        $limit = $this->limitcheck($request->limit);
        $offset =  $this->offsetcheck($limit, $request->offset);
        if($getname){$name='%'.$getname.'%';}
        else{$name='%%';}
        if($getmu){$mu='%'.$getmu.'%';}
        else{$mu='%%';}
        if($getta){$ta='%'.$getta.'%';}
        else{$ta='%%';}
        if($getvillage){$village='%'.$getvillage.'%';}
        else{$village='%%';}
            if($typegetdata == 'all' || $typegetdata == 'several'){
                $GetFarmerAllAdmin = 
                DB::table('farmers')->select('farmers.id as idTblPetani','farmers.farmer_no as kodePetani', 'farmers.name as namaPetani','farmers.user_id as ff_no', 'farmers.mou_no',
                'farmers.ktp_no as nik','desas.name as namaDesa','field_facilitators.name as ff','farmers.complete_data', 'farmers.approve', 'farmers.join_date')
                ->leftjoin('field_facilitators', 'field_facilitators.ff_no', '=', 'farmers.user_id')
                ->leftjoin('desas', 'desas.kode_desa', '=', 'farmers.village')
                ->join('main_pivots', 'main_pivots.key2', 'farmers.farmer_no')
                ->where('farmers.name', 'Like', $name)->where('farmers.mu_no', 'Like', $mu)->where('farmers.target_area', 'Like', $ta)
                ->where('farmers.village', 'Like', $village)->where('farmers.is_dell', '=', 0)->orderBy('farmers.name', 'ASC')
                ->where('main_pivots.program_year', 'LIKE', "%$py%")
                ->where('main_pivots.type', '=', "ff_farmer");
                
                if ($typegetdata == 'several' && $ff) {
                    $employee_no = Auth::user()->employee_no;
                    $employee = DB::table('employees')->where('nik', $employee_no)->first();
                    $ffdecode = [];
                    if ($employee->position_no == 19) {
                        $ffdecode = DB::table('main_pivots')->where([
                                'type' => 'fc_ff',
                                'key1' => $employee_no,
                                ['program_year','like', "%$py%"]
                            ])->pluck('key2')->toArray();
                    }
                    if ($employee->position_no == 20) {
                        $list_fc = DB::table('employee_structure')->where('manager_code','=',$employee_no)->pluck('nik')->toArray();
                        $ffdecode = DB::table('main_pivots')->where([
                                'type' => 'fc_ff',
                                ['program_year','like', "%$py%"]
                            ])
                            ->whereIn('key1', array_merge($list_fc, [$employee_no]))
                            ->pluck('key2')->toArray();
                    }
                    if (count($ffdecode) == 0) {
                        // var_dump($ff);
                        $ffdecode = (explode(",",$ff));
                    }
                    // var_dump($ffdecode);
                    $GetFarmerAllAdmin = $GetFarmerAllAdmin
                        ->wherein('main_pivots.key1',$ffdecode);
                }
                    
                $farmer_no_all = $GetFarmerAllAdmin->pluck('kodePetani')->toArray();
                $GetFarmerAllAdmin = $GetFarmerAllAdmin->groupBy('farmers.farmer_no')->get();

                $dataval = [];
                $listval=array(); 
                foreach ($GetFarmerAllAdmin as $val) {
                    $status = '';
                    if($val->complete_data==1 && $val->approve==1){
                        $status = 'Sudah Verifikasi';
                    }else if($val->complete_data==1 && $val->approve==0){
                        $status = 'Belum Verifikasi';
                    }else{
                        $status = 'Belum Lengkap';
                    }
                    $dataval = ['idTblPetani'=>$val->idTblPetani,'kode'=>$val->kodePetani, 'nama'=>$val->namaPetani, 
                    'nik'=>$val->nik, 'desa' => $val->namaDesa, 'user' => $val->ff, 'status' => $status, 'mou_no' => $val->mou_no, 'join_date' => $val->join_date, 'ff_no' => $val->ff_no];
                    // if (isset($request->program_year)) {
                    //     if ($request->program_year == $this->getProgramYearFarmer($val->mou_no)) {
                    //         array_push($listval, $dataval);
                    //     }
                    // } else {
                        array_push($listval, $dataval);
                    // }
                }
                //fillter Total Farmer
                $farmers = DB::table('farmers')->where([
                ['farmers.program_year','like',"%$py%"]
                ]);

                if(count($GetFarmerAllAdmin)!=0){
                        $count = count($listval);
                        
                        $petaniBelumVerif = DB::table('farmers')->join('main_pivots', 'main_pivots.key2', 'farmers.farmer_no')->whereIn('farmers.farmer_no', $farmer_no_all)->where('main_pivots.type', 'ff_farmer')->where('main_pivots.program_year', 'LIKE', "%$py%")->where('approve', 0 )->count();
                        $petaniBelumVerifi = DB::table('farmers')->whereIn('farmers.farmer_no', $farmer_no_all)->where('approve', 0 )->count();
                        $petaniSudahVerif = DB::table('farmers')->join('main_pivots', 'main_pivots.key2', 'farmers.farmer_no')->whereIn('farmer_no', $farmer_no_all)->where('main_pivots.type', 'ff_farmer')->where('main_pivots.program_year', 'LIKE', "%$py%")->where('approve', 1)->count();
                        $petaniBelumLengkap = DB::table('farmers')->join('main_pivots', 'main_pivots.key2', 'farmers.farmer_no')->whereIn('farmer_no', $farmer_no_all)->where('main_pivots.type', 'ff_farmer')->where('main_pivots.program_year', 'LIKE', "%$py%")->where('complete_data', 0)->count();
                    $data = [
                        'petani_belum_verif' => $petaniBelumVerif ?? 0,
                        'petani_belum_verifi' => $petaniBelumVerifi ?? 0,
                        'petani_sudah_verif' => $petaniSudahVerif ?? 0,
                        'petani_belum_lengkap' => $petaniBelumLengkap ?? 0,
                        'count'=>$count, 'data'=>$listval, 'ff'=>$ff,
                    
                    
                    ];
                    $rslt =  $this->ResultReturn(200, 'success', $data);
                    return response()->json($rslt, 200);  
                }
                else{
                    $rslt =  $this->ResultReturn(404, 'doesnt match data', 'doesnt match data');
                    return response()->json($rslt, 404);
                }
            }
            else{
                $rslt =  $this->ResultReturn(404, 'doesnt match data', 'doesnt match data');
                return response()->json($rslt, 404);
            }
             
            // Farmer::where('name', 'Like', $name)->where('mu_no', 'Like', $mu)->where('target_area', 'Like', $ta)->where('village', 'Like', $village)->where('is_dell', '=', 0)->orderBy('name', 'ASC')->get();
        //get Farmer Count
        
        
        
    }

    public function GetFarmerAllAdminDev(Request $request){
        $typegetdata = $request->typegetdata;
        $ff = $request->ff;
        $nik = $request->nik;
        $role = $request->role;
        $py = $request->program_year;
        $getmu = $request->mu;
        $getta = $request->ta;
        $getvillage = $request->village;        
        $getname = $request->name;
        $limit = $this->limitcheck($request->limit);
        $offset =  $this->offsetcheck($limit, $request->offset);
        if($getname){$name='%'.$getname.'%';}
        else{$name='%%';}
        if($getmu){$mu='%'.$getmu.'%';}
        else{$mu='%%';}
        if($getta){$ta='%'.$getta.'%';}
        else{$ta='%%';}
        if($getvillage){$village='%'.$getvillage.'%';}
        else{$village='%%';}
            if($typegetdata == 'all' || $typegetdata == 'several'){
                $GetFarmerAllAdmin = 
                DB::table('farmers')->select('farmers.id as idTblPetani','farmers.farmer_no as kodePetani', 'farmers.name as namaPetani','farmers.user_id as ff_no', 'farmers.mou_no',
                'farmers.ktp_no as nik','desas.name as namaDesa','field_facilitators.name as ff','farmers.complete_data', 'farmers.approve', 'farmers.join_date')
                ->leftjoin('field_facilitators', 'field_facilitators.ff_no', '=', 'farmers.user_id')
                ->leftjoin('desas', 'desas.kode_desa', '=', 'farmers.village')
                ->join('main_pivots', 'main_pivots.key2', 'farmers.farmer_no')
                ->where('farmers.name', 'Like', $name)->where('farmers.mu_no', 'Like', $mu)->where('farmers.target_area', 'Like', $ta)
                ->where('farmers.village', 'Like', $village)->where('farmers.is_dell', '=', 0)->orderBy('farmers.name', 'ASC')
                ->where('main_pivots.program_year', 'LIKE', "%$py%")
                ->where('main_pivots.type', '=', "ff_farmer");
                
                if ($typegetdata == 'several' && $ff) {
                    $employee_no = Auth::user()->employee_no;
                    $employee = DB::table('employees')->where('nik', $employee_no)->first();
                    $ffdecode = [];
                    if ($employee->position_no == 19) {
                        $ffdecode = DB::table('main_pivots')->where([
                                'type' => 'fc_ff',
                                'key1' => $employee_no,
                                ['program_year','like', "%$py%"]
                            ])->pluck('key2')->toArray();
                    }
                    if ($employee->position_no == 20) {
                        $list_fc = DB::table('employee_structure')->where('manager_code','=',$employee_no)->pluck('nik')->toArray();
                        $ffdecode = DB::table('main_pivots')->where([
                                'type' => 'fc_ff',
                                ['program_year','like', "%$py%"]
                            ])
                            ->whereIn('key1', array_merge($list_fc, [$employee_no]))
                            ->pluck('key2')->toArray();
                    }
                    if (count($ffdecode) == 0) {
                        // var_dump($ff);
                        $ffdecode = (explode(",",$ff));
                    }
                    // var_dump($ffdecode);
                    $GetFarmerAllAdmin = $GetFarmerAllAdmin
                        ->wherein('main_pivots.key1',$ffdecode);
                }
                
                $GetFarmerAllAdmin = $GetFarmerAllAdmin->groupBy('farmers.farmer_no')->get();

                $dataval = [];
                $listval=array(); 
                foreach ($GetFarmerAllAdmin as $val) {
                    $status = '';
                    if($val->complete_data==1 && $val->approve==1){
                        $status = 'Sudah Verifikasi';
                    }else if($val->complete_data==1 && $val->approve==0){
                        $status = 'Belum Verifikasi';
                    }else{
                        $status = 'Belum Lengkap';
                    }
                    $dataval = ['idTblPetani'=>$val->idTblPetani,'kode'=>$val->kodePetani, 'nama'=>$val->namaPetani, 
                    'nik'=>$val->nik, 'desa' => $val->namaDesa, 'user' => $val->ff, 'status' => $status, 'mou_no' => $val->mou_no, 'join_date' => $val->join_date, 'ff_no' => $val->ff_no];
                    // if (isset($request->program_year)) {
                    //     if ($request->program_year == $this->getProgramYearFarmer($val->mou_no)) {
                    //         array_push($listval, $dataval);
                    //     }
                    // } else {
                        array_push($listval, $dataval);
                    // }
                }
                if(count($GetFarmerAllAdmin)!=0){
                        $count = count($listval);
                    
                        $farmer_no_all = Farmer::pluck('farmers.farmer_no')->toArray();
                        
                        $petaniBelumVerif = DB::table('farmers')->join('main_pivots', 'main_pivots.key2', 'farmers.farmer_no')->whereIn('farmers.farmer_no', $farmer_no_all)->where('main_pivots.type', 'ff_farmer')->where('main_pivots.program_year', 'LIKE', "%$py%")->where('approve', 0 )->count();
                        $petaniBelumVerifi = DB::table('farmers')->whereIn('farmers.farmer_no', $farmer_no_all)->where('approve', 0 )->count();
                        $petaniSudahVerif = DB::table('farmers')->join('main_pivots', 'main_pivots.key2', 'farmers.farmer_no')->whereIn('farmer_no', $farmer_no_all)->where('main_pivots.type', 'ff_farmer')->where('main_pivots.program_year', 'LIKE', "%$py%")->where('approve', 1)->count();
                        $petaniBelumLengkap = DB::table('farmers')->join('main_pivots', 'main_pivots.key2', 'farmers.farmer_no')->whereIn('farmer_no', $farmer_no_all)->where('main_pivots.type', 'ff_farmer')->where('main_pivots.program_year', 'LIKE', "%$py%")->where('complete_data', 0)->count();
                    $data = [
                        'petani_belum_verif' => $petaniBelumVerif ?? 0,
                        'petani_belum_verifi' => $petaniBelumVerifi ?? 0,
                        'petani_sudah_verif' => $petaniSudahVerif ?? 0,
                        'petani_belum_lengkap' => $petaniBelumLengkap ?? 0,
                        'count'=>$count, 'data'=>$listval, 'ff'=>$ff,
                    
                    
                    ];
                    $rslt =  $this->ResultReturn(200, 'success', $data);
                    return response()->json($rslt, 200);  
                }
                else{
                    $rslt =  $this->ResultReturn(404, 'doesnt match data', 'doesnt match data');
                    return response()->json($rslt, 404);
                }
            }
            else{
                $rslt =  $this->ResultReturn(404, 'doesnt match data', 'doesnt match data');
                return response()->json($rslt, 404);
            }
             
            // Farmer::where('name', 'Like', $name)->where('mu_no', 'Like', $mu)->where('target_area', 'Like', $ta)->where('village', 'Like', $village)->where('is_dell', '=', 0)->orderBy('name', 'ASC')->get();
        //get Farmer Count
        
    }
    public function GetFarmerNoAll(Request $request){
        $typegetdata = $request->typegetdata;
        $ff = $request->ff;

        $nik = $request->nik;
        $role = $request->role;

        $getmu = $request->mu;
        $getta = $request->ta;
        $getvillage = $request->village;        
        $getname = $request->name;
        $limit = $this->limitcheck($request->limit);
        $offset =  $this->offsetcheck($limit, $request->offset);
        if($getname){$name='%'.$getname.'%';}
        else{$name='%%';}
        if($getmu){$mu='%'.$getmu.'%';}
        else{$mu='%%';}
        if($getta){$ta='%'.$getta.'%';}
        else{$ta='%%';}
        if($getvillage){$village='%'.$getvillage.'%';}
        else{$village='%%';}
        try{
            if($typegetdata == 'all' || $typegetdata == 'several'){
                if($typegetdata == 'all'){
                    $GetFarmerAllAdmin = 
                    DB::table('farmers')->select('farmers.id as idTblPetani','farmers.farmer_no as kodePetani', 'farmers.name as namaPetani',
                    'farmers.ktp_no as nik')
                    ->leftjoin('field_facilitators', 'field_facilitators.ff_no', '=', 'farmers.user_id')
                    ->leftjoin('desas', 'desas.kode_desa', '=', 'farmers.village')
                    ->where('farmers.name', 'Like', $name)->where('farmers.mu_no', 'Like', $mu)->where('farmers.target_area', 'Like', $ta)
                    ->where('farmers.village', 'Like', $village)->where('farmers.is_dell', '=', 0)->orderBy('farmers.name', 'ASC')->get();
               
                }else{
                    // var_dump($ff);
                    $ffdecode = (explode(",",$ff));
                    // var_dump($ffdecode);
                    $GetFarmerAllAdmin = 
                    DB::table('farmers')->select('farmers.id as idTblPetani','farmers.farmer_no as kodePetani', 'farmers.name as namaPetani',
                    'farmers.ktp_no as nik')
                    ->leftjoin('field_facilitators', 'field_facilitators.ff_no', '=', 'farmers.user_id')
                    ->leftjoin('desas', 'desas.kode_desa', '=', 'farmers.village')
                    ->wherein('farmers.user_id',$ffdecode)
                    ->where('farmers.name', 'Like', $name)->where('farmers.mu_no', 'Like', $mu)->where('farmers.target_area', 'Like', $ta)
                    ->where('farmers.village', 'Like', $village)->where('farmers.is_dell', '=', 0)->orderBy('farmers.name', 'ASC')->get();
               
                }
               

                if(count($GetFarmerAllAdmin)!=0){
                    if($typegetdata == 'all'){
                        $count =
                        DB::table('farmers')
                        ->leftjoin('field_facilitators', 'field_facilitators.ff_no', '=', 'farmers.user_id')
                        ->leftjoin('desas', 'desas.kode_desa', '=', 'farmers.village')
                        ->where('farmers.name', 'Like', $name)->where('farmers.mu_no', 'Like', $mu)->where('farmers.target_area', 'Like', $ta)
                        ->where('farmers.village', 'Like', $village)->where('farmers.is_dell', '=', 0)->count();
                    }else{
                        $ffdecode = (explode(",",$ff));
                        $count =
                        DB::table('farmers')
                        ->leftjoin('field_facilitators', 'field_facilitators.ff_no', '=', 'farmers.user_id')
                        ->leftjoin('desas', 'desas.kode_desa', '=', 'farmers.village')
                        ->wherein('farmers.user_id',$ffdecode)
                        ->where('farmers.name', 'Like', $name)->where('farmers.mu_no', 'Like', $mu)->where('farmers.target_area', 'Like', $ta)
                        ->where('farmers.village', 'Like', $village)->where('farmers.is_dell', '=', 0)->count();
                    } 
                    
                    $data = ['count'=>$count, 'data'=>$GetFarmerAllAdmin, 'ff'=>$ff];
                    $rslt =  $this->ResultReturn(200, 'success', $data);
                    return response()->json($rslt, 200);  
                }
                else{
                    $rslt =  $this->ResultReturn(404, 'doesnt match data', 'doesnt match data');
                    return response()->json($rslt, 404);
                }
            }
            else{
                $rslt =  $this->ResultReturn(404, 'doesnt match data', 'doesnt match data');
                return response()->json($rslt, 404);
            }
             
            // Farmer::where('name', 'Like', $name)->where('mu_no', 'Like', $mu)->where('target_area', 'Like', $ta)->where('village', 'Like', $village)->where('is_dell', '=', 0)->orderBy('name', 'ASC')->get();
             
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }

    public function ExportFarmerAllAdmin(Request $request)
    {
        $typegetdata = $request->typegetdata;
        $ff = $request->ff;
        
        $py = $request->program_year;

        $getmu = $request->mu;
        $getta = $request->ta;
        $getvillage = $request->village;        
        $getname = $request->name;
        if($getname){$name='%'.$getname.'%';}
        else{$name='%%';}
        if($getmu){$mu='%'.$getmu.'%';}
        else{$mu='%%';}
        if($getta){$ta='%'.$getta.'%';}
        else{$ta='%%';}
        if($getvillage){$village='%'.$getvillage.'%';}
        else{$village='%%';}        
        if($typegetdata == 'all' || $typegetdata == 'several'){
            $GetFarmerAllAdmin = DB::table('farmers')
                ->leftjoin('field_facilitators', 'field_facilitators.ff_no', '=', 'farmers.user_id')
                ->leftJoin('managementunits', 'managementunits.mu_no', 'field_facilitators.mu_no')
                ->leftJoin('target_areas', 'target_areas.area_code', 'field_facilitators.target_area')
                ->leftjoin('desas', 'desas.kode_desa', '=', 'field_facilitators.working_area')
                ->leftjoin('employees', 'employees.nik', '=', 'field_facilitators.fc_no')
                ->select(
                    'farmers.*',
                    'managementunits.name as mu_name',
                    'target_areas.name as ta_name',
                    'desas.name as village_name',
                    'employees.name as fc_name',
                    'field_facilitators.name as ff_name',
                    'farmers.name as farmer_name',
                    'farmers.gender as farmer_gender',
                    'farmers.ktp_no as farmer_ktp',
                    'farmers.address as farmer_address',
                    'farmers.rt as farmer_rt',
                    'farmers.rw as farmer_rw',
                    'farmers.mou_no'
                )
                ->where([
                    ['farmers.name', 'LIKE', $name],
                    ['farmers.mou_no', 'LIKE', '%'.$py.'%'],
                    ['farmers.user_id', 'LIKE', 'FF%'],
                    'farmers.is_dell' => 0,
                    'farmers.approve' => 1,
                    ['field_facilitators.mu_no', 'LIKE', $mu],
                    ['field_facilitators.target_area', 'LIKE', $ta],
                    ['field_facilitators.village', 'LIKE', $village]
                ]);
           
            if($ff){
                // var_dump($ff);
                $ffdecode = (explode(",",$ff));
                // var_dump($ffdecode);
                $GetFarmerAllAdmin = $GetFarmerAllAdmin->wherein('farmers.user_id',$ffdecode);
            }
            
            $GetFarmerAllAdmin = $GetFarmerAllAdmin->get();
            
            $listval=array(); 
            foreach ($GetFarmerAllAdmin as $val) {
                $fpy = $this->getProgramYearFarmer($val->mou_no);
                if ($fpy == $py) {
                    $val->program_year = $fpy ?? $py ?? date('Y');
                    array_push($listval, $val);  
                } 
            }

            if(count($listval)!=0){

                $datas = (object)[
                    'count' => count($listval),
                    'data' => $listval
                ];                    
                // return response()->json($datas, 200);

                return view('exportpetani', [
                    'count' => count($listval),
                    'data' => $listval
                ]);
            }
            else{
                $rslt =  $this->ResultReturn(404, 'doesnt match data', 'doesnt match data');
                return response()->json($rslt, 404);
            }
        }
        else{
            $rslt =  $this->ResultReturn(404, 'doesnt match data', 'doesnt match data');
            return response()->json($rslt, 404);
        }     
    }

    public function ExportFarmerAllAdminNew(Request $req)
    {
        $farmer_no = $req->farmer_no;
        $py = $req->program_year;
        
        $datas = Farmer::
            select(
                'desas.name as village_name',
                'field_facilitators.name as ff_name',
                'field_facilitators.ff_no',
                'farmers.name as farmer_name',
                'farmers.farmer_no',
                'farmers.approve as farmer_status'
            )
            ->join('desas', 'desas.kode_desa', 'farmers.village')
            ->join('main_pivots', 'main_pivots.key2', 'farmers.farmer_no')
            ->join('field_facilitators', 'field_facilitators.ff_no', 'main_pivots.key1')
            ->where([
                'main_pivots.type' => 'ff_farmer',
                ['main_pivots.program_year', 'LIKE', "%$py%"],
                ['main_pivots.key1', 'LIKE', "FF%"]
            ])
            ->whereIn('farmers.farmer_no', $farmer_no)->get();
        foreach ($datas as $data) {
            $data->farmer_status = $data->farmer_status ? 'Sudah Verifikasi' : 'Belum Verifikasi';
            
            $fc_pivot = DB::table('main_pivots')->where([
                'type' => 'fc_ff',
                'key2' => $data->ff_no,
                ['main_pivots.program_year', 'LIKE', "%$py%"]
            ])->first();
            if ($fc_pivot) {
                // managerial
                $fc = DB::table('employees')->where('nik', $fc_pivot->key1)->first();
                $data->fc_name = $fc->name ?? '';
                if ($fc) {
                    $data->um_name = DB::table('employees')
                        ->join('employee_structure', 'employee_structure.manager_code', 'employees.nik')
                        ->where('employee_structure.nik', $fc->nik)->first()->name ?? '';
                }
                
                // working area
                $ff_wa = DB::table('ff_working_areas')
                    ->where('ff_no', $data->ff_no)
                    ->where('program_year', 'LIKE', "%$py%")
                    ->first();    
                if ($ff_wa) {
                    $data->mu_name = DB::table('managementunits')->where('mu_no', $ff_wa->mu_no)->first()->name ?? '';
                    $data->ta_name = DB::table('target_areas')->where('area_code', $ff_wa->area_code)->first()->name ?? '';
                }
                
                // lands
                $lands = DB::table('lahans')
                    ->select('lahans.approve', 'lahans.lahan_no', 'lahans.pohon_kayu','lahans.pohon_mpts', 'lahans.complete_data')
                    ->join('lahan_tutupans', 'lahan_tutupans.lahan_no', 'lahans.lahan_no')
                    ->where([
                        'lahans.farmer_no' => $data->farmer_no,
                        'lahan_tutupans.program_year' => $py
                    ])->get();
                $land_tree_codes = (array)[];
                foreach ($lands as $land) {
                    $data->total_wood += (int)$land->pohon_kayu;
                    $data->total_mpts += (int)$land->pohon_mpts;
                    $data->land_status = $land->complete_data == 0 ? 'Belum Lengkap' : ($land->approve ? 'Sudah Verifikasi' : 'Belum Verifikasi');
                    
                    $tree_codes = DB::table('lahan_details')
                        ->whereYear('lahan_details.detail_year', $py)->where('lahan_details.lahan_no', $land->lahan_no)
                        ->groupBy('lahan_details.tree_code')->pluck('lahan_details.tree_code')->toArray();
                    array_push($land_tree_codes, ...$tree_codes);
                }
                $data->seeds_name = implode(',', DB::table('trees')->whereIn('tree_code', $land_tree_codes)->groupBy('tree_code')->pluck('tree_name')->toArray());
                if (!isset($data->land_status)) $data->land_status = 'Belum Ada Data';
                $data->total_wood_mpts = ($data->total_wood ?? 0) + ($data->total_mpts ?? 0);
            }
        }
        
        return response()->json($datas);
    }

    /**
     * @SWG\Get(
     *   path="/api/GetFarmerAllTemp",
     *   tags={"Farmers"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Farmers All Temp",
     *   operationId="GetFarmerAllTemp",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="name",in="query", type="string"),
     *      @SWG\Parameter(name="user_id",in="query", type="string"),
     *      @SWG\Parameter(name="limit",in="query", type="integer"),
     *      @SWG\Parameter(name="offset",in="query", type="integer"),
     * )
     */
    public function GetFarmerAllTempDelete(Request $request){
        // $userId = $request->user_id;
        // $getname = $request->name;
        // $limit = $this->limitcheck($request->limit);
        // $offset =  $this->offsetcheck($limit, $request->offset);
        // if($getname){$name='%'.$getname.'%';}
        // else{$name='%%';}
        try{
            $GetFarmerAll = Farmer::where('is_dell', '=', 1)->where('ktp_document', '=', '-')->where('signature', '=', '-')->orderBy('name', 'ASC')->get();
            if(count($GetFarmerAll)!=0){
                $count = Farmer::where('is_dell', '=', 1)->where('ktp_document', '=', '-')->where('signature', '=', '-')->count();
                $data = ['count'=>$count, 'data'=>$GetFarmerAll];
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

    public function GetFarmerAllDesa(Request $request){
        $userId = $request->user_id;
        $getvillage = $request->village;
        $year = $request->year ?? '_';
        // $limit = $this->limitcheck($request->limit);
        // $offset =  $this->offsetcheck($limit, $request->offset);
        // if($getvillage){$village='%'.$getvillage.'%';}
        // else{$village='%%';}
        
        $GetFarmerAll = Farmer::where('is_dell',0) 
                ->where(function($query) use ($getvillage,$userId,$year) {
                    $query->where('village',$getvillage)
						->orWhere('user_id',$userId);
                })->where('mou_no','like','%'.$year.'%')->orderBy('name', 'ASC')
                ->get();

        if(count($GetFarmerAll)!=0){
            $farmerDetail = [];
            foreach($GetFarmerAll as $pIndex => $farmers){
                $detail = FarmerDetail::where(['farmer_no' => $farmers->farmer_no])->get();
                array_push($farmerDetail, ...$detail);
                
                //GET Program Year Main Pivot
                $mainPivot = MainPivot::where('key2', $farmers->farmer_no)->orderBy('id','desc')->first();
                $farmers->year = $mainPivot->program_year ?? "-";
                $farmers->user = $mainPivot->key1 ?? "-";
                $getKec = Kecamatan::where('kode_kecamatan', $farmers->kecamatan)->first();
                $getDesa = Desa::where('kode_desa', $farmers->village)->first();
                $farmers->desa = $getDesa->name." - ".$getKec->name;
            }
            
            $count = count($GetFarmerAll);
            $data = ['count'=>$count, 'data'=>$GetFarmerAll, 'detail'=>$farmerDetail];
            $rslt =  $this->ResultReturn(200, 'success', $data);
            return response()->json($rslt, 200);
        }else{
            $rslt =  $this->ResultReturn(404, 'doesnt match data', 'doesnt match data');
            return response()->json($rslt, 404);
        }
    }
    
    /**
     * @SWG\Get(
     *   path="/api/GetFarmerAll",
     *   tags={"Farmers"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Farmers All",
     *   operationId="GetFarmerAll",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="name",in="query", type="string"),
     *      @SWG\Parameter(name="user_id",in="query", required=true, type="string"),
     *      @SWG\Parameter(name="limit",in="query", type="integer"),
     *      @SWG\Parameter(name="offset",in="query", type="integer"),
     * )
     */
    public function GetFarmerAll(Request $request){
        $userId = $request->user_id;
        $getname = $request->name;
        $limit = $this->limitcheck($request->limit);
        $offset =  $this->offsetcheck($limit, $request->offset);
        if($getname){$name='%'.$getname.'%';}
        else{$name='%%';}
        try{
            $GetFarmerAll = Farmer::where('user_id', '=', $userId)->where('name', 'Like', $name)->where('is_dell', '=', 0)->orderBy('name', 'ASC')->get();
            if(count($GetFarmerAll)!=0){
                $farmerDetail = [];
                foreach($GetFarmerAll as $pIndex => $farmers){
                    $detail = FarmerDetail::where(['farmer_no' => $farmers->farmer_no])->get();
                    array_push($farmerDetail, ...$detail);
                    
                    // GET Program_Year FROM Main_Pivot
                    $mainPivot = MainPivot::where('key2', $farmers->farmer_no)->where('type', 'ff_farmer')->first();
                    $farmers->year = $mainPivot->program_year ?? "-";
                
                    $getKec = Kecamatan::where('kode_kecamatan', $farmers->kecamatan)->first();
                    $getDesa = Desa::where('kode_desa', $farmers->village)->first();
                    $farmers->desa = $getDesa->name." - ".$getKec->name;
                }

                $count = Farmer::where('user_id', '=', $userId)->where('is_dell', '=', 0)->count();
                $data = ['count'=>$count, 'data'=>$GetFarmerAll, 'detail'=>$farmerDetail];
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
     *   path="/api/GetFarmerNotComplete",
     *   tags={"Farmers"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Farmers Not Complete",
     *   operationId="GetFarmerNotComplete",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="name",in="query", type="string"),
     *      @SWG\Parameter(name="user_id",in="query", required=true, type="string"),
     *      @SWG\Parameter(name="limit",in="query", type="integer"),
     *      @SWG\Parameter(name="offset",in="query", type="integer"),
     * )
     */
    public function GetFarmerNotComplete(Request $request){
        $userId = $request->user_id;
        $getname = $request->name;
        $limit = $this->limitcheck($request->limit);
        $offset =  $this->offsetcheck($limit, $request->offset);
        if($getname){$name='%'.$getname.'%';}
        else{$name='%%';}
        try{
            $GetFarmerNotComplete = Farmer::where('user_id', '=', $userId)->where('name', 'Like', $name)->where('is_dell', '=', 0)->where('complete_data', '=', 0)->orderBy('name', 'ASC')->limit($limit)->offset($offset)->get();
            if(count($GetFarmerNotComplete)!=0){
                $count = Farmer::where('user_id', '=', $userId)->where('is_dell', '=', 0)->where('complete_data', '=', 0)->count();
                $data = ['count'=>$count, 'data'=>$GetFarmerNotComplete];
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
     *   path="/api/GetFarmerCompleteNotApprove",
     *   tags={"Farmers"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Farmers Complete Not Approve",
     *   operationId="GetFarmerCompleteNotApprove",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="name",in="query", type="string"),
     *      @SWG\Parameter(name="user_id",in="query", required=true, type="string"),
     *      @SWG\Parameter(name="limit",in="query", type="integer"),
     *      @SWG\Parameter(name="offset",in="query", type="integer"),
     * )
     */
    public function GetFarmerCompleteNotApprove(Request $request){
        $userId = $request->user_id;
        $getname = $request->name;
        $limit = $this->limitcheck($request->limit);
        $offset =  $this->offsetcheck($limit, $request->offset);
        if($getname){$name='%'.$getname.'%';}
        else{$name='%%';}
        try{
            $GetFarmerCompleteNotApprove = Farmer::where('user_id', '=', $userId)->where('name', 'Like', $name)->where('is_dell', '=', 0)->where('complete_data', '=', 1)->where('approve', '=', 0)->orderBy('name', 'ASC')->limit($limit)->offset($offset)->get();
            if(count($GetFarmerCompleteNotApprove)!=0){
                $count = Farmer::where('user_id', '=', $userId)->where('is_dell', '=', 0)->where('complete_data', '=', 1)->where('approve', '=', 0)->count();
                $data = ['count'=>$count, 'data'=>$GetFarmerCompleteNotApprove];
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
     *   path="/api/GetFarmerCompleteAndApprove",
     *   tags={"Farmers"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Farmers Complete And Approve",
     *   operationId="GetFarmerCompleteAndApprove",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="name",in="query", type="string"),
     *      @SWG\Parameter(name="user_id",in="query", required=true, type="string"),
     *      @SWG\Parameter(name="limit",in="query", type="integer"),
     *      @SWG\Parameter(name="offset",in="query", type="integer"),
     * )
     */
    public function GetFarmerCompleteAndApprove(Request $request){
        $userId = $request->user_id;
        $getname = $request->name;
        $limit = $this->limitcheck($request->limit);
        $offset =  $this->offsetcheck($limit, $request->offset);
        if($getname){$name='%'.$getname.'%';}
        else{$name='%%';}
        try{
            $GetFarmerCompleteAndApprove = Farmer::where('user_id', '=', $userId)->where('name', 'Like', $name)->where('is_dell', '=', 0)->where('complete_data', '=', 1)->where('approve', '=', 1)->orderBy('name', 'ASC')->limit($limit)->offset($offset)->get();
            if(count($GetFarmerCompleteAndApprove)!=0){
                $count = Farmer::where('user_id', '=', $userId)->where('is_dell', '=', 0)->where('complete_data', '=', 1)->where('approve', '=', 1)->count();
                $data = ['count'=>$count, 'data'=>$GetFarmerCompleteAndApprove ];
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
     *   path="/api/GetFarmerDetail",
     *   tags={"Farmers"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Get Farmer Detail",
     *   operationId="GetFarmerDetail",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="id",in="query", required=true, type="string")
     * )
     */
    public function GetFarmerDetail(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'id' => 'required'
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
            $GetFarmerDetail = 
            DB::table('farmers')->where('farmers.id', '=', $request->id)
            ->select('farmers.active','farmers.rt','farmers.rw','farmers.address','farmers.approve','farmers.birthday','farmers.city',
            'farmers.complete_data','farmers.created_at','farmers.education','farmers.ethnic','farmers.farmer_no',
            'farmers.farmer_profile','farmers.gender','farmers.group_no','farmers.id','farmers.is_dell',
            'farmers.join_date','kabupatens.kab_code','kabupatens.kabupaten_no','farmers.kecamatan','desas.kode_desa',
            'kecamatans.kode_kecamatan','desas.kode_ta','farmers.ktp_document','farmers.ktp_no','farmers.main_income',
            'farmers.main_job','farmers.marrital_status','farmers.mou_no','farmers.mu_no','farmers.name', 'farmers.nickname',
            'farmers.non_formal_education','farmers.number_family_member','farmers.origin','farmers.phone','farmers.post_code',
            'farmers.province','provinces.province_code','farmers.religion','farmers.side_income','farmers.side_job',
            'farmers.signature','farmers.target_area','farmers.updated_at','farmers.user_id','farmers.village',
            'provinces.name as namaProvinsi','kabupatens.name as namaKabupaten','kecamatans.name as namaKecamatan',
            'desas.name as namaDesa','target_areas.name as namaTa','managementunits.name as namaMu','farmer_groups.name as namaKelompok')
            ->leftjoin('provinces', 'provinces.province_code', '=', 'farmers.province')
            ->leftjoin('kabupatens', 'kabupatens.kabupaten_no', '=', 'farmers.city')
            ->leftjoin('kecamatans', 'kecamatans.kode_kecamatan', '=', 'farmers.kecamatan')
            ->leftjoin('desas', 'desas.kode_desa', '=', 'farmers.village')
            ->leftjoin('target_areas', 'target_areas.area_code', '=', 'farmers.target_area')
            ->leftjoin('managementunits', 'managementunits.mu_no', '=', 'farmers.mu_no')
            ->leftjoin('farmer_groups', 'farmer_groups.group_no', '=', 'farmers.group_no')
            ->first();
            if($GetFarmerDetail){
                $rslt =  $this->ResultReturn(200, 'success', $GetFarmerDetail);
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
    
    public function GetDetailFarmer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'farmer_no' => 'required'
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        }
            
        $FarmerDetail = DB::table('farmers')->where('farmers.farmer_no', '=', $request->farmer_no)
            ->select('farmers.*')
            ->leftjoin('provinces', 'provinces.province_code', '=', 'farmers.province')
            ->leftjoin('kabupatens', 'kabupatens.kabupaten_no', '=', 'farmers.city')
            ->leftjoin('kecamatans', 'kecamatans.kode_kecamatan', '=', 'farmers.kecamatan')
            ->leftjoin('desas', 'desas.kode_desa', '=', 'farmers.village')
            ->leftjoin('target_areas', 'target_areas.area_code', '=', 'farmers.target_area')
            ->leftjoin('managementunits', 'managementunits.mu_no', '=', 'farmers.mu_no')
            ->leftjoin('farmer_groups', 'farmer_groups.group_no', '=', 'farmers.group_no')
            ->leftjoin('field_facilitators', 'field_facilitators.ff_no', '=', 'farmers.user_id')
            ->first();
            
        if($FarmerDetail){
            $rslt =  $this->ResultReturn(200, 'success', $GetFarmerDetail);
            return response()->json($rslt, 200);  
        }
        else{
            $rslt =  $this->ResultReturn(404, 'doesnt match data', 'doesnt match data');
            return response()->json($rslt, 404);
        } 
    }
    
    public function GetFarmerDetailWeb(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'id' => 'required'
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
            $GetFarmerDetail = 
            DB::table('farmers')->where('farmers.id', '=', $request->id)
            ->select('farmers.active','farmers.rt','farmers.rw','farmers.address','farmers.approve','farmers.birthday','farmers.city',
            'farmers.complete_data','farmers.created_at','farmers.education','farmers.ethnic','farmers.farmer_no',
            'farmers.farmer_profile','farmers.gender','farmers.group_no','farmers.id','farmers.is_dell',
            'farmers.join_date','kabupatens.kab_code','kabupatens.kabupaten_no','farmers.kecamatan','desas.kode_desa',
            'kecamatans.kode_kecamatan','desas.kode_ta','farmers.ktp_document','farmers.ktp_no','farmers.main_income',
            'farmers.main_job','farmers.marrital_status','farmers.mou_no','farmers.mu_no','farmers.name', 'farmers.nickname',
            'farmers.non_formal_education','farmers.number_family_member','farmers.origin','farmers.phone','farmers.post_code',
            'farmers.province','provinces.province_code','farmers.religion','farmers.side_income','farmers.side_job',
            'farmers.signature','farmers.target_area','farmers.updated_at','farmers.user_id','farmers.village',
            'provinces.name as namaProvinsi','kabupatens.name as namaKabupaten','kecamatans.name as namaKecamatan',
            'desas.name as namaDesa','target_areas.name as namaTa','managementunits.name as namaMu','farmer_groups.name as namaKelompok', 'field_facilitators.id as ff_id', 'field_facilitators.ff_no', 'field_facilitators.name as ff_name')
            ->leftjoin('provinces', 'provinces.province_code', '=', 'farmers.province')
            ->leftjoin('kabupatens', 'kabupatens.kabupaten_no', '=', 'farmers.city')
            ->leftjoin('kecamatans', 'kecamatans.kode_kecamatan', '=', 'farmers.kecamatan')
            ->leftjoin('desas', 'desas.kode_desa', '=', 'farmers.village')
            ->leftjoin('target_areas', 'target_areas.area_code', '=', 'farmers.target_area')
            ->leftjoin('managementunits', 'managementunits.mu_no', '=', 'farmers.mu_no')
            ->leftjoin('farmer_groups', 'farmer_groups.group_no', '=', 'farmers.group_no')
            ->leftjoin('field_facilitators', 'field_facilitators.ff_no', '=', 'farmers.user_id')
            ->first();
            
            $GetFarmerDetail->FarmerDetail = DB::table('farmer_details')->select('farmer_details.*', 'trees.tree_name')
                ->join('trees', 'trees.tree_code', '=', 'farmer_details.tree_code')
                ->where('farmer_no', '=', $GetFarmerDetail->farmer_no)
                ->get();
            
            if($GetFarmerDetail){
                $rslt =  $this->ResultReturn(200, 'success', $GetFarmerDetail);
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
     *   path="/api/GetFarmerDetailKtpNo",
     *   tags={"Farmers"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Farmer Detail KtpNo",
     *   operationId="GetFarmerDetailKtpNo",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="ktp_no",in="query", required=true, type="string")
     * )
     */
    public function GetFarmerDetailKtpNo(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'ktp_no' => 'required'
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
            $GetFarmerDetail = 
            DB::table('farmers')->where('farmers.ktp_no', '=', $request->ktp_no)
            ->select('farmers.active','farmers.rt','farmers.rw','farmers.address','farmers.approve','farmers.birthday','farmers.city',
            'farmers.complete_data','farmers.created_at','farmers.education','farmers.ethnic','farmers.farmer_no',
            'farmers.farmer_profile','farmers.gender','farmers.group_no','farmers.id','farmers.is_dell',
            'farmers.join_date','kabupatens.kab_code','kabupatens.kabupaten_no','farmers.kecamatan','desas.kode_desa',
            'kecamatans.kode_kecamatan','desas.kode_ta','farmers.ktp_document','farmers.ktp_no','farmers.main_income',
            'farmers.main_job','farmers.marrital_status','farmers.mou_no','farmers.mu_no','farmers.name',
            'farmers.non_formal_education','farmers.number_family_member','farmers.origin','farmers.phone','farmers.post_code',
            'farmers.province','provinces.province_code','farmers.religion','farmers.side_income','farmers.side_job',
            'farmers.signature','farmers.target_area','farmers.updated_at','farmers.user_id','farmers.village',
            'provinces.name as namaProvinsi','kabupatens.name as namaKabupaten','kecamatans.name as namaKecamatan',
            'desas.name as desa','target_areas.name as namaTa','managementunits.name as namaMu','farmer_groups.name as namaKelompok')
            ->leftjoin('provinces', 'provinces.province_code', '=', 'farmers.province')
            ->leftjoin('kabupatens', 'kabupatens.kabupaten_no', '=', 'farmers.city')
            ->leftjoin('kecamatans', 'kecamatans.kode_kecamatan', '=', 'farmers.kecamatan')
            ->leftjoin('desas', 'desas.kode_desa', '=', 'farmers.village')
            ->leftjoin('target_areas', 'target_areas.area_code', '=', 'farmers.target_area')
            ->leftjoin('managementunits', 'managementunits.mu_no', '=', 'farmers.mu_no')
            ->leftjoin('farmer_groups', 'farmer_groups.group_no', '=', 'farmers.group_no')
            ->first();
            if($GetFarmerDetail){
                // GET Program_Year FROM Main_Pivot
                $mainPivot = MainPivot::where('key1', 'farmers.user_id')->where('key2', 'farmers.farmer_no')->where('type', 'ff_farmer')->first();
                $GetFarmerDetail->year = $mainPivot->program_year ?? "-";
                
                // $getKec = Kecamatan::where('kode_kecamatan', 'farmers.kecamatan')->first();
                // $getDesa = Desa::where('kode_desa', 'farmers.village')->first();
                // $GetFarmerDetail->desa = $getDesa ?? "";
                
                $rslt =  $this->ResultReturn(200, 'success', $GetFarmerDetail);
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
     *   path="/api/GetFarmerNoDropDown",
     *   tags={"Farmers"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get FarmerNumber DropDown",
     *   operationId="GetFarmerNoDropDown",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="user_id",in="query", required=true, type="string"),
     *      @SWG\Parameter(name="name",in="query", type="string")
     * )
     */
    public function GetFarmerNoDropDown(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'user_id' => 'required'
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors(), $validator->errors());
                return response()->json($rslt, 400);
            }
            $GetFarmerNoDropDown = 
            DB::table('farmers')->select('id', 'farmer_no', 'name', 'user_id')->where('user_id', '=', $request->user_id)
            ->where('name', 'Like', '%'.$request->name.'%')->orderBy('name', 'ASC')->limit(10)->get();
            if($GetFarmerNoDropDown){
                $rslt =  $this->ResultReturn(200, 'success', $GetFarmerNoDropDown);
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
     *   path="/api/AddMandatoryFarmer",
	 *   tags={"Farmers"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Add Mandatory Farmer",
     *   operationId="AddMandatoryFarmer",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Add Mandatory Farmer",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
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
     *              @SWG\Property(property="join_date", type="date", example="2021-03-20"),
     *              @SWG\Property(property="number_family_member", type="int", example="2"),   
     *              @SWG\Property(property="mu_no", type="string", example="024"),
     *              @SWG\Property(property="target_area", type="string", example="test"),
     *              @SWG\Property(property="active", type="int", example="1"),
     *              @SWG\Property(property="user_id", type="string", example="U0002"),
     *              @SWG\Property(property="ktp_document", type="int", example="test"),
     *          ),
     *      )
     * )
     *
     */
    public function AddMandatoryFarmer(Request $request){
        try{
            // date_default_timezone_set("Asia/Bangkok");

            $validator = Validator::make($request->all(), [
                'ktp_no' => 'required|max:255|unique:farmers',
                'name' => 'required|max:255',
                'nickname' => 'required|max:255',
                'birthday' => 'required|max:255',
                'religion' => 'required|max:255',
                'rt' => 'required',
                'rw' => 'required',
                'address' => 'required|max:255',
                'village' => 'required|max:255',
                'marrital_status' => 'required|max:255',
                'phone' => 'required|max:255',
                'ethnic' => 'required|max:255',
                'origin' => 'required|max:255',
                'gender' => 'required|max:255',
                'join_date' => 'required|max:255',
                'number_family_member' => 'required|max:11',              
                'mu_no' => 'required|max:255',
                'target_area' => 'required|max:255',
                'active' => 'required|max:1',
                'user_id' => 'required|max:11'              
            ]);

            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }

            $getLastIdFarmer = Farmer::orderBy('farmer_no','desc')->first(); 
            if($getLastIdFarmer){
                $farmerno = 'F'.str_pad(((int)substr($getLastIdFarmer->farmer_no,-8) + 1), 8, '0', STR_PAD_LEFT);
            }else{
                $farmerno = 'F00000001';
            }
            $day = Carbon::now()->format('d');
            $month = Carbon::now()->format('m');
            $year = Carbon::now()->format('Y');
            $yearbefore = $year - 1;
            
            if($month < 5){
                $mou_no = '3112'.$yearbefore.'_'.$request->ktp_no;
            }else{
                $mou_no = $day.$month.$year.'_'.$request->ktp_no;    
            }

            

            // var_dump( $mou_no);
            $getDesa = Desa::select('kode_desa','name','kode_kecamatan', 'post_code')->where('kode_desa','=',$request->village)->first(); 
            $getKec = Kecamatan::select('kode_kecamatan','name','kabupaten_no')->where('kode_kecamatan','=',$getDesa->kode_kecamatan)->first(); 
            $getKab = Kabupaten::select('kabupaten_no','name','province_code')->where('kabupaten_no','=',$getKec->kabupaten_no)->first(); 
            $getProv = Province::select('province_code','name')->where('province_code','=',$getKab->province_code)->first();
            // $farmercount = Farmer::count();
            // $farmerno = 'F'.str_pad($farmercount+1, 8, '0', STR_PAD_LEFT);

            // var_dump($getDesa);
            $signature = $this->ReplaceNull($request->signature, 'string');
            // $post_code = $this->ReplaceNull($request->post_code, 'string');
            $group_no = $this->ReplaceNull($request->group_no, 'string');
            $main_income = $this->ReplaceNull($request->main_income, 'int');
            $side_income = $this->ReplaceNull($request->side_income, 'int');
            $main_job = $this->ReplaceNull($request->main_job, 'string');
            $side_job = $this->ReplaceNull($request->side_job, 'string');
            $education = $this->ReplaceNull($request->education, 'string');
            $non_formal_education = $this->ReplaceNull($request->non_formal_education, 'string');
            $farmer_profile = $this->ReplaceNull($request->farmer_profile, 'string');
            $ktp_document = $this->ReplaceNull($request->ktp_document, 'string');
            $pohon = $this->ReplaceNull($request->trees, 'string');

            $complete_data = 0;
            if($group_no != "-" && $main_job != "-" && $side_job != "-" && $education != "-" && $non_formal_education != "-" && $farmer_profile != "-" && $pohon != "-" && $signature != "-" && $ktp_document != "-")
            {
               $complete_data = 1;
            }

            // var_dump('test');
            Farmer::create([
                'farmer_no' => $farmerno,
                'name' => $request->name,
                'nickname' => $request->nickname,
                'birthday' => $request->birthday,
                'religion' => $request->religion,
                'ethnic' => $request->ethnic,
                'origin' => $request->origin,
                'gender' => $request->gender,
                'join_date' => $request->join_date,
                'number_family_member' => $request->number_family_member,
                'ktp_no' => $request->ktp_no,
                'phone' => $request->phone,
                'rt' => $request->rt,
                'rw' => $request->rw,
                'address' => $request->address,
                'village' => $request->village,
                'kecamatan' => $getKec->kode_kecamatan,
                'city' => $getKab->kabupaten_no,
                'province' => $getProv->province_code,
                'mu_no' => $request->mu_no,
                'target_area' => $request->target_area,
                'active' => $request->active,
                'user_id' => $request->user_id,
                'ktp_document' => $ktp_document,
                'marrital_status' => $request->marrital_status,

                'signature' => $signature,
                'post_code' => $getDesa->post_code,
                'group_no' => $group_no,
                'mou_no' => $mou_no,    
                
                'main_income' => $main_income,
                'side_income' => $side_income,
                'main_job' => $main_job,
                'side_job' => $side_job,
                'education' => $education,
                'non_formal_education' => $non_formal_education,
                'farmer_profile' => $farmer_profile,  
                
                // 'signature' => $this->ReplaceNull($request->signature, 'string'),
                // 'post_code' => $getDesa->post_code,
                // 'group_no' => $this->ReplaceNull($request->group_no, 'string'),
                // 'mou_no' => $mou_no,
                // 'main_income' => $this->ReplaceNull($request->main_income, 'int'),
                // 'side_income' => $this->ReplaceNull($request->side_income, 'int'),
                // 'main_job' => $this->ReplaceNull($request->main_job, 'string'),
                // 'side_job' => $this->ReplaceNull($request->side_job, 'string'),
                // 'education' => $this->ReplaceNull($request->education, 'string'),
                // 'non_formal_education' => $this->ReplaceNull($request->non_formal_education, 'string'),
                // 'farmer_profile' => $this->ReplaceNull($request->farmer_profile, 'string'),               
                
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),

                'complete_data' =>$complete_data,
                'is_dell' => 0
            ]);
            
            if($pohon != "-"){
                FarmerDetail::where('farmer_no',$farmerno)->delete();
                $trees = explode(",",$pohon);
                foreach ($trees as $tree) {
                    FarmerDetail::create([
                        'farmer_no' => $farmerno,
                        'tree_code' => $tree,
                        'amount' => 0,
                        'detail_year' => substr($request->year,-4),
                        'created_at'=>Carbon::now(),
                        'updated_at'=>Carbon::now()
                    ]);
                }
            }
            
            // CREATE Main_Pivot
            if ($request->year != null) {
                MainPivot::create([
                    'type' => 'ff_farmer',
                    'key1' => $request->user_id,
                    'key2' => $farmerno,
                    'active' => 1,
                    'program_year' => $request->year
                ]);
            }
            
            $user = Auth::user();
            
            LogFarmerActivity::create([
                 'log_time' => Carbon::now(),
                 'status_type' => 'NOTICE',
                 'activity' => 'Created' . '_' . $farmerno,
                 'farmer_no' => $farmerno,
                 'name' => $request->name,
                 'ktp_no' => $request->ktp_no,
                 'fc' => '-',
                 'ff_name' => $request->user_id,
                 'email_log' => Auth::user()->email,
                 'created_at' => Carbon::now(),
                 'updated_at' => Carbon::now()
            ]);
            
            // create Farmer Log
            $logData = [
                'status' => 'Created',
                'farmer_no' => $farmerno
            ];
            $this->createFarmerLog($logData);
            
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200); 
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }
    
    public function AddDetailFarmer(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'farmer_no' => 'required',
                'tree_code' => 'required', 
                'amount' => 'required', 
                'detail_year' => 'required',
                'user_id' => 'required',
            ]);

            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }

            // var_dump($coordinate);
            // 'farmer_no', 'tree_code', 'amount', 'detail_year', 'user_id','created_at', 'updated_at'
            FarmerDetail::create([
                'farmer_no' => $request->farmer_no,
                'tree_code' => $request->tree_code,
                'amount' => 0,
                'detail_year' => $request->detail_year,
                'user_id' => $request->user_id,

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
     *   path="/api/UpdateFarmer",
	 *   tags={"Farmers"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Update Farmer",
     *   operationId="UpdateFarmer",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Update Farmer",
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
    public function UpdateFarmer(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'farmer_no' => 'required',
                'ktp_no' => 'required|max:255',
                'name' => 'required|max:255',
                'nickname' => 'required|max:255',
                'birthday' => 'required|max:255',
                'religion' => 'required|max:255',
                'rt' => 'required',
                'rw' => 'required',
                'address' => 'required|max:255',
                'village' => 'required|max:255',
                'marrital_status' => 'required|max:255',
                'phone' => 'required|max:255',
                'ethnic' => 'required|max:255',
                'origin' => 'required|max:255',
                'gender' => 'required|max:255',
                'mou_no' => 'max:255',
                // 'join_date' => 'required|max:255',
                'number_family_member' => 'required|max:11',              
                'mu_no' => 'required|max:255',
                'target_area' => 'required|max:255',
                'active' => 'required|max:1',
                'user_id' => 'required|max:11',
                'ktp_document' => 'required'                
            ]);

            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }

            $nickname = $this->ReplaceNull($request->nickname, 'string');
            $mou_no = $this->ReplaceNull($request->mou_no, 'string');
            $signature = $this->ReplaceNull($request->signature, 'string');
            // $post_code = $this->ReplaceNull($request->post_code, 'string');
            $group_no = $this->ReplaceNull($request->group_no, 'string');
            $main_income = $this->ReplaceNull($request->main_income, 'int');
            $side_income = $this->ReplaceNull($request->side_income, 'int');
            $main_job = $this->ReplaceNull($request->main_job, 'string');
            $side_job = $this->ReplaceNull($request->side_job, 'string');
            $education = $this->ReplaceNull($request->education, 'string');
            $non_formal_education = $this->ReplaceNull($request->non_formal_education, 'string');
            $farmer_profile = $this->ReplaceNull($request->farmer_profile, 'string');
            $pohon = $this->ReplaceNull($request->trees, 'string');

            $getDesa = Desa::select('kode_desa','name','kode_kecamatan','post_code')->where('kode_desa','=',$request->village)->first(); 
            $getKec = Kecamatan::select('kode_kecamatan','name','kabupaten_no')->where('kode_kecamatan','=',$getDesa->kode_kecamatan)->first(); 
            $getKab = Kabupaten::select('kabupaten_no','name','province_code')->where('kabupaten_no','=',$getKec->kabupaten_no)->first(); 
            $getProv = Province::select('province_code','name')->where('province_code','=',$getKab->province_code)->first();
   


            Farmer::where('farmer_no', '=', $request->farmer_no)
            ->update
            ([
                'name' => $request->name,
                'nickname' => $nickname,
                'birthday' => $request->birthday,
                'religion' => $request->religion,
                'ethnic' => $request->ethnic,
                'origin' => $request->origin,
                'gender' => $request->gender,
                'number_family_member' => $request->number_family_member,
                'ktp_no' => $request->ktp_no,
                'phone' => $request->phone,
                'rt' => $request->rt,
                'rw' => $request->rw,
                'address' => $request->address,
                'village' => $request->village,
                'kecamatan' => $getKec->kode_kecamatan,
                'city' => $getKab->kabupaten_no,
                'province' => $getProv->province_code,
                'mu_no' => $request->mu_no,
                'mou_no' => $mou_no,
                'target_area' => $request->target_area,
                'active' => $request->active,
                'user_id' => $request->user_id,
                'ktp_document' => $request->ktp_document,
                'marrital_status' => $request->marrital_status,
                
                'signature' => $signature,
                'post_code' => $getDesa->post_code,
                'group_no' => $group_no,
                'main_income' => $main_income,
                'side_income' => $side_income,
                'main_job' => $main_job,
                'side_job' => $side_job,
                'education' => $education,
                'non_formal_education' => $non_formal_education,
                'farmer_profile' => $farmer_profile,               
                
                'updated_at'=>Carbon::now(),

                'is_dell' => 0
            ]);
            if($group_no != "-" && $main_job != "-" && $side_job != "-" && $education != "-" && $non_formal_education != "-" && $farmer_profile != "-" && $pohon != "-" && $signature != "-")
            {
                Farmer::where('farmer_no', '=', $request->farmer_no)
                ->update
                (['complete_data' => 1]);
            }
            
            if($pohon != "-"){
                FarmerDetail::where('farmer_no',$request->farmer_no)->delete();
                $trees = explode(",",$pohon);
                foreach ($trees as $tree) {
                    FarmerDetail::create([
                        'farmer_no' => $request->farmer_no,
                        'tree_code' => $tree,
                        'amount' => 0,
                        'detail_year' => $request->year,
                        'created_at'=>Carbon::now(),
                        'updated_at'=>Carbon::now()
                    ]);
                }
            }
            
            if ($request->year != null) {
                // UPDATE program_year FROM Main_Pivot
                // MainPivot::where('type','ff_farmer')->where('key1',$request->user_id)->where('key2',$request->farmer_no)->where('program_year','!=', $request->year)->update(
                //     [
                //         'program_year' => $request->year
                //     ]);
                    
                $pivot = MainPivot::where([
                    'key1' => $request->user_id,
                    'key2' => $request->farmer_no,
                    'type' => 'ff_farmer'
                ])->first();

                if(is_null($pivot)) {
                    $pivot = new MainPivot([
                        'type' => 'ff_farmer',
                        'program_year' => substr($request->year,-4),
                        'key1' => $request->user_id,
                        'key2' => $request->farmer_no,
                        'active' => 1
                    ]);
                    $pivot->save();
                } else {
                    $pivot->program_year = $request->year;
                    $pivot->update();
                }
            }
            
            $user = Auth::user();
            
            LogFarmerActivity::create([
                 'log_time' => Carbon::now(),
                 'status_type' => 'NOTICE',
                 'activity' => 'Updated' . '_' . $request->farmer_no,
                 'farmer_no' => $request->farmer_no,
                 'name' => $request->name,
                 'ktp_no' => $request->ktp_no,
                 'fc' => '-',
                 'ff_name' => $request->user_id,
                 'email_log' => Auth::user()->email,
                 'created_at' => Carbon::now(),
                 'updated_at' => Carbon::now()
            ]);
            
            // create Farmer Log
            $logData = [
                'status' => 'Updated',
                'farmer_no' => $request->farmer_no
            ];
            $this->createFarmerLog($logData);
            
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200); 
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }
    
    // update ff farmer in all relation
    public function UpdateFarmerFF(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'farmer_no' => 'required|exists:farmers,farmer_no',
                'ff_no' => 'required|exists:field_facilitators,ff_no'
            ]);

            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
            
            $farmer = Farmer::where('farmer_no', '=', $request->farmer_no)->update(['user_id' => $request->ff_no]);
            $lahan = Lahan::where('farmer_no', '=', $request->farmer_no)->first();
            if ($lahan) { 
                $lahan->update(['user_id' => $request->ff_no]);
                $lahanDetail = LahanDetail::where('lahan_no', '=', $lahan->lahan_no)->update(['user_id' => $request->ff_no]);
                $sosTam = PlantingSocializations::where('no_lahan', '=', $lahan->lahan_no)->update(['ff_no' => $request->ff_no]);
                $penLub = PlantingHoleSurviellance::where('lahan_no', '=', $lahan->lahan_no)->update(['user_id' => $request->ff_no]);
                $distribution = Distribution::where('farmer_no', '=', $request->farmer_no)->update(['ff_no' => $request->ff_no]);
                $monitoring = Monitoring::where('lahan_no', '=', $lahan->lahan_no)->update(['user_id' => $request->ff_no]);
                $monitoring2 = Monitoring2::where('lahan_no', '=', $lahan->lahan_no)->update(['user_id' => $request->ff_no]);
            }
            
            $rslt = $this->ResultReturn(200, 'success', [
                'farmer' => $farmer ?? 0,
                'lahan' => $lahan ? 1 : 0,
                'lahanDetail' => $lahanDetail ?? 0,
                'sosTam' => $sosTam ?? 0,
                'penLub' => $penLub ?? 0,
                'distribution' => $distribution ?? 0,
                'monitoring' => $monitoring ?? 0,
                'monitoring2' => $monitoring2 ?? 0,
            ]);
            return response()->json($rslt, 200);
        } catch (\Exception $ex){
            return response()->json($ex);
        }
    }

    /**
     * @SWG\Post(
     *   path="/api/SoftDeleteFarmer",
	 *   tags={"Farmers"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Soft Delete Farmer",
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
    public function SoftDeleteFarmer(Request $request){
        try{
            $validator = Validator::make($request->all(), [    
                'id' => 'required'
            ]);

            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
            Farmer::where('id', '=', $request->id)
                    ->update(['is_dell' => 1]);
                    
            $farmer = Farmer::where('id', '=', $request->id)->first();
            FarmerDetail::where('farmer_no', '=', $farmer->farmer_no)->delete();
            
            $lahan = Lahan::where('farmer_no', '=', $farmer->farmer_no)->first();
            LahanDetail::where('lahan_no', '=', $lahan->lahan_no)->delete();
            Lahan::where('lahan_no', '=', $lahan->lahan_no)->delete();
            
                    
            // create Farmer Log
            $logData = [
                'status' => 'Soft Deleted',
                'farmer_no' => Farmer::where('id', '=', $request->id)->first()->farmer_no ?? ''
            ];
            $this->createFarmerLog($logData);
            
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200); 
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }

    /**
     * @SWG\Post(
     *   path="/api/DeleteFarmer",
	 *   tags={"Farmers"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Delete Farmer",
     *   operationId="DeleteFarmer",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Delete Farmer",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="id", type="string", example="2")
     *          ),
     *      )
     * )
     *
     */
    public function DeleteFarmer(Request $request){
        try{
            $validator = Validator::make($request->all(), [    
                'id' => 'required'
            ]);

            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
                    
            // create Farmer Log
            $logData = [
                'status' => 'Deleted',
                'farmer_no' => Farmer::where('id', '=', $request->id)->first()->farmer_no ?? ''
            ];
            $this->createFarmerLog($logData);
            
            DB::table('farmers')->where('id', $request->id)->delete();
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200); 
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }

    /**
     * @SWG\Post(
     *   path="/api/VerificationFarmer",
	 *   tags={"Farmers"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Verification Farmer",
     *   operationId="VerificationFarmer",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Verification Farmer",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="id", type="string", example="2")
     *          ),
     *      )
     * )
     *
     */
    public function VerificationFarmer(Request $request){
            $validator = Validator::make($request->all(), [    
                'id' => 'required'
            ]);

            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
            Farmer::where('id', '=', $request->id)
                    ->update
                    ([
                        'complete_data' => 1,
                        'approve' => 1,
                    ]);
                    
            
                    
            LogFarmerActivity::create([
                 'log_time' => Carbon::now(),
                 'status_type' => 'NOTICE',
                 'activity' => 'Verified',
                 'farmer_no' => Farmer::where('id', '=', $request->id)->first()->farmer_no ?? '',
                 'name' => Farmer::where('id', '=', $request->id)->first()->name ?? '',
                 'ktp_no' => Farmer::where('id', '=', $request->id)->first()->ktp_no ?? '',
                 'fc' => '-',
                 'ff_name' => Farmer::where('id', '=', $request->id)->first()->user_id ?? '',
                 'email_log' => Auth::user()->email,
                 'created_at' => Carbon::now(),
                 'updated_at' => Carbon::now()
            ]);
                    
            // create Farmer Log
            $logData = [
                'status' => 'Verified',
                'farmer_no' => Farmer::where('id', '=', $request->id)->first()->farmer_no ?? ''
            ];
            $this->createFarmerLog($logData);
            
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200); 
    }
    
    public function UnverificationFarmer(Request $request){
        try{
            $validator = Validator::make($request->all(), [    
                'farmer_no' => 'required'
            ]);

            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
            Farmer::where('farmer_no', '=', $request->farmer_no)
                    ->update
                    ([
                        'complete_data' => 1,
                        'approve' => 0,
                    ]);
                    
            
                    
            LogFarmerActivity::create([
                 'log_time' => Carbon::now(),
                 'status_type' => 'WARNING',
                 'activity' => 'Unverified',
                 'farmer_no' => Farmer::where('farmer_no', '=', $request->farmer_no)->first()->farmer_no ?? '',
                 'name' => Farmer::where('farmer_no', '=', $request->farmer_no)->first()->name ?? '',
                 'ktp_no' => Farmer::where('farmer_no', '=', $request->farmer_no)->first()->ktp_no ?? '',
                 'fc' => '-',
                 'ff_name' => Farmer::where('farmer_no', '=', $request->farmer_no)->first()->user_id ?? '',
                 'email_log' => Auth::user()->email,
                 'created_at' => Carbon::now(),
                 'updated_at' => Carbon::now()
            ]);
                    
            // create Farmer Log
            $logData = [
                'status' => 'Unverified',
                'farmer_no' => Farmer::where('farmer_no', '=', $request->farmer_no)->first()->farmer_no ?? ''
            ];
            $this->createFarmerLog($logData);
            
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200); 
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }

    public function addFarmer(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'farmer_no' => 'required|max:255',
                'name' => 'required|max:255',
                'nickname' => 'required|max:255',
                'birthday' => 'required|max:255',
                'religion' => 'required|max:255',
                'ethnic' => 'required|max:255',
                'origin' => 'required|max:255',
                'gender' => 'required|max:255',
                'join_date' => 'required|max:255',
                'number_family_member' => 'required|max:255',
                'ktp_no' => 'required|max:255',
                'phone' => 'required|max:255',
                'address' => 'required|max:255',
                'village' => 'required|max:255',
                'kecamatan' => 'required|max:255',
                'city' => 'required|max:255',
                'province' => 'required|max:255',
                'post_code' => 'required|max:255',
                'mu_no' => 'required|max:255',
                'target_area' => 'required|max:255',
                'group_no' => 'required|max:255',
                'mou_no' => 'required|max:255',
                'main_income' => 'required|max:255',
                'side_income' => 'required|max:255',
                'active' => 'required|max:255',
                'user_id' => 'required|max:255',
                'created_at' => 'required|max:255',
                'updated_at' => 'required|max:255',
                'ktp_document' => 'required',
                'farmer_profile' => 'required',
                'marrital_status' => 'required|max:255',
                'main_job' => 'required|max:255',
                'side_job' => 'required|max:255',
                'education' => 'required|max:255',
                'non_formal_education' => 'required|max:255'
            ]);

            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors(), $validator->errors());
                return response()->json($rslt, 400);
            }

            Farmer::create([
                'farmer_no' => $farmer_no,
                'name' => $name,
                'nickname' => $nickname,
                'birthday' => $birthday,
                'religion' => $religion,
                'ethnic' => $ethnic,
                'origin' => $origin,
                'gender' => $gender,
                'join_date' => $join_date,
                'number_family_member' => $number_family_member,
                'ktp_no' => $ktp_no,
                'phone' => $phone,
                'address' => $address,
                'village' => $village,
                'kecamatan' => $kecamatan,
                'city' => $city,
                'province' => $province,
                'post_code' => $post_code,
                'mu_no' => $mu_no,
                'target_area' => $target_area,
                'group_no' => $group_no,
                'mou_no' => $mou_no,
                'main_income' => $main_income,
                'side_income' => $side_income,
                'active' => $active,
                'user_id' => $user_id,
                'created_at' => $created_at,
                'updated_at' => $updated_at,
                'ktp_document' => $ktp_document,
                'farmer_profile' => $farmer_profile,
                'marrital_status' => $marrital,
                'main_job' => $main_job,
                'side_job' => $side_job,
                'education' => $education,
                'non_formal_education' => $main_job,
                'is_dell' => 0
            ]);
                    
            // create Farmer Log
            $logData = [
                'status' => 'Created',
                'farmer_no' => $farmer_no
            ];
            $this->createFarmerLog($logData);
            
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200); 
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }

    /**
     * @SWG\Get(
     *   path="/api/GetFarmerGroupsDropDown",
     *   tags={"Farmers"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Farmer Groups DropDown",
     *   operationId="GetFarmerGroupsDropDown",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="name",in="query", type="string"),
     *      @SWG\Parameter(name="mu_no",in="query", required=true, type="string"),
     * )
     */
    public function GetFarmerGroupsDropDown(Request $request){
        $mu_no = $request->mu_no;
        $getname = $request->name;
        if($getname){$name='%'.$getname.'%';}
        else{$name='%%';}
        try{
            $GetFarmerGroupsDropDown = FarmerGroups::where('mu_no', '=', $mu_no)->where('name', 'Like', $name)->orderBy('name', 'ASC')->get();
            if(count($GetFarmerGroupsDropDown)!=0){
                $count = FarmerGroups::where('mu_no', '=', $mu_no)->where('name', 'Like', $name)->count();
                $data = ['count'=>$count, 'data'=>$GetFarmerGroupsDropDown];
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

    function test(){
        
        $oldfile = $_GET["oldfile"];
        $nama = $_GET["nama"];
        $file = $_FILES["files"];        

        if($_FILES['files']){

            $name = $_FILES["files"]["name"];
        $extensi = pathinfo($name, PATHINFO_EXTENSION);
        $avatar_tmp_name = $_FILES["files"]["tmp_name"];

            $dirUpload = "Uploads/";
            $val = 0;
            $description = 'eror file format';
            

            $file_ext=explode('.',$name);
            $file_ext=end($file_ext);
            $file_ext=strtolower($file_ext);

            $extensions= array("jpg","jpeg","png");  

            if(in_array($extensi,$extensions)== false){
                $val = $val + 1;
            }            

            if($_FILES["files"]["error"] != 0){
                $val = $val + 1;
                $description = 'Ukuran file kebesaran bung!!';
            }

            $TempName = $dirUpload.$nama.'.'.$file_ext;
            $NewName = $nama.'.'.$file_ext;

            if($val > 0){
                http_response_code(401);
                $stat = ['code'=>'401', 'description'=>$description];
                $data = ['status'=>$stat, 'result'=>'error'];
                print_r(json_encode(['success'=>false, 'data'=>$data], true));
            }else{

                if($oldfile){
                    unlink($oldfile);
                }
                
                $terupload = move_uploaded_file($avatar_tmp_name, $TempName);

                http_response_code(200);
                $stat = ['code'=>'200', 'description'=>'file upload success'];
                $data = ['status'=>$stat, 'result'=>'success'];
                print_r(json_encode(['success'=>true, 'data'=>$data ,'dirUpload'=>$dirUpload ,'nama'=>$name,'file'=>$_FILES["files"],'avatar_tmp_name'=>$avatar_tmp_name, 'TempName'=>$TempName, 'NewName'=>$NewName], true));

            }
        }
    }
    
    public function RemoveOldFarmer(Request $req) {
        try{
            $validator = Validator::make($req->all(), [
                'program_year' => 'required',
                'farmer_no' => 'required|exists:farmers,farmer_no'
            ]);

            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors(), $validator->errors());
                return response()->json($rslt, 400);
            }
            
            $farmer = Farmer::where('farmer_no', $req->farmer_no)->first();
            
            // mou no
            $mou = explode('_', $farmer->mou_no);
            if($mou[1] == $req->program_year) {
                unset($mou[1]);
                $mou = implode('_', $mou);
                
                $farmer->update(['mou_no' => $mou]);
                
                $datas = [
                    'message' => 'Berhasil remove petani dari program tahun ' . $req->program_year,
                    'mou' => $mou,
                ];
                    
                // create Farmer Log
                $logData = [
                    'status' => 'Remove Petani From '. $req->program_year,
                    'farmer_no' => $req->farmer_no
                ];
                $this->createFarmerLog($logData);
                
            
                $rslt =  $this->ResultReturn(200, 'success', $datas);
                return response()->json($rslt, 200);
            } else {
                $rslt =  $this->ResultReturn(400, 'failed', 'Petani tidak mengikuti program tahun '. $req->program_year);
                return response()->json($rslt, 400);
            }
        } catch (\Exception $ex){
            return response()->json($ex);
        }
    }
    
    public function FarmerTrashedData(){
        try{
            $GetFarmerAll = Farmer::where('is_dell', '=', 1)->orderBy('updated_at', 'desc')->orderBy('name', 'ASC')->get();
            if(count($GetFarmerAll)!=0){
                $count = count($GetFarmerAll);
                $data = ['count'=>$count, 'data'=>$GetFarmerAll];
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
    
    public function FarmerRestoreData(Request $req) {
        try{
            $validator = Validator::make($req->all(), [
                'farmer_no' => 'required|exists:farmers,farmer_no'
            ]);

            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors(), $validator->errors());
                return response()->json($rslt, 400);
            }
            
            $farmer = Farmer::where('farmer_no', $req->farmer_no)->where('is_dell', 1)->first();
            if ($farmer) {
                $farmer->update(['is_dell' => 0]);
                    
                // create Farmer Log
                $logData = [
                    'status' => 'Restore Petani',
                    'farmer_no' => $req->farmer_no
                ];
                $this->createFarmerLog($logData);
                
                $rslt =  $this->ResultReturn(200, 'success', 'Success restore farmer data.');
                return response()->json($rslt, 200);
            } else {
                $rslt =  $this->ResultReturn(404, 'doesnt match data', 'doesnt match data');
                return response()->json($rslt, 404);
            }
            
        } catch (\Exception $ex){
            return response()->json($ex);
        }
    }
    
    // Create Log for Farmer
    public function createFarmerLog($logData) {
        // get farmer data
        $farmer = Farmer::where('farmer_no', $logData['farmer_no'])->first();
        // get ff data
        if(isset($farmer->user_id)) {
            $ff = FieldFacilitator::where('ff_no', $farmer->user_id)->first();
        }
        // get fc data
        if (isset($ff->fc_no)) {
            $fc = Employee::where('nik', $ff->fc_no)->first();
        }
        // user auth data
        $user = Auth::user();
        // set message
        $message =  $logData['status'] . ' ' . 
                    ($logData['farmer_no'] ?? '-') . 
                    '[name = ' . 
                    ($farmer['name'] ?? '-') .
                    ', nickname = ' .
                    ($farmer['nickname'] ?? '-') .
                    ', ktp = ' .
                    ($farmer['ktp_no'] ?? '-') .
                    ', ff = ' . 
                    ($ff->name ?? '-') .
                    ', fc = ' . 
                    ($fc->name ?? '-') .
                    '] by ' . 
                    ($user['email'] ?? '-');
        
        
        $log = Log::channel('farmers');
        
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
    
    public function createFarmerLogDB(){
        // get farmer data
        $farmer = Farmer::where('farmer_no', $logData['farmer_no'])->first();
        // get ff data
        if(isset($farmer->user_id)) {
            $ff = FieldFacilitator::where('ff_no', $farmer->user_id)->first();
        }
        // get fc data
        if (isset($ff->fc_no)) {
            $fc = Employee::where('nik', $ff->fc_no)->first();
        }
        // user auth data
        $user = Auth::user();
        
        // set message
        $message =  $logData['status'] . ' ' . 
                    ($logData['farmer_no'] ?? '-') . 
                    '[name = ' . 
                    ($farmer['name'] ?? '-') .
                    ', nickname = ' .
                    ($farmer['nickname'] ?? '-') .
                    ', ktp = ' .
                    ($farmer['ktp_no'] ?? '-') .
                    ', ff = ' . 
                    ($ff->name ?? '-') .
                    ', fc = ' . 
                    ($fc->name ?? '-') .
                    '] by ' . 
                    ($user['email'] ?? '-');
                    
        
        
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
    
    // utility: get Program Year Farmer
    private function getProgramYearFarmer($mou_no) {
        if (substr($mou_no, 13, 1) === '_') {
            return substr($mou_no, 9, 4);
        } else {
            return substr($mou_no, 4, 4);
        }
    }
}
