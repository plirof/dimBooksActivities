<?php
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    $_SESSION['error'] = '⛔ You need to login as admin first!';
    redirect('../index.php');
}

$user = getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'import') {
    header('Content-Type: application/json');
    $quizData = isset($_POST['quiz_data']) ? $_POST['quiz_data'] : '';
    
    if (empty($quizData)) {
        echo json_encode(['success' => false, 'error' => 'No quiz data provided']);
        exit;
    }
    
    $quiz = json_decode($quizData, true);
    
    if (!$quiz || !isset($quiz['title']) || !isset($quiz['questions'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid quiz format']);
        exit;
    }
    
    $newQuiz = [
        'id' => generateId(),
        'title' => $quiz['title'] . ' (Imported)',
        'description' => isset($quiz['description']) ? $quiz['description'] : '',
        'questions' => isset($quiz['questions']) ? $quiz['questions'] : [],
        'randomize_questions' => isset($quiz['randomize_questions']) ? $quiz['randomize_questions'] : false,
        'randomize_answers' => isset($quiz['randomize_answers']) ? $quiz['randomize_answers'] : false,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    foreach ($newQuiz['questions'] as &$question) {
        $question['id'] = generateId();
        if (!isset($question['time'])) {
            $question['time'] = 20;
        }
        if (!isset($question['points'])) {
            $question['points'] = 1000;
        }
    }
    unset($question);
    
    $quizzes = getQuizzes();
    $quizzes['quizzes'][] = $newQuiz;
    
    if (writeQuizzes($quizzes)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to save quiz']);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['duplicate_quiz'])) {
    $quizId = sanitizeInput($_POST['quiz_id']);
    $quizzes = getQuizzes();
    
    $sourceQuiz = null;
    foreach ($quizzes['quizzes'] as $quiz) {
        if ($quiz['id'] === $quizId) {
            $sourceQuiz = $quiz;
            break;
        }
    }
    
    if (!$sourceQuiz) {
        $_SESSION['error'] = '❌ Quiz not found!';
        redirect('./manage_quizzes.php');
    }
    
    $duplicatedQuiz = [
        'id' => generateId(),
        'title' => $sourceQuiz['title'] . ' (Copy)',
        'description' => $sourceQuiz['description'],
        'questions' => $sourceQuiz['questions'],
        'randomize_questions' => isset($sourceQuiz['randomize_questions']) ? $sourceQuiz['randomize_questions'] : false,
        'randomize_answers' => isset($sourceQuiz['randomize_answers']) ? $sourceQuiz['randomize_answers'] : false,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    foreach ($duplicatedQuiz['questions'] as &$question) {
        $question['id'] = generateId();
        if (isset($question['answers'])) {
            $question['answers'] = $question['answers'];
        }
    }
    unset($question);
    
    $quizzes['quizzes'][] = $duplicatedQuiz;
    
    if (writeQuizzes($quizzes)) {
        $_SESSION['success'] = '✅ Quiz duplicated successfully!';
    } else {
        $_SESSION['error'] = '❌ Failed to duplicate quiz!';
    }
    
    redirect('./manage_quizzes.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_quiz'])) {
    $quizId = sanitizeInput($_POST['quiz_id']);
    $quizzes = getQuizzes();
    
    $found = false;
    foreach ($quizzes['quizzes'] as $index => $quiz) {
        if ($quiz['id'] === $quizId) {
            array_splice($quizzes['quizzes'], $index, 1);
            $found = true;
            break;
        }
    }
    
    if ($found && writeQuizzes($quizzes)) {
        $_SESSION['success'] = '✅ Quiz deleted successfully!';
    } else {
        $_SESSION['error'] = '❌ Failed to delete quiz!';
    }
    
    redirect('./manage_quizzes.php');
}

$quizzes = getQuizzes();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Quizzes - Quiz Game</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <nav class="navbar">
            <a href="dashboard.php" class="navbar-brand">🎮 Quiz Admin</a>
            <div class="navbar-menu">
                <span>👋 Hello, <?php echo htmlspecialchars($user['username']); ?>!</span>
                <a href="manage_users.php">👥 Users</a>
                <a href="manage_quizzes.php">📝 Quizzes</a>
                <a href="host_game.php">🎯 Host Game</a>
                <a href="../api/auth.php">
                    <form style="display: inline;" action="../api/auth.php" method="POST">
                        <input type="hidden" name="action" value="logout">
                        <button type="submit" class="btn btn-danger btn-sm" style="margin-left: 15px;">🚪 Logout</button>
                    </form>
                </a>
            </div>
        </nav>
        
        <h1>📝 Manage Quizzes</h1>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <h2>📋 All Quizzes</h2>
            <div class="mb-20">
                <div class="d-flex" style="align-items: center; gap: 15px; flex-wrap: wrap;">
                    <label for="category-filter">🏷️ Filter by Category:</label>
                    <select id="category-filter" onchange="filterQuizzes()" style="padding: 10px;">
                        <option value="">All Categories</option>
                        <option value="math">Math</option>
                        <option value="science">Science</option>
                        <option value="geography">Geography</option>
                        <option value="history">History</option>
                        <option value="literature">Literature</option>
                        <option value="language">Language</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>

            
            <div class="mb-20">
                <a href="create_quiz.php" class="btn btn-success">➕ Create New Quiz</a>
                <label style="display: inline-block; margin-left: 20px;">
                    <input type="file" id="import-file" accept=".json" style="display: none;" onchange="importQuiz()">
                    <button type="button" class="btn btn-info" onclick="document.getElementById('import-file').click()">📥 Import Quiz (JSON)</button>
                </label>
            </div>
            
            <table class="table">
                <thead>
                    <tr>
                        <th>📝 Quiz Title</th><th>🏷️ Category</th>
                        <th>❓ Questions</th>
                        <th>⏱️ Avg Time</th>
                        <th>🎯 Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($quizzes['quizzes'])): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 40px;">
                                <div class="alert alert-info" style="display: inline-block;">
                                    ℹ️ No quizzes yet! Create your first quiz to get started.
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($quizzes['quizzes'] as $quiz): ?>
                            <?php 
                            $avgTime = 0;
                            if (!empty($quiz['questions'])) {
                                $totalTime = 0;
                                foreach ($quiz['questions'] as $q) {
                                    $totalTime += isset($q['time']) ? $q['time'] : 20;
                                }
                                $avgTime = round($totalTime / count($quiz['questions']));
                            }
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($quiz['title']); ?></td>
                                <td><?php echo count($quiz['questions']); ?> questions</td>
                                <td><?php echo $avgTime; ?>s avg</td>
                                <td>
                                    <div class="d-flex">
                                        <button onclick="exportQuiz('<?php echo $quiz['id']; ?>')" class="btn btn-sm btn-warning" title="Export to JSON">
                                            📥
                                        </button>
                                        <a href="edit_quiz.php?id=<?php echo $quiz['id']; ?>" class="btn btn-sm btn-primary">✏️ Edit</a>
                                        <a href="host_game.php?quiz=<?php echo $quiz['id']; ?>" class="btn btn-sm btn-success">🎯 Play</a>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="quiz_id" value="<?php echo $quiz['id']; ?>">
                                            <button type="submit" name="duplicate_quiz" class="btn btn-sm btn-info">
                                                📋 Duplicate
                                            </button>
                                        </form>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="quiz_id" value="<?php echo $quiz['id']; ?>">
                                            <button type="submit" name="delete_quiz" class="btn btn-sm btn-danger" 
                                                    onclick="return confirm('Are you sure you want to delete this quiz?')">
                                                🗑️
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="text-center mt-20">
            <a href="dashboard.php" class="btn btn-info">← Back to Dashboard</a>
        </div>
    </div>
    
    <script>
        var quizzes = <?php echo json_encode($quizzes['quizzes']); ?>;
        
        function exportQuiz(quizId) {
            var quiz = null;
            for (var i = 0; i < quizzes.length; i++) {
                if (quizzes[i].id === quizId) {
                    quiz = quizzes[i];
                    break;
                }
            }
            
            if (!quiz) {
                alert('Quiz not found!');
                return;
            }
            
            var quizData = JSON.stringify(quiz, null, 2);
            var blob = new Blob([quizData], {type: 'application/json;charset=utf-8;'});
            var url = URL.createObjectURL(blob);
            var a = document.createElement('a');
            a.href = url;
            a.download = quiz.title.replace(/[^a-zA-Z0-9]/g, '_') + '_' + new Date().toISOString().slice(0,10) + '.json';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }
        
        function importQuiz() {
            var fileInput = document.getElementById('import-file');
            var file = fileInput.files[0];
            
            if (!file) {
                return;
            }
            
            var reader = new FileReader();
            reader.onload = function(e) {
                try {
                    var quiz = JSON.parse(e.target.result);
                    
                    if (!quiz.title || !quiz.questions) {
                        alert('Invalid quiz file format!');
                        return;
                    }
                    
                    var formData = new FormData();
                    formData.append('action', 'import');
                    formData.append('quiz_data', e.target.result);
                    
                    fetch('./manage_quizzes.php', {
                        method: 'POST',
                        credentials: 'same-origin',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Quiz imported successfully!');
                            location.reload();
                        } else {
                            alert('Error: ' + (data.error || 'Failed to import quiz'));
                        }
                    })
                    .catch(error => {
                        alert('Error importing: ' + error);
                    });
                } catch (error) {
                    alert('Error parsing JSON: ' + error);
                }
            };
            
            reader.readAsText(file);
    <script src="quiz_filter.js"></script>
    <script src="quiz_filter.js"></script>

    <script>
function filterQuizzes() {
    var categoryFilter = document.getElementById('category-filter').value;
    var quizRows = document.querySelectorAll('.quiz-row');
    
    quizRows.forEach(function(row) {
        if (!categoryFilter) {
            row.style.display = 'table-row';
            return;
        }
        
        var quizCategory = row.getAttribute('data-category');
        if (quizCategory === categoryFilter) {
            row.style.display = 'table-row';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>

    </script>
</body>
</html>
