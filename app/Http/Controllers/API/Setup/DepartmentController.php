<?php

namespace App\Http\Controllers\API\Setup;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\DepartmentImport;
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
     *     path="/api/departments",
     *     summary="Get a list of departments",
     *     tags={"department"},
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
    *                     @OA\Property(property="department_name", type="string"),
    *                     @OA\Property(property="uuid", type="string"),
    *                     @OA\Property(property="parent_id", type="integer"),
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
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('View Department'))
        {
            $department =DB::table('departments')
                            ->join('users', 'users.id', '=', 'departments.created_by')
                            ->select('departments.*','users.first_name','users.middle_name','users.last_name',)
                            ->get();

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
     *     path="/api/departments",
     *     summary="Store a new department",
     *     tags={"department"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="department_name", type="string"),
     *             @OA\Property(property="parent_id", type="string")
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
        $user_id = auth()->user()->id;

        if((auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL')) && $request->upload_excel)
        {
            $data = Validator::make($request->all(),[
                'upload_excel' => 'mimes:xls,xlsx,csv'
            ]);

            if($data->fails()){
                return response()->json($data->errors());       
            }
            
            try{
                $path = $request->file('upload_excel')->getRealPath();
                $data = Excel::import(new DepartmentImport,request()->file('upload_excel'));
                $respose =[
                    'message'=> 'Department inserted successfully',
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
        else if(auth()->user()->can('Create Department'))
        {
            $check_value = DB::select("SELECT department_name FROM departments WHERE LOWER(department_name) = LOWER('$request->department_name')");

            if(sizeof($check_value) != 0)
            {
                $respose =[
                    'message' =>'Department name allraedy exists',
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
                    'message' =>'Department inserted successfully',
                    'statusCode'=> 201
                ];
        
                return response()->json($respose);
            }
            catch (Exception $e)
            {
                return response()
                    ->json(['message' => 'Internal server error','statusCode'=> 401 ,'error'=> $e->getMessage()]);
            }

        }
        else{
            return response()
                ->json(['message' => 'unAuthenticated','statusCode'=> 401]);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/departments/{department_id}",
     *     summary="Get a specific department",
     *     tags={"department"},
     *     @OA\Parameter(
     *         name="department_id",
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
    *                     @OA\Property(property="department_name", type="string"),
    *                     @OA\Property(property="uuid", type="string"),
    *                     @OA\Property(property="parent_id", type="integer"),
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
    public function show($department_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('View Department'))
        {
            $department = DB::table('departments')
                                ->join('users', 'users.id', '=', 'departments.created_by')
                                ->select('departments.*','users.first_name','users.middle_name','users.last_name',)
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
     *     path="/api/departments/{department_id}",
     *     summary="Update an existing department",
     *     tags={"department"},
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
     *             @OA\Property(property="department_name", type="string"),
     *             @OA\Property(property="parent_id", type="string")
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
    public function update(Request $request, string $department_id)
    {
        $user_id = auth()->user()->id;
        
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
    *     path="/api/departments/{department_id}",
    *     summary="Delete a department",
    *     tags={"department"},
    *     @OA\Parameter(
    *         name="department_id",
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
