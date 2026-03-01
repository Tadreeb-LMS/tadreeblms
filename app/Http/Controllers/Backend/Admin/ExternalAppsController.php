<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\EnvManagerTrait;
use App\Models\ExternalApp;
use App\Services\ExternalApps\ExternalAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ExternalAppsController extends Controller
{
    use EnvManagerTrait;

    protected $externalAppService;

    public function __construct(ExternalAppService $externalAppService)
    {
        $this->externalAppService = $externalAppService;
        $this->middleware('permission:settings_access');
    }

    /**
     * Display a listing of external apps
     */
    public function index()
    {
        if (!auth()->user()->isAdmin()) {
            return abort(403);
        }

        $apps = $this->externalAppService->getInstalledApps();

        return view('backend.settings.external-apps.index', compact('apps'));
    }

    /**
     * Show the form for creating a new external app
     */
    public function create()
    {
        if (!auth()->user()->isAdmin()) {
            return abort(403);
        }

        return view('backend.settings.external-apps.create');
    }

    /**
     * Store a newly created external app in storage
     */
    public function store(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            return abort(403);
        }

        $request->validate([
            'zip_file' => 'required|file|mimes:zip|max:102400', // Max 100MB
        ], [
            'zip_file.required' => 'Please upload a zip file',
            'zip_file.mimes'    => 'File must be a zip archive',
            'zip_file.max'      => 'File size cannot exceed 100MB',
        ]);

        try {
            $zipFile    = $request->file('zip_file');
            $moduleName = $request->input('module_name');

            // Auto-collect module name from zip file name if not provided
            if (!$moduleName) {
                $fileName   = $zipFile->getClientOriginalName();
                $moduleName = pathinfo($fileName, PATHINFO_FILENAME);
                $moduleName = \Illuminate\Support\Str::slug($moduleName);
            }

            if (empty($moduleName)) {
                return redirect()->back()->with('error', 'Could not determine module name from file name. Please rename the file and try again.');
            }

            $result = $this->externalAppService->uploadAndInstall($zipFile, $moduleName);

            if ($result['success']) {
                return redirect()->route('admin.external-apps.index')
                    ->with('success', $result['message']);
            } else {
                return redirect()->back()
                    ->with('error', $result['message']);
            }

        } catch (\Exception $e) {
            Log::error('Error uploading external app', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->with('error', 'An error occurred while uploading the module. Please try again.');
        }
    }

    /**
     * Display the specified external app
     */
    public function show($slug)
    {
        if (!auth()->user()->isAdmin()) {
            return abort(403);
        }

        $app = $this->externalAppService->getApp($slug);

        return view('backend.settings.external-apps.show', compact('app'));
    }

    /**
     * Toggle enabled status of external app
     */
    public function toggleStatus(Request $request, $slug)
    {
        if (!auth()->user()->isAdmin()) {
            return abort(403);
        }

        try {
            $enabled = $request->input('enabled') === 'true' || $request->input('enabled') === 1;
            $app     = $this->externalAppService->toggleStatus($slug, $enabled);

            return response()->json([
                'success' => true,
                'message' => $enabled ? 'Module enabled successfully' : 'Module disabled successfully',
                'app'     => $app,
            ]);

        } catch (\Exception $e) {
            Log::error('Error toggling external app status', [
                'slug'  => $slug,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Show configuration form for external app.
     * Credentials are read from the module's own .env file — not the main .env.
     */
    public function editConfig($slug)
    {
        if (!auth()->user()->isAdmin()) {
            return abort(403);
        }

        $app       = $this->externalAppService->getApp($slug);
        $moduleEnv = $this->externalAppService->getModuleEnvAll($slug);

        return view('backend.settings.external-apps.configure', compact('app', 'moduleEnv'));
    }

    /**
     * Update configuration for external app.
     * Credentials are written to the module's own .env file — not the main .env.
     */
    public function updateConfig(Request $request, $slug)
    {
        if (!auth()->user()->isAdmin()) {
            return abort(403);
        }

        try {
            $app  = $this->externalAppService->getApp($slug);
            $data = $request->except('_token');

            // Validate before saving
            $this->externalAppService->validateConfiguration($slug, $data);

            // Write credentials to the module's .env
            $this->externalAppService->setModuleEnv($slug, $data);

            // Touch the DB record so last_updated_at is current
            $app->update(['is_setup' => true, 'last_updated_at' => now()]);

            return redirect()->route('admin.external-apps.edit-config', $slug)
                ->with('success', 'Configuration saved successfully.');

        } catch (\Exception $e) {
            Log::error('Error updating external app configuration', [
                'slug'  => $slug,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Delete/Uninstall external app (AJAX)
     */
    public function destroy($slug)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $result = $this->externalAppService->uninstall($slug);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Error uninstalling external app', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error uninstalling module: ' . $e->getMessage(),
            ], 500);
        }
    }
}
