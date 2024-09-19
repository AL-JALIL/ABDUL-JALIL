<?php

namespace App\Http\Controllers\API\Setup;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\Setup\GeneralController;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\WorkingStationsImport;
use App\Models\WorkingStations;
use Illuminate\Support\Str;
use Exception;
use Validator;
use DB;

class WorkingStationsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:Setup Management|Create Work Station|Create Work Station|Update Work Station|Update Work Station|Delete Work Station', ['only' => ['index','create','store','update','destroy']]);
    }

    /**
     * @OA\Get(
     *     path="/api/workStations",
     *     summary="Get a list of work stations",
     *     tags={"workStations"},
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
    *                     @OA\Property(property="working_station_id", type="integer", example=2),
    *                     @OA\Property(property="working_station_name", type="string", example="Human Resource"),
    *                     @OA\Property(property="location_id", type="string", example="1"),
    *                     @OA\Property(property="location_name", type="string", example="Mpendae"),
    *                     @OA\Property(property="admin_hierarchy_id", type="string", example="1"),
    *                     @OA\Property(property="admin_hierarchy_name", type="string", example="Human Resource"),
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
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL'))
        {
            $workingStation = DB::table('working_stations')
                                    ->join('geographical_locations','geographical_locations.location_id','=','working_stations.location_id')
                                    ->join('admin_hierarchies','admin_hierarchies.admin_hierarchy_id','=','working_stations.admin_hierarchy_id')
                                    ->select('working_stations.*',
                                        'geographical_locations.location_id','geographical_locations.location_name',
                                        'admin_hierarchies.admin_hierarchy_id','admin_hierarchies.admin_hierarchy_name')
                                    ->get();

            $workingStations = [];
            foreach($workingStation as $item){
                array_push($workingStations, array(
                    'working_station_id' => $item->working_station_id,
                    'working_station_name' => $item->working_station_name,
                    'location_id' => $item->location_id,
                    'location_name' => $item->location_name,
                    'admin_hierarchy_id' => $item->admin_hierarchy_id,
                    'admin_hierarchy_name' => $item->admin_hierarchy_name,
                    'created_by' => $item->created_by,
                    'deleted_at' => $item->deleted_at,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                    'isSelected' => false
                ));
            }

            $respose =[
                'data' => $workingStations,
                'statusCode'=> 200
            ];

            return response()->json($respose);
        }else if(auth()->user()->can('Zone B Permission'))
        {
            $id = auth()->user()->id;
            $get_user_hierarchy = DB::select("SELECT uh.*, cd.admin_hierarchy_id
                                    FROM user_hierarchies uh
                                    INNER JOIN users u ON u.id = uh.user_id
                                    INNER JOIN working_stations ws ON ws.working_station_id = uh.working_station_id
                                    INNER JOIN admin_hierarchies ah ON ah.admin_hierarchy_id = ws.admin_hierarchy_id
                                    INNER JOIN admin_hierarchies dv ON dv.admin_hierarchy_id = ah.parent_id
                                    INNER JOIN admin_hierarchies dp ON dp.admin_hierarchy_id = dv.parent_id
                                    INNER JOIN admin_hierarchies cd ON cd.admin_hierarchy_id = dp.parent_id
                                    WHERE uh.status = 1 AND u.id = $id
                                ");

            $admin_hierarchy_id = $get_user_hierarchy[0]->admin_hierarchy_id;

            $workingStation = DB::select("SELECT ws.*, gl.location_id, gl.location_name, ah.admin_hierarchy_id, ah.admin_hierarchy_name
                                    FROM working_stations ws
                                    INNER JOIN geographical_locations gl ON gl.location_id = ws.location_id
                                    INNER JOIN admin_hierarchies ah ON ah.admin_hierarchy_id = ws.admin_hierarchy_id
                                    INNER JOIN admin_hierarchies dv ON dv.admin_hierarchy_id = ah.parent_id
                                    INNER JOIN admin_hierarchies dp ON dp.admin_hierarchy_id = dv.parent_id
                                    INNER JOIN admin_hierarchies cd ON cd.admin_hierarchy_id = dp.parent_id
                                    WHERE cd.admin_hierarchy_id = '$admin_hierarchy_id'
                              ");

            $workingStations = [];
            foreach($workingStation as $item){
                array_push($workingStations, array(
                    'working_station_id' => $item->working_station_id,
                    'working_station_name' => $item->working_station_name,
                    'location_id' => $item->location_id,
                    'location_name' => $item->location_name,
                    'admin_hierarchy_id' => $item->admin_hierarchy_id,
                    'admin_hierarchy_name' => $item->admin_hierarchy_name,
                    'created_by' => $item->created_by,
                    'deleted_at' => $item->deleted_at,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                    'isSelected' => false
                ));
            }

            $respose =[
                'data' => $workingStations,
                'statusCode'=> 200
            ];

            return response()->json($respose);

        }else if(auth()->user()->can('Department permission'))
        {
            $id = auth()->user()->id;
            $get_user_hierarchy = DB::select("SELECT uh.*, dp.admin_hierarchy_id
                                    FROM user_hierarchies uh
                                    INNER JOIN users u ON u.id = uh.user_id
                                    INNER JOIN working_stations ws ON ws.working_station_id = uh.working_station_id
                                    INNER JOIN admin_hierarchies ah ON ah.admin_hierarchy_id = ws.admin_hierarchy_id
                                    INNER JOIN admin_hierarchies dv ON dv.admin_hierarchy_id = ah.parent_id
                                    INNER JOIN admin_hierarchies dp ON dp.admin_hierarchy_id = dv.parent_id
                                    WHERE uh.status = 1 AND u.id = $id
                                ");

            $admin_hierarchy_id = $get_user_hierarchy[0]->admin_hierarchy_id;

            $workingStation = DB::select("SELECT ws.*, gl.location_id, gl.location_name, ah.admin_hierarchy_id, ah.admin_hierarchy_name
                                    FROM working_stations ws
                                    INNER JOIN geographical_locations gl ON gl.location_id = ws.location_id
                                    INNER JOIN admin_hierarchies ah ON ah.admin_hierarchy_id = ws.admin_hierarchy_id
                                    INNER JOIN admin_hierarchies dv ON dv.admin_hierarchy_id = ah.parent_id
                                    INNER JOIN admin_hierarchies dp ON dp.admin_hierarchy_id = dv.parent_id
                                    WHERE dp.admin_hierarchy_id = '$admin_hierarchy_id'
                              ");

            $workingStations = [];
            foreach($workingStation as $item){
                array_push($workingStations, array(
                    'working_station_id' => $item->working_station_id,
                    'working_station_name' => $item->working_station_name,
                    'location_id' => $item->location_id,
                    'location_name' => $item->location_name,
                    'admin_hierarchy_id' => $item->admin_hierarchy_id,
                    'admin_hierarchy_name' => $item->admin_hierarchy_name,
                    'created_by' => $item->created_by,
                    'deleted_at' => $item->deleted_at,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                    'isSelected' => false
                ));
            }

            $respose =[
                'data' => $workingStations,
                'statusCode'=> 200
            ];

            return response()->json($respose);
        }else if(auth()->user()->can('Division permission'))
        {
            $id = auth()->user()->id;
            $get_user_hierarchy = DB::select("SELECT uh.*, dv.admin_hierarchy_id
                                    FROM user_hierarchies uh
                                    admin_hierarchies                           INNER JOIN users u ON u.id = uh.user_id
                                    INNER JOIN working_stations ws ON ws.working_station_id = uh.working_station_id
                                    INNER JOIN admin_hierarchies ah ON ah.admin_hierarchy_id = ws.admin_hierarchy_id
                                    INNER JOIN admin_hierarchies dv ON dv.admin_hierarchy_id = ah.parent_id
                                    WHERE uh.status = 1 AND u.id = $id
                                ");

            $admin_hierarchy_id = $get_user_hierarchy[0]->admin_hierarchy_id;

            $workingStation = DB::select("SELECT ws.*, gl.location_id, gl.location_name, ah.admin_hierarchy_id, ah.admin_hierarchy_name
                                    FROM working_stations ws
                                    INNER JOIN geographical_locations gl ON gl.location_id = ws.location_id
                                    INNER JOIN admin_hierarchies ah ON ah.admin_hierarchy_id = ws.admin_hierarchy_id
                                    INNER JOIN admin_hierarchies dv ON dv.admin_hierarchy_id = ah.parent_id
                                    WHERE ah.admin_hierarchy_id = '$admin_hierarchy_id'
                              ");

            $workingStations = [];
            foreach($workingStation as $item){
                array_push($workingStations, array(
                    'working_station_id' => $item->working_station_id,
                    'working_station_name' => $item->working_station_name,
                    'location_id' => $item->location_id,
                    'location_name' => $item->location_name,
                    'admin_hierarchy_id' => $item->admin_hierarchy_id,
                    'admin_hierarchy_name' => $item->admin_hierarchy_name,
                    'created_by' => $item->created_by,
                    'deleted_at' => $item->deleted_at,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                    'isSelected' => false
                ));
            }

            $respose =[
                'data' => $workingStations,
                'statusCode'=> 200
            ];

            return response()->json($respose);
        }else if(auth()->user()->can('Unit permission'))
        {
            $id = auth()->user()->id;
            $get_user_hierarchy = DB::select("SELECT uh.*, ah.admin_hierarchy_id
                                    FROM user_hierarchies uh
                                    INNER JOIN users u ON u.id = uh.user_id
                                    INNER JOIN working_stations ws ON ws.working_station_id = uh.working_station_id
                                    INNER JOIN admin_hierarchies ah ON ah.admin_hierarchy_id = ws.admin_hierarchy_id
                                    WHERE uh.status = 1 AND u.id = $id
                                ");

            $admin_hierarchy_id = $get_user_hierarchy[0]->admin_hierarchy_id;

            $workingStation = DB::select("SELECT ws.*, gl.location_id, gl.location_name, ah.admin_hierarchy_id, ah.admin_hierarchy_name
                                    FROM working_stations ws
                                    INNER JOIN geographical_locations gl ON gl.location_id = ws.location_id
                                    INNER JOIN admin_hierarchies ah ON ah.admin_hierarchy_id = ws.admin_hierarchy_id
                                    WHERE ah.admin_hierarchy_id = '$admin_hierarchy_id'
                              ");

            $workingStations = [];
            foreach($workingStation as $item){
                array_push($workingStations, array(
                    'working_station_id' => $item->working_station_id,
                    'working_station_name' => $item->working_station_name,
                    'location_id' => $item->location_id,
                    'location_name' => $item->location_name,
                    'admin_hierarchy_id' => $item->admin_hierarchy_id,
                    'admin_hierarchy_name' => $item->admin_hierarchy_name,
                    'created_by' => $item->created_by,
                    'deleted_at' => $item->deleted_at,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                    'isSelected' => false
                ));
            }

            $respose =[
                'data' => $workingStations,
                'statusCode'=> 200
            ];

            return response()->json($respose);
        }else if(auth()->user()->can('DMO permission'))
        {
            $get_user_hierarchy = DB::table('user_hierarchies')
                                ->join('users', 'users.id','=', 'user_hierarchies.user_id')
                                ->join('geographical_locations','geographical_locations.location_id','=','users.location_id')
                                ->join('working_stations', 'working_stations.working_station_id','=', 'user_hierarchies.working_station_id')
                                ->join('admin_hierarchies', 'admin_hierarchies.admin_hierarchy_id','=', 'working_stations.admin_hierarchy_id')
                                ->select('geographical_locations.location_id', 'admin_hierarchies.admin_hierarchy_id', 'admin_hierarchies.parent_id', 'working_stations.working_station_id')
                                ->where('users.id', auth()->user()->id)
                                ->where('user_hierarchies.status',1)
                                ->get();

            $working_location_id = $get_user_hierarchy[0]->location_id;
            $working_station_id = $get_user_hierarchy[0]->working_station_id;
            $admin_hierarchy_id = $get_user_hierarchy[0]->admin_hierarchy_id;

            $workingStation = DB::table('working_stations')
                                ->join('geographical_locations','geographical_locations.location_id','=','working_stations.location_id')
                                ->join('admin_hierarchies','admin_hierarchies.admin_hierarchy_id','=','working_stations.admin_hierarchy_id')
                                ->select('working_stations.*',
                                    'geographical_locations.location_id','geographical_locations.location_name',
                                    'admin_hierarchies.admin_hierarchy_id','admin_hierarchies.admin_hierarchy_name')
                                ->where('geographical_locations.parent_id','=', $working_location_id)
                                ->where('admin_hierarchies.admin_hierarchy_id', '=', $admin_hierarchy_id)
                                ->where('working_stations.working_station_id', '!=', $working_station_id)
                                ->get();

            $workingStations = [];
            foreach($workingStation as $item){
                array_push($workingStations, array(
                    'working_station_id' => $item->working_station_id,
                    'working_station_name' => $item->working_station_name,
                    'location_id' => $item->location_id,
                    'location_name' => $item->location_name,
                    'admin_hierarchy_id' => $item->admin_hierarchy_id,
                    'admin_hierarchy_name' => $item->admin_hierarchy_name,
                    'created_by' => $item->created_by,
                    'deleted_at' => $item->deleted_at,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                    'isSelected' => false
                ));
            }

            $respose =[
                'data' => $workingStations,
                'statusCode'=> 200
            ];

            return response()->json($respose);
        }else if(auth()->user()->can('Setup Management'))
        {
            $get_user_hierarchy = DB::table('user_hierarchies')
                                        ->where('user_id', auth()->user()->id)
                                        ->where('user_hierarchies.status',1)
                                        ->get();

            $working_station_id = $get_user_hierarchy[0]->working_station_id;

            $workingStation = DB::table('working_stations')
                                    ->join('geographical_locations','geographical_locations.location_id','=','working_stations.location_id')
                                    ->join('admin_hierarchies','admin_hierarchies.admin_hierarchy_id','=','working_stations.admin_hierarchy_id')
                                    ->select('working_stations.*',
                                        'geographical_locations.location_id','geographical_locations.location_name',
                                        'admin_hierarchies.admin_hierarchy_id','admin_hierarchies.admin_hierarchy_name')
                                    ->where('working_stations.working_station_id','=', $working_station_id)
                                    ->get();

            $workingStations = [];
            foreach($workingStation as $item){
                array_push($workingStations, array(
                    'working_station_id' => $item->working_station_id,
                    'working_station_name' => $item->working_station_name,
                    'location_id' => $item->location_id,
                    'location_name' => $item->location_name,
                    'admin_hierarchy_id' => $item->admin_hierarchy_id,
                    'admin_hierarchy_name' => $item->admin_hierarchy_name,
                    'created_by' => $item->created_by,
                    'deleted_at' => $item->deleted_at,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                    'isSelected' => false
                ));
            }

            $respose =[
                'data' => $workingStations,
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
     *     path="/api/workStations",
     *     summary="Store a new work stations",
     *     tags={"workStations"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="working_station_name", type="string"),
     *             @OA\Property(property="location_id", type="string"),
     *             @OA\Property(property="admin_hierarchy_id", type="string"),
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
        if(auth()->user()->hasRole('ROLE ADMIN') && $request->upload_excel){

            $data = Validator::make($request->all(),[
                'upload_excel' => 'mimes:xls,xlsx,csv'
            ]);

            if($data->fails()){
                return response()->json($data->errors());       
            }
            
            try{
                $path = $request->file('upload_excel')->getRealPath();
                $data = Excel::import(new WorkingStationsImport,request()->file('upload_excel'));
                $respose =[
                    'message'=> 'Working Station Inserted Successfully',
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
        else if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Create Work Station'))
        {
            $user_id = auth()->user()->id;
    
            $check_value = DB::select("SELECT working_station_name FROM working_stations WHERE LOWER(working_station_name) = LOWER('$request->working_station_name')");

            if(sizeof($check_value) != 0)
            {
                $respose =[
                    'message' =>'Working Station Name Alraedy Exists',
                    'statusCode'=> 400
                ];
    
                return response()->json($respose);       
            }

            try{
                $WorkingStations = WorkingStations::create([
                    'uuid' => Str::uuid(),
                    'working_station_name' => $request->working_station_name,
                    'location_id' => $request->location_id,
                    'admin_hierarchy_id' => $request->admin_hierarchy_id,
                    'created_by' => $user_id
                ]);
        
                $respose =[
                    'message' =>'Working Station Inserted Successfully',
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
     *     path="/api/workStations/{working_station_id}",
     *     summary="Get a specific work station",
     *     tags={"workStations"},
     *     @OA\Parameter(
     *         name="working_station_id",
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
    *                     @OA\Property(property="working_station_id", type="integer", example=2),
    *                     @OA\Property(property="working_station_name", type="string", example="Human Resource"),
    *                     @OA\Property(property="location_id", type="string", example="1"),
    *                     @OA\Property(property="location_name", type="string", example="Mpendae"),
    *                     @OA\Property(property="admin_hierarchy_id", type="string", example="1"),
    *                     @OA\Property(property="admin_hierarchy_name", type="string", example="Human Resource"),
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
    public function show(string $working_station_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        {
            $workingStations = DB::table('working_stations')
                                ->join('users', 'users.id', '=', 'working_stations.created_by')
                                ->join('geographical_locations','geographical_locations.location_id','=','working_stations.location_id')
                                ->join('admin_hierarchies','admin_hierarchies.admin_hierarchy_id','=','working_stations.admin_hierarchy_id')
                                ->select('working_stations.*','geographical_locations.location_name','admin_hierarchies.admin_hierarch_name')
                                ->where('working_stations.working_station_id', '=',$working_station_id)
                                ->get();

            if (sizeof($workingStations) > 0) 
            {
                $respose =[
                    'data' => $workingStations,
                    'statusCode'=> 200
                ];

                return response()->json($respose);

            }else{
                return response()
                ->json(['message' => 'No Working Station Found','statusCode'=> 400]);
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
     *     path="/api/workStations/{working_station_id}",
     *     summary="Update a work station",
     *     tags={"workStations"},
     *     @OA\Parameter(
     *         name="working_station_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="working_station_name", type="string"),
     *             @OA\Property(property="location_id", type="string"),
     *             @OA\Property(property="admin_hierarchy_id", type="string")
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
    public function update(Request $request, string $working_station_id)
    {
            $check_value = DB::select("SELECT working_station_name FROM working_stations WHERE LOWER(working_station_name) = LOWER('$request->working_station_name') and working_station_id != $working_station_id");

            if(sizeof($check_value) != 0)
            {
                $respose =[
                    'message' =>'Working Station Name Alraedy Exists',
                    'statusCode'=> 400
                ];
    
                return response()->json($respose);       
            }

        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Update Upload Types'))
        {
            $user_id = auth()->user()->id;
            try{
                $WorkingStations = WorkingStations::find($working_station_id);
                $WorkingStations->working_station_name  = $request->working_station_name;
                $WorkingStations->location_id  = $request->location_id;
                $WorkingStations->admin_hierarchy_id  = $request->admin_hierarchy_id;
                $WorkingStations->created_by = $user_id;
                $WorkingStations->update();

                $respose =[
                    'message' =>'Working Station Updated Successfully',
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
     *     path="/api/workStations/{working_station_id}",
     *     summary="Delete a work station",
     *     tags={"workStations"},
     *     @OA\Parameter(
     *         name="working_station_id",
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
    public function destroy(string $working_station_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Delete Upload Types'))
        {
            $delete = WorkingStations::find($working_station_id);
            if ($delete != null) {
                $delete->delete();
                
                $respose =[
                    'message'=> 'Working Station Blocked Successfuly',
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
