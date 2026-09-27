<?php
require_once '../config.php';

header('Content-Type: application/json');

$pin = isset($_GET['pin']) ? sanitizeInput($_GET['pin']) : '';

if (empty($pin)) {
    echo json_encode(['success' => false, 'error' => 'Game PIN is required']);
    exit;
}

$games = getGames();

if (!isset($games['active_games'][$pin])) {
    echo json_encode(['success' => false, 'error' => 'Game not found']);
    exit;
}

$game = $games['active_games'][$pin];

echo json_encode([
    'success' => true,
    'status' => $game['status'],
    'participants' => $game['participants']
]);
?>
