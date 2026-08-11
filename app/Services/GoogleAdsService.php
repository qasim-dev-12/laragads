<?php

namespace App\Services;

use Google\Ads\GoogleAds\Lib\V22\GoogleAdsClientBuilder;
use Google\Ads\GoogleAds\Lib\ConfigurationLoader;
use Google\Ads\GoogleAds\Lib\OAuth2TokenBuilder;
use Google\Ads\GoogleAds\V22\Services\SearchGoogleAdsStreamRequest;
use Exception;
use Illuminate\Support\Facades\Log;

class GoogleAdsService
{
    protected $client;

    /**
     * Initialize Google Ads client from configuration file
     */
    public function __construct()
    {
        try {
            $configFile = config('services.google_ads.config_file');
            $configurationLoader = new ConfigurationLoader();
            $configuration = $configurationLoader->fromFile($configFile);

            // Build OAuth2 credentials
            $oAuth2Credential = (new OAuth2TokenBuilder())
                ->withClientId($configuration->getConfiguration('clientId', 'OAUTH2'))
                ->withClientSecret($configuration->getConfiguration('clientSecret', 'OAUTH2'))
                ->withRefreshToken($configuration->getConfiguration('refreshToken', 'OAUTH2'))
                ->build();

            // Build Google Ads client
            $this->client = (new GoogleAdsClientBuilder())
                ->from($configuration)
                ->withOAuth2Credential($oAuth2Credential)
                ->build();
        } catch (Exception $e) {
            Log::error('Failed to initialize Google Ads client: ' . $e->getMessage());
            throw new Exception('Failed to initialize Google Ads client: ' . $e->getMessage());
        }
    }

    /**
     * Get daily spending for a customer within a date range
     *
     * @param string $customerId The Google Ads customer ID (without dashes)
     * @param string $fromDate Start date in YYYY-MM-DD format
     * @param string $toDate End date in YYYY-MM-DD format
     * @return array Array of spending data with detailed metrics
     * @throws Exception
     */
    public function getDailySpend(string $customerId, string $fromDate, string $toDate): array
    {
        try {
            $googleAdsServiceClient = $this->client->getGoogleAdsServiceClient();
            // dd($googleAdsServiceClient);

            // Build GAQL query to get spending data
            $query = sprintf(
                "SELECT
                    customer.id,
                    customer.descriptive_name,
                    customer.currency_code,
                    segments.date,
                    metrics.cost_micros,
                    metrics.impressions,
                    metrics.clicks,
                    metrics.conversions,
                    metrics.ctr,
                    metrics.average_cpc
                FROM customer
                WHERE segments.date BETWEEN '%s' AND '%s'
                ORDER BY segments.date DESC",
                $fromDate,
                $toDate
            );

            // Execute the query
            $stream = $googleAdsServiceClient->searchStream(
                SearchGoogleAdsStreamRequest::build($customerId, $query)
            );

            $spendings = [];
            $totalCost = 0;
            $totalImpressions = 0;
            $totalClicks = 0;
            $totalConversions = 0;
            $currencyCode = null;

            // Iterate through the stream responses
            foreach ($stream->readAll() as $response) {
                // Get results from each response
                foreach ($response->getResults() as $row) {
                    $cost = $row->getMetrics()->getCostMicros() / 1000000; // Convert micros to currency

                    // Get currency code from customer (should be the same for all rows)
                    if ($currencyCode === null && $row->getCustomer()->getCurrencyCode()) {
                        $currencyCode = $row->getCustomer()->getCurrencyCode();
                    }

                    $spending = [
                        'date' => $row->getSegments()->getDate(),
                        'customer_id' => $row->getCustomer()->getId(),
                        'customer_name' => $row->getCustomer()->getDescriptiveName(),
                        'currency_code' => $row->getCustomer()->getCurrencyCode(),
                        'cost' => round($cost, 2),
                        'cost_micros' => $row->getMetrics()->getCostMicros(),
                        'impressions' => $row->getMetrics()->getImpressions(),
                        'clicks' => $row->getMetrics()->getClicks(),
                        'conversions' => round($row->getMetrics()->getConversions(), 2),
                        'ctr' => $row->getMetrics()->getCtr() ? round($row->getMetrics()->getCtr() * 100, 2) : 0,
                        'average_cpc' => $row->getMetrics()->getAverageCpc() ? round($row->getMetrics()->getAverageCpc() / 1000000, 2) : 0,
                    ];

                    $spendings[] = $spending;
                    $totalCost += $cost;
                    $totalImpressions += $row->getMetrics()->getImpressions();
                    $totalClicks += $row->getMetrics()->getClicks();
                    $totalConversions += $row->getMetrics()->getConversions();
                }
            }

            return [
                'daily_breakdown' => $spendings,
                'totals' => [
                    'currency_code' => $currencyCode,
                    'cost' => round($totalCost, 2),
                    'impressions' => $totalImpressions,
                    'clicks' => $totalClicks,
                    'conversions' => round($totalConversions, 2),
                    'average_ctr' => $totalImpressions > 0 ? round(($totalClicks / $totalImpressions) * 100, 2) : 0,
                    'average_cpc' => $totalClicks > 0 ? round($totalCost / $totalClicks, 2) : 0,
                ],
                'count' => count($spendings),
            ];
        } catch (Exception $e) {
            Log::error('Google Ads API error: ' . $e->getMessage());
            throw new Exception('Failed to fetch daily spending: ' . $e->getMessage());
        }
    }
}

