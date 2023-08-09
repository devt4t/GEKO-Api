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

class Monitoring2Controller extends Controller
{
    public function ShowTotalTreesGeneral(Request $req) {
        $validator = Validator::make($req->all(), [
            'program_year' => 'required',
            'category' => 'required|in:KAYU,MPTS',
            'mu_no' => 'required|exists:managementunits,mu_no',
            'type' => 'required|in:general,category'
        ]);
        if($validator->fails()) return response()->json($validator->errors()->first(), 400);
        else $py = $req->program_year;
        
        // get tree code category
        if ($req->category == 'KAYU') $tree_code = DB::table('trees')->where('tree_category', 'Pohon_Kayu')->pluck('tree_code');
        else if ($req->category == 'MPTS') $tree_code = DB::table('trees')->where('tree_category', 'Pohon_Buah')->pluck('tree_code');
        else $tree_code = [];
        
        // get ff no per-MU
        $ff_no = DB::table('field_facilitators')->where('mu_no', $req->mu_no)->pluck('ff_no');
        // get monitoring no by list ff no per-MU
        $mon_no = DB::table('monitorings')->whereIn('user_id', $ff_no)->where(['planting_year' => $py, 'is_validate' => 2, 'is_dell' => 0])->pluck('monitoring_no');
        $mon_no_all = DB::table('monitorings')->whereIn('user_id', $ff_no)->where(['is_validate' => 2, 'is_dell' => 0])->pluck('monitoring_no');
        // get trees 
        $trees = DB::table('monitoring_details');
        if ($req->type == 'category') {
            $trees = $trees
                ->leftJoin('trees', 'trees.tree_code', 'monitoring_details.tree_code')
                ->select(
                    'monitoring_details.tree_code',
                    'trees.tree_name',
                    DB::raw('SUM(monitoring_details.qty) as qty')
                );
        }
        $trees = $trees->where([
                'monitoring_details.status' => 'sudah_ditanam', 
                'monitoring_details.condition' => 'hidup'
            ])
            ->whereIn('monitoring_details.monitoring_no', $mon_no_all)
            ->whereIn('monitoring_details.tree_code', $tree_code)
            ->orderBy('qty', 'DESC');
        
        if ($req->type == 'general') {
            $trees = $trees->sum('qty');
            $treesPy = DB::table('monitoring_details')
                ->where([
                    'monitoring_details.status' => 'sudah_ditanam', 
                    'monitoring_details.condition' => 'hidup'
                ])
                ->whereIn('monitoring_details.monitoring_no', $mon_no)
                ->whereIn('monitoring_details.tree_code', $tree_code)
                ->sum('qty');
        
            $rslt = (object)[
                'data' => (object)[
                    'qty' => $trees,
                    'qty_py' => $treesPy
                ]
            ];
        }
        else {
            $trees = $trees->groupBy('tree_code')->get();
            foreach ($trees as $tree) {
                $tree->qty_py = DB::table('monitoring_details')->where([
                        'monitoring_details.status' => 'sudah_ditanam', 
                        'monitoring_details.condition' => 'hidup',
                        'tree_code' => $tree->tree_code
                    ])
                    ->whereIn('monitoring_details.monitoring_no', $mon_no)
                    ->sum('qty');
            }
        
            $rslt = (object)[
                'total' => count($trees),
                'data' => $trees
            ];
        }
        return response()->json($rslt, 200);
    }
    
    public function ShowListLandPerTreeCode(Request $req) {
        $validator = Validator::make($req->all(), [
            'program_year' => 'required',
            'tree_code' => 'required|exists:monitoring_details,tree_code',
            'mu_no' => 'required|exists:managementunits,mu_no'
        ]);
        if($validator->fails()) return response()->json($validator->errors()->first(), 400);
        else $py = $req->program_year;
        
        // get trees detail
        $tree = DB::table('trees')->where('tree_code', $req->tree_code)->first();
        // get ff no per-MU
        $ff_no = DB::table('field_facilitators')->where('mu_no', $req->mu_no)->pluck('ff_no');
        // get monitoring no by list ff no per-MU
        $query = DB::table('monitoring_details')
            ->join('monitorings', 'monitorings.monitoring_no', 'monitoring_details.monitoring_no')
            ->join('field_facilitators', 'field_facilitators.ff_no', 'monitorings.user_id')
            ->join('employees', 'employees.nik', 'field_facilitators.fc_no')
            ->join('farmers', 'farmers.farmer_no', 'monitorings.farmer_no')
            ->join('trees', 'trees.tree_code', 'monitoring_details.tree_code')
            ->select(
                'monitorings.monitoring_no',
                'employees.name as fc_name',
                'monitorings.user_id as ff_no',
                'field_facilitators.name as ff_name',
                'monitorings.farmer_no',
                'farmers.name as farmer_name',
                'monitorings.planting_date',
                'monitorings.lahan_no',
                'monitoring_details.tree_code',
                'trees.tree_name',
                'monitoring_details.qty'
            )
            ->whereIn('monitorings.user_id', $ff_no)
            ->where([
                'monitorings.planting_year' => $py, 
                'monitorings.is_validate' => 2, 
                'monitorings.is_dell' => 0,
                'monitoring_details.tree_code' => $req->tree_code,
                'monitoring_details.status' => 'sudah_ditanam', 
                'monitoring_details.condition' => 'hidup'
            ])->orderBy('monitoring_details.qty', 'DESC');
            
        $list = $query->get();
        $sumTrees = $query->sum('qty');
        
        $rslt = (object)[
            'mu' => DB::table('managementunits')->where('mu_no', $req->mu_no)->first()->name ?? '-',
            'program_year' => $req->program_year,
            'tree' => (object)[
                'code' => $req->tree_code,
                'name' => $tree->tree_name ?? '-',
                'category' => $tree->tree_category ? ($tree->tree_category == 'Pohon_Kayu' ? 'KAYU' : 'MPTS') : '-',
                'sum_amount' => $sumTrees
            ],
            'list' => [
                'total' => count($list),
                'data' => $list
            ],
            // 'data_all' => $data_all,
        ];
        return response()->json($rslt, 200);
    }
}
