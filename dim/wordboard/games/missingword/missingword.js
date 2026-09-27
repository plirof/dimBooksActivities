var missingword = {
    sentences: [],
    currentSentence: 0,
    score: 0,
    answers: [],
    
    init: function(data) {
        this.sentences = data;
        this.currentSentence = 0;
        this.score = 0;
        this.answers = [];
        
        this.renderSentence();
        this.renderOptions();
        this.updateButtons();
    },
    
    renderSentence: function() {
        var sentence = this.sentences[this.currentSentence];
        var container = document.getElementById('sentenceContainer');
        
        var parts = sentence.sentence.split('___');
        var html = '';
        
        for (var i = 0; i < parts.length; i++) {
            html += parts[i];
            if (i < parts.length - 1) {
                var filled = this.answers[this.currentSentence] !== undefined;
                html += '<span class="missing-word" id="missingWord">' + (filled ? this.answers[this.currentSentence] : '...') + '</span>';
            }
        }
        
        container.innerHTML = html;
        document.getElementById('score').textContent = this.score;
    },
    
    renderOptions: function() {
        var sentence = this.sentences[this.currentSentence];
        var container = document.getElementById('optionsContainer');
        container.innerHTML = '';
        
        var options = sentence.options.slice();
        
        for (var i = 0; i < options.length; i++) {
            var btn = document.createElement('div');
            btn.className = 'option-btn';
            btn.textContent = options[i];
            btn.onclick = this.selectOption.bind(this, options[i]);
            container.appendChild(btn);
        }
    },
    
    selectOption: function(option) {
        this.answers[this.currentSentence] = option;
        document.getElementById('missingWord').textContent = option;
        document.getElementById('missingWord').classList.add('filled');
        
        if (option === this.sentences[this.currentSentence].correct) {
            document.getElementById('missingWord').classList.add('correct');
            document.getElementById('missingWord').classList.remove('incorrect');
        } else {
            document.getElementById('missingWord').classList.add('incorrect');
            document.getElementById('missingWord').classList.remove('correct');
        }
    },
    
    nextSentence: function() {
        if (this.answers[this.currentSentence] === undefined) {
            alert('Please select an answer.');
            return;
        }
        
        if (this.answers[this.currentSentence] === this.sentences[this.currentSentence].correct) {
            this.score++;
        }
        
        if (this.currentSentence < this.sentences.length - 1) {
            this.currentSentence++;
            this.renderSentence();
            this.renderOptions();
            this.updateButtons();
        } else {
            this.finishGame();
        }
    },
    
    prevSentence: function() {
        if (this.currentSentence > 0) {
            this.currentSentence--;
            this.renderSentence();
            this.renderOptions();
            this.updateButtons();
        }
    },
    
    updateButtons: function() {
        document.getElementById('prevBtn').disabled = (this.currentSentence === 0);
        
        if (this.currentSentence === this.sentences.length - 1) {
            document.getElementById('nextBtn').textContent = 'Finish';
        } else {
            document.getElementById('nextBtn').textContent = 'Next';
        }
    },
    
    finishGame: function() {
        var percentage = Math.round((this.score / this.sentences.length) * 100);
        
        document.getElementById('missingwordContainer').style.display = 'none';
        document.getElementById('resultContainer').style.display = 'block';
        document.getElementById('finalScore').textContent = percentage + '%';
        
        var message = '';
        if (percentage >= 90) {
            message = 'Excellent! You filled in all the blanks correctly!';
        } else if (percentage >= 70) {
            message = 'Great job! Keep practicing!';
        } else if (percentage >= 50) {
            message = 'Not bad, but keep practicing!';
        } else {
            message = 'Keep practicing. You can do better!';
        }
        
        document.getElementById('resultMessage').textContent = message;
        
        this.saveResult(percentage);
    },
    
    saveResult: function(percentage) {
        var urlParams = new URLSearchParams(window.location.search);
        var activityId = urlParams.get('id');
        
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '../../api/save_result.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        
        xhr.onload = function() {
            if (xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                if (!response.success) {
                    console.error('Failed to save result:', response.message);
                }
            }
        };
        
        xhr.send(JSON.stringify({
            activity_id: activityId,
            score: percentage
        }));
    }
};

window.onload = function() {
    var urlParams = new URLSearchParams(window.location.search);
    var activityId = urlParams.get('id');
    
    if (!activityId) {
        alert('Activity ID is required.');
        window.location.href = '../dashboard.php';
        return;
    }
    
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '../../api/load_activity.php', true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    
    xhr.onload = function() {
        if (xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);
            if (response.success && response.activity && response.activity.type === 'missingword') {
                document.getElementById('missingwordTitle').textContent = response.activity.title;
                missingword.init(response.activity.data.sentences);
            } else {
                alert('Failed to load activity.');
                window.location.href = '../dashboard.php';
            }
        }
    };
    
    xhr.send(JSON.stringify({
        id: activityId
    }));
};
