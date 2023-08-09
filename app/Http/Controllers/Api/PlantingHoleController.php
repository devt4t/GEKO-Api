<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use App\User;
use App\Employee;
use App\Farmer;
use App\Lahan;
use App\LahanUmum;
use App\LahanUmumHoleDetail;
use App\FieldFacilitator;
use App\PlantingHoleSurviellance;
use App\PlantingHoleSurviellanceDetail;
use App\Distribution;
use App\DistributionDetail;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PlantingHoleController extends Controller
{
    /**
     * @SWG\Get(
     *   path="/api/GetPlantingHoleFF",
     *   tags={"PlantingHole"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get PlantingHole FF",
     *   operationId="GetSosisalisasiTanamFF",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="ff_no",in="query", required=true, type="string"),
     * )
     */
    public function GetPlantingHoleFF(Request $request){
        $ff_no = $request->ff_no;
        $getpy = $request->planting_year;
        if($getpy){$py='%'.$getpy.'%';}
        else{$py='%%';}
           
        $GetPH = DB::table('planting_hole_surviellance')
            ->select('planting_hole_surviellance.id','planting_hole_surviellance.lahan_no',
            'planting_hole_surviellance.ph_form_no','planting_hole_surviellance.planting_year','planting_hole_surviellance.total_holes', 'planting_hole_surviellance.counter_hole_standard',
            'planting_hole_surviellance.latitude', 'planting_hole_surviellance.longitude',
            'planting_hole_surviellance.is_validate','planting_hole_surviellance.validate_by',
            'planting_hole_surviellance.farmer_signature','planting_hole_surviellance.gambar1','planting_hole_surviellance.gambar2',
            'planting_hole_surviellance.gambar3',
            'planting_hole_surviellance.is_dell', 'planting_hole_surviellance.created_at',  'planting_hole_surviellance.user_id as ff_no',
            'farmers.name as nama_petani', 'field_facilitators.name as nama_ff')
            ->leftjoin('lahans', 'lahans.lahan_no', '=', 'planting_hole_surviellance.lahan_no')
            ->leftjoin('farmers', 'farmers.farmer_no', '=', 'lahans.farmer_no')
            ->leftjoin('field_facilitators', 'field_facilitators.ff_no', '=', 'planting_hole_surviellance.user_id')
            ->where('planting_hole_surviellance.is_dell','=',0)
            ->where('planting_hole_surviellance.user_id','=',$ff_no)
            ->where('planting_hole_surviellance.planting_year', 'like', $py)
            ->get();
            
        if($GetPH){
            $holDetails = [];
            foreach($GetPH as $pIndex => $hole){
                $getFF = DB::table('field_facilitators')->where('ff_no', '=', $hole->ff_no)->first();
                
                $getDetailPlantingHole = DB::table('planting_hole_details')
                ->select('planting_hole_details.id', 'planting_hole_details.ph_form_no', 'planting_hole_details.tree_code', 
                       'planting_hole_details.amount', 'planting_hole_details.created_at', 'planting_hole_details.updated_at',
                       'tree_locations.tree_name as tree_name', 'tree_locations.category as tree_category', 'tree_locations.mu_no as mu_no')
                ->leftjoin('tree_locations', 'tree_locations.tree_code', '=', 'planting_hole_details.tree_code')
                ->where([
                    'ph_form_no' => $hole->ph_form_no,
                    'mu_no' => $getFF->mu_no
                ])->get();
                array_push($holDetails, ...$getDetailPlantingHole);
                $GetPH[$pIndex]->ph_form_no = (string)$hole->ph_form_no;
            }
            
            $data = [
                'data' => $GetPH,
                'planting_hole_details' => $holDetails
            ];

            $rslt =  $this->ResultReturn(200, 'success', $data);
            return response()->json($rslt, 200);
            
        }

        // if(count($GetPH)!=0){ 
        //     $count = DB::table('planting_hole_surviellance')
        //         ->leftjoin('lahans', 'lahans.lahan_no', '=', 'planting_hole_surviellance.lahan_no')
        //         ->leftjoin('farmers', 'farmers.farmer_no', '=', 'lahans.farmer_no')
        //         ->leftjoin('field_facilitators', 'field_facilitators.ff_no', '=', 'planting_hole_surviellance.user_id')
        //         ->where('planting_hole_surviellance.is_dell','=',0)
        //         ->where('planting_hole_surviellance.user_id','=',$ff_no)
        //         ->count();
            
        //     $data = ['count'=>$count, 'data'=>$GetPH];
        //     $rslt =  $this->ResultReturn(200, 'success', $data);
        //     return response()->json($rslt, 200); 
        // }else{
        //     $rslt =  $this->ResultReturn(404, 'doesnt match data', 'doesnt match data');
        //     return response()->json($rslt, 404);
        // }
    }

    /**
     * @SWG\Get(
     *   path="/api/GetPlantingHoleAdmin",
     *   tags={"PlantingHole"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get PlantingHole Admin",
     *   operationId="GetPlantingHoleAdmin",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="typegetdata",in="query",required=true, type="string"),
     *      @SWG\Parameter(name="ff",in="query",required=true, type="string"),
     * )
     */
    public function GetPlantingHoleAdmin(Request $request){
        $py = $request->program_year;
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
        try{
            // set first query
            $GetPH = DB::table('planting_hole_surviellance')
                ->leftjoin('lahans', 'lahans.lahan_no', '=', 'planting_hole_surviellance.lahan_no')
                ->leftjoin('farmers', 'farmers.farmer_no', '=', 'lahans.farmer_no')
                ->leftjoin('field_facilitators', 'field_facilitators.ff_no', '=', 'planting_hole_surviellance.user_id')
                ->select(
                    'planting_hole_surviellance.id',
                    'planting_hole_surviellance.lahan_no',
                    'planting_hole_surviellance.is_checked',
                    'planting_hole_surviellance.ph_form_no',
                    'planting_hole_surviellance.planting_year',
                    'planting_hole_surviellance.total_holes',
                    'planting_hole_surviellance.counter_hole_standard',
                    'planting_hole_surviellance.latitude', 
                    'planting_hole_surviellance.longitude',
                    'planting_hole_surviellance.is_validate',
                    'planting_hole_surviellance.validate_by',
                    'planting_hole_surviellance.pohon_kayu',
                    'planting_hole_surviellance.pohon_mpts',
                    'planting_hole_surviellance.tanaman_bawah',
                    'planting_hole_surviellance.is_dell', 
                    'planting_hole_surviellance.created_at', 
                    'planting_hole_surviellance.user_id',
                    'farmers.name as nama_petani', 
                    'field_facilitators.name as nama_ff'
                )
                ->where([
                    ['planting_hole_surviellance.is_dell','=',0],
                    'planting_hole_surviellance.planting_year' => $py,
                    ['lahans.mu_no','like',$mu],
                    ['lahans.target_area','like',$ta],
                    ['lahans.village','like',$village]
                ]);
            
           
            if($typegetdata == 'all' || $typegetdata == 'several'){
                if($ff){
                    // set ff
                    $ffdecode = (explode(",",$ff));
                    // second query
                    $GetPH = $GetPH->wherein('planting_hole_surviellance.user_id',$ffdecode);
                }
                // last query
                $newData = $GetPH
                    ->orderBy('planting_hole_surviellance.created_at', 'DESC')
                    ->get();
                // foreach ($newData as $phIndex => $phData) {
                //     $seed = PlantingHoleSurviellanceDetail::where('ph_form_no', $phData->ph_form_no);
                //     $newData[$phIndex]->total_bibit = $seed->sum('amount');
                // }
                
                if($count = $GetPH->count()){
                    $data = ['count'=>$count, 'data'=>$newData];
                    // set response
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
                
            // var_dump(count($GetLahanNotComplete));
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }
    
    /**
     * @SWG\Get(
     *   path="/api/GetPlantingHoleDetail",
     *   tags={"PlantingHole"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get PlantingHole Detail",
     *   operationId="GetSosisalisasiTanamDetail",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="ph_form_no",in="query", required=true, type="string"),
     * )
     */
    public function GetPlantingHoleDetail(Request $request){
        $ph_form_no = $request->ph_form_no;
        try{
           
                $GetPHDetail = DB::table('planting_hole_surviellance')
                    ->select('planting_hole_surviellance.id','planting_hole_surviellance.lahan_no',
                    'planting_hole_surviellance.ph_form_no','planting_hole_surviellance.planting_year',
                    'planting_hole_surviellance.latitude', 'planting_hole_surviellance.longitude',
                    'planting_hole_surviellance.farmer_signature','planting_hole_surviellance.gambar1','planting_hole_surviellance.gambar2', 'planting_hole_surviellance.gambar3 as catatan',
                    'planting_hole_surviellance.is_validate','planting_hole_surviellance.validate_by','planting_hole_surviellance.total_holes', 'planting_hole_surviellance.counter_hole_standard',
                    'planting_hole_surviellance.is_dell', 'planting_hole_surviellance.created_at', 
                    'farmers.name as nama_petani', 'field_facilitators.name as nama_ff', 'planting_hole_surviellance.user_id')
                    ->leftjoin('lahans', 'lahans.lahan_no', '=', 'planting_hole_surviellance.lahan_no')
                    ->leftjoin('farmers', 'farmers.farmer_no', '=', 'lahans.farmer_no')
                    ->leftjoin('field_facilitators', 'field_facilitators.ff_no', '=', 'planting_hole_surviellance.user_id')
                    // ->where('planting_hole_surviellance.is_dell','=',0)
                    ->where('planting_hole_surviellance.ph_form_no','=',$ph_form_no)
                    ->first();
                
                if (isset($GetPHDetail->user_id)) {
                    $ff = FieldFacilitator::where('ff_no', $GetPHDetail->user_id)->first();
                } else{
                    $rslt =  $this->ResultReturn(404, 'doesnt match data', 'doesnt match data');
                    return response()->json($rslt, 404);
                }

                if($GetPHDetail && $ff){ 
                    $GetPHDetailList = DB::table('planting_hole_details')
                        ->select('planting_hole_details.id',
                        'planting_hole_details.ph_form_no','planting_hole_details.tree_code','tree_locations.category as tree_category',
                        'planting_hole_details.amount', 'planting_hole_details.created_at',
                        'tree_locations.tree_name as tree_name')
                        ->leftjoin('tree_locations', 'tree_locations.tree_code', '=', 'planting_hole_details.tree_code')
                        // ->where('planting_hole_surviellance.is_dell','=',0)
                        ->where('planting_hole_details.ph_form_no','=',$ph_form_no)
                        ->where('tree_locations.mu_no','=',$ff->mu_no)
                        ->get();
                    
                    $data = ['list_detail'=>$GetPHDetailList, 'data'=>$GetPHDetail];
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
    
    public function GetPlantingHoleLahanUmumAdmin(Request $request)
    {
        $py = $request->program_year;
        $typegetdata = $request->typegetdata;
        $pic = $request->created_by;
        $getmu = $request->mu;
        $getvillage = $request->village;
        if($getmu){$mu='%'.$getmu.'%';}
        else{$mu='%%';}
        if($getvillage){$village='%'.$getvillage.'%';}
        else{$village='%%';}
        if($pic){$cr='%'.$pic.'%';}
        else{$cr='%%';}
        
        $GetPHL = DB::table('lahan_umums')
            ->leftjoin('employees', 'employees.nik', '=', 'lahan_umums.employee_no')
            ->leftjoin('managementunits', 'managementunits.mu_no', '=', 'lahan_umums.mu_no')
            ->select(
                'lahan_umums.id',
                'lahan_umums.lahan_no',
                'lahan_umums.mou_no',
                'lahan_umums.program_year',
                'lahan_umums.total_holes',
                'lahan_umums.counter_hole_standard',
                'lahan_umums.employee_no',
                'lahan_umums.pic_lahan',
                'lahan_umums.mu_no',
                'lahan_umums.village',
                'lahan_umums.latitude', 
                'lahan_umums.longitude',
                'lahan_umums.is_verified',
                'lahan_umums.verified_by',
                'lahan_umums.pohon_kayu',
                'lahan_umums.pohon_mpts',
                'lahan_umums.tanaman_bawah',
                'lahan_umums.status',
                'lahan_umums.is_dell', 
                'lahan_umums.is_checked',
                'lahan_umums.created_at',
                'lahan_umums.created_by',
                'employees.name as nama_pic',
                'managementunits.name as nama_mu'
            )
            ->where([
                ['lahan_umums.is_dell', '=', 0],
                ['lahan_umums.total_holes', '>', 0],
                'lahan_umums.program_year' => $py,])
            ->where('lahan_umums.created_by', 'like', $cr);
        
        if($typegetdata == 'all' || $typegetdata == 'several'){
            if($typegetdata == 'all'){
                // second query
                $GetPH = $GetPHL->where([
                    ['lahan_umums.mu_no','like',$mu],
                    ['lahan_umums.village','like',$village]
                ]);
            }else{
                // set ff
                $picdecode = (explode(",",$pic));
                // second query
                $GetPH = $GetPHL->wherein('lahan_umums.created_by',$picdecode);
            }
            // last query
            $newData = $GetPH
                ->orderBy('lahan_umums.created_at', 'DESC')
                ->get();
            // foreach ($newData as $phIndex => $phData) {
            //     $seed = PlantingHoleSurviellanceDetail::where('ph_form_no', $phData->ph_form_no);
            //     $newData[$phIndex]->total_bibit = $seed->sum('amount');
            // }
            
            $rslt =  $this->ResultReturn(200, 'success', $newData);
            return response()->json($rslt, 200);
        }else{
            $rslt =  $this->ResultReturn(404, 'doesnt match data', 'doesnt match data');
            return response()->json($rslt, 404);
        }
    }
    
    public function GetPlantingHoleLahanUmumDetail(Request $request)
    {
        $lahanno = $request->lahan_no;
           
        $GetPHDetail = DB::table('lahan_umums')
            ->select('lahan_umums.id as idLahanUmum','lahan_umums.lahan_no as lahan_no', 'lahan_umums.program_year as program_year', 'lahan_umums.latitude as latitude', 'lahan_umums.longitude as longitude', 'lahan_umums.pic_lahan',
            'lahan_umums.mou_no as mou_no','lahan_umums.total_holes as total_holes','lahan_umums.counter_hole_standard as counter_hole_standard', 'lahan_umums.employee_no as employee_no',
            'lahan_umums.is_verified as is_verified','lahan_umums.verified_by as verified_by','lahan_umums.pohon_kayu as pohon_kayu', 'lahan_umums.pohon_mpts as pohon_mpts',
            'lahan_umums.is_dell as is_dell', 'lahan_umums.created_at as created_at', 'employees.name as nama_pic as nama_pic', 'lahan_umums.mu_no as mu_no', 'managementunits.name as mu_name', 'lahan_umums.photo_hole1 as hole1', 'lahan_umums.photo_hole2 as hole2')
            ->leftjoin('managementunits', 'managementunits.mu_no', '=', 'lahan_umums.mu_no')
            ->leftjoin('employees', 'employees.nik', '=', 'lahan_umums.employee_no')
            // ->where('planting_hole_surviellance.is_dell','=',0)
            ->where('lahan_umums.lahan_no','=',$lahanno)
            ->first();
        
        if (isset($GetPHDetail->employee_no)) {
            $ff = Employee::where('nik', $GetPHDetail->employee_no)->first();
        } else{
            $rslt =  $this->ResultReturn(404, 'doesnt match data', 'doesnt match data');
            return response()->json($rslt, 404);
        }

        if($GetPHDetail && $ff){ 
            $GetPHDetailList = DB::table('lahan_umum_hole_details')
                ->select('lahan_umum_hole_details.id',
                'lahan_umum_hole_details.lahan_no','lahan_umum_hole_details.tree_code','tree_locations.category as tree_category',
                'lahan_umum_hole_details.amount', 'lahan_umum_hole_details.created_at',
                'tree_locations.tree_name as tree_name')
                ->leftjoin('tree_locations', 'tree_locations.tree_code', '=', 'lahan_umum_hole_details.tree_code')
                // ->where('planting_hole_surviellance.is_dell','=',0)
                ->where('lahan_umum_hole_details.lahan_no','=',$lahanno)
                ->where('tree_locations.mu_no','=',$GetPHDetail->mu_no)
                ->get();
            
            $data = ['id'=>$GetPHDetail->idLahanUmum,
                     'lahan_no'=>$GetPHDetail->lahan_no,
                     'program_year'=>$GetPHDetail->program_year,
                     'latitude'=>$GetPHDetail->latitude,
                     'longitude'=>$GetPHDetail->longitude,
                     'mou_no'=>$GetPHDetail->mou_no,
                     'total_holes'=>$GetPHDetail->total_holes,
                     'counter_hole_standard'=>$GetPHDetail->counter_hole_standard,
                     'employee_no'=>$GetPHDetail->employee_no,
                     'is_verified'=>$GetPHDetail->is_verified,
                     'verified_by'=>$GetPHDetail->verified_by,
                     'pohon_kayu'=>$GetPHDetail->pohon_kayu,
                     'pohon_mpts'=>$GetPHDetail->pohon_mpts,
                     'is_dell'=>$GetPHDetail->is_dell,
                     'created_at'=>$GetPHDetail->created_at,
                     'nama_pic'=>$GetPHDetail->nama_pic,
                     'pic_lahan'=>$GetPHDetail->pic_lahan,
                     'mu_no'=>$GetPHDetail->mu_no,
                     'mu_name'=>$GetPHDetail->mu_name,
                     'photo_hole1'=>$GetPHDetail->hole1,
                     'photo_hole2'=>$GetPHDetail->hole2,
                     'list_detail'=>$GetPHDetailList];
            $rslt =  $this->ResultReturn(200, 'success', $data);
            return response()->json($rslt, 200); 
        }else{
            $rslt =  $this->ResultReturn(404, 'doesnt match data', 'doesnt match data');
            return response()->json($rslt, 404);
        }
    }
    
    /**
     * @SWG\Get(
     *   path="/api/GetPlantingHoleDetailFFNo",
     *   tags={"PlantingHole"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get PlantingHole Detail FFNo",
     *   operationId="GetPlantingHoleDetailFFNo",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="user_id",in="query", required=true, type="string"),
     * )
     */
    public function GetPlantingHoleDetailFFNo(Request $request){
        $user_id = $request->user_id;
        try{
                $ff = FieldFacilitator::where('ff_no', $user_id)->first();
                
                $GetPHDetail = DB::table('planting_hole_surviellance')
                    ->select('planting_hole_surviellance.id','planting_hole_surviellance.lahan_no',
                    'planting_hole_surviellance.ph_form_no','planting_hole_surviellance.planting_year',
                    'planting_hole_surviellance.latitude', 'planting_hole_surviellance.longitude',
                    'planting_hole_surviellance.farmer_signature','planting_hole_surviellance.gambar1','planting_hole_surviellance.gambar2',
                    'planting_hole_surviellance.is_validate','planting_hole_surviellance.validate_by','planting_hole_surviellance.total_holes','planting_hole_surviellance.counter_hole_standard',
                    'planting_hole_surviellance.is_dell', 'planting_hole_surviellance.created_at', 
                    'farmers.name as nama_petani', 'field_facilitators.name as nama_ff', 'planting_hole_surviellance.user_id')
                    ->leftjoin('lahans', 'lahans.lahan_no', '=', 'planting_hole_surviellance.lahan_no')
                    ->leftjoin('farmers', 'farmers.farmer_no', '=', 'lahans.farmer_no')
                    ->leftjoin('field_facilitators', 'field_facilitators.ff_no', '=', 'planting_hole_surviellance.user_id')
                    // ->where('planting_hole_surviellance.is_dell','=',0)
                    ->where('planting_hole_surviellance.user_id','=',$user_id)
                    ->first();

                if($GetPHDetail){ 
                    $GetPHDetailList = DB::table('planting_hole_details')
                        ->select('planting_hole_details.id',
                        'planting_hole_details.ph_form_no','planting_hole_details.tree_code','tree_locations.category as tree_category',
                        'planting_hole_details.amount', 'planting_hole_details.created_at',
                        'tree_locations.tree_name as tree_name')
                        ->leftjoin('tree_locations', 'tree_locations.tree_code', '=', 'planting_hole_details.tree_code')
                        ->leftjoin('planting_hole_surviellance', 'planting_hole_surviellance.ph_form_no', '=', 'planting_hole_details.ph_form_no')
                        // ->where('planting_hole_surviellance.is_dell','=',0)
                        ->where('planting_hole_surviellance.user_id','=',$user_id)
                        ->where('tree_locations.mu_no','=',$ff->mu_no)
                        ->get();
                    
                    $data = ['list_detail'=>$GetPHDetailList];
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

    public function CetakLabelLubangTanam(Request $request){
        $validator = Validator::make($request->all(), [
            'ph_form_no' => 'required'
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        }
        
        $labels = $this->generateSeedlingLabels($request->ph_form_no);
        return view('cetakLabelLubangTanam', $labels);
    }

    public function CetakLabelLubangTanamTemp(Request $request){
        $validator = Validator::make($request->all(), [
            'ph_form_no' => 'required'
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        }
        
        $labels = $this->generateSeedlingLabels($request->ph_form_no);
        return view('cetakLabelLubangTanam', $labels);
    }

    public function CetakBuktiPenyerahan(Request $request){
        $validator = Validator::make($request->all(), [
            'ph_form_no' => 'required'
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        }
        
        $labels = $this->generateSeedlingLabels($request->ph_form_no);


        $GetPHDetail = DB::table('planting_hole_surviellance')
                ->select('planting_hole_surviellance.id','planting_hole_surviellance.lahan_no',
                'planting_hole_surviellance.ph_form_no','planting_hole_surviellance.planting_year',
                'planting_hole_surviellance.latitude', 'planting_hole_surviellance.longitude',
                'planting_hole_surviellance.farmer_signature','planting_hole_surviellance.gambar1','planting_hole_surviellance.gambar2',
                'planting_hole_surviellance.is_validate','planting_hole_surviellance.validate_by','planting_hole_surviellance.total_holes',
                'planting_hole_surviellance.is_dell', 'planting_hole_surviellance.created_at', 
                'farmers.name as nama_petani', 'field_facilitators.name as nama_ff', 'planting_hole_surviellance.user_id',
                'field_facilitators.fc_no')
                ->leftjoin('lahans', 'lahans.lahan_no', '=', 'planting_hole_surviellance.lahan_no')
                ->leftjoin('farmers', 'farmers.farmer_no', '=', 'lahans.farmer_no')
                ->leftjoin('field_facilitators', 'field_facilitators.ff_no', '=', 'planting_hole_surviellance.user_id')
                ->where('planting_hole_surviellance.ph_form_no','=',$request->ph_form_no)
                ->first();

        // get FC data
        $fcData = Employee::where('nik', $GetPHDetail->fc_no)->first();
        
        return view('cetakBuktiPenyerahan', [
            'LubangTanamDetail' => $labels['lubangTanamDetail'],
            'listvalbag' =>  $labels['listLabel'],
            'fcData' => $fcData
        ]);
    }

    public function CetakExcelPlantingHoleAll(Request $request){
        try{
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

            $getmax_kayu = $request->max_kayu;
			if($getmax_kayu){$max_kayu=$getmax_kayu;}
            else{$max_kayu=10;}
			$getmax_mpts = $request->max_mpts;
			if($getmax_mpts){$max_mpts=$getmax_mpts;}
            else{$max_mpts=8;}
			$getmax_crops = $request->max_crops;
			if($getmax_crops){$max_crops=$getmax_crops;}
            else{$max_crops=5;}

			if($typegetdata == 'all'){
				$GetPHAll = DB::table('planting_hole_surviellance')
				->select('planting_hole_surviellance.id','planting_hole_surviellance.lahan_no',
				'planting_hole_surviellance.ph_form_no','planting_hole_surviellance.planting_year','planting_hole_surviellance.total_holes',
				'planting_hole_surviellance.latitude', 'planting_hole_surviellance.longitude',
				'planting_hole_surviellance.is_validate','planting_hole_surviellance.validate_by',
				'planting_hole_surviellance.is_dell', 'planting_hole_surviellance.created_at', 'planting_hole_surviellance.user_id',
				'farmers.name as nama_petani', 'field_facilitators.name as nama_ff')
				->leftjoin('lahans', 'lahans.lahan_no', '=', 'planting_hole_surviellance.lahan_no')
				->leftjoin('farmers', 'farmers.farmer_no', '=', 'lahans.farmer_no')
				->leftjoin('field_facilitators', 'field_facilitators.ff_no', '=', 'planting_hole_surviellance.user_id')
				->where('planting_hole_surviellance.is_dell','=',0)                                        
				->where('lahans.mu_no','like',$mu)
				->where('lahans.target_area','like',$ta)
				->where('lahans.village','like',$village)
				// ->where('planting_hole_surviellance.user_id','=',$ff_no)
				->get();

			}else{
				$ffdecode = (explode(",",$ff));

				$GetPHAll = DB::table('planting_hole_surviellance')
				->select('planting_hole_surviellance.id','planting_hole_surviellance.lahan_no',
				'planting_hole_surviellance.ph_form_no','planting_hole_surviellance.planting_year','planting_hole_surviellance.total_holes',
				'planting_hole_surviellance.latitude', 'planting_hole_surviellance.longitude',
				'planting_hole_surviellance.is_validate','planting_hole_surviellance.validate_by',
				'planting_hole_surviellance.is_dell', 'planting_hole_surviellance.created_at', 'planting_hole_surviellance.user_id',
				'farmers.name as nama_petani', 'field_facilitators.name as nama_ff')
				->leftjoin('lahans', 'lahans.lahan_no', '=', 'planting_hole_surviellance.lahan_no')
				->leftjoin('farmers', 'farmers.farmer_no', '=', 'lahans.farmer_no')
				->leftjoin('field_facilitators', 'field_facilitators.ff_no', '=', 'planting_hole_surviellance.user_id')
				->where('planting_hole_surviellance.is_dell','=',0)
				->wherein('planting_hole_surviellance.user_id',$ffdecode)
				// ->where('planting_hole_surviellance.user_id','=',$ff_no)
				->get();
			}
            
            // var_dump($max_kayu);

            if(count($GetPHAll)!=0){

                $get_amount_bag = 0;                                 
																
																
				$dataxx = [];
				$listxxx=array();

				foreach($GetPHAll as  $valphall){
						$GetPH = DB::table('planting_hole_details')
                        ->select('planting_hole_details.id','planting_hole_details.ph_form_no','planting_hole_details.tree_code',
                                'planting_hole_details.amount','trees.tree_name','trees.tree_category',
                                'farmers.name as nama_petani', 'field_facilitators.name as nama_ff', 'lahans.village',
                                'planting_hole_surviellance.id','planting_hole_surviellance.lahan_no',
                                'planting_hole_surviellance.ph_form_no','planting_hole_surviellance.planting_year',
                                'planting_hole_surviellance.total_holes', 'planting_hole_surviellance.latitude', 
                                'planting_hole_surviellance.longitude','planting_hole_surviellance.is_validate',
                                'planting_hole_surviellance.validate_by','planting_hole_surviellance.is_dell', 
                                'planting_hole_surviellance.created_at', 'planting_hole_surviellance.user_id'
                                )
                        ->leftjoin('planting_hole_surviellance', 'planting_hole_surviellance.ph_form_no', '=', 'planting_hole_details.ph_form_no')
                        ->join('trees', 'trees.tree_code', '=', 'planting_hole_details.tree_code')
                        ->leftjoin('lahans', 'lahans.lahan_no', '=', 'planting_hole_surviellance.lahan_no')
                        ->leftjoin('farmers', 'farmers.farmer_no', '=', 'lahans.farmer_no')
                        ->leftjoin('field_facilitators', 'field_facilitators.ff_no', '=', 'planting_hole_surviellance.user_id')
                        ->where('planting_hole_surviellance.is_dell','=',0)                                        
                        ->where('planting_hole_surviellance.ph_form_no','=',$valphall->ph_form_no)
						->orderBy('trees.tree_category', 'DESC')
                        // ->where('ph_form_no','=',$GetPH->ph_form_no)
                        ->get();

					// $dataxx = ['code' => $valphall->ph_form_no,'count' => count($GetPH)];
					// array_push($listxxx, $dataxx);

					$datavalpohon = [];
					$listvalpohon=array();
					$datavalbag = [];
					$listvalbag=array(); 

					$datavaltemp = [];
					$listvaltemp=array();
					$datavalbag = [];
					$listvalbag=array();
					$looping = false;
					$amount_loop = 0; 
					$mount_total_temp = 0;
					$sisa = 0;
					$previous_category = '-';
					$x = 0; 
					$previous_code_ph = '-';
					$max = 0;
					$datamaxbagph = [];
					$listmaxbagph=array();

					

					foreach($GetPH as  $valpohon){

						$GetSosialisasiDetail = DB::table('planting_socializations')
									->select('planting_socializations.id','planting_socializations.no_lahan',
									'planting_socializations.farmer_no','planting_socializations.form_no','planting_socializations.planting_year',
									'planting_socializations.no_document', 'planting_socializations.ff_no','planting_socializations.validation','planting_socializations.validate_by',
									'planting_socializations.is_dell', 'planting_socializations.created_at')
									->where('planting_socializations.no_lahan','=',$valpohon->lahan_no)
									->first();

						$Desas = DB::table('desas')->where('kode_desa','=',$valpohon->village)->first();
						$nama_desa = '-';
						if($Desas){
							$nama_desa = $Desas->name;
						}
						
						$planting_period = DB::table('planting_period')->where('form_no','=',$GetSosialisasiDetail->form_no)->first();
						$distribution_time = '-';
						$distribution_location = '-';
						if($planting_period){
							// $distribution_time = $planting_period->distribution_time;
							$date=date_create($planting_period->distribution_time);
							$distribution_time = date_format($date,"d-F-Y");
							$distribution_location = $planting_period->distribution_location;
						}

						$nama_ff = $valpohon->nama_ff;
						$nama_petani = $valpohon->nama_petani;
						$ph_form_no = $valpohon->ph_form_no;
						$lahan_no = $valpohon->lahan_no;
						$total_holes = $valpohon->total_holes;
						

						$new_name =$valpohon->tree_name;
						if (strripos($valpohon->tree_name, "Crops") !== false) {
							$new_name = substr($valpohon->tree_name,0,-8);
						}

						// $dataxx = ['code' => $valphall->ph_form_no,'count' => count($GetPH),'nama_desa' => $nama_desa,
						// 'distribution_location' => $distribution_location,'distribution_time' => $distribution_time,
						// 'valpohon' => $new_name,'amount' => $valpohon->amount];
						// array_push($listxxx, $dataxx);

						if($valpohon->tree_category =='Pohon_Kayu'){
								$batas = $max_kayu;
								$pohon_kategori = 'Pohon_Kayu';

								if($valpohon->amount > $batas){
									$looping = true;
									if ($sisa != 0 && $previous_category==$valpohon->tree_category){
										$valsisainput = $max_kayu-$sisa;
										$datavaltemp = ['pohon' => $new_name,'amount' => $valsisainput];
										array_push($listvaltemp, $datavaltemp);

										$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
										'pohon_kategori'=>$pohon_kategori,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
										'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
										array_push($listvalbag, $datavalbag);

										$amount_loop = ceil($valpohon->amount - $valsisainput/$max_kayu);
										$mount_total_temp = $valpohon->amount - $valsisainput;
									}else{
										if($sisa != 0){
											$prv_ctg = $this->convertcategorytrees($previous_category);
											$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
											'pohon_kategori'=>$prv_ctg,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
											'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
											array_push($listvalbag, $datavalbag);
										}                                
										$amount_loop = ceil($valpohon->amount/$max_kayu);
										$mount_total_temp = $valpohon->amount;
									}  
								}else{
										$looping = false;
										$datavaltemp = ['pohon' => $new_name,'amount' => $valpohon->amount];
										array_push($listvaltemp, $datavaltemp);

										$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
										'pohon_kategori'=>$pohon_kategori,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
										'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
										array_push($listvalbag, $datavalbag);

										$datavaltemp = [];
										$listvaltemp=array();
								}
																																
						}else if($valpohon->tree_category =='Pohon_Buah'){
							$pohon_kategori = 'Pohon_Buah (MPTS)';
							if($valpohon->amount > $max_mpts){
								$looping = true;
								if ($sisa != 0 && $previous_category==$valpohon->tree_category){
									$valsisainput = $max_mpts-$sisa;
									$datavaltemp = ['pohon' => $new_name,'amount' => $valsisainput];
									array_push($listvaltemp, $datavaltemp);

									$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
									'pohon_kategori'=>$pohon_kategori,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
									'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
									array_push($listvalbag, $datavalbag);

									$amount_loop = ceil($valpohon->amount - $valsisainput/$max_mpts);
									$mount_total_temp = $valpohon->amount - $valsisainput;
								}else{  
									if($sisa != 0){
										$prv_ctg = $this->convertcategorytrees($previous_category);
										$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
										'pohon_kategori'=>$prv_ctg,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
										'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
										array_push($listvalbag, $datavalbag);
									}                               
									$amount_loop = ceil($valpohon->amount/$max_mpts);
									$mount_total_temp = $valpohon->amount;
								} 
								// $amount_loop = ceil($valpohon->amount/6);
								// $mount_total_temp = $valpohon->amount;
							}else{
								$looping = false;
								$datavaltemp = ['pohon' => $new_name,'amount' => $valpohon->amount];
								array_push($listvaltemp, $datavaltemp);

								$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
								'pohon_kategori'=>$pohon_kategori,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
								'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
								array_push($listvalbag, $datavalbag);

								$datavaltemp = [];
								$listvaltemp=array();
							}
						}else{
							$pohon_kategori = 'Crops';
							if($valpohon->amount > $max_crops){
								$looping = true;
								if ($sisa != 0 && $previous_category==$valpohon->tree_category){
									$valsisainput = $max_crops-$sisa;
									$datavaltemp = ['pohon' => $new_name,'amount' => $valsisainput];
									array_push($listvaltemp, $datavaltemp);

									$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
									'pohon_kategori'=>$pohon_kategori,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
									'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
									array_push($listvalbag, $datavalbag);

									$amount_loop = ceil($valpohon->amount - $valsisainput/$max_crops);
									$mount_total_temp = $valpohon->amount - $valsisainput;
								}else{    
									if($sisa != 0){
										$prv_ctg = $this->convertcategorytrees($previous_category);
										$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
										'pohon_kategori'=>$prv_ctg,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
										'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
										array_push($listvalbag, $datavalbag);
									}                             
									$amount_loop = ceil($valpohon->amount/$max_crops);
									$mount_total_temp = $valpohon->amount;
								} 
								// $amount_loop = ceil($valpohon->amount/5);
								// $mount_total_temp = $valpohon->amount;
							}else{
								$looping = false;
								$datavaltemp = ['pohon' => $new_name,'amount' => $valpohon->amount];
								array_push($listvaltemp, $datavaltemp);

								$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
								'pohon_kategori'=>$pohon_kategori,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
								'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
								array_push($listvalbag, $datavalbag);

								$datavaltemp = [];
								$listvaltemp=array();
							}
						}
							
						if( $looping == true){
							$nn = 1;
							for ($x = 1; $x <= $amount_loop; $x++) {
								$datavaltemp = [];
								$listvaltemp=array();
								$sisa=0;

								if($nn>$amount_loop){
									break;
								}

								if($valpohon->tree_category =='Pohon_Kayu'){
									if($mount_total_temp > $max_kayu){
												
										$datavaltemp = ['pohon' => $new_name,'amount' => $max_kayu];
										array_push($listvaltemp, $datavaltemp);
										
										$mount_total_temp = $mount_total_temp - $max_kayu;

										
										$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
										'pohon_kategori'=>$pohon_kategori,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
										'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
										array_push($listvalbag, $datavalbag);
									}else{
										if($mount_total_temp == 0){
											break;
										}
										$datavaltemp = ['pohon' => $new_name,'amount' => $mount_total_temp];
										array_push($listvaltemp, $datavaltemp);

										if($mount_total_temp == $max_kayu){
											$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
											'pohon_kategori'=>$pohon_kategori,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
											'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
											array_push($listvalbag, $datavalbag);

											$datavaltemp = [];
											$listvaltemp=array();
											$sisa=0;
										}else{
											$sisa = $mount_total_temp;
											$previous_category=$valpohon->tree_category;
										}
									}

								}else if($valpohon->tree_category =='Pohon_Buah'){
									if($mount_total_temp > $max_mpts){
										$datavaltemp = ['pohon' => $new_name,'amount' => $max_mpts];
										array_push($listvaltemp, $datavaltemp);

										
										$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
										'pohon_kategori'=>$pohon_kategori,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
										'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
										array_push($listvalbag, $datavalbag);
										$mount_total_temp = $mount_total_temp - $max_mpts;
									}else{
										$datavaltemp = ['pohon' => $new_name,'amount' => $mount_total_temp];
										array_push($listvaltemp, $datavaltemp);

										if($mount_total_temp == $max_mpts){
											$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
											'pohon_kategori'=>$pohon_kategori,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
											'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
											array_push($listvalbag, $datavalbag);

											$datavaltemp = [];
											$listvaltemp=array();
											$sisa=0;
										}else{
											$sisa = $mount_total_temp;
											$previous_category=$valpohon->tree_category;
										}
									}

								}else{
									if($mount_total_temp > $max_crops){
										$datavaltemp = ['pohon' => $new_name,'amount' => $max_crops];
										array_push($listvaltemp, $datavaltemp);

										
										$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
										'pohon_kategori'=>$pohon_kategori,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
										'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
										array_push($listvalbag, $datavalbag);
										$mount_total_temp = $mount_total_temp - $max_crops;
									}else{
										$datavaltemp = ['pohon' => $new_name,'amount' => $mount_total_temp];
										array_push($listvaltemp, $datavaltemp);
										if($mount_total_temp == $max_crops){
											$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
											'pohon_kategori'=>$pohon_kategori,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
											'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
											array_push($listvalbag, $datavalbag);

											$datavaltemp = [];
											$listvaltemp=array();
											$sisa=0;
										}else{
											$sisa = $mount_total_temp;
											$previous_category=$valpohon->tree_category;
										}
										
									}

								}

								$nn = $nn + 1;
							}
						}
					}

					// $prv_ctg = $this->convertcategorytrees($previous_category);
					// $datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
					// 'pohon_kategori'=>$prv_ctg,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
					// 'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];;
					// array_push($listvalbag, $datavalbag);
				
					$datavalfix = [];
					$listvalfix=array();
					$ii = 001;
					$countlist = count($listvalbag);
					foreach($listvalbag as  $valbag){
								
						$dataxx = ['no_bag'=>$ii.'/'.$countlist, 'nama_ff'=>$valbag['nama_ff'],'nama_petani'=>$valbag['nama_petani'],
						'distribution_time'=>$valbag['distribution_time'],'pohon_kategori'=>$valbag['pohon_kategori'],'nama_desa'=>$valbag['nama_desa'],
						'distribution_location'=>$valbag['distribution_location'],'listvaltemp'=>$valbag['listvaltemp'],
						'lahan_no'=>$valbag['lahan_no'],'total_holes'=>$valbag['total_holes']];
						// $datavalbag = ['no_bag'=>$x.'/'.$get_amount_bag, 'listvaltemp'=>$listvaltemp, 'qrcodelahan'=>$qrcodelahan, 'n'=>$n];
						array_push($listxxx, $dataxx);

						$ii = $ii + 001;
					}
			
				}                   
                                    

				// var_dump($listxxx);

				$nama_title = 'Cetak Excel Data Lubang Tanam & Distribusi Bibit'; 
                $listvalbag = $listxxx;

                return view('cetakPlantingHoleAll', compact('nama_title','listvalbag'));
            }else{
                $rslt =  $this->ResultReturn(404, 'doesnt match data', 'data tidak ada');
                return response()->json($rslt, 404);
            }
            // var_dump(count($GetLahanNotComplete));
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }
    
	public function CetakExcelLoadingPlan(Request $request){
        try{
			$getmax_kayu = $request->max_kayu;
			if($getmax_kayu){$max_kayu=$getmax_kayu;}
            else{$max_kayu=10;}
			$getmax_mpts = $request->max_mpts;
			if($getmax_mpts){$max_mpts=$getmax_mpts;}
            else{$max_mpts=8;}
			$getmax_crops = $request->max_crops;
			if($getmax_crops){$max_crops=$getmax_crops;}
            else{$max_crops=5;}

            $typegetdatadownload = $request->typegetdatadownload;
			$detailexcel=[];
			if($typegetdatadownload == 'ff'){
				if($request->ff){
					$GetPHAll = DB::table('planting_hole_surviellance')
						->select('planting_hole_surviellance.id','planting_hole_surviellance.lahan_no',
						'planting_hole_surviellance.ph_form_no','planting_hole_surviellance.planting_year','planting_hole_surviellance.total_holes',
						'planting_hole_surviellance.latitude', 'planting_hole_surviellance.longitude',
						'planting_hole_surviellance.is_validate','planting_hole_surviellance.validate_by',
						'planting_hole_surviellance.is_dell', 'planting_hole_surviellance.created_at', 'planting_hole_surviellance.user_id',
						'farmers.name as nama_petani', 'field_facilitators.name as nama_ff')
						->leftjoin('lahans', 'lahans.lahan_no', '=', 'planting_hole_surviellance.lahan_no')
						->leftjoin('farmers', 'farmers.farmer_no', '=', 'lahans.farmer_no')
						->leftjoin('field_facilitators', 'field_facilitators.ff_no', '=', 'planting_hole_surviellance.user_id')
						->where('planting_hole_surviellance.is_dell','=',0)   
						->where('planting_hole_surviellance.user_id','=',$request->ff)
						// ->where('planting_hole_surviellance.user_id','=',$ff_no)
						->get();

						$GetDetail = DB::table('planting_socializations')
									->select('planting_socializations.id','planting_socializations.no_lahan','lahans.village',
									'planting_socializations.farmer_no','planting_socializations.form_no','planting_socializations.planting_year',
									'planting_socializations.no_document', 'planting_socializations.ff_no','planting_socializations.validation','planting_socializations.validate_by',
									'planting_socializations.is_dell', 'planting_socializations.created_at')
									->leftjoin('lahans', 'lahans.lahan_no', '=', 'planting_socializations.no_lahan')
									->where('planting_socializations.ff_no','=',$request->ff)
									->first();
						$planting_period_detail = DB::table('planting_period')->where('form_no','=',$GetDetail->form_no)->first();
						$distribution_time = '-';
						$distribution_location = '-';
						if($planting_period_detail){
							// $distribution_time = $planting_period->distribution_time;
							$date=date_create($planting_period_detail->distribution_time);
							$distribution_time = date_format($date,"d-F-Y");
							$distribution_location = $planting_period_detail->distribution_location;
						}
						$Desas = DB::table('desas')->where('kode_desa','=',$GetDetail->village)->first();
						$nama_desa = '-';
						if($Desas){
							$nama_desa = $Desas->name;
						}
						$GetFF= DB::table('field_facilitators')
									->select('field_facilitators.name')
									->where('field_facilitators.ff_no','=',$request->ff)
									->first();			
						$detailexcel = ['type' => 'loading_plan','nama_ff' => $GetFF->name,'distribution_time' => $distribution_time, 
						'distribution_location' => $distribution_location, 'nama_desa' => $nama_desa];
						
						$nama_title = 'Cetak Excel Loading Plan'; 
				}else{
					$rslt =  $this->ResultReturn(404, 'doesnt match data', 'data tidak ada');
                	return response()->json($rslt, 404);
				}
				

			}else{
				// $ffdecode = (explode(",",$ff));
				if($request->farmer_no){
					$GetPHAll = DB::table('planting_hole_surviellance')
						->select('planting_hole_surviellance.id','planting_hole_surviellance.lahan_no',
						'planting_hole_surviellance.ph_form_no','planting_hole_surviellance.planting_year','planting_hole_surviellance.total_holes',
						'planting_hole_surviellance.latitude', 'planting_hole_surviellance.longitude',
						'planting_hole_surviellance.is_validate','planting_hole_surviellance.validate_by',
						'planting_hole_surviellance.is_dell', 'planting_hole_surviellance.created_at', 'planting_hole_surviellance.user_id',
						'farmers.name as nama_petani', 'field_facilitators.name as nama_ff')
						->leftjoin('lahans', 'lahans.lahan_no', '=', 'planting_hole_surviellance.lahan_no')
						->leftjoin('farmers', 'farmers.farmer_no', '=', 'lahans.farmer_no')
						->leftjoin('field_facilitators', 'field_facilitators.ff_no', '=', 'planting_hole_surviellance.user_id')
						->where('planting_hole_surviellance.is_dell','=',0)   
						->where('lahans.farmer_no','=',$request->farmer_no)
						// ->where('planting_hole_surviellance.user_id','=',$ff_no)
						->get();

						$GetDetail = DB::table('planting_socializations')
									->select('planting_socializations.id','planting_socializations.no_lahan','lahans.village',
									'planting_socializations.farmer_no','planting_socializations.form_no','planting_socializations.planting_year',
									'planting_socializations.no_document', 'planting_socializations.ff_no','planting_socializations.validation','planting_socializations.validate_by',
									'planting_socializations.is_dell', 'planting_socializations.created_at')
									->leftjoin('lahans', 'lahans.lahan_no', '=', 'planting_socializations.no_lahan')
									->where('planting_socializations.farmer_no','=',$request->farmer_no)
									->first();
						$planting_period_detail = DB::table('planting_period')->where('form_no','=',$GetDetail->form_no)->first();
						$distribution_time = '-';
						$distribution_location = '-';
						if($planting_period_detail){
							// $distribution_time = $planting_period->distribution_time;
							$date=date_create($planting_period_detail->distribution_time);
							$distribution_time = date_format($date,"d-F-Y");
							$distribution_location = $planting_period_detail->distribution_location;
						}
						$Desas = DB::table('desas')->where('kode_desa','=',$GetDetail->village)->first();
						$nama_desa = '-';
						if($Desas){
							$nama_desa = $Desas->name;
						}
						$GetFarmer= DB::table('farmers')
									->select('farmers.name', 'farmers.user_id')
									->where('farmers.farmer_no','=',$request->farmer_no)
									->first();
						$GetFF= DB::table('field_facilitators')
									->select('field_facilitators.name')
									->where('field_facilitators.ff_no','=',$GetFarmer->user_id)
									->first();			
						$detailexcel = ['type' => 'farmer_report','nama_ff' => $GetFF->name,'nama_petani' => $GetFarmer->name,
						'distribution_time' => $distribution_time, 'distribution_location' => $distribution_location, 'nama_desa' => $nama_desa];
						
						$nama_title = 'Cetak Excel Farmer Receipt'; 
					}else{
					$rslt =  $this->ResultReturn(404, 'doesnt match data', 'data tidak ada');
                	return response()->json($rslt, 404);
				}
			}
            
            

            if(count($GetPHAll)!=0){

                $get_amount_bag = 0;                                 
																
																
				$dataxx = [];
				$listxxx=array();

				foreach($GetPHAll as  $valphall){
						$GetPH = DB::table('planting_hole_details')
                        ->select('planting_hole_details.id','planting_hole_details.ph_form_no','planting_hole_details.tree_code',
                                'planting_hole_details.amount','trees.tree_name','trees.tree_category',
                                'farmers.name as nama_petani', 'field_facilitators.name as nama_ff', 'lahans.village',
                                'planting_hole_surviellance.id','planting_hole_surviellance.lahan_no',
                                'planting_hole_surviellance.ph_form_no','planting_hole_surviellance.planting_year',
                                'planting_hole_surviellance.total_holes', 'planting_hole_surviellance.latitude', 
                                'planting_hole_surviellance.longitude','planting_hole_surviellance.is_validate',
                                'planting_hole_surviellance.validate_by','planting_hole_surviellance.is_dell', 
                                'planting_hole_surviellance.created_at', 'planting_hole_surviellance.user_id'
                                )
                        ->leftjoin('planting_hole_surviellance', 'planting_hole_surviellance.ph_form_no', '=', 'planting_hole_details.ph_form_no')
                        ->join('trees', 'trees.tree_code', '=', 'planting_hole_details.tree_code')
                        ->leftjoin('lahans', 'lahans.lahan_no', '=', 'planting_hole_surviellance.lahan_no')
                        ->leftjoin('farmers', 'farmers.farmer_no', '=', 'lahans.farmer_no')
                        ->leftjoin('field_facilitators', 'field_facilitators.ff_no', '=', 'planting_hole_surviellance.user_id')
                        ->where('planting_hole_surviellance.is_dell','=',0)                                        
                        ->where('planting_hole_surviellance.ph_form_no','=',$valphall->ph_form_no)
						->orderBy('trees.tree_category', 'DESC')
                        // ->where('ph_form_no','=',$GetPH->ph_form_no)
                        ->get();

					// $dataxx = ['code' => $valphall->ph_form_no,'count' => count($GetPH)];
					// array_push($listxxx, $dataxx);

					$datavalpohon = [];
					$listvalpohon=array();
					$datavalbag = [];
					$listvalbag=array(); 

					$datavaltemp = [];
					$listvaltemp=array();
					$datavalbag = [];
					$listvalbag=array();
					$looping = false;
					$amount_loop = 0; 
					$mount_total_temp = 0;
					$sisa = 0;
					$previous_category = '-';
					$x = 0; 
					$previous_code_ph = '-';
					$max = 0;
					$datamaxbagph = [];
					$listmaxbagph=array();

					foreach($GetPH as  $valpohon){

						$GetSosialisasiDetail = DB::table('planting_socializations')
									->select('planting_socializations.id','planting_socializations.no_lahan',
									'planting_socializations.farmer_no','planting_socializations.form_no','planting_socializations.planting_year',
									'planting_socializations.no_document', 'planting_socializations.ff_no','planting_socializations.validation','planting_socializations.validate_by',
									'planting_socializations.is_dell', 'planting_socializations.created_at')
									->where('planting_socializations.no_lahan','=',$valpohon->lahan_no)
									->first();

						$Desas = DB::table('desas')->where('kode_desa','=',$valpohon->village)->first();
						$nama_desa = '-';
						if($Desas){
							$nama_desa = $Desas->name;
						}
						
						$planting_period = DB::table('planting_period')->where('form_no','=',$GetSosialisasiDetail->form_no)->first();
						$distribution_time = '-';
						$distribution_location = '-';
						if($planting_period){
							// $distribution_time = $planting_period->distribution_time;
							$date=date_create($planting_period->distribution_time);
							$distribution_time = date_format($date,"d-F-Y");
							$distribution_location = $planting_period->distribution_location;
						}

						$nama_ff = $valpohon->nama_ff;
						$nama_petani = $valpohon->nama_petani;
						$ph_form_no = $valpohon->ph_form_no;
						$lahan_no = $valpohon->lahan_no;
						$total_holes = $valpohon->total_holes;
						

						$new_name =$valpohon->tree_name;
						if (strripos($valpohon->tree_name, "Crops") !== false) {
							$new_name = substr($valpohon->tree_name,0,-8);
						}

						// $dataxx = ['code' => $valphall->ph_form_no,'count' => count($GetPH),'nama_desa' => $nama_desa,
						// 'distribution_location' => $distribution_location,'distribution_time' => $distribution_time,
						// 'valpohon' => $new_name,'amount' => $valpohon->amount];
						// array_push($listxxx, $dataxx);

						if($valpohon->tree_category =='Pohon_Kayu'){
								$batas = $max_kayu;
								$pohon_kategori = 'Pohon_Kayu';

								if($valpohon->amount > $batas){
									$looping = true;
									if ($sisa != 0 && $previous_category==$valpohon->tree_category){
										$valsisainput = $max_kayu-$sisa;
										$datavaltemp = ['pohon' => $new_name,'amount' => $valsisainput];
										array_push($listvaltemp, $datavaltemp);

										$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
										'pohon_kategori'=>$pohon_kategori,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
										'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
										array_push($listvalbag, $datavalbag);

										$amount_loop = ceil($valpohon->amount - $valsisainput/$max_kayu);
										$mount_total_temp = $valpohon->amount - $valsisainput;
									}else{
										if($sisa != 0){
											$prv_ctg = $this->convertcategorytrees($previous_category);
											$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
											'pohon_kategori'=>$prv_ctg,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
											'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
											array_push($listvalbag, $datavalbag);
										}                                
										$amount_loop = ceil($valpohon->amount/$max_kayu);
										$mount_total_temp = $valpohon->amount;
									}  
								}else{
										$looping = false;
										$datavaltemp = ['pohon' => $new_name,'amount' => $valpohon->amount];
										array_push($listvaltemp, $datavaltemp);

										$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
										'pohon_kategori'=>$pohon_kategori,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
										'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
										array_push($listvalbag, $datavalbag);

										$datavaltemp = [];
										$listvaltemp=array();
								}
																																
						}else if($valpohon->tree_category =='Pohon_Buah'){
							$pohon_kategori = 'Pohon_Buah (MPTS)';
							if($valpohon->amount > $max_mpts){
								$looping = true;
								if ($sisa != 0 && $previous_category==$valpohon->tree_category){
									$valsisainput = $max_mpts-$sisa;
									$datavaltemp = ['pohon' => $new_name,'amount' => $valsisainput];
									array_push($listvaltemp, $datavaltemp);

									$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
									'pohon_kategori'=>$pohon_kategori,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
									'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
									array_push($listvalbag, $datavalbag);

									$amount_loop = ceil($valpohon->amount - $valsisainput/$max_mpts);
									$mount_total_temp = $valpohon->amount - $valsisainput;
								}else{  
									if($sisa != 0){
										$prv_ctg = $this->convertcategorytrees($previous_category);
										$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
										'pohon_kategori'=>$prv_ctg,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
										'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
										array_push($listvalbag, $datavalbag);
									}                               
									$amount_loop = ceil($valpohon->amount/$max_mpts);
									$mount_total_temp = $valpohon->amount;
								} 
								// $amount_loop = ceil($valpohon->amount/6);
								// $mount_total_temp = $valpohon->amount;
							}else{
								$looping = false;
								$datavaltemp = ['pohon' => $new_name,'amount' => $valpohon->amount];
								array_push($listvaltemp, $datavaltemp);

								$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
								'pohon_kategori'=>$pohon_kategori,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
								'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
								array_push($listvalbag, $datavalbag);

								$datavaltemp = [];
								$listvaltemp=array();
							}
						}else{
							$pohon_kategori = 'Crops';
							if($valpohon->amount > $max_crops){
								$looping = true;
								if ($sisa != 0 && $previous_category==$valpohon->tree_category){
									$valsisainput = $max_crops-$sisa;
									$datavaltemp = ['pohon' => $new_name,'amount' => $valsisainput];
									array_push($listvaltemp, $datavaltemp);

									$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
									'pohon_kategori'=>$pohon_kategori,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
									'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
									array_push($listvalbag, $datavalbag);

									$amount_loop = ceil($valpohon->amount - $valsisainput/$max_crops);
									$mount_total_temp = $valpohon->amount - $valsisainput;
								}else{    
									if($sisa != 0){
										$prv_ctg = $this->convertcategorytrees($previous_category);
										$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
										'pohon_kategori'=>$prv_ctg,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
										'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
										array_push($listvalbag, $datavalbag);
									}                             
									$amount_loop = ceil($valpohon->amount/$max_crops);
									$mount_total_temp = $valpohon->amount;
								} 
								// $amount_loop = ceil($valpohon->amount/5);
								// $mount_total_temp = $valpohon->amount;
							}else{
								$looping = false;
								$datavaltemp = ['pohon' => $new_name,'amount' => $valpohon->amount];
								array_push($listvaltemp, $datavaltemp);

								$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
								'pohon_kategori'=>$pohon_kategori,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
								'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
								array_push($listvalbag, $datavalbag);

								$datavaltemp = [];
								$listvaltemp=array();
							}
						}
							
						if( $looping == true){
							$nn = 1;
							for ($x = 1; $x <= $amount_loop; $x++) {
								$datavaltemp = [];
								$listvaltemp=array();
								$sisa=0;

								if($nn>$amount_loop){
									break;
								}

								if($valpohon->tree_category =='Pohon_Kayu'){
									if($mount_total_temp > $max_kayu){
												
										$datavaltemp = ['pohon' => $new_name,'amount' => $max_kayu];
										array_push($listvaltemp, $datavaltemp);
										
										$mount_total_temp = $mount_total_temp - $max_kayu;

										
										$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
										'pohon_kategori'=>$pohon_kategori,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
										'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
										array_push($listvalbag, $datavalbag);
									}else{
										if($mount_total_temp == 0){
											break;
										}
										$datavaltemp = ['pohon' => $new_name,'amount' => $mount_total_temp];
										array_push($listvaltemp, $datavaltemp);

										if($mount_total_temp == $max_kayu){
											$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
											'pohon_kategori'=>$pohon_kategori,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
											'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
											array_push($listvalbag, $datavalbag);

											$datavaltemp = [];
											$listvaltemp=array();
											$sisa=0;
										}else{
											$sisa = $mount_total_temp;
											$previous_category=$valpohon->tree_category;
										}
									}

								}else if($valpohon->tree_category =='Pohon_Buah'){
									if($mount_total_temp > $max_mpts){
										$datavaltemp = ['pohon' => $new_name,'amount' => $max_mpts];
										array_push($listvaltemp, $datavaltemp);

										
										$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
										'pohon_kategori'=>$pohon_kategori,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
										'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
										array_push($listvalbag, $datavalbag);
										$mount_total_temp = $mount_total_temp - $max_mpts;
									}else{
										$datavaltemp = ['pohon' => $new_name,'amount' => $mount_total_temp];
										array_push($listvaltemp, $datavaltemp);

										if($mount_total_temp == $max_mpts){
											$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
											'pohon_kategori'=>$pohon_kategori,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
											'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
											array_push($listvalbag, $datavalbag);

											$datavaltemp = [];
											$listvaltemp=array();
											$sisa=0;
										}else{
											$sisa = $mount_total_temp;
											$previous_category=$valpohon->tree_category;
										}
									}

								}else{
									if($mount_total_temp > $max_crops){
										$datavaltemp = ['pohon' => $new_name,'amount' => $max_crops];
										array_push($listvaltemp, $datavaltemp);

										
										$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
										'pohon_kategori'=>$pohon_kategori,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
										'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
										array_push($listvalbag, $datavalbag);
										$mount_total_temp = $mount_total_temp - $max_crops;
									}else{
										$datavaltemp = ['pohon' => $new_name,'amount' => $mount_total_temp];
										array_push($listvaltemp, $datavaltemp);
										if($mount_total_temp == $max_crops){
											$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
											'pohon_kategori'=>$pohon_kategori,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
											'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
											array_push($listvalbag, $datavalbag);

											$datavaltemp = [];
											$listvaltemp=array();
											$sisa=0;
										}else{
											$sisa = $mount_total_temp;
											$previous_category=$valpohon->tree_category;
										}
										
									}

								}

								$nn = $nn + 1;
							}
						}
					}

					// $prv_ctg = $this->convertcategorytrees($previous_category);
					// $datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
					// 'pohon_kategori'=>$prv_ctg,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
					// 'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];;
					// array_push($listvalbag, $datavalbag);
				
					$datavalfix = [];
					$listvalfix=array();
					$ii = 1;
					$countlist = count($listvalbag);
					foreach($listvalbag as  $valbag){
								
						$dataxx = ['no_bag'=>$ii.'/'.$countlist, 'nama_ff'=>$valbag['nama_ff'],'nama_petani'=>$valbag['nama_petani'],
						'distribution_time'=>$valbag['distribution_time'],'pohon_kategori'=>$valbag['pohon_kategori'],'nama_desa'=>$valbag['nama_desa'],
						'distribution_location'=>$valbag['distribution_location'],'listvaltemp'=>$valbag['listvaltemp'],
						'lahan_no'=>$valbag['lahan_no'],'total_holes'=>$valbag['total_holes']];
						// $datavalbag = ['no_bag'=>$x.'/'.$get_amount_bag, 'listvaltemp'=>$listvaltemp, 'qrcodelahan'=>$qrcodelahan, 'n'=>$n];
						array_push($listxxx, $dataxx);

						$ii = $ii + 1;
					}
			
				}                   
                                    

				// var_dump($listxxx);

                $listvalbag = $listxxx;

				// var_dump($listvalbag);

                return view('cetakPlantingHoleLoadingPlan', compact('nama_title','listvalbag','detailexcel'));
            }else{
                $rslt =  $this->ResultReturn(404, 'doesnt match data', 'data tidak ada');
                return response()->json($rslt, 404);
            }
            // var_dump(count($GetLahanNotComplete));
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }
    
	public function CetakExcelPackingPlan(Request $request){
        try{
			$getmax_kayu = $request->max_kayu;
			if($getmax_kayu){$max_kayu=$getmax_kayu;}
            else{$max_kayu=10;}
			$getmax_mpts = $request->max_mpts;
			if($getmax_mpts){$max_mpts=$getmax_mpts;}
            else{$max_mpts=8;}
			$getmax_crops = $request->max_crops;
			if($getmax_crops){$max_crops=$getmax_crops;}
            else{$max_crops=5;}

            // $typegetdata = $request->typegetdata;
			// $planting_period = DB::table('planting_period')->where('form_no','=',$GetSosialisasiDetail->form_no)->first();
            $GetLahanNoSosialisasi = DB::table('planting_socializations')
									->join('planting_period', 'planting_period.form_no', '=', 'planting_socializations.form_no')
									->where('planting_period.distribution_time','=',$request->date)
									->pluck('planting_socializations.no_lahan');

			$distribution_time = '-';
			if(count($GetLahanNoSosialisasi)!=0){
				
				$date=date_create($request->date);
				$distribution_time = date_format($date,"d-F-Y");

				$GetPHAll = DB::table('planting_hole_surviellance')
				->select('planting_hole_surviellance.id','planting_hole_surviellance.lahan_no',
				'planting_hole_surviellance.ph_form_no','planting_hole_surviellance.planting_year','planting_hole_surviellance.total_holes',
				'planting_hole_surviellance.latitude', 'planting_hole_surviellance.longitude',
				'planting_hole_surviellance.is_validate','planting_hole_surviellance.validate_by',
				'planting_hole_surviellance.is_dell', 'planting_hole_surviellance.created_at', 'planting_hole_surviellance.user_id',
				'farmers.name as nama_petani', 'field_facilitators.name as nama_ff')
				->leftjoin('lahans', 'lahans.lahan_no', '=', 'planting_hole_surviellance.lahan_no')
				->leftjoin('farmers', 'farmers.farmer_no', '=', 'lahans.farmer_no')
				->leftjoin('field_facilitators', 'field_facilitators.ff_no', '=', 'planting_hole_surviellance.user_id')
				->where('planting_hole_surviellance.is_dell','=',0)
				->wherein('planting_hole_surviellance.lahan_no',$GetLahanNoSosialisasi)
				// ->where('planting_hole_surviellance.user_id','=',$ff_no)
				->get();

			}else{
				$rslt =  $this->ResultReturn(404, 'doesnt match data', 'data tidak ada');
                return response()->json($rslt, 404);
			}
            
            

            if(count($GetPHAll)!=0){

                $get_amount_bag = 0;                                 
																
																
				$dataxx = [];
				$listxxx=array();

				foreach($GetPHAll as  $valphall){
						$GetPH = DB::table('planting_hole_details')
                        ->select('planting_hole_details.id','planting_hole_details.ph_form_no','planting_hole_details.tree_code',
                                'planting_hole_details.amount','trees.tree_name','trees.tree_category',
                                'farmers.name as nama_petani', 'field_facilitators.name as nama_ff', 'lahans.village',
                                'planting_hole_surviellance.id','planting_hole_surviellance.lahan_no',
                                'planting_hole_surviellance.ph_form_no','planting_hole_surviellance.planting_year',
                                'planting_hole_surviellance.total_holes', 'planting_hole_surviellance.latitude', 
                                'planting_hole_surviellance.longitude','planting_hole_surviellance.is_validate',
                                'planting_hole_surviellance.validate_by','planting_hole_surviellance.is_dell', 
                                'planting_hole_surviellance.created_at', 'planting_hole_surviellance.user_id'
                                )
                        ->leftjoin('planting_hole_surviellance', 'planting_hole_surviellance.ph_form_no', '=', 'planting_hole_details.ph_form_no')
                        ->join('trees', 'trees.tree_code', '=', 'planting_hole_details.tree_code')
                        ->leftjoin('lahans', 'lahans.lahan_no', '=', 'planting_hole_surviellance.lahan_no')
                        ->leftjoin('farmers', 'farmers.farmer_no', '=', 'lahans.farmer_no')
                        ->leftjoin('field_facilitators', 'field_facilitators.ff_no', '=', 'planting_hole_surviellance.user_id')
                        ->where('planting_hole_surviellance.is_dell','=',0)                                        
                        ->where('planting_hole_surviellance.ph_form_no','=',$valphall->ph_form_no)
						->orderBy('trees.tree_category', 'DESC')
                        // ->where('ph_form_no','=',$GetPH->ph_form_no)
                        ->get();

					// $dataxx = ['code' => $valphall->ph_form_no,'count' => count($GetPH)];
					// array_push($listxxx, $dataxx);

					$datavalpohon = [];
					$listvalpohon=array();
					$datavalbag = [];
					$listvalbag=array(); 

					$datavaltemp = [];
					$listvaltemp=array();
					$datavalbag = [];
					$listvalbag=array();
					$looping = false;
					$amount_loop = 0; 
					$mount_total_temp = 0;
					$sisa = 0;
					$previous_category = '-';
					$x = 0; 
					$previous_code_ph = '-';
					$max = 0;
					$datamaxbagph = [];
					$listmaxbagph=array();

					foreach($GetPH as  $valpohon){

						$GetSosialisasiDetail = DB::table('planting_socializations')
									->select('planting_socializations.id','planting_socializations.no_lahan',
									'planting_socializations.farmer_no','planting_socializations.form_no','planting_socializations.planting_year',
									'planting_socializations.no_document', 'planting_socializations.ff_no','planting_socializations.validation','planting_socializations.validate_by',
									'planting_socializations.is_dell', 'planting_socializations.created_at')
									->where('planting_socializations.no_lahan','=',$valpohon->lahan_no)
									->first();

						$Desas = DB::table('desas')->where('kode_desa','=',$valpohon->village)->first();
						$nama_desa = '-';
						if($Desas){
							$nama_desa = $Desas->name;
						}
						
						$planting_period = DB::table('planting_period')->where('form_no','=',$GetSosialisasiDetail->form_no)->first();
						$distribution_time = '-';
						$distribution_location = '-';
						if($planting_period){
							// $distribution_time = $planting_period->distribution_time;
							$date=date_create($planting_period->distribution_time);
							$distribution_time = date_format($date,"d-F-Y");
							$distribution_location = $planting_period->distribution_location;
						}

						$nama_ff = $valpohon->nama_ff;
						$nama_petani = $valpohon->nama_petani;
						$ph_form_no = $valpohon->ph_form_no;
						$lahan_no = $valpohon->lahan_no;
						$total_holes = $valpohon->total_holes;
						

						$new_name =$valpohon->tree_name;
						if (strripos($valpohon->tree_name, "Crops") !== false) {
							$new_name = substr($valpohon->tree_name,0,-8);
						}

						// $dataxx = ['code' => $valphall->ph_form_no,'count' => count($GetPH),'nama_desa' => $nama_desa,
						// 'distribution_location' => $distribution_location,'distribution_time' => $distribution_time,
						// 'valpohon' => $new_name,'amount' => $valpohon->amount];
						// array_push($listxxx, $dataxx);

						if($valpohon->tree_category =='Pohon_Kayu'){
								$batas = $max_kayu;
								$pohon_kategori = 'Pohon_Kayu';

								if($valpohon->amount > $batas){
									$looping = true;
									if ($sisa != 0 && $previous_category==$valpohon->tree_category){
										$valsisainput = $max_kayu-$sisa;
										$datavaltemp = ['pohon' => $new_name,'amount' => $valsisainput];
										array_push($listvaltemp, $datavaltemp);

										$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
										'pohon_kategori'=>$pohon_kategori,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
										'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
										array_push($listvalbag, $datavalbag);

										$amount_loop = ceil($valpohon->amount - $valsisainput/$max_kayu);
										$mount_total_temp = $valpohon->amount - $valsisainput;
									}else{
										if($sisa != 0){
											$prv_ctg = $this->convertcategorytrees($previous_category);
											$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
											'pohon_kategori'=>$prv_ctg,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
											'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
											array_push($listvalbag, $datavalbag);
										}                                
										$amount_loop = ceil($valpohon->amount/$max_kayu);
										$mount_total_temp = $valpohon->amount;
									}  
								}else{
										$looping = false;
										$datavaltemp = ['pohon' => $new_name,'amount' => $valpohon->amount];
										array_push($listvaltemp, $datavaltemp);

										$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
										'pohon_kategori'=>$pohon_kategori,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
										'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
										array_push($listvalbag, $datavalbag);

										$datavaltemp = [];
										$listvaltemp=array();
								}
																																
						}else if($valpohon->tree_category =='Pohon_Buah'){
							$pohon_kategori = 'Pohon_Buah (MPTS)';
							if($valpohon->amount > $max_mpts){
								$looping = true;
								if ($sisa != 0 && $previous_category==$valpohon->tree_category){
									$valsisainput = $max_mpts-$sisa;
									$datavaltemp = ['pohon' => $new_name,'amount' => $valsisainput];
									array_push($listvaltemp, $datavaltemp);

									$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
									'pohon_kategori'=>$pohon_kategori,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
									'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
									array_push($listvalbag, $datavalbag);

									$amount_loop = ceil($valpohon->amount - $valsisainput/$max_mpts);
									$mount_total_temp = $valpohon->amount - $valsisainput;
								}else{  
									if($sisa != 0){
										$prv_ctg = $this->convertcategorytrees($previous_category);
										$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
										'pohon_kategori'=>$prv_ctg,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
										'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
										array_push($listvalbag, $datavalbag);
									}                               
									$amount_loop = ceil($valpohon->amount/$max_mpts);
									$mount_total_temp = $valpohon->amount;
								} 
								// $amount_loop = ceil($valpohon->amount/6);
								// $mount_total_temp = $valpohon->amount;
							}else{
								$looping = false;
								$datavaltemp = ['pohon' => $new_name,'amount' => $valpohon->amount];
								array_push($listvaltemp, $datavaltemp);

								$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
								'pohon_kategori'=>$pohon_kategori,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
								'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
								array_push($listvalbag, $datavalbag);

								$datavaltemp = [];
								$listvaltemp=array();
							}
						}else{
							$pohon_kategori = 'Crops';
							if($valpohon->amount > $max_crops){
								$looping = true;
								if ($sisa != 0 && $previous_category==$valpohon->tree_category){
									$valsisainput = $max_crops-$sisa;
									$datavaltemp = ['pohon' => $new_name,'amount' => $valsisainput];
									array_push($listvaltemp, $datavaltemp);

									$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
									'pohon_kategori'=>$pohon_kategori,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
									'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
									array_push($listvalbag, $datavalbag);

									$amount_loop = ceil($valpohon->amount - $valsisainput/$max_crops);
									$mount_total_temp = $valpohon->amount - $valsisainput;
								}else{    
									if($sisa != 0){
										$prv_ctg = $this->convertcategorytrees($previous_category);
										$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
										'pohon_kategori'=>$prv_ctg,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
										'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
										array_push($listvalbag, $datavalbag);
									}                             
									$amount_loop = ceil($valpohon->amount/$max_crops);
									$mount_total_temp = $valpohon->amount;
								} 
								// $amount_loop = ceil($valpohon->amount/5);
								// $mount_total_temp = $valpohon->amount;
							}else{
								$looping = false;
								$datavaltemp = ['pohon' => $new_name,'amount' => $valpohon->amount];
								array_push($listvaltemp, $datavaltemp);

								$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
								'pohon_kategori'=>$pohon_kategori,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
								'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
								array_push($listvalbag, $datavalbag);

								$datavaltemp = [];
								$listvaltemp=array();
							}
						}
							
						if( $looping == true){
							$nn = 1;
							for ($x = 1; $x <= $amount_loop; $x++) {
								$datavaltemp = [];
								$listvaltemp=array();
								$sisa=0;

								if($nn>$amount_loop){
									break;
								}

								if($valpohon->tree_category =='Pohon_Kayu'){
									if($mount_total_temp > $max_kayu){
												
										$datavaltemp = ['pohon' => $new_name,'amount' => $max_kayu];
										array_push($listvaltemp, $datavaltemp);
										
										$mount_total_temp = $mount_total_temp - $max_kayu;

										
										$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
										'pohon_kategori'=>$pohon_kategori,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
										'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
										array_push($listvalbag, $datavalbag);
									}else{
										if($mount_total_temp == 0){
											break;
										}
										$datavaltemp = ['pohon' => $new_name,'amount' => $mount_total_temp];
										array_push($listvaltemp, $datavaltemp);

										if($mount_total_temp == $max_kayu){
											$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
											'pohon_kategori'=>$pohon_kategori,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
											'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
											array_push($listvalbag, $datavalbag);

											$datavaltemp = [];
											$listvaltemp=array();
											$sisa=0;
										}else{
											$sisa = $mount_total_temp;
											$previous_category=$valpohon->tree_category;
										}
									}

								}else if($valpohon->tree_category =='Pohon_Buah'){
									if($mount_total_temp > $max_mpts){
										$datavaltemp = ['pohon' => $new_name,'amount' => $max_mpts];
										array_push($listvaltemp, $datavaltemp);

										
										$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
										'pohon_kategori'=>$pohon_kategori,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
										'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
										array_push($listvalbag, $datavalbag);
										$mount_total_temp = $mount_total_temp - $max_mpts;
									}else{
										$datavaltemp = ['pohon' => $new_name,'amount' => $mount_total_temp];
										array_push($listvaltemp, $datavaltemp);

										if($mount_total_temp == $max_mpts){
											$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
											'pohon_kategori'=>$pohon_kategori,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
											'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
											array_push($listvalbag, $datavalbag);

											$datavaltemp = [];
											$listvaltemp=array();
											$sisa=0;
										}else{
											$sisa = $mount_total_temp;
											$previous_category=$valpohon->tree_category;
										}
									}

								}else{
									if($mount_total_temp > $max_crops){
										$datavaltemp = ['pohon' => $new_name,'amount' => $max_crops];
										array_push($listvaltemp, $datavaltemp);

										
										$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
										'pohon_kategori'=>$pohon_kategori,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
										'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
										array_push($listvalbag, $datavalbag);
										$mount_total_temp = $mount_total_temp - $max_crops;
									}else{
										$datavaltemp = ['pohon' => $new_name,'amount' => $mount_total_temp];
										array_push($listvaltemp, $datavaltemp);
										if($mount_total_temp == $max_crops){
											$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
											'pohon_kategori'=>$pohon_kategori,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
											'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
											array_push($listvalbag, $datavalbag);

											$datavaltemp = [];
											$listvaltemp=array();
											$sisa=0;
										}else{
											$sisa = $mount_total_temp;
											$previous_category=$valpohon->tree_category;
										}
										
									}

								}

								$nn = $nn + 1;
							}
						}
					}

					// $prv_ctg = $this->convertcategorytrees($previous_category);
					// $datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
					// 'pohon_kategori'=>$prv_ctg,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
					// 'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];;
					// array_push($listvalbag, $datavalbag);
				
					$datavalfix = [];
					$listvalfix=array();
					$ii = 1;
					$countlist = count($listvalbag);
					foreach($listvalbag as  $valbag){
								
						$dataxx = ['no_bag'=>$ii.'/'.$countlist, 'nama_ff'=>$valbag['nama_ff'],'nama_petani'=>$valbag['nama_petani'],
						'distribution_time'=>$valbag['distribution_time'],'pohon_kategori'=>$valbag['pohon_kategori'],'nama_desa'=>$valbag['nama_desa'],
						'distribution_location'=>$valbag['distribution_location'],'listvaltemp'=>$valbag['listvaltemp'],
						'lahan_no'=>$valbag['lahan_no'],'total_holes'=>$valbag['total_holes']];
						// $datavalbag = ['no_bag'=>$x.'/'.$get_amount_bag, 'listvaltemp'=>$listvaltemp, 'qrcodelahan'=>$qrcodelahan, 'n'=>$n];
						array_push($listxxx, $dataxx);

						$ii = $ii + 1;
					}
			
				}                   
                                    

				// var_dump($listxxx);

				$nama_title = 'Cetak Excel Packing Plan Report'; 
                $listvalbag = $listxxx;

                return view('cetakPackingPlan', compact('nama_title','listvalbag', 'distribution_time'));
            }else{
                $rslt =  $this->ResultReturn(404, 'doesnt match data', 'data tidak ada');
                return response()->json($rslt, 404);
            }
            // var_dump(count($GetLahanNotComplete));
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }
    
	public function CetakExcelShippingPlan(Request $request){
        try{
			$getmax_kayu = $request->max_kayu;
			if($getmax_kayu){$max_kayu=$getmax_kayu;}
            else{$max_kayu=10;}
			$getmax_mpts = $request->max_mpts;
			if($getmax_mpts){$max_mpts=$getmax_mpts;}
            else{$max_mpts=8;}
			$getmax_crops = $request->max_crops;
			if($getmax_crops){$max_crops=$getmax_crops;}
            else{$max_crops=5;}
            // $typegetdata = $request->typegetdata;
			// $planting_period = DB::table('planting_period')->where('form_no','=',$GetSosialisasiDetail->form_no)->first();
            $GetLahanNoSosialisasi = DB::table('planting_socializations')
									->join('planting_period', 'planting_period.form_no', '=', 'planting_socializations.form_no')
									->where('planting_period.distribution_time','=',$request->date)
									->pluck('planting_socializations.no_lahan');

			$distribution_time = '-';
			if(count($GetLahanNoSosialisasi)!=0){
				
				$date=date_create($request->date);
				$distribution_time = date_format($date,"d-F-Y");

				$GetPHAll = DB::table('planting_hole_surviellance')
				->select('planting_hole_surviellance.id','planting_hole_surviellance.lahan_no',
				'planting_hole_surviellance.ph_form_no','planting_hole_surviellance.planting_year','planting_hole_surviellance.total_holes',
				'planting_hole_surviellance.latitude', 'planting_hole_surviellance.longitude',
				'planting_hole_surviellance.is_validate','planting_hole_surviellance.validate_by',
				'planting_hole_surviellance.is_dell', 'planting_hole_surviellance.created_at', 'planting_hole_surviellance.user_id',
				'farmers.name as nama_petani', 'field_facilitators.name as nama_ff')
				->leftjoin('lahans', 'lahans.lahan_no', '=', 'planting_hole_surviellance.lahan_no')
				->leftjoin('farmers', 'farmers.farmer_no', '=', 'lahans.farmer_no')
				->leftjoin('field_facilitators', 'field_facilitators.ff_no', '=', 'planting_hole_surviellance.user_id')
				->where('planting_hole_surviellance.is_dell','=',0)
				->wherein('planting_hole_surviellance.lahan_no',$GetLahanNoSosialisasi)
				->orderBy('planting_hole_surviellance.user_id', 'asc')
				// ->where('planting_hole_surviellance.user_id','=',$ff_no)
				->get();

			}else{
				$rslt =  $this->ResultReturn(404, 'doesnt match data', 'data tidak ada');
                return response()->json($rslt, 404);
			}
            
            

            if(count($GetPHAll)!=0){

                $get_amount_bag = 0;                                 
																
																
				$dataxx = [];
				$listxxx=array();

				$qty_total = 0; 
				$ff_code_previous = '-';
				 
				$countmax = count($GetPHAll);
				$xxx = 0 ;
				$datafftemp = [];
				$listfftemp=array();
				foreach($GetPHAll as  $valphall){
						$GetPH = DB::table('planting_hole_details')
                        ->select('planting_hole_details.id','planting_hole_details.ph_form_no','planting_hole_details.tree_code',
                                'planting_hole_details.amount','trees.tree_name','trees.tree_category',
                                'farmers.name as nama_petani', 'field_facilitators.name as nama_ff', 'lahans.village',
                                'planting_hole_surviellance.id','planting_hole_surviellance.lahan_no',
                                'planting_hole_surviellance.ph_form_no','planting_hole_surviellance.planting_year',
                                'planting_hole_surviellance.total_holes', 'planting_hole_surviellance.latitude', 
                                'planting_hole_surviellance.longitude','planting_hole_surviellance.is_validate',
                                'planting_hole_surviellance.validate_by','planting_hole_surviellance.is_dell', 
                                'planting_hole_surviellance.created_at', 'planting_hole_surviellance.user_id'
                                )
                        ->leftjoin('planting_hole_surviellance', 'planting_hole_surviellance.ph_form_no', '=', 'planting_hole_details.ph_form_no')
                        ->join('trees', 'trees.tree_code', '=', 'planting_hole_details.tree_code')
                        ->leftjoin('lahans', 'lahans.lahan_no', '=', 'planting_hole_surviellance.lahan_no')
                        ->leftjoin('farmers', 'farmers.farmer_no', '=', 'lahans.farmer_no')
                        ->leftjoin('field_facilitators', 'field_facilitators.ff_no', '=', 'planting_hole_surviellance.user_id')
                        ->where('planting_hole_surviellance.is_dell','=',0)                                        
                        ->where('planting_hole_surviellance.ph_form_no','=',$valphall->ph_form_no)
						->orderBy('trees.tree_category', 'DESC')
                        // ->where('ph_form_no','=',$GetPH->ph_form_no)
                        ->get();

					// $dataxx = ['code' => $valphall->ph_form_no,'count' => count($GetPH)];
					// array_push($listxxx, $dataxx);

					$datavalpohon = [];
					$listvalpohon=array();
					$datavalbag = [];
					$listvalbag=array(); 

					$datavaltemp = [];
					$listvaltemp=array();
					$datavalbag = [];
					$listvalbag=array();
					$looping = false;
					$amount_loop = 0; 
					$mount_total_temp = 0;
					$sisa = 0;
					$previous_category = '-';
					$x = 0; 
					$previous_code_ph = '-';
					$max = 0;
					$datamaxbagph = [];
					$listmaxbagph=array();

					foreach($GetPH as  $valpohon){

						$GetSosialisasiDetail = DB::table('planting_socializations')
									->select('planting_socializations.id','planting_socializations.no_lahan',
									'planting_socializations.farmer_no','planting_socializations.form_no','planting_socializations.planting_year',
									'planting_socializations.no_document', 'planting_socializations.ff_no','planting_socializations.validation','planting_socializations.validate_by',
									'planting_socializations.is_dell', 'planting_socializations.created_at')
									->where('planting_socializations.no_lahan','=',$valpohon->lahan_no)
									->first();

						$Desas = DB::table('desas')->where('kode_desa','=',$valpohon->village)->first();
						$nama_desa = '-';
						if($Desas){
							$nama_desa = $Desas->name;
						}
						
						$planting_period = DB::table('planting_period')->where('form_no','=',$GetSosialisasiDetail->form_no)->first();
						$distribution_time = '-';
						$distribution_location = '-';
						if($planting_period){
							// $distribution_time = $planting_period->distribution_time;
							$date=date_create($planting_period->distribution_time);
							$distribution_time = date_format($date,"d-F-Y");
							$distribution_location = $planting_period->distribution_location;
						}

						$nama_ff = $valpohon->nama_ff;
						$nama_petani = $valpohon->nama_petani;
						$ph_form_no = $valpohon->ph_form_no;
						$lahan_no = $valpohon->lahan_no;
						$total_holes = $valpohon->total_holes;
						

						$new_name =$valpohon->tree_name;
						if (strripos($valpohon->tree_name, "Crops") !== false) {
							$new_name = substr($valpohon->tree_name,0,-8);
						}

						// $dataxx = ['code' => $valphall->ph_form_no,'count' => count($GetPH),'nama_desa' => $nama_desa,
						// 'distribution_location' => $distribution_location,'distribution_time' => $distribution_time,
						// 'valpohon' => $new_name,'amount' => $valpohon->amount];
						// array_push($listxxx, $dataxx);

						if($valpohon->tree_category =='Pohon_Kayu'){
								$batas = $max_kayu;
								$pohon_kategori = 'Pohon_Kayu';

								if($valpohon->amount > $batas){
									$looping = true;
									if ($sisa != 0 && $previous_category==$valpohon->tree_category){
										$valsisainput = $max_kayu-$sisa;
										$datavaltemp = ['pohon' => $new_name,'amount' => $valsisainput];
										array_push($listvaltemp, $datavaltemp);

										$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
										'pohon_kategori'=>$pohon_kategori,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
										'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
										array_push($listvalbag, $datavalbag);

										$amount_loop = ceil($valpohon->amount - $valsisainput/$max_kayu);
										$mount_total_temp = $valpohon->amount - $valsisainput;
									}else{
										if($sisa != 0){
											$prv_ctg = $this->convertcategorytrees($previous_category);
											$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
											'pohon_kategori'=>$prv_ctg,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
											'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
											array_push($listvalbag, $datavalbag);
										}                                
										$amount_loop = ceil($valpohon->amount/$max_kayu);
										$mount_total_temp = $valpohon->amount;
									}  
								}else{
										$looping = false;
										$datavaltemp = ['pohon' => $new_name,'amount' => $valpohon->amount];
										array_push($listvaltemp, $datavaltemp);

										$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
										'pohon_kategori'=>$pohon_kategori,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
										'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
										array_push($listvalbag, $datavalbag);

										$datavaltemp = [];
										$listvaltemp=array();
								}
																																
						}else if($valpohon->tree_category =='Pohon_Buah'){
							$pohon_kategori = 'Pohon_Buah (MPTS)';
							if($valpohon->amount > $max_mpts){
								$looping = true;
								if ($sisa != 0 && $previous_category==$valpohon->tree_category){
									$valsisainput = $max_mpts-$sisa;
									$datavaltemp = ['pohon' => $new_name,'amount' => $valsisainput];
									array_push($listvaltemp, $datavaltemp);

									$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
									'pohon_kategori'=>$pohon_kategori,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
									'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
									array_push($listvalbag, $datavalbag);

									$amount_loop = ceil($valpohon->amount - $valsisainput/$max_mpts);
									$mount_total_temp = $valpohon->amount - $valsisainput;
								}else{  
									if($sisa != 0){
										$prv_ctg = $this->convertcategorytrees($previous_category);
										$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
										'pohon_kategori'=>$prv_ctg,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
										'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
										array_push($listvalbag, $datavalbag);
									}                               
									$amount_loop = ceil($valpohon->amount/$max_mpts);
									$mount_total_temp = $valpohon->amount;
								} 
								// $amount_loop = ceil($valpohon->amount/6);
								// $mount_total_temp = $valpohon->amount;
							}else{
								$looping = false;
								$datavaltemp = ['pohon' => $new_name,'amount' => $valpohon->amount];
								array_push($listvaltemp, $datavaltemp);

								$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
								'pohon_kategori'=>$pohon_kategori,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
								'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
								array_push($listvalbag, $datavalbag);

								$datavaltemp = [];
								$listvaltemp=array();
							}
						}else{
							$pohon_kategori = 'Crops';
							if($valpohon->amount > $max_crops){
								$looping = true;
								if ($sisa != 0 && $previous_category==$valpohon->tree_category){
									$valsisainput = $max_crops-$sisa;
									$datavaltemp = ['pohon' => $new_name,'amount' => $valsisainput];
									array_push($listvaltemp, $datavaltemp);

									$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
									'pohon_kategori'=>$pohon_kategori,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
									'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
									array_push($listvalbag, $datavalbag);

									$amount_loop = ceil($valpohon->amount - $valsisainput/$max_crops);
									$mount_total_temp = $valpohon->amount - $valsisainput;
								}else{    
									if($sisa != 0){
										$prv_ctg = $this->convertcategorytrees($previous_category);
										$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
										'pohon_kategori'=>$prv_ctg,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
										'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
										array_push($listvalbag, $datavalbag);
									}                             
									$amount_loop = ceil($valpohon->amount/$max_crops);
									$mount_total_temp = $valpohon->amount;
								} 
								// $amount_loop = ceil($valpohon->amount/5);
								// $mount_total_temp = $valpohon->amount;
							}else{
								$looping = false;
								$datavaltemp = ['pohon' => $new_name,'amount' => $valpohon->amount];
								array_push($listvaltemp, $datavaltemp);

								$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
								'pohon_kategori'=>$pohon_kategori,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
								'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
								array_push($listvalbag, $datavalbag);

								$datavaltemp = [];
								$listvaltemp=array();
							}
						}
							
						if( $looping == true){
							$nn = 1;
							for ($x = 1; $x <= $amount_loop; $x++) {
								$datavaltemp = [];
								$listvaltemp=array();
								$sisa=0;

								if($nn>$amount_loop){
									break;
								}

								if($valpohon->tree_category =='Pohon_Kayu'){
									if($mount_total_temp > $max_kayu){
												
										$datavaltemp = ['pohon' => $new_name,'amount' => $max_kayu];
										array_push($listvaltemp, $datavaltemp);
										
										$mount_total_temp = $mount_total_temp - $max_kayu;

										
										$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
										'pohon_kategori'=>$pohon_kategori,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
										'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
										array_push($listvalbag, $datavalbag);
									}else{
										if($mount_total_temp == 0){
											break;
										}
										$datavaltemp = ['pohon' => $new_name,'amount' => $mount_total_temp];
										array_push($listvaltemp, $datavaltemp);

										if($mount_total_temp == $max_kayu){
											$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
											'pohon_kategori'=>$pohon_kategori,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
											'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
											array_push($listvalbag, $datavalbag);

											$datavaltemp = [];
											$listvaltemp=array();
											$sisa=0;
										}else{
											$sisa = $mount_total_temp;
											$previous_category=$valpohon->tree_category;
										}
									}

								}else if($valpohon->tree_category =='Pohon_Buah'){
									if($mount_total_temp > $max_mpts){
										$datavaltemp = ['pohon' => $new_name,'amount' => $max_mpts];
										array_push($listvaltemp, $datavaltemp);

										
										$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
										'pohon_kategori'=>$pohon_kategori,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
										'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
										array_push($listvalbag, $datavalbag);
										$mount_total_temp = $mount_total_temp - $max_mpts;
									}else{
										$datavaltemp = ['pohon' => $new_name,'amount' => $mount_total_temp];
										array_push($listvaltemp, $datavaltemp);

										if($mount_total_temp == $max_mpts){
											$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
											'pohon_kategori'=>$pohon_kategori,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
											'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
											array_push($listvalbag, $datavalbag);

											$datavaltemp = [];
											$listvaltemp=array();
											$sisa=0;
										}else{
											$sisa = $mount_total_temp;
											$previous_category=$valpohon->tree_category;
										}
									}

								}else{
									if($mount_total_temp > $max_crops){
										$datavaltemp = ['pohon' => $new_name,'amount' => $max_crops];
										array_push($listvaltemp, $datavaltemp);

										
										$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
										'pohon_kategori'=>$pohon_kategori,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
										'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
										array_push($listvalbag, $datavalbag);
										$mount_total_temp = $mount_total_temp - $max_crops;
									}else{
										$datavaltemp = ['pohon' => $new_name,'amount' => $mount_total_temp];
										array_push($listvaltemp, $datavaltemp);
										if($mount_total_temp == $max_crops){
											$datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
											'pohon_kategori'=>$pohon_kategori,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
											'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];
											array_push($listvalbag, $datavalbag);

											$datavaltemp = [];
											$listvaltemp=array();
											$sisa=0;
										}else{
											$sisa = $mount_total_temp;
											$previous_category=$valpohon->tree_category;
										}
										
									}

								}

								$nn = $nn + 1;
							}
						}
					}

					// $prv_ctg = $this->convertcategorytrees($previous_category);
					// $datavalbag = ['ph_form_no'=>$ph_form_no,'nama_ff'=>$nama_ff,'nama_petani'=>$nama_petani,'distribution_time'=>$distribution_time,
					// 'pohon_kategori'=>$prv_ctg,'nama_desa'=>$nama_desa,'distribution_location'=>$distribution_location,
					// 'lahan_no'=>$lahan_no,'total_holes'=>$total_holes,'listvaltemp'=>$listvaltemp];;
					// array_push($listvalbag, $datavalbag);
					$GetLahanNoSosialisasi = DB::table('planting_socializations')
									->join('planting_period', 'planting_period.form_no', '=', 'planting_socializations.form_no')
									->where('planting_socializations.no_lahan','=',$valphall->lahan_no)
									->first();
									
					$distribution_time = '-';
					$distribution_location = '-';
					if($planting_period){
						$date=date_create($GetLahanNoSosialisasi->distribution_time);
						$distribution_time = date_format($date,"d-F-Y");
						$distribution_location = $GetLahanNoSosialisasi->distribution_location;
					}
					$nama_ff = $valphall->nama_ff;
				
					$datavalfix = [];
					$listvalfix=array();
					$ii = 1;
					$countlist = count($listvalbag);

					$xxx = $xxx+1;
					if($ff_code_previous == '-'){
						$ff_code_previous = $valphall->user_id;
					}

					if($valphall->user_id == $ff_code_previous){
						$qty_total = $qty_total+$countlist; 
					}else{
						$ff_code_previous = $valphall->user_id;
						$datafftemp = ['nama_ff'=>$nama_ff,'qty_total'=>$qty_total,'distribution_location'=>$distribution_location];
						array_push($listfftemp, $datafftemp);
					}
					if($xxx ==$countmax ){
						// var_dump('last');
						// $ff_code_previous = $valphall->user_id;
						$datafftemp = ['nama_ff'=>$nama_ff,'qty_total'=>$qty_total,'distribution_location'=>$distribution_location];
						array_push($listfftemp, $datafftemp);
						// var_dump($listfftemp);
					}
			
				}                   
                                    

				// var_dump($xxx);
				// var_dump($countmax);
				// var_dump($listfftemp);

				$nama_title = 'Cetak Excel Shipping Plan Report'; 
                $listvalbag = $listxxx;

                return view('cetakShippingPlan', compact('nama_title','listfftemp', 'distribution_time'));
            }else{
                $rslt =  $this->ResultReturn(404, 'doesnt match data', 'data tidak ada');
                return response()->json($rslt, 404);
            }
            // var_dump(count($GetLahanNotComplete));
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }

	public function convertcategorytrees($val){
		if($val =='Pohon_Buah'){
			return 'Pohon_Buah (MPTS)';
		}else if($val =='Pohon_Kayu'){
			return 'Pohon_Kayu';
		}else{
			return 'Crops';
		}
	}

    /**
     * @SWG\Post(
     *   path="/api/AddPlantingHole",
	 *   tags={"PlantingHole"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Add PlantingHole",
     *   operationId="AddPlantingHole",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Add PlantingHole",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="user_id", type="string", example="FF0001"),
     *              @SWG\Property(property="lahan_no", type="string", example="L0000001"),
     *              @SWG\Property(property="total_holes", type="string", example="2021"),
     *              @SWG\Property(property="counter_hole_standard", type="string", example="50"),
     *              @SWG\Property(property="planting_year", type="string", example="2021"),
     *              @SWG\Property(property="farmer_signature", type="string", example="-"),
     *              @SWG\Property(property="gambar1", type="string", example="Nullable"),
     *              @SWG\Property(property="gambar2", type="string", example="Nullable"),
     *              @SWG\Property(property="list_pohon", type="string", example="array pohon json decode tree_code dan amount"),
     *          ),
     *      )
     * )
     *
     */
    public function AddPlantingHole(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'lahan_no' => 'required|unique:planting_hole_surviellance', 
            'total_holes' => 'required',
            'counter_hole_standard' => 'required',
            'planting_year' => 'required',
            'farmer_signature' => 'required',
            // 'list_pohon' => 'required',
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        } 

        DB::beginTransaction();

        try{             
            
            $Lahan = DB::table('lahans')->where('lahan_no','=',$request->lahan_no)->first();
            
            // print_r($Lahan);
            if($Lahan){
                $year = Carbon::now()->format('Y');
                $ph_form_no = 'PH-'.$year.'-'.substr($request->lahan_no,-10);

                $validation = 0;
                $validate_by = '-';
                if($request->validate_by){
                    $validation = 1;
                    $validate_by = $request->validate_by;
                }

                $pohon_mpts = 0;
                $pohon_non_mpts = 0;
                $pohon_bawah = 0;

                foreach($request->list_pohon as $val){
                    PlantingHoleSurviellanceDetail::create([
                        'ph_form_no' => $ph_form_no,
                        'tree_code' => $val['tree_code'],
                        'amount' => $val['amount'],
        
                        'created_at'=>Carbon::now(),
                        'updated_at'=>Carbon::now()
                    ]);


                    $trees_get = DB::table('trees')->where('tree_code','=',$val['tree_code'])->first();

                    if( $trees_get->tree_category == "Pohon_Buah"){
                        $pohon_mpts = $pohon_mpts + $val['amount'];
                    }else if($trees_get->tree_category == "Tanaman_Bawah_Empon"){
                        $pohon_bawah = $pohon_bawah + $val['amount'];
                    }else{
                        $pohon_non_mpts = $pohon_non_mpts + $val['amount'];
                    }
                }

                PlantingHoleSurviellance::create([
                    'ph_form_no' => $ph_form_no,
                    'planting_year' => $request->planting_year,
                    'total_holes' => $request->total_holes,
                    'counter_hole_standard' => $request->counter_hole_standard,
                    'farmer_signature' => $request->farmer_signature,
                    'gambar1' => $this->ReplaceNull($request->gambar1, 'string'),
                    'gambar2' => $this->ReplaceNull($request->gambar2, 'string'),
                    'gambar3' => $this->ReplaceNull($request->gambar3, 'string'),
                    'lahan_no' => $request->lahan_no,
                    'latitude' => $Lahan->latitude,
                    'longitude' => $Lahan->longitude,
                    'is_validate' => $validation,
                    'validate_by' => $validate_by,

                    'pohon_kayu' => $pohon_non_mpts,
                    'pohon_mpts' => $pohon_mpts,
                    'tanaman_bawah' => $pohon_bawah,
    
                    'user_id' => $request->user_id,
    
                    'created_at'=>Carbon::now(),
                    'updated_at'=>Carbon::now(),
    
                    'is_dell' => 0
                ]);
                
                // create logs
                $this->createLogs([
                    'status' => 'Created',
                    'ph_form_no' => $ph_form_no
                ]);

                DB::commit();
    
                $rslt =  $this->ResultReturn(200, 'success', 'success');
                return response()->json($rslt, 200); 
            }else{
                $rslt =  $this->ResultReturn(400, 'gagal', 'gagal');
                return response()->json($rslt, 400);
            }

            
        }catch (\Exception $ex){
            DB::rollback();
            $rslt =  $this->ResultReturn(400, 'gagal',$ex);
            return response()->json($rslt, 400);
        }
    }

	/**
     * @SWG\Post(
     *   path="/api/AddPlantingHoleByFFNo",
	 *   tags={"PlantingHole"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Add PlantingHole by FF",
     *   operationId="AddPlantingHoleByFFNo",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Add PlantingHole by FF",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="ff_no", type="string", example="FF0001"),
     *          ),
     *      )
     * )
     *
     */
    public function AddPlantingHoleByFFNo(Request $request){
        $validator = Validator::make($request->all(), [
            // 'user_id' => 'required',
            'ff_no' => 'required', 
            // 'total_holes' => 'required', 
            // 'planting_year' => 'required',
            // 'farmer_signature' => 'required',
            // 'list_pohon' => 'required',
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        } 

        DB::beginTransaction();

        try{      
			
			// $ph = DB::table('planting_hole_surviellance')->where('user_id','=',$request->ff_no)->get();

			// if(count($ph)!=0){
			// 	$rslt =  $this->ResultReturn(400, 'gagal', 'gagal');
			// 		return response()->json($rslt, 400);
			// }else{
				$sostam = DB::table('planting_socializations')->where('ff_no','=',$request->ff_no)->get();

				// var_dump($sostam);

				if(count($sostam)!=0){
					foreach($sostam as $valxxx){
						$phready = DB::table('planting_hole_surviellance')->where('lahan_no','=',$valxxx->no_lahan)->get();
						if(count($phready)==0){
							$Lahan = DB::table('lahans')->where('lahan_no','=',$valxxx->no_lahan)->first();

						// var_dump($valxxx->no_lahan);

							$year = Carbon::now()->format('Y');
							$ph_form_no = 'PH-'.$year.'-'.substr($valxxx->no_lahan,-10);
			
							$validation = 0;
							$validate_by = '-';
			
							$sostamdetail = DB::table('planting_details')->where('form_no','=',$valxxx->form_no)->get();

							$pohon_mpts = 0;
							$pohon_non_mpts = 0;
							$pohon_bawah = 0;
							$total = 0;
							foreach($sostamdetail as $val){
								PlantingHoleSurviellanceDetail::create([
									'ph_form_no' => $ph_form_no,
									'tree_code' => $val->tree_code,
									'amount' => $val->amount,
					
									'created_at'=>Carbon::now(),
									'updated_at'=>Carbon::now()
								]);
			
								$trees_get = DB::table('trees')->where('tree_code','=',$val->tree_code)->first();
			
								if( $trees_get->tree_category == "Pohon_Buah"){
									$pohon_mpts = $pohon_mpts + $val->amount;
								}else if($trees_get->tree_category == "Tanaman_Bawah_Empon"){
									$pohon_bawah = $pohon_bawah + $val->amount;
								}else{
									$pohon_non_mpts = $pohon_non_mpts + $val->amount;
								}

								$total = $total + $val->amount;
							}

							PlantingHoleSurviellance::create([
								'ph_form_no' => $ph_form_no,
								'planting_year' => $valxxx->planting_year,
								'total_holes' => $total,
								'counter_hole_standard' => $request->counter_hole_standard,
								'farmer_signature' => $this->ReplaceNull($request->farmer_signature, 'string'),
								'gambar1' => $this->ReplaceNull($request->gambar1, 'string'),
								'gambar2' => $this->ReplaceNull($request->gambar2, 'string'),
								'gambar3' => $this->ReplaceNull($request->gambar3, 'string'),
								'lahan_no' => $valxxx->no_lahan,
								'latitude' => $Lahan->latitude,
								'longitude' => $Lahan->longitude,
								'is_validate' => $validation,
								'validate_by' => $validate_by,
			
								'pohon_kayu' => $pohon_non_mpts,
								'pohon_mpts' => $pohon_mpts,
								'tanaman_bawah' => $pohon_bawah,
				
								'user_id' => $request->ff_no,
				
								'created_at'=>Carbon::now(),
								'updated_at'=>Carbon::now(),
				
								'is_dell' => 0
							]);
                
                            // create logs
                            $this->createLogs([
                                'status' => 'Created',
                                'ph_form_no' => $ph_form_no
                            ]);
			
							DB::commit();
						}

						

					}

					$rslt =  $this->ResultReturn(200, 'success', 'success');
					return response()->json($rslt, 200); 
				}else{
					$rslt =  $this->ResultReturn(400, 'gagal', 'gagal');
					return response()->json($rslt, 400);
				}
			// }

			

            
        }catch (\Exception $ex){
            DB::rollback();
            $rslt =  $this->ResultReturn(400, 'gagal',$ex);
            return response()->json($rslt, 400);
        }
    }

    /**
     * @SWG\Post(
     *   path="/api/UpdatePlantingHole",
	 *   tags={"PlantingHole"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Update PlantingHole",
     *   operationId="UpdatePlantingHole",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Update PlantingHole",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="ph_form_no", type="string", example="PH-2021-0000001"),
     *              @SWG\Property(property="user_id", type="string", example="FF0001"),
     *              @SWG\Property(property="lahan_no", type="string", example="L0000001"),
     *              @SWG\Property(property="total_holes", type="string", example="2021"),
     *              @SWG\Property(property="planting_year", type="string", example="2021"),
     *              @SWG\Property(property="farmer_signature", type="string", example="-"),
     *              @SWG\Property(property="gambar1", type="string", example="Nullable"),
     *              @SWG\Property(property="gambar2", type="string", example="Nullable"),
     *          ),
     *      )
     * )
     *
     */
    public function UpdatePlantingHole(Request $request){
        $validator = Validator::make($request->all(), [
            'ph_form_no' => 'required',
            'user_id' => 'required',
            'lahan_no' => 'required', 
            'total_holes' => 'required',
            'counter_hole_standard' => 'required',
            'planting_year' => 'required',
            'farmer_signature' => 'required',
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        }  
        
        DB::beginTransaction();

        try{            
            
            $form_no_old = $request->ph_form_no;
            $Lahan = DB::table('lahans')->where('lahan_no','=',$request->lahan_no)->first();
            $planting_hole_surviellance = DB::table('planting_hole_surviellance')->where('ph_form_no','=',$form_no_old)->first();
            
            if($planting_hole_surviellance){
                
                if($planting_hole_surviellance->is_validate == 1) {
                $rslt =  $this->ResultReturn(400, 'Sudah terverifikasi!!!', 'Sudah terverifikasi!!!');
                return response()->json($rslt, 400);
                }
                
                $year = Carbon::now()->format('Y');
                // $form_no = 'PH-'.$year.'-'.substr($request->lahan_no,-10);

                $validation = 0;
                $validate_by = '-';
                if($request->validate_by){
                    $validation = 1;
                    $validate_by = $request->validate_by;
                }

                PlantingHoleSurviellance::where('ph_form_no', '=', $form_no_old)
                ->update([
                    // 'form_no' => $form_no,
                    'planting_year' => $request->planting_year,
                    'total_holes' => $request->total_holes,
                    'counter_hole_standard' => $request->counter_hole_standard,
                    'farmer_signature' => $request->farmer_signature,
                    'gambar1' => $this->ReplaceNull($request->gambar1, 'string'),
                    'gambar2' => $this->ReplaceNull($request->gambar2, 'string'),
                    'gambar3' => $this->ReplaceNull($request->gambar3, 'string'),
                    'lahan_no' => $request->lahan_no,
                    'latitude' => $Lahan->latitude,
                    'longitude' => $Lahan->longitude,
                    'is_validate' => $validation,
                    'validate_by' => $validate_by,
    
                    'user_id' => $request->user_id,
    
                    'updated_at'=>Carbon::now(),
    
                ]);
                
                // create logs
                $this->createLogs([
                    'status' => 'Updated [' . implode(',', $request->all()) . '] in',
                    'ph_form_no' => $form_no_old
                ]);

                DB::commit();
    
                $rslt =  $this->ResultReturn(200, 'success', 'success');
                return response()->json($rslt, 200); 
            }else{
                $rslt =  $this->ResultReturn(400, 'doesnt match data', 'doesnt match data');
                return response()->json($rslt, 400);
            }
            
        }catch (\Exception $ex){
            DB::rollback();
            $rslt =  $this->ResultReturn(400, 'gagal',$ex);
            return response()->json($rslt, 400);
        }
    }

    /**
     * @SWG\Post(
     *   path="/api/UpdatePlantingHoleAll",
	 *   tags={"PlantingHole"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Update PlantingHole All",
     *   operationId="UpdatePlantingHoleAll",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Update PlantingHole All",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="ph_form_no", type="string", example="PH-2021-0000001"),
     *              @SWG\Property(property="user_id", type="string", example="FF0001"),
     *              @SWG\Property(property="lahan_no", type="string", example="L0000001"),
     *              @SWG\Property(property="total_holes", type="string", example="2021"),
     *              @SWG\Property(property="planting_year", type="string", example="2021"),
     *              @SWG\Property(property="farmer_signature", type="string", example="-"),
     *              @SWG\Property(property="list_pohon", type="string", example="array pohon json decode tree_code dan amount"),
     *              @SWG\Property(property="gambar1", type="string", example="Nullable"),
     *              @SWG\Property(property="gambar2", type="string", example="Nullable"),
     *          ),
     *      )
     * )
     *
     */
    public function UpdatePlantingHoleAll(Request $request){
        $validator = Validator::make($request->all(), [
            'ph_form_no' => 'required',
            'user_id' => 'required',
            'lahan_no' => 'required', 
            'total_holes' => 'required', 
            'planting_year' => 'required',
            'farmer_signature' => 'required',
            'list_pohon' => 'required',
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        }  
        
        DB::beginTransaction();

        try{            
            
            $form_no_old = $request->ph_form_no;
            $Lahan = DB::table('lahans')->where('lahan_no','=',$request->lahan_no)->first();
            $planting_hole_surviellance = DB::table('planting_hole_surviellance')->where('ph_form_no','=',$form_no_old)->first();
            
            if($planting_hole_surviellance){
                $year = Carbon::now()->format('Y');
                // $form_no = 'PH-'.$year.'-'.substr($request->lahan_no,-10);

                DB::table('planting_hole_details')->where('ph_form_no', $form_no_old)->delete();

                
                $pohon_mpts = 0;
                $pohon_non_mpts = 0;
                $pohon_bawah = 0;

                foreach($request->list_pohon as $val){
                    PlantingHoleSurviellanceDetail::create([
                        'ph_form_no' => $form_no_old,
                        'tree_code' => $val['tree_code'],
                        'amount' => $val['amount'],
        
                        'created_at'=>Carbon::now(),
                        'updated_at'=>Carbon::now()
                    ]);
                
                    // create logs
                    $this->createLogs([
                        'status' => 'Updated Trees ' . '[' . $val['tree_code'] .','. $val['amount'] . '] in',
                        'ph_form_no' => $form_no_old
                    ]);

                    $trees_get = DB::table('trees')->where('tree_code','=',$val['tree_code'])->first();

                    if( $trees_get->tree_category == "Pohon_Buah"){
                        $pohon_mpts = $pohon_mpts + $val['amount'];
                    }else if($trees_get->tree_category == "Tanaman_Bawah_Empon"){
                        $pohon_bawah = $pohon_bawah + $val['amount'];
                    }else{
                        $pohon_non_mpts = $pohon_non_mpts + $val['amount'];
                    }
                }

                $validation = 0;
                $validate_by = '-';
                if($request->validate_by){
                    $validation = 1;
                    $validate_by = $request->validate_by;
                }

                PlantingHoleSurviellance::where('ph_form_no', '=', $form_no_old)
                ->update([
                    // 'form_no' => $form_no,
                    'planting_year' => $request->planting_year,
                    'total_holes' => $request->total_holes,
                    'counter_hole_standard' => $request->counter_hole_standard,
                    'farmer_signature' => $request->farmer_signature,
                    'gambar1' => $this->ReplaceNull($request->gambar1, 'string'),
                    'gambar2' => $this->ReplaceNull($request->gambar2, 'string'),
                    'gambar3' => $this->ReplaceNull($request->gambar3, 'string'),
                    'lahan_no' => $request->lahan_no,
                    'latitude' => $Lahan->latitude,
                    'longitude' => $Lahan->longitude,
                    'is_validate' => $validation,
                    'validate_by' => $validate_by,
                    'pohon_kayu' => $pohon_non_mpts,
                    'pohon_mpts' => $pohon_mpts,
                    'tanaman_bawah' => $pohon_bawah,
    
                    'user_id' => $request->user_id,
    
                    'updated_at'=>Carbon::now(),
    
                    // 'is_dell' => 0
                ]);
                
                // create logs
                $this->createLogs([
                    'status' => 'Updated [' . 'total_holes => ' . $request->total_holes . ', total_hole_standard => ' . $request->counter_hole_standard . '] in',
                    'ph_form_no' => $form_no_old
                ]);

                // $LahanDetails = DB::table('lahan_details')->where('lahan_no','=',$request->no_lahan)->get();

                // DB::table('planting_details')->where('form_no', $form_no_old)->delete();

                // foreach($LahanDetails as $val){
                //     PlantingSocializationsDetails::create([
                //         'form_no' => $form_no,
                //         'tree_code' => $val->tree_code,
                //         'amount' => $val->amount,
        
                //         'created_at'=>Carbon::now(),
                //         'updated_at'=>Carbon::now()
                //     ]);
                // }

                DB::commit();
    
                $rslt =  $this->ResultReturn(200, 'success', 'success');
                return response()->json($rslt, 200); 
            }else{
                $rslt =  $this->ResultReturn(400, 'doesnt match data', 'doesnt match data');
                return response()->json($rslt, 400);
            }

            
        }catch (\Exception $ex){
            DB::rollback();
            $rslt =  $this->ResultReturn(400, 'gagal',$ex);
            return response()->json($rslt, 400);
        }
    }

    /**
     * @SWG\Post(
     *   path="/api/UpdatePohonPlantingHole",
	 *   tags={"PlantingHole"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Update Pohon PlantingHole",
     *   operationId="UpdatePohonPlantingHole",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Update Pohon PlantingHole",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="ph_form_no", type="string", example="SO-2021-0000001"),
     *              @SWG\Property(property="list_pohon", type="string", example="array pohon bosku"),
     *          ),
     *      )
     * )
     *
     */
    public function UpdatePohonPlantingHole(Request $request){
        $validator = Validator::make($request->all(), [
            'ph_form_no' => 'required',
            'list_pohon' => 'required',
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        } 

        DB::beginTransaction();

        try{
             
            
            $form_no_old = $request->ph_form_no;
            $list_pohon = $request->list_pohon;
            $planting_hole_surviellance = DB::table('planting_hole_surviellance')->where('ph_form_no','=',$form_no_old)->first();
            
            if($planting_hole_surviellance){
                
                DB::table('planting_hole_details')->where('ph_form_no', $form_no_old)->delete();

                
                $pohon_mpts = 0;
                $pohon_non_mpts = 0;
                $pohon_bawah = 0;

                foreach($request->list_pohon as $val){
                    PlantingHoleSurviellanceDetail::create([
                        'ph_form_no' => $form_no_old,
                        'tree_code' => $val['tree_code'],
                        'amount' => $val['amount'],
        
                        'created_at'=>Carbon::now(),
                        'updated_at'=>Carbon::now()
                    ]);
                
                
                    // create logs
                    $this->createLogs([
                        'status' => 'Updated Trees ' . '[' . $val['tree_code'] .','. $val['amount'] . '] in',
                        'ph_form_no' => $form_no_old
                    ]);

                    $trees_get = DB::table('trees')->where('tree_code','=',$val['tree_code'])->first();

                    if( $trees_get->tree_category == "Pohon_Buah"){
                        $pohon_mpts = $pohon_mpts + $val['amount'];
                    }else if($trees_get->tree_category == "Tanaman_Bawah_Empon"){
                        $pohon_bawah = $pohon_bawah + $val['amount'];
                    }else{
                        $pohon_non_mpts = $pohon_non_mpts + $val['amount'];
                    }
                }

                PlantingHoleSurviellance::where('ph_form_no', '=', $form_no_old)
                ->update([
                    // 'form_no' => $form_no,
                    'pohon_kayu' => $pohon_non_mpts,
                    'pohon_mpts' => $pohon_mpts,
                    'tanaman_bawah' => $pohon_bawah,
    
                    'updated_at'=>Carbon::now(),
    
                    // 'is_dell' => 0
                ]);

                DB::commit();
    
                $rslt =  $this->ResultReturn(200, 'success', 'success');
                return response()->json($rslt, 200); 
            }else{
                $rslt =  $this->ResultReturn(400, 'doesnt match data', 'doesnt match data');
                return response()->json($rslt, 400);
            }

            
        }catch (\Exception $ex){
            DB::rollback();
            $rslt =  $this->ResultReturn(400, 'gagal',$ex);
            return response()->json($rslt, 400);
        }
    }

    /**
     * @SWG\Post(
     *   path="/api/SoftDeletePlantingHole",
	 *   tags={"PlantingHole"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="SoftDelete PlantingHole",
     *   operationId="SoftDeletePlantingHole",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="SoftDelete PlantingHole",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="ph_form_no", type="string", example="SO-2021-0000001"),
     *          ),
     *      )
     * )
     *
     */
    public function SoftDeletePlantingHole(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'ph_form_no' => 'required',
            ]);

            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }  
            
            $form_no_old = $request->ph_form_no;
            $planting_hole_surviellance = DB::table('planting_hole_surviellance')->where('ph_form_no','=',$form_no_old)->first();
            
            if($planting_hole_surviellance){

                PlantingHoleSurviellance::where('ph_form_no', '=', $form_no_old)
                ->update([    
                    'updated_at'=>Carbon::now(),    
                    'is_dell' => 1
                ]);
                
                
                // create logs
                $this->createLogs([
                    'status' => 'Soft Deleted',
                    'ph_form_no' => $form_no_old
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

    /**
     * @SWG\Post(
     *   path="/api/ValidatePlantingHole",
	 *   tags={"PlantingHole"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Validate PlantingHole",
     *   operationId="ValidatePlantingHole",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Validate PlantingHole",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="ph_form_no", type="string", example="SO-2021-0000001"),
     *              @SWG\Property(property="validate_by", type="string", example="00-11010"),
     *          ),
     *      )
     * )
     *
     */
    public function ValidatePlantingHole(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'ph_form_no' => 'required',
                'validate_by' => 'required',
            ]);

            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }  
            
            $form_no_old = $request->ph_form_no;
            $planting_hole_surviellance = DB::table('planting_hole_surviellance')->where('ph_form_no','=',$form_no_old)->first();
            
            if($planting_hole_surviellance){

                PlantingHoleSurviellance::where('ph_form_no', '=', $form_no_old)
                ->update([    
                    'updated_at'=>Carbon::now(),
                    'validate_by' => $request->validate_by,    
                    'is_validate' => 1
                ]);
                
                
                // create logs
                $this->createLogs([
                    'status' => 'Verified',
                    'ph_form_no' => $form_no_old
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
    
    public function UnvalidatePlantingHole(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'ph_form_no' => 'required',
            ]);

            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }  
            
            $form_no_old = $request->ph_form_no;
            $planting_hole_surviellance = DB::table('planting_hole_surviellance')->where('ph_form_no','=',$form_no_old)->first();
            
            if($planting_hole_surviellance){

                PlantingHoleSurviellance::where('ph_form_no', '=', $form_no_old)
                ->update([    
                    'updated_at'=>Carbon::now(),
                    'validate_by' => '-',    
                    'is_validate' => 0
                ]);
                
                
                // create logs
                $this->createLogs([
                    'status' => 'Unverified',
                    'ph_form_no' => $form_no_old
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
    
    public function CheckedPlantingHole(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ph_form_no' => 'required|exists:planting_hole_surviellance,ph_form_no',
            'program_year' => 'required'
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        } else {
            $form_no = $request->ph_form_no;
            $py = $request->program_year;
            $user = Auth::user()->email;
        }
        // Get PH data
        $PHData = PlantingHoleSurviellance::where(['ph_form_no' => $form_no, ['is_checked', '!=', 1]])->first();
        if($PHData){
            // Get Lahan
            $lahan = Lahan::where('lahan_no', $PHData->lahan_no)->first();
            if ($lahan) {
                // Get FF
                $ff = FieldFacilitator::where('ff_no', $lahan->user_id)->first();
                // Get Farmer
                $farmer = Farmer::where('farmer_no', $lahan->farmer_no)->first();
                // Get Sostam
                $sostam = DB::table('planting_socializations')->where(['no_lahan' => $lahan->lahan_no, 'planting_year' => $py])->first();
                if ($farmer && $sostam && $ff) {
                    $DCreate = [];
                    // set distribution number
                    $DCreate['distribution_no'] = 'D-' . $py . '-' . $farmer->farmer_no;
                    // get distribution date
                    $DCreate['distribution_date'] = DB::table('planting_period')->where('form_no', $sostam->form_no)->first()->distribution_time;
                    // get labels data
                    $labels = $this->generateSeedlingLabels($form_no)['listLabel'] ?? [];
                    // get total tree amount data
                    $DCreate['total_tree_amount'] = 0;
                    // get total bag data
                    $DCreate['total_bag'] = 0;
                    
                    // return response()->json($labels, 200);
                    
                    // check if Distribution is exist for this farmer
                    $distributionDataExist = Distribution::where('distribution_no', $DCreate['distribution_no'])->count();
                    if ($distributionDataExist == 0) {
                        // Create New Distribution Data
                        $createDistribution = Distribution::create([
                            'distribution_no' => $DCreate['distribution_no'],
                            'distribution_date' => $DCreate['distribution_date'],
                            'ff_no' => $ff->ff_no,
                            'farmer_no' => $farmer->farmer_no,
                            'farmer_signature' => '',
                            'distribution_note' => '',
                            'distribution_photo' => '',
                            'status' => 0,
                            'total_bags' => $DCreate['total_bag'],
                            'total_tree_amount' => $DCreate['total_tree_amount'],
                            'is_loaded' => 0,
                            'loaded_by' => '',
                            'is_distributed' => 0,
                            'distributed_by' => '',
                            'is_dell' => 0,
                        ]);
                    }
                    
                    // create distribution details data
                    foreach($labels as $label) {
                        foreach($label['tree_name'] as $labelTreeIndex => $labelTreeName) {
                            $createDistributionDetail = DistributionDetail::create([
                                'distribution_no' => $DCreate['distribution_no'],
                                'bag_number' => $label['bag_code'],
                                'tree_name' => $labelTreeName,
                                'tree_category' => $label['tree_category'],
                                'tree_amount' => $label['amount'][$labelTreeIndex],
                                'is_loaded' => 0,
                                'loaded_by' => '-',
                                'is_distributed' => 0,
                                'distributed_by' => '-'
                            ]);
                            $DCreate['total_tree_amount'] += $label['amount'][$labelTreeIndex];
                        }
                        
                        $DCreate['total_bag'] += 1;
                    }
                    
                    // update total tree_amount & total bag
                    $distributionNewData = Distribution::where('distribution_no', $DCreate['distribution_no'])->first();
                    $distributionNewData->update([
                        'total_tree_amount' => $distributionNewData->total_tree_amount + $DCreate['total_tree_amount'],
                        'total_bags' => $distributionNewData->total_bags + $DCreate['total_bag'],
                    ]);
                    
                    
                    // updating checked list
                    PlantingHoleSurviellance::where('ph_form_no', '=', $form_no)
                    ->update([    
                        'updated_at'=> Carbon::now(),
                        'checked_by' => $user,    
                        'is_checked' => 1
                    ]);
        
                    $rslt =  $this->ResultReturn(200, 'success', 'success');
                    return response()->json($rslt, 200);   
                } else {
                    $rslt =  $this->ResultReturn(404, 'doesnt match data', 'FF / Farmer / Sostam data not found.');
                    return response()->json($rslt, 404);
                }
            } else {
                $rslt =  $this->ResultReturn(404, 'doesnt match data', 'Lahan data not found.');
                return response()->json($rslt, 404);
            }
        } else {
            $rslt =  $this->ResultReturn(404, 'doesnt match data', 'Planting Hole data not found or its already checked.');
            return response()->json($rslt, 404);
        }
    }
    
    public function ExportExcelPenilikanLubang(Request $req) {
        $validator = Validator::make($req->all(), [
            'program_year' => 'required',
            'type' => 'required',
            'mu' => 'required',
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        }
        
        $penlub = PlantingHoleSurviellance::
            join('field_facilitators', 'field_facilitators.ff_no', 'planting_hole_surviellance.user_id')
            ->join('lahans', 'lahans.lahan_no', 'planting_hole_surviellance.lahan_no')
            ->join('farmers', 'farmers.farmer_no', 'lahans.farmer_no')
            ->join('managementunits', 'managementunits.mu_no', 'field_facilitators.mu_no')
            ->join('target_areas', 'target_areas.area_code', 'field_facilitators.target_area')
            ->join('desas', 'desas.kode_desa', 'field_facilitators.working_area')
            ->select(
                'managementunits.name as mu_name',
                'target_areas.name as ta_name',
                'desas.name as village_name',
                'field_facilitators.name as ff_name',
                'field_facilitators.ff_no',
                'field_facilitators.fc_no',
                'farmers.name as farmer_name',
                'farmers.farmer_no',
                'planting_hole_surviellance.is_validate',
                DB::raw('SUM(planting_hole_surviellance.total_holes) as holes'),
                DB::raw('SUM(planting_hole_surviellance.counter_hole_standard) as holes_standard')
            )
            ->where([
                'planting_hole_surviellance.is_dell' => 0,
                // 'planting_hole_surviellance.is_validate' => 1,
                'planting_hole_surviellance.planting_year' => $req->program_year
            ]);
        
        if ($req->type == 'area') {
            $penlub = $penlub->where('field_facilitators.mu_no', $req->mu);
            if ($req->ta) {
                $penlub = $penlub->where('field_facilitators.target_area', $req->ta);
                
                if ($req->village) {
                    $penlub = $penlub->where('field_facilitators.working_area', $req->village);
                }
            }
        } else if ($type == 'employee') {
            
        }
        
        $penlub = $penlub
            ->groupBy('farmers.farmer_no')
            ->get();
        
        foreach($penlub as $index => $pen) {
            $fc =  Employee::where('nik', $pen->fc_no)->first();
            $umNIK = DB::table('employee_structure')->where('nik', $fc->nik)->first()->manager_code ?? '';
            $penlub[$index]->fc_name = $fc->name ?? '-';
            if ($umNIK) {
                $um =  Employee::where('nik', $umNIK)->first();
                $penlub[$index]->um_name = $um->name ?? '-';
            } else $penlub[$index]->um_name = '-';
        }
        
        return view('exportExcelPenilikanLubangTanam', ['datas' => $penlub]);
    }
    
    // Create Logs
    private function createLogs($logData) {
        // get main data
        $main = PlantingHoleSurviellance::where('ph_form_no', $logData['ph_form_no'])->first();
        // get Lahan Data
        if (isset($main->lahan_no)) {
            $lahan = Lahan::where('lahan_no', $main->lahan_no)->first();
        }
        // get Petani Data
        if (isset($lahan->farmer_no)) {
            $farmer = Farmer::where('farmer_no', $lahan->farmer_no)->first();
        }
        // get ff data
        if(isset($main->user_id)) {
            $ff = FieldFacilitator::where('ff_no', $main->user_id)->first();
        }
        // get fc data
        if (isset($ff->fc_no)) {
            $fc = Employee::where('nik', $ff->fc_no)->first();
        }
        // user auth data
        $user = Auth::user();
        
        // set message
        $message =  $logData['status'] . ' ' . 
                    ($main['ph_form_no'] ?? '-') . 
                    '[lahan = ' . 
                    ($lahan['lahan_no'] ?? '-') .
                    ', petani = ' . 
                    ($farmer['farmer_no'] ?? '-') . '_' . ($farmer['name'] ?? '-') . '_' . ($farmer['nickname'] ?? '-') .
                    ', ff = ' . 
                    ($ff->ff_no ?? '-') . '_' . ($ff->name ?? '-') .
                    ', fc = ' . 
                    ($fc->name ?? '-') .
                    '] ' .
                    'by ' .
                    ($user['email'] ?? '-');
                    
        $log = Log::channel('planting_holes');
        
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
    
    // Generate QR Code
    private function generateqrcode ($val)
    {
        $qrcode = QrCode::size(90)->generate($val);
        return $qrcode;
    }
    
    public function CetakLabelUmumLubangTanamTemp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lahan_no' => 'required' 
        ]);
        
        if($validator->fails()){
            $rslt = $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        }
        
        $labels = $this->generateUmumSeedlingLabels($request->lahan_no);
        // return $labels;
        return view('cetakLabelUmumLubangTanam', $labels);
    }
    
    private function generateUmumSeedlingLabels($lahan_no) {
        // Get Lahan Umum & Distribution Date
        $GetPHUDetail = LahanUmum::
                leftjoin('employees', 'employees.nik', '=', 'lahan_umums.employee_no')
                ->select(
                    'lahan_umums.id', 
                    'lahan_umums.lahan_no', 
                    'lahan_umums.program_year', 
                    'lahan_umums.mu_no',
                    'lahan_umums.latitude', 
                    'lahan_umums.longitude', 
                    'lahan_umums.distribution_date', 
                    'lahan_umums.planting_realization_date', 
                    'lahan_umums.employee_no', 
                    'lahan_umums.is_verified', 
                    'lahan_umums.verified_by', 
                    'lahan_umums.is_dell', 
                    'employees.name as employee_name',
                    'lahan_umums.pic_lahan'
                    )
                ->where('lahan_umums.lahan_no', '=', $lahan_no)
                ->first();
        
        if($GetPHUDetail){
            // GET Seedling List
            $seedCategories = ['KAYU', 'MPTS', 'CROPS'];
            $seedCategoriesLoopCount = 0;
            $perBagsAmount = [
                'KAYU' => 10,
                'MPTS' => [8,6],
                'CROPS' => 10
            ];
            $total8 = ['PETAI', 'MANGGA', 'NANGKA', 'SAWO', 'RAMBUTAN'];
            $seedsList = [];
            // GET per category seedling detail
            foreach($seedCategories as $seedCategory) {
                $seedsList[$seedCategory] = LahanUmumHoleDetail::
                    join('tree_locations', 'tree_locations.tree_code', '=', 'lahan_umum_hole_details.tree_code')
                    ->select('lahan_umum_hole_details.id',
                            'lahan_umum_hole_details.lahan_no',
                            'lahan_umum_hole_details.tree_code',
                            'lahan_umum_hole_details.amount',
                            'tree_locations.tree_name',
                            'tree_locations.category')
                    ->where([
                        ['lahan_umum_hole_details.lahan_no','=',$GetPHUDetail->lahan_no],
                        'tree_locations.mu_no' => $GetPHUDetail->mu_no,
                        'tree_locations.category' => $seedCategory
                    ])
                    ->orderBy('lahan_umum_hole_details.amount', 'DESC')
                    ->get();
                $seedCategoriesLoopCount += 1;
            }
            
            // GET BAGS amount
            $bagsList = [];
            foreach ($seedCategories as $seedCategory) {
                foreach ($seedsList[$seedCategory] as $seedling) {
                    $totalSeed = $seedling->amount;
                    $reminder = 0;
                    if ($seedCategory != 'MPTS') {
                        $totalBagPerSeed = ceil($seedling->amount / $perBagsAmount[$seedCategory]);
                        for ($i = 1;$i <= $totalBagPerSeed;$i++) {
                            $amountReminder = $totalSeed - $perBagsAmount[$seedCategory];
                            $seedData = [
                                'amount_reminder' => $amountReminder,
                                'tree_name' => $seedling->tree_name,
                                'tree_category' => $seedling->category
                            ];
                            if ($amountReminder < 0) {
                                $seedData['amount'] = $perBagsAmount[$seedCategory] + $amountReminder;
                                $reminder = $amountReminder;
                            } else {
                                $seedData['amount'] = $perBagsAmount[$seedCategory];
                            }
                            array_push($bagsList, $seedData);
                            $totalSeed -= $perBagsAmount[$seedCategory];
                        }
                    } else {
                        if (in_array($seedling->tree_name, $total8)) {
                            $totalBagPerSeed = ceil($seedling->amount / $perBagsAmount[$seedCategory][0]);
                            for ($i = 1;$i <= $totalBagPerSeed;$i++) {
                                $amountReminder = $totalSeed - $perBagsAmount[$seedCategory][0];
                                $seedData = [
                                    'amount_reminder' => $amountReminder,
                                    'tree_name' => $seedling->tree_name,
                                    'tree_category' => $seedling->category
                                ];
                                if ($amountReminder < 0) {
                                    $seedData['amount'] = $perBagsAmount[$seedCategory][0] + $amountReminder;
                                    $reminder = $amountReminder;
                                } else {
                                    $seedData['amount'] = $perBagsAmount[$seedCategory][0];
                                }
                                array_push($bagsList, $seedData);
                                $totalSeed -= $perBagsAmount[$seedCategory][0];
                            }
                        } else {
                            $totalBagPerSeed = ceil($seedling->amount / $perBagsAmount[$seedCategory][1]);
                            for ($i = 1;$i <= $totalBagPerSeed;$i++) {
                                $amountReminder = $totalSeed - $perBagsAmount[$seedCategory][1];
                                $seedData = [
                                    'amount_reminder' => $amountReminder,
                                    'tree_name' => $seedling->tree_name,
                                    'tree_category' => $seedling->category
                                ];
                                if ($amountReminder < 0) {
                                    $seedData['amount'] = $perBagsAmount[$seedCategory][1] + $amountReminder;
                                    $reminder = $amountReminder;
                                } else {
                                    $seedData['amount'] = $perBagsAmount[$seedCategory][1];
                                }
                                array_push($bagsList, $seedData);
                                $totalSeed -= $perBagsAmount[$seedCategory][1];
                            }
                        }
                    }
                }
            }
            
            // grouping Bags
            $labelsList = [];
            $labelIndex = 0;
            $bagNo = 0;
            foreach ($bagsList as $bag) {
                if ($labelIndex > 0 && $labelsList[$labelIndex -1]['tree_category'] == $bag['tree_category']) {
                    // get max cap
                    if ($labelsList[$labelIndex -1]['tree_category'] != 'MPTS') {
                        $cap = $perBagsAmount[$bag['tree_category']];
                    } else {
                        if (in_array($labelsList[$labelIndex -1]['tree_name'][count($labelsList[$labelIndex -1]['tree_name']) - 1], $total8)) {
                            $cap = $perBagsAmount[$bag['tree_category']][0];
                        } else {
                            $cap = $perBagsAmount[$bag['tree_category']][1];
                        }
                    }
                    $capLeft = $cap - $labelsList[$labelIndex -1]['total_amount'];
                    // check cap
                    if ($capLeft > 0) {
                        if ($bag['amount'] < $capLeft && $bag['amount'] > 0) {
                            // if same tree_name
                            if ($bag['tree_name'] == $labelsList[$labelIndex -1]['tree_name'][count($labelsList[$labelIndex -1]['tree_name']) - 1]) {
                                $labelsList[$labelIndex -1]['amount'][count($labelsList[$labelIndex -1]['amount']) - 1] += $bag['amount'];
                            } else {
                                // push new tree_name
                                array_push($labelsList[$labelIndex -1]['tree_name'], $bag['tree_name']);
                                array_push($labelsList[$labelIndex -1]['amount'], $bag['amount']);
                            }
                            $labelsList[$labelIndex -1]['total_amount'] += $bag['amount'];
                        } else {
                            // if same tree_name
                            if ($bag['tree_name'] == $labelsList[$labelIndex -1]['tree_name'][count($labelsList[$labelIndex -1]['tree_name']) - 1]) { 
                                $labelsList[$labelIndex -1]['amount'][count($labelsList[$labelIndex -1]['amount']) - 1] += $capLeft;
                            } else {
                                // push new tree_name
                                array_push($labelsList[$labelIndex -1]['tree_name'], $bag['tree_name']);
                                array_push($labelsList[$labelIndex -1]['amount'], $capLeft);
                            }
                            $labelsList[$labelIndex -1]['total_amount'] += $capLeft;
                            $totalLeft = $bag['amount'] - $capLeft;
                            
                            if (($totalLeft) > 0) {
                                $labelsList[$labelIndex]['bag_no'] = $labelIndex + 1;
                                $labelsList[$labelIndex]['capacity_left'] = $totalLeft;
                                $labelsList[$labelIndex]['tree_category'] = $bag['tree_category'];
                                $labelsList[$labelIndex]['tree_name'] = [$bag['tree_name']];
                                $labelsList[$labelIndex]['amount'] = [$totalLeft];
                                $labelsList[$labelIndex]['total_amount'] = $totalLeft;
                                $labelIndex += 1;
                            }
                        }
                    } else {
                        $totalLeft = $bag['amount'];
                        $labelsList[$labelIndex]['bag_no'] = $labelIndex + 1;
                        $labelsList[$labelIndex]['capacity_left'] = $bag['amount_reminder'];
                        $labelsList[$labelIndex]['tree_category'] = $bag['tree_category'];
                        $labelsList[$labelIndex]['tree_name'] = [$bag['tree_name']];
                        $labelsList[$labelIndex]['amount'] = [$totalLeft];
                        $labelsList[$labelIndex]['total_amount'] = $totalLeft;
                        $labelIndex += 1;
                    }
                } else {
                    $labelsList[$labelIndex]['bag_no'] = $labelIndex + 1;
                    $labelsList[$labelIndex]['capacity_left'] = $bag['amount_reminder'];
                    $labelsList[$labelIndex]['tree_category'] = $bag['tree_category'];
                    $labelsList[$labelIndex]['tree_name'] = [$bag['tree_name']];
                    $labelsList[$labelIndex]['amount'] = [$bag['amount']];
                    $labelsList[$labelIndex]['total_amount'] = $bag['amount'];
                    $labelIndex += 1;
                }
            }
            // update bag number & other detail in labels
            foreach ($labelsList as $labelsListIndex => $label) {
                $labelsList[$labelsListIndex]['bag_no'] = $labelsList[$labelsListIndex]['bag_no'] . '/' . $labelIndex;
                $labelsList[$labelsListIndex]['bag_code'] = $labelsList[$labelsListIndex]['bag_no'] . '-' . $GetPHUDetail->lahan_no;
                $labelsList[$labelsListIndex]['qr_code'] = $this->generateqrcode(($labelsList[$labelsListIndex]['bag_no'] . '-' . $GetPHUDetail->lahan_no));
                $labelsList[$labelsListIndex]['pic_name'] = $GetPHUDetail->pic_name;
                $labelsList[$labelsListIndex]['lahan_no'] = $GetPHUDetail->lahan_no;
                $labelsList[$labelsListIndex]['date'] = date("d/m/Y", strtotime($GetPHUDetail->distribution_time));
                $labelsList[$labelsListIndex]['location'] = $GetPHUDetail->address;
            }
            return [
                'lubangTanamDetail' => $GetPHUDetail,
                'listLabel' => $labelsList];
        }else{
            $rslt =  $this->ResultReturn(404, 'doesnt match data', 'No Lahan Tidak ada dalam SOSTAM');
            return response()->json($rslt, 404);
        }
    } 
    
    // Create Bags Seeds
    private function generateSeedlingLabels($ph_form_no) {
        // GET Planting Hole Data
        $GetPHDetail = PlantingHoleSurviellance::
                leftjoin('lahans', 'lahans.lahan_no', '=', 'planting_hole_surviellance.lahan_no')
                ->leftjoin('farmers', 'farmers.farmer_no', '=', 'lahans.farmer_no')
                ->leftjoin('field_facilitators', 'field_facilitators.ff_no', '=', 'planting_hole_surviellance.user_id')
                ->select('planting_hole_surviellance.id',
                        'planting_hole_surviellance.lahan_no',
                        'planting_hole_surviellance.ph_form_no',
                        'planting_hole_surviellance.planting_year',
                        'planting_hole_surviellance.latitude', 
                        'planting_hole_surviellance.longitude',
                        'planting_hole_surviellance.farmer_signature',
                        'planting_hole_surviellance.gambar1',
                        'planting_hole_surviellance.gambar2',
                        'planting_hole_surviellance.is_validate',
                        'planting_hole_surviellance.validate_by',
                        'planting_hole_surviellance.total_holes',
                        'planting_hole_surviellance.is_dell', 
                        'planting_hole_surviellance.created_at', 
                        'farmers.name as nama_petani', 
                        'field_facilitators.name as nama_ff', 
                        'planting_hole_surviellance.user_id')
                ->where('planting_hole_surviellance.ph_form_no','=',$ph_form_no)
                ->first();

        if($GetPHDetail){
            // GET sostam data
            $GetSosialisasiDetail = DB::table('planting_socializations')
                    ->select('planting_socializations.id',
                            'planting_socializations.no_lahan',
                            'planting_socializations.farmer_no',
                            'planting_socializations.form_no',
                            'planting_socializations.planting_year',
                            'planting_socializations.no_document', 
                            'planting_socializations.ff_no',
                            'planting_socializations.validation',
                            'planting_socializations.validate_by',
                            'planting_socializations.is_dell', 
                            'planting_socializations.created_at')
                    ->where('planting_socializations.no_lahan','=',$GetPHDetail->lahan_no)
                    ->first();
            
            // GET FF data
            $field_facilitators = DB::table('field_facilitators')->where('ff_no','=',$GetPHDetail->user_id)->first();
            // GET lahan data
            $Lahan = DB::table('lahans')->where('lahan_no','=',$GetPHDetail->lahan_no)->first();
            
            if ($GetSosialisasiDetail && $field_facilitators && $Lahan) {
                // Get Farmer data
                $Farmer = DB::table('farmers')->where('farmer_no','=',$GetSosialisasiDetail->farmer_no)->first();
                // Get desa data
                $Desas = DB::table('desas')->where('kode_desa','=',$Farmer->village)->first();
                // Get distribution Period
                $planting_period = DB::table('planting_period')->where('form_no','=',$GetSosialisasiDetail->form_no)->first();
        
        
                if ($Farmer && $Desas && $planting_period) {    
                    // GET Seedling List
                    $seedCategories = ['KAYU', 'MPTS', 'CROPS'];
                    $seedCategoriesLoopCount = 0;
                    $perBagsAmount = [
                        'KAYU' => 10,
                        'MPTS' => [8,6],
                        'CROPS' => 10
                    ];
                    $total8 = ['PETAI', 'MANGGA', 'NANGKA', 'SAWO', 'RAMBUTAN'];
                    $seedsList = [];
                    // GET per category seedling detail
                    foreach($seedCategories as $seedCategory) {
                        $seedsList[$seedCategory] = PlantingHoleSurviellanceDetail::
                            join('tree_locations', 'tree_locations.tree_code', '=', 'planting_hole_details.tree_code')
                            ->select('planting_hole_details.id',
                                    'planting_hole_details.ph_form_no',
                                    'planting_hole_details.tree_code',
                                    'planting_hole_details.amount',
                                    'tree_locations.tree_name',
                                    'tree_locations.category')
                            ->where([
                                ['planting_hole_details.ph_form_no','=',$GetPHDetail->ph_form_no],
                                'tree_locations.mu_no' => $field_facilitators->mu_no,
                                'tree_locations.category' => $seedCategory
                            ])
                            ->orderBy('planting_hole_details.amount', 'DESC')
                            ->get();
                        $seedCategoriesLoopCount += 1;
                    }
                    
                    // GET BAGS amount
                    $bagsList = [];
                    foreach ($seedCategories as $seedCategory) {
                        foreach ($seedsList[$seedCategory] as $seedling) {
                            $totalSeed = $seedling->amount;
                            $reminder = 0;
                            if ($seedCategory != 'MPTS') {
                                $totalBagPerSeed = ceil($seedling->amount / $perBagsAmount[$seedCategory]);
                                for ($i = 1;$i <= $totalBagPerSeed;$i++) {
                                    $amountReminder = $totalSeed - $perBagsAmount[$seedCategory];
                                    $seedData = [
                                        'amount_reminder' => $amountReminder,
                                        'tree_name' => $seedling->tree_name,
                                        'tree_category' => $seedling->category
                                    ];
                                    if ($amountReminder < 0) {
                                        $seedData['amount'] = $perBagsAmount[$seedCategory] + $amountReminder;
                                        $reminder = $amountReminder;
                                    } else {
                                        $seedData['amount'] = $perBagsAmount[$seedCategory];
                                    }
                                    array_push($bagsList, $seedData);
                                    $totalSeed -= $perBagsAmount[$seedCategory];
                                }
                            } else {
                                if (in_array($seedling->tree_name, $total8)) {
                                    $totalBagPerSeed = ceil($seedling->amount / $perBagsAmount[$seedCategory][0]);
                                    for ($i = 1;$i <= $totalBagPerSeed;$i++) {
                                        $amountReminder = $totalSeed - $perBagsAmount[$seedCategory][0];
                                        $seedData = [
                                            'amount_reminder' => $amountReminder,
                                            'tree_name' => $seedling->tree_name,
                                            'tree_category' => $seedling->category
                                        ];
                                        if ($amountReminder < 0) {
                                            $seedData['amount'] = $perBagsAmount[$seedCategory][0] + $amountReminder;
                                            $reminder = $amountReminder;
                                        } else {
                                            $seedData['amount'] = $perBagsAmount[$seedCategory][0];
                                        }
                                        array_push($bagsList, $seedData);
                                        $totalSeed -= $perBagsAmount[$seedCategory][0];
                                    }
                                } else {
                                    $totalBagPerSeed = ceil($seedling->amount / $perBagsAmount[$seedCategory][1]);
                                    for ($i = 1;$i <= $totalBagPerSeed;$i++) {
                                        $amountReminder = $totalSeed - $perBagsAmount[$seedCategory][1];
                                        $seedData = [
                                            'amount_reminder' => $amountReminder,
                                            'tree_name' => $seedling->tree_name,
                                            'tree_category' => $seedling->category
                                        ];
                                        if ($amountReminder < 0) {
                                            $seedData['amount'] = $perBagsAmount[$seedCategory][1] + $amountReminder;
                                            $reminder = $amountReminder;
                                        } else {
                                            $seedData['amount'] = $perBagsAmount[$seedCategory][1];
                                        }
                                        array_push($bagsList, $seedData);
                                        $totalSeed -= $perBagsAmount[$seedCategory][1];
                                    }
                                }
                            }
                        }
                    }
                    
                    // grouping Bags
                    $labelsList = [];
                    $labelIndex = 0;
                    $bagNo = 0;
                    foreach ($bagsList as $bag) {
                        if ($labelIndex > 0 && $labelsList[$labelIndex -1]['tree_category'] == $bag['tree_category']) {
                            // get max cap
                            if ($labelsList[$labelIndex -1]['tree_category'] != 'MPTS') {
                                $cap = $perBagsAmount[$bag['tree_category']];
                            } else {
                                if (in_array($labelsList[$labelIndex -1]['tree_name'][count($labelsList[$labelIndex -1]['tree_name']) - 1], $total8)) {
                                    $cap = $perBagsAmount[$bag['tree_category']][0];
                                } else {
                                    $cap = $perBagsAmount[$bag['tree_category']][1];
                                }
                            }
                            $capLeft = $cap - $labelsList[$labelIndex -1]['total_amount'];
                            // check cap
                            if ($capLeft > 0) {
                                if ($bag['amount'] < $capLeft && $bag['amount'] > 0) {
                                    // if same tree_name
                                    if ($bag['tree_name'] == $labelsList[$labelIndex -1]['tree_name'][count($labelsList[$labelIndex -1]['tree_name']) - 1]) {
                                        $labelsList[$labelIndex -1]['amount'][count($labelsList[$labelIndex -1]['amount']) - 1] += $bag['amount'];
                                    } else {
                                        // push new tree_name
                                        array_push($labelsList[$labelIndex -1]['tree_name'], $bag['tree_name']);
                                        array_push($labelsList[$labelIndex -1]['amount'], $bag['amount']);
                                    }
                                    $labelsList[$labelIndex -1]['total_amount'] += $bag['amount'];
                                } else {
                                    // if same tree_name
                                    if ($bag['tree_name'] == $labelsList[$labelIndex -1]['tree_name'][count($labelsList[$labelIndex -1]['tree_name']) - 1]) { 
                                        $labelsList[$labelIndex -1]['amount'][count($labelsList[$labelIndex -1]['amount']) - 1] += $capLeft;
                                    } else {
                                        // push new tree_name
                                        array_push($labelsList[$labelIndex -1]['tree_name'], $bag['tree_name']);
                                        array_push($labelsList[$labelIndex -1]['amount'], $capLeft);
                                    }
                                    $labelsList[$labelIndex -1]['total_amount'] += $capLeft;
                                    $totalLeft = $bag['amount'] - $capLeft;
                                    
                                    if (($totalLeft) > 0) {
                                        $labelsList[$labelIndex]['bag_no'] = $labelIndex + 1;
                                        $labelsList[$labelIndex]['capacity_left'] = $totalLeft;
                                        $labelsList[$labelIndex]['tree_category'] = $bag['tree_category'];
                                        $labelsList[$labelIndex]['tree_name'] = [$bag['tree_name']];
                                        $labelsList[$labelIndex]['amount'] = [$totalLeft];
                                        $labelsList[$labelIndex]['total_amount'] = $totalLeft;
                                        $labelIndex += 1;
                                    }
                                }
                            } else {
                                $totalLeft = $bag['amount'];
                                $labelsList[$labelIndex]['bag_no'] = $labelIndex + 1;
                                $labelsList[$labelIndex]['capacity_left'] = $bag['amount_reminder'];
                                $labelsList[$labelIndex]['tree_category'] = $bag['tree_category'];
                                $labelsList[$labelIndex]['tree_name'] = [$bag['tree_name']];
                                $labelsList[$labelIndex]['amount'] = [$totalLeft];
                                $labelsList[$labelIndex]['total_amount'] = $totalLeft;
                                $labelIndex += 1;
                            }
                        } else {
                            $labelsList[$labelIndex]['bag_no'] = $labelIndex + 1;
                            $labelsList[$labelIndex]['capacity_left'] = $bag['amount_reminder'];
                            $labelsList[$labelIndex]['tree_category'] = $bag['tree_category'];
                            $labelsList[$labelIndex]['tree_name'] = [$bag['tree_name']];
                            $labelsList[$labelIndex]['amount'] = [$bag['amount']];
                            $labelsList[$labelIndex]['total_amount'] = $bag['amount'];
                            $labelIndex += 1;
                        }
                    }
                    // update bag number & other detail in labels
                    foreach ($labelsList as $labelsListIndex => $label) {
                        $labelsList[$labelsListIndex]['bag_no'] = $labelsList[$labelsListIndex]['bag_no'] . '/' . $labelIndex;
                        $labelsList[$labelsListIndex]['bag_code'] = $labelsList[$labelsListIndex]['bag_no'] . '-' . $Lahan->lahan_no;
                        $labelsList[$labelsListIndex]['qr_code'] = $this->generateqrcode(($labelsList[$labelsListIndex]['bag_no'] . '-' . $Lahan->lahan_no));
                        $labelsList[$labelsListIndex]['farmer_name'] = $Farmer->name;
                        $labelsList[$labelsListIndex]['ff_name'] = $field_facilitators->name;
                        $labelsList[$labelsListIndex]['lahan_no'] = $Lahan->lahan_no;
                        $labelsList[$labelsListIndex]['date'] = date("d/m/Y", strtotime($planting_period->distribution_time));
                        $labelsList[$labelsListIndex]['location'] = $planting_period->distribution_location;
                    }
                    
                    // $rslt =  $this->ResultReturn(200, 'match data', ['label' => $labelsList, 'bags' => $bagsList]);
                    // return response()->json($rslt, 200);
                    
                    return [
                        'lubangTanamDetail' => $GetPHDetail,
                        'listLabel' => $labelsList
                    ];
                } else{
                    $rslt =  $this->ResultReturn(404, 'doesnt match data', 'No Lahan Tidak ada dalam SOSTAM');
                    return response()->json($rslt, 404);
                }     
            } else{
                $rslt =  $this->ResultReturn(404, 'doesnt match data', 'No Lahan Tidak ada dalam SOSTAM');
                return response()->json($rslt, 404);
            }
        } else{
            $rslt =  $this->ResultReturn(404, 'doesnt match data', 'No Lahan Tidak ada dalam SOSTAM');
            return response()->json($rslt, 404);
        }
    }
    
    // API For Nursery
    public function NurseryGetPlantingHole(Request $request) {
        // validation
        $validator = Validator::make($request->all(), [
            'program_year' => 'required',
            'limit' => 'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        } else {
            $py = $request->program_year;
            $limit = $request->limit;
            $dt = $request->distribution_time;
            $san = $request->status_approval_nursery;
            $mu_no = $request->mu_no;

        }
        
        $ph_ff_no = DB::table('planting_hole_surviellance')->where('planting_year', $py)->groupBy('user_id')->pluck('user_id');
        $form_no = DB::table('planting_socializations')->whereIn('ff_no', $ph_ff_no)->groupBy('ff_no')->pluck('form_no');
        $datas = DB::table('planting_socializations')
            ->select(
                DB::raw('DATE(planting_period.distribution_time) as distribution_time'), 
                'planting_period.distribution_location',
                'planting_period.distribution_coordinates',
                'planting_period.rec_armada as armada',
                'field_facilitators.name as ff_name',
                'field_facilitators.ff_no'
            )
            ->join('planting_period', 'planting_period.form_no', 'planting_socializations.form_no')
            ->join('field_facilitators', 'field_facilitators.ff_no', 'planting_socializations.ff_no')
            ->whereIn('planting_socializations.form_no', $form_no);
        
        if ($dt) {
            $datas = $datas->whereDate('planting_period.distribution_time', $dt);
        }
        if ($san) {
            $sanStatus = $san == 'Menunggu' ? 0 : ($san == 'Disetujui' ? 2 : 1);
            $datas = $datas->where('planting_socializations.status_approval_nursery', $sanStatus);
        }
            
        $datas = $datas
            ->groupBy('field_facilitators.ff_no')
            ->orderBy('planting_socializations.created_at')
            ->paginate($limit);

        
        // get tree codes
        $wood_seeds = DB::table('trees')->where([
            'tree_category' => 'Pohon_Kayu'
        ])->pluck('tree_code')->toArray();
        $mpts_seeds = DB::table('trees')->where([
            'tree_category' => 'Pohon_Buah'
        ])->pluck('tree_code')->toArray();
        foreach ($datas as $data) {
            $data->program_year = $py;
            
            $penlubQuery = DB::table('planting_hole_surviellance')
                ->where([
                    'user_id' => $data->ff_no,
                    'planting_year' => $py    
                ]);
            $data->status_approval_nursery = $penlubQuery->first()->status_approval_nursery ?? 0;
            $data->holes = $penlubQuery->select(DB::raw('SUM(total_holes) as total'))->groupBy('user_id')->first()->total ?? 0;
            $data->holes_standard = $penlubQuery->select(DB::raw('SUM(counter_hole_standard) as total'))->groupBy('user_id')->first()->total ?? 0;
            
            $data->mu_no = DB::table('ff_working_areas')->where([
                    'ff_working_areas.ff_no' => $data->ff_no,
                    ['ff_working_areas.program_year', 'LIKE', "%$py%"]
                ])->first()->mu_no ?? '';
            $data->mu_name = DB::table('managementunits')
                ->where([
                    'mu_no' => $data->mu_no
                ])->first()->name ?? '';
                
            $form_no_ff = DB::table('planting_socializations')
                ->where([
                    'ff_no' => $data->ff_no,
                    'planting_year' => $py
                ])->pluck('form_no')->toArray();
            $form_no_ff_ph = DB::table('planting_hole_surviellance')
                ->where([
                    'user_id' => $data->ff_no,
                    'planting_year' => $py
                ])->pluck('ph_form_no')->toArray();
            if (count($form_no_ff_ph) > 0) {
                $data->progress = round(count($form_no_ff_ph) / count($form_no_ff) * 100);
            } else $data->progress = 0;
            $data->total_wood = DB::table('planting_hole_details')
                ->whereIn('ph_form_no', $form_no_ff_ph)
                ->whereIn('tree_code', $wood_seeds)
                ->sum('amount');
            $data->total_mpts = DB::table('planting_hole_details')
                ->whereIn('ph_form_no', $form_no_ff_ph)
                ->whereIn('tree_code', $mpts_seeds)
                ->sum('amount');
            
            $data->total_seeds = (int)$data->total_wood + (int)$data->total_mpts;
        }
        return response()->json($datas, 200);
    }
    public function NurseryDetailPlantingHole(Request $req) {
        $validator = Validator::make($req->all(), [
            'program_year' => 'required',
            'ff_no' => 'required|exists:field_facilitators,ff_no'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }
        $py = $req->program_year;
        $ff_no = $req->ff_no;
        $form_no = DB::table('planting_socializations')
            ->where([
                'planting_year' => $py,
                'ff_no' => $ff_no
            ])->pluck('form_no')->toArray();
        $phQuery = DB::table('planting_hole_surviellance')
            ->where([
                'planting_year' => $py,
                'user_id' => $ff_no
            ]);
        $ph_form_no = $phQuery->pluck('ph_form_no')->toArray();
        
        $datas = DB::table('planting_period')
            ->select('distribution_time', 'distribution_coordinates', 'rec_armada as armada', 'distribution_location')
            ->whereIn('form_no', $form_no)->first();
        $sostam = DB::table('planting_socializations')->whereIn('form_no', $form_no)
            ->orderBy('created_at')->first();
        
        $datas->ff_no = $ff_no;
        $datas->ff_name = DB::table('field_facilitators')->where('ff_no', $ff_no)->first()->name ?? '';
        
        $datas->program_year = $py;
        
        $datas->mu_name = DB::table('managementunits')
            ->select('managementunits.name as mu_name')
            ->join('ff_working_areas', 'ff_working_areas.mu_no', 'managementunits.mu_no')
            ->where([
                'ff_working_areas.ff_no' => $ff_no,
                ['ff_working_areas.program_year', 'LIKE', "%$py%"]
            ])->first()->mu_name ?? '';
        
        $datas->status_approval_nursery = $phQuery->first()->status_approval_nursery ?? 0;
        $datas->reject_description = $phQuery->first()->reject_description ?? '';
        $datas->holes = $phQuery->select(DB::raw('SUM(total_holes) as total'))->groupBy('user_id')->first()->total ?? 0;
        $datas->holes_standard = $phQuery->select(DB::raw('SUM(counter_hole_standard) as total'))->groupBy('user_id')->first()->total ?? 0;
        
        $datas->seeds_detail = DB::table('planting_hole_details')
            ->select('trees.tree_code', 'trees.tree_name', 'trees.tree_category', 'planting_hole_details.amount')
            ->join('trees', 'trees.tree_code', 'planting_hole_details.tree_code')
            ->whereIn('ph_form_no', $ph_form_no)
            ->get();
        if (count($ph_form_no) > 0) {
            $datas->progress = round(count($ph_form_no) / count($form_no) * 100);
        } else $datas->progress = 0;
            
        return response()->json($datas);
    }
    public function GEKO_getPrintLabelList(Request $req) {
        if ($req->program_year) {
            $py = preg_replace("/[^0-9]/", "", $req->program_year);
        } else $py = '2023';
        $paginate = DB::table('planting_hole_surviellance')
            ->select(
                'planting_hole_surviellance.ph_form_no',
                'planting_hole_surviellance.lahan_no',
                'planting_hole_surviellance.is_checked',
                'planting_hole_surviellance.reject_description',
                DB::raw('(planting_hole_surviellance.pohon_kayu + planting_hole_surviellance.pohon_mpts + planting_hole_surviellance.tanaman_bawah) as seeds_total'),
                DB::raw('((planting_hole_surviellance.counter_hole_standard) * 5) as fertilizer_total'),
                'field_facilitators.name as ff_name',
                'field_facilitators.ff_no',
                'farmers.name as farmer_name',
                'farmers.farmer_no'
            )
            ->join('field_facilitators', 'field_facilitators.ff_no', 'planting_hole_surviellance.user_id')
            ->join('lahans', 'lahans.lahan_no', 'planting_hole_surviellance.lahan_no')
            ->join('farmers', 'farmers.farmer_no', 'lahans.farmer_no')
            ->where([
                'planting_hole_surviellance.is_dell' => 0,
                'planting_hole_surviellance.is_validate' => 1,
                'planting_hole_surviellance.planting_year' => $py,
                // 'planting_hole_surviellance.status_approval_nursery' => 2
            ]);
        // search
        if ($req->search) {
            $paginate = $paginate->where('planting_hole_surviellance.lahan_no', 'LIKE', "%$req->search%")
                ->orWhere('farmers.name', 'LIKE', "%$req->search%")
                ->orWhere('field_facilitators.name', 'LIKE', "%$req->search%");
        }
        
        // filter
        if ($req->mu_name) {
            $filter_ff_no = $paginate->pluck('field_facilitators.ff_no')->toArray();
            $filter_by_mu_ff = DB::table('ff_working_areas')
                ->join('managementunits', 'managementunits.mu_no', 'ff_working_areas.mu_no')
                ->whereIn('ff_working_areas.ff_no', $filter_ff_no)
                ->where('managementunits.name', 'LIKE', "%$req->mu_name%")->pluck('ff_working_areas.ff_no');
            $paginate = $paginate->whereIn('field_facilitators.ff_no', $filter_by_mu_ff);
        }
        if ($req->farmer_name) {
            $paginate = $paginate->where('farmers.name', 'LIKE', "%$req->farmer_name%");
        }if ($req->ff_name) {
            $paginate = $paginate->where('field_facilitators.name', 'LIKE', "%$req->ff_name%");
        }if ($req->is_checked === '0' || $req->is_checked === '1') {
            $paginate = $paginate->where('planting_hole_surviellance.is_checked', '=', $req->is_checked);
        }if ($req->distribution_date) {
            $filter_lahan_no = DB::table('planting_period')
                ->select(DB::raw("REPLACE(form_no, 'SO-$py-', '10_') as lahan_no"))
                ->whereDate('distribution_time', $req->distribution_date)->pluck('lahan_no');
            $paginate = $paginate->whereIn('planting_hole_surviellance.lahan_no', $filter_lahan_no);
        }
        
        // order
        if ($req->order && $req->sort) {
            if ($req->order == 'distribution_date') {
                $pluck_lahan_no = $paginate->pluck('planting_hole_surviellance.lahan_no');
                $sort_lahan_no = DB::table('planting_period')
                    ->select(
                        DB::raw("REPLACE(form_no, 'SO-$py-', '10_') as lahan_no")
                    )
                    ->where('form_no', 'LIKE', "SO-$py-%")
                    ->orderBy('distribution_time', $req->sort)->pluck('lahan_no')->toArray();
                $paginate = $paginate->orderByRaw("FIELD (planting_hole_surviellance.lahan_no, '" . implode("', '", $sort_lahan_no) . "')");

            } else {
                $paginate = $paginate->orderBy($req->order, $req->sort);
            }
        }
        
        $paginate = $paginate->paginate($req->limit);
        
        foreach ($paginate as $data) {
            $sostam = DB::table('planting_socializations')->where([
                'ff_no' => $data->ff_no,
                'planting_year' => $py,
            ])->first();
            if ($sostam) {
                $data->distribution_date = DB::table('planting_period')
                    ->select(DB::raw('DATE(distribution_time) as distribution_date'))
                    ->where('form_no', $sostam->form_no)->first()->distribution_date;
            }
        }
        
        $rslt = [
            'data' => $paginate->items(),
            'total' => $paginate->total(),
            'totalPage' => $paginate->lastPage()
        ];
        return response()->json($rslt, 200);
    }
}
