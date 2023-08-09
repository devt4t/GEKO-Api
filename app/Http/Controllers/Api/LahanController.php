<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use DB;
use Carbon\Carbon;
use App\User;
use App\Desa;
use App\Farmer;
use App\FieldFacilitator;
use App\Employee;
use App\Kecamatan;
use App\Kabupaten;
use App\Province;
use App\Lahan;
use App\LahanTutupan;
use App\LahanDetail;
use App\MainPivot;
use App\Tree;
use App\TreeLocation;

class LahanController extends Controller
{
    /**
     * @SWG\Get(
     *   path="/api/GetLahanAllAdmin",
     *   tags={"Lahan"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Lahan All Admin",
     *   operationId="GetLahanAllAdmin",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="typegetdata",in="query", type="string"),
     *      @SWG\Parameter(name="ff",in="query", type="string"),
     *      @SWG\Parameter(name="mu",in="query",  type="string"),
     *      @SWG\Parameter(name="ta",in="query", type="string"),
     *      @SWG\Parameter(name="village",in="query",  type="string"),
     * )
     */
    public function GetLahanAllAdmin(Request $request){
        $typegetdata = $request->typegetdata;
        $ff = $request->ff;
        $py = $request->program_year;
        $per_page = $request->per_page;
        $mu = $request->mu;
        $ta = $request->ta;
        $village = $request->village;
        
        if ($ff) {
            $ff = explode(',', $ff);
        } else $ff = [];
        $filtered = false;
        $filter_lahan_no = [];
        
        if ($mu || $ta || $village) {
            $ff_wa = DB::table('ff_working_areas')
                ->where([
                    ['program_year', 'LIKE', "%$py%"],
                ]);
            if ($mu) $ff_wa = $ff_wa->where('mu_no', $mu);
            if ($ta) $ff_wa = $ff_wa->where('area_code', $ta);
            if ($village) $ff_wa = $ff_wa->where('kode_desa', $village);
            if (count($ff) > 0) {
                $ff_wa = $ff_wa->whereIn('ff_no', $ff);
            }
            
            $ff = $ff_wa->pluck('ff_no')->toArray();
            $filtered = true;
        }
        
        if (count($ff) > 0) {
            $farmer_no = DB::table('main_pivots')
                ->where([
                    'type' => 'ff_farmer',
                    ['program_year', 'LIKE', "%$py%"]    
                ])->whereIn('key1', $ff)->pluck('key2')->toArray();
            $filter_lahan_no = DB::table('main_pivots')
                ->where([
                    'type' => 'farmer_lahan',
                    ['program_year', 'LIKE', "%$py%"]    
                ])->whereIn('key1', $farmer_no)->pluck('key2')->toArray();
            $filtered = true;
        }
        
        
        $lahans = DB::table('lahans')->select('lahans.id as id_lahan', 
                'lahans.lahan_no',
                'lahans.longitude', 
                'lahans.user_id', 
                'lahans.latitude',
                'lahans.coordinate',
                'lahans.lahan_type',
                'farmers.farmer_no as farmer_no', 
                'farmers.ktp_no as farmer_nik', 
                'farmers.name as farmer_name',
                'desas.name as village_name',
                'lahans.document_no', 
                'lahans.complete_data', 
                'lahans.approve', 
                'lahans.updated_gis', 
                'lahans.is_dell', 
                'lahans.created_at', 
                'lahans.created_time',
                'lahans.pohon_kayu', 
                'lahans.pohon_mpts',
                'lahans.mu_no',
                'lahans.target_area',
                //'lahan_tutupans.program_year',
                'lahan_tutupans.tutupan_lahan',
                'lahan_tutupans.land_area', 
                'lahan_tutupans.pattern as opsi_pola_tanam'
            )
            ->join('farmers', 'farmers.farmer_no', '=', 'lahans.farmer_no')
            ->join('desas', 'desas.kode_desa', '=', 'lahans.village')
            ->join('lahan_tutupans', 'lahan_tutupans.lahan_no', '=', 'lahans.lahan_no');
        
        
        $lahans = $lahans->where([
                ['lahan_tutupans.program_year','like',"%$py%"]
            ]);
        if (count($filter_lahan_no) > 0 || $filtered === true) {
            $lahans = $lahans->whereIn('lahans.lahan_no', $filter_lahan_no);
        }
            
        // search
        $Scolumn = $request->search_column;
        $Svalue = $request->search_value;
        if ($Scolumn && $Svalue) {
            $Ncolumn = '';
            $type = 'LIKE';
            if ($Scolumn == 'farmer_no') $Ncolumn = 'farmers.farmer_no';
            if ($Scolumn == 'farmer_name') $Ncolumn = 'farmers.name';
            if ($Scolumn == 'lahan_no') $Ncolumn = 'lahans.lahan_no';
            if ($Scolumn == 'village_name') $Ncolumn = 'desas.name';
            if ($Scolumn == 'farmer_nik') $Ncolumn = 'farmers.ktp_no';
            if ($Scolumn == 'opsi_pola_tanam') {
                $type = '=';
                $Ncolumn = 'lahan_tutupans.pattern';
            }
            if ($type == 'LIKE') $Svalue = "%$Svalue%";
            if ($Ncolumn && $Svalue) {
                $lahans = $lahans->where($Ncolumn, $type, "$Svalue");
                $ff_no_search = $lahans->pluck('lahans.user_id')->toArray();
            }
            
            if ($Scolumn == 'ff_name' && $Svalue) {
                $searchQuery = DB::table('main_pivots')->where([
                    'type' => 'ff_farmer',
                    ['program_year', 'LIKE', "%$py%" ],
                ])->join('field_facilitators', 'field_facilitators.ff_no', 'main_pivots.key1')
                ->where([
                    ['field_facilitators.name', 'LIKE', "%$Svalue%"]
                ]);
                
                $ff_no_search = $searchQuery->pluck('key1')->toArray();
                $farmer_no_search = $searchQuery->pluck('key2')->toArray();
                
                $lahans = $lahans->whereIn('farmers.farmer_no', $farmer_no_search);
            }
        }
        
        // sorting data
        if ($request->sortBy) {
            $sorting = explode(',',$request->sortBy);
            $desc = explode(',',$request->sortDesc);
            foreach ($sorting as $sortIndex => $sort) {
                $lahans = $lahans->orderBy($sort, $desc[$sortIndex] == 'true' ? 'DESC' : 'ASC');
            }
        }
        
        // filtered status
        if ($status = $request->status) {
            if ($status == 'Terverifikasi') {
                $lahans = $lahans->where('lahans.approve', 1);
            }
            if ($status == 'Belum Lengkap') {
                $lahans = $lahans->where('lahans.complete_data', 0);
            }
            if ($status == 'Belum Verifikasi') {
                $lahans = $lahans->where([
                    'lahans.approve' => 0,
                    'lahans.complete_data' => 1
                ]);
            }
            $ff_no_status = $lahans->pluck('lahans.user_id')->toArray();
        }
        
        // get lahan count
        $lahans_no_all = $lahans->pluck('lahans.lahan_no')->toArray();
        $farmer_no_all = $lahans->pluck('farmers.farmer_no')->toArray();
        $lahanTotal = count($lahans_no_all);
        $lahanBelumLengkap = DB::table('lahans')->whereIn('lahan_no', $lahans_no_all)->where('complete_data', 0)->count();
        $lahanBelumVerifikasi = DB::table('lahans')->whereIn('lahan_no', $lahans_no_all)->where([
                'lahans.approve' => 0,
                'lahans.complete_data' => 1
            ])->count();
        $lahanTerverifikasi = DB::table('lahans')->whereIn('lahan_no', $lahans_no_all)->where('lahans.approve', 1)->count();
        // query petani belum ada data lahan
        $petaniSudahAdaLahan = DB::table('main_pivots')
        ->where([
            'type' => 'farmer_lahan',
            ['program_year', 'LIKE', "%$py%"]
            ])->pluck('key1')->toArray();
        $petaniBelumAdaLahan = DB::table('main_pivots')
            ->where([
                'type' => 'ff_farmer',
                ['program_year', 'LIKE', "%$py%"]
            ])
            ->whereNotIn('key2', $petaniSudahAdaLahan);
        if (count($ff) > 0) {
            $petaniBelumAdaLahan = $petaniBelumAdaLahan->whereIn('key1', $ff);
        }
        if (isset($ff_no_search)) {
            $petaniBelumAdaLahan = $petaniBelumAdaLahan->whereIn('key1', $ff_no_search);
        }
        if(isset($ff_no_status)){
            $petaniBelumAdaLahan = $petaniBelumAdaLahan->whereIn('key1', $ff_no_status);
        }
        $petaniBelumAdaLahan = $petaniBelumAdaLahan
            ->groupBy('key2')->get();
        
        $lahans = $lahans->paginate($per_page);
        
        foreach ($lahans as $lahan) {
            // field facilitator
            $ff_no = DB::table('main_pivots')->where([
                    'type' => 'ff_farmer',
                    'key2' => $lahan->farmer_no,
                    ['program_year', 'LIKE', "%$py%" ],
                ])->first()->key1 ?? '';
            $ff_data = DB::table('field_facilitators')->where('ff_no', $ff_no)->first();
            $lahan->ff_name = $ff_data->name ?? '';
        
            // get seed type
            $lahan->jenis_bibit = DB::table('lahan_details')->join('trees', 'trees.tree_code', 'lahan_details.tree_code')
            ->where([
                'lahan_details.lahan_no' => $lahan->lahan_no,
            ])->whereYear('lahan_details.detail_year', $py)->pluck('trees.tree_name')->toArray();
            // $lahan_create_at = DB::table('lahans')->join('main_pivot', 'main_pivot.key2')->whereYear('')
        }
        
        $rslt =  [
            'total' => $lahanTotal ?? 0,
            'belum_ada' => $petaniBelumAdaLahan ? count($petaniBelumAdaLahan) : 0,
            'belum_lengkap' => $lahanBelumLengkap ?? 0,
            'belum_verifikasi' => $lahanBelumVerifikasi ?? 0,
            'terverifikasi' => $lahanTerverifikasi ?? 0,
            'lahan' => $lahans,
        ];
        return response()->json($rslt, 200); 
    }
    
    public function GetLahanAllAdminNew(Request $request){
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
        $py = $request->program_year;
        $getmu = $request->mu;
        $getta = $request->ta;
        $getvillage = $request->village;
        if($getmu){$mu='%'.$getmu.'%';}
        else{$mu='%%';}
        if($getta){$ta='%'.$getta.'%';}
        else{$ta='%%';}
        if($getvillage){$village='%'.$getvillage.'%';}
        else{$village='%%';}
            
        $GetLahanAll = DB::table('lahans')
            ->select('lahans.id as id_lahan', 
            'lahans.lahan_no as lahan_no',
            'lahans.longitude', 
            'lahans.latitude',
            'lahans.coordinate',
            'lahans.lahan_type', 
            'farmers.farmer_no as farmer_no', 
            'farmers.ktp_no as farmer_nik', 
            'farmers.name as farmer_name',
            'desas.name as village_name',
            'field_facilitators.name as ff_name',
            'lahans.document_no', 
            'lahans.complete_data', 
            'lahans.approve', 
            'lahans.updated_gis', 
            'lahans.is_dell', 
            'lahans.created_at', 
            'lahans.created_time', 
            'lahan_tutupans.tutupan_lahan', 
            'lahans.pohon_kayu', 
            'lahans.pohon_mpts', 
            'lahan_tutupans.land_area', 
            'lahan_tutupans.pattern as opsi_pola_tanam')
            ->join('farmers', 'farmers.farmer_no', '=', 'lahans.farmer_no')
            ->join('field_facilitators', 'field_facilitators.ff_no', '=', 'lahans.user_id')
            ->join('desas', 'desas.kode_desa', '=', 'lahans.village')
            ->join('lahan_tutupans', 'lahan_tutupans.lahan_no', '=', 'lahans.lahan_no')
            ->where('lahans.is_dell','=',0)
            ->where('lahans.mu_no','like',$mu)
            ->where('lahans.target_area','like',$ta)
            ->where('lahans.village','like',$village)
            ->where('lahan_tutupans.program_year','like',$py);
        
        $userNIK = Auth::user()->employee_no;
        $positionNo = (string)Employee::where('nik', $userNIK)->first()->position_no ?? '0';
        $ITPositions = DB::table('employee_positions')->where('position_group', 'IT')->pluck('position_no')->toArray();
        if (in_array($positionNo, $ITPositions) == false) $GetLahanAll = $GetLahanAll->where('lahans.user_id', 'LIKE', 'FF%');
            
        if ($py) {
            // if($py > 2022){
            // $GetLahanAll = $GetLahanAll
            //     ->whereYear('lahans.created_time', $py);    
            // }else{
            $GetLahanAll = $GetLahanAll
                ->join('main_pivots', 'main_pivots.key2', '=', 'lahans.lahan_no')
                ->where('main_pivots.type', '=', 'farmer_lahan')
                ->where('main_pivots.program_year', 'like', "%$py%");
            // }
        }
            
        if($ff){
            $ffdecode = $this->getFFListByUserPY($py);
            $GetLahanAll = $GetLahanAll
            ->wherein('lahans.user_id',$ffdecode);
        }
            
        // search
        if ($request->search_column && $request->search_value) {
            $Scolumn = $request->search_column;
            $type = 'LIKE';
            $Svalue = $request->search_value;
            if ($Scolumn == 'ff_name') $Scolumn = 'field_facilitators.name';
            if ($Scolumn == 'farmer_no') $Scolumn = 'farmers.farmer_no';
            if ($Scolumn == 'farmer_name') $Scolumn = 'farmers.name';
            if ($Scolumn == 'lahan_no') $Scolumn = 'lahans.lahan_no';
            if ($Scolumn == 'village_name') $Scolumn = 'desas.name';
            if ($Scolumn == 'farmer_nik') $Scolumn = 'farmers.ktp_no';
            if ($Scolumn == 'opsi_pola_tanam') {
                $type = '=';
                $Scolumn = 'lahan_tutupans.pattern';
            }
            
            if ($type == 'LIKE') $Svalue = "%$Svalue%";
            $GetLahanAll = $GetLahanAll->where($Scolumn, $type, "$Svalue");
        }
        // sorting data
        if ($request->sortBy) {
            $sorting = explode(',',$request->sortBy);
            $desc = explode(',',$request->sortDesc);
            foreach ($sorting as $sortIndex => $sort) {
                $GetLahanAll = $GetLahanAll->orderBy($sort, $desc[$sortIndex] == 'true' ? 'DESC' : 'ASC');
            }
        }
        // tester data
        if ($request->tester_data == '0') {
            $GetLahanAll = $GetLahanAll->where([
                ['field_facilitators.ff_no', 'LIKE', 'FF%'],
                ['field_facilitators.name', 'NOT LIKE', '%FF%'],
                ['lahans.lahan_no', 'LIKE', '10_0%']
            ]);
        }
        // filtered status
        if ($status = $request->status) {
            if ($status == 'Terverifikasi') {
                $GetLahanAll = $GetLahanAll->where('lahans.approve', 1);
            }
            if ($status == 'Belum Lengkap') {
                $GetLahanAll = $GetLahanAll->where('lahans.complete_data', 0);
            }
            if ($status == 'Belum Verifikasi') {
                $GetLahanAll = $GetLahanAll->where([
                    'lahans.approve' => 0,
                    'lahans.complete_data' => 1
                ]);
            }
        }

        $GetLahanAll = $GetLahanAll->orderBy('lahans.created_at', 'desc')->groupBy('lahans.lahan_no')->paginate($per_page);
        
        foreach ($GetLahanAll as $data) {
            $data->jenis_bibit = DB::table('lahan_details')->join('trees', 'trees.tree_code', 'lahan_details.tree_code')
            ->where([
                'lahan_details.lahan_no' => $data->lahan_no,
            ])->whereYear('lahan_details.detail_year', $py)->pluck('trees.tree_name')->toArray();
        }

        if(count($GetLahanAll)!=0){ 
            $rslt =  $this->ResultReturn(200, 'success', $GetLahanAll);
            return response()->json($rslt, 200);  
        }
        else{
            $rslt =  $this->ResultReturn(404, 'doesnt match data', 'doesnt match data');
            return response()->json($rslt, 404);
        }
    }
    
    public function GetLahanAllAdminView(Request $request){
        $typegetdata = $request->typegetdata;
        $ff = $request->ff;
        $py = $request->program_year;
        $getmu = $request->mu;
        $getta = $request->ta;
        $getvillage = $request->village;
        if($getmu){$mu='%'.$getmu.'%';}
        else{$mu='%%';}
        if($getta){$ta='%'.$getta.'%';}
        else{$ta='%%';}
        if($getvillage){$village='%'.$getvillage.'%';}
        else{$village='%%';}
            
        $GetLahanAll = DB::table('lahans')
            ->select('lahans.id as id_lahan', 
            'lahans.lahan_no as lahan_no',
            'lahans.longitude', 
            'lahans.latitude',
            'lahans.coordinate',
            'lahans.lahan_type', 
            'farmers.farmer_no as farmer_no', 
            'farmers.ktp_no as farmer_nik', 
            'farmers.name as farmer_name',
            'desas.name as village_name',
            'field_facilitators.name as ff_name',
            'lahans.document_no', 
            'lahans.complete_data', 
            'lahans.approve', 
            'lahans.updated_gis', 
            'lahans.is_dell', 
            'lahans.created_at', 
            'lahans.created_time', 
            'lahan_tutupans.tutupan_lahan', 
            'lahans.pohon_kayu', 
            'lahans.pohon_mpts', 
            'lahan_tutupans.land_area', 
            'lahan_tutupans.pattern as opsi_pola_tanam')
            ->join('farmers', 'farmers.farmer_no', '=', 'lahans.farmer_no')
            ->join('field_facilitators', 'field_facilitators.ff_no', '=', 'lahans.user_id')
            ->join('desas', 'desas.kode_desa', '=', 'lahans.village')
            ->join('lahan_tutupans', 'lahan_tutupans.lahan_no', '=', 'lahans.lahan_no')
            ->where('lahans.is_dell','=',0)->get();
            // ->where('lahans.mu_no','like',$mu)
            // ->where('lahans.target_area','like',$ta)
            // ->where('lahans.village','like',$village)
            // ->where('lahan_tutupans.program_year','like',$py);
            
        return $GetLahanAll;
        
        if ($py) {
            $GetLahanAll = $GetLahanAll
                ->join('main_pivots', 'main_pivots.key2', '=', 'lahans.lahan_no')
                ->where('main_pivots.type', '=', 'farmer_lahan')
                ->where('main_pivots.program_year', 'like', "%$py%");
            // }
        }
        
        $GetLahanAll = $GetLahanAll->orderBy('lahans.created_at', 'desc')->groupBy('lahans.lahan_no');
        
        if($GetLahanAll){ 
            $rslt =  $this->ResultReturn(200, 'success', $GetLahanAll);
            return response()->json($rslt, 200);  
        }
        else{
            $rslt =  $this->ResultReturn(404, 'doesnt match data', 'doesnt match data');
            return response()->json($rslt, 404);
        }
    }
    
    public function GetLahanFF(Request $request){
        $ff_no = $request->user_id;
        $getpy = $request->created_at;
        // $getfarmerno = $request->farmer_no;
        if($getpy){$py='%'.$getpy.'%';}
        else{$py='%%';}
        // if($getfarmerno){$farmer_no='%'.$getfarmerno.'%';}
        // else{$farmer_no='%%';}
        
        $lahan = Lahan::where('user_id', '=', $ff_no)->where('is_dell', '=', 0)->where('created_at', 'like', $py)->orderBy('id', 'ASC')->get();
        
        if($lahan){
            $lahDetails = [];
            foreach($lahan as $sIndex => $lhn){
                $getFF = DB::table('field_facilitators')->where('ff_no', '=', $lhn->ff_no)->first();
                $getDetailLahan = DB::table('lahan_details')
                ->select('lahan_details.id', 'lahan_details.lahan_no', 'lahan_details.tree_code', 'lahan_details.amount', 'lahan_details.detail_year', 'lahan_details.user_id', 'lahan_details.created_at', 'lahan_details.updated_at', 
                    'tree_locations.tree_name as tree_name', 'tree_locations.category as tree_category', 'tree_locations.mu_no as mu_no')
                ->leftjoin('tree_locations', 'tree_locations.tree_code', '=', 'lahan_details.tree_code')
                ->where([
                    'lahan_no' =>$lhn->lahan_no,
                    'mu_no' => $getFF->mu_no
                ])->get();
                array_push($lahDetails, ...$getDetailLahan);
                $getDetailLahan[$sIndex]->lahan_no = (string)$lhn->lahan_no;
            }
            
            if(count($lahan)!=0){
                foreach ($lahan as $detail) {
                    $detail->main_pivot = MainPivot::where('key2', $detail->lahan_no)->where('type', 'farmer_lahan')->get();
                }
                $rslt =  $this->ResultReturn(200, 'success', $lahan);
                return response()->json($rslt, 200);  
            }
            
            
             $data = [
                'data' => $lahan,
                'lahan_details' => $lahDetails
            ];
        }
        
        $rslt = $this->ResultReturn(200, 'success', $data);
        return response()->json($rslt, 200);
    }

    public function ExportLahanAllAdmin(Request $request){
        $typegetdata = $request->typegetdata;
        $py = $request->program_year ?? date('Y');
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
                $GetLahanAll = DB::table('lahans')
                ->select(
                    'lahans.id as idTblLahan',
                    'lahans.lahan_no as lahanNo',
                    'lahans.longitude',
                    'lahans.latitude',
                    'lahans.coordinate',
                    'lahans.pohon_kayu',
                    'lahans.pohon_mpts',
                    'lahans.land_area',
                    'lahans.planting_area',
                    'lahans.opsi_pola_tanam',
                    'lahans.lahan_type',
                    'lahans.document_no',
                    'provinces.name as province',
                    'kabupatens.name as kabupaten', 
                    'kecamatans.name as kecamatan', 
                    'managementunits.name as mu',
                    'target_areas.name as ta',
                    'desas.name as desa',
                    'farmers.farmer_no as farmer_no', 
                    'farmers.name as farmer_name',
                    'lahans.user_id as ff_no',
                    'field_facilitators.name as ff_name',
                    'field_facilitators.fc_no',
                    'lahans.complete_data', 
                    'lahans.approve', 
                    'lahans.is_dell',
                    'lahans.updated_gis'
                )
                ->leftjoin('farmers', 'farmers.farmer_no', '=', 'lahans.farmer_no')
                ->leftjoin('field_facilitators', 'field_facilitators.ff_no', '=', 'lahans.user_id')
                ->leftjoin('provinces', 'provinces.province_code', '=', 'lahans.province')
                ->leftjoin('kabupatens', 'kabupatens.kabupaten_no', '=', 'lahans.city')
                ->leftjoin('kecamatans', 'kecamatans.kode_kecamatan', '=', 'lahans.kecamatan')
                ->leftjoin('managementunits', 'managementunits.mu_no', '=', 'lahans.mu_no')
                ->leftjoin('target_areas', 'target_areas.area_code', '=', 'lahans.target_area')
                ->leftjoin('desas', 'desas.kode_desa', '=', 'lahans.village')
                ->where([
                    'lahans.is_dell' => 0,
                    ['lahans.lahan_no','LIKE','10_%'],
                    ['lahans.user_id', 'LIKE', 'FF%'],
                    ['lahans.mu_no','LIKE',$mu],
                    ['lahans.target_area','LIKE',$ta],
                    ['lahans.village','LIKE',$village]
                ])
                ->whereYear('lahans.created_time', $py)
                ->orderby('lahans.lahan_no','asc');
                
                if ($ff) {
                    $ffdecode = (explode(",",$ff));
                    $GetLahanAll = $GetLahanAll->wherein('lahans.user_id',$ffdecode);
                }
                
                $GetLahanAll = $GetLahanAll->get();

                if(count($GetLahanAll)!=0){ 
                    
                    // $data = ['count'=>$count, 'data'=>$listval];
                    // $rslt =  $this->ResultReturn(200, 'success', $data);
                    // return response()->json($rslt, 200);
                    

                    $nama_title = "Data Lahan GEKO Tahun Program $py";  

                    // var_dump($listval);

                    return view('exportlahan', [
                        'py' => $py,
                        'listval' => $GetLahanAll,
                        'nama_title' => $nama_title
                    ]);
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

    public function ExportLahanAllSuperAdmin(Request $request)
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
                if($typegetdata == 'all'){
                    $GetLahanAll = DB::table('lahans')->select('lahans.id as idTblLahan','lahans.lahan_no as lahanNo','lahans.longitude','lahans.latitude','lahans.coordinate',
                    'lahans.pohon_kayu','lahans.pohon_mpts','lahans.land_area','lahans.planting_area','kecamatans.name as nama_kec','managementunits.name as nama_mu',
                    'farmers.farmer_no as kodePetani', 'farmers.name as namaPetani','desas.name as namaDesa','lahans.user_id as ff_no','users.name as ff','lahans.complete_data', 'lahans.approve', 'lahans.is_dell')
                    ->leftjoin('farmers', 'farmers.farmer_no', '=', 'lahans.farmer_no')
                    ->leftjoin('users', 'users.employee_no', '=', 'lahans.user_id')
                    ->leftjoin('desas', 'desas.kode_desa', '=', 'lahans.village')
                    ->leftjoin('kecamatans', 'kecamatans.kode_kecamatan', '=', 'lahans.kecamatan')
                    ->leftjoin('managementunits', 'managementunits.mu_no', '=', 'lahans.mu_no')
                    ->where('lahans.is_dell','=',0)
                    ->where('lahans.lahan_no','not like','00_%')
                    ->where('lahans.mu_no','like',$mu)
                    ->where('lahans.target_area','like',$ta)
                    ->where('lahans.village','like',$village)
                    // ->where('lahans.lahan_no','=','10_0000005890')
                    ->orderby('lahans.lahan_no','asc')
                    ->get();
                }else{
                    $ffdecode = (explode(",",$ff));

                    $GetLahanAll = DB::table('lahans')->select('lahans.id as idTblLahan','lahans.lahan_no as lahanNo','lahans.longitude','lahans.latitude','lahans.coordinate',
                    'lahans.pohon_kayu','lahans.pohon_mpts','lahans.land_area','lahans.planting_area','kecamatans.name as nama_kec','managementunits.name as nama_mu',
                    'farmers.farmer_no as kodePetani', 'farmers.name as namaPetani','desas.name as namaDesa','lahans.user_id as ff_no','users.name as ff','lahans.complete_data', 'lahans.approve', 'lahans.is_dell')
                    ->leftjoin('farmers', 'farmers.farmer_no', '=', 'lahans.farmer_no')
                    ->leftjoin('users', 'users.employee_no', '=', 'lahans.user_id')
                    ->leftjoin('desas', 'desas.kode_desa', '=', 'lahans.village')
                    ->leftjoin('kecamatans', 'kecamatans.kode_kecamatan', '=', 'lahans.kecamatan')
                    ->leftjoin('managementunits', 'managementunits.mu_no', '=', 'lahans.mu_no')
                    ->where('lahans.is_dell','=',0)
                    ->wherein('lahans.user_id',$ffdecode)
                    ->where('lahans.mu_no','like',$mu)
                    ->where('lahans.target_area','like',$ta)
                    ->where('lahans.village','like',$village)
                    ->orderby('lahans.lahan_no','asc')
                    ->get();
                }

                $getTrees=DB::table('trees')
                        ->select('tree_name','tree_code')
                        ->get();

                $dataval = [];
                $listval=array();
                foreach ($GetLahanAll as $val) {
                    $status = '';
                    if($val->complete_data==1 && $val->approve==1){
                        $status = 'Sudah Verifikasi';
                    }else if($val->complete_data==1 && $val->approve==0){
                        $status = 'Belum Verifikasi';
                    }else{
                        $status = 'Belum Lengkap';
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

                    $lahan_details=DB::table('lahan_details')
                            ->select('tree_code')
                            ->where('lahan_no', '=',$val->lahanNo)
                            ->get();

                    $listlhndtl=array();
                    array_push($listlhndtl, 'Nilai0Array');
                    foreach ($lahan_details as $lhndtl) {
                        array_push($listlhndtl, $lhndtl->tree_code);
                    }

                    // print_r($listlhndtl);

                    $datavaltrees = [];
                    $listvaltrees=array();
                    foreach ($getTrees as $value) {
                        $countPohon = 0;

                        $rslt_search = array_search($value->tree_code,$listlhndtl);

                        // var_dump($value->tree_code);
                        // var_dump($rslt_search);
                        // var_dump('---------');
                        
                        if($rslt_search){
                            // var_dump($rslt_search);
                            $getPohonFix=DB::table('lahan_details')
                            ->where('lahan_no', '=',$val->lahanNo)
                            ->where('tree_code', '=',$value->tree_code)
                            ->first();
                            $countPohon = $getPohonFix->amount;
                        }else{
                            $countPohon = 0;
                        }
                        // echo '<br>';

                        array_push($listvaltrees, $countPohon);
                    }
                    

                    // var_dump($getFC->name);

                    $dataval = ['idTblLahan'=>$val->idTblLahan,'lahanNo'=>$val->lahanNo, 'location'=>$val->latitude." ".$val->longitude, 'coordinate'=>$val->coordinate,
                    'kodePetani'=>$val->kodePetani, 'petani'=>$val->namaPetani, 'desa' => $val->namaDesa, 'user' => $val->ff, 'status' => $status,
                    'pohon_kayu' => $val->pohon_kayu,'pohon_mpts' => $val->pohon_mpts,'land_area' => $val->land_area,'planting_area' => $val->planting_area, 
                    'ff' => $val->ff,'nama_fc_lahan' => $nama_fc,'nama_kec' => $val->nama_kec,'nama_mu' => $val->nama_mu,'is_dell' => $val->is_dell,'listvaltrees' => $listvaltrees];
                    array_push($listval, $dataval);
                }

                if(count($GetLahanAll)!=0){ 
                    
                    // $data = ['count'=>$count, 'data'=>$listval];
                    // $rslt =  $this->ResultReturn(200, 'success', $data);
                    // return response()->json($rslt, 200);
                    

                    $nama_title = 'Cetak Excel Data Lahan';  

                    // var_dump($listval);

                    return view('exportlahanSuperAdmin', compact('listval', 'nama_title', 'getTrees'));
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

    /**
     * @SWG\Get(
     *   path="/api/GetLahanAll",
     *   tags={"Lahan"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Lahan All",
     *   operationId="GetLahanAll",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="user_id",in="query", required=true, type="string"),
     *      @SWG\Parameter(name="farmer_no",in="query", type="string"),
     *      @SWG\Parameter(name="limit",in="query", type="integer"),
     *      @SWG\Parameter(name="offset",in="query", type="integer"),
     *      @SWG\Parameter(name="program_year",in="query", type="string"),
     * )
     */
    public function GetLahanAll(Request $request){
        $limit = $this->limitcheck($request->limit);
        $offset =  $this->offsetcheck($limit, $request->offset);
        $getfarmerno = $request->farmer_no;
        $py = $request->program_year;
        if($getfarmerno){$farmer_no='%'.$getfarmerno.'%';}
        else{$farmer_no='%%';}
        try{
            if($farmer_no!='%%'){
                $GetLahanAll = Lahan::where('user_id', '=', $request->user_id)->where('farmer_no','like',$farmer_no)->where('is_dell', '=', 0)->orderBy('id', 'ASC')->get();
            }else{
                $GetLahanAll = Lahan::where('user_id', '=', $request->user_id)->where('is_dell', '=', 0)->orderBy('id', 'ASC')->get();
            }

            if(count($GetLahanAll)!=0){
                if($farmer_no!='%%'){
                    $count = Lahan::where('user_id', '=', $request->user_id)->where('farmer_no','like',$farmer_no)->where('is_dell', '=', 0)->count();
                }else{
                    $count = Lahan::where('user_id', '=', $request->user_id)->where('is_dell', '=', 0)->count();
                }
                
                foreach ($GetLahanAll as $detail) {
                    $getPivot = MainPivot::where([
                        'key2' => $detail->lahan_no,
                        'key1' => $detail->farmer_no,
                        'type' => 'farmer_lahan'
                    ])->first();
                    $detail->year = $getPivot->program_year ?? '';
                    
                    if($request->year != null){
                        $getTutupan = LahanTutupan::where([
                            'lahan_no' => $detail->lahan_no
                            // 'program_year' => $request->year
                        ])->orderBy('id','desc')->first();
                        $detail->tutupan = $getTutupan->tutupan_lahan ?? "-";
                        $detail->pola_tanam = $getTutupan->pattern ?? "-";
                    }
                    
                    $getFarmer = Farmer::where('farmer_no',$detail->farmer_no)->first();
                    $detail->namaPetani = $getFarmer->nickname ?? $getFarmer->name;
                    
                    $getKec = Kecamatan::where('kode_kecamatan', $detail->kecamatan)->first();
                    $getDesa = Desa::where('kode_desa', $detail->village)->first();
                    $detail->desa = $getDesa->name." - ".$getKec->name;
                }
                $data = ['count'=>$count, 'data'=>$GetLahanAll];
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
    
    public function GetLahanAllDesa(Request $request){
        $userId = $request->user_id;
        $getvillage = $request->farmer_no;

        $GetLahanAll = Lahan::where('farmer_no', '=', $getvillage)
                ->where('is_dell', '=', 0)
                ->where('user_id', '!=', $userId)
                ->orderBy('lahan_no', 'ASC')
                ->get();
                
        if(count($GetLahanAll)!=0){
            $lahanDetail = [];
            foreach($GetLahanAll as $pIndex => $lahans){
                $detail = LahanDetail::where(['lahan_no' => $lahans->lahan_no])->get();
                array_push($lahanDetail, ...$detail);
                
                $getPivot = MainPivot::where([
                        'key2' => $lahans->lahan_no,
                        'key1' => $lahans->farmer_no,
                        'type' => 'farmer_lahan'
                    ])->first();
                    $lahans->year = $getPivot->program_year ?? '';
                    
                    if($request->year != null){
                        $getTutupan = LahanTutupan::where([
                            'lahan_no' => $lahans->lahan_no
                            // 'program_year' => $request->year
                        ])->orderBy('id','desc')->first();
                        $lahans->tutupan = $getTutupan->tutupan_lahan ?? "-";
                        $lahans->pola_tanam = $getTutupan->pattern ?? "-";
                    }
                    
                    $getFarmer = Farmer::where('farmer_no',$lahans->farmer_no)->first();
                    $lahans->namaPetani = $getFarmer->nickname ?? $getFarmer->name;
                    
                    $getKec = Kecamatan::where('kode_kecamatan', $lahans->kecamatan)->first();
                    $getDesa = Desa::where('kode_desa', $lahans->village)->first();
                    $lahans->desa = $getDesa->name." - ".$getKec->name;
            }
            
            $count = Lahan::where('farmer_no', '=', $getvillage)->where('is_dell', '=', 0)->count();
            $data = ['count'=>$count, 'data'=>$GetLahanAll, 'detail'=>$lahanDetail];
            $rslt =  $this->ResultReturn(200, 'success', $data);
            return response()->json($rslt, 200);
        }else{
            $rslt =  $this->ResultReturn(404, 'doesnt match data', 'doesnt match data');
            return response()->json($rslt, 404);
        }
    }
    
    /**
     * @SWG\Get(
     *   path="/api/GetLahanNotComplete",
     *   tags={"Lahan"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Lahan Not Complete",
     *   operationId="GetLahanNotComplete",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="user_id",in="query", required=true, type="string"),
     *      @SWG\Parameter(name="farmer_no",in="query", type="string"),
     *      @SWG\Parameter(name="limit",in="query", type="integer"),
     *      @SWG\Parameter(name="offset",in="query", type="integer"),
     * )
     */
    public function GetLahanNotComplete(Request $request){
        $limit = $this->limitcheck($request->limit);
        $offset =  $this->offsetcheck($limit, $request->offset);
        $getfarmerno = $request->farmer_no;
        if($getfarmerno){$farmer_no='%'.$getfarmerno.'%';}
        else{$farmer_no='%%';}
        try{
            // var_dump(count($GetLahanNotComplete));
            if($farmer_no!='%%'){
                $GetLahanNotComplete = Lahan::where('user_id', '=', $request->user_id)->where('farmer_no','like',$farmer_no)->where('is_dell', '=', 0)->where('complete_data', '=', 0)->orderBy('id', 'ASC')->limit($limit)->offset($offset)->get();
            }else{
                $GetLahanNotComplete = Lahan::where('user_id', '=', $request->user_id)->where('is_dell', '=', 0)->where('complete_data', '=', 0)->orderBy('id', 'ASC')->limit($limit)->offset($offset)->get();
            }
            if(count($GetLahanNotComplete)!=0){
                
                if($farmer_no!='%%'){
                    $count = Lahan::where('user_id', '=', $request->user_id)->where('farmer_no','like',$farmer_no)->where('is_dell', '=', 0)->where('complete_data', '=', 0)->count();
                }else{
                    $count = Lahan::where('user_id', '=', $request->user_id)->where('is_dell', '=', 0)->where('complete_data', '=', 0)->count();
                }
                $data = ['count'=>$count, 'data'=>$GetLahanNotComplete];
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
     *   path="/api/GetLahanCompleteNotApprove",
     *   tags={"Lahan"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Lahan Complete Not Approve",
     *   operationId="GetLahanCompleteNotApprove",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="user_id",in="query", required=true,  type="string"),
     *      @SWG\Parameter(name="farmer_no",in="query", type="string"),
     *      @SWG\Parameter(name="limit",in="query", type="integer"),
     *      @SWG\Parameter(name="offset",in="query", type="integer"),
     * )
     */
    public function GetLahanCompleteNotApprove(Request $request){
        $limit = $this->limitcheck($request->limit);
        $offset =  $this->offsetcheck($limit, $request->offset);
        $getfarmerno = $request->farmer_no;
        if($getfarmerno){$farmer_no='%'.$getfarmerno.'%';}
        else{$farmer_no='%%';}
        try{            
            if($farmer_no!='%%'){
                $GetLahanCompleteNotApprove = Lahan::where('user_id', '=', $request->user_id)->where('farmer_no', 'Like', $farmer_no)->where('is_dell', '=', 0)->where('complete_data', '=', 1)->where('approve', '=', 0)->orderBy('id', 'ASC')->limit($limit)->offset($offset)->get();
            }else{
                $GetLahanCompleteNotApprove = Lahan::where('user_id', '=', $request->user_id)->where('is_dell', '=', 0)->where('complete_data', '=', 1)->where('approve', '=', 0)->orderBy('id', 'ASC')->limit($limit)->offset($offset)->get();
            }
            if(count($GetLahanCompleteNotApprove)!=0){
                
                if($farmer_no!='%%'){
                    $count = Lahan::where('user_id', '=', $request->user_id)->where('is_dell', '=', 0)->where('farmer_no', 'Like', $farmer_no)->where('complete_data', '=', 1)->where('approve', '=', 0)->count();
                }else{
                    $count = Lahan::where('user_id', '=', $request->user_id)->where('is_dell', '=', 0)->where('complete_data', '=', 1)->where('approve', '=', 0)->count();
                }
                $data = ['count'=>$count, 'data'=>$GetLahanCompleteNotApprove];
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
     *   path="/api/GetCompleteAndApprove",
     *   tags={"Lahan"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Complete And Approve",
     *   operationId="GetCompleteAndApprove",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="user_id",in="query", required=true,  type="string"),
     *      @SWG\Parameter(name="farmer_no",in="query", type="string"),
     *      @SWG\Parameter(name="limit",in="query", type="integer"),
     *      @SWG\Parameter(name="offset",in="query", type="integer"),
     * )
     */
    public function GetCompleteAndApprove(Request $request){
        $limit = $this->limitcheck($request->limit);
        $offset =  $this->offsetcheck($limit, $request->offset);
        $getfarmerno = $request->farmer_no;
        if($getfarmerno){$farmer_no='%'.$getfarmerno.'%';}
        else{$farmer_no='%%';}
        try{
            
            if($farmer_no!='%%'){
                $GetCompleteAndApprove = Lahan::where('user_id', '=', $request->user_id)->where('farmer_no', 'Like', $farmer_no)->where('is_dell', '=', 0)->where('complete_data', '=', 1)->where('approve', '=', 1)->orderBy('id', 'ASC')->limit($limit)->offset($offset)->get();
            }else{
                $GetCompleteAndApprove = Lahan::where('user_id', '=', $request->user_id)->where('is_dell', '=', 0)->where('complete_data', '=', 1)->where('approve', '=', 1)->orderBy('id', 'ASC')->limit($limit)->offset($offset)->get();
            }
            if(count($GetCompleteAndApprove)!=0){
                
                if($farmer_no!='%%'){
                    $count = Lahan::where('user_id', '=', $request->user_id)->where('farmer_no', 'Like', $farmer_no)->where('is_dell', '=', 0)->where('complete_data', '=', 1)->where('approve', '=', 1)->count();
                }else{
                    $count = Lahan::where('user_id', '=', $request->user_id)->where('is_dell', '=', 0)->where('complete_data', '=', 1)->where('approve', '=', 1)->count();
                }
                $data = ['count'=>$count, 'data'=>$GetCompleteAndApprove];
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
     *   path="/api/GetLahanDetail",
     *   tags={"Lahan"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Lahan Detail",
     *   operationId="GetLahanDetail",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="id",in="query", required=true,  type="string")
     * )
     */
    public function GetLahanDetail(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'id' => 'required'
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
            $GetLahanDetail = 
            DB::table('lahans')->select('lahans.id','lahans.lahan_no','lahans.document_no','lahans.land_area',
            'lahans.planting_area','lahans.longitude','lahans.latitude','lahans.coordinate','lahans.internal_code',
            'lahans.polygon','lahans.village','lahans.kecamatan','lahans.city','lahans.province',
            'lahans.description','lahans.elevation','lahans.soil_type','lahans.current_crops',
            'lahans.active','lahans.farmer_no','lahans.farmer_temp','lahans.mu_no',
            'lahans.target_area','lahans.user_id','lahans.sppt','lahans.tutupan_lahan',
            'lahans.photo1','lahans.photo2','lahans.photo3','lahans.photo4','lahans.group_no','lahans.kelerengan_lahan',
            'lahans.fertilizer','lahans.pesticide','lahans.access_to_water_sources','lahans.water_availability',
            'lahans.access_to_lahan','lahans.potency','lahans.barcode','lahans.lahan_type','lahans.jarak_lahan','lahans.exposure',
            'lahans.opsi_pola_tanam','lahans.type_sppt','lahans.complete_data','lahans.approve',
            'lahans.pohon_kayu','lahans.pohon_mpts',
            'provinces.name as namaProvinsi','kabupatens.name as namaKabupaten','kecamatans.name as namaKecamatan',
            'desas.name as namaDesa','target_areas.name as namaTa','managementunits.name as namaMu','farmers.name as namaPetani','farmer_groups.name as namaKelompok', 'lahans.created_at')
            ->leftjoin('provinces', 'provinces.province_code', '=', 'lahans.province')
            ->leftjoin('kabupatens', 'kabupatens.kabupaten_no', '=', 'lahans.city')
            ->leftjoin('kecamatans', 'kecamatans.kode_kecamatan', '=', 'lahans.kecamatan')
            ->leftjoin('desas', 'desas.kode_desa', '=', 'lahans.village')
            ->leftjoin('target_areas', 'target_areas.area_code', '=', 'lahans.target_area')
            ->leftjoin('managementunits', 'managementunits.mu_no', '=', 'lahans.mu_no')
            ->leftjoin('farmers', 'farmers.farmer_no', '=', 'lahans.farmer_no')
            ->leftjoin('farmer_groups', 'farmer_groups.group_no', '=', 'lahans.group_no');   
            
            if ($request->lahan_no) $GetLahanDetail = $GetLahanDetail->where('lahans.lahan_no', '=', $request->lahan_no); 
            else $GetLahanDetail = $GetLahanDetail->where('lahans.id', '=', $request->id);
            
            $GetLahanDetail = $GetLahanDetail->first();
            
            $ff = FieldFacilitator::where('ff_no', $GetLahanDetail->user_id)->first();
            
            if($GetLahanDetail){
                $getDetailTreesLAhan =  DB::table('lahan_details')->select('lahan_details.id','lahan_details.lahan_no','lahan_details.tree_code','lahan_details.amount',
                'lahan_details.detail_year','tree_locations.category as tree_category','tree_locations.tree_name')
                ->leftjoin('tree_locations', 'tree_locations.tree_code', '=', 'lahan_details.tree_code')
                ->where('lahan_details.lahan_no', '=', $GetLahanDetail->lahan_no)
                ->where('tree_locations.mu_no', $ff->mu_no)
                ->get();

                // var_dump($getDetailTreesLAhan);


                $LahanDetailNew = ['id'=>$GetLahanDetail->id,'lahan_no'=>$GetLahanDetail->lahan_no,'document_no'=>$GetLahanDetail->document_no,'internal_code'=>$GetLahanDetail->internal_code,'land_area'=>$GetLahanDetail->land_area,'planting_area'=>$GetLahanDetail->planting_area,
                'longitude'=>$GetLahanDetail->longitude,'latitude'=>$GetLahanDetail->latitude,'coordinate'=>$GetLahanDetail->coordinate,'polygon'=>$GetLahanDetail->polygon,'village'=>$GetLahanDetail->village,
                'kecamatan'=>$GetLahanDetail->kecamatan,'city'=>$GetLahanDetail->city,'province'=>$GetLahanDetail->province,'description'=>$GetLahanDetail->description,'elevation'=>$GetLahanDetail->elevation,
                'soil_type'=>$GetLahanDetail->soil_type,'current_crops'=>$GetLahanDetail->current_crops,'active'=>$GetLahanDetail->active,'farmer_no'=>$GetLahanDetail->farmer_no,'farmer_temp'=>$GetLahanDetail->farmer_temp,
                'mu_no'=>$GetLahanDetail->mu_no,'target_area'=>$GetLahanDetail->target_area,'user_id'=>$GetLahanDetail->user_id,'sppt'=>$GetLahanDetail->sppt,'tutupan_lahan'=>$GetLahanDetail->tutupan_lahan,
                'complete_data'=>$GetLahanDetail->complete_data,'approve'=>$GetLahanDetail->approve,
                'tutupan_lahan'=>$GetLahanDetail->tutupan_lahan,'photo1'=>$GetLahanDetail->photo1,'photo2'=>$GetLahanDetail->photo2,'photo3'=>$GetLahanDetail->photo3,'photo4'=>$GetLahanDetail->photo4,
                'group_no'=>$GetLahanDetail->group_no,'kelerengan_lahan'=>$GetLahanDetail->kelerengan_lahan,'fertilizer'=>$GetLahanDetail->fertilizer,'pesticide'=>$GetLahanDetail->pesticide,'access_to_water_sources'=>$GetLahanDetail->access_to_water_sources,
                'water_availability'=>$GetLahanDetail->water_availability,'access_to_lahan'=>$GetLahanDetail->access_to_lahan,'potency'=>$GetLahanDetail->potency,'barcode'=>$GetLahanDetail->barcode,'lahan_type'=>$GetLahanDetail->lahan_type,
                'jarak_lahan'=>$GetLahanDetail->jarak_lahan,'exposure'=>$GetLahanDetail->exposure,'namaProvinsi'=>$GetLahanDetail->namaProvinsi,'namaKabupaten'=>$GetLahanDetail->namaKabupaten,'namaKecamatan'=>$GetLahanDetail->namaKecamatan,
                'namaDesa'=>$GetLahanDetail->namaDesa,'namaTa'=>$GetLahanDetail->namaTa,'namaMu'=>$GetLahanDetail->namaMu,'namaPetani'=>$GetLahanDetail->namaPetani,'namaKelompok'=>$GetLahanDetail->namaKelompok,
                'pohon_kayu'=>$GetLahanDetail->pohon_kayu,'pohon_mpts'=>$GetLahanDetail->pohon_mpts,
                'opsi_pola_tanam'=>$GetLahanDetail->opsi_pola_tanam,'type_sppt'=>$GetLahanDetail->type_sppt,'created_at'=>$GetLahanDetail->created_at,'DetailTreesLahan'=>$getDetailTreesLAhan];
                $rslt =  $this->ResultReturn(200, 'success', $LahanDetailNew);
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
     *   path="/api/GetLahanDetailLahanNo",
     *   tags={"Lahan"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Lahan Detail LahanNo",
     *   operationId="GetLahanDetailLahanNo",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="lahan_no",in="query", required=true,  type="string")
     * )
     */
    public function GetLahanDetailLahanNo(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'lahan_no' => 'required'
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
            $GetLahanDetail = 
            DB::table('lahans')->select('lahans.id','lahans.lahan_no','lahans.document_no','lahans.land_area',
            'lahans.planting_area','lahans.longitude','lahans.latitude','lahans.coordinate','lahans.internal_code',
            'lahans.polygon','lahans.village','lahans.kecamatan','lahans.city','lahans.province',
            'lahans.description','lahans.elevation','lahans.soil_type','lahans.current_crops',
            'lahans.active','lahans.farmer_no','lahans.farmer_temp','lahans.mu_no',
            'lahans.target_area','lahans.user_id','lahans.sppt','lahans.tutupan_lahan',
            'lahans.photo1','lahans.photo2','lahans.photo3','lahans.photo4','lahans.group_no','lahans.kelerengan_lahan',
            'lahans.fertilizer','lahans.pesticide','lahans.access_to_water_sources','lahans.water_availability',
            'lahans.access_to_lahan','lahans.potency','lahans.barcode','lahans.lahan_type','lahans.jarak_lahan','lahans.exposure',
            'lahans.opsi_pola_tanam','lahans.type_sppt','lahans.complete_data','lahans.approve', 'lahans.created_time',
            'lahans.pohon_kayu','lahans.pohon_mpts',
            'provinces.name as namaProvinsi','kabupatens.name as namaKabupaten','kecamatans.name as namaKecamatan',
            'desas.name as namaDesa','target_areas.name as namaTa','managementunits.name as namaMu','farmers.name as namaPetani','farmer_groups.name as namaKelompok')
            ->where('lahans.lahan_no', '=', $request->lahan_no)
            ->leftjoin('provinces', 'provinces.province_code', '=', 'lahans.province')
            ->leftjoin('kabupatens', 'kabupatens.kabupaten_no', '=', 'lahans.city')
            ->leftjoin('kecamatans', 'kecamatans.kode_kecamatan', '=', 'lahans.kecamatan')
            ->leftjoin('desas', 'desas.kode_desa', '=', 'lahans.village')
            ->leftjoin('target_areas', 'target_areas.area_code', '=', 'lahans.target_area')
            ->leftjoin('managementunits', 'managementunits.mu_no', '=', 'lahans.mu_no')
            ->leftjoin('farmers', 'farmers.farmer_no', '=', 'lahans.farmer_no')
            ->leftjoin('farmer_groups', 'farmer_groups.group_no', '=', 'lahans.group_no')
            ->first();            
            
            if($GetLahanDetail){
                $getDetailTreesLAhan =  DB::table('lahan_details')->select('lahan_details.id','lahan_details.lahan_no','lahan_details.tree_code','lahan_details.amount',
                'lahan_details.detail_year','trees.tree_category','trees.tree_name')
                ->leftjoin('trees', 'trees.tree_code', '=', 'lahan_details.tree_code')
                ->where('lahan_details.lahan_no', '=', $GetLahanDetail->lahan_no)
                ->get();

                // var_dump($getDetailTreesLAhan);
                $getPivot = MainPivot::where([
                        'key2' => $GetLahanDetail->lahan_no,
                        'key1' => $GetLahanDetail->farmer_no,
                        'type' => 'farmer_lahan'
                    ])->first();
                
                $getTutupan = LahanTutupan::where([
                        'lahan_no' => $GetLahanDetail->lahan_no
                        // 'program_year' => $request->year
                    ])->orderBy('id','desc')->first();

                $LahanDetailNew = ['id'=>$GetLahanDetail->id,'lahan_no'=>$GetLahanDetail->lahan_no,'document_no'=>$GetLahanDetail->document_no,'internal_code'=>$GetLahanDetail->internal_code,'land_area'=>$GetLahanDetail->land_area,'planting_area'=>$GetLahanDetail->planting_area,
                'longitude'=>$GetLahanDetail->longitude,'latitude'=>$GetLahanDetail->latitude,'coordinate'=>$GetLahanDetail->coordinate,'polygon'=>$GetLahanDetail->polygon,'village'=>$GetLahanDetail->village,
                'kecamatan'=>$GetLahanDetail->kecamatan,'city'=>$GetLahanDetail->city,'province'=>$GetLahanDetail->province,'description'=>$GetLahanDetail->description,'elevation'=>$GetLahanDetail->elevation,
                'soil_type'=>$GetLahanDetail->soil_type,'current_crops'=>$GetLahanDetail->current_crops,'active'=>$GetLahanDetail->active,'farmer_no'=>$GetLahanDetail->farmer_no,'farmer_temp'=>$GetLahanDetail->farmer_temp,
                'mu_no'=>$GetLahanDetail->mu_no,'target_area'=>$GetLahanDetail->target_area,'user_id'=>$GetLahanDetail->user_id,'sppt'=>$GetLahanDetail->sppt,'tutupan_lahan'=>$GetLahanDetail->tutupan_lahan,
                'complete_data'=>$GetLahanDetail->complete_data,'approve'=>$GetLahanDetail->approve, 'created_time'=>$GetLahanDetail->created_time,
                'tutupan_lahan'=>$GetLahanDetail->tutupan_lahan,'photo1'=>$GetLahanDetail->photo1,'photo2'=>$GetLahanDetail->photo2,'photo3'=>$GetLahanDetail->photo3,'photo4'=>$GetLahanDetail->photo4,
                'group_no'=>$GetLahanDetail->group_no,'kelerengan_lahan'=>$GetLahanDetail->kelerengan_lahan,'fertilizer'=>$GetLahanDetail->fertilizer,'pesticide'=>$GetLahanDetail->pesticide,'access_to_water_sources'=>$GetLahanDetail->access_to_water_sources,
                'water_availability'=>$GetLahanDetail->water_availability,'access_to_lahan'=>$GetLahanDetail->access_to_lahan,'potency'=>$GetLahanDetail->potency,'barcode'=>$GetLahanDetail->barcode,'lahan_type'=>$GetLahanDetail->lahan_type,
                'jarak_lahan'=>$GetLahanDetail->jarak_lahan,'exposure'=>$GetLahanDetail->exposure,'namaProvinsi'=>$GetLahanDetail->namaProvinsi,'namaKabupaten'=>$GetLahanDetail->namaKabupaten,'namaKecamatan'=>$GetLahanDetail->namaKecamatan,
                'namaDesa'=>$GetLahanDetail->namaDesa,'namaTa'=>$GetLahanDetail->namaTa,'namaMu'=>$GetLahanDetail->namaMu,'namaPetani'=>$GetLahanDetail->namaPetani,'namaKelompok'=>$GetLahanDetail->namaKelompok,
                'pohon_kayu'=>$GetLahanDetail->pohon_kayu,'pohon_mpts'=>$GetLahanDetail->pohon_mpts,'desa'=>$GetLahanDetail->namaDesa.' - '.$GetLahanDetail->namaKecamatan,
                'opsi_pola_tanam'=>$GetLahanDetail->opsi_pola_tanam,'type_sppt'=>$GetLahanDetail->type_sppt,'year' => $getPivot->program_year ?? '','tutupan' => $getTutupan->tutupan_lahan ?? '-','pola_tanam' => $getTutupan->pattern ?? '-','DetailTreesLahan'=>$getDetailTreesLAhan];
                    
                $rslt =  $this->ResultReturn(200, 'success', $LahanDetailNew);
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
     *   path="/api/GetLahanDetailBarcode",
     *   tags={"Lahan"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Lahan Detail Barcode",
     *   operationId="GetLahanDetailBarcode",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="barcode",in="query", required=true, type="string")
     * )
     */
    public function GetLahanDetailBarcode(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'barcode' => 'required|string|max:255'
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
            $GetLahanDetailBarcode = 
            DB::table('lahans')->where('lahans.barcode', '=', $request->barcode)
            ->leftjoin('provinces', 'provinces.province_code', '=', 'lahans.province')
            ->leftjoin('kabupatens', 'kabupatens.kabupaten_no', '=', 'lahans.city')
            ->leftjoin('kecamatans', 'kecamatans.kode_kecamatan', '=', 'lahans.kecamatan')
            ->leftjoin('desas', 'desas.kode_desa', '=', 'lahans.village')
            ->first();
            if($GetLahanDetailBarcode){
                $rslt =  $this->ResultReturn(200, 'success', $GetLahanDetailBarcode);
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
     *   path="/api/AddMandatoryLahan",
	 *   tags={"Lahan"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Add Mandatory Lahan",
     *   operationId="AddMandatoryLahan",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Add Mandatory Lahan",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="document_no", type="string", example="0909090909"),
     *              @SWG\Property(property="type_sppt", type="integer", example=1),
     *              @SWG\Property(property="land_area", type="string", example="8200.00"),
     *              @SWG\Property(property="longitude", type="date", example="110.3300613"),
     *              @SWG\Property(property="latitude", type="string", example="-7.580778"),
     *              @SWG\Property(property="village", type="string", example="33.05.10.18"),
     *              @SWG\Property(property="mu_no", type="string", example="025"),
     *              @SWG\Property(property="target_area", type="string", example="025001"),
     *              @SWG\Property(property="farmer_no", type="string", example="F00000001"),
     *              @SWG\Property(property="fertilizer", type="string", example="Kimia"),   
     *              @SWG\Property(property="pesticide", type="string", example="Kimia"),
     *              @SWG\Property(property="sppt", type="string", example="File"),
     *              @SWG\Property(property="active", type="int", example="1"),
     *              @SWG\Property(property="user_id", type="string", example="U0002")
     *          ),
     *      )
     * )
     *
     */
    public function AddMandatoryLahan(Request $request){
        try{
            $validator = Validator::make($request->all(), [                
                'document_no' => 'required|max:255',
                'type_sppt' => 'required',
                'land_area' => 'required',
                'longitude' => 'required',
                'latitude' => 'required',
                'village' => 'required|max:255',
                'target_area' => 'required|max:255',
                'mu_no' => 'required|max:255',
                'active' => 'required|max:1',
                'farmer_no' => 'required',
                'user_id' => 'required',
                'fertilizer' => 'required|max:255',
                'pesticide' => 'required|max:255',
                'sppt' => 'required',
            ]);

            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }

            $coordinate = $this->getCordinate($request->longitude, $request->latitude);

            $getLastIdLahan = Lahan::orderBy('lahan_no','desc')->first(); 
            if($getLastIdLahan){
                $lahan_no = '10_'.str_pad(((int)substr($getLastIdLahan->lahan_no,-10) + 1), 10, '0', STR_PAD_LEFT);
            }else{
                $lahan_no = '10_0000000001';
            }

            $getDesa = Desa::select('kode_desa','name','kode_kecamatan')->where('kode_desa','=',$request->village)->first(); 
            $getKec = Kecamatan::select('kode_kecamatan','name','kabupaten_no')->where('kode_kecamatan','=',$getDesa->kode_kecamatan)->first(); 
            $getKab = Kabupaten::select('kabupaten_no','name','province_code')->where('kabupaten_no','=',$getKec->kabupaten_no)->first(); 
            $getProv = Province::select('province_code','name')->where('province_code','=',$getKab->province_code)->first();
            
            $codeempintern = '';
            $internal_code = '-';
            if($request->type_sppt == 1){
                if($request->user_id){
                    $getUser = User::select('employee_no','role','name')->where('employee_no','=',$request->user_id)->first(); 
                    if($getUser->role == 'ff'){
                        $getUserFF = DB::table('field_facilitators')->where('ff_no','=',$getUser->employee_no)->first();
                        $codeempintern = $getUserFF->fc_no;
                    }else{
                        $codeempintern = $getUser->employee_no;
                    }
                }
                $ss = substr($codeempintern,0,2);
                $tt = str_replace(".","",$getDesa->kode_desa);
                $getLastInternalCodeLahan = Lahan::orderBy('lahan_no','desc')->first(); 
                if($getLastInternalCodeLahan){
                    $internal_code = $ss.$tt.str_pad(((int)substr($getLastInternalCodeLahan->internal_code,-4) + 1), 4, '0', STR_PAD_LEFT);
                }else{
                    $internal_code = $ss.$tt.'00001';
                }
            }

            $description = $this->ReplaceNull($request->description, 'string');
            $photo1 = $this->ReplaceNull($request->photo1, 'string');
            $access_to_water_sources = $this->ReplaceNull($request->access_to_water_sources, 'string');
            $access_to_lahan = $this->ReplaceNull($request->access_to_lahan, 'string');
            $water_availability = $this->ReplaceNull($request->water_availability, 'string');
            $jarak_lahan = $this->ReplaceNull($request->jarak_lahan, 'string');

            $complete_data = 0;
            if($description != "-" && $photo1 != "-" && $access_to_water_sources != "-" && $access_to_lahan != "-" && $jarak_lahan != "-"  && $water_availability != "-")
            {
                $complete_data = 1;
            }

            Lahan::create([
                'lahan_no' => $lahan_no,
                'barcode' => $lahan_no,
                'document_no' => $request->document_no,
                'internal_code' => $internal_code,
                'land_area' => $request->land_area,
                'longitude' => $request->longitude,
                'latitude' => $request->latitude,
                'coordinate' => $coordinate,
                'village' => $request->village,
                'kecamatan' => $getKec->kode_kecamatan,
                'city' => $getKab->kabupaten_no,
                'province' => $getProv->province_code,
                'mu_no' => $request->mu_no,
                'target_area' => $request->target_area,
                'active' => $request->active,
                'farmer_no' => $request->farmer_no,
                'user_id' => $request->user_id,
                'fertilizer' => $request->fertilizer,
                'pesticide' => $request->pesticide,
                'sppt' => $request->sppt,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),

                'planting_area' => $this->ReplaceNull($request->planting_area, 'int'),
                'polygon' => $this->ReplaceNull($request->polygon, 'string'),
                'description' => $this->ReplaceNull($request->description, 'string'),
                'elevation' => $this->ReplaceNull($request->elevation, 'string'),
                'lahan_type' => $this->ReplaceNull($request->lahan_type, 'string'),
                'soil_type' => $this->ReplaceNull($request->soil_type, 'string'),
                'exposure' => $this->ReplaceNull($request->exposure, 'string'),
                'potency' => $this->ReplaceNull($request->potency, 'string'),
                'current_crops' => $this->ReplaceNull($request->current_crops, 'string'),
                'tutupan_lahan' => $this->ReplaceNull($request->tutupan_lahan, 'string'),
                'photo1' => $this->ReplaceNull($request->photo1, 'string'),
                'photo2' => $this->ReplaceNull($request->photo2, 'string'),
                'photo3' => $this->ReplaceNull($request->photo3, 'string'),
                'photo4' => $this->ReplaceNull($request->photo4, 'string'),
                'group_no' => $this->ReplaceNull($request->group_no, 'string'),
                'kelerengan_lahan' => $this->ReplaceNull($request->kelerengan_lahan, 'string'),

                'access_to_water_sources' => $access_to_water_sources,
                'access_to_lahan' => $access_to_lahan,
                'jarak_lahan' => $jarak_lahan,
                'water_availability' => $water_availability,

                'complete_data' =>$complete_data,
                'is_dell' => 0
            ]);
            
            if ($request->year != null) {
                MainPivot::create([
                    'type' => 'farmer_lahan',
                    'key1' => $request->farmer_no,
                    'key2' => $lahan_no,
                    'program_year' => $request->year
                ]);
            }
            
            // LahanTutupan::create([
            //     'lahan_no' => $lahan_no,
            //     'land_area' => $this->ReplaceNull($request->land_area, 'int'),
            //     'planting_area' => $this->ReplaceNull($request->planting_area, 'int'),
            //     'planting_year' => $year,
            //     'sisa_luasan' => null,
            //     'percentage_sisa_luasan' => null,
            //     'created_at'=>Carbon::now(),
            //     'updated_at'=>Carbon::now()
            // ]);
            
            
            // create Lahan Log
            $logData = [
                'status' => 'Created',
                'lahan_no' => $lahan_no,
            ];
            $this->createLogs($logData);
            
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200); 
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }

    /**
     * @SWG\Post(
     *   path="/api/AddMandatoryLahanComplete",
	 *   tags={"Lahan"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Add Mandatory Lahan Complete",
     *   operationId="AddMandatoryLahanComplete",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Add Mandatory Lahan Complete",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="lahan_no", type="string", example="10_000000002"),
     *              @SWG\Property(property="document_no", type="string", example="0909090909"),
     *              @SWG\Property(property="type_sppt", type="integer", example=1),
     *              @SWG\Property(property="land_area", type="string", example="8200.00"),
     *              @SWG\Property(property="longitude", type="date", example="110.3300613"),
     *              @SWG\Property(property="latitude", type="string", example="-7.580778"),
     *              @SWG\Property(property="village", type="string", example="33.05.10.18"),
     *              @SWG\Property(property="mu_no", type="string", example="025"),
     *              @SWG\Property(property="target_area", type="string", example="025001"),
     *              @SWG\Property(property="farmer_no", type="string", example="F00000001"),
     *              @SWG\Property(property="fertilizer", type="string", example="Kimia"),   
     *              @SWG\Property(property="pesticide", type="string", example="Kimia"),
     *              @SWG\Property(property="sppt", type="string", example="File"),
     *              @SWG\Property(property="active", type="int", example="1"),
     *              @SWG\Property(property="user_id", type="string", example="U0002")
     *          ),
     *      )
     * )
     *
     */
    public function AddMandatoryLahanComplete(Request $request){
        try{
            $validator = Validator::make($request->all(), [                
                'lahan_no' => 'required|max:255|unique:lahans',
                'type_sppt' => 'required',
                'land_area' => 'required',
                'longitude' => 'required',
                'latitude' => 'required',
                'village' => 'required',
                'target_area' => 'required',
                'mu_no' => 'required',
                'active' => 'required|max:1',
                'farmer_no' => 'required',
                'user_id' => 'required',
                'fertilizer' => 'required',
                'pesticide' => 'required',
                'sppt' => 'required',
            ]);

            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }

            $coordinate = $this->getCordinate($request->longitude, $request->latitude);

            // $getLastIdLahan = Lahan::orderBy('lahan_no','desc')->first(); 
            // if($getLastIdLahan){
            //     $lahan_no = '10_'.str_pad(((int)substr($getLastIdLahan->lahan_no,-10) + 1), 10, '0', STR_PAD_LEFT);
            // }else{
            //     $lahan_no = '10_0000000001';
            // }

            $getDesa = Desa::select('kode_desa','name','kode_kecamatan')->where('kode_desa','=',$request->village)->first(); 
            $getKec = Kecamatan::select('kode_kecamatan','name','kabupaten_no')->where('kode_kecamatan','=',$getDesa->kode_kecamatan)->first(); 
            $getKab = Kabupaten::select('kabupaten_no','name','province_code')->where('kabupaten_no','=',$getKec->kabupaten_no)->first(); 
            $getProv = Province::select('province_code','name')->where('province_code','=',$getKab->province_code)->first();
            
            
            $codeempintern = '';
            $internal_code = '-';
            if($request->type_sppt != 0){
                // if($request->user_id){
                //     $getUser = User::select('employee_no','role','name')->where('employee_no','=',$request->user_id)->first(); 
                //     if($getUser->role == 'ff'){
                //         $getUserFF = DB::table('field_facilitators')->where('ff_no','=',$getUser->employee_no)->first();
                //         $codeempintern = $getUserFF->fc_no;
                //     }else{
                //         $codeempintern = $getUser->employee_no;
                //     }
                // }
                // $ss = substr($codeempintern,0,2);
                // $tt = str_replace(".","",$getDesa->kode_desa);
                // var_dump($getDesa->kode_desa);
                // var_dump(Carbon::now()->format('Y'));
                // var_dump($request->mu_no);
                // var_dump($request->lahan_no);
                // $barcodestring = str.substring(3, 13) ;
                // var_dump($barcodestring);

                $tt = $getDesa->kode_desa;
                $year = Carbon::now()->format('Y');
                $mu = $request->mu_no;
                $str = $request->lahan_no;
                $barcodestring = substr($str,3, 13) ;

                $internal_code = $tt.$year.$mu.$barcodestring;

                
                // var_dump($internal_code);

                // $getLastInternalCodeLahan = Lahan::orderBy('lahan_no','desc')->first(); 
                // if($getLastInternalCodeLahan){
                //     $internal_code = $tt.str_pad(((int)substr($getLastInternalCodeLahan->internal_code,-10) + 1), 10, '0', STR_PAD_LEFT);
                // }else{
                //     $internal_code = $tt.'0000000001';
                // }
            }
            // var_dump('test');
            $description = $this->ReplaceNull($request->description, 'string');
            $photo1 = $this->ReplaceNull($request->photo1, 'string');
            $access_to_water_sources = $this->ReplaceNull($request->access_to_water_sources, 'string');
            $access_to_lahan = $this->ReplaceNull($request->access_to_lahan, 'string');
            $water_availability = $this->ReplaceNull($request->water_availability, 'string');
            $jarak_lahan = $this->ReplaceNull($request->jarak_lahan, 'string');

            $complete_data = 0;
            if($description != "-" && $photo1 != "-" && $access_to_water_sources != "-" && $access_to_lahan != "-" && $jarak_lahan != "-"  && $water_availability != "-")
            {
                $complete_data = 1;
            }

            // var_dump($request->lahan_no);
            Lahan::create([
                'lahan_no' => $request->lahan_no,
                'barcode' => $request->lahan_no,
                'document_no' => $request->document_no,
                'internal_code' => $internal_code,
                'land_area' => $request->land_area,
                'longitude' => $request->longitude,
                'latitude' => $request->latitude,
                'coordinate' => $coordinate,
                'village' => $request->village,
                'kecamatan' => $getKec->kode_kecamatan,
                'city' => $getKab->kabupaten_no,
                'province' => $getProv->province_code,
                'mu_no' => $request->mu_no,
                'target_area' => $request->target_area,
                'active' => $request->active,
                'farmer_no' => $request->farmer_no,
                'farmer_temp' => $this->ReplaceNull($request->farmer_temp, 'string'),
                'user_id' => $request->user_id,
                'fertilizer' => $request->fertilizer,
                'pesticide' => $request->pesticide,
                'sppt' => $request->sppt,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),

                'planting_area' => $this->ReplaceNull($request->planting_area, 'int'),
                'polygon' => $this->ReplaceNull($request->polygon, 'string'),
                'description' => $this->ReplaceNull($request->description, 'string'),
                'elevation' => $this->ReplaceNull($request->elevation, 'string'),
                'lahan_type' => $this->ReplaceNull($request->lahan_type, 'string'),
                'soil_type' => $this->ReplaceNull($request->soil_type, 'string'),
                'exposure' => $this->ReplaceNull($request->exposure, 'string'),
                'potency' => $this->ReplaceNull($request->potency, 'string'),
                'current_crops' => $this->ReplaceNull($request->current_crops, 'string'),
                'tutupan_lahan' => $this->ReplaceNull($request->tutupan_lahan, 'string'),
                'photo1' => $this->ReplaceNull($request->photo1, 'string'),
                'photo2' => $this->ReplaceNull($request->photo2, 'string'),
                'photo3' => $this->ReplaceNull($request->photo3, 'string'),
                'photo4' => $this->ReplaceNull($request->photo4, 'string'),
                'group_no' => $this->ReplaceNull($request->group_no, 'string'),
                'kelerengan_lahan' => $this->ReplaceNull($request->kelerengan_lahan, 'string'),

                'access_to_water_sources' => $access_to_water_sources,
                'access_to_lahan' => $access_to_lahan,
                'jarak_lahan' => $jarak_lahan,
                'water_availability' => $water_availability,

                // 'opsi_pola_tanam' => $this->ReplaceNull($request->opsi_pola_tanam, 'string'),
                // 'pohon_kayu' => $this->ReplaceNull($request->pohon_kayu, 'string'),
                // 'pohon_mpts' => $this->ReplaceNull($request->pohon_mpts, 'string'),
                // 'type_sppt' => $this->ReplaceNull($request->type_sppt, 'int'),

                'complete_data' =>$complete_data,
                'is_dell' => 0
            ]);
            
            // LahanTutupan::create([
            //     'lahan_no' => $request->lahan_no,
            //     'land_area' => $this->ReplaceNull($request->land_area, 'int'),
            //     'planting_area' => $this->ReplaceNull($request->planting_area, 'int'),
            //     'planting_year' => $year,
            //     'sisa_luasan' => null,
            //     'percentage_sisa_luasan' => null,
            //     'created_at'=>Carbon::now(),
            //     'updated_at'=>Carbon::now()
            // ]);
            
            
            // create Lahan Log
            $logData = [
                'status' => 'Created',
                'lahan_no' => $request->lahan_no,
            ];
            $this->createLogs($logData);
            
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200); 
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }

    /**
     * @SWG\Post(
     *   path="/api/VerificationLahan",
	 *   tags={"Lahan"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Verification Lahan",
     *   operationId="VerificationLahan",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Verification Lahan",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="id", type="string", example="2")
     *          ),
     *      )
     * )
     *
     */
    public function VerificationLahan(Request $request){
        try{
            $validator = Validator::make($request->all(), [    
                'id' => 'required'
            ]);

            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
            Lahan::where('id', '=', $request->id)
                    ->update
                    ([
                        'complete_data' => 1,
                        'approve' => 1,
                    ]);
            
            
            // create Lahan Log
            $logData = [
                'status' => 'Verified',
                'lahan_no' => Lahan::where('id', '=', $request->id)->first()->lahan_no ?? '999999999999999999',
            ];
            $this->createLogs($logData);
            
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200); 
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }
    
    public function UnverificationLahan(Request $req){
        $validator = Validator::make($req->all(), [    
            'lahan_no' => 'required|exists:lahans,lahan_no',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->first(), 400);
        } else {
            // Lahan
            $lahan = DB::table('lahans')->where('lahan_no', $req->lahan_no)->first();
            if ($lahan) {
                $py = date('Y', strtotime($lahan->created_time));
                $lahanBibit = DB::table('lahan_details')->where('lahan_no', $lahan->lahan_no)->get();
                $farmer = DB::table('farmers')->where('farmer_no', $lahan->farmer_no)->first();
                $farmerLahan = DB::table('lahans')->where('farmer_no', $lahan->farmer_no)->pluck('lahan_no');
                $ff = DB::table('field_facilitators')->where('ff_no', $lahan->user_id)->first();
                $fc = DB::table('employees')->where('nik', $ff->fc_no)->first();
                // Create Log
                $logData = [
                    'status' => 'Unverified',
                    'lahan_no' => $req->lahan_no,
                ];
                $this->createLogs($logData);
                // Update Data
                Lahan::where('lahan_no', $req->lahan_no)->update(['approve' => 0]);
            } else return response()->json('Lahan not found.', 404);
            // Sosialisasi Tanam 
            $sostam = DB::table('planting_socializations')->where(['no_lahan' => $lahan->lahan_no, 'planting_year' => $py])->first();
            if ($sostam) {
                $sostamBibit = DB::table('planting_details')->where('form_no', $sostam->form_no)->get();
                $sostamPeriod = DB::table('planting_period')->where('form_no', $sostam->form_no)->first();
                // Create Log
                $sostamLog = "Deleted $sostam->form_no [lahan=$lahan->lahan_no, petani=$farmer->farmer_no-$farmer->name-$farmer->nickname, ff=$ff->ff_no-$ff->name, fc=$fc->nik-$fc->name] by " . (Auth::user()->email ?? '-');
                Log::channel('planting_socializations')->alert($sostamLog);
                // Delete Data
                DB::table('planting_socializations')->where(['no_lahan' => $lahan->lahan_no, 'planting_year' => $py])->delete();
                DB::table('planting_details')->where('form_no', $sostam->form_no)->delete();
                DB::table('planting_period')->where('form_no', $sostam->form_no)->delete();
            }
            // Penilikan Lubang
            $penlub = DB::table('planting_hole_surviellance')->where(['lahan_no' => $lahan->lahan_no, 'planting_year' => $py])->first();
            if ($penlub) {
                $penlubBibit = DB::table('planting_hole_details')->where('ph_form_no', $penlub->ph_form_no)->get();
                // Create Log
                $penlubLog = "Deleted $penlub->ph_form_no [lahan=$lahan->lahan_no, petani=$farmer->farmer_no-$farmer->name-$farmer->nickname, ff=$ff->ff_no-$ff->name, fc=$fc->nik-$fc->name] by " . (Auth::user()->email ?? '-');
                Log::channel('planting_holes')->alert($penlubLog);
                // Update is Checked PH other land in farmer
                DB::table('planting_hole_surviellance')->whereIn('lahan_no', $farmerLahan)->where(['planting_year' => $py])->update(['is_checked' => 0]);
                // Delete Data
                DB::table('planting_hole_surviellance')->where(['lahan_no' => $lahan->lahan_no, 'planting_year' => $py])->delete();
                DB::table('planting_hole_surviellance')->where(['lahan_no' => $lahan->lahan_no, 'planting_year' => $py])->delete();
                DB::table('planting_hole_details')->where('ph_form_no', $penlub->ph_form_no)->delete();
            }
            // Material Organic
            $organic = DB::table('organics')->where(['farmer_no' => $lahan->farmer_no, ['organic_no', 'LIKE', '%-' . $py . '-%']])->get();
            if (count($organic) > 0) {
                $organicLog = [];
                foreach ($organic as $org) {
                    // Create Log
                    $organicLogMessage = "Deleted $org->organic_no - $org->organic_name [petani=$farmer->farmer_no-$farmer->name-$farmer->nickname, ff=$ff->ff_no-$ff->name, fc=$fc->nik-$fc->name] by " . (Auth::user()->email ?? '-');
                    Log::channel('material_organics')->alert($organicLogMessage);
                    array_push($organicLog, $organicLogMessage);
                }
                // Delete Data
                DB::table('organics')->where(['farmer_no' => $lahan->farmer_no, ['organic_no', 'LIKE', '%-' . $py . '-%']])->delete();
            }
            // Distribusi
            $distribusi = DB::table('distributions')->where(['farmer_no' => $lahan->farmer_no, ['distribution_no', 'LIKE', 'D-'.$py.'-%']])->first();
            if ($distribusi) {
                $distribusiLabel = DB::table('distribution_details')->where('distribution_no', $distribusi->distribution_no)->get();
                $distribusiAdjustment = DB::table('distribution_adjustments')->where('distribution_no', $distribusi->distribution_no)->get();
                // Create Log
                $distribusiLog = "Deleted $distribusi->distribution_no [petani=$farmer->farmer_no-$farmer->name-$farmer->nickname, ff=$ff->ff_no-$ff->name, fc=$fc->nik-$fc->name] by " . (Auth::user()->email ?? '-');
                Log::channel('distributions')->alert($distribusiLog);
                // Delete Data
                DB::table('distributions')->where(['farmer_no' => $lahan->farmer_no, ['distribution_no', 'LIKE', 'D-'.$py.'-%']])->delete();
                DB::table('distribution_details')->where('distribution_no', $distribusi->distribution_no)->delete();
                DB::table('distribution_adjustments')->where('distribution_no', $distribusi->distribution_no)->delete();
            }
            // Realisasi Tanam
            $mon1 = DB::table('monitorings')->where(['planting_year' => $py, 'farmer_no' => $lahan->farmer_no])->first();
            if ($mon1) {
                $mon1Bibit = DB::table('monitoring_details')->where('monitoring_no', $mon1->monitoring_no)->get();
                // Create Log
                $mon1Log = "Deleted $mon1->monitoring_no [lahan=$mon1->lahan_no, petani=$farmer->farmer_no-$farmer->name-$farmer->nickname, ff=$ff->ff_no-$ff->name, fc=$fc->nik-$fc->name] by " . (Auth::user()->email ?? '-');
                Log::channel('monitoring_1')->alert($mon1Log);
                // Delete Data
                DB::table('monitorings')->where(['planting_year' => $py, 'farmer_no' => $lahan->farmer_no])->delete();
                DB::table('monitoring_details')->where('monitoring_no', $mon1->monitoring_no)->delete();
            }
            
            $datas = (object)[
                'program_year' => $py,
                'lahan' => (object)[
                    'main' => $lahan,
                    'bibit' => $lahanBibit,
                ],
                'sostam' => (object)[
                    'main' => $sostam,
                    'bibit' => $sostamBibit ?? [],
                    'period' => $sostamPeriod ?? '',
                    'log' => $sostamLog ?? '-'
                ],
                'penlub' => (object)[
                    'main' => $penlub,
                    'bibit' => $penlubBibit ?? [],
                    'log' => $penlubLog ?? '-'
                ],
                'material_organic' => (object)[
                    'data' => $organic,
                    'log' => $organicLog ?? '-'
                ],
                'distribusi' => (object)[
                    'main' => $distribusi,
                    'label' => $distribusiLabel ?? [],
                    'adjustment' => $distribusiAdjustment ?? [],
                    'log' => $distribusiLog ?? '-'
                ],
                'mon1' => (object)[
                    'main' => $mon1,
                    'bibit' => $mon1Bibit ?? [],
                    'log' => $mon1Log ?? '-'
                ],
            ];
            return response()->json($datas, 200);
        }
    }

    /**
     * @SWG\Post(
     *   path="/api/AddMandatoryLahanBarcode",
	 *   tags={"Lahan"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Add Mandatory Lahan Barcode",
     *   operationId="AddMandatoryLahanBarcode",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Add Mandatory Lahan Barcode",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="user_id", type="string", example="U0001"),
     *              @SWG\Property(property="farmer_no", type="string", example="F00000001"),
     *              @SWG\Property(property="farmer_temp", type="string", example="bambang"),
     *              @SWG\Property(property="barcode", type="string", example="L0000001"),
     *              @SWG\Property(property="longitude", type="string", example="110.3300613"),
     *              @SWG\Property(property="latitude", type="string", example="-7.580778")
     *          ),
     *      )
     * )
     *
     */
    public function AddMandatoryLahanBarcode(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'farmer_temp' => 'required', 
                'farmer_no' => 'required', 
                'barcode' => 'required|max:255|unique:lahans',
                'longitude' => 'required|max:255',
                'latitude' => 'required|max:255'
            ]);

            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }

            $coordinate = $this->getCordinate($request->longitude, $request->latitude);

            $kec= '-';
            $city= '-';
            $province= '-';

            if($request->village){
                $getDesa = Desa::select('kode_desa','name','kode_kecamatan')->where('kode_desa','=',$request->village)->first(); 
                $getKec = Kecamatan::select('kode_kecamatan','name','kabupaten_no')->where('kode_kecamatan','=',$getDesa->kode_kecamatan)->first(); 
                $getKab = Kabupaten::select('kabupaten_no','name','province_code')->where('kabupaten_no','=',$getKec->kabupaten_no)->first(); 
                $getProv = Province::select('province_code','name')->where('province_code','=',$getKab->province_code)->first();
            
                $kec = $getKec->kode_kecamatan;
                $city = $getKab->kabupaten_no;
                $province = $getProv->province_code; 
            }
            


            $codeempintern = '';
            $internal_code = '-';
            if($request->type_sppt){
                if($request->type_sppt == 1 || $request->type_sppt == 2 || $request->type_sppt == 3){

                    $tt = $getDesa->kode_desa;
                    $year = Carbon::now()->format('Y');
                    $mu = $request->mu_no;
                    $str = $request->barcode;
                    $barcodestring = substr($str,3, 13) ;

                    $internal_code = $tt.$year.$mu.$barcodestring;

                }
            }

            Lahan::create([
                'lahan_no' => $request->barcode,
                'longitude' => $request->longitude,
                'latitude' => $request->latitude,
                'coordinate' => $coordinate,
                'barcode' => $request->barcode,
                'farmer_temp' => $request->farmer_temp,
                'farmer_no' => $request->farmer_no,
                'user_id' => $request->user_id,

                'internal_code' => $internal_code,

                'document_no' => $this->ReplaceNull($request->document_no, 'string'),
                'land_area' => $this->ReplaceNull($request->land_area, 'int'),                
                'village' => $this->ReplaceNull($request->village, 'string'),
                'kecamatan' => $kec,
                'city' => $city,
                'province' => $province,
                'mu_no' => $this->ReplaceNull($request->mu_no, 'string'),
                'target_area' => $this->ReplaceNull($request->target_area, 'string'),
                'active' => $this->ReplaceNull($request->active, 'int'),
                // 'farmer_no' => $this->ReplaceNull($request->farmer_no, 'string'),                
                'fertilizer' => $this->ReplaceNull($request->fertilizer, 'string'),
                'pesticide' => $this->ReplaceNull($request->pesticide, 'string'),
                'sppt' => $this->ReplaceNull($request->sppt, 'string'),
                'created_time' => $request->created_time,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),

                'planting_area' => $this->ReplaceNull($request->planting_area, 'int'),
                'polygon' => $this->ReplaceNull($request->polygon, 'string'),
                'description' => $this->ReplaceNull($request->description, 'string'),
                'elevation' => $this->ReplaceNull($request->elevation, 'string'),
                'soil_type' => $this->ReplaceNull($request->soil_type, 'string'),
                'current_crops' => $this->ReplaceNull($request->current_crops, 'string'),
                'tutupan_lahan' => $this->ReplaceNull($request->tutupan_lahan, 'string'),
                'photo1' => $this->ReplaceNull($request->photo1, 'string'),
                'photo2' => $this->ReplaceNull($request->photo2, 'string'),
                'photo3' => $this->ReplaceNull($request->photo3, 'string'),
                'photo4' => $this->ReplaceNull($request->photo4, 'string'),
                'group_no' => $this->ReplaceNull($request->group_no, 'string'),
                'kelerengan_lahan' => $this->ReplaceNull($request->kelerengan_lahan, 'string'),
                'access_to_water_sources' => $this->ReplaceNull($request->access_to_water_sources, 'string'),
                'access_to_lahan' => $this->ReplaceNull($request->access_to_lahan, 'string'),
                'lahan_type' => $this->ReplaceNull($request->lahan_type, 'string'),
                
                // 'access_to_water_sources' => $access_to_water_sources,
                // 'access_to_lahan' => $access_to_lahan,
                'jarak_lahan' =>$this->ReplaceNull($request->jarak_lahan, 'string'),
                'water_availability' => $this->ReplaceNull($request->water_availability, 'string'),

                'opsi_pola_tanam' => $this->ReplaceNull($request->opsi_pola_tanam, 'string'),
                'pohon_kayu' => $this->ReplaceNull($request->pohon_kayu, 'int'),
                'pohon_mpts' => $this->ReplaceNull($request->pohon_mpts, 'int'),
                'type_sppt' => $this->ReplaceNull($request->type_sppt, 'int'),

                'is_dell' => 0
            ]);
            
            $document_no = $this->ReplaceNull($request->document_no, 'string');
            $land_area = $this->ReplaceNull($request->land_area, 'int');
            $village = $this->ReplaceNull($request->village, 'string');
            $mu_no = $this->ReplaceNull($request->mu_no, 'string');
            $target_area = $this->ReplaceNull($request->target_area, 'string');

            $fertilizer = $this->ReplaceNull($request->fertilizer, 'string');
            $pesticide = $this->ReplaceNull($request->pesticide, 'string');
            $sppt = $this->ReplaceNull($request->sppt, 'string');

            $description = $this->ReplaceNull($request->description, 'string');
            $photo1 = $this->ReplaceNull($request->photo1, 'string');
            $photo2 = $this->ReplaceNull($request->photo2, 'string');
            $photo3 = $this->ReplaceNull($request->photo3, 'string');
            $photo4 = $this->ReplaceNull($request->photo4, 'string');

            $group_no = $this->ReplaceNull($request->group_no, 'string');
            $planting_area = $this->ReplaceNull($request->planting_area, 'int');
            $polygon = $this->ReplaceNull($request->polygon, 'string');
            $elevation = $this->ReplaceNull($request->elevation, 'string');
            $soil_type = $this->ReplaceNull($request->soil_type, 'string');
            $current_crops = $this->ReplaceNull($request->current_crops, 'string');
            $tutupan_lahan = $this->ReplaceNull($request->tutupan_lahan, 'string');
            $kelerengan_lahan = $this->ReplaceNull($request->kelerengan_lahan, 'string');

            $access_to_water_sources = $this->ReplaceNull($request->access_to_water_sources, 'string');
            $access_to_lahan = $this->ReplaceNull($request->access_to_lahan, 'string');
            $water_availability = $this->ReplaceNull($request->water_availability, 'string');
            $lahan_type = $this->ReplaceNull($request->lahan_type, 'string');
            $potency = $this->ReplaceNull($request->potency, 'string');
            $jarak_lahan = $this->ReplaceNull($request->jarak_lahan, 'string');
            $exposure = $this->ReplaceNull($request->exposure, 'string');           

            if($document_no != "-" &&$land_area != 0 &&$village != "-" &&$mu_no != "-" &&$target_area != "-" &&$sppt != "-" && $fertilizer != "-" && $pesticide != "-" && $description != "-" &&  $access_to_water_sources != "-" && $access_to_lahan != "-" && $jarak_lahan != "-"  && $water_availability != "-")
            {
                Lahan::where('lahan_no', '=', $request->lahan_no)
                ->update
                (['complete_data' => 1]);
            }
            
            if ($request->year != null) {
                MainPivot::create([
                    'type' => 'farmer_lahan',
                    'key1' => $request->farmer_no,
                    'key2' => $request->barcode,
                    'active' => 1,
                    'program_year' => $request->year
                ]);
                
                LahanTutupan::create([
                    'lahan_no' => $request->barcode,
                    'land_area' => $request->land_area,
                    'planting_area' => $request->planting_area,
                    'program_year' => $request->year,
                    'tutupan_lahan' => $request->tutupan_lahan,
                    'pattern' => $request->opsi_pola_tanam
                ]);
            }
            
            // create Lahan Log
            $logData = [
                'status' => 'Created',
                'lahan_no' => $request->barcode,
            ];
            $this->createLogs($logData);
            
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200); 
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }

    /**
     * @SWG\Get(
     *   path="/api/GetLahanDetailTrees",
     *   tags={"Lahan"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Lahan Detail Trees",
     *   operationId="GetLahanDetailTrees",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="user_id",in="query", required=true, type="string")
     * )
     */
    public function GetLahanDetailTrees(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|string|max:255'
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
            $getDetailTreesLAhan =  DB::table('lahan_details')->select('lahan_details.id','lahan_details.lahan_no','lahan_details.tree_code','lahan_details.amount',
                'lahan_details.detail_year','trees.tree_name')
                ->leftjoin('trees', 'trees.tree_code', '=', 'lahan_details.tree_code')
                ->where('lahan_details.user_id', '=', $request->user_id)
                ->get();
            if($getDetailTreesLAhan){
                $rslt =  $this->ResultReturn(200, 'success', $getDetailTreesLAhan);
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
     *   path="/api/AddDetailLahan",
	 *   tags={"Lahan"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Add Detail Lahan",
     *   operationId="AddDetailLahan",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Add Detail Lahan",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="user_id", type="string", example="U0001"),
     *              @SWG\Property(property="lahan_no", type="string", example="L00000001"),
     *              @SWG\Property(property="tree_code", type="string", example="T0001"),
     *              @SWG\Property(property="amount", type="string", example="50"),
     *              @SWG\Property(property="detail_year", type="string", example="2021-04-20"),
     *          ),
     *      )
     * )
     *
     */
    public function AddDetailLahan(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'lahan_no' => 'required',
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
            // 'lahan_no', 'tree_code', 'amount', 'detail_year', 'user_id','created_at', 'updated_at'
            LahanDetail::create([
                'lahan_no' => $request->lahan_no,
                'tree_code' => $request->tree_code,
                'amount' => $request->amount,
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
     *   path="/api/DeleteDetailLahan",
	 *   tags={"Lahan"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Delete Detail Lahan",
     *   operationId="DeleteDetailLahan",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Delete Detail Lahan",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="id", type="string", example="1")
     *          ),
     *      )
     * )
     *
     */
    public function DeleteDetailLahan(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'id' => 'required'
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
    
            DB::table('lahan_details')->where('id', $request->id)->delete();
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200);
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }

    /**
     * @SWG\Post(
     *   path="/api/UpdateDetailLahanPohon",
	 *   tags={"Lahan"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="UpdateDetailLahanPohon",
     *   operationId="UpdateDetailLahanPohon",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="UpdateDetailLahanPohon",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="user_id", type="string", example="U0001"),
     *              @SWG\Property(property="lahan_no", type="string", example="L00000001"),
     *              @SWG\Property(property="tree_list", type="string", example="T0001"),
     *              @SWG\Property(property="detail_year", type="string", example="2021-04-20"),
     *          ),
     *      )
     * )
     *
     */
    public function UpdateDetailLahanPohon(Request $request){
        $validator = Validator::make($request->all(), [
            'lahan_no' => 'required',
            'tree_list' => 'required', 
            'detail_year' => 'required',
            'user_id' => 'required',
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        }

        $pohon_mpts = 0;
        $pohon_non_mpts = 0;
        $pohon_bawah = 0;
        $luas_area_tanam = 0;

        $listTree = $request->tree_list;

        foreach($listTree as $val){
            if($val['tree_category'] == "Pohon_Buah" || $val['tree_category'] == "MPTS"){
                $pohon_mpts = $pohon_mpts + $val['amount'];
            }else if($val['tree_category'] == "Tanaman_Bawah_Empon" || $val['tree_category'] == "CROPS"){
                $pohon_bawah = $pohon_bawah + $val['amount'];
            }else{
                // $tree = TreeLocation::where('tree_code', $val['tree_code'])->first();
                // if($tree->category == "KAYU"){
                    $pohon_non_mpts = $pohon_non_mpts + $val['amount'];   
                // }else if($tree->category == "MPTS"){
                //     $pohon_mpts = $pohon_mpts + $val['amount'];
                // }else{
                // $pohon_bawah = $pohon_bawah + $val['amount'];
                // }
            }
        }

        // $luas_area_tanam = ($pohon_mpts + $pohon_non_mpts) * 4;
        $tahun = Str::substr($request->detail_year,0,4);

        $getLahan =  DB::table('lahans')
            ->where('lahan_no', '=', $request->lahan_no)
            ->first();
        $luas_area_tanam = $getLahan->planting_area;
        
        if($getLahan->land_area >= $luas_area_tanam){
            DB::table('lahan_details')->where('lahan_no',$request->lahan_no)->where('detail_year', 'LIKE', "{$tahun}%")->delete();

            $listTree = $request->tree_list;
            // var_dump($listTree);
            foreach($listTree as $val){

                // var_dump($val['tree_code']);
                LahanDetail::create([
                    'lahan_no' => $request->lahan_no,
                    'tree_code' => $val['tree_code'],
                    'amount' => $val['amount'],
                    'detail_year' => $request->detail_year,
                    'user_id' => $request->user_id,
    
                    'created_at'=>Carbon::now(),
                    'updated_at'=>Carbon::now()
                ]);
                
                // if($val['tree_category'] == "Pohon_Buah" || $val['tree_category'] == "MPTS"){
                //     $pohon_mpts = $pohon_mpts + $val['amount'];
                // }else{
                //     $pohon_non_mpts = $pohon_non_mpts + $val['amount'];
                // }
            }

            Lahan::where('lahan_no', '=', $request->lahan_no)
            ->update([
                'pohon_mpts' => $pohon_mpts,
                'pohon_kayu' => $pohon_non_mpts,
                'tanaman_bawah' => $pohon_bawah,
                // 'planting_area' => $luas_area_tanam,
                'user_id' => $request->user_id,

                'updated_at'=>Carbon::now()
            ]);

            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200);
        }else{
            $rslt =  $this->ResultReturn(400, 'Pohon terlalu banyak, luas area tidak mencukupi', 'Pohon terlalu banyak, luas area tidak mencukupi');
            return response()->json($rslt, 400);
        }
    }

    /**
     * @SWG\Post(
     *   path="/api/UpdateLahan",
	 *   tags={"Lahan"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Update Lahan",
     *   operationId="UpdateLahan",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Update Lahan",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="lahan_no", type="integer", example="L00000001"),
     *              @SWG\Property(property="document_no", type="string", example="0909090909"),
     *              @SWG\Property(property="type_sppt", type="integer", example=1),
     *              @SWG\Property(property="land_area", type="string", example="8200.00"),
     *              @SWG\Property(property="longitude", type="date", example="110.3300613"),
     *              @SWG\Property(property="latitude", type="string", example="-7.580778"),
     *              @SWG\Property(property="coordinate", type="string", example="S734.847E11019.935"),
     *              @SWG\Property(property="village", type="string", example="33.05.10.18"),
     *              @SWG\Property(property="mu_no", type="string", example="025"),
     *              @SWG\Property(property="target_area", type="string", example="025001"),
     *              @SWG\Property(property="farmer_no", type="string", example="F00000001"),
     *               @SWG\Property(property="farmer_temp", type="string", example="Nullable"),
     *              @SWG\Property(property="fertilizer", type="string", example="Nullable"),   
     *              @SWG\Property(property="pesticide", type="string", example="Nullable"),
     *              @SWG\Property(property="sppt", type="string", example="Nullable"),
     *              @SWG\Property(property="description", type="string", example="Nullable"),
     *              @SWG\Property(property="photo1", type="string", example="Nullable"),
     *              @SWG\Property(property="photo2", type="string", example="Nullable"),
     *              @SWG\Property(property="photo3", type="string", example="Nullable"),
     *              @SWG\Property(property="photo4", type="string", example="Nullable"),
     *              @SWG\Property(property="group_no", type="string", example="Nullable"),
     *              @SWG\Property(property="planting_area", type="string", example="Nullable"),
     *              @SWG\Property(property="polygon", type="string", example="Nullable"),
     *              @SWG\Property(property="elevation", type="string", example="Nullable"),
     *              @SWG\Property(property="soil_type", type="string", example="Nullable"),
     *              @SWG\Property(property="current_crops", type="string", example="Nullable"),
     *              @SWG\Property(property="tutupan_lahan", type="string", example="Nullable"),
     *              @SWG\Property(property="kelerengan_lahan", type="string", example="Nullable"),
     *              @SWG\Property(property="access_to_water_sources", type="string", example="Nullable"),
     *              @SWG\Property(property="access_to_lahan", type="string", example="Nullable"), 
     *              @SWG\Property(property="water_availability", type="string", example="Nullable"),
     *              @SWG\Property(property="lahan_type", type="string", example="Nullable"),
     *              @SWG\Property(property="potency", type="string", example="Nullable"),
     *              @SWG\Property(property="jarak_lahan", type="string", example="Nullable"),
     *              @SWG\Property(property="exposure", type="string", example="Nullable"),  
     *              @SWG\Property(property="opsi_pola_tanam", type="string", example="Nullable"), 
     *              @SWG\Property(property="pohon_kayu", type="string", example="Nullable"), 
     *              @SWG\Property(property="pohon_mpts", type="string", example="Nullable"), 
     *              @SWG\Property(property="active", type="int", example="1"),
     *              @SWG\Property(property="user_id", type="string", example="U0002")
     * 
     *          ),
     *      )
     * )
     *
     */
    public function UpdateLahan(Request $request){
        try{
            $validator = Validator::make($request->all(), [    
                'lahan_no' => 'required',            
                'document_no' => 'required|max:255',
                'type_sppt' => 'required|max:255',
                'land_area' => 'required|max:255',
                'longitude' => 'required|max:255',
                'latitude' => 'required|max:255',
                'village' => 'required|max:255',
                'target_area' => 'required|max:255',
                'mu_no' => 'required|max:255',
                'active' => 'required|max:1',
                'farmer_no' => 'required|max:11',
                'user_id' => 'required|max:11',
            ]);
            
            // create Lahan Log
            $logData = [
                'status' => 'Updated',
                'lahan_no' => $request->lahan_no,
                'updated_data' => $request->all()
            ];
            $this->createLogs($logData);

            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }

            // $getLastIdLahan = Lahan::orderBy('lahan_no','desc')->first(); 
            // if($getLastIdLahan){
            //     $lahan_no = 'L'.str_pad(((int)substr($getLastIdLahan->lahan_no,-8) + 1), 8, '0', STR_PAD_LEFT);
            // }else{
            //     $lahan_no = 'L00000001';
            // }
            // if($request->type_sppt){}
            // else{

            // }

            $getDesa = Desa::select('kode_desa','name','kode_kecamatan')->where('kode_desa','=',$request->village)->first(); 
            $getKec = Kecamatan::select('kode_kecamatan','name','kabupaten_no')->where('kode_kecamatan','=',$getDesa->kode_kecamatan)->first(); 
            $getKab = Kabupaten::select('kabupaten_no','name','province_code')->where('kabupaten_no','=',$getKec->kabupaten_no)->first(); 
            $getProv = Province::select('province_code','name')->where('province_code','=',$getKab->province_code)->first();
            
            // $codeempintern = '';
            // $internal_code = '-';
            // if($request->type_sppt == 1){
            //     if($request->user_id){
            //         $getUser = User::select('employee_no','role','name')->where('employee_no','=',$request->user_id)->first(); 
            //         if($getUser->role == 'ff'){
            //             $getUserFF = DB::table('field_facilitators')->where('ff_no','=',$getUser->employee_no)->first();
            //             $codeempintern = $getUserFF->fc_no;
            //         }else{
            //             $codeempintern = $getUser->employee_no;
            //         }
            //     }
            //     $ss = substr($codeempintern,0,2);
            //     $tt = str_replace(".","",$getDesa->kode_desa);
            //     $getLastInternalCodeLahan = Lahan::orderBy('lahan_no','desc')->first(); 
            //     if($getLastInternalCodeLahan){
            //         $internal_code = $ss.$tt.str_pad(((int)substr($getLastInternalCodeLahan->internal_code,-4) + 1), 4, '0', STR_PAD_LEFT);
            //     }else{
            //         $internal_code = $ss.$tt.'00001';
            //     }
            // }

            $coordinate = $this->getCordinate($request->longitude, $request->latitude);

            $codeempintern = '';
            $internal_code = '-';
            if($request->type_sppt != 0){

                $tt = $getDesa->kode_desa;
                $year = Carbon::now()->format('Y');
                $mu = $request->mu_no;
                $str = $request->lahan_no;
                $barcodestring = substr($str,3, 13) ;

                $internal_code = $tt.$year.$mu.$barcodestring;

            }

            $fertilizer = $this->ReplaceNull($request->fertilizer, 'string');
            $pesticide = $this->ReplaceNull($request->pesticide, 'string');
            $sppt = $this->ReplaceNull($request->sppt, 'string');

            $description = $this->ReplaceNull($request->description, 'string');
            $photo1 = $this->ReplaceNull($request->photo1, 'string');
            $photo2 = $this->ReplaceNull($request->photo2, 'string');
            $photo3 = $this->ReplaceNull($request->photo3, 'string');
            $photo4 = $this->ReplaceNull($request->photo4, 'string');
            $group_no = $this->ReplaceNull($request->group_no, 'string');
            $planting_area = $this->ReplaceNull($request->planting_area, 'int');
            $polygon = $this->ReplaceNull($request->polygon, 'string');
            $elevation = $this->ReplaceNull($request->elevation, 'string');
            $soil_type = $this->ReplaceNull($request->soil_type, 'string');
            $current_crops = $this->ReplaceNull($request->current_crops, 'string');
            $tutupan_lahan = $this->ReplaceNull($request->tutupan_lahan, 'string');
            $kelerengan_lahan = $this->ReplaceNull($request->kelerengan_lahan, 'string');
            $access_to_water_sources = $this->ReplaceNull($request->access_to_water_sources, 'string');
            $access_to_lahan = $this->ReplaceNull($request->access_to_lahan, 'string');
            $water_availability = $this->ReplaceNull($request->water_availability, 'string');
            $lahan_type = $this->ReplaceNull($request->lahan_type, 'string');
            $potency = $this->ReplaceNull($request->potency, 'string');
            $jarak_lahan = $this->ReplaceNull($request->jarak_lahan, 'string');
            $exposure = $this->ReplaceNull($request->exposure, 'string');

            
            Lahan::where('lahan_no', '=', $request->lahan_no)
            ->update([
                'document_no' => $request->document_no,
                'internal_code' => $internal_code,
                'land_area' => $request->land_area,
                'longitude' => $request->longitude,
                'latitude' => $request->latitude,
                'coordinate' => $coordinate,
                'village' => $request->village,
                'kecamatan' => $getKec->kode_kecamatan,
                'city' => $getKab->kabupaten_no,
                'province' => $getProv->province_code,
                'mu_no' => $request->mu_no,
                'target_area' => $request->target_area,
                'active' => $request->active,
                'farmer_no' => $request->farmer_no,
                'farmer_temp' => $this->ReplaceNull($request->farmer_temp, 'string'),
                'user_id' => $request->user_id,

                'fertilizer' => $fertilizer,
                'pesticide' => $pesticide,
                'sppt' => $sppt,

                'updated_at'=>Carbon::now(),

                'access_to_water_sources' => $access_to_water_sources,
                'access_to_lahan' => $access_to_lahan,
                'jarak_lahan' => $jarak_lahan,
                'water_availability' => $water_availability,

                'planting_area' => $planting_area,
                'polygon' => $polygon,
                'description' => $description,
                'exposure' => $exposure,
                'elevation' => $elevation,
                'soil_type' => $soil_type,
                'lahan_type' => $lahan_type,
                'potency' => $potency,
                'current_crops' => $current_crops,
                'tutupan_lahan' => $tutupan_lahan,
                'group_no' => $group_no,
                'kelerengan_lahan' => $kelerengan_lahan,

                'photo1' => $photo1,
                'photo2' => $photo2,
                'photo3' => $photo3,
                'photo4' => $photo4,

                'opsi_pola_tanam' => $this->ReplaceNull($request->opsi_pola_tanam, 'string'),
                'pohon_kayu' => $this->ReplaceNull($request->pohon_kayu, 'int'),
                'pohon_mpts' => $this->ReplaceNull($request->pohon_mpts, 'int'),
                'type_sppt' => $this->ReplaceNull($request->type_sppt, 'int'),
                

                'is_dell' => 0
            ]);
            // var_dump('-');
            // $getUserIdLahan = Lahan::where('id','=',$request->id)->first();
            // if($request->user_id == $getUserIdLahan->user_id ){}
            if($sppt != "-" && $fertilizer != "-" && $pesticide != "-" && $description != "-" &&  $access_to_water_sources != "-" && $access_to_lahan != "-" && $jarak_lahan != "-"  && $water_availability != "-")
            {
                Lahan::where('lahan_no', '=', $request->lahan_no)
                ->update
                (['complete_data' => 1]);
            }
            
            if ($request->year != null) {
                MainPivot::where('type','farmer_lahan')->where('key1',$request->farmer_no)->where('key2',$request->lahan_no)->where('program_year','!=', $request->year)->update(
                    [
                        'program_year' => $request->year ?? ""
                    ]);
                    
                $tutup = LahanTutupan::where([
                    'lahan_no' => $request->lahan_no,
                    'program_year' => substr($request->year,-4)
                ])->first();

                if(is_null($tutup)) {
                    $tutup = new LahanTutupan([
                        'lahan_no' => $request->lahan_no,
                        'program_year' => substr($request->year,-4),
                        'land_area' => $request->land_area,
                        'planting_area' => $request->planting_area,
                        'tutupan_lahan' => $request->tutupan_lahan,
                        'pattern' => $request->opsi_pola_tanam
                    ]);
                    $tutup->save();
                } else {
                    $tutup->land_area = $request->land_area;
                    $tutup->planting_area = $request->planting_area;
                    $tutup->tutupan_lahan = $request->tutupan_lahan;
                    $tutup->pattern = $request->opsi_pola_tanam;
                    $tutup->update();
                }
            }

            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200); 
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }
    
    /**
     * @SWG\Post(
     *   path="/api/UpdateLahanGIS",
	 *   tags={"Lahan"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Update Lahan GIS",
     *   operationId="UpdateLahanGIS",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Update Lahan GIS",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="lahan_no", type="integer", example="L00000001"),
     *              @SWG\Property(property="soil_type", type="string", example="Mandatory"),
     *              @SWG\Property(property="current_crops", type="string", example="Mandatory"),
     *              @SWG\Property(property="tutupan_lahan", type="string", example="Mandatory"),
     *              @SWG\Property(property="kelerengan_lahan", type="string", example="Mandatory"),     * 
     *              @SWG\Property(property="elevation", type="string", example="Mandatory"),
     *              @SWG\Property(property="potency", type="string", example="Mandatory"),
     *              @SWG\Property(property="exposure", type="string", example="Mandatory"),  
     * 
     *          ),
     *      )
     * )
     *
     */
    public function UpdateLahanGIS(Request $request){
        try{
            $validator = Validator::make($request->all(), [    
                'lahan_no' => 'required',           
                'longitude' => 'required',
                'latitude' => 'required',
                'soil_type' => 'required',
                'potency' => 'required',
                'kelerengan_lahan' => 'required',
                'village' => 'required',
                // 'exposure' => 'required',
                'elevation' => 'required',
                // 'current_crops' => 'required',
                'tutupan_lahan' => 'required',
            ]);

            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }

            $coordinate = $this->getCordinate($request->longitude, $request->latitude);
            
            // create Lahan Log
            $logData = [
                'status' => 'Updated GIS',
                'lahan_no' => $request->lahan_no,
                'updated_data' => $request->all()
            ];
            $this->createLogs($logData);
            
            Lahan::where('lahan_no', '=', $request->lahan_no)
            ->update([
                'village' => $request->village,
                'longitude' => $request->longitude,
                'latitude' => $request->latitude,
                'coordinate' => $coordinate,

                // 'exposure' => $request->exposure,
                'elevation' => $request->elevation,
                'soil_type' => $request->soil_type,
                'potency' => $request->potency,
                // 'current_crops' => $request->current_crops,
                'tutupan_lahan' => $request->tutupan_lahan,
                'kelerengan_lahan' => $request->kelerengan_lahan,
                'updated_gis' => 'sudah',

            ]);

            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200); 
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }

    /**
     * @SWG\Post(
     *   path="/api/SoftDeleteLahan",
	 *   tags={"Lahan"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Soft Delete Lahan",
     *   operationId="SoftDeleteLahan",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Soft Delete Lahan",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="id", type="string", example="2")
     *          ),
     *      )
     * )
     *
     */
    public function SoftDeleteLahan(Request $request){
        try{
            $validator = Validator::make($request->all(), [    
                'id' => 'required'
            ]);

            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
            Lahan::where('id', '=', $request->id)
                    ->update
                    ([
                        'is_dell' => 1
                    ]);
                    
            // create Lahan Log
            $logData = [
                'status' => 'Soft Deleted',
                'lahan_no' => Lahan::where('id', '=', $request->id)->first()->lahan_no ?? '9999999999999999'
            ];
            $this->createLogs($logData);
            
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200); 
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }
    
    // Create Logs for Lahan
    public function createLogs($logData) {
        // get lahan data
        $lahan = Lahan::where('lahan_no', $logData['lahan_no'])->first();
        // get Petani Data
        if (isset($lahan->farmer_no)) {
            $farmer = Farmer::where('farmer_no', $lahan->farmer_no)->first();
        }
        // get ff data
        if(isset($lahan->user_id)) {
            $ff = FieldFacilitator::where('ff_no', $lahan->user_id)->first();
        }
        // get fc data
        if (isset($ff->fc_no)) {
            $fc = Employee::where('nik', $ff->fc_no)->first();
        }
        // user auth data
        $user = Auth::user();
        
        // set message
        $message =  $logData['status'] . ' ' . 
                    ($lahan['lahan_no'] ?? '-') . 
                    '[petani = ' . 
                    ($farmer['farmer_no'] ?? '-') . '_' . ($farmer['name'] ?? '-') . '_' . ($farmer['nickname'] ?? '-') .
                    ', ff = ' . 
                    ($ff->ff_no ?? '-') . '_' . ($ff->name ?? '-') .
                    ', fc = ' . 
                    ($fc->name ?? '-') .
                    ']' . 
                    ($logData['status'] == 'Updated' || $logData['status'] == 'Updated GIS'  ? (', Updated data = [' . $this->getSpecificUpdatedLahanData($logData['updated_data']) . '] ') : ' ') .
                    'by ' .
                    ($user['email'] ?? '-');
                    
        $log = Log::channel('lahans');
        
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
    
    // get specific Updated lahan data
    private function getSpecificUpdatedLahanData($data) {
        // get lahan data
        $lahan = Lahan::where('lahan_no', ($data['lahan_no'] ?? '999999999999999'))->first();
        if ($lahan) {
            // $listUpdatedData = []; 
            $listUpdatedData = array_map(function($value, $key) use ($lahan) {
                if (isset($value) && isset($lahan[$key])) {
                    if ($value != $lahan[$key]) {
                        return $key.'=('.$lahan[$key].' => ' .$value . ')';
                    }
                }
            }, array_values($data), array_keys($data));
            
            $listUpdatedData =  array_filter($listUpdatedData);
            return implode(',',$listUpdatedData);
            // return str_replace('&', ',', urldecode(http_build_query($listUpdatedData)));
        } else {
            return '-';
        }
    }
}
