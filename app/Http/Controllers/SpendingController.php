<?php

namespace App\Http\Controllers;

use App\Services\GoogleAdsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Exception;

class SpendingController extends Controller
{
    protected $googleAdsService;

    public function __construct(GoogleAdsService $googleAdsService)
    {
        $this->googleAdsService = $googleAdsService;
    }

    /**
     * Get spending data for a customer within a date range
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        // Validate API key
        $apiKey = $request->get('key');
        $expectedApiKey = config('services.google_ads.api_key');

        if (!$apiKey || $apiKey !== $expectedApiKey) {
            return response()->json([
                'error' => 'Invalid or missing API key'
            ], 401);
        }


        // Get and validate customer ID (remove dashes if present)
        $customerId = str_replace('-', '', $request->get('customer_id'));
        if (!$customerId) {
            return response()->json([
                'error' => 'customer_id parameter is required'
            ], 400);
        }

        if (!in_array($customerId, config('services.google_ads.customer_ids'))) {
            return response()->json([
                'error' => 'Invalid customer ID'
            ], 400);
        }


        // dd($customerId);

        // Get date range (default to current day if not provided)
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        if (!$startDate) {
            $startDate = Carbon::today()->format('Y-m-d');
        }

        if (!$endDate) {
            $endDate = Carbon::today()->format('Y-m-d');
        }

        // Validate date format
        try {
            $startDateCarbon = Carbon::createFromFormat('Y-m-d', $startDate);
            $endDateCarbon = Carbon::createFromFormat('Y-m-d', $endDate);

            if ($startDateCarbon->gt($endDateCarbon)) {
                return response()->json([
                    'error' => 'start_date must be before or equal to end_date'
                ], 400);
            }
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Invalid date format. Use YYYY-MM-DD format'
            ], 400);
        }

        try {
            // Get spending data from service
            $data = $this->googleAdsService->getDailySpend($customerId, $startDate, $endDate);



            return response()->json([
                'success' => true,
                'customer_id' => $customerId,
                'date_range' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
                'totals' => $data['totals'],
                'daily_breakdown' => $data['daily_breakdown'],
                'count' => $data['count'],
            ]);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'An error occurred while fetching spending data',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

