<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Game - Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div style="max-width: 500px; margin: 50px auto;">
            <div class="card">
                <h1>🎮 Quiz Time!</h1>
                
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
                
                <form action="api/auth.php" method="POST">
                    <input type="hidden" name="action" value="login">
                    <div class="form-group">
                        <label for="username">👤 Username</label>
                        <input type="text" id="username" name="username" required 
                               placeholder="Enter your username" autocomplete="username">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">🔐 Password</label>
                        <input type="password" id="password" name="password" required 
                               placeholder="Enter your password" autocomplete="current-password">
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">🚀 Let's Play!</button>
                </form>
                
                <div class="mt-40 text-center">
                    <p style="font-weight: 700; color: #666; margin-bottom: 15px;">
                        🌟 Join a Game
                    </p>
                    <a href="student/join.php" class="btn btn-success btn-lg">
                        🎯 Join Game
                    </a>
                </div>

                <div class="mt-40 text-center">
                    <p style="font-weight: 700; color: #666; margin-bottom: 15px;">
                        👤 Play Solo
                    </p>
                    <a href="student/solo.php" class="btn btn-primary btn-lg">
                        🎮 Play Solo Quiz
                    </a>
                </div>
            </div>
            
            <div class="card text-center" style="margin-top: 20px;">
                <p style="font-weight: 700; color: #666;">
                    📝 Default Admin Login:<br>
                    Username: <strong>admin</strong><br>
                    Password: <strong>admin123</strong>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
