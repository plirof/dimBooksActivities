<?php
session_start();
require_once 'file_utils.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);
    
    $username = isset($data['username']) ? trim($data['username']) : '';
    $password = isset($data['password']) ? $data['password'] : '';
    
    $users = readJSONFile('../admin/users.json');
    
    if ($users && isset($users[$username])) {
        if (password_verify($password, $users[$username]['password'])) {
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $users[$username]['role'];
            
            $redirectUrl = 'index.php';
            if ($users[$username]['role'] === 'admin') {
                $redirectUrl = 'admin/dashboard.php';
            } else if ($users[$username]['role'] === 'publisher') {
                $redirectUrl = 'admin/publisher_dashboard.php';
            } else if ($users[$username]['role'] === 'student') {
                $redirectUrl = 'student/dashboard.php';
            }
            
            echo json_encode(array(
                'success' => true,
                'role' => $users[$username]['role'],
                'username' => $username,
                'redirect' => $redirectUrl
            ));
            exit;
        }
    }
    
    echo json_encode(array('success' => false, 'message' => 'Invalid username or password.'));
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_SESSION['username'])) {
        echo json_encode(array(
            'success' => true,
            'logged_in' => true,
            'role' => $_SESSION['role'],
            'username' => $_SESSION['username']
        ));
    } else {
        echo json_encode(array('success' => true, 'logged_in' => false));
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    session_destroy();
    echo json_encode(array('success' => true, 'message' => 'Logged out successfully.'));
}
