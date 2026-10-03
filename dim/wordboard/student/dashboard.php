<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'student') {
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

usort($activities, function($a, $b) {
    return strcmp($b['created_date'], $a['created_date']);
});

$username = $_SESSION['username'];
$results = readJSONFile('results.json');
if ($results === null) {
    $results = array();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard - Wordboard</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Student Dashboard</h1>
            <div class="header-actions">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <button onclick="logout()">Logout</button>
            </div>
        </div>
        
        <h2>Available Activities</h2>
        
        <?php if (count($activities) > 0): ?>
            <div class="activity-grid" id="activityGrid"></div>
        <?php else: ?>
            <p>No activities available yet.</p>
        <?php endif; ?>
        
        <?php if (count($results) > 0): ?>
            <h2>Your Results</h2>
            <div class="list-section">
                <ul class="user-list" id="resultsList"></ul>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="../js/auth.js"></script>
    <script>
        var allActivities = <?php echo json_encode($activities, JSON_UNESCAPED_UNICODE); ?>;
        var userResults = <?php echo json_encode($results); ?>;
        var username = <?php echo json_encode($username); ?>;
        
        function renderActivities() {
            var grid = document.getElementById('activityGrid');
            if (!grid) return;
            
            for (var i = 0; i < allActivities.length; i++) {
                var activity = allActivities[i];
                var card = document.createElement('div');
                card.className = 'activity-card';
                card.onclick = function() {
                    playActivity(this.getAttribute('data-id'));
                };
                card.setAttribute('data-id', activity.id);
                
                var result = userResults[activity.id];
                var resultHtml = '';
                if (result && result[username]) {
                    resultHtml = '<p style="color: green;"><strong>Score:</strong> ' + result[username].score + '%</p>';
                } else {
                    resultHtml = '<p style="color: #999;">Not yet completed</p>';
                }
                
                card.innerHTML = 
                    '<h3>' + escapeHtml(activity.title) + '</h3>' +
                    '<p><strong>Type:</strong> ' + escapeHtml(activity.type) + '</p>' +
                    resultHtml +
                    '<span class="activity-type ' + activity.type + '">' + escapeHtml(activity.type) + '</span>';
                
                grid.appendChild(card);
            }
        }
        
        function renderResults() {
            var list = document.getElementById('resultsList');
            if (!list) return;
            
            for (var activityId in userResults) {
                var result = userResults[activityId];
                if (result && result[username]) {
                    var activityTitle = 'Unknown';
                    for (var i = 0; i < allActivities.length; i++) {
                        if (allActivities[i].id === activityId) {
                            activityTitle = allActivities[i].title;
                            break;
                        }
                    }
                    
                    var li = document.createElement('li');
                    li.innerHTML = 
                        '<span>' + escapeHtml(activityTitle) + '</span>' +
                        '<span style="font-weight: bold; color: green;">' + result[username].score + '%</span>';
                    list.appendChild(li);
                }
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
        
        function playActivity(id) {
            window.location.href = 'play.php?id=' + id;
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            renderActivities();
            renderResults();
        });
    </script>
</body>
</html>