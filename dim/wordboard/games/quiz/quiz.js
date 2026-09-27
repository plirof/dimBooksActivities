var quiz = {
    questions: [],
    currentQuestion: 0,
    score: 0,
    answers: [],
    
    init: function(data) {
        this.questions = data;
        this.currentQuestion = 0;
        this.score = 0;
        this.answers = [];
        
        this.showQuestion();
        this.updateProgress();
    },
    
    showQuestion: function() {
        var question = this.questions[this.currentQuestion];
        var questionText = document.getElementById('questionText');
        var optionsContainer = document.getElementById('options');
        
        questionText.textContent = question.question;
        optionsContainer.innerHTML = '';
        
        for (var i = 0; i < question.options.length; i++) {
            var option = document.createElement('div');
            option.className = 'option';
            option.textContent = question.options[i];
            option.onclick = this.selectOption.bind(this, i);
            
            if (this.answers[this.currentQuestion] === i) {
                option.classList.add('selected');
            }
            
            optionsContainer.appendChild(option);
        }
        
        this.updateButtons();
    },
    
    selectOption: function(index) {
        this.answers[this.currentQuestion] = index;
        
        var options = document.querySelectorAll('.option');
        for (var i = 0; i < options.length; i++) {
            options[i].classList.remove('selected');
        }
        
        options[index].classList.add('selected');
    },
    
    nextQuestion: function() {
        if (this.answers[this.currentQuestion] === undefined) {
            alert('Please select an answer before proceeding.');
            return;
        }
        
        if (this.currentQuestion < this.questions.length - 1) {
            this.currentQuestion++;
            this.showQuestion();
            this.updateProgress();
        } else {
            this.finishQuiz();
        }
    },
    
    prevQuestion: function() {
        if (this.currentQuestion > 0) {
            this.currentQuestion--;
            this.showQuestion();
            this.updateProgress();
        }
    },
    
    updateProgress: function() {
        var progress = ((this.currentQuestion + 1) / this.questions.length) * 100;
        document.getElementById('progress').style.width = progress + '%';
    },
    
    updateButtons: function() {
        document.getElementById('prevBtn').disabled = (this.currentQuestion === 0);
        
        if (this.currentQuestion === this.questions.length - 1) {
            document.getElementById('nextBtn').textContent = 'Finish';
        } else {
            document.getElementById('nextBtn').textContent = 'Next';
        }
    },
    
    finishQuiz: function() {
        var correctCount = 0;
        
        for (var i = 0; i < this.questions.length; i++) {
            if (this.answers[i] === this.questions[i].correct) {
                correctCount++;
            }
        }
        
        var percentage = Math.round((correctCount / this.questions.length) * 100);
        
        document.getElementById('quizContainer').style.display = 'none';
        document.getElementById('resultContainer').style.display = 'block';
        document.getElementById('finalScore').textContent = percentage + '%';
        
        var message = '';
        if (percentage >= 90) {
            message = 'Excellent! You did a great job!';
        } else if (percentage >= 70) {
            message = 'Good job! Keep practicing!';
        } else if (percentage >= 50) {
            message = 'Not bad, but there is room for improvement.';
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
            if (response.success && response.activity && response.activity.type === 'quiz') {
                document.getElementById('quizTitle').textContent = response.activity.title;
                quiz.init(response.activity.data.questions);
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
