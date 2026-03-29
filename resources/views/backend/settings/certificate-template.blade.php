@extends('backend.layouts.app')
@section('title', 'Certificate Template | ' . app_name())

@push('after-styles')
<style>
    :root {
        --primary-color: {{ $settings['primary_color'] ?? '#d4af37' }};
        --secondary-color: {{ $settings['secondary_color'] ?? '#f5d670' }};
        --bg-color: {{ $settings['bg_color'] ?? '#1a1a2e' }};
        --text-color: {{ $settings['text_color'] ?? '#ffffff' }};
    }

    .certificate-card {
        border: 2px solid #e0e0e0;
        border-radius: 12px;
        overflow: hidden;
        transition: box-shadow 0.3s ease;
    }

    .certificate-card:hover {
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    }

    .certificate-preview {
        position: relative;
        background: var(--bg-color);
        padding: 40px 50px;
        min-height: 420px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: var(--text-color);
        font-family: 'Georgia', serif;
        transition: all 0.3s ease;
        overflow: hidden;
    }

    .certificate-preview.modern-light {
        font-family: 'Inter', sans-serif;
    }

    .logo-container { margin-bottom: 15px; }
    .cert-badge { margin-bottom: 15px; }
    .cert-seal { margin: 0 20px; }
    
    .cert-footer { 
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        width: 100%;
        margin-top: 20px;
        padding-top: 16px;
        border-top: 1px solid var(--primary-color);
        border-top-color: rgba(0, 0, 0, 0.1);
    }

    .texture-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 1; /* Above background, below content if content has z-index: 2 */
        opacity: 0.2;
    }

    .texture-noise {
        background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E");
    }

    .texture-dots {
        background-image: radial-gradient(currentColor 1px, transparent 1px);
        background-size: 15px 15px;
    }

    .texture-lines {
        background-image: repeating-linear-gradient(45deg, transparent, transparent 10px, currentColor 10px, currentColor 11px);
    }

    .texture-parchment {
        background-image: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAMAAAAp4uSqAAAAVFBMVEUAAADl5eXm5ubn5+fo6Ojp6enq6urr6+vs7Ozt7e3u7u7v7+/w8PDx8fHy8vLz8/P09PT19fX29vb39/f4+Pj5+Pn6+vr7+/v8/Pz9/f3+/v7////026Y/AAAABXRSTlMABAnz9NisEtwAAAE0SURBVEjH7ZXRisMgEEXruGlM06T//8vD7Lp0u8mYpX1YmAdBeO6YpInS+U0fN336/EofN336/EofN336/EofN336/EofN336/EofN336/EofN336/EofN336/EofN336/EofN336/EofN336/EofN336/EofN336/EofN336/EofN336/EofN336/EofN336/EofN336/EofN336/EofN336/EofN336/EofN336/EofN336/EofN336/EofN336/EofN336/EofN336/EofN336/EofN336/EofN336/EofN336/EofN336/EofN336/EofN336/EofN336/EofN336/EofN336/EofN336/EofN336/EofN336/EofN336/EofN336/EofN336/EofN336/EofN336/EofN336/EofN336/EofN336/EofN336/EofN336/EofN336/EofN336/EofN336/HkfF+cTqA+OAm4AAAAASUVORK5CYII=");
        opacity: 0.4;
    }

    .texture-overlay {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 1;
        background-repeat: repeat;
        background-position: center;
    }

    .certificate-preview > *:not(.texture-overlay) {
        position: relative;
        z-index: 2;
    }

    .certificate-preview.modern-light {
        font-family: 'Inter', sans-serif;
    }

    .certificate-preview .border-ornament {
        position: absolute;
        inset: 12px;
        border: 2px solid var(--primary-color);
        opacity: 0.5;
        border-radius: 6px;
        pointer-events: none;
    }

    .certificate-preview .border-ornament::before,
    .certificate-preview .border-ornament::after {
        content: '';
        position: absolute;
        width: 20px;
        height: 20px;
        border-color: var(--primary-color);
        border-style: solid;
    }

    .certificate-preview .border-ornament::before {
        top: -2px;
        left: -2px;
        border-width: 3px 0 0 3px;
    }

    .certificate-preview .border-ornament::after {
        bottom: -2px;
        right: -2px;
        border-width: 0 3px 3px 0;
    }

    .cert-badge {
        width: 70px;
        height: 70px;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 16px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    .cert-badge i,
    .cert-badge svg {
        font-size: 32px;
        color: var(--bg-color);
    }

    .cert-label {
        font-size: 11px;
        letter-spacing: 4px;
        text-transform: uppercase;
        color: var(--primary-color);
        margin-bottom: 6px;
    }

    .cert-title {
        font-size: 28px;
        font-weight: 700;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: var(--text-color);
        margin-bottom: 12px;
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .cert-divider {
        width: 80px;
        height: 2px;
        background: linear-gradient(to right, transparent, var(--primary-color), transparent);
        margin: 12px auto;
    }

    .cert-presented-to {
        font-size: 13px;
        color: var(--text-color);
        opacity: 0.7;
        letter-spacing: 2px;
        text-transform: uppercase;
        margin-bottom: 6px;
    }

    .cert-recipient-name {
        font-size: 26px;
        font-style: italic;
        color: var(--secondary-color);
        margin-bottom: 10px;
        text-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .cert-course-label {
        font-size: 11px;
        color: var(--text-color);
        opacity: 0.6;
        letter-spacing: 2px;
        text-transform: uppercase;
        margin-bottom: 4px;
    }

    .cert-course-name {
        font-size: 16px;
        color: var(--text-color);
        font-weight: 600;
        margin-bottom: 20px;
    }

    .cert-footer {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        width: 100%;
        margin-top: 20px;
        padding-top: 16px;
        border-top: 1px solid var(--primary-color);
        border-top-color: rgba(0, 0, 0, 0.1);
    }

    .cert-signature-block {
        text-align: center;
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .cert-signature-wrapper {
        display: inline-block;
        border-top: 1px solid var(--primary-color);
        padding-top: 6px;
        min-width: 100px;
    }

    .cert-signature-label {
        font-size: 10px;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: var(--text-color);
        opacity: 0.7;
    }

    .cert-seal {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 0 0 4px rgba(0, 0, 0, 0.05), 0 0 0 8px rgba(0, 0, 0, 0.02);
        flex-shrink: 0;
        margin: 0 20px;
    }

    .cert-seal i {
        font-size: 24px;
        color: var(--bg-color);
    }

    .template-badge {
        position: absolute;
        top: 16px;
        right: 16px;
        background: rgba(0, 0, 0, 0.1);
        border: 1px solid var(--primary-color);
        color: var(--primary-color);
        font-size: 10px;
        letter-spacing: 1px;
        text-transform: uppercase;
        padding: 4px 10px;
        border-radius: 20px;
    }

    /* Template Specific Overrides */
    .certificate-preview.modern-light {
        border: 25px solid #f8f9fa;
        font-family: 'Poppins', sans-serif;
    }
    .certificate-preview.modern-light .border-ornament {
        display: none;
    }
    .certificate-preview.modern-light .cert-title {
        font-weight: 300;
        letter-spacing: 8px;
    }
    .certificate-preview.modern-light .cert-recipient-name {
        font-family: 'Poppins', sans-serif;
        font-style: normal;
        font-weight: 600;
        text-transform: uppercase;
        border-bottom: 2px solid var(--primary-color);
        display: inline-block;
        padding-bottom: 5px;
    }

    .certificate-preview.elegant-gold {
        border: 20px solid var(--primary-color);
        border-image: linear-gradient(135deg, #d4af37, #f5d670, #8e6d10) 1;
        background: #fffcf0;
    }
    .certificate-preview.elegant-gold .cert-title {
        font-family: 'Georgia', serif;
        color: #8e6d10;
    }
    .certificate-preview.elegant-gold .cert-recipient-name {
        color: #8e6d10;
    }

    .page-header-section {
        background: #fff;
        border-radius: 10px;
        padding: 24px 28px;
        margin-bottom: 24px;
        border: 1px solid #e8e8e8;
    }

    .page-header-section h4 {
        margin-bottom: 4px;
        font-size: 20px;
        font-weight: 600;
        color: #2c3e50;
    }

    .page-header-section p {
        margin: 0;
        color: #7f8c8d;
        font-size: 14px;
    }

    .grid-active {
        background-image: 
            linear-gradient(rgba(0,0,0,.1) 1px, transparent 1px),
            linear-gradient(90deg, rgba(0,0,0,.1) 1px, transparent 1px) !important;
        background-size: 20px 20px !important;
    }

    .template-info-row {
        display: flex;
        gap: 12px;
        margin-top: 16px;
        flex-wrap: wrap;
    }

    .template-info-pill {
        background: #f4f6f9;
        border: 1px solid #e0e0e0;
        border-radius: 20px;
        padding: 4px 14px;
        font-size: 12px;
        color: #555;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .template-info-pill i {
        color: #3d85c8;
    }

    .color-picker-wrapper {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 15px;
    }
    
    .color-picker-wrapper label {
        margin-bottom: 0;
        flex: 1;
    }

    input[type="color"] {
        border: none;
        width: 40px;
        height: 40px;
        padding: 0;
        cursor: pointer;
        background: none;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">

    <div class="page-header-section">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4><i class="fas fa-certificate mr-2 text-warning"></i>Certificate Template</h4>
                <p>Customize and manage certificate templates issued to learners.</p>
            </div>
        </div>
        <div class="template-info-row">
            <span class="template-info-pill"><i class="fas fa-palette"></i> <span id="template-count">3</span> Templates Available</span>
            <span class="template-info-pill"><i class="fas fa-check-circle"></i> Live Preview</span>
            <span class="template-info-pill"><i class="fas fa-expand-arrows-alt"></i> A4 Landscape</span>
        </div>
    </div>

    <form action="{{ route('admin.certificate-template-settings.save') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-xl-8 col-lg-7 col-md-12">
                <div class="card certificate-card">
                    <div class="card-header d-flex align-items-center justify-content-between bg-white">
                        <div class="d-flex align-items-center">
                            <span class="badge badge-warning mr-2" style="font-size:11px;">Preview</span>
                            <strong id="preview-template-name" style="font-size:15px;">{{ ucfirst(str_replace('-', ' ', $settings['template'] ?? 'classic-dark')) }}</strong>
                        </div>
                        <small class="text-muted">Dynamic Visualization</small>
                    </div>

                    <div id="certificate-preview" class="certificate-preview {{ $settings['template'] ?? 'classic-dark' }}">
                        <div id="texture-overlay" class="texture-overlay {{ $settings['bg_texture'] ?? '' }}"></div>
                        <div class="border-ornament"></div>
                        
                        <div class="logo-container" id="preview-logo-container">
                            @if($settings['logo_image'] ?? null)
                                <img src="{{ asset($settings['logo_image'] ?? '') }}" alt="Logo" style="max-height: 60px;">
                            @endif
                        </div>

                        <div class="cert-badge" id="preview-cert-badge" style="{{ ($settings['show_badge'] ?? 1) ? '' : 'display:none;' }}">
                            <i class="fas fa-trophy"></i>
                        </div>

                        <div class="cert-label" id="preview-cert-label">{{ $settings['cert_label'] ?? 'Certificate of Completion' }}</div>
                        <div class="cert-title" id="preview-cert-title">{{ $settings['cert_title'] ?? 'Achievement Award' }}</div>
                        <div class="cert-divider"></div>

                        <div class="cert-presented-to">This certificate is proudly presented to</div>
                        <div class="cert-recipient-name">John A. Smith</div>

                        <div class="cert-course-label">for successfully completing</div>
                        <div class="cert-course-name">Advanced Web Development Fundamentals</div>

                        <div class="cert-footer">
                            <div class="cert-signature-block">
                                <div class="cert-signature-wrapper" id="preview-signature-wrapper" style="{{ ($settings['show_signature'] ?? 1) ? '' : 'display:none;' }}">
                                    <div class="cert-signature-label">Instructor</div>
                                </div>
                            </div>

                            <div class="cert-signature-block">
                                <div class="cert-signature-wrapper">
                                    <div class="cert-signature-label">Date Issued</div>
                                </div>
                            </div>

                            <div class="cert-seal" id="preview-cert-seal" style="{{ ($settings['show_seal'] ?? 1) ? '' : 'display:none;' }}">
                                @if($settings['seal_image'] ?? null)
                                    <img src="{{ asset($settings['seal_image'] ?? '') }}" alt="Seal" style="max-width: 100%; border-radius: 50%;">
                                @else
                                    <i class="fas fa-certificate"></i>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="card-footer bg-white">
                        <div class="text-muted" style="font-size:13px;">
                            <i class="fas fa-info-circle mr-1"></i>
                            This is a live preview. Changes will be reflected here immediately. Save your changes to apply them to all issued certificates.
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header bg-white">
                        <strong><i class=" mr-2"></i>Display & Assets</strong>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 border-right">
                                <h6>Toggle Elements</h6>
                                <div class="form-check mb-2">
                                    <input type="checkbox" name="show_badge" id="show-badge-toggle" class="form-check-input" {{ ($settings['show_badge'] ?? 1) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="show-badge-toggle">Show Badge</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input type="checkbox" name="show_seal" id="show-seal-toggle" class="form-check-input" {{ ($settings['show_seal'] ?? 1) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="show-seal-toggle">Show Seal</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input type="checkbox" name="show_signature" id="show-signature-toggle" class="form-check-input" {{ ($settings['show_signature'] ?? 1) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="show-signature-toggle">Show Signature</label>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <h6>Upload Images</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Logo</label>
                                            @if($settings['logo_image'] ?? null)
                                                <div class="mb-2">
                                                    <img src="{{ asset($settings['logo_image']) }}" style="max-height: 30px;">
                                                    <div class="form-check">
                                                        <input type="checkbox" name="remove_logo_image" value="1" class="form-check-input" id="remove-logo" style="width:12px; height:12px;">
                                                        <label class="form-check-label text-danger small" for="remove-logo">Remove</label>
                                                    </div>
                                                </div>
                                            @endif
                                            <input type="file" name="logo_image" id="logo-upload" class="form-control-file small">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Seal</label>
                                            @if($settings['seal_image'] ?? null)
                                                <div class="mb-2">
                                                    <img src="{{ asset($settings['seal_image']) }}" style="max-height: 30px;">
                                                    <div class="form-check">
                                                        <input type="checkbox" name="remove_seal_image" value="1" class="form-check-input" id="remove-seal" style="width:12px; height:12px;">
                                                        <label class="form-check-label text-danger small" for="remove-seal">Remove</label>
                                                    </div>
                                                </div>
                                            @endif
                                            <input type="file" name="seal_image" id="seal-upload" class="form-control-file small">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-lg-5 col-md-12">
                <div class="card">
                    <div class="card-header bg-white">
                        <strong><i class="fas fa-sliders-h mr-2"></i>Settings</strong>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Template Style</label>
                            <select name="template" id="template-select" class="form-control">
                                <option value="classic-dark" {{ ($settings['template'] ?? 'classic-dark') == 'classic-dark' ? 'selected' : '' }}>Classic Dark</option>
                                <option value="modern-light" {{ ($settings['template'] ?? 'classic-dark') == 'modern-light' ? 'selected' : '' }}>Modern Light</option>
                                <option value="elegant-gold" {{ ($settings['template'] ?? 'classic-dark') == 'elegant-gold' ? 'selected' : '' }}>Elegant Gold</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Background Texture</label>
                            <select name="bg_texture" id="texture-select" class="form-control">
                                <option value="" {{ ($settings['bg_texture'] ?? '') == '' ? 'selected' : '' }}>None</option>
                                <option value="texture-noise" {{ ($settings['bg_texture'] ?? '') == 'texture-noise' ? 'selected' : '' }}>Noise</option>
                                <option value="texture-dots" {{ ($settings['bg_texture'] ?? '') == 'texture-dots' ? 'selected' : '' }}>Dots</option>
                                <option value="texture-lines" {{ ($settings['bg_texture'] ?? '') == 'texture-lines' ? 'selected' : '' }}>Diagonal Lines</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Certificate Label</label>
                            <input type="text" name="cert_label" id="cert-label-input" class="form-control" value="{{ $settings['cert_label'] ?? 'Certificate of Completion' }}">
                        </div>

                        <div class="form-group">
                            <label>Certificate Title</label>
                            <input type="text" name="cert_title" id="cert-title-input" class="form-control" value="{{ $settings['cert_title'] ?? 'Achievement Award' }}">
                        </div>

                        <hr>

                        <div class="color-picker-wrapper">
                            <label>Primary Color</label>
                            <input type="color" name="primary_color" id="primary-color-picker" value="{{ $settings['primary_color'] ?? '#d4af37' }}">
                        </div>

                        <div class="color-picker-wrapper">
                            <label>Secondary Color</label>
                            <input type="color" name="secondary_color" id="secondary-color-picker" value="{{ $settings['secondary_color'] ?? '#f5d670' }}">
                        </div>

                        <div class="color-picker-wrapper">
                            <label>Background Color</label>
                            <input type="color" name="bg_color" id="bg-color-picker" value="{{ $settings['bg_color'] ?? '#1a1a2e' }}">
                        </div>

                        <div class="color-picker-wrapper">
                            <label>Text Color</label>
                            <input type="color" name="text_color" id="text-color-picker" value="{{ $settings['text_color'] ?? '#ffffff' }}">
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-save mr-2"></i>Save Settings
                            </button>
                            <button type="button" id="reset-colors" class="btn btn-outline-secondary btn-block mt-2">
                                <i class="fas fa-undo mr-2"></i>Reset to Default
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

</div>

@push('after-scripts')
<script>
    $(document).ready(function() {
        const root = document.querySelector(':root');
        const preview = $('#certificate-preview');
        const templateSelect = $('#template-select');
        const previewName = $('#preview-template-name');

        const defaultColors = {
            'classic-dark': {
                'primary': '#d4af37',
                'secondary': '#f5d670',
                'bg': '#1a1a2e',
                'text': '#ffffff'
            },
            'modern-light': {
                'primary': '#3498db',
                'secondary': '#2c3e50',
                'bg': '#ffffff',
                'text': '#2c3e50'
            },
            'elegant-gold': {
                'primary': '#8e6d10',
                'secondary': '#c5a028',
                'bg': '#fffcf0',
                'text': '#333333'
            }
        };

        function updatePreview() {
            root.style.setProperty('--primary-color', $('#primary-color-picker').val());
            root.style.setProperty('--secondary-color', $('#secondary-color-picker').val());
            root.style.setProperty('--bg-color', $('#bg-color-picker').val());
            root.style.setProperty('--text-color', $('#text-color-picker').val());
            $('#preview-cert-label').text($('#cert-label-input').val());
            $('#preview-cert-title').text($('#cert-title-input').val());
        }

        $('input[type="color"], #cert-label-input, #cert-title-input').on('input', function() {
            updatePreview();
        });

        // Toggle elements
        $('#show-badge-toggle').on('change', function() {
            $('#preview-cert-badge').toggle(this.checked);
        });
        $('#show-seal-toggle').on('change', function() {
            if (this.checked) {
                $('#preview-cert-seal').show();
            } else {
                $('#preview-cert-seal').hide();
            }
        });
        $('#show-signature-toggle').on('change', function() {
            $('#preview-signature-wrapper').toggle(this.checked);
        });

        $('#texture-select').on('change', function() {
            $('#texture-overlay').attr('class', 'texture-overlay ' + $(this).val());
        });

        // Instant removal preview
        $('#remove-logo').on('change', function() {
            $('#preview-logo-container').toggle(!this.checked);
        });
        $('#remove-seal').on('change', function() {
            if (this.checked) {
                $('#preview-cert-seal').hide();
            } else {
                $('#preview-cert-seal').toggle($('#show-seal-toggle').is(':checked'));
            }
        });

        // Trigger change on load to sync preview
        $('#texture-select, #remove-logo, #remove-seal').trigger('change');

        // Image uploads preview
        function readURL(input, targetId, isSeal = false) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    if (isSeal) {
                        $('#' + targetId).html('<img src="' + e.target.result + '" style="max-width: 100%; border-radius: 50%;">');
                    } else {
                        $('#' + targetId).html('<img src="' + e.target.result + '" style="max-height: 60px;">');
                    }
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        $('#logo-upload').change(function() {
            $('#remove-logo').prop('checked', false).trigger('change');
            readURL(this, 'preview-logo-container');
        });
        $('#seal-upload').change(function() {
            $('#remove-seal').prop('checked', false).trigger('change');
            readURL(this, 'preview-cert-seal', true);
        });

        templateSelect.on('change', function() {
            const template = $(this).val();
            preview.removeClass('classic-dark modern-light elegant-gold').addClass(template);
            previewName.text(template.replace('-', ' ').replace(/\b\w/g, l => l.toUpperCase()));
            
            // Optionally apply default colors for the template
            if (confirm('Apply default colors for ' + template + ' template?')) {
                const config = defaultColors[template];
                $('#primary-color-picker').val(config.primary);
                $('#secondary-color-picker').val(config.secondary);
                $('#bg-color-picker').val(config.bg);
                $('#text-color-picker').val(config.text);
                updatePreview();
            }
        });

        $('#reset-colors').on('click', function() {
            const template = templateSelect.val();
            const config = defaultColors[template];
            $('#primary-color-picker').val(config.primary);
            $('#secondary-color-picker').val(config.secondary);
            $('#bg-color-picker').val(config.bg);
            $('#text-color-picker').val(config.text);
            updatePreview();
        });
    });
</script>
@endpush
@endsection
