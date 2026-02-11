<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Services\LicenseService;
use App\Services\KeygenClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LicenseController extends Controller
{
    protected $licenseService;
    protected $keygenClient;

    public function __construct(LicenseService $licenseService, KeygenClient $keygenClient)
    {
        $this->licenseService = $licenseService;
        $this->keygenClient = $keygenClient;
    }

    /**
     * Display the license settings page.
     */
    public function index()
    {
        if (!auth()->user()->isAdmin()) {
            return abort(403);
        }

        $stats = $this->licenseService->getUsageStats();
        $isConfigured = $this->keygenClient->isConfigured();

        return view('backend.settings.license', compact('stats', 'isConfigured'));
    }

    /**
     * Activate a new license key.
     */
    public function activate(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'license_key' => 'required|string|min:10|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
       
        $result = $this->licenseService->activateLicense($request->license_key);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'],
            'code' => $result['code'] ?? 'ERROR',
        ], 400);
    }

    /**
     * Revalidate the current license.
     */
    public function revalidate(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $license = $this->licenseService->getCurrentLicense();

        if (!$license) {
            return response()->json([
                'success' => false,
                'message' => 'No active license found.',
            ], 400);
        }

        $result = $this->licenseService->revalidateLicense($license);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'cached' => $result['cached'] ?? false,
        ]);
    }

    /**
     * Remove/deactivate the current license.
     */
    public function remove(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $removed = $this->licenseService->removeLicense();

        if ($removed) {
            return response()->json([
                'success' => true,
                'message' => 'License removed successfully.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No active license to remove.',
        ], 400);
    }

    /**
     * Get current license status (for AJAX refresh).
     */
    public function status()
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $stats = $this->licenseService->getUsageStats();

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Sync users to Keygen.sh.
     */
    public function syncUsers(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $result = $this->licenseService->syncUsersToKeygen();

        // Always return detailed info for debugging
        return response()->json([
            'success' => $result['success'] ?? false,
            'message' => $result['success']
                ? 'Users synced to license server.'
                : ($result['error'] ?? 'Failed to sync users.'),
            'created' => $result['created'] ?? 0,
            'attached' => $result['attached'] ?? 0,
            'failed' => $result['failed'] ?? 0,
            'total' => $result['total'] ?? 0,
            'errors' => $result['errors'] ?? [],
        ], $result['success'] ? 200 : 400);
    }

    /**
     * Check if a new user can be created (API for AJAX validation).
     */
    public function checkUserLimit()
    {
        $result = $this->licenseService->canCreateUser();

        return response()->json($result);
    }
}