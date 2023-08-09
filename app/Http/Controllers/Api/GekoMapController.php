<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use App\Participant;
use App\Project;
use App\ProjectDetail;
use App\ProjectMedia;
use App\User;
use App\Win;
use App\WinDetail;
use App\Lahan;
use App\Farmer;

class GekoMapController extends Controller
{
    /**
     * @SWG\Get(
     *   path="/api/GetLahan",
     *   tags={"GekoMap"},
     *   summary="GetLahan",
     *   operationId="GetLahan",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="lahan_no",in="query", required=true, type="string")
     * )
     */
    public function GetLahan(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'lahan_no' => 'required'
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
            $GetLahan = DB::table('lahans')
                ->select('farmers.farmer_no','farmers.name as farmer_name','farmers.join_date as farmer_join_date','farmers.address as farmer_address',
                    'farmers.farmer_profile as farmer_photo', 'lahans.lahan_no', 'lahans.longitude as lahan_longitude', 'lahans.latitude as lahan_latitude',
                    'lahans.photo1 as lahan_photo1', 'lahans.photo2 as lahan_photo2', 'lahans.photo3 as lahan_photo3', 'lahans.photo4 as lahan_photo4',
                    'provinces.name as lahan_provinces', 'kabupatens.name as lahan_kabupatens', 'desas.name as lahan_village')
                ->join('farmers', 'farmers.farmer_no', '=', 'lahans.farmer_no')
                ->join('provinces', 'provinces.province_code', '=', 'lahans.province')
                ->join('kabupatens', 'kabupatens.kabupaten_no', '=', 'lahans.city')
                ->join('desas', 'desas.kode_desa', '=', 'lahans.village')
                // ->join('lahan_details', 'lahan_details.tree_code', '=', 'lahans.lahan_no')
                // ->join('trees', 'trees.tree_code', '=', 'lahan_details.tree_code')
                ->where('lahans.lahan_no', '=', $request->lahan_no)
                ->first();

            if($GetLahan){

                $GetLahanDetail = DB::table('lahan_details')
                ->select('lahan_details.amount as tree_amount','trees.tree_code','trees.tree_name','trees.short_information as trees_short_information',
                'trees.photo1 as trees_photo1','trees.photo2 as trees_photo2')
                ->join('trees', 'trees.tree_code', '=', 'lahan_details.tree_code')
                ->where('lahan_details.lahan_no', '=', $GetLahan->lahan_no)
                ->get();

                $data = ['LahanInformation'=>$GetLahan, 'ListDetailLahan'=>$GetLahanDetail ];

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

    /**
     * @SWG\Get(
     *   path="/api/GetSYT",
     *   tags={"GekoMap"},
     *   summary="GetSYT",
     *   operationId="GetSYT",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="wins",in="query", required=true, type="string")
     * )
     */
    public function GetSYT(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'wins' => 'required'
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
            $GetWin = DB::table('wins')
                ->where('wins.wins', '=', $request->wins)
                ->first();

            if($GetWin){
                $GetParticipant = DB::table('participants')
                ->select('participants.participant_no','participants.first_name as participants_first_name','participants.last_name as participants_last_name',
                'participants.join_date as participants_join_date', 'participants.company as participants_company','participants.photo as participants_photo')
                ->where('participants.participant_no', '=', $GetWin->participant_no)
                ->first();

                $GetProject = DB::table('projects')
                ->select('projects.project_no','projects.project_name','projects.project_description','projects.donors as project_donors','projects.total_trees as project_total_trees',
                'projects.location as project_location')
                ->where('projects.project_no', '=', $GetWin->project_no)
                ->first();

                $GetWinDetail = DB::table('win_details')
                ->select('farmers.farmer_no','farmers.name as farmer_name','farmers.join_date as farmer_join_date','farmers.address as farmer_address',
                'farmers.farmer_profile as farmer_photo', 'lahans.lahan_no', 'lahans.longitude as lahan_longitude', 'lahans.latitude as lahan_latitude',
                'lahans.photo1 as lahan_photo1', 'lahans.photo2 as lahan_photo2', 'lahans.photo3 as lahan_photo3', 'lahans.photo4 as lahan_photo4',
                'provinces.name as lahan_provinces', 'kabupatens.name as lahan_kabupatens', 'desas.name as lahan_village',
                'trees.tree_name','trees.short_information as trees_short_information','trees.photo1 as trees_photo1','trees.photo2 as trees_photo2')
                ->join('lahans', 'lahans.lahan_no', '=', 'win_details.lahan_no')
                ->join('farmers', 'farmers.farmer_no', '=', 'lahans.farmer_no')
                ->join('provinces', 'provinces.province_code', '=', 'lahans.province')
                ->join('kabupatens', 'kabupatens.kabupaten_no', '=', 'lahans.city')
                ->join('desas', 'desas.kode_desa', '=', 'lahans.village')
                ->join('trees', 'trees.tree_code', '=', 'win_details.tree_code')
                ->where('win_details.wins', '=', $GetWin->wins)
                ->get();

                $data = ['ParticipantInformation'=>$GetParticipant, 'ProjectInformation'=>$GetProject,'WinsDetail'=>$GetWin, 'ListDetail'=>$GetWinDetail ];

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

    /**
     * @SWG\Get(
     *   path="/api/GetSOT",
     *   tags={"GekoMap"},
     *   summary="GetSOT",
     *   operationId="GetSOT",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="participant_no",in="query", required=true, type="string")
     * )
     */
    public function GetSOT(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'participant_no' => 'required'
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
            $GetParticipant = DB::table('participants')
                ->select('participants.participant_no','participants.first_name as participants_first_name','participants.last_name as participants_last_name',
                'participants.join_date as participants_join_date', 'participants.company as participants_company','participants.photo as participants_photo')
                ->where('participants.participant_no', '=', $request->participant_no)
                ->first();
                

            if($GetParticipant){
                
                $GetWin = DB::table('wins')
                ->where('wins.participant_no', '=', $GetParticipant->participant_no)
                ->get();

                $datavalproject = [];
                $listvalproject=array(); 
                $datavalwindetail = [];
                $listvalwindetail=array(); 
                foreach ($GetWin as $val) {
                    $GetProject = DB::table('projects')
                    ->select('projects.project_no','projects.project_name','projects.project_description','projects.donors as project_donors','projects.total_trees as project_total_trees',
                    'projects.location as project_location','wins.total_trees as wins_total_trees','wins.wins as wins_no')
                    ->join('wins', 'wins.project_no', '=', 'projects.project_no')
                    ->where('wins.wins', '=', $val->wins)
                    ->first();

                    $datavalproject = ['project_no'=>$GetProject->project_no,'project_name'=>$GetProject->project_name, 'project_description'=>$GetProject->project_description, 
                    'project_donors'=>$GetProject->project_donors, 'project_total_trees' => $GetProject->project_total_trees, 'project_location' => $GetProject->project_location,
                    'wins_total_trees' => $GetProject->wins_total_trees, 'wins_no' => $GetProject->wins_no];
                    array_push($listvalproject, $datavalproject);

                    $GetWinDetail = DB::table('win_details')
                    ->select('farmers.farmer_no','farmers.name as farmer_name','farmers.join_date as farmer_join_date','farmers.address as farmer_address',
                    'farmers.farmer_profile as farmer_photo', 'lahans.lahan_no', 'lahans.longitude as lahan_longitude', 'lahans.latitude as lahan_latitude',
                    'lahans.photo1 as lahan_photo1', 'lahans.photo2 as lahan_photo2', 'lahans.photo3 as lahan_photo3', 'lahans.photo4 as lahan_photo4',
                    'provinces.name as lahan_provinces', 'kabupatens.name as lahan_kabupatens', 'desas.name as lahan_village',
                    'trees.tree_name','trees.short_information as trees_short_information','trees.photo1 as trees_photo1','trees.photo2 as trees_photo2')
                    ->join('lahans', 'lahans.lahan_no', '=', 'win_details.lahan_no')
                    ->join('farmers', 'farmers.farmer_no', '=', 'lahans.farmer_no')
                    ->join('provinces', 'provinces.province_code', '=', 'lahans.province')
                    ->join('kabupatens', 'kabupatens.kabupaten_no', '=', 'lahans.city')
                    ->join('desas', 'desas.kode_desa', '=', 'lahans.village')
                    ->join('trees', 'trees.tree_code', '=', 'win_details.tree_code')
                    ->where('win_details.wins', '=', $val->wins)
                    ->get();

                    $datavalwindetail = ['WinNo'=>$val->wins, 'ListGroupWinNo'=>$GetWinDetail];
                    array_push($listvalwindetail, $datavalwindetail);
                }


                $data = ['ParticipantInformation'=>$GetParticipant, 'ProjectInformation'=>$listvalproject,'WinsDetail'=>$GetWin, 'ListDetail'=>$listvalwindetail ];

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
}
