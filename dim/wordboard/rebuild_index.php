<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(array('success' => false, 'message' => 'POST only.'));
    exit;
}

require_once __DIR__ . '/api/file_utils.php';

$count = rebuildActivityIndex();

if ($count > 0) {
    echo json_encode(array('success' => true, 'count' => $count));
} else {
    echo json_encode(array('success' => false, 'message' => 'No activities found.'));
}
