<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class MarketplaceService
{
    public function getApps(): array
    {
        return Cache::remember('marketplace_apps', 300, function () {
            $response = Http::timeout(10)->get('http://localhost:3000/marketplaces/apps.json');

            if (! $response->successful()) {
                return [];
            }

            $data = $response->json();

            return is_array($data) ? $data : [];
        });
    }
}