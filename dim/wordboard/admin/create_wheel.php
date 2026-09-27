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
    <title><?php echo $isEditing ? 'Edit Wheel Activity - Wordboard' : 'Create Wheel Activity - Wordboard'; ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .segment-form {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            border-left: 4px solid #667eea;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .segment-number {
            font-weight: bold;
            color: #667eea;
            min-width: 30px;
        }
        
        .segment-input {
            flex: 1;
        }
        
        .remove-btn {
            background: #dc3545;
            padding: 5px 15px;
            font-size: 14px;
        }
        
        .remove-btn:hover {
            background: #c82333;
        }
        
        .add-segment-btn {
            background: #28a745;
            margin-bottom: 20px;
        }
        
        .add-segment-btn:hover {
            background: #218838;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?php echo $isEditing ? 'Edit Random Wheel Activity' : 'Create Random Wheel Activity'; ?></h1>
            <div class="header-actions">
                <a href="create_activity.php" class="button">Back to Activity Types</a>
                <a href="dashboard.php" class="button">Back to Dashboard</a>
            </div>
        </div>
        
        <form id="wheelForm">
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
            
            <h2>Wheel Segments</h2>
            <div id="segmentsContainer"></div>
            
            <button type="button" class="add-segment-btn" onclick="addSegment()">Add Segment</button>
            
            <div class="form-section">
                <button type="submit">Save Activity</button>
            </div>
        </form>
    </div>
    
    <script>
        var segmentCount = 0;
        var isEditing = <?php echo $isEditing ? 'true' : 'false'; ?>;
        var editingId = '<?php echo $isEditing ? htmlspecialchars($editingActivity['id']) : ''; ?>';
        var editingData = <?php echo $isEditing ? json_encode($editingActivity['data']) : '{}'; ?>;
        var editingTags = <?php echo $isEditing ? json_encode(isset($editingActivity['tags']) ? $editingActivity['tags'] : array()) : '[]'; ?>;
        
        document.addEventListener('DOMContentLoaded', function() {
            if (isEditing && editingTags.length > 0) {
                document.getElementById('tags').value = editingTags.join(', ');
            }
        });
        
        function addSegment(segmentText) {
            segmentCount++;
            
            var container = document.getElementById('segmentsContainer');
            var segmentDiv = document.createElement('div');
            segmentDiv.className = 'segment-form';
            segmentDiv.id = 'segment_' + segmentCount;
            
            var textValue = segmentText ? segmentText : '';
            
            segmentDiv.innerHTML = 
                '<span class="segment-number">' + segmentCount + '</span>' +
                '<input type="text" class="segment-input" required placeholder="Enter segment text" value="' + textValue + '">' +
                '<button type="button" class="remove-btn" onclick="removeSegment(' + segmentCount + ')">Remove</button>';
            
            container.appendChild(segmentDiv);
        }
        
        function removeSegment(id) {
            var segmentDiv = document.getElementById('segment_' + id);
            if (segmentDiv) {
                segmentDiv.parentNode.removeChild(segmentDiv);
            }
        }
        
        var currentUsername = <?php echo json_encode(isset($_SESSION['username']) ? $_SESSION['username'] : 'admin'); ?>;
        
        document.getElementById('wheelForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            var title = document.getElementById('title').value;
            var segmentForms = document.querySelectorAll('.segment-form');
            
            if (segmentForms.length < 2) {
                alert('Please add at least 2 segments.');
                return;
            }
            
            var segments = [];
            
            for (var i = 0; i < segmentForms.length; i++) {
                var input = segmentForms[i].querySelector('.segment-input');
                segments.push(input.value);
            }
            
            var tagsInput = document.getElementById('tags').value;
            var tags = tagsInput ? tagsInput.split(',').map(function(t) { return t.trim(); }).filter(function(t) { return t.length > 0; }) : [];
            
            var activityData = {
                title: title,
                type: 'wheel',
                tags: tags,
                created_by: currentUsername,
                created_date: new Date().toISOString().split('T')[0],
                data: {
                    segments: segments
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
                    'templates/wheel/templates.json',
                    'templates/greek/wheel/templates.json',
                    'templates/computer/wheel/templates.json'
                ];
                
                var fileIndex = 0;
                var foundTemplate = null;
                
                function tryNextFile() {
                    if (fileIndex >= templateFiles.length) {
                        for (var i = 0; i < 6; i++) {
                            addSegment();
                        }
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
                                
                                if (foundTemplate && foundTemplate.segments) {
                                    document.getElementById('title').value = foundTemplate.name;
                                    for (var j = 0; j < foundTemplate.segments.length; j++) {
                                        addSegment(foundTemplate.segments[j]);
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
            } else if (isEditing && editingData.segments) {
                for (var i = 0; i < editingData.segments.length; i++) {
                    addSegment(editingData.segments[i]);
                }
            } else {
                for (var i = 0; i < 6; i++) {
                    addSegment();
                }
            }
        }
        
        initPage();
    </script>
</body>
</html>
