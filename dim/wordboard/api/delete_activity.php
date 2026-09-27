<?php
session_start();
require_once 'file_utils.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(array('success' => false, 'message' => 'Invalid request method.'));
    exit;
}

if (!isset($_SESSION['username']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'publisher')) {
    echo json_encode(array('success' => false, 'message' => 'Unauthorized.'));
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

if ($_SESSION['role'] === 'publisher') {
    if (!isset($activity['created_by']) || $activity['created_by'] !== $_SESSION['username']) {
        echo json_encode(array('success' => false, 'message' => 'You can only delete your own activities.'));
        exit;
    }
}

if (unlink($filePath)) {
    removeIndexEntry($id, $activitiesDir);
    echo json_encode(array('success' => true, 'message' => 'Activity deleted successfully.'));
} else {
    echo json_encode(array('success' => false, 'message' => 'Failed to delete activity.'));
}
