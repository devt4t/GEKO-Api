<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use DB;
use Carbon\Carbon;
use App\Desa;
use App\Trees;
use App\Distribution;
use App\DistributionDetail;
use App\DistributionAdjustment;
use App\Farmer;
use App\FarmerTrainingDetail;
use App\Employee;
use App\EmployeeStructure;
use App\FieldFacilitator;
use App\Lahan;
use App\LahanDetail;
use App\LahanUmum;
use App\LahanUmumDetail;
use App\LahanUmumHoleDetail;
use App\ManagementUnit;
use App\Monitoring;
use App\MonitoringDetail;
use App\PlantingSocializations;
use App\PlantingSocializationsDetails;
use App\PlantingSocializationsPeriod;
use App\PlantingHoleSurviellance;
use App\PlantingHoleSurviellanceDetail;
use App\TargetArea;
use App\TreeLocation;
use App\User;

use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TemporaryController extends Controller {
    public function __construct()
    {
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', 400);
    }
    // Sosialisasi Tanam: fixing nulled max_seed_amount
    public function fixNullMaxSeedAmountSosialisasiTanam(Request $req) {
        // set validation variable
        if (isset($req->validation)) $validation = $req->validation;
        else $validation = 0;
        
        // set progam year variable
        if (isset($req->program_year)) $py = $req->program_year;
        else $py = '2022';
        
        // set delete bool
        if (isset($req->delete_data)) {
            if ($req->delete_data == 'true') $delete = true;
            else $delete = false;
        }
        else $delete = false;
        
        // query sostam
        $query = PlantingSocializations::where([
                'planting_year' => $py,
                'max_seed_amount' => null,
                ['form_no', 'LIKE', 'SO-'.$py.'-000%'],
                'validation' => $validation
            ]);
        
        // sostam data
        $datas = $query->get();
        
        // total
        $counting = [
                'sostam_data' => 0,
                'trees' => 0,
                'period' => 0
            ];
            
        // foreach sostam
        foreach($datas as $index => $data) {
            // get trees data
            $trees = PlantingSocializationsDetails::where('form_no', $data->form_no);
            $counting['trees'] += $trees->count();
            if ($delete) {
                // $trees->delete();
            } else {
                $datas[$index]->trees = $trees->get() ?? [];
            }
            
            // get period data
            $period = PlantingSocializationsPeriod::where('form_no', $data->form_no);
            $counting['period'] += $period->count();
            if($delete) {
                // $period->delete();
            } else {
                $datas[$index]->period = $period->get() ?? [];
            }
        }
        
        // delete sostam
        $counting['sostam_data'] = $query->count();
        if ($delete) {
            // $query->delete()
        }
           
        if ($delete) {
            $rslt =  $this->ResultReturn(200, 'success', [
                    'delete_status' => $delete,
                    'deleted_counting' => $counting
                ]);
        } else {
            $rslt =  $this->ResultReturn(200, 'success', [
                    'delete_status' => $delete,
                    'data_counting' => $counting,
                    'total' => $query->count(),
                    'datas' => $datas
                ]);
        }
        return response()->json($rslt, 200); 
    }
    
    // Get Top 5 Trees Sosialisasi Tanam
    public function getTopTreesSosialisasiTanam(Request $req) {
        // validate request
        $validate = Validator::make($req->all(), [
            'program_year' => 'required',
            'activity' => 'required',
            'nursery' => 'required',
            'total' => 'required',
        ]);
        
        // validation fails
        if($validate->fails()){
            $rslt =  $this->ResultReturn(400, $validate->errors()->first(), $validate->errors()->first());
            return response()->json($rslt, 400);
        } else {
            $py = $req->program_year;
            $max = $req->total;
            $activity = $req->activity;
            $nursery = explode(',', $req->nursery);
            $mu_no = [];
            foreach ($nursery as $nur) {
                array_push($mu_no, ...$this->getNurseryAlocationReverse($nur));
            }
            $ff_no = FieldFacilitator::whereIn('mu_no', $mu_no)->pluck('ff_no');
        }
        
        if ($activity == 'Sosialisasi Tanam') {
            $scopes = PlantingSocializations::
                join('field_facilitators', 'field_facilitators.ff_no', 'planting_socializations.ff_no')
                ->whereIn('field_facilitators.mu_no', $mu_no)
                ->where([
                        'planting_year' => $py,
                        ['no_lahan', 'LIKE', '10_00%']
                    ])
                ->pluck('planting_socializations.form_no');
            $SUMAll = PlantingSocializationsDetails::
                select(
                    'tree_code',
                    DB::raw('SUM(amount) as total')
                )
            ->whereIn('form_no', $scopes)
            ->groupBy('tree_code')
            ->orderBy('total', 'DESC')
            ->get();
        } else if ($activity == 'Penilikan Lubang') {
            $scopes = PlantingHoleSurviellance::
                join('field_facilitators', 'field_facilitators.ff_no', 'planting_hole_surviellance.user_id')
                ->whereIn('field_facilitators.mu_no', $mu_no)
                ->where([
                        'planting_year' => $py,
                        ['lahan_no', 'LIKE', '10_00%']
                    ])
                ->pluck('planting_hole_surviellance.ph_form_no');
            $SUMAll = PlantingHoleSurviellanceDetail::
                select(
                    'tree_code',
                    DB::raw('SUM(amount) as total')
                )
            ->whereIn('ph_form_no', $scopes)
            ->groupBy('tree_code')
            ->orderBy('total', 'DESC')
            ->get();
        }
        
        $KAYU = [];
        $MPTS = [];
        $CROPS = [];
        
        $distribution_no = Distribution::where('distribution_no', 'LIKE', 'D-'.$py.'%')->whereIn('ff_no', $ff_no)->pluck('distribution_no');
        
        if (isset($SUMAll)) {
            foreach($SUMAll as $seedIndex => $seed) {
                $seedDetail = DB::table('trees')->where('tree_code', $seed->tree_code)->first();
                
                $seed->tree_name = $seedDetail->tree_name;
                $seed->tree_category = $seedDetail->tree_category;
                $seed->printed = DistributionDetail::where([
                    ['tree_name', 'LIKE', '%'.$seed->tree_name.'%'],
                ])->whereIn('distribution_no', $distribution_no)->sum('tree_amount');
                $seed->unprinted = (int)$seed->total - (int)$seed->printed;
            
                if (count($KAYU) < $max && $seedDetail->tree_category == 'Pohon_Kayu') {
                    array_push($KAYU, $seed);
                }
            
                if (count($MPTS) < $max && $seedDetail->tree_category == 'Pohon_Buah') {
                    array_push($MPTS, $seed);
                }
            
                if (count($CROPS) < $max && $seedDetail->tree_category == 'Tanaman_Bawah_Empon') {
                    array_push($CROPS, $seed);
                }
            }
        
            $rslt = [
                'rank' => [
                    'KAYU' => $KAYU,
                    'MPTS' => $MPTS,
                    'CROPS' => $CROPS
                ],
                'general' => [
                    'count' => count($SUMAll),
                    'data' => $SUMAll,
                ]
            ];
            
            return response()->json($rslt, 200); 
        } else return response()->json('Bad Request', 400); 
    }
    
    // Get Top Farmer Training
    public function getTopFarmerTraining(Request $req) {
        $limit = $req->limit ?? 10;
        $datas = FarmerTrainingDetail::
            join('farmers', 'farmers.farmer_no', 'farmer_training_details.farmer_no')
            ->select('farmer_training_details.farmer_no', 'farmers.name', DB::raw('COUNT(farmer_training_details.id) as total'))
            ->groupBy('farmer_no')
            ->orderBy('total', 'desc')
            ->limit($limit)
            ->get();
        
        foreach($datas as $index => $data) {
            $datas[$index]->listFarmerTraining = FarmerTrainingDetail::
                join('farmer_trainings', 'farmer_trainings.training_no', 'farmer_training_details.training_no')
                ->select(
                    'farmer_trainings.training_no',
                    'farmer_trainings.user_id'
                )
                ->where('farmer_no', $data->farmer_no)
                ->get();
        }
        
        return response()->json($datas, 200);
    }
    
    // Export Bibit By Field Facilitator
    public function ExportBibitByFF(Request $req) {
        // validate request
        $validate = Validator::make($req->all(), [
            'ff_no' => 'required',
            'program_year' => 'required',
            'activity' => 'required'
        ]);
        
        // validation fails
        if($validate->fails()){
            $rslt =  $this->ResultReturn(400, $validate->errors()->first(), $validate->errors()->first());
            return response()->json($rslt, 400);
        }
         else {
            $py = $req->program_year;
            $ffNo = explode(',', $req->ff_no);
            $activity = $req->activity;
        }
        
        $total_bibit = 0;
        $total_bibit_details = [];
        $ff_data = [];
        foreach ($ffNo as $ff_no) {
            $ff = FieldFacilitator::leftJoin('desas', 'desas.kode_desa', '=', 'field_facilitators.working_area')
                ->select('field_facilitators.*', 'desas.name as village_name')
                ->where('field_facilitators.ff_no', $ff_no)->first();
            if ($ff && $activity == 'sostam') {
                array_push($ff_data, $ff);
                $sostamFF = PlantingSocializations::where(['ff_no' => $ff->ff_no, 'planting_year' => $py, 'is_dell' => 0])->pluck('form_no');
                $seeds = PlantingSocializationsDetails::
                    leftJoin('tree_locations', 'tree_locations.tree_code', '=', 'planting_details.tree_code')
                    ->select(
                            'tree_locations.tree_code',
                            'tree_locations.tree_name',
                            'tree_locations.category',
                            'planting_details.amount'
                        )
                    ->whereIn('planting_details.form_no', $sostamFF)
                    ->where('tree_locations.mu_no', $ff->mu_no)
                    ->orderBy('tree_locations.tree_name');
                $total_bibit += $seeds->sum('amount');
                $seedsGet = $seeds->get();
                foreach ($seedsGet as $seed) {
                    $exists = 0;
                    if ($seed->amount > 0) {
                        foreach($total_bibit_details as $newSeedIndex => $newSeed) {
                            if ($seed->tree_code == $newSeed->tree_code && $exists == 0) {
                                $total_bibit_details[$newSeedIndex]->amount += (int)$seed->amount;
                                $exists += 1;
                            }
                        }
                        if ($exists == 0) {
                            array_push($total_bibit_details, $seed);
                        }
                    }
                }
            } else if ($ff && $activity == 'penlub') {
                array_push($ff_data, $ff);
                $penlubFF = PlantingHoleSurviellance::where(['user_id' => $ff->ff_no, 'planting_year' => $py, 'is_dell' => 0, 'is_validate' => 1])->pluck('ph_form_no');
                $seeds = PlantingHoleSurviellanceDetail::
                    leftJoin('tree_locations', 'tree_locations.tree_code', '=', 'planting_hole_details.tree_code')
                    ->select(
                            'tree_locations.tree_code',
                            'tree_locations.tree_name',
                            'tree_locations.category',
                            'planting_hole_details.amount'
                        )
                    ->whereIn('planting_hole_details.ph_form_no', $penlubFF)
                    ->where('tree_locations.mu_no', $ff->mu_no)
                    ->orderBy('tree_locations.tree_name');
                $total_bibit += $seeds->sum('amount');
                $seedsGet = $seeds->get();
                foreach ($seedsGet as $seed) {
                    $exists = 0;
                    if ($seed->amount > 0) {
                        foreach($total_bibit_details as $newSeedIndex => $newSeed) {
                            if ($seed->tree_code == $newSeed->tree_code && $exists == 0) {
                                $total_bibit_details[$newSeedIndex]->amount += (int)$seed->amount;
                                $exists += 1;
                            }
                        }
                        if ($exists == 0) {
                            array_push($total_bibit_details, $seed);
                        }
                    }
                }
                
            }
        }
        
        $total_bibit_grouping = [
                'KAYU' => [],
                'MPTS' => [],
                'CROPS' => []
            ];
            
        foreach($total_bibit_details as $seedIndex => $seed) {
            array_push($total_bibit_grouping[$seed->category], $seed);
        }
        
        $datas = [
                'program_year' => $py,
                'distribution_date' => $req->distribution_date,
                'FF' => $ffNo,
                'FF_details' => $ff_data,
                'activity' => $activity,
                'total_bibit' => $total_bibit,
                'total_bibit_details' => [
                        'KAYU' => $total_bibit_grouping['KAYU'],
                        'MPTS' => $total_bibit_grouping['MPTS'],
                        'CROPS' => $total_bibit_grouping['CROPS'],
                    ],
            ];

        return view('exportBibitByFF', ['datas' => $datas]);
    }
    
    // Export Bibit By Field Facilitator
    public function ExportBibitLahanUmum(Request $req) {
        // validate request
        $validate = Validator::make($req->all(), [
            'lahan_no' => 'required',
            'program_year' => 'required',
            'activity' => 'required'
        ]);
        
        // validation fails
        if($validate->fails()){
            $rslt =  $this->ResultReturn(400, $validate->errors()->first(), $validate->errors()->first());
            return response()->json($rslt, 400);
        }
         else {
            $py = $req->program_year;
            $lahanNo = explode(',', $req->lahan_no);
            $activity = $req->activity;
        }
        
        $total_bibit = 0;
        $total_bibit_details = [];
        $lahan_data = [];
        foreach ($lahanNo as $lahan_no) {
            $lahan = LahanUmum::
                leftJoin('desas', 'desas.kode_desa', '=', 'lahan_umums.village')
                ->select('lahan_umums.*', 'desas.name as village_name')
                ->where('lahan_umums.lahan_no', $lahan_no)->first();
            if ($lahan && $activity == 'lahan') {
                array_push($lahan_data, $lahan);
                $seeds = LahanUmumDetail::
                    join('trees', 'trees.tree_code', '=', 'lahan_umum_details.tree_code')
                    ->select(
                            'trees.tree_code',
                            'trees.tree_name',
                            'trees.tree_category as category',
                            'lahan_umum_details.amount'
                        )
                    ->where('lahan_umum_details.lahan_no', $lahan->lahan_no)
                    ->orderBy('trees.tree_name');
                $total_bibit += $seeds->sum('amount');
                $seedsGet = $seeds->get();
                foreach ($seedsGet as $seed) {
                    $exists = 0;
                    if ($seed->amount > 0) {
                        foreach($total_bibit_details as $newSeedIndex => $newSeed) {
                            if ($seed->tree_code == $newSeed->tree_code && $exists == 0) {
                                $total_bibit_details[$newSeedIndex]->amount += (int)$seed->amount;
                                $exists += 1;
                            }
                        }
                        if ($exists == 0) {
                            array_push($total_bibit_details, $seed);
                        }
                    }
                }
            } else if ($lahan && $activity == 'penlub') {
                array_push($lahan_data, $lahan);
                $seeds = LahanUmumHoleDetail::
                    join('trees', 'trees.tree_code', '=', 'lahan_umum_hole_details.tree_code')
                    ->select(
                            'trees.tree_code',
                            'trees.tree_name',
                            'trees.tree_category as category',
                            'lahan_umum_hole_details.amount'
                        )
                    ->where('lahan_umum_hole_details.lahan_no', $lahan->lahan_no)
                    ->orderBy('trees.tree_name');
                $total_bibit += $seeds->sum('amount');
                $seedsGet = $seeds->get();
                foreach ($seedsGet as $seed) {
                    $exists = 0;
                    if ($seed->amount > 0) {
                        foreach($total_bibit_details as $newSeedIndex => $newSeed) {
                            if ($seed->tree_code == $newSeed->tree_code && $exists == 0) {
                                $total_bibit_details[$newSeedIndex]->amount += (int)$seed->amount;
                                $exists += 1;
                            }
                        }
                        if ($exists == 0) {
                            array_push($total_bibit_details, $seed);
                        }
                    }
                }
            }
        }
        
        $total_bibit_grouping = [
                'Pohon_Kayu' => [],
                'Pohon_Buah' => [],
                'Tanaman_Bawah_Empon' => []
            ];
            
        foreach($total_bibit_details as $seedIndex => $seed) {
            array_push($total_bibit_grouping[$seed->category], $seed);
        }
        
        $datas = [
                'program_year' => $py,
                'distribution_date' => $req->distribution_date,
                'lahans' => $lahanNo,
                'pic_details' => $lahan_data,
                'activity' => $activity,
                'total_bibit' => $total_bibit,
                'total_bibit_details' => [
                        'KAYU' => $total_bibit_grouping['Pohon_Kayu'],
                        'MPTS' => $total_bibit_grouping['Pohon_Buah'],
                        'CROPS' => $total_bibit_grouping['Tanaman_Bawah_Empon'],
                    ],
            ];

        // return response()->json($datas, 200);
        return view('exportBibitLahanUmum', ['datas' => $datas]);
    }
    
    // Export Distribution Report
    public function ExportDistributionByFarmer(Request $req) {
        // validate request
        $validate = Validator::make($req->all(), [
            'distribution_no' => 'required|exists:distributions,distribution_no'
        ]);
        // validation fails
        if($validate->fails()){
            $rslt =  $this->ResultReturn(400, $validate->errors()->first(), $validate->errors()->first());
            return response()->json($rslt, 400);
        }
         else {
            $disNo = $req->distribution_no;
        }
        
        // Get Distribution Data
        $main = Distribution::
            join('farmers', 'farmers.farmer_no', 'distributions.farmer_no')
            ->join('field_facilitators', 'field_facilitators.ff_no', 'distributions.ff_no')
            ->select(
                'distributions.distribution_no',
                'distributions.ff_no',
                'field_facilitators.name as ff_name',
                'distributions.farmer_no',
                'farmers.name as farmer_name',
                'distributions.farmer_signature',
                'distributions.distribution_photo',
                'distributions.distribution_date',
                'distributions.distribution_note',
                'distributions.status',
                'distributions.total_bags',
                'distributions.total_tree_amount',
                'distributions.is_loaded',
                'distributions.loaded_by',
                'distributions.is_distributed',
                'distributions.distributed_by'
            )
            ->where('distributions.distribution_no', $disNo)->first();
        
        $details = DistributionDetail::where('distribution_no', $disNo)->get();
        return view('exportDistributionByFarmer', ['main' => $main, 'details' => $details]);
    }
    
    // Get Total Distributed Label 
    public function GetDistributedLabel(Request $req) {
        // validate request
        $validate = Validator::make($req->all(), [
            'date_start' => 'required|date',
            'date_end' => 'required|date',
            'nursery' => 'required'
        ]);
        // validation fails
        if($validate->fails()){
            $rslt =  $this->ResultReturn(400, $validate->errors()->first(), $validate->errors()->first());
            return response()->json($rslt, 400);
        } else {
            $start = $req->date_start;
            $end = $req->date_end;
            $nursery = explode(',', $req->nursery);
            $mu_no = [];
            foreach ($nursery as $nur) {
                array_push($mu_no, ...$this->getNurseryAlocationReverse($nur));
            }
            if ($exceptMU = explode(',', $req->except_mu)) {
                foreach($exceptMU as $exMU) {
                    $key = array_search($exMU, $mu_no);
                    if ($key > -1) {
                       array_splice($mu_no, $key, 1);
                    }
                }
            }
        }
        $ff_no = Distribution::
            join('field_facilitators', 'field_facilitators.ff_no', 'distributions.ff_no')
            ->whereDate('distributions.distribution_date', '>=', $start)
            ->whereDate('distributions.distribution_date', '<=', $end)
            ->where('distributions.ff_no', 'LIKE', 'FF0%')
            ->whereIn('field_facilitators.mu_no', $mu_no)
            ->groupBy('ff_no')->pluck('distributions.ff_no');
        
        $distributions = Distribution::
            whereIn('ff_no', $ff_no)
            ->whereDate('created_at', '>=', $start)
            ->whereDate('created_at', '<=', $end)
            ->pluck('distribution_no');
        
        $distributionsDetail = DistributionDetail::
            whereIn('distribution_no', $distributions);
        
        $isPrintedLabels = DistributionDetail::
            whereIn('distribution_no', $distributions)->groupBy('bag_number');
            
        // return response()->json($isPrintedLabels, 200);
            
            
        $rslt = [
            'date_range' => [
                'start' => $start,
                'end' => $end
            ],
            'nursery' => $nursery,
            'total_ff' => count($ff_no),
            'seedling' => [
                'is_printed' => $distributionsDetail->sum('tree_amount'),
                // 'is_printed_details' => $this->getDistributionDetailTrees('is_printed', $distributions, $start, $end),
                'is_loaded' => $distributionsDetail->where('is_loaded', 1)->sum('tree_amount'),
                // 'is_loaded_details' => $this->getDistributionDetailTrees('is_loaded', $distributions, $start, $end),
                'is_distributed' => $distributionsDetail->where('is_distributed', 1)->sum('tree_amount'),
                // 'is_distributed_details' => $this->getDistributionDetailTrees('is_distributed', $distributions, $start, $end),
            ],
            'labels' => [
                'is_printed' => $isPrintedLabels->count(),
                'is_loaded' => DistributionDetail::
            whereIn('distribution_no', $distributions)
            ->whereDate('updated_at', '>=', $start)
            ->whereDate('updated_at', '<=', $end)->where('is_loaded', 1)->groupBy('bag_number')->count(),
                'is_distributed' => DistributionDetail::
            whereIn('distribution_no', $distributions)
            ->whereDate('updated_at', '>=', $start)
            ->whereDate('updated_at', '<=', $end)->where('is_distributed', 1)->groupBy('bag_number')->count()
            ]
        ];
        return response()->json($rslt, 200);
    }
    
    // Get Distribution Report Data
    public function GetDistributionReport(Request $req) {
        // validate request
        $validate = Validator::make($req->all(), [
            'date_start' => 'required|date',
            'date_end' => 'required|date',
            'program_year' => 'required'
        ]);
        // validation fails
        if($validate->fails()){
            $rslt =  $this->ResultReturn(400, $validate->errors()->first(), $validate->errors()->first());
            return response()->json($rslt, 400);
        } else {
            $start = $req->date_start;
            $end = $req->date_end;
            $py = $req->program_year;
        }
        
        $distributions = Distribution::
            where('ff_no', 'LIKE', 'FF%')
            ->whereDate('distribution_date', '>=', $start)
            ->whereDate('distribution_date', '<=', $end)
            ->groupBy('ff_no')
            ->orderBy('distribution_date')->orderBy('ff_no')->get();
            
        $treesData = DB::table('tree_locations')->select('tree_name', 'tree_code', 'category')->groupBy('tree_code')->orderBy('category')->orderBy('tree_name')->get();
        
        $newLists = [];
        foreach($distributions as $d) {
            $ff = FieldFacilitator::where(['ff_no' => $d->ff_no, ['ff_no', 'LIKE', 'FF%']])->first();
            if ($ff) {
                // $farmer = Farmer::where(['farmer_no' => $d->farmer_no])->first();
                $fc = Employee::where('nik', $ff->fc_no)->first();
                $um_nik = DB::table('employee_structure')->where('nik', $ff->fc_no)->first()->manager_code ?? 999999999999;
                $um = Employee::where('nik', $um_nik)->first() ?? '';
                
                // Per FF
                // $dis_no_ff = Distribution::where('ff_no', $d->ff_no)->whereIn('distribution_no', $distribution_no)->pluck('distribution_no');
                // $monitoring_no_ff = Monitoring::where(['user_id' => $d->ff_no, 'planting_year' => $py])->pluck('monitoring_no');
                // $sostam_ff = PlantingSocializations::where(['ff_no' => $d->ff_no, 'planting_year' => $py])->pluck('form_no');
                // $penlub_ff = PlantingHoleSurviellance::where(['user_id' => $d->ff_no, 'planting_year' => $py])->pluck('ph_form_no');
                
                // Per Farmer
                $dis_no_ff = [$d->distribution_no];
                $sostam = PlantingSocializations::where(['farmer_no' => $d->farmer_no, 'planting_year' => $py]);
                $monitoring_no_ff = Monitoring::where(['farmer_no' => $d->farmer_no, 'planting_year' => $py])->pluck('monitoring_no');
                $sostam_ff = $sostam->pluck('form_no');
                $lahans = $sostam->pluck('no_lahan'); 
                $lahanF = Lahan::whereIn('lahan_no', $lahans)->first();
                $penlub_ff = PlantingHoleSurviellance::whereIn('lahan_no', $lahans)->pluck('ph_form_no');
                
                $data = [
                    'No' => count($newLists) + 1,
                    'Date' => date('d/m/Y', strtotime($d->distribution_date)),
                    'Nursery' => $this->getNurseryAlocation($ff->mu_no),
                    'MU' => DB::table('managementunits')->where('mu_no', $ff->mu_no)->first()->name ?? '-',
                    'TA' => DB::table('target_areas')->where('area_code', $ff->target_area)->first()->name ?? '-',
                    'Village' => DB::table('desas')->where('kode_desa', $ff->working_area)->first()->name ?? '-',
                    'UM' => $um->name ?? '-',
                    'FC' => $fc->name ?? '-',
                    'FF' => $ff->name ?? '-',
                    'FF No' => $ff->ff_no ?? '-',
                    // 'FF No' => $ff->ff_no ?? '-',
                    // 'Farmer' => $farmer->name ?? '-',
                    // 'Planting Pattern' => $lahanF->opsi_pola_tanam ?? '-',
                ];
                
                // $treesDetails = [];
                // foreach($treesData as $tree) {
                //     $data[$tree->tree_name] = [
                //         'sostam' => (int)PlantingSocializationsDetails::whereIn('form_no', $sostam_ff)->where(['tree_code' => $tree->tree_code])->sum('amount'),
                //         'penlub' => (int)PlantingHoleSurviellanceDetail::whereIn('ph_form_no', $penlub_ff)->where(['tree_code' => $tree->tree_code])->sum('amount'),
                //         'loaded' => (int)DistributionDetail::whereIn('distribution_no', $dis_no_ff)->where(['tree_name' => $tree->tree_name, 'is_loaded' => 1])->sum('tree_amount'),
                //         'received' => (int)DistributionAdjustment::whereIn('distribution_no', $dis_no_ff)->where(['tree_code' => $tree->tree_code])->sum('total_tree_received'),
                //         'planted_live' => (int)MonitoringDetail::whereIn('monitoring_no', $monitoring_no_ff)->where([ 'tree_code' => $tree->tree_code, 'status' => 'sudah_ditanam', 'condition' => 'hidup' ])->sum('qty'),
                //         'dead' => (int)MonitoringDetail::whereIn('monitoring_no', $monitoring_no_ff)->where([ 'tree_code' => $tree->tree_code, 'condition' => 'mati' ])->sum('qty'),
                //         'lost' => (int)MonitoringDetail::whereIn('monitoring_no', $monitoring_no_ff)->where([ 'tree_code' => $tree->tree_code, 'condition' => 'hilang' ])->sum('qty'),
                //     ];
                // }
                array_push($newLists, (object)$data);
            }
        }
            
        $rslt = [
            'total_ff' => count($newLists),
            // 'total_farmer' => count($newLists),
            'distributions' => $newLists
        ];
            
        return response()->json($rslt, 200);
    }
    // Get Distribution Report Data Per FF
    public function GetDistributionReportPerFF(Request $req) {
        // validate request
        $validate = Validator::make($req->all(), [
            'ff_no' => 'required',
            'program_year' => 'required',
            'per' => 'required'
        ]);
        // validation fails
        if($validate->fails()){
            $rslt =  $this->ResultReturn(400, $validate->errors()->first(), $validate->errors()->first());
            return response()->json($rslt, 400);
        } else {
            // $start = $req->date_start;
            // $end = $req->date_end;
            $py = $req->program_year;
            $ffArray = explode("FF", $req->ff_no);
            foreach($ffArray as $ffIndex => $ffa) {
                $ffArray[$ffIndex] = 'FF'.str_replace("\n", "", $ffa);
            }
            array_shift($ffArray);
            $perFF = array_slice($ffArray, 0, $req->per);
            $queueFF = array_slice($ffArray, $req->per, count($ffArray));
            // return response()->json($queueFF);
        }
        
        $distributions = Distribution::
            where([['ff_no', 'LIKE', 'FF%'], ['distribution_no', 'LIKE', 'D-'.$py.'%']])
            ->whereIn('ff_no', $perFF)
            // ->whereDate('distribution_date', '>=', $start)
            // ->whereDate('distribution_date', '<=', $end)
            // ->groupBy('ff_no')
            ->orderBy('distribution_date')->orderBy('ff_no')->get();
            
        $treesData = TreeLocation::select('tree_name', 'tree_code', 'category')->groupBy('tree_code')->orderBy('category')->orderBy('tree_name')->get();
        
        $newLists = [];
        foreach($distributions as $d) {
            $ff = FieldFacilitator::where(['ff_no' => $d->ff_no, ['ff_no', 'LIKE', 'FF%']])->first();
            if ($ff) {
                $farmer = Farmer::where(['farmer_no' => $d->farmer_no])->first();
                $fc = Employee::where('nik', $ff->fc_no)->first();
                $um_nik = EmployeeStructure::where('nik', $ff->fc_no)->first()->manager_code ?? 999999999999;
                $um = Employee::where('nik', $um_nik)->first() ?? '';
                
                // Per FF
                // $dis_no_ff = Distribution::where('ff_no', $d->ff_no)->whereIn('distribution_no', $distribution_no)->pluck('distribution_no');
                // $monitoring_no_ff = Monitoring::where(['user_id' => $d->ff_no, 'planting_year' => $py])->pluck('monitoring_no');
                // $sostam_ff = PlantingSocializations::where(['ff_no' => $d->ff_no, 'planting_year' => $py])->pluck('form_no');
                // $penlub_ff = PlantingHoleSurviellance::where(['user_id' => $d->ff_no, 'planting_year' => $py])->pluck('ph_form_no');
                
                // Per Farmer
                $dis_no_farmer = [$d->distribution_no];
                $sostam = PlantingSocializations::where(['farmer_no' => $d->farmer_no, 'planting_year' => $py, 'validation' => 1, 'is_dell' => 0]);
                $monitoring_no_ff = Monitoring::where(['farmer_no' => $d->farmer_no, 'planting_year' => $py])->pluck('monitoring_no');
                $sostam_ff = $sostam->pluck('form_no');
                $lahans = $sostam->pluck('no_lahan'); 
                $lahanF = Lahan::whereIn('lahan_no', $lahans)->first();
                $penlub_ff = PlantingHoleSurviellance::whereIn('lahan_no', $lahans)->pluck('ph_form_no');
                $treesInSostam = PlantingSocializationsDetails::whereIn('form_no', $sostam_ff)->pluck('tree_code')->toArray();
                
                $data = [
                    'No' => count($newLists) + 1,
                    'Date' => date('d/m/Y', strtotime($d->distribution_date)),
                    'Nursery' => $this->getNurseryAlocation($ff->mu_no),
                    'MU' => ManagementUnit::where('mu_no', $ff->mu_no)->first()->name ?? '-',
                    'TA' => TargetArea::where('area_code', $ff->target_area)->first()->name ?? '-',
                    'Village' => Desa::where('kode_desa', $ff->working_area)->first()->name ?? '-',
                    'UM' => $um->name ?? '-',
                    'FC' => $fc->name ?? '-',
                    'FF' => $ff->name ?? '-',
                    // 'FF No' => $ff->ff_no ?? '-',
                    'Farmer' => $farmer->name ?? '-',
                    'PlantingPattern' => $lahanF->opsi_pola_tanam ?? '-',
                    // 'lahan' => $lahans,
                    // 'sostam' => $sostam_ff,
                ];
                
                // $treesDetails = [];
                // if ($req->download === 'true') {
                    foreach($treesData as $tree) {
                        if (in_array($tree->tree_code, $treesInSostam)) {
                            // $data['jenis_bibit'][$tree->tree_name] = 'yow';
                            $data['jenis_bibit'][$tree->tree_name.'/sostam'] = (int)PlantingSocializationsDetails::whereIn('form_no', $sostam_ff)->where(['tree_code' => $tree->tree_code])->sum('amount');
                            $data['jenis_bibit'][$tree->tree_name.'/penlub'] = (int)PlantingHoleSurviellanceDetail::whereIn('ph_form_no', $penlub_ff)->where(['tree_code' => $tree->tree_code])->sum('amount');
                            $data['jenis_bibit'][$tree->tree_name.'/loaded'] = (int)DistributionDetail::whereIn('distribution_no', $dis_no_farmer)->where(['tree_name' => $tree->tree_name, 'is_loaded' => 1])->sum('tree_amount');
                            $data['jenis_bibit'][$tree->tree_name.'/received'] = (int)DistributionAdjustment::whereIn('distribution_no', $dis_no_farmer)->where(['tree_code' => $tree->tree_code])->sum('total_tree_received');
                            $data['jenis_bibit'][$tree->tree_name.'/planted_live'] = (int)MonitoringDetail::whereIn('monitoring_no', $monitoring_no_ff)->where([ 'tree_code' => $tree->tree_code, 'status' => 'sudah_ditanam', 'condition' => 'hidup' ])->sum('qty');
                            $data['jenis_bibit'][$tree->tree_name.'/dead'] = (int)MonitoringDetail::whereIn('monitoring_no', $monitoring_no_ff)->where([ 'tree_code' => $tree->tree_code, 'condition' => 'mati' ])->sum('qty');
                            $data['jenis_bibit'][$tree->tree_name.'/lost'] = (int)MonitoringDetail::whereIn('monitoring_no', $monitoring_no_ff)->where([ 'tree_code' => $tree->tree_code, 'condition' => 'hilang' ])->sum('qty');
                        } else {
                            $data['jenis_bibit'][$tree->tree_name.'/sostam'] = 0; 
                            $data['jenis_bibit'][$tree->tree_name.'/penlub'] = 0; 
                            $data['jenis_bibit'][$tree->tree_name.'/loaded'] = 0; 
                            $data['jenis_bibit'][$tree->tree_name.'/received'] = 0; 
                            $data['jenis_bibit'][$tree->tree_name.'/planted_live'] = 0; 
                            $data['jenis_bibit'][$tree->tree_name.'/dead'] = 0; 
                            $data['jenis_bibit'][$tree->tree_name.'/lost'] = 0; 
                        }
                    }
                // }
                array_push($newLists, (object)$data);
            }
        }
            
        $rslt = [
            'py' => $py,
            'download' => $req->download ?? 'false',
            'ff_no' => $perFF,
            'queue' => $queueFF,
            'total_farmer' => count($newLists),
            // 'trees_data' => $treesData,
            'distributions' => $newLists
        ];
        
        return response()->json($rslt);
            
        // return view('ExportAllDataPerFF', ['data' => $rslt]);
    }
    public function GetDistributionReportOutside(Request $req) {
        // validate request
        $validate = Validator::make($req->all(), [
            'ff_no' => 'required',
            'program_year' => 'required',
            'per' => 'required'
        ]);
        // validation fails
        if($validate->fails()){
            $rslt =  $this->ResultReturn(400, $validate->errors()->first(), $validate->errors()->first());
            return response()->json($rslt, 400);
        } else {
            // $start = $req->date_start;
            // $end = $req->date_end;
            $py = $req->program_year;
            $ffArray = explode("FF", $req->ff_no);
            foreach($ffArray as $ffIndex => $ffa) {
                $ffArray[$ffIndex] = 'FF'.str_replace("\n", "", $ffa);
            }
            array_shift($ffArray);
            $perFF = array_slice($ffArray, 0, $req->per);
            $queueFF = array_slice($ffArray, $req->per, count($ffArray));
            // return response()->json($queueFF);
        }
        
        $distributions = Distribution::
            where([['ff_no', 'LIKE', 'FF%'], ['distribution_no', 'LIKE', 'D-'.$py.'%']])
            ->whereIn('ff_no', $perFF)
            // ->whereDate('distribution_date', '>=', $start)
            // ->whereDate('distribution_date', '<=', $end)
            // ->groupBy('ff_no')
            ->orderBy('distribution_date')->orderBy('ff_no');
        
        $form_no_sostam = PlantingSocializations::where(['planting_year' => $py, 'validation' => 1, 'is_dell' => 0])->pluck('form_no');
        $tree_code_sostam = PlantingSocializationsDetails::whereIn('form_no', $form_no_sostam)->groupBy('tree_code')->pluck('tree_code');
            
        $treesData = TreeLocation::select('tree_name', 'tree_code', 'category');
        $treesData = $treesData->whereIn('tree_code', $tree_code_sostam);
        $treesData = $treesData->groupBy('tree_code')->orderBy('category')->orderBy('tree_name');
        $treesData = $treesData->get();
        
        $kayu = array_filter($treesData->toArray(), function($tree) {
            return $tree['category'] == 'KAYU';
        });
        $mpts = array_filter($treesData->toArray(), function($tree) {
            return $tree['category'] == 'MPTS';
        });
        $crops = array_filter($treesData->toArray(), function($tree) {
            return $tree['category'] == 'CROPS';
        });
        
        $newLists = [];
        
        $distributions = $distributions->get();
        foreach($distributions as $d) {
            $ff = FieldFacilitator::where(['ff_no' => $d->ff_no, ['ff_no', 'LIKE', 'FF%']])->first();
            if ($ff) {
                $farmer = Farmer::where(['farmer_no' => $d->farmer_no])->first();
                $fc = Employee::where('nik', $ff->fc_no)->first();
                $um_nik = EmployeeStructure::where('nik', $ff->fc_no)->first()->manager_code ?? 999999999999;
                $um = Employee::where('nik', $um_nik)->first() ?? '';
                
                // Per FF
                // $dis_no_ff = Distribution::where('ff_no', $d->ff_no)->whereIn('distribution_no', $distribution_no)->pluck('distribution_no');
                // $monitoring_no_ff = Monitoring::where(['user_id' => $d->ff_no, 'planting_year' => $py])->pluck('monitoring_no');
                // $sostam_ff = PlantingSocializations::where(['ff_no' => $d->ff_no, 'planting_year' => $py])->pluck('form_no');
                // $penlub_ff = PlantingHoleSurviellance::where(['user_id' => $d->ff_no, 'planting_year' => $py])->pluck('ph_form_no');
                
                // Per Farmer
                $dis_no_farmer = [$d->distribution_no];
                $sostam = PlantingSocializations::where(['farmer_no' => $d->farmer_no, 'planting_year' => $py, 'validation' => 1, 'is_dell' => 0]);
                $monitoring_no_farmer = Monitoring::where(['farmer_no' => $d->farmer_no, 'planting_year' => $py, 'is_validate' => 2])->pluck('monitoring_no')->toArray();
                if (count($monitoring_no_farmer) > 0) {
                    $monitoringDate = Monitoring::where('monitoring_no', $monitoring_no_farmer[0])->first()->planting_date ?? null;
                    if ($monitoringDate) $monitoringDateFormat = date('Y/m/d', strtotime($monitoringDate));
                }
                $sostam_ff = $sostam->pluck('form_no');
                $lahans = $sostam->pluck('no_lahan'); 
                $lahanF = Lahan::whereIn('lahan_no', $lahans)->first();
                $penlub_ff = PlantingHoleSurviellance::whereIn('lahan_no', $lahans)->pluck('ph_form_no');
                $treesInSostam = PlantingSocializationsDetails::whereIn('form_no', $sostam_ff)->pluck('tree_code')->toArray();
                
                $data = [
                    'No' => count($newLists) + 1,
                    'DistributionDate' => date('Y/m/d', strtotime($d->distribution_date)),
                    'PlantingDate' => $monitoringDateFormat ?? '-',
                    'Nursery' => $this->getNurseryAlocation($ff->mu_no),
                    'MU' => ManagementUnit::where('mu_no', $ff->mu_no)->first()->name ?? '-',
                    'TA' => TargetArea::where('area_code', $ff->target_area)->first()->name ?? '-',
                    'Village' => Desa::where('kode_desa', $ff->working_area)->first()->name ?? '-',
                    'UM' => $um->name ?? '-',
                    'FC' => $fc->name ?? '-',
                    'FF' => $ff->name ?? '-',
                    // 'FF No' => $ff->ff_no ?? '-',
                    'Farmer' => $farmer->name ?? '-',
                    'PlantingPattern' => $lahanF->opsi_pola_tanam ?? '-',
                    // 'lahan' => $lahans,
                    // 'sostam' => $sostam_ff,
                ];
                
                // $treesDetails = [];
                if ($req->download === 'true') {
                    foreach($treesData as $tree) {
                        if (in_array($tree->tree_code, $treesInSostam)) {
                            // $data['jenis_bibit'][$tree->tree_name] = 'yow';
                            $data['jenis_bibit'][$tree->tree_name.'/sostam'] = (int)PlantingSocializationsDetails::whereIn('form_no', $sostam_ff)->where(['tree_code' => $tree->tree_code])->sum('amount');
                            $data['jenis_bibit'][$tree->tree_name.'/penlub'] = (int)PlantingHoleSurviellanceDetail::whereIn('ph_form_no', $penlub_ff)->where(['tree_code' => $tree->tree_code])->sum('amount');
                            $data['jenis_bibit'][$tree->tree_name.'/loaded'] = (int)DistributionDetail::whereIn('distribution_no', $dis_no_farmer)->where(['tree_name' => $tree->tree_name, 'is_loaded' => 1])->sum('tree_amount');
                            $data['jenis_bibit'][$tree->tree_name.'/received'] = (int)DistributionAdjustment::whereIn('distribution_no', $dis_no_farmer)->where(['tree_code' => $tree->tree_code])->sum('total_tree_received');
                            // $data['jenis_bibit'][$tree->tree_name.'/sostam'] = 0; 
                            // $data['jenis_bibit'][$tree->tree_name.'/penlub'] = 0; 
                            // $data['jenis_bibit'][$tree->tree_name.'/loaded'] = 0; 
                            // $data['jenis_bibit'][$tree->tree_name.'/received'] = 0; 
                            $data['jenis_bibit'][$tree->tree_name.'/planted_live'] = (int)MonitoringDetail::whereIn('monitoring_no', $monitoring_no_farmer)->where([ 'tree_code' => $tree->tree_code, 'status' => 'sudah_ditanam', 'condition' => 'hidup' ])->sum('qty');
                            $data['jenis_bibit'][$tree->tree_name.'/dead'] = (int)MonitoringDetail::whereIn('monitoring_no', $monitoring_no_farmer)->where([ 'tree_code' => $tree->tree_code, 'condition' => 'mati' ])->sum('qty');
                            $data['jenis_bibit'][$tree->tree_name.'/lost'] = (int)MonitoringDetail::whereIn('monitoring_no', $monitoring_no_farmer)->where([ 'tree_code' => $tree->tree_code, 'condition' => 'hilang' ])->sum('qty');
                        } else {
                            $data['jenis_bibit'][$tree->tree_name.'/sostam'] = 0; 
                            $data['jenis_bibit'][$tree->tree_name.'/penlub'] = 0; 
                            $data['jenis_bibit'][$tree->tree_name.'/loaded'] = 0; 
                            $data['jenis_bibit'][$tree->tree_name.'/received'] = 0; 
                            $data['jenis_bibit'][$tree->tree_name.'/planted_live'] = 0; 
                            $data['jenis_bibit'][$tree->tree_name.'/dead'] = 0; 
                            $data['jenis_bibit'][$tree->tree_name.'/lost'] = 0; 
                        }
                    }
                }
                array_push($newLists, (object)$data);
            }
        }
            
        $rslt = [
            'py' => $py,
            'per' => $req->per ?? 1,
            'download' => $req->download ?? 'false',
            'ff_no' => implode("", $perFF),
            'queue' => implode("", $queueFF),
            'total_farmer' => count($newLists),
            'trees_data' => $treesData,
            'trees_count' => (object)[
                'kayu' => $kayu ? count($kayu) : 0,
                'mpts' => $kayu ? count($mpts) : 0,
                'crops' => $kayu ? count($crops) : 0,
            ],
            'distributions' => $newLists
        ];
        
        // return response()->json($rslt);
            
        return view('temp/ExportAllDataPerFF', ['data' => $rslt]);
    }
    public function GetDistributionReportUmumOutside(Request $req) {
        // validate request
        $validate = Validator::make($req->all(), [
            'program_year' => 'required',
        ]);
        // validation fails
        if($validate->fails()){
            $rslt =  $this->ResultReturn(400, $validate->errors()->first(), $validate->errors()->first());
            return response()->json($rslt, 400);
        } else {
            // $start = $req->date_start;
            // $end = $req->date_end;
            $py = $req->program_year;
            // return response()->json($queueFF);
        }
        
        $distributions = DB::table('lahan_umum_distributions')
            // ->whereDate('distribution_date', '>=', $start)
            // ->whereDate('distribution_date', '<=', $end)
            // ->groupBy('ff_no')
            ->where('status', 2)
            ->orderBy('distribution_date')->orderBy('created_at');
        
        $lahan_nos = LahanUmum::where(['program_year' => $py, 'is_dell' => 0])->whereIn('mou_no', $distributions->pluck('mou_no'))->pluck('lahan_no');
        $tree_codes = LahanUmumDetail::whereIn('lahan_no', $lahan_nos)->groupBy('tree_code')->pluck('tree_code');
            
        $treesData = TreeLocation::select('tree_name', 'tree_code', 'category');
        $treesData = $treesData->whereIn('tree_code', $tree_codes);
        $treesData = $treesData->groupBy('tree_code')->orderBy('category')->orderBy('tree_name');
        $treesData = $treesData->get();
        
        $kayu = array_filter($treesData->toArray(), function($tree) {
            return $tree['category'] == 'KAYU';
        });
        $mpts = array_filter($treesData->toArray(), function($tree) {
            return $tree['category'] == 'MPTS';
        });
        $crops = array_filter($treesData->toArray(), function($tree) {
            return $tree['category'] == 'CROPS';
        });
        
        $newLists = [];
        
        $distributions = $distributions->get();
        foreach($distributions as $d) {
            $lahan = LahanUmum::where(['mou_no' => $d->mou_no])->first();
            if ($lahan) {
                $pic_t4t = Employee::where('nik', $lahan->employee_no)->first();
                
                // Per MoU
                $dis_no_mou = [$d->distribution_no];
                $lahanss = LahanUmum::where(['mou_no' => $d->mou_no,'program_year' => $py, 'is_dell' => 0]);
                $monitoring_no_mou = DB::table('lahan_umum_monitorings')->where(['mou_no' => $d->mou_no, 'program_year' => $py, 'is_verified' => 2])->pluck('monitoring_no')->toArray();
                if (count($monitoring_no_mou) > 0) {
                    $monitoringDate = DB::table('lahan_umum_monitorings')->where('monitoring_no', $monitoring_no_mou[0])->first()->planting_date ?? null;
                    if ($monitoringDate) $monitoringDateFormat = date('Y/m/d', strtotime($monitoringDate));
                }
                $lahans = $lahanss->pluck('lahan_no'); 
                $lahanF = LahanUmum::whereIn('lahan_no', $lahans)->first();
                $treesInRecord = DB::table('lahan_umum_details')->whereIn('lahan_no', $lahans)->pluck('tree_code')->toArray();
                
                $data = [
                    'No' => count($newLists) + 1,
                    'MoUNo' => $d->mou_no,
                    'DistributionDate' => date('Y/m/d', strtotime($d->distribution_date)),
                    'PlantingDate' => $monitoringDateFormat ?? '-',
                    'Nursery' => $this->getNurseryAlocation($lahanF->mu_no),
                    'MU' => ManagementUnit::where('mu_no', $lahanF->mu_no)->first()->name ?? '-',
                    'Regency' => DB::table('kabupatens')->where('kabupaten_no', $lahanF->regency)->first()->name ?? '-',
                    'District' => DB::table('kecamatans')->where('kode_kecamatan', $lahanF->district)->first()->name ?? '-',
                    'Village' => Desa::where('kode_desa', $lahanF->village)->first()->name ?? '-',
                    'pic_t4t' => $pic_t4t->name ?? '-',
                    'pic_lahan' => $lahanF->pic_lahan ?? '-',
                    'PlantingPattern' => $lahanF->pattern_planting ?? '-',
                    // 'lahan' => $lahans,
                ];
                
                // $treesDetails = [];
                // if ($req->download === 'true' || count($newLists) == 0) {
                    foreach($treesData as $tree) {
                        if (in_array($tree->tree_code, $treesInRecord)) {
                            // $data['jenis_bibit'][$tree->tree_name] = 'yow';
                            $data['jenis_bibit'][$tree->tree_name.'/input'] = (int)DB::table('lahan_umum_details')->whereIn('lahan_no', $lahans)->where(['tree_code' => $tree->tree_code])->sum('amount');
                            $data['jenis_bibit'][$tree->tree_name.'/penlub'] = (int)DB::table('lahan_umum_hole_details')->whereIn('lahan_no', $lahans)->where(['tree_code' => $tree->tree_code])->sum('amount');
                            $data['jenis_bibit'][$tree->tree_name.'/loaded'] = (int)DB::table('lahan_umum_distribution_details')->whereIn('distribution_no', $dis_no_mou)->where(['tree_name' => $tree->tree_name, 'is_loaded' => 1])->sum('tree_amount');
                            $data['jenis_bibit'][$tree->tree_name.'/received'] = (int)DB::table('lahan_umum_adjustments')->whereIn('distribution_no', $dis_no_mou)->where(['tree_code' => $tree->tree_code])->sum('total_tree_received');
                            // $data['jenis_bibit'][$tree->tree_name.'/sostam'] = 0; 
                            // $data['jenis_bibit'][$tree->tree_name.'/penlub'] = 0; 
                            // $data['jenis_bibit'][$tree->tree_name.'/loaded'] = 0; 
                            // $data['jenis_bibit'][$tree->tree_name.'/received'] = 0; 
                            $data['jenis_bibit'][$tree->tree_name.'/planted_live'] = (int)DB::table('lahan_umum_monitoring_details')->whereIn('monitoring_no', $monitoring_no_mou)->where([ 'tree_code' => $tree->tree_code, 'status' => 'sudah_ditanam', 'condition' => 'hidup' ])->sum('qty');
                            $data['jenis_bibit'][$tree->tree_name.'/dead'] = (int)DB::table('lahan_umum_monitoring_details')->whereIn('monitoring_no', $monitoring_no_mou)->where([ 'tree_code' => $tree->tree_code, 'condition' => 'mati' ])->sum('qty');
                            $data['jenis_bibit'][$tree->tree_name.'/lost'] = (int)DB::table('lahan_umum_monitoring_details')->whereIn('monitoring_no', $monitoring_no_mou)->where([ 'tree_code' => $tree->tree_code, 'condition' => 'hilang' ])->sum('qty');
                        } else {
                            $data['jenis_bibit'][$tree->tree_name.'/input'] = 0; 
                            $data['jenis_bibit'][$tree->tree_name.'/penlub'] = 0; 
                            $data['jenis_bibit'][$tree->tree_name.'/loaded'] = 0; 
                            $data['jenis_bibit'][$tree->tree_name.'/received'] = 0; 
                            $data['jenis_bibit'][$tree->tree_name.'/planted_live'] = 0; 
                            $data['jenis_bibit'][$tree->tree_name.'/dead'] = 0; 
                            $data['jenis_bibit'][$tree->tree_name.'/lost'] = 0; 
                        }
                    }
                // }
                array_push($newLists, (object)$data);
            }
        }
            
        $rslt = [
            'py' => $py,
            'download' => $req->download ?? 'false',
            'total_mou' => count($newLists),
            'trees_data' => $treesData,
            'trees_count' => (object)[
                'kayu' => $kayu ? count($kayu) : 0,
                'mpts' => $kayu ? count($mpts) : 0,
                'crops' => $kayu ? count($crops) : 0,
            ],
            'distributions' => $newLists
        ];
        
        // return response()->json($rslt);
            
        return view('temp/ExportAllDataLahanUmum', ['data' => $rslt]);
    }
    
    public function GetDataMainLahan(Request $req) {
        // validate request
        $validate = Validator::make($req->all(), [
            'program_year' => 'required'
        ]);
        // validation fails
        if($validate->fails()){
            $rslt =  $this->ResultReturn(400, $validate->errors()->first(), $validate->errors()->first());
            return response()->json($rslt, 400);
        } else {
            $py = $req->program_year;
        }
        
        $lahans = Lahan::whereYear('created_time', $py)
            ->join('farmers', 'farmers.farmer_no', 'lahans.farmer_no')
            ->join('field_facilitators', 'field_facilitators.ff_no', 'lahans.user_id')
            ->join('managementunits', 'managementunits.mu_no', 'field_facilitators.mu_no')
            ->join('target_areas', 'target_areas.area_code', 'field_facilitators.target_area')
            ->join('desas', 'desas.kode_desa', 'field_facilitators.working_area')
            ->join('employees', 'employees.nik', 'field_facilitators.fc_no')
            ->select(
                'managementunits.name as ManagementUnit',
                'target_areas.name as TargetArea',
                'desas.name as Village',
                'employees.name as FC',
                'field_facilitators.name as FF',
                'farmers.name as Farmer',
                'lahans.lahan_no as LahanNo',
                'lahans.document_no as DocumentNo',
                'lahans.type_sppt as SPPTType',
                'lahans.land_area as LandArea',
                'lahans.tutupan_lahan as LandCover',
                'lahans.planting_area as PlantingArea',
                'lahans.jarak_lahan as LandDistance',
                'lahans.access_to_lahan as LandAccess',
                'lahans.water_availability as WaterAvailability',
                'lahans.access_to_water_sources as WaterAccess',
                'lahans.lahan_type as LandType',
                'lahans.polygon as LandShape',
                'lahans.fertilizer as Fertilizer',
                'lahans.pesticide as Pesticide',
                'lahans.opsi_pola_tanam as PlantingPattern',
                'lahans.description as Description',
                'lahans.longitude as Longitude',
                'lahans.latitude as Latitude',
                'lahans.coordinate as Coordinate'
            )
            ->where([
                'lahans.is_dell' => 0, 
                'lahans.approve' => 1, 
                ['lahans.user_id', 'LIKE', 'FF%'],
                'farmers.approve' => 1,
                'farmers.is_dell' => 0
            ])
            ->orderBy('lahans.created_at')
            ->get();
        
        $rslt = (object)[
            'py' => $py,
            'count' => count($lahans),
            'data' => $lahans
        ];
        
        // return response()->json($rslt, 200);
        return view('ExportDataLahansPerPY', ['data' => $rslt]);
        
    }
    
    // MONITORING ENHANCED
    public function GetMonitoringReportEnhanced(Request $req) {
        $ff_no = DB::table('field_facilitators')->where('fc_no', $req->fc_no)->pluck('ff_no');
        // return response()->json($ff_no);
        $py = $req->program_year;
        
        $generated_data = [];
        $monitoring_no_all = DB::table('monitorings')->whereIn('user_id', $ff_no)->where(['planting_year' => $py, 'is_validate' => 2])->pluck('monitoring_no');
        foreach ($monitoring_no_all as $mna) {
            $monitoring = DB::table('monitorings')->where('monitoring_no', $mna)->first();
            // GET MONITORING Data
            $monitoring_details_pl = DB::table('monitoring_details')->select('tree_code', 'qty as amount')->where([
                'status' => 'sudah_ditanam',
                'condition' => 'hidup',
                'monitoring_no' => $monitoring->monitoring_no
            ])->get()->toArray();
            $monitoring_details_d = DB::table('monitoring_details')->select('tree_code', 'qty as amount')->where([
                'condition' => 'mati',
                'monitoring_no' => $monitoring->monitoring_no
            ])->get()->toArray();
            $monitoring_details_l = DB::table('monitoring_details')->select('tree_code', 'qty as amount')->where([
                'condition' => 'hilang',
                'monitoring_no' => $monitoring->monitoring_no
            ])->get()->toArray();
            
            $lahan_no_arr = explode(",", $monitoring->lahan_no);
            // GET TOTAL TREE RECEIVED
            $total_tree_received = DB::table('distribution_adjustments')
                ->select('lahan_no', 'tree_code', 'total_tree_received as amount')
                ->whereIn('lahan_no', $lahan_no_arr)
                ->get()->toArray();
            
            $set_per_lahan = [];
            // SET DATA PER LAHAN
            $sum_pl = [];
            $sum_d = [];
            $sum_l = [];
            foreach ($lahan_no_arr as $lnaIndex => $lna) {
                // get lahan details
                $per_lahan_data = DB::table('lahans')
                    ->select(
                        'managementunits.name as mu_name', 
                        'target_areas.name as ta_name',
                        'desas.name as village_name',
                        'employees.name as fc_name',
                        'field_facilitators.name as ff_name',
                        'lahans.lahan_no',
                        'lahans.document_no',
                        'lahans.coordinate',
                        'lahans.land_area',
                        'lahans.planting_area',
                        'farmers.name as farmer_name',
                        'farmers.ktp_no as farmer_ktp',
                        'lahans.opsi_pola_tanam'
                    )
                    ->join('managementunits', 'managementunits.mu_no','lahans.mu_no')
                    ->join('target_areas', 'target_areas.area_code','lahans.target_area')
                    ->join('desas', 'desas.kode_desa','lahans.village')
                    ->join('field_facilitators', 'field_facilitators.ff_no','lahans.user_id')
                    ->join('farmers', 'farmers.farmer_no','lahans.farmer_no')
                    ->join('employees', 'employees.nik','field_facilitators.fc_no')
                    ->where([
                        'lahans.lahan_no' => $lna
                    ])->first();
                $per_lahan_data->program_year = $monitoring->planting_year;
                $per_lahan_data->planting_date = $monitoring->planting_date;
                $per_lahan_data->is_validate = $monitoring->is_validate;
                $per_lahan_data->lahan_trees = [];
                $per_lahan_data->lahan_tree_codes = [];
                // filter ph details
                $total_tree_received_filter = array_filter($total_tree_received, function($object2) use ($lna) {
                    return $object2->lahan_no == $lna;
                });
                // SET PERCENTAGE && SET MONITORING DATA
                foreach ($total_tree_received_filter as $ttrfIndex => $ttrf) {
                    array_push($per_lahan_data->lahan_tree_codes, $ttrf->tree_code);
                    // set percentage
                    $sum_per_jenis = array_sum(array_map(function($phdetail) use ($ttrf) {
                        if ($ttrf->tree_code == $phdetail->tree_code) return $phdetail->amount;  
                    }, $total_tree_received));
                    $ttrf->sum_all = $sum_per_jenis;
                    if ($sum_per_jenis == 0) $ttrf->percentage = 0;
                    else $ttrf->percentage = round($ttrf->amount / $sum_per_jenis * 100);
                    // set monitoring data planting live
                    $monitoring_filtered_pl = array_filter($monitoring_details_pl, function($mon_filter_pl) use ($ttrf) {
                        return $mon_filter_pl->tree_code == $ttrf->tree_code;
                    });
                    $monitoring_amount_pl = array_shift($monitoring_filtered_pl)->amount ?? 0;
                    $ttrf->monitoring_planted_live_all = $monitoring_amount_pl;
                    // set monitoring data dead
                    $monitoring_filtered_d = array_filter($monitoring_details_d, function($mon_filter_d) use ($ttrf) {
                        return $mon_filter_d->tree_code == $ttrf->tree_code;
                    });
                    $monitoring_amount_d = array_shift($monitoring_filtered_d)->amount ?? 0;
                    $ttrf->monitoring_dead_all = $monitoring_amount_d;
                    // set monitoring data lost
                    $monitoring_filtered_l = array_filter($monitoring_details_l, function($mon_filter_l) use ($ttrf) {
                        return $mon_filter_l->tree_code == $ttrf->tree_code;
                    });
                    $monitoring_amount_l = array_shift($monitoring_filtered_l)->amount ?? 0;
                    $ttrf->monitoring_lost_all = $monitoring_amount_l;
                    
                    $monitoring_planted_live = round($monitoring_amount_pl * $ttrf->percentage / 100);
                    $monitoring_dead = round($monitoring_amount_d * $ttrf->percentage / 100);
                    $monitoring_lost = round($monitoring_amount_l * $ttrf->percentage / 100);
                    if (!isset($sum_pl[$ttrf->tree_code])) {
                        $sum_pl[$ttrf->tree_code] = 0;
                        $sum_d[$ttrf->tree_code] = 0;
                        $sum_l[$ttrf->tree_code] = 0;
                    }
                    $sum_pl[$ttrf->tree_code] += $monitoring_planted_live;
                    $sum_d[$ttrf->tree_code] += $monitoring_dead;
                    $sum_l[$ttrf->tree_code] += $monitoring_lost;
                    if ($lnaIndex == (count($lahan_no_arr) - 1)) {
                        // FIX Different data
                        $monitoring_planted_live -= $sum_pl[$ttrf->tree_code] - $ttrf->monitoring_planted_live_all;
                        $monitoring_dead -= $sum_d[$ttrf->tree_code] - $ttrf->monitoring_dead_all;
                        $monitoring_lost -= $sum_l[$ttrf->tree_code] - $ttrf->monitoring_lost_all;
                    }
                    $ttrf->monitoring_planted_live = $monitoring_planted_live;
                    $ttrf->monitoring_dead = $monitoring_dead;
                    $ttrf->monitoring_lost = $monitoring_lost;
                    
                    array_push($per_lahan_data->lahan_trees, $ttrf);
                }
                
                array_push($set_per_lahan, $per_lahan_data);
            }
            
            array_push($generated_data, ...$set_per_lahan);
        }
        
        // 
        
        $rslt = [
            'trees_data' => TreeLocation::select('tree_name', 'tree_code')->orderBy('tree_name')->groupBy('tree_code')->get(),
            'export_data' => (object)[
                'count' => count($generated_data),
                'list' => $generated_data
            ],
        ];
        
        // return response()->json($rslt, 200);
        return view('temp.exportMonitoringEnhanced', $rslt);
    }
    
    // Get Survival Rate
    public function GetSurvivalRate(Request $req) {
        
        $monitorings = DB::table('monitorings');
        
        $monitorings = $monitorings->where();
    }
    
    public function CheckLahan() {
        $distributions = DB::table('distributions')->get();
        $match = 0;
        $diff = 0;
        $sos404 = 0;
        $per404 = 0;
        foreach ($distributions as $d) {
            $sostam = DB::table('planting_socializations')->where([
                    'planting_year' => '2022',
                    'farmer_no' => $d->farmer_no
                ])->first();
                
            if ($sostam) {
                $period = DB::table('planting_period')->where('form_no', $sostam->form_no)->first();
                if ($period) {
                    $sostam_date = date('Y-m-d', strtotime($period->distribution_time));
                    $distribution_date = date('Y-m-d', strtotime($d->distribution_date));
                    if ($sostam_date == $distribution_date) $match += 1;
                    else $diff += 1;
                } else $per404 += 1;
            } else $sos404 += 1;
        }
        return response()->json([
            'match' => $match,
            'diff' => $diff,
            'sos404' => $sos404,
            'per404' => $per404,
        ], 200);
    }

    public function updateLatLongLahan(Request $request) {
        $datas = explode(";", $request->datas);
        
        $treesNames = Trees::orderBy('tree_name')->pluck('tree_name')->toArray();
        
        $header = explode("\t", $datas[0]);
        
        $lahans = [];
        foreach ($datas as $dataIndex => $data) {
            if ($dataIndex > 0 && $data) {
                $explodedData = explode("\t", $data);
                $lahan = [];
                $lahan['lahan_details'] = [];
                foreach ($explodedData as $edIndex => $eD) {
                    if ($eD || $eD > 0) {
                        if (in_array($header[$edIndex], $treesNames)) {
                            array_push($lahan['lahan_details'], [
                                'tree_name' => $header[$edIndex],
                                'tree_code' => Trees::where('tree_name', $header[$edIndex])->first()->tree_code ?? '',
                                'amount' => (int)$eD
                            ]);
                        } else {
                            $lahan[$header[$edIndex]] = str_replace("\n", '', $eD);
                        }
                    }
                }
                
                array_push($lahans, $lahan);
            }
        }
        $cCount = 0;
        foreach ($lahans as $lData) {
            // create Lahan Details
            if (isset($lData['no_lahan'])) {
                // delete existing
                LahanDetail::where('lahan_no', $lData['no_lahan'])->delete();
                PlantingSocializationsPeriod::where('form_no', 'SO-'.$lData['planting_year'].'-'.str_replace('10_', '', $lData['no_lahan']))->delete();
                PlantingSocializationsDetails::where('form_no', 'SO-'.$lData['planting_year'].'-'.str_replace('10_', '', $lData['no_lahan']))->delete();
                
                // create planting period
                PlantingSocializationsPeriod::create([
                    'form_no' => 'SO-'.$lData['planting_year'].'-'.str_replace('10_', '', $lData['no_lahan']),
                    'pembuatan_lubang_tanam' => date("Y/m/d H:i:s", strtotime($lData['pembuatan_lubang_tanam'])),
                    'distribution_time' => date("Y/m/d H:i:s", strtotime($lData['distribution_time'])),
                    'distribution_location' => $lData['distribution_location'],
                    'planting_time' => date("Y/m/d H:i:s", strtotime($lData['planting_time']))
                ]);
                foreach ($lData['lahan_details'] as $lDetail) {
                    // create lahan details
                    LahanDetail::create([
                        'lahan_no' => $lData['no_lahan'],
                        'tree_code' => $lDetail['tree_code'],
                        'amount' => $lDetail['amount'],
                        'detail_year' => '2022-12-31',
                        'user_id' => Lahan::where('lahan_no', $lData['no_lahan'])->first()->user_id ?? '',
                    ]);
                    // create planting details
                    PlantingSocializationsDetails::create([
                        'form_no' => 'SO-'.$lData['planting_year'].'-'.str_replace('10_', '', $lData['no_lahan']),
                        'tree_code' => $lDetail['tree_code'],
                        'amount' => $lDetail['amount'],
                    ]);
                    
                    $cCount += 1;
                }
            } else {
                return response()->json('GAGAL', 500);   
            }
        }
        
        $rslt = [
            'created' => [
                'count' => $cCount
            ],
            'header' => $header,
            'datas' => [
                'count' => count($lahans),
                'list' => $lahans
            ],
        ];
        return response()->json($rslt, 200);
    }

    public function MassUpdateLatLongLahan(Request $request) {
        $datas = explode(";", $request->datas);
        
        $header = ['lahan_no', 'longitude', 'latitude'];
        
        $lahans = [];
        $updated = 0;
        $failed = [];
        foreach ($datas as $dataIndex => $data) {
            if ($data) {
                $explodedData = explode("\t", $data);
                $lahan = [];
                foreach ($explodedData as $edIndex => $eD) {
                    if ($eD) $lahan[$header[$edIndex]] = str_replace(",", ".", str_replace("\n", '', $eD));
                }
                
                $lahan['coordinate'] = $this->getCordinate($lahan['longitude'], $lahan['latitude']);
                
                $lahanOld = Lahan::where('lahan_no', $lahan['lahan_no'])->first();
                if ($lahanOld) {
                    $changedLongitude = $lahanOld->longitude != $lahan['longitude'];
                    $changedLatitude = $lahanOld->latitude != $lahan['latitude'];
                    $changedCoordinate = $lahanOld->coordinate != $lahan['coordinate'];
                    $changedUpdatedGis = $lahanOld->updated_gis == 'belum';
                    if($changedLongitude || $changedLatitude || $changedCoordinate || $changedUpdatedGis) {
                        $log = Log::channel('lahans');
                        
                        $petani = Farmer::where('farmer_no', $lahanOld->farmer_no)->first()->name ?? '-';
                        $ff = FieldFacilitator::where('ff_no', $lahanOld->user_id)->first();
                        $ffName = $ff->name ?? '-';
                        if ($ff) $fc = Employee::where('nik', $ff->fc_no)->first()->name ?? '-';
                        else $fc = '-';
                        
                        $message = "Updated GIS " . $lahan['lahan_no'] . " [petani=$petani, ff=$ffName, fc=$fc]";
                        
                        $message = $message . " changelog (";
                        if ($changedLongitude) $message = $message . $lahanOld->longitude . "=>" . $lahan['longitude'] . ", ";
                        if ($changedLatitude) $message = $message . $lahanOld->latitude . "=>" . $lahan['latitude'] . ", ";
                        if ($changedCoordinate) $message = $message . $lahanOld->coordinate . "=>" . $lahan['coordinate'] . ", ";
                        if ($changedUpdatedGis) $message = $message . "belum=>sudah";
                        $message = $message . ") by " . (Auth::user()->email ?? '-');
                        
                        $log->notice($message);
                    }
                    
                    Lahan::where('lahan_no', $lahan['lahan_no'])->update([
                        'longitude' => $lahan['longitude'],
                        'latitude' => $lahan['latitude'],
                        'coordinate' => $lahan['coordinate'],
                        'updated_gis' => 'sudah'
                    ]);
                    $updated += 1;
                } else array_push($failed, $lahan['lahan_no']);
                
                array_push($lahans, $lahan);
            }
        }
        
        $rslt = [
            'a_header' => $header,
            'b_updated' => $updated,
            'c_failed_list' => $failed,
            'd_list' => [
                'count' => count($lahans),
                'data' => $lahans
            ],
        ];
        return response()->json($rslt, 200);
    }

    public function updateLatLongLahanTemp(Request $request) {
        $file = $request->file('file');
        if ($file) {
            $filename = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension(); //Get extension of uploaded file
            $tempPath = $file->getRealPath();
            $fileSize = $file->getSize(); //Get size of uploaded file in bytes
            //Check for file extension and size
            $check = $this->checkUploadedFileProperties($extension, $fileSize);
            if (!$check) return response()->json("Failed to upload file", 400);
            //Where uploaded file will be stored on the server 
            $location = 'excel-uploads'; //Created an "uploads" folder for that
            // Upload file
            $file->move($location, $filename);
            // In case the uploaded file path is to be stored in the database 
            $filepath = public_path($location . "/" . $filename);
            // Reading file
            $file = fopen($filepath, "r");
            
            $importData_arr = array(); // Read through the file and store the contents as an array
            $i = 0;
            //Read the contents of the uploaded file 
            while (($filedata = fgetcsv($file, 1000, ",")) !== FALSE) {
                $num = count($filedata);
                // Skip first row (Remove below comment if you want to skip the first row)
                if ($i == 0) {
                    $i++;
                    continue;
                }
                for ($c = 0; $c < $num; $c++) {
                    $importData_arr[$i][] = mb_convert_encoding($filedata[$c], 'UTF-8', 'UTF-8');
                }
                $i++;
            }
            fclose($file); //Close after reading
            $j = 0;
            $uploadedData = [];
            foreach ($importData_arr as $importData) {
                $j++;
                array_push($uploadedData, $importData);
            }
            $rslt = [
                'data' => $this->convert_from_latin1_to_utf8_recursively($importData_arr),
                'message' => "$j records successfully uploaded"
            ];
            return response()->json($rslt, 200);
        } else {
            //no file was uploaded
            return response()->json([
                'message' => "No any file was uploaded",
            ], 400);
        }
    }
    
    public function getDataLahanRequestMbakNovi(Request $req) {
        $lahans = DB::table('lahans')->select(
            'lahan_no', 
            'land_area',
            'opsi_pola_tanam',
            'farmer_no as Farmer', 
            'user_id as FF',
            'planting_area', 
            'mu_no as MU', 
            'target_area as TA', 
            'village as Village', 
            'lahan_type as Type', 
            'latitude',
            'longitude',
            DB::raw('YEAR(created_time) as Program_Year')
        )
            ->where(['approve' => 1, ['lahan_no', 'LIKE', '10_00%'], ['user_id', 'LIKE', 'FF%']]);
        if ($req->program_year) {
            $lahans = $lahans->whereYear('created_time', '2022');
        }
        if ($req->start_date && $req->end_date) {
            $lahans = $lahans
                ->whereDate('created_at', '>=', $req->start_date)
                ->whereDate('created_at', '<=', $req->end_date);
        }
        
        $lahans = $lahans->orderBy('created_at')->get();
            
        $lahansNoDetails = [];
        foreach ($lahans as $lahan) {
                $FF = DB::table('field_facilitators')->where('ff_no', $lahan->FF)->first();
                $mon1Query = DB::table('monitoring_details')->where([
                    ['monitoring_no', 'LIKE', '%-'.str_replace('10_', '', $lahan->lahan_no)],
                    'status' => 'sudah_ditanam',
                    'condition' => 'hidup'
                ]);
                
                $lahan->MU = DB::table('managementunits')->where('mu_no', $lahan->MU)->first()->name ?? '';
                $lahan->TA = DB::table('target_areas')->where('area_code', $lahan->TA)->first()->name ?? '';
                $lahan->Village = DB::table('desas')->where('kode_desa', $lahan->Village)->first()->name ?? '';
                $lahan->Farmer = DB::table('farmers')->where('farmer_no', $lahan->Farmer)->first()->name ?? '';
                $lahan->FF = $FF->name ?? '';
                $lahan->FC = DB::table('employees')->where('nik', $FF->fc_no)->first()->name ?? '';
                $lahan->planting_area = (int)$lahan->planting_area;
                $lahan->land_area = (int)$lahan->land_area;
                $lahan->monitoring1_total = $mon1Query->sum('qty');
                
                $monitoring_details = [];
                foreach ($mon1Query->get() as $mo1_detail) {
                    $tree_name = DB::table('trees')->where('tree_code', $mo1_detail->tree_code)->first()->tree_name ?? '';
                    $tree_qty = $mo1_detail->qty ?? 0;
                    array_push($monitoring_details, "$tree_name: $tree_qty");
                }
                $lahan->monitoring1_seeds = implode(", ", $monitoring_details);
        }
        
        return response()->json([
            'count' => count($lahans),
            'datas' => $lahans,
            'no_trees_data' => [
                'count' => count($lahansNoDetails),
                'datas' => $lahansNoDetails
            ]
        ], 200);
    }
    
    public function RequestDataMbakAnin(Request $req) {
        $datas = DB::table('farmer_trainings')
            ->join('managementunits', 'managementunits.mu_no', 'farmer_trainings.mu_no')
            ->join('target_areas', 'target_areas.area_code', 'farmer_trainings.target_area')
            ->join('desas', 'desas.kode_desa', 'farmer_trainings.village')
            ->join('employees', 'employees.nik', 'farmer_trainings.field_coordinator')
            ->select(
                'farmer_trainings.training_date',
                'farmer_trainings.first_material',
                'farmer_trainings.second_material',
                'managementunits.name as mu_name',
                'target_areas.name as ta_name',
                'desas.name as village_name',
                'employees.name as fc_name'
            )
            ->where([
                ['farmer_trainings.ff_no', 'LIKE', 'FF00%'],
                'farmer_trainings.program_year' => $req->program_year
            ])
            ->whereNotIn('farmer_trainings.mu_no', explode(",", $req->except_mu))
            ->orderBy('training_date')
            ->orderBy('mu_name')
            ->orderBy('ta_name')
            ->orderBy('village_name')
            ->orderBy('fc_name')
            ->get();
        
        $new_datas = [];
        
        foreach ($datas as $data) {
            $firstMaterial = DB::table('training_materials')->where('material_no', $data->first_material)->first()->material_name ?? '-';
            $data->first_material = $firstMaterial;
            $secondMaterial = DB::table('training_materials')->where('material_no', $data->second_material)->first()->material_name ?? '-';
            $data->second_material = $secondMaterial;
            $exists = false;
            foreach ($new_datas as $new) {
                $sameCount = 0;
                if ($new->training_date == $data->training_date) $sameCount += 1;
                if ($new->mu_name == $data->mu_name) $sameCount += 1;
                if ($new->ta_name == $data->ta_name) $sameCount += 1;
                if ($new->village_name == $data->village_name) $sameCount += 1;
                if ($new->fc_name == $data->fc_name) $sameCount += 1;
                if ($new->second_material == $data->second_material) $sameCount += 1;
                if ($sameCount == 6) {
                    $exists = true;
                    break;
                }
            }
            if ($exists == false) array_push($new_datas, $data);
        }
        
        return response()->json([
            'count' => [
                'origin' => count($datas),
                'filtered' => count($new_datas),
            ],
            'list' => [
                'origin' => $datas,
                'filtered' => $new_datas
            ]
        ], 200);
        
    }
    
    // request pak pandu
    public function GetDataLahanByDocumentSPPT(Request $req) {
        // $fc_no = DB::table('employee_structure')->whereIn('manager_code', explode(",", $req->um_no))->pluck('nik');
        $ff_no = FieldFacilitator::where('fc_no', $req->fc_no)->pluck('ff_no');
        $datas = Lahan::
            select(
                'managementunits.name as mu_name',
                'target_areas.name as ta_name',
                'desas.name as village_name',
                'farmers.name as farmer_name',
                'farmers.farmer_no',
                'lahans.lahan_no',
                'lahans.document_no',
                DB::raw('YEAR(lahans.created_time) as program_year')
            )
            ->join('managementunits', 'managementunits.mu_no', 'lahans.mu_no')
            ->join('target_areas', 'target_areas.area_code', 'lahans.target_area')
            ->join('desas', 'desas.kode_desa', 'lahans.village')
            ->join('farmers', 'farmers.farmer_no', 'lahans.farmer_no')
            ->where([
                ['lahans.document_no', '!=', '-'],
                'lahans.approve' => 1,
                ['lahans.lahan_no', 'like', "10\_%"]
            ])
            ->whereYear('lahans.created_time', '=', '2021')
            ->whereIn('lahans.user_id', $ff_no)
            ->orderBy('managementunits.name')
            ->orderBy('target_areas.name')
            ->orderBy('desas.name')
            ->orderBy('farmers.name')
            ->orderBy('lahans.lahan_no')
            ->get();
        $tree_codes = DB::table('monitoring_details')->groupBy('tree_code')->pluck('tree_code');
        $trees = DB::table('trees')->whereIn('tree_code', $tree_codes)->where('tree_category', '!=', 'Tanaman_Bawah_Empon')->orderBy('tree_name')->get();
        $new_datas = [];
        foreach ($datas as $data) {
            $data->document_no = "'" . str_replace('_', '',str_replace(' ','',str_replace('.','',str_replace('-', '', $data->document_no))));
            if (strlen($data->document_no) == 19) {
                $data->monitoring_no = DB::table('monitorings')->where('lahan_no', 'like', "%".$data->lahan_no."%")->first()->monitoring_no ?? null;
                if ($data->monitoring_no) {
                    $tree_mon = DB::table('monitoring_details')->where([
                            'monitoring_no' => $data->monitoring_no,
                            'condition' => 'hidup',
                            'status' => 'sudah_ditanam'
                        ])->get();
                    foreach ($tree_mon as $tm) {
                        if ($data->program_year == '2022') {
                            $received_farmer = DB::table('distribution_adjustments')
                                ->select(DB::raw('SUM(total_tree_received) as total'))->where([
                                    'farmer_no' => $data->farmer_no,
                                    'tree_code' => $tm->tree_code
                                ])->groupBy('farmer_no')->first()->total ?? 0;
                            $received_lahan = DB::table('distribution_adjustments')->where([
                                    'farmer_no' => $data->farmer_no,
                                    'lahan_no' => $data->lahan_no,
                                    'tree_code' => $tm->tree_code
                                ])->first()->total_tree_received ?? 0;
                            if ($received_lahan != 0) $separate = round($tm->qty * $received_lahan / $received_farmer);
                            (object)$data[$tm->tree_code] = $separate ?? 0;
                        } else if ($data->program_year == '2021') {
                            (object)$data[$tm->tree_code] = $tm->qty;
                        }
                    }
                }
                array_push($new_datas, $data);
            }
        }
        
        $rslt = [
            'count' => count($new_datas),
            'data' => $new_datas,
            'trees' => $trees
        ];
        return response()->json($rslt, 200);
        // return view('temp.ExportLahanSPPTRequired', $rslt);
    }
    
    public function GetPHPInfo() {
        return phpinfo();
    }
    
    // Set Load and distribute data
    public function setLoadAndDistributeData(Request $req) {
        // validate request
        $validate = Validator::make($req->all(), [
            'farmer_no' => 'required',
            'program_year' => 'required',
            'pic_email' => 'required'
        ]);
        // validation fails
        if($validate->fails())return response()->json($validate->errors()->first(), 400);
        
        // normalize farmer_no
        $farmer_no = array_unique(explode(",", preg_replace('/(?<!^)F/', ',F', str_replace("\n", "", $req->farmer_no))));
        
        $dFarmers = DB::table('distributions')->whereIn('farmer_no', $farmer_no)->where('distribution_no', 'LIKE', "D-$req->program_year-%")->get();
        
        $dis_farmer_no = [];
        foreach ($dFarmers as $d) {
            array_push($dis_farmer_no, $d->farmer_no);
            // update load & distribute in table detail
            DB::table('distribution_details')->where('distribution_no', $d->distribution_no)->update([
                'is_loaded' => 1,
                'loaded_by' => $req->pic_email,
                'is_distributed' => 1,
                'distributed_by' => $req->pic_email,
            ]);
            // update load & distribute in table main
            DB::table('distributions')->where('distribution_no', $d->distribution_no)->update([
                'is_loaded' => 1,
                'loaded_by' => $req->pic_email,
                'is_distributed' => 1,
                'distributed_by' => $req->pic_email,
                'distribution_note' => DB::table('farmers')->where('farmer_no', $d->farmer_no)->first()->name ?? '-',
                'farmer_signature' => "Uploads/DB_ttd_$d->farmer_no.png",
                'distribution_photo' => "Uploads/DB_foto_$d->farmer_no.jpg"
            ]);
        }
        
        $rslt = [
                'request_farmer_total' => count($farmer_no),
                'distribution_data' => (object)[
                    'discovered_count' => count($dFarmers),
                    'not_found_count' => count(array_diff($farmer_no, $dis_farmer_no)),
                    'not_found_list' => array_diff($farmer_no, $dis_farmer_no),
                ]
            ];
        
        return response()->json($rslt, 200);
    }
    
    // Fix duplicated village
    public function DeleteDuplicatedVillage(Request $req) {
        // validate request
        $validate = Validator::make($req->all(), [
            'key' => 'required',
            'del_desa' => 'required|exists:desas,kode_desa',
            'replace_desa' => 'required|exists:desas,kode_desa'
        ]);
        // validation fails
        if($validate->fails())return response()->json($validate->errors()->first(), 400);
        if ($req->key === 'trees4trees_oye') {
            $del_desa = $req->del_desa;
            $replace_desa = $req->replace_desa;
            
            $check_data = [
                (object)[
                    'table' => 'farmers',
                    'village_key' => 'village'
                ],
                (object)[
                    'table' => 'farmer_trainings',
                    'village_key' => 'village'
                ],
                (object)[
                    'table' => 'field_facilitators',
                    'village_key' => 'working_area'
                ],
                (object)[
                    'table' => 'form_minats',
                    'village_key' => 'village'
                ],
                (object)[
                    'table' => 'lahans',
                    'village_key' => 'village'
                ],
                (object)[
                    'table' => 'lahan_umums',
                    'village_key' => 'village'
                ],
                (object)[
                    'table' => 'rras',
                    'village_key' => 'village'
                ],
                (object)[
                    'table' => 'scooping_visits',
                    'village_key' => 'village'
                ],
                (object)[
                    'table' => 'village_borders',
                    'village_key' => 'kode_desa'
                ],
            ];
            $rslt = [];
            foreach ($check_data as $cd) {
                // check if exists
                $check = DB::table($cd->table)->where($cd->village_key, $del_desa)->count();
                if ($check > 0) {
                    // update desa
                    DB::table($cd->table)->where($cd->village_key, $del_desa)->update([$cd->village_key => $replace_desa]);
                }
                $rslt[$cd->table] = $check;
            }
            $rslt['DESA'] = DB::table('desas')->where('kode_desa', $del_desa)->get();
            // delete desa
            DB::table('desas')->where('kode_desa', $del_desa)->delete();
            return response()->json($rslt, 200);
        } else {
            return response()->json(':)', 401);
        }
    }
    
    // Get3s4Nursery
    public function Get3s4Nursery() {
        $query = "SELECT tree_name as seed_name,
            1 as seed_type_id
            FROM `trees`
            WHERE `tree_category` = 'Pohon_Kayu'
            UNION ALL
            SELECT tree_name as seed_name,
            2 as seed_type_id
            FROM `trees`
            WHERE `tree_category` = 'Pohon_Buah'
            UNION ALL
            SELECT tree_name as seed_name,
            3 as seed_type_id
            FROM `trees`
            WHERE `tree_category` = 'Tanaman_Bawah_Empon'";
        $datas = DB::select($query);
        return response()->json($datas, 200);
    }
    
    // Utilities
    private static function convert_from_latin1_to_utf8_recursively($dat)
    {
       if (is_string($dat)) {
         return utf8_encode($dat);
       } elseif (is_array($dat)) {
         $ret = [];
         foreach ($dat as $i => $d) $ret[ $i ] = self::convert_from_latin1_to_utf8_recursively($d);

         return $ret;
       } elseif (is_object($dat)) {
         foreach ($dat as $i => $d) $dat->$i = self::convert_from_latin1_to_utf8_recursively($d);

         return $dat;
       } else {
          return $dat;
       }
    }
    
    // Utilities: get details distributions seedling
    private function getDistributionDetailTrees($type, $distributions, $start, $end) {
        $distributionsDetail = DistributionDetail::
            whereIn('distribution_no', $distributions)
            ->whereDate('updated_at', '>=', $start)
            ->whereDate('updated_at', '<=', $end);
        
        if ($type != 'is_printed') $distributionsDetail = $distributionsDetail->where($type, 1);
        
        $distributionsDetail = $distributionsDetail->get();
        
        $list = [];
        // $totalSeeds = 0;
        foreach($distributionsDetail as $label) {
            // $totalSeeds += (int)$label->tree_amount;
            $pushData = [
                'tree_category' => $label->tree_category,
                'tree_name' => $label->tree_name,
                'tree_amount' => (int)$label->tree_amount
            ];
            if (count($list) == 0) array_push($list, $pushData);
            else {
                $exist = 0;
                foreach ($list as $lisIndex => $lis) {
                    if ($lis['tree_name'] == $label->tree_name) {
                        $list[$lisIndex]['tree_amount'] += (int)$label->tree_amount;
                        $exist = 1;
                        break;
                    }
                }
                if ($exist == 0) array_push($list, $pushData);
                else $exist = 0;
            }
        }
        
        return $list ?? [];
    }
    
    // Utilities: Get MU from Nursery
    private function getNurseryAlocationReverse($nursery) {
        $nur = [
            'Arjasari' => ['022', '024', '025', '020', '029'],
            'Ciminyak' => ['023', '026', '027', '021'],
            'Kebumen' => ['019'],
            'Pati' => ['015', '016']
        ];
        
        return $nur[$nursery];
    }
    // Utilities: Get Nursery Location
    private function getNurseryAlocation($mu_no) {
        $ciminyak   = ['023', '026', '027', '021'];
        $arjasari   = ['022', '024', '025', '020', '029'];
        $kebumen    = ['019'];
        $pati       = ['015', '016'];
        
        $nursery = 'Tidak Ada';
        if (in_array($mu_no, $ciminyak)) {
            $nursery = 'Ciminyak';
        } elseif (in_array($mu_no, $arjasari)) {
            $nursery = 'Arjasari';
        } elseif (in_array($mu_no, $kebumen)) {
            $nursery = 'Kebumen';
        } elseif (in_array($mu_no, $pati)) {
            $nursery = 'Pati';
        }
        
        return $nursery;
    }
    
    private function checkUploadedFileProperties($extension, $fileSize) {
        $valid_extension = array("csv", "xlsx"); //Only want csv and excel files
        $maxFileSize = 2097152; // Uploaded file size limit is 2mb
        if (in_array(strtolower($extension), $valid_extension)) {
            if ($fileSize <= $maxFileSize) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}