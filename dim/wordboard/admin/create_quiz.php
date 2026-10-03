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
    <title><?php echo $isEditing ? 'Edit Quiz Activity - Wordboard' : 'Create Quiz Activity - Wordboard'; ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .question-form {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
        }
        
        .question-form h3 {
            margin-top: 0;
            color: #667eea;
        }
        
        .option-input {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }
        
        .option-input input[type="radio"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        
        .option-input input[type="text"] {
            flex: 1;
        }
        
        .remove-btn {
            background: #dc3545;
            padding: 5px 15px;
            font-size: 14px;
            margin-left: 10px;
        }
        
        .remove-btn:hover {
            background: #c82333;
        }
        
        .add-question-btn {
            background: #28a745;
            margin-bottom: 20px;
        }
        
        .add-question-btn:hover {
            background: #218838;
        }
        
        #questionsContainer {
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?php echo $isEditing ? 'Edit Quiz Activity' : 'Create Quiz Activity'; ?></h1>
            <div class="header-actions">
                <a href="dashboard.php" class="button">Back to Dashboard</a>
            </div>
        </div>
        
        <form id="quizForm">
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
            
            <h2>Questions</h2>
            <div id="questionsContainer"></div>
            
            <button type="button" class="add-question-btn" onclick="addQuestion()">Add Question</button>
            
            <div class="form-section">
                <button type="submit">Save Activity</button>
            </div>
        </form>
    </div>
    
    <script>
        var questionCount = 0;
        var isEditing = <?php echo $isEditing ? 'true' : 'false'; ?>;
        var editingId = '<?php echo $isEditing ? htmlspecialchars($editingActivity['id']) : ''; ?>';
        var editingData = <?php echo $isEditing ? json_encode($editingActivity['data'], JSON_UNESCAPED_UNICODE) : '{}'; ?>;
        var editingTags = <?php echo $isEditing ? json_encode(isset($editingActivity['tags']) ? $editingActivity['tags'] : array(), JSON_UNESCAPED_UNICODE) : '[]'; ?>;
        
        document.addEventListener('DOMContentLoaded', function() {
            if (isEditing && editingTags.length > 0) {
                document.getElementById('tags').value = editingTags.join(', ');
            }
        });
        
        function addQuestion(questionData) {
            questionCount++;
            
            var container = document.getElementById('questionsContainer');
            var questionDiv = document.createElement('div');
            questionDiv.className = 'question-form';
            questionDiv.id = 'question_' + questionCount;
            
            var questionText = questionData ? questionData.question : '';
            var options = questionData ? questionData.options : ['', '', '', ''];
            var correct = questionData ? questionData.correct : 0;
            
            questionDiv.innerHTML = 
                '<h3>Question ' + questionCount + '</h3>' +
                '<div class="form-group">' +
                    '<label>Question Text:</label>' +
                    '<input type="text" class="question-text" required value="' + questionText + '">' +
                '</div>' +
                '<h4>Options (select the correct answer):</h4>' +
                '<div class="options-container">' +
                    '<div class="option-input">' +
                        '<input type="radio" name="correct_' + questionCount + '" value="0" required ' + (correct === 0 ? 'checked' : '') + '>' +
                        '<input type="text" class="option-text" required placeholder="Option 1" value="' + options[0] + '">' +
                    '</div>' +
                    '<div class="option-input">' +
                        '<input type="radio" name="correct_' + questionCount + '" value="1" ' + (correct === 1 ? 'checked' : '') + '>' +
                        '<input type="text" class="option-text" required placeholder="Option 2" value="' + options[1] + '">' +
                    '</div>' +
                    '<div class="option-input">' +
                        '<input type="radio" name="correct_' + questionCount + '" value="2" ' + (correct === 2 ? 'checked' : '') + '>' +
                        '<input type="text" class="option-text" required placeholder="Option 3" value="' + options[2] + '">' +
                    '</div>' +
                    '<div class="option-input">' +
                        '<input type="radio" name="correct_' + questionCount + '" value="3" ' + (correct === 3 ? 'checked' : '') + '>' +
                        '<input type="text" class="option-text" required placeholder="Option 4" value="' + options[3] + '">' +
                    '</div>' +
                '</div>' +
                '<button type="button" class="remove-btn" onclick="removeQuestion(' + questionCount + ')">Remove Question</button>';
            
            container.appendChild(questionDiv);
        }
        
        function removeQuestion(id) {
            var questionDiv = document.getElementById('question_' + id);
            if (questionDiv) {
                questionDiv.parentNode.removeChild(questionDiv);
            }
        }
        
        var currentUsername = <?php echo json_encode(isset($_SESSION['username']) ? $_SESSION['username'] : 'admin'); ?>;
        
        document.getElementById('quizForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            var title = document.getElementById('title').value;
            var questionForms = document.querySelectorAll('.question-form');
            
            if (questionForms.length === 0) {
                alert('Please add at least one question.');
                return;
            }
            
            var questions = [];
            
            for (var i = 0; i < questionForms.length; i++) {
                var form = questionForms[i];
                var questionText = form.querySelector('.question-text').value;
                var optionInputs = form.querySelectorAll('.option-text');
                var correctRadio = form.querySelector('input[type="radio"]:checked');
                
                if (!correctRadio) {
                    alert('Please select a correct answer for Question ' + (i + 1));
                    return;
                }
                
                var options = [];
                for (var j = 0; j < optionInputs.length; j++) {
                    options.push(optionInputs[j].value);
                }
                
                questions.push({
                    question: questionText,
                    options: options,
                    correct: parseInt(correctRadio.value)
                });
            }
            
            var tagsInput = document.getElementById('tags').value;
            var tags = tagsInput ? tagsInput.split(',').map(function(t) { return t.trim(); }).filter(function(t) { return t.length > 0; }) : [];
            
            var activityData = {
                title: title,
                type: 'quiz',
                tags: tags,
                created_by: currentUsername,
                created_date: new Date().toISOString().split('T')[0],
                data: {
                    questions: questions
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
                // Try to load template from multiple locations
                var templateFiles = [
                    'templates/quiz/templates.json',
                    'templates/greek/quiz/templates.json',
                    'templates/computer/quiz/templates.json'
                ];
                
                var fileIndex = 0;
                var foundTemplate = null;
                
                function tryNextFile() {
                    if (fileIndex >= templateFiles.length) {
                        // Template not found in any location
                        addQuestion();
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
                                
                                if (foundTemplate && foundTemplate.questions) {
                                    // Set title from template
                                    document.getElementById('title').value = foundTemplate.name;
                                    
                                    // Load questions from template
                                    for (var j = 0; j < foundTemplate.questions.length; j++) {
                                        addQuestion(foundTemplate.questions[j]);
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
            } else if (isEditing && editingData.questions) {
                // Load existing questions
                for (var i = 0; i < editingData.questions.length; i++) {
                    addQuestion(editingData.questions[i]);
                }
            } else {
                // Add empty question for new activity
                addQuestion();
            }
        }
        
        initPage();
    </script>
</body>
</html>
