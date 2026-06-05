<?php

declare(strict_types=1);

final class FileUploadValidator
{
    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    private const VIDEO_EXTENSIONS = ['mp4', 'mov', 'webm', 'avi', 'mkv'];

    public function __construct(
        private readonly int $maxThumbnailBytes = 5_242_880,
        private readonly int $maxImageBytes = 10_485_760,
        private readonly int $maxVideoBytes = 104_857_600,
    ) {}

    /**
     * @param array{name:string,type:string,tmp_name:string,error:int,size:int} $file
     */
    public function validateThumbnail(array $file): void
    {
        $this->assertUploadedFile($file, 'Thumbnail');
        $this->assertExtension($file['name'], self::IMAGE_EXTENSIONS, 'Thumbnail');
        $this->assertMaxSize((int) $file['size'], $this->maxThumbnailBytes, 'Thumbnail');
    }

    /**
     * @param array{name:string,type:string,tmp_name:string,error:int,size:int} $file
     */
    public function validateImage(array $file): void
    {
        $this->assertUploadedFile($file, 'Image');
        $this->assertExtension($file['name'], self::IMAGE_EXTENSIONS, 'Image');
        $this->assertMaxSize((int) $file['size'], $this->maxImageBytes, 'Image');
    }

    /**
     * @param array{name:string,type:string,tmp_name:string,error:int,size:int} $file
     */
    public function validateVideo(array $file): void
    {
        $this->assertUploadedFile($file, 'Video');
        $this->assertExtension($file['name'], self::VIDEO_EXTENSIONS, 'Video');
        $this->assertMaxSize((int) $file['size'], $this->maxVideoBytes, 'Video');
    }

    public function isVideoExtension(string $extension): bool
    {
        return in_array(strtolower($extension), self::VIDEO_EXTENSIONS, true);
    }

    /**
     * @param array{name:string,type:string,tmp_name:string,error:int,size:int} $file
     */
    private function assertUploadedFile(array $file, string $label): void
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException($label . ' upload failed.');
        }

        if (!is_uploaded_file($file['tmp_name'] ?? '')) {
            throw new InvalidArgumentException($label . ' is not a valid upload.');
        }
    }

    /**
     * @param list<string> $allowed
     */
    private function assertExtension(string $filename, array $allowed, string $label): void
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if ($ext === '' || !in_array($ext, $allowed, true)) {
            throw new InvalidArgumentException(
                $label . ' must be one of: ' . implode(', ', $allowed) . '.'
            );
        }
    }

    private function assertMaxSize(int $bytes, int $max, string $label): void
    {
        if ($bytes > $max) {
            $maxMb = (int) round($max / 1_048_576);
            throw new InvalidArgumentException($label . " must be {$maxMb}MB or smaller.");
        }
    }
}
