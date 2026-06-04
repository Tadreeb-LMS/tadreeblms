@php
    $settings = array_merge(\App\Support\CertificateTemplate::defaults(), $settings ?? []);
    $data = $data ?? [];
    $embedAssets = $embedAssets ?? false;
    $elementId = $elementId ?? 'certificate-preview';

    $imageSrc = function ($path) use ($embedAssets) {
        if (! $path) {
            return null;
        }

        if (\Illuminate\Support\Str::startsWith($path, ['http://', 'https://', 'data:'])) {
            return $path;
        }

        $relativePath = ltrim($path, '/');
        $absolutePath = public_path($relativePath);

        if ($embedAssets && is_file($absolutePath)) {
            $mimeType = mime_content_type($absolutePath) ?: 'image/png';
            return 'data:' . $mimeType . ';base64,' . base64_encode(file_get_contents($absolutePath));
        }

        return asset($relativePath);
    };

    $logoSrc = $imageSrc($settings['logo_image'] ?? null);
    $sealSrc = $imageSrc($settings['seal_image'] ?? null);
@endphp

<div id="{{ $elementId }}" class="certificate-preview {{ $settings['template'] ?? 'classic-dark' }}">
    <div id="texture-overlay" class="texture-overlay {{ $settings['bg_texture'] ?? '' }}"></div>
    <div class="border-ornament"></div>

    <div class="logo-container" id="preview-logo-container">
        @if($logoSrc)
            <img src="{{ $logoSrc }}" alt="{{ __('certificate_template.logo') }}">
        @endif
    </div>

    <div class="cert-badge" id="preview-cert-badge" style="{{ ($settings['show_badge'] ?? 1) ? '' : 'display:none;' }}">
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <path fill="currentColor" d="M18 2H6v3H3v3c0 2.21 1.79 4 4 4h.26A6.02 6.02 0 0 0 11 15.92V19H7v3h10v-3h-4v-3.08A6.02 6.02 0 0 0 16.74 12H17c2.21 0 4-1.79 4-4V5h-3V2ZM5 8V7h1v2.83A2.01 2.01 0 0 1 5 8Zm14 0c0 .73-.4 1.37-1 1.72V7h1v1Z"/>
        </svg>
    </div>

    <div class="cert-label" id="preview-cert-label">{{ $settings['cert_label'] ?? __('certificate_template.default_cert_label') }}</div>
    <div class="cert-title" id="preview-cert-title">{{ $settings['cert_title'] ?? __('certificate_template.default_cert_title') }}</div>
    <div class="cert-divider"></div>

    <div class="cert-presented-to">{{ __('certificate_template.presented_to') }}</div>
    <div class="cert-recipient-name">{{ $data['name'] ?? __('certificate_template.sample_recipient_name') }}</div>

    <div class="cert-course-label">{{ __('certificate_template.completed_label') }}</div>
    <div class="cert-course-name">{{ $data['course_name'] ?? __('certificate_template.sample_course_name') }}</div>

    <div class="cert-footer">
        <div class="cert-signature-block">
            <div class="cert-signature-wrapper" id="preview-signature-wrapper" style="{{ ($settings['show_signature'] ?? 1) ? '' : 'display:none;' }}">
                <div class="cert-signature-label">{{ __('certificate_template.instructor') }}</div>
            </div>
        </div>

        <div class="cert-signature-block">
            <div class="cert-signature-wrapper">
                <div class="cert-signature-label">{{ $data['date'] ?? __('certificate_template.date_issued') }}</div>
            </div>
        </div>

        <div class="cert-seal" id="preview-cert-seal" style="{{ ($settings['show_seal'] ?? 1) ? '' : 'display:none;' }}">
            @if($sealSrc)
                <img src="{{ $sealSrc }}" alt="{{ __('certificate_template.seal') }}">
            @else
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path fill="currentColor" d="m12 2 2.2 2.1 3-.5.8 2.9 2.8 1.3-1.4 2.7 1.4 2.7-2.8 1.3-.8 2.9-3-.5L12 19l-2.2-2.1-3 .5-.8-2.9-2.8-1.3 1.4-2.7-1.4-2.7L6 6.5l.8-2.9 3 .5L12 2Zm-2.1 9.5-1.4-1.4-1.4 1.4 2.8 2.8 6-6-1.4-1.4-4.6 4.6Z"/>
                </svg>
            @endif
        </div>
    </div>
</div>
