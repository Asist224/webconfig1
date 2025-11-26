<?php
// import-webchat-config.php - Импорт конфигурации веб-чата
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Проверка метода запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Метод не разрешен']);
    exit;
}

// Проверяем наличие файла
if (!isset($_FILES['config']) || $_FILES['config']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode([
        'success' => false,
        'error' => 'Файл не загружен или произошла ошибка при загрузке'
    ]);
    exit;
}

$uploadedFile = $_FILES['config'];
$configFile = 'webchat-config.js';

// Проверяем расширение файла
$fileExtension = strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION));
if (!in_array($fileExtension, ['js', 'json'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Неверный формат файла. Поддерживаются только .js и .json'
    ]);
    exit;
}

// Создаем резервную копию текущего файла
$backupDir = 'backups/';
if (!file_exists($backupDir)) {
    mkdir($backupDir, 0755, true);
}

if (file_exists($configFile)) {
    $timestamp = date('Y-m-d_H-i-s');
    $backupFile = $backupDir . 'webchat-config.backup.' . $timestamp . '.js';
    copy($configFile, $backupFile);
}

// Читаем содержимое загруженного файла
$content = file_get_contents($uploadedFile['tmp_name']);

// Сохраняем новый файл конфигурации
if (file_put_contents($configFile, $content) !== false) {
    echo json_encode([
        'success' => true,
        'message' => 'Конфигурация успешно импортирована',
        'backup' => isset($backupFile) ? basename($backupFile) : null
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Ошибка при сохранении файла конфигурации'
    ]);
}
?>
