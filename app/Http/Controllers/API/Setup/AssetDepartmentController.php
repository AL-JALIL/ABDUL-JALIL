<?php

namespace App\Http\Controllers\API\Setup;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AssetDepartment;
use Illuminate\Support\Str;
use Exception;
use Validator;
use DB;

class AssetDepartmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:Setup Modules|Create Asset Department|Create Asset Department|Update Asset Department|Update Asset Department|Delete Asset Department', ['only' => ['index','create','store','update','destroy']]);
    }

        /**
     * @OA\Get(
     *     path="/api/assetDepartments",
     *     summary="Get a list of asset departments",
     *     tags={"assetDepartments"},
     *     security={{"sanctum":{}}},
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
     *                     @OA\Property(property="asset_id", type="integer", example=1),
     *                     @OA\Property(property="department_id", type="integer", example=2),
     *                     @OA\Property(property="condition_id", type="integer", example=3),
     *                     @OA\Property(property="start_date", type="string", format="date-time", example="2024-09-01 10:30:45"),
     *                     @OA\Property(property="status", type="string", example="Active")
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
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Modules'))
        {
            $asset_department =DB::table('asset_departments')->get();

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
     *     path="/api/assetDepartments",
     *     summary="Create a new asset department",
     *     tags={"assetDepartments"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="asset_id", type="integer", example=1),
     *             @OA\Property(property="department_id", type="integer", example=2),
     *             @OA\Property(property="condition_id", type="integer", example=3),
     *             @OA\Property(property="registration_number", type="string", example="12345XYZ"),
     *             @OA\Property(property="status", type="string", example="Active")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Asset department created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Asset department Inserted Successfully"),
     *             @OA\Property(property="statusCode", type="integer", example=201)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Asset not found"),
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


    public function store(Request $request)
{
    if (auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Create Identification')) {
        $user_id = auth()->user()->id;

        try {
            // Validate if asset exists
            $asset_info = DB::table('assets')
                ->where('asset_id', $request->asset_id)
                ->first();

            if (!$asset_info) {
                return response()->json([
                    'message' => 'Asset not found',
                    'statusCode' => 404
                ]);
            }

            // Validate if condition exists
            $condition_info = DB::table('conditions')
                ->where('condition_id', $request->condition_id)
                ->first();

            if (!$condition_info) {
                return response()->json([
                    'message' => 'Condition not found',
                    'statusCode' => 404
                ]);
            }

            // Validate if department exists
            $department_info = DB::table('departments')
                ->where('department_id', $request->department_id)
                ->first();

            if (!$department_info) {
                return response()->json([
                    'message' => 'Department not found',
                    'statusCode' => 404
                ]);
            }

            // If all IDs are valid, proceed to insert data into asset_departments
            $asset_department = AssetDepartment::create([
                'asset_id' => $request->asset_id,
                'department_id' => $request->department_id,  // Ensure this is passed correctly from Postman
                'condition_id' => $request->condition_id,
                'start_date' => now(), // Automatically set the current date
                'registration_number' => $request->registration_number, // User provided
                'status' => $request->status,
                'created_by' => $user_id,
                
            ]);

            return response()->json([
                'message' => 'Asset department Inserted Successfully',
                'statusCode' => 201
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'statusCode' => 401]);
        }
    } else {
        return response()->json(['message' => 'unAuthenticated', 'statusCode' => 401]);
    }
}

    /**
     * @OA\Get(
     *     path="/api/assetDepartments/{id}",
     *     summary="Get a specific asset department by ID",
     *     tags={"assetDepartments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
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
     *                 type="object",
     *                 @OA\Property(property="asset_id", type="integer", example=1),
     *                 @OA\Property(property="department_id", type="integer", example=2),
     *                 @OA\Property(property="condition_id", type="integer", example=3),
     *                 @OA\Property(property="start_date", type="string", format="date-time", example="2024-09-01 10:30:45"),
     *                 @OA\Property(property="status", type="string", example="Active")
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


// get specific asset_department
public function show(string $asset_department_id)
{
    if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Modules'))
    {
        $asset_department = DB::table('asset_departments')
                            ->select('asset_departments.*')
                            ->where('asset_departments.asset_department_id', '=',$asset_department_id)
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
     *     path="/api/assetDepartments/{id}",
     *     summary="Update an existing asset department",
     *     tags={"assetDepartments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Asset Department ID"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="asset_id", type="integer", example=1),
     *             @OA\Property(property="department_id", type="integer", example=2),
     *             @OA\Property(property="condition_id", type="integer", example=3),
     *             @OA\Property(property="registration_number", type="string", example="12345XYZ"),
     *             @OA\Property(property="status", type="string", example="Active")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Asset department updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Asset department Updated Successfully"),
     *             @OA\Property(property="statusCode", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Asset department not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Asset department not found"),
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


/// update asset_department
public function update(Request $request, string $asset_department_id)
{
    if (auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Update asset_department')) {
        // Check if registration_number already exists, excluding the current record
        $check_value = DB::select(
            "SELECT registration_number FROM asset_departments WHERE LOWER(registration_number) = LOWER(?) AND asset_department_id != ?",
            [$request->registration_number, $asset_department_id]
        );

        if (sizeof($check_value) != 0) {
            return response()->json([
                'message' => 'Registration Number Already Exists',
                'statusCode' => 400
            ]);
        }

        $user_id = auth()->user()->id;
        try {
            // Find the asset_department by ID
            $asset_department = AssetDepartment::find($asset_department_id);

            if (!$asset_department) {
                return response()->json([
                    'message' => 'Asset department not found',
                    'statusCode' => 404
                ]);
            }

            // Update the asset_department fields
            $asset_department->asset_id = $request->asset_id;
            $asset_department->department_id = $request->department_id;
            $asset_department->condition_id = $request->condition_id;
            $asset_department->start_date = now();  // Automatically set the current date
            $asset_department->registration_number = $request->registration_number;
            $asset_department->status = $request->status;
            $asset_department->created_by = $user_id;
            $asset_department->save();  // Save changes

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
    } else {
        return response()->json([
            'message' => 'Unauthorized',
            'statusCode' => 401
        ]);
    }
}

    /**
     * @OA\Delete(
     *     path="/api/assetDepartments/{id}",
     *     summary="Delete an asset department",
     *     tags={"assetDepartments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Asset Department ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Asset department deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Asset Department Deleted Successfully"),
     *             @OA\Property(property="statusCode", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Asset department not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Asset department not found"),
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


public function destroy(string $asset_department_id)
{
    if (auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Delete Upload Types')) {
        $delete = AssetDepartment::find($asset_department_id);

        if ($delete != null) {
            $delete->delete();

            return response()->json([
                'message' => 'Asset Department Deleted Successfully',
                'statusCode' => 200 // 200 is more appropriate for successful deletion
            ]);
        } else {
            return response()->json([
                'message' => 'Asset Department Not Found',
                'statusCode' => 404 // 404 for not found
            ]);
        }
    } else {
        return response()->json([
            'message' => 'Unauthorized',
            'statusCode' => 401
        ]);
    }
}


}

