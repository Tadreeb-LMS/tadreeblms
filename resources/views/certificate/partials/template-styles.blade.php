:root {
    --primary-color: {{ $settings['primary_color'] ?? '#d4af37' }};
    --secondary-color: {{ $settings['secondary_color'] ?? '#f5d670' }};
    --bg-color: {{ $settings['bg_color'] ?? '#1a1a2e' }};
    --text-color: {{ $settings['text_color'] ?? '#ffffff' }};
}

.certificate-preview {
    position: relative;
    width: 100%;
    aspect-ratio: 297 / 210;
    min-height: 420px;
    box-sizing: border-box;
    background: var(--bg-color);
    padding: 40px 50px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    color: var(--text-color);
    font-family: Georgia, serif;
    transition: all 0.3s ease;
    overflow: hidden;
}

.certificate-preview,
.certificate-preview * {
    box-sizing: border-box;
}

.certificate-preview > *:not(.texture-overlay):not(.border-ornament) {
    position: relative;
    z-index: 2;
}

.certificate-preview.modern-light {
    border: 25px solid #f8f9fa;
    font-family: Inter, Poppins, Arial, sans-serif;
}

.certificate-preview.elegant-gold {
    border: 20px solid var(--primary-color);
    border-image: linear-gradient(135deg, #d4af37, #f5d670, #8e6d10) 1;
    background: #fffcf0;
}

.logo-container {
    min-height: 1px;
    margin-bottom: 15px;
}

.logo-container img {
    max-height: 60px;
    max-width: 220px;
}

.texture-overlay {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 1;
    opacity: 0.2;
    background-repeat: repeat;
    background-position: center;
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

.certificate-preview .border-ornament {
    position: absolute;
    inset: 12px;
    border: 2px solid var(--primary-color);
    opacity: 0.5;
    border-radius: 6px;
    pointer-events: none;
    z-index: 1;
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
    right: -2px;
    bottom: -2px;
    border-width: 0 3px 3px 0;
}

.certificate-preview.modern-light .border-ornament {
    display: none;
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
    width: 32px;
    height: 32px;
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
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.certificate-preview.modern-light .cert-title {
    font-weight: 300;
    letter-spacing: 8px;
}

.certificate-preview.elegant-gold .cert-title {
    font-family: Georgia, serif;
    color: #8e6d10;
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
    max-width: 86%;
    font-size: 26px;
    font-style: italic;
    color: var(--secondary-color);
    margin-bottom: 10px;
    text-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    overflow-wrap: anywhere;
}

.certificate-preview.modern-light .cert-recipient-name {
    font-family: Inter, Poppins, Arial, sans-serif;
    font-style: normal;
    font-weight: 600;
    text-transform: uppercase;
    border-bottom: 2px solid var(--primary-color);
    display: inline-block;
    padding-bottom: 5px;
}

.certificate-preview.elegant-gold .cert-recipient-name {
    color: #8e6d10;
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
    max-width: 88%;
    font-size: 16px;
    color: var(--text-color);
    font-weight: 600;
    margin-bottom: 20px;
    overflow-wrap: anywhere;
}

.cert-footer {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    width: 100%;
    margin-top: 20px;
    padding-top: 16px;
    border-top: 1px solid rgba(0, 0, 0, 0.1);
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
    overflow: hidden;
}

.cert-seal img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}

.cert-seal i,
.cert-seal svg {
    width: 24px;
    height: 24px;
    color: var(--bg-color);
}

.certificate-print-page {
    width: 297mm;
    height: 210mm;
    overflow: hidden;
    background: var(--bg-color);
}

.certificate-print-page .certificate-preview {
    width: 297mm;
    height: 210mm;
    min-height: 0;
    aspect-ratio: auto;
    border-radius: 0;
    transition: none;
}
