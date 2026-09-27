<?php
require_once '../config.php';

$pin = isset($_SESSION['game_pin']) ? $_SESSION['game_pin'] : '';
$playerName = isset($_SESSION['player_name']) ? $_SESSION['player_name'] : '';

if (empty($pin) || empty($playerName)) {
    $_SESSION['error'] = '⛔ You need to join a game first!';
    redirect('join.php');
}

$games = getGames();

if (!isset($games['active_games'][$pin])) {
    $_SESSION['error'] = '❌ Game not found!';
    redirect('./join.php');
}

$game = $games['active_games'][$pin];

if (!isset($game['participants'][$playerName])) {
    $_SESSION['error'] = '❌ You are not in this game!';
    redirect('./join.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game - Quiz Game</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .podium-place {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 0 20px;
            animation: jump 0.5s ease-in-out infinite alternate;
        }
        
        .podium-place.podium-gold {
            animation-delay: 0s;
        }
        
        .podium-place.podium-silver {
            animation-delay: 0.2s;
        }
        
        .podium-place.podium-bronze {
            animation-delay: 0.4s;
        }
        
        @keyframes jump {
            0% { transform: translateY(0); }
            100% { transform: translateY(-10px); }
        }
        
        .podium-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #46178f 0%, #5c2a9e 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 800;
            font-size: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            margin-bottom: 10px;
        }
        
        .podium-name {
            font-weight: 800;
            font-size: 1.3rem;
            margin-bottom: 5px;
            max-width: 120px;
            text-align: center;
            word-wrap: break-word;
        }
        
        .podium-score {
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 10px;
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
    </style>
    <script>
        var gamePin = <?php echo json_encode($pin); ?>;
        var playerName = <?php echo json_encode($playerName); ?>;
        var currentQuestion = null;
        var currentQuestionData = null;
        var totalQuestions = 0;
        var timerInterval = null;
        var pollInterval = null;
        var canAnswer = false;
        
        function pollQuestion() {
            fetch('../api/poll.php?pin=' + gamePin + '&action=question', {
                credentials: 'same-origin'
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.question) {
                        if (currentQuestion !== data.question_number) {
                            currentQuestion = data.question_number;
                            totalQuestions = data.total_questions;
                            showQuestion(data.question, data.question_number);
                        }
                    } else if (data.success === false && data.error === 'No more questions') {
                        showFinalResults();
                    }
                })
                .catch(error => {
                    console.error('Polling error:', error);
                });
        }
        
        function showQuestion(question, questionNumber) {
            document.getElementById('waiting-area').style.display = 'none';
            document.getElementById('result-area').style.display = 'none';
            document.getElementById('game-area').style.display = 'block';
            document.getElementById('explanation-display').style.display = 'none';
            document.getElementById('streak-indicator').style.display = 'none';
            
            currentQuestionData = question;
            document.getElementById('question-number').textContent = '❓ Question ' + questionNumber + ' of ' + totalQuestions;
            document.getElementById('question-text').textContent = question.text;
            
            var colors = ['red', 'blue', 'yellow', 'green'];
            var answerButtons = document.querySelectorAll('.answer-option');
            
            answerButtons.forEach(function(btn, index) {
                btn.className = 'answer-option ' + colors[index];
                btn.textContent = question.answers[index];
                btn.onclick = function() { submitAnswer(index); };
            });
            
            canAnswer = true;
            startTimer(question.time);
        }
        
        function startTimer(timeLeft) {
            if (timeLeft === 0) {
                document.getElementById('timer-display').textContent = '∞';
                document.getElementById('timer-display').classList.remove('warning');
                document.getElementById('timer-progress-bar').style.width = '100%';
                document.getElementById('timer-progress-bar').classList.remove('warning', 'danger');
                clearInterval(timerInterval);
                return;
            }
            
            document.getElementById('timer-display').textContent = timeLeft;
            document.getElementById('timer-display').classList.remove('warning');
            
            var totalTime = timeLeft === 0 ? 30 : timeLeft;
            document.getElementById('timer-progress-bar').classList.remove('warning', 'danger');
            
            clearInterval(timerInterval);
            timerInterval = setInterval(function() {
                timeLeft--;
                document.getElementById('timer-display').textContent = timeLeft;
                
                var progressPercent = (timeLeft / totalTime) * 100;
                document.getElementById('timer-progress-bar').style.width = progressPercent + '%';
                
                if (timeLeft <= 5) {
                    document.getElementById('timer-display').classList.add('warning');
                    document.getElementById('timer-progress-bar').classList.add('warning');
                }
                
                if (timeLeft <= 0) {
                    clearInterval(timerInterval);
                    canAnswer = false;
                    disableAnswers();
                    waitForResult();
                }
            }, 1000);
        }
        
        function submitAnswer(answer) {
            if (!canAnswer) return;
            
            canAnswer = false;
            clearInterval(timerInterval);
            disableAnswers();
            
            fetch('../api/poll.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                credentials: 'same-origin',
                body: 'action=submit_answer&pin=' + gamePin + '&answer=' + answer
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('game-area').style.display = 'none';
                    document.getElementById('result-area').style.display = 'block';
                    
                    document.getElementById('result-message').textContent = data.is_correct ? '✅ Correct!' : '❌ Wrong!';
                    document.getElementById('result-message').className = data.is_correct ? 'result-correct' : 'result-wrong';
                    document.getElementById('points-earned').textContent = '+' + data.points;
                    document.getElementById('total-score').textContent = data.total_score;
                    
                    var streakBonus = '';
                    if (data.streak >= 3) {
                        streakBonus = '🔥 Streak Bonus: ' + (data.streak >= 7 ? '1.3x' : (data.streak >= 5 ? '1.2x' : '1.1x'));
                    }
                    
                    if (data.is_correct) {
                        document.getElementById('result-area').style.background = 'linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%)';
                    } else {
                        document.getElementById('result-area').style.background = 'linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%)';
                    }
                    
                    pollForResult();
                } else {
                    alert('Error: ' + data.error);
                }
            })
            .catch(error => {
                alert('Error submitting answer: ' + error);
            });
        }
        
        function disableAnswers() {
            var answerButtons = document.querySelectorAll('.answer-option');
            answerButtons.forEach(function(btn) {
                btn.onclick = null;
                btn.style.cursor = 'not-allowed';
            });
        }
        
        function waitForResult() {
            pollForResult();
        }
        
        function pollForResult() {
            fetch('../api/poll.php?pin=' + gamePin + '&action=player_result', {
                credentials: 'same-origin'
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('result-message').textContent = data.is_correct ? '✅ Correct!' : '❌ Wrong!';
                        document.getElementById('result-message').className = data.is_correct ? 'result-correct' : 'result-wrong';
                        
                        document.getElementById('your-answer').textContent = data.your_answer || '-';
                        document.getElementById('correct-answer').textContent = data.correct_answer || '-';
                        document.getElementById('percentage').textContent = data.percentage + '%';
                        document.getElementById('correct-count').textContent = data.correct_count;
                        document.getElementById('total-count').textContent = data.total_count;
                        
                        if (currentQuestionData && currentQuestionData.explanation) {
                            document.getElementById('explanation-display').style.display = 'block';
                            document.getElementById('explanation-text').textContent = currentQuestionData.explanation;
                        } else {
                            document.getElementById('explanation-display').style.display = 'none';
                        }
                        
                        var streakBonusElement = document.getElementById('streak-bonus');
                        if (data.streak >= 3) {
                            streakBonusElement.style.display = 'block';
                            var multiplier = data.streak >= 7 ? '1.3x' : (data.streak >= 5 ? '1.2x' : '1.1x');
                            streakBonusElement.innerHTML = '<span style="font-size: 1.5rem; color: #ff6b35; font-weight: 800;">🔥 Streak: ' + data.streak + ' (Bonus: ' + multiplier + ')</span>';
                        } else {
                            streakBonusElement.style.display = 'none';
                        }
                        
                        document.getElementById('points-earned').textContent = '+' + data.points;
                        document.getElementById('total-score').textContent = data.total_score;
                        
                        pollForNextQuestion();
                    }
                })
                .catch(error => {
                    setTimeout(pollForResult, 1000);
                });
        }
        
        function pollForNextQuestion() {
            fetch('../api/poll.php?pin=' + gamePin + '&action=question', {
                credentials: 'same-origin'
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.question) {
                        if (currentQuestion !== data.question_number) {
                            document.getElementById('result-area').style.display = 'none';
                            document.getElementById('game-area').style.display = 'block';
                            showQuestion(data.question, data.question_number);
                        } else {
                            setTimeout(pollForNextQuestion, 1000);
                        }
                    } else if (data.success === false && data.error === 'No more questions') {
                        showFinalResults();
                    } else {
                        setTimeout(pollForNextQuestion, 1000);
                    }
                })
                .catch(error => {
                    setTimeout(pollForNextQuestion, 1000);
                });
        }
        
        function showFinalResults() {
            document.getElementById('waiting-area').style.display = 'none';
            document.getElementById('game-area').style.display = 'none';
            document.getElementById('result-area').style.display = 'none';
            document.getElementById('final-results').style.display = 'block';
            
            fetch('../api/poll.php?pin=' + gamePin + '&action=final_results', {
                credentials: 'same-origin'
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.leaderboard) {
                        displayPodium(data.leaderboard);
                        displayLeaderboard(data.leaderboard);
                    }
                })
                .catch(error => {
                    console.error('Error getting final results:', error);
                });
        }
        
        function displayPodium(leaderboard) {
            if (leaderboard.length >= 1) {
                var first = leaderboard[0];
                document.getElementById('podium-1st').style.display = 'flex';
                document.querySelector('#podium-1st .podium-avatar').textContent = first.username.charAt(0).toUpperCase();
                document.querySelector('#podium-1st .podium-name').textContent = first.username + (first.username === playerName ? ' (You)' : '');
                document.querySelector('#podium-1st .podium-score').textContent = first.score;
            }
            
            if (leaderboard.length >= 2) {
                var second = leaderboard[1];
                document.getElementById('podium-2nd').style.display = 'flex';
                document.querySelector('#podium-2nd .podium-avatar').textContent = second.username.charAt(0).toUpperCase();
                document.querySelector('#podium-2nd .podium-name').textContent = second.username + (second.username === playerName ? ' (You)' : '');
                document.querySelector('#podium-2nd .podium-score').textContent = second.score;
            }
            
            if (leaderboard.length >= 3) {
                var third = leaderboard[2];
                document.getElementById('podium-3rd').style.display = 'flex';
                document.querySelector('#podium-3rd .podium-avatar').textContent = third.username.charAt(0).toUpperCase();
                document.querySelector('#podium-3rd .podium-name').textContent = third.username + (third.username === playerName ? ' (You)' : '');
                document.querySelector('#podium-3rd .podium-score').textContent = third.score;
            }
        }
        
        function displayLeaderboard(leaderboard) {
            var container = document.getElementById('leaderboard-container');
            var html = '<div class="leaderboard">';
            
            leaderboard.forEach(function(player, index) {
                var rankClass = '';
                if (index === 0) rankClass = 'gold';
                else if (index === 1) rankClass = 'silver';
                else if (index === 2) rankClass = 'bronze';
                
                var isMe = player.username === playerName;
                
                html += '<div class="leaderboard-item" style="' + (isMe ? 'border: 3px solid #46178f;' : '') + '">';
                html += '<div class="leaderboard-rank ' + rankClass + '">' + (index + 1) + '</div>';
                html += '<div class="leaderboard-name">' + player.username + (isMe ? ' (You)' : '') + '</div>';
                html += '<div class="leaderboard-score">' + player.score + '</div>';
                html += '</div>';
            });
            
            html += '</div>';
            container.innerHTML = html;
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            pollInterval = setInterval(pollQuestion, 1000);
        });
    </script>
</head>
<body>
    <div class="container">
        <div class="card" id="waiting-area">
            <h1>⏳ Waiting for Question...</h1>
            <div class="loading" style="font-size: 2rem;">Get ready!</div>
        </div>
        
        <div class="card" id="game-area" style="display: none;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 id="question-number">❓ Question 1</h2>
                 <div class="timer-progress"><div class="timer-progress-bar" id="timer-progress-bar"></div></div>
                 <div class="timer" id="timer-display">20</div>
            </div>
            
            <div class="question-display" id="question-text">
                Loading question...
            </div>
            
            <div id="streak-indicator" style="text-align: center; margin: 20px 0; display: none;">
                <span style="font-size: 1.2rem; color: #666;">🔥 Streak: </span>
                <strong id="streak-count" style="font-size: 1.5rem; color: #ff6b35;">0</strong>
            </div>
            
            <div class="answer-options">
                <button class="answer-option red">Option 1</button>
                <button class="answer-option blue">Option 2</button>
                <button class="answer-option yellow">Option 3</button>
                <button class="answer-option green">Option 4</button>
            </div>
            
            <div style="text-align: center; margin-top: 20px;">
                <p style="font-size: 1.2rem; color: #666;">Your Score: <strong id="current-score">0</strong></p>
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
                <div style="margin-bottom: 15px;">
                    <span style="color: #666; font-size: 1.1rem;">Correct Answer:</span><br>
                    <strong id="correct-answer" style="font-size: 1.5rem; color: #28a745;">-</strong>
                </div>
                <div>
                    <span style="color: #666; font-size: 1.1rem;">Players Who Got It Right:</span><br>
                    <strong id="percentage" style="font-size: 2rem; color: #1368ce;">0%</strong>
                    <span style="color: #666; font-size: 1rem;">(<span id="correct-count">0</span>/<span id="total-count">0</span>)</span>
                </div>
            </div>
            
            <div class="points-earned" id="points-earned">+1000</div>
            <p style="font-size: 1.3rem; margin-top: 20px;">Total Score: <strong id="total-score">1000</strong></p>
            <div class="loading" style="margin-top: 40px;">Waiting for next question...</div>
        </div>
        
        <div class="card" id="final-results" style="display: none;">
            <h1 style="text-align: center;">🏆 Final Results</h1>
            
            <div id="podium-container" style="display: flex; justify-content: center; align-items: flex-end; height: 350px; margin: 30px 0;">
                <div id="podium-2nd" class="podium-place podium-silver" style="display: none;">
                    <div class="podium-avatar"></div>
                    <div class="podium-name"></div>
                    <div class="podium-score"></div>
                    <div class="podium-block" style="background: #C0C0C0; height: 200px;">2</div>
                </div>
                <div id="podium-1st" class="podium-place podium-gold" style="display: none;">
                    <div class="podium-avatar"></div>
                    <div class="podium-name"></div>
                    <div class="podium-score"></div>
                    <div class="podium-block" style="background: #FFD700; height: 250px;">🥇</div>
                </div>
                <div id="podium-3rd" class="podium-place podium-bronze" style="display: none;">
                    <div class="podium-avatar"></div>
                    <div class="podium-name"></div>
                    <div class="podium-score"></div>
                    <div class="podium-block" style="background: #CD7F32; height: 150px;">3</div>
                </div>
            </div>
            
            <div id="leaderboard-container"></div>
            
            <div class="text-center mt-40">
                <p style="font-size: 1.5rem;">🎉 Thanks for playing!</p>
                <a href="join.php" class="btn btn-success btn-lg">🔄 Play Again</a>
            </div>
        </div>
    </div>
</body>
</html>
