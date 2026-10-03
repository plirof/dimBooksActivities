<?php
session_start();
require_once 'file_utils.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(array('success' => false, 'message' => 'Invalid request method.'));
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id'])) {
    echo json_encode(array('success' => false, 'message' => 'Activity ID is required.'));
    exit;
}

$id = $data['id'];
$activitiesDir = '../admin/activities/';

$filePath = findActivityFile($id, $activitiesDir);

if ($filePath === null) {
    echo json_encode(array('success' => false, 'message' => 'Activity not found.'));
    exit;
}

$activity = readJSONFile($filePath);

if ($activity === null) {
    echo json_encode(array('success' => false, 'message' => 'Activity not found.'));
    exit;
}

echo json_encode(array('success' => true, 'activity' => $activity), JSON_UNESCAPED_UNICODE);
