<?php

namespace App\Http\Controllers\API\Setup;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChalengeSolution;
use Illuminate\Support\Str;
use Exception;
use Validator;
use DB;
class ChalengeSolutionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:Setup Modules|Create Chalenge|Create ChalengeSolution|Update ChalengeSolution|Update ChalengeSolution|Delete ChalengeSolution', ['only' => ['index','create','store','update','destroy']]);
    }

    public function index()
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('View ChalengeSolution'))
        {
            $chalengeSolutions =DB::table('chalengeSolutions')->get();

            $respose =[
                'data' => $chalengeSolutions,
                'statusCode'=> 200
            ];

            return response()->json($respose);
        }
        else{
            return response()
                ->json(['message' => 'Unauthorized','statusCode'=> 401]);
        }
    }

    public function store(Request $request)
{
    if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Create Chalenge')) 
    {
        $user_id = auth()->user()->id;

        // Validate user_id existence in the users table
        $user_exists = DB::table('users')->where('id', $request->user_id)->exists();

        if (!$user_exists) 
        {
            return response()->json([
                'message' => 'User ID does not exist',
                'statusCode' => 400
            ]);
        }

        // Validate the incoming file
        $request->validate([
            'Solution_file' => 'required|mimes:jpg,jpeg,png,pdf,docx,txt|max:2048',
            'chalengeSolution' => 'required|string',
            'status' => 'required|string',
        ]);

        // Check if the chalengeSolution already exists
        $check_value = DB::select("SELECT chalenge FROM chalengeSolutions WHERE LOWER(chalenge) = LOWER('$request->chalenge')");

        if (sizeof($check_value) != 0) 
        {
            $response = [
                'message' => 'Chalenge Name Already Exists',
                'statusCode' => 400
            ];

            return response()->json($response);       
        }

        try {
            // Handle file upload
            if ($request->hasFile('Solution_file')) {
                $file = $request->file('Solution_file');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('uploads/chalengeSolutions', $fileName, 'public');
            }
                     

            // Create the new chalenge
            $chalenge = DB::table('chalengeSolutions')->insert([
                'user_id' => $request->user_id,
                'chalenge' => $request->chalenge,
                'Solution_file' => $filePath, // Store the file path or file name
                'status' => $request->status,
                'created_by' => $user_id
            ]);

            $response = [
                'message' => 'Chalenge Inserted Successfully',
                'statusCode' => 201
            ];

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

public function show(string $Solution_id)
{
    if (auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('View Chalenge')) {
        $chalenge = DB::table('chalengeSolutions')
                        ->select('chalengeSolutions.*')
                        ->where('chalengeSolutions.Solution_id', '=', $Solution_id)
                        ->get();

        // Check if the chalenge exists
        if ($chalenge->isNotEmpty()) {
            $response = [
                'data' => $chalenge,
                'statusCode' => 200
            ];

            return response()->json($response);
        } else {
            return response()
                ->json(['message' => 'No Chalenge Found', 'statusCode' => 400]);
        }
    } else {
        return response()
            ->json(['message' => 'Unauthorized', 'statusCode' => 401]);
    }
}

/*public function update(Request $request, string $Solution_id)
{
    if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Update Chalenge')) 
    {
        // Check if the chalenge exists
        $chalenge = DB::table('chalengeSolutions')->where('Solution_id', $Solution_id)->first();

        if (!$chalenge) {
            return response()->json([
                'message' => 'Chalenge not found',
                'statusCode' => 404
            ]);
        }

        // Validate the incoming data
        $request->validate([
            'Solution_file' => 'nullable|mimes:jpg,jpeg,png,pdf,docx,txt|max:2048',
            'chalenge' => 'required|string',
            'status' => 'required|string',
        ]);

        // Check if another chalenge with the same name exists
        $check_value = DB::table('chalengeSolutions')
            ->whereRaw('LOWER(chalenge) = ?', [strtolower($request->chalenge)])
            ->where('Solution_id', '!=', $Solution_id)
            ->exists();

        if ($check_value) {
            return response()->json([
                'message' => 'Chalenge Name Already Exists',
                'statusCode' => 400
            ]);
        }

        try {
            $filePath = $chalenge->Solution_file;

            // Handle file upload
            if ($request->hasFile('Solution_file')) {
                $file = $request->file('Solution_file');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('uploads/chalengeSolutions', $fileName, 'public');
            }

            // Update the chalenge
            DB::table('chalengeSolutions')->where('Solution_id', $Solution_id)->update([
                'chalenge' => $request->chalenge,
                'Solution_file' => $filePath,
                'status' => $request->status
            ]);

            $response = [
                'message' => 'Chalenge Updated Successfully',
                'statusCode' => 200
            ];

            return response()->json($response);
        } 
        catch (Exception $e) 
        {
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
    */

    public function destroy(string $Solution_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Delete chalenge'))
        {
            $delete = chalenge::find($Solution_id);
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