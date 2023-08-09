<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use DB;

/**
 * @SWG\Swagger(
 *     basePath="",
 *     schemes={"http", "https"},
 *     host=L5_SWAGGER_CONST_HOST,
 *     @SWG\Info(
 *         version="1.0.0",
 *         title="API Documentation",
 *     )
 * ),
 * @SWG\SecurityScheme(
 *     type="apiKey",
 *     description="Login with email and password to get the authentication token",
 *     name="Authorization",
 *     in="header",
 *     securityDefinition="apiAuth",
 * )
 */

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    public function ResultReturn($rsltCode, $msgDesc, $dataResult){

        $stat = ['code'=>$rsltCode, 'description'=>$msgDesc];
        $data = ['status'=>$stat, 'result'=>$dataResult];
        
        $typeSuccess = false;
        if($rsltCode == 200){
            $typeSuccess = true;
        }

        $rslt = ['success'=>$typeSuccess, 'data'=>$data];

        return $rslt;
    }
    public function ReplaceNull($req, $type){
        $rslt;
        if(strlen($req)-substr_count($req, ' ') == 0){
            if($type == 'int'){
                $rslt= 0;
            }elseif($type == 'date'){
                $rslt= "0000-01-01";
            }else{$rslt='-';}            
        }else{
            $rslt=$req;
        }
        return $rslt;
    }
    public function SetDefaultNull($req){
        $rslt;
        if(strlen($req)-substr_count($req, ' ') == 0){
            $rslt=NULL;            
        }else{
            $rslt=$req;
        }
        return $rslt;
    }

    public function limitcheck($getLimit){
        $limit = 0;
        if($getLimit){
            $limit=$getLimit;
        }
        else{
            $limit=10;
        }        
        return $limit;
    }
    public function offsetcheck($limit, $getOffset){
        $offset = 0;
        if($getOffset){
            if($getOffset == 1){
                $offset = 0;
            }
            else{
                $offset = ($getOffset-1)*$limit; 
            }            
        }
        
        return $offset;
    }
    public function getCordinate($long, $lat){
        if(substr($long,0,1)== '-'){$nLong = -1;}else{$nLong = 1;}
        if(substr($lat,0,1)== '-'){$nLat = -1;}else{$nLat = 1;}

        $DDLatitude = intval($lat)*$nLat;
        $MMLatitude = intval(($lat-intval($lat))*60)*$nLat;                
        $SSLatitude = round((((($lat-intval($lat))*60)-intval((($lat-intval($lat))*60)))*60*$nLat),2);
        if(intval($lat)<0){$PositionLat='S';}else{$PositionLat='N';}

        $DDLongitude = intval($long)*$nLong;
        $MMLongitude = intval(($long-intval($long))*60)*$nLong;                
        $SSLongitude = round((((($long-intval($long))*60)-intval((($long-intval($long))*60)))*60*$nLong),2);
        if(intval($long)<0){$PositionLong='S';}else{$PositionLong='E';}

        $la = $SSLatitude/60;
        $lo = $SSLongitude/60;

        $SumLat = $MMLatitude+$la;
        $SumLong = $MMLongitude+$lo;

        $coordinateLat = $PositionLat.$DDLatitude." ".substr($SumLat,0,6);
        $coordinateLong = $PositionLong.$DDLongitude." ".substr($SumLong,0,7);
        return $coordinateLat.'  '.$coordinateLong;
    }
    
    // Utilities: Get Nursery Location
    public function getNurseryAlocationGlobal($mu_no) {
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
    
    public function getNurseryAlocationReverseGlobal($nursery) {
        $nur = [
            'Arjasari' => ['022', '024', '025', '020', '029'],
            'Ciminyak' => ['023', '026', '027', '021'],
            'Kebumen' => ['019'],
            'Pati' => ['015', '016']
        ];
        
        return $nur[$nursery];
    }
    
    // get ff list by user
    public function getFFListByUserPY($py) {
        $user_id = Auth::user()->employee_no;
        $user = DB::table('employees')->where('nik', $user_id)->first();
        $fc_id = null;
        if ($user->position_no == 20) {
            $fc_id = DB::table('employee_structure')
                ->join('employees', 'employees.nik', 'employee_structure.nik')
                ->where(['manager_code' => $user_id, 'employees.position_no' => 19])->pluck('employees.nik')->toArray();
        } 
        if ($user->position_no == 19) {
            $fc_id = [$user_id];
        }
        if ($user->position_no == 23) {
            $um_id = DB::table('employee_structure')
                ->join('employees', 'employees.nik', 'employee_structure.nik')
                ->where(['manager_code' => $user_id, 'employees.position_no' => 20])->pluck('employees.nik')->toArray();
            $fc_id = DB::table('employee_structure')
                ->join('employees', 'employees.nik', 'employee_structure.nik')
                ->where(['employees.position_no' => 19])
                ->whereIn('manager_code', $um_id)->pluck('employees.nik')->toArray();
        }
        if ($fc_id) {
            $ff = DB::table('field_facilitators')
                ->join('main_pivots', 'main_pivots.key2', 'field_facilitators.ff_no')
                ->where([
                    'main_pivots.type' => 'fc_ff',
                    ['program_year', 'LIKE', "%$py%"]
                ])
                ->whereIn('main_pivots.key1', $fc_id)->pluck('key2')->toArray();
        }
        if (isset($ff)) if (count($ff) == 0) $ff = DB::table('main_pivots')->where(['type' => 'fc_ff', ['program_year', 'like', "%$py%"]])->pluck('key2')->toArray();
        
        return $ff ?? [];
    }
}
