<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'student') {
    header('Location: ../index.php');
    exit;
}

require_once '../api/file_utils.php';

if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

$activityId = $_GET['id'];

$filePath = findActivityFile($activityId, '../admin/activities/');
$activity = null;

if ($filePath !== null) {
    $activity = readJSONFile($filePath);
}

if ($activity === null) {
    echo 'Activity not found.';
    exit;
}

$gameFile = '';
switch ($activity['type']) {
    case 'quiz': $gameFile = 'games/quiz/quiz.html'; break;
    case 'match': $gameFile = 'games/match/match.html'; break;
    case 'wheel': $gameFile = 'games/wheel/wheel.html'; break;
    case 'crossword': $gameFile = 'games/crossword/crossword.html'; break;
    case 'wordsearch': $gameFile = 'games/wordsearch/wordsearch.html'; break;
    case 'missingword': $gameFile = 'games/missingword/missingword.html'; break;
    case 'groupsort': $gameFile = 'games/groupsort/groupsort.html'; break;
    default: echo 'Invalid activity type.'; exit;
}

header('Location: ' . $gameFile . '?id=' . $activityId);
exit;