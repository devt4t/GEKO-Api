<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Employee;
use App\Farmer;
use App\FieldFacilitator;
use App\Lahan;
use App\Monitoring;
use App\MonitoringDetail;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class DashboardController extends Controller {
// MAIN FUNCTIONS {
    // get dashboard total datas
    public function all(Request $req) {
        try {
            // validation
            $validator = Validator::make($req->all(), [
                'program_year' => 'required',
                'source' => 'required',
                'province' => 'required'
            ]);

            if($validator->fails()){
                return response()->json($validator->errors()->first(), 400);
            } else $py = $req->program_year;
            
            $mu_no = $req->mu_no ?? '';
            
            $sourceList = ['Lahan', 'Sosialisasi Tanam', 'Penilikan Lubang', 'Distribusi (Loaded)', 'Distribusi (Received)', 'Monitoring 1' ];
            
            // record section
            $records = (object)[
                'employee' => $this->getEmployeeRecord($py),
                'external' => $this->getExternalRecord($py, $req->source, $req->province, $req->ff, $mu_no),
                'land' => $this->getLandRecord($py, $req->source, $req->province, $req->ff, $mu_no),
                'plant' => $this->getPlantRecord($py, $req->source, $req->province, $req->ff, $mu_no)
            ];
            
            
            // set response data
            $datas = [
                'filter' => (object)[
                    'program_year' => $py,
                    'source' => $req->source,
                    'province' => $req->province,
                    'mu' => $mu_no
                ],
                'total' => [
                    'employee' => $records->employee ?? 0,
                    'ff' => $records->external->ff ?? 0,
                    'farmer' => $records->external->farmer ?? 0,
                    'land_total' => $records->land->total,
                    'land_area' => $records->land->area,
                    'trees' => $records->plant
                ]
            ];
            
            // response
            $rslt =  $this->ResultReturn(200, 'success', $datas);
            return response()->json($rslt, 200);
        } catch (\Exception $ex){
            return response()->json($ex);
        } 
    }
    public function totalDatas(Request $req) {
        // validation
        $validator = Validator::make($req->all(), [
            'program_year' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->first(), 400);
        }
        
        // simplify variable
        $py = $req->program_year;
        $mu_no = $req->mu_no ?? '';
            
        // record section
        $records = (object)[
            'employee' => $this->getEmployeeRecord($py),
            'external' => $this->getExternalRecord($py, $req->source, $req->province, $req->ff, $mu_no),
            'land' => $this->getLandRecord($py, $req->source, $req->province, $req->ff, $mu_no),
            'land_general' => $this->getLandGeneralRecord($py, $req->source, $req->province, $req->ff, $mu_no),
            'plant' => $this->getPlantRecord($py, 'Realisasi Tanam', $req->province, $req->ff, $mu_no)
        ];
            
        // set response data
        $datas = [
            'filter' => (object)[
                'program_year' => $py,
                'source' => $req->source,
                'province' => $req->province,
                'mu' => $mu_no
            ],
            'total' => [
                'employee' => $records->employee ?? 0,
                'ff' => $records->external->ff ?? 0,
                'farmer' => $records->external->farmer ?? 0,
                'land_total' => $records->land->total,
                'land_area' => $records->land->area,
                'land_general_total' => $records->land_general->total,
                'land_general_area_total' => $records->land_general->area,
                'trees' => $records->plant
            ]
        ];
        
        // response
        $rslt =  $this->ResultReturn(200, 'success', $datas);
        return response()->json($rslt, 200);
        
    }
    // get maps data
    public function GetDashboardMapData(Request $req) {
        // validation
        $validator = Validator::make($req->all(), [
            'program_year' => 'required',
            'source' => 'required',
            'province' => 'required'
        ]);
    
        if($validator->fails()){
            return response()->json($validator->errors()->first(), 400);
        } else $py = $req->program_year;
        
        $datas = Lahan::
            select(
                'lahans.lahan_no',
                'field_facilitators.name as ff_name',
                'farmers.name as farmer_name',
                'lahans.longitude',
                'lahans.latitude',
                'lahans.planting_area',
                'lahans.province',
                'lahans.city',
                'lahans.kecamatan',
                'lahans.village',
                'lahans.mu_no',
                'lahans.target_area'
            )
            ->join('farmers', 'farmers.farmer_no', 'lahans.farmer_no')
            ->join('field_facilitators', 'field_facilitators.ff_no', 'lahans.user_id')
            ->where([
                'lahans.approve' => 1,
                'lahans.is_dell' => 0,
                'farmers.approve' => 1,
                'farmers.is_dell' => 0,
                ['lahans.user_id', 'LIKE', 'FF%']
            ])
            ->whereYear('created_time', $py);
            
        if ($req->province) if ($req->province != 'all') $datas = $datas->where('lahans.province', $req->province);
        
        if ($req->ff) {
            $ff = explode(',', $req->ff);
            $datas = $datas->whereIn('lahans.user_id', $ff);
        }
        
        $datas = $datas->orderBy('lahans.planting_area', 'desc')->limit(300)->get();
        
        $rslt = [
            'count' => count($datas),
            'list' => $datas
        ];
        return response()->json($rslt, 200);
    }
    // get ff
    public function DetailFieldFacilitator(Request $req) {
        // validation
        $validator = Validator::make($req->all(), [
            'fc_no' => 'required|exists:employees,nik',
            'program_year' => 'required'
        ]);
        if($validator->fails()) return response()->json($validator->errors()->first(), 400);
        
        $py = $req->program_year;
        $fc_no = $req->fc_no;
        
        $datas = DB::table('field_facilitators')
            ->select(
                'field_facilitators.*', 
                'ff_working_areas.*', 
                'managementunits.name as mu_name', 
                'target_areas.name as ta_name',
                'desas.name as desa_name',
                'field_facilitators.id'
            )
            ->join('main_pivots', 'main_pivots.key2', 'field_facilitators.ff_no')
            ->join('ff_working_areas', 'ff_working_areas.ff_no', 'field_facilitators.ff_no')
            ->join('managementunits', 'ff_working_areas.mu_no', 'managementunits.mu_no')
            ->join('target_areas', 'ff_working_areas.area_code', 'target_areas.area_code')
            ->join('desas', 'ff_working_areas.kode_desa', 'desas.kode_desa')
            ->where([
                'main_pivots.type' => 'fc_ff',
                'main_pivots.key1' => $fc_no,
                ['main_pivots.program_year', 'LIKE', "%$py%"],
                ['ff_working_areas.program_year', 'LIKE', "%$py%"],
                'field_facilitators.active' => 1
            ])
            ->get();
        foreach ($datas as $data) {
            $farmer = DB::table('farmers')
                ->join('main_pivots', 'main_pivots.key2', 'farmers.farmer_no')
                ->where([
                    'main_pivots.type' => 'ff_farmer',
                    ['main_pivots.program_year', 'LIKE', "%$py%"],
                    'main_pivots.key1' => $data->ff_no
                ])->pluck('farmer_no');
            $lahan = DB::table('lahans')
                ->join('main_pivots', 'main_pivots.key2', 'lahans.lahan_no')
                ->where([
                    'main_pivots.type' => 'farmer_lahan',
                    ['main_pivots.program_year', 'LIKE', "%$py%"],
                ])
                ->whereIn('main_pivots.key1', $farmer)->groupBy('lahans.lahan_no')->get();
            $data->total_farmer = count($farmer);
            $data->total_lahan = count($lahan);
        }
        
        return response()->json($datas, 200);
        
    }
    // get petani lahan
    public function DetailPetaniLahan(Request $req) {
        // validation
        $validator = Validator::make($req->all(), [
            'ff_no' => 'required|exists:field_facilitators,ff_no',
            'program_year' => 'required'
        ]);
        if($validator->fails()) return response()->json($validator->errors()->first(), 400);
        
        $py = $req->program_year;
        $ff_no = $req->ff_no;
        
        $ff = DB::table('field_facilitators')->where('ff_no', $ff_no)->first();
        $farmers = DB::table('farmers')
            ->select(
                'farmers.farmer_no', 
                'farmers.name as farmer_name',
                'farmers.approve as farmer_status',
                'farmers.ktp_no as farmer_nik',
                'desas.name as desa_name'
            )
            ->join('main_pivots', 'main_pivots.key2', 'farmers.farmer_no')
            ->join('desas', 'desas.kode_desa', 'farmers.village')
            ->where([
                ['main_pivots.program_year', 'LIKE', "%$py%"],
                'main_pivots.key1' => $ff_no,
                'main_pivots.type' => 'ff_farmer'
            ])->get();
        
        $farmers_lahan = [];
        foreach ($farmers as $farmer) {
            $data = (object)[
                'ff_no' => $ff->ff_no,
                'ff_name' => $ff->name, 
                'farmer_name' => $farmer->farmer_name, 
                'farmer_no' => $farmer->farmer_no, 
                'desa_name' => $farmer->desa_name, 
                'farmer_status' => $farmer->farmer_status, 
                'farmer_nik' => $farmer->farmer_nik, 
                
            ];
            // get lahan
            $lahans = DB::table('lahans')
                ->select(
                    'farmers.name as farmer_name',
                    'farmers.farmer_no',
                    'farmers.ktp_no as farmer_nik',
                    'farmers.approve as farmer_status',
                    'lahans.lahan_no',
                    'lahans.land_area',
                    'lahans.document_no',
                    'lahan_tutupans.land_area',
                    'lahan_tutupans.tutupan_lahan',
                    'lahan_tutupans.pattern as opsi_pola_tanam',
                    'lahans.pohon_kayu',
                    'lahans.pohon_mpts',
                    'lahans.complete_data as lahan_complete',
                    'lahans.approve as lahan_approve',
                    'desas.name as desa_name'
                )
                ->join('main_pivots', 'main_pivots.key2', 'lahans.lahan_no')
                ->join('farmers', 'main_pivots.key1', 'farmers.farmer_no')
                ->join('lahan_tutupans', 'lahan_tutupans.lahan_no', 'lahans.lahan_no')
                ->join('desas', 'desas.kode_desa', 'lahans.village')
                ->where([
                    ['main_pivots.program_year', 'LIKE', "%$py%"],
                    'main_pivots.type' => 'farmer_lahan',
                    'lahan_tutupans.program_year' => $py,
                    'main_pivots.key1' => $farmer->farmer_no
                ])->get();
            if (count($lahans) > 0) {
                foreach ($lahans as $lahan) {
                    $data = (object) array_merge((array)$data, (array)$lahan);
                    $jenis_bibit = DB::table('lahan_details')->where([
                            'lahan_details.lahan_no' => $lahan->lahan_no,
                            ['lahan_details.detail_year', 'like', "%$py%"]
                        ])
                        ->join('trees', 'trees.tree_code', 'lahan_details.tree_code')
                        ->pluck('trees.tree_name');
                    $data->jenis_bibit = $jenis_bibit;
                    array_push($farmers_lahan, $data);
                }
            } else {
                array_push($farmers_lahan, $data);
            }
        }
            
        return response()->json($farmers_lahan, 200);
    }
// END: MAIN FUNCTIONS }
// PRIVATE FUNCTIONS {
    private function getEmployeeRecord($py) {
        $data = Employee::where('job_status', 'Active');
        if ($py != 'all') {
            $data = $data->whereYear('created_at', '<=', $py);
        }
        $data = $data->count();
        return $data;
    }
    
    private function getExternalRecord($py, $source = null, $province = null, $ff = null, $mu_no = null) {
        $fList = $this->getFarmerList($py, $source, $province, $ff, $mu_no);
        
        $farmer = Farmer::where(['is_dell' => 0, 'approve' => 1, ['user_id', 'LIKE', 'FF%']])
            ->whereIn('farmer_no', $fList);
            
        if ($province) if ($province != 'all') {
            $farmer = $farmer->where('province', $province);
        }
        
        $farmers = $farmer->count();
        
        $ffList = $farmer->groupBy('user_id')->pluck('user_id');
        
        $ff_list = FieldFacilitator::select('field_facilitators.ff_no')
        ->join('main_pivots', 'main_pivots.key2', 'field_facilitators.ff_no')
        ->join('ff_working_areas', 'ff_working_areas.ff_no', 'field_facilitators.ff_no')->where([
            'main_pivots.type' => 'fc_ff',
            ['field_facilitators.ff_no', 'like', "FF%" ],
            ['main_pivots.program_year', 'like', "%$py%"],
            'field_facilitators.active' => 1,
            ])->groupBy('field_facilitators.ff_no');
        if ($ff) {
            $exFF = $this->getFFListByUserPY($py);
            $ff_list = $ff_list->whereIn('field_facilitators.ff_no', $exFF);
        }
        if ($mu_no) {
            $ff_list = $ff_list->where([
                'ff_working_areas.mu_no' => $mu_no,
                ['ff_working_areas.program_year', 'like', "%$py%"]
            ]);
        }
        $ff_list = $ff_list->get();
        
        $datas = (object)[
            'ff' => count($ff_list),
            'farmer' => $farmers
        ];
        return $datas;
    }
    
    private function getLandRecord($py, $source = null, $province = null, $ff = null, $mu_no = null) {
        $fList = $this->getFarmerList($py, $source, $province, $ff, $mu_no);
        $lahan_nos = DB::table('main_pivots')->where([
                'type' => 'farmer_lahan',
                ['program_year', 'like', "%$py%"]
            ])->whereIn('key1', $fList)->pluck('key2');
        $lahan = Lahan::where(['approve' => 1, ['user_id', 'LIKE', 'FF%'], 'is_dell' => 0])->whereIn('lahan_no', $lahan_nos);
        if ($province) if ($province != 'all') {
            $lahan = $lahan->where('province', $province);
        }
        // get total lahan
        $total = $lahan->count();
        
        // get land areas
        $area = $lahan->sum('planting_area');
        
        $datas = (object)[
            'total' => $total,
            'area' => (int)$area
        ];
        return $datas;
    }
    
    private function getLandGeneralRecord($py, $source, $province, $ff = null, $mu_no = null) {
        $lahan = DB::table('lahan_umums')->where([
                'program_year' => $py,
                ['is_verified', '>', 0]
            ]);
        if ($province) if ($province != 'all') {
            $lahan = $lahan->where('province', $province);
        }
        if ($mu_no) {
            $lahan = $lahan->where('mu_no', $mu_no);
        }
        if ($ff) {
            $lahan = $lahan->whereIn('created_by', explode(',',$ff));
        }
        // get total lahan
        $total = $lahan->count();
        
        // get land areas
        $area = $lahan->sum('luas_tanam');
        
        $datas = (object)[
            'total' => $total,
            'area' => (int)$area
        ];
        return $datas;
    }
    
    private function getPlantRecord($py, $source, $province = null, $ff = null, $mu_no = null) {
        if ($ff) $exFF = explode(',', $ff);
        else $exFF = null;
        if ($source == 'Sosialisasi Tanam') {
            $form_no = DB::table('planting_socializations')
            ->where([
                'planting_socializations.is_dell' => 0, 
                ['planting_socializations.validation', '>=', 1],
                ['planting_socializations.ff_no', 'LIKE', 'FF%'],
                'planting_socializations.planting_year' => $py
            ]);
            if ($province != 'all') {
                $land_no = DB::table('lahans')->whereYear('created_time', $py)->where('province', $province)->pluck('lahan_no');
                $form_no = $form_no->whereIn('no_lahan', $land_no);
            }
            if ($exFF) $form_no = $form_no->whereIn('planting_socializations.ff_no', $exFF);
            $form_no = $form_no->pluck('form_no');
            $datas = DB::table('planting_details')->whereIn('form_no', $form_no)->sum('amount');
        } else if ($source == 'Penilikan Lubang') {
            $form_no = DB::table('planting_hole_surviellance')
            ->where([
                'planting_hole_surviellance.is_dell' => 0, 
                ['planting_hole_surviellance.is_validate', '>=', 1],
                ['planting_hole_surviellance.user_id', 'LIKE', 'FF%'],
                'planting_hole_surviellance.planting_year' => $py
            ]);
            if ($province != 'all') {
                $land_no = DB::table('lahans')->whereYear('created_time', $py)->where('province', $province)->pluck('lahan_no');
                $form_no = $form_no->whereIn('lahan_no', $land_no);
            }
            if ($exFF) $form_no = $form_no->whereIn('planting_hole_surviellance.user_id', $exFF);
            $form_no = $form_no->pluck('ph_form_no');
            $datas = DB::table('planting_hole_details')->whereIn('ph_form_no', $form_no)->sum('amount');
        } else if ($source == 'Distribusi') {
            $datas = DB::table('distribution_adjustments')->where([
                'is_verified' => 1,
                'planting_year' => $py,
                ['ff_no', 'LIKE', 'FF%']
            ]);
            
            if ($province != 'all') {
                $land_no = DB::table('lahans')->whereYear('created_time', $py)->where('province', $province)->pluck('lahan_no');
                $datas = $datas->whereIn('lahan_no', $land_no);
            }
            if ($exFF) $datas = $datas->whereIn('ff_no', $exFF);
            
            $datas = $datas->sum('total_tree_received');
        } else if ($source == 'Realisasi Tanam') {
            $form_no = DB::table('monitorings')->where([
                ['is_validate', 2],
                'planting_year' => $py,
                ['user_id', 'LIKE', 'FF%']
            ]);
            $form_no_mon = DB::table('lahan_umum_monitorings')->where([
                ['is_verified', 2],
                'program_year' => $py,
            ]);
            
            if ($province) if ($province != 'all') {
                $fNo = DB::table('farmers')->where(['province' => $province, 'approve' => 1])->pluck('farmer_no');
                $form_no = $form_no->whereIn('farmer_no', $fNo);
            }
            if ($mu_no) {
                $mou_no = DB::table('lahan_umums')->where([
                    ['is_verified', '>', 0],
                    'mu_no' => $mu_no
                ])->groupBy('mou_no')->pluck('mou_no');
                $form_no_mon = $form_no_mon->whereIn('mou_no', $mou_no);
            }
            if ($exFF) {
                $form_no = $form_no->whereIn('user_id', $exFF);
                $mou_no2 = DB::table('lahan_umums')->where([
                    ['is_verified', '>', 0]
                ])
                ->whereIn('created_at', $exFF)
                ->groupBy('mou_no')->pluck('mou_no');
                $form_no_mon = $form_no_mon->whereIn('mou_no', $mou_no2);
            }
            $fList = $this->getFarmerList($py, null, $province, $ff, $mu_no);
            $form_no = $form_no->whereIn('farmer_no', $fList);
                
            
            $form_no = $form_no->pluck('monitoring_no');
            $form_no_mon = $form_no_mon->pluck('monitoring_no');
            
            $total_lahan_petani = DB::table('monitoring_details')->whereIn('monitoring_no', $form_no)
                ->where(['status' => 'sudah_ditanam', 'condition' => 'hidup'])
                ->sum('qty');
            $total_lahan_umum = DB::table('lahan_umum_monitoring_details')->whereIn('monitoring_no', $form_no_mon)
                ->where(['status' => 'sudah_ditanam', 'condition' => 'hidup'])
                ->sum('qty');
            $datas = $total_lahan_petani + $total_lahan_umum;
        } else $datas = 0;
        return (int)$datas;
    }
// END: PRIVATE FUNCTIONS }
// UTILITIES {
    // get Program Year
    private function getFarmerProgramYear($mou_no) {
        if (substr($mou_no, 13, 1) === '_') {
            return substr($mou_no, 9, 4);
        } else {
            return substr($mou_no, 4, 4);
        }
    }
    // get farmer list
    private function getFarmerList($py, $source = null, $province = null, $ff = null, $mu_no = null) {
        if ($ff) $exFF = $this->getFFListByUserPY($py);
        else $exFF = null;
        if ($source == 'Pendataan') {
            $fList = DB::table('farmers')->where(['is_dell' => 0, 'approve' => 1, ['mou_no', 'LIKE', "%$py". "\_%"], ['user_id', 'LIKE', 'FF%']]);
            if ($exFF) $fList = $fList->whereIn('user_id', $exFF);
            $fList = $fList->pluck('farmer_no');
        } else if ($source == 'Sosialisasi Tanam') {
            $fList = DB::table('planting_socializations')
            ->where([
                'planting_socializations.is_dell' => 0, 
                ['planting_socializations.validation', '>=', 1],
                ['planting_socializations.ff_no', 'LIKE', 'FF%'],
                'planting_socializations.planting_year' => $py
            ]);
            if ($exFF) $fList = $fList->whereIn('planting_socializations.ff_no', $exFF);
            $fList = $fList->pluck('planting_socializations.farmer_no');
        } else if ($source == 'Penilikan Lubang') {
            $fList = DB::table('planting_hole_surviellance')
            ->join('lahans', 'lahans.lahan_no', 'planting_hole_surviellance.lahan_no')
            ->where([
                'planting_hole_surviellance.is_dell' => 0, 
                ['planting_hole_surviellance.is_validate', '>=', 1],
                ['planting_hole_surviellance.user_id', 'LIKE', 'FF%'],
                'planting_hole_surviellance.planting_year' => $py
            ]);
            if ($exFF) $fList = $fList->whereIn('planting_hole_surviellance.user_id', $exFF);
            $fList = $fList->pluck('lahans.farmer_no');
        } else if ($source == 'Distribusi') {
            $fList = DB::table('distribution_adjustments')->where([
                'is_verified' => 1,
                'planting_year' => $py,
                ['ff_no', 'LIKE', 'FF%']
            ]);
            if ($exFF) $fList = $fList->whereIn('ff_no', $exFF);
            $fList = $fList->pluck('farmer_no');
        } else if ($source == 'Realisasi Tanam') {
            $fList = DB::table('monitorings')->where([
                ['is_validate', '>=', 1],
                'planting_year' => $py,
                ['user_id', 'LIKE', 'FF%']
            ]);
            if ($exFF) $fList = $fList->whereIn('user_id', $exFF);
            $fList = $fList->pluck('farmer_no');
        } else {
            $fList = DB::table('main_pivots')
            ->join('farmers', 'farmers.farmer_no', 'main_pivots.key2')
            ->join('field_facilitators', 'field_facilitators.ff_no', 'main_pivots.key1')
            ->where([
                'farmers.is_dell' => 0, 
                'farmers.approve' => 1,  
                ['farmers.user_id', 'LIKE', 'FF%'],
                ['field_facilitators.name', 'NOT LIKE', '%FF%'],
                ['main_pivots.program_year', 'like', "%$py%"],
                'main_pivots.type' => "ff_farmer"
            ]);
            if ($exFF) $fList = $fList->whereIn('farmers.user_id', $exFF);
            if ($mu_no) $fList = $fList->where('farmers.mu_no', $mu_no);
            $fList = $fList->pluck('farmers.farmer_no');
        };
        return $fList;
    }
// END: UTILITIES }
}