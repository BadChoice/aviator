<?php

namespace App\Services\AppStore;

use Illuminate\Support\Facades\Http;

class AppStoreSearch
{

    static string $url = 'https://itunes.apple.com/search';

    /**
     * @param string $keyword
     * @param string $country
     * @param $limit
     * @return array
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    public function search(string $keyword, string $country = 'US', int $limit = 200) : array {

        $result = Http::get(url: self::$url, query: [
            "term" => $keyword,
            "country" => $country,
            "entity" => "software",
            "limit" => $limit,
        ]);

        return $result->json();
    }

    public function rankingPositionFor(string $application_id, string $keyword, string $country = 'US', int $limit = 250) : ?int
    {
        $results = $this->search(keyword: $keyword, country: $country, limit: $limit);

        return collect($results['results'])->search(function($result) use ($application_id){
            return $result['trackId'] == $application_id;
        });
    }
}

