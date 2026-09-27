<?php
require_once '../config.php';

$action = isset($_POST['action']) ? $_POST['action'] : '';
$username = isset($_POST['username']) ? sanitizeInput($_POST['username']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'login') {
        $users = getUsers();
        $foundUser = null;
        
        foreach ($users['users'] as $user) {
            if ($user['username'] === $username) {
                $foundUser = $user;
                break;
            }
        }
        
        if ($foundUser && password_verify($password, $foundUser['password'])) {
            $_SESSION['user'] = [
                'username' => $foundUser['username'],
                'role' => $foundUser['role']
            ];
            
            if ($foundUser['role'] === 'admin') {
                redirect('../admin/dashboard.php');
            } else {
                redirect('../student/dashboard.php');
            }
        } else {
            $_SESSION['error'] = '❌ Oops! Wrong username or password. Try again! 💪';
            redirect('../index.php');
        }
    } elseif ($action === 'logout') {
        $_SESSION['success'] = '👋 See you next time! Bye bye!';
        session_destroy();
        redirect('../index.php');
    }
} else {
    redirect('../index.php');
}
?>
