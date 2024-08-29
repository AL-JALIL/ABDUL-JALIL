<?php

namespace App\Http\Controllers\API\Setup;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\Setup\GeneralController;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\AdminHierarchiesImport;
use App\Models\AdminHierarchies;
use Exception;
use Validator;
use DB;

class AdminHierarchiesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:Setup Management|Create Admin Hierarchies|Create Admin Hierarchies|Update Admin Hierarchies|Update Admin Hierarchies|Delete Admin Hierarchies', ['only' => ['index','create','store','update','destroy']]);

    }


    private function getGrandChildren($parentId)
    {
        $children = DB::table('admin_hierarchies')
                    ->select('admin_hierarchies.*')
                    ->where('parent_id', $parentId)
                    ->get();

        return $children;
    }

    private function getChildren($parentId)
    {
        $children = DB::table('admin_hierarchies')
                    ->select('admin_hierarchies.*')
                    ->where('parent_id', $parentId)
                    ->whereNull('deleted_at')
                    ->get();

        $grandChildrenHierarchy = [];
        foreach ($children as $parent) {
            $parent->grandChildren = $this->getGrandChildren($parent->admin_hierarchy_id);
            $grandChildrenHierarchy[] = $parent;
        }

        return $grandChildrenHierarchy;
    }

    /**
     * @OA\Get(
     *     path="/api/adminHierarchies",
     *     summary="Get a list of admin hierarchies",
     *     tags={"adminHierarchies"},
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
    *                     @OA\Property(property="admin_hierarchy_id", type="integer", example=2),
    *                     @OA\Property(property="admin_hierarchy_name", type="string", example="Human Resource"),
    *                     @OA\Property(property="parent_id", type="string", example="1"),
    *                     @OA\Property(property="created_by", type="integer", example=1),
    *                     @OA\Property(property="first_name", type="string", example="Mohammed"),
    *                     @OA\Property(property="middle_name", type="string", example="Abdalla"),
    *                     @OA\Property(property="last_name", type="string", example="Bakar"),
    *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-08-28 11:30:25"),
    *                     @OA\Property(property="deleted_at", type="string", format="date-time", example="2024-08-28 11:30:25"),
    *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-08-28 11:30:25")
    *                 )
    *             ),
    *             @OA\Property(property="statusCode", type="integer", example=200)
    *         )
    *     )
    * )
    */
    public function index()
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        {
            $adminHierarchies = DB::table('admin_hierarchies')
                                ->join('users', 'users.id', '=', 'admin_hierarchies.created_by')
                                ->select('admin_hierarchies.*','users.first_name','users.middle_name','users.last_name','users.id')
                                ->where("parent_id","1001")
                                ->get();

             // Format parent and children recursively
        $formattedAdminHierarchies = [];
        foreach ($adminHierarchies as $parent) {
            $parent->children = $this->getChildren($parent->admin_hierarchy_id);
            $formattedAdminHierarchies[] = $parent;
        }



            $respose =[
                'data' => $formattedAdminHierarchies,
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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * @OA\Post(
     *     path="/api/adminHierarchies",
     *     summary="Store a new admin hierarchy",
     *     tags={"adminHierarchies"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="admin_hierarchy_name", type="string"),
     *             @OA\Property(property="parent_id", type="string"),
     *             @OA\Property(property="upload_excel", type="string", format="binary")
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
        $auto_id = random_int(10000, 99999).time();
        if((auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL')) && $request->upload_excel){

            $data = Validator::make($request->all(),[
                'upload_excel' => 'mimes:xls,xlsx,csv'
            ]);

            if($data->fails()){
                return response()->json($data->errors());
            }

            try{
                $path = $request->file('upload_excel')->getRealPath();
                $data = Excel::import(new AdminHierarchiesImport,request()->file('upload_excel'));

                $respose =[
                    'message'=> 'Admin Hierarchy Inserted Successfully',
                    'statusCode'=> 201
                ];
                return response()->json($respose);
            }
            catch (Exception $e)
            {
                return response()
                    ->json(['message' => $e->getMessage(),'statusCode'=> 500]);
            }

        }
        else if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Create Admin Hierarchies'))
        {
            $user_id = auth()->user()->id;

            $check_value = DB::select("SELECT admin_hierarchy_name FROM admin_hierarchies WHERE LOWER(admin_hierarchy_name) = LOWER('$request->admin_hierarchy_name')");

            if(sizeof($check_value) != 0)
            {
                $respose =[
                    'message' =>'Admin Hierarchy Name Alraedy Exists',
                    'statusCode'=> 400
                ];

                return response()->json($respose);
            }

            try{
                $AdminHierarchies = AdminHierarchies::create([
                    'admin_hierarchy_id' => $request->admin_hierarchy_id,
                    'admin_hierarchy_name' => $request->admin_hierarchy_name,
                    'parent_id' =>  $request->parent_id,
                    'created_by' => $user_id
                ]);

                $respose =[
                    'message' =>'Admin Hierarchy Inserted Successfully',
                    'statusCode'=> 201
                ];

                return response()->json($respose);
            }
            catch (Exception $e)
            {
                return response()
                    ->json(['message' => $e->getMessage(),'statusCode'=> 500]);
            }
        }
        else{
            return response()
                ->json(['message' => 'unAuthenticated','statusCode'=> 401]);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/adminHierarchies/{admin_hierarchy_id}",
     *     summary="Get a specific adminHierarchy",
     *     tags={"adminHierarchies"},
     *     @OA\Parameter(
     *         name="admin_hierarchy_id",
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
    *                     @OA\Property(property="admin_hierarchy_id", type="integer", example=2),
    *                     @OA\Property(property="admin_hierarchy_name", type="string", example="Human Resource"),
    *                     @OA\Property(property="parent_id", type="string", example="1"),
    *                     @OA\Property(property="created_by", type="integer", example=1),
    *                     @OA\Property(property="first_name", type="string", example="Mohammed"),
    *                     @OA\Property(property="middle_name", type="string", example="Abdalla"),
    *                     @OA\Property(property="last_name", type="string", example="Bakar"),
    *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-08-28 11:30:25"),
    *                     @OA\Property(property="deleted_at", type="string", format="date-time", example="2024-08-28 11:30:25"),
    *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-08-28 11:30:25")
    *                 )
    *             ),
    *             @OA\Property(property="statusCode", type="integer", example=200)
    *         )
    *     )
    * )
    */
    public function show(string $admin_hierarchy_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        {
            $adminHierarchies = DB::table('admin_hierarchies')
                                ->select('admin_hierarchies.*')
                                ->where('admin_hierarchies.admin_hierarchy_id', '=',$admin_hierarchy_id)
                                ->whereNull('admin_hierarchies.deleted_at')
                                ->get();

            if (sizeof($adminHierarchies) > 0)
            {
                $respose =[
                    'data' => $adminHierarchies,
                    'statusCode'=> 200
                ];

                return response()->json($respose);

            }else{
                return response()
                ->json(['message' => 'No Admin Hierarchy Found','statusCode'=> 400]);
            }

        }
        else{
            return response()
                ->json(['message' => 'unAuthenticated','statusCode'=> 401]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * @OA\Put(
     *     path="/api/adminHierarchies/{admin_hierarchy_id}",
     *     summary="Update a admin hierarchies",
     *     tags={"adminHierarchies"},
     *     @OA\Parameter(
     *         name="admin_hierarchy_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="admin_hierarchy_name", type="string"),
     *             @OA\Property(property="parent_id", type="string"),
     *             @OA\Property(property="label", type="string")
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
    public function update(Request $request, string $admin_hierarchy_id)
    {
        $check_value = DB::select("SELECT admin_hierarchy_name FROM admin_hierarchies WHERE LOWER(admin_hierarchy_name) = LOWER('$request->admin_hierarchy_name') and admin_hierarchy_id != '$admin_hierarchy_id'");

        if(sizeof($check_value) != 0)
        {
            $respose =[
                'message' =>'Admin Hierarchy Name Alraedy Exists',
                'statusCode'=> 400
            ];

            return response()->json($respose);
        }

        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Update Admin Hierarchies'))
        {
            $user_id = auth()->user()->id;
            try{
                $AdminHierarchies = AdminHierarchies::find($admin_hierarchy_id);
                $AdminHierarchies->admin_hierarchy_name  = $request->admin_hierarchy_name;
                $AdminHierarchies->created_by = $user_id;
                $AdminHierarchies->update();

                $respose =[
                    'message' =>'Admin Hierarchy Updated Successfully',
                    'statusCode'=> 201
                ];
                return response()->json($respose);
            }
            catch (Exception $e)
            {
                return response()
                    ->json(['message' => $e->getMessage(),'statusCode'=> 500]);
            }
        }
        else{
            return response()
                ->json(['message' => 'unAuthenticated','statusCode'=> 401]);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/adminHierarchies/{admin_hierarchy_id}",
     *     summary="Delete a admin hierarchy",
     *     tags={"adminHierarchies"},
     *     @OA\Parameter(
     *         name="admin_hierarchy_id",
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
    public function destroy(string $admin_hierarchy_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Delete Admin Hierarchies'))
        {
            $delete = AdminHierarchies::find($admin_hierarchy_id);
            if ($delete != null) {
                $delete->delete();

                $respose =[
                    'message'=> 'Admin Hierarchy Blocked Successfuly',
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
