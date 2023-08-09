<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('/ExportLahanTest', 'Api\LahanController@ExportLahanAllAdmin');

Route::get('/ExportLahanAllAdmin', 'Api\LahanController@ExportLahanAllAdmin');
Route::get('/ExportLahanAllSuperAdmin', 'Api\LahanController@ExportLahanAllSuperAdmin');
Route::get('/ExportSostamAllSuperAdmin', 'Api\SosialisasiTanamController@ExportSostamAllSuperAdmin');
Route::get('/ExportFarmerAllAdmin', 'Api\FarmerController@ExportFarmerAllAdmin');

Route::get('/CetakLabelSosTam', 'Api\SosialisasiTanamController@CetakLabelSosTam');

//Lahan Umum
Route::get('ExportLahanUmum', 'Api\LahanUmumController@ExportLahanUmum');
Route::get('ExportLahanUmumPenilikanLubang', 'Api\LahanUmumController@ExportLahanUmumPenilikanLubang');
Route::get('CetakLabelUmumLubangTanam', 'Api\LahanUmumController@CetakLabelUmumLubangTanam');
Route::get('CetakLabelUmumLubangTanamTemp', 'Api\PlantingHoleController@CetakLabelUmumLubangTanamTemp');
Route::get('CetakUmumBuktiPenyerahan', 'Api\LahanUmumController@CetakUmumBuktiPenyerahan');
Route::get('ExportDistributionReportLahanUmum', 'Api\LahanUmumController@ExportDistributionReportLahanUmum');
Route::get('ExportMonitoringLahanUmum', 'Api\LahanUmumController@ExportMonitoringLahanUmum');

// Penilikan Lubang Tanam
Route::get('/CetakLabelLubangTanam', 'Api\PlantingHoleController@CetakLabelLubangTanam');
Route::get('/CetakLabelLubangTanamTemp', 'Api\PlantingHoleController@CetakLabelLubangTanamTemp');
Route::get('/ExportExcelPenilikanLubang', 'Api\PlantingHoleController@ExportExcelPenilikanLubang');

Route::get('/CetakBuktiPenyerahanTemp', 'Api\PlantingHoleController@CetakBuktiPenyerahan');
Route::get('/CetakBuktiPenyerahan', 'Api\PlantingHoleController@CetakBuktiPenyerahan');
Route::get('/CetakExcelPlantingHoleAll', 'Api\PlantingHoleController@CetakExcelPlantingHoleAll');
Route::get('/CetakExcelLoadingPlan', 'Api\PlantingHoleController@CetakExcelLoadingPlan');
Route::get('/CetakExcelPackingPlan', 'Api\PlantingHoleController@CetakExcelPackingPlan');
Route::get('/CetakExcelShippingPlan', 'Api\PlantingHoleController@CetakExcelShippingPlan');

// Material Organic Export
Route::get('/api/ExportMaterialOrganic', 'Api\OrganicController@ExportMaterialOrganic');
// Farmer Training Export
Route::get('/api/ExportFarmerTraining', 'Api\FarmerTrainingController@ExportFarmerTraining');

// Monitoring Export
Route::get('/ExportMonitoring', 'Api\MonitoringController@ExportMonitoring');
// Distribution Export
Route::get('/ExportBibitExcel', 'Api\TemporaryController@ExportBibitByFF');
Route::get('/ExportBibitLahanUmumExcel', 'Api\TemporaryController@ExportBibitLahanUmum');
Route::get('/ExportDistributionByFarmer', 'Api\TemporaryController@ExportDistributionByFarmer');
Route::get('/ExportDistributionReport', 'Api\DistributionController@ExportDistributionReport');

//Mail to GIS
Route::get('/send-mail', 'Api\RraPraController@MailtoGis');

// KPI
Route::get('KPIExportExcel', 'Api\KPIController@KPIExportExcel');
