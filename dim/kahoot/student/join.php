<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Game - Quiz Game</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <div style="max-width: 600px; margin: 50px auto;">
            <div class="card">
                <h1>🎯 Join a Game</h1>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <?php 
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                        ?>
                    </div>
                <?php endif; ?>
                
                <form action="process_join.php" method="POST">
                    <div class="form-group">
                        <label for="pin">🎲 Game PIN</label>
                        <input type="text" id="pin" name="pin" required 
                               placeholder="Enter 4-digit PIN" 
                               maxlength="4" 
                                pattern="[0-9]{4}"
                                style="font-size: 2rem; text-align: center; letter-spacing: 10px;">
                    </div>
                        
                        <div class="form-group">
                            <label for="nickname-lang">🌐 Nickname Language</label>
                            <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                    <input type="radio" name="nickname_lang" value="greek" checked style="width: 20px; height: 20px; transform: scale(1.2);">
                                    <span style="font-size: 1.1rem; font-weight: 800;">🇬🇷 Ελληνικά</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                    <input type="radio" name="nickname_lang" value="english" style="width: 20px; height: 20px; transform: scale(1.2);">
                                    <span style="font-size: 1.1rem; font-weight: 800;">🇬🇧 English</span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="username">👤 Nickname (3-20 characters)</label>
                            <input type="text" id="username" name="username" required 
                                   placeholder="Enter your nickname or click Generate" 
                                   minlength="3" maxlength="20">
                            <button type="button" class="btn btn-info btn-sm" onclick="generateNickname()" style="margin-left: 10px;">
                                🎲 Generate Random Nickname
                            </button>
                        </div>
                    
                    <button type="submit" class="btn btn-primary btn-lg w-100">🚀 Join Game!</button>
                </form>
                
                <div class="mt-40 text-center">
                    <p style="font-weight: 700; color: #666; margin-bottom: 15px;">
                        Already have an account?
                    </p>
                    <a href="../index.php" class="btn btn-info">🔐 Login</a>
                </div>
            </div>
            
            <div class="card text-center" style="margin-top: 20px;">
                <p style="font-weight: 700; color: #666;">
                    📝 How to Play:<br>
                    1. Enter the Game PIN from your teacher<br>
                    2. Enter your nickname<br>
                    3. Wait for the game to start!<br>
                    4. Answer questions as fast as you can!
                </p>
            </div>
        </div>
    </div>
    <script src="nickname_generator.js"></script>
</body>
</html>
