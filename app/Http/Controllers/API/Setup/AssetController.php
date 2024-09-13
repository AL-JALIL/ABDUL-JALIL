<?php

namespace App\Http\Controllers\API\Setup;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Asset;
use Illuminate\Support\Str;
use Exception;
use Validator;
use DB;
class AssetController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:Setup Modules|Create Asset|Create Asset|Update Asset|Update Asset|Delete Asset|View Asset', ['only' => ['index','create','store','update','destroy']]);
    }

    /**
     * @OA\Get(
     *     path="/api/assets",
     *     summary="Get a list of assets",
     *     tags={"assets"},
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
     *                     @OA\Property(property="asset_id", type="integer", example=1),
     *                     @OA\Property(property="asset_name", type="string", example="Laptop"),
     *                     @OA\Property(property="serial_number", type="string", example="SN123456"),
     *                     @OA\Property(property="code", type="string", example="LAP-001"),
     *                     @OA\Property(property="created_by", type="integer", example=1),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-08-28 11:30:25"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-08-28 11:30:25")
     *                 )
     *             ),
     *             @OA\Property(property="statusCode", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized"),
     *             @OA\Property(property="statusCode", type="integer", example=401)
     *         )
     *     )
     * )
     */

    public function index()
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('View Asset'))
        {
            $asset =DB::table('assets')->get();

            $respose =[
                'data' => $asset,
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
     *     path="/api/assets",
     *     summary="Create a new asset",
     *     tags={"assets"},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="asset_name", type="string", example="Laptop"),
     *                 @OA\Property(property="serial_number", type="string", example="SN123456"),
     *                 @OA\Property(property="code", type="string", example="LAP-001")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Asset created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Asset Inserted Successfully"),
     *             @OA\Property(property="statusCode", type="integer", example=201)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Identification Already Exists"),
     *             @OA\Property(property="statusCode", type="integer", example=400)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="unAuthenticated"),
     *             @OA\Property(property="statusCode", type="integer", example=401)
     *         )
     *     )
     * )
     */

    public function store(Request $request)
    {
    if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Create Asset'))
        {
           
            
            $user_id = auth()->user()->id;
    
            $check_value = DB::select("SELECT serial_number FROM assets WHERE LOWER(serial_number) = ?", [strtolower($request->serial_number)]);

            if(sizeof($check_value) != 0)
            {
                $respose =[
                    'message' =>'Identification  Alraedy Exists',
                    'statusCode'=> 400
                ];
    
                return response()->json($respose);       
            }

            try{
                $asset = Asset::create([ 
                    'asset_name' => $request->asset_name,
                    'serial_number' => $request->serial_number,
                    'code' => $request->code,
                    'created_by' => $user_id
                ]);
        
                $respose =[
                    'message' =>'Asset Inserted Successfully',
                    'statusCode'=> 201
                ];
        
                return response()->json($respose);
            }
            catch (Exception $e)
            {
                return response()
                    ->json(['message' => $e->getMessage(),'statusCode'=> 401]);
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
     *     tags={"assets"},
     *     @OA\Parameter(
     *         name="asset_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="asset_id", type="integer", example=1),
     *                 @OA\Property(property="asset_name", type="string", example="Laptop"),
     *                 @OA\Property(property="serial_number", type="string", example="SN123456"),
     *                 @OA\Property(property="code", type="string", example="LAP-001"),
     *                 @OA\Property(property="created_by", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-08-28 11:30:25"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-08-28 11:30:25")
     *             ),
     *             @OA\Property(property="statusCode", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No asset Found"),
     *             @OA\Property(property="statusCode", type="integer", example=400)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="unAuthenticated"),
     *             @OA\Property(property="statusCode", type="integer", example=401)
     *         )
     *     )
     * )
     */



    // get specific asset
    public function show(string $asset_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('View Asset'))
        {
            $asset = DB::table('assets')
                                ->select('assets.*')
                                ->where('assets.asset_id', '=',$asset_id)
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
                ->json(['message' => 'No asset Found','statusCode'=> 400]);
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
     *     summary="Update an asset",
     *     tags={"assets"},
     *     @OA\Parameter(
     *         name="asset_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="asset_name", type="string", example="Updated Laptop"),
     *                 @OA\Property(property="serial_number", type="string", example="SN654321"),
     *                 @OA\Property(property="code", type="string", example="LAP-002")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Asset updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Asset Updated Successfully"),
     *             @OA\Property(property="statusCode", type="integer", example=201)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="asset Name Already Exists"),
     *             @OA\Property(property="statusCode", type="integer", example=400)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="unAuthenticated"),
     *             @OA\Property(property="statusCode", type="integer", example=401)
     *         )
     *     )
     * )
     */



    /// update asset
    public function update(Request $request, string $asset_id)
    {
       
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Update Asset'))
        {

            $check_value = DB::select("SELECT asset_name FROM assets WHERE LOWER(asset_name) = LOWER('$request->asset_name') and asset_id != $asset_id");

            if(sizeof($check_value) != 0)
            {
                $respose =[
                    'message' =>'asset Name Alraedy Exists',
                    'statusCode'=> 400
                ];
    
                return response()->json($respose);       
            }
            

            $user_id = auth()->user()->id;
            try{
                $asset = Asset::find($asset_id);
                $asset->asset_name = $request->asset_name;
                $asset->serial_number  = $request->serial_number;
                $asset->code  = $request->code;
                $asset->created_by = $user_id;
                $asset->update();

                $respose =[
                    'message' =>'Asset Updated Successfully',
                    'statusCode'=> 201
                ];
                return response()->json($respose); 
            }
            catch (Exception $e)
            {
                return response()
                    ->json(['message' => $e->getMessage(),'statusCode'=> 401]);
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
     *     summary="Delete an asset",
     *     tags={"assets"},
     *     @OA\Parameter(
     *         name="asset_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Asset deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Asset Blocked Successfully"),
     *             @OA\Property(property="statusCode", type="integer", example=201)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="unAuthenticated"),
     *             @OA\Property(property="statusCode", type="integer", example=401)
     *         )
     *     )
     * )
     */

    public function destroy(string $asset_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Delete Asset'))
        {
            $delete = Asset::find($asset_id);
            if ($delete != null) {
                $delete->delete();
                
                $respose =[
                    'message'=> 'Condition Blocked Successfuly',
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
