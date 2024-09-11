<?php

namespace App\Http\Controllers\API\Setup;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;
use Illuminate\Support\Str;
use Exception;
use Validator;
use DB;

class DepartmentController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:Setup Modules|Create Department|Create Department|Update Department|Update Department|Delete Department', ['only' => ['index','create','store','update','destroy']]);
    }
    
    /**
     * @OA\Get(
     *     path="/api/Departments",
     *     summary="Get a list of departments",
     *     tags={"Departments"},
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
     *                     @OA\Property(property="Department_id", type="integer", example=2),
     *                     @OA\Property(property="department_name", type="string", example="Out Patient OPD"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-08-28 11:30:25"),
     *                     @OA\Property(property="deleted_at", type="string", format="date-time", example="2024-08-28 11:30:25"),
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
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('View Department'))
        {
            $department =DB::table('departments')->get();

            $respose =[
                'data' => $department,
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
     *     path="/api/Departments",
     *     summary="Create a new department",
     *     tags={"Departments"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="department_name", type="string", example="Emergency Department"),
     *             @OA\Property(property="parent_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Department created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Department Inserted Successfully"),
     *             @OA\Property(property="statusCode", type="integer", example=201)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Department Name Already Exists"),
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
    if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Create Department'))
        {
            $user_id = auth()->user()->id;
    
            $check_value = DB::select("SELECT department_name FROM departments WHERE LOWER(department_name) = LOWER('$request->department_name')");

            if(sizeof($check_value) != 0)
            {
                $respose =[
                    'message' =>'Identification Name Alraedy Exists',
                    'statusCode'=> 400
                ];
    
                return response()->json($respose);       
            }

            try{
                $department = Department::create([ 
                    'department_name' => $request->department_name,
                    'parent_id'=>$request->parent_id,
                    'created_by' => $user_id
                ]);
        
                $respose =[
                    'message' =>'department Inserted Successfully',
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
     *     path="/api/Departments/{department_id}",
     *     summary="Get a specific department by ID",
     *     tags={"Departments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="department_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Department ID"
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
     *         description="No Department Found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No Department Found"),
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

// get specific department
    public function show(string $department_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('View Department'))
        {
            $department = DB::table('departments')
                                ->select('departments.*')
                                ->where('departments.department_id', '=',$department_id)
                                ->get();

            if (sizeof($department) > 0) 
            {
                $respose =[
                    'data' => $department,
                    'statusCode'=> 200
                ];

                return response()->json($respose);

            }else{
                return response()
                ->json(['message' => 'No Department Found','statusCode'=> 400]);
            }
                
        }
        else{
            return response()
                ->json(['message' => 'unAuthenticated','statusCode'=> 401]);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/Department/{department_id}",
     *     summary="Update an existing department",
     *     tags={"Departments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="department_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Department ID"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="department_name", type="string", example="Emergency Department"),
     *             @OA\Property(property="parent_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Department updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Department Updated Successfully"),
     *             @OA\Property(property="statusCode", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Department Name Already Exists"),
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

/// update department
    public function update(Request $request, string $department_id)
    {
       
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Update Department'))
        {

            $check_value = DB::select("SELECT department_name FROM departments WHERE LOWER(department_name) = LOWER('$request->department_name') and department_id != $department_id");

            if(sizeof($check_value) != 0)
            {
                $respose =[
                    'message' =>'Department Name Alraedy Exists',
                    'statusCode'=> 400
                ];
    
                return response()->json($respose);       
            }
            

            $user_id = auth()->user()->id;
            try{
                $department = Department::find($department_id);
                $department->department_name  = $request->department_name;
                $department->parent_id  = $request->parent_id;
                $department->created_by = $user_id;
                $department->update();

                $respose =[
                    'message' =>'Department Updated Successfully',
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
     *     path="/api/Department/{department_id}",
     *     summary="Delete a department",
     *     tags={"Departments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="department_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Department ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Department blocked successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Department Blocked Successfully"),
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


    public function destroy(string $department_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Delete Department'))
        {
            $delete = Department::find($department_id);
            if ($delete != null) {
                $delete->delete();
                
                $respose =[
                    'message'=> 'Department Blocked Successfuly',
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
