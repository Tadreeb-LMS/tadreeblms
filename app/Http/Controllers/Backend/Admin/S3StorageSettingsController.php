<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Services\ExternalApps\ExternalAppService;
use Aws\S3\S3Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class S3StorageSettingsController extends Controller
{
    protected $externalAppService;

    public function __construct(ExternalAppService $externalAppService)
    {
        $this->externalAppService = $externalAppService;
    }

    /**
     * Show S3 storage settings form.
     */
    public function index()
    {
        if (!auth()->user()->isAdmin()) {
            return abort(403);
        }

        $settings = [
            'STORAGE_DRIVER'       => ExternalAppService::staticGetModuleEnv('external-storage', 'STORAGE_DRIVER') ?: 'local',
            'S3_ACCESS_KEY_ID'     => ExternalAppService::staticGetModuleEnv('external-storage', 'S3_ACCESS_KEY_ID') ?: '',
            'S3_SECRET_ACCESS_KEY' => ExternalAppService::staticGetModuleEnv('external-storage', 'S3_SECRET_ACCESS_KEY') ?: '',
            'S3_DEFAULT_REGION'    => ExternalAppService::staticGetModuleEnv('external-storage', 'S3_DEFAULT_REGION') ?: 'us-east-1',
            'S3_BUCKET'            => ExternalAppService::staticGetModuleEnv('external-storage', 'S3_BUCKET') ?: '',
            'S3_URL'               => ExternalAppService::staticGetModuleEnv('external-storage', 'S3_URL') ?: '',
            'S3_ENDPOINT'          => ExternalAppService::staticGetModuleEnv('external-storage', 'S3_ENDPOINT') ?: '',
            'S3_ROOT'              => ExternalAppService::staticGetModuleEnv('external-storage', 'S3_ROOT') ?: '',
        ];

        return view('backend.settings.s3-storage', compact('settings'));
    }

    /**
     * Save S3 storage settings to module .env file.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            return abort(403);
        }

        $request->validate([
            'storage_driver'       => 'required|in:local,s3',
            's3_access_key_id'     => 'required_if:storage_driver,s3|nullable|string|max:255',
            's3_secret_access_key' => 'required_if:storage_driver,s3|nullable|string|max:255',
            's3_default_region'    => 'required_if:storage_driver,s3|nullable|string|max:50',
            's3_bucket'            => 'required_if:storage_driver,s3|nullable|string|max:255',
            's3_url'               => 'nullable|string|max:500',
            's3_endpoint'          => 'nullable|string|max:500',
            's3_root'              => 'nullable|string|max:255',
        ]);

        $driver = $request->input('storage_driver', 'local');

        $this->externalAppService->setModuleEnv('external-storage', [
            'STORAGE_DRIVER'       => $driver,
            'S3_ACCESS_KEY_ID'     => $request->input('s3_access_key_id', ''),
            'S3_SECRET_ACCESS_KEY' => $request->input('s3_secret_access_key', ''),
            'S3_DEFAULT_REGION'    => $request->input('s3_default_region', 'us-east-1'),
            'S3_BUCKET'            => $request->input('s3_bucket', ''),
            'S3_URL'               => $request->input('s3_url', ''),
            'S3_ENDPOINT'          => $request->input('s3_endpoint', ''),
            'S3_ROOT'              => $request->input('s3_root', ''),
        ]);

        return redirect()->route('admin.s3-storage-settings')
            ->with('success', 'S3 storage settings saved successfully.');
    }

    /**
     * Test S3 connection via AJAX.
     */
    public function testConnection(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $key      = $request->input('s3_access_key_id');
        $secret   = $request->input('s3_secret_access_key');
        $region   = $request->input('s3_default_region', 'us-east-1');
        $bucket   = $request->input('s3_bucket');
        $endpoint = $request->input('s3_endpoint');

        if (empty($key) || empty($secret) || empty($bucket)) {
            return response()->json([
                'success' => false,
                'message' => 'Please fill in Access Key, Secret Key, and Bucket Name before testing.',
            ], 400);
        }

        try {
            $config = [
                'version'     => 'latest',
                'region'      => $region ?: 'us-east-1',
                'credentials' => [
                    'key'    => $key,
                    'secret' => $secret,
                ],
            ];

            if (!empty($endpoint)) {
                $config['endpoint'] = $endpoint;
                $config['use_path_style_endpoint'] = true;
            }

            $client = new S3Client($config);
            $client->headBucket(['Bucket' => $bucket]);

            return response()->json([
                'success' => true,
                'message' => 'Connection successful! Your S3 bucket is accessible.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
            ]);
        }
    }
}
