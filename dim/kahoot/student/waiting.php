<?php
require_once '../config.php';

$pin = isset($_SESSION['game_pin']) ? $_SESSION['game_pin'] : '';
$playerName = isset($_SESSION['player_name']) ? $_SESSION['player_name'] : '';

if (empty($pin) || empty($playerName)) {
    $_SESSION['error'] = '⛔ You need to join a game first!';
    redirect('./join.php');
}

$games = getGames();

if (!isset($games['active_games'][$pin])) {
    $_SESSION['error'] = '❌ Game not found!';
    redirect('./join.php');
}

$game = $games['active_games'][$pin];

if (!isset($game['participants'][$playerName])) {
    $_SESSION['error'] = '❌ You are not in this game!';
    redirect('./join.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Waiting Room - Quiz Game</title>
    <link rel="stylesheet" href="../css/style.css">
    <script>
        var gamePin = <?php echo json_encode($pin); ?>;
        var playerName = <?php echo json_encode($playerName); ?>;
        var pollInterval = null;
        
        function pollGameStatus() {
            fetch('../api/poll.php?pin=' + gamePin + '&action=status', {
                credentials: 'same-origin'
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.status === 'playing') {
                            clearInterval(pollInterval);
                            window.location.href = 'game.php';
                        }
                    }
                })
                .catch(error => {
                    console.error('Polling error:', error);
                });
        }
        
         function updateParticipants() {
             fetch('../api/game_status.php?pin=' + gamePin, {
                 credentials: 'same-origin'
             })
                 .then(response => response.json())
                 .then(data => {
                     if (data.success && data.participants) {
                         var container = document.getElementById('participants-container');
                         var html = '';
                         
                         Object.keys(data.participants).forEach(function(name) {
                             var initial = name.substring(0, 1).toUpperCase();
                             html += '<div class="participant-avatar" title="' + name + '">' + initial + '</div>';
                         });
                         
                         container.innerHTML = html;
                         document.getElementById('player-count').textContent = Object.keys(data.participants).length;
                     }
                 })
                 .catch(error => {
                     console.error('Error updating participants:', error);
                 });
         }
         
         function leaveGame() {
             if (!confirm('Are you sure you want to leave this game?')) {
                 return;
             }
             
              fetch('../api/game.php', {
                  method: 'POST',
                  headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                  credentials: 'same-origin',
                  body: 'action=leave&pin=' + gamePin
              })
             .then(response => response.json())
             .then(data => {
                 if (data.success) {
                     window.location.href = 'join.php';
                 } else {
                     alert('Error: ' + (data.error || 'Failed to leave game'));
                 }
             })
             .catch(error => {
                 alert('Error: ' + error);
             });
         }
        
        document.addEventListener('DOMContentLoaded', function() {
            pollInterval = setInterval(pollGameStatus, 1500);
            setInterval(updateParticipants, 2000);
            updateParticipants();
        });
    </script>
</head>
<body>
    <div class="container">
        <div class="card" style="text-align: center; padding: 60px;">
            <h1>🎯 Waiting Room</h1>
            <p style="font-size: 1.5rem; margin: 20px 0;">You're: <strong><?php echo htmlspecialchars($playerName); ?></strong></p>
            <p style="font-size: 1.3rem; color: #666;">Waiting for the game to start...</p>
            
            <div style="margin: 40px 0;">
                <div class="loading" style="font-size: 2rem;">The game will start soon</div>
            </div>
            
            <div class="participants-grid" id="participants-container" style="max-height: 300px; margin-top: 30px;">
                <?php 
                foreach ($game['participants'] as $name => $data): 
                    $initial = substr($name, 0, 1);
                ?>
                    <div class="participant-avatar" title="<?php echo htmlspecialchars($name); ?>">
                        <?php echo strtoupper($initial); ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <h3 style="margin-top: 40px;">👥 Players: <span id="player-count"><?php echo count($game['participants']); ?></span></h3>
        </div>
        
        <div class="card text-center" style="margin-top: 20px;">
            <p style="font-weight: 700; color: #666;">
                💡 Tips for the game:<br>
                • Read questions carefully<br>
                • Answer as fast as you can for more points!<br>
                • Have fun!
            </p>
        </div>
        
        <div class="text-center mt-20">
            <a href="javascript:void(0);" onclick="leaveGame()" class="btn btn-warning">← Leave Game</a>
        </div>
    </div>
</body>
</html>
