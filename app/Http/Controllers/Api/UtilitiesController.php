<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DB;
use Carbon\Carbon;
use App\TaDesa;
use App\Desa;
use App\Kecamatan;
use App\Kabupaten;
use App\Province;
use App\ManagementUnit;
use App\TargetArea;
use App\Verification;
use App\Pekerjaan;
use App\Suku;
use App\MenuParent;
use App\MenuAccess;

class UtilitiesController extends Controller
{
    /**
     * @SWG\Get(
     *   path="/api/GetProvinceAdmin",
     *   tags={"Utilities"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Province Admin",
     *   operationId="GetProvinceAdmin",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
     * )
     */
    public function GetProvinceAdmin(Request $request){
        try{
            $GetProvince = Province::orderBy('name', 'ASC')->get();
            if(count($GetProvince)!=0){
                $rslt =  $this->ResultReturn(200, 'success', $GetProvince);
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
     *   path="/api/GetKabupatenAdmin",
     *   tags={"Utilities"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Kabupaten Admin",
     *   operationId="GetKabupatenAdmin",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
     * )
     */
    public function GetKabupatenAdmin(Request $request){
        try{
            $GetKabupaten =  DB::table('kabupatens')->select('kabupatens.id as id', 'kabupatens.kabupaten_no as kabupaten_no',
                            'kabupatens.name as namaKabupaten','provinces.province_code as province_code','provinces.name as namaProvinsi','kabupatens.kab_code as kab_code')
                            ->leftjoin('provinces', 'provinces.province_code', '=', 'kabupatens.province_code')
                            ->orderBy('kabupatens.name', 'ASC')->get();
            if(count($GetKabupaten)!=0){
                $rslt =  $this->ResultReturn(200, 'success', $GetKabupaten);
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
     *   path="/api/GetKecamatanAdmin",
     *   tags={"Utilities"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Kecamatan Admin",
     *   operationId="GetKecamatanAdmin",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
     * )
     */
    public function GetKecamatanAdmin(Request $request){
        try{
            $GetKecamatan = DB::table('kecamatans')->select('kecamatans.id as id', 'kabupatens.kabupaten_no as kabupaten_no',
            'kabupatens.name as namaKabupaten','kecamatans.kode_kecamatan as kode_kecamatan','kecamatans.name as namaKecamatan')
            ->leftjoin('kabupatens', 'kabupatens.kabupaten_no', '=', 'kecamatans.kabupaten_no')
            ->orderBy('kecamatans.name', 'ASC')->get();
            if(count($GetKecamatan)!=0){
                $rslt =  $this->ResultReturn(200, 'success', $GetKecamatan);
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
     *   path="/api/GetDesaAdmin",
     *   tags={"Utilities"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Desa Admin",
     *   operationId="GetDesaAdmin",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
     * )
     */
    public function GetDesaAdmin(Request $request)
    {
        // $GetDesaAdmin = DB::table('desas')->select('desas.id as id', 'desas.kode_desa as kode_desa', 'desas.name as namaDesa','desas.name as name','kecamatans.kode_kecamatan as kode_kecamatan','kecamatans.name as namaKecamatan',
        //     'target_areas.area_code as area_code','target_areas.name as namaTa','desas.post_code',DB::raw('CONCAT(desas.name, ", ", kecamatans.name) AS namaDesaKecamatan'))
        //     ->leftjoin('kecamatans', 'kecamatans.kode_kecamatan', '=', 'desas.kode_kecamatan')
        //     ->leftjoin('target_areas', 'target_areas.area_code', '=', 'desas.kode_ta')
        //     ->orderBy('desas.name', 'ASC')->get();
            
        // if(count($GetDesaAdmin)!=0){
        //     foreach ($GetDesaAdmin as $data) {
        //         $data->ta_desas = TaDesa::where('kode_desa', $data->kode_desa)->get();
        //     }
        //     $rslt =  $this->ResultReturn(200, 'success', $GetDesaAdmin);
        //     return response()->json($rslt, 200);  
        // }
        // else{
        //     $rslt =  $this->ResultReturn(404, 'doesnt match data', 'doesnt match data');
        //     return response()->json($rslt, 404);
        // }
        
        $request->program_year;
        $GetDesaAdminDev = DB::table('desas')->select('desas.id as id', 'desas.kode_desa as kode_desa', 'desas.name as namaDesa','desas.name as name','desas.program_year as program_year','desas.status as status','kecamatans.kode_kecamatan as kode_kecamatan','kecamatans.name as namaKecamatan', 'target_areas.area_code as area_code','target_areas.name as namaTa','desas.post_code',DB::raw('CONCAT(desas.name, ", ", kecamatans.name) AS namaDesaKecamatan'))
            ->leftjoin('kecamatans', 'kecamatans.kode_kecamatan', '=', 'desas.kode_kecamatan')
            ->leftjoin('target_areas', 'target_areas.area_code', '=', 'desas.kode_ta');
        
        if ($request->program_year) if ($request->program_year != 'All Data') 
        {
            if($request->program_year != "")
            {$GetDesaAdminDev = $GetDesaAdminDev->where('desas.program_year', 'LIKE', "%$request->program_year%");}
            else
            {$GetDesaAdminDev -> get();}
        }
        
        $GetDesaAdminDev = $GetDesaAdminDev ->get();
        if(count($GetDesaAdminDev)!=0){
            foreach ($GetDesaAdminDev as $data) {
                $data->ta_desas = TaDesa::where('kode_desa', $data->kode_desa)->get();
            }
            $rslt =  $this->ResultReturn(200, 'success', $GetDesaAdminDev);
            return response()->json($rslt, 200);  
        }
        else{
            $rslt =  $this->ResultReturn(404, 'doesnt match data', 'doesnt match data');
            return response()->json($rslt, 404);
        }
    }
    
    public function GetDesaAdminDev(Request $request)
    {
        $request->program_year;
        $GetDesaAdminDev = DB::table('desas')->select('desas.id as id', 'desas.kode_desa as kode_desa', 'desas.name as namaDesa','desas.name as name','desas.program_year as program_year','desas.status as status','kecamatans.kode_kecamatan as kode_kecamatan','kecamatans.name as namaKecamatan', 'target_areas.area_code as area_code','target_areas.name as namaTa','desas.post_code',DB::raw('CONCAT(desas.name, ", ", kecamatans.name) AS namaDesaKecamatan'))
            ->leftjoin('kecamatans', 'kecamatans.kode_kecamatan', '=', 'desas.kode_kecamatan')
            ->leftjoin('target_areas', 'target_areas.area_code', '=', 'desas.kode_ta');
        
        if ($request->program_year) if ($request->program_year != 'All Data') 
        {
            if($request->program_year != "")
            {$GetDesaAdminDev = $GetDesaAdminDev->where('desas.program_year', 'LIKE', "%$request->program_year%");}
            else
            {$GetDesaAdminDev -> get();}
        }
        
        $GetDesaAdminDev = $GetDesaAdminDev ->get();
        if(count($GetDesaAdminDev)!=0){
            foreach ($GetDesaAdminDev as $data) {
                $data->ta_desas = TaDesa::where('kode_desa', $data->kode_desa)->get();
            }
            $rslt =  $this->ResultReturn(200, 'success', $GetDesaAdminDev);
            return response()->json($rslt, 200);  
        }
        else{
            $rslt =  $this->ResultReturn(404, 'doesnt match data', 'doesnt match data');
            return response()->json($rslt, 404);
        }
    }
    
    /**
     * @SWG\Get(
     *   path="/api/GetManagementUnitAdmin",
     *   tags={"Utilities"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Management Unit Admin",
     *   operationId="GetManagementUnitAdmin",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
     * )
     */
    public function GetManagementUnitAdmin(Request $request){
        try{
            $GetManagementUnit = ManagementUnit::orderBy('created_at', 'desc');
            if ($request->program_year) if ($request->program_year != 'All Data') {
                $GetManagementUnit = $GetManagementUnit->where('program_year', 'LIKE', "%$request->program_year%");
            }
            $GetManagementUnit = $GetManagementUnit->get();
            if(count($GetManagementUnit)!=0){
                $rslt =  $this->ResultReturn(200, 'success', $GetManagementUnit);
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
     *   path="/api/GetTargetAreaAdmin",
     *   tags={"Utilities"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Target Area Admin",
     *   operationId="GetTargetAreaAdmin",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
     * )
     */
    public function GetTargetAreaAdmin(Request $request){
        try{
            $GetTargetArea = DB::table('target_areas')->select('target_areas.id as id', 'target_areas.area_code as area_code',
            'target_areas.name as namaTa', 'target_areas.name','target_areas.luas as luas', 'target_areas.program_year as program_year', 'target_areas.active as active','managementunits.mu_no as mu_no',
            'managementunits.name as namaMu','target_areas.kabupaten_no as kabupaten_no','kabupatens.name as namaKabupaten',
            'target_areas.province_code as province_code', 'provinces.name as namaProvince')
            ->leftjoin('managementunits', 'managementunits.mu_no', '=', 'target_areas.mu_no')
            ->leftjoin('kabupatens', 'kabupatens.kabupaten_no', '=', 'target_areas.kabupaten_no')
            ->leftjoin('provinces', 'provinces.province_code', '=', 'target_areas.province_code')
            ->orderBy('target_areas.created_at', 'DESC');
            if ($request->program_year) if ($request->program_year != 'Semua') {
                $GetTargetArea = $GetTargetArea->where('target_areas.program_year', 'like', "%$request->program_year%");
            }
            $GetTargetArea = $GetTargetArea->get();
            if(count($GetTargetArea)!=0){
                $rslt =  $this->ResultReturn(200, 'success', $GetTargetArea);
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
     *   path="/api/GetProvince",
     *   tags={"Utilities"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Province",
     *   operationId="GetProvince",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
     * )
     */
    public function GetProvince(Request $request){
        try{
            $GetProvince = Province::get();
            if(count($GetProvince)!=0){
                $rslt =  $this->ResultReturn(200, 'success', $GetProvince);
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
     *   path="/api/GetKabupaten",
     *   tags={"Utilities"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Kabupaten",
     *   operationId="GetKabupaten",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
      *      @SWG\Parameter(name="province_code",in="query", type="string"),
     * )
     */
    public function GetKabupaten(Request $request){
        try{
            $GetKabupaten = Kabupaten::where('province_code','=',$request->province_code)->get();
            if(count($GetKabupaten)!=0){
                $rslt =  $this->ResultReturn(200, 'success', $GetKabupaten);
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
     *   path="/api/GetKecamatan",
     *   tags={"Utilities"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Kecamatan",
     *   operationId="GetKecamatan",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
      *      @SWG\Parameter(name="kabupaten_no",in="query", type="string"),
     * )
     */
    public function GetKecamatan(Request $request){
        try{
            $GetKecamatan = Kecamatan::where('kabupaten_no','=',$request->kabupaten_no)->get();
            if(count($GetKecamatan)!=0){
                $rslt =  $this->ResultReturn(200, 'success', $GetKecamatan);
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
     *   path="/api/GetDesa",
     *   tags={"Utilities"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Desa",
     *   operationId="GetDesa",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
      *      @SWG\Parameter(name="kode_kecamatan",in="query", type="string"),
      *     @SWG\Parameter(name="kode_ta",in="query", type="string"),
     * )
     */
    public function GetDesa(Request $request){
        if($request->kode_kecamatan){
          $GetDesa = Desa::where('kode_kecamatan','=',$request->kode_kecamatan)->get();  
        }
        if($request->kode_ta){
            // $GetDesa = Desa::where('kode_ta','=',$request->kode_ta)->get(); 
            $getDesaList = Desa::select('kode_desa','name','kode_kecamatan','post_code');
            if ($request->program_year) {
                if ($request->program_year != 'Semua') {
                    $list_code_desa = DB::table('ta_desas')->where([
                            'area_code' => $request->kode_ta,
                            ['program_year', 'like', "%$request->program_year%"]
                        ])->pluck('kode_desa');
                    $getDesaList = $getDesaList->whereIn('kode_desa', $list_code_desa);
                } else {$getDesaList = $getDesaList->where('kode_ta','=',$request->kode_ta);}
            } else {$getDesaList = $getDesaList->where('kode_ta','=',$request->kode_ta);}
            
            $getDesaList = $getDesaList->get();
            
            $dataval = [];
            $GetDesa=array(); 
            // var_dump($getDesaList);
            if(count($getDesaList)!=0){
                foreach ($getDesaList as $val) { 
                    $getKec = Kecamatan::select('kode_kecamatan','name','kabupaten_no')->where('kode_kecamatan','=',$val->kode_kecamatan)->first();
                    $dataval = ['kode_desa'=>$val->kode_desa,'name'=>$val->name,'kode_kecamatan'=>$val->kode_kecamatan, 'namaKecamatan'=>$getKec->name,'namaDesaKecamatan'=>$val->name." - ".$getKec->name,'post_code'=>$val->post_code, 'program_year'=>$val->program_year];
                    array_push($GetDesa, $dataval);
                }  
            }    
        }
        
        if(count($GetDesa)!=0){
            $rslt =  $this->ResultReturn(200, 'success', $GetDesa);
            return response()->json($rslt, 200);  
        }
        else{
            $rslt =  $this->ResultReturn(404, 'doesnt match data', 'doesnt match data');
            return response()->json($rslt, 404);
        } 
    }

    /**
     * @SWG\Post(
     *   path="/api/AddProvince",
	 *   tags={"Utilities"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Add Province",
     *   operationId="AddProvince",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Add Province",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="province_code", type="string", example="JT"),
     *              @SWG\Property(property="name", type="string", example="Jawa Tengah")
     *          ),
     *      )
     * )
     *
     */
    public function AddProvince(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'province_code' => 'required|string|max:255|unique:provinces',
                'name' => 'required|string|max:255'
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
    
            Province::create([
                'province_code' => $request->province_code,
                'name' => $request->name,
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
     *   path="/api/AddKabupaten",
	 *   tags={"Utilities"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Add Kabupaten",
     *   operationId="AddKabupaten",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Add Kabupaten",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="kabupaten_no", type="string", example="33.04"),
     *              @SWG\Property(property="kab_code", type="string", example="04"),
     *              @SWG\Property(property="province_code", type="string", example="JT"),
     *              @SWG\Property(property="name", type="string", example="Kab. Banjarnegara")
     *          ),
     *      )
     * )
     *
     */
    public function AddKabupaten(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'kabupaten_no' => 'required|string|max:255|unique:kabupatens',
                'province_code' => 'required|string|max:255',
                'name' => 'required|string|max:255',
                'kab_code' => 'required|string|max:255|unique:kabupatens',
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
    
            Kabupaten::create([
                'kabupaten_no' => $request->kabupaten_no,
                'province_code' => $request->province_code,
                'name' => $request->name,
                'kab_code' => $request->kab_code,
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
     *   path="/api/AddKecamatan",
	 *   tags={"Utilities"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Add Kecamatan",
     *   operationId="AddKecamatan",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Add Kecamatan",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="kabupaten_no", type="string", example="33.04"),
     *              @SWG\Property(property="kode_kecamatan", type="string", example="33.04.09"),
     *              @SWG\Property(property="name", type="string", example="Banjarnegara")
     *          ),
     *      )
     * )
     *
     */
    public function AddKecamatan(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'kabupaten_no' => 'required|string|max:255',
                'name' => 'required|string|max:255',
                'kode_kecamatan' => 'required|string|max:255|unique:kecamatans',
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
    
            Kecamatan::create([
                'kabupaten_no' => $request->kabupaten_no,
                'name' => $request->name,
                'kode_kecamatan' => $request->kode_kecamatan,
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
     *   path="/api/AddDesa",
	 *   tags={"Utilities"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Add Desa",
     *   operationId="AddDesa",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Add Desa",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="kode_desa", type="string", example="33.04.09.02"),
     *              @SWG\Property(property="kode_kecamatan", type="string", example="33.04.09"),
     *              @SWG\Property(property="kode_ta", type="string", example="120200100000"),
     *              @SWG\Property(property="post_code", type="string", example="576060"),
     *              @SWG\Property(property="name", type="string", example="Desa Banjar")
     *          ),
     *      )
     * )
     *
     */
    public function AddDesa(Request $request){
        $validator = Validator::make($request->all(), [
            'kode_desa' => 'required|string|max:255|unique:desas',
            'name' => 'required|string|max:255',
            'kode_kecamatan' => 'required|max:255',
            'post_code' => 'required|max:255',
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        }

        Desa::create([
            'kode_desa' => $request->kode_desa,
            'name' => $request->name,
            'kode_kecamatan' => $request->kode_kecamatan,
            'kode_ta' => $request->kode_ta,
            'post_code' => $request->post_code,
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()
        ]);
        if ($request->ta_desas) {
            foreach ($request->ta_desas as $TD) {
                TaDesa::create([
                    'kode_desa' => $request->kode_desa,
                    'area_code' => $TD['area_code'],
                    'program_year' => $TD['program_year']
                ]);
            }
        }
        $rslt =  $this->ResultReturn(200, 'success', 'success');
        return response()->json($rslt, 200);
    }

    /**
     * @SWG\Post(
     *   path="/api/UpdateProvince",
	 *   tags={"Utilities"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Update Province",
     *   operationId="UpdateProvince",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Update Province",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="id", type="integer", example=1),
     *              @SWG\Property(property="province_code", type="string", example="JT"),
     *              @SWG\Property(property="name", type="string", example="Jawa Tengah")
     *          ),
     *      )
     * )
     *
     */
    public function UpdateProvince(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer|max:255',
                'province_code' => 'required|string|max:255',
                'name' => 'required|string|max:255'
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
    
            Province::where('id', '=', $request->id)
            ->update([
                'province_code' => $request->province_code,
                'name' => $request->name,
                'updated_at'=>Carbon::now()
            ]);
            Kabupaten::where('province_code', '=', $request->province_code)
            ->update([
                'province_code' => $request->province_code,
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
     *   path="/api/UpdateKabupaten",
	 *   tags={"Utilities"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Update Kabupaten",
     *   operationId="UpdateKabupaten",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Update Kabupaten",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="id", type="integer", example=1),
     *              @SWG\Property(property="kabupaten_no", type="string", example="33.04"),
     *              @SWG\Property(property="kab_code", type="string", example="04"),
     *              @SWG\Property(property="province_code", type="string", example="JT"),
     *              @SWG\Property(property="name", type="string", example="Kab. Banjarnegara")
     *          ),
     *      )
     * )
     *
     */
    public function UpdateKabupaten(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer|max:255',
                'kabupaten_no' => 'required|string|max:255',
                'province_code' => 'required|string|max:255',
                'name' => 'required|string|max:255',
                'kab_code' => 'required|string|max:255',
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
    
            Kabupaten::where('id', '=', $request->id)
            ->update([
                'kabupaten_no' => $request->kabupaten_no,
                'province_code' => $request->province_code,
                'name' => $request->name,
                'kab_code' => $request->kab_code,
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
     *   path="/api/UpdateKecamatan",
	 *   tags={"Utilities"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Update Kecamatan",
     *   operationId="UpdateKecamatan",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="UpdateKecamatan",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="id", type="integer", example=1),
     *              @SWG\Property(property="kabupaten_no", type="string", example="33.04"),
     *              @SWG\Property(property="kode_kecamatan", type="string", example="33.04.09"),
     *              @SWG\Property(property="name", type="string", example="Banjarnegara")
     *          ),
     *      )
     * )
     *
     */
    public function UpdateKecamatan(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer|max:255',
                'kabupaten_no' => 'required|string|max:255',
                'name' => 'required|string|max:255',
                'kode_kecamatan' => 'required|string|max:255',
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
    
            Kecamatan::where('id', '=', $request->id)
            ->update([
                'kabupaten_no' => $request->kabupaten_no,
                'name' => $request->name,
                'kode_kecamatan' => $request->kode_kecamatan,
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
     *   path="/api/UpdateDesa",
	 *   tags={"Utilities"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Update Desa",
     *   operationId="UpdateDesa",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Update Desa",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="id", type="integer", example=1),
     *              @SWG\Property(property="kode_desa", type="string", example="33.04.09.02"),
     *              @SWG\Property(property="kode_kecamatan", type="string", example="33.04.09"),
     *              @SWG\Property(property="kode_ta", type="string", example="120200100000"),
     *              @SWG\Property(property="post_code", type="string", example="576060"),
     *              @SWG\Property(property="name", type="string", example="Desa Banjar")
     *          ),
     *      )
     * )
     *
     */
    public function UpdateDesa(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'kode_desa' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'kode_kecamatan' => 'required|string|max:255',
            'post_code' => 'required|max:255',
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        }
        
        Desa::where('id', '=', $request->id)
        ->update([
            'kode_desa' => $request->kode_desa,
            'name' => $request->name,
            'kode_kecamatan' => $request->kode_kecamatan,
            'kode_ta' => $request->kode_ta,
            'post_code' => $request->post_code,
            'updated_at'=>Carbon::now()
        ]);
        TaDesa::where('kode_desa', $request->kode_desa)->delete();
        if ($request->ta_desas) {
            foreach ($request->ta_desas as $TD) {
                TaDesa::create([
                    'kode_desa' => $request->kode_desa,
                    'area_code' => $TD['area_code'],
                    'program_year' => $TD['program_year']
                ]);
            }
        }
        $rslt =  $this->ResultReturn(200, 'success', 'success');
        return response()->json($rslt, 200);
    }

    /**
     * @SWG\Post(
     *   path="/api/DeleteProvince",
	 *   tags={"Utilities"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Delete Province",
     *   operationId="DeleteProvince",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Delete Province",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="id", type="integer", example=1)
     *          ),
     *      )
     * )
     *
     */
    public function DeleteProvince(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer|max:255'
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
    
            DB::table('provinces')->where('id', $request->id)->delete();
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200);
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }

    /**
     * @SWG\Post(
     *   path="/api/DeleteKabupaten",
	 *   tags={"Utilities"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Delete Kabupaten",
     *   operationId="DeleteKabupaten",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Delete Kabupaten",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="id", type="integer", example=1)
     *          ),
     *      )
     * )
     *
     */
    public function DeleteKabupaten(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer|max:255',
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
    
            DB::table('kabupatens')->where('id', $request->id)->delete();
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200);
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }

    /**
     * @SWG\Post(
     *   path="/api/DeleteKecamatan",
	 *   tags={"Utilities"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Delete Kecamatan",
     *   operationId="DeleteKecamatan",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Delete Kecamatan",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="id", type="integer", example=1)
     *          ),
     *      )
     * )
     *
     */
    public function DeleteKecamatan(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer|max:255'
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
    
            DB::table('kecamatans')->where('id', $request->id)->delete();
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200);
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }

    /**
     * @SWG\Post(
     *   path="/api/DeleteDesa",
	 *   tags={"Utilities"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Delete Desa",
     *   operationId="DeleteDesa",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Delete Desa",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="id", type="integer", example=1)
     *          ),
     *      )
     * )
     *
     */
    public function DeleteDesa(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer|max:255'
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
    
            DB::table('desas')->where('id', $request->id)->delete();
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200);
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }

    /**
     * @SWG\Get(
     *   path="/api/GetManagementUnit",
     *   tags={"Utilities"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Management Unit",
     *   operationId="GetManagementUnit",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
     * )
     */
    public function GetManagementUnit(Request $request){
        try{
            $GetManagementUnit = ManagementUnit::where('active', 1);
            if ($request->program_year) if ($request->program_year != 'Semua') {
                $GetManagementUnit = $GetManagementUnit->where('program_year', 'like', "%$request->program_year%");
            }
            $GetManagementUnit = $GetManagementUnit->get();
            if(count($GetManagementUnit)!=0){
                $rslt =  $this->ResultReturn(200, 'success', $GetManagementUnit);
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
     *   path="/api/GetTargetArea",
     *   tags={"Utilities"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Target Area",
     *   operationId="GetTargetArea",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
     *   @SWG\Parameter(name="mu_no",in="query", type="string"),
     * )
     */
    public function GetTargetArea(Request $request){
        try{
            $GetTargetArea = TargetArea::where('mu_no','=', $request->mu_no);
            if ($request->program_year) if ($request->program_year != 'Semua') {
                $GetTargetArea = $GetTargetArea->where('program_year', 'like', "%$request->program_year%");
            }
            $GetTargetArea = $GetTargetArea->get();
            if(count($GetTargetArea)!=0){
                $rslt =  $this->ResultReturn(200, 'success', $GetTargetArea);
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
     *   path="/api/AddManagementUnit",
	 *   tags={"Utilities"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Add Management Unit",
     *   operationId="AddManagementUnit",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Add Management Unit",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="name", type="string", example="Kebumen")
     *          ),
     *      )
     * )
     *
     */
    public function AddManagementUnit(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255'
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
    
            $getLastIdTargetArea = ManagementUnit::orderBy('mu_no','desc')->first(); 
            if($getLastIdTargetArea){
                $mu_no_temp = (int)$getLastIdTargetArea->mu_no + 1;
                $mu_no = '0'.$mu_no_temp;
            }else{
                $mu_no = 001;
            }
            // $getCountMU = ManagementUnit::count();
            // $mu_no = str_pad($getCountMU+1, 3, '0', STR_PAD_LEFT);

            ManagementUnit::create([
                'mu_no' => $mu_no,
                'name' => $request->name,
                'program_year' => $request->program_year,
                'active' => 1,
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
     *   path="/api/AddTargetArea",
	 *   tags={"Utilities"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Add Target Area",
     *   operationId="AddTargetArea",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Add Target Area",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="mu_no", type="string", example="014"),
     *               @SWG\Property(property="name", type="string", example="Bojong"),
     *               @SWG\Property(property="kabupaten_no", type="string", example="05"),
     *               @SWG\Property(property="luas", type="int", example="100.0"),
     *          ),
     *      )
     * )
     *
     */
    public function AddTargetArea(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'mu_no' => 'required|max:255',
                'name' => 'required|string|max:255',
                'kabupaten_no' => 'required|max:255',
                'luas' => 'required|max:255',
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
    
            $getLastIdTargetArea = TargetArea::where('mu_no','=',$request->mu_no)->orderBy('area_code','desc')->first(); 
            if($getLastIdTargetArea){
                $getNewId_temp = (int)substr($getLastIdTargetArea->area_code,-3) + 1;
                $getNewId = '0'.$getNewId_temp;
            }else{
                $getNewId = 001;
            }
            
            $getProvCode = Kabupaten::where('kabupaten_no','=',$request->kabupaten_no)->first();
            $area_code = $request->mu_no.str_pad($getNewId, 3, '0', STR_PAD_LEFT);

            TargetArea::create([
                'area_code' => $area_code,
                'mu_no' => $request->mu_no,
                'name' => $request->name,
                'kabupaten_no' => $getProvCode->kabupaten_no,
                'luas' => $request->luas,
                'program_year' => $request->program_year,
                'province_code' => $getProvCode->province_code,
                'active' => 1,
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
     *   path="/api/UpdateManagementUnit",
	 *   tags={"Utilities"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Update Management Unit",
     *   operationId="UpdateManagementUnit",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Update Management Unit",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="id", type="integer", example=1),
     *              @SWG\Property(property="name", type="string", example="Kebumen"),
     *              @SWG\Property(property="active", type="string", example=1)
     *          ),
     *      )
     * )
     *
     */
    public function UpdateManagementUnit(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'id' => 'required|max:255',
                'name' => 'required|string|max:255',
                'active' => 'required|max:255'
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors(), $validator->errors());
                return response()->json($rslt, 400);
            }

            ManagementUnit::where('id', '=', $request->id)
            ->update([
                'name' => $request->name,
                'active' => $request->active,
                'program_year' => $request->program_year,
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
     *   path="/api/UpdateTargetArea",
	 *   tags={"Utilities"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Update Target Area",
     *   operationId="UpdateTargetArea",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Update Target Area",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="id", type="integer", example=1),
     *              @SWG\Property(property="name", type="string", example="Bojong1"),
     *              @SWG\Property(property="mu_no", type="string", example="014"),
     *              @SWG\Property(property="kabupaten_no", type="string", example="05"),
     *              @SWG\Property(property="luas", type="int", example="100.0"),
     *              @SWG\Property(property="active", type="string", example=1)
     *          ),
     *      )
     * )
     *
     */
    public function UpdateTargetArea(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'id' => 'required|max:255',
                'name' => 'required|max:255',
                'mu_no' => 'required|max:255',
                'kabupaten_no' => 'required|max:255',
                'luas' => 'required|max:255',
                'active' => 'required|max:255'
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }

            $getAreaCodeTargetArea = TargetArea::where('id', '=', $request->id)->first();
            if($getAreaCodeTargetArea->mu_no != $request->mu_no){
                $getLastIdTargetArea = TargetArea::where('mu_no','=',$request->mu_no)->orderBy('area_code','desc')->first(); 
                if($getLastIdTargetArea){
                    $getNewId = (int)substr($getLastIdTargetArea->area_code,-3) + 1;
                }else{
                    $getNewId = 001;
                }
                $area_code = $request->mu_no.str_pad($getNewId, 3, '0', STR_PAD_LEFT);
            }
            else{
                $area_code = $getAreaCodeTargetArea->area_code;
            }            

            $getProvCode = Kabupaten::where('kabupaten_no','=',$request->kabupaten_no)->first();
            TargetArea::where('id', '=', $request->id)
            ->update([
                'area_code' => $area_code,
                'mu_no' => $request->mu_no,
                'name' => $request->name,
                'kabupaten_no' => $request->kabupaten_no,
                'luas' => $request->luas,
                'program_year' => $request->program_year,
                'province_code' => $getProvCode->province_code,
                'active' => $request->active,
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
     *   path="/api/DeleteManagementUnit",
	 *   tags={"Utilities"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Delete Management Unit",
     *   operationId="DeleteManagementUnit",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Delete Management Unit",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="id", type="string", example="1")
     *          ),
     *      )
     * )
     *
     */
    public function DeleteManagementUnit(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'id' => 'required|max:255'
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
    
            DB::table('managementunits')->where('id', $request->id)->delete();
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200);
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }
    /**
     * @SWG\Post(
     *   path="/api/DeleteTargetArea",
	 *   tags={"Utilities"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Delete Target Area",
     *   operationId="DeleteTargetArea",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Delete Target Area",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="id", type="string", example="1")
     *          ),
     *      )
     * )
     *
     */
    public function DeleteTargetArea(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'id' => 'required|max:255'
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
    
            DB::table('target_areas')->where('id', $request->id)->delete();
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200);
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }

    /**
     * @SWG\Get(
     *   path="/api/GetVerification",
     *   tags={"Utilities"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Verification",
     *   operationId="GetVerification",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
     * )
     */
    public function GetVerification(Request $request){
        try{
            $GetVerification = Verification::select('verification_code', 'type')->get();
            if(count($GetVerification)!=0){
                $rslt =  $this->ResultReturn(200, 'success', $GetVerification);
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
     *   path="/api/AddVerification",
	 *   tags={"Utilities"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Add Verification",
     *   operationId="AddVerification",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Add Verification",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="verification_code", type="int", example="1"),
     *              @SWG\Property(property="type", type="string", example="verification_fc/verification_um/verification_plan/verification_pm")
     *          ),
     *      )
     * )
     *
     */
    public function AddVerification(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'verification_code' => 'required|integer|max:255|unique:verifications',
                'type' => 'required|string|max:255'
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }

            Verification::create([
                'verification_code' => $request->verification_code,
                'type' => $request->type,
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
     *   path="/api/UpdateVerification",
	 *   tags={"Utilities"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Update Verification",
     *   operationId="UpdateVerification",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Update Verification",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="id", type="string", example="1"),
     *              @SWG\Property(property="verification_code", type="int", example="1"),
     *              @SWG\Property(property="type", type="enum", example="verification_fc/verification_um/verification_plan/verification_pm")
     *          ),
     *      )
     * )
     *
     */
    public function UpdateVerification(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer|max:255',
                'verification_code' => 'required|integer|max:255',
                'type' => 'required|string|max:255'
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }

            Verification::where('id', '=', $request->id)
            ->update([
                'verification_code' => $request->verification_code,
                'type' => $request->type,
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
     *   path="/api/DeleteVerification",
	 *   tags={"Utilities"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Delete Verification",
     *   operationId="DeleteVerification",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Delete Verification",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="id", type="integer", example="1")
     *          ),
     *      )
     * )
     *
     */
    public function DeleteVerification(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer|max:255'
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
    
            DB::table('verifications')->where('id', $request->id)->delete();
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200);
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }

    /**
     * @SWG\Get(
     *   path="/api/GetPekerjaan",
     *   tags={"Utilities"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Pekerjaan",
     *   operationId="GetPekerjaan",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
     * )
     */
    public function GetPekerjaan(Request $request){
        try{
            $GetPekerjaan = Pekerjaan::select('id','code', 'name')->get();
            if(count($GetPekerjaan)!=0){
                $rslt =  $this->ResultReturn(200, 'success', $GetPekerjaan);
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
     *   path="/api/GetSuku",
     *   tags={"Utilities"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Suku",
     *   operationId="GetSuku",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
     * )
     */
    public function GetSuku(Request $request){
        try{
            $GetSuku = Suku::select('id','code', 'name')->get();
            if(count($GetSuku)!=0){
                $rslt =  $this->ResultReturn(200, 'success', $GetSuku);
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
     *   path="/api/AddPekerjaan",
	 *   tags={"Utilities"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Add Pekerjaan",
     *   operationId="AddPekerjaan",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Add Pekerjaan",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="name", type="string", example="Guru")
     *          ),
     *      )
     * )
     *
     */
    public function AddPekerjaan(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:pekerjaan',
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }

            $code = str_replace(' ', '_', $request->name);

            Pekerjaan::create([
                'name' => $request->name,
                'code' => $code,
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
     *   path="/api/AddSuku",
	 *   tags={"Utilities"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Add Suku",
     *   operationId="AddSuku",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Add Suku",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="name", type="string", example="Jawa")
     *          ),
     *      )
     * )
     *
     */
    public function AddSuku(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:suku',
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }

            $code = str_replace(' ', '_', $request->name);

            Suku::create([
                'name' => $request->name,
                'code' => $code,
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
     *   path="/api/UpdatePekerjaan",
	 *   tags={"Utilities"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Update Pekerjaan",
     *   operationId="UpdatePekerjaan",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Update Pekerjaan",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="id", type="string", example="1"),
     *              @SWG\Property(property="name", type="string", example="Guru"),
     *             ),
     *      )
     * )
     *
     */
    public function UpdatePekerjaan(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer|max:255',
                'name' => 'required|string|max:255|unique:pekerjaan',
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }

            $code = str_replace(' ', '_', $request->name);

            Pekerjaan::where('id', '=', $request->id)
            ->update([
                'name' => $request->name,
                'code' => $code,
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
     *   path="/api/UpdateSuku",
	 *   tags={"Utilities"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Update Suku",
     *   operationId="UpdateSuku",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Update Suku",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="id", type="string", example="1"),
     *              @SWG\Property(property="name", type="string", example="Guru"),
     *             ),
     *      )
     * )
     *
     */
    public function UpdateSuku(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer|max:255',
                'name' => 'required|string|max:255|unique:suku',
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }

            $code = str_replace(' ', '_', $request->name);

            Suku::where('id', '=', $request->id)
            ->update([
                'name' => $request->name,
                'code' => $code,
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
     *   path="/api/DeletePekerjaan",
	 *   tags={"Utilities"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Delete Pekerjaan",
     *   operationId="DeletePekerjaan",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Delete Pekerjaan",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="id", type="integer", example="1")
     *          ),
     *      )
     * )
     *
     */
    public function DeletePekerjaan(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer|max:255'
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
    
            DB::table('pekerjaan')->where('id', $request->id)->delete();
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200);
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }
    /**
     * @SWG\Post(
     *   path="/api/DeleteSuku",
	 *   tags={"Utilities"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Delete Suku",
     *   operationId="DeleteSuku",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Delete Suku",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="id", type="integer", example="1")
     *          ),
     *      )
     * )
     *
     */
    public function DeleteSuku(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer|max:255'
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
    
            DB::table('suku')->where('id', $request->id)->delete();
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200);
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }

    /**
     * @SWG\Get(
     *   path="/api/GetAllMenu",
     *   tags={"Utilities"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get All Menu",
     *   operationId="GetAllMenu",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
     * )
     */
    public function GetAllMenu(Request $request){
        try{
            $GetAllMenu = MenuAccess::get();
            
            if(count($GetAllMenu)!=0){
                $rslt =  $this->ResultReturn(200, 'success', $GetAllMenu);
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
     *   path="/api/GetAllMenuAccess",
     *   tags={"Utilities"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="GetAllMenuAccess",
     *   operationId="GetAllMenuAccess",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
     * )
     */
    public function GetAllMenuAccess(Request $request){
        try{
            $GetAllMenuAccess = MenuAccess::orderBy('name', 'ASC')->get();
            if(count($GetAllMenuAccess)!=0){
                $rslt =  $this->ResultReturn(200, 'success', $GetAllMenuAccess);
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

    public function Dashboard(Request $request){
        try{
            //Dashboard Waktu ------------------------------------------
            $now = Carbon::now();
            $dateallnow = $now->toDateString();
            $yearnow = now()->year;
            $monthnow = now()->month;
            $day = $now->format('l');
            $dateformat = $now->format('d F Y');
            $timestr = $now->format('H');
            $timeint = (int)$timestr;

            $greeting = '-';
            if($timeint>4 && $timeint<11 ){
                $greeting = 'Morning';
            }else if($timeint>10 && $timeint<17 ){
                $greeting = 'Afternoon';
            }else if($timeint>16 && $timeint<22 ){
                $greeting = 'Evening';
            }else{
                $greeting = 'Night';
            }

            //Dashboard Pemasukan Pengeluaran JumlahCust--------------------
            $Getfarmer = DB::table('farmers')
                    ->where('farmers.is_dell', '=', 0)            
                    ->count();

            $Getlahan = DB::table('lahans')
                    ->where('lahans.is_dell', '=', 0)              
                    ->count();

            $Getpohon = DB::table('lahans')
                    ->where('lahans.is_dell', '=', 0)
                    ->join('lahan_details','lahan_details.lahan_no','=', 'lahans.lahan_no')                 
                    ->sum('lahan_details.amount');


            //Dashboard Pohon-------------------------------------
            // SELECT ld.tree_code, t.tree_name, sum(ld.amount) as total FROM `lahan_details` ld 
            // join trees t on t.tree_code = ld.tree_code where t.tree_category = 'Pohon_Kayu' 
            // group by ld.tree_code ORDER by total desc

            $listarraytotalkayu=array();
            $listtempkayu=array();
            array_push($listtempkayu,'Nama');
            array_push($listtempkayu,'Value');
            array_push($listarraytotalkayu,$listtempkayu);

            $GetPohonKayu = DB::table('lahans')
                        ->select('lahan_details.tree_code', DB::raw('SUM(lahan_details.amount) as total'))
                        ->join('lahan_details','lahan_details.lahan_no','=', 'lahans.lahan_no')  
                        ->join('trees','trees.tree_code','=', 'lahan_details.tree_code') 
                        ->where('lahans.is_dell', '=', 0)
                        ->where('trees.tree_category', '=', 'Pohon_Kayu')
                        ->whereYear('lahans.created_at', '2022')
                        ->groupBy('lahan_details.tree_code')
                        ->orderBy('total', 'DESC')              
                        ->get();

            $n=1;
            foreach($GetPohonKayu as $val1)
            {
                if($n <= 5){
                    $getnamapohon = DB::table('trees')
                                    ->where('trees.tree_code', '=',$val1->tree_code)
                                    ->first();
                    $listtempkayu=array();
                    array_push($listtempkayu,$getnamapohon->tree_name);
                    array_push($listtempkayu,(int)$val1->total);
                    array_push($listarraytotalkayu,$listtempkayu);
                }else{
                    break;
                }
                $n += 1;
            } 
            
            $listarraytotalmpts=array();
            $listtempmpts=array();
            array_push($listtempmpts,'Nama');
            array_push($listtempmpts,'Value');
            array_push($listarraytotalmpts,$listtempmpts);
            $GetPohonMPTS = DB::table('lahans')
                        ->select('lahan_details.tree_code', DB::raw('SUM(lahan_details.amount) as total'))
                        ->join('lahan_details','lahan_details.lahan_no','=', 'lahans.lahan_no')  
                        ->join('trees','trees.tree_code','=', 'lahan_details.tree_code') 
                        ->where('lahans.is_dell', '=', 0)
                        ->where('trees.tree_category', '=', 'Pohon_Buah')
                        ->groupBy('lahan_details.tree_code')
                        ->orderBy('total', 'DESC')              
                        ->get();

            $n=1;
            foreach($GetPohonMPTS as $val1)
            {
                if($n <= 5){
                    $getnamapohon = DB::table('trees')
                                    ->where('trees.tree_code', '=',$val1->tree_code)
                                    ->first();
                    $listtempmpts=array();
                    array_push($listtempmpts,$getnamapohon->tree_name);
                    array_push($listtempmpts,(int)$val1->total);
                    array_push($listarraytotalmpts,$listtempmpts);
                }else{
                    break;
                }
                $n += 1;
            } 

            $listarraytotalcrops=array();
            $listtempcrops=array();
            array_push($listtempcrops,'Nama');
            array_push($listtempcrops,'Value');
            array_push($listarraytotalcrops,$listtempcrops);
            $GetPohonCrops = DB::table('lahans')
                        ->select('lahan_details.tree_code', DB::raw('SUM(lahan_details.amount) as total'))
                        ->join('lahan_details','lahan_details.lahan_no','=', 'lahans.lahan_no')  
                        ->join('trees','trees.tree_code','=', 'lahan_details.tree_code') 
                        ->where('lahans.is_dell', '=', 0)
                        ->where('trees.tree_category', '=', 'Tanaman_Bawah_Empon')
                        ->groupBy('lahan_details.tree_code')
                        ->orderBy('total', 'DESC')              
                        ->get();

            $n=1;
            foreach($GetPohonCrops as $val1)
            {
                if($n <= 5){
                    $getnamapohon = DB::table('trees')
                                    ->where('trees.tree_code', '=',$val1->tree_code)
                                    ->first();
                    $listtempcrops=array();
                    array_push($listtempcrops,$getnamapohon->tree_name);
                    array_push($listtempcrops,(int)$val1->total);
                    array_push($listarraytotalcrops,$listtempcrops);
                }else{
                    break;
                }
                $n += 1;
            } 
            

            $dataval = ['timestr'=>$timestr,'greeting'=>$greeting,'dateformat'=>$dateformat,'day'=>$day,
                        'Getfarmer'=>$Getfarmer,'Getlahan'=>$Getlahan,'Getpohon'=>$Getpohon,
                        'listarraytotalkayu'=>$listarraytotalkayu,'listarraytotalmpts'=>$listarraytotalmpts,
                        'listarraytotalcrops'=>$listarraytotalcrops];

            $rslt =  $this->ResultReturn(200, 'success', $dataval);
            return response()->json($rslt, 200);
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }
}
