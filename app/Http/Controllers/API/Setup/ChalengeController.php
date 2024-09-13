<?php

namespace App\Http\Controllers\API\Setup;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Chalenge;
use Illuminate\Support\Str;
use Exception;
use Validator;
use DB;

class ChalengeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:Setup Modules|Create Chalenge|Create Chalenge|Update Chalenge|Update Chalenge|Delete Chalenge', ['only' => ['index','create','store','update','destroy']]);
    }

    public function index()
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('View Chalenge'))
        {
            $chalenges =DB::table('chalenges')->get();

            $respose =[
                'data' => $chalenges,
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
            'chalenge_file' => 'required|mimes:jpg,jpeg,png,pdf,docx,txt|max:2048',
            'chalenge' => 'required|string',
            'status' => 'required|string',
        ]);

        // Check if the chalenge already exists
        $check_value = DB::select("SELECT chalenge FROM chalenges WHERE LOWER(chalenge) = LOWER('$request->chalenge')");

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
            if ($request->hasFile('chalenge_file')) {
                $file = $request->file('chalenge_file');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('uploads/chalenges', $fileName, 'public');
            }
                     

            // Create the new chalenge
            $chalenge = DB::table('chalenges')->insert([
                'user_id' => $request->user_id,
                'chalenge' => $request->chalenge,
                'chalenge_file' => $filePath, // Store the file path or file name
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

public function show(string $chalenge_id)
{
    if (auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('View Chalenge')) {
        $chalenge = DB::table('chalenges')
                        ->select('chalenges.*')
                        ->where('chalenges.chalenge_id', '=', $chalenge_id)
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

/*public function update(Request $request, string $chalenge_id)
{
    if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Update Chalenge')) 
    {
        // Check if the chalenge exists
        $chalenge = DB::table('chalenges')->where('chalenge_id', $chalenge_id)->first();

        if (!$chalenge) {
            return response()->json([
                'message' => 'Chalenge not found',
                'statusCode' => 404
            ]);
        }

        // Validate the incoming data
        $request->validate([
            'chalenge_file' => 'nullable|mimes:jpg,jpeg,png,pdf,docx,txt|max:2048',
            'chalenge' => 'required|string',
            'status' => 'required|string',
        ]);

        // Check if another chalenge with the same name exists
        $check_value = DB::table('chalenges')
            ->whereRaw('LOWER(chalenge) = ?', [strtolower($request->chalenge)])
            ->where('chalenge_id', '!=', $chalenge_id)
            ->exists();

        if ($check_value) {
            return response()->json([
                'message' => 'Chalenge Name Already Exists',
                'statusCode' => 400
            ]);
        }

        try {
            $filePath = $chalenge->chalenge_file;

            // Handle file upload
            if ($request->hasFile('chalenge_file')) {
                $file = $request->file('chalenge_file');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('uploads/chalenges', $fileName, 'public');
            }

            // Update the chalenge
            DB::table('chalenges')->where('chalenge_id', $chalenge_id)->update([
                'chalenge' => $request->chalenge,
                'chalenge_file' => $filePath,
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

    public function destroy(string $chalenge_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Delete chalenge'))
        {
            $delete = chalenge::find($chalenge_id);
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