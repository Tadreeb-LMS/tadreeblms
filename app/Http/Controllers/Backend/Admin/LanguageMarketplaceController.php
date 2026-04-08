<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Models\LanguageMarketplacePackage;
use App\Models\LanguageTranslationInvitation;
use App\Models\Locale;
use App\Services\LanguageMarketplaceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class LanguageMarketplaceController extends Controller
{
    /**
     * @var \App\Services\LanguageMarketplaceService
     */
    protected $service;

    public function __construct(LanguageMarketplaceService $service)
    {
        $this->service = $service;
    }

    public function publishEnglishSource(Request $request)
    {
        $this->authorizeAdmin();

        $sourceLocale = strtolower(trim((string) $request->input('source_locale', 'en')));
        if (!preg_match('/^[a-z]{2,8}(?:[_-][a-z0-9]{2,8})*$/i', $sourceLocale)) {
            return redirect()->route('admin.general-settings', ['tab' => 'language_settings'])
                ->withErrors(['Invalid source locale code.']);
        }

        $export = $this->service->exportLocalePackage($sourceLocale, $sourceLocale, 'source', true);
        $package = $this->service->createPackageRecord([
            'source_locale' => $sourceLocale,
            'target_locale' => $sourceLocale,
            'package_type' => 'source',
            'status' => 'published',
            'title' => strtoupper($sourceLocale) . ' Source Package',
            'version' => $export['version'],
            'manifest_path' => $export['relative_path'],
            'submitted_by' => auth()->id(),
            'submitted_at' => now(),
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'published_at' => now(),
        ]);

        return redirect()->route('admin.general-settings', ['tab' => 'language_settings'])
            ->withFlashSuccess('Source package published to docs and marketplace successfully for ' . strtoupper($sourceLocale) . '.');
    }

    public function inviteContributor(Request $request)
    {
        $this->authorizeAdmin();

        $request->validate([
            'invite_locale_code' => 'required|string|max:15',
            'contributor_email' => 'required|email|max:191',
            'contributor_name' => 'nullable|string|max:191',
            'source_package_id' => 'nullable|integer',
        ]);

        $sourcePackage = LanguageMarketplacePackage::query()
            ->where('id', $request->input('source_package_id'))
            ->where('status', 'published')
            ->first();

        if (!$sourcePackage) {
            $sourcePackage = $this->service->getLatestPublishedSourcePackage();
        }

        if (!$sourcePackage) {
            return redirect()->route('admin.general-settings', ['tab' => 'language_settings'])
                ->withErrors(['Publish at least one source package before inviting contributors.']);
        }

        $invitation = LanguageTranslationInvitation::create([
            'source_package_id' => $sourcePackage->id,
            'locale_code' => strtolower(trim((string) $request->input('invite_locale_code'))),
            'contributor_name' => $request->input('contributor_name'),
            'contributor_email' => $request->input('contributor_email'),
            'invite_token' => Str::random(64),
            'status' => 'pending',
            'invited_by' => auth()->id(),
            'expires_at' => now()->addDays(14),
        ]);

        return redirect()->route('admin.general-settings', ['tab' => 'language_settings'])
            ->withFlashSuccess('Contributor invitation created. Share the invite link from the marketplace table.');
    }

    public function downloadPackage($packageId)
    {
        $package = LanguageMarketplacePackage::findOrFail($packageId);
        $payload = $this->service->readStoredPackage($package->manifest_path);
        if (!$payload) {
            abort(404);
        }

        $filename = 'language-package-' . $package->target_locale . '-' . $package->version . '.json';
        return response()->streamDownload(function () use ($payload) {
            echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, $filename, ['Content-Type' => 'application/json']);
    }

    public function downloadManual()
    {
        $this->authorizeAdmin();

        $manualPath = base_path('docs/language-marketplace-manual.md');
        if (!File::exists($manualPath)) {
            return redirect()->route('admin.general-settings', ['tab' => 'language_settings'])
                ->withErrors(['Language Marketplace manual file is missing.']);
        }

        $logoDataUri = null;
        $logoPath = public_path('assets/img/logo.png');
        if (File::exists($logoPath)) {
            $logoDataUri = 'data:image/png;base64,' . base64_encode((string) File::get($logoPath));
        }

        $pdf = Pdf::loadView('backend.settings.language_marketplace_manual_pdf', [
            'generatedAt' => now(),
            'manualVersion' => 'v1.1',
            'logoDataUri' => $logoDataUri,
        ])->setPaper('a4');

        return $pdf->download('language-marketplace-manual.pdf');
    }

    public function approveSubmission($packageId)
    {
        $this->authorizeAdmin();

        $package = LanguageMarketplacePackage::where('status', 'submitted')->findOrFail($packageId);
        $payload = $this->service->readStoredPackage($package->manifest_path);
        if (!$payload) {
            return redirect()->route('admin.general-settings', ['tab' => 'language_settings'])
                ->withErrors(['Submission package file is missing.']);
        }

        $this->service->importLocalePackage($package->target_locale, $payload);

        $package->status = 'published';
        $package->reviewed_by = auth()->id();
        $package->reviewed_at = now();
        $package->published_at = now();
        $package->save();

        $githubSyncResult = null;
        if ((bool) config('services.github_docs.auto_sync_approved', false)) {
            $githubSyncResult = $this->service->syncPackageToGithub($package);
        }

        LanguageTranslationInvitation::where('submission_package_id', $package->id)->update([
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        if (Schema::hasTable('locales')) {
            $locale = Locale::firstOrCreate(['short_name' => $package->target_locale], ['name' => strtoupper($package->target_locale), 'display_type' => 'ltr']);
            if (Schema::hasColumn('locales', 'is_enabled')) {
                $locale->is_enabled = 1;
            }
            if (Schema::hasColumn('locales', 'library_package_path')) {
                $locale->library_package_path = $package->manifest_path;
            }
            if (Schema::hasColumn('locales', 'library_uploaded_at')) {
                $locale->library_uploaded_at = now();
            }
            $locale->save();
        }

        $message = 'Translation submission approved, imported, and published.';
        if (is_array($githubSyncResult)) {
            $message .= ' GitHub sync: ' . ($githubSyncResult['ok'] ? 'completed.' : ('failed (' . ($githubSyncResult['message'] ?? 'unknown error') . ').'));
        }

        return redirect()->route('admin.general-settings', ['tab' => 'language_settings'])
            ->withFlashSuccess($message);
    }

    public function syncPackageToGithub($packageId)
    {
        $this->authorizeAdmin();

        $package = LanguageMarketplacePackage::where('status', 'published')->findOrFail($packageId);
        $result = $this->service->syncPackageToGithub($package);

        if (!$result['ok']) {
            return redirect()->route('admin.general-settings', ['tab' => 'language_settings'])
                ->withErrors([$result['message'] ?? 'GitHub sync failed.']);
        }

        return redirect()->route('admin.general-settings', ['tab' => 'language_settings'])
            ->withFlashSuccess('Package synced to GitHub successfully.');
    }

    public function rejectSubmission(Request $request, $packageId)
    {
        $this->authorizeAdmin();

        $package = LanguageMarketplacePackage::where('status', 'submitted')->findOrFail($packageId);
        $reviewNotes = $request->input('review_notes');
        if (is_array($reviewNotes)) {
            $reviewNotes = $request->input('review_notes.' . $packageId);
        }
        if ($reviewNotes === null) {
            $reviewNotes = $request->input('submission_review_notes.' . $packageId);
        }
        $reviewNotes = trim((string) ($reviewNotes ?: 'Needs revision.'));

        $package->status = 'rejected';
        $package->reviewed_by = auth()->id();
        $package->reviewed_at = now();
        $package->review_notes = $reviewNotes;
        $package->save();

        LanguageTranslationInvitation::where('submission_package_id', $package->id)->update([
            'status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'review_notes' => $reviewNotes,
        ]);

        return redirect()->route('admin.general-settings', ['tab' => 'language_settings'])
            ->withFlashSuccess('Translation submission rejected with review notes.');
    }

    protected function authorizeAdmin()
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403);
        }
    }
}
