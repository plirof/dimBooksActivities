var wordsearch = {
    grid: [],
    words: [],
    foundWords: [],
    selectedCells: [],
    isSelecting: false,
    startCell: null,
    
    init: function(data) {
        this.grid = data.grid;
        this.words = data.words;
        this.foundWords = [];
        this.selectedCells = [];
        this.isSelecting = false;
        this.startCell = null;
        
        this.renderGrid();
        this.renderWordList();
    },
    
    renderGrid: function() {
        var gridContainer = document.getElementById('wordsearchGrid');
        gridContainer.innerHTML = '';
        
        for (var row = 0; row < this.grid.length; row++) {
            var rowDiv = document.createElement('div');
            rowDiv.className = 'ws-row';
            
            for (var col = 0; col < this.grid[row].length; col++) {
                var cellDiv = document.createElement('div');
                cellDiv.className = 'ws-cell';
                cellDiv.textContent = this.grid[row][col];
                cellDiv.dataset.row = row;
                cellDiv.dataset.col = col;
                
                cellDiv.addEventListener('mousedown', this.onCellMouseDown.bind(this));
                cellDiv.addEventListener('mouseenter', this.onCellMouseEnter.bind(this));
                cellDiv.addEventListener('mouseup', this.onCellMouseUp.bind(this));
                
                rowDiv.appendChild(cellDiv);
            }
            
            gridContainer.appendChild(rowDiv);
        }
        
        document.addEventListener('mouseup', this.onDocumentMouseUp.bind(this));
    },
    
    renderWordList: function() {
        var wordList = document.getElementById('wordList');
        wordList.innerHTML = '';
        
        for (var i = 0; i < this.words.length; i++) {
            var li = document.createElement('li');
            li.textContent = this.words[i];
            li.dataset.word = this.words[i];
            wordList.appendChild(li);
        }
    },
    
    onCellMouseDown: function(e) {
        this.isSelecting = true;
        this.startCell = {
            row: parseInt(e.target.dataset.row),
            col: parseInt(e.target.dataset.col)
        };
        this.selectedCells = [this.startCell];
        this.highlightSelection();
    },
    
    onCellMouseEnter: function(e) {
        if (!this.isSelecting) {
            return;
        }
        
        var endCell = {
            row: parseInt(e.target.dataset.row),
            col: parseInt(e.target.dataset.col)
        };
        
        this.selectedCells = this.getLine(this.startCell, endCell);
        this.highlightSelection();
    },
    
    onCellMouseUp: function(e) {
        this.checkWord();
        this.clearSelection();
        this.isSelecting = false;
    },
    
    onDocumentMouseUp: function(e) {
        if (this.isSelecting) {
            this.checkWord();
            this.clearSelection();
            this.isSelecting = false;
        }
    },
    
    getLine: function(start, end) {
        var line = [];
        var dr = end.row - start.row;
        var dc = end.col - start.col;
        
        var steps = Math.max(Math.abs(dr), Math.abs(dc));
        if (steps === 0) {
            return [start];
        }
        
        var rowStep = dr / steps;
        var colStep = dc / steps;
        
        if (!this.isStraightLine(rowStep, colStep)) {
            return [start];
        }
        
        for (var i = 0; i <= steps; i++) {
            line.push({
                row: start.row + Math.round(rowStep * i),
                col: start.col + Math.round(colStep * i)
            });
        }
        
        return line;
    },
    
    isStraightLine: function(rowStep, colStep) {
        if (Math.abs(rowStep) < 0.1 && Math.abs(colStep) < 0.1) {
            return true;
        }
        if (Math.abs(rowStep) < 0.1) {
            return true;
        }
        if (Math.abs(colStep) < 0.1) {
            return true;
        }
        if (Math.abs(Math.abs(rowStep) - Math.abs(colStep)) < 0.1) {
            return true;
        }
        return false;
    },
    
    highlightSelection: function() {
        var cells = document.querySelectorAll('.ws-cell');
        
        for (var i = 0; i < cells.length; i++) {
            cells[i].classList.remove('selected');
        }
        
        for (var j = 0; j < this.selectedCells.length; j++) {
            var cell = document.querySelector('.ws-cell[data-row="' + this.selectedCells[j].row + '"][data-col="' + this.selectedCells[j].col + '"]');
            if (cell && !cell.classList.contains('found')) {
                cell.classList.add('selected');
            }
        }
    },
    
    clearSelection: function() {
        var cells = document.querySelectorAll('.ws-cell');
        
        for (var i = 0; i < cells.length; i++) {
            cells[i].classList.remove('selected');
        }
        
        this.selectedCells = [];
    },
    
    checkWord: function() {
        if (this.selectedCells.length < 2) {
            return;
        }
        
        var word = '';
        for (var i = 0; i < this.selectedCells.length; i++) {
            word += this.grid[this.selectedCells[i].row][this.selectedCells[i].col];
        }
        
        var reversedWord = word.split('').reverse().join('');
        
        if (this.foundWords.indexOf(word) === -1 && this.foundWords.indexOf(reversedWord) === -1) {
            if (this.words.indexOf(word) !== -1) {
                this.foundWords.push(word);
                this.markWordAsFound(word);
            } else if (this.words.indexOf(reversedWord) !== -1) {
                this.foundWords.push(reversedWord);
                this.markWordAsFound(reversedWord);
            }
        }
        
        if (this.foundWords.length === this.words.length) {
            this.finishGame();
        }
    },
    
    markWordAsFound: function(word) {
        for (var i = 0; i < this.selectedCells.length; i++) {
            var cell = document.querySelector('.ws-cell[data-row="' + this.selectedCells[i].row + '"][data-col="' + this.selectedCells[i].col + '"]');
            if (cell) {
                cell.classList.add('found');
            }
        }
        
        var wordItem = document.querySelector('.word-list li[data-word="' + word + '"]');
        if (wordItem) {
            wordItem.classList.add('found');
        }
    },
    
    finishGame: function() {
        alert('Congratulations! You found all the words!');
        
        this.saveResult(100);
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
            if (response.success && response.activity && response.activity.type === 'wordsearch') {
                document.getElementById('wordsearchTitle').textContent = response.activity.title;
                wordsearch.init(response.activity.data);
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
