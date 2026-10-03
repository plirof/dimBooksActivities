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
    <title><?php echo $isEditing ? 'Edit Word Search Activity - Wordboard' : 'Create Word Search Activity - Wordboard'; ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .word-form {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            border-left: 4px solid #667eea;
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .word-number {
            font-weight: bold;
            color: #667eea;
            min-width: 30px;
        }
        
        .word-input {
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
        
        .add-word-btn {
            background: #28a745;
            margin-bottom: 20px;
        }
        
        .add-word-btn:hover {
            background: #218838;
        }
        
        .grid-options {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?php echo $isEditing ? 'Edit Word Search Activity' : 'Create Word Search Activity'; ?></h1>
            <div class="header-actions">
                <a href="create_activity.php" class="button">Back to Activity Types</a>
                <a href="dashboard.php" class="button">Back to Dashboard</a>
            </div>
        </div>
        
        <form id="wordsearchForm">
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
            
            <h2>Words to Hide</h2>
            <div id="wordsContainer"></div>
            
            <button type="button" class="add-word-btn" onclick="addWord()">Add Word</button>
            
            <div class="grid-options">
                <h3>Grid Options</h3>
                <div class="form-group">
                    <label for="gridSize">Grid Size (10-20):</label>
                    <input type="number" id="gridSize" value="12" min="10" max="20">
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" id="useGreekAlphabet" <?php echo ($isEditing && isset($editingActivity['data']['useGreekAlphabet']) && !$editingActivity['data']['useGreekAlphabet']) ? '' : 'checked'; ?>>
                        Use Greek Alphabet for random letters
                    </label>
                </div>
                <p>Enter words above. The system will automatically place them in the grid and fill remaining spaces with random letters.</p>
            </div>
            
            <div class="form-section">
                <button type="submit">Save Activity</button>
            </div>
        </form>
    </div>
    
    <script>
        var wordCount = 0;
        
        var isEditing = <?php echo $isEditing ? 'true' : 'false'; ?>;
        var editingId = '<?php echo $isEditing ? htmlspecialchars($editingActivity['id']) : ''; ?>';
        var editingData = <?php echo $isEditing ? json_encode($editingActivity['data'], JSON_UNESCAPED_UNICODE) : '{}'; ?>;
        var editingTags = <?php echo $isEditing ? json_encode(isset($editingActivity['tags']) ? $editingActivity['tags'] : array(), JSON_UNESCAPED_UNICODE) : '[]'; ?>;
        
        document.addEventListener('DOMContentLoaded', function() {
            if (isEditing && editingTags.length > 0) {
                document.getElementById('tags').value = editingTags.join(', ');
            }
        });
        
        function addWord(value) {
            wordCount++;
            
            var container = document.getElementById('wordsContainer');
            var wordDiv = document.createElement('div');
            wordDiv.className = 'word-form';
            wordDiv.id = 'word_' + wordCount;
            
            var inputValue = value ? 'value="' + value + '"' : '';
            wordDiv.innerHTML = 
                '<span class="word-number">' + wordCount + '</span>' +
                '<input type="text" class="word-input" required placeholder="Enter word to hide" ' + inputValue + '>' +
                '<button type="button" class="remove-btn" onclick="removeWord(' + wordCount + ')">Remove</button>';
            
            container.appendChild(wordDiv);
        }
        
        function removeWord(id) {
            var wordDiv = document.getElementById('word_' + id);
            if (wordDiv) {
                wordDiv.parentNode.removeChild(wordDiv);
            }
        }
        
        var currentUsername = <?php echo json_encode(isset($_SESSION['username']) ? $_SESSION['username'] : 'admin'); ?>;
        
        document.getElementById('wordsearchForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            var title = document.getElementById('title').value;
            var gridSize = parseInt(document.getElementById('gridSize').value);
            var wordForms = document.querySelectorAll('.word-form');
            
            if (wordForms.length < 3) {
                alert('Please add at least 3 words.');
                return;
            }
            
            var words = [];
            for (var i = 0; i < wordForms.length; i++) {
                var input = wordForms[i].querySelector('.word-input');
                words.push(input.value.toUpperCase());
            }
            
            var useGreekAlphabet = document.getElementById('useGreekAlphabet').checked;
            
            var tagsInput = document.getElementById('tags').value;
            var tags = tagsInput ? tagsInput.split(',').map(function(t) { return t.trim(); }).filter(function(t) { return t.length > 0; }) : [];
            var grid = generateGrid(gridSize, words, useGreekAlphabet);
            
            var activityData = {
                title: title,
                type: 'wordsearch',
                tags: tags,
                created_by: currentUsername,
                created_date: new Date().toISOString().split('T')[0],
                data: {
                    words: words,
                    gridSize: parseInt(gridSize),
                    useGreekAlphabet: useGreekAlphabet,
                    grid: grid
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
        
        function generateGrid(size, words, useGreekAlphabet) {
            var grid = [];
            for (var i = 0; i < size; i++) {
                grid[i] = [];
                for (var j = 0; j < size; j++) {
                    grid[i][j] = 'A';
                }
            }
            
            var directions = [
                [0, 1], [1, 0], [0, -1], [-1, 0], 
                [1, 1], [1, -1], [-1, 1], [-1, -1]
            ];
            
            for (var w = 0; w < words.length; w++) {
                var word = words[w];
                var placed = false;
                var attempts = 0;
                
                while (!placed && attempts < 100) {
                    attempts++;
                    
                    var dir = directions[Math.floor(Math.random() * directions.length)];
                    var startRow = Math.floor(Math.random() * size);
                    var startCol = Math.floor(Math.random() * size);
                    
                    if (canPlaceWord(grid, word, startRow, startCol, dir, size)) {
                        placeWord(grid, word, startRow, startCol, dir);
                        placed = true;
                    }
                }
            }
            
            var letters;
            if (useGreekAlphabet) {
                // Greek alphabet: Α Β Γ Δ Ε Ζ Η Θ Ι Κ Λ Μ Ν Ξ Ο Π Ρ Σ Τ Υ Φ Χ Ψ Ω
                letters = 'ΑΒΓΔΕΖΗΘΙΚΛΜΝΞΟΠΡΣΤΥΦΧΨΩ';
            } else {
                letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            }
            
            for (var i = 0; i < size; i++) {
                for (var j = 0; j < size; j++) {
                    if (grid[i][j] === 'A') {
                        grid[i][j] = letters[Math.floor(Math.random() * letters.length)];
                    }
                }
            }
            
            return grid;
        }
        
        function canPlaceWord(grid, word, row, col, dir, size) {
            for (var i = 0; i < word.length; i++) {
                var r = row + dir[0] * i;
                var c = col + dir[1] * i;
                
                if (r < 0 || r >= size || c < 0 || c >= size) {
                    return false;
                }
                
                if (grid[r][c] !== 'A' && grid[r][c] !== word[i]) {
                    return false;
                }
            }
            return true;
        }
        
        function placeWord(grid, word, row, col, dir) {
            for (var i = 0; i < word.length; i++) {
                grid[row + dir[0] * i][col + dir[1] * i] = word[i];
            }
        }
        
        // Initialize page
        function initPage() {
            var urlParams = new URLSearchParams(window.location.search);
            var templateId = urlParams.get('template');
            
            if (templateId) {
                var templateFiles = [
                    'templates/wordsearch/templates.json',
                    'templates/greek/wordsearch/templates.json',
                    'templates/computer/wordsearch/templates.json'
                ];
                
                var fileIndex = 0;
                var foundTemplate = null;
                
                function tryNextFile() {
                    if (fileIndex >= templateFiles.length) {
                        addWord('');
                        addWord('');
                        addWord('');
                        addWord('');
                        addWord('');
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
                                
                                if (foundTemplate && foundTemplate.words) {
                                    document.getElementById('title').value = foundTemplate.name;
                                    for (var j = 0; j < foundTemplate.words.length; j++) {
                                        addWord(foundTemplate.words[j]);
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
            } else if (isEditing && editingData.words) {
                for (var i = 0; i < editingData.words.length; i++) {
                    addWord(editingData.words[i]);
                }
            } else {
                addWord('');
                addWord('');
                addWord('');
                addWord('');
                addWord('');
            }
        }
        
        initPage();
    </script>
</body>
</html>
