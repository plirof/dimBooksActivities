var wheel = {
    segments: [],
    colors: ['#667eea', '#764ba2', '#f093fb', '#f5576c', '#4facfe', '#00f2fe', '#43e97b', '#38f9d7'],
    canvas: null,
    ctx: null,
    rotation: 0,
    isSpinning: false,
    history: [],
    
    init: function(data) {
        this.segments = data;
        this.canvas = document.getElementById('wheelCanvas');
        this.ctx = this.canvas.getContext('2d');
        this.rotation = 0;
        this.isSpinning = false;
        this.history = [];
        
        this.draw();
    },
    
    draw: function() {
        var ctx = this.ctx;
        var centerX = this.canvas.width / 2;
        var centerY = this.canvas.height / 2;
        var radius = Math.min(centerX, centerY) - 10;

        ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

        var anglePerSegment = (2 * Math.PI) / this.segments.length;

        for (var i = 0; i < this.segments.length; i++) {
            var textAngle = this.rotation + i * anglePerSegment - Math.PI / 2;
            var startAngle = textAngle - anglePerSegment / 2;
            var endAngle = textAngle + anglePerSegment / 2;

            ctx.beginPath();
            ctx.moveTo(centerX, centerY);
            ctx.arc(centerX, centerY, radius, startAngle, endAngle);
            ctx.closePath();

            ctx.fillStyle = this.colors[i % this.colors.length];
            ctx.fill();

            if (this.segments[i] && this.segments[i] !== undefined) {
                ctx.save();
                ctx.translate(centerX, centerY);
                ctx.rotate(textAngle);
                ctx.textAlign = 'right';
                ctx.fillStyle = 'white';
                ctx.font = 'bold 14px Arial';
                ctx.fillText(this.segments[i], radius - 10, 5);
                ctx.restore();
            }
        }

        ctx.beginPath();
        ctx.arc(centerX, centerY, 20, 0, 2 * Math.PI);
        ctx.fillStyle = '#333';
        ctx.fill();
    },
    
    spin: function() {
        if (this.isSpinning) {
            return;
        }
        
        this.isSpinning = true;
        document.getElementById('spinBtn').disabled = true;
        document.getElementById('resultText').textContent = 'Spinning...';
        
        var spins = 5 + Math.random() * 5;
        var targetRotation = this.rotation + spins * 2 * Math.PI;
        var duration = 3000;
        var startTime = null;
        var startRotation = this.rotation;
        
        var animate = function(timestamp) {
            if (!startTime) {
                startTime = timestamp;
            }
            
            var elapsed = timestamp - startTime;
            var progress = elapsed / duration;
            
            if (progress < 1) {
                var easeOut = 1 - Math.pow(1 - progress, 3);
                this.rotation = startRotation + (targetRotation - startRotation) * easeOut;
                this.draw();
                requestAnimationFrame(animate.bind(this));
            } else {
                this.rotation = targetRotation;
                this.draw();
                this.showResult();
            }
        }.bind(this);
        
        requestAnimationFrame(animate);
    },
    
    showResult: function() {
        this.isSpinning = false;
        document.getElementById('spinBtn').disabled = false;

        var anglePerSegment = (2 * Math.PI) / this.segments.length;
        var anglePerSegmentDeg = (360 / this.segments.length);

        var arrowAngleDeg = 270;
        var rotationDeg = (this.rotation * 180 / Math.PI) % 360;
        if (rotationDeg < 0) {
            rotationDeg += 360;
        }

        var winningIndex = 0;
        var minDiff = Infinity;

        for (var i = 0; i < this.segments.length; i++) {
            var segmentTextAngleDeg = (rotationDeg - 90 + i * anglePerSegmentDeg) % 360;
            if (segmentTextAngleDeg < 0) {
                segmentTextAngleDeg += 360;
            }

            var diff = Math.abs(arrowAngleDeg - segmentTextAngleDeg);
            if (diff > 180) {
                diff = 360 - diff;
            }

            if (diff < minDiff) {
                minDiff = diff;
                winningIndex = i;
            }
        }

        var result = this.segments[winningIndex];
        document.getElementById('resultText').textContent = result;

        this.history.unshift(result);
        if (this.history.length > 10) {
            this.history.pop();
        }

        this.updateHistory();

        this.saveSpin(result);
    },
    
    updateHistory: function() {
        var historyList = document.getElementById('historyList');
        historyList.innerHTML = '';
        
        for (var i = 0; i < this.history.length; i++) {
            var li = document.createElement('li');
            li.textContent = (i + 1) + '. ' + this.history[i];
            historyList.appendChild(li);
        }
    },
    
    saveSpin: function(result) {
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
            score: 100
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
            if (response.success && response.activity && response.activity.type === 'wheel') {
                document.getElementById('wheelTitle').textContent = response.activity.title;
                wheel.init(response.activity.data.segments);
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
