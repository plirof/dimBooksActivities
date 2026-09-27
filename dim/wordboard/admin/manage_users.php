<?php
session_start();
require_once '../api/file_utils.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$message = '';

if (isset($_GET['message'])) {
    $message = urldecode($_GET['message']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $username = $_POST['username'];
            $password = $_POST['password'];
            $isPublisher = isset($_POST['is_publisher']) && $_POST['is_publisher'] === 'on';
            
            if (empty($username) || empty($password)) {
                $message = 'Username and password are required.';
            } else {
                $users = readJSONFile('users.json');
                
                if (isset($users[$username])) {
                    $message = 'Username already exists.';
                } else {
                    $users[$username] = array(
                        'password' => password_hash($password, PASSWORD_DEFAULT),
                        'role' => $isPublisher ? 'publisher' : 'student'
                    );
                    
                    if (writeJSONFile('users.json', $users)) {
                        header('Location: manage_users.php?message=' . urlencode('User added successfully.'));
                        exit;
                    } else {
                        $message = 'Error saving user.';
                    }
                }
            }
        } elseif ($_POST['action'] === 'delete') {
            $username = $_POST['username'];
            
            if ($username === 'admin') {
                $message = 'Cannot delete admin account.';
            } else {
                $users = readJSONFile('users.json');
                
                if (isset($users[$username])) {
                    unset($users[$username]);
                    
                    if (writeJSONFile('users.json', $users)) {
                        header('Location: manage_users.php?message=' . urlencode('User deleted successfully.'));
                        exit;
                    } else {
                        $message = 'Error deleting user.';
                    }
                } else {
                    $message = 'User not found.';
                }
            }
        } elseif ($_POST['action'] === 'toggle_permission') {
            $username = $_POST['username'];
            
            if ($username === 'admin') {
                $message = 'Cannot change admin permissions.';
            } else {
                $users = readJSONFile('users.json');
                
                if (isset($users[$username])) {
                    $currentRole = $users[$username]['role'];
                    $users[$username]['role'] = ($currentRole === 'student') ? 'publisher' : 'student';
                    
                    if (writeJSONFile('users.json', $users)) {
                        header('Location: manage_users.php?message=' . urlencode('User permission updated successfully.'));
                        exit;
                    } else {
                        $message = 'Error updating user permission.';
                    }
                } else {
                    $message = 'User not found.';
                }
            }
        }
    }
}

$users = readJSONFile('users.json');
$nonAdminUsers = array();
foreach ($users as $username => $data) {
    if ($data['role'] !== 'admin') {
        $nonAdminUsers[$username] = $data;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Manage Users - Wordboard</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .user-list li {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px;
            background: white;
            margin-bottom: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .user-info {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .role-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .role-badge.student {
            background: #17a2b8;
            color: white;
        }
        
        .role-badge.publisher {
            background: #fd7e14;
            color: white;
        }
        
        .toggle-permission {
            padding: 6px 12px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            margin-right: 10px;
        }
        
        .toggle-permission:hover {
            background: #5568d3;
        }
        
        .back-button {
            display: inline-block;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 20px;
            font-weight: bold;
        }
        
        .back-button:hover {
            background: #5568d3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Manage Users</h1>
            <div class="header-actions">
                <a href="dashboard.php" class="back-button">Back to Dashboard</a>
            </div>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, 'Error') !== false ? 'error' : 'success'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="form-section">
            <h2>Add New User</h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <label for="is_publisher">Grant Publisher Permission:</label>
                    <input type="checkbox" id="is_publisher" name="is_publisher">
                    <small>Publishers can create their own activities</small>
                </div>
                
                <button type="submit">Add User</button>
            </form>
        </div>
        
        <div class="list-section">
            <h2>Users</h2>
            <?php if (count($nonAdminUsers) > 0): ?>
                <ul class="user-list">
                    <?php foreach ($nonAdminUsers as $username => $data): ?>
                        <li>
                            <div class="user-info">
                                <span><strong><?php echo htmlspecialchars($username); ?></strong></span>
                                <span class="role-badge <?php echo htmlspecialchars($data['role']); ?>">
                                    <?php echo htmlspecialchars($data['role']); ?>
                                </span>
                            </div>
                            <div class="user-actions">
                                <form method="POST" action="" class="inline-form">
                                    <input type="hidden" name="action" value="toggle_permission">
                                    <input type="hidden" name="username" value="<?php echo htmlspecialchars($username); ?>">
                                    <button type="submit" class="toggle-permission">
                                        Toggle Permission
                                    </button>
                                </form>
                                <form method="POST" action="" class="inline-form">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="username" value="<?php echo htmlspecialchars($username); ?>">
                                    <button type="submit" class="delete-btn">Delete</button>
                                </form>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No users found.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
