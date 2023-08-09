<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use App\User;
use App\Desa;
use App\Kecamatan;
use App\Kabupaten;
use App\Province;
use App\LahanUmum;
use App\LahanUmumDetail;
use App\LahanUmumHoleDetail;
use App\LahanUmumDistribution;
use App\LahanUmumDistributionDetail;
use App\LahanUmumAdjustment;
use App\LahanUmumMonitoring;
use App\LahanUmumMonitoringDetail;
use App\Trees as Tree;
use App\TreeLocation;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class LahanUmumController extends Controller
{
// Pendataan {
    // GET Lahan Umum {
    /**
     * @SWG\Get(
     *   path="/api/GetLahanUmumAllAdmin",
     *   tags={"LahanUmum"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Lahan Umum All Admin",
     *   operationId="GetLahanUmumAllAdmin",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="typegetdata",in="query", type="string"),
     *      @SWG\Parameter(name="pic_lahan",in="query", type="string"),
     *      @SWG\Parameter(name="mu",in="query",  type="string"),
     *      @SWG\Parameter(name="ta",in="query", type="string"),
     *      @SWG\Parameter(name="village",in="query",  type="string"),
     * )
     */
    public function GetLahanUmumAllAdmin(Request $request)
    {
        $program_year = $request->program_year;
        $getcr = $request->created_by;
        
        if($getcr){$cr='%'.$getcr.'%';}
        else{$cr='%%';}
        
        $GetLahanUmumAll = DB::table('lahan_umums')->select('lahan_umums.id as idTblLahan','lahan_umums.lahan_no as lahanNo', 'lahan_umums.mou_no as mou_no','lahan_umums.longitude','lahan_umums.latitude','lahan_umums.coordinate', 'lahan_umums.luas_lahan', 'lahan_umums.pattern_planting', 'lahan_umums.pic_lahan', 'managementunits.name as mu', 'lahan_umums.total_holes', 'desas.name as namaDesa','employees.name as employee','lahan_umums.complete_data', 'lahan_umums.is_verified', 'lahan_umums.is_dell')
        ->leftjoin('employees', 'employees.nik', '=', 'lahan_umums.employee_no')
        ->leftjoin('managementunits', 'managementunits.mu_no', '=', 'lahan_umums.mu_no')
        ->leftjoin('desas', 'desas.kode_desa', '=', 'lahan_umums.village')
        ->where('lahan_umums.is_dell','=',0)
        ->where('lahan_umums.program_year', '=', $program_year)
        ->where('lahan_umums.created_by', 'LIKE', $cr)
        ->orderBy('lahan_umums.created_at', 'desc')
        ->get();

        $rslt =  $this->ResultReturn(200, 'success', $GetLahanUmumAll);
        return response()->json($rslt, 200);  
       
    }
    
    public function GetDetailLahanUmum(Request $request)
    {
        $lahan_no = $request->lahan_no;
        
        $getDetailLahanUmum = DB::table('lahan_umums')
        ->select('lahan_umums.id as idTblLahan',
                 'lahan_umums.lahan_no as lahanNo',
                 'lahan_umums.mu_no as mu_no',
                 'lahan_umums.province as province',
                 'lahan_umums.regency as regency',
                 'lahan_umums.district as district',
                 'lahan_umums.village as village',
                 'lahan_umums.employee_no as employee_no',
                 'lahan_umums.pic_lahan as pic_lahan',
                 'lahan_umums.ktp_no as ktp_no',
                 'lahan_umums.address as address',
                 'lahan_umums.mou_no as mou_no',
                 'lahan_umums.program_year as program_year',
                 'lahan_umums.luas_lahan as luas_lahan', 
                 'lahan_umums.luas_tanam as luas_tanam', 
                 'lahan_umums.pattern_planting as pattern_planting',
                 'lahan_umums.access_lahan as access_lahan',
                 'lahan_umums.jarak_lahan as jarak_lahan',
                 'lahan_umums.status as status',
                 'lahan_umums.longitude as longitude',
                 'lahan_umums.latitude as latitude',
                 'lahan_umums.planting_hole_date as planting_hole_date',
                 'lahan_umums.distribution_date as distribution_date',
                 'lahan_umums.planting_realization_date as planting_realization_date',
                 'lahan_umums.created_at as created_at',
                 'lahan_umums.updated_at as updated_at',
                 'lahan_umums.photo_doc as photo_doc',
                 'lahan_umums.photo1 as photo1',
                 'lahan_umums.photo2 as photo2',
                 'lahan_umums.photo3 as photo3',
                 'lahan_umums.active as active',
                 'lahan_umums.tutupan_lahan as tutupan_lahan',
                 'lahan_umums.description as description',
                 'lahan_umums.created_by as created_by',
                 'lahan_umums.coordinate as coordinate', 
                 'managementunits.name as mu',
                 'provinces.name as namaProvince',
                 'kabupatens.name as namaRegency',
                 'kecamatans.name as namaDistrict',
                 'desas.name as namaDesa',
                 'users.name as employee',
                 'employees.name as employeeName',
                 'lahan_umums.complete_data as complete_data', 
                 'lahan_umums.is_verified as is_verified',
                 'lahan_umums.verified_by as verified_by',
                 'lahan_umums.is_dell as is_dell')
        ->leftjoin('users', 'users.email', '=', 'lahan_umums.created_by')
        ->leftjoin('provinces', 'provinces.province_code', '=', 'lahan_umums.province')
        ->leftjoin('kabupatens', 'kabupatens.kabupaten_no', '=', 'lahan_umums.regency')
        ->leftjoin('kecamatans', 'kecamatans.kode_kecamatan', '=', 'lahan_umums.district')
        ->leftjoin('employees', 'employees.nik', '=', 'lahan_umums.employee_no')
        ->leftjoin('managementunits', 'managementunits.mu_no', '=', 'lahan_umums.mu_no')
        ->leftjoin('desas', 'desas.kode_desa', '=', 'lahan_umums.village')
        ->where('lahan_umums.is_dell','=',0)
        ->where('lahan_umums.lahan_no', '=', $lahan_no)
        ->first();
        
        if($getDetailLahanUmum){
            $getDetailTreesLahan =  DB::table('lahan_umum_details')->select('lahan_umum_details.id','lahan_umum_details.lahan_no','lahan_umum_details.tree_code','lahan_umum_details.amount',
            'lahan_umum_details.detail_year','tree_locations.category as tree_category','tree_locations.tree_name')
            ->leftjoin('tree_locations', 'tree_locations.tree_code', '=', 'lahan_umum_details.tree_code')
            ->where('lahan_umum_details.lahan_no', '=', $getDetailLahanUmum->lahanNo)
            ->where('tree_locations.mu_no', '=', $getDetailLahanUmum->mu_no)
            ->get();
            
        $LahanUmumDetail = ['lahan_no'=>$getDetailLahanUmum->lahanNo,
                            'mu_no'=>$getDetailLahanUmum->mu_no,
                            'mu_name'=>$getDetailLahanUmum->mu,
                            'province'=>$getDetailLahanUmum->province,
                            'province_name'=>$getDetailLahanUmum->namaProvince,
                            'regency'=>$getDetailLahanUmum->regency,
                            'regency_name'=>$getDetailLahanUmum->namaRegency,
                            'district'=>$getDetailLahanUmum->district,
                            'district_name'=>$getDetailLahanUmum->namaDistrict,
                            'village'=>$getDetailLahanUmum->village,
                            'village_name'=>$getDetailLahanUmum->namaDesa,
                            'employeeName'=>$getDetailLahanUmum->employeeName,
                            'employee_no'=>$getDetailLahanUmum->employee_no,
                            'pic_lahan'=>$getDetailLahanUmum->pic_lahan,
                            'ktp_no'=>$getDetailLahanUmum->ktp_no,
                            'address'=>$getDetailLahanUmum->address,
                            'mou_no'=>$getDetailLahanUmum->mou_no,
                            'program_year'=>$getDetailLahanUmum->program_year,
                            'luas_lahan'=>$getDetailLahanUmum->luas_lahan,
                            'luas_tanam'=>$getDetailLahanUmum->luas_tanam,
                            'pattern_planting'=>$getDetailLahanUmum->pattern_planting,
                            'access_lahan'=>$getDetailLahanUmum->access_lahan,
                            'jarak_lahan'=>$getDetailLahanUmum->jarak_lahan,
                            'status'=>$getDetailLahanUmum->status,
                            'longitude'=>$getDetailLahanUmum->longitude,
                            'latitude'=>$getDetailLahanUmum->latitude,
                            'planting_hole_date'=>$getDetailLahanUmum->planting_hole_date,
                            'distribution_date'=>$getDetailLahanUmum->distribution_date,
                            'planting_realization_date'=>$getDetailLahanUmum->planting_realization_date,
                            'complete_data'=>$getDetailLahanUmum->complete_data,
                            'is_verified'=>$getDetailLahanUmum->is_verified,
                            'verified_by'=>$getDetailLahanUmum->verified_by,
                            'created_at'=>$getDetailLahanUmum->created_at,
                            'updated_at'=>$getDetailLahanUmum->updated_at,
                            'photo_doc'=>$getDetailLahanUmum->photo_doc,
                            'photo1'=>$getDetailLahanUmum->photo1,
                            'photo2'=>$getDetailLahanUmum->photo2,
                            'photo3'=>$getDetailLahanUmum->photo3,
                            'active'=>$getDetailLahanUmum->active,
                            'coordinate'=>$getDetailLahanUmum->coordinate,
                            'tutupan_lahan'=>$getDetailLahanUmum->tutupan_lahan,
                            'is_dell'=>$getDetailLahanUmum->is_dell,
                            'description'=>$getDetailLahanUmum->description,
                            'created_by'=>$getDetailLahanUmum->created_by,
                            'DetailLahanUmum'=>$getDetailTreesLahan];
                            
                            $rslt =  $this->ResultReturn(200, 'success', $LahanUmumDetail);
                            return response()->json($rslt, 200); 
        }
        
        $rslt =  $this->ResultReturn(200, 'success', $getDetailLahanUmum);
        return response()->json($rslt, 200);
    }
    
    public function GetDetailLahanUmumMOU(Request $request)
    {
        $mou_no = $request->mou_no;
        
        $getDetailLahanUmum = DB::table('lahan_umums')
        ->select('lahan_umums.id as idTblLahan',
                 'lahan_umums.lahan_no as lahanNo',
                 'lahan_umums.mu_no as mu_no',
                 'lahan_umums.province as province',
                 'lahan_umums.regency as regency',
                 'lahan_umums.district as district',
                 'lahan_umums.village as village',
                 'lahan_umums.employee_no as employee_no',
                 'lahan_umums.pic_lahan as pic_lahan',
                 'lahan_umums.ktp_no as ktp_no',
                 'lahan_umums.address as address',
                 'lahan_umums.mou_no as mou_no',
                 'lahan_umums.program_year as program_year',
                 'lahan_umums.luas_lahan as luas_lahan', 
                 'lahan_umums.luas_tanam as luas_tanam', 
                 'lahan_umums.pattern_planting as pattern_planting',
                 'lahan_umums.access_lahan as access_lahan',
                 'lahan_umums.jarak_lahan as jarak_lahan',
                 'lahan_umums.status as status',
                 'lahan_umums.longitude as longitude',
                 'lahan_umums.latitude as latitude',
                 'lahan_umums.planting_hole_date as planting_hole_date',
                 'lahan_umums.distribution_date as distribution_date',
                 'lahan_umums.planting_realization_date as planting_realization_date',
                 'lahan_umums.created_at as created_at',
                 'lahan_umums.updated_at as updated_at',
                 'lahan_umums.photo_doc as photo_doc',
                 'lahan_umums.photo1 as photo1',
                 'lahan_umums.photo2 as photo2',
                 'lahan_umums.photo3 as photo3',
                 'lahan_umums.active as active',
                 'lahan_umums.tutupan_lahan as tutupan_lahan',
                 'lahan_umums.description as description',
                 'lahan_umums.created_by as created_by',
                 'lahan_umums.coordinate as coordinate', 
                 'managementunits.name as mu',
                 'provinces.name as namaProvince',
                 'kabupatens.name as namaRegency',
                 'kecamatans.name as namaDistrict',
                 'desas.name as namaDesa',
                 'users.name as employee',
                 'employees.name as employeeName',
                 'lahan_umums.complete_data as complete_data', 
                 'lahan_umums.is_verified as is_verified',
                 'lahan_umums.verified_by as verified_by',
                 'lahan_umums.is_dell as is_dell')
        ->leftjoin('users', 'users.email', '=', 'lahan_umums.created_by')
        ->leftjoin('provinces', 'provinces.province_code', '=', 'lahan_umums.province')
        ->leftjoin('kabupatens', 'kabupatens.kabupaten_no', '=', 'lahan_umums.regency')
        ->leftjoin('kecamatans', 'kecamatans.kode_kecamatan', '=', 'lahan_umums.district')
        ->leftjoin('employees', 'employees.nik', '=', 'lahan_umums.employee_no')
        ->leftjoin('managementunits', 'managementunits.mu_no', '=', 'lahan_umums.mu_no')
        ->leftjoin('desas', 'desas.kode_desa', '=', 'lahan_umums.village')
        ->where('lahan_umums.is_dell','=',0)
        ->where('lahan_umums.mou_no', '=', $mou_no)
        ->first();
        
        if($getDetailLahanUmum){
            $getDetailTreesLahan =  DB::table('lahan_umum_details')->select('lahan_umum_details.id','lahan_umum_details.lahan_no','lahan_umum_details.tree_code','lahan_umum_details.amount',
            'lahan_umum_details.detail_year','tree_locations.category as tree_category','tree_locations.tree_name')
            ->leftjoin('tree_locations', 'tree_locations.tree_code', '=', 'lahan_umum_details.tree_code')
            ->where('lahan_umum_details.lahan_no', '=', $getDetailLahanUmum->lahanNo)
            ->where('tree_locations.mu_no', '=', $getDetailLahanUmum->mu_no)
            ->get();
            
        $LahanUmumDetail = ['lahan_no'=>$getDetailLahanUmum->lahanNo,
                            'mu_no'=>$getDetailLahanUmum->mu_no,
                            'mu_name'=>$getDetailLahanUmum->mu,
                            'province'=>$getDetailLahanUmum->province,
                            'province_name'=>$getDetailLahanUmum->namaProvince,
                            'regency'=>$getDetailLahanUmum->regency,
                            'regency_name'=>$getDetailLahanUmum->namaRegency,
                            'district'=>$getDetailLahanUmum->district,
                            'district_name'=>$getDetailLahanUmum->namaDistrict,
                            'village'=>$getDetailLahanUmum->village,
                            'village_name'=>$getDetailLahanUmum->namaDesa,
                            'employeeName'=>$getDetailLahanUmum->employeeName,
                            'employee_no'=>$getDetailLahanUmum->employee_no,
                            'pic_lahan'=>$getDetailLahanUmum->pic_lahan,
                            'ktp_no'=>$getDetailLahanUmum->ktp_no,
                            'address'=>$getDetailLahanUmum->address,
                            'mou_no'=>$getDetailLahanUmum->mou_no,
                            'program_year'=>$getDetailLahanUmum->program_year,
                            'luas_lahan'=>$getDetailLahanUmum->luas_lahan,
                            'luas_tanam'=>$getDetailLahanUmum->luas_tanam,
                            'pattern_planting'=>$getDetailLahanUmum->pattern_planting,
                            'access_lahan'=>$getDetailLahanUmum->access_lahan,
                            'jarak_lahan'=>$getDetailLahanUmum->jarak_lahan,
                            'status'=>$getDetailLahanUmum->status,
                            'longitude'=>$getDetailLahanUmum->longitude,
                            'latitude'=>$getDetailLahanUmum->latitude,
                            'planting_hole_date'=>$getDetailLahanUmum->planting_hole_date,
                            'distribution_date'=>$getDetailLahanUmum->distribution_date,
                            'planting_realization_date'=>$getDetailLahanUmum->planting_realization_date,
                            'complete_data'=>$getDetailLahanUmum->complete_data,
                            'is_verified'=>$getDetailLahanUmum->is_verified,
                            'verified_by'=>$getDetailLahanUmum->verified_by,
                            'created_at'=>$getDetailLahanUmum->created_at,
                            'updated_at'=>$getDetailLahanUmum->updated_at,
                            'photo_doc'=>$getDetailLahanUmum->photo_doc,
                            'photo1'=>$getDetailLahanUmum->photo1,
                            'photo2'=>$getDetailLahanUmum->photo2,
                            'photo3'=>$getDetailLahanUmum->photo3,
                            'active'=>$getDetailLahanUmum->active,
                            'coordinate'=>$getDetailLahanUmum->coordinate,
                            'tutupan_lahan'=>$getDetailLahanUmum->tutupan_lahan,
                            'is_dell'=>$getDetailLahanUmum->is_dell,
                            'description'=>$getDetailLahanUmum->description,
                            'created_by'=>$getDetailLahanUmum->created_by,
                            'DetailLahanUmum'=>$getDetailTreesLahan];
                            
                            $rslt =  $this->ResultReturn(200, 'success', $LahanUmumDetail);
                            return response()->json($rslt, 200); 
        }
        
        $rslt =  $this->ResultReturn(200, 'success', $getDetailLahanUmum);
        return response()->json($rslt, 200);
    }

    /**
     * @SWG\Get(
     *   path="/api/GetLahanUmumAll",
     *   tags={"LahanUmum"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Lahan Umum All",
     *   operationId="GetLahanUmumAll",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="user_id",in="query", required=true, type="string"),
     *      @SWG\Parameter(name="pic_lahan",in="query", type="string"),
     *      @SWG\Parameter(name="limit",in="query", type="integer"),
     *      @SWG\Parameter(name="offset",in="query", type="integer"),
     * )
     */
    public function GetLahanUmumAll(Request $request){
        $limit = $this->limitcheck($request->limit);
        $offset =  $this->offsetcheck($limit, $request->offset);
        $getfarmerno = $request->pic_lahan;
        if($getfarmerno){$pic_lahan='%'.$getfarmerno.'%';}
        else{$pic_lahan='%%';}
        try{
            // var_dump(count($GetLahanNotComplete));
            if($pic_lahan!='%%'){
                $GetLahanUmumAll = LahanUmum::where('user_id', '=', $request->user_id)->where('pic_lahan','like',$pic_lahan)->where('is_dell', '=', 0)->orderBy('id', 'ASC')->get();
            }else{
                $GetLahanUmumAll = LahanUmum::where('user_id', '=', $request->user_id)->where('is_dell', '=', 0)->orderBy('id', 'ASC')->get();
            }
            if(count($GetLahanAll)!=0){
                
                if($pic_lahan!='%%'){
                    $count = LahanUmum::where('user_id', '=', $request->user_id)->where('pic_lahan','like',$pic_lahan)->where('is_dell', '=', 0)->count();
                }else{
                    $count = LahanUmum::where('user_id', '=', $request->user_id)->where('is_dell', '=', 0)->count();
                }
                $data = ['count'=>$count, 'data'=>$GetLahanUmumAll];
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
    // End: Get Lahan Umum }
    // Add Lahan Umum {
    /**
     * @SWG\Post(
     *   path="/api/AddMandatoryLahanUmum",
	 *   tags={"LahanUmum"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Add Mandatory Lahan Umum",
     *   operationId="AddMandatoryLahanUmum",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Add Mandatory Lahan Umum",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="luas_lahan", type="string", example="8200.00"),
     *              @SWG\Property(property="longitude", type="date", example="110.3300613"),
     *              @SWG\Property(property="latitude", type="string", example="-7.580778"),
     *              @SWG\Property(property="village", type="string", example="33.05.10.18"),
     *              @SWG\Property(property="mu_no", type="string", example="025"),
     *              @SWG\Property(property="target_area", type="string", example="025001"),
     *              @SWG\Property(property="active", type="int", example="1"),
     *              @SWG\Property(property="user_id", type="string", example="U0002")
     *          ),
     *      )
     * )
     *
     */
    public function AddMandatoryLahanUmum(Request $request){
        $validator = Validator::make($request->all(), [
            'mou_no' => 'required',
            'lahan_no' => 'unique:lahan_umums',
            'longitude' => 'required',
            'latitude' => 'required',
            'village' => 'required|max:255',
            'mu_no' => 'required|max:255',
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        }

        $coordinate = $this->getCordinate($request->longitude, $request->latitude);

        $getLastIdLahan = LahanUmum::orderBy('lahan_no','desc')->first(); 
        if($getLastIdLahan){
            $lahan_no = '11_'.str_pad(((int)substr($getLastIdLahan->lahan_no,-10) + 1), 10, '0', STR_PAD_LEFT);
        }else{
            $lahan_no = '11_0000000001';
        }

        $getDesa = Desa::select('kode_desa','name','kode_kecamatan')->where('kode_desa','=',$request->village)->first(); 
        // $getKec = Kecamatan::select('kode_kecamatan','name','kabupaten_no')->where('kode_kecamatan','=',$getDesa->kode_kecamatan)->first(); 
        // $getKab = Kabupaten::select('kabupaten_no','name','province_code')->where('kabupaten_no','=',$getKec->kabupaten_no)->first(); 
        // $getProv = Province::select('province_code','name')->where('province_code','=',$getKab->province_code)->first();
        
        // $complete_data = 0;
        // if($description != "-" && $photo1 != "-" && $access_lahan != "-" && $jarak_lahan != "-")
        // {
        //     $complete_data = 1;
        // }

        LahanUmum::create([
            'lahan_no' => $lahan_no,
            'mou_no' => $request->mou_no,
            'employee_no' => $request->employee_no,
            'pic_lahan' => $request->pic_lahan,
            'ktp_no' => $request->ktp_no,
            'program_year' => $request->program_year,
            
            'luas_lahan' => $request->luas_lahan,
            'luas_tanam' => $request->luas_tanam,
            'pattern_planting' => $this->ReplaceNull($request->pattern_planting, 'string'),
            'status' => $request->status,
            'jarak_lahan' => $request->jarak_lahan,
            'access_lahan' => $request->access_lahan,
            
            'mu_no' => $request->mu_no,
            'province' => $request->province,
            'regency' => $request->regency,
            'district' => $request->district,
            'village' => $request->village,
            'address' => $request->address,
            
            'longitude' => $request->longitude,
            'latitude' => $request->latitude,
            'planting_hole_date' => $request->planting_hole_date,
            'distribution_date' => $request->distribution_date,
            'planting_realization_date' => $request->planting_realization_date,
            
            'complete_data' =>'0',
            'is_verified' => '0',
            'verified_by' => '-',
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now(),
            
            'photo1' => $this->ReplaceNull($request->photo1, 'string'),
            'photo2' => $this->ReplaceNull($request->photo2, 'string'),
            'photo3' => $this->ReplaceNull($request->photo3, 'string'),
            'photo_doc' => $this->ReplaceNull($request->photo4, 'string'),
            'photo_hole1' => $request->photo_hole1,
            'photo_hole2' => $request->photo_hole2,
            
            'active' => 1,
            'coordinate' => $coordinate,
            'tutupan_lahan' => $this->ReplaceNull($request->tutupan_lahan, 'string'),

            'is_dell' => 0,
            'description' => '-',
            'created_by' => $request->created_by
        ]);
        
        $listTree = $request->list_trees;
        
        $addedTrees = [];
        foreach($listTree as $val){
            LahanUmumDetail::create([
                'lahan_no' => $lahan_no,
                'tree_code' => $val['tree_code'],
                'amount' => $val['tree_amount'],
                'detail_year' => $request['program_year'],
                'is_dell' => '0',

                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now()
            ]);
            
            array_push($addedTrees, ((DB::table('trees')->where('tree_code', $val['tree_code'])->first()->tree_name ?? 'jenis_pohon') . " => " . ($val['tree_amount'])));
        }
        
        $addedTreesToString = implode(", ", $addedTrees);
        $addedLahanData = str_replace('=', ':', http_build_query($request->except(['list_trees']),'',', '));
        
        // create log
        $this->createLog([
            'status' => 'Created',
            'lahan_no' => $lahan_no,
            'message' => ' [ '. $addedLahanData . ', jenis bibit: (' . $addedTreesToString . ')] in '
        ]);
        
        // $rslt =  $this->ResultReturn(200, $listTree, 'success');
        return response()->json($listTree, 200);
    }
    
    /**
     * @SWG\Post(
     *   path="/api/AddDetailLahanUmum",
	 *   tags={"LahanUmum"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Add Detail Lahan Umum",
     *   operationId="AddDetailLahanUmum",
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
    public function AddDetailLahanUmum(Request $request){
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
    // end: add lahan umum }
    /**
     * @SWG\Post(
     *   path="/api/VerificationLahanUmum",
	 *   tags={"LahanUmum"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Verification Lahan Umum",
     *   operationId="VerificationLahanUmum",
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
    public function VerificationLahanUmum(Request $request){
        try{
            $validator = Validator::make($request->all(), [    
                'lahan_no' => 'required'
            ]);

            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
        
            // create log
            $this->createLog([
                'status' => 'Verification',
                'lahan_no' => $request->lahan_no,
                'message' => ' [ status: 0 => 1 ] in '
            ]);
            LahanUmum::where('lahan_no', '=', $request->lahan_no)
                    ->update
                    ([
                        'complete_data' => 1,
                        'is_verified' => 1,
                        'verified_by' => $request->verified_by
                    ]);
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200); 
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }
    
    public function PlantingHoleVerificationLahanUmum(Request $request){
        try{
            $validator = Validator::make($request->all(), [    
                'lahan_no' => 'required'
            ]);

            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
            
            // create log
            $this->createLog([
                'status' => 'Verification',
                'lahan_no' => $request->lahan_no,
                'message' => ' Planting Hole [ status: 1 => 2 ] in '
            ]);
            
            LahanUmum::where('lahan_no', '=', $request->lahan_no)
                    ->update
                    ([
                        'is_verified' => 2,
                        'verified_by' => $request->verified_by
                    ]);
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200); 
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }
    
    public function UnverificationLahanUmum(Request $request){
        try{
            $validator = Validator::make($request->all(), [    
                'lahan_no' => 'required'
            ]);

            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
            LahanUmum::where('lahan_no', '=', $request->lahan_no)
                    ->update
                    ([
                        'complete_data' => 1,
                        'is_verified' => 0,
                        'verified_by' => $request->verified_by
                    ]);
                
            // create log
            $this->createLog([
                'status' => 'Unverification',
                'lahan_no' => $request->lahan_no,
                'message' => ' [ status: 1 => 0 ] in '
            ]);
            
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200); 
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }
    
    public function UnverificationOneLahanUmum(Request $request){
        try{
            $validator = Validator::make($request->all(), [    
                'lahan_no' => 'required'
            ]);

            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
            LahanUmum::where('lahan_no', '=', $request->lahan_no)
                    ->update
                    ([
                        'total_holes' => 0,
                        'counter_hole_standard' => 0,
                        'complete_data' => 1,
                        'is_verified' => 1,
                        'verified_by' => $request->verified_by
                    ]);
                    
            LahanUmumHoleDetail::where('lahan_no', '=', $request->lahan_no)->delete();
            
            // create log
            $this->createLog([
                'status' => 'Unverification',
                'lahan_no' => $request->lahan_no,
                'message' => ' Planting Hole [ total holes: 0, status: 2 => 1 ] in '
            ]);
            
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200); 
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }
    
    public function DestroyLahanUmum(Request $req) {
        $validator = Validator::make($req->all(), [    
            'lahan_no' => 'required|exists:lahan_umums,lahan_no'
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        } else {
            
            // create log
            $this->createLog([
                'status' => 'Deleted',
                'lahan_no' => $req->lahan_no
            ]);            
            
            $data = LahanUmum::where('lahan_no', $req->lahan_no)->first();
            // delete data
            $data->delete();
            LahanUmumDetail::where('lahan_no', $req->lahan_no)->delete();
            LahanUmumHoleDetail::where('lahan_no', $req->lahan_no)->delete();
            
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200); 
        }
    }

    /**
     * @SWG\Post(
     *   path="/api/UpdateLahanUmum",
	 *   tags={"LahanUmum"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Update Lahan Umum",
     *   operationId="UpdateLahanUmum",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Update Lahan Umum",
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
    public function UpdateLahanUmum(Request $request)
    {
        $validator = Validator::make($request->all(), [    
            'luas_lahan' => 'required|max:255',
            'longitude' => 'required|max:255',
            'latitude' => 'required|max:255',
            'village' => 'required|max:255',
            'mu_no' => 'required|max:255',
            'pic_lahan' => 'required',
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        }

        $getDesa = Desa::select('kode_desa','name','kode_kecamatan')->where('kode_desa','=',$request->village)->first(); 

        $coordinate = $this->getCordinate($request->longitude, $request->latitude);

        $photo1 = $this->ReplaceNull($request->photo1, 'string');
        $photo2 = $this->ReplaceNull($request->photo2, 'string');
        $photo3 = $this->ReplaceNull($request->photo3, 'string');
        $photo_doc = $this->ReplaceNull($request->photo_doc, 'string');
        $luas_tanam = $this->ReplaceNull($request->luas_tanam, 'int');
        $tutupan_lahan = $this->ReplaceNull($request->tutupan_lahan, 'string');
        
        LahanUmum::where('lahan_no', '=', $request->lahan_no)
        ->update([
            'lahan_no' => $request->lahan_no,
            'mou_no' => $request->mou_no,
            'employee_no' => $request->employee_no,
            'pic_lahan' => $request->pic_lahan,
            'ktp_no' => $request->ktp_no,
            'program_year' => $request->program_year,
            
            'luas_lahan' => $request->luas_lahan,
            'luas_tanam' => $request->luas_tanam,
            'pattern_planting' => $this->ReplaceNull($request->pattern_planting, 'string'),
            'status' => $request->status,
            'jarak_lahan' => $request->jarak_lahan,
            'access_lahan' => $request->access_lahan,
            
            'mu_no' => $request->mu_no,
            'province' => $request->province,
            'regency' => $request->regency,
            'district' => $request->district,
            'village' => $request->village,
            'address' => $request->address,
            
            'longitude' => $request->longitude,
            'latitude' => $request->latitude,
            'planting_hole_date' => $request->planting_hole_date,
            'distribution_date' => $request->distribution_date,
            'planting_realization_date' => $request->planting_realization_date,
            
            'coordinate' => $coordinate,
            'tutupan_lahan' => $this->ReplaceNull($request->tutupan_lahan, 'string'),

            'description' => $request->description
        ]);
        
        LahanUmum::where('mou_no', '=', $request->mou_no)->update([
            'employee_no' => $request->employee_no,
            'pic_lahan' => $request->pic_lahan,
            'ktp_no' => $request->ktp_no,
            'program_year' => $request->program_year,
            'mu_no' => $request->mu_no,
            'province' => $request->province,
            'planting_hole_date' => $request->planting_hole_date,
            'distribution_date' => $request->distribution_date,
            'planting_realization_date' => $request->planting_realization_date,
        ]);
        
        if($request->photo4 != ''){
            LahanUmum::where('lahan_no', '=', $request->lahan_no)->update(['photo_doc'=>$request->photo4]);
        }
        if($request->photo1 != ''){
            LahanUmum::where('lahan_no', '=', $request->lahan_no)->update(['photo1'=>$request->photo1]);
        }
        
        if($request->photo2 != ''){
            LahanUmum::where('lahan_no', '=', $request->lahan_no)->update(['photo2'=>$request->photo2]);
        }
        
        if($request->photo3 != ''){
            LahanUmum::where('lahan_no', '=', $request->lahan_no)->update(['photo3'=>$request->photo3]);
        }
        
        LahanUmumDetail::where('lahan_no', '=', $request->lahan_no)->delete();
        
        $listTree = $request->list_trees;
        
        $addedTrees = [];
        foreach($listTree as $val){
            LahanUmumDetail::create([
                'lahan_no' => $request->lahan_no,
                'tree_code' => $val['tree_code'],
                'amount' => $val['tree_amount'],
                'detail_year' => $request['program_year'],
                'is_dell' => '0',

                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now()
            ]);
            array_push($addedTrees, ((DB::table('trees')->where('tree_code', $val['tree_code'])->first()->tree_name ?? 'jenis_pohon') . " => " . ($val['tree_amount'])));
        }
        
        $addedTreesToString = implode(", ", $addedTrees);
        $updatedLahanData = str_replace('=', ':', http_build_query($request->except(['list_trees']),'',', '));
        // create log
        $this->createLog([
            'status' => 'Updated',
            'lahan_no' => $request->lahan_no,
            'message' => ' [ '. $updatedLahanData .', jenis bibit: (' . $addedTreesToString . ')] in '
        ]);

        $rslt =  $this->ResultReturn(200, 'success', 'success');
        return response()->json($rslt, 200);
    }
    
    public function UpdateHoleLahanUmum(Request $request)
    {
        $validator = Validator::make($request->all(), [    
            'total_holes' => 'required',
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        }
        
        LahanUmum::where('lahan_no', '=', $request->lahan_no)
        ->update([
            'total_holes' => $request->total_holes,
            'counter_hole_standard' => $request->counter_hole_standard,
            'pohon_kayu' => $request->pohon_kayu,
            'pohon_mpts' => $request->pohon_mpts,
            'tanaman_bawah' => $request->tanaman_bawah,
            'photo_hole1' => $request->photo_hole1,
            'photo_hole2' => $request->photo_hole2,
        ]);
        
        LahanUmumHoleDetail::where('lahan_no', '=', $request->lahan_no)->delete();
        
        $listTree = $request->list_trees;
        
        $addedTrees = [];
        foreach($listTree as $val){
            LahanUmumHoleDetail::create([
                'lahan_no' => $request->lahan_no,
                'tree_code' => $val['tree_code'],
                'amount' => $val['amount'],
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now()
            ]);
            array_push($addedTrees, ((DB::table('trees')->where('tree_code', $val['tree_code'])->first()->tree_name ?? 'jenis_pohon') . " => " . ($val['amount'])));
        }
        
        $addedTreesToString = implode(", ", $addedTrees);
        $updatedLahanData = str_replace('=', ':', http_build_query($request->except(['list_trees']),'',', '));
        // create log
        $this->createLog([
            'status' => 'Updated',
            'lahan_no' => $request->lahan_no,
            'message' => ' Planting Hole [ '. $updatedLahanData .', jenis bibit: (' . $addedTreesToString . ')] in '
        ]);

        $rslt =  $this->ResultReturn(200, 'success', 'success');
        return response()->json($rslt, 200);
    }

    /**
     * @SWG\Post(
     *   path="/api/SoftDeleteLahanUmum",
	 *   tags={"LahanUmum"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Soft Delete Lahan Umum",
     *   operationId="SoftDeleteLahanUmum",
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
            LahanUmum::where('id', '=', $request->id)
                    ->update
                    ([
                        'is_dell' => 1
                    ]);
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200); 
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }
    
    public function DeleteMonitoringLahanUmum(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'monitoring_no' => 'required',
            ]);

            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            } 
            
            $monitoring_no = $request->monitoring_no;
            $monitoring = LahanUmumMonitoring::where('monitoring_no','=',$monitoring_no)->first();
            
            if($monitoring){
                $userEmail = Auth::user()->email ?? '-';
                $message = "Deleted Monitoring-1 $request->monitoring_no [mou_no: $monitoring->mou_no] by $userEmail";
                Log::channel('lahan_umums')->alert($message);
                
                LahanUmumMonitoringDetail::where('monitoring_no', $monitoring_no)->delete();
                LahanUmumMonitoring::where('monitoring_no', '=', $monitoring_no)
                ->delete();
    
                $rslt =  $this->ResultReturn(200, 'success', 'success');
                return response()->json($rslt, 200); 
            }else{
                $rslt =  $this->ResultReturn(400, 'doesnt match data', 'doesnt match data');
                return response()->json($rslt, 400);
            }

            
        }catch (\Exception $ex){
            $rslt =  $this->ResultReturn(400, 'gagal',$ex);
            return response()->json($rslt, 400);
        }
    }
// End: Pendataan }

// Export Data {
    public function ExportLahanUmum(Request $req) {
        $validator = Validator::make($req->all(), [
            'program_year' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->first(), 400);
        } 
        $datas = LahanUmum::
            join('provinces', 'provinces.province_code', 'lahan_umums.province')
            ->join('kabupatens', 'kabupatens.kabupaten_no', 'lahan_umums.regency')
            ->join('kecamatans', 'kecamatans.kode_kecamatan', 'lahan_umums.district')
            ->join('managementunits', 'managementunits.mu_no', 'lahan_umums.mu_no')
            ->join('desas', 'desas.kode_desa', 'lahan_umums.village')
            ->select(
                'lahan_umums.*',
                'provinces.name as province',
                'kabupatens.name as kabupaten',
                'kecamatans.name as kecamatan',
                'managementunits.name as mu',
                'desas.name as desa'
            )
            ->where(['lahan_umums.program_year' => $req->program_year,'is_dell' => 0]);
        
        if ($req->created_by) {
            $datas = $datas->whereIn('created_by', explode(",", $req->created_by));
        }
        $lahan_no = $datas->pluck('lahan_no');
        $tree_code = LahanUmumDetail::whereIn('lahan_no', $lahan_no)->pluck('tree_code');
        $trees = DB::table('trees')->whereIn('tree_code', $tree_code)->orderBy('tree_name')->get();
        $datas = $datas->get();
        
        foreach ($datas as $data) {
            $lahan_trees = [];
            foreach($trees as $tree) {
                $ltd = LahanUmumDetail::where(['lahan_no' => $data->lahan_no, 'tree_code' => $tree->tree_code])->first();
                if ($ltd) array_push($lahan_trees, (int)$ltd->amount);
                else array_push($lahan_trees, 0);
            }
            $data->lahan_trees = $lahan_trees;
        }
        
        $rslt = [
            'py' => $req->program_year,
            'nama_title' => 'Export Lahan Umum',
            'trees' => (object)[
                'count' => count($trees),
                'data' => $trees
            ],
            'lahan' => (object)[
                'count' => count($datas),
                'data' => $datas
            ]
        ];
        // return response()->json($rslt, 200);
        return view('lahan_umum.export_lahan', $rslt);
    }
    
    public function ExportLahanUmumPenilikanLubang(Request $req) {
        $validator = Validator::make($req->all(), [
            'program_year' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->first(), 400);
        } 
        $datas = LahanUmum::
            join('provinces', 'provinces.province_code', 'lahan_umums.province')
            ->join('kabupatens', 'kabupatens.kabupaten_no', 'lahan_umums.regency')
            ->join('kecamatans', 'kecamatans.kode_kecamatan', 'lahan_umums.district')
            ->join('managementunits', 'managementunits.mu_no', 'lahan_umums.mu_no')
            ->join('desas', 'desas.kode_desa', 'lahan_umums.village')
            ->select(
                'lahan_umums.*',
                'provinces.name as province',
                'kabupatens.name as kabupaten',
                'kecamatans.name as kecamatan',
                'managementunits.name as mu',
                'desas.name as desa'
            )
            ->where(['program_year' => $req->program_year,'is_dell' => 0, ['total_holes', '!=', 0]]);
        
        if ($req->created_by) {
            $datas = $datas->whereIn('created_by', explode(",", $req->created_by));
        }
        $lahan_no = $datas->pluck('lahan_no');
        $tree_code = LahanUmumHoleDetail::whereIn('lahan_no', $lahan_no)->pluck('tree_code');
        $trees = DB::table('trees')->whereIn('tree_code', $tree_code)->orderBy('tree_name')->get();
        $datas = $datas->get();
        
        foreach ($datas as $data) {
            $lahan_trees = [];
            foreach($trees as $tree) {
                $ltd = LahanUmumHoleDetail::where(['lahan_no' => $data->lahan_no, 'tree_code' => $tree->tree_code])->first();
                if ($ltd) array_push($lahan_trees, (int)$ltd->amount);
                else array_push($lahan_trees, 0);
            }
            $data->lahan_trees = $lahan_trees;
        }
        
        $rslt = [
            'py' => $req->program_year,
            'nama_title' => 'Export Penilikan Lubang Lahan Umum',
            'trees' => (object)[
                'count' => count($trees),
                'data' => $trees
            ],
            'lahan' => (object)[
                'count' => count($datas),
                'data' => $datas
            ]
        ];
        // return response()->json($rslt, 200);
        return view('lahan_umum.export_penilikan_lubang', $rslt);
    }
    
    public function ExportDistributionReportLahanUmum(Request $req) {
        $validator = Validator::make($req->all(), [
            'program_year' => 'required',
            'nursery' => 'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->first(), 400);
        }
        
        $datas = LahanUmumDistribution::where([
            ['distribution_no', 'LIKE', "D-$req->program_year-%"],
            'distribution_date' => $req->distribution_date,
            ['status', '>', 0]
        ]);
        
        $distribution_no = $datas->pluck('distribution_no');
        $tree_code = LahanUmumAdjustment::whereIn('distribution_no', $distribution_no)->groupBy('tree_code')->pluck('tree_code');
        $trees = Tree::whereIn('tree_code', $tree_code)->orderBy('tree_name')->get();
        
        $datas = $datas->get();
        $final_data = [];
        foreach ($datas as $data) {
            // get trees detail
            $tree_codes = LahanUmumAdjustment::where('distribution_no', $data->distribution_no)->pluck('tree_code')->toArray();
            $amounts = [];
            foreach ($trees as $tree) {
                if (in_array($tree->tree_code, $tree_codes)) {
                    $da_amount = LahanUmumAdjustment::select(
                        'total_distributed', 'broken_seeds', 'missing_seeds', 'total_tree_received'    
                    )->where(['distribution_no' => $data->distribution_no, 'tree_code' => $tree->tree_code])->first();
                    array_push($amounts, $da_amount);
                } else array_push($amounts, (object)[
                    'total_distributed' => 0,
                    'broken_seeds' => 0,
                    'missing_seeds' => 0,
                    'total_tree_received' => 0,
                ]);
            }
            $data->trees = $amounts;
            // get lahan data
            $lahan_data = LahanUmum::where('mou_no', $data->mou_no)->first();
            // set mou no & created_by
            $data->mou_no = $lahan_data->mou_no;
            $data->lahan_created_by = $lahan_data->created_by;
            // set nursery
            if ($lahan_data->nursery) $data->nursery = $lahan_data->nursery;
            else $data->nursery = $this->getNurseryAlocationGlobal($lahan_data->mu_no);
            // set location name
            $data->mu = DB::table('managementunits')->where('mu_no', $lahan_data->mu_no)->first()->name ?? '-';
            $data->province = DB::table('provinces')->where('province_code', $lahan_data->province)->first()->name ?? '-';
            $data->regency = DB::table('kabupatens')->where('kabupaten_no', $lahan_data->regency)->first()->name ?? '-';
            $data->district = DB::table('kecamatans')->where('kode_kecamatan', $lahan_data->district)->first()->name ?? '-';
            $data->village = DB::table('desas')->where('kode_desa', $lahan_data->village)->first()->name ?? '-';
            // set PIC
            $data->pic_t4t = DB::table('employees')->where('nik', $lahan_data->employee_no)->first()->name ?? '-';
            $data->pic_lahan = $lahan_data->pic_lahan ?? '-';
            
            // filter by nursery
            if ($req->nursery != 'All') { if ($req->nursery == $data->nursery) array_push($final_data, $data); }
            else array_push($final_data, $data);
        }
        
        // filter by created by
        if ($req->created_by) {
            $created = explode(',', $req->created_by);
            $final_data = array_filter(
                $final_data,
                function ($val) use ($created) {
                    return in_array($val->lahan_created_by, $created);
                }  
            );
        }
        
        $rslt = [
            'py' => $req->program_year,
            'nama_title' => 'Export Distribution Report Lahan Umum',
            'distribution_date' => $req->distribution_date,
            'trees' => (object)[
                'count' => count($trees),
                'data' => $trees
            ],
            'distributions' => (object)[
                'count' => count($final_data),
                'data' => $final_data
            ]
        ];
        
        // return response()->json($rslt, 200);
        return view('lahan_umum.export_distribution_report', $rslt);
    }
    
    public function ExportMonitoringLahanUmum(Request $req) {
        $validator = Validator::make($req->all(), [
            'program_year' => 'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->first(), 400);
        }
        
        $datas = LahanUmumMonitoring::where([
            'program_year' => $req->program_year,
            'is_dell' => 0,
            ['mou_no', 'NOT LIKE', '00000%']
        ]);
        
        if ($req->created_by) {
            $cb = explode(',', $req->created_by);
            $mou_no = LahanUmum::whereIn('created_by', $cb)->groupBy('mou_no')->pluck('mou_no');
            $datas = $datas->whereIn('mou_no', $mou_no);
        }
        
        // get trees
        $monitoring_no = $datas->pluck('monitoring_no');
        $tree_code = LahanUmumMonitoringDetail::whereIn('monitoring_no', $monitoring_no)->groupBy('tree_code')->pluck('tree_code');
        $trees = Tree::whereIn('tree_code', $tree_code)->orderBy('tree_name')->get();
        
        $datas = $datas->orderBy('planting_date')->get();
        
        foreach ($datas as $data) {
            $tree_details = [];
            $tree_codes = LahanUmumMonitoringDetail::where('monitoring_no', $data->monitoring_no)->pluck('tree_code')->toArray();
            foreach($trees as $tree) {
                if (in_array($tree->tree_code, $tree_codes)) {
                    array_push($tree_details, (object)[
                        'planted_life' => (int)LahanUmumMonitoringDetail::where(['monitoring_no' => $data->monitoring_no, 'tree_code' => $tree->tree_code, 'status' => 'sudah_ditanam', 'condition' => 'hidup'])->sum('qty'),
                        'dead' => (int)LahanUmumMonitoringDetail::where(['monitoring_no' => $data->monitoring_no, 'tree_code' => $tree->tree_code, 'condition' => 'mati'])->sum('qty'),
                        'lost' => (int)LahanUmumMonitoringDetail::where(['monitoring_no' => $data->monitoring_no, 'tree_code' => $tree->tree_code, 'condition' => 'hilang'])->sum('qty')
                    ]);
                } else array_push($tree_details, (object)[
                        'planted_life' => 0,
                        'dead' => 0,
                        'lost' => 0
                    ]);
            }
            $data->tree_details = $tree_details;
            
            // get lahan detail
            $lahan = LahanUmum::where('mou_no', $data->mou_no)->first();
            if ($lahan) {
                $data->mu_name = DB::table('managementunits')->where('mu_no', $lahan->mu_no)->first()->name ?? '-';
                $data->province_name = DB::table('provinces')->where('province_code', $lahan->province)->first()->name ?? '-';
                $data->regency_name = DB::table('kabupatens')->where('kabupaten_no', $lahan->regency)->first()->name ?? '-';
                $data->district_name = DB::table('kecamatans')->where('kode_kecamatan', $lahan->district)->first()->name ?? '-';
                $data->village_name = DB::table('desas')->where('kode_desa', $lahan->village)->first()->name ?? '-';
                $data->address = $lahan->address ?? '-';
                $data->pic_t4t = DB::table('employees')->where('nik', $lahan->employee_no)->first()->name ?? '-';
                $data->pic_lahan = $lahan->pic_lahan ?? '-';
            }
        }
        
        $rslt = [
            'py' => $req->program_year,
            'trees' => $trees,
            'data' => $datas
        ];
        
        // return response()->json($rslt, 200);
        return view('lahan_umum/export_monitoring', $rslt);
    }
// End Export Data }
// DISTRIBUSI LAHAN UMUM MODULE    {

    private function generateUmumSeedlingLabels($lahan_no) {
        // Get Lahan Umum & Distribution Date
        $GetPHUDetail = LahanUmum::
                leftjoin('employees', 'employees.nik', '=', 'lahan_umums.employee_no')
                ->select(
                    'lahan_umums.id', 
                    'lahan_umums.lahan_no', 
                    'lahan_umums.program_year', 
                    'lahan_umums.mu_no',
                    'lahan_umums.latitude', 
                    'lahan_umums.longitude', 
                    'lahan_umums.distribution_date', 
                    'lahan_umums.planting_realization_date', 
                    'lahan_umums.employee_no', 
                    'lahan_umums.is_verified', 
                    'lahan_umums.verified_by', 
                    'lahan_umums.is_dell', 
                    'employees.name as employee_name',
                    'lahan_umums.pic_lahan'
                    )
                ->where('lahan_umums.lahan_no', '=', $lahan_no)
                ->first();
        
        if($GetPHUDetail){
            // GET Seedling List
            $seedCategories = ['KAYU', 'MPTS', 'CROPS'];
            $seedCategoriesLoopCount = 0;
            $perBagsAmount = [
                'KAYU' => 10,
                'MPTS' => [8,6],
                'CROPS' => 10
            ];
            $total8 = ['PETAI', 'MANGGA', 'NANGKA', 'SAWO', 'RAMBUTAN'];
            $seedsList = [];
            // GET per category seedling detail
            foreach($seedCategories as $seedCategory) {
                $seedsList[$seedCategory] = LahanUmumHoleDetail::
                    join('tree_locations', 'tree_locations.tree_code', '=', 'lahan_umum_hole_details.tree_code')
                    ->select('lahan_umum_hole_details.id',
                            'lahan_umum_hole_details.lahan_no',
                            'lahan_umum_hole_details.tree_code',
                            'lahan_umum_hole_details.amount',
                            'tree_locations.tree_name',
                            'tree_locations.category')
                    ->where([
                        ['lahan_umum_hole_details.lahan_no','=',$GetPHUDetail->lahan_no],
                        'tree_locations.mu_no' => $GetPHUDetail->mu_no,
                        'tree_locations.category' => $seedCategory
                    ])
                    ->orderBy('lahan_umum_hole_details.amount', 'DESC')
                    ->get();
                $seedCategoriesLoopCount += 1;
            }
            
            // GET BAGS amount
            $bagsList = [];
            foreach ($seedCategories as $seedCategory) {
                foreach ($seedsList[$seedCategory] as $seedling) {
                    $totalSeed = $seedling->amount;
                    $reminder = 0;
                    if ($seedCategory != 'MPTS') {
                        $totalBagPerSeed = ceil($seedling->amount / $perBagsAmount[$seedCategory]);
                        for ($i = 1;$i <= $totalBagPerSeed;$i++) {
                            $amountReminder = $totalSeed - $perBagsAmount[$seedCategory];
                            $seedData = [
                                'amount_reminder' => $amountReminder,
                                'tree_name' => $seedling->tree_name,
                                'tree_category' => $seedling->category
                            ];
                            if ($amountReminder < 0) {
                                $seedData['amount'] = $perBagsAmount[$seedCategory] + $amountReminder;
                                $reminder = $amountReminder;
                            } else {
                                $seedData['amount'] = $perBagsAmount[$seedCategory];
                            }
                            array_push($bagsList, $seedData);
                            $totalSeed -= $perBagsAmount[$seedCategory];
                        }
                    } else {
                        if (in_array($seedling->tree_name, $total8)) {
                            $totalBagPerSeed = ceil($seedling->amount / $perBagsAmount[$seedCategory][0]);
                            for ($i = 1;$i <= $totalBagPerSeed;$i++) {
                                $amountReminder = $totalSeed - $perBagsAmount[$seedCategory][0];
                                $seedData = [
                                    'amount_reminder' => $amountReminder,
                                    'tree_name' => $seedling->tree_name,
                                    'tree_category' => $seedling->category
                                ];
                                if ($amountReminder < 0) {
                                    $seedData['amount'] = $perBagsAmount[$seedCategory][0] + $amountReminder;
                                    $reminder = $amountReminder;
                                } else {
                                    $seedData['amount'] = $perBagsAmount[$seedCategory][0];
                                }
                                array_push($bagsList, $seedData);
                                $totalSeed -= $perBagsAmount[$seedCategory][0];
                            }
                        } else {
                            $totalBagPerSeed = ceil($seedling->amount / $perBagsAmount[$seedCategory][1]);
                            for ($i = 1;$i <= $totalBagPerSeed;$i++) {
                                $amountReminder = $totalSeed - $perBagsAmount[$seedCategory][1];
                                $seedData = [
                                    'amount_reminder' => $amountReminder,
                                    'tree_name' => $seedling->tree_name,
                                    'tree_category' => $seedling->category
                                ];
                                if ($amountReminder < 0) {
                                    $seedData['amount'] = $perBagsAmount[$seedCategory][1] + $amountReminder;
                                    $reminder = $amountReminder;
                                } else {
                                    $seedData['amount'] = $perBagsAmount[$seedCategory][1];
                                }
                                array_push($bagsList, $seedData);
                                $totalSeed -= $perBagsAmount[$seedCategory][1];
                            }
                        }
                    }
                }
            }
            
            // grouping Bags
            $labelsList = [];
            $labelIndex = 0;
            $bagNo = 0;
            foreach ($bagsList as $bag) {
                if ($labelIndex > 0 && $labelsList[$labelIndex -1]['tree_category'] == $bag['tree_category']) {
                    // get max cap
                    if ($labelsList[$labelIndex -1]['tree_category'] != 'MPTS') {
                        $cap = $perBagsAmount[$bag['tree_category']];
                    } else {
                        if (in_array($labelsList[$labelIndex -1]['tree_name'][count($labelsList[$labelIndex -1]['tree_name']) - 1], $total8)) {
                            $cap = $perBagsAmount[$bag['tree_category']][0];
                        } else {
                            $cap = $perBagsAmount[$bag['tree_category']][1];
                        }
                    }
                    $capLeft = $cap - $labelsList[$labelIndex -1]['total_amount'];
                    // check cap
                    if ($capLeft > 0) {
                        if ($bag['amount'] < $capLeft && $bag['amount'] > 0) {
                            // if same tree_name
                            if ($bag['tree_name'] == $labelsList[$labelIndex -1]['tree_name'][count($labelsList[$labelIndex -1]['tree_name']) - 1]) {
                                $labelsList[$labelIndex -1]['amount'][count($labelsList[$labelIndex -1]['amount']) - 1] += $bag['amount'];
                            } else {
                                // push new tree_name
                                array_push($labelsList[$labelIndex -1]['tree_name'], $bag['tree_name']);
                                array_push($labelsList[$labelIndex -1]['amount'], $bag['amount']);
                            }
                            $labelsList[$labelIndex -1]['total_amount'] += $bag['amount'];
                        } else {
                            // if same tree_name
                            if ($bag['tree_name'] == $labelsList[$labelIndex -1]['tree_name'][count($labelsList[$labelIndex -1]['tree_name']) - 1]) { 
                                $labelsList[$labelIndex -1]['amount'][count($labelsList[$labelIndex -1]['amount']) - 1] += $capLeft;
                            } else {
                                // push new tree_name
                                array_push($labelsList[$labelIndex -1]['tree_name'], $bag['tree_name']);
                                array_push($labelsList[$labelIndex -1]['amount'], $capLeft);
                            }
                            $labelsList[$labelIndex -1]['total_amount'] += $capLeft;
                            $totalLeft = $bag['amount'] - $capLeft;
                            
                            if (($totalLeft) > 0) {
                                $labelsList[$labelIndex]['bag_no'] = $labelIndex + 1;
                                $labelsList[$labelIndex]['capacity_left'] = $totalLeft;
                                $labelsList[$labelIndex]['tree_category'] = $bag['tree_category'];
                                $labelsList[$labelIndex]['tree_name'] = [$bag['tree_name']];
                                $labelsList[$labelIndex]['amount'] = [$totalLeft];
                                $labelsList[$labelIndex]['total_amount'] = $totalLeft;
                                $labelIndex += 1;
                            }
                        }
                    } else {
                        $totalLeft = $bag['amount'];
                        $labelsList[$labelIndex]['bag_no'] = $labelIndex + 1;
                        $labelsList[$labelIndex]['capacity_left'] = $bag['amount_reminder'];
                        $labelsList[$labelIndex]['tree_category'] = $bag['tree_category'];
                        $labelsList[$labelIndex]['tree_name'] = [$bag['tree_name']];
                        $labelsList[$labelIndex]['amount'] = [$totalLeft];
                        $labelsList[$labelIndex]['total_amount'] = $totalLeft;
                        $labelIndex += 1;
                    }
                } else {
                    $labelsList[$labelIndex]['bag_no'] = $labelIndex + 1;
                    $labelsList[$labelIndex]['capacity_left'] = $bag['amount_reminder'];
                    $labelsList[$labelIndex]['tree_category'] = $bag['tree_category'];
                    $labelsList[$labelIndex]['tree_name'] = [$bag['tree_name']];
                    $labelsList[$labelIndex]['amount'] = [$bag['amount']];
                    $labelsList[$labelIndex]['total_amount'] = $bag['amount'];
                    $labelIndex += 1;
                }
            }
            // update bag number & other detail in labels
            foreach ($labelsList as $labelsListIndex => $label) {
                $labelsList[$labelsListIndex]['bag_no'] = $labelsList[$labelsListIndex]['bag_no'] . '/' . $labelIndex;
                $labelsList[$labelsListIndex]['bag_code'] = $labelsList[$labelsListIndex]['bag_no'] . '-' . $GetPHUDetail->lahan_no;
                $labelsList[$labelsListIndex]['qr_code'] = $this->generateqrcode(($labelsList[$labelsListIndex]['bag_no'] . '-' . $GetPHUDetail->lahan_no));
                $labelsList[$labelsListIndex]['pic_name'] = $GetPHUDetail->pic_name;
                $labelsList[$labelsListIndex]['lahan_no'] = $GetPHUDetail->lahan_no;
                $labelsList[$labelsListIndex]['date'] = date("d/m/Y", strtotime($GetPHUDetail->distribution_time));
                $labelsList[$labelsListIndex]['location'] = $GetPHUDetail->address;
            }
            return [
                'lubangTanamDetail' => $GetPHUDetail,
                'listLabel' => $labelsList];
        }else{
            $rslt =  $this->ResultReturn(404, 'doesnt match data', 'No Lahan Tidak ada dalam SOSTAM');
            return response()->json($rslt, 404);
        }
    } 
    
    public function CetakLabelUmumLubangTanam(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lahan_no' => 'required' 
        ]);
        
        if($validator->fails()){
            $rslt = $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        }
        
        $labels = $this->generateUmumSeedlingLabels($request->lahan_no);
        // return $labels;
        return view('cetakLabelUmumLubangTanam', $labels);
    }
    
    public function CetakUmumBuktiPenyerahan(Request $request){
        $validator = Validator::make($request->all(), [
            'lahan_no' => 'required'
        ]);
        
        $lahan_no = $request->lahan_no;

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        }
        
        $labels = $this->generateUmumSeedlingLabels($request->lahan_no);
        
        $GetPHUDetail = LahanUmum::
                leftjoin('employees', 'employees.nik', '=', 'lahan_umums.employee_no')
                ->select('lahan_umums.id', 'lahan_umums.lahan_no', 'lahan_umums.program_year', 'lahan_umums.latitude', 'lahan_umums.longitude', 'lahan_umums.distribution_date', 'lahan_umums.planting_realization_date', 'lahan_umums.employee_no', 'lahan_umums.is_verified', 'lahan_umums.verified_by', 'lahan_umums.is_dell', 'employees.name', 'lahan_umums.pic_lahan')
                ->where('lahan_umums.lahan_no', '=', $lahan_no)
                ->first();
                
        return view('cetakBuktiUmumPenyerahan', [
            'lubangTanamDetail' => $labels['lubangTanamDetail'],
            'listvalbag' => $labels['listLabel'],
        ]);
    }
    
    // Generate QR Code
    private function generateqrcode ($val)
    {
        $qrcode = QrCode::size(90)->generate($val);
        return $qrcode;
    }
    
    // get Distribution Report
    public function GetUmumDistributionReport(Request $req) {
        //$dist_no = $req->distribuiton_no;
        $distribution_date = $req->distribution_date;
        $py = $req->program_year;
        $created_by = $req->created_by;
        
        $datas = LahanUmumDistribution::
            leftjoin('lahan_umums', 'lahan_umums.mou_no', 'lahan_umum_distributions.mou_no')
            ->leftjoin('employees', 'employees.nik', 'lahan_umums.employee_no')
            ->leftjoin('managementunits', 'managementunits.mu_no', 'lahan_umums.mu_no')
            ->select(
                'lahan_umum_distributions.distribution_no',
                'lahan_umum_distributions.mou_no',
                'lahan_umums.employee_no',
                'lahan_umum_distributions.distribution_date',
                'lahan_umum_distributions.status',
                'employees.name as pic_t4t',
                'lahan_umums.pic_lahan',
                'managementunits.name as mu_name'
                )
            ->where([
                ['lahan_umum_distributions.distribution_no', 'LIKE', 'D-'.$py.'%'],
                'lahan_umum_distributions.is_dell' => 0,
            ])
            ->whereDate('lahan_umum_distributions.distribution_date', $distribution_date)
            ->groupBy('lahan_umum_distributions.mou_no');
            
            
        if ($req->created_by) {
            $datas = $datas->where('lahan_umums.created_by', $created_by);
        }
        
        $datas = $datas->get();
        
        // get Total Bags
        foreach($datas as $index => $data) {
            $datas[$index]->sum_all_bags = count(LahanUmumDistributionDetail::where([
                    'distribution_no' =>  $data->distribution_no
                ])->groupBy('bag_number')->get());
            $datas[$index]->sum_loaded_bags = count(LahanUmumDistributionDetail::where([
                    'distribution_no' =>  $data->distribution_no,
                    'is_loaded' => 1
                ])->groupBy('bag_number')->get());
            $datas[$index]->sum_distributed_bags = count(LahanUmumDistributionDetail::where([
                    'distribution_no' =>  $data->distribution_no,
                    'is_loaded' => 1,
                    'is_distributed' => 1
                ])->groupBy('bag_number')->get());
            $datas[$index]->adj_kayu = LahanUmumAdjustment::where('distribution_no', $data->distribution_no)->where('tree_category', 'KAYU')->sum('total_tree_received') ?? 0;
            $datas[$index]->adj_mpts = LahanUmumAdjustment::where('distribution_no', $data->distribution_no)->where('tree_category', 'MPTS')->sum('total_tree_received') ?? 0;
            $datas[$index]->adj_crops = LahanUmumAdjustment::where('distribution_no', $data->distribution_no)->where('tree_category', 'CROPS')->sum('total_tree_received') ?? 0;
        }
            
        $rslt =  $this->ResultReturn(200, 'success', $datas);
        return response()->json($rslt, 200); 
    }
    
    //get Distribution Report Detail
    public function GetUmumDistributionDetailReport(Request $req)
    {
        $dist = $req->distribution_no;
        
        $datas = LahanUmumDistribution::
            join('lahan_umums', 'lahan_umums.mou_no', 'lahan_umum_distributions.mou_no')
            ->join('managementunits', 'managementunits.mu_no', 'lahan_umums.mu_no')
            ->join('employees', 'employees.nik', 'lahan_umum_distributions.employee_no')
            ->select('lahan_umum_distributions.distribution_no as distribution_no',
                     'lahan_umum_distributions.distribution_date as distribution_date',
                     'lahan_umum_distributions.mou_no as mou_no',
                     'lahan_umum_distributions.employee_no as employee_no',
                     'lahan_umums.mu_no as mu_no',
                     'lahan_umum_distributions.distribution_note as distribution_note',
                     'lahan_umum_distributions.distribution_photo as distribution_photo',
                     'lahan_umum_distributions.status as status',
                     'lahan_umum_distributions.total_bags as total_bags',
                     'lahan_umum_distributions.total_tree_amount as total_tree_amount',
                     'lahan_umum_distributions.is_loaded as is_loaded',
                     'lahan_umum_distributions.loaded_by as loaded_by',
                     'lahan_umum_distributions.is_distributed as is_distributed',
                     'lahan_umum_distributions.distributed_by as distributed_by',
                     'lahan_umum_distributions.created_at as created_at',
                     'lahan_umum_distributions.updated_at as updated_at',
                     'lahan_umum_distributions.is_dell as is_dell',
                     'lahan_umum_distributions.deleted_by as deleted_by',
                     'lahan_umum_distributions.approved_by as approved_by',
                     'managementunits.name as mu_name')
            ->where('lahan_umum_distributions.distribution_no', '=', $dist)
            ->where('lahan_umum_distributions.is_dell', '=', 0)
            ->first();
            
            if($datas){
                $getLahanUmum = LahanUmum::where('mou_no', $datas->mou_no)->first();
                $getDetailDistribution =  DB::table('lahan_umum_distribution_details')
                ->select('lahan_umum_distribution_details.id',
                         'lahan_umum_distribution_details.distribution_no',
                         'lahan_umum_distribution_details.bag_number',
                         'lahan_umum_distribution_details.tree_name',
                         'lahan_umum_distribution_details.tree_category',
                         'lahan_umum_distribution_details.tree_amount',
                         'lahan_umum_distribution_details.is_loaded',
                         'lahan_umum_distribution_details.loaded_by',
                         'lahan_umum_distribution_details.is_distributed',
                         'lahan_umum_distribution_details.distributed_by',
                         'lahan_umum_distribution_details.created_at',
                         'lahan_umum_distribution_details.updated_at')
                ->where('lahan_umum_distribution_details.distribution_no', '=', $datas->distribution_no)
                ->get();
                
                $getDetailAdjustment = DB::table('lahan_umum_adjustments')
                ->leftjoin('tree_locations', 'tree_locations.tree_code', 'lahan_umum_adjustments.tree_code')
                ->select('lahan_umum_adjustments.id',
                         'lahan_umum_adjustments.distribution_no',
                         'lahan_umum_adjustments.lahan_no',
                         'lahan_umum_adjustments.adjust_date',
                         'lahan_umum_adjustments.tree_code',
                         'lahan_umum_adjustments.tree_category',
                         'lahan_umum_adjustments.total_distributed',
                         'lahan_umum_adjustments.broken_seeds',
                         'lahan_umum_adjustments.missing_seeds',
                         'lahan_umum_adjustments.total_tree_received',
                         'lahan_umum_adjustments.planting_year',
                         'lahan_umum_adjustments.is_dell',
                         'lahan_umum_adjustments.is_verified',
                         'lahan_umum_adjustments.created_by',
                         'lahan_umum_adjustments.approved_by',
                         'lahan_umum_adjustments.updated_by',
                         'lahan_umum_adjustments.created_at',
                         'lahan_umum_adjustments.updated_at',
                         'tree_locations.tree_name')
                ->where('lahan_umum_adjustments.distribution_no', '=', $datas->distribution_no)
                ->where('tree_locations.mu_no', '=', $datas->mu_no)
                ->get();
                
                $DistributionDetail = [
                     'distribution_no'=>$datas->distribution_no,
                     'mou_no' => $datas->mou_no,
                     'distribution_date'=>$datas->distribution_date,
                     'distribution_note'=>$datas->distribution_note,
                     'distribution_photo'=>$datas->distribution_photo,
                     'status'=>$datas->status,
                     'total_bags'=>$datas->total_bags,
                     'total_tree_amount'=>$datas->total_tree_amount,
                     'is_loaded'=>$datas->is_loaded,
                     'loaded_by'=>$datas->loaded_by,
                     'is_distributed'=>$datas->is_distributed,
                     'distributed_by'=>$datas->distributed_by,
                     'created_at'=>$datas->created_at,
                     'updated_at'=>$datas->updated_at,
                     'is_dell'=>$datas->is_dell,
                     'deleted_by'=>$datas->deleted_by,
                     'approved_by'=>$datas->approved_by,
                     'distributionDetail'=>$getDetailDistribution,
                     'distributionAdjustment'=>$getDetailAdjustment,
                     'pic_lahan' => $getLahanUmum->pic_lahan,
                     'pic_t4t' => DB::table('employees')->where('nik', $getLahanUmum->employee_no)->first()->name ?? '-'
                ];
                
                $data = $DistributionDetail;
                
                $rslt =  $this->ResultReturn(200, 'success', $data);
                return response()->json($rslt, 200);
            }
            
        $rslt =  $this->ResultReturn(200, 'success', $datas);
        return response()->json($rslt, 200); 
    }
    
    public function DistributionVerificationPM(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'distribution_no' => 'required|exists:lahan_umum_distributions,distribution_no'
        ]);
        
        // validation fails
        if($validate->fails()){
            $rslt =  $this->ResultReturn(400, $validate->errors()->first(), $validate->errors()->first());
            return response()->json($rslt, 400);
        } else {
            $dn = $request->distribution_no;
        }
        
        $verif = DB::table('lahan_umum_distributions')->where('distribution_no', '=', $dn)->first();
        
        if($verif){
            LahanUmumDistribution::where('distribution_no', '=', $dn)
                ->update([
                    'updated_at' => Carbon::now(),
                    'approved_by' => $request->approved_by,
                    'status' => 2
            ]);

            $listTree = $request->list_trees;

            foreach($listTree as $val){
                $tree_code = DB::table('tree_locations')->where('tree_name', '=', $val['tree_name'])->first()->tree_code ?? '-';
                
                LahanUmumAdjustment::where([
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
    
    public function CreateDistributionLahanUmum(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lahan_no' => 'required|exists:lahan_umums,lahan_no',
            'program_year' => 'required'
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        } else {
            $lahan_no = $request->lahan_no;
            $py = $request->program_year;
            $user = Auth::user()->email;
            $mou_no = $request->mou_no;
        }
        
        $LUData = LahanUmum::where(['lahan_no' => $lahan_no, ['is_checked', '!=', 1]])->first();
        
        if($LUData) {
            $DCreate = [];
            $DCreate['distribution_no'] = 'D-' . $py . '-' . $mou_no;
            $DCreate['distribution_date'] = LahanUmum::where('lahan_no', $lahan_no)->first()->distribution_date;
            $labels = $this->generateUmumSeedlingLabels($lahan_no)['listLabel'] ?? [];
            $DCreate['total_tree_amount'] = 0;
            $DCreate['total_bag'] = 0;
            
            $distributionDataExist = LahanUmumDistribution::where('distribution_no', $DCreate['distribution_no'])->count();
            if($distributionDataExist == 0) {
                //create new Distribution Data
                $createDistribution = LahanUmumDistribution::create([
                    'distribution_no' => $DCreate['distribution_no'],
                    'distribution_date' => $DCreate['distribution_date'],
                    'employee_no' => $LUData->employee_no,
                    'mou_no' => $LUData->mou_no,
                    'distribution_note' => $request->distribution_note,
                    'distribution_photo' => $request->distribution_photo,
                    'status' => 0,
                    'total_bags' => $DCreate['total_bag'],
                    'total_tree_amount' => $DCreate['total_tree_amount'],
                    'is_loaded' => 0,
                    'loaded_by' => $user,
                    'is_distributed' => 0,
                    'distributed_by' => '',
                    'is_dell' => 0,
                ]);
            }
            
            //create distribution details data
            foreach($labels as $label) {
                foreach($label['tree_name'] as $labelTreeIndex => $labelTreeName) {
                    $createDistributionDetail = LahanUmumDistributionDetail::create([
                        'distribution_no' => $DCreate['distribution_no'],
                        'bag_number' => $label['bag_code'],
                        'tree_name' => $labelTreeName,
                        'tree_category' => $label['tree_category'],
                        'tree_amount' => $label['amount'][$labelTreeIndex],
                        'is_loaded' => 0,
                        'loaded_by' => '-',
                        'is_distributed' => 0,
                        'distributed_by' => '-'
                    ]);
                    $DCreate['total_tree_amount'] += $label['amount'][$labelTreeIndex];
                }
                $DCreate['total_bag'] += 1;
            }
            
             // update total tree_amount & total bag
            $distributionNewData = LahanUmumDistribution::where('distribution_no', $DCreate['distribution_no'])->first();
            $distributionNewData->update([
                'total_tree_amount' => $distributionNewData->total_tree_amount + $DCreate['total_tree_amount'],
                'total_bags' => $distributionNewData->total_bags + $DCreate['total_bag'],
            ]);
            
            //updating cheked list
            LahanUmum::where('lahan_no', '=', $lahan_no)->update([
                'updated_at' => Carbon::now(),
                'checked_by' => $user,
                'is_checked' => 1,
            ]);
            
            $rslt = $this->ResultReturn(200, 'success', 'Lahan created!');
            return response()->json($rslt, 200);
        }
        $rslt =  $this->ResultReturn(404, 'doesnt match data', 'Lahan data not found.');
        return response()->json($rslt, 404);
    }
    
    public function UpdatedDistributionLahanUmum(Request $req){
        // validate request
        $validate = Validator::make($req->all(), [
            'bags_number' => 'required',
            'program_year' => 'required'
        ]);
        
        // validation fails
        if($validate->fails()){
            $rslt =  $this->ResultReturn(400, $validate->errors()->first(), $validate->errors()->first());
            return response()->json($rslt, 400);
        }else {
            $py = $req->program_year;
            $mou_no = $req->mou_no;
        }
        // datas
        $datas = LahanUmumDistributionDetail::where('is_distributed', 0)->whereIn('bag_number', $req->bags_number);
        
        if($req->distribution_photo) LahanUmumDistribution::where('mou_no', $mou_no)->update(['distribution_photo' => $req->distribution_photo]);
        
        if ($datas->count() > 0) {
            // update
            $datas->update([
                'is_distributed' => 1,
                'distributed_by' => Auth::user()->email
            ]);
            
            $distribution = LahanUmumDistribution::where(['mou_no' => $req->mou_no])->pluck('distribution_no');
            $bagsLoaded = LahanUmumDistributionDetail::where('is_loaded', 1)->whereIn('distribution_no', $distribution)->count();
            $bagsAll = LahanUmumDistributionDetail::whereIn('distribution_no', $distribution)->count();
            
            if ($bagsLoaded == $bagsAll) {
                LahanUmumDistribution::where(['mou_no' => $req->mou_no])->update(['is_loaded' => 1, 'loaded_by' => Auth::user()->email]);
            }
            
            return response()->json(('Update distributed labels success!'), 200);
        } else if ($req->distribution_photo) {
            return response()->json('Update photo success.', 200);
        } else {
            return response()->json('Data not found, or already scanned.', 404);
        }
    }
    
    public function GetUmumDistributionAdjustment(Request $req) {
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
            $existingMoU = DB::table('lahan_umum_monitorings')->pluck('mou_no');
        }
        
        $datas = DB::table('lahan_umum_adjustments')
            ->join('lahan_umums', 'lahan_umums.lahan_no', 'lahan_umum_adjustments.lahan_no')
            ->join('employees', 'employees.nik', 'lahan_umums.employee_no')
            ->select(
                'lahan_umums.mou_no',
                'employees.name as pic_t4t_name',
                'lahan_umums.pic_lahan as pic_lahan_name'
            )
            ->where([
                'lahan_umum_adjustments.is_verified' => 1,
                'lahan_umums.active' => 1,
                'lahan_umums.is_verified' => 2,
                'lahan_umums.is_dell' => 0
                // ['lahan_umum_adjustments.is_dell', '!=', 1]
                
            ])
            ->whereNotIn('lahan_umums.mou_no', $existingMoU);
            
        if ($req->created_by) {
            $datas = $datas->whereIn('lahan_umums.created_by', explode(",", $req->created_by));
        }
        
        $datas = $datas->orderBy('lahan_umum_adjustments.created_at', 'DESC')
            ->groupBy('lahan_umums.mou_no')
            ->get();
            
        return response()->json($datas, 200);
    }
    
    public function CreateLahanUmumAdjustment(Request $request)
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
        
        $dist = DB::table('lahan_umum_distributions')->where('distribution_no', '=', $dn)->first();
        
        LahanUmumAdjustment::where('distribution_no', '=', $dn)->delete();
            
            if($dist){
                LahanUmumDistribution::where('distribution_no', '=', $dn)
                    ->update([
                        'updated_at' => Carbon::now(),
                        'approved_by' => $request->approved_by,
                        'status' => 1
                ]);
                
                $listTree = $request->list_trees;
                // return response()->json($request->all(), 200);
        
                foreach($listTree as $val){
                    LahanUmumAdjustment::create([
                        'distribution_no' => $dist->distribution_no,
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
    
    private function getNurseryAlocationReverse($nursery) {
        $nur = [
            'Arjasari' => ['022', '024', '025', '020', '029'],
            'Ciminyak' => ['023', '026', '027', '021'],
            'Kebumen' => ['019'],
            'Pati' => ['015']
        ];
        
        return $nur[$nursery];
    }
    
    public function GetLoadingLineLahanUmum(Request $req)
    {
        $py = $req->program_year;
        $typegetdata = $req->typegetdata;
        $getmu = $req->mu;
        $getvillage = $req->village;
        if($getmu){$mu='%'.$getmu.'%';}
        else{$mu='%%';}
        if($getvillage){$village='%'.$getvillage.'%';}
        else{$village='%%';}
        
        $datas = LahanUmumDistribution::
            leftjoin('lahan_umums', 'lahan_umums.mou_no', '=', 'lahan_umum_distributions.mou_no')
            ->leftjoin('employees', 'employees.nik', '=', 'lahan_umums.employee_no')
            ->join('managementunits', 'managementunits.mu_no', '=', 'lahan_umums.mu_no')
            ->select('lahan_umum_distributions.is_loaded',
                     'lahan_umums.mou_no as mou_no',
                     'lahan_umums.pic_lahan as pic_lahan',
                     'managementunits.name as mu_name',
                     'employees.name as employee_name',
                     'lahan_umum_distributions.employee_no',
                     'lahan_umum_distributions.total_bags',
                     'lahan_umum_distributions.total_tree_amount')
            ->where([
                'lahan_umum_distributions.is_dell' => 0,
                ['lahan_umums.mu_no', 'LIKE', $mu],
                ['lahan_umums.village', 'LIKE', $village],
                ['lahan_umum_distributions.distribution_no', 'LIKE', 'D-'.$py.'%']
            ])
            ->whereDate('lahan_umum_distributions.distribution_date', $req->distribution_date);
            
        if($req->nursery != 'All' && $req->nursery != '') {
            $listMU = $this->getNurseryAlocationReverse($req->nursery);
            $datas = $datas->whereIn('lahan_umums.mu_no', $listMU);
        }
        
        $datas = $datas
            ->groupBy('lahan_umum_distributions.mou_no')
            ->get();
            
        foreach($datas as $dataIndex => $data) {
            $PHQuery = LahanUmum::where([
                'mou_no' => $data->mou_no,
                'is_dell' => 0,
                'is_verified' => 2,
                'program_year' => $py
            ]);
            
            //return $PHQuery->get();
            $totalPHAll = $PHQuery->count();
            $totalPHPrinted = $PHQuery->where('is_checked', 1)->count();
            $datas[$dataIndex]['ph_printed'] = $totalPHPrinted;
            $datas[$dataIndex]['ph_all'] = $totalPHAll;
            $datas[$dataIndex]['printed_progress'] = round($totalPHPrinted / ($totalPHAll == 0 ? 1 : $totalPHAll) * 100);
        }
        
        $rslt = $this->ResultReturn(200, 'success', $datas);
        return response()->json($rslt, 200);
    }
    
    public function GetLoadingLineDetailLahanUmum(Request $req)
    {
        $validate = Validator::make($req->all(), [
            'mou_no' => 'required|exists:lahan_umums,mou_no',
            'program_year' => 'required',
        ]);
        
        // validation fails
        if($validate->fails()){
            $rslt =  $this->ResultReturn(400, $validate->errors()->first(), $validate->errors()->first());
            return response()->json($rslt, 400);
        } else {
            $py = $req->program_year;
            $lu = LahanUmum::where('mou_no', $req->mou_no)->first();
        }
        
        $dist = LahanUmumDistribution::
            leftjoin('lahan_umums', 'lahan_umums.mou_no', '=', 'lahan_umum_distributions.mou_no')
            ->leftjoin('employees', 'employees.nik', '=', 'lahan_umums.employee_no')
            ->join('managementunits', 'managementunits.mu_no', '=', 'lahan_umums.mu_no')
            ->select('lahan_umum_distributions.distribution_no',
                     'lahan_umum_distributions.distribution_date',
                     'employees.name as employee_name',
                     'managementunits.name as mu_name',
                     'lahan_umums.pic_lahan as pic_lahan',
                     'lahan_umums.mou_no as mou_no',
                     'lahan_umums.lahan_no as lahan_no',
                     'lahan_umum_distributions.total_bags',
                     'lahan_umum_distributions.total_tree_amount',
                     'lahan_umum_distributions.is_loaded')
            ->where(['lahan_umums.lahan_no' => $lu->lahan_no,  ['lahan_umum_distributions.distribution_no', 'LIKE', 'D-'.$py.'%']])->get();
        
        //    
            
        $totalBags = 0;
        $totalTreesAmount = 0;
        if(count($dist) > 0) {
            foreach($dist as $disIndex => $distribution) {
                $dist[$disIndex]['bags_number'] = LahanUmumDistributionDetail::where('distribution_no', $distribution->distribution_no)->groupBy('bag_number')->orderBy('id')->pluck('bag_number');
                $dist[$disIndex]['bags_number_loaded'] = LahanUmumDistributionDetail::where(['distribution_no' => $distribution->distribution_no, 'is_loaded' => 1])->groupBy('bag_number')->orderBy('id')->pluck('bag_number');
                $dist[$disIndex]['labels_list'] = LahanUmumDistributionDetail::where('distribution_no', $distribution->distribution_no)->get();
                $totalBags += $distribution->total_bags;
                $totalTreesAmount += $distribution->total_tree_amount;
            }
            return response()->json([
                'total_bags' => $totalBags,
                'total_trees_amount' => $totalTreesAmount,
                'distribution_details' => $dist
            ], 200);
        } else {
            return response()->json('Data not found', 404);
        }
    }
    
    public function UpdatedLoadingLahanUmum(Request $req){
        // validate request
        $validate = Validator::make($req->all(), [
            'bags_number' => 'required',
            'program_year' => 'required'
        ]);
        
        // validation fails
        if($validate->fails()){
            $rslt =  $this->ResultReturn(400, $validate->errors()->first(), $validate->errors()->first());
            return response()->json($rslt, 400);
        }else {
            $py = $req->program_year;
            $mou_no = $req->mou_no;
        }
        // datas
        $datas = LahanUmumDistributionDetail::where('is_distributed', 0)->whereIn('bag_number', $req->bags_number);
        
        if ($datas->count() > 0) {
            // update
            $datas->update([
                'is_loaded' => 1,
                'loaded_by' => Auth::user()->email
            ]);
            
            $distribution = LahanUmumDistribution::where(['mou_no' => $req->mou_no])->pluck('distribution_no');
            $bagsLoaded = LahanUmumDistributionDetail::where('is_loaded', 1)->whereIn('distribution_no', $distribution)->count();
            $bagsAll = LahanUmumDistributionDetail::whereIn('distribution_no', $distribution)->count();
            
            if ($bagsLoaded == $bagsAll) {
                LahanUmumDistribution::where(['mou_no' => $req->mou_no])->update(['is_loaded' => 1, 'loaded_by' => Auth::user()->email]);
            }
            
            return response()->json(('Update distributed labels success!'), 200);
        } else if ($req->distribution_photo) {
            return response()->json('Update photo success.', 200);
        } else {
            return response()->json('Data not found, or already scanned.', 404);
        }
    }
// END: Distribusi }
    
//MONITORING REALISASI TANAM MODULE {
    public function GetMonitoringLahanUmumAdmin(Request $request)
    {
        $getmu = $request->mu;
        $getvillage = $request->village;
        $getpy = $request->program_year;
        $per_page = $request->per_page ?? 20;
        if($getmu){$mu='%'.$getmu.'%';}
        else{$mu='%%';}
        if($getvillage){$village='%'.$getvillage.'%';}
        else{$village='%%';}
        
        $searchColumn = [
            'mu_name' => 'managementunits.name',
            'mou_no' => 'lahan_umums.mou_no',
            'pic_t4t_name' => 'employees.name',
            'pic_lahan_name' => 'lahan_umums.pic_lahan',
            'lahan_no' => 'lahan_umum_monitorings.lahan_no',
            'is_validate' => 'lahan_umum_monitorings.is_verified'
        ];
        
        $GetMon = LahanUmumMonitoring::
            join('lahan_umums', 'lahan_umums.mou_no', '=', 'lahan_umum_monitorings.mou_no')
            ->leftjoin('managementunits', 'managementunits.mu_no', '=', 'lahan_umums.mu_no')
            ->join('employees', 'employees.nik', '=', 'lahan_umums.employee_no')
            ->select('lahan_umum_monitorings.id',
                   'lahan_umum_monitorings.monitoring_no',
                   'lahan_umums.mou_no',
                   'lahan_umum_monitorings.lahan_no',
                   'lahan_umum_monitorings.program_year',
                   'lahan_umum_monitorings.planting_date',
                   'lahan_umum_monitorings.is_verified as is_validate',
                   'lahan_umum_monitorings.verified_by',
                   'lahan_umum_monitorings.lahan_condition',
                   'lahan_umum_monitorings.qty_kayu',
                   'lahan_umum_monitorings.qty_mpts',
                   'lahan_umum_monitorings.qty_crops',
                   'lahan_umum_monitorings.qty_std',
                   'lahan_umum_monitorings.is_dell',
                   'lahan_umum_monitorings.created_by',
                   'lahan_umum_monitorings.created_at',
                   'lahan_umums.employee_no',
                   'employees.name as pic_t4t_name',
                   'lahan_umums.pic_lahan as pic_lahan_name',
                   'managementunits.mu_no as mu_no',
                   'managementunits.name as mu_name')
            ->where([
                ['lahan_umum_monitorings.is_dell','=',0],
                [$searchColumn[$request->search_column], 'LIKE', '%'.$request->search_value.'%'],    
                ['lahan_umums.mu_no','like',$mu],
                ['lahan_umums.village','like',$village],
                ['lahan_umum_monitorings.program_year', '=', $getpy]
            ]);
            
        if ($request->created_by) {
            $GetMon = $GetMon->whereIn('lahan_umum_monitorings.created_by', explode(",", $request->created_by));
        }
        
        if ($request->sortBy) {
            $sortBy = explode(',', $request->sortBy);
            $sortDesc = explode(',', $request->sortDesc);
            foreach ($sortBy as $sortIndex => $sort) {
                $GetMon = $GetMon->orderBy($sort, ($sortDesc[$sortIndex] == 'true' ? 'DESC' : 'ASC'));
            }
        }
        
        $GetMon = $GetMon->orderBy('lahan_umum_monitorings.created_at', 'DESC')->groupBy('lahan_umums.mou_no')->paginate($per_page);
            
        //$data = [ 'data' => $GetMon ];
        $rslt =  $this->ResultReturn(200, 'success', $GetMon);
        return response()->json($rslt, 200);
    }
    
    public function GetMonitoringDetailLahanUmumAdmin(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'monitoring_no' => 'required|exists:lahan_umum_monitorings,monitoring_no',
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        } else {
            $main = DB::table('lahan_umum_monitorings')->where('monitoring_no', $req->monitoring_no)->first();
            
            $mainLahan = DB::table('lahan_umums')->where('lahan_no', explode(',', $main->lahan_no)[0])->first();
            
            $main->mou_no = $mainLahan->mou_no ?? '-';
            $main->nama_pic_lahan = $mainLahan->pic_lahan ?? '?';
            $main->nama_pic_t4t = DB::table('employees')->where('nik', $mainLahan->employee_no)->first()->name ?? '?';
            $main->gambar1 = $main->photo1;
            $main->gambar2 = $main->photo2;
            $main->gambar3 = $main->photo3;
            
            
            $detail = DB::table('lahan_umum_monitoring_details')
                        ->leftJoin('trees', 'trees.tree_code', '=', 'lahan_umum_monitoring_details.tree_code')
                        ->select('lahan_umum_monitoring_details.*', 'lahan_umum_monitoring_details.qty as amount', 'trees.tree_name', 'trees.tree_category')
                        ->where('lahan_umum_monitoring_details.monitoring_no','=',$req->monitoring_no)
                        ->get();
            
            $data = ['list_detail'=>$detail, 'data'=>$main];
            $rslt =  $this->ResultReturn(200, 'success', $data);
            
            return response()->json($rslt, 200);
        }
        
    }
    
    public function CreateMonitoringLahanUmum(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'created_by' => 'required',
            'monitoring_no' => 'unique:lahan_umum_monitorings, monitoring_no',
            'mou_no' => 'required',
            //'planting_date' => 'required', 
            //'program_year' => 'required',
            'list_trees' => 'required',
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        }else{
            $Lahan = LahanUmum::where('mou_no', '=', $request->mou_no)->first();
        }
        
        if($Lahan){
            $year = Carbon::now()->format('Y');
            $monitoring_no = 'UMO1-'.$request->program_year.'-'.$request->mou_no;

            $is_verified = 0;
            $verified_by = '-';
            if($request->verified_by){
                $is_verified = 1;
                $verified_by = $request->verified_by;
            }

            $pohon_mpts = 0;
            $pohon_non_mpts = 0;
            $pohon_bawah = 0;
            $qty_std = 0;
        
            $updatedTrees = [];
            
            foreach($request->list_trees as $val){
                LahanUmumMonitoringDetail::create([
                    'monitoring_no' => $monitoring_no,
                    'tree_code' => $val['tree_code'],
                    'qty' => $val['qty'],
                    'status' => $val['status'],
                    'condition'=> $val['condition'],
                    // 'planting_date'=> $val['planting_date'],
                    'planting_date' => $request->planting_date,
                    'created_at'=>Carbon::now(),
                    'updated_at'=>Carbon::now()
                ]);
                
                $trees_get = TreeLocation::where('tree_code', '=', $val['tree_code'])->first();
                
                if($trees_get->tree_category == "Pohon Buah"){
                    $pohon_mpts = $pohon_mpts + $val['qty'];
                }else if ($trees_get->tree_category == "Tanaman_Bawah_Empon"){
                    $pohon_bawah = $pohon_bawah + $val['qty'];
                }else{
                    $pohon_non_mpts = $pohon_non_mpts + $val['qty'];
                }
                
                array_push($updatedTrees, ((DB::table('trees')->where('tree_code', $val['tree_code'])->first()->tree_name ?? 'jenis_pohon') . " => " . ($val['qty'].'_'.$val['status'].'_'.$val['condition'])));
            }
            
            $updatedTreesToString = implode(", ", $updatedTrees);
            
            LahanUmumMonitoring::create([
                'monitoring_no' => $monitoring_no,
                'program_year' => $request->program_year,
                'planting_date' => $request->planting_date,
                'lahan_no' => $request->lahan_no,
                'lahan_condition' => $request->lahan_condition,
                
                'qty_kayu' => $pohon_non_mpts,
                'qty_mpts' => $pohon_mpts,
                'qty_crops' => $pohon_bawah,
                'qty_std' => $this->ReplaceNull($request->qty_std, 'int'),
                'photo1' => $request->photo1,
                'photo2' => $this->ReplaceNull($request->photo2, 'string'),
                'photo3' => $this->ReplaceNull($request->photo3, 'string'),
                'is_verified' => $is_verified,
                'verified_by' => $verified_by,

                'created_by' => $request->created_by,

                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),

                'is_dell' => 0,
                'mou_no' => $request->mou_no
            ]);
        
            $this->createLogByMOU([
                'status' => 'Created',
                'type' => 'Monitoring-1',
                'mou_no' => $request->mou_no,
                'message' => " (planting_date => $request->planting_date, lahan_no => $request->lahan_no, lahan_condition => $request->lahan_condition, qty_std => $request->qty_std,  $updatedTreesToString)" 
            ]);
            
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200);
        }else{
            $rslt =  $this->ResultReturn(400, 'doesnt match data', 'doesnt match data');
            return response()->json($rslt, 400);
        }
    }
    
    public function UpdateMonitoringLahanUmum(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mou_no' => 'required',
            'created_by' => 'required', 
            'planting_date' => 'required', 
            'program_year' => 'required',
            'qty_std' => 'required',
            'list_trees' => 'required',
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        }
        
        $monitoring = LahanUmumMonitoring::where('mou_no','=',$request->mou_no)->first();
        
        if($monitoring){
            $monitoring_no = $monitoring->monitoring_no;
            
            LahanUmumMonitoringDetail::where('monitoring_no', $monitoring->monitoring_no)->delete();

            $pohon_mpts = 0;
            $pohon_non_mpts = 0;
            $pohon_bawah = 0;
            
            $updatedTrees = [];
            
            foreach($request->list_trees as $val){
                LahanUmumMonitoringDetail::create([
                    'monitoring_no' => $monitoring_no,
                    'tree_code' => $val['tree_code'],
                    'qty' => $val['qty'],
                    'status' => $val['status'],
                    'condition' => $val['condition'],
                    'planting_date' => $request->planting_date,
                    'updated_at'=>Carbon::now()
                ]);

                $trees_get = DB::table('trees')->where('tree_code','=',$val['tree_code'])->first();

                if( $trees_get->tree_category == "Pohon_Buah"){
                    $pohon_mpts = $pohon_mpts + $val['qty'];
                }else if($trees_get->tree_category == "Tanaman_Bawah_Empon"){
                    $pohon_bawah = $pohon_bawah + $val['qty'];
                }else{
                    $pohon_non_mpts = $pohon_non_mpts + $val['qty'];
                }
                
                array_push($updatedTrees, ((DB::table('trees')->where('tree_code', $val['tree_code'])->first()->tree_name ?? 'jenis_pohon') . " => " . ($val['qty'].'_'.$val['status'].'_'.$val['condition'])));
            }
            
            $updatedTreesToString = implode(", ", $updatedTrees);
            
            LahanUmumMonitoring::where('mou_no', '=', $request->mou_no)
            ->update([
                'planting_date' => $request->planting_date,
                'lahan_no' => $request->lahan_no,
                'lahan_condition' => $request->lahan_condition,
                'photo1' => $request->photo1,
                'photo2' => $this->ReplaceNull($request->photo2, 'string'),
                'photo3' => $this->ReplaceNull($request->photo3, 'string'),
                'qty_std' => $this->ReplaceNull($request->qty_std, 'int'),

                'qty_kayu' => $pohon_non_mpts,
                'qty_mpts' => $pohon_mpts,
                'qty_crops' => $pohon_bawah,

                'updated_at'=>Carbon::now(),

                // 'is_dell' => 0
            ]);
        
            $this->createLogByMOU([
                'status' => 'Updated',
                'type' => 'Monitoring-1',
                'mou_no' => $request->mou_no,
                'message' => " (planting_date => $request->planting_date, lahan_no => $request->lahan_no, lahan_condition => $request->lahan_condition, qty_std => $request->qty_std,  $updatedTreesToString)" 
            ]);
            
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200);
        }else{
            $rslt =  $this->ResultReturn(400, 'doesnt match data', 'doesnt match data');
            return response()->json($rslt, 400);
        }
    }
    
    public function SoftDeleteMonitoringLahanUmum(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'monitoring_no' => 'required',
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        }  
        
        $monitoring_no = $request->monitoring_no;
        $monitoring = DB::table('lahan_umum_monitorings')->where('monitoring_no','=',$monitoring_no)->first();
        
        if($monitoring){

            LahanUmumMonitoring::where('monitoring_no', '=', $monitoring_no)
            ->update([    
                'updated_at'=>Carbon::now(),    
                'is_dell' => 1
            ]);

            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200); 
        }else{
            $rslt =  $this->ResultReturn(400, 'doesnt match data', 'doesnt match data');
            return response()->json($rslt, 400);
        }
    }

    public function ValidateMonitoringLahanUmum(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'monitoring_no' => 'required|exists:lahan_umum_monitorings,monitoring_no',
            'validate_by' => 'required',
            'list_trees' => 'required',
            'is_verified' => 'required'
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        } else $monitoring_no = $request->monitoring_no;
        
        // Update Trees Detail
        DB::table('lahan_umum_monitoring_details')->where('monitoring_no', $monitoring_no)->delete();

        $pohon_mpts = 0;
        $pohon_non_mpts = 0;
        $pohon_bawah = 0;
        
        $updatedTrees = [];

        foreach($request->list_trees as $val){
            LahanUmumMonitoringDetail::create([
                'monitoring_no' => $monitoring_no,
                'tree_code' => $val['tree_code'],
                'qty' => $val['qty'],
                'status' => $val['status'],
                'condition' => $val['condition'],
                'planting_date' => $val['planting_date'],
            ]);

            $trees_get = DB::table('trees')->where('tree_code','=',$val['tree_code'])->first();

            if( $trees_get->tree_category == "Pohon_Buah"){
                $pohon_mpts = $pohon_mpts + $val['qty'];
            }else if($trees_get->tree_category == "Tanaman_Bawah_Empon"){
                $pohon_bawah = $pohon_bawah + $val['qty'];
            }else{
                $pohon_non_mpts = $pohon_non_mpts + $val['qty'];
            }
            
            array_push($updatedTrees, ((DB::table('trees')->where('tree_code', $val['tree_code'])->first()->tree_name ?? 'jenis_pohon') . " => " . ($val['qty'].'_'.$val['status'].'_'.$val['condition'])));
        }
        $updatedTreesToString = implode(", ", $updatedTrees);
        
        $monitoring = DB::table('lahan_umum_monitorings')->where('monitoring_no', $monitoring_no)->first();
        
        $this->createLogByMOU([
            'status' => "Verification$request->is_verified",
            'type' => 'Monitoring-1',
            'mou_no' => $monitoring->mou_no,
            'message' => " (status => $request->is_verified, " . $updatedTreesToString . ")" 
        ]);

        LahanUmumMonitoring::where('monitoring_no', $monitoring_no)
        ->update([    
            'updated_at'=>Carbon::now(),
            'verified_by' => $request->validate_by,    
            'is_verified' => $request->is_verified,
            'qty_kayu' => $pohon_non_mpts,
            'qty_mpts' => $pohon_mpts,
            'qty_crops' => $pohon_bawah,
        ]);

        $rslt =  $this->ResultReturn(200, 'success', 'success');
        return response()->json($rslt, 200); 
    }
    
    public function UnverificationMonitoringLahanUmum(Request $req) {
        $validator = Validator::make($req->all(), [
            'monitoring_no' => 'required|exists:lahan_umum_monitorings,monitoring_no',
            'is_validate' => 'required',
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        } else {
            $data = LahanUmumMonitoring::where('monitoring_no', $req->monitoring_no)->first();
            LahanUmumMonitoring::where('monitoring_no', $req->monitoring_no)->update([
                'is_verified' => $req->is_validate    
            ]);  
        
            $this->createLogByMOU([
                'status' => 'Unverfication' . ((int)$req->is_validate + 1),
                'type' => 'Monitoring-1',
                'mou_no' => $data->mou_no,
                'message' => " (is_verified => $req->is_validate)" 
            ]);

            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200); 
        }
    }
    
    public function MonitoringLahanUmumVerificationPM(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'monitoring_no' => 'required',
        ]);

        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        }  
        
        $monitoring_no = $request->monitoring_no;
        $monitoring = DB::table('lahan_umum_monitorings')->where('monitoring_no','=',$monitoring_no)->first();
        
        if($monitoring){
            
            LahanUmumMonitoringDetail::where([
                'monitoring_no' => $monitoring->monitoring_no 
            ])->delete();
            
            $listTree = $request->list_trees;
            
            foreach($listTree as $val){
                LahanUmumMonitoringDetail::create([
                    'monitoring_no' => $monitoring->monitoring_no,
                    'tree_code' =>  $val['tree_code'],
                    'qty' => $val['qty'],
                    'status' => $val['status'],
                    'condition' => $val['condition'],
                    'planting_date' => $val['planting_date']
                ]);
            }

            LahanUmumMonitoring::where('monitoring_no', '=', $monitoring_no)
            ->update([    
                'updated_at'=>Carbon::now(),
                'verified_by' => $request->verified_by,    
                'is_verified' => 2
            ]);

            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200); 
        }else{
            $rslt =  $this->ResultReturn(400, 'doesnt match data', 'doesnt match data');
            return response()->json($rslt, 400);
        }
    }
// END: MONITORING }
    // Create Logs
    private function createLog($logData) {
        // get main data
        $main = DB::table('lahan_umums')->where('lahan_no', $logData['lahan_no'])->first();
        
        // get fc data
        if (isset($main->employee_no)) {
            $employee = DB::table('employees')->where('nik', $main->employee_no)->first();
        }
        // user auth data
        $user = Auth::user();
        
        // set message
        $message =  $logData['status'] . ' ' . 
                    ($main->lahan_no ?? '-') . 
                    ($logData['message'] ?? '') .
                    '[pic lahan = ' . 
                    ($main->pic_lahan ?? '-') .
                    ', pic T4T = ' . 
                    ($employee->name ?? '-') .
                    '] ' .
                    'by ' .
                    ($user->email ?? '-');
                    
        $log = Log::channel('lahan_umums');
        
        if ($logData['status'] == 'Updated' || $logData['status'] == 'Created' || $logData['status'] == 'Verification') {
            $log->notice($message);
        } else if ($logData['status'] == 'Soft Deleted' || $logData['status'] == 'Unverification') {
            $log->warning($message);
        } else if ($logData['status'] == 'Deleted') {
            $log->alert($message);
        } else {
            $log->info($message);
        }
        
        
        
        return true;
    }
    private function createLogByMOU($logData) {
        // get main data
        $main = DB::table('lahan_umums')->where('mou_no', $logData['mou_no'])->first();
        $main2 = DB::table('lahan_umum_monitorings')->where('mou_no', $logData['mou_no'])->first();
        
        // get fc data
        if (isset($main->employee_no)) {
            $employee = DB::table('employees')->where('nik', $main->employee_no)->first();
        }
        // user auth data
        $user = Auth::user();
        
        // set message
        $message =  ($logData['type'] ?? '?') . ' ' . $logData['status'] . ' ' . 
                    ($logData['mou_no'] ?? '-') . 
                    ($logData['message'] ?? '') .
                    '[lahan_no = ' . 
                    ($main2->lahan_no ?? '-') .
                    ', pic lahan = ' . 
                    ($main->pic_lahan ?? '-') .
                    ', pic T4T = ' . 
                    ($employee->name ?? '-') .
                    '] ' .
                    'by ' .
                    ($user->email ?? '-');
                    
        $log = Log::channel('lahan_umums');
        
        if ($logData['status'] == 'Updated' || $logData['status'] == 'Created' || $logData['status'] == 'Verification') {
            $log->notice($message);
        } else if ($logData['status'] == 'Soft Deleted' || $logData['status'] == 'Unverification') {
            $log->warning($message);
        } else if ($logData['status'] == 'Deleted') {
            $log->alert($message);
        } else {
            $log->info($message);
        }
        
        
        
        return true;
    }
}
