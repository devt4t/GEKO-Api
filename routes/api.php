<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('Uploads', 'UploadController@Uploads');
Route::post('Regist', 'UserController@Regist');
Route::post('Login', 'UserController@Login');
Route::post('LoginWeb', 'UserController@LoginWeb');
Route::post('ForgotPassword', 'UserController@ForgotPassword');

Route::get('GetFarmerAllTemp', 'Api\FarmerController@GetFarmerAllTempDelete');
Route::get('GetFarmerDetailTemp', 'Api\FarmerController@GetFarmerDetail');
Route::get('GetTotalTreesPlanted', 'Api\TreesPlantedController@index');
Route::get('GekoDashboardAllOutside', 'Api\DashboardController@all');

Route::get('GetApi', 'UserController@GetApi');

Route::get('GetDistributionReportFullOutside', 'Api\TemporaryController@GetDistributionReportOutside');
Route::get('GetDistributionReportUmumOutside', 'Api\TemporaryController@GetDistributionReportUmumOutside');
Route::get('GetMonitoringReportEnhanced', 'Api\TemporaryController@GetMonitoringReportEnhanced');
Route::get('GetDataMainLahan', 'Api\TemporaryController@GetDataMainLahan');
Route::get('GetDataLahanByDocumentSPPT', 'Api\TemporaryController@GetDataLahanByDocumentSPPT');

Route::get('Get3s4Nursery', 'Api\TemporaryController@Get3s4Nursery');

Route::get('GetPHPInfo', 'Api\TemporaryController@GetPHPInfo');

Route::group(['middleware' => ['auth:api']], function () {
    Route::post('Logout', 'UserController@Logout');
    Route::post('EditProfile', 'UserController@EditProfile');
    Route::post('EditUser', 'UserController@EditUser');
    Route::post('DeleteUser', 'UserController@DeleteUser');
    Route::post('ResetPasswordUser', 'UserController@ResetPasswordUser');
    Route::get('GetUser', 'UserController@GetUser');

    // Dashboard
    Route::get('Dashboard', 'Api\UtilitiesController@Dashboard');
    Route::get('GekoDashboardAll', 'Api\DashboardController@all');
    Route::get('GekoDashboardTotalDatas', 'Api\DashboardController@totalDatas');
    Route::get('GetDashboardMapData', 'Api\DashboardController@GetDashboardMapData');
    Route::get('Dashboard/DetailFieldFacilitator', 'Api\DashboardController@DetailFieldFacilitator');
    Route::get('Dashboard/DetailPetaniLahan', 'Api\DashboardController@DetailPetaniLahan');
    
    Route::get('GetProvinceAdmin', 'Api\UtilitiesController@GetProvinceAdmin');
    Route::get('GetKabupatenAdmin', 'Api\UtilitiesController@GetKabupatenAdmin');
    Route::get('GetKecamatanAdmin', 'Api\UtilitiesController@GetKecamatanAdmin');
    Route::get('GetDesaAdmin', 'Api\UtilitiesController@GetDesaAdmin');
    Route::get('GetDesaAdminDev', 'Api\UtilitiesController@GetDesaAdminDev');
    Route::get('GetManagementUnitAdmin', 'Api\UtilitiesController@GetManagementUnitAdmin');
    Route::get('GetTargetAreaAdmin', 'Api\UtilitiesController@GetTargetAreaAdmin');

    Route::get('GetProvince', 'Api\UtilitiesController@GetProvince');
    Route::get('GetKabupaten', 'Api\UtilitiesController@GetKabupaten');
    Route::get('GetKecamatan', 'Api\UtilitiesController@GetKecamatan');
    Route::get('GetDesa', 'Api\UtilitiesController@GetDesa');

    Route::post('AddProvince', 'Api\UtilitiesController@AddProvince');
    Route::post('AddKabupaten', 'Api\UtilitiesController@AddKabupaten');
    Route::post('AddKecamatan', 'Api\UtilitiesController@AddKecamatan');
    Route::post('AddDesa', 'Api\UtilitiesController@AddDesa');

    Route::post('UpdateProvince', 'Api\UtilitiesController@UpdateProvince');
    Route::post('UpdateKabupaten', 'Api\UtilitiesController@UpdateKabupaten');
    Route::post('UpdateKecamatan', 'Api\UtilitiesController@UpdateKecamatan');
    Route::post('UpdateDesa', 'Api\UtilitiesController@UpdateDesa');

    Route::post('DeleteProvince', 'Api\UtilitiesController@DeleteProvince');
    Route::post('DeleteKabupaten', 'Api\UtilitiesController@DeleteKabupaten');
    Route::post('DeleteKecamatan', 'Api\UtilitiesController@DeleteKecamatan');
    Route::post('DeleteDesa', 'Api\UtilitiesController@DeleteDesa');

    Route::get('GetManagementUnit', 'Api\UtilitiesController@GetManagementUnit');
    Route::get('GetTargetArea', 'Api\UtilitiesController@GetTargetArea');
    Route::post('AddManagementUnit', 'Api\UtilitiesController@AddManagementUnit');
    Route::post('AddTargetArea', 'Api\UtilitiesController@AddTargetArea');
    Route::post('UpdateManagementUnit', 'Api\UtilitiesController@UpdateManagementUnit');
    Route::post('UpdateTargetArea', 'Api\UtilitiesController@UpdateTargetArea');
    Route::post('DeleteManagementUnit', 'Api\UtilitiesController@DeleteManagementUnit');
    Route::post('DeleteTargetArea', 'Api\UtilitiesController@DeleteTargetArea');

    Route::get('GetVerification', 'Api\UtilitiesController@GetVerification');
    Route::post('AddVerification', 'Api\UtilitiesController@AddVerification');
    Route::post('UpdateVerification', 'Api\UtilitiesController@UpdateVerification');
    Route::post('DeleteVerification', 'Api\UtilitiesController@DeleteVerification');

    Route::get('GetPekerjaan', 'Api\UtilitiesController@GetPekerjaan');
    Route::get('GetSuku', 'Api\UtilitiesController@GetSuku');
    Route::post('AddPekerjaan', 'Api\UtilitiesController@AddPekerjaan');
    Route::post('AddSuku', 'Api\UtilitiesController@AddSuku');
    Route::post('UpdatePekerjaan', 'Api\UtilitiesController@UpdatePekerjaan');
    Route::post('UpdateSuku', 'Api\UtilitiesController@UpdateSuku');
    Route::post('DeletePekerjaan', 'Api\UtilitiesController@DeletePekerjaan');
    Route::post('DeleteSuku', 'Api\UtilitiesController@DeleteSuku');

    Route::get('GetAllMenuAccess', 'Api\UtilitiesController@GetAllMenuAccess');
    
    //SCOOPING VISIT
    Route::get('GetScoopingAll', 'Api\RraPraController@GetScoopingAll');
    Route::get('GetDetailScooping', 'Api\RraPraController@GetDetailScooping');
    Route::post('AddScooping', 'Api\RraPraController@AddScooping');
    Route::post('UpdateScooping', 'Api\RraPraController@UpdateScooping');
    Route::post('VerificationScooping', 'Api\RraPraController@VerificationScooping');
    Route::post('VerificationPMScooping', 'Api\RraPraController@VerificationPMScooping');
    Route::post('UnverificationScooping', 'Api\RraPraController@UnverificationScooping');
    Route::get('MailtoGis', 'Api\RraPraController@MailtoGis');
    Route::get('EmailtoGis', 'Api\RraPraController@EmailtoGis');
    
    //RRA-PRA
    Route::get('GetRraPraAll', 'Api\RraPraController@GetRraPraAll');
    Route::get('GetDetailRraPra', 'Api\RraPraController@GetDetailRraPra');
    Route::post('AddRraPra', 'Api\RraPraController@AddRraPra');
    Route::post('UpdateRraPra', 'Api\RraPraController@UpdateRraPra');
    Route::post('VerificationRraPra', 'Api\RraPraController@VerificationRraPra');
    Route::post('VerificationRraPraDev', 'Api\RraPraController@VerificationRraPraDev');
    Route::post('VerificationPMRraPra', 'Api\RraPraController@VerificationPMRraPra');
    Route::post('UnverificationRraPra', 'Api\RraPraController@UnverificationRraPra');
    Route::get('ReportRra', 'Api\RraPraController@ReportRra');
    Route::get('ReportPra', 'Api\RraPraController@ReportPra');
    
    
    
    // REPORT SCOOPING VISIT-RRA-PRA
    Route::get('ReportScooping', 'Api\RraPraController@ReportScooping');
    Route::get('ReportRra', 'Api\RraPraController@ReportRra');
    Route::get('ReportPra', 'Api\RraPraController@ReportPra');

    // Farmer
    Route::get('GetFarmerAllAdmin', 'Api\FarmerController@GetFarmerAllAdmin');
    Route::get('GetFarmerAllAdminDev', 'Api\FarmerController@GetFarmerAllAdminDev');
    Route::get('GetFarmerNoAll', 'Api\FarmerController@GetFarmerNoAll');
    Route::get('GetFarmerAll', 'Api\FarmerController@GetFarmerAll');
    Route::get('GetFarmerAllDesa', 'Api\FarmerController@GetFarmerAllDesa');
    Route::get('GetFarmerNotComplete', 'Api\FarmerController@GetFarmerNotComplete');
    Route::get('GetFarmerCompleteNotApprove', 'Api\FarmerController@GetFarmerCompleteNotApprove');
    Route::get('GetFarmerCompleteAndApprove', 'Api\FarmerController@GetFarmerCompleteAndApprove');
    Route::get('GetFarmerDetail', 'Api\FarmerController@GetFarmerDetail');
    Route::get('GetFarmerDetailWeb', 'Api\FarmerController@GetFarmerDetailWeb');
    Route::get('GetFarmerDetailKtpNo', 'Api\FarmerController@GetFarmerDetailKtpNo');
    Route::get('GetFarmerNoDropDown', 'Api\FarmerController@GetFarmerNoDropDown');
    Route::get('GetFarmerGroupsDropDown', 'Api\FarmerController@GetFarmerGroupsDropDown');
    Route::post('AddMandatoryFarmer', 'Api\FarmerController@AddMandatoryFarmer');
    
    Route::post('AddDetailFarmer', 'Api\FarmerController@AddDetailFarmer');
    Route::get('GetFarmerDetail', 'Api\FarmerController@GetFarmerDetail');
    
    Route::post('UpdateFarmer', 'Api\FarmerController@UpdateFarmer');
    Route::post('UpdateFarmerFF', 'Api\FarmerController@UpdateFarmerFF');
    Route::post('SoftDeleteFarmer', 'Api\FarmerController@SoftDeleteFarmer');
    Route::post('VerificationFarmer', 'Api\FarmerController@VerificationFarmer');
    Route::post('UnverificationFarmer', 'Api\FarmerController@UnverificationFarmer');
    Route::post('DeleteFarmer', 'Api\FarmerController@DeleteFarmer');
    Route::get('RemoveOldFarmer', 'Api\FarmerController@RemoveOldFarmer');
    Route::get('FarmerTrashedData', 'Api\FarmerController@FarmerTrashedData');
    Route::get('FarmerRestoreData', 'Api\FarmerController@FarmerRestoreData');
    // export farmer
    Route::post('ExportFarmerAllAdminNew', 'Api\FarmerController@ExportFarmerAllAdminNew');

    // Lahan
    Route::get('GetLahanAllAdmin', 'Api\LahanController@GetLahanAllAdmin');
    Route::get('GetLahanAllAdminNew', 'Api\LahanController@GetLahanAllAdminNew');
    Route::get('GetLahanAllAdminView', 'Api\LahanController@GetLahanAllAdminView');
    Route::get('GetLahanAll', 'Api\LahanController@GetLahanAll');
    Route::get('GetLahanAllDesa', 'Api\LahanController@GetLahanAllDesa');
    Route::get('GetLahanNotComplete', 'Api\LahanController@GetLahanNotComplete');
    Route::get('GetLahanFF', 'Api\LahanController@GetLahanFF');
    Route::get('GetLahanCompleteNotApprove', 'Api\LahanController@GetLahanCompleteNotApprove');
    Route::get('GetCompleteAndApprove', 'Api\LahanController@GetCompleteAndApprove');
    Route::get('GetLahanDetail', 'Api\LahanController@GetLahanDetail');
    Route::get('GetLahanDetailLahanNo', 'Api\LahanController@GetLahanDetailLahanNo');
    Route::get('GetLahanDetailBarcode', 'Api\LahanController@GetLahanDetailBarcode');
    Route::post('AddMandatoryLahan', 'Api\LahanController@AddMandatoryLahan');
    Route::post('AddMandatoryLahanComplete', 'Api\LahanController@AddMandatoryLahanComplete');
    Route::post('AddMandatoryLahanBarcode', 'Api\LahanController@AddMandatoryLahanBarcode');
    Route::get('GetLahanDetailTrees', 'Api\LahanController@GetLahanDetailTrees');
    Route::post('AddDetailLahan', 'Api\LahanController@AddDetailLahan');
    Route::post('DeleteDetailLahan', 'Api\LahanController@DeleteDetailLahan');
    Route::post('UpdateLahan', 'Api\LahanController@UpdateLahan');
    Route::post('UpdateLahanGIS', 'Api\LahanController@UpdateLahanGIS');
    Route::post('VerificationLahan', 'Api\LahanController@VerificationLahan');
    Route::post('UnverificationLahan', 'Api\LahanController@UnverificationLahan');
    Route::post('SoftDeleteLahan', 'Api\LahanController@SoftDeleteLahan');
    Route::post('UpdateDetailLahanPohon', 'Api\LahanController@UpdateDetailLahanPohon');
    
    //TUTUPAN LAHAN
    Route::get('GetLahanTutupanRequestAllAdmin', 'Api\LahanTutupanController@GetLahanTutupanRequestAllAdmin');
    Route::get('GetDetailTutupanLahanRequest', 'Api\LahanTutupanController@GetDetailTutupanLahanRequest');
    Route::post('AddLahanTutupanRequest', 'Api\LahanTutupanController@AddLahanTutupanRequest');
    Route::post('UpdateLahanTutupanRequest', 'Api\LahanTutupanController@UpdateLahanTutupanRequest');
    Route::post('VerificationLahanTutupanFC', 'Api\LahanTutupanController@VerificationLahanTutupanFC');
    Route::post('VerificationLahanTutupanUM', 'Api\LahanTutupanController@VerificationLahanTutupanUM');
    Route::post('UnverificationLahanTutupan', 'Api\LahanTutupanController@UnverificationLahanTutupan');
    
    
    // LAHAN UMUM
    Route::get('GetLahanUmumAllAdmin', 'Api\LahanUmumController@GetLahanUmumAllAdmin');
    Route::get('GetDetailLahanUmum', 'Api\LahanUmumController@GetDetailLahanUmum');
    Route::get('GetDetailLahanUmumMOU', 'Api\LahanUmumController@GetDetailLahanUmumMOU');
    Route::get('GetLahanUmumAll', 'Api\LahanUmumController@GetLahanUmumAll');
    Route::post('AddMandatoryLahanUmum', 'Api\LahanUmumController@AddMandatoryLahanUmum');
    Route::post('AddDetailLahanUmum', 'Api\LahanController@AddDetailLahanUmum');
    Route::post('UpdateLahanUmum', 'Api\LahanUmumController@UpdateLahanUmum');
    Route::post('UpdateHoleLahanUmum','Api\LahanUmumController@UpdateHoleLahanUmum');
    Route::post('VerificationLahanUmum', 'Api\LahanUmumController@VerificationLahanUmum');
    Route::post('PlantingHoleVerificationLahanUmum', 'Api\LahanUmumController@PlantingHoleVerificationLahanUmum');
    Route::post('UnverificationLahanUmum', 'Api\LahanUmumController@UnverificationLahanUmum');
    Route::post('SoftDeleteLahanUmum', 'Api\LahanUmumController@SoftDeleteLahanUmum');
    Route::post('UnverificationOneLahanUmum', 'Api\LahanUmumController@UnverificationOneLahanUmum');
    Route::post('CreateDistributionLahanUmum', 'Api\LahanUmumController@CreateDistributionLahanUmum');
    Route::get('GetUmumDistributionDetailReport', 'Api\LahanUmumController@GetUmumDistributionDetailReport');
    Route::get('GetUmumDistributionReport', 'Api\LahanUmumController@GetUmumDistributionReport');
    Route::get('GetUmumDistributionAdjustment', 'Api\LahanUmumController@GetUmumDistributionAdjustment');
    Route::post('DistributionVerificationPM', 'Api\LahanUmumController@DistributionVerificationPM');
    Route::post('CreateLahanUmumAdjustment', 'Api\LahanUmumController@CreateLahanUmumAdjustment');
    Route::post('UpdatedDistributionLahanUmum', 'Api\LahanUmumController@UpdatedDistributionLahanUmum');
    Route::post('UpdatedLoadingLahanUmum', 'Api\LahanUmumController@UpdatedLoadingLahanUmum');
    Route::get('GetLoadingLineLahanUmum', 'Api\LahanUmumController@GetLoadingLineLahanUmum');
    Route::get('GetLoadingLineDetailLahanUmum', 'Api\LahanUmumController@GetLoadingLineDetailLahanUmum');
    Route::get('GetMonitoringLahanUmumAdmin', 'Api\LahanUmumController@GetMonitoringLahanUmumAdmin');
    Route::get('GetMonitoringDetailLahanUmumAdmin', 'Api\LahanUmumController@GetMonitoringDetailLahanUmumAdmin');
    Route::post('CreateMonitoringLahanUmum', 'Api\LahanUmumController@CreateMonitoringLahanUmum');
    Route::post('UpdateMonitoringLahanUmum', 'Api\LahanUmumController@UpdateMonitoringLahanUmum');
    Route::post('SoftDeleteMonitoringLahanUmum', 'Api\LahanUmumController@SoftDeleteMonitoringLahanUmum');
    Route::post('DeleteMonitoringLahanUmum', 'Api\LahanUmumController@DeleteMonitoringLahanUmum');
    Route::post('ValidateMonitoringLahanUmum', 'Api\LahanUmumController@ValidateMonitoringLahanUmum');
    Route::post('UnverificationMonitoringLahanUmum', 'Api\LahanUmumController@UnverificationMonitoringLahanUmum');
    Route::post('MonitoringLahanUmumVerificationPM', 'Api\LahanUmumController@MonitoringLahanUmumVerificationPM');
    Route::post('CreateMonitoringLahanUmum', 'Api\LahanUmumController@CreateMonitoringLahanUmum');
    Route::post('UpdateMonitoringLahanUmum', 'Api\LahanUmumController@UpdateMonitoringLahanUmum');
    Route::post('DestroyLahanUmum', 'Api\LahanUmumController@DestroyLahanUmum');

    

    // TREES
    Route::get('GetTreesAll', 'Api\TreesController@GetTreesAll');
    Route::get('GetTreesLocation', 'Api\TreesController@GetTreesLocation');
    Route::post('AdjustTreesLocation', 'Api\TreesController@AdjustTreesLocation');
    Route::get('GetTrees', 'Api\TreesController@GetTrees');
    Route::get('GetTreesDetail', 'Api\TreesController@GetTreesDetail');
    Route::post('AddTrees', 'Api\TreesController@AddTrees');
    Route::post('UpdateTrees', 'Api\TreesController@UpdateTrees');
    Route::post('DeleteTrees', 'Api\TreesController@DeleteTrees');

    // Field Facilitator
    Route::get('GetFieldFacilitatorAllWeb', 'Api\FieldFacilitatorController@GetFieldFacilitatorAllWeb');
    Route::get('GetFieldFacilitatorAll', 'Api\FieldFacilitatorController@GetFieldFacilitatorAll');
    Route::get('GetFieldFacilitator', 'Api\FieldFacilitatorController@GetFieldFacilitator');
    Route::get('GetFieldFacilitatorDetail', 'Api\FieldFacilitatorController@GetFieldFacilitatorDetail');
    Route::post('AddFieldFacilitator', 'Api\FieldFacilitatorController@AddFieldFacilitator');
    Route::post('UpdateFieldFacilitator', 'Api\FieldFacilitatorController@UpdateFieldFacilitator');
    Route::post('DeleteFieldFacilitator', 'Api\FieldFacilitatorController@DeleteFieldFacilitator');
    Route::post('NonactivateFieldFacilitator', 'Api\FieldFacilitatorController@NonactivateFieldFacilitator');
    Route::post('ChangeFCFieldFacilitator', 'Api\FieldFacilitatorController@ChangeFCFieldFacilitator');
    
    Route::get('GetActivityUserId', 'Api\ActivityController@GetActivityUserId');
    Route::get('GetActivityLahanUser', 'Api\ActivityController@GetActivityLahanUser');
    Route::post('AddActivity', 'Api\ActivityController@AddActivity');
    Route::post('UpdateActivity', 'Api\ActivityController@UpdateActivity');
    Route::post('DeleteActivity', 'Api\ActivityController@DeleteActivity');
    Route::get('GetActivityDetail', 'Api\ActivityController@GetActivityDetail');
    Route::post('AddActivityDetail', 'Api\ActivityController@AddActivityDetail');
    Route::post('UpdateActivityDetail', 'Api\ActivityController@UpdateActivityDetail');
    Route::post('DeleteActivityDetail', 'Api\ActivityController@DeleteActivityDetail');

    // Sosialisasi Program & Form Minat
    Route::get('GetFormMinatAllAdmin', 'Api\FormMinatController@GetFormMinatAllAdmin');
    Route::get('GetFormMinatAll', 'Api\FormMinatController@GetFormMinatAll');
    Route::get('GetFormMinatDetail', 'Api\FormMinatController@GetFormMinatDetail');
    Route::post('AddFormMinat', 'Api\FormMinatController@AddFormMinat');
    Route::post('UpdateFormMinat', 'Api\FormMinatController@UpdateFormMinat');
    Route::post('DeleteFormMinat', 'Api\FormMinatController@DeleteFormMinat');
    Route::post('VerificationFormMinat', 'Api\FormMinatController@VerificationFormMinat');
    Route::post('UnverificationFormMinat', 'Api\FormMinatController@UnverificationFormMinat');

    Route::get('GetEmployeeAll', 'Api\EmployeeController@GetEmployeeAll');
    Route::get('GetEmployeebyManager', 'Api\EmployeeController@GetEmployeebyManager');
    Route::get('GetEmployeebyPosition', 'Api\EmployeeController@GetEmployeebyPosition');
    Route::get('GetFFbyUMandFC', 'Api\EmployeeController@GetFFbyUMandFC');
    Route::get('GetEmployeeManagePosition', 'Api\EmployeeController@GetEmployeeManagePosition');
    Route::get('GetJobPosition', 'Api\EmployeeController@GetJobPosition');
    Route::post('EditPositionEmp', 'Api\EmployeeController@EditPositionEmp');
    Route::get('GetEmployeeMenuAccess', 'Api\EmployeeController@GetEmployeeMenuAccess');
    Route::post('EditMenuAccessEmp', 'Api\EmployeeController@EditMenuAccessEmp');
    Route::post('AddEmployee', 'Api\EmployeeController@AddEmployee');
    Route::post('EditEmployee', 'Api\EmployeeController@EditEmployee');
    Route::post('DeleteEmployee', 'Api\EmployeeController@DeleteEmployee');

    // Sosialisasi Tanam
    Route::get('GetSosisalisasiTanamAdmin', 'Api\SosialisasiTanamController@GetSosisalisasiTanamAdmin');
    Route::get('GetSosisalisasiTanamAdminLimit', 'Api\SosialisasiTanamController@GetSosisalisasiTanamAdminLimit');
    Route::get('GetSosisalisasiTanamTimeAll', 'Api\SosialisasiTanamController@GetSosisalisasiTanamTimeAll');
    Route::get('GetSosisalisasiTanamFF', 'Api\SosialisasiTanamController@GetSosisalisasiTanamFF');
    Route::get('GetDetailSosisalisasiTanam', 'Api\SosialisasiTanamController@GetDetailSosisalisasiTanam');
    Route::get('GetDetailSosisalisasiTanamFFNo', 'Api\SosialisasiTanamController@GetDetailSosisalisasiTanamFFNo');
    Route::get('GetOpsiPolaTanamOptions', 'Api\SosialisasiTanamController@GetOpsiPolaTanamOptions');
    Route::post('AddSosisalisasiTanam', 'Api\SosialisasiTanamController@AddSosisalisasiTanam');
    Route::post('UpdateSosisalisasiTanam', 'Api\SosialisasiTanamController@UpdateSosisalisasiTanam');
    Route::post('UpdatePohonSosisalisasiTanam', 'Api\SosialisasiTanamController@UpdatePohonSosisalisasiTanam');
    Route::post('SoftDeleteSosisalisasiTanam', 'Api\SosialisasiTanamController@SoftDeleteSosisalisasiTanam');
    Route::post('ValidateSosisalisasiTanam', 'Api\SosialisasiTanamController@ValidateSosisalisasiTanam');
    Route::post('UnverificationSosialisasiTanam', 'Api\SosialisasiTanamController@UnverificationSosialisasiTanam');
    Route::post('createLogSostamCheck', 'Api\SosialisasiTanamController@createLogSostamCheck');
    Route::get('getFFOptionsSostam', 'Api\SosialisasiTanamController@getFFOptionsSostam');
    Route::get('getFFLahanSostam', 'Api\SosialisasiTanamController@getFFLahanSostam');
    Route::post('createSostamByFF', 'Api\SosialisasiTanamController@createSostamByFF');
    Route::post('UpdateSosialisasiTanamPeriod', 'Api\SosialisasiTanamController@UpdateSosialisasiTanamPeriod');
    Route::post('deleteSosialisasiTanamForm', 'Api\SosialisasiTanamController@deleteSosialisasiTanamForm');
    Route::post('deleteSosialisasiTanamPeriod', 'Api\SosialisasiTanamController@deleteSosialisasiTanamPeriod');
    Route::post('createSosialisasiTanamPeriod', 'Api\SosialisasiTanamController@createSosialisasiTanamPeriod');
    Route::post('UpdatePetaniSusulan', 'Api\SosialisasiTanamController@UpdatePetaniSusulan');
    
    // API For Nursery
        // sostam
    // Route::get('GEKO_GetSosisalisasiTanamAdminLimit', 'Api\SosialisasiTanamController@GetSosisalisasiTanamAdminLimit');
    Route::get('SostamDetailForNursery', 'Api\SosialisasiTanamController@SostamDetailForNursery');
    Route::get('SostamEventForNursery', 'Api\SosialisasiTanamController@SostamEventForNursery');
        // penlub 
    Route::get('NurseryGetPlantingHole', 'Api\PlantingHoleController@NurseryGetPlantingHole');
    Route::get('GEKO_getPrintLabelList', 'Api\PlantingHoleController@GEKO_getPrintLabelList');
        // distribution
    Route::get('GEKO_getLoadingLine', 'Api\DistributionController@GEKO_getLoadingLine');

    //Planting Hole
    Route::get('GetPlantingHoleAdmin', 'Api\PlantingHoleController@GetPlantingHoleAdmin');
    Route::get('GetPlantingHoleFF', 'Api\PlantingHoleController@GetPlantingHoleFF');
    Route::get('GetPlantingHoleDetail', 'Api\PlantingHoleController@GetPlantingHoleDetail');
    Route::get('GetPlantingHoleDetailFFNo', 'Api\PlantingHoleController@GetPlantingHoleDetailFFNo');
    Route::post('AddPlantingHole', 'Api\PlantingHoleController@AddPlantingHole');
    Route::post('AddPlantingHoleByFFNo', 'Api\PlantingHoleController@AddPlantingHoleByFFNo');
    Route::post('UpdatePlantingHole', 'Api\PlantingHoleController@UpdatePlantingHole');
    Route::post('UpdatePlantingHoleAll', 'Api\PlantingHoleController@UpdatePlantingHoleAll');
    Route::post('UpdatePohonPlantingHole', 'Api\PlantingHoleController@UpdatePohonPlantingHole');
    Route::post('SoftDeletePlantingHole', 'Api\PlantingHoleController@SoftDeletePlantingHole');
    Route::post('ValidatePlantingHole', 'Api\PlantingHoleController@ValidatePlantingHole');
    Route::post('UnvalidatePlantingHole', 'Api\PlantingHoleController@UnvalidatePlantingHole');
    Route::get('CheckedPlantingHole', 'Api\PlantingHoleController@CheckedPlantingHole');
    Route::get('GetPlantingHoleLahanUmumAdmin', 'Api\PlantingHoleController@GetPlantingHoleLahanUmumAdmin');
    Route::get('GetPlantingHoleLahanUmumDetail', 'Api\PlantingHoleController@GetPlantingHoleLahanUmumDetail');

    //Monitoring
    Route::get('GetMonitoringFF', 'Api\MonitoringController@GetMonitoringFF');
    Route::get('GetMonitoringAdmin', 'Api\MonitoringController@GetMonitoringAdmin');
    Route::get('GetMonitoringDetail', 'Api\MonitoringController@GetMonitoringDetail'); 
    Route::get('GetMonitoringDetailFFNo', 'Api\MonitoringController@GetMonitoringDetailFFNo'); 
    Route::get('GetMonitoringTest', 'Api\MonitoringController@GetMonitoringTest');    
    Route::post('AddMonitoring', 'Api\MonitoringController@AddMonitoring');
    Route::post('AddMonitoringNew', 'Api\MonitoringController@AddMonitoringNew');
    Route::post('UpdateMonitoring', 'Api\MonitoringController@UpdateMonitoring');
    Route::post('UpdatePohonMonitoring', 'Api\MonitoringController@UpdatePohonMonitoring');
    Route::post('SoftDeleteMonitoring', 'Api\MonitoringController@SoftDeleteMonitoring');
    Route::post('DeleteMonitoring', 'Api\MonitoringController@DeleteMonitoring');
    Route::post('ValidateMonitoring', 'Api\MonitoringController@ValidateMonitoring');
    Route::post('MonitoringVerificationUM', 'Api\MonitoringController@MonitoringVerificationUM');
    Route::post('UnverificationMonitoring', 'Api\MonitoringController@UnverificationMonitoring');
    Route::post('UpdateSPPTMonitoring', 'Api\MonitoringController@UpdateSPPTMonitoring');
    
    //Monitoring 2
    Route::get('GetMonitoring2FF', 'Api\MonitoringController@GetMonitoring2FF');
    Route::get('GetMonitoring2Admin', 'Api\MonitoringController@GetMonitoring2Admin');
    Route::get('GetMonitoring2Detail', 'Api\MonitoringController@GetMonitoring2Detail'); 
    Route::get('GetMonitoring2DetailFFNo', 'Api\MonitoringController@GetMonitoring2DetailFFNo'); 
    Route::post('AddMonitoring2', 'Api\MonitoringController@AddMonitoring2');
    Route::post('UpdateMonitoring2', 'Api\MonitoringController@UpdateMonitoring2');
    Route::post('UpdatePohonMonitoring2', 'Api\MonitoringController@UpdatePohonMonitoring2');
    Route::post('SoftDeleteMonitoring2', 'Api\MonitoringController@SoftDeleteMonitoring2');
    Route::post('ValidateMonitoring2', 'Api\MonitoringController@ValidateMonitoring2');
    // Monitoring 2 NEW
    Route::get('ShowTotalTreesGeneral', 'Api\Monitoring2Controller@ShowTotalTreesGeneral');
    Route::get('ShowListLandPerTreeCode', 'Api\Monitoring2Controller@ShowListLandPerTreeCode');
    
    // Farmer Training Routers
    Route::get('GetFarmerTrainingAllAdmin', 'Api\FarmerTrainingController@GetFarmerTrainingAllAdmin');
    Route::get('DetailFarmerTraining', 'Api\FarmerTrainingController@DetailFarmerTraining');
    Route::get('GetFarmerTrainingAll', 'Api\FarmerTrainingController@GetFarmerTrainingAll');
    Route::get('GetFarmerTrainingAllTempDelete', 'Api\FarmerTrainingController@GetFarmerTrainingAllTempDelete');
    Route::post('AddFarmerTraining', 'Api\FarmerTrainingController@AddFarmerTraining');
    Route::post('UpdateFarmerTraining', 'Api\FarmerTrainingController@UpdateFarmerTraining');
    Route::post('AddDetailFarmerTraining', 'Api\FarmerTrainingController@AddDetailFarmerTraining');
    Route::post('UpdateFarmerTraining', 'Api\FarmerTrainingController@UpdateFarmerTraining');
    Route::post('DeleteFarmerTrainingDetail', 'Api\FarmerTrainingController@DeleteFarmerTrainingDetail');
    Route::post('SoftDeleteFarmerTraining', 'Api\FarmerTrainingController@SoftDeleteFarmerTraining');
    Route::post('DeleteFarmerTraining', 'Api\FarmerTrainingController@DeleteFarmerTraining');
    Route::post('UploadExternalFarmerTraining', 'Api\FarmerTrainingController@TrialUploadPhotoExternal');
    Route::get('GetTrainingMaterials', 'Api\FarmerTrainingController@GetTrainingMaterials');
    
    // Organic
    Route::get('GetOrganicAll', 'Api\OrganicController@GetOrganicAll');
    Route::get('GetOrganicAllAdmin', 'Api\OrganicController@GetOrganicAllAdmin');
    Route::get('GetOrganicFF', 'Api\OrganicController@GetOrganicFF');
    Route::post('AddOrganic', 'Api\OrganicController@AddOrganic');
    Route::post('UpdateOrganic', 'Api\OrganicController@UpdateOrganic');
    Route::post('SoftDeleteOrganic', 'Api\OrganicController@SoftDeleteOrganic');
    Route::post('DeleteOrganic', 'Api\OrganicController@DeleteOrganic');
    Route::post('ValidateOrganic', 'Api\OrganicController@ValidateOrganic');
    Route::post('UnvalidateOrganic', 'Api\OrganicController@UnvalidateOrganic');
    
    // KPI
    Route::get('KPIFF', 'Api\KPIController@ByFF');
    Route::get('KPIFC', 'Api\KPIController@ByFC');
    Route::get('KPIFCDev', 'Api\KPIController@ByFCDev');
    Route::get('KPIUM', 'Api\KPIController@ByUM');
    Route::get('KPIUMDev', 'Api\KPIController@ByUMDev');

    // Distribution
    Route::get('DistributionCalendar', 'Api\DistributionController@DistributionCalendar');
    Route::get('DistributionCalendarLahanUmum', 'Api\DistributionController@DistributionCalendarLahanUmum');
    Route::get('DistributionSeedDetail', 'Api\DistributionController@DistributionSeedDetail');
    Route::get('DistributionPeriodDetail', 'Api\DistributionController@DistributionPeriodDetail');
    Route::get('DistributionPeriodLahanUmumDetail', 'Api\DistributionController@DistributionPeriodLahanUmumDetail');
    Route::post('UpdateLahanUmumPeriod', 'Api\DistributionController@UpdateLahanUmumPeriod');
    Route::get('GetDistributionFF', 'Api\DistributionController@GetDistributionFF');
    Route::post('UpdateDistribution', 'Api\DistributionController@UpdateDistribution');
    Route::post('CompletedDistribution', 'Api\DistributionController@CompletedDistribution');
    Route::post('LoadedDistribution', 'Api\DistributionController@LoadedDistribution');
    Route::post('FinishLoadingBagsDistributions', 'Api\DistributionController@FinishLoadingBagsDistributions');
    Route::get('GetDistributionReport', 'Api\DistributionController@GetDistributionReport');
    Route::get('GetDetailDistributionReport', 'Api\DistributionController@GetDetailDistributionReport');
    Route::post('CreateAdjustment', 'Api\DistributionController@CreateAdjustment');
    Route::post('UnverificationDistribution', 'Api\DistributionController@UnverificationDistribution');
    Route::post('DistributionVerificationUM', 'Api\DistributionController@DistributionVerificationUM');

    // SeedlingChangeRequest
    Route::get('SeedlingChangeRequest/GetMU', 'Api\SeedlingChangeRequestController@GetMU');
    Route::get('SeedlingChangeRequest/GetFF', 'Api\SeedlingChangeRequestController@GetFF');
    Route::get('SeedlingChangeRequest/GetFarmer', 'Api\SeedlingChangeRequestController@GetFarmer');
    Route::get('SeedlingChangeRequest/GetLand', 'Api\SeedlingChangeRequestController@GetLand');
    Route::get('SeedlingChangeRequest/GetLandDetail', 'Api\SeedlingChangeRequestController@GetLandDetail');
    Route::get('SeedlingChangeRequest/GetTreesPerMU', 'Api\SeedlingChangeRequestController@GetTreesPerMU');
    Route::get('SeedlingChangeRequest/GetRequests', 'Api\SeedlingChangeRequestController@GetRequests');
    Route::get('SeedlingChangeRequest/DetailRequest', 'Api\SeedlingChangeRequestController@DetailRequest');
    Route::post('SeedlingChangeRequest/AddRequest', 'Api\SeedlingChangeRequestController@AddRequest');
    Route::post('SeedlingChangeRequest/Verification', 'Api\SeedlingChangeRequestController@Verification');
    Route::post('SeedlingChangeRequest/Reject', 'Api\SeedlingChangeRequestController@Reject');
    Route::post('SeedlingChangeRequest/Cancel', 'Api\SeedlingChangeRequestController@Cancel');
    
    // Packing Label
    Route::get('GetPackingLabelByLahan', 'Api\DistributionController@GetPackingLabelByLahan');
    Route::get('GetPackingLabelLahanUmum', 'Api\DistributionController@GetPackingLabelLahanUmum');
    Route::get('GetPackingLabelByLahanTemp', 'Api\DistributionController@GetPackingLabelByLahanTemp');
    // Loading Line
    Route::get('GetLoadingLine', 'Api\DistributionController@GetLoadingLine');
    Route::get('GetLoadingLineDetailFF', 'Api\DistributionController@GetLoadingLineDetailFF');
    Route::post('LoadedDistributionBagsNumber', 'Api\DistributionController@LoadedDistributionBagsNumber');
    
    //Driver & Truck
    Route::get('GetDriver', 'Api\TransportationController@GetDriver');
    Route::get('GetDetailDriver', 'Api\TransportationController@GetDetailDriver');
    Route::post('AddDriver', 'Api\TransportationController@AddDriver');
    Route::post('UpdateDriver', 'Api\TransportationController@UpdateDriver');
    Route::post('DeleteDriver', 'Api\TransportationController@DeleteDriver');
    Route::get('GetTruck', 'Api\TransportationController@GetTruck');
    Route::get('GetDetailTruck', 'Api\TransportationController@GetDetailTruck');
    Route::post('AddTruck', 'Api\TransportationController@AddTruck');
    Route::post('UpdateTruck', 'Api\TransportationController@UpdateTruck');
    Route::post('DeleteTruck', 'Api\TransportationController@DeleteTruck');
    
    // Temporary
    Route::get('fixNullMaxSeedAmountSosialisasiTanam', 'Api\TemporaryController@fixNullMaxSeedAmountSosialisasiTanam');
    Route::get('getTopTreesSosialisasiTanam', 'Api\TemporaryController@getTopTreesSosialisasiTanam');
    Route::get('GetDistributedLabel', 'Api\TemporaryController@GetDistributedLabel');
    Route::get('GetDistributionReportFull', 'Api\TemporaryController@GetDistributionReport');
    Route::get('GetDistributionReportFullPerFF', 'Api\TemporaryController@GetDistributionReportPerFF');
    Route::post('UpdateLatLongLahan', 'Api\TemporaryController@updateLatLongLahan');
    Route::post('MassUpdateLatLongLahan', 'Api\TemporaryController@MassUpdateLatLongLahan');
    Route::post('UpdateLatLongLahanTemp', 'Api\TemporaryController@updateLatLongLahanTemp');
    Route::get('CheckLahan', 'Api\TemporaryController@CheckLahan');
    Route::get('getDataLahanRequestMbakNovi', 'Api\TemporaryController@getDataLahanRequestMbakNovi');
    Route::get('RequestDataMbakAnin', 'Api\TemporaryController@RequestDataMbakAnin');
    Route::get('GetSurvivalRate', 'Api\TemporaryController@GetSurvivalRate');
    Route::get('setLoadAndDistributeData', 'Api\TemporaryController@setLoadAndDistributeData');
    // Route::get('GetDataLahanByDocumentSPPT', 'Api\TemporaryController@GetDataLahanByDocumentSPPT');
    Route::post('DeleteDuplicatedVillage', 'Api\TemporaryController@DeleteDuplicatedVillage');
    
});
