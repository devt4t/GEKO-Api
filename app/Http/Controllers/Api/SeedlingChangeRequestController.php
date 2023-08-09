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
use App\Monitoring;
use App\MonitoringDetail;
use App\Monitoring2;
use App\Monitoring2Detail;
use App\SCR;
use App\SCRSeed;

class SeedlingChangeRequestController extends Controller
{
    // get data {
        // get data options {
    public function GetMU(Request $req) {
        $validator = Validator::make($req->all(), [
            'program_year' => 'required',
            'land_program' => 'required',
            'nursery' => 'required',
            'distribution_date' => 'required|date',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->first(), 400);
        } else {
            $py = $req->program_year;
            $lp = $req->land_program;
            $dd = $req->distribution_date;
        }
        
        if ($lp == 'Petani') {
            $form_no_period = DB::table('planting_period')
                ->whereDate('distribution_time', $dd)
                ->pluck('form_no');
            
            $mu_no = DB::table('planting_socializations')
                ->join('field_facilitators', 'field_facilitators.ff_no', 'planting_socializations.ff_no')
                ->where([
                    'planting_socializations.planting_year' => $py,
                    'planting_socializations.validation' => 1,
                    'planting_socializations.is_dell' => 0
                ])
                ->whereIn('form_no', $form_no_period)
                ->whereIn('field_facilitators.mu_no', $this->getNurseryAlocationReverseGlobal($req->nursery))
                ->groupBy('field_facilitators.mu_no')
                ->pluck('field_facilitators.mu_no');
        } else if ($lp == 'Umum') {
            $mu_no = DB::table('lahan_umums')->whereDate('distribution_date', $dd)
                ->where([
                    ['is_verified', '>', 0],
                    'program_year' => $py,
                ])
                // ->orWhere('nursery', $req->nursery)
                ->whereIn('mu_no', $this->getNurseryAlocationReverseGlobal($req->nursery))
                ->groupBy('mu_no')
                ->pluck('mu_no');
        }
            
        $mu = DB::table('managementunits')
            ->select('mu_no', 'name')
            ->whereIn('mu_no', $mu_no)
            ->orderBy('name')
            ->get();
            
        $rslt = [
            'total' => count($mu),
            'list' => $mu
        ];
        return response()->json($rslt, 200);
    }
    
    public function GetFF(Request $req) {
        $validator = Validator::make($req->all(), [
            'program_year' => 'required',
            'land_program' => 'required',
            'mu_no' => 'required|exists:managementunits,mu_no',
            'distribution_date' => 'required|date',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->first(), 400);
        } else {
            // simplify
            $py = $req->program_year;
            $lp = $req->land_program;
            $dd = $req->distribution_date;
        }
        if ($lp == 'Petani') {
            $form_no_period = DB::table('planting_period')
                ->whereDate('distribution_time', $dd)
                ->pluck('form_no');
            
            $ff = DB::table('planting_socializations')
                ->join('field_facilitators', 'field_facilitators.ff_no', 'planting_socializations.ff_no')
                ->select('field_facilitators.name as ff_name', 'field_facilitators.ff_no')
                ->where([
                    'planting_socializations.planting_year' => $py,
                    'planting_socializations.validation' => 1,
                    'planting_socializations.is_dell' => 0,
                    'field_facilitators.mu_no' => $req->mu_no
                ])
                ->whereIn('form_no', $form_no_period)
                ->groupBy('field_facilitators.ff_no')
                ->orderBy('field_facilitators.name')
                ->get();
                
            $rslt = [
                'total' => count($ff),
                'list' => $ff
            ];
        } else if ($lp == 'Umum') {
            $pic = DB::table('lahan_umums')->whereDate('distribution_date', $dd)
                ->join('employees', 'employees.nik', 'lahan_umums.employee_no')
                ->select('employees.name as employee_name', 'lahan_umums.employee_no')
                ->where([
                    ['lahan_umums.is_verified', '>', 0],
                    'lahan_umums.program_year' => $py,
                    'lahan_umums.mu_no' => $req->mu_no,
                ])
                ->groupBy('employee_no')
                ->orderBy('employee_name')
                ->get();
                
            $rslt = [
                'total' => count($pic),
                'list' => $pic
            ];
        }
        return response()->json($rslt, 200);
    }
    
    public function GetFarmer(Request $req) {
        $validator = Validator::make($req->all(), [
            'program_year' => 'required',
            'land_program' => 'required',
            'distribution_date' => 'required|date',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->first(), 400);
        } else {
            $py = $req->program_year;
            $lp = $req->land_program;
            $dd = $req->distribution_date;
        }
        
        if ($lp == 'Petani') {
            $farmer = DB::table('planting_socializations')
                ->join('field_facilitators', 'field_facilitators.ff_no', 'planting_socializations.ff_no')
                ->join('farmers', 'farmers.farmer_no', 'planting_socializations.farmer_no')
                ->select('farmers.name as farmer_name', 'farmers.farmer_no')
                ->where([
                    'planting_socializations.planting_year' => $py,
                    'planting_socializations.validation' => 1,
                    'planting_socializations.is_dell' => 0,
                    'field_facilitators.ff_no' => $req->ff_no
                ])
                ->groupBy('planting_socializations.farmer_no')
                ->orderBy('farmers.name')
                ->get();
                
            // get land(s) farmer
            foreach ($farmer as $farm) {
                $farm->lahan_no = DB::table('lahans')->whereYear('created_time', $py)->where('farmer_no', $farm->farmer_no)->pluck('lahan_no')->toArray();
            }
                
            $rslt = [
                'total' => count($farmer),
                'list' => $farmer
            ];
        } else if ($lp == 'Umum') {
            $mou = DB::table('lahan_umums')
                ->select('pic_lahan', 'mou_no')
                ->whereDate('distribution_date', $dd)
                ->where([
                    'employee_no' => $req->employee_no,
                    ['is_verified', '>', 0]
                ])
                ->groupBy('mou_no')
                ->orderBy('pic_lahan')
                ->get();
                
            $rslt = [
                'total' => count($mou),
                'list' => $mou
            ];
        }
        
        return response()->json($rslt, 200);
    }
    
    public function GetLand(Request $req) {
        $validator = Validator::make($req->all(), [
            'program_year' => 'required',
            'land_program' => 'required',
            'distribution_date' => 'required|date',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->first(), 400);
        } else {
            // simplify
            $py = $req->program_year;
            $lp = $req->land_program;
            $dd = $req->distribution_date;
        }
        
        if ($lp == 'Petani') {
            $lands = DB::table('planting_socializations')
                ->join('farmers', 'farmers.farmer_no', 'planting_socializations.farmer_no')
                ->join('lahans', 'lahans.lahan_no', 'planting_socializations.no_lahan')
                ->join('planting_details', 'planting_details.form_no', 'planting_socializations.form_no')
                ->select(
                    'lahans.lahan_no',
                    DB::raw('SUM(planting_details.amount) as bibit_sostam')
                )
                ->where([
                    'planting_socializations.planting_year' => $py,
                    'planting_socializations.validation' => 1,
                    'planting_socializations.is_dell' => 0,
                    'planting_socializations.farmer_no' => $req->farmer_no
                ])
                ->groupBy('planting_socializations.form_no')
                ->orderBy('lahans.lahan_no')
                ->get();
            $lahan_no = [];
            foreach ($lands as $land) {
                $hole = DB::table('planting_hole_surviellance')->where([
                        'lahan_no' => $land->lahan_no,
                        'is_validate' => 1,
                        'is_dell' => 0
                    ])->first();
                if ($hole) {
                    $land->bibit_penlub = (int)DB::table('planting_hole_details')->where('ph_form_no', $hole->ph_form_no)->sum('amount');
                    $land->is_printed = $hole->is_checked;
                } else {
                    $land->bibit_penlub = 0;
                    $land->is_printed = 0;
                }
                array_push($lahan_no, $land->lahan_no);
            }
            
            // get farmers activities
            $acts = [];
            // lands
            if (count($lands) > 0) array_push($acts, 'Pendataan Lahan'); 
            // sosialisasi tanam
            $sostam = DB::table('planting_socializations')
                ->where([
                    'farmer_no' => $req->farmer_no,
                    'planting_year' => $py,
                    // 'validation' => 1,
                    // 'is_dell' => 0
                ])->first();
            if ($sostam) array_push($acts, 'Sosialisasi Tanam');
            // Penilikan Lubang
            $penlub = DB::table('planting_hole_surviellance')->whereIn('lahan_no', $lahan_no)
                ->where(['planting_year' => $py])
                ->first();
            if ($penlub) array_push($acts, 'Penilikan Lubang');
            // Print Label
            $printed = DB::table('planting_hole_surviellance')->whereIn('lahan_no', $lahan_no)
                ->where(['planting_year' => $py, 'is_checked' => 1])
                ->first();
            if ($printed) array_push($acts, 'Print Label');
            // Load label
            $dis = DB::table('distributions')
                ->where([
                    'farmer_no' => $req->farmer_no,
                    ['distribution_no', 'LIKE', "D-$py-%"]
                ]);
            $load = $dis->where('is_loaded', 1)->first();
            if ($load) array_push($acts, 'Load Bag');
            // distribute label
            $distributed = DB::table('distribution_adjustments')
                ->where([
                    'farmer_no' => $req->farmer_no, 
                    'planting_year' => $py
                ])->first();
            if ($distributed) array_push($acts, 'Distribute Bag');
            else {
                $distributed2 = $dis->where(['is_loaded' => 1, 'is_distributed' => 1])->first();
                if ($distributed2) array_push($acts, 'Distribute Bag');
            }
            // monitoring
            $monitoring = DB::table('monitorings')->where([
                    'farmer_no' => $req->farmer_no,
                    'planting_year' => $py
                ])->first();
            if ($monitoring) array_push($acts, 'Realisasi Tanam');
        } else if ($lp == 'Umum') {
            $lands = DB::table('lahan_umums')->where('mou_no', $req->mou_no)->get();
            $lahan_no = [];
            foreach ($lands as $land) { array_push($lahan_no, $land->lahan_no); }
            // get mou activities
            $acts = [];
            // Pendataan Lahan
            if (count($lands) > 0) array_push($acts, 'Pendataan Lahan');
            // Penilikan Lubang
            $penlub = DB::table('lahan_umums')
                ->where([
                    'mou_no' => $req->mou_no,
                    ['total_holes', '>', 0]
                ])->first();
            if ($penlub) array_push($acts, 'Penilikan Lubang');
            // Print Label
            $printed = DB::table('lahan_umums')->whereIn('lahan_no', $lahan_no)
                ->where(['program_year' => $py, 'is_checked' => 1])
                ->first();
            if ($printed) array_push($acts, 'Print Label');
            // Load label
            $dis = DB::table('lahan_umum_distributions')
                ->where([
                    'mou_no' => $req->mou_no,
                    ['distribution_no', 'LIKE', "D-$py-%"]
                ]);
            $load = $dis->where(['is_loaded' => 1])->first();
            if ($load) array_push($acts, 'Load Bag');
            // distribute label
            $distributed = DB::table('lahan_umum_adjustments')
                ->where([
                    'planting_year' => $py,
                    ['distribution_no', 'LIKE', "%-$req->mou_no"]
                ])->first();
            if ($distributed) array_push($acts, 'Distribute Bag');
            else {
                $distributed2 = $dis->where(['is_loaded' => 1, 'is_distributed' => 1])->first();
                if ($distributed2) array_push($acts, 'Distribute Bag');
            }
            // monitoring
            $monitoring = DB::table('lahan_umum_monitorings')->where([
                    'mou_no' => $req->mou_no,
                    'program_year' => $py
                ])->first();
            if ($monitoring) array_push($acts, 'Realisasi Tanam');
        }
        
            
        $rslt = [
            'total' => count($lands),
            'list' => $lands,
            'activities' => $acts ?? []
        ];
        return response()->json($rslt, 200);
    }
    
    public function GetLandDetail(Request $req) {
        $validator = Validator::make($req->all(), [
            'program_year' => 'required',
            'land_program' => 'required|in:Petani,Umum',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors()->first(), 400);
        } else {
            $py = $req->program_year;
            $lp = $req->land_program;
        }
        if ($lp == 'Petani') $validatorTable = 'planting_hole_surviellance';
        else if ($lp == 'Umum') $validatorTable = 'lahan_umum_hole_details';
        $validator2 = Validator::make($req->all(), [
            'lahan_no' => "required|exists:$validatorTable,lahan_no",
        ]);
        if($validator2->fails()){
            return response()->json($validator2->errors()->first(), 400);
        }
        
        if ($lp == 'Petani') {
            $form_no = DB::table('planting_hole_surviellance')->where(['lahan_no' => $req->lahan_no])->first()->ph_form_no;
            $penlub_bibit = DB::table('planting_hole_details')
                ->join('trees', 'trees.tree_code', 'planting_hole_details.tree_code')
                ->select('trees.tree_name', 'trees.tree_category', 'planting_hole_details.tree_code', 'planting_hole_details.amount')
                ->where([
                    'planting_hole_details.ph_form_no' => $form_no
                ])
                ->orderBy('trees.tree_name')
                ->get();
            
        } else if ($lp == 'Umum') {
            $penlub_bibit = DB::table('lahan_umum_hole_details')
                ->join('trees', 'trees.tree_code', 'lahan_umum_hole_details.tree_code')
                ->select('trees.tree_name', 'trees.tree_category', 'lahan_umum_hole_details.tree_code', 'lahan_umum_hole_details.amount')
                ->where([
                    'lahan_umum_hole_details.lahan_no' => $req->lahan_no
                ])
                ->orderBy('trees.tree_name')
                ->get();
        }
            
        $data = (object)[
            'penlub_bibit' => $penlub_bibit ?? []
        ];
    
        return response()->json($data,200);
    }
    
    public function GetTreesPerMU(Request $req) {
        $validator = Validator::make($req->all(), [
            'mu_no' => 'required|exists:managementunits,mu_no',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors()->first(), 400);
        }
        
        $trees = DB::table('tree_locations')
            ->join('trees', 'trees.tree_code', 'tree_locations.tree_code')
            ->select('trees.tree_name', 'trees.tree_code', 'trees.tree_category')
            ->where('mu_no', $req->mu_no)
            ->groupBy('trees.tree_code')
            ->orderBy('trees.tree_name')
            ->get(); 
            
        return response()->json($trees, 200);
    }
        // end: get data options }
        // get data main {
            // get list request
    public function GetRequests(Request $req) {
        $validator = Validator::make($req->all(), [
            'land_program' => 'required|in:Petani,Umum',
            'program_year' => 'required',
            'nursery' => 'required'
        ]);
        if($validator->fails()){
            return response()->json($validator->errors()->first(), 400);
        } else {
            $py = $req->program_year;
            $lp = $req->land_program;
        }
        
        $user = Auth::user();
        
        $main = SCR::
            join('managementunits', 'managementunits.mu_no', 'seedling_change_requests.mu_no')
            ->select('seedling_change_requests.*', 'managementunits.name as mu_name')
            ->where([
                'program_year' => $py,
                'land_program' => $lp,
            ])
            ->orderBy('created_at', 'desc');
        
        if ($req->nursery != 'All') $main = $main->where('nursery', $req->nursery);
            
        if ($lp == 'Petani' && $req->ff) {
            $fff = explode(",", $req->ff);
            $farmers_no = DB::table('farmers')->whereIn('user_id', $fff)->pluck('farmer_no');
            $main = $main->whereIn('farmer_no', $farmers_no);
        }
        
        $main = $main->get();
        
        $list = [];
        foreach($main as $val) {
            if ($lp == 'Petani') {
                $farmer = DB::table('farmers')->where('farmer_no', $val->farmer_no)->first();
                if ($farmer) {
                    $val->farmer_name = $farmer->name ?? '-';
                    $val->ff_name = DB::table('field_facilitators')->where('ff_no', $farmer->user_id)->first()->name ?? '-';
                    array_push($list, $val);
                }
            } else {
                $lahan_mou = DB::table('lahan_umums')
                    ->join('employees', 'employees.nik', 'lahan_umums.employee_no')
                    ->select('employees.name as employee_name', 'lahan_umums.pic_lahan')
                    ->where('mou_no', $val->mou_no);
                if ($req->created_by) $lahan_mou = $lahan_mou->whereIn('created_by', explode(',', $req->created_by));
                $lahan_mou = $lahan_mou->first();
                if ($lahan_mou) {
                    $val->pic_t4t_name = $lahan_mou->employee_name ?? '-';
                    $val->pic_lahan = $lahan_mou->pic_lahan ?? '-';
                    array_push($list, $val);
                }
            }
        } 
        
        $rslt = (object)[
            'user' => $user,
            'count' => count($list),
            'list' => $list
        ];
        
        return response()->json($rslt, 200);
    }
            // get detail request
    public function DetailRequest(Request $req) {
        // validator {
        $validator = Validator::make($req->all(), [
            'request_no' => 'required|exists:seedling_change_requests,request_no'
        ]);
        if($validator->fails()){
            return response()->json($validator->errors()->first(), 400);
        }
        
        $data = SCR::where('request_no', $req->request_no)->first();
        if ($data) {
            // get created_by name
            $data->created_by_name = DB::table('users')->where('email', $data->created_by)->first()->name ?? '-';
            if ($data->land_program == 'Petani') {
                $farmer = DB::table('farmers')
                    ->join('field_facilitators', 'field_facilitators.ff_no', 'farmers.user_id')
                    ->select('farmers.name as farmer_name', 'field_facilitators.name as ff_name', 'field_facilitators.fc_no')
                    ->where('farmer_no', $data->farmer_no)->first();
                    
                $data->farmer_name = $farmer->farmer_name ?? '-';
                $data->ff_name = $farmer->ff_name;
                $fc = DB::table('employees')->where('nik', $farmer->fc_no)->first();
                if ($fc) {
                    $data->fc_name = $fc->name;
                    $um_nik = DB::table('employee_structure')->where('nik', $fc->nik)->first()->manager_code ?? null;
                    if ($um_nik) {
                        $um = DB::table('employees')->where('nik', $um_nik)->first();
                        if ($um) $data->um_name = $um->name;
                    }
                }
            } else {
                $lahan = DB::table('lahan_umums')->where('mou_no', $data->mou_no)->first();
                if ($lahan) {
                    $data->pic_lahan_name = $lahan->pic_lahan;
                    $data->pic_t4t_name = DB::table('employees')->where('nik', $lahan->employee_no)->first()->name ?? '-';
                    $data->created_lu_email = $lahan->created_by;
                }
            }
            $seeds = SCRSeed::
                join('trees', 'trees.tree_code', 'seedling_change_request_seeds.tree_code')
                ->select('seedling_change_request_seeds.*', 'trees.tree_name')
                ->where('request_no', $data->request_no)->get();
            
            $rslt = (object)[
                'main' => $data,
                'seeds' => $seeds
            ];
            return response()->json($rslt, 200);
            
        } else return response()->json('Request data not found.', 400);
        
    }
        // end: get data main}
    // end: get data }
    // end: get data }
    
    // Add Request Method
    private $addValidator = [
        'program_year' => 'required',
        'land_program' => 'required|in:Petani,Umum',
        'distribution_date' => 'required|date',
        'nursery' => 'required',
        'mu_no' => 'required|exists:managementunits,mu_no',
        'lahan_no' => 'required',
        'notes' => 'required',
        'seedlings' => 'required'
    ];
    public function AddRequest(Request $req) {
        // validator {
        $validator = Validator::make($req->all(), $this->addValidator);
        if($validator->fails()){
            return response()->json($validator->errors()->first(), 400);
        }
        if ($req->land_program == 'Petani') {
            $addValidator2 = ['farmer_no' => 'required|exists:farmers,farmer_no'];
        } else $addValidator2 = ['mou_no' => 'required|exists:lahan_umums,mou_no'];
        $validator2 = Validator::make($req->all(), $addValidator2);
        if($validator2->fails()){
            return response()->json($validator2->errors()->first(), 400);
        }
        // end: validator }
        // simplify variable data {
        $py = $req->program_year;
        $lp = $req->land_program;
        $dd = $req->distribution_date;
        $nur = $req->nursery;
        $seedlings = $req->seedlings;
        // end: simplify }
        
        // check existing request data {
        if ($lp == 'Petani') $check = ['farmer_no' => $req->farmer_no];
        else $check = ['mou_no' => $req->mou_no];
        $exist = SCR::where(array_merge($check, [
            'program_year' => $py,
            'status' => 'requested'
        ]))->first();
        if ($exist) return response()->json('Data already requested! Cancel previous request for creating new request.', 400);
        // end: check }
        
        // store main data
        $request_no = $this->getNewRequestNo($py,$lp);
        $main = array_merge($req->except('seedlings'), [
                'request_no' => $request_no,
                'status' => 'requested',
                'created_by' => Auth::user()->email ?? '-'
            ]);
        $createMain = SCR::create($main);
        if (!$createMain) return response()->json('Failed to create request!', 400);
        foreach ($seedlings as $seed) {
            $seed = array_merge([
                'request_no' => $request_no
            ], array_diff($seed, ['is_checked', 'tree_category']));
            SCRSeed::create($seed);
        }
        
        $this->writeLog('Created', $request_no);
        
        $rslt = (object)[
            'main' => $main,
            'seedligs' => $seedlings
        ];
        return response()->json($rslt, 200);
    }
    
    // Verification
    public function Verification(Request $req) {
        // validator {
        $validator = Validator::make($req->all(), [
            'request_no' => 'required|exists:seedling_change_requests,request_no'
        ]);
        if($validator->fails()){
            return response()->json($validator->errors()->first(), 400);
        }
        
        $SCR = SCR::where('request_no', $req->request_no)->first();
        $status = $SCR->verification ?? 0;
        if ($SCR->land_program == 'Umum') {
            if ($status == 1) $status += 1;
        }
        if ($status + 1 == 3) {
            // EXECUTE SEEDLING CHANGE REQUEST {
                // simplify variable
            $lp = $SCR->land_program;
            $py = $SCR->program_year;
            $uniqCol = $lp == 'Petani' ? 'farmer_no' : 'mou_no';
                // delete realisasi tanam
            if ($SCR->last_activity == 'Realisasi Tanam') {
                if ($lp == 'Petani') {
                    $monTable = 'monitorings';
                    $monDTable = 'monitoring_details';
                } else {
                    $monTable = 'lahan_umum_monitorings';
                    $monDTable = 'lahan_umum_monitoring_details';
                }
                    
                $mon = DB::table($monTable)->where(['program_year' => $py, $uniqCol => $SCR[$uniqCol]]);
                $exMon = $mon->first();
                if ($exMon) {
                    // delete monitoring detail
                    DB::table($monDTable)->where('monitoring_no', $mon->monitoring_no)->delete();
                    // delete monitoring main
                    $mon->delete();
                }
            }
                // delete distribution data
            $stepD = ['Print Label', 'Load Bag', 'Distribute Bag', 'Realisasi Tanam'];
            if (in_array($SCR->last_activity, $stepD)) {
                if ($lp == 'Petani') {
                    $disTable = 'distributions';
                    $adjTable = 'distribution_adjustments';
                    $disDTable = 'distribution_details';
                } else {
                    $disTable = 'lahan_umum_distributions';
                    $adjTable = 'lahan_umum_adjustments';
                    $disDTable = 'lahan_umum_distribution_details';
                }
                $dis = DB::table($disTable)->where([
                    ['distribution_no', 'LIKE', "D-$py-%"],
                    $uniqCol => $SCR[$uniqCol]
                ]);
                $exDis = $dis->first();
                if ($exDis) {
                    // delete distribution detail
                    DB::table($disDTable)->where('distribution_no', $exDis->distribution_no)->delete();
                    // delete distribution adjustment
                    DB::table($adjTable)->where('distribution_no', $exDis->distribution_no)->delete();
                    // delete distribution main
                    $dis->delete();
                }
            }
                // execute per seeds data
            $lahans_no = explode(",", $SCR->lahan_no);
            
            foreach ($lahans_no as $lahan_no) {
                // update is_checked in planting_hole
                if ($lp == 'Petani') $pphTable = 'planting_hole_surviellance';
                else $pphTable = 'lahan_umums';
                DB::table($pphTable)->where('lahan_no', $lahan_no)->update(['is_checked' => 0]);
                
                $changedSeeds = SCRSeed::where([
                        'request_no' => $req->request_no,
                        'lahan_no' => $lahan_no
                    ])->get();
                foreach ($changedSeeds as $changeSeed) {
                    $type = $changeSeed->type;
                    // lahan detail
                    $lahanTable = $lp == 'Petani' ? 'lahans' : 'lahan_umums';
                    $lahanDTable = $lp == 'Petani' ? 'lahan_details' : 'lahan_umum_details';
                        // execute per-type
                    if ($type == 'remove') DB::table($lahanDTable)->where(['lahan_no' => $changeSeed->lahan_no,'tree_code' => $changeSeed->tree_code])->delete();
                    else if ($type == 'new') {
                        $createLData = [
                            'lahan_no' => $lahan_no,
                            'tree_code' => $changeSeed->tree_code,
                            'amount' => $lp == 'Umum' ? $changeSeed->new_amount : 0 
                        ];
                        DB::table($lahanDTable)->insert($createLData);
                    } else if ($type == 'change' && $lp == 'Umum') {
                        DB::table($lahanDTable)->where(['lahan_no' => $changeSeed->lahan_no,'tree_code' => $changeSeed->tree_code])->update(['amount' => $changeSeed->new_amount]);
                    }
                    // sostam 
                    if ($lp == 'Petani') {
                        $sostamQuery = DB::table('planting_socializations')->where('no_lahan', $lahan_no);
                        $sostam = $sostamQuery->first();
                        if ($sostam) {
                            // sostam detail
                            if ($type == 'remove' || $type == 'change') {
                                $sostamDQuery = DB::table('planting_details')->where(['form_no' => $sostam->form_no, 'tree_code' => $changeSeed->tree_code]);
                                if ($type == 'remove') $sostamDQuery->delete();
                                else $sostamDQuery->update(['amount' => $changeSeed->new_amount]);
                            }
                            else if ($type == 'new') DB::table('planting_details')->insert(['form_no' => $sostam->form_no, 'tree_code' => $changeSeed->tree_code, 'amount' => $changeSeed->new_amount]);
                            // update max_seed_amount in sostam
                            $sostam_detail_sum = DB::table('planting_details')->where(['form_no' => $sostam->form_no])->sum('amount');
                            $sostamQuery->update(['max_seed_amount' => $sostam_detail_sum]);
                        }
                    }
                    // penlub
                    if ($lp == 'Petani') {
                        $penlubQuery = DB::table('planting_hole_surviellance')->where('lahan_no', $lahan_no);
                        $penlub = $penlubQuery->first();
                        $penlubDTable = 'planting_hole_details';
                        $penlubDColumn = 'ph_form_no';
                        $penlubUnique = $penlub->ph_form_no;
                        if ($penlub) {
                            // penlub detail
                            if ($type == 'remove' || $type == 'change') {
                                $penlubDQuery = DB::table($penlubDTable)->where(['ph_form_no' => $penlub->ph_form_no, 'tree_code' => $changeSeed->tree_code]);
                                if ($type == 'remove') $penlubDQuery->delete();
                                else $penlubDQuery->update(['amount' => $changeSeed->new_amount]);
                            } else if ($type == 'new') {
                                DB::table($penlubDTable)->insert([
                                    'ph_form_no' => $penlub->ph_form_no, 
                                    'tree_code' => $changeSeed->tree_code,
                                    'amount' => $changeSeed->new_amount
                                ]);
                            }
                        }
                    } else {
                        $penlubQuery = DB::table('lahan_umums')->where('lahan_no', $lahan_no);
                        $penlubDTable = 'lahan_umum_hole_details';
                        $penlubDColumn = 'lahan_no';
                        $penlubUnique = $lahan_no;
                        // penlub detail
                        if ($type == 'new') {
                            DB::table($penlubDTable)->insert([
                                'lahan_no' => $lahan_no,
                                'tree_code' => $changeSeed->tree_code,
                                'amount' => $changeSeed->new_amount
                            ]);
                        } else {
                            $penlubDQuery = DB::table($penlubDTable)->where(['lahan_no' => $lahan_no, 'tree_code' => $changeSeed->tree_code]);
                            if ($type == 'remove') $penlubDQuery->delete();
                            else if ($type == 'change') $penlubDQuery->update(['amount' => $changeSeed->new_amount]);
                        }
                        
                    }
                    // update total pohon in main data
                    $pohon_kayu = DB::table($penlubDTable)
                        ->join('trees', 'trees.tree_code', "$penlubDTable.tree_code")
                        ->where([
                            "$penlubDTable.$penlubDColumn" => $penlubUnique,
                            'trees.tree_category' => 'Pohon_Kayu'
                        ])->sum("$penlubDTable.amount");
                    $pohon_buah = DB::table($penlubDTable)
                        ->join('trees', 'trees.tree_code', "$penlubDTable.tree_code")
                        ->where([
                            "$penlubDTable.$penlubDColumn" => $penlubUnique,
                            'trees.tree_category' => 'Pohon_Buah'
                        ])->sum("$penlubDTable.amount");
                    $tanaman_bawah = DB::table($penlubDTable)
                        ->join('trees', 'trees.tree_code', "$penlubDTable.tree_code")
                        ->where([
                            "$penlubDTable.$penlubDColumn" => $penlubUnique,
                            'trees.tree_category' => 'Tanaman_Bawah_Empon'
                        ])->sum("$penlubDTable.amount");
                    $penlubQuery->update([
                            'pohon_kayu' => $pohon_kayu,
                            'pohon_mpts' => $pohon_buah,
                            'tanaman_bawah' => $tanaman_bawah
                        ]);
                    // update total holes if < total seeds
                    $total_holes = $penlubQuery->first()->total_holes ?? null;
                    if ($total_holes) {
                        $total_seeds = (int)$pohon_kayu + (int)$pohon_buah + (int)$tanaman_bawah;
                        if ($total_holes < $total_seeds) {
                            $penlubQuery->update(['total_holes' => $total_seeds]);
                        }
                    }
                    
                }
            }
            
            // END: EXECUTE }
            
            SCR::where('request_no', $req->request_no)->update([
                'execute_time' => Carbon::now(),
                'status' => 'executed',
            ]);
        }
        
        $status_new = $status + 1;
        $verification_by_col = "verification$status_new"."_by";
        if ($status_new < 4) {
            $verification = SCR::where('request_no', $req->request_no)->update([
                $verification_by_col => Auth::user()->email,
                'verification' => $status_new
            ]);
            if ($verification) {
                $this->writeLog('Verified', $req->request_no);
                return response()->json('Verification success!', 200);
            }
            else return response()->json('Failed to verif data.', 500);
        } else return response()->json('Status already verified!', 400);
        
    }
    
    // Reject
    public function Reject(Request $req) {
        // validator {
        $validator = Validator::make($req->all(), [
            'request_no' => 'required|exists:seedling_change_requests,request_no'
        ]);
        if($validator->fails()){
            return response()->json($validator->errors()->first(), 400);
        }
        $reject = SCR::where('request_no', $req->request_no)->update(['status' => 'rejected']);
        if ($reject) {
            $this->writeLog('Rejected', $req->request_no);
            return response()->json('Reject success!', 200);
        }
        else return response()->json('Failed to reject data.', 500);
        
    }
    // Cancel
    public function Cancel(Request $req) {
        // validator {
        $validator = Validator::make($req->all(), [
            'request_no' => 'required|exists:seedling_change_requests,request_no'
        ]);
        if($validator->fails()){
            return response()->json($validator->errors()->first(), 400);
        }
        $reject = SCR::where('request_no', $req->request_no)->update(['status' => 'canceled']);
        if ($reject) {
            $this->writeLog('Canceled', $req->request_no);
            return response()->json('Canceling success!', 200);
        }
        else return response()->json('Failed to cancel data.', 500);
        
    }
    
    // utilities {
    private function getNewRequestNo($py,$lp) {
        $lpCode = $lp == 'Petani' ? 1 : 2;
        $ddCode = date('Ymd', strtotime(Carbon::now()));
        $temp = "SCR-$py-$ddCode$lpCode";
        $last = SCR::where('request_no', 'LIKE', "$temp%")->orderBy('request_no', 'DESC')->first();
        if ($last) {
            $req_no = $last->request_no;
            $last_no = str_replace($temp, '', $req_no);
            $no = (int)$last_no + 1;
        } else $no = 1;
        
        $req_no_new = $temp . str_pad ( $no, 3, "0", STR_PAD_LEFT);
        return $req_no_new;
    }
    private function writeLog($type, $request_no) {
        $log = Log::channel('seedling_change_requests');
        
        $user = Auth::user();
        $request = SCR::where('request_no', $request_no)->first();
        $data = [
            "program: $request->land_program",
            "lahan_no: $request->lahan_no"
        ];
        if ($type != 'Created') {
            array_push($data, "created_by: $request->created_by");
        } 
        array_push($data, ...[
            "verification1_by: $request->verification1_by",
            "verification2_by: $request->verification2_by",
            "verification3_by: $request->verification3_by",
        ]);
        $dataImp = implode(" | ", $data);
        
        if ($type == 'Verified') $type = "Verified $request->verification";
        
        $message = "$type > $request_no ($dataImp) > by $user->email";
        
        if ($type == 'Created') {
            $log->info($message);
        } else if ($type == 'Rejected' || $type == 'Canceled') {
            $log->alert($message);
        } else {
            $log->notice($message);
        }
    }
    // end: utilities }
}
