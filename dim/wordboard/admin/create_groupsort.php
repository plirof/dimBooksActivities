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
    <title><?php echo $isEditing ? 'Edit Group Sort Activity - Wordboard' : 'Create Group Sort Activity - Wordboard'; ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .group-form {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
        }
        
        .group-form h3 {
            margin-top: 0;
            color: #667eea;
        }
        
        .items-inputs {
            margin-top: 15px;
        }
        
        .item-input {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }
        
        .item-number {
            color: #666;
            min-width: 30px;
        }
        
        .remove-item-btn {
            background: #dc3545;
            padding: 3px 10px;
            font-size: 12px;
        }
        
        .remove-group-btn {
            background: #dc3545;
            padding: 5px 15px;
            font-size: 14px;
            margin-top: 10px;
        }
        
        .add-group-btn {
            background: #28a745;
            margin-bottom: 20px;
        }
        
        .add-group-btn:hover {
            background: #218838;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?php echo $isEditing ? 'Edit Group Sort Activity' : 'Create Group Sort Activity'; ?></h1>
            <div class="header-actions">
                <a href="create_activity.php" class="button">Back to Activity Types</a>
                <a href="dashboard.php" class="button">Back to Dashboard</a>
            </div>
        </div>
        
        <form id="groupsortForm">
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
            
            <h2>Groups</h2>
            <div id="groupsContainer"></div>
            
            <button type="button" class="add-group-btn" onclick="addGroup()">Add Group</button>
            
            <div class="form-section">
                <button type="submit">Save Activity</button>
            </div>
        </form>
    </div>
    
    <script>
        var groupCount = 0;
        
        var isEditing = <?php echo $isEditing ? 'true' : 'false'; ?>;
        var editingId = '<?php echo $isEditing ? htmlspecialchars($editingActivity['id']) : ''; ?>';
        var editingData = <?php echo $isEditing ? json_encode($editingActivity['data']) : '{}'; ?>;
        var editingTags = <?php echo $isEditing ? json_encode(isset($editingActivity['tags']) ? $editingActivity['tags'] : array()) : '[]'; ?>;
        
        document.addEventListener('DOMContentLoaded', function() {
            if (isEditing && editingTags.length > 0) {
                document.getElementById('tags').value = editingTags.join(', ');
            }
        });
        
        function addGroup(groupData) {
            groupCount++;
            
            var container = document.getElementById('groupsContainer');
            var groupDiv = document.createElement('div');
            groupDiv.className = 'group-form';
            groupDiv.id = 'group_' + groupCount;
            
            var groupNameValue = groupData ? groupData.name : '';
            var groupItems = groupData ? groupData.items : [];
            
            groupDiv.innerHTML = 
                '<h3>Group ' + groupCount + '</h3>' +
                '<div class="form-group">' +
                    '<label>Group Name:</label>' +
                    '<input type="text" class="group-name" required placeholder="e.g., Fruits" value="' + groupNameValue + '">' +
                '</div>' +
                '<div class="items-inputs">' +
                    '<h4>Items:</h4>' +
                    '<div class="item-list"></div>' +
                    '<button type="button" onclick="addItem(this.parentNode)">Add Item</button>' +
                '</div>' +
                '<button type="button" class="remove-group-btn" onclick="removeGroup(' + groupCount + ')">Remove Group</button>';
            
            container.appendChild(groupDiv);
            
            // Add items if groupData provided
            var itemList = groupDiv.querySelector('.item-list');
            if (groupItems && groupItems.length > 0) {
                for (var i = 0; i < groupItems.length; i++) {
                    addItem(itemList.parentNode, groupItems[i]);
                }
            } else {
                // Add default empty items
                addItem(itemList.parentNode);
                addItem(itemList.parentNode);
            }
        }
        
        function addItem(container, itemValue) {
            var itemsInputs = container.querySelector('.items-inputs');
            var items = itemsInputs.querySelectorAll('.item-input');
            var itemNumber = items.length + 1;
            
            var textValue = itemValue || '';
            
            var itemDiv = document.createElement('div');
            itemDiv.className = 'item-input';
            itemDiv.innerHTML = 
                '<span class="item-number">' + itemNumber + '.</span>' +
                '<input type="text" class="item-text" required placeholder="Enter item" value="' + textValue + '">' +
                '<button type="button" class="remove-item-btn" onclick="removeItem(this)">×</button>';
            
            itemsInputs.insertBefore(itemDiv, itemsInputs.lastElementChild);
            renumberItems(itemsInputs);
        }
        
        function removeItem(btn) {
            var itemDiv = btn.parentNode;
            itemDiv.parentNode.removeChild(itemDiv);
        }
        
        var currentUsername = <?php echo json_encode(isset($_SESSION['username']) ? $_SESSION['username'] : 'admin'); ?>;
        
        document.getElementById('groupsortForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            var title = document.getElementById('title').value;
            var groupForms = document.querySelectorAll('.group-form');
            
            if (groupForms.length < 2) {
                alert('Please add at least 2 groups.');
                return;
            }
            
            var groups = [];
            
            for (var i = 0; i < groupForms.length; i++) {
                var form = groupForms[i];
                var groupName = form.querySelector('.group-name').value;
                var itemInputs = form.querySelectorAll('.item-text');
                
                var items = [];
                for (var j = 0; j < itemInputs.length; j++) {
                    items.push(itemInputs[j].value);
                }
                
                if (items.length === 0) {
                    alert('Please add at least one item to each group.');
                    return;
                }
                
                groups.push({
                    name: groupName,
                    items: items
                });
            }
            
            var tagsInput = document.getElementById('tags').value;
            var tags = tagsInput ? tagsInput.split(',').map(function(t) { return t.trim(); }).filter(function(t) { return t.length > 0; }) : [];
            
            var activityData = {
                title: title,
                type: 'groupsort',
                tags: tags,
                created_by: currentUsername,
                created_date: new Date().toISOString().split('T')[0],
                data: {
                    groups: groups
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
                    'templates/groupsort/templates.json',
                    'templates/greek/groupsort/templates.json',
                    'templates/computer/groupsort/templates.json'
                ];
                
                var fileIndex = 0;
                var foundTemplate = null;
                
                function tryNextFile() {
                    if (fileIndex >= templateFiles.length) {
                        addGroup();
                        addGroup();
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
                                
                                if (foundTemplate && foundTemplate.groups && foundTemplate.items) {
                                    document.getElementById('title').value = foundTemplate.name;
                                    var groupsMap = {};
                                    for (var g = 0; g < foundTemplate.groups.length; g++) {
                                        groupsMap[foundTemplate.groups[g]] = [];
                                    }
                                    for (var j = 0; j < foundTemplate.items.length; j++) {
                                        var item = foundTemplate.items[j];
                                        if (groupsMap[item.group]) {
                                            groupsMap[item.group].push(item.text);
                                        }
                                    }
                                    for (var g = 0; g < foundTemplate.groups.length; g++) {
                                        var groupName = foundTemplate.groups[g];
                                        var groupData = {
                                            name: groupName,
                                            items: groupsMap[groupName] || []
                                        };
                                        addGroup(groupData);
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
            } else if (isEditing && editingData.groups) {
                for (var i = 0; i < editingData.groups.length; i++) {
                    addGroup(editingData.groups[i]);
                }
            } else {
                addGroup();
                addGroup();
            }
        }
        
        initPage();
    </script>
</body>
</html>
