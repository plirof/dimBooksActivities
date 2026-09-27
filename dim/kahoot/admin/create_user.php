<?php
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    $_SESSION['error'] = '⛔ You need to login as admin first!';
    redirect('../index.php');
}

$user = getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    $role = sanitizeInput($_POST['role']);
    
    $users = getUsers();
    
    foreach ($users['users'] as $u) {
        if ($u['username'] === $username) {
            $_SESSION['error'] = '❌ Username already exists! Please choose another.';
            redirect('./create_user.php');
        }
    }
    
    $users['users'][] = [
        'username' => $username,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'role' => $role
    ];
    
    if (writeUsers($users)) {
        $_SESSION['success'] = '✅ User created successfully! 🎉';
        redirect('./manage_users.php');
    } else {
        $_SESSION['error'] = '❌ Failed to create user!';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create User - Quiz Game</title>
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
        
        <h1>➕ Create New User</h1>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>
        
        <div class="card" style="max-width: 600px; margin: 0 auto;">
            <form method="POST">
                <div class="form-group">
                    <label for="username">👤 Username</label>
                    <input type="text" id="username" name="username" required 
                           placeholder="Enter username (3-20 characters, letters/numbers/underscore)"
                           minlength="<?php echo MIN_USERNAME_LENGTH; ?>"
                           maxlength="<?php echo MAX_USERNAME_LENGTH; ?>"
                           pattern="[a-zA-Z0-9_]+"
                           title="Only letters, numbers, and underscore allowed">
                    <small style="color: #666; font-size: 0.9rem;">
                        Only letters, numbers, and underscore allowed
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="password">🔐 Password</label>
                    <input type="password" id="password" name="password" required 
                           placeholder="Enter password (6-50 characters)"
                           minlength="<?php echo MIN_PASSWORD_LENGTH; ?>"
                           maxlength="<?php echo MAX_PASSWORD_LENGTH; ?>">
                </div>
                
                <div class="form-group">
                    <label for="role">🎭 Role</label>
                    <select id="role" name="role" required>
                        <option value="student" selected>🎓 Student</option>
                        <option value="admin">👨‍💼 Admin</option>
                    </select>
                </div>
                
                <div class="d-flex justify-between mt-40">
                    <a href="manage_users.php" class="btn btn-warning">← Cancel</a>
                    <button type="submit" class="btn btn-success">➕ Create User</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
