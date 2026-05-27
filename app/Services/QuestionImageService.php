<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class QuestionImageService
{
    public function storeUploaded(?UploadedFile $file, ?string $currentPath = null): ?string
    {
        if (!$file) {
            return $currentPath;
        }

        $path = $file->store('question-images', 'public');

        $this->deleteManagedImage($currentPath);

        return $path;
    }

    public function storeFromUrl(?string $url, ?string $currentPath = null): ?string
    {
        $url = trim((string) $url);

        if ($url === '') {
            return $currentPath;
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw ValidationException::withMessages([
                'file' => ['Kolom image_url harus berisi URL gambar yang valid.'],
            ]);
        }

        try {
            $response = Http::timeout(20)->get($url)->throw();
        } catch (RequestException|Throwable $exception) {
            throw ValidationException::withMessages([
                'file' => ['Gagal mengambil gambar dari image_url: ' . $url],
            ]);
        }

        $contentType = strtolower((string) $response->header('Content-Type', ''));

        if (!Str::startsWith($contentType, 'image/')) {
            throw ValidationException::withMessages([
                'file' => ['File pada image_url bukan gambar yang didukung.'],
            ]);
        }

        $extension = $this->resolveExtension($contentType, $url);
        $path = 'question-images/' . Str::uuid() . '.' . $extension;

        Storage::disk('public')->put($path, $response->body());
        $this->deleteManagedImage($currentPath);

        return $path;
    }

    private function resolveExtension(string $contentType, string $url): string
    {
        $extension = match ($contentType) {
            'image/jpeg', 'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            default => pathinfo((string) parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION),
        };

        return $extension !== '' ? strtolower($extension) : 'jpg';
    }

    private function deleteManagedImage(?string $path): void
    {
        if (!$path || !Str::startsWith($path, 'question-images/')) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
