<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class GooglePlacesService
{
    private Client $client;
    private string $apiKey;

    public function __construct()
    {
        $this->client = new Client(['timeout' => 10]);
        $this->apiKey = config('services.google.places_key', '');
    }

    public function search(string $branche, string $stadt, ?string $pageToken = null): array
    {
        if (empty($this->apiKey) || $this->apiKey === 'dein_key_hier') {
            Log::warning('GooglePlacesService: Kein API-Key konfiguriert.');
            return ['results' => [], 'next_page_token' => null];
        }

        try {
            $data     = $this->textSearch($branche, $stadt, $pageToken);
            $placeIds = $data['place_ids'];
            $token    = $data['next_page_token'];
        } catch (\Exception $e) {
            Log::error('GooglePlacesService textSearch: ' . $e->getMessage());
            return ['results' => [], 'next_page_token' => null];
        }

        $results = [];
        foreach ($placeIds as $placeId) {
            try {
                $detail = $this->placeDetail($placeId, $branche, $stadt);
                if ($detail) {
                    $results[] = $detail;
                }
            } catch (\Exception $e) {
                Log::warning("GooglePlacesService detail({$placeId}): " . $e->getMessage());
            }
        }

        return ['results' => $results, 'next_page_token' => $token];
    }

    public function autocompleteCity(string $input): array
    {
        if (empty($this->apiKey) || strlen($input) < 2) {
            return [];
        }

        try {
            $response = $this->client->get('https://maps.googleapis.com/maps/api/place/autocomplete/json', [
                'query' => [
                    'input'    => $input,
                    'types'    => '(cities)',
                    'language' => 'de',
                    'key'      => $this->apiKey,
                ],
            ]);

            $data = json_decode((string) $response->getBody(), true);

            if (($data['status'] ?? '') !== 'OK') {
                return [];
            }

            return array_map(
                fn($p) => $p['structured_formatting']['main_text'] ?? $p['description'],
                array_slice($data['predictions'] ?? [], 0, 6)
            );
        } catch (\Exception $e) {
            Log::warning('GooglePlacesService autocompleteCity: ' . $e->getMessage());
            return [];
        }
    }

    private function textSearch(string $branche, string $stadt, ?string $pageToken = null): array
    {
        $params = [
            'key'      => $this->apiKey,
            'language' => 'de',
        ];

        if ($pageToken) {
            $params['pagetoken'] = $pageToken;
        } else {
            $params['query'] = "{$branche} in {$stadt}";
        }

        $response = $this->client->get('https://maps.googleapis.com/maps/api/place/textsearch/json', [
            'query' => $params,
        ]);

        $data = json_decode((string) $response->getBody(), true);

        if (($data['status'] ?? '') !== 'OK') {
            Log::warning('GooglePlacesService textSearch status: ' . ($data['status'] ?? 'unknown'));
            return ['place_ids' => [], 'next_page_token' => null];
        }

        return [
            'place_ids'       => array_column($data['results'] ?? [], 'place_id'),
            'next_page_token' => $data['next_page_token'] ?? null,
        ];
    }

    private function placeDetail(string $placeId, string $branche, string $stadt): ?array
    {
        $response = $this->client->get('https://maps.googleapis.com/maps/api/place/details/json', [
            'query' => [
                'place_id' => $placeId,
                'fields'   => 'name,formatted_address,formatted_phone_number,website,rating,user_ratings_total,business_status,photos',
                'key'      => $this->apiKey,
                'language' => 'de',
            ],
        ]);

        $data = json_decode((string) $response->getBody(), true);

        if (($data['status'] ?? '') !== 'OK') {
            return null;
        }

        $r = $data['result'] ?? [];

        if (($r['business_status'] ?? '') === 'CLOSED_PERMANENTLY') {
            return null;
        }

        $websiteUrl = $r['website'] ?? null;

        $photoUrl = null;
        if (!empty($r['photos'][0]['photo_reference'])) {
            $photoUrl = 'https://maps.googleapis.com/maps/api/place/photo'
                . '?maxwidth=400'
                . '&photo_reference=' . $r['photos'][0]['photo_reference']
                . '&key=' . $this->apiKey;
        }

        return [
            'place_id'    => $placeId,
            'name'        => $r['name'] ?? 'Unbekannt',
            'adresse'     => $r['formatted_address'] ?? '',
            'stadt'       => $stadt,
            'branche'     => $branche,
            'telefon'     => $r['formatted_phone_number'] ?? null,
            'website_url' => $websiteUrl,
            'has_website' => !empty($websiteUrl),
            'rating'      => isset($r['rating']) ? (float) $r['rating'] : null,
            'bewertungen' => isset($r['user_ratings_total']) ? (int) $r['user_ratings_total'] : null,
            'photo_url'   => $photoUrl,
        ];
    }
}
