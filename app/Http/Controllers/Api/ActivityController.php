<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DB;
use Carbon\Carbon;
use App\Activity;
use App\ActivityDetail;

class ActivityController extends Controller
{
    /**
     * @SWG\Get(
     *   path="/api/GetActivityUserId",
     *   tags={"Activity"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Activity UserId",
     *   operationId="GetActivityUserId",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="user_id",in="query", required=true, type="string"),
     *      @SWG\Parameter(name="limit",in="query", type="integer"),
     *      @SWG\Parameter(name="offset",in="query", type="integer"),
     * )
     */
    public function GetActivityUserId(Request $request){
        $limit = $this->limitcheck($request->limit);
        $offset =  $this->offsetcheck($limit, $request->offset);
        try{
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|max:255'
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
            $GetActivityUserId = DB::table('activities')->select('activities.id', 'activities.activity_no', 
            'activities.activity_type', 'activities.total_trees', 'activities.programs', 'activities.activity_description', 'activities.lahan_no','activities.field_facilitator',
            'field_facilitators.name', 'activities.verification_code', 'activities.activity_date', 'activities.created_at')
            ->leftjoin('field_facilitators', 'field_facilitators.ff_no', '=', 'activities.field_facilitator')
            ->where('activities.user_id', '=', $request->user_id)->orderBy('activities.id', 'ASC')->limit($limit)->offset($offset)->get();
            // var_dump($GetFieldFacilitator);
            if(count($GetActivityUserId)!=0){
                $count = Activity::where('user_id', '=', $request->user_id)->count();
                $data = ['count'=>$count, 'data'=>$GetActivityUserId];
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
     *   path="/api/GetActivityLahanUser",
     *   tags={"Activity"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Activity Lahan User",
     *   operationId="GetActivityLahanUser",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="lahan_no",in="query", required=true, type="string"),
     *      @SWG\Parameter(name="user_id",in="query", required=true, type="string"),
     *      @SWG\Parameter(name="limit",in="query", type="integer"),
     *      @SWG\Parameter(name="offset",in="query", type="integer"),
     * )
     */
    public function GetActivityLahanUser(Request $request){
        $limit = $this->limitcheck($request->limit);
        $offset =  $this->offsetcheck($limit, $request->offset);
        try{
            $validator = Validator::make($request->all(), [
                'lahan_no' => 'required|integer|max:255',
                'user_id' => 'required|integer|max:255'
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }

            $GetActivityLahanUser = DB::table('activities')->select('activities.id', 'activities.activity_no', 
            'activities.activity_type', 'activities.total_trees', 'activities.programs', 'activities.activity_description','activities.lahan_no','activities.field_facilitator',
            'field_facilitators.name', 'activities.verification_code', 'activities.activity_date', 'activities.created_at')
            ->leftjoin('field_facilitators', 'field_facilitators.ff_no', '=', 'activities.field_facilitator')
            ->where('activities.lahan_no', '=', $request->lahan_no)
            ->where('activities.user_id', '=', $request->user_id)
            ->orderBy('activities.id', 'ASC')->limit($limit)->offset($offset)->get();
            // var_dump($GetFieldFacilitator);
            if(count($GetActivityLahanUser)!=0){
                $count = Activity::where('user_id', '=', $request->user_id)->where('lahan_no', '=', $request->lahan_no)->count();
                $data = ['count'=>$count, 'data'=>$GetActivityLahanUser];
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
     * @SWG\Post(
     *   path="/api/AddActivity",
	 *   tags={"Activity"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Add Activity",
     *   operationId="AddActivity",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Add Activity",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="activity_type", type="enum", example="planning/visit/distribution/realitation/monitoring"),
     *              @SWG\Property(property="activity_date", type="date", example="2021-04-05"),
     *              @SWG\Property(property="ff_no", type="string", example="12345678"),
     *              @SWG\Property(property="lahan_no", type="string", example="L00001"),
     *              @SWG\Property(property="total_trees", type="integer", example="222"),
     *              @SWG\Property(property="programs", type="string", example="test"),
     *              @SWG\Property(property="activity_description", type="string", example="Tanam Pohon"),
     *              @SWG\Property(property="user_id", type="string", example="U0001"),
     *              @SWG\Property(property="verification_code", type="string", example="1/2/3")
     *          ),
     *      )
     * )
     *
     */    
    public function AddActivity(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'activity_type' => 'required|max:255',
                'activity_date' => 'required|max:255',
                'ff_no' => 'required|max:255',
                'lahan_no' => 'required|max:255',
                'total_trees' => 'required|max:255',
                'programs' => 'required|max:255',
                'activity_description' => 'required',
                'user_id' => 'required|max:255',
                'verification_code' => 'required|max:255'
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }

            $getLastIdActivity = Activity::orderBy('activity_no','desc')->first(); 
            // var_dump($$getLastIdTrees->tree_code);
            $getYearNow = Carbon::now()->format('Y');
            if($getLastIdActivity){
                $ff_no = 'A'.str_pad(((int)substr($getLastIdActivity->activity_no,-4) + 1), 6, '0', STR_PAD_LEFT);
            }else{
                $ff_no = 'A000001';
            }            
    
            Activity::create([
                'activity_no' => $activity_no,
                'activity_type' => $request->activity_type,
                'activity_date' => $request->activity_date,
                'field_facilitator' => $request->ff_no,
                'lahan_no' => $request->lahan_no,
                'total_trees' => $request->total_trees,
                'programs' => $request->programs,
                'activity_description' => $request->activity_description,
                'user_id' => $request->user_id,
                'verification_code' => $request->verification_code,

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
     *   path="/api/UpdateActivity",
	 *   tags={"Activity"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Update Activity",
     *   operationId="UpdateActivity",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Update Activity",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="id", type="enum", example="1"),
     *              @SWG\Property(property="activity_type", type="enum", example="planning/visit/distribution/realitation/monitoring"),
     *              @SWG\Property(property="activity_date", type="date", example="2021-04-05"),
     *              @SWG\Property(property="ff_no", type="string", example="12345678"),
     *              @SWG\Property(property="lahan_no", type="string", example="L00001"),
     *              @SWG\Property(property="total_trees", type="integer", example="222"),
     *              @SWG\Property(property="programs", type="string", example="test"),
     *              @SWG\Property(property="activity_description", type="string", example="Tanam Pohon"),
     *              @SWG\Property(property="user_id", type="string", example="U0001"),
     *              @SWG\Property(property="verification_code", type="string", example="1/2/3")
     *          ),
     *      )
     * )
     *
     */    
    public function UpdateActivity(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'id' => 'required|max:255',
                'activity_type' => 'required|max:255',
                'activity_date' => 'required|max:255',
                'ff_no' => 'required|max:255',
                'lahan_no' => 'required|max:255',
                'total_trees' => 'required|max:255',
                'programs' => 'required|max:255',
                'activity_description' => 'required',
                'user_id' => 'required|max:255',
                'verification_code' => 'required|max:255'
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }          
    
            Activity::where('id', '=', $request->id)
            ->update([
                'activity_no' => $activity_no,
                'activity_type' => $request->activity_type,
                'activity_date' => $request->activity_date,
                'field_facilitator' => $request->ff_no,
                'lahan_no' => $request->lahan_no,
                'total_trees' => $request->total_trees,
                'programs' => $request->programs,
                'activity_description' => $request->activity_description,
                'user_id' => $request->user_id,
                'verification_code' => $request->verification_code,

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
     *   path="/api/DeleteActivity",
	 *   tags={"Activity"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Delete Activity",
     *   operationId="DeleteActivity",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Delete Activity",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="id", type="enum", example="1")
     *          ),
     *      )
     * )
     *
     */    
    public function DeleteActivity(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'id' => 'required|max:255'
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }     
    
            DB::table('activities')->where('id', $request->id)->delete();

            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200);
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }

    /**
     * @SWG\Get(
     *   path="/api/GetActivityDetail",
     *   tags={"Activity"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Activity Detail",
     *   operationId="GetActivityDetail",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="activity_no",in="query", type="string"),
     *      @SWG\Parameter(name="limit",in="query", type="integer"),
     *      @SWG\Parameter(name="offset",in="query", type="integer"),
     * )
     */
    public function GetActivityDetail(Request $request){
        $limit = $this->limitcheck($request->limit);
        $offset =  $this->offsetcheck($limit, $request->offset);
        try{
            $validator = Validator::make($request->all(), [
                'activity_no' => 'required|integer|max:255'
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }
            $GetActivityDetail = DB::table('activity_details')->select('id', 'activity_no', 
            'tree_code', 'amount', 'detail_date', 'tree_status', 'growth_percentage','diameter',
            'high', 'polybags', 'created_at', 'list_of_things')
            ->where('activity_no', '=', $request->activity_no)
            ->orderBy('id', 'ASC')->limit($limit)->offset($offset)->get();
            // var_dump($GetFieldFacilitator);
            if(count($GetActivityDetail)!=0){
                $count = ActivityDetail::where('activity_no', '=', $request->activity_no)->count();
                $data = ['count'=>$count, 'data'=>$GetActivityDetail];
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
     * @SWG\Post(
     *   path="/api/AddActivityDetail",
	 *   tags={"Activity"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Add Activity Detail",
     *   operationId="AddActivityDetail",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Add Activity Detail",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="activity_no", type="string", example="A000001"),
     *              @SWG\Property(property="tree_code", type="string", example="T0001"),
     *              @SWG\Property(property="amount", type="string", example="200"),
     *              @SWG\Property(property="detail_date", type="date", example="2021-02-20"),
     *              @SWG\Property(property="tree_status", type="string", example="Nullable"),
     *              @SWG\Property(property="growth_percentage", type="integer", example="Nullable"),
     *              @SWG\Property(property="diameter", type="integer", example="Nullable"),
     *              @SWG\Property(property="high", type="integer", example="Nullable"),
     *              @SWG\Property(property="polybags", type="boolean", example="Nullable"),
     *              @SWG\Property(property="list_of_things", type="string", example="List Of Things")
     *          ),
     *      )
     * )
     *
     */    
    public function AddActivityDetail(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'activity_no' => 'required|max:255',
                'tree_code' => 'required|max:255',
                'amount' => 'required|max:255',
                'detail_date' => 'required|max:255',
                'list_of_things' => 'required|max:255'
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }       
    
            ActivityDetail::create([
                'activity_no' => $request->activity_no,
                'tree_code' => $request->tree_code,
                'amount' => $request->amount,
                'detail_date' => $request->detail_date,

                'tree_status' => $this->ReplaceNull($request->tree_status, 'string'),
                'growth_percentage' => $this->ReplaceNull($request->growth_percentage, 'int'),
                'diameter' => $this->ReplaceNull($request->diameter, 'int'),
                'high' => $this->ReplaceNull($request->high, 'int'),
                'polybags' => $this->ReplaceNull($request->polybags, 'int'),

                'list_of_things' => $request->list_of_things,

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
     *   path="/api/UpdateActivityDetail",
	 *   tags={"Activity"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Update Activity Detail",
     *   operationId="UpdateActivityDetail",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Update Activity Detail",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="id", type="string", example="1"),
     *              @SWG\Property(property="tree_code", type="string", example="T0001"),
     *              @SWG\Property(property="amount", type="string", example="200"),
     *              @SWG\Property(property="detail_date", type="string", example="2021-02-20"),
     *              @SWG\Property(property="tree_status", type="string", example="Nullable"),
     *              @SWG\Property(property="growth_percentage", type="integer", example="Nullable"),
     *              @SWG\Property(property="diameter", type="integer", example="Nullable"),
     *              @SWG\Property(property="high", type="integer", example="Nullable"),
     *              @SWG\Property(property="polybags", type="boolean", example="Nullable"),
     *              @SWG\Property(property="list_of_things", type="string", example="List Of Things")
     *          ),
     *      )
     * )
     *
     */    
    public function UpdateActivityDetail(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'id' => 'required|max:255',
                'tree_code' => 'required|max:255',
                'amount' => 'required|max:255',
                'detail_date' => 'required|max:255',
                'list_of_things' => 'required|max:255'
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }       
    
            ActivityDetail::where('id', '=', $request->id)
            ->update([
                'tree_code' => $request->tree_code,
                'amount' => $request->amount,
                'detail_date' => $request->detail_date,

                'tree_status' => $this->ReplaceNull($request->tree_status, 'string'),
                'growth_percentage' => $this->ReplaceNull($request->growth_percentage, 'int'),
                'diameter' => $this->ReplaceNull($request->diameter, 'int'),
                'high' => $this->ReplaceNull($request->high, 'int'),
                'polybags' => $this->ReplaceNull($request->polybags, 'int'),
                
                'list_of_things' => $request->list_of_things,

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
     *   path="/api/DeleteActivityDetail",
	 *   tags={"Activity"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Delete Activity Detail",
     *   operationId="DeleteActivityDetail",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Delete Activity Detail",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="id", type="enum", example="1")
     *          ),
     *      )
     * )
     *
     */    
    public function DeleteActivityDetail(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'id' => 'required|max:255'
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors()->first(), $validator->errors()->first());
                return response()->json($rslt, 400);
            }     
    
            DB::table('activity_details')->where('id', $request->id)->delete();

            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200);
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }
}
