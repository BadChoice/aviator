<?php

namespace App\Services\AppleAds;

use App\Models\AppleAdsCampaign;
use Carbon\Carbon;
use Illuminate\Support\Str;

class AppleAdsRecommendation
{
    private AppleAds $appleAds;

    public function __construct()
    {
        $this->appleAds = AppleAds::make();
    }

    public function analyze(AppleAdsCampaign $campaign): array
    {
        $recommendations = [];

        $budgetRecommendations = $this->analyzeBudgetUtilization($campaign);
        $recommendations = array_merge($recommendations, $budgetRecommendations);

        $keywordRecommendations = $this->analyzeKeywordPerformance($campaign);
        $recommendations = array_merge($recommendations, $keywordRecommendations);

        $adRecommendations = $this->analyzeAdPerformance($campaign);
        $recommendations = array_merge($recommendations, $adRecommendations);

        return $recommendations;
    }

    private function analyzeBudgetUtilization(AppleAdsCampaign $campaign): array
    {
        $recommendations = [];

        if (!$campaign->daily_budget_amount) {
            return $recommendations;
        }

        try {
            $endDate = Carbon::now();
            $startDate = Carbon::now()->subDays(7);

            $metricsResponse = $this->appleAds->getCampaignMetrics($campaign->apple_campaign_id, [
                'startTime' => $startDate->toIso8601String(),
                'endTime' => $endDate->toIso8601String(),
                'selector' => [
                    'orderBy' => [
                        ['field' => 'localSpend', 'sortOrder' => 'DESCENDING']
                    ]
                ],
                'groupBy' => ['countryOrRegion'],
                'returnRowTotals' => true,
                'returnRecordsWithNoMetrics' => false,
            ]);

            $totals = $metricsResponse['data']['reportingDataResponse']['row'][0]['total'] ?? null;

            if ($totals && isset($totals['localSpend'])) {
                $dailySpend = $totals['localSpend']['amount'] / 7;
                $dailyBudget = (float) $campaign->daily_budget_amount;
                $utilizationRate = $dailySpend / $dailyBudget;

                if ($utilizationRate > 0.9) {
                    $recommendations[] = [
                        'id' => Str::uuid()->toString(),
                        'type' => 'budget',
                        'priority' => 'high',
                        'title' => 'Increase Daily Budget',
                        'description' => sprintf(
                            'Campaign is utilizing %.0f%% of daily budget (%.2f of %.2f). Consider increasing budget to avoid missing opportunities.',
                            $utilizationRate * 100,
                            $dailySpend,
                            $dailyBudget
                        ),
                        'action' => [
                            'type' => 'update_budget',
                            'new_daily_budget' => $dailyBudget * 1.2,
                        ],
                        'expected_impact' => 'Potential to increase reach by 20-30%',
                    ];
                } elseif ($utilizationRate < 0.5) {
                    $recommendations[] = [
                        'id' => Str::uuid()->toString(),
                        'type' => 'budget',
                        'priority' => 'low',
                        'title' => 'Consider Reducing Daily Budget',
                        'description' => sprintf(
                            'Campaign is only utilizing %.0f%% of daily budget (%.2f of %.2f). Budget could be reallocated elsewhere.',
                            $utilizationRate * 100,
                            $dailySpend,
                            $dailyBudget
                        ),
                        'action' => [
                            'type' => 'update_budget',
                            'new_daily_budget' => $dailySpend * 1.2,
                        ],
                        'expected_impact' => 'Optimize budget allocation',
                    ];
                }
            }
        } catch (\Exception $e) {
            // Silently skip if metrics unavailable
        }

        return $recommendations;
    }

    private function analyzeKeywordPerformance(AppleAdsCampaign $campaign): array
    {
        $recommendations = [];
        $targetCPA = 5.0;

        try {
            $adGroupsResponse = $this->appleAds->getAdGroups($campaign->apple_campaign_id);
            $adGroups = $adGroupsResponse['data'] ?? [];

            foreach ($adGroups as $adGroup) {
                $keywordsResponse = $this->appleAds->getKeywords($campaign->apple_campaign_id, $adGroup['id']);
                $keywords = $keywordsResponse['data'] ?? [];

                foreach ($keywords as $keyword) {
                    if ($keyword['status'] !== 'ACTIVE') {
                        continue;
                    }

                    $endDate = Carbon::now();
                    $startDate = Carbon::now()->subDays(30);

                    try {
                        $metricsResponse = $this->appleAds->getCampaignMetrics($campaign->apple_campaign_id, [
                            'startTime' => $startDate->toIso8601String(),
                            'endTime' => $endDate->toIso8601String(),
                            'selector' => [
                                'conditions' => [
                                    [
                                        'field' => 'keywordId',
                                        'operator' => 'EQUALS',
                                        'values' => [(string) $keyword['id']]
                                    ]
                                ]
                            ],
                            'returnRowTotals' => true,
                        ]);

                        $totals = $metricsResponse['data']['reportingDataResponse']['row'][0]['total'] ?? null;

                        if ($totals && isset($totals['installs'], $totals['avgCPA'])) {
                            $installs = $totals['installs'];
                            $avgCPA = $totals['avgCPA']['amount'];

                            if ($installs > 10 && $avgCPA < $targetCPA) {
                                $recommendations[] = [
                                    'id' => Str::uuid()->toString(),
                                    'type' => 'keyword',
                                    'priority' => 'medium',
                                    'title' => 'Increase Bid for High-Performing Keyword',
                                    'description' => sprintf(
                                        'Keyword "%s" has good performance (CPA: %.2f, %d installs). Increase bid to gain more traffic.',
                                        $keyword['text'],
                                        $avgCPA,
                                        $installs
                                    ),
                                    'action' => [
                                        'type' => 'update_keyword_bids',
                                        'campaign_id' => $campaign->apple_campaign_id,
                                        'adgroup_id' => $adGroup['id'],
                                        'keyword_id' => $keyword['id'],
                                        'new_bid' => ($keyword['bidAmount']['amount'] ?? 1) * 1.2,
                                    ],
                                    'expected_impact' => 'Increase traffic for high-converting keyword',
                                ];
                            } elseif ($totals['localSpend']['amount'] > 100 && $avgCPA > $targetCPA) {
                                $recommendations[] = [
                                    'id' => Str::uuid()->toString(),
                                    'type' => 'keyword',
                                    'priority' => 'high',
                                    'title' => 'Decrease Bid for Expensive Keyword',
                                    'description' => sprintf(
                                        'Keyword "%s" has high CPA (%.2f) with spend of %.2f. Consider reducing bid.',
                                        $keyword['text'],
                                        $avgCPA,
                                        $totals['localSpend']['amount']
                                    ),
                                    'action' => [
                                        'type' => 'update_keyword_bids',
                                        'campaign_id' => $campaign->apple_campaign_id,
                                        'adgroup_id' => $adGroup['id'],
                                        'keyword_id' => $keyword['id'],
                                        'new_bid' => ($keyword['bidAmount']['amount'] ?? 1) * 0.8,
                                    ],
                                    'expected_impact' => 'Reduce spend on underperforming keyword',
                                ];
                            }
                        }
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }
        } catch (\Exception $e) {
            // Silently skip if data unavailable
        }

        return $recommendations;
    }

    private function analyzeAdPerformance(AppleAdsCampaign $campaign): array
    {
        $recommendations = [];

        try {
            $adGroupsResponse = $this->appleAds->getAdGroups($campaign->apple_campaign_id);
            $adGroups = $adGroupsResponse['data'] ?? [];

            foreach ($adGroups as $adGroup) {
                $adsResponse = $this->appleAds->getAds($campaign->apple_campaign_id, $adGroup['id']);
                $ads = $adsResponse['data'] ?? [];

                foreach ($ads as $ad) {
                    if ($ad['status'] !== 'ENABLED') {
                        continue;
                    }

                    $endDate = Carbon::now();
                    $startDate = Carbon::now()->subDays(30);

                    try {
                        $metricsResponse = $this->appleAds->getCampaignMetrics($campaign->apple_campaign_id, [
                            'startTime' => $startDate->toIso8601String(),
                            'endTime' => $endDate->toIso8601String(),
                            'selector' => [
                                'conditions' => [
                                    [
                                        'field' => 'adId',
                                        'operator' => 'EQUALS',
                                        'values' => [(string) $ad['id']]
                                    ]
                                ]
                            ],
                            'returnRowTotals' => true,
                        ]);

                        $totals = $metricsResponse['data']['reportingDataResponse']['row'][0]['total'] ?? null;

                        if ($totals && isset($totals['impressions'], $totals['taps'], $totals['localSpend'])) {
                            $impressions = $totals['impressions'];
                            $taps = $totals['taps'];
                            $spend = $totals['localSpend']['amount'];
                            $ctr = $impressions > 0 ? ($taps / $impressions) : 0;

                            if ($ctr < 0.01 && $spend > 100) {
                                $recommendations[] = [
                                    'id' => Str::uuid()->toString(),
                                    'type' => 'ad',
                                    'priority' => 'medium',
                                    'title' => 'Pause Low-Performing Ad',
                                    'description' => sprintf(
                                        'Ad "%s" has low CTR (%.2f%%) with spend of %.2f. Consider pausing and creating a new variant.',
                                        $ad['name'],
                                        $ctr * 100,
                                        $spend
                                    ),
                                    'action' => [
                                        'type' => 'pause_ad',
                                        'campaign_id' => $campaign->apple_campaign_id,
                                        'adgroup_id' => $adGroup['id'],
                                        'ad_id' => $ad['id'],
                                    ],
                                    'expected_impact' => 'Stop wasting budget on underperforming creative',
                                ];
                            }
                        }
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }
        } catch (\Exception $e) {
            // Silently skip if data unavailable
        }

        return $recommendations;
    }

    public function apply(AppleAdsCampaign $campaign, array $recommendation): bool
    {
        try {
            $action = $recommendation['action'];

            switch ($action['type']) {
                case 'update_budget':
                    $this->appleAds->updateCampaign($campaign->apple_campaign_id, [
                        'dailyBudgetAmount' => [
                            'amount' => (string) $action['new_daily_budget'],
                            'currency' => $campaign->currency,
                        ]
                    ]);

                    $campaign->update([
                        'daily_budget_amount' => $action['new_daily_budget'],
                    ]);
                    break;

                case 'update_keyword_bids':
                    $this->appleAds->updateKeywordBid(
                        $action['campaign_id'],
                        $action['adgroup_id'],
                        $action['keyword_id'],
                        [
                            'bidAmount' => [
                                'amount' => (string) $action['new_bid'],
                                'currency' => $campaign->currency,
                            ]
                        ]
                    );
                    break;

                case 'pause_ad':
                    // Note: Apple Search Ads API may not support direct ad status updates
                    // This would need to be done through the campaign/adgroup level
                    throw new \Exception('Pausing ads is not supported via API. Please do this manually in Apple Search Ads dashboard.');

                default:
                    throw new \Exception('Unknown action type: ' . $action['type']);
            }

            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
