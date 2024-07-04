<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserDetails;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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


        $wereHouse=Warehouse::pluck('id')->toArray();
        try {
            $importSuccess = true; // Set this based on your actual import logic

            if ($importSuccess) {
                foreach ($importData as $data){
                    // Check for duplicates based on period_id, name, and phone
                    $existingUser = User::where('period_id', $periodId)
                        ->where('name', $data['names'])
                        ->where('phone', $data['nrcNo'])
                        ->first();

                    if ($existingUser) {
                        // If user already exists, skip to the next iteration
                        continue;
                    }

                    $response=new User();
                    $response->period_id = $data['periodName'];
                    $response->name  = $data['names'];
                    $response->phone  = $data['nrcNo'];
                    $response->address  = $data['district'].','.$data['province'];
                    $response->warehouse_id  = 1;
                    $response->company_id  = 1;
                    $response->save();
                    foreach ($wereHouse as $wereHouseData) {
                        $userDetailsData=new UserDetails();
                        $userDetailsData->warehouse_id = $wereHouseData;
                        $userDetailsData->user_id  = $response->id;
                        $userDetailsData->opening_balance  = 0;
                        $userDetailsData->opening_balance_type  = 'receive';
                        $userDetailsData->save();
                    }

                }
                return response()->json(['data' => $response,'status' => 200, 'message' => 'Data imported successfully.']);
            } else {
                return response()->json(['success' => false, 'message' => 'Data import failed.'], 500);
            }
        } catch (\Exception $e) {
            Log::error('Data import error: ' . $e->getMessage()).$e->getLine();
            return response()->json(['success' => false, 'message' => 'An error occurred during data import.'], 500);
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
}
