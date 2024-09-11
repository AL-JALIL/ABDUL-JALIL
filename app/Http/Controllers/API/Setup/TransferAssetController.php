<?php

namespace App\Http\Controllers\API\Setup;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TransferAsset;
use Illuminate\Support\Str;
use Exception;
use Validator;
use DB;

class TransferAssetController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:Setup Modules|Create Transfer Asset|Update Transfer Asset|Delete Transfer Asset', ['only' => ['index','store','update','destroy']]);
    }

    /**
 * @OA\Get(
 *     path="/api/transferAssets",
 *     summary="Get a list of transfer assets",
 *     tags={"transferAssets"},
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
 *                     @OA\Property(property="transfer_asset_id", type="integer", example=1),
 *                     @OA\Property(property="department_id", type="integer", example=2),
 *                     @OA\Property(property="registration_number", type="string", example="12345XYZ"),
 *                     @OA\Property(property="transfer_type", type="string", example="Permanent"),
 *                     @OA\Property(property="reason", type="string", example="Relocation")
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
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->can('View Transfer Asset'))
        {
            $transfer_assets = DB::table('transfer_assets')->get();

            $response = [
                'data' => $transfer_assets,
                'statusCode' => 200
            ];

            return response()->json($response);
        }
        else {
            return response()->json(['message' => 'Unauthorized', 'statusCode' => 401]);
        }
    }

    /**
 * @OA\Post(
 *     path="/api/transferAssets",
 *     summary="Create a new transfer asset",
 *     tags={"transferAssets"},
 *     security={{"sanctum":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="department_id", type="integer", example=2),
 *             @OA\Property(property="registration_number", type="string", example="12345XYZ"),
 *             @OA\Property(property="transfer_type", type="string", example="Permanent"),
 *             @OA\Property(property="reason", type="string", example="Relocation")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Transfer asset created successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Transfer asset created successfully"),
 *             @OA\Property(property="statusCode", type="integer", example=201)
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
        if (auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Create Transfer Asset'))
        {
            $user_id = auth()->user()->id;

            try {
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

                // Insert the transfer asset
                $transfer_asset = TransferAsset::create([
                    'department_id' => $request->department_id,
                    'registration_number' => $request->registration_number,
                    'transfer_type' => $request->transfer_type,
                    'reason' => $request->reason,
                    'created_by' => $user_id,
                ]);

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

    /**
 * @OA\Get(
 *     path="/api/transferAssets/{transfer_asset_id}",
 *     summary="Get a specific transfer asset by ID",
 *     tags={"transferAssets"},
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="transfer_asset_id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer"),
 *         description="Transfer Asset ID"
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="data", type="object"),
 *             @OA\Property(property="statusCode", type="integer", example=200)
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="No Transfer Asset Found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="No transfer_asset Found"),
 *             @OA\Property(property="statusCode", type="integer", example=400)
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
public function show(string $transfer_asset_id)
{
    if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('View Transfer Asset'))
    {
        $transfer_asset = DB::table('transfer_assets')
                            ->select('transfer_assets.*')
                            ->where('transfer_assets.transfer_asset_id', '=', $transfer_asset_id)
                            ->get();

        if (sizeof($transfer_asset) > 0) 
        {
            $response = [
                'data' => $transfer_asset,
                'statusCode' => 200
            ];

            return response()->json($response);
        } 
        else 
        {
            return response()->json(['message' => 'No transfer_asset Found', 'statusCode' => 400]);
        }
    } 
    else 
    {
        return response()->json(['message' => 'Unauthorized', 'statusCode' => 401]);
    }
}

/**
 * @OA\Put(
 *     path="/api/transferAssets/{transfer_asset_id}",
 *     summary="Update an existing transfer asset",
 *     tags={"transferAssets"},
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="transfer_asset_id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer"),
 *         description="Transfer Asset ID"
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="department_id", type="integer", example=2),
 *             @OA\Property(property="registration_number", type="string", example="12345XYZ"),
 *             @OA\Property(property="transfer_type", type="string", example="Permanent"),
 *             @OA\Property(property="reason", type="string", example="Relocation")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Transfer asset updated successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Transfer asset updated successfully"),
 *             @OA\Property(property="statusCode", type="integer", example=200)
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Registration Number Already Exists",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Registration Number Already Exists"),
 *             @OA\Property(property="statusCode", type="integer", example=400)
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Transfer asset not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Transfer asset not found"),
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


/// update transfer_asset
public function update(Request $request, string $transfer_asset_id)
{
    if (auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Update Transfer Asset')) {
        // Check if registration_number already exists, excluding the current record
        $check_value = DB::select(
            "SELECT registration_number FROM transfer_assets WHERE LOWER(registration_number) = LOWER(?) AND transfer_asset_id != ?",
            [$request->registration_number, $transfer_asset_id]
        );

        if (sizeof($check_value) != 0) {
            return response()->json([
                'message' => 'Registration Number Already Exists',
                'statusCode' => 400
            ]);
        }

        $user_id = auth()->user()->id;
        try {
            // Find the transfer_asset by ID
            $transfer_asset = TransferAsset::find($transfer_asset_id);

            if (!$transfer_asset) {
                return response()->json([
                    'message' => 'Transfer asset not found',
                    'statusCode' => 404
                ]);
            }

            // Update the transfer_asset fields
            $transfer_asset->department_id = $request->department_id;
            $transfer_asset->registration_number = $request->registration_number;
            $transfer_asset->transfer_type = $request->transfer_type;
            $transfer_asset->reason = $request->reason;
            $transfer_asset->created_by = $user_id;
            $transfer_asset->save();  // Save changes

            return response()->json([
                'message' => 'Transfer asset updated successfully',
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
 *     path="/api/transferAssets/{transfer_asset_id}",
 *     summary="Delete a transfer asset",
 *     tags={"transferAssets"},
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="transfer_asset_id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer"),
 *         description="Transfer Asset ID"
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Transfer asset deleted successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Transfer Asset Deleted Successfully"),
 *             @OA\Property(property="statusCode", type="integer", example=200)
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Transfer asset not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Transfer Asset Not Found"),
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

public function destroy(string $transfer_asset_id)
{
    if (auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Delete Transfer Asset')) {
        // Find the transfer asset by ID
        $transfer_asset = TransferAsset::find($transfer_asset_id);

        if ($transfer_asset) {
            // Soft delete or permanent delete based on your logic
            $transfer_asset->delete();

            return response()->json([
                'message' => 'Transfer Asset Deleted Successfully',
                'statusCode' => 200 // 200 indicates a successful operation
            ]);
        } else {
            return response()->json([
                'message' => 'Transfer Asset Not Found',
                'statusCode' => 404 // 404 means the asset was not found
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

