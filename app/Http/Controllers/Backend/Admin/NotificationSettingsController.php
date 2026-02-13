<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Services\NotificationSettingsService;
use Illuminate\Http\Request;

class NotificationSettingsController extends Controller
{
    protected $notificationSettingsService;

    public function __construct(NotificationSettingsService $notificationSettingsService)
    {
        $this->notificationSettingsService = $notificationSettingsService;
    }

    /**
     * Display the notification settings page
     */
    public function index()
    {
        if (!auth()->user()->isAdmin()) {
            return abort(403);
        }

        $settings = $this->notificationSettingsService->getSettingsForUI();

        return view('backend.settings.notifications', compact('settings'));
    }

    /**
     * Update a single notification setting
     */
    public function update(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'module' => 'required|string|max:50',
            'event' => 'required|string|max:100',
            'channel' => 'required|string|max:30',
            'enabled' => 'required|boolean',
        ]);

        try {
            $this->notificationSettingsService->updateSetting(
                $request->module,
                $request->event,
                $request->channel,
                $request->enabled,
                auth()->id(),
                $request->ip()
            );

            return response()->json([
                'success' => true,
                'message' => 'Setting updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update setting',
            ], 500);
        }
    }

    /**
     * Bulk update all settings for a module
     */
    public function bulkUpdateModule(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'module' => 'required|string|max:50',
            'enabled' => 'required|boolean',
        ]);

        try {
            $this->notificationSettingsService->bulkUpdateModule(
                $request->module,
                $request->enabled,
                auth()->id(),
                $request->ip()
            );

            return response()->json([
                'success' => true,
                'message' => 'Module settings updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update module settings',
            ], 500);
        }
    }

    /**
     * Bulk update all settings for a channel
     */
    public function bulkUpdateChannel(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'channel' => 'required|string|max:30',
            'enabled' => 'required|boolean',
        ]);

        try {
            $this->notificationSettingsService->bulkUpdateChannel(
                $request->channel,
                $request->enabled,
                auth()->id(),
                $request->ip()
            );

            return response()->json([
                'success' => true,
                'message' => 'Channel settings updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update channel settings',
            ], 500);
        }
    }

    /**
     * Display the audit log
     */
    public function auditLog()
    {
        if (!auth()->user()->isAdmin()) {
            return abort(403);
        }

        $logs = $this->notificationSettingsService->getAuditLog(20);

        return view('backend.settings.notification_audit_log', compact('logs'));
    }
}
