<?php
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    $_SESSION['error'] = '⛔ You need to login as admin first!';
    redirect('../index.php');
}

$user = getCurrentUser();

$quizId = isset($_GET['id']) ? sanitizeInput($_GET['id']) : '';

if (empty($quizId)) {
    $_SESSION['error'] = '❌ Quiz ID is required!';
    redirect('./manage_quizzes.php');
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
    $_SESSION['error'] = '❌ Quiz not found!';
    redirect('./manage_quizzes.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Quiz - Quiz Game</title>
    <link rel="stylesheet" href="../css/style.css">
    <script>
        function addQuestion() {
            var questionCount = document.querySelectorAll('.question-item').length;
            var questionHtml = `
                <div class="card question-item" data-question="${questionCount}">
                    <h3>❓ Question ${questionCount + 1}</h3>
                    <div class="form-group">
                        <label>📝 Question Text</label>
                        <input type="text" name="questions[${questionCount}][text]" required 
                               placeholder="Enter your question..." style="width: 100%; padding: 15px;">
                    </div>
                    
                    <div class="form-group">
                        <label>⏱️ Time Limit</label>
                        <select name="questions[${questionCount}][time]" required style="width: 100%; padding: 15px;">
                            <option value="10">10 seconds</option>
                            <option value="20" selected>20 seconds</option>
                            <option value="30">30 seconds</option>
                            <option value="60">60 seconds</option>
                            <option value="90">90 seconds</option>
                            <option value="120">120 seconds</option>
                            <option value="0">No time limit</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>💡 Correct Answer</label>
                        <select name="questions[${questionCount}][correct]" required style="width: 100%; padding: 15px;">
                            <option value="0">🔴 Option 1 (Red)</option>
                            <option value="1">🔵 Option 2 (Blue)</option>
                            <option value="2">🟡 Option 3 (Yellow)</option>
                            <option value="3">🟢 Option 4 (Green)</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>🎯 Number of Answers</label>
                        <select name="questions[${questionCount}][answer_count]" required onchange="updateAnswerOptions(this)" style="width: 100%; padding: 15px;">
                            <option value="2">2 answers</option>
                            <option value="3" selected>3 answers</option>
                            <option value="4">4 answers</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>🎯 Answer Options</label>
                        <div id="answer-inputs-${questionCount}" class="answer-inputs" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px;">
                            <div>
                                <label style="color: #46178f;">🔴 Option 1</label>
                                <input type="text" name="questions[${questionCount}][answers][0]" required 
                                       placeholder="Answer option 1..." style="width: 100%; padding: 15px;">
                            </div>
                            <div>
                                <label style="color: #1368ce;">🔵 Option 2</label>
                                <input type="text" name="questions[${questionCount}][answers][1]" required 
                                       placeholder="Answer option 2..." style="width: 100%; padding: 15px;">
                            </div>
                            <div>
                                <label style="color: #d69e00;">🟡 Option 3</label>
                                <input type="text" name="questions[${questionCount}][answers][2]" required 
                                       placeholder="Answer option 3..." style="width: 100%; padding: 15px;">
                            </div>
                            <div>
                                <label style="color: #26890c;">🟢 Option 4</label>
                                <input type="text" name="questions[${questionCount}][answers][3]" required 
                                       placeholder="Answer option 4..." style="width: 100%; padding: 15px;">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>📖 Explanation (shown after question)</label>
                        <input type="text" name="questions[${questionCount}][explanation]" 
                               placeholder="Explain why this is the correct answer..."
                               style="width: 100%; padding: 15px;">
                    </div>
                    
                    <div style="display: flex; gap: 10px; margin-top: 20px;">
                        <button type="button" class="btn btn-success btn-sm" onclick="duplicateQuestion(this)">📋 Duplicate</button>
                        <button type="button" class="btn btn-info btn-sm" onclick="moveQuestion(this, -1)">⬆️ Move Up</button>
                        <button type="button" class="btn btn-info btn-sm" onclick="moveQuestion(this, 1)">⬇️ Move Down</button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="removeQuestion(this)">🗑️ Remove Question</button>
                    </div>
                </div>
            `;
            
            var questionsContainer = document.getElementById('questions-container');
            var tempDiv = document.createElement('div');
            tempDiv.innerHTML = questionHtml.trim();
            questionsContainer.appendChild(tempDiv.firstChild);
            
            updateQuestionNumbers();
        }
        
        function updateAnswerOptions(selectElement) {
            var questionItem = selectElement.closest('.question-item');
            var answerCount = parseInt(selectElement.value);
            var answerInputsContainer = questionItem.querySelector('.answer-inputs');
            var answerInputs = answerInputsContainer.querySelectorAll('input');
            var correctSelect = questionItem.querySelector('select[name$="[correct]"]');
            
            var colors = ['red', 'blue', 'yellow', 'green'];
            var colorLabels = ['🔴 Red', '🔵 Blue', '🟡 Yellow', '🟢 Green'];
            
            var gridColumns = '';
            if (answerCount === 2) {
                gridColumns = 'repeat(2, 1fr)';
            } else if (answerCount === 3) {
                gridColumns = 'repeat(3, 1fr)';
            } else {
                gridColumns = 'repeat(2, 1fr)';
            }
            
            answerInputsContainer.style.gridTemplateColumns = gridColumns;
            
            for (var i = 0; i < answerInputs.length; i++) {
                if (i < answerCount) {
                    answerInputs[i].parentElement.style.display = 'block';
                    var labelElement = answerInputs[i].previousElementSibling;
                    labelElement.textContent = colorLabels[i];
                } else {
                    answerInputs[i].parentElement.style.display = 'none';
                }
            }
            
            var currentOptions = '';
            for (var i = 0; i < answerCount; i++) {
                currentOptions += '<option value="' + i + '">' + colorLabels[i] + '</option>';
            }
            correctSelect.innerHTML = currentOptions;
        }
        
        function updateQuestionNumbers() {
            var questions = document.querySelectorAll('.question-item');
            questions.forEach(function(item, index) {
                item.setAttribute('data-question', index);
                var title = item.querySelector('h3');
                if (title) {
                    title.textContent = '❓ Question ' + (index + 1);
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            var form = document.getElementById('quiz-form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    var formData = new FormData(form);

                    fetch('../api/quiz.php', {
                        method: 'POST',
                        credentials: 'same-origin',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = './manage_quizzes.php';
                        } else {
                            alert('Error: ' + (data.error || 'Failed to save quiz'));
                        }
                    })
                    .catch(error => {
                        alert('Error saving quiz: ' + error);
                    });
                });
            }
        });
    </script>
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
        
        <h1>✏️ Edit Quiz</h1>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <form id="quiz-form" method="POST" action="../api/quiz.php">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="quiz_id" value="<?php echo $quiz['id']; ?>">
                
                <div class="form-group">
                    <label for="title">📝 Quiz Title</label>
                    <input type="text" id="title" name="title" required 
                           value="<?php echo htmlspecialchars($quiz['title']); ?>"
                           placeholder="Enter quiz title" 
                           style="width: 100%; padding: 15px;">
                </div>
                
                <div class="form-group">
                    <label for="description">📖 Description</label>
                    <textarea id="description" name="description" rows="3"
                              placeholder="Enter a brief description of this quiz..."
                              style="width: 100%; padding: 15px;"><?php echo htmlspecialchars($quiz['description'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group" style="background: rgba(0,0,0,0.03); padding: 20px; border-radius: 10px; margin: 20px 0;">
                    <label style="font-weight: 800; font-size: 1.1rem; margin-bottom: 15px;">⚙️ Game Settings</label>
                    <div style="display: flex; gap: 30px; flex-wrap: wrap;">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="checkbox" name="randomize_questions" value="1"
                                   <?php echo isset($quiz['randomize_questions']) && $quiz['randomize_questions'] ? 'checked' : ''; ?>
                                   style="width: 20px; height: 20px; transform: scale(1.2);">
                            <span>🔀 Randomize Question Order</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="checkbox" name="randomize_answers" value="1"
                                   <?php echo isset($quiz['randomize_answers']) && $quiz['randomize_answers'] ? 'checked' : ''; ?>
                                   style="width: 20px; height: 20px; transform: scale(1.2);">
                            <span>🔀 Randomize Answer Order</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="checkbox" name="allow_solo" value="1"
                                   <?php echo isset($quiz['allow_solo']) && $quiz['allow_solo'] ? 'checked' : ''; ?>
                                   style="width: 20px; height: 20px; transform: scale(1.2);">
                            <span>👤 Allow Solo Mode</span>
                        </label>
                    </div>
                    <small style="color: #666; margin-top: 10px; display: block;">These options are applied when quiz is played, not saved permanently.</small>
                </div>

                <hr style="margin: 40px 0; border: none; border-top: 3px solid #e0e0e0;">
                
                <h2 style="margin-bottom: 30px;">❓ Questions</h2>
                
                <div id="questions-container">
                    <?php if (empty($quiz['questions'])): ?>
                        <!-- No existing questions, will add one via JavaScript -->
                    <?php else: ?>
                        <?php foreach ($quiz['questions'] as $index => $q): ?>
                        <div class="card question-item" data-question="<?php echo $index; ?>">
                            <h3>❓ Question <?php echo $index + 1; ?></h3>
                            <div class="form-group">
                                <label>📝 Question Text</label>
                                <input type="text" name="questions[<?php echo $index; ?>][text]" required 
                                       value="<?php echo htmlspecialchars($q['text']); ?>"
                                       placeholder="Enter your question..." style="width: 100%; padding: 15px;">
                                <input type="hidden" name="questions[<?php echo $index; ?>][id]" value="<?php echo htmlspecialchars($q['id']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label>⏱️ Time Limit</label>
                                <select name="questions[<?php echo $index; ?>][time]" required style="width: 100%; padding: 15px;">
                                    <option value="10" <?php echo $q['time'] === 10 ? 'selected' : ''; ?>>10 seconds</option>
                                    <option value="20" <?php echo $q['time'] === 20 ? 'selected' : ''; ?>>20 seconds</option>
                                    <option value="30" <?php echo $q['time'] === 30 ? 'selected' : ''; ?>>30 seconds</option>
                                    <option value="60" <?php echo $q['time'] === 60 ? 'selected' : ''; ?>>60 seconds</option>
                                    <option value="90" <?php echo $q['time'] === 90 ? 'selected' : ''; ?>>90 seconds</option>
                                    <option value="120" <?php echo $q['time'] === 120 ? 'selected' : ''; ?>>120 seconds</option>
                                    <option value="0" <?php echo $q['time'] === 0 ? 'selected' : ''; ?>>No time limit</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>💡 Correct Answer</label>
                                <select name="questions[<?php echo $index; ?>][correct]" required style="width: 100%; padding: 15px;">
                                    <option value="0" <?php echo $q['correct'] === 0 ? 'selected' : ''; ?>>🔴 Option 1 (Red)</option>
                                    <option value="1" <?php echo $q['correct'] === 1 ? 'selected' : ''; ?>>🔵 Option 2 (Blue)</option>
                                    <option value="2" <?php echo $q['correct'] === 2 ? 'selected' : ''; ?>>🟡 Option 3 (Yellow)</option>
                                    <option value="3" <?php echo $q['correct'] === 3 ? 'selected' : ''; ?>>🟢 Option 4 (Green)</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>📖 Explanation (shown after question)</label>
                                <input type="text" name="questions[<?php echo $index; ?>][explanation]" 
                                       value="<?php echo htmlspecialchars($q['explanation'] ?? ''); ?>"
                                       placeholder="Explain why this is the correct answer..."
                                       style="width: 100%; padding: 15px;">
                            </div>
                            
                            <div class="form-group">
                                <label>🎯 Answer Options</label>
                                <div class="answer-inputs" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
                                    <div>
                                        <label style="color: #46178f;">🔴 Option 1</label>
                                        <input type="text" name="questions[<?php echo $index; ?>][answers][0]" required 
                                               value="<?php echo htmlspecialchars($q['answers'][0] ?? ''); ?>"
                                               placeholder="Answer option 1..." style="width: 100%; padding: 15px;">
                                    </div>
                                    <div>
                                        <label style="color: #1368ce;">🔵 Option 2</label>
                                        <input type="text" name="questions[<?php echo $index; ?>][answers][1]" required 
                                               value="<?php echo htmlspecialchars($q['answers'][1] ?? ''); ?>"
                                               placeholder="Answer option 2..." style="width: 100%; padding: 15px;">
                                    </div>
                                    <div>
                                        <label style="color: #d69e00;">🟡 Option 3</label>
                                        <input type="text" name="questions[<?php echo $index; ?>][answers][2]" required 
                                               value="<?php echo htmlspecialchars($q['answers'][2] ?? ''); ?>"
                                               placeholder="Answer option 3..." style="width: 100%; padding: 15px;">
                                    </div>
                                    <div>
                                        <label style="color: #26890c;">🟢 Option 4</label>
                                        <input type="text" name="questions[<?php echo $index; ?>][answers][3]" required 
                                               value="<?php echo htmlspecialchars($q['answers'][3] ?? ''); ?>"
                                               placeholder="Answer option 4..." style="width: 100%; padding: 15px;">
                                    </div>
                                </div>
                            </div>
                            
                            <div style="display: flex; gap: 10px; margin-top: 20px;">
                                <button type="button" class="btn btn-success btn-sm" onclick="duplicateQuestion(this)">📋 Duplicate</button>
                                <button type="button" class="btn btn-info btn-sm" onclick="moveQuestion(this, -1)">⬆️ Move Up</button>
                                <button type="button" class="btn btn-info btn-sm" onclick="moveQuestion(this, 1)">⬇️ Move Down</button>
                                <button type="button" class="btn btn-danger btn-sm" onclick="removeQuestion(this)">🗑️ Remove Question</button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <div class="text-center mt-40 mb-40">
                    <button type="button" class="btn btn-success btn-lg" onclick="addQuestion()">➕ Add Another Question</button>
                </div>
                
                <hr style="margin: 40px 0; border: none; border-top: 3px solid #e0e0e0;">
                
                <div class="d-flex justify-between mt-40">
                    <a href="manage_quizzes.php" class="btn btn-warning">← Cancel</a>
                    <button type="submit" class="btn btn-primary btn-lg">💾 Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
