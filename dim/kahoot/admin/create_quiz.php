<?php
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    $_SESSION['error'] = '⛔ You need to login as admin first!';
    redirect('../index.php');
}

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Quiz - Quiz Game</title>
    <link rel="stylesheet" href="../css/style.css">
    <script>
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
                    labelElement.textContent = colors[i] + ' ' + colorLabels[i];
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
                        <label>🎯 Number of Answers</label>
                        <select name="questions[${questionCount}][answer_count]" required onchange="updateAnswerOptions(this)" style="width: 100%; padding: 15px;">
                            <option value="2">2 answers</option>
                            <option value="3" selected>3 answers</option>
                            <option value="4">4 answers</option>
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
                        <label>🎯 Answer Options</label>
                        <div id="answer-inputs-${questionCount}" class="answer-inputs" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
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
        
        function duplicateQuestion(button) {
            var questionItem = button.closest('.question-item');
            var clone = questionItem.cloneNode(true);

            var questionCount = document.querySelectorAll('.question-item').length;
            clone.setAttribute('data-question', questionCount);
            clone.querySelector('h3').textContent = '❓ Question ' + (questionCount + 1);

            var inputs = clone.querySelectorAll('input, select');
            var currentQuestion = questionCount;

            inputs.forEach(function(input) {
                var name = input.name;
                if (name) {
                    name = name.replace(/questions\[\d+\]/g, 'questions[' + currentQuestion + ']');
                    input.name = name;
                }
            });

            var answerCountSelect = clone.querySelector('select[name$="[answer_count]"]');
            if (answerCountSelect) {
                updateAnswerOptions(answerCountSelect);
            }

            questionItem.parentNode.insertBefore(clone, questionItem.nextElementSibling);
            updateQuestionNumbers();
        }
        
        function moveQuestion(button, direction) {
            var questionItem = button.closest('.question-item');
            var container = questionItem.parentNode;
            
            if (direction === -1) {
                var prevItem = questionItem.previousElementSibling;
                if (prevItem) {
                    container.insertBefore(questionItem, prevItem);
                }
            } else {
                var nextItem = questionItem.nextElementSibling;
                if (nextItem) {
                    container.insertBefore(nextItem, questionItem);
                }
            }
            
            updateQuestionNumbers();
        }
        
        function removeQuestion(button) {
            var questionItem = button.closest('.question-item');
            questionItem.remove();
            updateQuestionNumbers();
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
        
        function generateQuizJson() {
            var title = document.getElementById('title').value || '';
            var description = document.getElementById('description').value || '';
            var category = document.getElementById('category').value || '';
            var tags = document.getElementById('tags').value || '';
            var randomizeQuestions = document.querySelector('input[name="randomize_questions"]').checked;
            var randomizeAnswers = document.querySelector('input[name="randomize_answers"]').checked;
            var allowSolo = document.querySelector('input[name="allow_solo"]').checked;
            var questions = [];

            var questionItems = document.querySelectorAll('.question-item');
            questionItems.forEach(function(item) {
                var questionText = item.querySelector('input[name$="[text]"]').value || '';
                var time = parseInt(item.querySelector('select[name$="[time]"]').value) || 20;
                var correct = parseInt(item.querySelector('select[name$="[correct]"]').value) || 0;
                var explanation = item.querySelector('input[name$="[explanation]"]').value || '';

                var answers = [];
                var answerInputs = item.querySelectorAll('input[name$="[answers]"]');
                answerInputs.forEach(function(input) {
                    if (input.value && input.parentElement.style.display !== 'none') {
                        answers.push(input.value);
                    }
                });

                if (questionText && answers.length >= 2) {
                    questions.push({
                        id: '',
                        text: questionText,
                        answers: answers,
                        correct: correct,
                        explanation: explanation,
                        time: time,
                        points: 1000
                    });
                }
            });

            var quizJson = {
                id: '',
                title: title,
                description: description,
                category: category,
                tags: tags,
                questions: questions,
                randomize_questions: randomizeQuestions,
                randomize_answers: randomizeAnswers,
                allow_solo: allowSolo,
                created_at: ''
            };

            return JSON.stringify(quizJson, null, 2);
        }

        function updateJsonCode() {
            var jsonCode = generateQuizJson();
            document.getElementById('quiz-json-code').value = jsonCode;
        }

        function submitQuizCode() {
            var jsonCode = document.getElementById('quiz-json-code').value;

            try {
                var quiz = JSON.parse(jsonCode);

                if (!quiz.title || !quiz.questions || quiz.questions.length === 0) {
                    throw new Error('Invalid quiz format');
                }

                for (var i = 0; i < quiz.questions.length; i++) {
                    var q = quiz.questions[i];
                    if (!q.text || !q.answers || q.answers.length < 2 || !q.hasOwnProperty('correct')) {
                        throw new Error('Invalid question at index ' + i);
                    }
                    if (q.correct < 0 || q.correct >= q.answers.length) {
                        throw new Error('Invalid correct answer index at question ' + (i + 1));
                    }
                }

                var formData = new FormData();
                formData.append('action', 'import_json');
                formData.append('quiz_json', JSON.stringify(quiz));

                fetch('../api/quiz.php', {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Quiz imported successfully from JSON!');
                        window.location.href = 'manage_quizzes.php';
                    } else {
                        alert('Error: ' + (data.error || 'Failed to import quiz'));
                    }
                })
                .catch(error => {
                    alert('Error importing quiz: ' + error);
                });

            } catch (e) {
                alert('Invalid JSON code: ' + e.message);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            addQuestion();

            var form = document.getElementById('quiz-form');
            if (form) {
                form.addEventListener('input', updateJsonCode);
                form.addEventListener('change', updateJsonCode);
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
                            alert('Error: ' + (data.error || 'Failed to create quiz'));
                        }
                    })
                    .catch(error => {
                        alert('Error creating quiz: ' + error);
                    });
                });

                updateJsonCode();
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
        
        <h1>➕ Create New Quiz</h1>
        
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
                <input type="hidden" name="action" value="create">
                
                <div class="form-group">
                    <label for="title">📝 Quiz Title</label>
                    <input type="text" id="title" name="title" required 
                           placeholder="Enter quiz title (e.g., Math Challenge)" 
                           style="width: 100%; padding: 15px;">
                </div>
                
                <div class="form-group">
                    <label for="description">📖 Description</label>
                    <textarea id="description" name="description" rows="3"
                              placeholder="Enter a brief description of this quiz..."
                              style="width: 100%; padding: 15px;"></textarea>
                </div>
                <div class="form-group">
                    <label for="category">🏷️ Category</label>
                    <input type="text" id="category" name="category" required 
                           placeholder="e.g., Math, Science, Geography, Literature"
                           style="width: 100%; padding: 15px;">
                </div>

                <div class="form-group">
                    <label for="tags">🏷️ Tags</label>
                    <input type="text" id="tags" name="tags"
                           placeholder="Comma-separated tags (e.g., easy, beginner, algebra)"
                           style="width: 100%; padding: 15px;">
                </div>
                <div class="form-group" style="background: rgba(0,0,0,0.03); padding: 20px; border-radius: 10px; margin: 20px 0;">
                    <label style="font-weight: 800; font-size: 1.1rem; margin-bottom: 15px;">⚙️ Game Settings</label>
                    <div style="display: flex; gap: 30px; flex-wrap: wrap;">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="checkbox" name="randomize_questions" value="1" style="width: 20px; height: 20px; transform: scale(1.2);">
                            <span>🔀 Randomize Question Order</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="checkbox" name="randomize_answers" value="1" style="width: 20px; height: 20px; transform: scale(1.2);">
                            <span>🔀 Randomize Answer Order</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="checkbox" name="allow_solo" value="1" style="width: 20px; height: 20px; transform: scale(1.2);">
                            <span>👤 Allow Solo Mode</span>
                        </label>
                    </div>
                    <small style="color: #666; margin-top: 10px; display: block;">These options are applied when the quiz is played, not saved permanently.</small>
                </div>
                
                <hr style="margin: 40px 0; border: none; border-top: 3px solid #e0e0e0;">
                
                <h2 style="margin-bottom: 30px;">❓ Questions</h2>
                
                <div id="questions-container">
                    <!-- Questions will be added here dynamically -->
                </div>
                
                <div class="text-center mt-40 mb-40">
                    <button type="button" class="btn btn-success btn-lg" onclick="addQuestion()">➕ Add Another Question</button>
                </div>
                
                <hr style="margin: 40px 0; border: none; border-top: 3px solid #e0e0e0;">
                
                <div class="d-flex justify-between mt-40">
                    <a href="manage_quizzes.php" class="btn btn-warning">← Cancel</a>
                    <button type="submit" class="btn btn-primary btn-lg">💾 Save Quiz</button>
                </div>
            </form>
        </div>

        <div class="card mt-40">
            <h2>📋 Quiz JSON Code</h2>
            <p style="color: #666; margin-bottom: 20px;">
                This code updates in real-time as you edit the quiz above. You can also paste your own JSON code here and import it.
            </p>
            <textarea id="quiz-json-code" rows="20"
                      style="width: 100%; padding: 15px; font-family: 'Courier New', monospace; font-size: 14px; background: #f5f5f5; border: 2px solid #ddd; border-radius: 10px; resize: vertical;"
                      placeholder="Quiz JSON code will appear here..."></textarea>
            <div class="text-center mt-20">
                <button type="button" class="btn btn-success btn-lg" onclick="submitQuizCode()">📥 Submit Quiz Code</button>
            </div>
        </div>
    </div>
</body>
</html>
