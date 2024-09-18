<?php

namespace App\Http\Controllers\API\Setup;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Condition;
use Illuminate\Support\Str;
use Exception;
use Validator;
use DB;

class ConditionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:Setup Modules|Create Condition|Create Condition|Update Condition|Update Condition|Delete Condition', ['only' => ['index','create','store','update','destroy']]);
    }

    /**
     * @OA\Get(
     *     path="/api/conditions",
     *     summary="Get a list of conditions",
     *     tags={"conditions"},
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
     *                     @OA\Property(property="condition_id", type="integer", example=1),
     *                     @OA\Property(property="condition_name", type="string", example="New"),
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
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('View Condition'))
        {
            $condition =DB::table('conditions')->get();

            $respose =[
                'data' => $condition,
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
     * @OA\Get(
     *     path="/api/conditions/{condition_id}",
     *     summary="Get a specific condition",
     *     tags={"conditions"},
     *     @OA\Parameter(
     *         name="condition_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
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
     *                     @OA\Property(property="condition_id", type="integer", example=1),
     *                     @OA\Property(property="condition_name", type="string", example="New"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-08-28 11:30:25"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-08-28 11:30:25")
     *                 )
     *             ),
     *             @OA\Property(property="statusCode", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="No condition found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No condition Found"),
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

    // get specific condition
    public function show(string $condition_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('View Condition'))
        {
            $condition = DB::table('conditions')
                                ->select('conditions.*')
                                ->where('conditions.condition_id', '=',$condition_id)
                                ->get();

            if (sizeof($condition) > 0) 
            {
                $respose =[
                    'data' => $condition,
                    'statusCode'=> 200
                ];

                return response()->json($respose);

            }else{
                return response()
                ->json(['message' => 'No condition Found','statusCode'=> 400]);
            }
                
        }
        else{
            return response()
                ->json(['message' => 'unAuthenticated','statusCode'=> 401]);
        }
    }

     /**
     * @OA\Delete(
     *     path="/api/conditions/{condition_id}",
     *     summary="Delete a specific condition",
     *     tags={"conditions"},
     *     @OA\Parameter(
     *         name="condition_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Condition Blocked Successfully"),
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

    

    public function destroy(string $asset_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Delete Condition'))
        {
            $delete = Condition::find($asset_id);
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
