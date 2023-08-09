<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use App\ScoopingVisit;
use App\ScoopingVisitFigure;
use App\VillageBorder;
use App\Rra;
use App\RraDusun;
use App\RraLandUse;
use App\RraCommunityInstitution;
use App\RraExistingPlant;
use App\RraOrganicPotential;
use App\RraProductionMarketing;
use App\RraInnovativeFarmer;

use App\Desa;
use App\Pra;
use App\PraDisasterDetail;
use App\PraFertilizerDetail;
use App\PraDryLandSpread;
use App\PraLandOwnership;
use App\PraPesticideDetail;
use App\PraWatersourceDetail;
use App\PraExistingProblem;
use App\PraFarmerIncome;

use App\SocialimpactFaunaDetail;
use App\SocialimpactFloraDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Mail;

class RraPraController extends Controller
{
//SCOOPING VISIT

    public function GetScoopingAll(Request $request)
    {
        $userID = $request->user_id;
        
        $GetAllScooping = DB::table('scooping_visits')
                ->select('scooping_visits.id',
                         'scooping_visits.data_no',
                         'scooping_visits.village',
                         'scooping_visits.land_area',
                         'scooping_visits.potential_dusun',
                         'scooping_visits.start_scooping_date',
                         'scooping_visits.end_scooping_date',
                         'scooping_visits.user_id',
                         'scooping_visits.verified_by',
                         'scooping_visits.is_verify',
                         'scooping_visits.created_at',
                         'scooping_visits.status',
                         'desas.name as village_name',
                         'users.name as pic_name',
                         'employees.name as pic_manager')
                ->leftjoin('desas', 'desas.kode_desa', '=', 'scooping_visits.village')
                ->join('users', 'users.email', 'scooping_visits.user_id')
                ->join('employee_structure', 'employee_structure.nik', 'users.employee_no')
                ->leftjoin('employees', 'employees.nik', 'employee_structure.manager_code')
                ->where('scooping_visits.is_dell','=',0)
                ->orderBy('scooping_visits.created_at', 'desc');
        
        if($userID){
            $GetAllScooping = $GetAllScooping->whereIn('scooping_visits.user_id', explode(',',$userID));
        }
        $GetAllScooping = $GetAllScooping->get();

        $rslt = $this->ResultReturn(200, 'Success', $GetAllScooping);
        return response()->json($rslt, 200);
    }
    
    public function GetDetailScooping(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'data_no' => 'required',
        ]);
        
        if($validator->fails()){
            $rslt = $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        }
        
        $gis_data_no = $request->data_no;
        
        $GetScoopingDetail = DB::table('scooping_visits')
                ->select('scooping_visits.*',
                    'scooping_visits.altitude as land_height',
                    'scooping_visits.slope as land_slope',
                    'scooping_visits.goverment_place as government_place',
                    'provinces.name as province_name',
                    'kabupatens.name as city_name',
                    'kecamatans.name as district_name',
                    'desas.name as village_name')
                ->join('provinces', 'provinces.province_code', 'scooping_visits.province')
                ->join('kabupatens', 'kabupatens.kabupaten_no', 'scooping_visits.city')
                ->join('kecamatans', 'kecamatans.kode_kecamatan', 'scooping_visits.district')
                ->join('desas', 'desas.kode_desa', 'scooping_visits.village')
                ->where('scooping_visits.data_no', '=', $gis_data_no)
                ->first();
        
        $GetScoopingDetail->scooping_figures = DB::table('scooping_visit_figures')
                ->select('scooping_visit_figures.*')
                ->where('data_no', '=', $gis_data_no)->get();
                
        $rslt = $this->ResultReturn(200, 'success', $GetScoopingDetail);
        return response()->json($rslt, 200);
    }
    
    public function AddScooping(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'village' => 'required',
        ]);
        
        if($validator->fails()){
            $rslt = $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        }
        
        $scooping_no = ScoopingVisit::Maxno();
        
        $scooping =  ScoopingVisit::create([
            'data_no' => $scooping_no,
            'province' => $request->province,
            'city' => $request->city,
            'district' => $request->district,
            'village' => $request->village,
            'start_scooping_date' => $request->start_scooping_date,
            'end_scooping_date' => $request->end_scooping_date,
            'accessibility' => $request->accessibility,
            'land_area' =>$request->land_area,
            'land_type' => $request->land_type,
            'slope' => $request->slope,
            'altitude' => $request->altitude,
            'vegetation_density' => $request->vegetation_density,
            'water_source' => $request->water_source,
            'rainfall' => $request->rainfall,
            'agroforestry_type' => $request->agroforestry_type,
            'goverment_place' => $request->government_place,
            'land_coverage' => $request->land_coverage,
            'electricity_source' => $request->electricity_source,
            'dry_land_area' => $request->dry_land_area,
            'village_polygon' => $request->village_polygon,
            'dry_land_polygon' => $request->dry_land_polygon,
            'total_dusun' => $request->total_dusun,
            'potential_dusun' => $request->potential_dusun,
            'potential_description' => $request->potential_description,
            'total_male' => $request->total_male,
            'total_female' => $request->total_female,
            'total_kk' => $request->total_kk,
            'photo_road_access' => $request->photo_road_access,
            'photo_meeting' => $request->photo_meeting,
            'photo_dry_land' => $request->photo_dry_land,
            'village_profile' => $request->village_profile,
            'status' => $request->status,
            'complete_data' => '0',
            'is_dell' => '0',
            'user_id' => $request->user_id ?? Auth::user()->email ?? '-',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        
        $village_figures = $request->village_figures;
        
        foreach($village_figures as $value){
            ScoopingVisitFigure::create([
                'data_no' => $scooping_no,
                'name' => $value['name'],
                'position' => $value['position'],
                'phone' => $value['phone'],
                'whatsapp' => $value['whatsapp'],
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now()
            ]);
        }
        
        // $logData = [
        //     'status' => 'Created Scooping',
        //     'data_no' => $data_no
        // ];
        // $this->createLog($logData);
        
        $rslt = $this->ResultReturn(200, 'success', $scooping_no);
        return response()->json($rslt, 200);
    }
    
    public function UpdateScooping(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'data_no' => 'required',
        ]);
        
        if($validator->fails()){
            $rslt = $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        }
        
        ScoopingVisit::where('data_no', '=', $request->data_no)
        ->update([
            'province' => $request->province,
            'city' => $request->city,
            'district' => $request->district,
            'village' => $request->village,
            'start_scooping_date' => $request->start_scooping_date,
            'end_scooping_date' => $request->end_scooping_date,
            'accessibility' => $request->accessibility,
            'land_area' =>$request->land_area,
            'land_type' => $request->land_type,
            'slope' => $request->slope,
            'altitude' => $request->altitude,
            'vegetation_density' => $request->vegetation_density,
            'water_source' => $request->water_source,
            'rainfall' => $request->rainfall,
            'agroforestry_type' => $request->agroforestry_type,
            'goverment_place' => $request->government_place,
            'land_coverage' => $request->land_coverage,
            'electricity_source' => $request->electricity_source,
            'dry_land_area' => $request->dry_land_area,
            'village_polygon' => $request->village_polygon,
            'dry_land_polygon' => $request->dry_land_polygon,
            'total_dusun' => $request->total_dusun,
            'potential_dusun' => $request->potential_dusun,
            'potential_description' => $request->potential_description,
            'total_male' => $request->total_male,
            'total_female' => $request->total_female,
            'total_kk' => $request->total_kk,
            'status' => $request->status,
            'updated_at' => Carbon::now(),
            'updated_by' => Auth::user()->email
        ]);
        if ($request->photo_road_access) {
            ScoopingVisit::where('data_no', '=', $request->data_no)->update([
                'photo_road_access' => $request->photo_road_access,
            ]);
        }
        if ($request->photo_meeting) {
            ScoopingVisit::where('data_no', '=', $request->data_no)->update([
            'photo_meeting' => $request->photo_meeting,
            ]);
        }
        if ($request->photo_dry_land) {
            ScoopingVisit::where('data_no', '=', $request->data_no)->update([
            'photo_dry_land' => $request->photo_dry_land,
            ]);
        }
        if ($request->village_profile) {
            ScoopingVisit::where('data_no', '=', $request->data_no)->update([
            'village_profile' => $request->village_profile,
            ]);
        }
        
        $rslt = $this->ResultReturn(200, 'success', $request->data_no);
        return response()->json($rslt, 200);
    }
    
    public function VerificationScooping(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'data_no' => 'required',
        ]);
        
        if($validator->fails()){
            $rslt = $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        }
        
        $form_data_no = $request->data_no;
        $scooping = DB::table('scooping_visits')->where('data_no', '=', $form_data_no)->first();
        
        if($scooping) {
            ScoopingVisit::where('data_no', '=', $form_data_no)
            ->update([
                'verified_at'=>Carbon::now(),
                'verified_by'=> Auth::user()->email,
                'is_verify'=> 1,
                'status' => 'submit_review'
            ]);
            
            // $this->createLogs([
            //     'status'=>'Verified Social Officer',
            //     'organic_no'=>$form_data_no
            // ]);
            
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200); 
        }else{
            $rslt =  $this->ResultReturn(400, 'doesnt match data', 'doesnt match data');
            return response()->json($rslt, 400);
        }
    }
    
    public function VerificationPMScooping(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'data_no' => 'required',
            'verified_by' => 'required',
        ]);
        
        if($validator->fails()){
            $rslt = $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        }
        
        $form_data_no = $request->data_no;
        $scooping = DB::table('scooping_visits')->where('data_no', '=', $form_data_no)->first();
        
        if($scooping) {
            ScoopingVisit::where('data_no', '=', $form_data_no)
            ->update([
                'updated_at'=>Carbon::now(),
                'verified_by'=>$request->verified_by,
                'is_verify'=> 2
            ]);
            
            // $this->createLogs([
            //     'is_verify'=>'Verified Program Manager',
            //     'organic_no'=>$form_data_no
            // ]);
            
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200); 
        }else{
            $rslt =  $this->ResultReturn(400, 'doesnt match data', 'doesnt match data');
            return response()->json($rslt, 400);
        }
    }
    
    public function UnverificationScooping(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'data_no' => 'required',
            'verified_by' => 'required',
        ]);
        
        if($validator->fails()){
            $rslt = $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        }
        
        $form_data_no = $request->data_no;
        $scooping = DB::table('scooping_visits')->where('data_no', '=', $form_data_no)->first();
        
        if($scooping) {
            ScoopingVisit::where('data_no', '=', $form_data_no)
            ->update([
                'updated_at'=>Carbon::now(),
                'status' => 'document_saving',
                'verified_by'=>'-',
                'is_verify'=> 0
            ]);
            
            // $this->createLogs([
            //     'is_verify'=>'Unverified',
            //     'organic_no'=>$form_data_no
            // ]);
            
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200); 
        }else{
            $rslt =  $this->ResultReturn(400, 'doesnt match data', 'doesnt match data');
            return response()->json($rslt, 400);
        }
    }
    
    public function MailtoGis(Request $request)
    {
        $gis_data_no = $request->data_no;
        
        $GetScoopingDetail = DB::table('scooping_visits')
                ->select('scooping_visits.*',
                    'scooping_visits.altitude as land_height',
                    'scooping_visits.slope as land_slope',
                    'scooping_visits.goverment_place as government_place',
                    'provinces.name as province_name',
                    'kabupatens.name as city_name',
                    'kecamatans.name as district_name',
                    'desas.name as village_name'
                )->join('provinces', 'provinces.province_code', 'scooping_visits.province')
                ->join('kabupatens', 'kabupatens.kabupaten_no', 'scooping_visits.city')
                ->join('kecamatans', 'kecamatans.kode_kecamatan', 'scooping_visits.district')
                ->join('desas', 'desas.kode_desa', 'scooping_visits.village')
                ->where('scooping_visits.data_no', '=', $gis_data_no)
                ->first();
        
        $data = ['data' => $GetScoopingDetail];
        
        //$data = array('name'=>"Notification to GIS Officer about Scooping Visit");
        // Path or name to the blade template to be rendered
        $template_path = 'email_template';

        Mail::send(['html'=> $template_path ], $data, function($message) use($request) {
            // Set the receiver and subject of the mail.
            $message->to('haryadi@trees4trees.org', 'Mas Haryadi')->subject('GIS Mailing');
            // Set the sender
            $message->from('iyas.muzani@trees4trees.org', 'Notification to GIS Officer about Scooping Visit');
        });
        
        ScoopingVisit::where('data_no', '=', $GetScoopingDetail->data_no)
        ->update([
            'updated_at'=>Carbon::now(),
            'email_to_gis'=> $GetScoopingDetail->email_to_gis + 1
        ]);

        return response()->json("Email to GIS Officer sent, check your inbox.");;
    }
    
    public function EmailtoGis()
    {
        Mail::to('kresna@trees4trees.org')->send(new NotifyMail());
 
        if (Mail::failures()) {
            return response()->Fail('Sorry! Please try again latter');
        }
        else{
            return response()->success('Great! Successfully send in your mail');
        }
    }
    
    
//RRA-PRA    
    
    public function GetRraPraAll(Request $request)
    {
        $GetAllRra = DB::table('rras')
                ->select('rras.id',
                         'rras.form_no',
                         'rras.village',
                         'rras.rra_pra_date_start',
                         'rras.rra_pra_date_end',
                         'rras.user_id',
                         'rras.verified_by',
                         'rras.is_verify',
                         'rras.created_at',
                         'rras.status',
                         'desas.name as village_name',
                         'users.name as pic_name',
                         'employees.name as pic_manager')
                ->leftjoin('desas', 'desas.kode_desa', '=', 'rras.village')
                ->join('users', 'users.email', 'rras.user_id')
                ->join('employee_structure', 'employee_structure.nik', 'users.employee_no')
                ->leftjoin('employees', 'employees.nik', 'employee_structure.manager_code')
                ->where('rras.is_dell','=',0)
                ->orderBy('rras.created_at', 'desc')
                ->get();
        $rslt = $this->ResultReturn(200, 'Success', $GetAllRra);
        return response()->json($rslt, 200);
    }
    
    public function GetDetailRraPra(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'form_no' => 'required',
        ]);
        
        if($validator->fails()){
            $rslt = $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        }
        
        $scooping_form_no = $request->form_no;
        
        $GetScoopingDetail = DB::table('scooping_visits')
                ->select('scooping_visits.*',
                    'scooping_visits.altitude as land_height',
                    'scooping_visits.slope as land_slope',
                    'scooping_visits.goverment_place as government_place',
                    'provinces.name as province_name',
                    'kabupatens.name as city_name',
                    'kecamatans.name as district_name',
                    'desas.name as village_name')
                ->join('provinces', 'provinces.province_code', 'scooping_visits.province')
                ->join('kabupatens', 'kabupatens.kabupaten_no', 'scooping_visits.city')
                ->join('kecamatans', 'kecamatans.kode_kecamatan', 'scooping_visits.district')
                ->join('desas', 'desas.kode_desa', 'scooping_visits.village')
                ->where('scooping_visits.data_no', '=', $scooping_form_no)
                ->first();
        
        $GetScoopingDetail->scooping_figures = DB::table('scooping_visit_figures')
                ->select('scooping_visit_figures.*')
                ->where('scooping_visit_figures.data_no', '=', $scooping_form_no)->get();
        
        $GetRraDetail = DB::table('rras')
                ->select('rras.*',
                    'desas.name as village_name'
                )
                ->join('desas', 'desas.kode_desa', 'rras.village')
                ->where('rras.form_no', '=', $scooping_form_no)
                ->first();
                
        $GetRraDetail->VillageBorder = DB::table('village_borders')
                ->select('village_borders.*',
                    'kabupatens.name as city_name',
                    'kecamatans.name as district_name',
                    'desas.name as village_name')
                ->join('kabupatens', 'kabupatens.kabupaten_no', 'village_borders.kabupaten_no')
                ->join('kecamatans', 'kecamatans.kode_kecamatan', 'village_borders.kode_kecamatan')
                ->join('desas', 'desas.kode_desa', 'village_borders.kode_desa')
                ->where('rra_no', '=', $GetRraDetail->rra_no)->get();
                
        $GetRraDetail->CommunityInstitution = DB::table('rra_community_institutions')
                ->select('rra_community_institutions.*')
                ->where('rra_no', '=', $GetRraDetail->rra_no)->get();
        
        $GetRraDetail->Dusun = DB::table('rra_dusuns')
                ->select('rra_dusuns.*')
                ->where('rra_no', '=', $GetRraDetail->rra_no)->get();
        
        $GetRraDetail->ExistingPlant = DB::table('rra_existing_plants')
                ->select('rra_existing_plants.*')
                ->where('rra_no', '=', $GetRraDetail->rra_no)->get();
        
        $GetRraDetail->InnovativeFarmer = DB::table('rra_innovative_farmers')
                ->select('rra_innovative_farmers.*')
                ->where('rra_no', '=', $GetRraDetail->rra_no)->get();
        
        $GetRraDetail->LandUse = DB::table('rra_land_uses')
                ->select('rra_land_uses.*')
                ->where('rra_no', '=', $GetRraDetail->rra_no)->get();
                
        $GetRraDetail->OrganicPotential = DB::table('rra_organic_potentials')
                ->select('rra_organic_potentials.*')
                ->where('rra_no', '=', $GetRraDetail->rra_no)->get();
        
        $GetRraDetail->ProductionMarketing = DB::table('rra_production_marketings')
                ->select('rra_production_marketings.*')
                ->where('rra_no', '=', $GetRraDetail->rra_no)->get();
                
                
        
        $GetPraDetail = DB::table('pras')
                ->select('pras.*')
                ->where('pras.form_no', '=', $scooping_form_no)
                ->first();
        if ($GetPraDetail) {
            $GetPraDetail->DisasterHistory = DB::table('pra_disaster_details')
                    ->select('pra_disaster_details.*')
                    ->where('pra_no', '=', $GetPraDetail->pra_no)->get();
            
            $GetPraDetail->ExistingProblem = DB::table('pra_existing_problems')
                    ->select('pra_existing_problems.*')
                    ->where('pra_no', '=', $GetPraDetail->pra_no)->get();
            
            $GetPraDetail->FarmerIncome = DB::table('pra_farmer_incomes')
                    ->select('pra_farmer_incomes.*')
                    ->where('pra_no', '=', $GetPraDetail->pra_no)->get();
            
            $GetPraDetail->Fertilizer = DB::table('pra_fertilizer_details')
                    ->select('pra_fertilizer_details.*')
                    ->where('pra_no', '=', $GetPraDetail->pra_no)->get();
            
            $GetPraDetail->Pesticide = DB::table('pra_pesticide_details')
                    ->select('pra_pesticide_details.*')
                    ->where('pra_no', '=', $GetPraDetail->pra_no)->get();
                    
            $GetPraDetail->DryLandSpread = DB::table('pra_dry_land_spreads')
                    ->select('pra_dry_land_spreads.*')
                    ->where('pra_no', '=', $GetPraDetail->pra_no)->get();
                    
            $GetPraDetail->Watersource = DB::table('pra_watersource_details')
                    ->select('pra_watersource_details.*')
                    ->where('pra_no', '=', $GetPraDetail->pra_no)->get();
            
            $GetPraDetail->LandOwnership = DB::table('pra_land_ownerships')
                    ->select('pra_land_ownerships.*')
                    ->where('pra_no', '=', $GetPraDetail->pra_no)->get();
                    
            $GetPraDetail->Flora = DB::table('socialimpact_flora_details')
                    ->select('socialimpact_flora_details.*')
                    ->where('pra_no', '=', $GetPraDetail->pra_no)->get();
                    
            $GetPraDetail->Fauna = DB::table('socialimpact_fauna_details')
                    ->select('socialimpact_fauna_details.*')
                    ->where('pra_no', '=', $GetPraDetail->pra_no)->get();
        }
                
                
        $rslt = $this->ResultReturn(200, 'success', ['Scooping' => $GetScoopingDetail, 'RRA' => $GetRraDetail, 'PRA' => $GetPraDetail]);
        return response()->json($rslt, 200);
    }
    
    public function AddRraPra(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'form_no' => 'required',
        ]);
        
        if($validator->fails()){
            $rslt = $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        }
        
        $rra_no = Rra::Maxno();
        $pra_no = Pra::Maxno();
        $form_no = $request->form_no;
        
        $rra_main =  Rra::create([
            'rra_no' => $rra_no,
            'form_no' => $request->form_no,
            'rra_pra_date_start' => $request->rra_pra_date_start,
            'rra_pra_date_end' => $request->rra_pra_date_end,
            'village' => $request->village,
            'lahan_menurut_masyarakat' => $request->lahan_menurut_masyarakat,
            'tanah_sawah' => $request->tanah_sawah,
            'tegal_ladang' => $request->tegal_ladang,
            'pemukiman' => $request->pemukiman,
            'pekarangan' => $request->pekarangan,
            'tanah_rawa' => $request->tanah_rawa,
            'waduk_danau' => $request->waduk_danau,
            'tanah_perkebunan_rakyat' => $request->tanah_perkebunan_rakyat,
            'tanah_perkebunan_negara' => $request->tanah_perkebunan_negara,
            'tanah_perkebunan_swasta' => $request->tanah_perkebunan_swasta,
            'hutan_lindung' => $request->hutan_lindung,
            'hutan_rakyat' => $request->hutan_rakyat,
            'fasilitas_umum' => $request->fasilitas_umum,
            'complete_data' => '0',
            'is_dell' => '0',
            'user_id' => $request->user_id ?? Auth::user()->email ?? '-',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'commodity_photo' => $request->commodity_photo,
            'institution_photo' => $request->institution_photo,
            'organic_farming_photo' => $request->organic_farming_photo,
            'status' => $request->status
        ]);
        
        $village_border = $request->village_border;
        $land_use_patterns = $request->land_use_patterns;
        $existing_plants = $request->existing_plants;
        $community_institutions = $request->community_institutions;
        $organic_farming_potential = $request->organic_farming_potential;
        $production_marketing = $request->production_marketing;
        $identification_of_innovative_farmers = $request->identification_of_innovative_farmers;
        $dusuns = $request->dusuns;
        
        foreach($village_border as $value){
            VillageBorder::create([
                'rra_no' => $rra_no,
                'point' => $value['point'],
                'border_type' => $value['border_type'],
                'kabupaten_no' => $value['kabupaten_no'],
                'kode_kecamatan' => $value['kode_kecamatan'],
                'kode_desa' => $value['kode_desa'],
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now()
            ]);
        }
        
        foreach($land_use_patterns as $value){
            RraLandUse::create([
                'rra_no' => $rra_no,
                'pattern' => $value['pattern'],
                'plant' => $value['plant'] ? implode(',' , $value['plant']) : '',
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now()
            ]);
        } 
        
        foreach($existing_plants as $value){
            RraExistingPlant::create([
                'rra_no' => $rra_no,
                'plant_type' => $value['plant_type'],
                'plant' => $value['plant'] ? implode(',' , $value['plant']) : '',
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now()
            ]);
        }
        
        foreach($community_institutions as $value){
            RraCommunityInstitution::create([
                'rra_no' => $rra_no,
                'institution_name' => $value['institution_name'],
                'role' => $value['role'] ? implode(',', $value['role']) : '',
                'description' => $value['description'],
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now()
            ]);
        }
        
        foreach($organic_farming_potential as $value){
            RraOrganicPotential::create([
                'rra_no' => $rra_no,
                'potential_category' => $value['potential_category'],
                'source' => $value['source'],
                'name' => $value['name'],
                'description' => $value['description'],
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now()
            ]);
        }
        
        foreach($production_marketing as $value){
            RraProductionMarketing::create([
                'rra_no' => $rra_no,
                'commodity_name' => $value['commodity_name'],
                'capacity' => $value['capacity'],
                'method' => $value['method'],
                'period' => $value['period'],
                'description' => $value['description'],
                'customer' => $value['customer'],
                'phone' => $value['phone'],
                'email' => $value['email'],
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
                'capacity_switcher' => $value['capacity_switcher'],
                'has_customer' => $value['has_customer']
            ]);
        }
        
        foreach($identification_of_innovative_farmers as $value){
            RraInnovativeFarmer::create([
                'rra_no' => $rra_no,
                'farmer_name' => $value['farmer_name'],
                'specialitation' => $value['specialitation'],
                'potential' => $value['potential'],
                'description' => $value['description'],
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now()
            ]);
        }
        
        foreach($dusuns as $value){
            RraDusun::create([
                'rra_no' => $rra_no,
                'dusun_name' => $value['dusun_name'],
                'potential' => $value['potential'],
                'land_area' => $value['land_area'],
                'accessibility' => $value['accessibility'],
                'dry_land_area' => $value['dry_land_area'],
                'pic_dusun' => $value['pic_dusun'],
                'position' => $value['position'],
                'phone' => $value['phone'],
                'whatsapp' => $value['whatsapp'],
                'total_rw' => $value['total_rw'],
                'total_rt' => $value['total_rt'],
                'total_male' => $value['total_male'],
                'total_female' => $value['total_female'],
                'total_kk' => $value['total_kk'],
                'total_farmer_family' => $value['total_farmer_family'],
                'average_family_member' => $value['average_family_member'],
                'average_farmer_family_member' => $value['average_farmer_family_member'],
                'education_elementary_junior_hs' => $value['education_elementary_junior_hs'],
                'education_senior_hs' => $value['education_senior_hs'],
                'education_college' => $value['education_college'],
                'age_productive' => $value['age_productive'],
                'age_non_productive' => $value['age_non_productive'],
                'job_farmer' => $value['job_farmer'],
                'job_farm_workers' => $value['job_farm_workers'],
                'job_private_employee' => $value['job_private_employee'],
                'job_state_employee' => $value['job_state_employee'],
                'job_enterpreneur' => $value['job_enterpreneur'],
                'job_others' => $value['job_others'],
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
                'dusun_access_photo' => $value['dusun_access_photo'],
                'data_land_area_source' => $value['data_land_area_source'],
                'data_dry_land_area_source' => $value['data_dry_land_area_source'],
                'has_detail_kk' => $value['has_detail_kk'],
                'total_non_farmer_family'=> $value['total_non_farmer_family'],
                'has_avg_member'=> $value['has_avg_member'],
                'has_detail_avg_member'=> $value['has_detail_avg_member'],
                'average_non_farmer_family_member'=> $value['average_non_farmer_family_member'],
                'data_productive_source'=> $value['data_productive_source'],
                'data_job_source'=> $value['data_job_source'],
                'job_farmer_switcher'=> $value['job_farmer_switcher'],
                'job_farm_workers_switcher'=> $value['job_farm_workers_switcher'],
                'job_private_employee_switcher'=> $value['job_private_employee_switcher'],
                'job_state_employee_switcher'=> $value['job_state_employee_switcher'],
                'job_enterpreneur_switcher'=> $value['job_enterpreneur_switcher'],
                'job_others_switcher'=> $value['job_others_switcher']
            ]);   
        }
        
        $pra_main =  Pra::create([
            'pra_no' => $pra_no,
            'form_no' => $request->form_no,
            'land_ownership_description' => $request->land_ownership_description,
            'distribution_of_critical_land_locations_description' => $request->distribution_of_critical_land_locations_description,
            'collection_type' => $request->collection_type,
            'man_min_income' => $request->man_min_income,
            'man_max_income' => $request->man_max_income,
            'man_income_source' => $request->man_income_source,
            'man_commodity_name' => $request->man_commodity_name,
            'man_method' => $request->man_method,
            'man_average_capacity' => $request->man_average_capacity,
            'man_marketing' => $request->man_marketing,
            'man_period' => $request->man_period,
            'man_source' => $request->man_source,
            'woman_min_income' => $request->woman_min_income,
            'woman_max_income' => $request->woman_max_income,
            'woman_income_source' => $request->woman_income_source,
            'woman_commodity_name' => $request->woman_commodity_name,
            'woman_method' => $request->woman_method,
            'woman_average_capacity' => $request->woman_average_capacity,
            'woman_marketing' => $request->woman_marketing,
            'woman_period' => $request->woman_period,
            'woman_source' => $request->woman_source,
            'income_description' => $request->income_description,
            'land_utilization_source' => $request->land_utilization_source,
            'land_utilization_plant_type' => $request->land_utilization_plant_type ? implode(',', $request->land_utilization_plant_type) : '',
            'land_utilization_description' => $request->land_utilization_description,
            'pra_watersource_description' => $request->pra_watersource_description,
            'complete_data' => '0',
            'is_dell' => '0',
            'user_id' => $request->user_id ?? Auth::user()->email ?? '-',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'dry_land_photo' => $request->dry_land_photo,
            'dry_land_photo2' => $request->dry_land_photo2,
            'watersource_photo' => $request->watersource_photo,
            'status' => $request->status
        ]);
        
        $pra_disaster = $request->disaster_history;
        $pra_fertilizer = $request->pra_fertilizer;
        $pra_pesticide = $request->pra_pesticide;
        $land_ownership = $request->land_ownership;
        $pra_watersource = $request->pra_watersource;
        $existing_problem = $request->problem_existing;
        $dry_land = $request->distribution_of_critical_land_locations;
        $farmer_income = $request->farmer_income;
        $sosfauna = $request->fauna_data;
        $sosflora = $request->flora_data;
        
        foreach($pra_disaster as $value){
            PraDisasterDetail::create([
                'pra_no' => $pra_no,
                'disaster_name' => $value['disaster_name'],
                'disaster_categories' => $value['disaster_categories'],
                'year' => $value['year'],
                'fatalities' => $value['fatalities'],
                'has_fatalities' => $value['has_fatalities'],
                'detail' => $value['detail'],
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now()
            ]);
        }
        
        foreach($pra_fertilizer as $value){
            PraFertilizerDetail::create([
                'pra_no' => $pra_no,
                'fertilizer_categories' => $value['fertilizer_categories'],
                'fertilizer_type' => $value['fertilizer_type'],
                'fertilizer_name' => $value['fertilizer_name'],
                'fertilizer_source' => $value['fertilizer_source'],
                'fertilizer_description' => $value['fertilizer_description'],
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now()
            ]);
        }
        
        foreach($pra_pesticide as $value){
            PraPesticideDetail::create([
                'pra_no' => $pra_no,
                'pesticide_categories' => $value['pesticide_categories'],
                'pesticide_type' => $value['pesticide_type'],
                'pesticide_name' => $value['pesticide_name'],
                'pesticide_source' => $value['pesticide_source'],
                'pesticide_description' => $value['pesticide_description'],
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now()
            ]);
        }
        
        foreach($dry_land as $value){
            PraDryLandSpread::create([
                'pra_no' => $pra_no,
                'dusun_name' => $value['dusun_name'],
                'type_utilization' => $value['type_utilization'],
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now()
            ]);
        }
        
        foreach($land_ownership as $value){
            PraLandOwnership::create([
                'pra_no' => $pra_no,
                'type_ownership' => $value['type_ownership'],
                'land_ownership' => $value['land_ownership'],
                'percentage' => $value['percentage'],
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now()
            ]);
        }
        
        foreach($pra_watersource as $value){
            PraWatersourceDetail::create([
                'pra_no' => $pra_no,
                'watersource_name' => $value['watersource_name'],
                'watersource_type' => $value['watersource_type'],
                'watersource_condition' => $value['watersource_condition'],
                'consumption' => $value['consumption'],
                'watersource_utilization' => $value['watersource_utilization'],
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now()
            ]);
        }
        
        foreach($existing_problem as $value){
            PraExistingProblem::create([
                'pra_no' => $pra_no,
                'problem_categories' => $value['problem_categories'],
                'problem_name' => $value['problem_name'],
                'problem_source' => $value['problem_source'],
                'problem_solution' => $value['problem_solution'],
                'date' => $value['date'],
                'impact_to_people' => $value['impact_to_people'],
                'interval_problem' => $value['interval_problem'],
                'priority' => $value['priority'],
                'potential' => $value['potential'],
                'total_value' => $value['total_value'],
                'ranking' => $value['ranking'],
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now()
            ]);
        }
        
        foreach($farmer_income as $value){
            PraFarmerIncome::create([
                'pra_no' => $pra_no,
                'name' => $value['name'],
                'gender' => $value['gender'],
                'source' => $value['source'],
                'source_income' => $value['source_income'],
                'capacity' => $value['capacity'],
                'commodity_name' => $value['commodity_name'],
                'family_member' => $value['family_member'],
                'family_type' => $value['family_type'],
                'indirect_method' => $value['indirect_method'],
                'job' => $value['job'],
                'method' => $value['method'],
                'period' => $value['period'],
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now()
            ]);
        }
        
        foreach($sosfauna as $value){
            SocialimpactFaunaDetail::create([
                'pra_no' => $pra_no,
                'fauna_categories' => $value['fauna_categories'],
                'fauna_name' => $value['fauna_name'],
                'fauna_population' => $value['fauna_population'],
                'fauna_foodsource' => $value['fauna_foodsource'],
                'fauna_status' => $value['fauna_status'],
                'fauna_habitat' => $value['fauna_habitat'],
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now()
            ]);
        }
        
        foreach($sosflora as $value){
            SocialimpactFloraDetail::create([
                'pra_no' => $pra_no,
                'flora_categories' => $value['flora_categories'],
                'flora_name' => $value['flora_name'],
                'flora_population' => $value['flora_population'],
                'flora_foodsource' => $value['flora_foodsource'],
                'flora_status' => $value['flora_status'],
                'flora_habitat' => $value['flora_habitat'],
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now()
            ]);
        }
        
        // $logData = [
        //     'status' => 'Created Scooping',
        //     'data_no' => $data_no
        // ];
        // $this->createLog($logData);
        
        $rslt = $this->ResultReturn(200, 'success', 'success');
        return response()->json($rslt, 200);
    }
    
    public function UpdateRraPra(Request $request)
    {
         $validator = Validator::make($request->all(), [
            'form_no' => 'required',
        ]);
        
        if($validator->fails()){
            $rslt = $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        }

        $form_no = $request->form_no;
        
        $rra = Rra::where('form_no', '=', $request->form_no)->first();
        
        $rra_main =  Rra::where('form_no', '=', $request->form_no)->update([
            'rra_pra_date_start' => $request->rra_pra_date_start,
            'rra_pra_date_end' => $request->rra_pra_date_end,
            'village' => $request->village,
            'lahan_menurut_masyarakat' => $request->lahan_menurut_masyarakat,
            'tanah_sawah' => $request->tanah_sawah,
            'tegal_ladang' => $request->tegal_ladang,
            'pemukiman' => $request->pemukiman,
            'pekarangan' => $request->pekarangan,
            'tanah_rawa' => $request->tanah_rawa,
            'waduk_danau' => $request->waduk_danau,
            'tanah_perkebunan_rakyat' => $request->tanah_perkebunan_rakyat,
            'tanah_perkebunan_negara' => $request->tanah_perkebunan_negara,
            'tanah_perkebunan_swasta' => $request->tanah_perkebunan_swasta,
            'hutan_lindung' => $request->hutan_lindung,
            'hutan_rakyat' => $request->hutan_rakyat,
            'fasilitas_umum' => $request->fasilitas_umum,
            'complete_data' => '0',
            'is_dell' => '0',
            'updated_at' => Carbon::now(),
            'commodity_photo' => $request->commodity_photo,
            'institution_photo' => $request->institution_photo,
            'organic_farming_photo' => $request->organic_farming_photo,
            'status' => $request->status
        ]);
        
        $village_border = $request->village_border;
        $land_use_patterns = $request->land_use_patterns;
        $existing_plants = $request->existing_plants;
        $community_institutions = $request->community_institutions;
        $organic_farming_potential = $request->organic_farming_potential;
        $production_marketing = $request->production_marketing;
        $identification_of_innovative_farmers = $request->identification_of_innovative_farmers;
        $dusuns = $request->dusuns;
        
        VillageBorder::where('rra_no', '=', $rra->rra_no)->delete();
        RraLandUse::where('rra_no', '=', $rra->rra_no)->delete();
        RraExistingPlant::where('rra_no', '=', $rra->rra_no)->delete();
        RraCommunityInstitution::where('rra_no', '=', $rra->rra_no)->delete();
        RraOrganicPotential::where('rra_no', '=', $rra->rra_no)->delete();
        RraProductionMarketing::where('rra_no', '=', $rra->rra_no)->delete();
        RraInnovativeFarmer::where('rra_no', '=', $rra->rra_no)->delete();
        RraDusun::where('rra_no', '=', $rra->rra_no)->delete();
        
        foreach($village_border as $value){
            VillageBorder::create([
                'rra_no' => $rra->rra_no,
                'point' => $value['point'],
                'border_type' => $value['border_type'],
                'kabupaten_no' => $value['kabupaten_no'],
                'kode_kecamatan' => $value['kode_kecamatan'],
                'kode_desa' => $value['kode_desa'],
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now()
            ]);
        }
        
        foreach($land_use_patterns as $value){
            RraLandUse::create([
                'rra_no' => $rra->rra_no,
                'pattern' => $value['pattern'],
                'plant' => $value['plant'] ? implode(',' , $value['plant']) : '',
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now()
            ]);
        } 
        
        foreach($existing_plants as $value){
            RraExistingPlant::create([
                'rra_no' => $rra->rra_no,
                'plant_type' => $value['plant_type'],
                'plant' => $value['plant'] ? implode(',' , $value['plant']) : '',
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now()
            ]);
        }
        
        foreach($community_institutions as $value){
            RraCommunityInstitution::create([
                'rra_no' => $rra->rra_no,
                'institution_name' => $value['institution_name'],
                'role' => $value['role'] ? implode(',', $value['role']) : '',
                'description' => $value['description'],
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now()
            ]);
        }
        
        foreach($organic_farming_potential as $value){
            RraOrganicPotential::create([
                'rra_no' => $rra->rra_no,
                'potential_category' => $value['potential_category'],
                'source' => $value['source'],
                'name' => $value['name'],
                'description' => $value['description'],
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now()
            ]);
        }
        
        foreach($production_marketing as $value){
            RraProductionMarketing::create([
                'rra_no' => $rra->rra_no,
                'commodity_name' => $value['commodity_name'],
                'capacity' => $value['capacity'],
                'method' => $value['method'],
                'period' => $value['period'],
                'description' => $value['description'],
                'customer' => $value['customer'],
                'phone' => $value['phone'],
                'email' => $value['email'],
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
                'capacity_switcher' => $value['capacity_switcher'],
                'has_customer' => $value['has_customer']
            ]);
        }
        
        foreach($identification_of_innovative_farmers as $value){
            RraInnovativeFarmer::create([
                'rra_no' => $rra->rra_no,
                'farmer_name' => $value['farmer_name'],
                'specialitation' => $value['specialitation'],
                'potential' => $value['potential'],
                'description' => $value['description'],
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now()
            ]);
        }
        
        foreach($dusuns as $value){
            RraDusun::create([
                'rra_no' => $rra->rra_no,
                'dusun_name' => $value['dusun_name'],
                'potential' => $value['potential'],
                'land_area' => $value['land_area'],
                'accessibility' => $value['accessibility'],
                'dry_land_area' => $value['dry_land_area'],
                'pic_dusun' => $value['pic_dusun'],
                'position' => $value['position'],
                'phone' => $value['phone'],
                'whatsapp' => $value['whatsapp'],
                'total_rw' => $value['total_rw'],
                'total_rt' => $value['total_rt'],
                'total_male' => $value['total_male'],
                'total_female' => $value['total_female'],
                'total_kk' => $value['total_kk'],
                'total_farmer_family' => $value['total_farmer_family'],
                'average_family_member' => $value['average_family_member'],
                'average_farmer_family_member' => $value['average_farmer_family_member'],
                'education_elementary_junior_hs' => $value['education_elementary_junior_hs'],
                'education_senior_hs' => $value['education_senior_hs'],
                'education_college' => $value['education_college'],
                'age_productive' => $value['age_productive'],
                'age_non_productive' => $value['age_non_productive'],
                'job_farmer' => $value['job_farmer'],
                'job_farm_workers' => $value['job_farm_workers'],
                'job_private_employee' => $value['job_private_employee'],
                'job_state_employee' => $value['job_state_employee'],
                'job_enterpreneur' => $value['job_enterpreneur'],
                'job_others' => $value['job_others'],
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
                'dusun_access_photo' => $value['dusun_access_photo'],
                'data_land_area_source' => $value['data_land_area_source'],
                'data_dry_land_area_source' => $value['data_dry_land_area_source'],
                'has_detail_kk' => $value['has_detail_kk'],
                'total_non_farmer_family'=> $value['total_non_farmer_family'],
                'has_avg_member'=> $value['has_avg_member'],
                'has_detail_avg_member'=> $value['has_detail_avg_member'],
                'average_non_farmer_family_member'=> $value['average_non_farmer_family_member'],
                'data_productive_source'=> $value['data_productive_source'],
                'data_job_source'=> $value['data_job_source'],
                'job_farmer_switcher'=> $value['job_farmer_switcher'],
                'job_farm_workers_switcher'=> $value['job_farm_workers_switcher'],
                'job_private_employee_switcher'=> $value['job_private_employee_switcher'],
                'job_state_employee_switcher'=> $value['job_state_employee_switcher'],
                'job_enterpreneur_switcher'=> $value['job_enterpreneur_switcher'],
                'job_others_switcher'=> $value['job_others_switcher']
            ]);   
        }
        
        $pra = Pra::where('form_no', '=', $request->form_no)->first();
        
        if($pra) {
            $pra_main =  Pra::where('form_no', '=', $request->form_no)->update([
                'land_ownership_description' => $request->land_ownership_description,
                'collection_type' => $request->collection_type,
                'distribution_of_critical_land_locations_description' => $request->distribution_of_critical_land_locations_description,
                'man_min_income' => $request->man_min_income,
                'man_max_income' => $request->man_max_income,
                'man_income_source' => $request->man_income_source,
                'man_commodity_name' => $request->man_commodity_name,
                'man_method' => $request->man_method,
                'man_average_capacity' => $request->man_average_capacity,
                'man_marketing' => $request->man_marketing,
                'man_period' => $request->man_period,
                'man_source' => $request->man_source,
                'woman_min_income' => $request->woman_min_income,
                'woman_max_income' => $request->woman_max_income,
                'woman_income_source' => $request->woman_income_source,
                'woman_commodity_name' => $request->woman_commodity_name,
                'woman_method' => $request->woman_method,
                'woman_average_capacity' => $request->woman_average_capacity,
                'woman_marketing' => $request->woman_marketing,
                'woman_period' => $request->woman_period,
                'woman_source' => $request->woman_source,
                'income_description' => $request->income_description,
                'land_utilization_source' => $request->land_utilization_source,
                'land_utilization_plant_type' => $request->land_utilization_plant_type ? implode(',', $request->land_utilization_plant_type) : '',
                'land_utilization_description' => $request->land_utilization_description,
                'pra_watersource_description' => $request->pra_watersource_description,
                'complete_data' => '0',
                'is_dell' => '0',
                'updated_at' => Carbon::now(),
                'dry_land_photo' => $request->dry_land_photo,
                'dry_land_photo2' => $request->dry_land_photo2,
                'watersource_photo' => $request->watersource_photo,
                'status' => $request->status
            ]);   
            // $pra_no = $pra_main->pra_no;
        }else{
            $pra_no = Pra::Maxno();
            
            $pra =  Pra::create([
                'pra_no' => $pra_no,
                'form_no' => $request->form_no,
                'land_ownership_description' => $request->land_ownership_description,
                'distribution_of_critical_land_locations_description' => $request->distribution_of_critical_land_locations_description,
                'collection_type' => $request->collection_type,
                'man_min_income' => $request->man_min_income,
                'man_max_income' => $request->man_max_income,
                'man_income_source' => $request->man_income_source,
                'man_commodity_name' => $request->man_commodity_name,
                'man_method' => $request->man_method,
                'man_average_capacity' => $request->man_average_capacity,
                'man_marketing' => $request->man_marketing,
                'man_period' => $request->man_period,
                'man_source' => $request->man_source,
                'woman_min_income' => $request->woman_min_income,
                'woman_max_income' => $request->woman_max_income,
                'woman_income_source' => $request->woman_income_source,
                'woman_commodity_name' => $request->woman_commodity_name,
                'woman_method' => $request->woman_method,
                'woman_average_capacity' => $request->woman_average_capacity,
                'woman_marketing' => $request->woman_marketing,
                'woman_period' => $request->woman_period,
                'woman_source' => $request->woman_source,
                'income_description' => $request->income_description,
                'land_utilization_source' => $request->land_utilization_source,
                'land_utilization_plant_type' => $request->land_utilization_plant_type ? implode(',', $request->land_utilization_plant_type) : '',
                'land_utilization_description' => $request->land_utilization_description,
                'pra_watersource_description' => $request->pra_watersource_description,
                'complete_data' => '0',
                'is_dell' => '0',
                'user_id' => $request->user_id ?? Auth::user()->email ?? '-',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'dry_land_photo' => $request->dry_land_photo,
                'dry_land_photo2' => $request->dry_land_photo2,
                'watersource_photo' => $request->watersource_photo,
                'status' => $request->status
            ]);
        }
        
        PraDisasterDetail::where('pra_no', '=', $pra->pra_no)->delete();
        PraFertilizerDetail::where('pra_no', '=', $pra->pra_no)->delete();
        PraPesticideDetail::where('pra_no', '=', $pra->pra_no)->delete();
        PraDryLandSpread::where('pra_no', '=', $pra->pra_no)->delete();
        PraLandOwnership::where('pra_no', '=', $pra->pra_no)->delete();
        PraWatersourceDetail::where('pra_no', '=', $pra->pra_no)->delete();
        PraExistingProblem::where('pra_no', '=', $pra->pra_no)->delete();
        PraFarmerIncome::where('pra_no', '=', $pra->pra_no)->delete();
        SocialimpactFaunaDetail::where('pra_no', '=', $pra->pra_no)->delete();
        SocialimpactFloraDetail::where('pra_no', '=', $pra->pra_no)->delete();
        
        $pra_disaster = $request->disaster_history;
        $pra_fertilizer = $request->pra_fertilizer;
        $pra_pesticide = $request->pra_pesticide;
        $dry_land = $request->distribution_of_critical_land_locations;
        $land_ownership = $request->land_ownership;
        $pra_watersource = $request->pra_watersource;
        $existing_problem = $request->problem_existing;
        $farmer_income = $request->farmer_income;
        $sosfauna = $request->fauna_data;
        $sosflora = $request->flora_data;
        
        foreach($pra_disaster as $value){
            PraDisasterDetail::create([
                'pra_no' => $pra->pra_no,
                'disaster_name' => $value['disaster_name'],
                'disaster_categories' => $value['disaster_categories'],
                'year' => $value['year'],
                'fatalities' => $value['fatalities'],
                'has_fatalities' => $value['has_fatalities'],
                'detail' => $value['detail'],
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now()
            ]);
        }
        
        foreach($pra_fertilizer as $value){
            PraFertilizerDetail::create([
                'pra_no' => $pra->pra_no,
                'fertilizer_categories' => $value['fertilizer_categories'],
                'fertilizer_type' => $value['fertilizer_type'],
                'fertilizer_name' => $value['fertilizer_name'],
                'fertilizer_source' => $value['fertilizer_source'],
                'fertilizer_description' => $value['fertilizer_description'],
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now()
            ]);
        }
        
        foreach($pra_pesticide as $value){
            PraPesticideDetail::create([
                'pra_no' => $pra->pra_no,
                'pesticide_categories' => $value['pesticide_categories'],
                'pesticide_type' => $value['pesticide_type'],
                'pesticide_name' => $value['pesticide_name'],
                'pesticide_source' => $value['pesticide_source'],
                'pesticide_description' => $value['pesticide_description'],
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now()
            ]);
        }
        
        foreach($dry_land as $value){
            PraDryLandSpread::create([
                'pra_no' => $pra->pra_no,
                'dusun_name' => $value['dusun_name'],
                'type_utilization' => $value['type_utilization'],
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now()
            ]);
        }
        
        foreach($land_ownership as $value){
            PraLandOwnership::create([
                'pra_no' => $pra->pra_no,
                'type_ownership' => $value['type_ownership'],
                'land_ownership' => $value['land_ownership'],
                'percentage' => $value['percentage'],
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now()
            ]);
        }
        
        foreach($pra_watersource as $value){
            PraWatersourceDetail::create([
                'pra_no' => $pra->pra_no,
                'watersource_name' => $value['watersource_name'],
                'watersource_type' => $value['watersource_type'],
                'watersource_condition' => $value['watersource_condition'],
                'consumption' => $value['consumption'],
                'watersource_utilization' => $value['watersource_utilization'],
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now()
            ]);
        }
        
        foreach($existing_problem as $value){
            PraExistingProblem::create([
                'pra_no' => $pra->pra_no,
                'problem_categories' => $value['problem_categories'],
                'problem_name' => $value['problem_name'],
                'problem_source' => $value['problem_source'],
                'problem_solution' => $value['problem_solution'],
                'date' => $value['date'],
                'impact_to_people' => $value['impact_to_people'],
                'interval_problem' => $value['interval_problem'],
                'priority' => $value['priority'],
                'potential' => $value['potential'],
                'total_value' => $value['total_value'],
                'ranking' => $value['ranking'],
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now()
            ]);
        }
        
        foreach($farmer_income as $value){
            PraFarmerIncome::create([
                'pra_no' => $pra->pra_no,
                'name' => $value['name'],
                'gender' => $value['gender'],
                'source' => $value['source'],
                'source_income' => $value['source_income'],
                'capacity' => $value['capacity'],
                'commodity_name' => $value['commodity_name'],
                'family_member' => $value['family_member'],
                'family_type' => $value['family_type'],
                'indirect_method' => $value['indirect_method'],
                'job' => $value['job'],
                'method' => $value['method'],
                'period' => $value['period'],
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now()
            ]);
        }
        
        foreach($sosfauna as $value){
            SocialimpactFaunaDetail::create([
                'pra_no' => $pra->pra_no,
                'fauna_categories' => $value['fauna_categories'],
                'fauna_name' => $value['fauna_name'],
                'fauna_population' => $value['fauna_population'],
                'fauna_foodsource' => $value['fauna_foodsource'],
                'fauna_status' => $value['fauna_status'],
                'fauna_habitat' => $value['fauna_habitat'],
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now()
            ]);
        }
        
        foreach($sosflora as $value){
            SocialimpactFloraDetail::create([
                'pra_no' => $pra->pra_no,
                'flora_categories' => $value['flora_categories'],
                'flora_name' => $value['flora_name'],
                'flora_population' => $value['flora_population'],
                'flora_foodsource' => $value['flora_foodsource'],
                'flora_status' => $value['flora_status'],
                'flora_habitat' => $value['flora_habitat'],
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now()
            ]);
        }
        
        // $logData = [
        //     'status' => 'Created Scooping',
        //     'data_no' => $data_no
        // ];
        // $this->createLog($logData);
        
        $rslt = $this->ResultReturn(200, 'success', 'success');
        return response()->json($rslt, 200);
    }
    
    public function VerificationRraPra(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'form_no' => 'required',
        ]);
        
        if($validator->fails()){
            $rslt = $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        }
        
        $form_data_no = $request->form_no;
        $scooping = DB::table('rras')->where('form_no', '=', $form_data_no)->first();
        
        if($scooping) {
            Rra::where('form_no', '=', $form_data_no)
            ->update([
                'verified_at'=>Carbon::now(),
                'verified_by'=> Auth::user()->email,
                'is_verify'=> 1,
                'status' => 'submit_review'
            ]);
            
            Pra::where('form_no', '=', $form_data_no)
            ->update([
                'verified_at'=>Carbon::now(),
                'verified_by'=> Auth::user()->email,
                'is_verify'=> 1,
                'status' => 'submit_review'
            ]);
            
            Desa::where('kode_desa', '=', $scooping->village)
            ->update([
                'status'=>1,
                'updated_at'=>Carbon::now(),
            ]);
            
            // $this->createLogs([
            //     'status'=>'Verified Social Officer',
            //     'organic_no'=>$form_data_no
            // ]);
            
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200); 
        }else{
            $rslt =  $this->ResultReturn(400, 'doesnt match data', 'doesnt match data');
            return response()->json($rslt, 400);
        }
    }
    
    public function VerificationRraPraDev(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'form_no' => 'required',
        ]);
        
        if($validator->fails()){
            $rslt = $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        }
        
        $form_data_no = $request->form_no;
        $scooping = DB::table('rras')->where('form_no', '=', $form_data_no)->first();
        
        if($scooping) {
            Rra::where('form_no', '=', $form_data_no)
            ->update([
                'verified_at'=>Carbon::now(),
                'verified_by'=> Auth::user()->email,
                'is_verify'=> 1,
                'status' => 'submit_review'
            ]);
            
            Pra::where('form_no', '=', $form_data_no)
            ->update([
                'verified_at'=>Carbon::now(),
                'verified_by'=> Auth::user()->email,
                'is_verify'=> 1,
                'status' => 'submit_review'
            ]);
            
            Desa::where('kode_desa', '=', $scooping->village)
            ->update([
                'status'=>1,
                'updated_at'=>Carbon::now(),
            ]);
            
            // $this->createLogs([
            //     'status'=>'Verified Social Officer',
            //     'organic_no'=>$form_data_no
            // ]);
            
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200); 
        }else{
            $rslt =  $this->ResultReturn(400, 'doesnt match data', 'doesnt match data');
            return response()->json($rslt, 400);
        }
    }
    
    public function VerificationPMRraPra(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'form_no' => 'required',
        ]);
        
        if($validator->fails()){
            $rslt = $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        }
        
        $form_data_no = $request->form_no;
        $scooping = DB::table('rras')->where('form_no', '=', $form_data_no)->first();
        
        if($scooping) {
            Rra::where('form_no', '=', $form_data_no)
            ->update([
                'verified_at'=>Carbon::now(),
                'verified_by'=> Auth::user()->email,
                'is_verify'=> 2
            ]);
            
            Pra::where('form_no', '=', $form_data_no)
            ->update([
                'verified_at'=>Carbon::now(),
                'verified_by'=> Auth::user()->email,
                'is_verify'=> 2
            ]);
            
            // $this->createLogs([
            //     'status'=>'Verified Social Officer',
            //     'organic_no'=>$form_data_no
            // ]);
            
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200); 
        }else{
            $rslt =  $this->ResultReturn(400, 'doesnt match data', 'doesnt match data');
            return response()->json($rslt, 400);
        }
    }
    
    public function UnverificationRraPra(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'form_no' => 'required',
        ]);
        
        if($validator->fails()){
            $rslt = $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        }
        
        $form_data_no = $request->form_no;
        $scooping = DB::table('rras')->where('form_no', '=', $form_data_no)->first();
        
        if($scooping) {
            Rra::where('form_no', '=', $form_data_no)
            ->update([
                'verified_at'=>Carbon::now(),
                'verified_by'=> '-',
                'is_verify'=> 0,
                'status' => 'ready_to_submit'
            ]);
            
            Pra::where('form_no', '=', $form_data_no)
            ->update([
                'verified_at'=>Carbon::now(),
                'verified_by'=> '-',
                'is_verify'=> 0,
                'status' => 'ready_to_submit'
            ]);
            
            Desa::where('kode_desa', '=', $scooping->village)
            ->update([
                'status'=>0,
                'updated_at'=>Carbon::now(),
            ]);
            
            // $this->createLogs([
            //     'status'=>'Verified Social Officer',
            //     'organic_no'=>$form_data_no
            // ]);
            
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200); 
        }else{
            $rslt =  $this->ResultReturn(400, 'doesnt match data', 'doesnt match data');
            return response()->json($rslt, 400);
        }
    }
    
    public function ReportScooping(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'data_no' => 'required',
        ]);
        
        if($validator->fails()){
            $rslt = $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        }
        
        $gis_data_no = $request->data_no;
        
        $GetScoopingDetail = DB::table('scooping_visits')
                ->select('scooping_visits.*',
                    'scooping_visits.altitude as land_height',
                    'scooping_visits.slope as land_slope',
                    'scooping_visits.goverment_place as government_place',
                    'provinces.name as province_name',
                    'kabupatens.name as city_name',
                    'kecamatans.name as district_name',
                    'desas.name as village_name'
                    
                )
                ->join('provinces', 'provinces.province_code', 'scooping_visits.province')
                ->join('kabupatens', 'kabupatens.kabupaten_no', 'scooping_visits.city')
                ->join('kecamatans', 'kecamatans.kode_kecamatan', 'scooping_visits.district')
                ->join('desas', 'desas.kode_desa', 'scooping_visits.village')
                ->where('scooping_visits.data_no', '=', $gis_data_no)
                ->first();
        
        $GetScoopingDetail->scooping_figures = DB::table('scooping_visit_figures')
                ->select('scooping_visit_figures.*')
                ->where('data_no', '=', $gis_data_no)->get();
                
        $rslt = $this->ResultReturn(200, 'success', $GetScoopingDetail);
        return response()->json($rslt, 200);
    }
    
    public function ReportRra(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rra_no' => 'required',
        ]);
        
        if($validator->fails()){
            $rslt = $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        }
        
        $rra_no = $request->rra_no;
        
        $GetRraDetail = DB::table('rras')
                ->select('rras.*')
                ->where('rra_no', '=', $rra_no)
                ->first();
                
        $GetRraDetail->community_institution = DB::table('rra_community_institutions')
                ->select('rra_community_institutions.*')
                ->where('rra_no', '=', $rra_no)->get();
                
        $GetRraDetail->rra_dusuns = DB::table('rra_dusuns')
                ->select('rra_dusuns.*')
                ->where('rra_no', '=', $rra_no)->get();
                
        $GetRraDetail->existing_plants = DB::table('rra_existing_plants')
                ->select('rra_existing_plants.*')
                ->where('rra_no', '=', $rra_no)->get();
                
        $GetRraDetail->village_border = DB::table('village_borders')
                ->select('village_borders.*',
                    'kabupatens.name as city_name',
                    'kecamatans.name as district_name',
                    'desas.name as village_name')
                ->join('kabupatens', 'kabupatens.kabupaten_no', 'village_borders.kabupaten_no')
                ->join('kecamatans', 'kecamatans.kode_kecamatan', 'village_borders.kode_kecamatan')
                ->join('desas', 'desas.kode_desa', 'village_borders.kode_desa')
                ->where('rra_no', '=', $rra_no)->get();
                
        $GetRraDetail->land_use_patterns = DB::table('rra_land_uses')
                ->select('rra_land_uses.*')
                ->where('rra_no', '=', $rra_no)->get();
                
        $GetRraDetail->organic_farming_potential = DB::table('rra_organic_potentials')
                ->select('rra_organic_potentials.*')
                ->where('rra_no', '=', $rra_no)->get();
                
        $GetRraDetail->production_marketing = DB::table('rra_production_marketings')
                ->select('rra_production_marketings.*')
                ->where('rra_no', '=', $rra_no)->get();
                
        $GetRraDetail->identification_of_innovative_farmers = DB::table('rra_innovative_farmers')
                ->select('rra_innovative_farmers.*')
                ->where('rra_no', '=', $rra_no)->get();
        
        $rslt = $this->ResultReturn(200, 'success', $GetRraDetail);
        return response()->json($rslt, 200);
    }
    
    public function ReportPra(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pra_no' => 'required',
        ]);
        
        if($validator->fails()){
            $rslt = $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
            return response()->json($rslt, 400);
        }
        
        $pra_no = $request->pra_no;
        
        $GetPraDetail = DB::table('pras')
                ->select('pras.*')
                ->where('pra_no', '=', $pra_no)
                ->first();
                
        $GetPraDetail->DisasterHistory = DB::table('pra_disaster_details')
                ->select('pra_disaster_details.*')
                ->where('pra_no', '=', $GetPraDetail->pra_no)->get();
        
        $GetPraDetail->ExistingProblem = DB::table('pra_existing_problems')
                ->select('pra_existing_problems.*')
                ->where('pra_no', '=', $GetPraDetail->pra_no)->get();
        
        $GetPraDetail->FarmerIncome = DB::table('pra_farmer_incomes')
                ->select('pra_farmer_incomes.*')
                ->where('pra_no', '=', $GetPraDetail->pra_no)->get();
        
        $GetPraDetail->Fertilizer = DB::table('pra_fertilizer_details')
                ->select('pra_fertilizer_details.*')
                ->where('pra_no', '=', $GetPraDetail->pra_no)->get();
        
        $GetPraDetail->Pesticide = DB::table('pra_pesticide_details')
                ->select('pra_pesticide_details.*')
                ->where('pra_no', '=', $GetPraDetail->pra_no)->get();
                
        $GetPraDetail->Watersource = DB::table('pra_watersource_details')
                ->select('pra_watersource_details.*')
                ->where('pra_no', '=', $GetPraDetail->pra_no)->get();
        
        $GetPraDetail->LandOwnership = DB::table('pra_land_ownerships')
                ->select('pra_land_ownerships.*')
                ->where('pra_no', '=', $GetPraDetail->pra_no)->get();
                
        $rslt = $this->ResultReturn(200, 'success', $GetPraDetail);
        return response()->json($rslt, 200);
    }
    
    public function createLog($logData)
    {
        // get main data
        $main = DB::table('scooping_visits')->where('data_no', $logData['data_no'])->first();
        
        //get desa
        
    }
}