<?php
require_once '../config.php';

if (!isLoggedIn()) {
    header('Content-Type: application/json');
    $user = getCurrentUser();
    echo json_encode(['success' => false, 'error' => 'Unauthorized', 'debug' => [
        'has_session' => isset($_SESSION),
        'has_user' => isset($_SESSION['user']),
        'user' => $user,
        'session_id' => session_id()
    ]]);
    exit;
}

$user = getCurrentUser();
$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($action)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

header('Content-Type: application/json');

if ($action === 'create') {
    if (!isAdmin()) {
        echo json_encode(['success' => false, 'error' => 'Only admins can create games']);
        exit;
    }
    
    $quizId = sanitizeInput($_POST['quiz_id']);
    
    if (empty($quizId)) {
        echo json_encode(['success' => false, 'error' => 'Quiz ID is required']);
        exit;
    }
    
    $quizzes = getQuizzes();
    $quiz = null;
    foreach ($quizzes['quizzes'] as $q) {
        if ($q['id'] === $quizId) {
            $quiz = $q;
            break;
        }
    }
    
    if (!$quiz) {
        echo json_encode(['success' => false, 'error' => 'Quiz not found']);
        exit;
    }
    
    $pin = generateGamePin();
    
    $games = getGames();
    $games['active_games'][$pin] = [
        'quiz_id' => $quizId,
        'host' => $user['username'],
        'status' => 'waiting',
        'current_question' => 0,
        'participants' => [],
        'question_start_time' => null,
        'question_end_time' => null
    ];
    
    $sessionData = [
        'quiz' => $quiz,
        'answers' => [],
        'scores' => [],
        'streaks' => [],
        'question_results' => []
    ];
    
    if (isset($quiz['randomize_questions']) && $quiz['randomize_questions']) {
        shuffle($sessionData['quiz']['questions']);
        $sessionData['quiz']['questions'] = array_values($sessionData['quiz']['questions']);
    }
    
    if (isset($quiz['randomize_answers']) && $quiz['randomize_answers']) {
        foreach ($sessionData['quiz']['questions'] as &$question) {
            $originalAnswers = $question['answers'];
            $originalCorrect = $question['correct'];
            $correctAnswer = $originalAnswers[$originalCorrect];
            
            shuffle($question['answers']);
            $question['answers'] = array_values($question['answers']);
            
            $newCorrectIndex = array_search($correctAnswer, $question['answers']);
            $question['correct'] = $newCorrectIndex;
        }
        unset($question);
    }
    
    if (writeGames($games) && writeGameSession($pin, $sessionData)) {
        echo json_encode(['success' => true, 'pin' => $pin]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to create game']);
    }

} elseif ($action === 'start') {
    $pin = sanitizeInput($_POST['pin']);
    
    if (empty($pin)) {
        echo json_encode(['success' => false, 'error' => 'Game PIN is required']);
        exit;
    }
    
    $games = getGames();
    
    if (!isset($games['active_games'][$pin])) {
        echo json_encode(['success' => false, 'error' => 'Game not found']);
        exit;
    }
    
    if ($games['active_games'][$pin]['host'] !== $user['username']) {
        echo json_encode(['success' => false, 'error' => 'You are not the host of this game']);
        exit;
    }
    
    $games['active_games'][$pin]['status'] = 'playing';
    $games['active_games'][$pin]['current_question'] = 0;
    
    if (writeGames($games)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to start game']);
    }

} elseif ($action === 'next_question') {
    $pin = sanitizeInput($_POST['pin']);
    
    if (empty($pin)) {
        echo json_encode(['success' => false, 'error' => 'Game PIN is required']);
        exit;
    }
    
    $games = getGames();
    
    if (!isset($games['active_games'][$pin])) {
        echo json_encode(['success' => false, 'error' => 'Game not found']);
        exit;
    }
    
    $games['active_games'][$pin]['current_question']++;
    $games['active_games'][$pin]['question_start_time'] = time();
    
    $session = getGameSession($pin);
    if ($session) {
        $session['question_results'][] = [
            'question_index' => $games['active_games'][$pin]['current_question'] - 1,
            'answers' => $session['answers']
        ];
        $session['answers'] = [];
        writeGameSession($pin, $session);
    }
    
    if (writeGames($games)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to advance question']);
    }

} elseif ($action === 'end') {
    $pin = sanitizeInput($_POST['pin']);
    
    if (empty($pin)) {
        echo json_encode(['success' => false, 'error' => 'Game PIN is required']);
        exit;
    }
    
    $games = getGames();
    
    if (!isset($games['active_games'][$pin])) {
        echo json_encode(['success' => false, 'error' => 'Game not found']);
        exit;
    }
    
    $games['active_games'][$pin]['status'] = 'ended';
    
    if (writeGames($games)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to end game']);
    }

} elseif ($action === 'delete') {
    $pin = sanitizeInput($_POST['pin']);
    
    if (empty($pin)) {
        echo json_encode(['success' => false, 'error' => 'Game PIN is required']);
        exit;
    }
    
    $games = getGames();
    
    if (!isset($games['active_games'][$pin])) {
        echo json_encode(['success' => false, 'error' => 'Game not found']);
        exit;
    }
    
    if ($games['active_games'][$pin]['host'] !== $user['username']) {
        echo json_encode(['success' => false, 'error' => 'You are not the host of this game']);
        exit;
    }
    
    unset($games['active_games'][$pin]);
    
    $sessionFile = SESSION_DIR . '/' . $pin . '.json';
    if (file_exists($sessionFile)) {
        unlink($sessionFile);
    }
    
    if (writeGames($games)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to delete game']);
    }

} elseif ($action === 'leave') {
    $pin = sanitizeInput($_POST['pin']);
    
    if (empty($pin)) {
        echo json_encode(['success' => false, 'error' => 'Game PIN is required']);
        exit;
    }
    
    $games = getGames();
    
    if (!isset($games['active_games'][$pin])) {
        echo json_encode(['success' => false, 'error' => 'Game not found']);
        exit;
    }
    
    if (!isset($games['active_games'][$pin]['participants'][$user['username']])) {
        echo json_encode(['success' => false, 'error' => 'You are not in this game']);
        exit;
    }
    
    unset($games['active_games'][$pin]['participants'][$user['username']]);
    
    if (writeGames($games)) {
        unset($_SESSION['game_pin']);
        unset($_SESSION['player_name']);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to leave game']);
    }

} else {
    echo json_encode(['success' => false, 'error' => 'Unknown action']);
}
?>
