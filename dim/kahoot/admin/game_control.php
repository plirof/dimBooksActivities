<?php
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    $_SESSION['error'] = '⛔ You need to login as admin first!';
    redirect('../index.php');
}

$user = getCurrentUser();
$quizId = isset($_GET['quiz']) ? sanitizeInput($_GET['quiz']) : '';

if (empty($quizId)) {
    $_SESSION['error'] = '❌ Quiz ID is required!';
    redirect('./host_game.php');
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
    redirect('./host_game.php');
}

$games = getGames();
$existingGame = null;
foreach ($games['active_games'] as $pin => $game) {
    if ($game['host'] === $user['username'] && $game['quiz_id'] === $quizId && $game['status'] === 'waiting') {
        $existingGame = $game;
        $existingGame['pin'] = $pin;
        break;
    }
}

$gamePin = $existingGame ? $existingGame['pin'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Control - Quiz Game</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .game-pin-large {
            font-size: 6rem;
            font-family: 'Fredoka One', cursive;
            color: white;
            text-align: center;
            letter-spacing: 20px;
            text-shadow: 5px 5px 0px rgba(0,0,0,0.2);
            margin: 30px 0;
        }
        
        .participant-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #46178f 0%, #5c2a9e 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 800;
            font-size: 1.2rem;
            margin: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            animation: popIn 0.3s ease-out;
        }
        
        .participants-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            max-height: 400px;
            overflow-y: auto;
            padding: 20px;
            background: rgba(255,255,255,0.1);
            border-radius: 15px;
        }
    </style>
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
        
        <h1>🎮 Game Control</h1>
        
        <div class="card">
            <h2>📝 <?php echo htmlspecialchars($quiz['title']); ?></h2>
            <p><?php echo count($quiz['questions']); ?> questions ready to play!</p>
        </div>
        
        <?php if ($existingGame): ?>
            <div class="card">
                <h2>🎯 Game PIN</h2>
                <div class="game-pin-large"><?php echo $gamePin; ?></div>
                <p style="text-align: center; font-size: 1.2rem; color: #666; margin-bottom: 30px;">
                    Students should enter this PIN to join the game
                </p>
                
                <div class="participants-grid" id="participants-list">
                    <?php 
                    $participantCount = 0;
                    foreach ($existingGame['participants'] as $username => $data): 
                        $participantCount++;
                        $initial = strtoupper(substr($username, 0, 1));
                    ?>
                        <div class="participant-avatar" title="<?php echo htmlspecialchars($username); ?>">
                            <?php echo $initial; ?>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php if ($participantCount === 0): ?>
                        <p style="color: white; text-align: center; width: 100%;">
                            Waiting for students to join...
                        </p>
                    <?php endif; ?>
                </div>
                
                <div class="text-center mt-40">
                    <h3>👥 Players Joined: <span id="player-count"><?php echo $participantCount; ?></span></h3>
                </div>
                
                <div class="text-center mt-20">
                    <label style="cursor: pointer; font-size: 1.1rem; color: #555;">
                        <input type="checkbox" id="auto-advance-check"> ⏭ Auto-advance after 30s
                    </label>
                </div>
                
                <div class="d-flex justify-between mt-40">
                    <a href="host_game.php" class="btn btn-danger btn-lg">🚪 End Game</a>
                    <button class="btn btn-success btn-lg" onclick="startGame()">🚀 Start Game</button>
                </div>
            </div>
            
            <div class="card" id="game-area" style="display: none;">
                <h3 style="text-align: right; margin-bottom: 10px;">👥 Players: <span id="game-player-count">0</span></h3>
                <h2 id="question-number">❓ Question 1</h2>
                <div class="question-display" id="question-text"></div>
                
                <div id="timer-display" class="timer">20</div>
                
                <div id="answer-stats" style="margin: 30px 0;"></div>
                
                <div class="text-center mt-40" id="next-question-btn" style="display: none;">
                    <button class="btn btn-primary btn-lg" onclick="nextQuestion()">Next Question ➡️</button>
                </div>
            </div>
        <?php else: ?>
            <div class="card" style="text-align: center; padding: 60px;">
                <h2>🎯 Ready to Start?</h2>
                <p style="font-size: 1.3rem; margin: 30px 0; color: #666;">
                    Click the button below to generate a game PIN and start accepting players!
                </p>
                <button class="btn btn-success btn-lg" onclick="createGame()">🎲 Generate Game PIN</button>
            </div>
        <?php endif; ?>
        
        <div class="text-center mt-20">
            <a href="host_game.php" class="btn btn-warning">← Back to Quiz Selection</a>
        </div>
    </div>
    
    <script>
        var gamePin = <?php echo $gamePin ? json_encode($gamePin) : 'null'; ?>;
        var quizId = <?php echo json_encode($quizId); ?>;
        var currentQuestionData = null;
        var totalQuestions = 0;
        var timerInterval = null;
        var autoAdvanceTimeout = null;
        var autoAdvanceEnabled = false;
        
        function createGame() {
            fetch('../api/game.php', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=create&quiz_id=' + quizId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    gamePin = data.pin;
                    location.reload();
                } else {
                    alert('Error: ' + data.error);
                }
            })
            .catch(error => {
                alert('Error creating game: ' + error);
            });
        }
        
        function startGame() {
            if (!confirm('Are you ready to start the game?')) {
                return;
            }
            
            fetch('../api/game.php', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=start&pin=' + gamePin
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelector('.card:nth-of-type(2)').style.display = 'none';
                    document.getElementById('game-area').style.display = 'block';
                    fetch('../api/game_status.php?pin=' + gamePin, {
                        credentials: 'same-origin'
                    })
                        .then(response => response.json())
                        .then(statusData => {
                            if (statusData.success && statusData.participants) {
                                var playerCount = Object.keys(statusData.participants).length;
                                var element = document.getElementById('game-player-count');
                                if (element) {
                                    element.textContent = playerCount;
                                }
                            }
                        });
                    autoAdvanceEnabled = document.getElementById('auto-advance-check').checked;
                    pollParticipants();
                    fetchQuestion();
                } else {
                    var errorMsg = 'Error: ' + data.error;
                    if (data.debug) {
                        errorMsg += '\n\nDebug info:\n' + JSON.stringify(data.debug, null, 2);
                    }
                    alert(errorMsg);
                }
            })
            .catch(error => {
                alert('Error starting game: ' + error);
            });
        }
        
        function fetchQuestion() {
            fetch('../api/poll.php?pin=' + gamePin + '&action=host_question', {
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.question) {
                    currentQuestionData = data.question;
                    totalQuestions = data.total_questions;
                    showQuestion(data.question_number);
                } else {
                    endGame();
                }
            })
            .catch(error => {
                console.error('Error fetching question:', error);
            });
        }
        
        function showQuestion(questionNumber) {
            var question = currentQuestionData;
            document.getElementById('question-number').textContent = '❓ Question ' + questionNumber + ' of ' + totalQuestions;
            document.getElementById('question-text').textContent = question.text;
            document.getElementById('timer-display').textContent = question.time || 20;
            document.getElementById('timer-display').classList.remove('warning');
            document.getElementById('next-question-btn').style.display = 'none';
            document.getElementById('answer-stats').innerHTML = '';
            
            var timeLeft = question.time || 20;
            clearInterval(timerInterval);
            timerInterval = setInterval(function() {
                timeLeft--;
                document.getElementById('timer-display').textContent = timeLeft;
                
                if (timeLeft <= 5) {
                    document.getElementById('timer-display').classList.add('warning');
                }
                
                if (timeLeft <= 0) {
                    clearInterval(timerInterval);
                    showResults();
                }
            }, 1000);
            
            pollAnswers();
        }
        
        function pollAnswers() {
            if (timerInterval === null) return;
            
            fetch('../api/poll.php?pin=' + gamePin + '&action=answers', {
                credentials: 'same-origin'
            })
                .then(response => response.json())
                .then(data => {
                    if (data.answers) {
                        displayAnswerStats(data.answers);
                    }
                    if (timerInterval !== null) {
                        setTimeout(pollAnswers, 1000);
                    }
                })
                .catch(error => {
                    console.error('Polling error:', error);
                    if (timerInterval !== null) {
                        setTimeout(pollAnswers, 1000);
                    }
                });
        }
        
        function pollParticipants() {
            if (timerInterval === null) return;
            
            fetch('../api/game_status.php?pin=' + gamePin, {
                credentials: 'same-origin'
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.participants) {
                        var playerCount = Object.keys(data.participants).length;
                        var element = document.getElementById('game-player-count');
                        if (element) {
                            element.textContent = playerCount;
                        }
                    }
                    if (timerInterval !== null) {
                        setTimeout(pollParticipants, 2000);
                    }
                })
                .catch(error => {
                    console.error('Participant polling error:', error);
                    if (timerInterval !== null) {
                        setTimeout(pollParticipants, 2000);
                    }
                });
        }
        
        function displayAnswerStats(answers) {
            var element = document.getElementById('answer-stats');
            if (!element) return;
            
            var statsHtml = '<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; text-align: center;">';
            var colors = ['#46178f', '#1368ce', '#d69e00', '#26890c'];
            var labels = ['🔴 Red', '🔵 Blue', '🟡 Yellow', '🟢 Green'];
            
            for (var i = 0; i < 4; i++) {
                var count = answers[i] || 0;
                statsHtml += '<div style="background: ' + colors[i] + '; color: white; padding: 20px; border-radius: 15px;">';
                statsHtml += '<div style="font-size: 2.5rem; font-family: Fredoka One, cursive;">' + count + '</div>';
                statsHtml += '<div>' + labels[i] + '</div>';
                statsHtml += '</div>';
            }
            statsHtml += '</div>';
            
            element.innerHTML = statsHtml;
        }
        
        function showResults() {
            var question = currentQuestionData;
            var correctIndex = question.correct;
            var correctAnswer = question.answers[correctIndex];
            
            document.getElementById('question-text').innerHTML = 
                '✅ Correct Answer: <span style="color: #28a745;">' + correctAnswer + '</span>';
            
            fetch('../api/poll.php?pin=' + gamePin + '&action=final_results', {
                credentials: 'same-origin'
            })
                .then(response => response.json())
                .then(data => {
                    if (data.leaderboard) {
                        displayLeaderboard(data.leaderboard);
                    }
                });
            
            document.getElementById('next-question-btn').style.display = 'block';
            
            if (autoAdvanceEnabled) {
                autoAdvanceTimeout = setTimeout(nextQuestion, 30000);
            }
        }
        
        function displayLeaderboard(leaderboard) {
            var element = document.getElementById('answer-stats');
            if (!element) return;
            
            var html = '<h3 style="text-align: center; margin-bottom: 20px;">🏆 Leaderboard</h3>';
            html += '<div class="leaderboard">';
            
            leaderboard.slice(0, 5).forEach(function(player, index) {
                html += '<div class="leaderboard-item">';
                html += '<div class="leaderboard-rank">' + (index + 1) + '</div>';
                html += '<div class="leaderboard-name">' + player.username + '</div>';
                html += '<div class="leaderboard-score">' + player.score + '</div>';
                html += '</div>';
            });
            
            html += '</div>';
            element.innerHTML = html;
        }
        
        function nextQuestion() {
            if (autoAdvanceTimeout) {
                clearTimeout(autoAdvanceTimeout);
                autoAdvanceTimeout = null;
            }
            fetch('../api/game.php', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=next_question&pin=' + gamePin
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    fetchQuestion();
                } else {
                    alert('Error advancing question: ' + data.error);
                }
            })
            .catch(error => {
                alert('Error: ' + error);
            });
        }
        
        function endGame() {
            clearInterval(timerInterval);
            timerInterval = null;
            if (autoAdvanceTimeout) {
                clearTimeout(autoAdvanceTimeout);
                autoAdvanceTimeout = null;
            }
            
            document.getElementById('game-area').innerHTML = 
                '<div class="game-over"><h2 style="font-size: 3rem;">🎉 Game Over!</h2><p style="font-size: 1.5rem;">Thanks for playing!</p>' +
                '<div style="margin-top: 30px;"><button class="btn btn-info btn-lg" onclick="exportResults()">📥 Export Results (CSV)</button>' +
                '<button class="btn btn-success btn-lg" style="margin-left: 20px;" onclick="location.reload()">🔄 Play Again</button></div></div>';
            
            fetch('../api/game.php', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=end&pin=' + gamePin
            });
        }
        
        function exportResults() {
            fetch('../api/poll.php?pin=' + gamePin + '&action=export_results', {
                credentials: 'same-origin'
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.csv) {
                        var blob = new Blob([data.csv], {type: 'text/csv;charset=utf-8;'});
                        var url = URL.createObjectURL(blob);
                        var a = document.createElement('a');
                        a.href = url;
                        a.download = 'quiz_results_' + new Date().toISOString().slice(0,10) + '.csv';
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                        URL.revokeObjectURL(url);
                    } else {
                        alert('Error: ' + (data.error || 'Failed to export results'));
                    }
                })
                .catch(error => {
                    alert('Error exporting: ' + error);
                });
        }
    </script>
</body>
</html>
