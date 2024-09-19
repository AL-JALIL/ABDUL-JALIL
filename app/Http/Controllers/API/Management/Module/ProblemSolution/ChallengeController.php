<?php

namespace App\Http\Controllers\API\Management\Module\ProblemSolution;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Challenges;
use App\Enums\Status;
use App\Models\ChalengeSolutions;
use Illuminate\Support\Str;
use Exception;
use Validator;
use DB;

class ChallengeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:View Challenge|Create Challenge|View Challenge|Delete Challenge|Create Challenge Solution|Delete Challenge Solution', ['only' => ['view_challenge','store_challenge','show_challenge','destroy_challenge','store_solution','destroy_solution']]);
    }

    /**
     * @OA\Get(
     *     path="/api/viewChallenge",
     *     summary="Get a list of challenges",
     *     tags={"challenges"},
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
    *                     @OA\Property(property="chalenge_id", type="string"),
    *                     @OA\Property(property="uuid", type="string"),
    *                     @OA\Property(property="challenge_title", type="string"),
    *                     @OA\Property(property="description", type="string"),
    *                     @OA\Property(property="status", type="string"),
    *                     @OA\Property(property="department_id", type="integer"),
    *                     @OA\Property(property="department_name", type="integer"),
    *                     @OA\Property(property="user_id", type="integer"),
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
    public function view_challenge()
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('View Challenge'))
        {
            $challenges = DB::table('challenges')
                            ->join('users', 'users.id', '=', 'challenges.user_id')
                            ->join('departments', 'departments.department_id', '=', 'challenges.department_id')
                            ->select('challenges.*','users.first_name','users.middle_name','users.last_name','departments.department_name')
                            ->get();

            $respose =[
                'data' => $challenges,
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
     *     path="/api/storeChallenge",
     *     summary="Store a new challenges",
     *     tags={"challenges"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="challenge_title", type="string"),
     *             @OA\Property(property="department_id", type="integer"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="challenge_file", type="string")
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
    public function store_challenge(Request $request)
    {
        $request->validate([
            'challenge_title' => 'required',
            'department_id' => 'required',
            'description' => 'required'
        ]);

        if($data->fails()){
            return response()->json($data->errors());       
        }

        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Create Chalenge')) 
        {
            $user_id = auth()->user()->id;

            try
            {
                if($request->file('chalenge_file')) {

                    $upload_file_name = $request->file('chalenge_file');
                    $file_name = ($upload_file_name)->getClientOriginalName();
                    $temp = explode(".", $file_name);
                    $newfilename = random_int(1000000000, 9999999999) . '.' . end($temp);
                    $upload_file_name->move(public_path("uploads/challenges/"), $newfilename);

                    $challenges = Challenges::create([ 
                        'user_id' => $user_id,
                        'uuid' => Str::uuid(),
                        'challenge_title' => $request->challenge_title,
                        'department_id' => $request->department_id,
                        'description' => $request->description,
                        'challenge_file' => $newfilename,
                    ]);
    
                    $response = [
                        'message' => 'Challenge inserted successfully',
                        'statusCode' => 201
                    ];
                }
                else{

                    $challenges = Challenges::create([ 
                        'user_id' => $user_id,
                        'uuid' => Str::uuid(),
                        'challenge_title' => $request->challenge_title,
                        'department_id' => $request->department_id,
                        'description' => $request->description,
                    ]);
    
                    $response = [
                        'message' => 'Challenge inserted successfully',
                        'statusCode' => 201
                    ];
                }
                return response()->json($response);
            } 
            catch (Exception $e) 
            {
                return response()->json([
                    'message' => $e->getMessage(),
                    'statusCode' => 401
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
    * @OA\Get(
    *     path="/api/showChallenge/{chalenge_id}",
    *     summary="Get a specific challenges",
    *     tags={"challenges"},
    *     @OA\Parameter(
    *         name="chalenge_id",
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
    *                     @OA\Property(property="chalenge_id", type="string"),
    *                     @OA\Property(property="uuid", type="string"),
    *                     @OA\Property(property="challenge_title", type="string"),
    *                     @OA\Property(property="description", type="string"),
    *                     @OA\Property(property="status", type="string"),
    *                     @OA\Property(property="department_id", type="integer"),
    *                     @OA\Property(property="department_name", type="integer"),
    *                     @OA\Property(property="user_id", type="integer"),
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
    public function show_challenge($chalenge_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('View Chalenge')) 
        {
            $chalenge = DB::table('challenges')
                            ->join('users', 'users.id', '=', 'challenges.user_id')
                            ->join('departments', 'departments.department_id', '=', 'challenges.department_id')
                            ->join('chalenge_solutions', 'chalenge_solutions.chalenge_id', '=', 'challenges.chalenge_id')
                            ->select('challenges.*','users.first_name','users.middle_name','users.last_name','departments.department_name','chalenge_solutions.solution')
                            ->where('challenges.chalenge_id', '=', $chalenge_id)
                            ->get();
            $response = [
                'data' => $chalenge,
                'statusCode' => 200
            ];

            return response()->json($response);

        } else {
            return response()
                ->json(['message' => 'Unauthorized', 'statusCode' => 401]);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/destroyChallenge/{chalenge_id}",
     *     summary="Delete an challenges",
     *     tags={"challenges"},
     *     @OA\Parameter(
     *         name="chalenge_id",
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
    public function destroy_challenge(string $chalenge_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Delete chalenge'))
        {
            $delete = Challenges::find($chalenge_id);
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

    /**
     * @OA\Post(
     *     path="/api/storeSolution",
     *     summary="Store a challenges solution",
     *     tags={"challenges"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="solution", type="string"),
     *             @OA\Property(property="chalenge_id", type="integer"),
     *             @OA\Property(property="solution_file", type="string")
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
    public function store_solution(Request $request)
    {
        $request->validate([
            'Solution_file' => 'required|mimes:pdf|max:2048',
            'solution' => 'required|string',
            'chalenge_id' => 'required|string'
        ]);

        if($data->fails()){
            return response()->json($data->errors());       
        }
        
        $user_id = auth()->user()->id;

        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Create Chalenge')) 
        {
            try
            {
                if($request->file('Solution_file')) 
                {
                    $upload_file_name = $request->file('Solution_file');
                    $file_name = ($upload_file_name)->getClientOriginalName();
                    $temp = explode(".", $file_name);
                    $newfilename = random_int(1000000000, 9999999999) . '.' . end($temp);
                    $upload_file_name->move(public_path("uploads/Solutions/"), $newfilename);

                    $chalengeSolutions = ChalengeSolutions::create([ 
                        'created_by' => $user_id,
                        'uuid' => Str::uuid(),
                        'chalenge_id' => $request->chalenge_id,
                        'solution_file' => $newfilename,
                        'solution' => $request->solution,
                        'status' => $request->solution,
                    ]);
    
                    $response = [
                        'message' => 'Challenge inserted successfully',
                        'statusCode' => 201
                    ];
                }
                else
                {
                    $chalengeSolutions = ChalengeSolutions::create([ 
                        'created_by' => $user_id,
                        'uuid' => Str::uuid(),
                        'chalenge_id' => $request->chalenge_id,
                        'solution' => $request->solution,
                        'status' => $request->solution,
                    ]);
    
                    $response = [
                        'message' => 'Challenge inserted successfully',
                        'statusCode' => 201
                    ];
                }
                return response()->json($response);
            } 
            catch (Exception $e) 
            {
                return response()->json([
                    'message' => $e->getMessage(),
                    'statusCode' => 401
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
     *     path="/api/destroySolution/{chalenge_solution_id}",
     *     summary="Delete a chalenge solution_",
     *     tags={"challenges"},
     *     @OA\Parameter(
     *         name="chalenge_solution_id",
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
    public function destroy_solution($chalenge_solution_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Delete Solution'))
        {
            $delete = ChalengeSolutions::find($chalenge_solution_id);
            if ($delete != null) {
                $delete->delete();
                
                $respose =[
                    'message'=> 'Solution Blocked Successfuly',
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
