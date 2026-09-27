<?php
require_once '../config.php';

$quizzes = getQuizzes();
$soloQuizzes = [];

foreach ($quizzes['quizzes'] as $quiz) {
    if (isset($quiz['allow_solo']) && $quiz['allow_solo'] === true) {
        $soloQuizzes[] = $quiz;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solo Quiz - Quiz Game</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>👤 Play Solo Quiz</h1>
            <p style="color: #666; margin-bottom: 30px;">Choose a quiz to play on your own. No PIN required!</p>

            <?php if (empty($soloQuizzes)): ?>
                <div class="alert alert-warning">
                    <p style="font-size: 1.2rem; text-align: center;">
                        😕 No solo quizzes available yet!<br>
                        <small>Ask your teacher to enable solo mode for quizzes.</small>
                    </p>
                </div>
            <?php else: ?>
                <?php foreach ($soloQuizzes as $quiz): ?>
                    <div class="card" style="margin-bottom: 20px; border-left: 5px solid #46178f;">
                        <h3 style="margin-bottom: 10px;">📝 <?php echo htmlspecialchars($quiz['title']); ?></h3>

                        <?php if (!empty($quiz['description'])): ?>
                            <p style="color: #666; margin-bottom: 15px;">
                                <?php echo htmlspecialchars($quiz['description']); ?>
                            </p>
                        <?php endif; ?>

                        <div style="display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 20px;">
                            <span style="background: #e3f2fd; padding: 5px 15px; border-radius: 15px; font-size: 0.9rem;">
                                🏷️ <?php echo htmlspecialchars($quiz['category'] ?? 'General'); ?>
                            </span>
                            <span style="background: #e8f5e9; padding: 5px 15px; border-radius: 15px; font-size: 0.9rem;">
                                ❓ <?php echo count($quiz['questions']); ?> Questions
                            </span>
                        </div>

                        <div style="text-align: right;">
                            <a href="solo_game.php?quiz_id=<?php echo urlencode($quiz['id']); ?>" 
                               class="btn btn-primary btn-lg">
                                🎮 Play Now
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <div class="mt-40 text-center">
                <a href="../index.php" class="btn btn-warning">← Back to Menu</a>
            </div>
        </div>
    </div>
</body>
</html>
