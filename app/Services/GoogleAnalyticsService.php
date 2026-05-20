<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GoogleAnalyticsService
{
    public function getReport(string $startDate = '30daysAgo', string $endDate = 'today'): array
    {
        $propertyId = setting('ga4_property_id');
        $credentialsPath = storage_path('app/analytics/service-account-credentials.json');
        if (! $propertyId || ! file_exists($credentialsPath)) return ['error' => 'GA4 not configured'];

        return Cache::remember('ga4_report_'.$startDate.'_'.$endDate, 3600, function () use ($propertyId, $credentialsPath, $startDate, $endDate) {
            try {
                $client = new \Google\Client();
                $client->setAuthConfig($credentialsPath);
                $client->addScope('https://www.googleapis.com/auth/analytics.readonly');
                $analytics = new \Google\Service\AnalyticsData($client);
                $property = 'properties/'.$propertyId;

                $response = $analytics->properties->runReport($property, new \Google\Service\AnalyticsData\RunReportRequest([
                    'dateRanges' => [['startDate' => $startDate, 'endDate' => $endDate]],
                    'dimensions' => [['name' => 'date']],
                    'metrics' => [['name' => 'sessions'], ['name' => 'activeUsers'], ['name' => 'screenPageViews'], ['name' => 'bounceRate'], ['name' => 'averageSessionDuration']],
                ]));

                $topPages = $analytics->properties->runReport($property, new \Google\Service\AnalyticsData\RunReportRequest([
                    'dateRanges' => [['startDate' => $startDate, 'endDate' => $endDate]],
                    'dimensions' => [['name' => 'pagePath'], ['name' => 'pageTitle']],
                    'metrics' => [['name' => 'screenPageViews'], ['name' => 'averageSessionDuration']],
                    'limit' => 10,
                ]));

                $topCountries = $analytics->properties->runReport($property, new \Google\Service\AnalyticsData\RunReportRequest([
                    'dateRanges' => [['startDate' => $startDate, 'endDate' => $endDate]],
                    'dimensions' => [['name' => 'country']],
                    'metrics' => [['name' => 'sessions'], ['name' => 'activeUsers']],
                    'limit' => 10,
                ]));

                $browsers = $analytics->properties->runReport($property, new \Google\Service\AnalyticsData\RunReportRequest([
                    'dateRanges' => [['startDate' => $startDate, 'endDate' => $endDate]],
                    'dimensions' => [['name' => 'browser']],
                    'metrics' => [['name' => 'sessions']],
                    'limit' => 5,
                ]));

                return [
                    'overview' => $this->parseReport($response),
                    'top_pages' => $this->parseReport($topPages),
                    'top_countries' => $this->parseReport($topCountries),
                    'browsers' => $this->parseReport($browsers),
                ];
            } catch (\Throwable $e) {
                Log::error('GA4 Error: '.$e->getMessage());
                return ['error' => $e->getMessage()];
            }
        });
    }

    private function parseReport($response): array
    {
        $results = [];
        foreach ($response->getRows() ?? [] as $row) {
            $item = [];
            foreach ($row->getDimensionValues() as $i => $dim) $item['dim_'.$i] = $dim->getValue();
            foreach ($row->getMetricValues() as $i => $metric) $item['metric_'.$i] = $metric->getValue();
            $results[] = $item;
        }
        return $results;
    }
}
