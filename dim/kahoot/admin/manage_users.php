<?php
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    $_SESSION['error'] = '⛔ You need to login as admin first!';
    redirect('./dashboard.php');
}

$user = getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_user'])) {
        $usernameToDelete = sanitizeInput($_POST['username']);
        
        if ($usernameToDelete === DEFAULT_ADMIN_USERNAME) {
            $_SESSION['error'] = '❌ Cannot delete the default admin account!';
            redirect('./manage_users.php');
        } else {
            $users = getUsers();
            $found = false;
            foreach ($users['users'] as $index => $u) {
                if ($u['username'] === $usernameToDelete) {
                    unset($users['users'][$index]);
                    $users['users'] = array_values($users['users']);
                    $found = true;
                    break;
                }
            }
            
            if ($found && writeUsers($users)) {
                $_SESSION['success'] = '✅ User deleted successfully!';
            } else {
                $_SESSION['error'] = '❌ Failed to delete user!';
            }
        }
        
        redirect('./manage_users.php');
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'delete_multiple') {
        if (!isset($_POST['users_to_delete'])) {
            $_SESSION['error'] = '❌ No users selected for deletion!';
            redirect('./manage_users.php');
        }
        
        $usersToDelete = $_POST['users_to_delete'];
        $users = getUsers();
        
        $deleted = 0;
        $adminSkipped = false;
        foreach ($usersToDelete as $usernameToDelete) {
            if ($usernameToDelete === DEFAULT_ADMIN_USERNAME) {
                $adminSkipped = true;
                continue;
            }
            
            foreach ($users['users'] as $index => $u) {
                if ($u['username'] === $usernameToDelete) {
                    unset($users['users'][$index]);
                    $deleted++;
                    break;
                }
            }
        }
        
        $users['users'] = array_values($users['users']);
        
        if ($deleted > 0) {
            if (writeUsers($users)) {
                $_SESSION['success'] = '✅ Successfully deleted ' . $deleted . ' user(s)!';
            } else {
                $_SESSION['error'] = '❌ Failed to delete users!';
            }
        } else if ($adminSkipped) {
            $_SESSION['error'] = '❌ Cannot delete admin account!';
        } else {
            $_SESSION['error'] = '❌ No users were deleted!';
        }
        
        redirect('./manage_users.php');
    }
    
    if (isset($_POST['generate_multiple'])) {
        $prefix = isset($_POST['prefix']) ? sanitizeInput($_POST['prefix']) : 'student';
        $count = isset($_POST['count']) ? intval($_POST['count']) : 15;
        $randomPasswords = isset($_POST['random_passwords']);
        
        if ($count < 1 || $count > 50) {
            $_SESSION['error'] = '❌ Count must be between 1 and 50!';
            redirect('./manage_users.php');
        }
        
        $passwordLength = 6;
        $passwordChars = 'abcdefghijklmnopqrstuvwxyz0123456789!@#$%';
        
        $users = getUsers();
        $generated = 0;
        $generatedUsers = [];
        
        for ($i = 0; $i < $count; $i++) {
            $username = $prefix . ($i + 1);
            $exists = false;
            foreach ($users['users'] as $u) {
                if ($u['username'] === $username) {
                    $exists = true;
                    break;
                }
            }
            
            if ($exists) {
                continue;
            }
            
            if ($randomPasswords) {
                $password = '';
                for ($j = 0; $j < $passwordLength; $j++) {
                    $password .= $passwordChars[rand(0, strlen($passwordChars) - 1)];
                }
            } else {
                $password = $username;
            }
            
            $users['users'][] = [
                'username' => $username,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'role' => 'student'
            ];
            
            $generatedUsers[] = [
                'username' => $username,
                'password' => $password
            ];
            
            $generated++;
        }
        
        if ($generated > 0) {
            if (writeUsers($users)) {
                $_SESSION['success'] = '✅ Successfully generated ' . $generated . ' student accounts!';
                
                $_SESSION['generated_accounts'] = $generatedUsers;
            } else {
                $_SESSION['error'] = '❌ Failed to generate users!';
            }
        } else {
            $_SESSION['error'] = '❌ Could not generate unique usernames! Try a different prefix.';
        }
        
        redirect('./manage_users.php');
    }
    
    if (isset($_POST['clear_generated'])) {
        unset($_SESSION['generated_accounts']);
        redirect('./manage_users.php');
    }
    
    if (isset($_POST['change_password'])) {
        $username = sanitizeInput($_POST['username']);
        $newPassword = $_POST['new_password'];
        
        if (empty($newPassword)) {
            $_SESSION['error'] = '❌ Password cannot be empty!';
            redirect('./manage_users.php');
        }
        
        if ($username === DEFAULT_ADMIN_USERNAME) {
            $_SESSION['error'] = '❌ Cannot change admin password this way!';
            redirect('./manage_users.php');
        }
        
        $users = getUsers();
        $found = false;
        foreach ($users['users'] as &$u) {
            if ($u['username'] === $username) {
                $u['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
                $found = true;
                break;
            }
        }
        
        if ($found && writeUsers($users)) {
            $_SESSION['success'] = '✅ Password changed successfully for ' . htmlspecialchars($username) . '!';
        } else {
            $_SESSION['error'] = '❌ Failed to change password!';
        }
        
        redirect('./manage_users.php');
    }
}

$users = getUsers();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Quiz Game</title>
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
        
        <h1>👥 Manage Users</h1>
        
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
        
        <?php if (isset($_SESSION['generated_accounts'])): ?>
            <div class="alert alert-info" style="background: #d1ecf1; border-color: #bee5eb;">
                <h3 style="color: #0c5460; margin-bottom: 15px;">📋 Generated Accounts</h3>
                <?php foreach ($_SESSION['generated_accounts'] as $u): ?>
                    <div style="background: white; padding: 15px; margin: 10px 0; border-radius: 10px; display: inline-block; min-width: 250px;">
                        <strong style="color: #46178f;">👤 <?php echo htmlspecialchars($u['username']); ?></strong><br>
                        <strong style="color: #26890c;">🔑 <?php echo htmlspecialchars($u['password']); ?></strong>
                    </div>
                <?php endforeach; ?>
                <p style="margin-top: 20px; color: #666;">
                    <em>Save these credentials and share with students!</em>
                </p>
                <form method="POST" style="display: inline; margin-top: 20px;">
                    <input type="hidden" name="action" value="clear_generated">
                    <button type="submit" class="btn btn-warning">Clear This Message</button>
                </form>
            </div>
            <?php 
            unset($_SESSION['generated_accounts']);
            ?>
        <?php endif; ?>
        
        <!-- Add Single User Form -->
        <div class="card">
            <h2>➕ Add New Student</h2>
            <form method="POST" action="./create_user.php">
                <div class="form-group">
                    <label for="username">👤 Username</label>
                    <input type="text" id="username" name="username" required 
                           placeholder="Enter username (3-20 characters)"
                           minlength="<?php echo MIN_USERNAME_LENGTH; ?>"
                           maxlength="<?php echo MAX_USERNAME_LENGTH; ?>"
                           style="width: 100%; padding: 15px;">
                </div>
                
                <div class="form-group">
                    <label for="password">🔐 Password</label>
                    <input type="text" id="password" name="password" required 
                           placeholder="Enter password (any length)"
                           style="width: 100%; padding: 15px;">
                </div>
                
                <div class="form-group">
                    <label for="role">🎭 Role</label>
                    <select id="role" name="role" required style="width: 100%; padding: 15px;">
                        <option value="student" selected>🎓 Student</option>
                        <option value="admin">👨‍💼 Admin</option>
                    </select>
                </div>
                
                <div class="d-flex justify-between mt-40">
                    <button type="submit" class="btn btn-primary">
                        ➕ Add User
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Generate Multiple Users Form -->
        <div class="card">
            <h2>🎲 Generate Multiple Student Accounts</h2>
            <p style="color: #666; margin-bottom: 20px;">
                Generate multiple student accounts with random passwords at once!
            </p>
            <form method="POST">
                <div class="form-group">
                    <label for="prefix">🏷️ Username Prefix</label>
                    <input type="text" id="prefix" name="prefix" required 
                           placeholder="e.g., student"
                           value="student"
                           style="width: 100%; padding: 15px;">
                    <small style="color: #666;">Generated usernames will be: student1, student2, student3, etc.</small>
                </div>
                
                <div class="form-group">
                    <label for="count">📊 Number of Accounts</label>
                    <input type="number" id="count" name="count" required 
                           min="1" max="50" value="15"
                           placeholder="How many accounts to generate? (1-50)"
                           style="width: 100%; padding: 15px;">
                </div>
                
                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                        <input type="checkbox" id="random_passwords" name="random_passwords" value="1" 
                               style="width: 20px; height: 20px; transform: scale(1.2);">
                        <span>🎲 Generate Random Passwords</span>
                    </label>
                    <small style="color: #666;">If unchecked, password will be the same as username</small>
                </div>
                
                <div class="d-flex justify-center mt-40">
                    <button type="submit" name="generate_multiple" value="1" class="btn btn-success btn-lg">
                        🎲 Generate Accounts
                    </button>
                </div>
            </form>
        </div>
        
        <!-- All Users Table with Multi-Delete -->
        <div class="card">
            <div class="d-flex justify-between" style="align-items: center; margin-bottom: 20px;">
                <h2>📋 All Users</h2>
                <div class="d-flex" style="gap: 10px;">
                    <button onclick="toggleAllCheckboxes()" class="btn btn-info btn-sm">☑️ Select All</button>
                    <button onclick="clearAllCheckboxes()" class="btn btn-warning btn-sm">❌ Clear All</button>
                    <?php if (count($users['users']) > 1): ?>
                        <button onclick="submitMultipleDelete()" class="btn btn-danger btn-sm">
                            🗑️ Delete Selected (<?php echo count($users['users']) - 1; ?>)
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 50px;">
                            <input type="checkbox" id="select-all" onchange="toggleAllCheckboxes()" style="transform: scale(1.3);">
                        </th>
                        <th>👤 Username</th>
                        <th>🔑 Password (Admin Only)</th>
                        <th>🎭 Role</th>
                        <th>🎯 Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users['users'] as $u): ?>
                    <tr>
                        <td>
                            <?php if ($u['username'] !== DEFAULT_ADMIN_USERNAME): ?>
                                <input type="checkbox" class="user-checkbox" 
                                               name="users_to_delete[]" 
                                               value="<?php echo htmlspecialchars($u['username']); ?>"
                                               onchange="updateSelectButton()">
                            <?php else: ?>
                                <input type="checkbox" disabled title="Cannot delete admin account" style="opacity: 0.5;">
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($u['username']); ?></td>
                        <td>
                            <?php if ($u['role'] === 'admin'): ?>
                                <span style="font-family: monospace; font-size: 0.9rem;">•••••</span>
                            <?php else: ?>
                                <span style="color: #999;">Hidden</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge" style="padding: 5px 15px; border-radius: 30px; font-weight: 800; 
                                           <?php echo $u['role'] === 'admin' ? 'background: #46178f; color: white;' : 'background: #26890c; color: white;'; ?>">
                                <?php echo ucfirst($u['role']); ?>
                            </span>
                        </td>
                        <td>
                            <div class="d-flex" style="gap: 10px;">
                                <?php if ($u['username'] !== DEFAULT_ADMIN_USERNAME): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="username" value="<?php echo htmlspecialchars($u['username']); ?>">
                                        <button type="submit" name="delete_user" class="btn btn-sm btn-danger" 
                                                onclick="return confirm('Are you sure you want to delete <?php echo htmlspecialchars($u['username']); ?>?')">
                                            🗑️ Delete
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-primary" 
                                            onclick="showChangePassword('<?php echo htmlspecialchars($u['username']); ?>')">
                                        🔑 Change Password
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <form method="POST" id="delete-form" style="display: none;">
                <input type="hidden" name="action" value="delete_multiple">
            </form>
        </div>
        
        <div class="text-center mt-20">
            <a href="./dashboard.php" class="btn btn-info">← Back to Dashboard</a>
        </div>
        
        <!-- Change Password Modal -->
        <div id="changePasswordModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
            <div class="modal-content" style="background-color: #fefefe; margin: 15% auto; padding: 30px; border: 1px solid #888; width: 400px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.3);">
                <h2 style="margin-bottom: 20px;">🔑 Change Password</h2>
                <p id="changePasswordUsername" style="font-size: 1.1rem; margin-bottom: 20px; color: #46178f; font-weight: bold;"></p>
                <form method="POST">
                    <input type="hidden" name="username" id="passwordUsername">
                    <input type="hidden" name="change_password" value="1">
                    <div class="form-group">
                        <label for="new_password">🔐 New Password</label>
                        <input type="text" id="new_password" name="new_password" required 
                               placeholder="Enter new password"
                               style="width: 100%; padding: 15px; font-size: 1.1rem;">
                    </div>
                    <div class="d-flex justify-between mt-40">
                        <button type="button" onclick="closeChangePasswordModal()" class="btn btn-warning">Cancel</button>
                        <button type="submit" class="btn btn-success">Save New Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        function toggleAllCheckboxes() {
            var checkboxes = document.querySelectorAll('.user-checkbox:not(:disabled)');
            var selectAll = document.getElementById('select-all');
            checkboxes.forEach(function(checkbox) {
                checkbox.checked = selectAll.checked;
            });
            updateSelectButton();
        }
        
        function clearAllCheckboxes() {
            var checkboxes = document.querySelectorAll('.user-checkbox:not(:disabled)');
            var selectAll = document.getElementById('select-all');
            selectAll.checked = false;
            checkboxes.forEach(function(checkbox) {
                checkbox.checked = false;
            });
            updateSelectButton();
        }
        
        function updateSelectButton() {
            var checkboxes = document.querySelectorAll('.user-checkbox:not(:checked):not(:disabled)');
            var selectAll = document.getElementById('select-all');
            if (checkboxes.length === 0 && document.querySelectorAll('.user-checkbox:not(:disabled)').length > 0) {
                selectAll.checked = true;
            } else if (selectAll.checked && document.querySelectorAll('.user-checkbox:not(:checked):not(:disabled)').length > 0) {
                // Keep select-all checked if all non-disabled are checked
                var uncheckedDisabled = document.querySelectorAll('.user-checkbox:not(:checked):not(:disabled)');
                if (uncheckedDisabled.length === 0) {
                    selectAll.checked = true;
                } else {
                    selectAll.checked = false;
                }
            }
            
            var checkboxes2 = document.querySelectorAll('.user-checkbox:checked:not(:disabled)');
            var selectedCount = checkboxes2.length;
            
            var deleteButton = document.querySelector('button[onclick="submitMultipleDelete()"]');
            if (deleteButton) {
                deleteButton.textContent = '🗑️ Delete Selected (' + selectedCount + ')';
                deleteButton.disabled = selectedCount === 0;
            }
        }
        
        function submitMultipleDelete() {
            var checkboxes = document.querySelectorAll('.user-checkbox:checked:not(:disabled)');
            if (checkboxes.length === 0) {
                alert('Please select at least one user to delete!');
                return;
            }
            
            var usernames = [];
            checkboxes.forEach(function(checkbox) {
                usernames.push(checkbox.value);
            });
            
            var formData = new FormData();
            formData.append('action', 'delete_multiple');
            usernames.forEach(function(username) {
                formData.append('users_to_delete[]', username);
            });
            
            if (!confirm('Are you sure you want to delete ' + usernames.length + ' user(s)?')) {
                return;
            }
            
            fetch('./manage_users.php', {
                method: 'POST',
                body: formData
            }).then(function(response) {
                location.reload();
            });
        }
        
        function showChangePassword(username) {
            document.getElementById('passwordUsername').value = username;
            document.getElementById('changePasswordUsername').textContent = 'Username: ' + username;
            document.getElementById('changePasswordModal').style.display = 'block';
        }
        
        function closeChangePasswordModal() {
            document.getElementById('changePasswordModal').style.display = 'none';
        }
        
        window.onclick = function(event) {
            var modal = document.getElementById('changePasswordModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
