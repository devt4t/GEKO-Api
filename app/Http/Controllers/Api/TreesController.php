<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DB;
use Carbon\Carbon;
use App\Trees;
use App\TreeLocation;

class TreesController extends Controller
{
    /**
     * @SWG\Get(
     *   path="/api/GetTreesAll",
     *   tags={"Trees"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Trees AlL",
     *   operationId="GetTreesAll",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="tree_name",in="query", type="string"),
     * )
     */
    public function GetTreesAll(Request $request){
        $gettreename = $request->tree_name;
        if($gettreename){$tree_name='%'.$gettreename.'%';}
        else{$tree_name='%%';}
        try{
            $GetTrees = Trees::select('id', 'tree_code', 'tree_name', 'scientific_name', 'common_name', 'english_name','tree_category', 'created_at')->where('tree_name', 'Like', $tree_name)->orderBy('tree_name', 'ASC')->get();
            if(count($GetTrees)!=0){
                $count = Trees::where('tree_name', 'Like', $tree_name)->count();
                $data = ['count'=>$count, 'data'=>$GetTrees];
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
     *   path="/api/GetTreesLocation",
     *   tags={"Trees"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Trees Location",
     *   operationId="GetTreesLocation",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="mu_no",in="query", type="string"),
     * )
     */
    public function GetTreesLocation(Request $request){
        $getmu = $request->mu_no;
        if($getmu){$mu_no='%'.$getmu.'%';}
        else{$mu_no='%%';}
        try{
            $GetTrees = DB::table('tree_locations')->select('tree_locations.id', 'tree_locations.tree_code', 'tree_locations.tree_name', 'tree_locations.category', 'tree_locations.mu_no', 'tree_locations.region', 'tree_locations.planting_year', 'managementunits.name as mu_name')
                ->leftjoin('trees', 'trees.tree_code', '=', 'tree_locations.tree_code')
                ->leftjoin('managementunits', 'managementunits.mu_no', '=', 'tree_locations.mu_no');
            if ($getmu) {
                $GetTrees = $GetTrees->where('tree_locations.mu_no', '=', $getmu);
            }
            
            $GetTrees = $GetTrees->get();
                
            //$GetTrees = TreeLocations::select('id', 'tree_code', 'mu_no', 'region', 'planting_year', 'english_name','tree_category', 'created_at')->where('tree_name', 'Like', $tree_name)->orderBy('tree_name', 'ASC')->get();
            if(count($GetTrees)!=0){
                $count = TreeLocation::where('mu_no', 'Like', $mu_no)->count();
                $data = ['count'=>$count, 'data'=>$GetTrees];
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
    
    public function AdjustTreesLocation(Request $req) {
        $validator = Validator::make($req->all(), [
                'mu_no' => 'required|exists:managementunits,mu_no',
            ]);
        if($validator->fails()){
            $rslt =  $this->ResultReturn(400, $validator->errors(), $validator->errors());
            return response()->json($rslt, 400);
        } else {
            $mu = DB::table('managementunits')->where('mu_no', $req->mu_no)->first();
        }
        
        // delete tree locations
        $delete = TreeLocation::where('mu_no', $mu->mu_no);
        $deleteCount = $delete->count();
        $delete->delete();
        
        $KAYU = $req->KAYU ?? [];
        $MPTS = $req->MPTS ?? [];
        $CROPS = $req->CROPS ?? [];
        $count = [
            'KAYU' => 0,
            'MPTS' => 0,
            'CROPS' => 0,
        ];
        
        foreach ($KAYU as $treeCodeKAYU) {
            $getTreeKAYU = Trees::where('tree_code', $treeCodeKAYU)->first();
            TreeLocation::create([
                'tree_code' => $treeCodeKAYU,
                'tree_name' => strtoupper($getTreeKAYU->tree_name) ?? '',
                'category' => 'KAYU',
                'mu_no' => $mu->mu_no,
                'region' => $mu->name,
                'planting_year' => 2022
            ]);
            $count['KAYU'] += 1;
        }
        
        foreach ($MPTS as $treeCodeMPTS) {
            $getTreeMPTS = Trees::where('tree_code', $treeCodeMPTS)->first();
            TreeLocation::create([
                'tree_code' => $treeCodeMPTS,
                'tree_name' => strtoupper($getTreeMPTS->tree_name) ?? '',
                'category' => 'MPTS',
                'mu_no' => $mu->mu_no,
                'region' => $mu->name,
                'planting_year' => 2022
            ]);
            $count['MPTS'] += 1;
        }
        
        foreach ($CROPS as $treeCodeCROPS) {
            $getTreeCROPS = Trees::where('tree_code', $treeCodeCROPS)->first();
            TreeLocation::create([
                'tree_code' => $treeCodeCROPS,
                'tree_name' => strtoupper($getTreeCROPS->tree_name) ?? '',
                'category' => 'CROPS',
                'mu_no' => $mu->mu_no,
                'region' => $mu->name,
                'planting_year' => 2022
            ]);
            $count['CROPS'] += 1;
        }
        
        $rslt =  $this->ResultReturn(200, 'success', [
            'message' => 'success',
            'mu_name' => $mu->name,
            'deleted' => $deleteCount,
            'created' => $count
        ]);
        return response()->json($rslt, 200);
    }

    /**
     * @SWG\Get(
     *   path="/api/GetTrees",
     *   tags={"Trees"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Trees",
     *   operationId="GetTrees",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="tree_name",in="query", type="string"),
     *      @SWG\Parameter(name="limit",in="query", type="integer"),
     *      @SWG\Parameter(name="offset",in="query", type="integer"),
     * )
     */
    public function GetTrees(Request $request){
        $limit = $this->limitcheck($request->limit);
        $offset =  $this->offsetcheck($limit, $request->offset);
        $gettreename = $request->tree_name;
        if($gettreename){$tree_name='%'.$gettreename.'%';}
        else{$tree_name='%%';}
        try{
            $GetTrees = Trees::select('id', 'tree_code', 'tree_name', 'scientific_name', 'english_name','tree_category', 'created_at')->where('tree_name', 'Like', $tree_name)->orderBy('tree_name', 'ASC')->limit($limit)->offset($offset)->get();
            if(count($GetTrees)!=0){
                $count = Trees::count();
                $data = ['count'=>$count, 'data'=>$GetTrees];
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
     *   path="/api/GetTreesDetail",
     *   tags={"Trees"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Get Trees Detail",
     *   operationId="GetTreesDetail",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=500, description="internal server error"),
     *      @SWG\Parameter(name="id",in="query", type="string")
     * )
     */
    public function GetTreesDetail(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer|max:255'
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors(), $validator->errors());
                return response()->json($rslt, 400);
            }
            $GetTreesDetail = Trees::where('id', '=', $request->id)
            ->first();
            if($GetTreesDetail){
                $rslt =  $this->ResultReturn(200, 'success', $GetTreesDetail);
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
     *   path="/api/AddTrees",
	 *   tags={"Trees"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Add Trees",
     *   operationId="AddTrees",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Add Trees",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="tree_name", type="string", example="Mangga"),
     *              @SWG\Property(property="scientific_name", type="string", example="Mangifera indica"),
     *              @SWG\Property(property="common_name", type="string", example="Spanish Cherry"),
     *              @SWG\Property(property="short_information", type="string", example="Nullable"),
     *              @SWG\Property(property="tree_category", type="string", example="Pohon"),
     *              @SWG\Property(property="english_name", type="string", example="Nullable"),
     *              @SWG\Property(property="description", type="string", example="Nullable"),
     *              @SWG\Property(property="product_list", type="string", example="Nullable"),
     *              @SWG\Property(property="estimate_income", type="string", example="Nullable"),
     *              @SWG\Property(property="co2_capture", type="integer", example=0),
     *              @SWG\Property(property="photo1", type="string", example="Nullable"),
     *              @SWG\Property(property="photo2", type="string", example="Nullable")
     *          ),
     *      )
     * )
     *
     */
    public function AddTrees(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'tree_name' => 'required|string|max:255',
                'scientific_name' => 'required|string|max:255',
                'common_name' => 'required|string|max:255',
                'tree_category' => 'required|string|max:255'
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors(), $validator->errors());
                return response()->json($rslt, 400);
            }

            $getLastIdTrees = Trees::orderBy('tree_code','desc')->first(); 
            // var_dump($$getLastIdTrees->tree_code);
            if($getLastIdTrees){
                $tree_code = 'T'.str_pad(((int)substr($getLastIdTrees->tree_code,-4) + 1), 4, '0', STR_PAD_LEFT);
            }else{
                $tree_code = 'T0001';
            }

            
    
            Trees::create([
                'tree_code' => $tree_code,
                'tree_name' => $request->tree_name,
                'scientific_name' => $request->scientific_name,
                'common_name' => $request->common_name,
                'tree_category' => $request->tree_category,

                'english_name' => $this->ReplaceNull($request->english_name, 'string'),
                'short_information' => $this->ReplaceNull($request->short_information, 'string'),
                'description' => $this->ReplaceNull($request->description, 'string'),
                'product_list' => $this->ReplaceNull($request->product_list, 'string'),
                'estimate_income' => $this->ReplaceNull($request->estimate_income, 'string'),
                'co2_capture' => $this->ReplaceNull($request->co2_capture, 'int'),
                'photo1' => $this->ReplaceNull($request->photo1, 'string'),
                'photo2' => $this->ReplaceNull($request->photo2, 'string'),

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
     *   path="/api/UpdateTrees",
	 *   tags={"Trees"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Update Trees",
     *   operationId="UpdateTrees",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Update Trees",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="id", type="integer", example=1),
     *              @SWG\Property(property="tree_name", type="string", example="Mangga"),
     *              @SWG\Property(property="scientific_name", type="string", example="Mangifera indica"),
     *              @SWG\Property(property="common_name", type="string", example="Spanish Cherry"),
     *              @SWG\Property(property="short_information", type="string", example="Nullable"),
     *              @SWG\Property(property="tree_category", type="string", example="Pohon"),
     *              @SWG\Property(property="english_name", type="string", example="Nullable"),
     *              @SWG\Property(property="description", type="string", example="Nullable"),
     *              @SWG\Property(property="product_list", type="string", example="Nullable"),
     *              @SWG\Property(property="estimate_income", type="string", example="Nullable"),
     *              @SWG\Property(property="co2_capture", type="integer", example=0),
     *              @SWG\Property(property="photo1", type="string", example="Nullable"),
     *              @SWG\Property(property="photo2", type="string", example="Nullable")
     *          ),
     *      )
     * )
     *
     */
    public function UpdateTrees(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'id' => 'required|max:255',
                'tree_name' => 'required|string|max:255',
                'scientific_name' => 'required|string|max:255',
                'common_name' => 'required|string|max:255',
                'tree_category' => 'required|string|max:255'
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors(), $validator->errors());
                return response()->json($rslt, 400);
            }
    
            Trees::where('id', '=', $request->id)
            ->update([
                'tree_name' => $request->tree_name,
                'scientific_name' => $request->scientific_name,
                'common_name' => $request->common_name,
                'tree_category' => $request->tree_category,

                'english_name' => $this->ReplaceNull($request->english_name, 'string'),
                'short_information' => $this->ReplaceNull($request->short_information, 'string'),
                'description' => $this->ReplaceNull($request->description, 'string'),
                'product_list' => $this->ReplaceNull($request->product_list, 'string'),
                'estimate_income' => $this->ReplaceNull($request->estimate_income, 'string'),
                'co2_capture' => $this->ReplaceNull($request->co2_capture, 'int'),
                'photo1' => $this->ReplaceNull($request->photo1, 'string'),
                'photo2' => $this->ReplaceNull($request->photo2, 'string'),
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
     *   path="/api/DeleteTrees",
	 *   tags={"Trees"},
     *   security={
	 *     {"apiAuth": {}},
	 *   },
     *   summary="Delete Trees",
     *   operationId="DeleteTrees",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=401, description="Unauthenticated"),
     *   @SWG\Response(response=404, description="Not Found"),
     *   @SWG\Response(response=500, description="internal server error"),
	 *		@SWG\Parameter(
     *          name="Parameters",
     *          in="body",
	 *			description="Delete Trees",
     *          required=true, 
     *          type="string",
	 *   		@SWG\Schema(
     *              @SWG\Property(property="id", type="integer", example=1)
     *          ),
     *      )
     * )
     *
     */
    public function DeleteTrees(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer|max:255'
            ]);
    
            if($validator->fails()){
                $rslt =  $this->ResultReturn(400, $validator->errors(), $validator->errors());
                return response()->json($rslt, 400);
            }
    
            DB::table('trees')->where('id', $request->id)->delete();
            $rslt =  $this->ResultReturn(200, 'success', 'success');
            return response()->json($rslt, 200);
        }catch (\Exception $ex){
            return response()->json($ex);
        }
    }
}
