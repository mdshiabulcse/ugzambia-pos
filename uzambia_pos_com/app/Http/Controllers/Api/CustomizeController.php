<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CustomizeController extends Controller
{
    public function AllocationUserSearch(Request $request)
    {
        try {
            $period_id = User::distinct()
                ->orderBy('id', 'desc')
                ->pluck('period_id')
                ->filter(function ($period) {
                    return preg_match('/^\d{6}$/', $period);
                })
                ->first();

            if (!$period_id) {
                return response()->json([
                    'error' => [
                        'message' => 'No valid period_id found.',
                        'code' => 2
                    ]
                ], 404);
            }

            $query = User::where('period_id', $period_id);

            if ($request->has('filters') && !empty($request->input('filters'))) {
                $filterString = $request->input('filters');
                $query->whereRaw($filterString);
            }

            $users = $query->select('id', 'name', 'phone', 'period_id')->limit(5)->get();
            if ($users->isEmpty() || !$users->contains('period_id', $period_id)) {
                return response()->json([
                    'error' => [
                        'message' => 'No data found matching the specified Member.',
                        'code' => 2
                    ]
                ], 404);
            }
            return response()->json(['data' => $users], 200);

        } catch (\Exception $e) {
            Log::error('AllocationUserSearch Error: ' . $e->getMessage());
            return response()->json([
                'error' => [
                    'message' => 'An error occurred while processing your request.',
                    'code' => 1
                ]
            ], 500);
        }
    }




}
