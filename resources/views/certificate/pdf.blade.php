<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ $settings['cert_label'] ?? __('certificate_template.default_cert_label') }} - {{ $data['name'] ?? '' }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }

        html,
        body {
            width: 297mm;
            height: 210mm;
            margin: 0;
            padding: 0;
            overflow: hidden;
            background: {{ $settings['bg_color'] ?? '#1a1a2e' }};
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        @include('certificate.partials.template-styles', ['settings' => $settings])
    </style>
</head>
<body>
    <main class="certificate-print-page">
        @include('certificate.partials.template', [
            'settings' => $settings,
            'data' => $data,
            'embedAssets' => true,
            'elementId' => 'certificate-preview',
        ])
    </main>
</body>
</html>
