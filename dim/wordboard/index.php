<?php
require_once 'api/file_utils.php';

$activitiesDir = 'admin/activities/';
$index = getActivityIndex($activitiesDir);

$activities = array();
foreach ($index as $id => $entry) {
    $entry['id'] = $id;
    $activities[] = $entry;
}

// URL parameters for pre-filtering (AND logic for multiple tags)
$urlType = isset($_GET['type']) ? $_GET['type'] : 'all';
$urlSearch = isset($_GET['search']) ? $_GET['search'] : '';

// Pre-filter by type
if ($urlType !== 'all') {
    $activities = array_filter($activities, function($a) use ($urlType) {
        return isset($a['type']) && $a['type'] === $urlType;
    });
}

// Pre-filter by search (AND logic for multiple tags)
if ($urlSearch) {
    $searchTags = array_map('trim', explode(',', $urlSearch));
    $searchTags = array_filter($searchTags);
    
    if (!empty($searchTags)) {
        $activities = array_filter($activities, function($a) use ($searchTags) {
            $activityTags = isset($a['tags']) && is_array($a['tags']) ? $a['tags'] : array();
            
            foreach ($searchTags as $searchTag) {
                $found = false;
                foreach ($activityTags as $actTag) {
                    if (stripos($actTag, $searchTag) !== false) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) return false;
            }
            return true;
        });
    }
}

$activities = array_values($activities);

$showClassLessonFilters = true;

$classOrder = array('dimA' => 1, 'dimB' => 2, 'dimC' => 3, 'dimD' => 4, 'dimE' => 5, 'dimST' => 6);
$typeOrder = array('quiz' => 1, 'match' => 2, 'missingword' => 3, 'wheel' => 4, 'crossword' => 5, 'wordsearch' => 6, 'groupsort' => 7);

usort($activities, function($a, $b) use ($classOrder, $typeOrder) {
    $aTags = isset($a['tags']) && is_array($a['tags']) ? $a['tags'] : array();
    $bTags = isset($b['tags']) && is_array($b['tags']) ? $b['tags'] : array();

    $aClass = ''; $bClass = '';
    $aLes = 0; $bLes = 0;
    foreach ($aTags as $tag) {
        if (isset($classOrder[$tag])) $aClass = $tag;
        if (preg_match('/^les(\d+)$/', $tag, $m)) $aLes = intval($m[1]);
    }
    foreach ($bTags as $tag) {
        if (isset($classOrder[$tag])) $bClass = $tag;
        if (preg_match('/^les(\d+)$/', $tag, $m)) $bLes = intval($m[1]);
    }

    $aClassIdx = $aClass ? $classOrder[$aClass] : 99;
    $bClassIdx = $bClass ? $classOrder[$bClass] : 99;
    if ($aClassIdx !== $bClassIdx) return $aClassIdx - $bClassIdx;

    if ($aLes !== $bLes) return $aLes - $bLes;

    $aTypeIdx = isset($typeOrder[$a['type']]) ? $typeOrder[$a['type']] : 99;
    $bTypeIdx = isset($typeOrder[$b['type']]) ? $typeOrder[$b['type']] : 99;
    return $aTypeIdx - $bTypeIdx;
});
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>WordBoard - Play Activities</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .main-container {
            display: flex;
            gap: 40px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        
        .activities-section {
            flex: 2;
        }
        
        .login-section {
            flex: 1;
            max-width: 350px;
        }
        
        .login-container {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 20px;
        }
        
        .login-container h1 {
            font-size: 1.5em;
            margin-bottom: 5px;
            color: #667eea; 
            
        }
        
        .login-container h2 {
            font-size: 1em;
            font-weight: normal;
            color: #666;
            margin-bottom: 20px;
        }
        
        .activities-header {
            margin-bottom: 20px;
        }
        
        .activities-header h1 {
            /* color: #667eea; */
            color: #66deff;
            margin-bottom: 10px;
        }
        
        .activities-header p {
            color: #666;
        }
        
        .filter-section {
            background: #f9f9f9;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .filter-row {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
        }
        
        .filter-type, .filter-search {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .filter-section h3 {
            margin: 0;
            font-size: 14px;
            color: #333;
            white-space: nowrap;
        }
        
        .filter-search input {
            padding: 6px 12px;
            border: 2px solid #667eea;
            border-radius: 15px;
            font-size: 13px;
            width: 200px;
        }
        
        .filter-search input:focus {
            outline: none;
            border-color: #5568d3;
        }
        
        .filter-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .filter-btn {
            padding: 6px 14px;
            border: 2px solid #667eea;
            background: white;
            color: #667eea;
            border-radius: 15px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .filter-btn:hover {
            background: #667eea;
            color: white;
        }
        
        .filter-btn.active {
            background: #667eea;
            color: white;
        }
        
        .activity-card {
            display: block;
            padding: 15px;
            background: white;
            border-radius: 8px;
            border-left: 4px solid #667eea;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .activity-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .card-play-area {
            text-decoration: none;
            color: inherit;
            display: block;
            cursor: pointer;
        }

        .card-play-area h3 {
            margin: 0 0 5px 0;
            color: #333;
            font-size: 16px;
        }

        .card-play-area p {
            margin: 0;
            color: #666;
            font-size: 0.85em;
        }
        
        .activity-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 15px;
        }
        
        .activity-type-tag {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
            color: white;
            text-transform: uppercase;
            cursor: pointer;
            transition: opacity 0.2s;
        }

        .activity-type-tag:hover {
            opacity: 0.8;
        }

        .activity-type-tag.active {
            outline: 3px solid #333;
        }

        .activity-type-tag.quiz { background: #667eea; }
        .activity-type-tag.match { background: #28a745; }
        .activity-type-tag.wheel { background: #ffc107; color: #333; }
        .activity-type-tag.crossword { background: #dc3545; }
        .activity-type-tag.wordsearch { background: #17a2b8; }
        .activity-type-tag.missingword { background: #6f42c1; }
        .activity-type-tag.groupsort { background: #fd7e14; }
        
        .card-tags {
            margin-top: 8px;
        }
        
        .activity-tag {
            display: inline-block;
            padding: 2px 8px;
            background: #e9ecef;
            color: #495057;
            border-radius: 10px;
            font-size: 10px;
            margin-right: 4px;
            cursor: pointer;
            transition: background 0.2s, color 0.2s;
        }

        .activity-tag:hover {
            background: #667eea;
            color: white;
        }

        .activity-tag.active {
            background: #667eea;
            color: white;
        }

        .search-row {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .clear-btn {
            padding: 6px 14px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 15px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 500;
            transition: background 0.2s;
            white-space: nowrap;
            display: none;
        }

        .clear-btn:hover {
            background: #c82333;
        }

        .clear-btn.visible {
            display: inline-block;
        }

        .filter-class {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .filter-class .filter-buttons .filter-btn {
            border-color: #28a745;
            color: #28a745;
        }

        .filter-class .filter-buttons .filter-btn:hover,
        .filter-class .filter-buttons .filter-btn.active {
            background: #28a745;
            color: white;
        }

        .filter-lesson {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .filter-lesson select {
            padding: 6px 12px;
            border: 2px solid #ffc107;
            border-radius: 15px;
            font-size: 13px;
            background: white;
            color: #333;
            cursor: pointer;
        }

        .filter-lesson select:focus {
            outline: none;
            border-color: #e0a800;
        }
        
        @media (max-width: 900px) {
            .main-container {
                flex-direction: column;
            }
            
            .login-section {
                max-width: 100%;
                order: -1;
            }
            
            .login-container {
                position: static;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Activities Section -->
        <div class="activities-section">
            <div class="activities-header">
                <h1>WordBoard</h1>
                <p>Click on any activity below to play!</p>
            </div>
            
            <div class="filter-section">
                <div class="filter-row">
                    <div class="filter-type">
                        <h3>Type:</h3>
                        <div class="filter-buttons" id="typeButtons">
                            <button class="filter-btn active" data-filter="all">All</button>
                            <button class="filter-btn" data-filter="quiz">Quiz</button>
                            <button class="filter-btn" data-filter="match">Match</button>
                            <button class="filter-btn" data-filter="wheel">Wheel</button>
                            <button class="filter-btn" data-filter="crossword">Crossword</button>
                            <button class="filter-btn" data-filter="wordsearch">Word Search</button>
                            <button class="filter-btn" data-filter="missingword">Missing Word</button>
                            <button class="filter-btn" data-filter="groupsort">Group Sort</button>
                        </div>
                    </div>
                    <div class="filter-search">
                        <h3>Search:</h3>
                        <div class="search-row">
                            <input type="text" id="searchInput" placeholder="Search by title or tag (e.g., dimA, lesson01, math)">
                            <button type="button" id="clearFiltersBtn" class="clear-btn">Clear Filters</button>
                        </div>
                    </div>
                </div>
                <?php if ($showClassLessonFilters): ?>
                <div class="filter-row" style="margin-top: 12px;">
                    <div class="filter-class">
                        <h3>Class:</h3>
                        <div class="filter-buttons" id="classButtons">
                            <button class="filter-btn active" data-class="all">All</button>
                            <?php foreach (array_keys($classOrder) as $cls): ?>
                            <button class="filter-btn" data-class="<?php echo $cls; ?>"><?php echo $cls; ?></button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="filter-lesson">
                        <h3>Lesson:</h3>
                        <select id="lessonSelect">
                            <option value="all">All Lessons</option>
                            <?php for ($l = 1; $l <= 30; $l++): ?>
                            <option value="les<?php echo str_pad($l, 2, '0', STR_PAD_LEFT); ?>">Lesson <?php echo str_pad($l, 2, '0', STR_PAD_LEFT); ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div id="activitiesGrid" class="activity-grid"></div>
        
        <!-- Login Section -->
        <div class="login-section">
            <div class="login-container">
                <h1>WordBoard</h1>
                <h2>Teacher Login</h2>
                
                <form id="loginForm">
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <button type="submit">Login</button>
                </form>
                
                <div id="message" class="message"></div>

                <div style="margin-top: 15px; border-top: 1px solid #eee; padding-top: 12px;">
                    <button type="button" id="rebuildCacheBtn" style="background: none; border: none; color: #999; font-size: 11px; cursor: pointer; padding: 4px 8px;">Rebuild Cache</button>
                    <span id="rebuildCacheMsg" style="font-size: 11px; color: #999; margin-left: 5px;"></span>
                </div>
            </div>
        </div>
    </div>
    
    <script src="js/auth.js"></script>
    <script>
        var allActivities = <?php echo json_encode(array_values($activities), JSON_UNESCAPED_UNICODE); ?>;
        var currentFilter = 'all';
        var currentClass = 'all';
        var currentLesson = 'all';
        var selectedTags = [];
        var classLessonFiltersEnabled = <?php echo $showClassLessonFilters ? 'true' : 'false'; ?>;

        function syncTagsFromInput() {
            var val = document.getElementById('searchInput').value;
            if (!val) {
                selectedTags = [];
            } else {
                selectedTags = val.split(',').map(function(t) { return t.replace(/^\s+|\s+$/g, '').toLowerCase(); }).filter(function(t) { return t; });
            }
            updateClearBtn();
            highlightTags();
        }

        function syncInputFromTags() {
            document.getElementById('searchInput').value = selectedTags.join(', ');
            updateClearBtn();
        }

        function updateClearBtn() {
            var btn = document.getElementById('clearFiltersBtn');
            if (selectedTags.length > 0 || document.getElementById('searchInput').value) {
                btn.classList.add('visible');
            } else {
                btn.classList.remove('visible');
            }
        }

        function highlightTags() {
            var allTagSpans = document.querySelectorAll('.activity-tag');
            for (var i = 0; i < allTagSpans.length; i++) {
                var tagText = allTagSpans[i].getAttribute('data-tag').toLowerCase();
                var isActive = false;
                for (var j = 0; j < selectedTags.length; j++) {
                    if (tagText === selectedTags[j]) {
                        isActive = true;
                        break;
                    }
                }
                if (isActive) {
                    allTagSpans[i].classList.add('active');
                } else {
                    allTagSpans[i].classList.remove('active');
                }
            }
        }

        function toggleTag(tagValue) {
            var lower = tagValue.toLowerCase();
            var idx = -1;
            for (var i = 0; i < selectedTags.length; i++) {
                if (selectedTags[i] === lower) { idx = i; break; }
            }
            if (idx >= 0) {
                selectedTags.splice(idx, 1);
            } else {
                selectedTags.push(lower);
            }
            syncInputFromTags();
            renderActivities();
        }

        (function() {
            var urlParams = new URLSearchParams(window.location.search);
            var urlType = urlParams.get('type');
            var urlSearch = urlParams.get('search');

            if (urlType && urlType !== 'all') {
                currentFilter = urlType;
                document.querySelectorAll('.filter-btn').forEach(function(btn) {
                    btn.classList.remove('active');
                    if (btn.getAttribute('data-filter') === currentFilter) {
                        btn.classList.add('active');
                    }
                });
            }

            if (urlSearch) {
                document.getElementById('searchInput').value = urlSearch;
                syncTagsFromInput();
            }
        })();
        
        function getPlayUrl(activity) {
            var urls = {
                'quiz': 'games/quiz/quiz.html?id=' + activity.id,
                'match': 'games/match/match.html?id=' + activity.id,
                'wheel': 'games/wheel/wheel.html?id=' + activity.id,
                'crossword': 'games/crossword/crossword.html?id=' + activity.id,
                'wordsearch': 'games/wordsearch/wordsearch.html?id=' + activity.id,
                'missingword': 'games/missingword/missingword.html?id=' + activity.id,
                'groupsort': 'games/groupsort/groupsort.html?id=' + activity.id
            };
            return urls[activity.type] || '#';
        }
        
        function renderActivities() {
            var grid = document.getElementById('activitiesGrid');
            grid.innerHTML = '';
            
            var searchText = document.getElementById('searchInput').value.toLowerCase();
            
            var filteredActivities = allActivities.filter(function(activity) {
                if (currentFilter !== 'all' && activity.type !== currentFilter) {
                    return false;
                }
                
                if (classLessonFiltersEnabled && currentClass !== 'all') {
                    var hasClass = false;
                    if (activity.tags && Array.isArray(activity.tags)) {
                        for (var t = 0; t < activity.tags.length; t++) {
                            if (activity.tags[t] === currentClass) { hasClass = true; break; }
                        }
                    }
                    if (!hasClass) return false;
                }

                if (classLessonFiltersEnabled && currentLesson !== 'all') {
                    var hasLesson = false;
                    if (activity.tags && Array.isArray(activity.tags)) {
                        for (var t = 0; t < activity.tags.length; t++) {
                            if (activity.tags[t] === currentLesson) { hasLesson = true; break; }
                        }
                    }
                    if (!hasLesson) return false;
                }
                
                if (searchText) {
                    var searchTerms = searchText.split(',');
                    var allMatch = true;
                    for (var s = 0; s < searchTerms.length; s++) {
                        var term = searchTerms[s].replace(/^\s+|\s+$/g, '');
                        if (!term) continue;
                        var termFound = false;
                        if (activity.title && activity.title.toLowerCase().indexOf(term) !== -1) {
                            termFound = true;
                        }
                        if (!termFound && activity.tags && Array.isArray(activity.tags)) {
                            for (var j = 0; j < activity.tags.length; j++) {
                                if (activity.tags[j].toLowerCase().indexOf(term) !== -1) {
                                    termFound = true;
                                    break;
                                }
                            }
                        }
                        if (!termFound) { allMatch = false; break; }
                    }
                    if (!allMatch) return false;
                }
                
                return true;
            });
            
            if (filteredActivities.length === 0) {
                grid.innerHTML = '<p style="color: #666;">No activities found for this filter.</p>';
                return;
            }
            
            for (var i = 0; i < filteredActivities.length; i++) {
                var activity = filteredActivities[i];
                var card = document.createElement('div');
                card.className = 'activity-card';
                
                var tagsHtml = '';
                if (activity.tags && activity.tags.length > 0) {
                    var tagSpans = [];
                    for (var t = 0; t < activity.tags.length; t++) {
                        var tagVal = escapeHtml(activity.tags[t]);
                        var isActive = false;
                        for (var st = 0; st < selectedTags.length; st++) {
                            if (activity.tags[t].toLowerCase() === selectedTags[st]) { isActive = true; break; }
                        }
                        var cls = isActive ? 'activity-tag active' : 'activity-tag';
                        tagSpans.push('<span class="' + cls + '" data-tag="' + tagVal + '" onclick="toggleTag(\'' + tagVal.replace(/'/g, "\\'") + '\')">' + tagVal + '</span>');
                    }
                    tagsHtml = '<div class="card-tags">' + tagSpans.join(' ') + '</div>';
                }
                
                card.innerHTML = 
                    '<a href="' + getPlayUrl(activity) + '" class="card-play-area">' +
                    '<h3>' + escapeHtml(activity.title) + '</h3>' +
                    '<p>Click to play this activity</p>' +
                    '</a>' +
                    '<div class="card-tags">' +
                    (function() {
                        var typeTag = escapeHtml(activity.type);
                        var typeActive = false;
                        for (var k = 0; k < selectedTags.length; k++) {
                            if (activity.type.toLowerCase() === selectedTags[k]) { typeActive = true; break; }
                        }
                        var typeCls = typeActive ? 'activity-type-tag ' + activity.type + ' active' : 'activity-type-tag ' + activity.type;
                        return '<span class="' + typeCls + '" data-tag="' + typeTag + '" onclick="toggleTag(\'' + typeTag + '\')">' + typeTag + '</span>';
                    })() +
                    tagSpans.join(' ') +
                    '</div>';
                
                grid.appendChild(card);
            }
        }
        
        function escapeHtml(text) {
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            renderActivities();
            
            var filterButtons = document.querySelectorAll('#typeButtons .filter-btn');
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
            
            document.getElementById('searchInput').addEventListener('input', function() {
                syncTagsFromInput();
                renderActivities();
            });

            document.getElementById('clearFiltersBtn').addEventListener('click', function() {
                selectedTags = [];
                document.getElementById('searchInput').value = '';
                currentFilter = 'all';
                currentClass = 'all';
                currentLesson = 'all';
                var typeButtons = document.querySelectorAll('#typeButtons .filter-btn');
                for (var j = 0; j < typeButtons.length; j++) {
                    typeButtons[j].classList.remove('active');
                }
                document.querySelector('#typeButtons .filter-btn[data-filter="all"]').classList.add('active');
                if (classLessonFiltersEnabled) {
                    document.querySelector('.filter-btn[data-class="all"]').classList.add('active');
                    document.getElementById('lessonSelect').value = 'all';
                }
                updateClearBtn();
                renderActivities();
            });

            if (classLessonFiltersEnabled) {
                var classButtons = document.querySelectorAll('#classButtons .filter-btn');
                for (var ci = 0; ci < classButtons.length; ci++) {
                    classButtons[ci].addEventListener('click', function() {
                        for (var cj = 0; cj < classButtons.length; cj++) {
                            classButtons[cj].classList.remove('active');
                        }
                        this.classList.add('active');
                        currentClass = this.getAttribute('data-class');
                        renderActivities();
                    });
                }

                document.getElementById('lessonSelect').addEventListener('change', function() {
                    currentLesson = this.value;
                    renderActivities();
                });
            }
        });
        
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            var username = document.getElementById('username').value;
            var password = document.getElementById('password').value;
            var messageDiv = document.getElementById('message');
            
            messageDiv.textContent = 'Logging in...';
            messageDiv.className = 'message info';
            
            auth.login(username, password, function(response) {
                if (response.success) {
                    if (response.redirect) {
                        window.location.href = response.redirect;
                    } else if (response.role === 'admin') {
                        window.location.href = 'admin/dashboard.php';
                    } else if (response.role === 'publisher') {
                        window.location.href = 'admin/publisher_dashboard.php';
                    } else {
                        window.location.href = 'student/dashboard.php';
                    }
                } else {
                    messageDiv.textContent = response.message;
                    messageDiv.className = 'message error';
                }
            });
        });
        
        auth.checkLogin(function(response) {
            if (response.logged_in) {
                if (response.role === 'admin') {
                    window.location.href = 'admin/dashboard.php';
                } else if (response.role === 'publisher') {
                    window.location.href = 'admin/publisher_dashboard.php';
                } else {
                    window.location.href = 'student/dashboard.php';
                }
            }
        });

        document.getElementById('rebuildCacheBtn').addEventListener('click', function() {
            var btn = this;
            var msg = document.getElementById('rebuildCacheMsg');
            btn.disabled = true;
            msg.textContent = 'Rebuilding...';
            msg.style.color = '#667eea';

            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'rebuild_index.php', true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.onload = function() {
                btn.disabled = false;
                if (xhr.status === 200) {
                    var resp = JSON.parse(xhr.responseText);
                    if (resp.success) {
                        msg.textContent = 'Done (' + resp.count + ' activities)';
                        msg.style.color = '#28a745';
                    } else {
                        msg.textContent = 'Error: ' + resp.message;
                        msg.style.color = '#dc3545';
                    }
                } else {
                    msg.textContent = 'Request failed';
                    msg.style.color = '#dc3545';
                }
                setTimeout(function() { msg.textContent = ''; }, 5000);
            };
            xhr.onerror = function() {
                btn.disabled = false;
                msg.textContent = 'Network error';
                msg.style.color = '#dc3545';
            };
            xhr.send('{}');
        });
    </script>
</body>
</html>
