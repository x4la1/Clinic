<?php

namespace App\Service;

use http\Exception\InvalidArgumentException;
use http\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageService
{
    private string $uploadDir = '';
    private Filesystem $filesystem;

    public function __construct(ParameterBagInterface $params, Filesystem $filesystem)
    {
        $this->uploadDir = $params->get('kernel.project_dir') . '/public/uploads';
        $this->filesystem = $filesystem;
    }

    public function saveImage(string $base64, string $filename): string
    {
        if (!str_starts_with($base64, 'data:image/')) {
            throw new InvalidArgumentException("INVALID_IMAGE_FORMAT");
        }

        $matches = [];
        preg_match('/^data:image\/(\w+);base64,/', $base64, $matches);

        if (!isset($matches[1])) {
            throw new \InvalidArgumentException('INVALID_IMAGE_FORMAT');
        }

        $mimeType = $matches[1];
        $extension = $this->mimeTypeToExtension($mimeType);

        $base64String = substr($base64, strpos($base64, ',') + 1);

        $imageData = base64_decode($base64String, true);

        if ($imageData === false) {
            throw new \InvalidArgumentException('INVALID_IMAGE');
        }

        if (strlen($imageData) > 5 * 1024 * 1024) {
            throw new \InvalidArgumentException('INVALID_IMAGE_SIZE');
        }

        $fullFilename = $filename . '.' . $extension;
        $filePath = $this->uploadDir . '/' . $fullFilename;

        if ($this->filesystem->exists($filePath)) {
            $this->filesystem->remove($filePath);
        }

        file_put_contents($filePath, $imageData);

        return $filePath;
    }

    private function mimeTypeToExtension(string $mimeType): string
    {
        $map = [
            'jpeg' => 'jpg',
            'jpg' => 'jpg',
            'png' => 'png',
        ];

        return $map[$mimeType] ?? 'jpg';
    }

    public function deleteImage(string $filepath): bool
    {
        if ($this->filesystem->exists($filepath)) {
            $this->filesystem->remove($filepath);
            return true;
        }

        return false;
    }
}
