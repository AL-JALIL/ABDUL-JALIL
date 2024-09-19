<?php

namespace App\Http\Controllers\API\Setup;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Assets;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\AssetImport;
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
    *                     @OA\Property(property="asset_id", type="integer"),
    *                     @OA\Property(property="asset_name", type="string"),
    *                     @OA\Property(property="uuid", type="string"),
    *                     @OA\Property(property="serial_number", type="string"),
    *                     @OA\Property(property="code", type="string"),
    *                     @OA\Property(property="created_by", type="integer"),
    *                     @OA\Property(property="first_name", type="string"),
    *                     @OA\Property(property="middle_name", type="string"),
    *                     @OA\Property(property="last_name", type="string"),
    *                     @OA\Property(property="created_at", type="string", format="date-time"),
    *                     @OA\Property(property="deleted_at", type="string", format="date-time"),
    *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
     *             ),
     *             @OA\Property(property="statusCode", type="integer", example=200)
    *         )
    *     )
    * )
    */
    public function index()
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('View Asset'))
        {
            $asset = DB::table('assets')
                            ->join('users', 'users.id', '=', 'assets.created_by')
                            ->select('assets.*','users.first_name','users.middle_name','users.last_name',)
                            ->get();

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
     *     summary="Store a new assets",
     *     tags={"asset"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="asset_name", type="string"),
     *             @OA\Property(property="serial_number", type="string"),
     *             @OA\Property(property="code", type="string")
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
        $request->validate([
            'asset_name' => 'required',
            'serial_number' => 'required'
        ]);

        $auto_id = random_int(100000, 999999).time();
        $user_id = auth()->user()->id;
        
        if($data->fails()){
            return response()->json($data->errors());       
        }

        if((auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL'))  && $request->upload_excel)
        {
            $data = Validator::make($request->all(),[
                'upload_excel' => 'mimes:xls,xlsx,csv'
            ]);

            if($data->fails()){
                return response()->json($data->errors());       
            }
            
            try{
                $path = $request->file('upload_excel')->getRealPath();
                $data = Excel::import(new AssetImport,request()->file('upload_excel'));
                $respose =[
                    'message'=> 'Assert inserted successfully',
                    'statusCode'=> 201
                ];
                return response()->json($respose);
            }
            catch (Exception $e)
            {
                return response()
                    ->json(['message' => $e->failures(),'statusCode'=> 401]);
            } 

        }
        else if(auth()->user()->can('Create Asset'))
        {
            $check_value = DB::select("SELECT serial_number FROM assets WHERE LOWER(serial_number) = ?", [strtolower($request->serial_number)]);

            if(sizeof($check_value) != 0)
            {
                $respose =[
                    'message' =>'Assert allraedy exists',
                    'statusCode'=> 400
                ];
    
                return response()->json($respose);       
            }

            try{
                $asset = Assets::create([ 
                    'uuid' => Str::uuid(),
                    'asset_name' => $request->asset_name,
                    'serial_number' => $request->serial_number,
                    'code' => $auto_id,
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
     *     summary="Get a specific department",
     *     tags={"asset"},
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
    *             @OA\Property(
    *                 property="data",
    *                 type="array",
    *                 @OA\Items(
    *                     type="object",
    *                     @OA\Property(property="asset_name", type="string"),
    *                     @OA\Property(property="uuid", type="string"),
    *                     @OA\Property(property="serial_number", type="string"),
    *                     @OA\Property(property="code", type="string"),
    *                     @OA\Property(property="created_by", type="integer"),
    *                     @OA\Property(property="first_name", type="string"),
    *                     @OA\Property(property="middle_name", type="string"),
    *                     @OA\Property(property="last_name", type="string"),
    *                     @OA\Property(property="created_at", type="string", format="date-time"),
    *                     @OA\Property(property="deleted_at", type="string", format="date-time"),
    *                     @OA\Property(property="updated_at", type="string", format="date-time")
    *                 )
    *             ),
    *             @OA\Property(property="statusCode", type="integer", example=200)
    *         )
    *     )
    * )
    */
    public function show(string $asset_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('View Asset'))
        {
            $asset = DB::table('assets')
                        ->join('users', 'users.id', '=', 'assets.created_by')
                        ->select('assets.*','users.first_name','users.middle_name','users.last_name',)
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
     *     summary="Update an existing assets",
     *     tags={"asset_id"},
     *     @OA\Parameter(
     *         name="asset_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Department ID"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *                 @OA\Property(property="asset_name", type="string", example="Updated Laptop"),
     *                 @OA\Property(property="serial_number", type="string", example="SN654321"),
     *                 @OA\Property(property="code", type="string", example="LAP-002")
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
                $asset = Assets::find($asset_id);
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
