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
use Illuminate\Http\Request;

class TreesPlantedController extends Controller {
    public function index() {
        $datas = Monitoring::where('is_dell', 0)
            ->where('is_validate', 1);
        
        $datas = $datas->sum(DB::raw('qty_kayu + qty_mpts + qty_crops'));
        
        // response
        $rslt =  $this->ResultReturn(200, 'success', $datas);
        return response()->json($rslt, 200);
    }
}