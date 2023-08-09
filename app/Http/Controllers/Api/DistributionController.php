<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;

use App\Employee;
use App\FieldFacilitator;
use App\LahanUmum;
use App\LahanUmumDetail;
use App\LahanUmumHoleDetail;
use App\PlantingHoleSurviellance;
use App\PlantingHoleSurviellanceDetail;
use App\PlantingSocializations;
use App\PlantingSocializationsPeriod;
use App\PlantingSocializationsDetails as PlantingSocializationsDetail;
use App\Distribution;
use App\DistributionDetail;
use App\DistributionAdjustment;

class DistributionController extends Controller 
{
    public function GetDistributionFF(Request $request)
    {
        // validate request
        $validate = Validator::make($request->all(), [
            'ff_no' => 'required|exists:field_facilitators,ff_no',
            'program_year' => 'required',
        ]);
        
        // validation fails
        if ($validate->fails()) {
            $rslt =  $this->ResultReturn(400, $validate->errors()->first(), $validate->errors()->first());
            return response()->json($rslt, 400);
        } else {
            $ff_no = $request->ff_no;
            $py = $request->program_year;
        }
        try{
            $GetD = Distribution::where([
                'ff_no' => $ff_no,
                ['distribution_no', 'LIKE', 'D-'.$py.'%']
            ]);
            
            if($GetD->count() != 0){
                $distributions = $GetD->get();
                $disDetails = [];
                foreach($distributions as $dIndex => $distribution) {
                    $detailData = DistributionDetail::
                    select('distribution_no', 'bag_number', 'is_distributed', 'tree_name', 'tree_amount')
                    ->where([
                        'distribution_no' => $distribution->distribution_no,
                        'is_loaded' => 1
                    ])->groupBy('bag_number')
                    ->orderBy('id', 'ASC')
                    ->get();
                    array_push($disDetails, ...$detailData);
                    
                    if($distribution->farmer_signature == '') {
                        $distributions[$dIndex]->farmer_signature = '-';
                    }
                    if($distribution->distribution_photo == '') {
                        $distributions[$dIndex]->distribution_photo = '-';
                    }
                    if($distribution->distribution_note == '') {
                        $distributions[$dIndex]->distribution_note = '-';
                    }
                    
                    if($distribution->status > 1){
                        $distribution->status = 1;
                    }
                    
                    $distributions[$dIndex]->status = (string)$distribution->status;
                }
                
                $distAdjust = [];
                foreach($distributions as $aIndex => $distribution) {
                    $detailAdj = DistributionAdjustment::select('distribution_no', 'tree_code', DB::raw('SUM(total_tree_received ) as total_tree_received'))
                    ->where([
                        'distribution_no' => $distribution->distribution_no
                    ])->groupBy('tree_code')->get();
                    array_push($distAdjust, ...$detailAdj);
                }
                
                $data = [ 
                    'data'=> $distributions,
                    'data_details' => $disDetails,
                    'data_adjustment' =>  $distAdjust
                ];
                $rslt =  $this->ResultReturn(200, 'success', $data);
                return response()->json($rslt, 200); 
            }else{
                $rslt =  $this->ResultReturn(404, 'doesnt match data', 'doesnt match data');
                return response()->json($rslt, 404);
            }
        }catch(\Exception $ex){
            return response()->json($ex);
        }
    }
    
    public function UpdateDistribution(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'distribution_no' => 'required',
            'bags' => 'required|array'
        ]);
        
        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        } else {
            $dist_no = $request->distribution_no;
            $listLabels = $request->bags;
        }
        
        $getDist = Distribution::where('distribution_no', $dist_no);
        
        // update labels is_distributed
        $getDistDetail = DistributionDetail::whereIn('bag_number', $listLabels)->update([
            'is_distributed' => 1,
            'distributed_by' => Auth::user()->email
        ]);
        
        // upload farmer signature + update data
        if ($request->farmer_signature != '' && $request->farmer_signature != '-') {
            // $farmer_signature = $this->UploadPhotoExternal($request->farmer_signature, ('farmer-signatures/,'.$dist_no));
            Distribution::where('distribution_no', $dist_no)->update([
                'farmer_signature' => $request->farmer_signature
            ]);
        } else {
            Distribution::where('distribution_no', $dist_no)->update([
                'farmer_signature' => '-'
            ]);
        }
        // upload documentation photo + update data
        if ($request->documentation_photo != '' && $request->documentation_photo != '-') {
            // $fotoDokumentasi = $this->UploadPhotoExternal($request->documentation_photo, ('documentation-photos/,'.$dist_no));
            Distribution::where('distribution_no', $dist_no)->update([
                'distribution_photo' => $request->documentation_photo
            ]);
        } else {
            Distribution::where('distribution_no', $dist_no)->update([
                'distribution_photo' => '-'
            ]);
        }
        
        if ($request->distribution_note != '' && $request->distribution_note != '-') { 
            Distribution::where('distribution_no', $dist_no)->update([
                'distribution_note' => $request->distribution_note
            ]);
        } else {
            Distribution::where('distribution_no', $dist_no)->update([
                'distribution_note' => '-'
            ]);
        }
        
        // update main distribution distributed by
        Distribution::where('distribution_no', $dist_no)->update([
            'is_distributed' => 1,
            'distributed_by' => Auth::user()->email
        ]);
        
        $rslt =  $this->ResultReturn(200, 'success', 'success');
        return response()->json($rslt, 200);
    }
    
    public function CompletedDistribution(Request $request)
    {
        $dist_no = $request->distribution_no;
        $ff_no = $request->ff_no;
        
        try{
            $validator = Validator::make($request->all(), [
                'distribution_no' => 'required' 
            ]);
            
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
            
            $getDist = DB::table('distributions')->where('distribution_no', $dist_no)->first();
            
            $getDistDetail = DB::table('distribution_details')
                    ->select('distribution_details.distribution_no',
                             'distribution_details.bag_no',
                             'distribution_details.is_distributed')
                    ->leftjoin('distributions', 'distribution_details.distribution_no', '=', 'distributions.distribution_no')
                    ->where('distributions.distribution_no', $dist_no)
                    ->where('distributions.ff_no','=',$ff_no);
                    
            $getDistDetailCount = DB::table('distribution_details')
                    ->leftjoin('distributions', 'distribution_details.distribution_no', '=', 'distributions.distribution_no')
                    ->where('distributions.distribution_no', $dist_no)
                    ->where('distributions.ff_no','=',$ff_no)
                    ->count();
            
            if($getDistDetailCount == $getDist->total_bags){
                Distribution::where('distribution_no', '=', $getDist)
                ->update([
                    'status' => 1,
                    'updated_at' => Carbon::now()
                ]);
                
                DistributionDetail::where('distribution_no', '=', $getDistDetail)
                ->update([
                    'is_distributed' => 1,
                    'updated_at' => Carbon::now()
                ]);
                
            }else{
                Distribution::where('distribution_no', '=', $getDist)
                ->update([
                    'status' => 0,
                    'updated_at' => Carbon::now()
                ]);
            }
            
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200);
                
        }catch(\Exception $ex){
            return response()->json($ex);
        }
    }
    
    // get distribution calendar
    public function DistributionCalendar(Request $req) {
        // validate request
        $validate = Validator::make($req->all(), [
            'month' => 'required',
            'year' => 'required',
            'program_year' => 'required'
        ]);
        
        // validation fails
        if($validate->fails()){
            $rslt =  $this->ResultReturn(400, $validate->errors()->first(), $validate->errors()->first());
            return response()->json($rslt, 400);
        } else {
            $py = $req->program_year;
        }
        $periodFilter = PlantingSocializationsPeriod::
            whereMonth('distribution_time', $req->month)
            ->whereYear('distribution_time', $req->year)
            ->pluck('form_no');
        $ps = PlantingSocializations::
            leftJoin('field_facilitators', 'planting_socializations.ff_no', '=', 'field_facilitators.ff_no')
            ->leftJoin('managementunits', 'managementunits.mu_no', '=', 'field_facilitators.mu_no')
            ->select(
                'planting_socializations.planting_year',
                'planting_socializations.form_no',
                'managementunits.mu_no',
                'managementunits.name as mu_name',
                'field_facilitators.ff_no as ff_no',
                'field_facilitators.name as ff_name',
                DB::raw('COUNT(planting_socializations.id) as total_sostam')
            )
            ->where([
                'planting_socializations.planting_year' => $py,
                // 'planting_socializations.validation' => 1, 
                'planting_socializations.is_dell' => 0,
                ['field_facilitators.name', 'NOT LIKE', '%FF_%'],
                ['planting_socializations.no_lahan', 'LIKE', '10_000%'],
            ])
            ->whereIn('planting_socializations.form_no', $periodFilter)
            ->groupBy('field_facilitators.ff_no')
            ->orderBy('planting_socializations.created_at', 'ASC');
        
        if ($req->nursery) {
            if ($req->nursery != 'All') {
                $listMU = $this->getNurseryAlocationReverse($req->nursery);
                $ps = $ps->whereIn('field_facilitators.mu_no', $listMU);
            }
        }

        $psGet = $ps->get();
        $psNew = [];
        foreach ($psGet as $sosIndex => $sostam) {
            // get sostam period
            $period = PlantingSocializationsPeriod::where('form_no', $sostam->form_no)
                ->whereMonth('distribution_time', $req->month)
                ->whereYear('distribution_time', $req->year)
                ->first();
            if ($period) {
                $sostam->distribution_time = $period->distribution_time ?? '-';
                $sostam->distribution_location = $period->distribution_location ?? '-';
            
                // get sostam trees amount
                $ffSostam = PlantingSocializations::where(['ff_no' => $sostam->ff_no, 'planting_year' => $req->program_year])->pluck('form_no');
                $trees = PlantingSocializationsDetail::whereIn('form_no', $ffSostam)->sum('amount');
                $sostam->total_bibit_sostam = $trees;
            
                // get penilikan lubang
                $penlub = PlantingHoleSurviellance::where(
                    [
                        'user_id' => $sostam->ff_no, 
                        'planting_year' => $req->program_year,
                        'is_dell' => 0,
                        'is_validate' => 1,
                    ]);
                $penlubProgress = round($penlub->count() / $sostam->total_sostam * 100);
                $sostam->progress_penlub = $penlubProgress;
                
                // set total seeds penilikan lubang
                $penlub_kayu = $penlub->sum('pohon_kayu');
                $penlub_mpts = $penlub->sum('pohon_mpts');
                $penlub_crops = $penlub->sum('tanaman_bawah');
                $sostam->total_bibit_penlub = $penlub_kayu + $penlub_mpts + $penlub_crops;
                
                // push new sostam data 
                array_push($psNew, $sostam);
            }
        }
        
        $events = [];
        foreach($psNew as $psIndex => $psVal) {
            $nursery = $this->getNurseryAlocation($psVal->mu_no);
            $pushData = [
                'nursery' => $nursery,
                'date' => $psVal->distribution_time,
                'total' => 1,
                'total_bibit_sostam' => $psVal->total_bibit_sostam,
                'total_bibit_penlub' => (int) $psVal->total_bibit_penlub,
                'details' => [$psVal]
            ];
            if (count($events) == 0) {
                array_push($events, $pushData);
            } else {
                $added = 0;
                foreach($events as $evIndex => $evVal) {
                    if ($evVal['date'] == $psVal->distribution_time && $evVal['nursery'] == $nursery) {
                        array_push($events[$evIndex]['details'], $psVal);
                        $events[$evIndex]['total_bibit_sostam'] += $psVal->total_bibit_sostam;
                        $events[$evIndex]['total_bibit_penlub'] += (int) $psVal->total_bibit_penlub;
                        $events[$evIndex]['total'] += 1;
                        $added += 1;
                    }
                }
                if ($added == 0) {
                    array_push($events, $pushData);
                }
            }
        }
        
        $datas = [
            'count' => count($events),
            'datas' => $events
        ];
        $rslt =  $this->ResultReturn(200, 'success', $datas);
        return response()->json($rslt, 200);  
    }
    
    // get distribution calendar: lahan umum
    public function DistributionCalendarLahanUmum(Request $req) {
        // validate request
        $validate = Validator::make($req->all(), [
            'month' => 'required',
            'year' => 'required',
            'program_year' => 'required'
        ]);
        
        // validation fails
        if($validate->fails()){
            $rslt =  $this->ResultReturn(400, $validate->errors()->first(), $validate->errors()->first());
            return response()->json($rslt, 400);
        } else {
            $py = $req->program_year;
            $month = $req->month;
            $year = $req->year;
        }
        $lahanUmum = LahanUmum::
            whereMonth('distribution_date', $month)
            ->whereYear('distribution_date', $year)
            ->where([
                ['is_verified', '>', 0]
            ])
            ->groupBy('mou_no')
            ->get();
        
        $events = [];
        foreach ($lahanUmum as $lu) {
            $lahan_no = LahanUmum::where('mou_no', $lu->mou_no)->pluck('lahan_no');
            $lu->mu_name = DB::table('managementunits')->where('mu_no', $lu->mu_no)->first()->name ?? '-';
            $lu->employee_name = DB::table('employees')->where('nik', $lu->employee_no)->first()->name ?? '-';
            $lu->total_bibit_lahan = LahanUmumDetail::whereIn('lahan_no', $lahan_no)->sum('amount');
            $lu->total_bibit_penlub = LahanUmumHoleDetail::whereIn('lahan_no', $lahan_no)->sum('amount');
            
            $nursery = $lu->nursery ?? $this->getNurseryAlocation($lu->mu_no);
            if ($req->nursery == 'All' || ($req->nursery != 'All' && $nursery == $req->nursery)) {
                $pushData = (object)[
                    'nursery' => $nursery,
                    'date' => $lu->distribution_date,
                    'total' => 1,
                    'total_bibit_sostam' => $lu->total_bibit_lahan,
                    'total_bibit_penlub' => $lu->total_bibit_penlub,
                    'details' => [$lu]
                ];
                if (count($events) == 0) {
                    array_push($events, $pushData);
                } else {
                    $added = 0;
                    foreach($events as $evIndex => $evVal) {
                        if ($evVal->date == $lu->distribution_date && $evVal->nursery == $nursery) {
                            array_push($events[$evIndex]->details, $lu);
                            $events[$evIndex]->total_bibit_sostam += $lu->total_bibit_lahan;
                            $events[$evIndex]->total_bibit_penlub += $lu->total_bibit_penlub;
                            $events[$evIndex]->total += 1;
                            $added += 1;
                        }
                    }
                    if ($added == 0) {
                        array_push($events, $pushData);
                    }
                }
            } 
        }
        $datas = [
            'count' => count($events),
            'datas' => $events
        ];
        $rslt =  $this->ResultReturn(200, 'success', $datas);
        return response()->json($rslt, 200);  
    }
    
    // get distribution calendar: seed detail
    public function DistributionSeedDetail(Request $req) {
        // validate request
        $validate = Validator::make($req->all(), [
            'program_year' => 'required',
            'activity' => 'required'
        ]);
        
        // validation fails
        if($validate->fails()){
            $rslt =  $this->ResultReturn(400, $validate->errors()->first(), $validate->errors()->first());
            return response()->json($rslt, 400);
        } else {
            $py = $req->program_year;
            $ffNo = explode(',', $req->ff_no);
            $mouNo = explode(',', $req->mou_no);
            $activity = $req->activity;
        }
        if ($req->ff_no) {
            
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
                    $seeds = PlantingSocializationsDetail::
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
        } else if ($req->mou_no) {
            $total_bibit = 0;
            $total_bibit_details = [];
            $lahan_datas = [];
            $lahanNo = LahanUmum::whereIn('mou_no', $mouNo)->pluck('lahan_no');
            
            $tree_code_kayu = DB::table('trees')->where('tree_category', 'Pohon_Kayu')->pluck('tree_code');
            $tree_code_mpts = DB::table('trees')->where('tree_category', 'Pohon_Buah')->pluck('tree_code');
            $tree_code_crops = DB::table('trees')->where('tree_category', 'Tanaman_Bawah_Empon')->pluck('tree_code');
            if ($activity == 'lahan') {
                $total_bibit_grouping['KAYU'] = LahanUmumDetail::
                                join('trees', 'trees.tree_code', 'lahan_umum_details.tree_code')
                                ->select('lahan_umum_details.tree_code', DB::raw('SUM(lahan_umum_details.amount) as amount'), 'trees.tree_name')
                                ->whereIn('lahan_umum_details.lahan_no', $lahanNo)
                                ->where([
                                    'trees.tree_category' => 'Pohon_Kayu'
                                ]);
                $total_bibit_grouping['MPTS'] = LahanUmumDetail::
                                join('trees', 'trees.tree_code', 'lahan_umum_details.tree_code')
                                ->select('lahan_umum_details.tree_code', DB::raw('SUM(lahan_umum_details.amount) as amount'), 'trees.tree_name')
                                ->whereIn('lahan_umum_details.lahan_no', $lahanNo)
                                ->where([
                                    'trees.tree_category' => 'Pohon_Buah'
                                ]);
                $total_bibit_grouping['CROPS'] = LahanUmumDetail::
                                join('trees', 'trees.tree_code', 'lahan_umum_details.tree_code')
                                ->select('lahan_umum_details.tree_code', DB::raw('SUM(lahan_umum_details.amount) as amount'), 'trees.tree_name')
                                ->whereIn('lahan_umum_details.lahan_no', $lahanNo)
                                ->where([
                                    'trees.tree_category' => 'Tanaman_Bawah_Empon'
                                ]);
                $total_bibit = $total_bibit_grouping['KAYU']->sum('amount') + $total_bibit_grouping['MPTS']->sum('amount') + $total_bibit_grouping['CROPS']->sum('amount');
                $total_bibit_grouping = [
                    'KAYU' => $total_bibit_grouping['KAYU']->groupBy('lahan_umum_details.tree_code')->get(),
                    'MPTS' => $total_bibit_grouping['MPTS']->groupBy('lahan_umum_details.tree_code')->get(),
                    'CROPS' => $total_bibit_grouping['CROPS']->groupBy('lahan_umum_details.tree_code')->get()
                ];
            } else {
                $total_bibit_grouping['KAYU'] = LahanUmumHoleDetail::
                                join('trees', 'trees.tree_code', 'lahan_umum_hole_details.tree_code')
                                ->select('lahan_umum_hole_details.tree_code', DB::raw('SUM(lahan_umum_hole_details.amount) as amount'), 'trees.tree_name')
                                ->whereIn('lahan_umum_hole_details.lahan_no', $lahanNo)
                                ->where([
                                    'trees.tree_category' => 'Pohon_Kayu'
                                ]);
                $total_bibit_grouping['MPTS'] = LahanUmumHoleDetail::
                                join('trees', 'trees.tree_code', 'lahan_umum_hole_details.tree_code')
                                ->select('lahan_umum_hole_details.tree_code', DB::raw('SUM(lahan_umum_hole_details.amount) as amount'), 'trees.tree_name')
                                ->whereIn('lahan_umum_hole_details.lahan_no', $lahanNo)
                                ->where([
                                    'trees.tree_category' => 'Pohon_Buah'
                                ]);
                $total_bibit_grouping['CROPS'] = LahanUmumHoleDetail::
                                join('trees', 'trees.tree_code', 'lahan_umum_hole_details.tree_code')
                                ->select('lahan_umum_hole_details.tree_code', DB::raw('SUM(lahan_umum_hole_details.amount) as amount'), 'trees.tree_name')
                                ->whereIn('lahan_umum_hole_details.lahan_no', $lahanNo)
                                ->where([
                                    'trees.tree_category' => 'Tanaman_Bawah_Empon'
                                ]);
                $total_bibit = $total_bibit_grouping['KAYU']->sum('amount') + $total_bibit_grouping['MPTS']->sum('amount') + $total_bibit_grouping['CROPS']->sum('amount');
                $total_bibit_grouping = [
                    'KAYU' => $total_bibit_grouping['KAYU']->groupBy('lahan_umum_hole_details.tree_code')->get(),
                    'MPTS' => $total_bibit_grouping['MPTS']->groupBy('lahan_umum_hole_details.tree_code')->get(),
                    'CROPS' => $total_bibit_grouping['CROPS']->groupBy('lahan_umum_hole_details.tree_code')->get()
                ];
            }
            $employee_nos = LahanUmum::whereIn('mou_no', $mouNo)->orderBy('mou_no')->groupBy('employee_no')->pluck('employee_no');
            $datas = [
                'program_year' => $py,
                'pic_t4t' => Employee::whereIn('nik', $employee_nos)->pluck('name'),
                'pic_lahan' => LahanUmum::whereIn('mou_no', $mouNo)->orderBy('mou_no')->groupBy('pic_lahan')->pluck('pic_lahan'),
                'mou_no' => $mouNo,
                'activity' => $activity,
                'total_bibit' => $total_bibit,
                'total_bibit_details' => [
                        'KAYU' => $total_bibit_grouping['KAYU'],
                        'MPTS' => $total_bibit_grouping['MPTS'],
                        'CROPS' => $total_bibit_grouping['CROPS'],
                    ],
            ];
        }
        
        $rslt =  $this->ResultReturn(200, 'success', $datas);
        return response()->json($rslt, 200);
    }
    
    // get distribution calendar: period detail
    public function DistributionPeriodDetail(Request $req) {
        // validate request
        $validate = Validator::make($req->all(), [
            'ff_no' => 'required|exists:field_facilitators,ff_no',
            'program_year' => 'required',
        ]);
        
        // validation fails
        if($validate->fails()){
            $rslt =  $this->ResultReturn(400, $validate->errors()->first(), $validate->errors()->first());
            return response()->json($rslt, 400);
        } else {
            $py = $req->program_year;
            $ffNo = $req->ff_no;
        }
        
        
        $ff = FieldFacilitator::
            leftJoin('managementunits', 'managementunits.mu_no', 'field_facilitators.mu_no')
            ->leftJoin('target_areas', 'target_areas.area_code', 'field_facilitators.target_area')
            ->leftJoin('desas', 'desas.kode_desa', 'field_facilitators.working_area')
            ->select('field_facilitators.*', 
                'managementunits.name as mu_name', 
                'target_areas.name as ta_name',
                'desas.name as village_name')
            ->where('field_facilitators.ff_no', $ffNo)->first();
        if ($ff) {
            $sostamFF = PlantingSocializations::where(['ff_no' => $ff->ff_no, 'planting_year' => $py])->orderBy('created_at')->first();
            $period = PlantingSocializationsPeriod::where('form_no', $sostamFF->form_no)->first();
            $fc = Employee::where('nik', $ff->fc_no)->first();
        
            $datas = [
                    'program_year' => $py,
                    'FF' => $ff,
                    'FC' => $fc,
                    'sostam' => $sostamFF,
                    'period' => $period,
                ];
            
            $rslt =  $this->ResultReturn(200, 'success', $datas);
            return response()->json($rslt, 200);
        } else {
            $rslt =  $this->ResultReturn(404, 'Not Found', 'FF Data Not Found');
            return response()->json($rslt, 404);
        }
    }
    
    // get distribution calendar: period detail lahan umum
    public function DistributionPeriodLahanUmumDetail(Request $req) {
        // validate request
        $validate = Validator::make($req->all(), [
            'mou_no' => 'required|exists:lahan_umums,mou_no',
            'lahan_no' => 'required|exists:lahan_umums,lahan_no',
            'program_year' => 'required',
        ]);
        
        // validation fails
        if($validate->fails()){
            $rslt =  $this->ResultReturn(400, $validate->errors()->first(), $validate->errors()->first());
            return response()->json($rslt, 400);
        } else {
            $py = $req->program_year;
            $mou_no = $req->mou_no;
            $lahan_no = $req->lahan_no;
        }
        
        
        $lu = LahanUmum::where([
            'mou_no' => $mou_no,
            'lahan_no' => $lahan_no
        ])->first();
        $lu->mu_name = DB::table('managementunits')->where('mu_no', $lu->mu_no)->first()->name ?? '-';
        $lu->district_name = DB::table('kecamatans')->where('kode_kecamatan', $lu->district)->first()->name ?? '-';
        $lu->village_name = DB::table('desas')->where('kode_desa', $lu->village)->first()->name ?? '-';
        
        if ($lu) {
            $period = (object)[
                'planting_hole_date' => $lu->planting_hole_date,
                'distribution_time' => $lu->distribution_date,
                'planting_realization_date' => $lu->planting_realization_date
            ];
            $employee = Employee::where('nik', $lu->employee_no)->first();
        
            $datas = [
                    'program_year' => $py,
                    'data' => $lu,
                    'pic_lahan' => $lu->pic_lahan,
                    'pic_t4t' => $employee,
                    'nursery' => $lu->nursery ?? $this->getNurseryAlocation($lu->mu_no) ?? 'Tidak Ada',
                    'period' => $period,
                ];
            
            $rslt =  $this->ResultReturn(200, 'success', $datas);
            return response()->json($rslt, 200);
        } else {
            $rslt =  $this->ResultReturn(404, 'Not Found', 'FF Data Not Found');
            return response()->json($rslt, 404);
        }
    }
    
    // update distribution calendar: period detail lahan umum
    public function UpdateLahanUmumPeriod(Request $req) {
        // validate request
        $validate = Validator::make($req->all(), [
            'mou_no' => 'required|exists:lahan_umums,mou_no',
            'lahan_no' => 'required|exists:lahan_umums,lahan_no',
            'program_year' => 'required',
            'planting_hole_date' => 'required',
            'distribution_date' => 'required',
            'planting_realization_date' => 'required',
            'nursery' => 'required',
        ]);
        
        // validation fails
        if($validate->fails()){
            $rslt =  $this->ResultReturn(400, $validate->errors()->first(), $validate->errors()->first());
            return response()->json($rslt, 400);
        } else {
            $py = $req->program_year;
            $mou_no = $req->mou_no;
            $lahan_no = $req->lahan_no;
            $planting_hole_date = $req->planting_hole_date;
            $distribution_date = $req->distribution_date;
            $planting_realization_date = $req->planting_realization_date;
            $nursery = $req->nursery;
        }
        
        // update Lahan Umum Period
        $update = LahanUmum::where([
            'program_year' => $py,
            'mou_no' => $mou_no,
        ])->update([
            'planting_hole_date' => $planting_hole_date,
            'distribution_date' => $distribution_date,
            'planting_realization_date' => $planting_realization_date,
            'nursery' => $nursery,
        ]);
        if ($update) {
            return response()->json('success', 200);
        } else {
            return response()->json('failed', 500);
        }
    }
    
    // get packing label: by Lahan
    public function GetPackingLabelByLahan(Request $req) {
        $disDate = $req->distribution_date;
        $getSostam = PlantingSocializationsPeriod::whereDate('distribution_time', $disDate)->pluck('form_no');
        $getLahanNo = PlantingSocializations::whereIn('form_no', $getSostam)->pluck('no_lahan');
        
        $py = $req->program_year;
        $typegetdata = $req->typegetdata;
        $ff = $req->ff;
        $getmu = $req->mu;
        $getta = $req->ta;
        $getvillage = $req->village;
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
                ->leftjoin('managementunits', 'managementunits.mu_no', '=', 'lahans.mu_no')
                ->leftjoin('target_areas', 'target_areas.area_code', '=', 'lahans.target_area')
                ->leftjoin('farmers', 'farmers.farmer_no', '=', 'lahans.farmer_no')
                ->leftjoin('field_facilitators', 'field_facilitators.ff_no', '=', 'planting_hole_surviellance.user_id')
                ->select(
                    'planting_hole_surviellance.id',
                    'planting_hole_surviellance.lahan_no',
                    'planting_hole_surviellance.ph_form_no',
                    'planting_hole_surviellance.planting_year',
                    'planting_hole_surviellance.total_holes',
                    'planting_hole_surviellance.latitude', 
                    'planting_hole_surviellance.longitude',
                    'planting_hole_surviellance.is_validate',
                    'planting_hole_surviellance.validate_by',
                    'planting_hole_surviellance.is_dell', 
                    'planting_hole_surviellance.created_at', 
                    'planting_hole_surviellance.is_checked', 
                    'planting_hole_surviellance.user_id',
                    'managementunits.name as mu_name',
                    'target_areas.name as ta_name',
                    'farmers.name as nama_petani', 
                    'field_facilitators.name as nama_ff'
                )
                ->where([
                    ['planting_hole_surviellance.is_dell','=',0],
                    ['planting_hole_surviellance.is_validate','=',1],
                    'planting_hole_surviellance.planting_year' => $py,
                    ['field_facilitators.name', 'NOT LIKE', '%FF_%'],
                    // ['planting_hole_surviellance.lahan_no', 'LIKE', '10_000%']
                ])
                ->whereIn('planting_hole_surviellance.lahan_no', $getLahanNo);
            
           
            if($typegetdata == 'all' || $typegetdata == 'several'){
                if($typegetdata == 'all'){
                    // second query
                    $GetPH = $GetPH->where([
                        ['lahans.mu_no','like',$mu],
                        ['lahans.target_area','like',$ta],
                        ['lahans.village','like',$village]
                    ]);
                }else{
                    // set ff
                    $ffdecode = (explode(",",$ff));
                    // second query
                    $GetPH = $GetPH->wherein('planting_hole_surviellance.user_id',$ffdecode);
                }
                
                // third query
                if ($req->nursery != 'All') {
                    $listMU = $this->getNurseryAlocationReverse($req->nursery);
                    $GetPH = $GetPH->whereIn('lahans.mu_no', $listMU);
                }
                
                // last query
                $newData = $GetPH
                    ->orderBy('planting_hole_surviellance.created_at', 'DESC')
                    ->get();
                foreach ($newData as $phIndex => $phData) {
                    $seed = PlantingHoleSurviellanceDetail::where('ph_form_no', $phData->ph_form_no);
                    $newData[$phIndex]->total_bibit = $seed->sum('amount');
                }
                
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
    
    // get packing label: by Lahan Temporary
    public function GetPackingLabelByLahanTemp(Request $req) {
        $disDate = $req->distribution_date;
        $getSostam = PlantingSocializationsPeriod::whereDate('distribution_time', $disDate)->pluck('form_no');
        $getLahanNo = PlantingSocializations::whereIn('form_no', $getSostam)->pluck('no_lahan');
        
        $py = $req->program_year;
        $typegetdata = $req->typegetdata;
        $ff = $req->ff;
        $getmu = $req->mu;
        $getta = $req->ta;
        $getvillage = $req->village;
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
                ->leftjoin('managementunits', 'managementunits.mu_no', '=', 'lahans.mu_no')
                ->leftjoin('target_areas', 'target_areas.area_code', '=', 'lahans.target_area')
                ->leftjoin('farmers', 'farmers.farmer_no', '=', 'lahans.farmer_no')
                ->leftjoin('field_facilitators', 'field_facilitators.ff_no', '=', 'planting_hole_surviellance.user_id')
                ->select(
                    'planting_hole_surviellance.id',
                    'planting_hole_surviellance.lahan_no',
                    'planting_hole_surviellance.ph_form_no',
                    'planting_hole_surviellance.planting_year',
                    'planting_hole_surviellance.total_holes',
                    'planting_hole_surviellance.latitude', 
                    'planting_hole_surviellance.longitude',
                    'planting_hole_surviellance.is_validate',
                    'planting_hole_surviellance.validate_by',
                    'planting_hole_surviellance.is_dell', 
                    'planting_hole_surviellance.created_at', 
                    'planting_hole_surviellance.is_checked', 
                    'planting_hole_surviellance.user_id',
                    'managementunits.name as mu_name',
                    'target_areas.name as ta_name',
                    'farmers.name as nama_petani', 
                    'field_facilitators.name as nama_ff'
                )
                ->where([
                    ['planting_hole_surviellance.is_dell','=',0],
                    ['planting_hole_surviellance.is_validate','=',1],
                    'planting_hole_surviellance.planting_year' => $py,
                    ['field_facilitators.name', 'NOT LIKE', '%FF_%'],
                    // ['planting_hole_surviellance.lahan_no', 'LIKE', '10_000%']
                ])
                ->whereIn('planting_hole_surviellance.lahan_no', $getLahanNo);
            
           
            if($typegetdata == 'all' || $typegetdata == 'several'){
                if($typegetdata == 'all'){
                    // second query
                    $GetPH = $GetPH->where([
                        ['lahans.mu_no','like',$mu],
                        ['lahans.target_area','like',$ta],
                        ['lahans.village','like',$village]
                    ]);
                }else{
                    // set ff
                    $ffdecode = (explode(",",$ff));
                    // second query
                    $GetPH = $GetPH->wherein('planting_hole_surviellance.user_id',$ffdecode);
                }
                
                // third query
                if ($req->nursery != 'All') {
                    $listMU = $this->getNurseryAlocationReverse($req->nursery);
                    $GetPH = $GetPH->whereIn('lahans.mu_no', $listMU);
                }
                
                // last query
                $newData = $GetPH
                    ->orderBy('planting_hole_surviellance.created_at', 'DESC')
                    ->get();
                foreach ($newData as $phIndex => $phData) {
                    $seed = PlantingHoleSurviellanceDetail::where('ph_form_no', $phData->ph_form_no);
                    $newData[$phIndex]->total_bibit = $seed->sum('amount');
                }
                
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
    
    // get packing label: Lahan Umum
    public function GetPackingLabelLahanUmum(Request $req) {
        $disDate = $req->distribution_date;
        
        $py = $req->program_year;
        try{
            // set first query
            $GetPH = LahanUmumHoleDetail::
                join('lahan_umums', 'lahan_umums.lahan_no', 'lahan_umum_hole_details.lahan_no')
                ->select(
                    'lahan_umums.mu_no',
                    'lahan_umums.employee_no',
                    'lahan_umums.nursery',
                    'lahan_umums.pic_lahan',
                    'lahan_umums.lahan_no',
                    'lahan_umums.total_holes',
                    DB::raw('SUM(lahan_umum_hole_details.amount) as total_bibit'),
                    'lahan_umums.is_verified',
                    'lahan_umums.is_checked'
                )
                ->where('lahan_umums.is_verified', '>', 1)->whereDate('distribution_date', $disDate);
                
            // second query
            if ($req->created_by) {
                $GetPH = $GetPH->where('lahan_umums.created_by', $req->createdBy);
            }
                
            // last query
            $GetPH = $GetPH->groupBy('lahan_no')->get();
            // return response()->json($GetPH, 200);
            $newPH = [];
            if ($req->nursery != 'All') $listMU = $this->getNurseryAlocationReverse($req->nursery);
            foreach($GetPH as $dataIndex => $dataA) {
                // filter nursery
                if ($req->nursery != 'All') {
                    if ($dataA->nursery) {
                        if ($dataA->nursery == $req->nursery) array_push($newPH, $dataA);
                    } else if (in_array($dataA->mu_no, $listMU)) array_push($newPH, $dataA);
                } else array_push($newPH, $dataA);
            }
            
            // third query
            // if ($req->nursery != 'All') {
            //     $listMU = $this->getNurseryAlocationReverse($req->nursery);
            //     $GetPH = $GetPH->whereIn('lahan_umums.mu_no', $listMU)->orWhere('lahan_umums.nursery', $req->nursery);
            // }
            
            
            // add relational data
            foreach($newPH as $dataIndex => $data) {
                $data->mu_name = DB::table('managementunits')->where('mu_no', $data->mu_no)->first()->name ?? '-';
                $data->employee_name = Employee::where('nik', $data->employee_no)->first()->name ?? '-';
            }
            
            
            if($count = count($newPH)){
                $data = ['count'=>$count, 'data'=>$newPH];
                // set response
                $rslt =  $this->ResultReturn(200, 'success', $data);
                return response()->json($rslt, 200); 
            }
            else{
                $rslt =  $this->ResultReturn(404, 'doesnt match data', 'doesnt match data');
                return response()->json($rslt, 404);
            } 
            // var_dump(count($GetLahanNotComplete));
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }
    
    // get Loading Line data 
    public function GetLoadingLine(Request $req) {
        $py = $req->program_year;
        $typegetdata = $req->typegetdata;
        $ff = $req->ff;
        $getmu = $req->mu;
        $getta = $req->ta;
        $getvillage = $req->village;
        if($getmu){$mu='%'.$getmu.'%';}
        else{$mu='%%';}
        if($getta){$ta='%'.$getta.'%';}
        else{$ta='%%';}
        if($getvillage){$village='%'.$getvillage.'%';}
        else{$village='%%';}
        // first query: filter mandatory
        $datas = Distribution::
            join('field_facilitators', 'field_facilitators.ff_no', '=', 'distributions.ff_no')
            ->join('managementunits', 'managementunits.mu_no', '=', 'field_facilitators.mu_no')
            ->select(
                'distributions.is_loaded',
                'managementunits.name as mu_name',
                'field_facilitators.name as ff_name',
                'distributions.ff_no',
                DB::raw('SUM(distributions.total_bags) as total_bags'),
                DB::raw('SUM(distributions.total_tree_amount) as total_tree_amount')
            )
            ->where([
                'distributions.is_dell' => 0,
                ['field_facilitators.mu_no', 'LIKE', $mu],
                ['field_facilitators.target_area', 'LIKE', $ta],
                ['field_facilitators.working_area', 'LIKE', $village],
                ['distributions.distribution_no', 'LIKE', 'D-'.$py.'%'],
            ])
            ->whereDate('distributions.distribution_date', $req->distribution_date);
        
        // second query: filter by nursery
        if ($req->nursery != 'All' && $req->nursery != '') {
            $listMU = $this->getNurseryAlocationReverse($req->nursery);
            $datas = $datas->whereIn('field_facilitators.mu_no', $listMU);
        }
        
        // third query: filter by ff no
        if ($ff) {
            $listFF = explode(',',$ff);
            $datas = $datas->whereIn('distributions.ff_no', $listFF);
        }
        
        // final result
        $datas = $datas
            ->groupBy('distributions.ff_no')
            ->get();
            
        // get Progress Printed Data
        foreach($datas as $dataIndex => $data) {
            $PHQuery = PlantingHoleSurviellance::where([
                'user_id' => $data->ff_no, 
                'is_dell' => 0, 
                'is_validate' => 1, 
                'planting_year' => $py
            ]);
            $totalPHAll = $PHQuery->count();
            $totalPHPrinted = $PHQuery->where('is_checked', 1)->count();
            $datas[$dataIndex]['ph_printed'] =  $totalPHPrinted;
            $datas[$dataIndex]['ph_all'] =  $totalPHAll;
            $datas[$dataIndex]['printed_progress'] =  round($totalPHPrinted / $totalPHAll * 100);
        }
                    
        $rslt =  $this->ResultReturn(200, 'success', $datas);
        return response()->json($rslt, 200); 
        
    }
    // get loading line for nursery
    public function GEKO_getLoadingLine(Request $req) {
        if ($req->program_year) {
            $py = preg_replace("/[^0-9]/", "", $req->program_year);
        } else $py = '2023';
        
        $query = DB::table('distributions')
            ->select(
                'distributions.distribution_no',
                DB::raw('DATE(distributions.distribution_date) as distribution_date'),
                DB::raw('SUM(distributions.total_bags) as total_bags'),
                DB::raw('SUM(distributions.total_tree_amount) as total_seeds'),
                'distributions.is_loaded',
                'field_facilitators.name as ff_name',
                'field_facilitators.ff_no',
                'ff_working_areas.mu_no',
                'managementunits.name as mu_name'
            )
            ->join('field_facilitators', 'field_facilitators.ff_no', 'distributions.ff_no')
            ->join('ff_working_areas', 'ff_working_areas.ff_no', 'distributions.ff_no')
            ->join('managementunits', 'managementunits.mu_no', 'ff_working_areas.mu_no')
            ->where([
                ['distributions.distribution_no', 'LIKE', "D-$py-%"],
                ['ff_working_areas.program_year', 'LIKE', "%$py%"], 
            ]);
        
        if ($req->distribution_date) {
            $query = $query->whereDate('distributions.distribution_date', $req->distribution_date);
        }
        if ($req->mu_name) {
            $query = $query->where('managementunits.name','LIKE', "%$req->mu_name%");
        }
        if ($req->ff_name) {
            $query = $query->where('field_facilitators.name','LIKE', "%$req->ff_name%");
        }
        if ($req->is_loaded) {
            $query = $query->where('distributions.is_loaded','=', (int)$req->is_loaded);
        }
        
        if ($req->search) {
            $query = $query->where('managementunits.name','LIKE', $req->search)
                ->orWhere('field_facilitators.name','LIKE', $req->search);
        }
        
        // order
        if ($req->order && $req->sort) {
            $query = $query->orderBy($req->order, $req->sort);
        }
        
        $paginate = $query->groupBy('distributions.ff_no')->paginate($req->limit);
        
        $rslt = [
            'data' => $paginate->items(),
            'total' => $paginate->total(),
            'totalPage' => $paginate->lastPage()
        ];
        return response()->json($rslt, 200);
    }
    // get Loading Line: FF data Detail For SCAN WEB
    public function GetLoadingLineDetailFF(Request $req) {
        // validate request
        $validate = Validator::make($req->all(), [
            'ff_no' => 'required|exists:field_facilitators,ff_no',
            'program_year' => 'required',
        ]);
        
        // validation fails
        if($validate->fails()){
            $rslt =  $this->ResultReturn(400, $validate->errors()->first(), $validate->errors()->first());
            return response()->json($rslt, 400);
        } else {
            $py = $req->program_year;
            $ff = FieldFacilitator::where('ff_no', $req->ff_no)->first();
        }
        $distributions = Distribution::
            join('farmers', 'farmers.farmer_no', 'distributions.farmer_no')
            ->join('field_facilitators', 'field_facilitators.ff_no', 'distributions.ff_no')
            ->join('managementunits', 'managementunits.mu_no', 'field_facilitators.mu_no')
            ->select(
                'distributions.distribution_no',
                'distributions.distribution_date',
                'managementunits.name as mu_name',
                'distributions.farmer_no',
                'farmers.name as farmer_name',
                'distributions.ff_no',
                'field_facilitators.name as ff_name',
                'distributions.total_bags',
                'distributions.total_tree_amount',
                'distributions.is_loaded'
            )
            ->where(['distributions.ff_no' => $ff->ff_no, ['distributions.distribution_no', 'LIKE', 'D-'.$py.'%']])->get();
        
        $totalBags = 0;
        $totalTreesAmount = 0;
        if (count($distributions) > 0) {
            foreach($distributions as $disIndex => $distribution) {
                $distributions[$disIndex]['bags_number'] = DistributionDetail::where('distribution_no', $distribution->distribution_no)->groupBy('bag_number')->orderBy('id')->pluck('bag_number');
                $distributions[$disIndex]['bags_number_loaded'] = DistributionDetail::where(['distribution_no' => $distribution->distribution_no, 'is_loaded' => 1])->groupBy('bag_number')->orderBy('id')->pluck('bag_number');
                $distributions[$disIndex]['labels_list'] = DistributionDetail::where('distribution_no', $distribution->distribution_no)->get();
                $totalBags += $distribution->total_bags;
                $totalTreesAmount += $distribution->total_tree_amount;
            }
            return response()->json([
                'total_bags' => $totalBags,
                'total_trees_amount' => $totalTreesAmount,
                'distribution_details' => $distributions
            ], 200);
        } else {
            return response()->json('Data not found', 404);
        }
        
    }
    
    // update Loading Line: FF data Detail For SCAN WEB
    public function LoadedDistributionBagsNumber(Request $req) {
        // validate request
        $validate = Validator::make($req->all(), [
            'bags_number' => 'required',
            'ff_no' => 'required|exists:field_facilitators,ff_no',
            'program_year' => 'required'
        ]);
        
        // validation fails
        if($validate->fails()){
            $rslt =  $this->ResultReturn(400, $validate->errors()->first(), $validate->errors()->first());
            return response()->json($rslt, 400);
        }else {
            $py = $req->program_year;
        }
        // datas
        $datas = DistributionDetail::where('is_loaded', 0)->whereIn('bag_number', $req->bags_number);
        if ($datas->count() > 0) {
            // update
            $datas->update([
                'is_loaded' => 1,
                'loaded_by' => Auth::user()->email
            ]);
            
            $distribution = Distribution::where(['ff_no' => $req->ff_no, ['distribution_no', 'LIKE', 'D-'.$py.'%']])->pluck('distribution_no');
            $bagsLoaded = DistributionDetail::where('is_loaded', 1)->whereIn('distribution_no', $distribution)->count();
            $bagsAll = DistributionDetail::whereIn('distribution_no', $distribution)->count();
            
            if ($bagsLoaded == $bagsAll) {
                Distribution::where(['ff_no' => $req->ff_no, ['distribution_no', 'LIKE', 'D-'.$py.'%']])->update(['is_loaded' => 1, 'loaded_by' => Auth::user()->email]);
            }
            
            return response()->json(('Changed ' . $datas->count() . 'Data Distributions Succeed!'), 200);
        } else {
            return response()->json('Data not found, or already scanned.', 404);
        }
        
        
    }
    
    // update Loading Line: Force Finish Data
    public function FinishLoadingBagsDistributions(Request $req)  {
        // validate request
        $validate = Validator::make($req->all(), [
            'ff_no' => 'required|exists:field_facilitators,ff_no',
            'program_year' => 'required'
        ]);
        
        // validation fails
        if($validate->fails()){
            $rslt =  $this->ResultReturn(400, $validate->errors()->first(), $validate->errors()->first());
            return response()->json($rslt, 400);
        }else {
            $py = $req->program_year;
        }
        
        // update loaded status
        $update = Distribution::where(['ff_no' => $req->ff_no, ['distribution_no', 'LIKE', 'D-'.$py.'%']])->update(['is_loaded' => 1, 'loaded_by' => Auth::user()->email]);
        if ($update) {
            return response()->json(('Force finish loading bags success!'), 200);
        } else {
            return response()->json('Data not found, or already scanned.', 404);
        }
    }
    
    public function DistributionVerificationUM(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'distribution_no' => 'required|exists:distributions,distribution_no'
        ]);
        
        // validation fails
        if($validate->fails()){
            $rslt =  $this->ResultReturn(400, $validate->errors()->first(), $validate->errors()->first());
            return response()->json($rslt, 400);
        } else {
            $dn = $request->distribution_no;
        }
        
        $verif = DB::table('distributions')->where('distribution_no', '=', $dn)->first();
        
        if($verif){
            Distribution::where('distribution_no', '=', $dn)
                ->update([
                    'updated_at' => Carbon::now(),
                    'approved_by' => $request->approved_by,
                    'status' => 2
            ]);

            $listTree = $request->list_trees;

            foreach($listTree as $val){
                $tree_code = DB::table('tree_locations')->where('tree_name', '=', $val['tree_name'])->first()->tree_code ?? '-';
                
                DistributionAdjustment::where([
                    'distribution_no' => $verif->distribution_no,
                    'lahan_no' => $val['lahan_no'],
                    'tree_code' => $tree_code
                ])->update([
                    'broken_seeds' => $val['broken_seeds'],
                    'missing_seeds' => $val['missing_seeds'],
                    'total_distributed' => $val['total_distributed'],
                    'total_tree_received' => $val['total_tree_received'],
                    'is_verified' => '1',
                    'approved_by' => $request->approved_by,
                    'updated_at'=>Carbon::now()
                ]);
            }
            
            $rslt = $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200);
        }else{
            $rslt =  $this->ResultReturn(400, 'doesnt match data', 'doesnt match data');
                return response()->json($rslt, 400);
        }
    }
    
    public function UnverificationDistribution(Request $request){
        try{
            $validator = Validator::make($request->all(), [    
                'distribution_no' => 'required'
            ]);

            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
            Distribution::where('distribution_no', '=', $request->distribution_no)
                    ->update
                    ([
                        'status' => 0,
                        'approved_by' => $request->verified_by,
                        'updated_at' => Carbon::now()
                    ]);
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200); 
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }
    
    public function CreateAdjustment(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'distribution_no' => 'required',
            'program_year' => 'required',
        ]);
        
        // validation fails
        if($validate->fails()){
            $rslt =  $this->ResultReturn(400, $validate->errors()->first(), $validate->errors()->first());
            return response()->json($rslt, 400);
        } else {
            $dn = $request->distribution_no;
        }
        
        $dist = DB::table('distributions')->where('distribution_no', '=', $dn)->first();
        
        DistributionAdjustment::where('distribution_no', '=', $dn)->delete();
            
            if($dist){
                Distribution::where('distribution_no', '=', $dn)
                    ->update([
                        'updated_at' => Carbon::now(),
                        'approved_by' => $request->approved_by,
                        'status' => 1
                ]);
                
                $listTree = $request->list_trees;
                // return response()->json($request->all(), 200);
        
                foreach($listTree as $val){
                    DistributionAdjustment::create([
                        'distribution_no' => $dist->distribution_no,
                        'ff_no' => $request->ff_no,
                        'farmer_no' => $request->farmer_no,
                        'lahan_no' => $val['lahan_no'],
                        'adjust_date'=>$request->adjust_date,
                        'broken_seeds' => $val['broken_seeds'],
                        'missing_seeds' => $val['missing_seeds'],
                        'total_distributed' => $val['total_distributed'],
                        'total_tree_received' => $val['total_tree_received'],
                        'tree_category' => $val['tree_category'],
                        'tree_code' => DB::table('tree_locations')->where('tree_name', '=', $val['tree_name'])->first()->tree_code ?? '-',
                        'planting_year' => $request->program_year,
                        'created_by' => $request->approved_by,
                        'is_verified' => '0',
                        'created_at'=>Carbon::now(),
                        'updated_at'=>Carbon::now()
                    ]);
                }
                
                $rslt = $this->ResultReturn(200, 'success', 'success');
                return response()->json($rslt, 200);
            }else{
                $rslt =  $this->ResultReturn(400, 'doesnt match data', 'doesnt match data');
                    return response()->json($rslt, 400);
        }
    }
    
    public function GetDetailDistributionReport(Request $req)
    {
        $dist = $req->distribution_no;
        $ff = $req->ff_no;
        
        $datas = Distribution::
            join('field_facilitators', 'field_facilitators.ff_no', 'distributions.ff_no')
            ->join('farmers', 'farmers.farmer_no', 'distributions.farmer_no')
            ->join('managementunits', 'managementunits.mu_no', 'field_facilitators.mu_no')
            ->select('distributions.distribution_no as distribution_no',
                     'distributions.distribution_date as distribution_date',
                     'distributions.ff_no as ff_no',
                     'distributions.farmer_no as farmer_no',
                     'distributions.farmer_signature as farmer_signature',
                     'distributions.distribution_note as distribution_note',
                     'distributions.distribution_photo as distribution_photo',
                     'distributions.status as status',
                     'distributions.total_bags as total_bags',
                     'distributions.total_tree_amount as total_tree_amount',
                     'distributions.is_loaded as is_loaded',
                     'distributions.loaded_by as loaded_by',
                     'distributions.loaded_time as loaded_time',
                     'distributions.is_distributed as is_distributed',
                     'distributions.distributed_by as distributed_by',
                     'distributions.distributed_time as distributed_time',
                     'distributions.created_at as created_at',
                     'distributions.updated_at as updated_at',
                     'distributions.is_dell as is_dell',
                     'distributions.deleted_by as deleted_by',
                     'distributions.approved_by as approved_by',
                     'field_facilitators.name as ff_name',
                     'farmers.name as farmer_name',
                     'managementunits.name as mu_name')
            ->where('distributions.distribution_no', '=', $dist)
            ->where('distributions.is_dell', '=', 0)
            ->first();
            
            if($datas){
                $getDetailDistribution =  DB::table('distribution_details')
                ->select('distribution_details.id',
                         'distribution_details.distribution_no',
                         'distribution_details.bag_number',
                         'distribution_details.tree_name',
                         'distribution_details.tree_category',
                         'distribution_details.tree_amount',
                         'distribution_details.is_loaded',
                         'distribution_details.loaded_by',
                         'distribution_details.is_distributed',
                         'distribution_details.distributed_by',
                         'distribution_details.created_at',
                         'distribution_details.updated_at')
                ->where('distribution_details.distribution_no', '=', $datas->distribution_no)
                ->get();
                
                $getFF = DB::table('field_facilitators')->where('ff_no', '=', $datas->ff_no)->first();
                
                $getDetailAdjustment = DB::table('distribution_adjustments')
                ->leftjoin('tree_locations', 'tree_locations.tree_code', 'distribution_adjustments.tree_code')
                ->select('distribution_adjustments.id',
                         'distribution_adjustments.distribution_no',
                         'distribution_adjustments.ff_no',
                         'distribution_adjustments.farmer_no',
                         'distribution_adjustments.lahan_no',
                         'distribution_adjustments.adjust_date',
                         'distribution_adjustments.tree_code',
                         'distribution_adjustments.tree_category',
                         'distribution_adjustments.total_distributed',
                         'distribution_adjustments.broken_seeds',
                         'distribution_adjustments.missing_seeds',
                         'distribution_adjustments.total_tree_received',
                         'distribution_adjustments.planting_year',
                         'distribution_adjustments.is_dell',
                         'distribution_adjustments.is_verified',
                         'distribution_adjustments.created_by',
                         'distribution_adjustments.approved_by',
                         'distribution_adjustments.updated_by',
                         'distribution_adjustments.created_at',
                         'distribution_adjustments.updated_at',
                         'tree_locations.tree_name')
                ->where('distribution_adjustments.distribution_no', '=', $datas->distribution_no)
                ->where('tree_locations.mu_no', '=', $getFF->mu_no)
                ->get();
                
                $DistributionDetail = [
                     'distribution_no'=>$datas->distribution_no,
                     'distribution_date'=>$datas->distribution_date,
                     'ff_no'=>$datas->ff_no,
                     'ff_name'=>$datas->ff_name,
                     'farmer_no'=>$datas->farmer_no,
                     'farmer_name'=>$datas->farmer_name,
                     'farmer_signature'=>$datas->farmer_signature,
                     'distribution_note'=>$datas->distribution_note,
                     'distribution_photo'=>$datas->distribution_photo,
                     'status'=>$datas->status,
                     'total_bags'=>$datas->total_bags,
                     'total_tree_amount'=>$datas->total_tree_amount,
                     'is_loaded'=>$datas->is_loaded,
                     'loaded_by'=>$datas->loaded_by,
                     'loaded_time'=>$datas->loaded_time,
                     'is_distributed'=>$datas->is_distributed,
                     'distributed_by'=>$datas->distributed_by,
                     'distributed_time'=>$datas->distributed_time,
                     'created_at'=>$datas->created_at,
                     'updated_at'=>$datas->updated_at,
                     'is_dell'=>$datas->is_dell,
                     'deleted_by'=>$datas->deleted_by,
                     'approved_by'=>$datas->approved_by,
                     'distributionDetail'=>$getDetailDistribution,
                     'distributionAdjustment'=>$getDetailAdjustment
                ];
                
                $data = $DistributionDetail;
                
                $rslt =  $this->ResultReturn(200, 'success', $data);
                return response()->json($rslt, 200);
            }
            
        $rslt =  $this->ResultReturn(200, 'success', $datas);
        return response()->json($rslt, 200); 
    }
    
    // get Distribution Report
    public function GetDistributionReport(Request $req) {
        //$dist_no = $req->distribuiton_no;
        $distribution_date = $req->distribution_date;
        $py = $req->program_year;
        $typegetdata = $req->typegetdata;
        $ff = $req->ff;
        $getmu = $req->mu;
        $getta = $req->ta;
        $getvillage = $req->village;
        if($getmu){$mu='%'.$getmu.'%';}
        else{$mu='%%';}
        if($getta){$ta='%'.$getta.'%';}
        else{$ta='%%';}
        if($getvillage){$village='%'.$getvillage.'%';}
        else{$village='%%';}
        
        $datas = Distribution::
            join('field_facilitators', 'field_facilitators.ff_no', 'distributions.ff_no')
            ->join('farmers', 'farmers.farmer_no', 'distributions.farmer_no')
            ->join('managementunits', 'managementunits.mu_no', 'field_facilitators.mu_no')
            ->select(
                'distributions.distribution_no',
                'distributions.ff_no', 
                'distributions.farmer_no', 
                'distributions.distribution_date',
                'distributions.status',
                'field_facilitators.mu_no',
                'field_facilitators.name as ff_name',
                'farmers.name as farmer_name',
                'managementunits.name as mu_name'
                )
            ->where([
                ['distributions.distribution_no', 'LIKE', 'D-'.$py.'%'],
                'distributions.is_dell' => 0,
                ['field_facilitators.mu_no', 'LIKE', $mu],
                ['field_facilitators.target_area', 'LIKE', $ta],
                ['field_facilitators.working_area', 'LIKE', $village]
            ])
            ->whereDate('distributions.distribution_date', $distribution_date);
            
        if ($ff) {
            $listFF = explode(',',$ff);
            $datas = $datas->whereIn('distributions.ff_no', $listFF);
        }
        
        if ($req->nursery != 'All' && $req->nursery != '') {
            $listMU = $this->getNurseryAlocationReverse($req->nursery);
            $datas = $datas->whereIn('field_facilitators.mu_no', $listMU);
        }
        
        $datas = $datas->get();
        
        // get Total Bags
        foreach($datas as $index => $data) {
            $datas[$index]->sum_all_bags = count(DistributionDetail::where([
                    'distribution_no' =>  $data->distribution_no
                ])->groupBy('bag_number')->get());
            $datas[$index]->sum_loaded_bags = count(DistributionDetail::where([
                    'distribution_no' =>  $data->distribution_no,
                    'is_loaded' => 1
                ])->groupBy('bag_number')->get());
            $datas[$index]->sum_distributed_bags = count(DistributionDetail::where([
                    'distribution_no' =>  $data->distribution_no,
                    'is_loaded' => 1,
                    'is_distributed' => 1
                ])->groupBy('bag_number')->get());
            $datas[$index]->adj_kayu = DistributionAdjustment::where('distribution_no', $data->distribution_no)->where('tree_category', 'KAYU')->sum('total_tree_received') ?? 0;
            $datas[$index]->adj_mpts = DistributionAdjustment::where('distribution_no', $data->distribution_no)->where('tree_category', 'MPTS')->sum('total_tree_received') ?? 0;
            $datas[$index]->adj_crops = DistributionAdjustment::where('distribution_no', $data->distribution_no)->where('tree_category', 'CROPS')->sum('total_tree_received') ?? 0;
        }
            
        $rslt =  $this->ResultReturn(200, 'success', $datas);
        return response()->json($rslt, 200); 
    }
    
    public function ExportDistributionReport(Request $req) {
        $validate = Validator::make($req->all(), [
            'program_year' => 'required',
            'distribution_date' => 'required|date',
            'nursery' => 'required'
        ]);
        
        // validation fails
        if($validate->fails()){
            return response()->json($validate->errors()->first(), 400);
        }
        
        $datas = Distribution::
            join('field_facilitators', 'field_facilitators.ff_no', 'distributions.ff_no')
            ->join('managementunits', 'managementunits.mu_no', 'field_facilitators.mu_no')
            ->join('target_areas', 'target_areas.area_code', 'field_facilitators.target_area')
            ->join('desas', 'desas.kode_desa', 'field_facilitators.working_area')
            ->join('employees', 'employees.nik', 'field_facilitators.fc_no')
            ->join('farmers', 'farmers.farmer_no', 'distributions.farmer_no')
            ->join('distribution_adjustments', 'distribution_adjustments.farmer_no', 'distributions.farmer_no')
            ->select('distributions.*', 
                'distribution_adjustments.lahan_no',
                'field_facilitators.mu_no', 
                'managementunits.name as mu', 
                'target_areas.name as ta', 
                'desas.name as desa', 
                'employees.name as fc_name',
                'field_facilitators.name as ff_name',
                'farmers.name as farmer_name'
            )
            ->where([
                ['distributions.distribution_no', 'LIKE', "D-$req->program_year-%"],
                ['status', '>', 0],
                'distributions.is_dell' => 0,
            ])->whereDate('distribution_date', $req->distribution_date);
            
        if ($req->ff) {
            $ff = explode(",", $req->ff);
            $datas = $datas->whereIn('distributions.ff_no', $ff);
        }
        if ($req->nursery) if ($req->nursery != 'All') {
            $mu_no = $this->getNurseryAlocationReverse($req->nursery);
            $datas = $datas->whereIn('field_facilitators.mu_no', $mu_no);
        }
        $distribution_no = $datas->pluck('distribution_no');
        $tree_code = DistributionAdjustment::whereIn('distribution_no', $distribution_no)->groupBy('tree_code')->pluck('tree_code');
        $trees = DB::table('tree_locations')->groupBy('tree_code')->orderBy('tree_name')->get();
        $datas = $datas->orderBy('farmer_name')->groupBy('distribution_adjustments.lahan_no')->get();
        
        foreach ($datas as $data) {
            $amount = [];
            $dd_tree_code = DistributionAdjustment::where(['distribution_no' => $data->distribution_no, 'lahan_no' => $data->lahan_no])->pluck('tree_code')->toArray();
            foreach ($trees as $tree) {
                if (in_array($tree->tree_code, $dd_tree_code)) {
                    $dd_amount = DistributionAdjustment::select(
                        'total_distributed', 'broken_seeds', 'missing_seeds', 'total_tree_received'    
                    )->where(['distribution_no' => $data->distribution_no, 'tree_code' => $tree->tree_code, 'lahan_no' => $data->lahan_no])->first();
                    array_push($amount, $dd_amount);
                } else array_push($amount, (object)[
                        'total_distributed' => 0,
                        'broken_seeds' => 0,
                        'missing_seeds' => 0,
                        'total_tree_received' => 0,
                    ]);
            }
            $data->trees = $amount;
            
            $data->nursery = $this->getNurseryAlocation($data->mu_no);
        }
            
        $rslt = [
            'py' => $req->program_year,
            'distribution_date' => $req->distribution_date,
            'nama_title' => 'Export Distribution Report Lahan Petani',
            'trees' => (object) [
                'count' => count($trees),
                'data' => $trees
            ],
            'distributions' => (object) [
                'count' => count($datas),
                'data' => $datas
            ]
        ];
        // return response()->json($rslt, 200);
        return view('distributions.export_report', $rslt);
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
    private function getNurseryAlocationReverse($nursery) {
        $nur = [
            'Arjasari' => ['022', '024', '025', '020', '029'],
            'Ciminyak' => ['023', '026', '027', '021'],
            'Kebumen' => ['019'],
            'Pati' => ['015', '016']
        ];
        
        return $nur[$nursery];
    }
    
    // upload external training absent photo
    private function UploadPhotoExternal($file, $name) {
        if (isset($file) && isset($name)) {
            $dataToPost = [
                'nama' => $name
            ];
            
            $toArray = explode(',', $name);
            if (count($toArray) > 1) {
                $dataToPost['dir'] = $toArray[0];
                $dataToPost['nama'] = $toArray[1];
            }
            
            $url = "https://t4tadmin.kolaborasikproject.com/distributions/upload.php";
            $response = Http::attach('image', file_get_contents($file), 'gambar.' . $file->extension())
                ->post($url, $dataToPost);
            
            $content = $response->json();
            
            if ($content['code'] == 200) {
                return $content['data']['new_name'];
            } else return false;
        } else return false;
        
    }
}