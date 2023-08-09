<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use DB;

class KPIController extends Controller {
    // get KPI By FF
    public function ByFF(Request $req) {
        // validate request
        $validate = Validator::make($req->all(), [
            'ff_no' => 'required|exists:field_facilitators,ff_no',
            'program_year' => 'required'
        ]);
        
        // validation fails
        if($validate->fails()){
            $rslt =  $this->ResultReturn(400, $validate->errors()->first(), $validate->errors()->first());
            return response()->json($rslt, 400);
        } else $ff_no = $req->ff_no;
        
        // validation passed / success
        
        // set datas for response
        $datas = $this->getFFKPIPetaniLahan($ff_no, $req->program_year);
        
        $rslt =  $this->ResultReturn(200, 'success', $datas);
        return response()->json($rslt, 200);  
        
    }

    // get KPI By FC
    public function ByFC(Request $req) {
        // validate request
        $validate = Validator::make($req->all(), [
            'fc_no' => 'required|exists:employees,nik',
            'program_year' => 'required',
            'dates' => 'required',
            'activities' => 'required'
        ]);
        
        // validation fails
        if($validate->fails()){
            $rslt =  $this->ResultReturn(400, $validate->errors()->first(), $validate->errors()->first());
            return response()->json($rslt, 400);
        } else {
            $fc_no = $req->fc_no;
            $activities = explode(',', $req->activities);
            $req->dates = explode(',', $req->dates);
        }
        
        // get FF data
        $fieldFacilitators = DB::table('field_facilitators')
            ->where([
                'fc_no' => $fc_no,
                ['name', 'NOT LIKE', '%FF_%'],
                'active' => 1
            ])
            ->orderBy('name')->get();
        
        // get FC Data
        $fc_data = DB::table('employees')
            ->leftJoin('employee_structure', 'employee_structure.nik', '=', 'employees.nik')
            ->select(
                'employees.name',
                'employees.nik',
                'employee_structure.manager_code'
            )
            ->where('employees.nik', $fc_no)->first();
        
        // set data
        $datas = [
            'program_year' => $req->program_year,
            'um' => DB::table('employees')->where('nik', ($fc_data->manager_code ?? '999999999999'))->first()->name ?? '-',
            'fc' => $fc_data->name ?? '-',
            'petani_lahan' => [],
            'sostam' => [],
            'pelpet' => [],
            'penlub' => [],
            'pupuk' => [],
            'distribusi' => [],
            'monitoring1' => [],
        ];
        foreach($fieldFacilitators as $ff) {
            if (in_array('Pendataan Petani & Lahan', $activities)) {
                array_push($datas['petani_lahan'], $this->getFFKPIPetaniLahan($ff->ff_no, $req->program_year, $req->dates));
            }
            if (in_array('Sosialisasi Tanam', $activities)) {
                array_push($datas['sostam'], $this->getFFKPISosTam($ff->ff_no, $req->program_year, $req->dates));
            }
            if (in_array('Pelatihan Petani', $activities)) {
                array_push($datas['pelpet'], $this->getFFKPIPelPet($ff->ff_no, $req->program_year, $req->dates));
            }
            if (in_array('Penilikan Lubang', $activities)) {
                array_push($datas['penlub'], $this->getFFKPIPenLub($ff->ff_no, $req->program_year, $req->dates));
            }
            if (in_array('Material Organik (Pupuk)', $activities)) {
                array_push($datas['pupuk'], $this->getFFKPIPupuk($ff->ff_no, $req->program_year, $req->dates));
            }
            if (in_array('Distribusi', $activities)) {
                array_push($datas['distribusi'], $this->getFFKPIDistribution($ff->ff_no, $req->program_year, $req->dates));
            }
            if (in_array('Realisasi Tanam', $activities)) {
                array_push($datas['monitoring1'], $this->getFFKPIMonitoring1($ff->ff_no, $req->program_year, $req->dates));
            }
            
        }
        
        $rslt =  $this->ResultReturn(200, 'success', $datas);
        return response()->json($rslt, 200);  
    }
    public function ByFCDev(Request $req) {
        // validate request
        $validate = Validator::make($req->all(), [
            'fc_no' => 'required',
            'program_year' => 'required',
            'dates' => 'required',
            'activities' => 'required'
        ]);
        
        // validation fails
        if($validate->fails()){
            $rslt =  $this->ResultReturn(400, $validate->errors()->first(), $validate->errors()->first());
            return response()->json($rslt, 400);
        } else {
            $fc_no = $req->fc_no;
            $activities = explode(',', $req->activities);
            $req->dates = explode(',', $req->dates);
            $py = $req-> program_year;
        }
        
        // get FF data
        $fieldFacilitators = DB::table('field_facilitators')
        ->join('ff_working_areas', 'ff_working_areas.ff_no', '=' ,'field_facilitators.ff_no')
        ->join('main_pivots', 'main_pivots.key2', 'field_facilitators.ff_no')
        ->select(
            'field_facilitators.*', 
            'ff_working_areas.program_year', 
            'ff_working_areas.kode_desa'
            )
            ->where(['main_pivots.key1' => $fc_no,['field_facilitators.name', 'NOT LIKE', '%FF_%'],'field_facilitators.active' => 1])
            ->where(['ff_working_areas.program_year' => $py])
            ->where(['main_pivots.type' => 'fc_ff', ['main_pivots.program_year','like', "%$py%"]])
            ->groupBy('field_facilitators.ff_no')
            ->orderBy('field_facilitators.ff_no')->get();
        
        // get FC Data
        $fc_data = DB::table('employees')
            ->leftJoin('employee_structure', 'employee_structure.nik', '=', 'employees.nik')
            ->select(
                'employees.name',
                'employees.nik',
                'employee_structure.manager_code'
            )
            ->where('employees.nik', $fc_no)->first();
        
        // set data
        $datas = [
            'program_year' => $req->program_year,
            'um' => DB::table('employees')->where('nik', ($fc_data->manager_code ?? '999999999999'))->first()->name ?? '-',
            'fc' => $fc_data->name ?? '-',
            'petani_lahan' => [],
            'sostam' => [],
            'pelpet' => [],
            'penlub' => [],
            'pupuk' => [],
            'distribusi' => [],
            'monitoring1' => [],
        ];
        foreach($fieldFacilitators as $ff) {
            if (in_array('Pendataan Petani & Lahan', $activities)) {
                array_push($datas['petani_lahan'], $this->getFFKPIPetaniLahan($ff->ff_no, $req->program_year, $req->dates));
            }
            if (in_array('Sosialisasi Tanam', $activities)) {
                array_push($datas['sostam'], $this->getFFKPISosTam($ff->ff_no, $req->program_year, $req->dates));
            }
            if (in_array('Pelatihan Petani', $activities)) {
                array_push($datas['pelpet'], $this->getFFKPIPelPet($ff->ff_no, $req->program_year, $req->dates));
            }
            if (in_array('Penilikan Lubang', $activities)) {
                array_push($datas['penlub'], $this->getFFKPIPenLub($ff->ff_no, $req->program_year, $req->dates));
            }
            if (in_array('Material Organik (Pupuk)', $activities)) {
                array_push($datas['pupuk'], $this->getFFKPIPupuk($ff->ff_no, $req->program_year, $req->dates));
            }
            if (in_array('Distribusi', $activities)) {
                array_push($datas['distribusi'], $this->getFFKPIDistribution($ff->ff_no, $req->program_year, $req->dates));
            }
            if (in_array('Realisasi Tanam', $activities)) {
                array_push($datas['monitoring1'], $this->getFFKPIMonitoring1($ff->ff_no, $req->program_year, $req->dates));
            }
            
        }
        
        $rslt =  $this->ResultReturn(200, 'success', $datas);
        return response()->json($rslt, 200);  
    }
    
    // get KPI By Unit Manager    
    public function ByUM(Request $req) {
        // validate request
        $validate = Validator::make($req->all(), [
            'um_no' => 'required|exists:employees,nik',
            'program_year' => 'required'
        ]);
        
        // validation fails
        if($validate->fails()){
            $rslt =  $this->ResultReturn(400, $validate->errors()->first(), $validate->errors()->first());
            return response()->json($rslt, 400);
        } else $um_no = $req->um_no;
        
        $datas = [];
        
        // get fc
        $fieldCoordinators = DB::table('employee_structure')
            ->leftjoin('employees', 'employees.nik', '=', 'employee_structure.nik')
            ->where('employee_structure.manager_code', $um_no)
            ->orderBy('employees.name')
            ->get();
        
        foreach ($fieldCoordinators as $fcIndex => $fcData) {
            // get FF data
            $fieldFacilitators = DB::table('field_facilitators')->where('fc_no', $fcData->nik)->where('name', 'NOT LIKE', '%FF_%')->orderBy('name')->get();
            // get KPI data
            foreach($fieldFacilitators as $ffIndex => $ff) {
                $datas[$fcData->name][$ffIndex] = $this->getFFKPIPetaniLahan($ff->ff_no, $req->program_year);
            }
        }
        
        $rslt =  $this->ResultReturn(200, 'success', $datas);
        return response()->json($rslt, 200);
    }
    
    // Export Excel KPI 
    public function KPIExportExcel(Request $req) {
        // validate request
        $validate = Validator::make($req->all(), [
            'fc_no' => 'required|exists:employees,nik',
            'program_year' => 'required',
            'dates' => 'required',
            'activities' => 'required'
        ]);
        
        // validation fails
        if($validate->fails()){
            $rslt =  $this->ResultReturn(400, $validate->errors()->first(), $validate->errors()->first());
            return response()->json($rslt, 400);
        }  else {
            $fc_no = $req->fc_no;
            $activities = explode(',', $req->activities);
            $req->dates = explode(',', $req->dates);
        }
        // get FF data
        $fieldFacilitators = DB::table('field_facilitators')
            ->where([
                'fc_no' => $fc_no,
                ['name', 'NOT LIKE', '%FF_%']
            ])
            ->orderBy('name')->get();
        
        // get FC Data
        $fc_data = DB::table('employees')
            ->leftJoin('employee_structure', 'employee_structure.nik', '=', 'employees.nik')
            ->select(
                'employees.name',
                'employees.nik',
                'employee_structure.manager_code'
            )
            ->where('employees.nik', $fc_no)->first();
        
        // set data
        $datas = [
            'activities' => $activities,
            'dates' => $req->dates,
            'program_year' => (string)$req->program_year,
            'um' => DB::table('employees')->where('nik', ($fc_data->manager_code ?? '999999999999'))->first()->name ?? '-',
            'fc' => $fc_data->name ?? '-',
            'petani_lahan' => [],
            'sostam' => [],
            'pelpet' => [],
            'penlub' => [],
            'pupuk' => [],
            'distribusi' => [],
            'monitoring1' => [],
        ];
        foreach($fieldFacilitators as $ff) {
            if (in_array('Pendataan Petani & Lahan', $activities)) {
                array_push($datas['petani_lahan'], $this->getFFKPIPetaniLahan($ff->ff_no, $req->program_year, $req->dates));
            }
            if (in_array('Sosialisasi Tanam', $activities)) {
                array_push($datas['sostam'], $this->getFFKPISosTam($ff->ff_no, $req->program_year, $req->dates));
            }
            if (in_array('Pelatihan Petani', $activities)) {
                array_push($datas['pelpet'], $this->getFFKPIPelPet($ff->ff_no, $req->program_year, $req->dates));
            }
            if (in_array('Penilikan Lubang', $activities)) {
                array_push($datas['penlub'], $this->getFFKPIPenLub($ff->ff_no, $req->program_year, $req->dates));
            }
            if (in_array('Material Organik (Pupuk)', $activities)) {
                array_push($datas['pupuk'], $this->getFFKPIPupuk($ff->ff_no, $req->program_year, $req->dates));
            }
            if (in_array('Distribusi', $activities)) {
                array_push($datas['distribusi'], $this->getFFKPIDistribution($ff->ff_no, $req->program_year, $req->dates));
            }
            if (in_array('Realisasi Tanam', $activities)) {
                array_push($datas['monitoring1'], $this->getFFKPIMonitoring1($ff->ff_no, $req->program_year, $req->dates));
            }
            
        }
        return view('exportKPIByFC', ['datas' => $datas]);
            // return response()->json($datas, 200);
    }
    
    // utility: get FF KPI Petani Lahan
    private function getFFKPIPetaniLahan($ff_no, $program_year, $dates) {
        // get FF data
        $ff = DB::table('field_facilitators')->where('ff_no', $ff_no)->first();
        
        // get Petani
        $petani1 = $this->getPetaniByFF($ff->ff_no, $dates[0], $program_year);
        $petani2 = $this->getPetaniByFF($ff->ff_no, $dates[1], $program_year);
        $petani_progress = round((count($petani2) / 20) * 100);
        
        // set petani
        $petaniNow = [];
        foreach($petani2 as $farmer) {
            array_push($petaniNow, $farmer->farmer_no);
        }
        
        // get lahan
        $lahan1 = $this->getLahanByPetani($petaniNow, $dates[2], $program_year);
        $lahan2 = $this->getLahanByPetani($petaniNow, $dates[3], $program_year);
        $lahan_progress = round(($lahan2 / 20) * 100);
        
        // set datas for response
        $datas = [
            'ff' => $ff->name ?? '-',
            'petani' => [
                'petani1' => count($petani1),
                'petani2' => count($petani2) - count($petani1),
                'total_petani' => count($petani2),
                'progress_petani' => $petani_progress > 100 ? 100 : $petani_progress
            ],
            'lahan' => [
                'lahan1' => $lahan1,
                'lahan2' => $lahan2 - $lahan1,
                'total_lahan' => $lahan2,
                'progress_lahan' => $lahan_progress > 100 ? 100 : $lahan_progress
            ]
        ];
        
        return $datas;
    }
    
    // utility: get FF KPI Sosialisasi Tanam
    private function getFFKPISosTam($ff_no, $program_year, $dates) {
        // {
        //     ff: 'Asep',
        //     total_lahan: 35,
        //     total_sostam: 20,
        //     progress_sostam: '57%',
        //     total_bibit: '1900 Bibit'
        //   }
        
        // get FF data
        $ff = DB::table('field_facilitators')->where('ff_no', $ff_no)->first();
        
        // get Petani Data
        $farmers = $this->getPetaniByFF($ff->ff_no, $dates[1], $program_year);
        $petaniNow = [];
        foreach($farmers as $farmer) {
            array_push($petaniNow, $farmer->farmer_no);
        }
        
        
        // get Lahan Data
        $lahans = DB::table('lahans')
            ->leftJoin('field_facilitators', 'field_facilitators.ff_no', '=', 'lahans.user_id')
            ->where('lahans.user_id', $ff->ff_no)
            ->where('lahans.approve', 1)
            ->where('lahans.is_dell', 0)
            ->whereIn('lahans.farmer_no', $petaniNow)
            ->whereDate('lahans.created_at', '<=', $dates[3])
            ->whereYear('lahans.created_time', $program_year);
            
        // get Sostam Data
        $sostams = DB::table('planting_socializations')
            ->leftJoin('field_facilitators', 'field_facilitators.ff_no', '=', 'planting_socializations.ff_no')
            ->where([
                    'planting_socializations.planting_year' => $program_year,
                    'planting_socializations.validation' => 1,
                    'planting_socializations.is_dell' => 0
                ])
            ->whereDate('planting_socializations.created_at', '<=', $dates[4])
            ->whereIn('planting_socializations.no_lahan', $lahans->pluck('lahan_no'));
            
        $progressSostam = round($sostams->count() / ($lahans->count() == 0 ? 1 : $lahans->count()) * 100);
        
            
        
        // get Total bibit
        $totalBibit = 0;
        $totalBibitDetails = [];
        
        foreach($sostams->get() as $sostamData) {
            $trees = DB::table('planting_details')
                ->where('form_no', $sostamData->form_no);
            
            $treesDatas = $trees
                ->leftJoin('tree_locations', 'tree_locations.tree_code', '=', 'planting_details.tree_code')
                ->select('tree_locations.tree_code', 'tree_locations.tree_name', 'tree_locations.category', 'planting_details.amount')
                ->where([
                        'tree_locations.mu_no' => $ff->mu_no
                    ])
                ->get();
            foreach($treesDatas as $treeData) {
                $exists = 0;
                foreach($totalBibitDetails as $totalBibitDetail) {
                    if ($totalBibitDetail->tree_code == $treeData->tree_code) {
                       $totalBibitDetail->amount += $treeData->amount;
                       $exists += 1; 
                    }
                }
                if ($exists == 0) {
                    array_push($totalBibitDetails, $treeData);
                }
            }
            
            $totalBibit += $trees->sum('amount');
            
        }
        
        // get distribution time
        $firstSostam = $sostams->first();
        if (isset($firstSostam)) {
            $sostamPeriod = DB::table('planting_period')->where('form_no', $firstSostam->form_no)->first();
        }
        
        // set datas for response
        $datas = [
            'ff' => $ff->name ?? '-',
            'total_petani' => count($petaniNow),
            'total_lahan' => $lahans->count(),
            'total_sostam' => $sostams->count(),
            'distribution_time' => $sostamPeriod->distribution_time ?? '-',
            'progress_sostam' => $progressSostam,
            'total_bibit' => $totalBibit,
            'total_bibit_details' => $totalBibitDetails,
        ];
        
        return $datas;
    }
    
    // utility: get FF KPI Pelatihan Petani
    private function getFFKPIPelPet($ff_no, $program_year, $dates) {
        // get FF data
        $ff = DB::table('field_facilitators')->where('ff_no', $ff_no)->first();
        
        // get Petani Data
        $farmers = $this->getPetaniByFF($ff->ff_no, $dates[1], $program_year);
        $petaniNow = [];
        foreach($farmers as $farmer) {
            array_push($petaniNow, $farmer->farmer_no);
        }
        
        // get pelatihan petani
        $pelpet = DB::table('farmer_trainings')->where([
            'ff_no' => $ff_no,
            'program_year' => $program_year
        ])->first();
        
        if ($pelpet) {
            // get participant
            $participant = DB::table('farmer_training_details')->where('training_no', $pelpet->training_no)->get();
            $materi1 = DB::table('training_materials')->where('material_no', $pelpet->first_material)->first()->material_name;
            $materi2 = DB::table('training_materials')->where('material_no', $pelpet->second_material)->first()->material_name;
            $trainee = DB::table('users')->where('email', $pelpet->user_id)->first()->name;
            $mu = DB::table('managementunits')->where('mu_no', $pelpet->mu_no)->first()->name;
            $training_date = date("d F Y", strtotime($pelpet->training_date));
        } else $participant = [];
        
        // set datas for response
        $datas = [
            'mu_name' => $mu ?? '-',
            'ff' => $ff->name ?? '-',
            'total_farmer' => count($petaniNow),
            'total_participant' => count($participant),
            'trainee' => $trainee ?? '-',
            'training_date' => $training_date ?? '-',
            'materi1' => $materi1 ?? '-',
            'materi2' => $materi2 ?? '-',
        ];
        
        return $datas;
    }
    
    // utility: get FF KPI Penilikan Lubang
    private function getFFKPIPenLub($ff_no, $program_year, $dates) {
        
        // get FF data 
        $ff = DB::table('field_facilitators')->where('ff_no', $ff_no)->first();
        
        // get Petani Data
        $farmers = $this->getPetaniByFF($ff->ff_no, $dates[1], $program_year);
        $petaniNow = [];
        foreach($farmers as $farmer) {
            array_push($petaniNow, $farmer->farmer_no);
        }
            
        // get Lahan Data
        $lahans = DB::table('lahans')
            ->leftJoin('field_facilitators', 'field_facilitators.ff_no', '=', 'lahans.user_id')
            ->where('lahans.user_id', $ff->ff_no)
            ->where('lahans.approve', 1)
            ->where('lahans.is_dell', 0)
            ->whereIn('lahans.farmer_no', $petaniNow)
            ->whereDate('lahans.created_at', '<=', $dates[3])
            ->whereYear('lahans.created_time', $program_year);
        
        // get Sostam Data
        $sostams = DB::table('planting_socializations')
            ->leftJoin('field_facilitators', 'field_facilitators.ff_no', '=', 'planting_socializations.ff_no')
            ->where([
                    'planting_socializations.planting_year' => $program_year,
                    'planting_socializations.validation' => 1,
                    'planting_socializations.is_dell' => 0
                ])
            ->whereDate('planting_socializations.created_at', '<=', $dates[4])
            ->whereIn('planting_socializations.no_lahan', $lahans->pluck('lahan_no'));
            
        // get Penilikan Lubang Data
        $penlubs = DB::table('planting_hole_surviellance')
            ->leftJoin('field_facilitators', 'field_facilitators.ff_no', '=', 'planting_hole_surviellance.user_id')
            ->where([
                    'planting_hole_surviellance.planting_year' => $program_year,
                    'planting_hole_surviellance.is_validate' => 1,
                    'planting_hole_surviellance.is_dell' => 0
                ])
            ->whereDate('planting_hole_surviellance.created_at', '<=', $dates[5])
            ->whereIn('planting_hole_surviellance.lahan_no', $sostams->pluck('no_lahan'));
            
        $progressPenlub = round($penlubs->count() / ($sostams->count() == 0 ? 1 : $sostams->count()) * 100);
       
            
        
        // get Total bibit
        $totalBibit = 0;
        $totalBibitDetails = [];
        
        foreach($penlubs->get() as $penlubData) {
            $trees = DB::table('planting_hole_details')
                ->where('ph_form_no', $penlubData->ph_form_no);
            
            $treesDatas = $trees
                ->leftJoin('tree_locations', 'tree_locations.tree_code', '=', 'planting_hole_details.tree_code')
                ->select('tree_locations.tree_code', 'tree_locations.tree_name', 'tree_locations.category', 'planting_hole_details.amount')
                ->where([
                        'tree_locations.mu_no' => $ff->mu_no
                    ])
                ->get();
            foreach($treesDatas as $treeData) {
                $exists = 0;
                foreach($totalBibitDetails as $totalBibitDetail) {
                    if ($totalBibitDetail->tree_code == $treeData->tree_code) {
                       $totalBibitDetail->amount += $treeData->amount;
                       $exists += 1; 
                    }
                }
                if ($exists == 0) {
                    array_push($totalBibitDetails, $treeData);
                }
            }
            
            $totalBibit += $trees->sum('amount');
            
        }
        
        // set datas for response
        $datas = [
            'ff' => $ff->name ?? '-',
            'total_petani' => count($petaniNow),
            'total_lahan' => $lahans->count(),
            'total_sostam' => $sostams->count(),
            'total_penlub' => $penlubs->count(),
            'progress_penlub' => $progressPenlub,
            'total_bibit' => $totalBibit,
            'total_bibit_details' => $totalBibitDetails,
            'total_lubang' => $penlubs->sum('total_holes'),
            'total_lubang_standar' => $penlubs->sum('counter_hole_standard'),
        ];
        
        return $datas;
    }
    
    // utility: get FF KPI Pupuk
    private function getFFKPIPupuk($ff_no, $program_year, $dates) {
        
        // get FF data 
        $ff = DB::table('field_facilitators')->where('ff_no', $ff_no)->first();
        
        // get Petani Data
        $farmers = $this->getPetaniByFF($ff->ff_no, $dates[1], $program_year);
        $petaniNow = [];
        foreach($farmers as $farmer) {
            array_push($petaniNow, $farmer->farmer_no);
        }
        
        // get Lahan Data
        $lahans = DB::table('lahans')
            ->leftJoin('field_facilitators', 'field_facilitators.ff_no', '=', 'lahans.user_id')
            ->where('lahans.user_id', $ff->ff_no)
            ->where('lahans.approve', 1)
            ->where('lahans.is_dell', 0)
            ->whereIn('lahans.farmer_no', $petaniNow)
            ->whereDate('lahans.created_at', '<=', $dates[3])
            ->whereYear('lahans.created_at', $program_year);
            
        // get Penilikan Lubang Data
        $penlubs = DB::table('planting_hole_surviellance')
            ->leftJoin('field_facilitators', 'field_facilitators.ff_no', '=', 'planting_hole_surviellance.user_id')
            ->where([
                    'planting_hole_surviellance.planting_year' => $program_year,
                    'planting_hole_surviellance.is_validate' => 1,
                    'planting_hole_surviellance.is_dell' => 0
                ])
            ->whereDate('planting_hole_surviellance.created_at', '<=', $dates[5])
            ->whereIn('planting_hole_surviellance.lahan_no', $lahans->pluck('lahan_no'));
            
        if ($lahans->count() >= 20) {
            $progressPenlub = round($penlubs->count() / ($lahans->count() == 0 ? 1 : $lahans->count()) * 100);
        } else {
            $progressPenlub = round($penlubs->count() / 20 * 100);
        }
        
        // get Material Organik PUPUK
        $pupuks = DB::table('organics')->where([
            'created_by' => $ff->ff_no,
            'status' => 1
        ]);
        if (count($petaniNow) >= 20) {
            $progressPupuk = round($pupuks->count() / count($petaniNow) * 100);
        } else {
            $progressPupuk = round($pupuks->count() / 20 * 100);
        }
        
        // set datas for response
        $datas = [
            'ff' => $ff->name ?? '-',
            'total_petani' => count($petaniNow),
            'total_lahan' => $lahans->count(),
            'total_penlub' => $penlubs->count(),
            'progress_penlub' => $progressPenlub,
            'total_pupuks' => $pupuks->count(),
            'total_amount_pupuks' => $pupuks->sum('organic_amount'),
            'progress_pupuk' => $progressPupuk,
            'total_lubang' => $penlubs->sum('total_holes'),
            'total_lubang_standar' => $penlubs->sum('counter_hole_standard'),
        ];
        
        return $datas;
    }
    
    // utility: get FF KPI Distribusi
    private function getFFKPIDistribution($ff_no, $program_year, $dates) {
        
        // get FF data 
        $ff = DB::table('field_facilitators')->where('ff_no', $ff_no)->first();
        
        // get Petani Data
        $farmers = $this->getPetaniByFF($ff->ff_no, $dates[1], $program_year);
        $petaniNow = [];
        foreach($farmers as $farmer) {
            array_push($petaniNow, $farmer->farmer_no);
        }
        
        // get Lahan Data
        $lahans = DB::table('lahans')
            ->leftJoin('field_facilitators', 'field_facilitators.ff_no', '=', 'lahans.user_id')
            ->where('lahans.user_id', $ff->ff_no)
            ->where('lahans.approve', 1)
            ->where('lahans.is_dell', 0)
            ->whereIn('lahans.farmer_no', $petaniNow)
            ->whereDate('lahans.created_time', '<=', $dates[3])
            ->whereYear('lahans.created_time', $program_year);
            
        // get Penilikan Lubang Data
        $penlubs = DB::table('planting_hole_surviellance')
            ->leftJoin('field_facilitators', 'field_facilitators.ff_no', '=', 'planting_hole_surviellance.user_id')
            ->where([
                    'planting_hole_surviellance.planting_year' => $program_year,
                    'planting_hole_surviellance.is_validate' => 1,
                    'planting_hole_surviellance.is_dell' => 0
                ])
            ->whereDate('planting_hole_surviellance.created_at', '<=', $dates[5])
            ->whereIn('planting_hole_surviellance.lahan_no', $lahans->pluck('lahan_no'));
            
        if ($lahans->count() >= 20) {
            $progressPenlub = round($penlubs->count() / ($lahans->count() == 0 ? 1 : $lahans->count()) * 100);
        } else {
            $progressPenlub = round($penlubs->count() / 20 * 100);
        }
            
        
        // get Total bibit
        $totalBibit = 0;
        $totalBibitDetails = [];
        
        foreach($penlubs->get() as $penlubData) {
            $trees = DB::table('planting_hole_details')
                ->where('ph_form_no', $penlubData->ph_form_no);
            
            $treesDatas = $trees
                ->leftJoin('tree_locations', 'tree_locations.tree_code', '=', 'planting_hole_details.tree_code')
                ->select('tree_locations.tree_code', 'tree_locations.tree_name', 'tree_locations.category', 'planting_hole_details.amount')
                ->where([
                        'tree_locations.mu_no' => $ff->mu_no
                    ])
                ->get();
            foreach($treesDatas as $treeData) {
                $exists = 0;
                foreach($totalBibitDetails as $totalBibitDetail) {
                    if ($totalBibitDetail->tree_code == $treeData->tree_code) {
                       $totalBibitDetail->amount += $treeData->amount;
                       $exists += 1; 
                    }
                }
                if ($exists == 0) {
                    array_push($totalBibitDetails, $treeData);
                }
            }
            
            $totalBibit += $trees->sum('amount');
            
        }
        
        // Get Distribution Data
        $distributions = DB::table('distributions')->where([
            'ff_no' => $ff->ff_no,
            ['distribution_no', 'LIKE', 'D-' . $program_year . '%'],
        ])
        ->whereDate('distribution_date', '<=',$dates[6])->get();
        
        // Get SUM Seed Distribution
        $totalBibitDistribusi = [
            'all' => 0,
            'loaded' => 0,
            'distributed' => 0,
            'broken' => 0,
            'missing' => 0,
            'received' => 0,
        ];
        foreach ($distributions as $distribution) {
            $totalBibitDistribusi['all'] += DB::table('distribution_details')->where(['distribution_no' => $distribution->distribution_no])->sum('tree_amount');
            $totalBibitDistribusi['loaded'] += DB::table('distribution_details')->where(['distribution_no' => $distribution->distribution_no, 'is_loaded' => 1])->sum('tree_amount');
            $totalBibitDistribusi['distributed'] += DB::table('distribution_adjustments')->where(['distribution_no' => $distribution->distribution_no])->sum('total_distributed');
            $totalBibitDistribusi['broken'] += DB::table('distribution_adjustments')->where(['distribution_no' => $distribution->distribution_no])->sum('broken_seeds');
            $totalBibitDistribusi['missing'] += DB::table('distribution_adjustments')->where(['distribution_no' => $distribution->distribution_no])->sum('missing_seeds');
            $totalBibitDistribusi['received'] += DB::table('distribution_adjustments')->where(['distribution_no' => $distribution->distribution_no])->sum('total_tree_received');
        }
        
        // set datas for response
        $datas = [
            'ff' => $ff->name ?? '-',
            'total_petani' => count($petaniNow),
            'total_lahan' => $lahans->count(),
            'total_penlub' => $penlubs->count(),
            'progress_penlub' => $progressPenlub,
            'penlub_total_bibit' => $totalBibit,
            'penlub_total_bibit_details' => $totalBibitDetails,
            'total_distribusi' => count($distributions),
            'total_bibit_distribusi_all' => $totalBibitDistribusi['all'],
            'total_bibit_distribusi_loaded' => $totalBibitDistribusi['loaded'],
            'total_bibit_distribusi_distributed' => $totalBibitDistribusi['distributed'],
            'total_bibit_distribusi_broken' => $totalBibitDistribusi['broken'],
            'total_bibit_distribusi_missing' => $totalBibitDistribusi['missing'],
            'total_bibit_distribusi_received' => $totalBibitDistribusi['received'],
        ];
        
        return $datas;
    }
    
    // utility: get FF KPI Monitoring 1
    private function getFFKPIMonitoring1($ff_no, $program_year, $dates) {
        
        // get FF data 
        $ff = DB::table('field_facilitators')->where('ff_no', $ff_no)->first();
        
        // get Petani Data
        $farmers = $this->getPetaniByFF($ff->ff_no, $dates[1], $program_year);
        $petaniNow = [];
        foreach($farmers as $farmer) {
            array_push($petaniNow, $farmer->farmer_no);
        }
        
        // Get Distribution Data
        $distributions = DB::table('distributions')->where([
            'ff_no' => $ff->ff_no,
            ['distribution_no', 'LIKE', 'D-' . $program_year . '%'],
        ])
        ->whereDate('distribution_date', '<=',$dates[6])->get();
        
        $monitorings = DB::table('monitorings')->where([
            'user_id' => $ff->ff_no,
            ['monitoring_no', 'LIKE', 'MO1-' . $program_year . '%'],
        ])->count();
        
        // Get SUM Seed Distribution
        $totalBibitMonitoring = [
            'received' => 0,
            'planted_live' => 0,
            'dead' => 0,
            'lost' => 0,
        ];
        foreach ($distributions as $distribution) {
            $totalBibitMonitoring['received'] += DB::table('distribution_adjustments')->where(['distribution_no' => $distribution->distribution_no])->sum('total_tree_received');
            $totalBibitMonitoring['planted_live'] += DB::table('monitoring_details')->where([['monitoring_no', 'MO1-'.$program_year.'-'.$distribution->farmer_no], 'status' => 'sudah_ditanam', 'condition' => 'hidup'])->sum('qty');
            $totalBibitMonitoring['dead'] += DB::table('monitoring_details')->where([['monitoring_no', 'MO1-'.$program_year.'-'.$distribution->farmer_no], 'condition' => 'mati'])->sum('qty');
            $totalBibitMonitoring['lost'] += DB::table('monitoring_details')->where([['monitoring_no', 'MO1-'.$program_year.'-'.$distribution->farmer_no], 'condition' => 'hilang'])->sum('qty');
        }
        
        // set datas for response
        $datas = [
            'ff' => $ff->name ?? '-',
            'total_petani' => count($petaniNow),
            'total_distribusi' => count($distributions),
            'total_monitoring' => $monitorings,
            'progress_monitoring' => round($monitorings / (count($distributions) == 0 ? 1 : count($distributions)) * 100),
            'total_seed_received' => $totalBibitMonitoring['received'],
            'total_seed_planted_live' => $totalBibitMonitoring['planted_live'],
            'total_seed_dead' => $totalBibitMonitoring['dead'],
            'total_seed_lost' => $totalBibitMonitoring['lost'],
        ];
        
        return $datas;
    }
    
    // utility: get Petani By FF Petani Lahan
    private function getPetaniByFF($ff_no, $date, $program_year) {
        $datas = DB::table('farmers')
            ->where('user_id', $ff_no)
            ->whereDate('created_at', '<=', $date)
            ->where('approve', 1)
            ->where('is_dell', 0)
            ->get();
        $farmers = [];
        foreach ($datas as $index => $data) {
            if ($this->getFarmerProgramYear($data->mou_no) == $program_year) {
                array_push($farmers, $data);
            }
        }
        
        return $farmers;
    }
    
    // utility: get Lahan By Petani
    private function getLahanByPetani($farmers, $date, $program_year) {
        $lahans = DB::table('lahans')
            ->whereIn('farmer_no', $farmers)
            ->whereDate('created_at', '<=', $date)
            ->whereYear('created_time', $program_year)
            ->where('approve', 1)
            ->where('is_dell', 0)
            ->count();
        return $lahans;
    }
    
    // utility: get Program Year
    private function getFarmerProgramYear($mou_no) {
        if (substr($mou_no, 13, 1) === '_') {
            return substr($mou_no, 9, 4);
        } else {
            return substr($mou_no, 4, 4);
        }
    }
}