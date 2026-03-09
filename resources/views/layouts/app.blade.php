@php
    $landingPageEnabled = \App\Models\Setting::where('key', 'landing_page_enabled')->value('value') ?? '1';
    $footerContent = \App\Models\Setting::where('key', 'footer_content')->value('value') ?? '© ' . date('Y') . ' TadreebLMS';
@endphp

<!-- Existing content -->
@if($landingPageEnabled == '1')
    <footer>
        {{ $footerContent }}
    </footer>
@endif
