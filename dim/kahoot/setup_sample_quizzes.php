<?php
require_once 'config.php';

$sampleQuizzes = [
    [
        'id' => 'sample_math_001',
        'title' => 'Basic Math Challenge',
        'description' => 'Test your basic math skills with addition, subtraction, multiplication, and division!',
        'questions' => [
            [
                'id' => generateId(),
                'text' => 'What is 15 + 27?',
                'answers' => ['32', '42', '45', '52'],
                'correct' => 1,
                'time' => 20,
                'points' => 1000
            ],
            [
                'id' => generateId(),
                'text' => 'What is 100 - 35?',
                'answers' => ['55', '65', '75', '85'],
                'correct' => 1,
                'time' => 20,
                'points' => 1000
            ],
            [
                'id' => generateId(),
                'text' => 'What is 8 × 7?',
                'answers' => ['48', '54', '56', '64'],
                'correct' => 2,
                'time' => 25,
                'points' => 1000
            ],
            [
                'id' => generateId(),
                'text' => 'What is 72 ÷ 8?',
                'answers' => ['8', '9', '10', '11'],
                'correct' => 1,
                'time' => 20,
                'points' => 1000
            ],
            [
                'id' => generateId(),
                'text' => 'What is 25 × 4?',
                'answers' => ['80', '90', '100', '110'],
                'correct' => 2,
                'time' => 25,
                'points' => 1000
            ]
        ],
        'created_at' => date('Y-m-d H:i:s')
    ],
    [
        'id' => 'sample_geography_001',
        'title' => 'World Geography',
        'description' => 'Explore the world with these fun geography questions!',
        'questions' => [
            [
                'id' => generateId(),
                'text' => 'What is the capital of France?',
                'answers' => ['London', 'Berlin', 'Paris', 'Madrid'],
                'correct' => 2,
                'time' => 20,
                'points' => 1000
            ],
            [
                'id' => generateId(),
                'text' => 'Which continent is Egypt in?',
                'answers' => ['Asia', 'Europe', 'Africa', 'Australia'],
                'correct' => 2,
                'time' => 20,
                'points' => 1000
            ],
            [
                'id' => generateId(),
                'text' => 'What is the largest ocean on Earth?',
                'answers' => ['Atlantic', 'Indian', 'Arctic', 'Pacific'],
                'correct' => 3,
                'time' => 20,
                'points' => 1000
            ],
            [
                'id' => generateId(),
                'text' => 'Which country has the most people?',
                'answers' => ['USA', 'India', 'China', 'Indonesia'],
                'correct' => 2,
                'time' => 20,
                'points' => 1000
            ],
            [
                'id' => generateId(),
                'text' => 'What is the capital of Japan?',
                'answers' => ['Seoul', 'Beijing', 'Tokyo', 'Bangkok'],
                'correct' => 2,
                'time' => 20,
                'points' => 1000
            ]
        ],
        'created_at' => date('Y-m-d H:i:s')
    ],
    [
        'id' => 'sample_science_001',
        'title' => 'Fun Science Quiz',
        'description' => 'Test your science knowledge with these exciting questions!',
        'questions' => [
            [
                'id' => generateId(),
                'text' => 'What planet is known as the Red Planet?',
                'answers' => ['Venus', 'Mars', 'Jupiter', 'Saturn'],
                'correct' => 1,
                'time' => 20,
                'points' => 1000
            ],
            [
                'id' => generateId(),
                'text' => 'What gas do plants breathe in?',
                'answers' => ['Oxygen', 'Carbon Dioxide', 'Nitrogen', 'Hydrogen'],
                'correct' => 1,
                'time' => 20,
                'points' => 1000
            ],
            [
                'id' => generateId(),
                'text' => 'What is H2O commonly known as?',
                'answers' => ['Salt', 'Sugar', 'Water', 'Air'],
                'correct' => 2,
                'time' => 15,
                'points' => 1000
            ],
            [
                'id' => generateId(),
                'text' => 'How many legs does a spider have?',
                'answers' => ['6', '8', '10', '12'],
                'correct' => 1,
                'time' => 15,
                'points' => 1000
            ],
            [
                'id' => generateId(),
                'text' => 'What is the center of our solar system?',
                'answers' => ['Earth', 'Moon', 'Sun', 'Mars'],
                'correct' => 2,
                'time' => 15,
                'points' => 1000
            ]
        ],
        'created_at' => date('Y-m-d H:i:s')
    ]
];

$quizzes = getQuizzes();

foreach ($sampleQuizzes as $quiz) {
    $quizExists = false;
    foreach ($quizzes['quizzes'] as $existingQuiz) {
        if ($existingQuiz['id'] === $quiz['id']) {
            $quizExists = true;
            break;
        }
    }
    
    if (!$quizExists) {
        $quizzes['quizzes'][] = $quiz;
    }
}

if (writeQuizzes($quizzes)) {
    echo "✅ Sample quizzes loaded successfully!\n";
    echo "- Basic Math Challenge (5 questions)\n";
    echo "- World Geography (5 questions)\n";
    echo "- Fun Science Quiz (5 questions)\n";
} else {
    echo "❌ Failed to load sample quizzes!\n";
}
?>
