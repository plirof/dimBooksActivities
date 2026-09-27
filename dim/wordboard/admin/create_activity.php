<?php
session_start();

if (!isset($_SESSION['username']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'publisher')) {
    header('Location: ../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Create Activity - Wordboard</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .activity-types {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .type-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            border: 3px solid transparent;
        }
        
        .type-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            border-color: #667eea;
        }
        
        .type-card h3 {
            margin-top: 0;
            color: #667eea;
        }
        
        .type-card p {
            color: #666;
            margin-bottom: 0;
        }
        
        .template-browser {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .template-browser h3 {
            margin-top: 0;
            color: #667eea;
            margin-bottom: 15px;
        }
        
        .template-section {
            margin-bottom: 20px;
        }
        
        .template-section h4 {
            margin: 0 0 10px 0;
            color: #333;
        }
        
        .template-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .template-btn {
            padding: 10px 15px;
            background: #f0f0f0;
            border: 2px solid #ddd;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            transition: all 0.2s;
        }
        
        .template-btn:hover {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        .template-btn.selected {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        .template-description {
            color: #666;
            font-size: 13px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Create Activity</h1>
            <div class="header-actions">
                <a href="dashboard.php" class="button">Back to Dashboard</a>
            </div>
        </div>
        
        <h2>Choose Activity Type</h2>
        
        <div class="template-browser">
            <h3>📋 Quick Start with Templates</h3>
            <p style="color: #666; margin-bottom: 20px;">Select a template below to automatically fill in activity content, or choose "Create Empty" to start from scratch.</p>
            
            <div class="template-section">
                <h4>Quiz Templates</h4>
                <div class="template-list" id="quizTemplates"></div>
            </div>
            
            <div class="template-section">
                <h4>Word Match Templates</h4>
                <div class="template-list" id="matchTemplates"></div>
            </div>
            
            <div class="template-section">
                <h4>Wheel Templates</h4>
                <div class="template-list" id="wheelTemplates"></div>
            </div>
            
            <div class="template-section">
                <h4>Word Search Templates</h4>
                <div class="template-list" id="wordsearchTemplates"></div>
            </div>
            
            <div class="template-section">
                <h4>Crossword Templates</h4>
                <div class="template-list" id="crosswordTemplates"></div>
            </div>
            
            <div class="template-section">
                <h4>Missing Word Templates</h4>
                <div class="template-list" id="missingwordTemplates"></div>
            </div>
            
            <div class="template-section">
                <h4>Group Sort Templates</h4>
                <div class="template-list" id="groupsortTemplates"></div>
            </div>
            
            <div class="template-section" style="border-top: 2px dashed #667eea; padding-top: 20px; margin-top: 20px;">
                <h4 style="color: #667eea;">🇬🇷 Greek Language Templates</h4>
                <div class="template-section">
                    <h4>Quiz</h4>
                    <div class="template-list" id="greekQuizTemplates"></div>
                </div>
                <div class="template-section">
                    <h4>Word Match</h4>
                    <div class="template-list" id="greekMatchTemplates"></div>
                </div>
                <div class="template-section">
                    <h4>Wheel</h4>
                    <div class="template-list" id="greekWheelTemplates"></div>
                </div>
                <div class="template-section">
                    <h4>Word Search</h4>
                    <div class="template-list" id="greekWordsearchTemplates"></div>
                </div>
                <div class="template-section">
                    <h4>Crossword</h4>
                    <div class="template-list" id="greekCrosswordTemplates"></div>
                </div>
                <div class="template-section">
                    <h4>Missing Word</h4>
                    <div class="template-list" id="greekMissingwordTemplates"></div>
                </div>
                <div class="template-section">
                    <h4>Group Sort</h4>
                    <div class="template-list" id="greekGroupsortTemplates"></div>
                </div>
            </div>
            
            <div class="template-section" style="border-top: 2px dashed #28a745; padding-top: 20px; margin-top: 20px;">
                <h4 style="color: #28a745;">💻 Computer Templates (Greek)</h4>
                <div class="template-section">
                    <h4>Quiz</h4>
                    <div class="template-list" id="computerQuizTemplates"></div>
                </div>
                <div class="template-section">
                    <h4>Word Match</h4>
                    <div class="template-list" id="computerMatchTemplates"></div>
                </div>
                <div class="template-section">
                    <h4>Wheel</h4>
                    <div class="template-list" id="computerWheelTemplates"></div>
                </div>
                <div class="template-section">
                    <h4>Word Search</h4>
                    <div class="template-list" id="computerWordsearchTemplates"></div>
                </div>
                <div class="template-section">
                    <h4>Crossword</h4>
                    <div class="template-list" id="computerCrosswordTemplates"></div>
                </div>
                <div class="template-section">
                    <h4>Missing Word</h4>
                    <div class="template-list" id="computerMissingwordTemplates"></div>
                </div>
                <div class="template-section">
                    <h4>Group Sort</h4>
                    <div class="template-list" id="computerGroupsortTemplates"></div>
                </div>
            </div>
        </div>
        
        <h2 style="margin-bottom: 20px;">Create Empty Activity</h2>
        
        <div class="activity-types">
            <div class="type-card" onclick="window.location.href='create_quiz.php'">
                <h3>Quiz</h3>
                <p>Multiple choice questions</p>
            </div>
            <div class="type-card" onclick="window.location.href='create_match.php'">
                <h3>Word Match</h3>
                <p>Match pairs of items</p>
            </div>
            <div class="type-card" onclick="window.location.href='create_wheel.php'">
                <h3>Random Wheel</h3>
                <p>Spin the wheel</p>
            </div>
            <div class="type-card" onclick="window.location.href='create_crossword.php'">
                <h3>Crossword</h3>
                <p>Fill in the grid</p>
            </div>
            <div class="type-card" onclick="window.location.href='create_wordsearch.php'">
                <h3>Word Search</h3>
                <p>Find hidden words</p>
            </div>
            <div class="type-card" onclick="window.location.href='create_missingword.php'">
                <h3>Missing Word</h3>
                <p>Fill in the blanks</p>
            </div>
            <div class="type-card" onclick="window.location.href='create_groupsort.php'">
                <h3>Group Sort</h3>
                <p>Categorize items</p>
            </div>
        </div>
        
        <script>
            var selectedTemplate = null;
            var templatesData = {};
            
            // Map file paths to container IDs
            var categoryMap = {
                'templates/quiz/templates.json': 'quizTemplates',
                'templates/match/templates.json': 'matchTemplates',
                'templates/wheel/templates.json': 'wheelTemplates',
                'templates/wordsearch/templates.json': 'wordsearchTemplates',
                'templates/crossword/templates.json': 'crosswordTemplates',
                'templates/missingword/templates.json': 'missingwordTemplates',
                'templates/groupsort/templates.json': 'groupsortTemplates',
                // Greek
                'templates/greek/quiz/templates.json': 'greekQuizTemplates',
                'templates/greek/match/templates.json': 'greekMatchTemplates',
                'templates/greek/wheel/templates.json': 'greekWheelTemplates',
                'templates/greek/wordsearch/templates.json': 'greekWordsearchTemplates',
                'templates/greek/crossword/templates.json': 'greekCrosswordTemplates',
                'templates/greek/missingword/templates.json': 'greekMissingwordTemplates',
                'templates/greek/groupsort/templates.json': 'greekGroupsortTemplates',
                // Computer
                'templates/computer/quiz/templates.json': 'computerQuizTemplates',
                'templates/computer/match/templates.json': 'computerMatchTemplates',
                'templates/computer/wheel/templates.json': 'computerWheelTemplates',
                'templates/computer/wordsearch/templates.json': 'computerWordsearchTemplates',
                'templates/computer/crossword/templates.json': 'computerCrosswordTemplates',
                'templates/computer/missingword/templates.json': 'computerMissingwordTemplates',
                'templates/computer/groupsort/templates.json': 'computerGroupsortTemplates'
            };
            
            function loadTemplates() {
                var templateFiles = [
                    'templates/quiz/templates.json',
                    'templates/match/templates.json',
                    'templates/wheel/templates.json',
                    'templates/wordsearch/templates.json',
                    'templates/crossword/templates.json',
                    'templates/missingword/templates.json',
                    'templates/groupsort/templates.json',
                    // Greek templates
                    'templates/greek/quiz/templates.json',
                    'templates/greek/match/templates.json',
                    'templates/greek/wheel/templates.json',
                    'templates/greek/wordsearch/templates.json',
                    'templates/greek/crossword/templates.json',
                    'templates/greek/missingword/templates.json',
                    'templates/greek/groupsort/templates.json',
                    // Computer templates
                    'templates/computer/quiz/templates.json',
                    'templates/computer/match/templates.json',
                    'templates/computer/wheel/templates.json',
                    'templates/computer/wordsearch/templates.json',
                    'templates/computer/crossword/templates.json',
                    'templates/computer/missingword/templates.json',
                    'templates/computer/groupsort/templates.json'
                ];
                
                var loadedCount = 0;
                
                templateFiles.forEach(function(file) {
                    var xhr = new XMLHttpRequest();
                    xhr.open('GET', file, true);
                    
                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            try {
                                var data = JSON.parse(xhr.responseText);
                                templatesData[file] = data.templates || [];
                                displayTemplates(file);
                                loadedCount++;
                            } catch (e) {
                                console.error('Error parsing template file:', file, e);
                            }
                        }
                    };
                    
                    xhr.onerror = function() {
                        console.error('Failed to load template file:', file);
                    };
                    
                    xhr.send();
                });
            }
            
            function displayTemplates(filePath) {
                var containerId = categoryMap[filePath];
                var container = document.getElementById(containerId);
                
                if (!container || !templatesData[filePath]) return;
                
                var templates = templatesData[filePath];
                var html = '';
                
                templates.forEach(function(template) {
                    // Determine the target page based on file path
                    var targetPage = 'quiz';
                    if (filePath.includes('match')) targetPage = 'match';
                    else if (filePath.includes('wheel')) targetPage = 'wheel';
                    else if (filePath.includes('wordsearch')) targetPage = 'wordsearch';
                    else if (filePath.includes('crossword')) targetPage = 'crossword';
                    else if (filePath.includes('missingword')) targetPage = 'missingword';
                    else if (filePath.includes('groupsort')) targetPage = 'groupsort';
                    
                    html += '<button class="template-btn" onclick="selectTemplate(\'' + filePath + '\', \'' + template.id + '\', \'' + targetPage + '\')">';
                    html += template.name;
                    html += '</button>';
                });
                
                container.innerHTML = html;
            }
            
            function selectTemplate(filePath, templateId, targetPage) {
                // Clear previous selection
                var allBtns = document.querySelectorAll('.template-btn');
                allBtns.forEach(function(btn) {
                    btn.classList.remove('selected');
                });
                
                // Find and select the clicked button
                event.target.classList.add('selected');
                
                selectedTemplate = { filePath: filePath, id: templateId };
                
                // Redirect to the appropriate creation page with template
                window.location.href = 'create_' + targetPage + '.php?template=' + templateId;
            }
            
            function getUrlParameter(name) {
                var urlParams = new URLSearchParams(window.location.search);
                return urlParams.get(name);
            }
            
            // Load templates on page load
            window.onload = loadTemplates;
        </script>
