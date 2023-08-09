<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use App\User;
use App\Lahan;
use App\Monitoring;
use App\MonitoringDetail;
use App\Monitoring2;
use App\Monitoring2Detail;

class MonitoringController extends Controller
{
    // MONITORING 1 {
    /**
     * @SWG\Get(
     *   path="/api/GetMonitoringFF",
     *   tags={"Monitoring"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Monitoring FF",
     *   operationId="GetMonitoringFF",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="ff_no",in="query", required=true, type="string"),
     * )
     */
    public function GetMonitoringFF(Request $request)
    {
        $ff_no = $request->ff_no;
        $getpy = $request->planting_year;
        if($getpy){$py='%'.$getpy.'%';}
        else{$py='%%';}
        
        $datas = Monitoring::
            select('monitorings.id','monitorings.lahan_no', 'monitorings.farmer_no',
            'monitorings.monitoring_no as monitoring_no','monitorings.planting_year', 'monitorings.type_sppt', 'monitorings.planting_date',
            'monitorings.is_validate','monitorings.validate_by','monitorings.lahan_condition',
            'monitorings.qty_kayu','monitorings.qty_mpts','monitorings.qty_crops','monitorings.qty_std',
            'monitorings.is_dell', 'monitorings.created_at',  'monitorings.user_id',                    
            'monitorings.gambar1','monitorings.gambar2','monitorings.gambar3',
            'farmers.name as nama_petani', 'field_facilitators.name as nama_ff')
            ->leftjoin('farmers', 'farmers.farmer_no', '=', 'monitorings.farmer_no')
            ->leftjoin('field_facilitators', 'field_facilitators.ff_no', '=', 'monitorings.user_id')
            ->where('monitorings.is_dell','=',0)
            ->where('monitorings.user_id','=',$ff_no)
            ->where('monitorings.planting_year', 'like', $py)
            ->get();
            
        if($datas){
            $monDetails = [];
            foreach($datas as $mIndex => $monitoring){
                $getDetailMonitoring =  MonitoringDetail::
                select('id','monitoring_no', 'tree_code', 'qty', 'status',
                         'condition', 'planting_date','created_at','updated_at')
                ->where([
                    'monitoring_no' => $monitoring->monitoring_no
                ])->get();
                array_push($monDetails, ...$getDetailMonitoring);
                
                if($monitoring->is_validate > 1){
                        $monitoring->is_validate = 1;
                    }
                
                $datas[$mIndex]->monitoring_no = (string)$monitoring->monitoring_no;
            }
            
            $data = [
                'data' => $datas,
                'monitoring_details' => $monDetails
            ];

            $rslt =  $this->ResultReturn(200, 'success', $data);
            return response()->json($rslt, 200);
        }
    }

    // Get Monitoring All with Pagination
    public function GetMonitoringAdmin(Request $req) {
        $validator = Validator::make($req->all(), [
            'program_year' => 'required',
            'per_page' => 'required',
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        } else {
            $py = $req->program_year;
            $pp = $req->per_page;
            
            // old default
            $typegetdata = $req->typegetdata;
            $ff = $req->ff;
            $getmu = $req->mu;
            $getta = $req->ta;
            $getvillage = $req->village;
            $getpy = $req->planting_year;
            if($getmu){$mu='%'.$getmu.'%';}
            else{$mu='%%';}
            if($getta){$ta='%'.$getta.'%';}
            else{$ta='%%';}
            if($getvillage){$village='%'.$getvillage.'%';}
            else{$village='%%';}
        }
        
        $searchColumn = [
            'mu_name' => 'managementunits.name',
            'nama_ff' => 'field_facilitators.name',
            'nama_petani' => 'farmers.name',
            'lahan_no' => 'monitorings.lahan_no',
            'is_validate' => 'monitorings.is_validate',
        ];
        
        // mandatory query
        $datas = DB::table('monitorings')
            ->join('farmers', 'farmers.farmer_no', '=', 'monitorings.farmer_no')
            ->join('field_facilitators', 'field_facilitators.ff_no', '=', 'monitorings.user_id')
            ->join('managementunits', 'managementunits.mu_no', '=', 'farmers.mu_no')
            ->select(
                'monitorings.id',
                'monitorings.lahan_no',
                'monitorings.farmer_no',
                'monitorings.monitoring_no',
                'monitorings.planting_year',
                'monitorings.planting_date',
                'monitorings.type_sppt',
                'monitorings.is_validate',
                'monitorings.validate_by',
                'monitorings.lahan_condition',
                'monitorings.qty_kayu', 
                'monitorings.qty_mpts', 
                'monitorings.qty_crops',
                'monitorings.qty_std',
                'monitorings.is_dell', 
                'monitorings.created_at', 
                'farmers.name as nama_petani', 
                'monitorings.user_id as ff_no',
                'field_facilitators.name as nama_ff', 
                'managementunits.mu_no as mu_no', 
                'managementunits.name as mu_name'
            )
            ->where([
                ['monitorings.monitoring_no', 'LIKE', 'MO1-'.$py.'%'],
                'monitorings.is_dell' => 0,
                [$searchColumn[$req->search_column], 'LIKE', '%'.$req->search_value.'%'],
                ['farmers.mu_no','like',$mu],
                ['farmers.target_area','like',$ta],
                ['farmers.village','like',$village]
            ]);
            
        if ($ff) {
            $ffdecode = explode(",",$ff);
            $datas = $datas->whereIn('monitorings.user_id', $ffdecode);
        }
        
        if ($req->sortBy) {
            $sortBy = explode(',', $req->sortBy);
            $sortDesc = explode(',', $req->sortDesc);
            foreach ($sortBy as $sortIndex => $sort) {
                $datas = $datas->orderBy($sort, ($sortDesc[$sortIndex] == 'true' ? 'DESC' : 'ASC'));
            }
        }
            
        if ((int)$pp == -1) {
            $datas = $datas->paginate($datas->count());
        } else {
            $datas = $datas->paginate((int)$pp);
        }
        
        foreach ($datas as $data) {
            $KAYU = DB::table('tree_locations')->where(['category' => 'KAYU', 'mu_no' => $data->mu_no])->pluck('tree_code');
            $MPTS = DB::table('tree_locations')->where(['category' => 'MPTS', 'mu_no' => $data->mu_no])->pluck('tree_code');
            $data->kayu_hidup = DB::table('monitoring_details')->where(['monitoring_no' => $data->monitoring_no, 'status' => 'sudah_ditanam', 'condition' => 'hidup'])->whereIn('tree_code', $KAYU)->sum('qty');
            $data->kayu_mati = DB::table('monitoring_details')->where(['monitoring_no' => $data->monitoring_no, 'condition' => 'mati'])->whereIn('tree_code', $KAYU)->sum('qty');
            $data->kayu_hilang = DB::table('monitoring_details')->where(['monitoring_no' => $data->monitoring_no, 'condition' => 'hilang'])->whereIn('tree_code', $KAYU)->sum('qty');
            $data->mpts_hidup = DB::table('monitoring_details')->where(['monitoring_no' => $data->monitoring_no, 'status' => 'sudah_ditanam', 'condition' => 'hidup'])->whereIn('tree_code', $MPTS)->sum('qty');
            $data->mpts_mati = DB::table('monitoring_details')->where(['monitoring_no' => $data->monitoring_no, 'condition' => 'mati'])->whereIn('tree_code', $MPTS)->sum('qty');
            $data->mpts_hilang = DB::table('monitoring_details')->where(['monitoring_no' => $data->monitoring_no, 'condition' => 'hilang'])->whereIn('tree_code', $MPTS)->sum('qty');
        }
            
        $rslt =  $this->ResultReturn(200, 'success', $datas);
        return response()->json($rslt, 200);
    }

    /**
     * @SWG\Get(
     *   path="/api/GetMonitoringDetail",
     *   tags={"Monitoring"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Monitoring Detail",
     *   operationId="GetMonitoringDetail",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="monitoring_no",in="query", required=true, type="string"),
     * )
     */
    public function GetMonitoringDetail(Request $request){
        $monitoring_no = $request->monitoring_no;
        try{
           
                $GetMonitoringDetail = DB::table('monitorings')
                    ->select('monitorings.id','monitorings.lahan_no','monitorings.farmer_no',
                    'monitorings.monitoring_no','monitorings.planting_year','monitorings.planting_date', 'monitorings.type_sppt',
                    'monitorings.is_validate','monitorings.validate_by','monitorings.lahan_condition',
                    'monitorings.qty_kayu','monitorings.qty_mpts','monitorings.qty_crops','monitorings.qty_std',
                    'monitorings.gambar1','monitorings.gambar2','monitorings.gambar3',
                    'monitorings.is_dell', 'monitorings.created_at',  'monitorings.user_id as ff_no',
                    'farmers.name as nama_petani', 'field_facilitators.name as nama_ff', 'monitorings.user_id')
                    ->leftjoin('farmers', 'farmers.farmer_no', '=', 'monitorings.farmer_no')
                    ->leftjoin('field_facilitators', 'field_facilitators.ff_no', '=', 'monitorings.user_id')
                    ->where('monitorings.monitoring_no','=',$monitoring_no)
                    ->first();


                if($GetMonitoringDetail){ 
                    $GetMonitoringDetailList = DB::table('monitoring_details')
                        ->select('monitoring_details.id',
                        'monitoring_details.monitoring_no','monitoring_details.tree_code','trees.tree_category',
                        'monitoring_details.qty','monitoring_details.qty as amount', 'monitoring_details.status', 'monitoring_details.condition', 
                        'monitoring_details.planting_date','monitoring_details.created_at',
                        'trees.tree_name as tree_name')
                        ->leftjoin('trees', 'trees.tree_code', '=', 'monitoring_details.tree_code')
                        ->where('monitoring_details.monitoring_no','=',$monitoring_no)
                        ->get();
                    
                    $count_list_pohon = count($GetMonitoringDetailList); 
                        
                    // var_dump($GetMonitoringDetail);
                    
                    $data = ['list_detail'=>$GetMonitoringDetailList,'count_list_pohon'=>$count_list_pohon, 'data'=>$GetMonitoringDetail];
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

    /**
     * @SWG\Get(
     *   path="/api/GetMonitoringDetailFFNo",
     *   tags={"Monitoring"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Monitoring Detail FFNo",
     *   operationId="GetMonitoringDetailFFNo",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="user_id",in="query", required=true, type="string"),
     * )
     */
    public function GetMonitoringDetailFFNo(Request $request){
        $user_id = $request->user_id;
        try{
           
                $GetPHDetail = DB::table('monitorings')
                    ->where('monitorings.user_id','=',$user_id)
                    ->first();

                if($GetPHDetail){ 
                    $GetPHDetailList = DB::table('monitoring_details')
                        ->select('monitoring_details.id',
                        'monitoring_details.monitoring_no','monitoring_details.tree_code','trees.tree_category',
                        'monitoring_details.qty', 'monitoring_details.status', 'monitoring_details.condition', 
                        'monitoring_details.planting_date','monitoring_details.created_at',
                        'trees.tree_name')
                        ->leftjoin('trees', 'trees.tree_code', '=', 'monitoring_details.tree_code')
                        ->leftjoin('monitorings', 'monitorings.monitoring_no', '=', 'monitoring_details.monitoring_no')
                        // ->where('planting_hole_surviellance.is_dell','=',0)
                        ->where('monitorings.user_id','=',$user_id)
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

    /**
     * @SWG\Get(
     *   path="/api/GetMonitoringTest",
     *   tags={"Monitoring"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="GetMonitoringTest",
     *   operationId="GetMonitoringTest",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="monitoring_no",in="query", required=true, type="string"),
     * )
     */
    public function GetMonitoringTest(Request $request){
        $monitoring_no = $request->monitoring_no;
        try{
           
                $GetMonitoringDetail = DB::table('planting_socializations')
                    ->pluck('form_no');


                if($GetMonitoringDetail){                         
                    $data = [ 'datadetail'=>$GetMonitoringDetail];
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

    /**
     * @SWG\Post(
     *   path="/api/AddMonitoring",
	 *   tags={"Monitoring"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Add Monitoring",
     *   operationId="AddMonitoring",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Add Monitoring",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="user_id", type="string", example="FF0001"),
     *              @SWG\Property(property="lahan_no", type="string", example="L0000001"),
     *              @SWG\Property(property="farmer_no", type="string", example="F0000001"),
     *              @SWG\Property(property="planting_year", type="string", example="2021"),
     *              @SWG\Property(property="planting_date", type="string", example="2021-10-10"),
     *              @SWG\Property(property="lahan_condition", type="string", example="-"),
     *              @SWG\Property(property="gambar1", type="string", example="-"),
     *              @SWG\Property(property="gambar2", type="string", example="Nullable"),
     *              @SWG\Property(property="gambar3", type="string", example="Nullable"),
     *              @SWG\Property(property="list_pohon", type="string", example="array pohon json decode tree_code, qty, status dll"),
     *          ),
     *      )
     * )
     *
     */
    public function AddMonitoring(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'monitoring_no' => 'required|unique:monitorings, monitoring_nof',
            'farmer_no' => 'required', 
            'planting_date' => 'required', 
            'planting_year' => 'required',
            'lahan_condition' => 'required',
            'list_pohon' => 'required',
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
                $monitoring_no = 'MO1-'.$request->planting_year.'-'.substr($request->lahan_no,-10);

                $validation = 0;
                $validate_by = '-';
                if($request->validate_by){
                    $validation = 1;
                    $validate_by = $request->validate_by;
                }

                $pohon_mpts = 0;
                $pohon_non_mpts = 0;
                $pohon_bawah = 0;
                
                // var_dump ($request->list_pohon);
                // 'monitoring_no', 'tree_code', 'qty','status','condition','planting_date',
                foreach($request->list_pohon as $val){
                    MonitoringDetail::create([
                        'monitoring_no' => $monitoring_no,
                        'tree_code' => $val['tree_code'],
                        'qty' => $val['qty'],
                        'status' => $val['status'],
                        'condition' => $val['condition'],
                        'planting_date' => $val['planting_date'],
        
                        'created_at'=>Carbon::now(),
                        'updated_at'=>Carbon::now()
                    ]);

                    $trees_get = DB::table('trees')->where('tree_code','=',$val['tree_code'])->first();

                    if( $trees_get->tree_category == "Pohon_Buah"){
                        $pohon_mpts = $pohon_mpts + $val['qty'];
                    }else if($trees_get->tree_category == "Tanaman_Bawah_Empon"){
                        $pohon_bawah = $pohon_bawah + $val['qty'];
                    }else{
                        $pohon_non_mpts = $pohon_non_mpts + $val['qty'];
                    }
                }
                // var_dump ($pohon_mpts);
            //     'monitoring_no', 'planting_year','planting_date', 'farmer_no', 'lahan_no',
            //     'qty_kayu', 'qty_mpts',  'qty_crops',  'lahan_condition',  'user_id', 
            //    'validation', 'validate_by', 'created_at','updated_at','is_dell'
                Monitoring::create([
                    'monitoring_no' => $monitoring_no,
                    'planting_year' => $request->planting_year,
                    'planting_date' => $request->planting_date,
                    'type_sppt' => $request->type_sppt,
                    'farmer_no' => $request->farmer_no,
                    'lahan_no' => $request->lahan_no,
                    'lahan_condition' => $request->lahan_condition,
                    'gambar1' => $request->gambar1,
                    'gambar2' => $this->ReplaceNull($request->gambar2, 'string'),
                    'gambar3' => $this->ReplaceNull($request->gambar3, 'string'),
                    'is_validate' => $validation,
                    'validate_by' => $validate_by,

                    'qty_kayu' => $pohon_non_mpts,
                    'qty_mpts' => $pohon_mpts,
                    'qty_crops' => $pohon_bawah,
    
                    'user_id' => $request->user_id,
    
                    'created_at'=>Carbon::now(),
                    'updated_at'=>Carbon::now(),
    
                    'is_dell' => 0
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
     *   path="/api/AddMonitoringNew",
	 *   tags={"Monitoring"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Add Monitoring New",
     *   operationId="AddMonitoringNew",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Add Monitoring New",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="user_id", type="string", example="FF0001"),
     *              @SWG\Property(property="farmer_no", type="string", example="F0000001"),
     *              @SWG\Property(property="planting_year", type="string", example="2021"),
     *              @SWG\Property(property="planting_date", type="string", example="2021-10-10"),
     *              @SWG\Property(property="lahan_condition", type="string", example="-"),
     *              @SWG\Property(property="gambar1", type="string", example="-"),
     *              @SWG\Property(property="gambar2", type="string", example="Nullable"),
     *              @SWG\Property(property="gambar3", type="string", example="Nullable"),
     *              @SWG\Property(property="list_pohon", type="string", example="array pohon json decode tree_code, qty, status dll"),
     *          ),
     *      )
     * )
     *
     */
    public function AddMonitoringNew(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'lahan_no' => 'unique:monitorings',
            'farmer_no' => 'required', 
            'planting_date' => 'required', 
            'planting_year' => 'required',
            'lahan_condition' => 'required',
            'list_pohon' => 'required'
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        } 

        DB::beginTransaction();
        
        $Lahan = DB::table('farmers')->where('farmer_no','=',$request->farmer_no)->first();
            
            // print_r($Lahan);
            if($Lahan){
                $year = Carbon::now()->format('Y');
                $monitoring_no = 'MO1-'.$request->planting_year.'-'.substr($request->farmer_no,-9);

                $validation = 0;
                $validate_by = '-';
                if($request->validate_by){
                    $validation = 1;
                    $validate_by = $request->validate_by;
                }

                $pohon_mpts = 0;
                $pohon_non_mpts = 0;
                $pohon_bawah = 0;
                
                // var_dump ($request->list_pohon);
                // 'monitoring_no', 'tree_code', 'qty','status','condition','planting_date',
                foreach($request->list_pohon as $val){
                    MonitoringDetail::create([
                        'monitoring_no' => $monitoring_no,
                        'tree_code' => $val['tree_code'],
                        'qty' => $val['qty'],
                        'status' => $val['status'],
                        'condition' => $val['condition'],
                        'planting_date' => $val['planting_date'],
        
                        'created_at'=>Carbon::now(),
                        'updated_at'=>Carbon::now()
                    ]);

                    $trees_get = DB::table('trees')->where('tree_code','=',$val['tree_code'])->first();

                    if( $trees_get->tree_category == "Pohon_Buah"){
                        $pohon_mpts = $pohon_mpts + $val['qty'];
                    }else if($trees_get->tree_category == "Tanaman_Bawah_Empon"){
                        $pohon_bawah = $pohon_bawah + $val['qty'];
                    }else{
                        $pohon_non_mpts = $pohon_non_mpts + $val['qty'];
                    }
                }
                // var_dump ($pohon_mpts);
            //     'monitoring_no', 'planting_year','planting_date', 'farmer_no', 'lahan_no',
            //     'qty_kayu', 'qty_mpts',  'qty_crops',  'lahan_condition',  'user_id', 
            //    'validation', 'validate_by', 'created_at','updated_at','is_dell'
                Monitoring::create([
                    'monitoring_no' => $monitoring_no,
                    'planting_year' => $request->planting_year,
                    'planting_date' => $request->planting_date,
                    'type_sppt' => $request->type_sppt,
                    'farmer_no' => $request->farmer_no,
                    'lahan_no' => $request->lahan_no,
                    'lahan_condition' => $request->lahan_condition,
                    'qty_std' => $this->ReplaceNull($request->qty_std, 'int'),
                    'gambar1' => $request->gambar1,
                    'gambar2' => $this->ReplaceNull($request->gambar2, 'string'),
                    'gambar3' => $this->ReplaceNull($request->gambar3, 'string'),
                    'is_validate' => $validation,
                    'validate_by' => $validate_by,

                    'qty_kayu' => $pohon_non_mpts,
                    'qty_mpts' => $pohon_mpts,
                    'qty_crops' => $pohon_bawah,
    
                    'user_id' => $request->user_id,
    
                    'created_at'=>Carbon::now(),
                    'updated_at'=>Carbon::now(),
    
                    'is_dell' => 0
                ]);

                DB::commit();
    
                $rslt =  $this->ResultReturn(200, 'success', 'success');
                return response()->json($rslt, 200); 
            }else{
                $rslt =  $this->ResultReturn(400, 'gagal', 'gagal');
                return response()->json($rslt, 400);
            }
    }

    /**
     * @SWG\Post(
     *   path="/api/UpdateMonitoring",
	 *   tags={"Monitoring"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Update Monitoring",
     *   operationId="UpdateMonitoring",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Update Monitoring",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="monitoring_no", type="string", example="MO1-2021-0000001"),
     *              @SWG\Property(property="user_id", type="string", example="FF0001"),
     *              @SWG\Property(property="lahan_no", type="string", example="L0000001"),
     *              @SWG\Property(property="farmer_no", type="string", example="F0000001"),
     *              @SWG\Property(property="planting_year", type="string", example="2021"),
     *              @SWG\Property(property="planting_date", type="string", example="2021-10-10"),
     *              @SWG\Property(property="lahan_condition", type="string", example="-"),
     *              @SWG\Property(property="list_pohon", type="string", example="-"),
     *              @SWG\Property(property="gambar1", type="string", example="-"),
     *              @SWG\Property(property="gambar2", type="string", example="Nullable"),
     *              @SWG\Property(property="gambar3", type="string", example="Nullable"),
     *          ),
     *      )
     * )
     *
     */
    public function UpdateMonitoring(Request $request){
        $validator = Validator::make($request->all(), [
            'monitoring_no' => 'required',
            'user_id' => 'required',
            'farmer_no' => 'required', 
            'planting_date' => 'required', 
            'planting_year' => 'required',
            'lahan_condition' => 'required',
            'qty_std' => 'required',
            'list_pohon' => 'required',
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        }  
        
        DB::beginTransaction();

        try{            
            $monitoring_no = $request->monitoring_no;
            // $Lahan = DB::table('lahans')->where('lahan_no','=',$request->lahan_no)->first();
            $monitoring = DB::table('monitorings')->where('monitoring_no','=',$monitoring_no)->first();
            
            if($monitoring){
                $year = Carbon::now()->format('Y');
                // $form_no = 'PH-'.$year.'-'.substr($request->lahan_no,-10);

                $validation = 0;
                $validate_by = '-';
                if($request->validate_by){
                    $validation = 1;
                    $validate_by = $request->validate_by;
                }
                
                DB::table('monitoring_details')->where('monitoring_no', $monitoring_no)->delete();

                $pohon_mpts = 0;
                $pohon_non_mpts = 0;
                $pohon_bawah = 0;

                foreach($request->list_pohon as $val){
                    MonitoringDetail::create([
                        'monitoring_no' => $monitoring_no,
                        'tree_code' => $val['tree_code'],
                        'qty' => $val['qty'],
                        'status' => $val['status'],
                        'condition' => $val['condition'],
                        'planting_date' => $val['planting_date'],
        
                        'created_at'=>Carbon::now(),
                        'updated_at'=>Carbon::now()
                    ]);

                    $trees_get = DB::table('trees')->where('tree_code','=',$val['tree_code'])->first();

                    if( $trees_get->tree_category == "Pohon_Buah"){
                        $pohon_mpts = $pohon_mpts + $val['qty'];
                    }else if($trees_get->tree_category == "Tanaman_Bawah_Empon"){
                        $pohon_bawah = $pohon_bawah + $val['qty'];
                    }else{
                        $pohon_non_mpts = $pohon_non_mpts + $val['qty'];
                    }
                }

                Monitoring::where('monitoring_no', '=', $monitoring_no)
                ->update([
                    'planting_year' => $request->planting_year,
                    'planting_date' => $request->planting_date,
                    'type_sppt' => $request->type_sppt,
                    'farmer_no' => $request->farmer_no,
                    'lahan_no' => $request->lahan_no,
                    'lahan_condition' => $request->lahan_condition,
                    'gambar1' => $request->gambar1,
                    'gambar2' => $this->ReplaceNull($request->gambar2, 'string'),
                    'gambar3' => $this->ReplaceNull($request->gambar3, 'string'),
                    'qty_std' => $this->ReplaceNull($request->qty_std, 'int'),

                    'qty_kayu' => $pohon_non_mpts,
                    'qty_mpts' => $pohon_mpts,
                    'qty_crops' => $pohon_bawah,
    
                    'user_id' => $request->user_id,
    
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
     *   path="/api/UpdatePohonMonitoring",
	 *   tags={"Monitoring"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Update Pohon Monitoring",
     *   operationId="UpdatePohonMonitoring",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Update Pohon Monitoring",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="monitoring_no", type="string", example="MO1-2021-0000001"),
     *              @SWG\Property(property="list_pohon", type="string", example="array pohon tree_code, qty, status dll"),
     *          ),
     *      )
     * )
     *
     */
    public function UpdatePohonMonitoring(Request $request){
        $validator = Validator::make($request->all(), [
            'monitoring_no' => 'required',
            'list_pohon' => 'required',
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        } 

        DB::beginTransaction();

        try{
             
            
            $monitoring_no = $request->monitoring_no;
            $list_pohon = $request->list_pohon;
            $monitoring = DB::table('monitorings')->where('monitoring_no','=',$monitoring_no)->first();
            
            if($monitoring){
                
                DB::table('monitoring_details')->where('monitoring_no', $monitoring_no)->delete();

                
                $pohon_mpts = 0;
                $pohon_non_mpts = 0;
                $pohon_bawah = 0;

                foreach($request->list_pohon as $val){
                    MonitoringDetail::where('monitoring_no', '=', $monitoring_no)
                    ->update([
                        'monitoring_no' => $monitoring_no,
                        'tree_code' => $val['tree_code'],
                        'qty' => $val['qty'],
                        'status' => $val['status'],
                        'condition' => $val['condition'],
                        'planting_date' => $val['planting_date'],
        
                        'created_at'=>Carbon::now(),
                        'updated_at'=>Carbon::now()
                    ]);

                    $trees_get = DB::table('trees')->where('tree_code','=',$val['tree_code'])->first();

                    if( $trees_get->tree_category == "Pohon_Buah"){
                        $pohon_mpts = $pohon_mpts + $val['qty'];
                    }else if($trees_get->tree_category == "Tanaman_Bawah_Empon"){
                        $pohon_bawah = $pohon_bawah + $val['qty'];
                    }else{
                        $pohon_non_mpts = $pohon_non_mpts + $val['qty'];
                    }
                }

                Monitoring::where('monitoring_no', '=', $monitoring_no)
                ->update([
                    // 'form_no' => $form_no,
                    'qty_kayu' => $pohon_non_mpts,
                    'qty_mpts' => $pohon_mpts,
                    'qty_crops' => $pohon_bawah,
    
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
     *   path="/api/SoftDeleteMonitoring",
	 *   tags={"Monitoring"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="SoftDelete Monitoring",
     *   operationId="SoftDeleteMonitoring",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="SoftDelete Monitoring",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="monitoring_no", type="string", example="MO1-2021-0000001"),
     *          ),
     *      )
     * )
     *
     */
    public function SoftDeleteMonitoring(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'monitoring_no' => 'required',
            ]);

            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }  
            
            $monitoring_no = $request->monitoring_no;
            $monitoring = DB::table('monitorings')->where('monitoring_no','=',$monitoring_no)->first();
            
            if($monitoring){

                Monitoring::where('monitoring_no', '=', $monitoring_no)
                ->update([    
                    'updated_at'=>Carbon::now(),    
                    'is_dell' => 1
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
    
    public function DeleteMonitoring(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'monitoring_no' => 'required',
            ]);

            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            } 
            
            $monitoring_no = $request->monitoring_no;
            $monitoring = DB::table('monitorings')->where('monitoring_no','=',$monitoring_no)->first();
            
            if($monitoring){
                $this->createLog([
                    'status' => 'Deleted',
                    'monitoring_no' => $monitoring_no
                ]);
                
                MonitoringDetail::where('monitoring_no', $monitoring_no)->delete();
                Monitoring::where('monitoring_no', '=', $monitoring_no)
                ->delete();
    
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
     *   path="/api/ValidateMonitoring",
	 *   tags={"Monitoring"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Validate Monitoring",
     *   operationId="ValidateMonitoring",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Validate Monitoring",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="monitoring_no", type="string", example="MO1-2021-0000001"),
     *              @SWG\Property(property="validate_by", type="string", example="00-11010"),
     *          ),
     *      )
     * )
     *
     */
    public function ValidateMonitoring(Request $request){
        
        $validator = Validator::make($request->all(), [
            'monitoring_no' => 'required',
            'validate_by' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->first(), 400);
        }  
        // if (Auth::user()->email != 'iyas.muzani@trees4trees.org') {
        //     return response()->json('Maintenance, please wait!', 500);
        // }
        $monitoring_no = $request->monitoring_no;
        $monitoring = DB::table('monitorings')->where('monitoring_no','=',$monitoring_no)->first();
        
        if(!$request->list_trees){
            if($monitoring){

                Monitoring::where('monitoring_no', '=', $monitoring_no)
                ->update([    
                    'updated_at'=>Carbon::now(),
                    'validate_by' => $request->validate_by,    
                    'is_validate' => 1
                ]);
                
                $rslt =  $this->ResultReturn(200, 'success', 'success');
                return response()->json($rslt, 200);
            }else{
                $rslt =  $this->ResultReturn(400, 'doesnt match data', 'doesnt match data');
                return response()->json($rslt, 400);
            }
        }else{
            if($monitoring){
                
                DB::table('monitoring_details')->where('monitoring_no', $monitoring_no)->delete();

                $pohon_mpts = 0;
                $pohon_non_mpts = 0;
                $pohon_bawah = 0;
                
                $updatedTrees = [];

                foreach($request->list_trees as $val){
                    MonitoringDetail::create([
                        'monitoring_no' => $monitoring_no,
                        'tree_code' => $val['tree_code'],
                        'qty' => $val['qty'],
                        'status' => $val['status'],
                        'condition' => $val['condition'],
                        'planting_date' => $val['planting_date'],
        
                        'created_at'=>Carbon::now(),
                        'updated_at'=>Carbon::now()
                    ]);

                    $trees_get = DB::table('trees')->where('tree_code','=',$val['tree_code'])->first();

                    if( $trees_get->tree_category == "Pohon_Buah"){
                        $pohon_mpts = $pohon_mpts + $val['qty'];
                    }else if($trees_get->tree_category == "Tanaman_Bawah_Empon"){
                        $pohon_bawah = $pohon_bawah + $val['qty'];
                    }else{
                        $pohon_non_mpts = $pohon_non_mpts + $val['qty'];
                    }
                    
                    array_push($updatedTrees, ((DB::table('trees')->where('tree_code', $val['tree_code'])->first()->tree_name ?? 'jenis_pohon') . " => " . ($val['qty'].'_'.$val['status'].'_'.$val['condition'])));
                }
                $updatedTreesToString = implode(", ", $updatedTrees);
                // return response()->json($updatedTreesToString, 200);
                // create Log
                $this->createLog([
                    'status' => 'Verification',
                    'monitoring_no' => $monitoring_no,
                    'message' => " [status => 1, " . $updatedTreesToString . "]" 
                ]);
                
                Monitoring::where('monitoring_no', '=', $monitoring_no)
                ->update([    
                    'updated_at'=>Carbon::now(),
                    'validate_by' => $request->validate_by,    
                    'is_validate' => 1,
                    'qty_kayu' => $pohon_non_mpts,
                    'qty_mpts' => $pohon_mpts,
                    'qty_crops' => $pohon_bawah,
                ]);
    
                $rslt =  $this->ResultReturn(200, 'success', 'success');
                return response()->json($rslt, 200); 
            }else{
                $rslt =  $this->ResultReturn(400, 'doesnt match data', 'doesnt match data');
                return response()->json($rslt, 400);
            }   
        }
    }
    
    public function MonitoringVerificationUM(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'monitoring_no' => 'required',
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        }  
        
        $monitoring_no = $request->monitoring_no;
        $monitoring = DB::table('monitorings')->where('monitoring_no','=',$monitoring_no)->first();
        
        if($monitoring){
            
            MonitoringDetail::where([
                'monitoring_no' => $monitoring->monitoring_no 
            ])->delete();

            $pohon_mpts = 0;
            $pohon_non_mpts = 0;
            $pohon_bawah = 0;
            
            $updatedTrees = [];
            
            $listTree = $request->list_trees;
            
            foreach($listTree as $val){
                MonitoringDetail::create([
                    'monitoring_no' => $monitoring->monitoring_no,
                    'tree_code' =>  $val['tree_code'],
                    'qty' => $val['qty'],
                    'status' => $val['status'],
                    'condition' => $val['condition'],
                    'planting_date' => $val['planting_date']
                ]);

                $trees_get = DB::table('trees')->where('tree_code','=',$val['tree_code'])->first();
    
                if( $trees_get->tree_category == "Pohon_Buah"){
                    $pohon_mpts = $pohon_mpts + $val['qty'];
                }else if($trees_get->tree_category == "Tanaman_Bawah_Empon"){
                    $pohon_bawah = $pohon_bawah + $val['qty'];
                }else{
                    $pohon_non_mpts = $pohon_non_mpts + $val['qty'];
                }
                
                array_push($updatedTrees, ((DB::table('trees')->where('tree_code', $val['tree_code'])->first()->tree_name ?? 'jenis_pohon') . " => " . ($val['qty'].'_'.$val['status'].'_'.$val['condition'])));
            }

            $updatedTreesToString = implode(", ", $updatedTrees);
            // create Log
            $this->createLog([
                'status' => 'Verification',
                'monitoring_no' => $monitoring_no,
                'message' => " [status => 2, " . $updatedTreesToString . "]" 
            ]);
            Monitoring::where('monitoring_no', '=', $monitoring_no)
            ->update([    
                'updated_at'=>Carbon::now(),
                'validate_by' => $request->validate_by,    
                'is_validate' => 2,
                'qty_kayu' => $pohon_non_mpts,
                'qty_mpts' => $pohon_mpts,
                'qty_crops' => $pohon_bawah,
            ]);

            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200); 
        }else{
            $rslt =  $this->ResultReturn(400, 'doesnt match data', 'doesnt match data');
            return response()->json($rslt, 400);
        }
    }
    
    public function ExportMonitoring(Request $req) {
        $validator = Validator::make($req->all(), [
            'program_year' => 'required',
            'land_program' => 'required|in:Petani',
            'ff' => 'required'
        ]);

        if($validator->fails())return response()->json($validator->errors()->first(), 400);
        else {
            $py = $req->program_year;
            $lp = $req->land_program;
            
            $data = DB::table('monitorings')
                ->join('farmers', 'farmers.farmer_no', 'monitorings.farmer_no')
                ->join('field_facilitators', 'field_facilitators.ff_no', 'farmers.user_id')
                ->join('employees', 'employees.nik', 'field_facilitators.fc_no')
                ->join('managementunits', 'managementunits.mu_no', 'farmers.mu_no')
                ->join('target_areas', 'target_areas.area_code', 'farmers.target_area')
                ->join('desas', 'desas.kode_desa', 'farmers.village')
                ->select(
                    'monitorings.*',
                    'monitorings.planting_year as program_year',
                    'monitorings.type_sppt as type_sppt',
                    'managementunits.name as mu_name',
                    'target_areas.name as ta_name',
                    'desas.name as village_name',
                    'employees.name as fc_name',
                    'field_facilitators.name as ff_name',
                    'farmers.name as farmer_name',
                    'farmers.ktp_no',
                    'farmers.address as farmer_address',
                    'farmers.rt as farmer_rt',
                    'farmers.rw as farmer_rw'
                )
                ->where([
                    'monitorings.planting_year' => $py,
                    ['farmers.mu_no', 'LIKE', "%$req->mu_no%"],
                    ['farmers.target_area', 'LIKE', "%$req->ta_no%"],
                    ['farmers.village', 'LIKE', "%$req->village_no%"],
                ]);
            if ($req->ff) {
                $ff = explode(",", $req->ff);
                $data = $data->whereIn('farmers.user_id', $ff);
            }
            $monitoring_no = $data->pluck('monitoring_no');
            $tree_code = MonitoringDetail::whereIn('monitoring_no', $monitoring_no)->groupBy('tree_code')->pluck('tree_code');
            $trees = DB::table('trees')->orderBy('tree_name')->whereIn('tree_code', $tree_code)->get();
            
            $data = $data->orderBy('monitorings.created_at')->get();
            
            foreach ($data as $d) {
                $d->lahan_no = explode(',', $d->lahan_no);
                $lahanQuery = DB::table('lahans')->whereIn('lahan_no', $d->lahan_no);
                $lahan = $lahanQuery->get();
                $d->planting_date = date('Y-m-d', strtotime($d->planting_date));
                
                $treesPlanted = DB::table('monitoring_details')->where('monitoring_no', $d->monitoring_no)->pluck('tree_code')->toArray();
                $tree_details = [];
                foreach($trees as $tree) {
                    if (in_array($tree->tree_code, $treesPlanted)) {
                        $planted_life = DB::table('monitoring_details')->where(['monitoring_no' => $d->monitoring_no, 'tree_code' => $tree->tree_code, 'status' => 'sudah_ditanam', 'condition' => 'hidup'])->sum('qty');
                        $dead = DB::table('monitoring_details')->where(['monitoring_no' => $d->monitoring_no, 'tree_code' => $tree->tree_code, 'condition' => 'mati'])->sum('qty');
                        $lost = DB::table('monitoring_details')->where(['monitoring_no' => $d->monitoring_no, 'tree_code' => $tree->tree_code, 'condition' => 'hilang'])->sum('qty');
                        // $sum = 1;
                        array_push($tree_details, (object)[
                            'planted_life' => (int)$planted_life,
                            'dead' => (int)$dead,
                            'lost' => (int)$lost,
                        ]);
                    } else array_push($tree_details, (object)[
                            'planted_life' => 0,
                            'dead' => 0,
                            'lost' => 0,
                        ]);
                }
                $d->tree_details = $tree_details;
            
                $d->document_no = [];
                $d->land_area = [];
                $d->planting_area = [];
                $d->planting_pattern = [];
                $d->land_distance = [];
                $d->access_lahan = [];
                $d->coordinate = [];
                $d->land_status = [];
                foreach ($lahan as $lIndex => $l) {
                    array_push($d->document_no, $l->document_no);
                    array_push($d->land_area, $l->land_area);
                    array_push($d->planting_area, $l->planting_area);
                    array_push($d->planting_pattern, $l->opsi_pola_tanam);
                    array_push($d->land_distance, $l->jarak_lahan);
                    array_push($d->access_lahan, $l->access_to_lahan);
                    array_push($d->coordinate, $l->coordinate);
                    array_push($d->land_status, $this->getTypeSppt($l->type_sppt));
                }
            }
            $rslt = [
                'trees' => $trees,
                'data' => $data
            ];
            return view('monitoring.export', $rslt);
            // return response()->json($rslt, 200);
        }
    }
    
    
    public function UnverificationMonitoring(Request $req) {
        $validator = Validator::make($req->all(), [
            'monitoring_no' => 'required|exists:monitorings,monitoring_no',
            'is_validate' => 'required'
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        }  
        
        $unverif = Monitoring::where('monitoring_no', $req->monitoring_no)->update(['is_validate' => $req->is_validate]);
        if ($unverif) {
            // create Log
            $this->createLog([
                'status' => 'Unverification',
                'monitoring_no' => $req->monitoring_no,
                'message' => " [status => $req->is_validate]"
            ]);
            return response()->json('Success', 200);
        }
        else return reponse()->json('Failed', 500);
    }
    
    public function UpdateSPPTMonitoring(Request $req) {
        $validator = Validator::make($req->all(), [
            'monitoring_no' => 'required|exists:monitorings,monitoring_no'
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        }  
        
        $mon = Monitoring::where('monitoring_no', $req->monitoring_no)->first();
        $lahan = Lahan::where('lahan_no', $mon->lahan_no)->first();
        $updateSPPT = Monitoring::where('monitoring_no', $req->monitoring_no)->update(['type_sppt'=> $lahan->type_sppt]);
        
        if ($updateSPPT) {
            // create Log
            return response()->json('Success', 200);
        }
        else return reponse()->json('Failed', 500);
    }
    // END: MONITORING 1}
    
    // MONITORING 2 {

    /**
     * @SWG\Get(
     *   path="/api/GetMonitoring2FF",
     *   tags={"Monitoring2"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Monitoring 2 FF",
     *   operationId="GetMonitoring2FF",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="ff_no",in="query", required=true, type="string"),
     * )
     */
    public function GetMonitoring2FF(Request $request){
        $ff_no = $request->ff_no;
        try{
           
                $GetmonitoringFF = DB::table('monitoring_2')
                    ->select('monitoring_2.id','monitoring_2.lahan_no',
                    'monitoring_2.monitoring_no','monitoring_2.planting_year','monitoring_2.planting_date',
                    'monitoring_2.is_validate','monitoring_2.validate_by','monitoring_2.lahan_condition',
                    'monitoring_2.qty_kayu','monitoring_2.qty_mpts','monitoring_2.qty_crops',
                    'monitoring_2.is_dell', 'monitoring_2.created_at',  'monitoring_2.user_id as ff_no',                    
                    'monitoring_2.gambar1','monitoring_2.gambar2','monitoring_2.gambar3',
                    'farmers.name as nama_petani', 'field_facilitators.name as nama_ff')
                    ->leftjoin('lahans', 'lahans.lahan_no', '=', 'monitoring_2.lahan_no')
                    ->leftjoin('farmers', 'farmers.farmer_no', '=', 'monitoring_2.farmer_no')
                    ->leftjoin('field_facilitators', 'field_facilitators.ff_no', '=', 'monitoring_2.user_id')
                    ->where('monitoring_2.is_dell','=',0)
                    ->where('monitoring_2.user_id','=',$ff_no)
                    ->get();

                if(count($GetmonitoringFF)!=0){ 
                    $count = DB::table('monitoring_2')
                        ->leftjoin('lahans', 'lahans.lahan_no', '=', 'monitoring_2.lahan_no')
                        ->leftjoin('farmers', 'farmers.farmer_no', '=', 'monitoring_2.farmer_no')
                        ->leftjoin('field_facilitators', 'field_facilitators.ff_no', '=', 'monitoring_2.user_id')
                        ->where('monitoring_2.is_dell','=',0)
                        ->where('monitoring_2.user_id','=',$ff_no)
                        ->count();
                    
                    $data = ['count'=>$count, 'data'=>$GetmonitoringFF];
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

    /**
     * @SWG\Get(
     *   path="/api/GetMonitoring2Admin",
     *   tags={"Monitoring2"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Monitoring 2 Admin",
     *   operationId="GetMonitoring2Admin",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="typegetdata",in="query",required=true, type="string"),
     *      @SWG\Parameter(name="ff",in="query",required=true, type="string"),
     * )
     */
    public function GetMonitoring2Admin(Request $request){
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
           
            if($typegetdata == 'all' || $typegetdata == 'several'){
                if($typegetdata == 'all'){
                    $GetPH = DB::table('monitoring_2')
                    ->select('monitoring_2.id','monitoring_2.lahan_no','monitoring_2.farmer_no',
                    'monitoring_2.monitoring_no','monitoring_2.planting_year','monitoring_2.planting_date',
                    'monitoring_2.is_validate','monitoring_2.validate_by','monitoring_2.lahan_condition',
                    'monitoring_2.qty_kayu', 'monitoring_2.qty_mpts', 'monitoring_2.qty_crops',
                    'monitoring_2.is_dell', 'monitoring_2.created_at', 'monitoring_2.user_id',
                    'lahans.longitude','lahans.latitude','lahans.coordinate',
                    'lahans.jarak_lahan','lahans.opsi_pola_tanam','lahans.access_to_lahan',
                    'lahans.planting_area','lahans.land_area','lahans.pohon_kayu','lahans.pohon_mpts','lahans.tanaman_bawah',
                    'farmers.name as nama_petani', 'field_facilitators.name as nama_ff')
                    ->leftjoin('lahans', 'lahans.lahan_no', '=', 'monitoring_2.lahan_no')
                    ->leftjoin('farmers', 'farmers.farmer_no', '=', 'monitoring_2.farmer_no')
                    ->leftjoin('field_facilitators', 'field_facilitators.ff_no', '=', 'monitoring_2.user_id')
                    ->where('monitoring_2.is_dell','=',0)                                        
                    ->where('lahans.mu_no','like',$mu)
                    ->where('lahans.target_area','like',$ta)
                    ->where('lahans.village','like',$village)
                    // ->where('monitoring.user_id','=',$ff_no)
                    ->get();

                }else{
                    $ffdecode = (explode(",",$ff));

                    $GetPH = DB::table('monitoring_2')
                    ->select('monitoring_2.id','monitoring_2.lahan_no','monitoring_2.farmer_no',
                    'monitoring_2.monitoring_no','monitoring_2.planting_year','monitoring_2.planting_date',
                    'monitoring_2.is_validate','monitoring_2.validate_by','monitoring_2.lahan_condition',
                    'monitoring_2.qty_kayu', 'monitoring_2.qty_mpts', 'monitoring_2.qty_crops',
                    'monitoring_2.is_dell', 'monitoring_2.created_at', 'monitoring_2.user_id',
                    'lahans.longitude','lahans.latitude','lahans.coordinate',
                    'lahans.jarak_lahan','lahans.opsi_pola_tanam','lahans.access_to_lahan',
                    'lahans.planting_area','lahans.land_area','lahans.pohon_kayu','lahans.pohon_mpts','lahans.tanaman_bawah',
                    'farmers.name as nama_petani', 'field_facilitators.name as nama_ff')
                    ->leftjoin('lahans', 'lahans.lahan_no', '=', 'monitoring_2.lahan_no')
                    ->leftjoin('farmers', 'farmers.farmer_no', '=', 'monitoring_2.farmer_no')
                    ->leftjoin('field_facilitators', 'field_facilitators.ff_no', '=', 'monitoring_2.user_id')
                    ->where('monitoring_2.is_dell','=',0)   
                    ->wherein('monitoring_2.user_id',$ffdecode)
                    // ->where('planting_hole_surviellance.user_id','=',$ff_no)
                    ->get();
                }


                $dataval = [];
                $listval=array();
                foreach ($GetPH as $val) {
                    $status = '';
                    if($val->is_validate==0){
                        $status = 'Belum Verifikasi';
                    }else{
                        $status = 'Sudah Verifikasi';
                    }
                    $dataval = ['id'=>$val->id,'lahan_no'=>$val->lahan_no, 'monitoring_no'=>$val->monitoring_no,
                    'planting_year'=>$val->planting_year, 'ff_no' => $val->user_id, 'is_validate' => $val->is_validate, 
                    'nama_ff'=>$val->nama_ff,'validate_by'=>$val->validate_by,  'nama_petani'=>$val->nama_petani, 'lahan_condition'=>$val->lahan_condition,
                    'longitude'=>$val->longitude,'latitude'=>$val->latitude,'coordinate'=>$val->coordinate,
                    'planting_area'=>$val->planting_area,'land_area'=>$val->land_area,'pohon_kayu'=>$val->pohon_kayu,
                    'pohon_mpts'=>$val->pohon_mpts,'tanaman_bawah'=>$val->tanaman_bawah,
                    'jarak_lahan'=>$val->jarak_lahan,'opsi_pola_tanam'=>$val->opsi_pola_tanam,'access_to_lahan'=>$val->access_to_lahan,
                    'status' => $status, 'is_dell' => $val->is_dell, 'created_at' => $val->created_at];
                    array_push($listval, $dataval);
                }

                
                // var_dump($listval);

                if(count($listval)!=0){ 
                    if($typegetdata == 'all'){
                        $count = DB::table('monitoring_2')
                        ->leftjoin('lahans', 'lahans.lahan_no', '=', 'monitoring_2.lahan_no')
                        ->leftjoin('farmers', 'farmers.farmer_no', '=', 'monitoring_2.farmer_no')
                        ->leftjoin('field_facilitators', 'field_facilitators.ff_no', '=', 'monitoring_2.user_id')
                        ->where('monitoring_2.is_dell','=',0)                                       
                        ->where('lahans.mu_no','like',$mu)
                        ->where('lahans.target_area','like',$ta)
                        ->where('lahans.village','like',$village)
                        ->count();
                    }else{
                        $ffdecode = (explode(",",$ff));

                        $count = DB::table('monitoring_2')
                        ->leftjoin('lahans', 'lahans.lahan_no', '=', 'monitoring_2.lahan_no')
                        ->leftjoin('farmers', 'farmers.farmer_no', '=', 'lahans.farmer_no')
                        ->leftjoin('field_facilitators', 'field_facilitators.ff_no', '=', 'monitoring_2.user_id')
                        ->where('monitoring_2.is_dell','=',0) 
                        ->wherein('monitoring_2.user_id',$ffdecode)
                        ->count();
                    }                   
                    
                    
                    $data = ['count'=>$count, 'data'=>$listval];
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
     *   path="/api/GetMonitoring2Detail",
     *   tags={"Monitoring2"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Monitoring 2 Detail",
     *   operationId="GetMonitoring2Detail",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="monitoring_no",in="query", required=true, type="string"),
     * )
     */
    public function GetMonitoring2Detail(Request $request){
        $monitoring_no = $request->monitoring_no;
        try{
           
                $GetMonitoringDetail = DB::table('monitoring_2')
                    ->select('monitoring_2.id','monitoring_2.lahan_no','monitoring_2.farmer_no',
                    'monitoring_2.monitoring_no','monitoring_2.planting_year','monitoring_2.planting_date',
                    'monitoring_2.is_validate','monitoring_2.validate_by','monitoring_2.lahan_condition',
                    'monitoring_2.qty_kayu','monitoring_2.qty_mpts','monitoring_2.qty_crops',
                    'monitoring_2.gambar1','monitoring_2.gambar2','monitoring_2.gambar3',
                    'monitoring_2.is_dell', 'monitoring_2.created_at',  'monitoring_2.user_id as ff_no',
                    'farmers.name as nama_petani', 'field_facilitators.name as nama_ff', 'monitoring_2.user_id')
                    ->leftjoin('lahans', 'lahans.lahan_no', '=', 'monitoring_2.lahan_no')
                    ->leftjoin('farmers', 'farmers.farmer_no', '=', 'monitoring_2.farmer_no')
                    ->leftjoin('field_facilitators', 'field_facilitators.ff_no', '=', 'monitoring_2.user_id')
                    ->where('monitoring_2.monitoring_no','=',$monitoring_no)
                    ->first();


                if($GetMonitoringDetail){ 
                    $GetMonitoringDetailList = DB::table('monitoring_2_detail')
                        ->select('monitoring_2_detail.id',
                        'monitoring_2_detail.monitoring_no','monitoring_2_detail.tree_code','trees.tree_category',
                        'monitoring_2_detail.qty','monitoring_2_detail.qty as amount', 'monitoring_2_detail.status', 'monitoring_2_detail.condition', 
                        'monitoring_2_detail.planting_date','monitoring_2_detail.created_at',
                        'trees.tree_name as tree_name')
                        ->leftjoin('trees', 'trees.tree_code', '=', 'monitoring_2_detail.tree_code')
                        ->where('monitoring_2_detail.monitoring_no','=',$monitoring_no)
                        ->get();
                    
                    $count_list_pohon = count($GetMonitoringDetailList); 
                        
                    // var_dump($GetMonitoringDetail);
                    
                    $data = ['list_detail'=>$GetMonitoringDetailList,'count_list_pohon'=>$count_list_pohon, 'data'=>$GetMonitoringDetail];
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

    /**
     * @SWG\Get(
     *   path="/api/GetMonitoring2DetailFFNo",
     *   tags={"Monitoring2"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Monitoring 2 Detail FFNo",
     *   operationId="GetMonitoring2DetailFFNo",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="user_id",in="query", required=true, type="string"),
     * )
     */
    public function GetMonitoring2DetailFFNo(Request $request){
        $user_id = $request->user_id;
        try{
           
                $GetPHDetail = DB::table('monitoring_2')
                    ->where('monitoring_2.user_id','=',$user_id)
                    ->first();

                if($GetPHDetail){ 
                    $GetPHDetailList = DB::table('monitoring_2_detail')
                        ->select('monitoring_2_detail.id',
                        'monitoring_2_detail.monitoring_no','monitoring_2_detail.tree_code','trees.tree_category',
                        'monitoring_2_detail.qty', 'monitoring_2_detail.status', 'monitoring_2_detail.condition', 
                        'monitoring_2_detail.planting_date','monitoring_2_detail.created_at',
                        'trees.tree_name')
                        ->leftjoin('trees', 'trees.tree_code', '=', 'monitoring_2_detail.tree_code')
                        ->leftjoin('monitoring_2', 'monitoring_2.monitoring_no', '=', 'monitoring_2_detail.monitoring_no')
                        // ->where('planting_hole_surviellance.is_dell','=',0)
                        ->where('monitoring_2.user_id','=',$user_id)
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

    /**
     * @SWG\Post(
     *   path="/api/ValidateMonitoring2",
	 *   tags={"Monitoring2"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Validate Monitoring 2",
     *   operationId="ValidateMonitoring2",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Validate Monitoring 2",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="monitoring_no", type="string", example="MO1-2021-0000001"),
     *              @SWG\Property(property="validate_by", type="string", example="00-11010"),
     *          ),
     *      )
     * )
     *
     */
    public function ValidateMonitoring2(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'monitoring_no' => 'required',
                'validate_by' => 'required',
            ]);

            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }  
            
            $monitoring_no = $request->monitoring_no;
            $monitoring = DB::table('monitoring_2')->where('monitoring_no','=',$monitoring_no)->first();
            
            if($monitoring){

                Monitoring2::where('monitoring_no', '=', $monitoring_no)
                ->update([    
                    'updated_at'=>Carbon::now(),
                    'validate_by' => $request->validate_by,    
                    'is_validate' => 1
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
     *   path="/api/AddMonitoring2",
	 *   tags={"Monitoring2"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Add Monitoring 2",
     *   operationId="AddMonitoring2",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Add Monitoring 2",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="user_id", type="string", example="FF0001"),
     *              @SWG\Property(property="lahan_no", type="string", example="L0000001"),
     *              @SWG\Property(property="farmer_no", type="string", example="F0000001"),
     *              @SWG\Property(property="planting_year", type="string", example="2021"),
     *              @SWG\Property(property="planting_date", type="string", example="2021-10-10"),
     *              @SWG\Property(property="lahan_condition", type="string", example="-"),
     *              @SWG\Property(property="gambar1", type="string", example="-"),
     *              @SWG\Property(property="gambar2", type="string", example="Nullable"),
     *              @SWG\Property(property="gambar3", type="string", example="Nullable"),
     *              @SWG\Property(property="list_pohon", type="string", example="array pohon json decode tree_code, qty, status dll"),
     *          ),
     *      )
     * )
     *
     */
    public function AddMonitoring2(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'lahan_no' => 'required|unique:monitoring', 
            'farmer_no' => 'required', 
            'planting_date' => 'required', 
            'planting_year' => 'required',
            'lahan_condition' => 'required',
            'list_pohon' => 'required',
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
                $monitoring_no = 'MO2-'.$request->planting_year.'-'.substr($request->lahan_no,-10);

                $validation = 0;
                $validate_by = '-';
                if($request->validate_by){
                    $validation = 1;
                    $validate_by = $request->validate_by;
                }

                $pohon_mpts = 0;
                $pohon_non_mpts = 0;
                $pohon_bawah = 0;
                
                // var_dump ($request->list_pohon);
                // 'monitoring_no', 'tree_code', 'qty','status','condition','planting_date',
                foreach($request->list_pohon as $val){
                    Monitoring2Detail::create([
                        'monitoring_no' => $monitoring_no,
                        'tree_code' => $val['tree_code'],
                        'qty' => $val['qty'],
                        'status' => $val['status'],
                        'condition' => $val['condition'],
                        'planting_date' => $val['planting_date'],
        
                        'created_at'=>Carbon::now(),
                        'updated_at'=>Carbon::now()
                    ]);

                    $trees_get = DB::table('trees')->where('tree_code','=',$val['tree_code'])->first();

                    if( $trees_get->tree_category == "Pohon_Buah"){
                        $pohon_mpts = $pohon_mpts + $val['qty'];
                    }else if($trees_get->tree_category == "Tanaman_Bawah_Empon"){
                        $pohon_bawah = $pohon_bawah + $val['qty'];
                    }else{
                        $pohon_non_mpts = $pohon_non_mpts + $val['qty'];
                    }
                }
                // var_dump ($pohon_mpts);
            //     'monitoring_no', 'planting_year','planting_date', 'farmer_no', 'lahan_no',
            //     'qty_kayu', 'qty_mpts',  'qty_crops',  'lahan_condition',  'user_id', 
            //    'validation', 'validate_by', 'created_at','updated_at','is_dell'
                Monitoring2::create([
                    'monitoring_no' => $monitoring_no,
                    'planting_year' => $request->planting_year,
                    'planting_date' => $request->planting_date,
                    'farmer_no' => $request->farmer_no,
                    'lahan_no' => $request->lahan_no,
                    'lahan_condition' => $request->lahan_condition,
                    'gambar1' => $request->gambar1,
                    'gambar2' => $this->ReplaceNull($request->gambar2, 'string'),
                    'gambar3' => $this->ReplaceNull($request->gambar3, 'string'),
                    'is_validate' => $validation,
                    'validate_by' => $validate_by,

                    'qty_kayu' => $pohon_non_mpts,
                    'qty_mpts' => $pohon_mpts,
                    'qty_crops' => $pohon_bawah,
    
                    'user_id' => $request->user_id,
    
                    'created_at'=>Carbon::now(),
                    'updated_at'=>Carbon::now(),
    
                    'is_dell' => 0
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
     *   path="/api/UpdateMonitoring2",
	 *   tags={"Monitoring2"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Update Monitoring 2",
     *   operationId="UpdateMonitoring2",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Update Monitoring 2",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="monitoring_no", type="string", example="MO1-2021-0000001"),
     *              @SWG\Property(property="user_id", type="string", example="FF0001"),
     *              @SWG\Property(property="lahan_no", type="string", example="L0000001"),
     *              @SWG\Property(property="farmer_no", type="string", example="F0000001"),
     *              @SWG\Property(property="planting_year", type="string", example="2021"),
     *              @SWG\Property(property="planting_date", type="string", example="2021-10-10"),
     *              @SWG\Property(property="lahan_condition", type="string", example="-"),
     *              @SWG\Property(property="list_pohon", type="string", example="-"),
     *              @SWG\Property(property="gambar1", type="string", example="-"),
     *              @SWG\Property(property="gambar2", type="string", example="Nullable"),
     *              @SWG\Property(property="gambar3", type="string", example="Nullable"),
     *          ),
     *      )
     * )
     *
     */
    public function UpdateMonitoring2(Request $request){
        $validator = Validator::make($request->all(), [
            'monitoring_no' => 'required',
            'user_id' => 'required',
            'lahan_no' => 'required', 
            'farmer_no' => 'required', 
            'planting_date' => 'required', 
            'planting_year' => 'required',
            'lahan_condition' => 'required',
            'list_pohon' => 'required',
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        }  
        
        DB::beginTransaction();

        try{            
            
            $monitoring_no = $request->monitoring_no;
            // $Lahan = DB::table('lahans')->where('lahan_no','=',$request->lahan_no)->first();
            $monitoring = DB::table('monitoring_2')->where('monitoring_no','=',$monitoring_no)->first();
            
            if($monitoring){
                $year = Carbon::now()->format('Y');
                // $form_no = 'PH-'.$year.'-'.substr($request->lahan_no,-10);

                $validation = 0;
                $validate_by = '-';
                if($request->validate_by){
                    $validation = 1;
                    $validate_by = $request->validate_by;
                }

                
                DB::table('monitoring_2_detail')->where('monitoring_no', $monitoring_no)->delete();

                $pohon_mpts = 0;
                $pohon_non_mpts = 0;
                $pohon_bawah = 0;

                foreach($request->list_pohon as $val){
                    Monitoring2Detail::create([
                        'monitoring_no' => $monitoring_no,
                        'tree_code' => $val['tree_code'],
                        'qty' => $val['qty'],
                        'status' => $val['status'],
                        'condition' => $val['condition'],
                        'planting_date' => $val['planting_date'],
        
                        'created_at'=>Carbon::now(),
                        'updated_at'=>Carbon::now()
                    ]);

                    $trees_get = DB::table('trees')->where('tree_code','=',$val['tree_code'])->first();

                    if( $trees_get->tree_category == "Pohon_Buah"){
                        $pohon_mpts = $pohon_mpts + $val['qty'];
                    }else if($trees_get->tree_category == "Tanaman_Bawah_Empon"){
                        $pohon_bawah = $pohon_bawah + $val['qty'];
                    }else{
                        $pohon_non_mpts = $pohon_non_mpts + $val['qty'];
                    }
                }

                

                Monitoring2::where('monitoring_no', '=', $monitoring_no)
                ->update([
                    'planting_year' => $request->planting_year,
                    'planting_date' => $request->planting_date,
                    'farmer_no' => $request->farmer_no,
                    'lahan_no' => $request->lahan_no,
                    'lahan_condition' => $request->lahan_condition,
                    'gambar1' => $request->gambar1,
                    'gambar2' => $this->ReplaceNull($request->gambar2, 'string'),
                    'gambar3' => $this->ReplaceNull($request->gambar3, 'string'),

                    'qty_kayu' => $pohon_non_mpts,
                    'qty_mpts' => $pohon_mpts,
                    'qty_crops' => $pohon_bawah,
    
                    'user_id' => $request->user_id,
    
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
     *   path="/api/UpdatePohonMonitoring2",
	 *   tags={"Monitoring2"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Update Pohon Monitoring 2",
     *   operationId="UpdatePohonMonitoring2",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Update Pohon Monitoring 2",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="monitoring_no", type="string", example="MO1-2021-0000001"),
     *              @SWG\Property(property="list_pohon", type="string", example="array pohon tree_code, qty, status dll"),
     *          ),
     *      )
     * )
     *
     */
    public function UpdatePohonMonitoring2(Request $request){
        $validator = Validator::make($request->all(), [
            'monitoring_no' => 'required',
            'list_pohon' => 'required',
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        } 

        DB::beginTransaction();

        try{
             
            
            $monitoring_no = $request->monitoring_no;
            $list_pohon = $request->list_pohon;
            $monitoring = DB::table('monitoring_2')->where('monitoring_no','=',$monitoring_no)->first();
            
            if($monitoring){
                
                DB::table('monitoring_2_detail')->where('monitoring_no', $monitoring_no)->delete();

                
                $pohon_mpts = 0;
                $pohon_non_mpts = 0;
                $pohon_bawah = 0;

                foreach($request->list_pohon as $val){
                    Monitoring2Detail::where('monitoring_no', '=', $monitoring_no)
                    ->update([
                        'monitoring_no' => $monitoring_no,
                        'tree_code' => $val['tree_code'],
                        'qty' => $val['qty'],
                        'status' => $val['status'],
                        'condition' => $val['condition'],
                        'planting_date' => $val['planting_date'],
        
                        'created_at'=>Carbon::now(),
                        'updated_at'=>Carbon::now()
                    ]);

                    $trees_get = DB::table('trees')->where('tree_code','=',$val['tree_code'])->first();

                    if( $trees_get->tree_category == "Pohon_Buah"){
                        $pohon_mpts = $pohon_mpts + $val['qty'];
                    }else if($trees_get->tree_category == "Tanaman_Bawah_Empon"){
                        $pohon_bawah = $pohon_bawah + $val['qty'];
                    }else{
                        $pohon_non_mpts = $pohon_non_mpts + $val['qty'];
                    }
                }

                Monitoring2::where('monitoring_no', '=', $monitoring_no)
                ->update([
                    // 'form_no' => $form_no,
                    'qty_kayu' => $pohon_non_mpts,
                    'qty_mpts' => $pohon_mpts,
                    'qty_crops' => $pohon_bawah,
    
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
     *   path="/api/SoftDeleteMonitoring2",
	 *   tags={"Monitoring2"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="SoftDelete Monitoring 2",
     *   operationId="SoftDeleteMonitoring2",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="SoftDelete Monitoring 2",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="monitoring_no", type="string", example="MO1-2021-0000001"),
     *          ),
     *      )
     * )
     *
     */
    public function SoftDeleteMonitoring2(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'monitoring_no' => 'required',
            ]);

            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }  
            
            $monitoring_no = $request->monitoring_no;
            $monitoring = DB::table('monitoring_2')->where('monitoring_no','=',$monitoring_no)->first();
            
            if($monitoring){

                Monitoring2::where('monitoring_no', '=', $monitoring_no)
                ->update([    
                    'updated_at'=>Carbon::now(),    
                    'is_dell' => 1
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
    
    // END: MONITORING 2 }
    
    // Create Logs
    private function createLog($logData) {
        // get main data
        $main = DB::table('monitorings')->where('monitoring_no', $logData['monitoring_no'])->first();
        
        // get Petani Data
        if (isset($main->farmer_no)) {
            $farmer = DB::table('farmers')->where('farmer_no', $main->farmer_no)->first();
        }
        
        // get ff data
        if(isset($main->user_id)) {
            $ff = DB::table('field_facilitators')->where('ff_no', $main->user_id)->first();
        }
        // get fc data
        if (isset($ff->fc_no)) {
            $fc = DB::table('employees')->where('nik', $ff->fc_no)->first();
        }
        // user auth data
        $user = Auth::user();
        
        // set message
        $message =  $logData['status'] . ' ' . 
                    ($main->monitoring_no ?? '-') . 
                    ($logData['message'] ?? '') .
                    '[petani = ' . 
                    ($farmer->farmer_no ?? '-') . '_' . ($farmer->name ?? '-') . '_' . ($farmer->nickname ?? '-') .
                    ', ff = ' . 
                    ($ff->ff_no ?? '-') . '_' . ($ff->name ?? '-') .
                    ', fc = ' . 
                    ($fc->name ?? '-') .
                    '] ' .
                    'by ' .
                    ($user->email ?? '-');
                    
        $log = Log::channel('monitoring_1');
        
        if ($logData['status'] == 'Updated' || $logData['status'] == 'Created' || $logData['status'] == 'Verification') {
            $log->notice($message);
        } else if ($logData['status'] == 'Soft Deleted' || $logData['status'] == 'Unverification') {
            $log->warning($message);
        } else if ($logData['status'] == 'Deleted') {
            $log->alert($message);
        } else {
            $log->info($message);
        }
        
        
        
        return true;
    }
    
    public function getTypeSppt($type) {
        $res = '-';
        if ($type == 0) {
            $res = "Pribadi";
        }
        if ($type == 1) {
            $res = "Keterkaitan Keluarga";
        }
        if ($type == 2) {
            $res = "Umum";
        }
        if ($type == 3) {
            $res = "Lain-lain";
        }
        return $res;
    }
}
