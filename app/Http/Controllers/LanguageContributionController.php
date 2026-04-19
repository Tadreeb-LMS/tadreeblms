<?php

namespace App\Http\Controllers;

use App\Models\LanguageMarketplacePackage;
use App\Models\LanguageTranslationInvitation;
use App\Services\LanguageMarketplaceService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class LanguageContributionController extends Controller
{
    /**
     * @var \App\Services\LanguageMarketplaceService
     */
    protected $service;

    public function __construct(LanguageMarketplaceService $service)
    {
        $this->service = $service;
    }

    public function show($token)
    {
        $invitation = LanguageTranslationInvitation::where('invite_token', $token)->firstOrFail();
        if ($invitation->expires_at && $invitation->expires_at->isPast() && $invitation->status === 'pending') {
            $invitation->status = 'expired';
            $invitation->save();
        }

        if ($invitation->status === 'expired') {
            abort(410, 'This translation invitation has expired.');
        }

        if (!$invitation->viewed_at) {
            $invitation->viewed_at = now();
            if ($invitation->status === 'pending') {
                $invitation->status = 'viewed';
            }
            $invitation->save();
        }

        $sourcePackage = $invitation->source_package_id ? LanguageMarketplacePackage::find($invitation->source_package_id) : null;
        $context = $this->service->buildContributionContext($sourcePackage, $invitation->locale_code);
        $sourceLocale = $context['source_locale'];
        $sourceModuleCount = $context['source_module_count'];
        $missingEntries = $context['missing_entries'];

        return view('language-marketplace.contribute', compact('invitation', 'sourcePackage', 'sourceLocale', 'missingEntries', 'sourceModuleCount'));
    }

    public function submit(Request $request, $token)
    {
        $invitation = LanguageTranslationInvitation::where('invite_token', $token)->firstOrFail();
        if ($invitation->expires_at && $invitation->expires_at->isPast()) {
            abort(410, 'This translation invitation has expired.');
        }

        $request->validate([
            'language_payload_file' => 'nullable|file|mimes:json,txt|max:5120',
            'language_payload_json' => 'nullable|string',
            'auto_translations' => 'nullable|array',
            'message' => 'nullable|string|max:1000',
        ]);

        $rawPayload = trim((string) $request->input('language_payload_json', ''));
        $autoTranslations = $this->normalizeAutoTranslations((array) $request->input('auto_translations', []));
        if ($request->hasFile('language_payload_file')) {
            $rawPayload = (string) file_get_contents($request->file('language_payload_file')->getRealPath());
        }

        if ($rawPayload === '' && empty($autoTranslations)) {
            return back()->withErrors(['Upload a JSON file, paste JSON payload, or fill at least one missing label.']);
        }

        $sourcePackage = $invitation->source_package_id ? LanguageMarketplacePackage::find($invitation->source_package_id) : null;
        try {
            $decoded = $this->service->buildSubmissionPayload(
                $sourcePackage,
                $invitation->locale_code,
                $rawPayload,
                $autoTranslations
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors([$e->getMessage()]);
        }

        $persisted = $this->service->persistSubmissionPayload($invitation->locale_code, $decoded);
        $version = $persisted['version'];
        $relativePath = $persisted['relative_path'];

        $package = LanguageMarketplacePackage::create([
            'source_locale' => (string) ($decoded['source_locale'] ?? 'en'),
            'target_locale' => $invitation->locale_code,
            'package_type' => 'translation',
            'status' => 'submitted',
            'title' => 'Contributor Submission for ' . strtoupper($invitation->locale_code),
            'version' => $version,
            'manifest_path' => $relativePath,
            'source_package_id' => $invitation->source_package_id,
            'review_notes' => trim((string) $request->input('message')) ?: null,
            'submitted_at' => now(),
        ]);

        $invitation->submission_package_id = $package->id;
        $invitation->status = 'submitted';
        $invitation->submitted_at = now();
        $invitation->save();

        return back()->with('status', 'Translation submitted successfully. It is now waiting for admin review.');
    }

    /**
     * Convert translator form inputs to nested module arrays.
     *
     * @param array $input
     * @return array
     */
    protected function normalizeAutoTranslations(array $input)
    {
        $result = [];

        foreach ($input as $module => $entries) {
            if (!is_array($entries)) {
                continue;
            }

            $moduleName = preg_replace('/[^a-z0-9_]+/', '_', strtolower(trim((string) $module)));
            if ($moduleName === '') {
                continue;
            }

            foreach ($entries as $keyPath => $value) {
                $value = trim((string) $value);
                if ($value === '') {
                    continue;
                }
                Arr::set($result[$moduleName], (string) $keyPath, $value);
            }
        }

        return $result;
    }

    public function downloadPublishedPackage($packageId)
    {
        $package = LanguageMarketplacePackage::query()
            ->where('id', $packageId)
            ->where('status', 'published')
            ->firstOrFail();

        $payload = $this->service->readStoredPackage($package->manifest_path);
        if (!$payload) {
            abort(404);
        }

        $filename = 'language-package-' . $package->target_locale . '-' . $package->version . '.json';
        return response()->streamDownload(function () use ($payload) {
            echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, $filename, ['Content-Type' => 'application/json']);
    }
}
