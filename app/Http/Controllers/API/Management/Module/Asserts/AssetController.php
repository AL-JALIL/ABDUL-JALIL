<?php

namespace App\Http\Controllers\API\Management\Module\Asserts;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AssetDepartments;
use App\Models\TransferAssets;
use Illuminate\Support\Str;
use App\Enums\Status;
use Exception;
use Validator;
use DB;

class AssertController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:View Asset Department|Create Asset Department|View Asset Department|Update Asset Department|Update Asset Department|Delete Asset Department|Create Asset Department', ['only' => ['asset_department','store_assert_department','view_assert_department','update_assert_department','destroy_assert_department','transfer_assert']]);
    }

     /**
     * @OA\Get(
     *     path="/api/assetDepartment",
     *     summary="Get a list of asset departments",
     *     tags={"assertDepartments"},
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
    *                     @OA\Property(property="asset_department_id", type="integer"),
    *                     @OA\Property(property="uuid", type="string"),
    *                     @OA\Property(property="asset_id", type="integer"),
    *                     @OA\Property(property="asset_name", type="string"),
    *                     @OA\Property(property="serial_number", type="string"),
    *                     @OA\Property(property="code", type="string"),
    *                     @OA\Property(property="registration_number", type="string"),
    *                     @OA\Property(property="department_id", type="integer"),
    *                     @OA\Property(property="department_name", type="string"),
    *                     @OA\Property(property="condition_id", type="integer"),
    *                     @OA\Property(property="condition_name", type="string"),
    *                     @OA\Property(property="start_date", type="string", format="date-time"),
    *                     @OA\Property(property="assert_status", type="string"),
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
    public function asset_department()
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('View Asset Department'))
        {
            $asset_department = DB::table('asset_departments')
                                ->join('users', 'users.id', '=', 'asset_departments.created_by')
                                ->join('departments', 'departments.department_id', '=', 'asset_departments.department_id')
                                ->join('conditions', 'conditions.condition_id', '=', 'asset_departments.condition_id')
                                ->join('assets', 'assets.asset_id', '=', 'asset_departments.asset_id')
                                ->select('asset_departments.*','users.first_name','users.middle_name','users.last_name','departments.department_name','conditions.condition_name','assets.asset_name','assets.serial_number','assets.code')
                                ->get();

            $respose =[
                'data' => $asset_department,
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
     *     path="/api/storeAssertDepartment",
     *     summary="Store a new assert department",
     *     tags={"assertDepartments"},
    *     @OA\RequestBody(
    *         required=true,
    *         @OA\JsonContent(
    *             type="object",
    *             @OA\Property(
    *                 property="asserts",
    *                 type="array",
    *                 @OA\Items(
    *                     type="object",
    *                     @OA\Property(property="assert_id", type="integer"),
    *                     @OA\Property(property="condition_id", type="integer"),
    *                     @OA\Property(property="registration_number", type="integer"),
    *                     @OA\Property(property="start_date", type="string", format="date-time"),
    *                     @OA\Property(property="department_id", type="integer")
    *                 )
    *             ),
    *             @OA\Property(
    *                 property="start_date",
    *                 type="string",
    *                 format="date"
    *             )
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
    public function store_assert_department(Request $request)
    {
        $user_id = auth()->user()->id;

        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Create Asset Department')) 
        {
            try
            {
                foreach($request->asserts as $assert)
                {
                    $asset_department = AssetDepartments::create([
                        'uuid' => Str::uuid(),
                        'asset_id' => $assert['asset_id'],
                        'department_id' => $assert['department_id'],
                        'condition_id' => $assert['condition_id'],
                        'start_date' => $request->start_date,
                        'registration_number' => $assert['registration_number'],
                        'assert_status' => $request->assert_status,
                        'created_by' => $user_id,
                        
                    ]);
                }

                return response()->json([
                    'message' => 'Asset department inserted successfully',
                    'statusCode' => 201
                ]);

            } catch (Exception $e) {
                return response()->json(['message' => $e->getMessage(), 'statusCode' => 401]);
            }
        } 
        else 
        {
            return response()->json(['message' => 'unAuthenticated', 'statusCode' => 401]);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/viewAssertDepartment/{asset_department_id}",
     *     summary="Get a specific asset department",
     *     tags={"assertDepartments"},
     *     @OA\Parameter(
     *         name="asset_department_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Asset Department ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
    *                     @OA\Property(property="asset_department_id", type="integer"),
    *                     @OA\Property(property="uuid", type="string"),
    *                     @OA\Property(property="asset_id", type="integer"),
    *                     @OA\Property(property="asset_name", type="string"),
    *                     @OA\Property(property="serial_number", type="string"),
    *                     @OA\Property(property="code", type="string"),
    *                     @OA\Property(property="registration_number", type="string"),
    *                     @OA\Property(property="department_id", type="integer"),
    *                     @OA\Property(property="department_name", type="string"),
    *                     @OA\Property(property="condition_id", type="integer"),
    *                     @OA\Property(property="condition_name", type="string"),
    *                     @OA\Property(property="start_date", type="string", format="date-time"),
    *                     @OA\Property(property="assert_status", type="string"),
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
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Asset department not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No asset_department Found"),
     *             @OA\Property(property="statusCode", type="integer", example=404)
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
    public function view_assert_department($asset_department_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('View Asset Department'))
        {
            $asset_department = DB::table('asset_departments')
                                ->join('users', 'users.id', '=', 'asset_departments.created_by')
                                ->join('departments', 'departments.department_id', '=', 'asset_departments.department_id')
                                ->join('conditions', 'conditions.condition_id', '=', 'asset_departments.condition_id')
                                ->join('assets', 'assets.asset_id', '=', 'asset_departments.asset_id')
                                ->select('asset_departments.*','users.first_name','users.middle_name','users.last_name','departments.department_name','conditions.condition_name','assets.asset_name','assets.serial_number','assets.code')
                                ->where('asset_departments.asset_department_id',$asset_department_id)
                                ->get();

            if (sizeof($asset_department) > 0) 
            {
                $respose =[
                    'data' => $asset_department,
                    'statusCode'=> 200
                ];

                return response()->json($respose);

            }else{
                return response()
                ->json(['message' => 'No asset_department Found','statusCode'=> 400]);
            }
                
        }
        else{
            return response()
                ->json(['message' => 'unAuthenticated','statusCode'=> 401]);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/updateAssertDepartment/{asset_department_id}",
     *     summary="Update a asset departments",
     *     tags={"assertDepartments"},
     *     @OA\Parameter(
     *         name="asset_department_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="asset_id", type="integer", example=1),
     *             @OA\Property(property="department_id", type="integer"),
     *             @OA\Property(property="condition_id", type="integer"),
     *             @OA\Property(property="registration_number", type="string"),
     *             @OA\Property(property="start_date", type="string", format="date-time"),
     *             @OA\Property(property="assert_status", type="string")
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
    public function update_assert_department(Request $request, string $asset_department_id)
    {
        $user_id = auth()->user()->id;
        if (auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Update Asset Department')) 
        {
            try 
            {
                $asset_department = AssetDepartments::find($asset_department_id);
                $asset_department->asset_id = $request->asset_id;
                $asset_department->department_id = $request->department_id;
                $asset_department->condition_id = $request->condition_id;
                $asset_department->start_date = $request->start_date;
                $asset_department->registration_number = $request->registration_number;
                $asset_department->assert_status = $request->assert_status;
                $asset_department->created_by = $user_id;
                $asset_department->update(); 

                return response()->json([
                    'message' => 'Asset department Updated Successfully',
                    'statusCode' => 200
                ]);

            } catch (Exception $e) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'statusCode' => 500
                ]);
            }
        } 
        else 
        {
            return response()->json([
                'message' => 'Unauthorized',
                'statusCode' => 401
            ]);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/destroyAssertDepartment/{asset_department_id}",
     *     summary="Delete a destroy assert department",
     *     tags={"assertDepartments"},
     *     @OA\Parameter(
     *         name="asset_department_id",
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
    public function destroy_assert_department(string $asset_department_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Delete Asset Department')) 
        {
            $delete = AssetDepartments::find($asset_department_id);

            if ($delete != null) {
                $delete->delete();

                return response()->json([
                    'message' => 'Asset department blocked successfully',
                    'statusCode' => 201
                ]);
            }
        } else {
            return response()->json([
                'message' => 'Unauthorized',
                'statusCode' => 401
            ]);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/transferAssert",
     *     summary="Update transfer assert",
     *     tags={"assertDepartments"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="start_date", type="string", format="date-time"),
     *             @OA\Property(property="department_id", type="integer"),
     *             @OA\Property(property="asset_id", type="array", @OA\Items(type="integer"))
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
    public function transfer_assert(Request $request)
    {
        $request->validate([
            'asset_id' => 'required',
            'department_id' => 'required',
            'start_date' => 'required'
        ]);

        if($data->fails()){
            return response()->json($data->errors());       
        }
        $user_id = auth()->user()->id;

        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Create Transfer Asset'))
        {
            try 
            {
                foreach($request->asset_id as $asset_id)
                {
                    $transfer_asset = TransferAsset::create([
                        'uuid' => Str::uuid(),
                        'department_id' => $request->department_id,
                        'registration_number' => $request->registration_number,
                        'transfer_type' => $request->transfer_type,
                        'reason' => $request->reason,
                        'created_by' => $user_id,
                    ]);


                }
                

                return response()->json([
                    'message' => 'Transfer asset created successfully',
                    'statusCode' => 201
                ]);
            } catch (Exception $e) {
                return response()->json(['message' => $e->getMessage(), 'statusCode' => 500]);
            }
        }
        else {
            return response()->json(['message' => 'Unauthorized', 'statusCode' => 401]);
        }
    }
}
