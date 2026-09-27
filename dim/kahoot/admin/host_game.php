<?php
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    $_SESSION['error'] = '⛔ You need to login as admin first!';
    redirect('../index.php');
}

$user = getCurrentUser();
$quizzes = getQuizzes();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Host Game - Quiz Game</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .preview-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.7);
        }
        .preview-modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 30px;
            border-radius: 15px;
            width: 80%;
            max-width: 800px;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
        }
        .preview-question {
            margin: 20px 0;
            padding: 15px;
            background: rgba(0,0,0,0.03);
            border-radius: 10px;
        }
        .preview-answer {
            margin: 5px 0;
            padding: 10px;
            border-radius: 5px;
        }
        .preview-answer.correct {
            border: 2px solid #28a745;
            background: #d4edda;
        }
    </style>
    <script>
        function previewQuiz(quizId) {
            var quiz = null;
            var quizzes = <?php echo json_encode($quizzes['quizzes']); ?>;
            
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
            
            var html = '<div class="preview-modal-content">';
            html += '<h2 style="margin-bottom: 20px;">📝 ' + quiz.title + '</h2>';
            html += '<p style="margin-bottom: 30px; color: #666;">' + (quiz.description || 'No description') + '</p>';
            html += '<h3 style="margin-bottom: 20px;">❓ Questions (' + quiz.questions.length + ')</h3>';
            
            for (var i = 0; i < quiz.questions.length; i++) {
                var q = quiz.questions[i];
                html += '<div class="preview-question">';
                html += '<h4 style="margin-bottom: 10px;">Question ' + (i + 1) + '</h4>';
                html += '<p style="font-size: 1.1rem; margin-bottom: 15px;">' + q.text + '</p>';
                
                for (var j = 0; j < q.answers.length; j++) {
                    var isCorrect = j === parseInt(q.correct);
                    html += '<div class="preview-answer' + (isCorrect ? ' correct' : '') + '">';
                    html += '<strong>' + (j + 1) + '.</strong> ' + q.answers[j];
                    html += '</div>';
                }
                
                html += '</div>';
            }
            
            html += '<div style="text-align: center; margin-top: 30px;"><button class="btn btn-primary" onclick="closePreview()">Close</button></div></div>';
            html += '</div>';
            
            document.getElementById('preview-modal').style.display = 'block';
            document.getElementById('preview-modal-content').innerHTML = html;
        }
        
        function closePreview() {
            document.getElementById('preview-modal').style.display = 'none';
        }
        
        window.onclick = function(event) {
            var modal = document.getElementById('preview-modal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
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
        
        <h1>🎯 Host a Game</h1>
        
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
            <h2>📚 Select a Quiz to Play</h2>
            
            <?php if (empty($quizzes['quizzes'])): ?>
                <div class="alert alert-info">
                    ℹ️ No quizzes available! Create a quiz first.
                    <a href="create_quiz.php" class="btn btn-primary" style="margin-left: 15px;">Create Quiz</a>
                </div>
            <?php else: ?>
                <?php foreach ($quizzes['quizzes'] as $quiz): ?>
                    <div class="card" style="margin-bottom: 20px; border-left: 5px solid #46178f;">
                        <h3><?php echo htmlspecialchars($quiz['title']); ?></h3>
                        <p style="margin-bottom: 15px;"><?php echo htmlspecialchars($quiz['description'] ?? 'No description'); ?></p>
                         <p style="font-weight: 700; color: #666; margin-bottom: 15px;">
                             ❓ <?php echo count($quiz['questions']); ?> questions
                         </p>
                         <div style="margin-bottom: 20px;">
                             <button onclick="previewQuiz('<?php echo $quiz['id']; ?>')" class="btn btn-info">
                                 👁️ Preview Quiz
                             </button>
                             <a href="game_control.php?quiz=<?php echo $quiz['id']; ?>" class="btn btn-success">
                                 🚀 Start Game
                             </a>
                         </div>
                     </div>
                 <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div class="text-center mt-20">
            <a href="dashboard.php" class="btn btn-info">← Back to Dashboard</a>
        </div>
    </div>
    
    <div id="preview-modal" class="preview-modal">
        <div id="preview-modal-content" class="preview-modal-content"></div>
    </div>
</body>
</html>
