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

if (!isset($data['title']) || !isset($data['type']) || !isset($data['data'])) {
    echo json_encode(array('success' => false, 'message' => 'Missing required fields.'));
    exit;
}

$type = $data['type'];
$activitiesDir = '../admin/activities/';
$typeDir = $activitiesDir . $type . '/';

if (!is_dir($typeDir)) {
    mkdir($typeDir, 0755, true);
}

$isUpdate = isset($data['id']) && $data['id'] !== '';

if ($isUpdate) {
    $id = $data['id'];
    $filePath = findActivityFile($id, $activitiesDir);

    if ($filePath !== null && file_exists($filePath)) {
        $existing = readJSONFile($filePath);

        if ($_SESSION['role'] === 'publisher') {
            if (!isset($existing['created_by']) || $existing['created_by'] !== $_SESSION['username']) {
                echo json_encode(array('success' => false, 'message' => 'You can only edit your own activities.'));
                exit;
            }
        }

        $activity = $existing;
        $activity['title'] = $data['title'];
        $activity['type'] = $data['type'];
        $activity['tags'] = isset($data['tags']) && is_array($data['tags']) ? $data['tags'] : array();
        $activity['data'] = $data['data'];
        $filePath = $typeDir . $id . '.json';
    } else {
        echo json_encode(array('success' => false, 'message' => 'Activity not found.'));
        exit;
    }
} else {
    $tags = isset($data['tags']) && is_array($data['tags']) ? $data['tags'] : array();
    $dimTag = '';
    $lesTag = '';
    foreach ($tags as $tag) {
        if (preg_match('/^(dim[A-Z]+)$/', $tag, $m)) $dimTag = $m[1];
        if (preg_match('/^les(\d+)$/', $tag, $m)) $lesTag = 'les' . str_pad($m[1], 2, '0', STR_PAD_LEFT);
    }

    if ($dimTag && $lesTag) {
        $prefix = $dimTag . '-' . $lesTag;
    } else {
        $prefix = 'gen-000';
    }

    $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
    $rand = '';
    for ($i = 0; $i < 3; $i++) {
        $rand .= $chars[mt_rand(0, strlen($chars) - 1)];
    }
    $id = $prefix . '-' . $type . '-' . $rand;

    $maxAttempts = 50;
    $attempt = 0;
    while (file_exists($typeDir . $id . '.json') && $attempt < $maxAttempts) {
        $rand = '';
        for ($i = 0; $i < 3; $i++) {
            $rand .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        $id = $prefix . '-' . $type . '-' . $rand;
        $attempt++;
    }

    $filePath = $typeDir . $id . '.json';

    $activity = array(
        'id' => $id,
        'title' => $data['title'],
        'type' => $data['type'],
        'tags' => $tags,
        'created_by' => isset($data['created_by']) ? $data['created_by'] : $_SESSION['username'],
        'created_date' => isset($data['created_date']) ? $data['created_date'] : date('Y-m-d H:i:s'),
        'data' => $data['data']
    );
}

if (writeJSONFile($filePath, $activity)) {
    updateIndexEntry($id, $activity, $activitiesDir);
    echo json_encode(array('success' => true, 'id' => $id));
} else {
    echo json_encode(array('success' => false, 'message' => 'Failed to save activity.'));
}
