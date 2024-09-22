<?php

namespace App\Http\Controllers;

use App\Models\Import;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
class DataImportApiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($periodName)
    {
        $response['data'] = DB::select('
        SELECT t1.*
        FROM imports t1
        INNER JOIN (
            SELECT MAX(id) as latest_id
            FROM imports
            WHERE periodName = ?
            GROUP BY nrcNo
        ) t2 ON t1.id = t2.latest_id
        ORDER BY t1.id DESC', [$periodName]);

        return response($response, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    public function importPeriod(Request $request){
        $response['import_period']=Import::distinct()->orderBy('id','desc')->pluck('periodName');
        return response($response, 200);
    }


    public function importTxtFile(Request $request)
    {
        $request->validate([
            'txt_file' => 'required|mimes:txt',
        ]);

        $filePath = $request->file('txt_file')->store('imports');
        $content = Storage::get($filePath);

        // Split the content into lines
        $lines = explode("\n", $content);

        // Define column widths
        $columnWidths = [
            'pers_area' => 20,
            'pers_sub_area' => 15,
            'empl_no' => 8,
            'man_no' => 8,
            'name' => 20,
            'nrc_no' => 20,
            'period' => 6,
            'sub_total' => 10,
            'total' => 10,
            'no_emp' => 2,
            'no_rec' => 2,
        ];

        // Calculate total width required
        $totalWidth = array_sum($columnWidths) + count($columnWidths) - 1; // Adding spaces between columns

        // Prepare to capture formatted lines
        $formattedLines = [];

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip empty lines or lines that don't meet the minimum width requirement
            if (strlen($line) < $totalWidth) {
                continue;
            }

            // Extract data for each column based on fixed widths
            $start = 0;
            $data = [];

            foreach ($columnWidths as $column => $width) {
                // Adjusting start position to account for extra spaces
                $data[$column] = trim(substr($line, $start, $width));
                $start += $width + 1; // Adding 1 for the space after each column
            }

            // Store the data only if the column data is not empty
            if (array_filter($data)) {
                $formattedLines[] = $data;
            }
        }

        // Debugging: show formatted lines
        dd($formattedLines);
    }




     // Add this for HTTP requests

//    public function allDataGetForNet(Request $request)
//    {
//        $page = (int) $request->input('page', 1);
//        $pageSize = (int) $request->input('pageSize', 10);
//
//        // Initialize query
//        $query = Import::query();
//
//
//        if ($request->filled('currentPeriod') && $request->filled('previousPeriod') == null) {
//            $query->where('periodName', $request->currentPeriod);
//        }
//
//        // Apply search filter if provided
//        if ($request->filled('search')) {
//            $query->where(function ($q) use ($request) {
//                $q->where('province', 'like', "%{$request->search}%")
//                    ->orWhere('district', 'like', "%{$request->search}%")
//                    ->orWhere('ministry', 'like', "%{$request->search}%")
//                    ->orWhere('employeeNo', 'like', "%{$request->search}%")
//                    ->orWhere('nrcNo', 'like', "%{$request->search}%");
//            });
//        }
//        $query->orderBy('id', 'desc');
//        // Paginate results
//        $data = $query->paginate($pageSize, ['*'], 'page', $page);
//
//        // Fetch phone numbers from external API
//        $apiUrl = env('API_UGZAMBIA_LOCAL') . 'api/v1/member-checking';
//
//        try {
//            $localResponse = Http::get($apiUrl);
//            $localPhonesData = $localResponse->json();
//
//            if (isset($localPhonesData['data'])) {
//                $localPhones = $localPhonesData['data']; // Array of phone numbers from external API
//            } else {
//                $localPhones = [];
//            }
//        } catch (\Exception $e) {
//            return response()->json([
//                'status' => 500,
//                'message' => 'Failed to fetch phone numbers from external API.',
//                'error' => $e->getMessage()
//            ]);
//        }
//
//        // Add matching status to each item
//        $dataWithStatus = $data->map(function ($item) use ($localPhones) {
//            $item->isMatching = in_array($item->nrcNo, $localPhones);
//            return $item;
//        });
//
//
//        // Return response with paginated data
//        return response()->json([
//            'total_data' => $dataWithStatus,
//            'total_count' => $data->total(),
//            'current_page' => $data->currentPage(),
//            'last_page' => $data->lastPage()
//        ]);
//    }


    public function allDataGetForNet(Request $request)
    {

        $showNonMatchingOnly = $request->input('showNonMatchingOnly');
        $page = (int) $request->input('page', 1);
        $pageSize = (int) $request->input('pageSize', 10);


        $query = Import::query();

        if ($request->filled('currentPeriod') && $request->filled('previousPeriod')) {
            $currentPeriodNrcNos = Import::where('periodName', $request->currentPeriod)
                ->pluck('nrcNo')
                ->toArray();

            $query->where('periodName', $request->previousPeriod);
        } else {
            // If no periods are provided, fetch all data and skip matching logic
            $currentPeriodNrcNos = [];
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('province', 'like', "%{$request->search}%")
                    ->orWhere('district', 'like', "%{$request->search}%")
                    ->orWhere('ministry', 'like', "%{$request->search}%")
                    ->orWhere('employeeNo', 'like', "%{$request->search}%")
                    ->orWhere('nrcNo', 'like', "%{$request->search}%");
            });
        }

        $query->orderBy('id', 'desc');

        if ($request->filled('currentPeriod') && $request->filled('previousPeriod')) {
            if ($showNonMatchingOnly == 'true'){
                $allData = $query->get();
                $dataWithStatus = $allData->map(function ($item) use ($currentPeriodNrcNos) {
                    $item->isMatching = in_array($item->nrcNo, $currentPeriodNrcNos);
                    return $item;
                });
                    $dataWithStatus = $dataWithStatus->filter(function ($item) {
                        return !$item->isMatching;
                    });

                    return response()->json([
                        'total_data' => $dataWithStatus->values(),
                        'total_count' => $dataWithStatus->count(),
                        'current_page' => 1,
                        'last_page' => 1 ,
                    ]);
            }else{
                $data = $query->paginate($pageSize, ['*'], 'page', $page);
                $dataWithStatus = $data->map(function ($item) use ($currentPeriodNrcNos) {
                    $item->isMatching = in_array($item->nrcNo, $currentPeriodNrcNos) ? true : false;
                    return $item;
                });
            }
        } else {
            $data = $query->paginate($pageSize, ['*'], 'page', $page);
            $dataWithStatus = $data->map(function ($item) {
                $item->isMatching = true;
                return $item;
            });
        }


        return response()->json([
            'total_data' => $dataWithStatus,
            'total_count' => $data->total(),
            'current_page' => $data->currentPage(),
            'last_page' => $data->lastPage()
        ]);
    }






    public function duplicateData()
    {

        $data['total_data'] = Import::count();
        $data['total_duplicate_data'] =  DB::table('imports')
            ->select('nrcNo')
            ->groupBy('nrcNo')
            ->having(DB::raw('COUNT(*)'), '>', 1)
            ->count();

        return response()->json($data);
    }


    public function allocationMissingUserSearch(Request $request)
    {
        $distinctPeriods = Import::distinct()
            ->orderBy('id', 'desc')
            ->pluck('periodName')
            ->filter(function ($period) {
                return preg_match('/^\d{6}$/', $period);
            })
            ->take(2)
            ->values();

        if ($distinctPeriods->count() < 2) {
            return response()->json([
                'error' => [
                    'message' => 'Not enough periods available.',
                    'code' => 1
                ]
            ], 400);
        }
        $latestPeriod = $distinctPeriods->get(0);
        $secondLatestPeriod = $distinctPeriods->get(1);
        $nrcNo = $request->input('nrc_no');
        $latestPeriodRecords = Import::where('periodName', $latestPeriod)
            ->where('nrcNo', $nrcNo)
            ->get();
        $secondLatestPeriodRecords = Import::where('periodName', $secondLatestPeriod)
            ->where('nrcNo', $nrcNo)
            ->get();
        $isMatching = $secondLatestPeriodRecords->isNotEmpty() && $latestPeriodRecords->isEmpty();
        return response()->json([
            'is_matching' => !$isMatching
        ]);


    }

    public function importLatestPeriod(Request $request){
        $response['import_period'] = Import::distinct()
            ->orderBy('id', 'desc')
            ->pluck('periodName')
            ->filter(function ($period) {
                return preg_match('/^\d{6}$/', $period);
            })
            ->first();
        return response($response, 200);
    }

}
