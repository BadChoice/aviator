<?php

namespace App\Services\AppStore;

use Illuminate\Support\Facades\Http;

class AppStoreSearch
{
    public static string $url = 'https://itunes.apple.com/search';

    /**
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    public function search(string $keyword, string $country = 'US', int $limit = 200): array
    {

        $result = Http::get(url: self::$url, query: [
            'term' => $keyword,
            'country' => $country,
            'entity' => 'software',
            'limit' => $limit,
        ]);

        return $result->json();
    }

    public function rankingPositionFor(string $application_id, string $keyword, string $country = 'US', int $limit = 250): ?int
    {
        $results = $this->search(keyword: $keyword, country: $country, limit: $limit);

        return collect($results['results'])->search(function ($result) use ($application_id) {
            return $result['trackId'] == $application_id;
        });
    }

    /**
     * Returns detailed info for a given app within a keyword search, including position and ratings.
     *
     * @return array{position:int|null, average_rating:float|null, rating_count:int|null}
     */
    public function trackInfoFor(string $application_id, string $keyword, string $country = 'US', int $limit = 250): array
    {
        $results = $this->search(keyword: $keyword, country: $country, limit: $limit);

        $collection = collect($results['results']);

        $position = $collection->search(function ($result) use ($application_id) {
            return $result['trackId'] == $application_id;
        });

        $item = $position !== false ? $collection->get($position) : null;

        return [
            'position' => $position !== false ? (int) $position : null,
            'average_rating' => $item['averageUserRating'] ?? null,
            'rating_count' => $item['userRatingCount'] ?? null,
        ];
    }
}
