<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserDetails;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Exception;
class ApiDataImportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $periodId = $request->input('period_id');
        $response = Http::get('https://api.ugzambia.net/api/data-import/'.$periodId);
        $importData = $response->json()['data'];

        $warehouseIds = Warehouse::pluck('id')->toArray();

        try {
            $importedUsers = []; // To keep track of imported users

            foreach ($importData as $data) {
                // Check if user already exists based on name, phone, and period_id
                $existingUser = User::where('name', $data['names'])
                    ->where('phone', $data['nrcNo'])
                    ->first();

                if ($existingUser) {
                    // If user already exists, skip to the next iteration
                    continue;
                }

                // Create new User record
                $newUser = new User();
                $newUser->period_id = $periodId;
                $newUser->name = $data['names'];
                $newUser->phone = $data['nrcNo'];
                $newUser->address = $data['district'] . ',' . $data['province'];
                $newUser->warehouse_id = 1; // Assuming default warehouse_id
                $newUser->company_id = 1; // Assuming default company_id
                $newUser->save();

                // Save details for each warehouse
                foreach ($warehouseIds as $warehouseId) {
                    $userDetails = new UserDetails();
                    $userDetails->warehouse_id = $warehouseId;
                    $userDetails->user_id = $newUser->id;
                    $userDetails->opening_balance = 0; // Example value, adjust as needed
                    $userDetails->opening_balance_type = 'receive'; // Example type, adjust as needed
                    $userDetails->save();
                }

                // Add the newly imported user to the list
                $importedUsers[] = $newUser;
            }

            return response()->json([
                'data' => $importedUsers,
                'status' => 200,
                'message' => 'Data imported successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error('Data import error: ' . $e->getMessage() . ' Line: ' . $e->getLine());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during data import.'
            ], 500);
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function totalMember()
    {
        $response['data']=User::count();
        return response()->json(['data' => $response,'status' => 200, 'message' => 'Data pull successfully.']);
    }

    public function duplicateDataRemove()
    {
        try {
            // Step 1: Identify Duplicate User IDs
            $duplicateUserIds = User::select('id')
                ->selectRaw('MAX(id) as max_id')
                ->from(DB::raw('(SELECT id, name, phone FROM users) as u'))
                ->groupBy('name', 'phone')
                ->havingRaw('COUNT(*) > 1')
                ->pluck('max_id');

            if ($duplicateUserIds->isEmpty()) {
                return response()->json(['message' => 'No duplicate users found.']);
            }

            // Step 2: Delete Related Records in UserDetails
            UserDetails::whereIn('user_id', $duplicateUserIds)->delete();

            // Step 3: Delete Duplicate Records in User
            User::whereIn('id', $duplicateUserIds)->delete();

            return response()->json(['message' => 'Duplicate users removed successfully.']);
        } catch (Exception $e) {
            // Log the error for debugging
            Log::error('Error removing duplicate users: ' . $e->getMessage());

            // Return a response with the error message
            return response()->json(['message' => 'An error occurred while removing duplicate users.', 'error' => $e->getMessage()], 500);
        }

//        // Find duplicates based on phone, name, and period_id
        $duplicateUsers = User::select('phone', 'name', 'period_id', DB::raw('COUNT(*) as count'))
            ->groupBy('phone', 'name')
            ->having('count', '>', 1) // Having more than 1 occurrence means duplicates
            ->get();
dd($duplicateUsers);
die();
//// Iterate through duplicates and delete except one (you can decide based on your logic)
//        foreach ($duplicateUsers as $duplicateUser) {
//            $usersToDelete = User::where('phone', $duplicateUser->phone)
//                ->where('name', $duplicateUser->name)
//                ->where('period_id', $duplicateUser->period_id)
//                ->orderBy('id', 'desc') // Order by id to keep the latest or earliest record
//                ->skip(1) // Skip the first record (keep one, delete the rest)
//                ->get();
//
//            foreach ($usersToDelete as $userToDelete) {
//                // Delete associated UserDetails records
//                UserDetails::where('user_id', $userToDelete->id)->delete();
//
//                // Delete the User record
//                $userToDelete->delete();
//            }
//        }
//
//// Return success message or do something else as needed
//        return response()->json([
//            'message' => 'Duplicate records and associated UserDetails deleted successfully.'
//        ]);
    }
}
