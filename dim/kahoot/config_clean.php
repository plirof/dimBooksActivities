<?php

session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('DATA_DIR', __DIR__ . '/data');
define('SESSION_DIR', __DIR__ . '/sessions');

define('DEFAULT_ADMIN_USERNAME', 'admin');
define('DEFAULT_ADMIN_PASSWORD', 'admin123');

define('POLL_INTERVAL', 1500);
define('GAME_PIN_LENGTH', 4);

define('MIN_USERNAME_LENGTH', 3);
define('MAX_USERNAME_LENGTH', 20);
define('MIN_PASSWORD_LENGTH', 6);
define('MAX_PASSWORD_LENGTH', 50);

define('ANSWER_COLORS', ['#46178f', '#1368ce', '#26890c', '#d69e00']);

if (!file_exists(DATA_DIR)) {
    mkdir(DATA_DIR, 0755, true);
}

if (!file_exists(SESSION_DIR)) {
    mkdir(SESSION_DIR, 0755, true);
}

function getFileLock($handle, $lockType) {
    $timeout = 5;
    $startTime = time();
    while (!flock($handle, $lockType | LOCK_NB)) {
        if (time() - $startTime >= $timeout) {
            return false;
        }
        usleep(100000);
    }
    return true;
}

function readJSONFile($filename) {
    if (!file_exists($filename)) {
        return null;
    }
    $handle = fopen($filename, 'r');
    if (!$handle) {
        return null;
    }
    if (!getFileLock($handle, LOCK_SH)) {
        fclose($handle);
        return null;
    }
    $content = stream_get_contents($handle);
    flock($handle, LOCK_UN);
    fclose($handle);
    return json_decode($content, true);
}

function writeJSONFile($filename, $data) {
    $handle = fopen($filename, 'c');
    if (!$handle) {
        return false;
    }
    if (!getFileLock($handle, LOCK_EX)) {
        fclose($handle);
        return false;
    }
    ftruncate($handle, 0);
    rewind($handle);
    fwrite($handle, json_encode($data, JSON_PRETTY_PRINT));
    flock($handle, LOCK_UN);
    fclose($handle);
    return true;
}

function getUsers() {
    $data = readJSONFile(DATA_DIR . '/users.json');
    if ($data === null) {
        $defaultUsers = [
            'users' => [
                [
                    'username' => DEFAULT_ADMIN_USERNAME,
                    'password' => password_hash(DEFAULT_ADMIN_PASSWORD, PASSWORD_DEFAULT),
                    'role' => 'admin'
                ]
            ]
        ];
        writeJSONFile(DATA_DIR . '/users.json', $defaultUsers);
        return $defaultUsers;
    }
    return $data;
}

function writeUsers($users) {
    return writeJSONFile(DATA_DIR . '/users.json', $users);
}

function getQuizzes() {
    $data = readJSONFile(DATA_DIR . '/quizzes.json');
    if ($data === null) {
        return ['quizzes' => []];
    }
    return $data;
}

function writeQuizzes($quizzes) {
    return writeJSONFile(DATA_DIR . '/quizzes.json', $quizzes);
}

function getGames() {
    $data = readJSONFile(DATA_DIR . '/games.json');
    if ($data === null) {
        return ['active_games' => []];
    }
    return $data;
}

function writeGames($games) {
    return writeJSONFile(DATA_DIR . '/games.json', $games);
}

function getGameSession($gamePin) {
    return readJSONFile(SESSION_DIR . '/' . $gamePin . '.json');
}

function writeGameSession($gamePin, $session) {
    return writeJSONFile(SESSION_DIR . '/' . $gamePin . '.json', $session);
}

function generateGamePin() {
    do {
        $pin = strval(mt_rand(1000, 9999));
        $games = getGames();
    } while (isset($games['active_games'][$pin]));
    return $pin;
}

function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function generateId() {
    return uniqid('', true);
}

function getCurrentUser() {
    return isset($_SESSION['user']) ? $_SESSION['user'] : null;
}

function isAdmin() {
    $user = getCurrentUser();
    return $user && $user['role'] === 'admin';
}

function isLoggedIn() {
    return getCurrentUser() !== null;
}

function redirect($url) {
    if (!preg_match('/^(\/|https?:|\.\/|\.\.\/|[^\/]+\/)/', $url)) {
        $url = '../' . $url;
    }
    header('Location: ' . $url);
    exit;
}

?>
