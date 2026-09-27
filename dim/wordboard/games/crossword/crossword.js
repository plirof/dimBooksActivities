var crossword = {
    grid: [],
    words: [],
    solvedCount: 0,
    totalWords: 0,
    // Current typing direction and cursor position. curDir is remembered
    // while typing, so at crossings the cursor keeps moving the same way.
    curDir: null,
    curRow: null,
    curCol: null,

    init: function(data) {
        this.grid = data.grid;
        this.words = data.words;
        this.solvedCount = 0;
        this.totalWords = this.words.length;
        this.curDir = null;
        this.curRow = null;
        this.curCol = null;

        this.renderGrid();
        this.renderClues();
    },

    renderGrid: function() {
        var gridContainer = document.getElementById('crosswordGrid');
        gridContainer.innerHTML = '';

        for (var row = 0; row < this.grid.length; row++) {
            var rowDiv = document.createElement('div');
            rowDiv.className = 'crossword-row';

            for (var col = 0; col < this.grid[row].length; col++) {
                var cellDiv = document.createElement('div');
                cellDiv.className = 'crossword-cell';

                if (this.grid[row][col] === '#') {
                    cellDiv.classList.add('black');
                } else {
                    var self = this;
                    var cellNum = this.getCellNumber(row, col);
                    if (cellNum) {
                        var numSpan = document.createElement('span');
                        numSpan.className = 'cell-number';
                        numSpan.textContent = cellNum;
                        cellDiv.appendChild(numSpan);
                    }

                    var input = document.createElement('input');
                    input.type = 'text';
                    input.maxLength = 1;
                    input.dataset.row = row;
                    input.dataset.col = col;
                    input.dataset.answer = this.grid[row][col];

                    // Select existing content on focus so typing replaces it.
                    input.addEventListener('focus', function(e) {
                        e.target.select();
                    });

                    // Clicking a cell sets the typing direction; clicking the
                    // same crossing cell again toggles across/down.
                    input.addEventListener('click', function(e) {
                        self.onCellClick(parseInt(e.target.dataset.row), parseInt(e.target.dataset.col));
                    });

                    input.addEventListener('input', function(e) {
                        var row = parseInt(e.target.dataset.row);
                        var col = parseInt(e.target.dataset.col);

                        e.target.parentElement.classList.remove('correct');

                        if (e.target.value.length === 1) {
                            self.advance(row, col, self.curDir, 1);
                        }
                    });

                    // Backspace on an empty cell jumps back one cell in the
                    // current direction and clears it.
                    input.addEventListener('keydown', function(e) {
                        if (e.keyCode !== 8) return;
                        if (e.target.value !== '') return;
                        e.preventDefault();
                        var row = parseInt(e.target.dataset.row);
                        var col = parseInt(e.target.dataset.col);
                        self.advance(row, col, self.curDir, -1);
                        var prev = document.querySelector('.crossword-cell input[data-row="' + self.curRow + '"][data-col="' + self.curCol + '"]');
                        if (prev) prev.value = '';
                    });

                    cellDiv.appendChild(input);
                }

                rowDiv.appendChild(cellDiv);
            }

            gridContainer.appendChild(rowDiv);
        }
    },

    getCellNumber: function(row, col) {
        for (var i = 0; i < this.words.length; i++) {
            var word = this.words[i];
            if (word.row === row && word.col === col) {
                return word.number;
            }
        }
        return null;
    },

    // All word indices whose span passes through (row, col).
    wordsAt: function(row, col) {
        var found = [];
        for (var i = 0; i < this.words.length; i++) {
            var word = this.words[i];
            for (var j = 0; j < word.answer.length; j++) {
                var r = word.row + (word.direction === 'down' ? j : 0);
                var c = word.col + (word.direction === 'across' ? j : 0);
                if (r === row && c === col) { found.push(i); break; }
            }
        }
        return found;
    },

    onCellClick: function(row, col) {
        var here = this.wordsAt(row, col);
        var dir;

        if (here.length === 1) {
            // The cell belongs to a single word: use its direction.
            dir = this.words[here[0]].direction;
        } else if (here.length > 1) {
            // Crossing cell: keep the current direction while typing, but
            // clicking the same cell again toggles across/down.
            if (this.curRow === row && this.curCol === col && this.curDir) {
                dir = (this.curDir === 'across') ? 'down' : 'across';
            } else {
                dir = this.curDir || 'across';
            }
        } else {
            dir = this.curDir || 'across';
        }

        this.curDir = dir;
        this.curRow = row;
        this.curCol = col;
        this.highlightActiveWord();
    },

    // Move the cursor one cell in the given direction (step 1 = forward,
    // -1 = backward). Stops at black cells and grid edges.
    advance: function(row, col, dir, step) {
        if (!dir) dir = 'across';
        var r = row + (dir === 'down' ? step : 0);
        var c = col + (dir === 'across' ? step : 0);
        if (r < 0 || c < 0 || r >= this.grid.length || c >= this.grid[r].length) return;
        if (this.grid[r][c] === '#') return;
        var input = document.querySelector('.crossword-cell input[data-row="' + r + '"][data-col="' + c + '"]');
        if (input) {
            this.curRow = r;
            this.curCol = c;
            input.focus();
        }
    },

    activeWordIndex: function() {
        if (!this.curDir) return null;
        var here = this.wordsAt(this.curRow, this.curCol);
        for (var i = 0; i < here.length; i++) {
            if (this.words[here[i]].direction === this.curDir) return here[i];
        }
        return null;
    },

    highlightActiveWord: function() {
        var cells = document.querySelectorAll('.crossword-cell');
        for (var i = 0; i < cells.length; i++) cells[i].classList.remove('active');
        var lis = document.querySelectorAll('.clue-list li');
        for (var j = 0; j < lis.length; j++) lis[j].classList.remove('active');

        var idx = this.activeWordIndex();
        if (idx === null) return;
        var word = this.words[idx];

        for (var k = 0; k < word.answer.length; k++) {
            var r = word.row + (word.direction === 'down' ? k : 0);
            var c = word.col + (word.direction === 'across' ? k : 0);
            var cellInput = document.querySelector('.crossword-cell input[data-row="' + r + '"][data-col="' + c + '"]');
            if (cellInput) cellInput.parentElement.classList.add('active');
        }
        var li = document.querySelector('.clue-list li[data-word-index="' + idx + '"]');
        if (li) li.classList.add('active');
    },

    renderClues: function() {
        var acrossContainer = document.getElementById('acrossClues');
        var downContainer = document.getElementById('downClues');

        acrossContainer.innerHTML = '';
        downContainer.innerHTML = '';

        for (var i = 0; i < this.words.length; i++) {
            var word = this.words[i];
            var li = document.createElement('li');
            li.textContent = word.number + '. ' + word.clue;
            li.dataset.wordIndex = i;
            li.dataset.direction = word.direction;
            li.onclick = this.focusWord.bind(this, i);

            if (word.direction === 'across') {
                acrossContainer.appendChild(li);
            } else {
                downContainer.appendChild(li);
            }
        }
    },

    focusWord: function(index) {
        var word = this.words[index];
        this.curDir = word.direction;
        this.curRow = word.row;
        this.curCol = word.col;
        this.highlightActiveWord();

        // Focus the first empty cell of the word (fall back to its start).
        var fr = word.row, fc = word.col;
        for (var j = 0; j < word.answer.length; j++) {
            var r = word.row + (word.direction === 'down' ? j : 0);
            var c = word.col + (word.direction === 'across' ? j : 0);
            var inp = document.querySelector('.crossword-cell input[data-row="' + r + '"][data-col="' + c + '"]');
            if (inp && inp.value === '') { fr = r; fc = c; break; }
        }
        this.curRow = fr;
        this.curCol = fc;

        var startCell = document.querySelector('.crossword-cell input[data-row="' + fr + '"][data-col="' + fc + '"]');
        if (startCell) {
            startCell.focus();
        }
    },

    checkAnswers: function() {
        var inputs = document.querySelectorAll('.crossword-cell input');
        var correctCount = 0;
        var totalCells = inputs.length;

        for (var i = 0; i < inputs.length; i++) {
            var input = inputs[i];
            var answer = input.dataset.answer.toUpperCase();
            var value = input.value.toUpperCase();

            input.parentElement.classList.remove('correct');

            if (value === answer) {
                input.parentElement.classList.add('correct');
                correctCount++;
            }
        }

        var percentage = Math.round((correctCount / totalCells) * 100);

        if (percentage === 100) {
            alert('Perfect! You solved the crossword!');
        } else if (percentage >= 80) {
            alert('Great job! ' + percentage + '% correct!');
        } else {
            alert('Keep trying! ' + percentage + '% correct.');
        }

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
            if (response.success && response.activity && response.activity.type === 'crossword') {
                document.getElementById('crosswordTitle').textContent = response.activity.title;
                crossword.init(response.activity.data);
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
