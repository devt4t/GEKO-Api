<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;

use App\DriverDetail;
use App\TruckDetail;
use App\DistributionTransport;

class TransportationController extends Controller
{
//DRIVER MODULE
    public function GetDriver(Request $request)
    {
        $GetDriver = DriverDetail::select('driver_details.id',
                        'driver_details.nik',
                        'driver_details.program_year',
                        'driver_details.name',
                        'driver_details.address',
                        'driver_details.photo',
                        'driver_details.created_by',
                        'driver_details.updated_by',
                        'driver_details.created_by')
                ->where('is_dell', '=', 0)
                ->get();
        
        $rslt =  $this->ResultReturn(200, 'success', $GetDriver);
        return response()->json($rslt, 200);
    }
    
    public function GetDetailDriver(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nik' => 'required|exists:driver_details'
        ]);
        
        if($validator->fails()) {
            $rslt = $this->ResultReturn(400, $validator->errors()->first(),
            $validator->errors()->first());
            return response()->json($rslt, 400);
        }
        
        $driver = $request->nik;
        
        $getDetailDriver = DriverDetail::select('driver_details.id',
                            'driver_details.nik',
                            'driver_details.program_year',
                            'driver_details.name',
                            'driver_details.address',
                            'driver_details.photo',
                            'driver_details.created_by',
                            'driver_details.updated_by',
                            'driver_details.created_by')
                    ->where('is_dell', '=', 0)
                    ->where('nik', '=', $driver)
                    ->first();
                    
        $rslt =  $this->ResultReturn(200, 'success', $getDetailDriver);
        return response()->json($rslt, 200);
    }
    
    public function AddDriver(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nik' => 'required|unique:driver_details',
            'name' => 'required',
        ]);
        
        if($validator->fails()) {
            $rslt = $this->ResultReturn(400, $validator->errors()->first(),
            $validator->errors()->first());
            return response()->json($rslt, 400);
        }
        
        DriverDetail::create([
            'nik' => $request->nik,
            'name' => $request->name,
            'program_year' => $request->program_year,
            'address' => $request->address,
            'photo' => $request->photo,
            'is_dell' => '0',
            'created_by' => $request->user_id,
            'updated_by' => '-',
            'deleted_by' => '-',
            'created_at' => Carbon::now()
        ]);
        
        $rslt = $this->ResultReturn(200, 'success', 'success');
        return response()->json($rslt, 200);
    }
    
    public function UpdateDriver(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nik' => 'required|exists:driver_details',
            'name' => 'required',
        ]);
        
        if($validator->fails()) {
            $rslt = $this->ResultReturn(400, $validator->errors()->first(),
            $validator->errors()->first());
            return response()->json($rslt, 400);
        }
        
        DriverDetail::where('nik', '=', $request->nik)->update([
            'nik' => $request->nik,
            'name' => $request->name,
            'program_year' => $request->program_year,
            'address' => $request->address,
            'photo' => $request->photo,
            'updated_by' => $request->user_id,
            'updated_at' => Carbon::now()
        ]);
        
        $rslt =  $this->ResultReturn(200, 'success', 'success');
        return response()->json($rslt, 200); 
    }
    
    public function DeleteDriver(Request $request)
    {
        $validator = Validator::make($request->all(), [    
            'id' => 'required|exists:driver_details,id',
            'deleted_by' =>'required|exists:employee,nik'
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        }
        
        DriverDetail::where('id', '=', $request->id)
            ->update([
                'deleted_by' => $request->user_id,
                'is_dell' => 1
            ]);
            
        $rslt =  $this->ResultReturn(200, 'success', 'success');
        return response()->json($rslt, 200);
    }

//TRUCK MODULE  
    public function GetTruck(Request $request)
    {
        $GetTruck = TruckDetail::select('truck_details.id',
                        'truck_details.plat_no',
                        'truck_details.program_year',
                        'truck_details.status',
                        'truck_details.type',
                        'truck_details.min_capacity',
                        'truck_details.max_capacity',
                        'truck_details.nursery',
                        'truck_details.active_date',
                        'truck_details.is_active',
                        'truck_details.created_by',
                        'truck_details.is_dell',
                        'truck_details.created_at')
                ->where('is_dell', '=', 0)
                ->get();
        
        $rslt =  $this->ResultReturn(200, 'success', $GetTruck);
        return response()->json($rslt, 200);
    }
    
    public function GetDetailTruck(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'plat_no' => 'required|exists:truck_details'
        ]);
        
        if($validator->fails()) {
            $rslt = $this->ResultReturn(400, $validator->errors()->first(),
            $validator->errors()->first());
            return response()->json($rslt, 400);
        }
        
        $plat_no = $request->plat_no;
        
        $getDetailTruck = TruckDetail::select('truck_details.id',
                        'truck_details.plat_no',
                        'truck_details.program_year',
                        'truck_details.status',
                        'truck_details.type',
                        'truck_details.min_capacity',
                        'truck_details.max_capacity',
                        'truck_details.nursery',
                        'truck_details.active_date',
                        'truck_details.is_active',
                        'truck_details.created_by',
                        'truck_details.is_dell',
                        'truck_details.created_at')
                ->where('is_dell', '=', 0)
                ->where('plat_no', '=', $plat_no)
                ->first();
        
        $rslt =  $this->ResultReturn(200, 'success', $getDetailTruck);
        return response()->json($rslt, 200);
    }
    
    public function AddTruck(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'plat_no' => 'required|unique:truck_details',
            'status' => 'required',
            'min_capacity' => 'required',
            'max_capacity' => 'required'
        ]);
        
        if($validator->fails()) {
            $rslt = $this->ResultReturn(400, $validator->errors()->first(),
            $validator->errors()->first());
            return response()->json($rslt, 400);
        }
        
        TruckDetail::create([
            'plat_no' => $request->plat_no,
            'program_year' => $request->program_year,
            'status' => $request->status,
            'is_dell' => '0',
            'type' => $request->type,
            'min_capacity' => $request->min_capacity,
            'max_capacity' => $request->max_capacity,
            'nursery' => $request->nursery,
            'active_date' => $request->active_date,
            'is_active' => $request->is_active,
            'created_by' => $request->user_id,
            'updated_by' => '-',
            'deleted_by' => '-',
            'created_at' => Carbon::now(),
        ]);
        
        $rslt = $this->ResultReturn(200, 'success', 'success');
        return response()->json($rslt, 200);
    }
    
    public function UpdateTruck(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'plat_no' => 'required|exists:truck_details',
            'status' => 'required',
            'min_capacity' => 'required',
            'max_capacity' => 'required'
        ]);
        
        if($validator->fails()) {
            $rslt = $this->ResultReturn(400, $validator->errors()->first(),
            $validator->errors()->first());
            return response()->json($rslt, 400);
        }
        
        TruckDetail::where('plat_no', '=', $request->plat_no)->update([
            'plat_no' => $request->plat_no,
            'program_year' => $request->program_year,
            'status' => $request->status,
            'type' => $request->type,
            'min_capacity' => $request->min_capacity,
            'max_capacity' => $request->max_capacity,
            'active_date' => $request->active_date,
            'is_active' => $request->is_active,
            'nursery' => $request->nursery,
            'created_by' => $request->user_id,
            'updated_by' => $request->user_id,
            'updated_at' => Carbon::now(),
        ]);
        
        $rslt = $this->ResultReturn(200, 'success', 'success');
        return response()->json($rslt, 200);
    }
    
    public function DeleteTruck(Request $request)
    {
        $validator = Validator::make($request->all(), [    
            'id' => 'required|exists:truck_details,id',
            'deleted_by' =>'required|exists:employee,nik'
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        }
        
        TruckDetail::where('id', '=', $request->id)
            ->update([
                'deleted_by' => $request->user_id,
                'is_dell' => 1
            ]);
            
        $rslt =  $this->ResultReturn(200, 'success', 'success');
        return response()->json($rslt, 200);
    }
    
//TRANSPORTATION MODULE
    public function GetDistributionTransportationDetail(Request $request)
    {
        $ff = $request->ff_no;
        $py = $request->program_year;
        $getmou = $request->mou_no;

        if($getmou){$mou='%'.$getmou.'%';}
        else{$mou='%%';}
        
        $GetTrans = DistributionTransport::select('distribution_transports.id',
                        'distribution_transports.transport_no',
                        'distribution_transports.ff_no',
                        'distribution_transports.program_year',
                        'distribution_transports.plat_no',
                        'distribution_transports.nik',
                        'distribution_transports.distribution_time',
                        'distribution_transports.village',
                        'distribution_transports.mu_no',
                        'distribution_transports.labels',
                        'distribution_transports.total_seedlings_loaded',
                        'distribution_transports.total_seedlings_distributed',
                        'distribution_transports.loaded_by',
                        'distribution_transports.created_at')
                ->where('is_dell', '=', 0)
                ->where('distribution_transports.ff_no', '=', $ff_no)
                ->where('distribution_transports.program_year', '=', $program_year)
                ->where('distribution_transports.mou_no', 'LIKE', $mou)
                ->get();
        
        $rslt =  $this->ResultReturn(200, 'success', $GetTrans);
        return response()->json($rslt, 200);
    }
    
    public function SaveDistributionTransport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transport_no' => 'required|unique:driver_details',
            'program_year' => 'required',
        ]);
        
        if($validator->fails()) {
            $rslt = $this->ResultReturn(400, $validator->errors()->first(),
            $validator->errors()->first());
            return response()->json($rslt, 400);
        }
        
        DistributionTransport::create([
            'transport_no' => DistributionTransport::Maxno(),
            'ff_no' => $request->ff_no,
            'program_year' => $request->program_year,
            'plat_no' => $request->plat_no,
            'nik' => $request->nik,
            'distribution_time' => $request->distribution_time,
            'village' => $request->village,
            'mu_no' => $request->mu_no,
            'target_area' => $request->target_area,
            'mou_no' => $request->mou_no,
            'labels' => $request->labels,
            'total_seedlings_loaded' => $request->total_seedlings_loaded,
            'total_seedlings_distributed' => $request->total_seedlings_distributed,
            'loaded_by' => $request->user_id,
            'updated_by' => '-',
            'created_at' => Carbon::now(),
            'updated_at' => '-'
        ]);
        
        $rslt = $this->ResultReturn(200, 'success', 'success');
        return response()->json($rslt, 200);
    }
}