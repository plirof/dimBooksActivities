var match = {
    pairs: [],
    cards: [],
    selectedCards: [],
    matchedCount: 0,
    wrongAttempts: 0,
    
    init: function(data) {
        this.pairs = data;
        this.cards = [];
        this.selectedCards = [];
        this.matchedCount = 0;
        this.wrongAttempts = 0;
        
        this.createCards();
        this.shuffleCards();
        this.renderBoard();
        
        document.getElementById('total').textContent = this.pairs.length;
    },
    
    createCards: function() {
        for (var i = 0; i < this.pairs.length; i++) {
            this.cards.push({
                id: 'pair_' + i + '_a',
                pairId: i,
                text: this.pairs[i].left,
                type: 'left'
            });
            
            this.cards.push({
                id: 'pair_' + i + '_b',
                pairId: i,
                text: this.pairs[i].right,
                type: 'right'
            });
        }
    },
    
    shuffleCards: function() {
        for (var i = this.cards.length - 1; i > 0; i--) {
            var j = Math.floor(Math.random() * (i + 1));
            var temp = this.cards[i];
            this.cards[i] = this.cards[j];
            this.cards[j] = temp;
        }
    },
    
    renderBoard: function() {
        var board = document.getElementById('matchBoard');
        board.innerHTML = '';
        
        for (var i = 0; i < this.cards.length; i++) {
            var card = document.createElement('div');
            card.className = 'match-card';
            card.id = this.cards[i].id;
            card.textContent = this.cards[i].text;
            card.onclick = this.selectCard.bind(this, i);
            board.appendChild(card);
        }
    },
    
    selectCard: function(index) {
        var card = this.cards[index];
        var cardElement = document.getElementById(card.id);
        
        if (cardElement.classList.contains('matched')) {
            return;
        }
        
        if (this.selectedCards.length === 0) {
            this.selectedCards.push(index);
            cardElement.classList.add('selected');
        } else if (this.selectedCards.length === 1) {
            var firstIndex = this.selectedCards[0];
            var firstCard = this.cards[firstIndex];
            var firstElement = document.getElementById(firstCard.id);
            
            if (firstCard.pairId === card.pairId && firstCard.type !== card.type) {
                firstElement.classList.remove('selected');
                cardElement.classList.add('matched');
                firstElement.classList.add('matched');
                this.matchedCount++;
                document.getElementById('score').textContent = this.matchedCount;
                this.selectedCards = [];
                
                if (this.matchedCount === this.pairs.length) {
                    this.finishMatch();
                }
            } else {
                firstElement.classList.add('wrong');
                cardElement.classList.add('wrong');
                this.wrongAttempts++;
                
                setTimeout(function() {
                    firstElement.classList.remove('selected', 'wrong');
                    cardElement.classList.remove('selected', 'wrong');
                }, 500);
                
                this.selectedCards = [];
            }
        }
    },
    
    finishMatch: function() {
        var percentage = Math.round(((this.pairs.length - this.wrongAttempts) / this.pairs.length) * 100);
        if (percentage < 0) {
            percentage = 0;
        }
        
        document.getElementById('matchContainer').style.display = 'none';
        document.getElementById('resultContainer').style.display = 'block';
        document.getElementById('finalScore').textContent = percentage + '%';
        document.getElementById('correctMatches').textContent = this.pairs.length;
        document.getElementById('wrongAttempts').textContent = this.wrongAttempts;
        
        var message = '';
        if (percentage >= 90) {
            message = 'Excellent! You matched all pairs perfectly!';
        } else if (percentage >= 70) {
            message = 'Great job! You did well!';
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
            if (response.success && response.activity && response.activity.type === 'match') {
                document.getElementById('matchTitle').textContent = response.activity.title;
                match.init(response.activity.data.pairs);
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
