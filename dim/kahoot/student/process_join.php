<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('./join.php');
}

$pin = sanitizeInput($_POST['pin']);
$username = sanitizeInput($_POST['username']);

if (empty($pin) || strlen($pin) !== 4 || !is_numeric($pin)) {
    $_SESSION['error'] = '❌ Invalid Game PIN! Please enter a 4-digit number.';
    redirect('./join.php');
}

if (strlen($username) < 3 || strlen($username) > 20) {
    $_SESSION['error'] = '❌ Nickname must be between 3 and 20 characters!';
    redirect('./join.php');
}

$games = getGames();

if (!isset($games['active_games'][$pin])) {
    $_SESSION['error'] = '❌ Game not found! Please check the Game PIN.';
    redirect('./join.php');
}

$game = $games['active_games'][$pin];

if ($game['status'] !== 'waiting') {
    $_SESSION['error'] = '❌ Game has already started! Please wait for the next game.';
    redirect('./join.php');
}

if (isset($game['participants'][$username])) {
    $_SESSION['error'] = '❌ This nickname is already taken! Please choose another.';
    redirect('./join.php');
}

$games['active_games'][$pin]['participants'][$username] = [
    'joined_at' => time()
];

if (!writeGames($games)) {
    $_SESSION['error'] = '❌ Failed to join game! Please try again.';
    redirect('./join.php');
}

$_SESSION['game_pin'] = $pin;
$_SESSION['player_name'] = $username;
$_SESSION['user'] = [
    'username' => $username,
    'role' => 'student'
];

redirect('./waiting.php');
?>
