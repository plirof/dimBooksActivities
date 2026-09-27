<?php
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    $_SESSION['error'] = '⛔ You need to login as admin first!';
    redirect('./dashboard.php');
}

$user = getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    $role = sanitizeInput($_POST['role']);
    
    if (empty($username) || empty($password) || empty($role)) {
        $_SESSION['error'] = '❌ All fields are required!';
        redirect('./create_user.php');
    }
    
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $_SESSION['error'] = '❌ Username can only contain letters, numbers, and underscores!';
        redirect('./create_user.php');
    }
    
    if ($username === DEFAULT_ADMIN_USERNAME && $role === 'admin') {
        $_SESSION['error'] = '❌ Username already exists! Please choose another.';
        redirect('./create_user.php');
    }
    
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
