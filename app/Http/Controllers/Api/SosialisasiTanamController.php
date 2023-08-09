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
use App\Farmer;
use App\Employee;
use App\FieldFacilitator;
use App\Lahan;
use App\LahanDetail;
use App\Distribution;
use App\PlantingSoc;
use App\PlantingSocializations;
use App\PlantingSocializationsDetails;
use App\PlantingSocializationsPeriod;
use App\User;
use App\SCR;

use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SosialisasiTanamController extends Controller
{
    /**
     * @SWG\Get(
     *   path="/api/GetSosisalisasiTanamAdmin",
     *   tags={"SosisalisasiTanam"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Sosisalisasi Tanam Admin",
     *   operationId="GetSosisalisasiTanamAdmin",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="typegetdata",in="query",required=true, type="string"),
     *      @SWG\Parameter(name="ff",in="query",required=true, type="string"),
     * )
     */
    public function GetSosisalisasiTanamAdmin(Request $request){
        // validation
        $validator = Validator::make($request->all(), [
            'program_year' => 'required',
            'per_page' => 'required'
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors(), $validator->errors());
            return response()->json($rslt, 400);
        } else {
            $py = $request->program_year;
            $per_page = $request->per_page;
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
        try{
            if($typegetdata == 'all' || $typegetdata == 'several'){
                $GetSosialisasiAll = PlantingSocializations::
                    leftjoin('farmers', 'farmers.farmer_no', '=', 'planting_socializations.farmer_no')
                    ->leftjoin('lahans', 'lahans.lahan_no', '=', 'planting_socializations.no_lahan')
                    ->leftjoin('users', 'users.employee_no', '=', 'planting_socializations.ff_no')
                    ->leftjoin('managementunits', 'managementunits.mu_no', '=', 'lahans.mu_no')
                    ->leftjoin('target_areas', 'target_areas.area_code', '=', 'lahans.target_area')
                    ->leftjoin('planting_period', 'planting_period.form_no', '=', 'planting_socializations.form_no')
                    // ->leftjoin('planting_details', 'planting_details.form_no', '=', 'planting_socializations.form_no')
                    ->select(
                        'planting_socializations.id',
                        'planting_socializations.no_lahan',
                        'planting_socializations.farmer_no',
                        'planting_socializations.form_no',
                        'planting_socializations.planting_year',
                        'planting_socializations.no_document',
                        'planting_socializations.ff_no',
                        'planting_socializations.validation',
                        'planting_socializations.validate_by',
                        'planting_socializations.is_dell',
                        'planting_socializations.created_at',
                        'planting_socializations.max_seed_amount',
                        'planting_socializations.training_material',
                        'planting_period.distribution_coordinates',
                        'lahans.land_area',
                        'lahans.opsi_pola_tanam',
                        'farmers.name as nama_petani',
                        'users.name as nama_ff'
                        // DB::raw('sum(planting_details.amount) as trees')
                    )
                    ->where([
                        ['planting_socializations.is_dell','=',0], 
                        'planting_socializations.planting_year' => $py
                    ]);
                
                $GetSosialisasiAll = $GetSosialisasiAll                 
                ->where('lahans.mu_no','like',$mu)
                //->where('managementunits.program_year', 'like', '%2023%')
                ->where('lahans.target_area','like',$ta)
                //->where('target_areas.program_year', 'like', '%2023%')
                ->where('lahans.village','like',$village);
                // type get data
                if($ff){
                    $ffdecode = (explode(",",$ff));

                    $GetSosialisasiAll = $GetSosialisasiAll
                    ->wherein('planting_socializations.ff_no',$ffdecode);
                }
                
                // search
                $psFields = ['no_lahan', 'form_no', 'validation'];
                $farmerFields = ['nama_petani'];
                $lahanFields = ['land_area', 'opsi_pola_tanam'];
                $ffFields = ['nama_ff'];
                if (strlen($request->search_value) >= 1) {
                    $searchTable = '';
                    if (in_array($request->search_column, $psFields)) $searchTable = 'planting_socializations.';
                    else if (in_array($request->search_column, $farmerFields)) { 
                        $searchTable = 'farmers.';
                        $request->search_column = 'name';
                    } 
                    else if (in_array($request->search_column, $lahanFields)) $searchTable = 'lahans.';
                    else if (in_array($request->search_column, $ffFields)) { 
                        $searchTable = 'users.';
                        $request->search_column = 'name';
                    } 
                    
                    if($searchTable) {
                        if ($request->search_column != 'opsi_pola_tanam') {
                            $GetSosialisasiAll = $GetSosialisasiAll
                                ->where($searchTable.$request->search_column, 'LIKE', '%' . $request->search_value . '%');
                        } else {
                            $GetSosialisasiAll = $GetSosialisasiAll
                                ->where($searchTable.$request->search_column, '=', $request->search_value);
                        }
                    }
                }
                
                // sorting data
                if ($request->sortBy == 'nama_ff') {
                    $GetSosialisasiAll = $GetSosialisasiAll
                        ->orderBy('users.name', $request->sortDesc == 'true' ? 'DESC' : 'ASC');
                } else if ($request->sortBy == 'form_no') {
                    $GetSosialisasiAll = $GetSosialisasiAll
                        ->orderBy('planting_socializations.form_no', $request->sortDesc == 'true' ? 'DESC' : 'ASC');
                } else if ($request->sortBy == 'nama_petani') {
                    $GetSosialisasiAll = $GetSosialisasiAll
                        ->orderBy('farmers.name', $request->sortDesc == 'true' ? 'DESC' : 'ASC');
                } else if ($request->sortBy == 'no_lahan') {
                    $GetSosialisasiAll = $GetSosialisasiAll
                        ->orderBy('planting_socializations.no_lahan', $request->sortDesc == 'true' ? 'DESC' : 'ASC');
                } else if ($request->sortBy == 'land_area') {
                    $GetSosialisasiAll = $GetSosialisasiAll
                        ->orderBy('lahans.land_area', $request->sortDesc == 'true' ? 'DESC' : 'ASC');
                } else if ($request->sortBy == 'opsi_pola_tanam') {
                    $GetSosialisasiAll = $GetSosialisasiAll
                        ->orderBy('lahans.opsi_pola_tanam', $request->sortDesc == 'true' ? 'DESC' : 'ASC');
                } else if ($request->sortBy == 'planting_year') {
                    $GetSosialisasiAll = $GetSosialisasiAll
                        ->orderBy('planting_socializations.planting_year', $request->sortDesc == 'true' ? 'DESC' : 'ASC');
                } else if ($request->sortBy == 'validation') {
                    $GetSosialisasiAll = $GetSosialisasiAll
                        ->orderBy('planting_socializations.validation', $request->sortDesc == 'true' ? 'DESC' : 'ASC');
                }
                
                $GetSosialisasiAll = $GetSosialisasiAll
                    ->orderBy('planting_socializations.created_at', 'desc');
                
                if ($per_page == -1 || $per_page == '-1') {
                    $GetSosialisasiAll = $GetSosialisasiAll->paginate($GetSosialisasiAll->count());
                } else {
                    $GetSosialisasiAll = $GetSosialisasiAll->paginate($per_page);
                }
                    
                
                $datas = [];
                foreach($GetSosialisasiAll as $index => $data) {
                    $trees = PlantingSocializationsDetails::where('form_no', $data->form_no)->sum('amount');
                    $data->trees_total = $trees;
                    
                    // $ff = FieldFacilitator::where('ff_no', $data->ff_no)->first()->name ?? '-';
                    // $GetSosialisasiAll[$index]->nama_ff = $ff;
                    
                    // $farmer = Farmer::where('farmer_no', $data->farmer_no)->first()->name ?? '-';
                    // $GetSosialisasiAll[$index]->nama_petani = $farmer;
                }
                $datas = $GetSosialisasiAll;
                $rslt =  $this->ResultReturn(200, 'success_'. count($datas) , $datas);
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
    // for api nursery
    public function GetSosisalisasiTanamAdminLimit(Request $request){
        // validation
        $validator = Validator::make($request->all(), [
            'program_year' => 'required',
            'limit' => 'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        } else {
            if ($request->program_year) {
                $py = preg_replace("/[^0-9]/", "", $request->program_year);
            } else $py = '2023';
            $limit = $request->limit;
            $dt = $request->distribution_time;
            $san = $request->status_approval_nursery;
            $mu_no = $request->mu_no;
        }
        
        $form_no = DB::table('planting_socializations')->where('planting_year', $py)->groupBy('ff_no')->pluck('form_no');
        $datas = DB::table('planting_socializations')
            ->select(
                'planting_socializations.id', 
                DB::raw('DATE(planting_period.distribution_time) as distribution_time'), 
                'field_facilitators.name as ff_name',
                'field_facilitators.ff_no',
                'planting_socializations.status_approval_nursery',
                'planting_socializations.created_at'
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
            $ff_farmer = DB::table('main_pivots')
                ->where([
                    'type' => 'ff_farmer',
                    'key1' => $data->ff_no,
                    ['program_year', 'LIKE', "%$py%"]
                ])->pluck('key2')->toArray();
            $farmer_lahan = DB::table('main_pivots')
                ->where([
                    'type' => 'farmer_lahan',
                    ['program_year', 'LIKE', "%$py%"]
                ])->whereIn('key1', $ff_farmer)->pluck('key2')->toArray();
            if (count($farmer_lahan) > 0) {
                $data->progress = round(count($form_no_ff) / count($farmer_lahan) * 100);
            } else $data->progress = 0;
            $data->total_wood = DB::table('planting_details')
                ->whereIn('form_no', $form_no_ff)
                ->whereIn('tree_code', $wood_seeds)
                ->sum('amount');
            $data->total_mpts = DB::table('planting_details')
                ->whereIn('form_no', $form_no_ff)
                ->whereIn('tree_code', $mpts_seeds)
                ->sum('amount');
            
            $data->total_seeds = (int)$data->total_wood + (int)$data->total_mpts;
        }
        return response()->json($datas, 200);
    }
    public function SostamDetailForNursery(Request $req) {
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
        
        $datas = DB::table('planting_period')
            ->select('distribution_time', 'distribution_coordinates', 'rec_armada as armada', 'distribution_location')
            ->whereIn('form_no', $form_no)->first();
        $sostam = DB::table('planting_socializations')->whereIn('form_no', $form_no)
            ->orderBy('created_at')->first();
        $datas->created_at = $sostam->created_at ?? '';
        $datas->status_approval_nursery = $sostam->status_approval_nursery ?? '';
        $datas->reject_description = $sostam->reject_description ?? '';
        
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
        
        $datas->seeds_detail = DB::table('planting_details')
            ->select('trees.tree_code', 'trees.tree_name', 'trees.tree_category', 'planting_details.amount')
            ->join('trees', 'trees.tree_code', 'planting_details.tree_code')
            ->whereIn('form_no', $form_no)
            ->get();
            
        $ff_farmer = DB::table('main_pivots')
            ->where([
                'type' => 'ff_farmer',
                'key1' => $ff_no,
                ['program_year', 'LIKE', "%$py%"]
            ])->pluck('key2')->toArray();
        $farmer_lahan = DB::table('main_pivots')
            ->where([
                'type' => 'farmer_lahan',
                ['program_year', 'LIKE', "%$py%"]
            ])->whereIn('key1', $ff_farmer)->pluck('key2')->toArray();
        if (count($farmer_lahan) > 0) {
            $datas->progress = round(count($form_no) / count($farmer_lahan) * 100);
        } else $datas->progress = 0;
            
        return response()->json($datas);
    }
    public function SostamEventForNursery(Request $req) {
        $validator = Validator::make($req->all(), [
            'program_year' => 'required',
            'year' => 'required',
            'month' => 'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }
        $py = $req->program_year;
        
        $datas = DB::table('planting_period')
            ->select(
                'planting_socializations.ff_no',
                'planting_period.distribution_time',
                'planting_socializations.created_at',
                'planting_socializations.status_approval_nursery'
            )
            ->join('planting_socializations', 'planting_socializations.form_no', 'planting_period.form_no')
            ->whereMonth('planting_period.distribution_time', $req->month)
            ->whereYear('planting_period.distribution_time', $req->year)
            ->orderBy('planting_socializations.created_at')
            ->groupBy('planting_socializations.ff_no')->get();
            
        return response()->json($datas, 200);
        
    }

    public function GetSosisalisasiTanamTimeAll(Request $request){
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
                    $GetSosialisasiAll = DB::table('planting_socializations')
                    ->select('planting_socializations.id','planting_socializations.no_lahan',
                    'planting_socializations.farmer_no','planting_socializations.form_no',
                    'planting_period.distribution_time','planting_period.distribution_location')
                    ->leftjoin('planting_period', 'planting_period.form_no', '=', 'planting_socializations.form_no')
                    ->where('planting_socializations.is_dell','=',0)               
                    ->orderBy('planting_period.distribution_time', 'asc')
                    ->get();
                }else{
                    $ffdecode = (explode(",",$ff));

                    $GetSosialisasiAll = DB::table('planting_socializations')
                    ->select('planting_socializations.id','planting_socializations.no_lahan',
                    'planting_socializations.farmer_no','planting_socializations.form_no',
                    'planting_period.distribution_time','planting_period.distribution_location', 'planting_period.distribution_coordinates', 'planting_period.rec_armada')
                    ->leftjoin('planting_period', 'planting_period.form_no', '=', 'planting_socializations.form_no')
                    ->where('planting_socializations.is_dell','=',0)
                    ->wherein('planting_socializations.ff_no',$ffdecode)
                    ->orderBy('planting_period.distribution_time', 'asc')
                    ->get(); 
                }

                if(count($GetSosialisasiAll)!=0){ 
                    if($typegetdata == 'all'){
                        $count = DB::table('planting_socializations')
                        ->leftjoin('planting_period', 'planting_period.form_no', '=', 'planting_socializations.form_no')
                        ->where('planting_socializations.is_dell','=',0)      
                        ->count();
                    }else{
                        $ffdecode = (explode(",",$ff));
                        
                        $count = DB::table('planting_socializations')
                        ->leftjoin('planting_period', 'planting_period.form_no', '=', 'planting_socializations.form_no')
                        ->where('planting_socializations.is_dell','=',0)
                        ->wherein('planting_socializations.ff_no',$ffdecode)
                        ->count();
                    }
                    
                    $data = ['count'=>$count, 'data'=>$GetSosialisasiAll];
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
     *   path="/api/GetSosisalisasiTanamFF",
     *   tags={"SosisalisasiTanam"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Sosisalisasi FF",
     *   operationId="GetSosisalisasiTanamFF",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="ff_no",in="query", required=true, type="string"),
     * )
     */
    public function GetSosisalisasiTanamFF(Request $request){
        $ff_no = $request->ff_no;
        $getpy = $request->planting_year;
        if($getpy){$py='%'.$getpy.'%';}
        else{$py='%%';}
        // $getmu = $request->mu;
        // $getta = $request->ta;
        // $getvillage = $request->village;
        // if($getmu){$mu='%'.$getmu.'%';}
        // else{$mu='%%';}
        // if($getta){$ta='%'.$getta.'%';}
        // else{$ta='%%';}
        // if($getvillage){$village='%'.$getvillage.'%';}
        // else{$village='%%';}
        $GetSosialisasiAll = DB::table('planting_socializations')
            ->select('planting_socializations.id',
            'planting_socializations.no_lahan',
            'planting_socializations.farmer_no',
            'planting_socializations.form_no',
            'planting_socializations.planting_year',
            'planting_socializations.no_document', 
            'planting_socializations.ff_no',
            'planting_socializations.training_material',
            'planting_socializations.validation',
            'planting_socializations.validate_by',
            'planting_socializations.signature', 
            'planting_period.pembuatan_lubang_tanam as pembuatan_lubang_tanam', 
            'planting_period.distribution_time as distribution_time',
            'planting_period.distribution_location as distribution_location', 
            'planting_period.distribution_coordinates as latitude', 
            'planting_period.rec_armada as armada', 
            'planting_period.planting_time as planting_time', 
            'planting_socializations.max_seed_amount',
            'planting_socializations.is_dell', 
            'planting_socializations.created_at', 
            'farmers.name as nama_petani', 'users.name as nama_ff')
            ->leftjoin('planting_period', 'planting_period.form_no', 'planting_socializations.form_no')
            ->leftjoin('farmers', 'farmers.farmer_no', '=', 'planting_socializations.farmer_no')
            ->leftjoin('lahans', 'lahans.lahan_no', '=', 'planting_socializations.no_lahan')
            ->leftjoin('users', 'users.employee_no', '=', 'planting_socializations.ff_no')
            ->where('planting_socializations.is_dell','=',0)
            ->where('planting_socializations.ff_no','=',$ff_no)
            ->where('planting_socializations.planting_year', 'like', $py)
            ->groupBy('planting_socializations.form_no')
            ->get();

        if ($GetSosialisasiAll) {
            $sosCoordinate = [];
            foreach ($GetSosialisasiAll as $cIndex => $sosialisasi) {
                $getPeriod = DB::table('planting_period')
                    ->where([
                        'form_no' => $sosialisasi->form_no 
                    ])->first();
                $getCoor = explode(',',  $getPeriod->distribution_coordinates ?? '');
                array_push($sosCoordinate, ...$getCoor);
            }
        }
            
        if($GetSosialisasiAll){
            $sosDetails = [];
            foreach($GetSosialisasiAll as $sIndex => $sosialisasi){
                $getFF = DB::table('field_facilitators')->where('ff_no', '=', $sosialisasi->ff_no)->first();
                $getDetailSosialisasi = DB::table('planting_details')
                ->select('planting_details.id', 
                        'planting_details.form_no', 
                        'planting_details.tree_code', 
                         'planting_details.amount', 
                         'planting_details.created_at', 
                         'planting_details.updated_at', 
                         'tree_locations.tree_name as tree_name', 
                         'tree_locations.category as tree_category', 
                         'tree_locations.mu_no as mu_no')
                ->leftjoin('tree_locations', 'tree_locations.tree_code', '=', 'planting_details.tree_code')
                ->where([
                    'form_no' =>$sosialisasi->form_no,
                    'mu_no' => $getFF->mu_no
                ])->get();
                array_push($sosDetails, ...$getDetailSosialisasi);
                $GetSosialisasiAll[$sIndex]->form_no = (string)$sosialisasi->form_no;
            }
            
            // $sosPeriods = [];
            // foreach($GetSosialisasiAll as $sIndex => $sosialisasi){
            //     $getPeriodSosialisasi = PlantingSocializationsPeriod::
            //     select('id', 'form_no', 'pembuatan_lubang_tanam', 'distribution_time', 'distribution_location', 'planting_time', 'created_at', 'updated_at')
            //     ->where([
            //         'form_no' => $sosialisasi->form_no
            //     ])->get();
            //     array_push($sosPeriods, ...$getPeriodSosialisasi);
            //     $GetSosialisasiAll[$sIndex]->form_no = (string)$sosialisasi->form_no;
            // }
            
            $data = [
                'data' => $GetSosialisasiAll,
                'sosialisasi_coordinates' => $sosCoordinate,
                'sosialisasi_details' => $sosDetails,
                //'sosialisasi_period' => $sosPeriods
            ];
        }
        
        $rslt = $this->ResultReturn(200, 'success', $data);
        return response()->json($rslt, 200);

        // if(count($GetSosialisasiAll)!=0){ 
        //     $count = DB::table('planting_socializations')
        //         ->leftjoin('farmers', 'farmers.farmer_no', '=', 'planting_socializations.farmer_no')
        //         ->leftjoin('lahans', 'lahans.lahan_no', '=', 'planting_socializations.no_lahan')
        //         ->leftjoin('users', 'users.employee_no', '=', 'planting_socializations.ff_no')
        //         ->where('planting_socializations.is_dell','=',0)
        //         ->where('planting_socializations.ff_no','=',$ff_no)
        //         ->count();
            
        //     $data = ['count'=>$count, 'data'=>$GetSosialisasiAll];
        //     $rslt =  $this->ResultReturn(200, 'success', $data);
        //     return response()->json($rslt, 200); 
        // }else{
        //     $rslt =  $this->ResultReturn(404, 'doesnt match data', 'doesnt match data');
        //     return response()->json($rslt, 404);
        // }
    }

    public function ExportSostamAllSuperAdmin(Request $request)
    {
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
                $GetSosialisasiAll = DB::table('planting_socializations')
                    ->select('lahans.longitude','lahans.latitude','lahans.coordinate',
                    'lahans.land_area','lahans.planting_area','lahans.opsi_pola_tanam',
                    'kecamatans.name as nama_kec','managementunits.name as nama_mu',
                    'desas.name as namaDesa','lahans.user_id as ff_no','users.name as ff',
    
                    'planting_socializations.id','planting_socializations.no_lahan',
                    'planting_socializations.farmer_no','planting_socializations.form_no','planting_socializations.planting_year',
                    'planting_socializations.no_document', 'planting_socializations.ff_no','planting_socializations.validation','planting_socializations.validate_by',
                    'planting_socializations.is_dell', 'planting_socializations.created_at', 'farmers.name as nama_petani', 'users.name as nama_ff',
                    'planting_period.pembuatan_lubang_tanam','planting_period.distribution_time','planting_period.distribution_location','planting_period.planting_time')
                    ->leftjoin('farmers', 'farmers.farmer_no', '=', 'planting_socializations.farmer_no')
                    ->leftjoin('planting_period', 'planting_period.form_no', '=', 'planting_socializations.form_no')
                    ->leftjoin('lahans', 'lahans.lahan_no', '=', 'planting_socializations.no_lahan')
                    ->leftjoin('desas', 'desas.kode_desa', '=', 'lahans.village')
                    ->leftjoin('kecamatans', 'kecamatans.kode_kecamatan', '=', 'lahans.kecamatan')
                    ->leftjoin('managementunits', 'managementunits.mu_no', '=', 'lahans.mu_no')
                    ->leftjoin('users', 'users.employee_no', '=', 'planting_socializations.ff_no')
                    ->where('planting_socializations.is_dell','=',0);
                    
                if (isset($request->program_year)) {
                    if ($request->program_year != '') {
                        $GetSosialisasiAll = $GetSosialisasiAll->where('planting_socializations.planting_year', $request->program_year);
                    }
                }
                if($typegetdata == 'all'){
                    $GetSosialisasiAll = $GetSosialisasiAll                 
                        ->where('lahans.mu_no','like',$mu)
                        ->where('lahans.target_area','like',$ta)
                        ->where('lahans.village','like',$village)
                        ->get();

                }else{
                    $ffdecode = (explode(",",$ff));

                    $GetSosialisasiAll = $GetSosialisasiAll
                    ->wherein('planting_socializations.ff_no',$ffdecode)
                    ->get(); 

                    
                }

                $getTrees=DB::table('trees')
                        ->select('tree_name','tree_code')
                        ->get();

                $dataval = [];
                $listval=array();

                // var_dump($GetSosialisasiAll);

                foreach ($GetSosialisasiAll as $val) {
                    $status = '';
                    if($val->validation==1){
                        $status = 'Sudah Verifikasi';                    
                    }else{
                        $status = 'Belum Verifikasi';
                    }

                    // var_dump($val->ff_no);
                    $getFF=DB::table('field_facilitators')
                    ->select('fc_no')
                    ->where('ff_no', '=',$val->ff_no)
                    ->first();
                    // var_dump($getFF);
                    if($getFF){
                        $getFC=DB::table('employees')
                        ->select('name')
                        ->where('nik', '=',$getFF->fc_no)
                        ->first();
                        $nama_fc = $getFC->name;
                    }else{
                        $nama_fc = '-';
                    }
                    
                    // var_dump($getFF);

                    $lahan_details=DB::table('planting_details')
                            ->select('tree_code','amount')
                            ->where('form_no', '=',$val->form_no)
                            ->get();
                    
                    // var_dump($lahan_details);

                    $listlhndtl=array();
                    array_push($listlhndtl, 'Nilai0Array');
                    $pohon_kayu = 0;
                    $pohon_mpts = 0;
                    $pohon_crops = 0;
                    $other = 0;
                    foreach ($lahan_details as $lhndtl) {
                        array_push($listlhndtl, $lhndtl->tree_code);

                        $getTreesCode=DB::table('trees')
                            ->select('tree_category','tree_code')
                            ->where('tree_code', '=',$lhndtl->tree_code)
                            ->first();

                            
                        
                        if($getTreesCode->tree_category == 'Pohon_Kayu'){
                            $pohon_kayu = $pohon_kayu + $lhndtl->amount;
                        }else if ($getTreesCode->tree_category == 'Pohon_Buah'){
                            $pohon_mpts = $pohon_mpts + $lhndtl->amount;
                        }else if ($getTreesCode->tree_category == 'Tanaman_Bawah_Empon'){
                            $pohon_crops = $pohon_crops + $lhndtl->amount;
                        }else{
                            $other = $other + $lhndtl->amount;
                        }

                        // var_dump($pohon_kayu);
                    }

                    // print_r($listlhndtl);

                    $datavaltrees = [];
                    $listvaltrees=array();
                    foreach ($getTrees as $value) {
                        $countPohon = 0;

                        $rslt_search = array_search($value->tree_code,$listlhndtl);
                        
                        if($rslt_search){
                            // var_dump($rslt_search);
                            $getPohonFix=DB::table('planting_details')
                            ->where('form_no', '=',$val->form_no)
                            ->where('tree_code', '=',$value->tree_code)
                            ->first();
                            $countPohon = $getPohonFix->amount;
                        }else{
                            $countPohon = 0;
                        }
                        // echo '<br>';

                        array_push($listvaltrees, $countPohon);
                    }

                    // var_dump($listvaltrees);
                    

                    // var_dump($getFC->name);
                    // var_dump('test');

                    $dataval = ['form_no'=>$val->form_no,'lahanNo'=>$val->no_lahan, 'location'=>$val->latitude." ".$val->longitude, 'coordinate'=>$val->coordinate,
                    'kodePetani'=>$val->farmer_no, 'petani'=>$val->nama_petani, 'desa' => $val->namaDesa, 'user' => $val->ff, 'status' => $status,
                    'pohon_kayu' => $pohon_kayu,'pohon_mpts' => $pohon_mpts,'pohon_crops' => $pohon_crops,'land_area' => $val->land_area,'planting_area' => $val->planting_area, 
                    'ff' => $val->ff,'nama_fc_lahan' => $nama_fc,'nama_kec' => $val->nama_kec,'nama_mu' => $val->nama_mu,
                    'form_no'=>$val->form_no,'planting_year' => $val->planting_year,'pembuatan_lubang_tanam' => $this->tanggal_indo($val->pembuatan_lubang_tanam),
                    'distribution_time' => $this->tanggal_indo($val->distribution_time),'distribution_location' => $val->distribution_location,'planting_time' => $this->tanggal_indo($val->planting_time),
                    'opsi_pola_tanam'=>$val->opsi_pola_tanam,'listvaltrees' => $listvaltrees];
                    array_push($listval, $dataval);

                    // var_dump($dataval);
                }

                

                if(count($GetSosialisasiAll)!=0){ 

                    $nama_title = 'Cetak Excel Data Sosialisasi Tanam'; 
                    // print_r($listval); 

                    return view('exportSostamSuperAdmin', compact('listval', 'nama_title', 'getTrees'));
                }
                else{
                    $rslt =  $this->ResultReturn(404, 'doesnt match data', 'doesnt match data');
                    return response()->json($rslt, 404);
                } 
            }else{
                $rslt =  $this->ResultReturn(404, 'doesnt match data', 'doesnt match data');
                return response()->json($rslt, 404);
            }
        }catch(\Exception $ex){
            return response()->json($ex);
        }        
    }

    public function tanggal_indo($tanggal)
    {
        // $bulan = array (1 =>   'Januari',
        //             'Februari',
        //             'Maret',
        //             'April',
        //             'Mei',
        //             'Juni',
        //             'Juli',
        //             'Agustus',
        //             'September',
        //             'Oktober',
        //             'November',
        //             'Desember'
        //         );
        // $split = explode('-', $tanggal);

        $date = date('d F Y', strtotime($tanggal));
        return $date;
    }

    /**
     * @SWG\Get(
     *   path="/api/GetDetailSosisalisasiTanam",
     *   tags={"SosisalisasiTanam"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Detail Sosisalisasi Tanam",
     *   operationId="GetDetailSosisalisasiTanam",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="form_no",in="query",required=true, type="string"),
     * )
     */
    public function GetDetailSosisalisasiTanam(Request $request){
            // validation
            $validator = Validator::make($request->all(), [
                'form_no' => 'required'
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }

            // get sostam data
            $sostam = PlantingSocializations::where('form_no','=',$request->form_no)
                    ->first();

            if($sostam){
                // get FF data
                $field_facilitators = FieldFacilitator::where('ff_no','=',$sostam->ff_no)->first();
                
                // get Farmer Data
                $Farmer = Farmer::where('farmer_no','=',$sostam->farmer_no)->first();
                
                // get Desa data
                $Desas = Desa::where('kode_desa','=',$Farmer->village)->first();
                
                // get Lahan data
                $Lahan = Lahan::where('lahan_no','=',$sostam->no_lahan)->first();
                
                // get Planting Period
                $planting_period = PlantingSocializationsPeriod::where('form_no','=',$sostam->form_no)->first();
                
                // get Planting Detail / Trees and SUM
                $pDetails = PlantingSocializationsDetails::
                                    leftjoin('tree_locations', 'tree_locations.tree_code', '=', 'planting_details.tree_code')
                                    ->select(
                                        'tree_locations.tree_code',
                                        'tree_locations.tree_name',
                                        'tree_locations.category as tree_category',
                                        'planting_details.amount'
                                    )
                                    ->where('planting_details.form_no','=',$sostam->form_no)
                                    ->where('tree_locations.mu_no', $field_facilitators->mu_no);
                                    
                $planting_details = $pDetails->get();
                $planting_details_sum = $pDetails->sum('amount');

                // get BAG amount
                $get_amount_bag = ceil($planting_details_sum/20);
                
                $listvalpohon=$planting_details;

                $datavalbag         = [];
                $listvalbag         = array();            
                $listvalnewpohon    = array();
                $datavalnewpohon    = [];   
                $newlist            = 'no'; 
                if ($get_amount_bag <= 1000) {
                    for ($x = 1; $x <= $get_amount_bag; $x++) {
                        $listvaltemp        = array();                       
                        $datavaltemp        = [];                 
                        $jumlah_new_pohon   = 0;
                        $jumlah_temp_detail = 0;
                        $jumlah_batas       = 20;
                        if($newlist == 'yes'){                        
                            $listvalpohon = $listvalnewpohon;        
                            $listvalnewpohon=array();
                            $datavalnewpohon = []; 
                        }
                        $newlist = 'no';    
                        foreach($listvalpohon as  $valdetail){
    
                            // fixing tree name
                            $new_name = $valdetail['tree_name'];
                            if (strripos($valdetail['tree_name'], "Crops") !== false) {
                                $new_name = substr($valdetail['tree_name'],0,-8);
                            }
                            $jumlah_new_pohon = $valdetail['amount'];
    
                            if($newlist == 'no'){
                                if($valdetail['amount'] < $jumlah_batas){
                                    $datavaltemp = ['pohon' => $new_name,'amount' => $valdetail['amount']];
                                    array_push($listvaltemp, $datavaltemp);
                                    $jumlah_temp_detail = $jumlah_temp_detail + $valdetail['amount'];
                                    $jumlah_batas = 20 - $jumlah_temp_detail;
                                }else{
                                    $datavaltemp = ['pohon' => $new_name,'amount' => $jumlah_batas];
                                    array_push($listvaltemp, $datavaltemp);
                                    $jumlah_new_pohon = $valdetail['amount'] - $jumlah_batas;
                                    $newlist = 'yes';
                                }
                            }
                            
                            if($newlist == 'yes'){
                                $datavalnewpohon = ['tree_name' => $new_name, 'tree_code' => $valdetail['tree_code'], 'amount' => $jumlah_new_pohon];
                                array_push($listvalnewpohon, $datavalnewpohon); 
                            }
                        }
                            
                        $datavalbag = ['no_bag'=>$x.'/'.$get_amount_bag, 'listvaltemp'=>$listvaltemp];
                        array_push($listvalbag, $datavalbag);
                    }
                }
                // $rslt =  $this->ResultReturn(500, 'check', 'Sampai sini');
                // return response()->json($rslt, 500);
                        
                // get validate name
                $validate_name = '-';
                if($sostam->validate_by != '-'){
                    $employees = DB::table('employees')->where('nik','=',$sostam->validate_by)->first();
                    $validate_name = $employees->name;
                }

                // tipe sppt lahan
                $type_sppt = "Sendiri";
                if($Lahan->type_sppt == 0){
                    $type_sppt = "Sendiri";
                }elseif($Lahan->type_sppt == 1){
                    $type_sppt = "Keterkaitan Keluarga";
                }elseif($Lahan->type_sppt == 2){
                    $type_sppt = "Umum";
                }else{
                    $type_sppt = "Lain-lain";
                }

                // set alamat
                $alamat = ($Desas->name ?? '-') .' ('. ($Farmer->address ?? '-') .')';
                
                if ($planting_period->distribution_time == '0000-00-00 00:00:00') {
                    $planting_period->distribution_time = '2023-01-31 00:00:00';
                }

                $SosialisasiDetail = [
                    'id'=>$sostam->id, 
                    'form_no'=>$sostam->form_no,
                    'signature'=>$sostam->signature,
                    'planting_year'=>$sostam->planting_year,
                    'validation'=>$sostam->validation,
                    'validate_by'=>$sostam->validate_by, 
                    'validate_name'=>$validate_name, 
                    'ff_no'=>$sostam->ff_no,
                    'mu_no'=>$field_facilitators->mu_no,
                    'ff_name'=>$sostam->name,
                    'kode'=>$sostam->farmer_no,
                    'farmer_no'=>$sostam->farmer_no,
                    'nama_petani'=>$Farmer->name,
                    'ktp_no'=>$Farmer->ktp_no,
                    'alamat'=>$alamat,
                    'no_lahan'=>$sostam->no_lahan,
                    'max_seed_amount'=>$sostam->max_seed_amount,
                    'opsi_pola_tanam'=>$Lahan->opsi_pola_tanam,
                    'document_no'=>$Lahan->document_no,
                    'type_sppt'=>$type_sppt,
                    'luas_lahan'=>$Lahan->land_area,
                    'luas_tanam'=>$Lahan->planting_area, 
                    'tutupan_lahan'=>$Lahan->tutupan_lahan, 
                    'current_crops'=>$Lahan->current_crops,
                    'pembuatan_lubang_tanam'=>$planting_period->pembuatan_lubang_tanam,
                    'distribution_time'=>$planting_period->distribution_time,
                    'planting_time'=>$planting_period->planting_time,
                    'distribution_location'=>$planting_period->distribution_location,
                    'distribution_coordinates'=>$planting_period->distribution_coordinates,
                    'rec_armada'=>$planting_period->rec_armada,
                    'planting_details'=>$planting_details,
                    'planting_details_sum'=>$planting_details_sum,
                    'get_amount_bag'=>$get_amount_bag,
                    'listvalbag'=>$listvalbag
                ];
                
                $rslt =  $this->ResultReturn(200, 'success', $SosialisasiDetail);
                return response()->json($rslt, 200); 
            }else{
                $rslt =  $this->ResultReturn(404, 'doesnt match data', 'doesnt match data');
                return response()->json($rslt, 404);
            }
    }

    /**
     * @SWG\Get(
     *   path="/api/GetDetailSosisalisasiTanamFFNo",
     *   tags={"SosisalisasiTanam"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Detail Sosisalisasi Tanam FFNo",
     *   operationId="GetDetailSosisalisasiTanamFFNo",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="ff_no",in="query",required=true, type="string"),
     * )
     */
    public function GetDetailSosisalisasiTanamFFNo(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'ff_no' => 'required'
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }

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
                    ->where('planting_socializations.ff_no','=',$request->ff_no)
                    ->where('planting_socializations.is_dell','=',0)
                    ->first();

            if($GetSosialisasiDetail){                
                $planting_period = DB::table('planting_period')
                                    ->join('planting_socializations', 'planting_socializations.form_no', '=', 'planting_period.form_no')
                                    ->where('planting_socializations.ff_no','=',$request->ff_no)
                                    ->where('planting_socializations.is_dell','=',0)
                                    ->get();
                $planting_details = DB::table('planting_details')
                                    ->select('planting_details.id','planting_details.form_no','planting_details.tree_code',
                                    'planting_details.amount','trees.tree_name','trees.tree_category')
                                    ->join('trees', 'trees.tree_code', '=', 'planting_details.tree_code')
                                    ->join('planting_socializations', 'planting_socializations.form_no', '=', 'planting_details.form_no')
                                    ->where('planting_socializations.ff_no','=',$request->ff_no)
                                    ->where('planting_socializations.is_dell','=',0)
                                    ->get();
                
                $data = ['planting_period'=>$planting_period, 'planting_details'=>$planting_details];
                
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

    public function CetakLabelSosTam(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'form_no' => 'required'
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }

            $GetSosialisasiDetail = DB::table('planting_socializations')
                    ->select('planting_socializations.id','planting_socializations.no_lahan',
                    'planting_socializations.farmer_no','planting_socializations.form_no','planting_socializations.planting_year',
                    'planting_socializations.no_document', 'planting_socializations.ff_no','planting_socializations.validation','planting_socializations.validate_by',
                    'planting_socializations.is_dell', 'planting_socializations.created_at')
                    ->where('planting_socializations.form_no','=',$request->form_no)
                    ->first();

            if($GetSosialisasiDetail){
                $field_facilitators = DB::table('field_facilitators')->where('ff_no','=',$GetSosialisasiDetail->ff_no)->first();
                $Farmer = DB::table('farmers')->where('farmer_no','=',$GetSosialisasiDetail->farmer_no)->first();
                $Desas = DB::table('desas')->where('kode_desa','=',$Farmer->village)->first();
                $Lahan = DB::table('lahans')->where('lahan_no','=',$GetSosialisasiDetail->no_lahan)->first();
                
                $planting_period = DB::table('planting_period')->where('form_no','=',$GetSosialisasiDetail->form_no)->first();
                $planting_details = DB::table('planting_details')
                                    ->select('planting_details.id','planting_details.form_no','planting_details.tree_code',
                                    'planting_details.amount','trees.tree_name','trees.tree_category')
                                    ->join('trees', 'trees.tree_code', '=', 'planting_details.tree_code')
                                    ->where('form_no','=',$GetSosialisasiDetail->form_no)
                                    ->get();

                $planting_details_sum = DB::table('planting_details')
                                    // ->select(DB::raw('SUM(planting_details.amount) As total'))
                                    ->join('trees', 'trees.tree_code', '=', 'planting_details.tree_code')
                                    ->where('planting_details.form_no','=',$GetSosialisasiDetail->form_no)
                                    ->sum('planting_details.amount');

                $get_amount_bag = ceil($planting_details_sum/20);
                                    
                $datavalpohon = [];
                $listvalpohon=array();
                foreach($planting_details as  $valpohon){                        
                    $datavalpohon = ['tree_name' => $valpohon->tree_name, 'tree_code' => $valpohon->tree_code, 'amount' => $valpohon->amount];
                    array_push($listvalpohon, $datavalpohon);                    
                }

                $datavalbag = [];
                $listvalbag=array();            
                $listvalnewpohon=array();
                $datavalnewpohon = [];   
                $newlist = 'no'; 
                for ($x = 1; $x <= $get_amount_bag; $x++) {
                    $listvaltemp=array();                       
                    $datavaltemp = [];                 
                    $jumlah_new_pohon = 0;
                    $jumlah_temp_detail = 0;
                    $jumlah_batas = 20;
                    if($newlist == 'yes'){                        
                        $listvalpohon = $listvalnewpohon;        
                        $listvalnewpohon=array();
                        $datavalnewpohon = []; 
                    }
                    $newlist = 'no';
                    $nn = 0;    
                    foreach($listvalpohon as  $valdetail){

                        $new_name = $valdetail['tree_name'];
                        if (strripos($valdetail['tree_name'], "Crops") !== false) {
                            $new_name = substr($valdetail['tree_name'],0,-8);
                        }
                        
                        $jumlah_new_pohon = $valdetail['amount'];

                        if($newlist == 'no'){
                            if($valdetail['amount'] < $jumlah_batas){
                                $datavaltemp = ['pohon' => $new_name,'amount' => $valdetail['amount']];
                                array_push($listvaltemp, $datavaltemp);
                                $jumlah_temp_detail = $jumlah_temp_detail + $valdetail['amount'];
                                $jumlah_batas = 20 - $jumlah_temp_detail;
                            }else{
                                $datavaltemp = ['pohon' => $new_name,'amount' => $jumlah_batas];
                                array_push($listvaltemp, $datavaltemp);
                                $jumlah_new_pohon = $valdetail['amount'] - $jumlah_batas;
                                $newlist = 'yes';
                            }
                            $nn = $nn + 1;
                        }
                        
                        
                        if($newlist == 'yes'){
                            $datavalnewpohon = ['tree_name' => $new_name, 'tree_code' => $valdetail['tree_code'], 'amount' => $jumlah_new_pohon];
                            array_push($listvalnewpohon, $datavalnewpohon); 
                        }
                                               
                    }

                    $now = Carbon::now();
                    $yearnow = now()->year;
                    $yeardigit = substr($yearnow,-2);
                    $no_lahan_qr = substr($GetSosialisasiDetail->no_lahan,2);
                    $no_qr_code = $x.'_'.$yeardigit.$no_lahan_qr;
                    $qrcodelahan = $this->generateqrcode($no_qr_code);

                    $n = 5-$nn;

                    $datavalbag = ['no_bag'=>$x.'/'.$get_amount_bag, 'listvaltemp'=>$listvaltemp, 'qrcodelahan'=>$qrcodelahan, 'n'=>$n];
                    array_push($listvalbag, $datavalbag);
                }


                $validate_name = '-';
                if($GetSosialisasiDetail->validate_by != '-'){
                    $employees = DB::table('employees')->where('nik','=',$GetSosialisasiDetail->validate_by)->first();
                    $validate_name = $employees->name;
                }

                $type_sppt = "Sendiri";
                if($Lahan->type_sppt == 0){
                    $type_sppt = "Sendiri";
                }elseif($Lahan->type_sppt == 1){
                    $type_sppt = "Keterkaitan Keluarga";
                }elseif($Lahan->type_sppt == 2){
                    $type_sppt = "Umum";
                }else{
                    $type_sppt = "Lain-lain";
                }

                // var_dump($type_sppt);
                $alamat = $Desas->name.' ('.$Farmer->address .')';

                
                $newDateformatdistribution = date("d/m/Y", strtotime($planting_period->distribution_time));

                $countnama = count(explode(" ",$Farmer->name));
                // var_dump($alamat);

                $SosialisasiDetail = ['id'=>$GetSosialisasiDetail->id, 'form_no'=>$GetSosialisasiDetail->form_no,
                'planting_year'=>$GetSosialisasiDetail->planting_year,'validation'=>$GetSosialisasiDetail->validation,
                'validate_by'=>$GetSosialisasiDetail->validate_by, 'validate_name'=>$validate_name, 
                'ff_no'=>$GetSosialisasiDetail->ff_no,'ff_name'=>$field_facilitators->name,
                'kode'=>$GetSosialisasiDetail->farmer_no,'farmer_no'=>$GetSosialisasiDetail->farmer_no,'nama_petani'=>$Farmer->name,'ktp_no'=>$Farmer->ktp_no,'alamat'=>$alamat,
                'no_lahan'=>$GetSosialisasiDetail->no_lahan,'opsi_pola_tanam'=>$Lahan->opsi_pola_tanam,'document_no'=>$Lahan->document_no,'type_sppt'=>$type_sppt,'luas_lahan'=>$Lahan->land_area,'luas_tanam'=>$Lahan->planting_area, 'current_crops'=>$Lahan->current_crops,
                'pembuatan_lubang_tanam'=>$planting_period->pembuatan_lubang_tanam,'distribution_time'=>$planting_period->distribution_time,
                'planting_time'=>$planting_period->planting_time,'distribution_location'=>$planting_period->distribution_location,
                'planting_details'=>$planting_details,'planting_details_sum'=>$planting_details_sum,'get_amount_bag'=>$get_amount_bag,
                'listvalbag'=>$listvalbag,'newDateformatdistribution'=>$newDateformatdistribution,'countnama'=>$countnama];
                
                return view('cetakLabelSostam', compact('SosialisasiDetail','listvalbag'));
            }else{
                $rslt =  $this->ResultReturn(404, 'doesnt match data', 'doesnt match data');
                return response()->json($rslt, 404);
            }
            // var_dump(count($GetLahanNotComplete));
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }

    public function generateqrcode ($val)
    {
        // $data = Data::findOrFail($id);
        $qrcode = QrCode::size(90)->generate($val);
        return $qrcode;
        // return view('qrcode',compact('qrcode'));
    }

    /**
     * @SWG\Post(
     *   path="/api/AddSosisalisasiTanam",
	 *   tags={"SosisalisasiTanam"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Add SosisalisasiTanam",
     *   operationId="AddSosisalisasiTanam",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Add SosisalisasiTanam",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="ff_no", type="string", example="FF0001"),
     *              @SWG\Property(property="farmer_no", type="string", example="F00000001"),
     *              @SWG\Property(property="no_lahan", type="string", example="L0000001"),
     *              @SWG\Property(property="planting_year", type="string", example="2021"),
     *              @SWG\Property(property="pembuatan_lubang_tanam", type="string", example="2021-10-22 10:00:00"),
     *              @SWG\Property(property="distribution_time", type="string", example="2021-10-22 10:00:00"),
     *              @SWG\Property(property="planting_time", type="string", example="2021-10-22 10:00:00"),
     *              @SWG\Property(property="distribution_location", type="string", example="Kebon"),
     *          ),
     *      )
     * )
     *
     */
    public function AddSosisalisasiTanam(Request $request){
        $validator = Validator::make($request->all(), [
            'ff_no' => 'required',
            'farmer_no' => 'required', 
            'no_lahan' => 'required|unique:planting_socializations', 
            'planting_year' => 'required',
            'pembuatan_lubang_tanam' => 'required',
            'distribution_time' => 'required',
            'planting_time' => 'required',
            'distribution_location' => 'required'
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        } 

        DB::beginTransaction();

        try{             
            
            $Lahan = DB::table('lahans')->where('lahan_no','=',$request->no_lahan)->first();
            
            if($Lahan){
                $year = Carbon::now()->format('Y');
                $form_no = 'SO-'.$year.'-'.substr($request->no_lahan,-10);
                $signature = $this->ReplaceNull($request->signature, 'string');
                $validation = 0;
                $validate_by = '-';
                if($request->validate_by){
                    $validation = 1;
                    $validate_by = $request->validate_by;
                }

                PlantingSocializations::create([
                    'form_no' => $form_no,
                    'planting_year' => $request->planting_year,
                    'farmer_no' => $request->farmer_no,
                    'no_lahan' => $request->no_lahan,
                    'no_document' => $Lahan->document_no,
                    'signature' => $signature,
                    'validation' => $validation,
                    'validate_by' => $validate_by,
    
                    'ff_no' => $request->ff_no,
                    'training_material' => $request->training_material,
                    'attendance'=> $request->attendance,
    
                    'created_at'=>Carbon::now(),
                    'updated_at'=>Carbon::now(),
    
                    'is_dell' => 0
                ]);
    
                PlantingSocializationsPeriod::create([
                    'form_no' => $form_no,
                    'pembuatan_lubang_tanam' => $request->pembuatan_lubang_tanam,
                    'distribution_time' => $request->distribution_time,
                    'distribution_location' => $request->distribution_location,
                    'planting_time' => $request->planting_time,
                    'distribution_coordinates' => $request->distribution_coordinates,
                    'rec_armada' => $request->distribution_rec_armada,
    
                    'created_at'=>Carbon::now(),
                    'updated_at'=>Carbon::now(),
                ]);

                $LahanDetails = DB::table('lahan_details')->where('lahan_no','=',$request->no_lahan)->get();
    
                foreach($LahanDetails as $val){
                    PlantingSocializationsDetails::create([
                        'form_no' => $form_no,
                        'tree_code' => $val->tree_code,
                        'amount' => $val->amount,
        
                        'created_at'=>Carbon::now(),
                        'updated_at'=>Carbon::now()
                    ]);
                }

                DB::commit();
                
                // creat logs
                $this->createLogs([
                    'status' => 'Created',
                    'form_no' => $form_no
                ]);
    
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
     *   path="/api/UpdateSosisalisasiTanam",
	 *   tags={"SosisalisasiTanam"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Update SosisalisasiTanam",
     *   operationId="UpdateSosisalisasiTanam",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Update SosisalisasiTanam",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="form_no", type="string", example="SO-2021-0000001"),
     *              @SWG\Property(property="ff_no", type="string", example="FF0001"),
     *              @SWG\Property(property="farmer_no", type="string", example="F00000001"),
     *              @SWG\Property(property="no_lahan", type="string", example="L0000001"),
     *              @SWG\Property(property="planting_year", type="string", example="2021"),
     *              @SWG\Property(property="pembuatan_lubang_tanam", type="string", example="2021-10-22 10:00:00"),
     *              @SWG\Property(property="distribution_time", type="string", example="2021-10-22 10:00:00"),
     *              @SWG\Property(property="planting_time", type="string", example="2021-10-22 10:00:00"),
     *              @SWG\Property(property="distribution_location", type="string", example="Kebon"),
     *          ),
     *      )
     * )
     *
     */
    public function UpdateSosisalisasiTanam(Request $request){
        $validator = Validator::make($request->all(), [
            'form_no' => 'required',
            'ff_no' => 'required',
            'farmer_no' => 'required', 
            'no_lahan' => 'required', 
            'planting_year' => 'required',
            'signature' => 'max:255',
            'pembuatan_lubang_tanam' => 'required',
            'distribution_time' => 'required',
            'planting_time' => 'required',
            'distribution_location' => 'required'
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        }  
        
        DB::beginTransaction();

        try{
            
            
            $form_no_old = $request->form_no;
            $Lahan = DB::table('lahans')->where('lahan_no','=',$request->no_lahan)->first();
            $planting_socializations = DB::table('planting_socializations')->where('form_no','=',$form_no_old)->first();
            
            if($planting_socializations){
                
                $validation = 0;
                $validate_by = '-';
                if($request->validate_by){
                    $validation = 1;
                    $validate_by = $request->validate_by;
                }

                PlantingSocializations::where('form_no', '=', $form_no_old)
                ->update([
                    'planting_year' => $request->planting_year,
                    'farmer_no' => $request->farmer_no,
                    'no_lahan' => $request->no_lahan,
                    'no_document' => $Lahan->document_no,
                    'signature' => $request->signature,
                    'validation' => $validation,
                    'validate_by' => $validate_by,
    
                    'ff_no' => $request->ff_no,
                    'training_material' => $request->training_material,
                    'attendance'=> $request->attendance,
                    
                    'updated_at'=>Carbon::now(),
    
                    // 'is_dell' => 0
                ]);
    
                PlantingSocializationsPeriod::where('form_no', '=', $form_no_old)
                ->update([
                    'pembuatan_lubang_tanam' => $request->pembuatan_lubang_tanam,
                    'distribution_time' => $request->distribution_time,
                    'distribution_location' => $request->distribution_location,
                    'planting_time' => $request->planting_time,
                    'distribution_coordinates' => $request->distribution_coordinates,
                    'rec_armada' => $request->distribution_rec_armada,
                    
                    
                    'updated_at'=>Carbon::now(),
                ]);
                
                $status = 'Updated [' . implode(',', $request->all()) . '] in';
            
                // creat logs
                $this->createLogs([
                    'status' => $status,
                    'form_no' => $form_no_old
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
    
    // Edit Sostam Period
    public function UpdateSosialisasiTanamPeriod(Request $req) {
        $validator = Validator::make($req->all(), [
            'program_year' => 'required',
            'ff_no' => 'required',
            'distribution_time' => 'required',
            'distribution_location' => 'required',
            'planting_time' => 'required',
            'pembuatan_lubang_tanam' => 'required'
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        } 
        
        $ps = PlantingSocializations::where([
                    'ff_no' => $req->ff_no,
                    'planting_year' => $req->program_year
                ])
            ->get();
            
         foreach ($ps as $sostam) {
            $updatePeriod = PlantingSocializationsPeriod::where('form_no', $sostam->form_no)->update([
                    'distribution_time' => $req->distribution_time,
                    'distribution_location' => $req->distribution_location,
                    'planting_time' => $req->planting_time,
                    'pembuatan_lubang_tanam' => $req->pembuatan_lubang_tanam,
                    'distribution_coordinates' => $request->distribution_coordinates,
                    'rec_armada' => $request->distribution_rec_armada,
                 ]);
                 
            // update if aleady have distribution
            $distribution = Distribution::where([
                    'farmer_no' => $sostam->farmer_no,
                    ['distribution_no', 'LIKE', "D-$req->program_year-%"],
                ])->first();
            if ($distribution) {Distribution::where([
                    'farmer_no' => $sostam->farmer_no,
                    ['distribution_no', 'LIKE', "D-$req->program_year-%"],
                ])->update(['distribution_date' => $req->distribution_time]);}
            // update if have request seedling change
            SCR::where(['farmer_no' => $sostam->farmer_no, 'program_year' => $req->program_year])->update(['distribution_date' => $req->distribution_time]);
            
            $status = 'Updated Period [' . implode(',', [
                    'distribution_time' => $req->distribution_time,
                    'distribution_location' => $req->distribution_location,
                ]) . '] in';
            
            // creat logs
            $this->createLogs([
                'status' => $status,
                'form_no' => $sostam->form_no
            ]);
         } 
                
        if ($ps) {
                
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200); 
            
        } else{
            $rslt =  $this->ResultReturn(400, 'doesnt match data', 'doesnt match data');
            return response()->json($rslt, 400);
        }
    }

    /**
     * @SWG\Post(
     *   path="/api/UpdatePohonSosisalisasiTanam",
	 *   tags={"SosisalisasiTanam"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Update Pohon SosisalisasiTanam",
     *   operationId="UpdatePohonSosisalisasiTanam",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Update Pohon SosisalisasiTanam",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="form_no", type="string", example="SO-2021-0000001"),
     *              @SWG\Property(property="list_pohon", type="string", example="array pohon bosku"),
     *          ),
     *      )
     * )
     *
     */
    public function UpdatePohonSosisalisasiTanam(Request $request){
        $validator = Validator::make($request->all(), [
            'form_no' => 'required',
            'list_pohon' => 'required',
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        } 

        DB::beginTransaction();

        try{
             
            
            $form_no_old = $request->form_no;
            $list_pohon = $request->list_pohon;
            $planting_socializations = PlantingSocializations::where('form_no','=',$form_no_old)->first();
            
            if (isset($request->max_seed_amount)) {
                if ($request->max_seed_amount != '' && $request->max_seed_amount != null && $request->max_seed_amount != 'null') {
                    $planting_socializations->update(['max_seed_amount' => $request->max_seed_amount]);
                }
            }
            
            if($planting_socializations){
                
                DB::table('planting_details')->where('form_no', $form_no_old)->delete();

                foreach($list_pohon as $val){
                    PlantingSocializationsDetails::create([
                        'form_no' => $form_no_old,
                        'tree_code' => $val['tree_code'],
                        'amount' => $val['amount'],
        
                        'created_at'=>Carbon::now(),
                        'updated_at'=>Carbon::now()
                    ]);
                    
                    $status = 'Trees Updated [' . implode(',', [$val['tree_name'], $val['amount']]) . '] in';
            
                    // creat logs
                    $this->createLogs([
                        'status' => $status,
                        'form_no' => $form_no_old
                    ]);
                }

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
     *   path="/api/SoftDeleteSosisalisasiTanam",
	 *   tags={"SosisalisasiTanam"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="SoftDelete SosisalisasiTanam",
     *   operationId="SoftDeleteSosisalisasiTanam",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="SoftDelete SosisalisasiTanam",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="form_no", type="string", example="SO-2021-0000001"),
     *          ),
     *      )
     * )
     *
     */
    public function SoftDeleteSosisalisasiTanam(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'form_no' => 'required',
            ]);

            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }  
            
            $form_no_old = $request->form_no;
            $planting_socializations = DB::table('planting_socializations')->where('form_no','=',$form_no_old)->first();
            
            if($planting_socializations){

                PlantingSocializations::where('form_no', '=', $form_no_old)
                ->update([    
                    'updated_at'=>Carbon::now(),    
                    'is_dell' => 1
                ]);
                
                // creat logs
                $this->createLogs([
                    'status' => 'Soft Deleted',
                    'form_no' => $form_no_old
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
     *   path="/api/ValidateSosisalisasiTanam",
	 *   tags={"SosisalisasiTanam"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Validate SosisalisasiTanam",
     *   operationId="ValidateSosisalisasiTanam",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Validate SosisalisasiTanam",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="form_no", type="string", example="SO-2021-0000001"),
     *              @SWG\Property(property="validate_by", type="string", example="00-11010"),
     *          ),
     *      )
     * )
     *
     */
    public function ValidateSosisalisasiTanam(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'form_no' => 'required',
                'validate_by' => 'required',
                'max_seed_amount' => 'required'
            ]);

            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }  
            
            $form_no_old = $request->form_no;
            $planting_socializations = DB::table('planting_socializations')->where('form_no','=',$form_no_old)->first();
            
            if($planting_socializations){

                PlantingSocializations::where('form_no', '=', $form_no_old)
                ->update([    
                    'updated_at'=>Carbon::now(),
                    'max_seed_amount' => $request->max_seed_amount,
                    'validate_by' => $request->validate_by,    
                    'validation' => 1
                ]);
                
                // creat logs
                $this->createLogs([
                    'status' => 'Verified',
                    'form_no' => $form_no_old
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
    
    public function UnverificationSosialisasiTanam(Request $request){
        $validator = Validator::make($request->all(), [
            'form_no' => 'required',
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        }  
        
        $form_no_old = $request->form_no;
        $planting_socializations = DB::table('planting_socializations')->where('form_no','=',$form_no_old)->first();
        
        if($planting_socializations){

            PlantingSocializations::where('form_no', '=', $form_no_old)
            ->update([    
                'updated_at'=>Carbon::now(),
                'validate_by' => '-',    
                'validation' => 0
            ]);
            
            // creat logs
            $this->createLogs([
                'status' => 'Unverified',
                'form_no' => $form_no_old
            ]);

            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200); 
        }else{
            $rslt =  $this->ResultReturn(400, 'doesnt match data', 'doesnt match data');
            return response()->json($rslt, 400);
        }
    }
    
    public function GetOpsiPolaTanamOptions() {
        $datas = Lahan::where('is_dell', 0)->where('opsi_pola_tanam', '!=', '-')->groupBy('opsi_pola_tanam')->pluck('opsi_pola_tanam');
        $rslt =  $this->ResultReturn(200, 'success', $datas);
        return response()->json($rslt, 200);
    }
    
    public function createLogSostamCheck(Request $req) {
        $datas = $this->createLogs([
                'status' => $req->status,
                'form_no' => $req->form_no
            ]);  
        
        $rslt = $this->ResultReturn(200, 'success', $datas);
        return response()->json($rslt, 200);
    }
    
    public function createSostamByFF(Request $req) {
        // validation
        $validator = Validator::make($req->all(), [
            'ff_no' => 'required|exists:field_facilitators,ff_no',
            'program_year' => 'required',
            'lahans' => 'required|array',
            'distribution_time' => 'required',
            'penlub_time' => 'required',
            'planting_time' => 'required',
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors(), $validator->errors());
            return response()->json($rslt, 400);
        } else {
            $ff = FieldFacilitator::where('ff_no', $req->ff_no)->first();
            $py = $req->program_year;
            
            // check response data
            // $rslt =  $this->ResultReturn(200, 'success', $req->all());
            // return response()->json($rslt, 200);
        }     
        
        // check sostam exist in ff
        $existSostam = PlantingSocializations::where(['ff_no' => $ff->ff_no, 'planting_year' => $py, 'is_dell' => 0])->orderBy('created_at', 'desc')->first();
        if ($existSostam) {
            if (isset($existSostam->form_no)) {
                $existsPeriod = PlantingSocializationsPeriod::where('form_no', $existSostam->form_no)->first();
                if ($existsPeriod) {
                    // set period
                    $req->penlub_time = $existsPeriod->pembuatan_lubang_tanam ?? $req->penlub_time;
                    $req->distribution_time = $existsPeriod->distribution_time ?? $req->distribution_time;
                    $req->distribution_location = $existsPeriod->distribution_location ?? $req->distribution_location;
                    $req->planting_time = $existsPeriod->planting_time ?? $req->planting_time;
                    $req->distribution_rec_armada = $existsPeriod->rec_armada ?? $req->distribution_rec_armada;
                    $req->distribution_coordinates = $existsPeriod->distribution_coordinates ?? $req->distribution_coordinates;
                }
            }
        }
        
        // set variable total
        $totalSuccess = [
                'data' => 0,
                'seeds' => 0,
                'period' => 0
            ];
        $totalFailed = [
                'data' => 0,
                'seeds' => 0,
                'period' => 0
            ];
            
        $soc_no = 'SO-'.$py.'-'.$ff;
        
        $socialization = new PlantingSoc();
        $socialization->soc_no = $soc_no;
        $socialization->ff_no = $ff->ff_no;
        $socialization->soc_date = Carbon::now();
        $socialization->program_year = $py;
        $socialization->absent = $req->absent;
        $socialization->save();
        
        // lahans loop
        foreach($req->lahans as $lahan) {
            $form_no = 'SO-'.$py.'-'.substr($lahan['lahan_no'],-10);
            
    
            PlantingSocializations::where('form_no', $form_no)->delete();
            PlantingSocializationsPeriod::where('form_no', $form_no)->delete();
            PlantingSocializationsDetails::where('form_no', $form_no)->delete();
            
            // create sostam data
            $createSostam = PlantingSocializations::create([
                    'form_no' => $form_no,
                    'soc_no' => $socialization->soc_no,
                    'planting_year' => $py,
                    'farmer_no' => $lahan['farmer_no'],
                    'no_lahan' => $lahan['lahan_no'],
                    'no_document' => $lahan['document_no'],
                    'max_seed_amount' => $lahan['max_seed_amount'],
                    'attendance' => $lahan['attendance'],
                    'validation' => 0,
                    'validate_by' => '-',
                        
                    'ff_no' => $ff->ff_no,
                ]);
            
            // $sostam = PlantingSocializations::where('form_no', $form_no)->first();
            
            // if($sostam->attendance = 1){
            //     PlantingSocializations::where('form_no', $form_no)->update([
            //         $sostam->status = 'ready',
            //     ]);
            // }
                
            if ($createSostam) {
                $totalSuccess['data'] += 1;
                
                
                // creat logs
                $this->createLogs([
                    'status' => 'Created',
                    'form_no' => $form_no
                ]);
            }
            else $totalFailed['data'] += 1;
            $createSostamPeriod = PlantingSocializationsPeriod::create([
                'form_no' => $form_no,
                'pembuatan_lubang_tanam' => $req->penlub_time,
                'distribution_time' => $req->distribution_time,
                'distribution_location' => $req->distribution_location,
                'planting_time' => $req->planting_time,
                'distribution_coordinates' => $req->distribution_coordinates,
                'rec_armada' => $req->distribution_rec_armada,
                'start_pembuatan_lubang_tanam' => $req->start_pembuatan_lubang_tanam,
                'end_planting_time' => $req->end_planting_time,
            ]);
            
            if ($createSostamPeriod) $totalSuccess['period'] += 1;
            else $totalFailed['period'] += 1;
    
            foreach($lahan['trees'] as $seed) {
                $createSostamSeed = PlantingSocializationsDetails::create([
                    'form_no' => $form_no,
                    'tree_code' => $seed['tree_code'],
                    'amount' => $seed['amount'],
                    'planting_year' => $py,
                ]);
                
                // counting sostam seed
                if ($createSostamSeed) $totalSuccess['seeds'] += 1;
                else $totalFailed['seeds'] += 1;
            }
        } 
        // set response datas
        $datas = [
            'created' => $totalSuccess,
            'failed' => $totalFailed
            ];
        $rslt =  $this->ResultReturn(200, 'success', $datas);
        return response()->json($rslt, 200);
    }
    
    public function getFFLahanSostam(Request $req) {
        // validation
        $validator = Validator::make($req->all(), [
            'ff_no' => 'required|exists:field_facilitators,ff_no',
            'program_year' => 'required'
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors(), $validator->errors());
            return response()->json($rslt, 400);
        } else {
            $ff = FieldFacilitator::where('ff_no', $req->ff_no)->first();
            $py = $req->program_year;
        }
            
        $lands = Lahan::
            join('farmers', 'farmers.farmer_no', '=', 'lahans.farmer_no')
            ->join('main_pivots', 'lahans.lahan_no', '=', 'main_pivots.key2')
            ->select(
                    'lahans.lahan_no',
                    'lahans.document_no',
                    'lahans.farmer_no',
                    'farmers.name as farmer_name',
                    'farmers.farmer_no',
                    'lahans.province',
                    'lahans.mu_no',
                    'lahans.target_area',
                    'lahans.village',
                    'lahans.user_id',
                    'lahans.land_area',
                    'lahans.planting_area',
                    'lahans.tutupan_lahan',
                    'lahans.coordinate',
                    'lahans.polygon',
                    'lahans.lahan_type',
                    'lahans.opsi_pola_tanam',
                    'lahans.pohon_kayu',
                    'lahans.pohon_mpts',
                    'lahans.tanaman_bawah',
                    DB::raw('count(lahans.id) as total_lahan'),
                    DB::raw('sum(lahans.pohon_kayu) as total_kayu'),
                    DB::raw('sum(lahans.pohon_mpts) as total_mpts')
                )
            ->where([
                'lahans.user_id' => $ff->ff_no, 
                'lahans.is_dell' => 0,
                'lahans.approve' => 1,
                'farmers.is_dell' => 0,
                'farmers.approve' => 1,
                ['main_pivots.program_year', 'LIKE', "%$py%"],
                'main_pivots.type' => 'farmer_lahan'
            ])
            ->orderBy('lahans.lahan_no')->groupBy('farmers.farmer_no')->get();
        $newLahan = [];
            
        foreach ($lands as $landIndex => $landVal) {
            $hasSostam = PlantingSocializations::where([
                    'no_lahan' => $landVal->lahan_no,
                    'planting_year' => $py,
                    'is_dell' => 0
                ])->count();
            if ($hasSostam == 0) {
                $landDetails = LahanDetail::
                    leftJoin('tree_locations', 'tree_locations.tree_code', '=', 'lahan_details.tree_code')
                    ->select('lahan_details.tree_code', 
                        'tree_locations.tree_name', 
                        'tree_locations.category',
                        'lahan_details.amount')
                    ->where([
                            'lahan_details.lahan_no' => $landVal->lahan_no,
                            'tree_locations.mu_no' => $landVal->mu_no
                        ])
                    ->whereYear('lahan_details.detail_year', $py)
                    ->get();
                $landVal->trees = $landDetails;
                array_push($newLahan, $landVal);
            }
        }
        
        $sostam = PlantingSocializations::
            where([
                'ff_no' => $ff->ff_no,
                'is_dell' => 0,
                'planting_year' => $py,
                'validation' => 1
            ])->count();
            
        $datas = [
            'ff' => $ff,
            'lahans' => $newLahan,
            'sostam' => $sostam
        ];
        
        $rslt =  $this->ResultReturn(200, 'success', $datas);
        return response()->json($rslt, 200);
        
    }
    
    public function getFFOptionsSostam(Request $req) {
        // validation
        $validator = Validator::make($req->all(), [
            'typegetdata' => 'required|in:all,several',
            'program_year' => 'required'
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors(), $validator->errors());
            return response()->json($rslt, 400);
        } else {
            $tgd = $req->typegetdata;
            $ff = explode(',', $req->ff ?? '');
            $py = $req->program_year;
        }
        
        $datas = FieldFacilitator::
            select('field_facilitators.name', 'field_facilitators.ff_no', 'ff_working_areas.mu_no')
            ->join('ff_working_areas', 'ff_working_areas.ff_no', 'field_facilitators.ff_no')
            ->join('lahans', 'lahans.user_id', 'field_facilitators.ff_no')
            ->where([
                ['ff_working_areas.program_year', 'LIKE', "%$py%"],
                'field_facilitators.active' => 1,
                'lahans.is_dell' => 0,
                'lahans.approve' => 1,
            ])
            ->whereYear('lahans.created_time', $py);
        
        if ($tgd == 'several' && isset($req->ff)) {
            $datas = $datas->whereIn('field_facilitators.ff_no', $ff);
        }
        
        $datas = $datas
            // ->where('ff_no', '02320210001')
            ->orderBy('field_facilitators.name')
            ->get();
        
        // $newFF = [];
        
        // foreach($datas as $index => $val) {
        //     $hasLand = Lahan::
        //         leftJoin('farmers', 'farmers.farmer_no', '=', 'lahans.farmer_no')
        //         ->where([
        //                 'lahans.user_id' => $val->ff_no, 
        //                 'lahans.is_dell' => 0,
        //                 'lahans.approve' => 1,
        //                 'farmers.is_dell' => 0,
        //                 'farmers.approve' => 1
        //             ])
        //         ->where('farmers.mou_no', 'LIKE', '%2022_%')
        //         ->whereYear('lahans.created_time', $py)
        //         ->count();
            
        //     if ($hasLand > 0) {
        //         array_push($newFF, $val);
        //     }
        // }
        
        $rslt =  $this->ResultReturn(200, 'success', $datas);
        return response()->json($rslt, 200);
    }
    
    public function deleteSosialisasiTanamForm(Request $req) {
        // validation
        $validator = Validator::make($req->all(), [
            'form_no' => 'required'
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors(), $validator->errors());
            return response()->json($rslt, 400);
        } else {
            $sostam = PlantingSocializations::where('form_no', $req->form_no)->first();
        }
        
        PlantingSocializationsPeriod::where('form_no',$req->form_no)->delete();
        PlantingSocializationsDetails::where('form_no', $req->form_no)->delete();
        PlantingSocializations::where('form_no', $req->form_no)->delete();
        
        
        $rslt =  $this->ResultReturn(200, 'success', 'Success Delete Sosialisasi Tanam');
        return response()->json($rslt, 200); 
        
    }
    
    public function deleteSosialisasiTanamPeriod(Request $req) {
        // validation
        $validator = Validator::make($req->all(), [
            'form_no' => 'required'
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors(), $validator->errors());
            return response()->json($rslt, 400);
        } else {
            $sostam = PlantingSocializations::where('form_no', $req->form_no)->first();
        }
        
        PlantingSocializationsPeriod::where('form_no',$req->form_no)->update([
            'pembuatan_lubang_tanam' => '00:00:00 00:00:00',
            'distribution_time' => '00:00:00 00:00:00',
            'distribution_location' => '00:00:00 00:00:00',
            'planting_time' => '00:00:00 00:00:00',
            'distribution_coordinates' => '-',
            'rec_armada' => '-',
            'start_pembuatan_lubang_tanam' => '00:00:00 00:00:00',
            'end_planting_time' => '00:00:00 00:00:00',
            'is_edit_coordinate' => 0,
            'is_dell' => 1
        ]);
        
        
        $rslt =  $this->ResultReturn(200, 'success', 'Success Delete Sosialisasi Tanam Period');
        return response()->json($rslt, 200); 
        
    }
    
    public function createSosialisasiTanamPeriod(Request $req) {
        $validator = Validator::make($req->all(), [
            // 'ff_no' => 'required|exists:field_facilitators,ff_no',
            // 'program_year' => 'required',
            'distribution_time' => 'required',
            'distribution_location' => 'required',
            'pembuatan_lubang_tanam' => 'required',
            'planting_time' => 'required',
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors(), $validator->errors());
            return response()->json($rslt, 400);
        }
        
        $createSostamPeriod = PlantingSocializationsPeriod::where('form_no', $req->form_no)->update([
            'form_no' => $req->form_no,
            'pembuatan_lubang_tanam' => $req->pembuatan_lubang_tanam,
            'distribution_time' => $req->distribution_time,
            'distribution_location' => $req->distribution_location,
            'planting_time' => $req->planting_time,
            'distribution_coordinates' => $req->distribution_coordinates,
            'rec_armada' => $req->distribution_rec_armada,
            'start_pembuatan_lubang_tanam' => $req->start_pembuatan_lubang_tanam,
            'end_planting_time' => $req->end_planting_time,
            'is_edit_coordinate' => 1,
            'is_dell' => 0
        ]);
        
        $rslt =  $this->ResultReturn(200, 'success', 'Success Create Sosialisasi Tanam Period');
        return response()->json($rslt, 200); 
        
    }
    
    public function UpdatePetaniSusulan(Request $req){
        $validator = Validator::make($req->all(), [
            //'status' => 'required',
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors(), $validator->errors());
            return response()->json($rslt, 400);
        }
        
        $createSostamPeriod = PlantingSocializations::where('form_no', $req->form_no)->update([
            'attendance' => 1,
            'status' => 'susulan',
            'confirm_date' => Carbon::now()
            
        ]);
        
        $rslt =  $this->ResultReturn(200, 'success', 'Success Update Petani Susulan');
        return response()->json($rslt, 200);
    }
    
    // Create Logs
    private function createLogs($logData) {
        // get main data
        $main = PlantingSocializations::where('form_no', $logData['form_no'])->first();
        // get Lahan Data
        if (isset($main->no_lahan)) {
            $lahan = Lahan::where('lahan_no', $main->no_lahan)->first();
        }
        // get Petani Data
        if (isset($main->farmer_no)) {
            $farmer = Farmer::where('farmer_no', $main->farmer_no)->first();
        }
        // get ff data
        if(isset($main->ff_no)) {
            $ff = FieldFacilitator::where('ff_no', $main->ff_no)->first();
        }
        // get fc data
        if (isset($ff->fc_no)) {
            $fc = Employee::where('nik', $ff->fc_no)->first();
        }
        // user auth data
        $user = Auth::user();
        
        // set message
        $message =  $logData['status'] . ' ' . 
                    ($main['form_no'] ?? '-') . 
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
                    
        $log = Log::channel('planting_socializations');
        
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
