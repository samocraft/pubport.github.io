<?php

declare(strict_types=1);

final class ProjectUploadService
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly FileUploadValidator $validator,
        private readonly string $uploadDir = 'uploads',
    ) {}

    /**
     * @param array<string, mixed> $post
     * @param array<string, mixed> $files
     * @return positive-int Project id
     */
    public function createProject(array $post, array $files): int
    {
        $title = trim((string) ($post['title'] ?? ''));
        $subtitle = trim((string) ($post['subtitle'] ?? ''));
        $category = ProjectCategory::tryFromInput((string) ($post['category'] ?? ''));

        if ($title === '') {
            throw new InvalidArgumentException('Project title is required.');
        }

        if ($category === null) {
            throw new InvalidArgumentException('Please select a valid category.');
        }

        if (!isset($files['thumbnail']) || !is_array($files['thumbnail'])) {
            throw new InvalidArgumentException('Thumbnail is required.');
        }

        $thumbnailFile = $files['thumbnail'];
        $this->validator->validateThumbnail($thumbnailFile);

        $imageFiles = $this->collectFileList($files['images'] ?? null);
        $videoFiles = $this->collectFileList($files['videos'] ?? null);

        if ($imageFiles === [] && $videoFiles === []) {
            throw new InvalidArgumentException('Add at least one image or video for the gallery.');
        }

        foreach ($imageFiles as $image) {
            $this->validator->validateImage($image);
        }

        foreach ($videoFiles as $video) {
            $this->validator->validateVideo($video);
        }

        $this->ensureUploadDirectory();

        $this->pdo->beginTransaction();

        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO projects (title, subtitle, category, thumbnail) VALUES (:title, :subtitle, :category, :thumbnail)'
            );
            $stmt->execute([
                'title' => $title,
                'subtitle' => $subtitle,
                'category' => $category->value,
                'thumbnail' => '',
            ]);

            $projectId = (int) $this->pdo->lastInsertId();

            if ($projectId < 1) {
                throw new RuntimeException('Could not create project record.');
            }

            $thumbnailPath = $this->storeFile($thumbnailFile, $projectId, 'thumb');

            $updateThumb = $this->pdo->prepare(
                'UPDATE projects SET thumbnail = :thumbnail WHERE id = :id'
            );
            $updateThumb->execute([
                'thumbnail' => $thumbnailPath,
                'id' => $projectId,
            ]);

            $position = 0;

            foreach ($imageFiles as $image) {
                $path = $this->storeFile($image, $projectId, (string) $position);
                $this->insertMedia($projectId, $path, $position);
                $position++;
            }

            foreach ($videoFiles as $video) {
                $path = $this->storeFile($video, $projectId, (string) $position);
                $this->insertMedia($projectId, $path, $position);
                $position++;
            }

            $this->pdo->commit();

            return $projectId;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function insertMedia(int $projectId, string $path, int $position): void
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        $stmt = $this->pdo->prepare(
            'INSERT INTO media (project_id, media_file, media_type, position) VALUES (:project_id, :media_file, :media_type, :position)'
        );
        $stmt->execute([
            'project_id' => $projectId,
            'media_file' => $path,
            'media_type' => $extension,
            'position' => $position,
        ]);
    }

    /**
     * @param array{name:string,tmp_name:string}|null $bucket
     * @return list<array{name:string,type:string,tmp_name:string,error:int,size:int}>
     */
    private function collectFileList(?array $bucket): array
    {
        if ($bucket === null || !isset($bucket['name']) || !is_array($bucket['name'])) {
            return [];
        }

        $files = [];
        $count = count($bucket['name']);

        for ($i = 0; $i < $count; $i++) {
            if (($bucket['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $files[] = [
                'name' => (string) $bucket['name'][$i],
                'type' => (string) ($bucket['type'][$i] ?? ''),
                'tmp_name' => (string) $bucket['tmp_name'][$i],
                'error' => (int) $bucket['error'][$i],
                'size' => (int) $bucket['size'][$i],
            ];
        }

        return $files;
    }

    /**
     * @param array{name:string,tmp_name:string} $file
     */
    private function storeFile(array $file, int $projectId, string $suffix): string
    {
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $safeName = sprintf(
            'p%d_%s_%s.%s',
            $projectId,
            $suffix,
            bin2hex(random_bytes(6)),
            $extension
        );

        $target = rtrim($this->uploadDir, '/\\') . DIRECTORY_SEPARATOR . $safeName;

        if (!move_uploaded_file($file['tmp_name'], $target)) {
            throw new RuntimeException('Failed to save uploaded file.');
        }

        return str_replace('\\', '/', $target);
    }

    private function ensureUploadDirectory(): void
    {
        if (!is_dir($this->uploadDir) && !mkdir($this->uploadDir, 0755, true) && !is_dir($this->uploadDir)) {
            throw new RuntimeException('Upload directory could not be created.');
        }
    }
}
