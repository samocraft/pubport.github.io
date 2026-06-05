<?php

declare(strict_types=1);

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: uploadpage.php');
    exit;
}

require_once __DIR__ . '/connection.php';
require_once __DIR__ . '/includes/ProjectCategory.php';
require_once __DIR__ . '/includes/FileUploadValidator.php';
require_once __DIR__ . '/includes/ProjectUploadService.php';

$token = (string) ($_POST['csrf_token'] ?? '');
$sessionToken = (string) ($_SESSION['csrf_token'] ?? '');

if ($token === '' || $sessionToken === '' || !hash_equals($sessionToken, $token)) {
    $_SESSION['upload_flash'] = ['type' => 'error', 'message' => 'Invalid form session. Please try again.'];
    header('Location: uploadpage.php');
    exit;
}

try {
    if (!$pdo instanceof PDO) {
        throw new RuntimeException('Database connection is unavailable.');
    }

    $service = new ProjectUploadService($pdo, new FileUploadValidator());
    $projectId = $service->createProject($_POST, $_FILES);

    $_SESSION['upload_flash'] = [
        'type' => 'success',
        'message' => 'Project published successfully.',
        'project_id' => $projectId,
    ];
} catch (InvalidArgumentException $e) {
    $_SESSION['upload_flash'] = ['type' => 'error', 'message' => $e->getMessage()];
} catch (Throwable $e) {
    $_SESSION['upload_flash'] = [
        'type' => 'error',
        'message' => 'Upload failed. Please check your files and try again.',
    ];
}

header('Location: uploadpage.php');
exit;
