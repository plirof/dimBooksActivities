<?php
session_start();

if (!isset($_SESSION['username']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'publisher')) {
    header('Location: ../index.php');
    exit;
}

require_once '../api/file_utils.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    $username = $_SESSION['username'];
    
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $message = 'All fields are required.';
    } elseif ($newPassword !== $confirmPassword) {
        $message = 'New password and confirmation do not match.';
    } elseif (strlen($newPassword) < 6) {
        $message = 'New password must be at least 6 characters long.';
    } else {
        $users = readJSONFile('users.json');
        
        if (!isset($users[$username])) {
            $message = 'User not found.';
        } else {
            if (!password_verify($currentPassword, $users[$username]['password'])) {
                $message = 'Current password is incorrect.';
            } else {
                $users[$username]['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
                
                if (writeJSONFile('users.json', $users)) {
                    $message = 'Password changed successfully!';
                } else {
                    $message = 'Error changing password.';
                }
            }
        }
    }
}
$userRole = isset($_SESSION['role']) ? $_SESSION['role'] : 'admin';
$pageTitle = ($userRole === 'admin') ? 'Change Admin Password' : 'Change Your Password';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo $pageTitle; ?> - Wordboard</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .password-form {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            margin: 20px auto;
        }
        
        .password-form h2 {
            margin-top: 0;
            color: #667eea;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .form-group small {
            display: block;
            margin-top: 5px;
            color: #666;
            font-size: 14px;
        }
        
        button {
            width: 100%;
            padding: 12px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        button:hover {
            background: #5568d3;
        }
        
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #667eea;
            text-decoration: none;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="password-form">
            <h2><?php echo $pageTitle; ?></h2>
            
            <?php if ($message): ?>
                <div class="message <?php echo strpos($message, 'Error') !== false || strpos($message, 'incorrect') !== false ? 'error' : 'success'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="hidden" name="action" value="change_password">
                
                <div class="form-group">
                    <label for="current_password">Current Password:</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                
                <div class="form-group">
                    <label for="new_password">New Password:</label>
                    <input type="password" id="new_password" name="new_password" required minlength="6">
                    <small>Password must be at least 6 characters long.</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                </div>
                
                <button type="submit">Change Password</button>
            </form>
            
            <a href="<?php echo ($userRole === 'admin') ? 'dashboard.php' : 'publisher_dashboard.php'; ?>" class="back-link">Back to Dashboard</a>
        </div>
    </div>
    
    <script>
        document.getElementById('new_password').addEventListener('input', function() {
            var confirmInput = document.getElementById('confirm_password');
            if (confirmInput.value) {
                var match = this.value === confirmInput.value;
                confirmInput.style.borderColor = match ? '#28a745' : '#dc3545';
            }
        });
        
        document.getElementById('confirm_password').addEventListener('input', function() {
            var newPassInput = document.getElementById('new_password');
            var match = this.value === newPassInput.value;
            this.style.borderColor = match ? '#28a745' : '#dc3545';
        });
    </script>
</body>
</html>