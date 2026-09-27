<?php
session_start();

if (!isset($_SESSION['username']) || ($_SESSION['role'] !== 'publisher' && $_SESSION['role'] !== 'admin')) {
    header('Location: ../index.php');
    exit;
}

require_once '../api/file_utils.php';

$activitiesDir = '../admin/activities/';
$index = getActivityIndex($activitiesDir);

$activities = array();
foreach ($index as $id => $entry) {
    $entry['id'] = $id;
    $activities[] = $entry;
}

$currentUser = $_SESSION['username'];
$currentRole = $_SESSION['role'];

$publisherActivities = array();
foreach ($activities as $activity) {
    if (isset($activity['created_by']) && $activity['created_by'] === $currentUser) {
        $publisherActivities[] = $activity;
    }
}

usort($publisherActivities, function($a, $b) {
    return strcmp($b['created_date'], $a['created_date']);
});
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Publisher Dashboard - Wordboard</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .filter-section {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        .filter-section h3 { margin: 0; color: #333; }
        .filter-buttons { display: flex; gap: 10px; flex-wrap: wrap; }
        .filter-btn {
            padding: 8px 16px;
            border: 2px solid #fd7e14;
            background: white;
            color: #fd7e14;
            border-radius: 20px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
        }
        .filter-btn:hover { background: #fd7e14; color: white; }
        .filter-btn.active { background: #fd7e14; color: white; }
        .activity-type {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            color: white;
            text-transform: uppercase;
        }
        .activity-type.quiz { background: #667eea; }
        .activity-type.match { background: #28a745; }
        .activity-type.wheel { background: #ffc107; color: #333; }
        .activity-type.crossword { background: #dc3545; }
        .activity-type.wordsearch { background: #17a2b8; }
        .activity-type.missingword { background: #6f42c1; }
        .activity-type.groupsort { background: #fd7e14; }
        .role-badge {
            background: #fd7e14;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Publisher Dashboard</h1>
            <div class="header-actions">
                <span>Welcome, <?php echo htmlspecialchars($currentUser); ?></span>
                <span class="role-badge"><?php echo htmlspecialchars($currentRole); ?></span>
                <a href="create_activity.php" class="button">Create Activity</a>
                <a href="change_password.php" class="button">Change Password</a>
                <button onclick="logout()">Logout</button>
            </div>
        </div>
        
        <h2>My Activities</h2>
        <p style="color: #666; margin-bottom: 20px;">You can only manage activities you have created.</p>
        
        <div class="filter-section">
            <h3>Filter by Type:</h3>
            <div class="filter-buttons">
                <button class="filter-btn active" data-filter="all">All</button>
                <button class="filter-btn" data-filter="quiz">Quiz</button>
                <button class="filter-btn" data-filter="match">Word Match</button>
                <button class="filter-btn" data-filter="wheel">Wheel</button>
                <button class="filter-btn" data-filter="crossword">Crossword</button>
                <button class="filter-btn" data-filter="wordsearch">Word Search</button>
                <button class="filter-btn" data-filter="missingword">Missing Word</button>
                <button class="filter-btn" data-filter="groupsort">Group Sort</button>
            </div>
        </div>
        
        <?php if (count($publisherActivities) > 0): ?>
            <div class="activity-grid" id="activityGrid"></div>
        <?php else: ?>
            <p>No activities yet. <a href="create_activity.php">Create your first activity!</a></p>
        <?php endif; ?>
    </div>
    
    <script src="../js/auth.js"></script>
    <script>
        var allActivities = <?php echo json_encode($publisherActivities); ?>;
        var currentFilter = 'all';
        
        function renderActivities() {
            var grid = document.getElementById('activityGrid');
            if (!grid) return;
            grid.innerHTML = '';
            
            var filteredActivities = allActivities.filter(function(activity) {
                if (currentFilter === 'all') return true;
                return activity.type === currentFilter;
            });
            
            if (filteredActivities.length === 0) {
                grid.innerHTML = '<p>No activities found for this filter.</p>';
                return;
            }
            
            for (var i = 0; i < filteredActivities.length; i++) {
                var activity = filteredActivities[i];
                var card = document.createElement('div');
                card.className = 'activity-card';
                card.setAttribute('data-type', activity.type);
                card.setAttribute('data-id', activity.id);
                card.onclick = function() {
                    viewActivity(this.getAttribute('data-id'));
                };
                
                card.innerHTML = 
                    '<h3>' + escapeHtml(activity.title) + '</h3>' +
                    '<p><strong>Type:</strong> ' + escapeHtml(activity.type) + '</p>' +
                    '<p><strong>Created:</strong> ' + escapeHtml(activity.created_date) + '</p>' +
                    '<span class="activity-type ' + activity.type + '">' + escapeHtml(activity.type) + '</span>';
                
                grid.appendChild(card);
            }
        }
        
        function escapeHtml(text) {
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        function logout() {
            auth.logout(function(response) {
                if (response.success) {
                    window.location.href = '../index.php';
                }
            });
        }
        
        function viewActivity(id) {
            window.location.href = 'view_activity.php?id=' + id;
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            renderActivities();
            
            var filterButtons = document.querySelectorAll('.filter-btn');
            for (var i = 0; i < filterButtons.length; i++) {
                filterButtons[i].addEventListener('click', function() {
                    for (var j = 0; j < filterButtons.length; j++) {
                        filterButtons[j].classList.remove('active');
                    }
                    this.classList.add('active');
                    currentFilter = this.getAttribute('data-filter');
                    renderActivities();
                });
            }
        });
    </script>
</body>
</html>