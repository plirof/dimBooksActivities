<?php
require_once __DIR__ . '/../config.php';

$sourceFile = __DIR__ . '/quizzes.json';
$quizDir = __DIR__ . '/quiz';

if (!file_exists($sourceFile)) {
    echo "No quizzes.json found. Nothing to migrate.\n";
    exit(0);
}

$data = readJSONFile($sourceFile);
if (!$data || !isset($data['quizzes'])) {
    echo "Invalid quizzes.json format.\n";
    exit(1);
}

if (!is_dir($quizDir)) {
    mkdir($quizDir, 0755, true);
}

$count = 0;
foreach ($data['quizzes'] as $quiz) {
    if (!isset($quiz['id'])) {
        echo "Skipping quiz with no id: " . ($quiz['title'] ?? 'unknown') . "\n";
        continue;
    }
    $file = $quizDir . '/' . $quiz['id'] . '.json';
    if (writeJSONFile($file, $quiz)) {
        $count++;
    } else {
        echo "Failed to write: {$file}\n";
        exit(1);
    }
}

$backupFile = $sourceFile . '.bak';
if (!rename($sourceFile, $backupFile)) {
    echo "Warning: Could not rename quizzes.json to quizzes.json.bak\n";
}

echo "✅ Migrated $count quizzes to data/quiz/\n";
echo "   quizzes.json renamed to quizzes.json.bak (backup)\n";
