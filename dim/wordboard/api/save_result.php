<?php
session_start();
require_once 'file_utils.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(array('success' => false, 'message' => 'Invalid request method.'));
    exit;
}

if (!isset($_SESSION['username'])) {
    echo json_encode(array('success' => false, 'message' => 'Unauthorized.'));
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['activity_id']) || !isset($data['score'])) {
    echo json_encode(array('success' => false, 'message' => 'Missing required fields.'));
    exit;
}

$activityId = $data['activity_id'];
$score = $data['score'];
$username = $_SESSION['username'];

$results = readJSONFile('../student/results.json');
if ($results === null) {
    $results = array();
}

if (!isset($results[$activityId])) {
    $results[$activityId] = array();
}

$results[$activityId][$username] = array(
    'score' => $score,
    'completed_date' => date('Y-m-d H:i:s')
);

if (writeJSONFile('../student/results.json', $results)) {
    echo json_encode(array('success' => true));
} else {
    echo json_encode(array('success' => false, 'message' => 'Failed to save result.'));
}
