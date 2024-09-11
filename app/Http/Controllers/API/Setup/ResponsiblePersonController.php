<?php

namespace App\Http\Controllers\API\Setup;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ResponsiblePerson;
use Illuminate\Support\Str;
use Exception;
use Validator;
use DB;


class ResponsiblePersonController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:Setup Modules|Create Responsible Person|Update Responsible Person|Delete Responsible Person', ['only' => ['index','store','update','destroy']]);
    }

    /**
 * @OA\Get(
 *     path="/api/responsiblePersons",
 *     summary="Get a list of responsible persons",
 *     tags={"responsiblePersons"},
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
 *                     @OA\Property(property="responsible_person_id", type="integer", example=1),
 *                     @OA\Property(property="payroll", type="string", example="P12345"),
 *                     @OA\Property(property="registration_number", type="string", example="REG123456"),
 *                     @OA\Property(property="date", type="string", format="date", example="2024-01-01"),
 *                     @OA\Property(property="status", type="string", example="Active"),
 *                     @OA\Property(property="created_by", type="integer", example=1),
 *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T00:00:00Z"),
 *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T00:00:00Z"),
 *                     @OA\Property(property="deleted_at", type="string", format="date-time", example=null)
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
        if (auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('View Responsible Person')) {
            $responsiblePersons = DB::table('responsible_persons')->get();

            $response = [
                'data' => $responsiblePersons,
                'statusCode' => 200
            ];

            return response()->json($response);
        } else {
            return response()->json(['message' => 'Unauthorized', 'statusCode' => 401]);
        }
    }

    /**
 * @OA\Post(
 *     path="/api/responsiblePersons",
 *     summary="Create a new responsible person",
 *     tags={"responsiblePersons"},
 *     security={{"sanctum":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="payroll", type="string", example="P12345"),
 *             @OA\Property(property="registration_number", type="string", example="REG123456"),
 *             @OA\Property(property="date", type="string", format="date", example="2024-01-01"),
 *             @OA\Property(property="status", type="string", example="Active")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Responsible person created successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Responsible person inserted successfully"),
 *             @OA\Property(property="statusCode", type="integer", example=201)
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Bad request",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Registration number already exists"),
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

    public function store(Request $request)
{
    if (auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Create Responsible Person')) {
        $user_id = auth()->user()->id;

        $checkValue = DB::select("SELECT registration_number FROM responsible_persons WHERE LOWER(registration_number) = ?", [strtolower($request->registration_number)]);

        if (sizeof($checkValue) != 0) {
            $response = [
                'message' => 'Registration number already exists',
                'statusCode' => 400
            ];

            return response()->json($response);
        }

        try {
            $responsiblePerson = ResponsiblePerson::create([
                'payroll' => $request->payroll,
                'registration_number' => $request->registration_number,
                'date' => $request->date,
                'status' => $request->status,
                'created_by' => auth()->user()->id
            ]);

            $response = [
                'message' => 'Responsible person inserted successfully',
                'statusCode' => 201
            ];

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'statusCode' => 500]);
        }
    } else {
        return response()->json(['message' => 'Unauthorized', 'statusCode' => 401]);
    }
}

/**
 * @OA\Get(
 *     path="/api/responsiblePersons/{responsible_person_id}",
 *     summary="Get details of a specific responsible person",
 *     tags={"responsiblePersons"},
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="responsible_person_id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="responsible_person_id", type="integer", example=1),
 *                 @OA\Property(property="payroll", type="string", example="P12345"),
 *                 @OA\Property(property="registration_number", type="string", example="REG123456"),
 *                 @OA\Property(property="date", type="string", format="date", example="2024-01-01"),
 *                 @OA\Property(property="status", type="string", example="Active"),
 *                 @OA\Property(property="created_by", type="integer", example=1),
 *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T00:00:00Z"),
 *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T00:00:00Z"),
 *                 @OA\Property(property="deleted_at", type="string", format="date-time", example=null)
 *             ),
 *             @OA\Property(property="statusCode", type="integer", example=200)
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="No responsible person found"),
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

public function show(string $responsible_person_id)
{
    if (auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('View Responsible Person')) {
        $responsiblePerson = DB::table('responsible_persons')
            ->select('responsible_persons.*')
            ->where('responsible_persons.responsible_person_id', '=', $responsible_person_id)
            ->get();

        if (sizeof($responsiblePerson) > 0) {
            $response = [
                'data' => $responsiblePerson,
                'statusCode' => 200
            ];

            return response()->json($response);
        } else {
            return response()->json(['message' => 'No responsible person found', 'statusCode' => 404]);
        }
    } else {
        return response()->json(['message' => 'Unauthorized', 'statusCode' => 401]);
    }
}

/**
 * @OA\Put(
 *     path="/api/responsiblePersons/{responsible_person_id}",
 *     summary="Update a responsible person",
 *     tags={"responsiblePersons"},
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="responsible_person_id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="payroll", type="string", example="P12345"),
 *             @OA\Property(property="registration_number", type="string", example="REG123456"),
 *             @OA\Property(property="date", type="string", format="date", example="2024-01-01"),
 *             @OA\Property(property="status", type="string", example="Active")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Responsible person updated successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Responsible Person Updated Successfully"),
 *             @OA\Property(property="statusCode", type="integer", example=200)
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Bad request",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Registration Number Already Exists"),
 *             @OA\Property(property="statusCode", type="integer", example=400)
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Responsible Person Not Found"),
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

public function update(Request $request, int $responsible_person_id)
{
    if (auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Update Responsible Person')) {
        // Check if the registration_number already exists for a different record
        $check_value = DB::table('responsible_persons')
            ->whereRaw('LOWER(registration_number) = LOWER(?) AND responsible_person_id != ?', [$request->registration_number, $responsible_person_id])
            ->exists();

        if ($check_value) {
            return response()->json([
                'message' => 'Registration Number Already Exists',
                'statusCode' => 400
            ]);
        }
        
        $user_id = auth()->user()->id;
        try {
            $responsiblePerson = ResponsiblePerson::find($responsible_person_id);
            if (!$responsiblePerson) {
                return response()->json(['message' => 'Responsible Person Not Found', 'statusCode' => 404]);
            }

            // Update the record with the request data
            $responsiblePerson->payroll = $request->payroll;
            $responsiblePerson->registration_number = $request->registration_number;
            $responsiblePerson->date = $request->date;
            $responsiblePerson->status = $request->status;
            $responsiblePerson->created_by = $user_id;
            $responsiblePerson->save();

            return response()->json([
                'message' => 'Responsible Person Updated Successfully',
                'statusCode' => 200
            ]); 
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'statusCode' => 500]);
        }
    } else {
        return response()->json(['message' => 'Unauthorized', 'statusCode' => 403]);
    } 
}

/**
 * @OA\Delete(
 *     path="/api/responsiblePersons/{responsible_person_id}",
 *     summary="Delete a responsible person",
 *     tags={"responsiblePersons"},
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="responsible_person_id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=204,
 *         description="Responsible person deleted successfully"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Responsible person not found"),
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

public function destroy(string $responsible_person_id)
{
    if (auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Delete Responsible Person'))
    {
        $responsiblePerson = ResponsiblePerson::find($responsible_person_id);
        
        if ($responsiblePerson) {
            try {
                $responsiblePerson->delete();

                $response = [
                    'message' => 'Responsible person deleted successfully',
                    'statusCode' => 200
                ];
                
                return response()->json($response);
            } catch (Exception $e) {
                return response()->json([
                    'message' => 'Error deleting responsible person: ' . $e->getMessage(),
                    'statusCode' => 500
                ]);
            }
        } else {
            return response()->json([
                'message' => 'Responsible person not found',
                'statusCode' => 404
            ]);
        }
    } 
    else 
    {
        return response()->json([
            'message' => 'Unauthorized',
            'statusCode' => 403
        ]);
    }
}



}
