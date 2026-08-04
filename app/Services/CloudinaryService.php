<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class CloudinaryService
{
    private string $cloudName;

    private string $apiKey;

    private string $apiSecret;

    public function __construct()
    {
        $this->cloudName = config('services.cloudinary.cloud_name', '');
        $this->apiKey = config('services.cloudinary.api_key', '');
        $this->apiSecret = config('services.cloudinary.api_secret', '');
    }

    public function isConfigured(): bool
    {
        return filled($this->cloudName) && filled($this->apiKey) && filled($this->apiSecret);
    }

    /**
     * Upload a video file to Cloudinary.
     *
     * @return array{secure_url: string, public_id: string, duration: float|null, thumbnail_url: string}
     */
    public function uploadVideo(string $filePath, string $folder = 'countryworld'): array
    {
        $timestamp = time();
        $paramsToSign = ['folder' => $folder, 'timestamp' => $timestamp];
        $signature = $this->sign($paramsToSign);

        $response = Http::attach('file', fopen($filePath, 'r'), basename($filePath))
            ->post("https://api.cloudinary.com/v1_1/{$this->cloudName}/video/upload", [
                'api_key' => $this->apiKey,
                'timestamp' => $timestamp,
                'signature' => $signature,
                'folder' => $folder,
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Cloudinary upload failed: '.$response->body());
        }

        $data = $response->json();

        return [
            'secure_url' => $data['secure_url'],
            'public_id' => $data['public_id'],
            'duration' => $data['duration'] ?? null,
            'thumbnail_url' => $this->thumbnailUrl($data['public_id']),
        ];
    }

    public function deleteVideo(string $publicId): void
    {
        $timestamp = time();
        $params = ['public_id' => $publicId, 'timestamp' => $timestamp];
        $signature = $this->sign($params);

        Http::post("https://api.cloudinary.com/v1_1/{$this->cloudName}/video/destroy", [
            'api_key' => $this->apiKey,
            'timestamp' => $timestamp,
            'signature' => $signature,
            'public_id' => $publicId,
        ]);
    }

    private function thumbnailUrl(string $publicId): string
    {
        return "https://res.cloudinary.com/{$this->cloudName}/video/upload/so_0,f_jpg/{$publicId}.jpg";
    }

    private function sign(array $params): string
    {
        ksort($params);
        $str = collect($params)->map(fn ($v, $k) => "{$k}={$v}")->implode('&');

        return sha1($str.$this->apiSecret);
    }
}
