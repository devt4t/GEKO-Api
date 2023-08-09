<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use DB;
use Carbon;

use App\Employee;
use App\FieldFacilitator;
use App\PlantingHoleSurviellance;
use App\PlantingHoleSurviellanceDetail;
use App\PlantingSocializations;
use App\PlantingSocializationsPeriod;
use App\PlantingSocializationsDetails as PlantingSocializationsDetail;
use App\Distribution;
use App\DistributionDetail;
use App\DistributionAdjustment;

class DistributionAdjustmentController extends Controller 
{

}