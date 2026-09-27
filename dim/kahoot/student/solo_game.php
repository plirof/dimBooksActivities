<?php
require_once '../config.php';

$quizId = isset($_GET['quiz_id']) ? sanitizeInput($_GET['quiz_id']) : '';

if (empty($quizId)) {
    $_SESSION['error'] = '❌ Quiz ID is required!';
    redirect('./solo.php');
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
    redirect('./solo.php');
}

if (!(isset($quiz['allow_solo']) && $quiz['allow_solo'] === true)) {
    $_SESSION['error'] = '❌ This quiz is not available for solo play!';
    redirect('./solo.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solo Game - Quiz Game</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .podium-block {
            width: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: 800;
            color: white;
            border-radius: 10px 10px 0 0;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .timer-progress {
            width: 100%;
            height: 12px;
            background: #e0e0e0;
            border-radius: 6px;
            overflow: hidden;
            margin: 10px 0;
        }

        .timer-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #46178f 0%, #5c2a9e 100%);
            width: 100%;
            transition: width 0.3s linear;
        }

        .timer-progress-bar.warning {
            background: linear-gradient(90deg, #f57c00 0%, #ff8c00 100%);
        }

        .timer-progress-bar.danger {
            background: linear-gradient(90deg, #dc3545 0%, #ff4b4b 100%);
            animation: pulse 0.5s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
    </style>
    <script>
        var quizData = <?php echo json_encode($quiz); ?>;
        var currentQuestionIndex = 0;
        var questions = [];
        var totalScore = 0;
        var streak = 0;
        var timerInterval = null;
        var questionStartTime = 0;
        var correctCount = 0;
        var questionResults = [];

        function shuffleArray(array) {
            var shuffled = array.slice();
            for (var i = shuffled.length - 1; i > 0; i--) {
                var j = Math.floor(Math.random() * (i + 1));
                [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
            }
            return shuffled;
        }

        function initGame() {
            questions = quizData.questions.slice();

            if (quizData.randomize_questions) {
                questions = shuffleArray(questions);
            }

            if (quizData.randomize_answers) {
                questions.forEach(function(q) {
                    var originalAnswers = q.answers.slice();
                    var correctAnswer = originalAnswers[q.correct];
                    var shuffledAnswers = shuffleArray(originalAnswers);
                    q.answers = shuffledAnswers;
                    q.correct = shuffledAnswers.indexOf(correctAnswer);
                });
            }

            currentQuestionIndex = 0;
            totalScore = 0;
            streak = 0;
            correctCount = 0;
            questionResults = [];

            showStartScreen();
        }

        function showStartScreen() {
            document.getElementById('start-area').style.display = 'block';
            document.getElementById('game-area').style.display = 'none';
            document.getElementById('result-area').style.display = 'none';
            document.getElementById('final-results').style.display = 'none';

            document.getElementById('quiz-title').textContent = quizData.title;
            document.getElementById('quiz-description').textContent = quizData.description || 'No description';
            document.getElementById('quiz-category').textContent = quizData.category || 'General';
            document.getElementById('quiz-questions-count').textContent = questions.length;
        }

        function startGame() {
            document.getElementById('start-area').style.display = 'none';
            document.getElementById('game-area').style.display = 'block';
            currentQuestionIndex = 0;
            showQuestion();
        }

        function showQuestion() {
            if (currentQuestionIndex >= questions.length) {
                showFinalResults();
                return;
            }

            var q = questions[currentQuestionIndex];
            document.getElementById('question-number').textContent = '❓ Question ' + (currentQuestionIndex + 1) + ' of ' + questions.length;
            document.getElementById('question-text').textContent = q.text;
            document.getElementById('current-score').textContent = totalScore;

            var colors = ['red', 'blue', 'yellow', 'green'];
            var answerButtons = document.querySelectorAll('.answer-option');

            answerButtons.forEach(function(btn, index) {
                btn.style.display = 'block';
                btn.className = 'answer-option ' + colors[index];
                if (index < q.answers.length) {
                    btn.textContent = q.answers[index];
                    btn.onclick = function() { submitAnswer(index); };
                } else {
                    btn.style.display = 'none';
                }
            });

            var gridColumns = q.answers.length === 2 ? 'repeat(2, 1fr)' : (q.answers.length === 3 ? 'repeat(3, 1fr)' : 'repeat(2, 1fr)');
            document.querySelector('.answer-options').style.gridTemplateColumns = gridColumns;

            questionStartTime = Date.now();
            startTimer(q.time);
        }

        function startTimer(timeLimit) {
            if (timeLimit === 0) {
                document.getElementById('timer-display').textContent = '∞';
                document.getElementById('timer-progress-bar').style.width = '100%';
                document.getElementById('timer-progress-bar').classList.remove('warning', 'danger');
                return;
            }

            var timeLeft = timeLimit;
            document.getElementById('timer-display').textContent = timeLeft;
            document.getElementById('timer-display').classList.remove('warning');
            document.getElementById('timer-progress-bar').classList.remove('warning', 'danger');

            clearInterval(timerInterval);
            timerInterval = setInterval(function() {
                timeLeft--;
                document.getElementById('timer-display').textContent = timeLeft;

                var progressPercent = (timeLeft / timeLimit) * 100;
                document.getElementById('timer-progress-bar').style.width = progressPercent + '%';

                if (timeLeft <= 5) {
                    document.getElementById('timer-display').classList.add('warning');
                    document.getElementById('timer-progress-bar').classList.add('warning');
                }

                if (timeLeft <= 0) {
                    clearInterval(timerInterval);
                    submitAnswer(-1);
                }
            }, 1000);
        }

        function submitAnswer(answerIndex) {
            clearInterval(timerInterval);
            var q = questions[currentQuestionIndex];
            var isCorrect = (answerIndex === q.correct);

            if (answerIndex === -1) {
                isCorrect = false;
            }

            var timeTaken = (Date.now() - questionStartTime) / 1000;
            var points = 0;

            if (isCorrect) {
                var timeLimit = q.time === 0 ? 30 : q.time;
                var speedMultiplier = Math.max(0.5, 1 - (timeTaken / timeLimit) * 0.5);
                points = Math.round(1000 * speedMultiplier);

                var streakMultiplier = 1;
                streak++;
                if (streak >= 7) {
                    streakMultiplier = 1.3;
                } else if (streak >= 5) {
                    streakMultiplier = 1.2;
                } else if (streak >= 3) {
                    streakMultiplier = 1.1;
                }
                points = Math.round(points * streakMultiplier);
                totalScore += points;
                correctCount++;
            } else {
                streak = 0;
            }

            questionResults.push({
                questionNumber: currentQuestionIndex + 1,
                isCorrect: isCorrect,
                yourAnswer: answerIndex >= 0 ? q.answers[answerIndex] : 'Time up',
                correctAnswer: q.answers[q.correct],
                points: isCorrect ? points : 0
            });

            document.getElementById('game-area').style.display = 'none';
            document.getElementById('result-area').style.display = 'block';

            document.getElementById('result-message').textContent = isCorrect ? '✅ Correct!' : '❌ Wrong!';
            document.getElementById('result-message').className = isCorrect ? 'result-correct' : 'result-wrong';
            document.getElementById('points-earned').textContent = isCorrect ? '+' + points : '+0';
            document.getElementById('total-score').textContent = totalScore;

            document.getElementById('your-answer').textContent = answerIndex >= 0 ? q.answers[answerIndex] : 'Time up';
            document.getElementById('correct-answer').textContent = q.answers[q.correct];

            var streakBonusElement = document.getElementById('streak-bonus');
            if (streak >= 3 && isCorrect) {
                streakBonusElement.style.display = 'block';
                var multiplier = streak >= 7 ? '1.3x' : (streak >= 5 ? '1.2x' : '1.1x');
                streakBonusElement.innerHTML = '<span style="font-size: 1.5rem; color: #ff6b35; font-weight: 800;">🔥 Streak: ' + streak + ' (Bonus: ' + multiplier + ')</span>';
            } else {
                streakBonusElement.style.display = 'none';
            }

            if (q.explanation) {
                document.getElementById('explanation-display').style.display = 'block';
                document.getElementById('explanation-text').textContent = q.explanation;
            } else {
                document.getElementById('explanation-display').style.display = 'none';
            }

            currentQuestionIndex++;

            setTimeout(function() {
                if (currentQuestionIndex >= questions.length) {
                    showFinalResults();
                } else {
                    document.getElementById('result-area').style.display = 'none';
                    document.getElementById('game-area').style.display = 'block';
                    showQuestion();
                }
            }, 2500);
        }

        function showFinalResults() {
            clearInterval(timerInterval);
            document.getElementById('game-area').style.display = 'none';
            document.getElementById('result-area').style.display = 'none';
            document.getElementById('final-results').style.display = 'block';

            var percentage = Math.round((correctCount / questions.length) * 100);
            document.getElementById('final-score').textContent = totalScore;
            document.getElementById('final-correct').textContent = correctCount;
            document.getElementById('final-total').textContent = questions.length;
            document.getElementById('final-percentage').textContent = percentage + '%';

            var resultsHtml = '<h3 style="margin-bottom: 20px;">📊 Question Results</h3><div style="max-height: 300px; overflow-y: auto;">';
            questionResults.forEach(function(r) {
                resultsHtml += '<div style="padding: 10px; margin-bottom: 10px; background: ' + (r.isCorrect ? '#d4edda' : '#f8d7da') + '; border-radius: 5px; border-left: 4px solid ' + (r.isCorrect ? '#28a745' : '#dc3545') + ';">';
                resultsHtml += '<strong>Q' + r.questionNumber + ':</strong> ' + (r.isCorrect ? '✅' : '❌') + ' ';
                resultsHtml += '<span style="color: #666;">(+' + r.points + ' pts)</span>';
                resultsHtml += '</div>';
            });
            resultsHtml += '</div>';
            document.getElementById('question-results').innerHTML = resultsHtml;
        }

        function playAgain() {
            window.location.href = 'solo.php';
        }

        document.addEventListener('DOMContentLoaded', function() {
            initGame();
        });
    </script>
</head>
<body>
    <div class="container">
        <div class="card" id="start-area">
            <h1>👤 Solo Quiz</h1>
            <div style="margin: 30px 0; padding: 20px; background: rgba(70, 23, 143, 0.05); border-radius: 15px;">
                <h2 id="quiz-title">Quiz Title</h2>
                <p id="quiz-description" style="color: #666; margin: 10px 0;">Quiz description</p>
                <div style="display: flex; gap: 15px; flex-wrap: wrap; margin-top: 15px;">
                    <span style="background: #e3f2fd; padding: 5px 15px; border-radius: 15px;">
                        🏷️ <span id="quiz-category">Category</span>
                    </span>
                    <span style="background: #e8f5e9; padding: 5px 15px; border-radius: 15px;">
                        ❓ <span id="quiz-questions-count">0</span> Questions
                    </span>
                </div>
            </div>
            <div class="text-center mt-40">
                <button type="button" class="btn btn-primary btn-lg" onclick="startGame()">🚀 Start Quiz</button>
            </div>
            <div class="text-center mt-40">
                <a href="solo.php" class="btn btn-warning">← Back to Quiz List</a>
            </div>
        </div>

        <div class="card" id="game-area" style="display: none;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 id="question-number">❓ Question 1</h2>
                <div>
                    <div class="timer" id="timer-display">20</div>
                    <div class="timer-progress"><div class="timer-progress-bar" id="timer-progress-bar"></div></div>
                </div>
            </div>

            <div class="question-display" id="question-text">
                Loading question...
            </div>

            <div class="answer-options" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
                <button class="answer-option red">Option 1</button>
                <button class="answer-option blue">Option 2</button>
                <button class="answer-option yellow">Option 3</button>
                <button class="answer-option green">Option 4</button>
            </div>

            <div style="text-align: center; margin-top: 20px;">
                <p style="font-size: 1.2rem; color: #666;">Score: <strong id="current-score">0</strong></p>
            </div>
        </div>

        <div class="card" id="result-area" style="display: none; text-align: center; padding: 40px;">
            <h1 id="result-message" style="font-size: 2.5rem; margin-bottom: 20px;">✅ Correct!</h1>

            <div id="streak-bonus" style="display: none; margin-bottom: 20px;">
                <span style="font-size: 1.5rem; color: #ff6b35; font-weight: 800;">🔥 Streak Bonus: 1.1x</span>
            </div>

            <div id="explanation-display" style="display: none; margin: 20px 0; padding: 15px; background: #e3f2fd; border-left: 4px solid #46178f; border-radius: 5px;">
                <span style="font-weight: 800; color: #46178f;">💡 Explanation:</span>
                <span id="explanation-text" style="margin-left: 10px;"></span>
            </div>

            <div id="result-details" style="margin: 30px 0; padding: 20px; background: rgba(0,0,0,0.05); border-radius: 15px;">
                <div style="margin-bottom: 15px;">
                    <span style="color: #666; font-size: 1.1rem;">Your Answer:</span><br>
                    <strong id="your-answer" style="font-size: 1.5rem; color: #46178f;">-</strong>
                </div>
                <div>
                    <span style="color: #666; font-size: 1.1rem;">Correct Answer:</span><br>
                    <strong id="correct-answer" style="font-size: 1.5rem; color: #28a745;">-</strong>
                </div>
            </div>

            <div class="points-earned" id="points-earned">+1000</div>
            <p style="font-size: 1.3rem; margin-top: 20px;">Total Score: <strong id="total-score">1000</strong></p>
            <div class="loading" style="margin-top: 40px;">Next question...</div>
        </div>

        <div class="card" id="final-results" style="display: none;">
            <h1 style="text-align: center;">🏆 Final Results</h1>

            <div style="text-align: center; margin: 30px 0; padding: 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 15px; color: white;">
                <div style="font-size: 1.2rem; margin-bottom: 10px;">Your Score</div>
                <div id="final-score" style="font-size: 3.5rem; font-weight: 800;">0</div>
                <div style="margin-top: 20px; font-size: 1.1rem;">
                    <span>✅ Correct: <strong id="final-correct">0</strong></span>
                    <span style="margin-left: 20px;">❓ Total: <strong id="final-total">0</strong></span>
                </div>
                <div style="margin-top: 10px; font-size: 1.5rem; font-weight: 800;">
                    <span id="final-percentage">0%</span>
                </div>
            </div>

            <div id="question-results" style="margin: 30px 0;"></div>

            <div class="text-center mt-40">
                <button type="button" class="btn btn-success btn-lg" onclick="playAgain()">🔄 Play Again</button>
                <a href="solo.php" class="btn btn-warning" style="margin-left: 15px;">← Choose Another Quiz</a>
            </div>
        </div>
    </div>
</body>
</html>
