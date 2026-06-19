<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class LocalUploadService
{
    public function ensureDirectory(string $subdir): void
    {
        $path = public_path('uploads/' . trim($subdir, '/'));

        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    public function storeImage(UploadedFile $file, string $subdir, string $prefix, int $tenantId): string
    {
        $this->ensureDirectory($subdir);

        $filename = $prefix . '_' . $tenantId . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('uploads/' . $subdir), $filename);

        return url('uploads/' . $subdir . '/' . $filename);
    }

    public function isLocalUpload(?string $url): bool
    {
        if ($url === null || $url === '') {
            return true;
        }

        return (bool) preg_match('#/uploads/(logos|banners|products|qris)/#', $url);
    }

    public function deleteIfLocal(?string $url): void
    {
        if (!$url || !$this->isLocalUpload($url)) {
            return;
        }

        $path = parse_url($url, PHP_URL_PATH);
        if (!$path) {
            return;
        }

        $fullPath = public_path(ltrim($path, '/'));

        if (file_exists($fullPath)) {
            @unlink($fullPath);
        }
    }
}
