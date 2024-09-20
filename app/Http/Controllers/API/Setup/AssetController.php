<?php

namespace App\Http\Controllers\API\Setup;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\Setup\GeneralController;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\AssetImport;
use App\Models\Assets;
use Illuminate\Support\Str;
use Exception;
use Validator;
use DB;


class AssetController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:Setup Modules|Create Asset|Update Asset|Delete Asset', ['only' => ['index','store','update','destroy']]);

    }

    private function getGrandChildren($parentId)
    {
        $children = DB::table('asset')
                    ->select('asset*')
                    ->where('parent_id', $parentId)
                    ->get();

        return $children;
    }

    private function getChildren($parentId)
    {
        $children = DB::table('asset')
                    ->select('asset.*')
                    ->where('parent_id', $parentId)
                    ->whereNull('deleted_at')
                    ->get();
                    $grandChildrenAsset = [];
                    foreach ($children as $parent) {
                        $parent->grandChildren = $this->getGrandChildren($parent->asset_id);
                        $grandChildrenAsset[] = $parent;
                    }
            
                    return $grandChildrenAsset;
                }

                 /**
     * @OA\Get(
     *     path="/api/asset",
     *     summary="Get a list of asset",
     *     tags={"asset"},
    *     @OA\Response(
    *         response=200,
    *         description="Successful operation",
    *         @OA\Header(
    *             header="Cache-Control",
    *             description="Cache control header",
    *             @OA\Schema(type="string", example="no-cache, private")
    *         ),
    *         @OA\Header(
    *             header="Content-Type",
    *             description="Content type header",
    *             @OA\Schema(type="string", example="application/json; charset=UTF-8")
    *         ),
    *         @OA\JsonContent(
    *             type="object",
    *             @OA\Property(
    *                 property="data",
    *                 type="array",
    *                 @OA\Items(
    *                     type="object",
    *                     @OA\Property(property="asset_id", type="integer", example=2),
    *                     @OA\Property(property="asset_name", type="string", example="Human Resource"),
    *                     @OA\Property(property="parent_id", type="string", example="1"),
    *                     @OA\Property(property="created_by", type="integer", example=1),
    *                     @OA\Property(property="first_name", type="string", example="Abdul_jalil"),
    *                     @OA\Property(property="middle_name", type="string", example="Ameir"),
    *                     @OA\Property(property="last_name", type="string", example="Ali"),
    *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-08-28 11:30:25"),
    *                     @OA\Property(property="deleted_at", type="string", format="date-time", example="2024-08-28 11:30:25"),
    *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-08-28 11:30:25")
    *                 )
    *             ),
    *             @OA\Property(property="statusCode", type="integer", example=200)
    *         )
    *     )
    * )
    */
    public function index()
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Modules'))
        {
            $asset = DB::table('asset')
                                ->join('users', 'users.id', '=', 'asset.created_by')
                                ->select('asset.*','users.first_name','users.middle_name','users.last_name','users.id')
                                ->where("parent_id","1001")
                                ->get();

             // Format parent and children recursively
        $formattedAsset = [];
        foreach ($assets as $parent) {
            $parent->children = $this->getChildren($parent->asset_id);
            $formattedAssets[] = $parent;
        }



            $respose =[
                'data' => $formattedAssets,
                'statusCode'=> 200
            ];

            return response()->json($respose);
        }
        else{
            return response()
                ->json(['message' => 'Unauthorized','statusCode'=> 401]);
        }
    }

 /**
     * @OA\Post(
     *     path="/api/asset",
     *     summary="Store a new assets",
     *     tags={"assets"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="assets_name", type="string"),
     *             @OA\Property(property="parent_id", type="string"),
     *             @OA\Property(property="upload_excel", type="string", format="binary")
     *         )
     *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Successful operation",
    *         @OA\Header(
    *             header="Cache-Control",
    *             description="Cache control header",
    *             @OA\Schema(type="string", example="no-cache, private")
    *         ),
    *         @OA\Header(
    *             header="Content-Type",
    *             description="Content type header",
    *             @OA\Schema(type="string", example="application/json; charset=UTF-8")
    *         ),
    *         @OA\JsonContent(
    *             type="object",
    *             @OA\Property(property="message", type="string"),
    *             @OA\Property(property="statusCode", type="integer")
    *         )
    *     )
    * )
    */
    public function store(Request $request)
    {
        $auto_id = random_int(10000, 99999).time();
        if((auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL')) && $request->upload_excel){

            $data = Validator::make($request->all(),[
                'upload_excel' => 'mimes:xls,xlsx,csv'
            ]);

            if($data->fails()){
                return response()->json($data->errors());
            }
            try{
                $path = $request->file('upload_excel')->getRealPath();
                $data = Excel::import(new AssetsImport,request()->file('upload_excel'));

                $respose =[
                    'message'=> 'Assets Inserted Successfully',
                    'statusCode'=> 201
                ];
                return response()->json($respose);
            }
            catch (Exception $e)
            {
                return response()
                    ->json(['message' => $e->getMessage(),'statusCode'=> 500]);
            }

        }

        else if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Create assets'))
        {
            $user_id = auth()->user()->id;

            $check_value = DB::select("SELECT assets_name FROM assets WHERE LOWER(assets_name) = LOWER('$request->assets_name')");

            if(sizeof($check_value) != 0)
            {
                $respose =[
                    'message' =>'assets Name Alraedy Exists',
                    'statusCode'=> 400
                ];

                return response()->json($respose);
            }

            try{
                $Assets = Assets::create([
                    'asset_id' => $request->asset_id,
                    'uuid' => Str::uuid(),
                    'assetname' => $request->asset_name,
                    'parent_id' =>  $request->parent_id,
                    'created_by' => $user_id
                ]);

                $respose =[
                    'message' =>'Assets Inserted Successfully',
                    'statusCode'=> 201
                ];

                return response()->json($respose);
            }
            catch (Exception $e)
            {
                return response()
                    ->json(['message' => $e->getMessage(),'statusCode'=> 500]);
            }
        }
        else{
            return response()
                ->json(['message' => 'unAuthenticated','statusCode'=> 401]);
        }
    }

     /**
     * @OA\Get(
     *     path="/api/assets/{asset_id}",
     *     summary="Get a specific asset",
     *     tags={"asset"},
     *     @OA\Parameter(
     *         name="assets_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Successful operation",
    *         @OA\Header(
    *             header="Cache-Control",
    *             description="Cache control header",
    *             @OA\Schema(type="string", example="no-cache, private")
    *         ),
    *         @OA\Header(
    *             header="Content-Type",
    *             description="Content type header",
    *             @OA\Schema(type="string", example="application/json; charset=UTF-8")
    *         ),
    *         @OA\JsonContent(
    *             type="object",
    *             @OA\Property(
    *                 property="data",
    *                 type="array",
    *                 @OA\Items(
    *                     type="object",
    *                     @OA\Property(property="asset_id", type="integer", example=2),
    *                     @OA\Property(property="asset_name", type="string", example="Human Resource"),
    *                     @OA\Property(property="parent_id", type="string", example="1"),
    *                     @OA\Property(property="created_by", type="integer", example=1),
    *                     @OA\Property(property="first_name", type="string", example="Abdul_jalil"),
    *                     @OA\Property(property="middle_name", type="string", example="Ameir"),
    *                     @OA\Property(property="last_name", type="string", example="Ali"),
    *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-08-28 11:30:25"),
    *                     @OA\Property(property="deleted_at", type="string", format="date-time", example="2024-08-28 11:30:25"),
    *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-08-28 11:30:25")
    *                 )
    *             ),
    *             @OA\Property(property="statusCode", type="integer", example=200)
    *         )
    *     )
    * )
    */

    public function show(string $asset_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Modules'))
        {
            $asset = DB::table('assets')
                                ->select('assets.*')
                                ->where('assets.asset_id', '=',$asset_id)
                                ->whereNull('asset.deleted_at')
                                ->get();

            if (sizeof($asset) > 0)
            {
                $respose =[
                    'data' => $asset,
                    'statusCode'=> 200
                ];

                return response()->json($respose);

            }else{
                return response()
                ->json(['message' => 'No Asset Found','statusCode'=> 400]);
            }

        }
        else{
            return response()
                ->json(['message' => 'unAuthenticated','statusCode'=> 401]);
        }
    }

 /**
     * @OA\Put(
     *     path="/api/assets/{asset_id}",
     *     summary="Update a asset",
     *     tags={"assets"},
     *     @OA\Parameter(
     *         name="asset_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="asset_name", type="string"),
     *             @OA\Property(property="parent_id", type="string"),
     *             @OA\Property(property="label", type="string")
     *         )
     *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Successful operation",
    *         @OA\Header(
    *             header="Cache-Control",
    *             description="Cache control header",
    *             @OA\Schema(type="string", example="no-cache, private")
    *         ),
    *         @OA\Header(
    *             header="Content-Type",
    *             description="Content type header",
    *             @OA\Schema(type="string", example="application/json; charset=UTF-8")
    *         ),
    *         @OA\JsonContent(
    *             type="object",
    *             @OA\Property(property="message", type="string"),
    *             @OA\Property(property="statusCode", type="integer")
    *         )
    *     )
    * )
    */

    public function update(Request $request, string $asset_id)
    {
        $check_value = DB::select("SELECT asset_name FROM assets WHERE LOWER(asset_name) = LOWER('$request->asset_name') and asset_id != '$asset_id'");

        if(sizeof($check_value) != 0)
        {
            $respose =[
                'message' =>'Asset Name Alraedy Exists',
                'statusCode'=> 400
            ];

            return response()->json($respose);
        }

        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Update Asset'))
        {
            $user_id = auth()->user()->id;
            try{
                $Assets = Assets::find($asset_id);
                $Assets->asset_name  = $request->asset_name;
                $Assets->created_by = $user_id;
                $Assets->update();

                $respose =[
                    'message' =>'Asset Updated Successfully',
                    'statusCode'=> 201
                ];
                return response()->json($respose);
            }
            catch (Exception $e)
            {
                return response()
                    ->json(['message' => $e->getMessage(),'statusCode'=> 500]);
            }
        }
        else{
            return response()
                ->json(['message' => 'unAuthenticated','statusCode'=> 401]);
        }
    }

     /**
     * @OA\Delete(
     *     path="/api/assets/{asset_id}",
     *     summary="Delete a asset",
     *     tags={"assets"},
     *     @OA\Parameter(
     *         name="asset_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Successful operation",
    *         @OA\Header(
    *             header="Cache-Control",
    *             description="Cache control header",
    *             @OA\Schema(type="string", example="no-cache, private")
    *         ),
    *         @OA\Header(
    *             header="Content-Type",
    *             description="Content type header",
    *             @OA\Schema(type="string", example="application/json; charset=UTF-8")
    *         ),
    *         @OA\JsonContent(
    *             type="object",
    *             @OA\Property(property="message", type="string"),
    *             @OA\Property(property="statusCode", type="integer")
    *         )
    *     )
    * )
    */
    public function destroy(string $asset_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Delete Asset'))
        {
            $delete = Assets::find($asset_id);
            if ($delete != null) {
                $delete->delete();

                $respose =[
                    'message'=> 'Asset Blocked Successfuly',
                    'statusCode'=> 201
                ];
                return response()->json($respose);
            }
        }
        else{
            return response()
                ->json(['message' => 'unAuthenticated','statusCode'=> 401]);
        }
    }

}