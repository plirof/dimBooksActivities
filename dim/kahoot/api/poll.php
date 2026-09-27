<?php
require_once '../config.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

$pin = isset($_GET['pin']) ? sanitizeInput($_GET['pin']) : (isset($_POST['pin']) ? sanitizeInput($_POST['pin']) : '');
$action = isset($_GET['action']) ? sanitizeInput($_GET['action']) : (isset($_POST['action']) ? sanitizeInput($_POST['action']) : '');

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
$session = getGameSession($pin);

if (!$session) {
    echo json_encode(['success' => false, 'error' => 'Game session not found']);
    exit;
}

if ($action === 'status') {
    echo json_encode([
        'success' => true,
        'status' => $game['status'],
        'current_question' => $game['current_question'],
        'quiz' => [
            'title' => $session['quiz']['title'],
            'questions' => count($session['quiz']['questions'])
        ]
    ]);

} elseif ($action === 'question') {
    if (!isset($session['quiz']['questions'][$game['current_question']])) {
        echo json_encode(['success' => false, 'error' => 'No more questions']);
        exit;
    }
    
    $question = $session['quiz']['questions'][$game['current_question']];
    
    echo json_encode([
        'success' => true,
        'question' => [
            'text' => $question['text'],
            'answers' => $question['answers'],
            'explanation' => isset($question['explanation']) ? $question['explanation'] : '',
            'time' => $question['time']
        ],
        'question_number' => $game['current_question'] + 1,
        'total_questions' => count($session['quiz']['questions'])
    ]);

} elseif ($action === 'host_question') {
    if (!isset($session['quiz']['questions'][$game['current_question']])) {
        echo json_encode(['success' => false, 'error' => 'No more questions']);
        exit;
    }
    
    $question = $session['quiz']['questions'][$game['current_question']];
    
    echo json_encode([
        'success' => true,
        'question' => $question,
        'question_number' => $game['current_question'] + 1,
        'total_questions' => count($session['quiz']['questions'])
    ]);

} elseif ($action === 'answers') {
    $answers = [0, 0, 0, 0];
    $questionIndex = $game['current_question'];
    
    if (isset($session['answers'][$questionIndex])) {
        foreach ($session['answers'][$questionIndex] as $answer) {
            if (isset($answer['answer']) && $answer['answer'] >= 0 && $answer['answer'] <= 3) {
                $answers[$answer['answer']]++;
            }
        }
    }
    
    $totalAnswers = isset($session['answers'][$questionIndex]) ? count($session['answers'][$questionIndex]) : 0;
    
    echo json_encode([
        'success' => true,
        'answers' => $answers,
        'total_answers' => $totalAnswers
    ]);

} elseif ($action === 'submit_answer') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'error' => 'POST required']);
        exit;
    }
    
    $playerName = isset($_SESSION['player_name']) ? $_SESSION['player_name'] : '';
    
    if (empty($playerName) || !isset($game['participants'][$playerName])) {
        echo json_encode(['success' => false, 'error' => 'Not in game']);
        exit;
    }
    
    $answer = isset($_POST['answer']) ? intval($_POST['answer']) : -1;
    
    if ($answer < 0 || $answer > 3) {
        echo json_encode(['success' => false, 'error' => 'Invalid answer']);
        exit;
    }
    
    if ($game['status'] !== 'playing') {
        echo json_encode(['success' => false, 'error' => 'Game not in progress']);
        exit;
    }
    
    $questionIndex = $game['current_question'];
    
    if (!isset($session['answers'][$questionIndex])) {
        $session['answers'][$questionIndex] = [];
    }
    
    $alreadyAnswered = false;
    foreach ($session['answers'][$questionIndex] as $a) {
        if ($a['username'] === $playerName) {
            $alreadyAnswered = true;
            break;
        }
    }
    
    if ($alreadyAnswered) {
        echo json_encode(['success' => false, 'error' => 'Already answered']);
        exit;
    }
    
    $question = $session['quiz']['questions'][$questionIndex];
    $isCorrect = ($answer === $question['correct']);
    
    $effectiveTime = $question['time'] === 0 ? 30 : $question['time'];
    $timeTaken = isset($game['question_start_time']) ? (time() - $game['question_start_time']) : $effectiveTime;
    
    if ($question['time'] === 0) {
        $scoreMultiplier = 1.0;
    } else {
        $timeLeft = max(1, $question['time'] - $timeTaken);
        $scoreMultiplier = $timeLeft / $question['time'];
    }
    $pointsEarned = round($question['points'] * $scoreMultiplier);
    
    if (!isset($session['scores'][$playerName])) {
        $session['scores'][$playerName] = 0;
    }
    
    if (!isset($session['streaks'][$playerName])) {
        $session['streaks'][$playerName] = 0;
    }
    
    if ($isCorrect) {
        $session['streaks'][$playerName]++;
        
        $streakBonus = 1.0;
        $streak = $session['streaks'][$playerName];
        if ($streak >= 3) {
            $streakBonus = 1.1;
        } elseif ($streak >= 5) {
            $streakBonus = 1.2;
        } elseif ($streak >= 7) {
            $streakBonus = 1.3;
        }
        
        $pointsEarned = round($pointsEarned * $streakBonus);
        $session['scores'][$playerName] += $pointsEarned;
    } else {
        $session['streaks'][$playerName] = 0;
    }
    
    $currentStreak = $session['streaks'][$playerName];
    
    $session['answers'][$questionIndex][] = [
        'username' => $playerName,
        'answer' => $answer,
        'is_correct' => $isCorrect,
        'points' => $isCorrect ? $pointsEarned : 0,
        'time_taken' => $timeTaken,
        'streak' => $currentStreak
    ];
    
    writeGameSession($pin, $session);
    
    echo json_encode([
        'success' => true,
        'is_correct' => $isCorrect,
        'points' => $isCorrect ? $pointsEarned : 0,
        'streak' => $currentStreak,
        'total_score' => $session['scores'][$playerName]
    ]);

} elseif ($action === 'final_results') {
    $leaderboard = [];
    
    if (isset($session['scores'])) {
        foreach ($session['scores'] as $username => $score) {
            $leaderboard[] = [
                'username' => $username,
                'score' => $score
            ];
        }
    }
    
    usort($leaderboard, function($a, $b) {
        return $b['score'] - $a['score'];
    });
    
    echo json_encode([
        'success' => true,
        'leaderboard' => $leaderboard
    ]);

} elseif ($action === 'export_results') {
    $csv = "Username,Score\n";
    
    if (isset($session['scores'])) {
        foreach ($session['scores'] as $username => $score) {
            $csv .= '"' . $username . '",' . $score . "\n";
        }
    }
    
    echo json_encode([
        'success' => true,
        'csv' => $csv
    ]);

} elseif ($action === 'player_result') {
    $playerName = isset($_SESSION['player_name']) ? $_SESSION['player_name'] : '';
    
    if (empty($playerName) || !isset($game['participants'][$playerName])) {
        echo json_encode(['success' => false, 'error' => 'Not in game']);
        exit;
    }
    
    $questionIndex = $game['current_question'];
    
    $playerResult = null;
    if (isset($session['answers'][$questionIndex])) {
        foreach ($session['answers'][$questionIndex] as $answer) {
            if ($answer['username'] === $playerName) {
                $playerResult = $answer;
                break;
            }
        }
    }
    
    $question = $session['quiz']['questions'][$questionIndex];
    
    $correctCount = 0;
    $totalCount = 0;
    if (isset($session['answers'][$questionIndex])) {
        $totalCount = count($session['answers'][$questionIndex]);
        foreach ($session['answers'][$questionIndex] as $answer) {
            if ($answer['is_correct']) {
                $correctCount++;
            }
        }
    }
    
    $percentage = $totalCount > 0 ? round(($correctCount / $totalCount) * 100, 1) : 0;
    
    $yourAnswer = $playerResult && isset($question['answers'][$playerResult['answer']]) 
        ? $question['answers'][$playerResult['answer']] 
        : '';
    $correctAnswer = isset($question['answers'][$question['correct']]) 
        ? $question['answers'][$question['correct']] 
        : '';
    
    echo json_encode([
        'success' => true,
        'is_correct' => $playerResult ? $playerResult['is_correct'] : false,
        'your_answer' => $yourAnswer,
        'correct_answer' => $correctAnswer,
        'your_answer_index' => $playerResult ? $playerResult['answer'] : -1,
        'correct_answer_index' => $question['correct'],
        'percentage' => $percentage,
        'correct_count' => $correctCount,
        'total_count' => $totalCount,
        'streak' => $playerResult ? $playerResult['streak'] : 0,
        'points' => $playerResult ? $playerResult['points'] : 0,
        'total_score' => isset($session['scores'][$playerName]) ? $session['scores'][$playerName] : 0
    ]);

} elseif ($action === 'leaderboard') {
    $leaderboard = [];
    
    if (isset($session['scores'])) {
        foreach ($session['scores'] as $username => $score) {
            $leaderboard[] = [
                'username' => $username,
                'score' => $score
            ];
        }
    }
    
    usort($leaderboard, function($a, $b) {
        return $b['score'] - $a['score'];
    });
    
    echo json_encode([
        'success' => true,
        'leaderboard' => array_slice($leaderboard, 0, 10)
    ]);

} elseif ($action === 'join') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'error' => 'POST required']);
        exit;
    }
    
    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'error' => 'Not logged in']);
        exit;
    }
    
    $user = getCurrentUser();
    
    if ($game['status'] !== 'waiting') {
        echo json_encode(['success' => false, 'error' => 'Game not accepting players']);
        exit;
    }
    
    if (isset($game['participants'][$user['username']])) {
        echo json_encode(['success' => false, 'error' => 'Already joined']);
        exit;
    }
    
    $games['active_games'][$pin]['participants'][$user['username']] = [
        'joined_at' => time()
    ];
    
    if (writeGames($games)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to join game']);
    }

} else {
    echo json_encode(['success' => false, 'error' => 'Unknown action']);
}
?>
