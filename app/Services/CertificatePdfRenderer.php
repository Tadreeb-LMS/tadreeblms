<?php

namespace App\Services;

use App\Support\CertificateTemplate;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;

class CertificatePdfRenderer
{
    public function render(array $data): string
    {
        $directory = storage_path('app/certificates/rendered');
        File::ensureDirectoryExists($directory);

        $id = (string) Str::uuid();
        $htmlPath = $directory . "/certificate-{$id}.html";
        $pdfPath = $directory . "/certificate-{$id}.pdf";

        $html = view('certificate.pdf', [
            'data' => $data,
            'settings' => CertificateTemplate::settings(),
        ])->render();

        File::put($htmlPath, $html);

        try {
            $process = new Process([
                $this->chromiumBinary(),
                '--headless',
                '--disable-gpu',
                '--disable-dev-shm-usage',
                '--no-sandbox',
                '--allow-file-access-from-files',
                '--no-pdf-header-footer',
                '--print-to-pdf-no-header',
                '--print-to-pdf=' . $pdfPath,
                'file://' . $htmlPath,
            ]);
            $process->setTimeout(60);
            $process->run();

            if (! $process->isSuccessful() || ! File::exists($pdfPath)) {
                throw new RuntimeException(trim($process->getErrorOutput() ?: $process->getOutput()));
            }
        } finally {
            File::delete($htmlPath);
        }

        return $pdfPath;
    }

    public function renderTo(array $data, string $targetPath): string
    {
        File::ensureDirectoryExists(dirname($targetPath));

        if (File::exists($targetPath)) {
            File::delete($targetPath);
        }

        $pdfPath = $this->render($data);
        File::move($pdfPath, $targetPath);

        return $targetPath;
    }

    private function chromiumBinary(): string
    {
        $candidates = array_filter([
            env('CHROME_BIN'),
            env('CHROMIUM_PATH'),
            '/usr/bin/chromium-browser',
            '/snap/bin/chromium',
            '/usr/bin/chromium',
            '/usr/bin/google-chrome',
            '/usr/bin/google-chrome-stable',
        ]);

        foreach ($candidates as $candidate) {
            if (File::exists($candidate)) {
                return $candidate;
            }
        }

        throw new RuntimeException('Chromium is required to render certificate PDFs. Set CHROME_BIN or CHROMIUM_PATH.');
    }
}
