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
    <title>Admin Dashboard - Quiz Game</title>
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
                <a href="../api/auth.php" onclick="this.method='POST';">
                    <form style="display: inline;" action="../api/auth.php" method="POST">
                        <input type="hidden" name="action" value="logout">
                        <button type="submit" class="btn btn-danger btn-sm" style="margin-left: 15px;">🚪 Logout</button>
                    </form>
                </a>
            </div>
        </nav>
        
        <h1>🏠 Welcome to Admin Dashboard!</h1>
        
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
            <h2>📊 Quick Stats</h2>
            <?php
            $users = getUsers();
            $quizzes = getQuizzes();
            $games = getGames();
            
            $studentCount = 0;
            foreach ($users['users'] as $u) {
                if ($u['role'] === 'student') {
                    $studentCount++;
                }
            }
            ?>
            
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
                <div style="background: linear-gradient(135deg, #46178f 0%, #5c2a9e 100%); color: white; padding: 30px; border-radius: 15px; text-align: center;">
                    <div style="font-size: 3rem; font-family: 'Fredoka One', cursive; margin-bottom: 10px;">
                        <?php echo $studentCount; ?>
                    </div>
                    <div>👥 Students</div>
                </div>
                
                <div style="background: linear-gradient(135deg, #1368ce 0%, #2d8ef5 100%); color: white; padding: 30px; border-radius: 15px; text-align: center;">
                    <div style="font-size: 3rem; font-family: 'Fredoka One', cursive; margin-bottom: 10px;">
                        <?php echo count($quizzes['quizzes']); ?>
                    </div>
                    <div>📝 Quizzes</div>
                </div>
                
                <div style="background: linear-gradient(135deg, #26890c 0%, #3aa518 100%); color: white; padding: 30px; border-radius: 15px; text-align: center;">
                    <div style="font-size: 3rem; font-family: 'Fredoka One', cursive; margin-bottom: 10px;">
                        <?php echo count($games['active_games']); ?>
                    </div>
                    <div>🎮 Active Games</div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <h2>🚀 Quick Actions</h2>
            <div class="d-flex flex-wrap justify-center mt-20">
                <a href="create_user.php" class="btn btn-primary btn-lg">➕ Create Student</a>
                <a href="create_quiz.php" class="btn btn-success btn-lg">➕ Create Quiz</a>
                <a href="host_game.php" class="btn btn-info btn-lg">🎯 Host Game</a>
                <a href="manage_users.php" class="btn btn-warning btn-lg">👥 Manage Users</a>
                <a href="manage_quizzes.php" class="btn btn-primary btn-lg">📝 Manage Quizzes</a>
            </div>
        </div>
        
        <div class="card">
            <h2>📚 Recent Quizzes</h2>
            <?php if (empty($quizzes['quizzes'])): ?>
                <div class="alert alert-info">
                    ℹ️ No quizzes yet! Create your first quiz to get started.
                </div>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>📝 Quiz Title</th>
                            <th>❓ Questions</th>
                            <th>🎯 Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($quizzes['quizzes'], 0, 5) as $quiz): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($quiz['title']); ?></td>
                            <td><?php echo count($quiz['questions']); ?></td>
                            <td>
                                <a href="edit_quiz.php?id=<?php echo $quiz['id']; ?>" class="btn btn-sm btn-primary">✏️ Edit</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <?php if (count($quizzes['quizzes']) > 5): ?>
                    <div class="text-center mt-20">
                        <a href="manage_quizzes.php" class="btn btn-info">View All Quizzes →</a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
