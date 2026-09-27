<?php
session_start();

if (!isset($_SESSION['username']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'publisher')) {
    header('Location: ../index.php');
    exit;
}

require_once '../api/file_utils.php';

if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

$activityId = $_GET['id'];

$filePath = findActivityFile($activityId, '../admin/activities/');
$activity = null;

if ($filePath !== null) {
    $activity = readJSONFile($filePath);
}

if ($activity === null) {
    echo 'Activity not found.';
    exit;
}

$canEdit = true;
$currentUser = $_SESSION['username'];
$userRole = $_SESSION['role'];

if ($userRole === 'publisher') {
    if (!isset($activity['created_by']) || $activity['created_by'] !== $currentUser) {
        $canEdit = false;
    }
}

$editPage = '';
switch ($activity['type']) {
    case 'quiz': $editPage = 'create_quiz.php?id=' . $activityId; break;
    case 'match': $editPage = 'create_match.php?id=' . $activityId; break;
    case 'wheel': $editPage = 'create_wheel.php?id=' . $activityId; break;
    case 'crossword': $editPage = 'create_crossword.php?id=' . $activityId; break;
    case 'wordsearch': $editPage = 'create_wordsearch.php?id=' . $activityId; break;
    case 'missingword': $editPage = 'create_missingword.php?id=' . $activityId; break;
    case 'groupsort': $editPage = 'create_groupsort.php?id=' . $activityId; break;
    default: $editPage = 'create_activity.php';
}

$playPage = '';
switch ($activity['type']) {
    case 'quiz': $playPage = '../games/quiz/quiz.html?id=' . $activityId; break;
    case 'match': $playPage = '../games/match/match.html?id=' . $activityId; break;
    case 'wheel': $playPage = '../games/wheel/wheel.html?id=' . $activityId; break;
    case 'crossword': $playPage = '../games/crossword/crossword.html?id=' . $activityId; break;
    case 'wordsearch': $playPage = '../games/wordsearch/wordsearch.html?id=' . $activityId; break;
    case 'missingword': $playPage = '../games/missingword/missingword.html?id=' . $activityId; break;
    case 'groupsort': $playPage = '../games/groupsort/groupsort.html?id=' . $activityId; break;
    default: $playPage = '#';
}

$results = readJSONFile('../student/results.json');
$activityResults = array();
if (isset($results[$activityId])) {
    $activityResults = $results[$activityId];
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>View Activity - Wordboard</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .activity-details {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            margin: 20px auto;
        }
        .activity-info { margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #eee; }
        .activity-info h2 { margin-top: 0; color: #667eea; }
        .activity-meta { color: #666; margin: 10px 0; }
        .activity-type {
            display: inline-block;
            padding: 5px 15px;
            background: #f0f0f0;
            border-radius: 15px;
            font-size: 14px;
            color: #666;
            margin-top: 10px;
        }
        .action-buttons { display: flex; gap: 15px; margin-bottom: 30px; }
        .results-section { background: #f9f9f9; padding: 20px; border-radius: 8px; }
        .results-table { width: 100%; border-collapse: collapse; }
        .results-table th, .results-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .results-table th { background: #667eea; color: white; font-weight: bold; }
        .results-table tr:hover { background: #f8f9ff; }
        .delete-btn { background: #dc3545; }
        .delete-btn:hover { background: #c82333; }
    </style>
</head>
<body>
    <div class="container">
        <div class="activity-details">
            <div class="nav">
                <a href="dashboard.php">Back to Dashboard</a>
            </div>
            
            <div class="activity-info">
                <h2><?php echo htmlspecialchars($activity['title']); ?></h2>
                <div class="activity-meta">
                    <strong>Type:</strong> <?php echo htmlspecialchars($activity['type']); ?><br>
                    <strong>Created:</strong> <?php echo htmlspecialchars($activity['created_date']); ?><br>
                    <?php if (isset($activity['created_by'])): ?>
                        <strong>Created By:</strong> <?php echo htmlspecialchars($activity['created_by']); ?><br>
                    <?php endif; ?>
                    <strong>ID:</strong> <?php echo htmlspecialchars($activity['id']); ?>
                </div>
                <span class="activity-type"><?php echo htmlspecialchars($activity['type']); ?></span>
            </div>
            
            <div class="action-buttons">
                <a href="<?php echo $playPage; ?>" class="button" target="_blank">Play / Preview</a>
                <?php if ($canEdit): ?>
                    <a href="<?php echo $editPage; ?>" class="button">Edit</a>
                    <button class="delete-btn" onclick="deleteActivity()">Delete</button>
                <?php endif; ?>
            </div>
            
            <div class="results-section">
                <h3>Student Results</h3>
                <?php if (count($activityResults) > 0): ?>
                    <table class="results-table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Score</th>
                                <th>Completed Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activityResults as $username => $result): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($username); ?></td>
                                    <td style="font-weight: bold; color: #667eea;"><?php echo htmlspecialchars($result['score']); ?>%</td>
                                    <td><?php echo htmlspecialchars($result['completed_date']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No students have completed this activity yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        function deleteActivity() {
            if (confirm('Are you sure you want to delete this activity?')) {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', '../api/delete_activity.php', true);
                xhr.setRequestHeader('Content-Type', 'application/json');
                
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        var response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            alert('Activity deleted successfully.');
                            window.location.href = 'dashboard.php';
                        } else {
                            alert('Error: ' + response.message);
                        }
                    }
                };
                
                xhr.send(JSON.stringify({ id: '<?php echo $activityId; ?>' }));
            }
        }
    </script>
</body>
</html>