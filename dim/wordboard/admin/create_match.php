<?php
session_start();

if (!isset($_SESSION['username']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'publisher')) {
    header('Location: ../index.php');
    exit;
}

require_once '../api/file_utils.php';

$isEditing = isset($_GET['id']);
$editingActivity = null;

if ($isEditing) {
    $activityId = $_GET['id'];
    $editingActivity = findActivityById($activityId, '../admin/activities/');
    
    if ($editingActivity === null) {
        header('Location: dashboard.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo $isEditing ? 'Edit Match Activity - Wordboard' : 'Create Match Activity - Wordboard'; ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .pair-form {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
        }
        
        .pair-form h3 {
            margin-top: 0;
            color: #667eea;
        }
        
        .pair-inputs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .remove-btn {
            background: #dc3545;
            padding: 5px 15px;
            font-size: 14px;
            margin-top: 10px;
        }
        
        .remove-btn:hover {
            background: #c82333;
        }
        
        .add-pair-btn {
            background: #28a745;
            margin-bottom: 20px;
        }
        
        .add-pair-btn:hover {
            background: #218838;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?php echo $isEditing ? 'Edit Word Match Activity' : 'Create Word Match Activity'; ?></h1>
            <div class="header-actions">
                <a href="create_activity.php" class="button">Back to Activity Types</a>
                <a href="dashboard.php" class="button">Back to Dashboard</a>
            </div>
        </div>
        
        <form id="matchForm">
            <div class="form-section">
                <h2>Activity Details</h2>
                
                <div class="form-group">
                    <label for="title">Title:</label>
                    <input type="text" id="title" name="title" required <?php echo $isEditing ? 'value="' . htmlspecialchars($editingActivity['title']) . '"' : ''; ?>>
                </div>
                
                <div class="form-group">
                    <label for="tags">Tags (comma separated):</label>
                    <input type="text" id="tags" name="tags" placeholder="e.g., dimA, lesson01, math">
                    <small style="color: #666;">Separate tags with commas</small>
                </div>
            </div>
            
            <h2>Word Pairs</h2>
            <div id="pairsContainer"></div>
            
            <button type="button" class="add-pair-btn" onclick="addPair()">Add Pair</button>
            
            <div class="form-section">
                <button type="submit">Save Activity</button>
            </div>
        </form>
    </div>
    
    <script>
        var pairCount = 0;
        var isEditing = <?php echo $isEditing ? 'true' : 'false'; ?>;
        var editingId = '<?php echo $isEditing ? htmlspecialchars($editingActivity['id']) : ''; ?>';
        var editingData = <?php echo $isEditing ? json_encode($editingActivity['data']) : '{}'; ?>;
        var editingTags = <?php echo $isEditing ? json_encode(isset($editingActivity['tags']) ? $editingActivity['tags'] : array()) : '[]'; ?>;
        
        document.addEventListener('DOMContentLoaded', function() {
            if (isEditing && editingTags.length > 0) {
                document.getElementById('tags').value = editingTags.join(', ');
            }
        });
        
        function addPair(pairData) {
            pairCount++;
            
            var container = document.getElementById('pairsContainer');
            var pairDiv = document.createElement('div');
            pairDiv.className = 'pair-form';
            pairDiv.id = 'pair_' + pairCount;
            
            var leftText = pairData ? pairData.left : '';
            var rightText = pairData ? pairData.right : '';
            
            pairDiv.innerHTML = 
                '<h3>Pair ' + pairCount + '</h3>' +
                '<div class="pair-inputs">' +
                    '<div class="form-group">' +
                        '<label>Left Item:</label>' +
                        '<input type="text" class="pair-left" required value="' + leftText + '">' +
                    '</div>' +
                    '<div class="form-group">' +
                        '<label>Right Item:</label>' +
                        '<input type="text" class="pair-right" required value="' + rightText + '">' +
                    '</div>' +
                '</div>' +
                '<button type="button" class="remove-btn" onclick="removePair(' + pairCount + ')">Remove Pair</button>';
            
            container.appendChild(pairDiv);
        }
        
        function removePair(id) {
            var pairDiv = document.getElementById('pair_' + id);
            if (pairDiv) {
                pairDiv.parentNode.removeChild(pairDiv);
            }
        }
        
        var currentUsername = <?php echo json_encode(isset($_SESSION['username']) ? $_SESSION['username'] : 'admin'); ?>;
        
        document.getElementById('matchForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            var title = document.getElementById('title').value;
            var pairForms = document.querySelectorAll('.pair-form');
            
            if (pairForms.length < 2) {
                alert('Please add at least 2 pairs.');
                return;
            }
            
            var pairs = [];
            
            for (var i = 0; i < pairForms.length; i++) {
                var form = pairForms[i];
                var left = form.querySelector('.pair-left').value;
                var right = form.querySelector('.pair-right').value;
                
                pairs.push({
                    left: left,
                    right: right
                });
            }
            
            var tagsInput = document.getElementById('tags').value;
            var tags = tagsInput ? tagsInput.split(',').map(function(t) { return t.trim(); }).filter(function(t) { return t.length > 0; }) : [];
            
            var activityData = {
                title: title,
                type: 'match',
                tags: tags,
                created_by: currentUsername,
                created_date: new Date().toISOString().split('T')[0],
                data: {
                    pairs: pairs
                }
            };
            
            // Include ID if editing
            if (isEditing) {
                activityData.id = editingId;
            }
            
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '../api/save_activity.php', true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            
            xhr.onload = function() {
                if (xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        alert('Activity saved successfully!');
                        var currentRole = <?php echo json_encode(isset($_SESSION['role']) ? $_SESSION['role'] : 'student'); ?>;
                        if (currentRole === 'publisher') {
                            window.location.href = 'publisher_dashboard.php';
                        } else {
                            window.location.href = 'dashboard.php';
                        }
                    } else {
                        alert('Error saving activity: ' + response.message);
                    }
                } else {
                    alert('Error saving activity.');
                }
            };
            
            xhr.send(JSON.stringify(activityData));
        });
        
        // Initialize page
        function initPage() {
            var urlParams = new URLSearchParams(window.location.search);
            var templateId = urlParams.get('template');
            
            if (templateId) {
                var templateFiles = [
                    'templates/match/templates.json',
                    'templates/greek/match/templates.json',
                    'templates/computer/match/templates.json'
                ];
                
                var fileIndex = 0;
                var foundTemplate = null;
                
                function tryNextFile() {
                    if (fileIndex >= templateFiles.length) {
                        addPair();
                        addPair();
                        return;
                    }
                    
                    var xhr = new XMLHttpRequest();
                    xhr.open('GET', templateFiles[fileIndex], true);
                    
                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            try {
                                var data = JSON.parse(xhr.responseText);
                                for (var i = 0; i < data.templates.length; i++) {
                                    if (data.templates[i].id === templateId) {
                                        foundTemplate = data.templates[i];
                                        break;
                                    }
                                }
                                
                                if (foundTemplate && foundTemplate.pairs) {
                                    document.getElementById('title').value = foundTemplate.name;
                                    for (var j = 0; j < foundTemplate.pairs.length; j++) {
                                        addPair(foundTemplate.pairs[j]);
                                    }
                                    alert('Template "' + foundTemplate.name + '" loaded! You can customize it before saving.');
                                } else {
                                    fileIndex++;
                                    tryNextFile();
                                }
                            } catch (e) {
                                console.error('Error loading template:', e);
                                fileIndex++;
                                tryNextFile();
                            }
                        } else {
                            fileIndex++;
                            tryNextFile();
                        }
                    };
                    
                    xhr.onerror = function() {
                        fileIndex++;
                        tryNextFile();
                    };
                    
                    xhr.send();
                }
                
                tryNextFile();
            } else if (isEditing && editingData.pairs) {
                for (var i = 0; i < editingData.pairs.length; i++) {
                    addPair(editingData.pairs[i]);
                }
            } else {
                addPair();
                addPair();
            }
        }
        
        initPage();
    </script>
</body>
</html>
