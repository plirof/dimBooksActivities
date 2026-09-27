<?php
require_once '../config.php';

if (!isLoggedIn() || isAdmin()) {
    $_SESSION['error'] = '⛔ This page is for students only!';
    redirect('../index.php');
}

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Quiz Game</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <nav class="navbar">
            <a href="dashboard.php" class="navbar-brand">🎮 Quiz Game</a>
            <div class="navbar-menu">
                <span>👋 Hello, <?php echo htmlspecialchars($user['username']); ?>!</span>
                <a href="join.php">🎯 Join Game</a>
                <a href="../api/auth.php">
                    <form style="display: inline;" action="../api/auth.php" method="POST">
                        <input type="hidden" name="action" value="logout">
                        <button type="submit" class="btn btn-danger btn-sm" style="margin-left: 15px;">🚪 Logout</button>
                    </form>
                </a>
            </div>
        </nav>
        
        <h1>🎓 Student Dashboard</h1>
        
        <div class="card" style="text-align: center; padding: 60px;">
            <h2>🎯 Ready to Play?</h2>
            <p style="font-size: 1.3rem; margin: 30px 0; color: #666;">
                Join a game using the Game PIN from your teacher!
            </p>
            <a href="join.php" class="btn btn-success btn-lg">🎲 Join Game</a>
        </div>
        
        <div class="card text-center">
            <h3>📝 How to Play</h3>
            <ul style="list-style: none; padding: 0; text-align: left; max-width: 500px; margin: 20px auto;">
                <li style="padding: 15px; margin-bottom: 10px; background: #f8f9fa; border-radius: 10px;">
                    1️⃣ Get the Game PIN from your teacher
                </li>
                <li style="padding: 15px; margin-bottom: 10px; background: #f8f9fa; border-radius: 10px;">
                    2️⃣ Enter the PIN and your nickname
                </li>
                <li style="padding: 15px; margin-bottom: 10px; background: #f8f9fa; border-radius: 10px;">
                    3️⃣ Wait for the game to start
                </li>
                <li style="padding: 15px; margin-bottom: 10px; background: #f8f9fa; border-radius: 10px;">
                    4️⃣ Answer questions fast to earn more points!
                </li>
            </ul>
        </div>
    </div>
</body>
</html>
