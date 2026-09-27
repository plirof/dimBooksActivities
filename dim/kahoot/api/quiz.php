<?php
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
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
    $title = sanitizeInput($_POST['title']);
    $description = isset($_POST['description']) ? sanitizeInput($_POST['description']) : '';
    $category = isset($_POST['category']) ? sanitizeInput($_POST['category']) : '';
    $tags = isset($_POST['tags']) ? sanitizeInput($_POST['tags']) : '';
    $questions = isset($_POST['questions']) ? $_POST['questions'] : [];
    $randomizeQuestions = isset($_POST['randomize_questions']) ? true : false;
    $randomizeAnswers = isset($_POST['randomize_answers']) ? true : false;
    $allowSolo = isset($_POST['allow_solo']) ? true : false;
    
    if (empty($title)) {
        echo json_encode(['success' => false, 'error' => 'Quiz title is required']);
        exit;
    }
    
    if (empty($questions)) {
        echo json_encode(['success' => false, 'error' => 'At least one question is required']);
        exit;
    }
    
    $validQuestions = [];
    foreach ($questions as $q) {
        if (empty($q['text']) || empty($q['answers']) || !isset($q['correct'])) {
            continue;
        }
        
        $answers = array_filter($q['answers'], function($a) { return !empty(trim($a)); });
        if (count($answers) < 2) {
            continue;
        }
        
        $validQuestions[] = [
            'id' => generateId(),
            'text' => sanitizeInput($q['text']),
            'answers' => array_map('sanitizeInput', array_values($answers)),
            'correct' => intval($q['correct']),
            'explanation' => isset($q['explanation']) ? sanitizeInput($q['explanation']) : '',
            'time' => isset($q['time']) ? max(10, min(120, intval($q['time']))) : 20,
            'points' => 1000
        ];
    }
    
    if (empty($validQuestions)) {
        echo json_encode(['success' => false, 'error' => 'No valid questions! Please fill in all required fields.']);
        exit;
    }
    
    $quizzes = getQuizzes();
    $quizzes['quizzes'][] = [
        'id' => generateId(),
        'title' => $title,
        'description' => $description,
        'category' => $category,
        'tags' => $tags,
        'questions' => $validQuestions,
        'randomize_questions' => $randomizeQuestions,
        'randomize_answers' => $randomizeAnswers,
        'allow_solo' => $allowSolo,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    if (writeQuizzes($quizzes)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to create quiz']);
    }
} elseif ($action === 'import_json') {
    $quizJson = isset($_POST['quiz_json']) ? $_POST['quiz_json'] : '';

    if (empty($quizJson)) {
        echo json_encode(['success' => false, 'error' => 'Quiz JSON is required']);
        exit;
    }

    $quiz = json_decode($quizJson, true);

    if (!$quiz || !isset($quiz['title']) || !isset($quiz['questions'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid quiz format']);
        exit;
    }

    if (empty($quiz['questions'])) {
        echo json_encode(['success' => false, 'error' => 'At least one question is required']);
        exit;
    }

    $validQuestions = [];
    foreach ($quiz['questions'] as $q) {
        if (empty($q['text']) || empty($q['answers']) || !isset($q['correct'])) {
            continue;
        }

        $answers = array_filter($q['answers'], function($a) { return !empty(trim($a)); });
        if (count($answers) < 2) {
            continue;
        }

        $correctIndex = intval($q['correct']);
        if ($correctIndex < 0 || $correctIndex >= count($answers)) {
            echo json_encode(['success' => false, 'error' => 'Invalid correct answer index']);
            exit;
        }

        $validQuestions[] = [
            'id' => generateId(),
            'text' => sanitizeInput($q['text']),
            'answers' => array_map('sanitizeInput', array_values($answers)),
            'correct' => $correctIndex,
            'explanation' => isset($q['explanation']) ? sanitizeInput($q['explanation']) : '',
            'time' => isset($q['time']) ? max(10, min(120, intval($q['time']))) : 20,
            'points' => 1000
        ];
    }

    if (empty($validQuestions)) {
        echo json_encode(['success' => false, 'error' => 'No valid questions! Please check your JSON format.']);
        exit;
    }

    $quizzes = getQuizzes();
    $quizzes['quizzes'][] = [
        'id' => generateId(),
        'title' => sanitizeInput($quiz['title']),
        'description' => isset($quiz['description']) ? sanitizeInput($quiz['description']) : '',
        'category' => isset($quiz['category']) ? sanitizeInput($quiz['category']) : '',
        'tags' => isset($quiz['tags']) ? sanitizeInput($quiz['tags']) : '',
        'questions' => $validQuestions,
        'randomize_questions' => isset($quiz['randomize_questions']) ? $quiz['randomize_questions'] : false,
        'randomize_answers' => isset($quiz['randomize_answers']) ? $quiz['randomize_answers'] : false,
        'allow_solo' => isset($quiz['allow_solo']) ? $quiz['allow_solo'] : false,
        'created_at' => date('Y-m-d H:i:s')
    ];

    if (writeQuizzes($quizzes)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to import quiz']);
    }

} elseif ($action === 'update') {
    $quizId = sanitizeInput($_POST['quiz_id']);
    $title = sanitizeInput($_POST['title']);
    $description = isset($_POST['description']) ? sanitizeInput($_POST['description']) : '';
    $category = isset($_POST['category']) ? sanitizeInput($_POST['category']) : '';
    $tags = isset($_POST['tags']) ? sanitizeInput($_POST['tags']) : '';
    $questions = isset($_POST['questions']) ? $_POST['questions'] : [];
    $randomizeQuestions = isset($_POST['randomize_questions']) ? true : false;
    $randomizeAnswers = isset($_POST['randomize_answers']) ? true : false;
    $allowSolo = isset($_POST['allow_solo']) ? true : false;
    
    if (empty($quizId)) {
        echo json_encode(['success' => false, 'error' => 'Quiz ID is required']);
        exit;
    }
    
    if (empty($title)) {
        echo json_encode(['success' => false, 'error' => 'Quiz title is required']);
        exit;
    }
    
    $quizzes = getQuizzes();
    $sourceQuiz = null;
    foreach ($quizzes['quizzes'] as $quiz) {
        if ($quiz['id'] === $quizId) {
            $sourceQuiz = $quiz;
            break;
        }
    }
    
    if (!$sourceQuiz) {
        echo json_encode(['success' => false, 'error' => 'Quiz not found']);
        exit;
    }
    
    $validQuestions = [];
    foreach ($questions as $q) {
        if (empty($q['text']) || empty($q['answers']) || !isset($q['correct'])) {
            continue;
        }
        
        $answers = array_filter($q['answers'], function($a) { return !empty(trim($a)); });
        if (count($answers) < 2) {
            continue;
        }
        
        $validQuestions[] = [
            'id' => isset($q['id']) ? sanitizeInput($q['id']) : generateId(),
            'text' => sanitizeInput($q['text']),
            'answers' => array_map('sanitizeInput', array_values($answers)),
            'correct' => intval($q['correct']),
            'explanation' => isset($q['explanation']) ? sanitizeInput($q['explanation']) : '',
            'time' => isset($q['time']) ? max(10, min(120, intval($q['time']))) : 20,
            'points' => 1000
        ];
    }
    
    if (empty($validQuestions)) {
        echo json_encode(['success' => false, 'error' => 'No valid questions! Please fill in all required fields.']);
        exit;
    }
    
    $found = false;
    foreach ($quizzes['quizzes'] as $index => $quiz) {
        if ($quiz['id'] === $quizId) {
            $quizzes['quizzes'][$index]['title'] = $title;
            $quizzes['quizzes'][$index]['description'] = $description;
            $quizzes['quizzes'][$index]['category'] = $category;
            $quizzes['quizzes'][$index]['tags'] = $tags;
            $quizzes['quizzes'][$index]['questions'] = $validQuestions;
            $quizzes['quizzes'][$index]['randomize_questions'] = $randomizeQuestions;
            $quizzes['quizzes'][$index]['randomize_answers'] = $randomizeAnswers;
            $quizzes['quizzes'][$index]['allow_solo'] = $allowSolo;
            $quizzes['quizzes'][$index]['updated_at'] = date('Y-m-d H:i:s');
            $found = true;
            break;
        }
    }
    
    if ($found && writeQuizzes($quizzes)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update quiz']);
    }
}
