<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ZoomService
{
    protected $baseUrl = 'https://api.zoom.us/v2/';

    public function __construct()
    {
    }

    /**
     * Get OAuth Access Token using Server-to-Server OAuth.
     * Tokens are cached for 3500 seconds (slightly less than 1 hour expiry).
     */
    protected function getAccessToken()
    {
        return Cache::remember('zoom_access_token', 3500, function () {
            $accountId = config('zoom.account_id');
            $clientId = config('zoom.client_id');
            $clientSecret = config('zoom.client_secret');

            if (!$accountId || !$clientId || !$clientSecret) {
                Log::error('Zoom credentials missing in config.');
                return null;
            }

            $response = Http::asForm()->withBasicAuth($clientId, $clientSecret)->post('https://zoom.us/oauth/token', [
                'grant_type' => 'account_credentials',
                'account_id' => $accountId,
            ]);

            if ($response->successful()) {
                return $response->json()['access_token'];
            }

            Log::error('Zoom OAuth Token Error: ' . $response->body());
            return null;
        });
    }

    protected function request()
    {
        $token = $this->getAccessToken();
        if (!$token) {
            // Throw exception or handle gracefully?
            // For now, return a pending request that will likely fail if executed, 
            // but we can't chain well if token is null.
            // Better to throw exception.
             throw new \Exception('Failed to retrieve Zoom Access Token.');
        }

        return Http::withToken($token)->baseUrl($this->baseUrl);
    }

    public function getUser($email = 'me')
    {
        // 'me' only works for the authorized account owner in Server-to-Server OAuth?
        // Actually, Server-to-Server OAuth acts on behalf of the account.
        // We typically list users or use a specific user ID.
        // But let's try 'me' or list users.
        return $this->request()->get("users/{$email}");
    }
    
    public function getFirstUser() 
    {
        $response = $this->request()->get("users", ['page_size' => 1]);
        if($response->successful()) {
            $users = $response->json()['users'];
            return !empty($users) ? $users[0] : null;
        }
        return null;
    }

    public function createMeeting($userId, array $data)
    {
        return $this->request()->post("users/{$userId}/meetings", $data);
    }

    public function updateMeeting($meetingId, array $data)
    {
        return $this->request()->patch("meetings/{$meetingId}", $data);
    }

    public function getMeeting($meetingId)
    {
        return $this->request()->get("meetings/{$meetingId}");
    }

    public function deleteMeeting($meetingId)
    {
        return $this->request()->delete("meetings/{$meetingId}");
    }
}
