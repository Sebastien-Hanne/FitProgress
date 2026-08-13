<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class ProfilePhotoUploader
{
    public function __construct(
        #[Autowire('%kernel.project_dir%/public/uploads/profile')]
        private readonly string $targetDirectory,
    ) {
    }

    public function upload(UploadedFile $photo): string
    {
        $extension = $photo->guessExtension() ?? 'bin';
        $filename = bin2hex(random_bytes(16)).'.'.$extension;
        $photo->move($this->targetDirectory, $filename);

        return 'uploads/profile/'.$filename;
    }

    public function delete(?string $relativePath): void
    {
        if ($relativePath === null || !str_starts_with($relativePath, 'uploads/profile/')) {
            return;
        }

        $filename = basename($relativePath);
        $path = $this->targetDirectory.DIRECTORY_SEPARATOR.$filename;
        if (is_file($path)) {
            @unlink($path);
        }
    }
}
