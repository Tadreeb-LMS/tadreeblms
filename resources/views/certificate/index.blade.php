<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Certificate of Completion - {{ $data['name'] }}</title>
    <style>
        /* CRITICAL: PDF engines (dompdf) render absolute positioning unpredictably.
           This template uses a nested TABLE structure for maximum stability. */

        @page {
            size: A4 landscape;
            margin: 0;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Helvetica', 'Arial', sans-serif;
            background-color: #ffffff;
            -webkit-print-color-adjust: exact;
        }

        /* Landscape A4 Wrapper (297mm x 210mm) */
        .page-container {
            width: 297mm;
            height: 210mm;
            position: relative;
            background-color: #ffffff;
            overflow: hidden;
            display: block;
        }

        /* Premium Navy Accent at the top */
        .navy-accent {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 18mm;
            background-color: #1a237e;
            z-index: 1;
        }

        /* Gold Outer Border (Fixed to prevent element collision) */
        .gold-border-outer {
            position: absolute;
            top: 8mm;
            left: 8mm;
            right: 8mm;
            bottom: 8mm;
            border: 4px solid #c5a059;
            z-index: 5;
        }

        /* Gold Inner Double Border with proper internal padding */
        .gold-border-inner {
            position: absolute;
            top: 12mm;
            left: 12mm;
            right: 12mm;
            bottom: 12mm;
            border: 2px double #c5a059;
            z-index: 6;
            background-color: rgba(255, 255, 255, 0.99);
            padding: 15mm; /* PROTECTIVE PADDING: No text will touch this border */
        }

        /* Robust Table Structure for precise layout without overlaps */
        .layout-table {
            width: 100%;
            height: 100%;
            border-collapse: collapse;
            z-index: 20;
            position: relative;
        }

        .header-cell {
            text-align: center;
            height: 25%;
            vertical-align: top;
        }

        .body-cell {
            text-align: center;
            height: 50%;
            vertical-align: middle;
        }

        .footer-cell {
            text-align: center;
            height: 25%;
            vertical-align: bottom;
        }

        /* Typography Hierarchy */
        .title {
            font-size: 42pt;
            font-weight: bold;
            color: #1a237e;
            margin: 0;
            letter-spacing: 3pt;
            text-transform: uppercase;
        }

        .subtitle {
            font-size: 16pt;
            color: #666;
            letter-spacing: 5pt;
            text-transform: uppercase;
            margin-top: 5mm;
        }

        .certify-text {
            font-size: 14pt;
            color: #333;
            margin-bottom: 5mm;
        }

        .student-name {
            font-size: 52pt;
            font-weight: bold;
            color: #c5a059;
            margin: 10mm 0;
            border-bottom: 1px solid #eee;
            display: inline-block;
            min-width: 160mm;
        }

        .course-info {
            font-size: 16pt;
            color: #333;
            margin-top: 5mm;
        }

        .course-title {
            font-size: 28pt;
            font-weight: bold;
            color: #1a237e;
            margin-top: 5mm;
        }

        /* Signature & Security Footer */
        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .footer-col {
            vertical-align: bottom;
            padding: 0;
        }

        .qr-box {
            text-align: left;
            width: 30mm;
            max-width: 30mm;
            overflow: hidden;
        }

        .qr-img {
            width: 28mm;
            height: 28mm;
            border: 1px solid #f0f0f0;
            padding: 1mm;
            background: #fff;
        }

        .cert-id {
            font-family: 'Courier New', monospace;
            font-size: 7pt;
            color: #999;
            margin-top: 4px;
            text-align: left;
            max-width: 30mm;
            word-break: break-all;
            overflow: hidden;
        }

        .signature-box {
            text-align: center;
        }

        .signature-line {
            border-top: 1.5px solid #a0a0a0;
            margin: 2mm 5mm 0 5mm;
            padding-top: 2mm;
            color: #888;
            font-size: 9pt;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .signature-data {
            font-weight: bold;
            font-size: 14pt;
            color: #1a237e;
        }

        .signature-auth {
            font-size: 22pt;
            color: #1a237e;
            font-family: 'Times New Roman', serif;
            font-style: italic;
        }

        /* Brand Identity Watermark */
        .watermark-bg {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-40deg);
            font-size: 90pt;
            font-weight: bold;
            color: rgba(26, 35, 126, 0.04);
            white-space: nowrap;
            z-index: 2;
        }
    </style>
</head>
<body>
    <div class="page-container">
        <!-- Floating Branded Elements -->
        <div class="navy-accent"></div>
        <div class="gold-border-outer"></div>
        <div class="gold-border-inner">
            
            <div class="watermark-bg">TADREEB LMS</div>

            <table class="layout-table">
                <!-- Header -->
                <tr>
                    <td class="header-cell">
                        <div class="title">Certificate</div>
                        <div class="subtitle">of Completion</div>
                    </td>
                </tr>

                <!-- Content Body -->
                <tr>
                    <td class="body-cell">
                        <div class="certify-text">This is to certify that</div>
                        <div class="student-name">{{ $data['name'] }}</div>
                        <div class="course-info">has successfully and with high honors completed the course</div>
                        <div class="course-title">{{ $data['course_name'] }}</div>
                    </td>
                </tr>

                <!-- Footer Section (Table in Table for Alignment) -->
                <tr>
                    <td class="footer-cell">
                        <table class="footer-table">
                            <tr>
                                <!-- Security Column (QR + ID) -->
                                <td class="footer-col" style="width: 33%; text-align: left; vertical-align: bottom;">
                                    <div class="qr-box">
                                        <img src="data:image/svg+xml;base64,{{ $data['qr'] }}" class="qr-img" alt="QR Link">
                                        <div class="cert-id" style="margin-bottom: 2px;">AUTHENTICITY VERIFIED</div>
                                        <div class="cert-id" style="font-weight: bold;">ID: {{ $data['certificate_id'] }}</div>
                                    </div>
                                </td>

                                <!-- Achievement Date -->
                                <td class="footer-col" style="width: 34%;">
                                    <div class="signature-box">
                                        <div class="signature-data">{{ $data['date'] }}</div>
                                        <div class="signature-line">Date of Achievement</div>
                                    </div>
                                </td>

                                <!-- Corporate Authority -->
                                <td class="footer-col" style="width: 33%; text-align: right;">
                                    <div class="signature-box" style="text-align: right;">
                                        <div class="signature-auth">TadreebLMS</div>
                                        <div class="signature-line" style="margin-right: 0;">Authorized Academic Authority</div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

        </div>
    </div>
</body>
</html>
