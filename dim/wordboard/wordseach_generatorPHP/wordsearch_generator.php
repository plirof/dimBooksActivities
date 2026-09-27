<?php

function createEmptyGrid($size) {
    $grid = [];
    for ($i = 0; $i < $size; $i++) {
        $grid[$i] = array_fill(0, $size, '');
    }
    return $grid;
}

function getRandomGreekLetter() {
    $letters = preg_split('//u', 'ΑΒΓΔΕΖΗΘΙΚΛΜΝΞΟΠΡΣΤΥΦΧΨΩ', -1, PREG_SPLIT_NO_EMPTY);
    return $letters[array_rand($letters)];
}

function canPlaceWord($grid, $word, $row, $col, $dir, $size) {
    $len = mb_strlen($word);

    for ($i = 0; $i < $len; $i++) {
        $r = $row;
        $c = $col;

        switch ($dir) {
            case 'H': $c += $i; break;
            case 'V': $r += $i; break;
            case 'D': $r += $i; $c += $i; break;
        }

        if ($r >= $size || $c >= $size) return false;

        if ($grid[$r][$c] !== '' && $grid[$r][$c] !== mb_substr($word, $i, 1)) {
            return false;
        }
    }
    return true;
}

function placeWord(&$grid, $word, $size) {
    $directions = ['H', 'V', 'D'];
    $attempts = 100;

    while ($attempts--) {
        $dir = $directions[array_rand($directions)];
        $row = rand(0, $size - 1);
        $col = rand(0, $size - 1);

        if (canPlaceWord($grid, $word, $row, $col, $dir, $size)) {
            for ($i = 0; $i < mb_strlen($word); $i++) {
                $letter = mb_substr($word, $i, 1);

                switch ($dir) {
                    case 'H': $grid[$row][$col + $i] = $letter; break;
                    case 'V': $grid[$row + $i][$col] = $letter; break;
                    case 'D': $grid[$row + $i][$col + $i] = $letter; break;
                }
            }
            return true;
        }
    }
    return false;
}

function fillGrid(&$grid, $size) {
    for ($i = 0; $i < $size; $i++) {
        for ($j = 0; $j < $size; $j++) {
            if ($grid[$i][$j] === '') {
                $grid[$i][$j] = getRandomGreekLetter();
            }
        }
    }
}

function printGrid($grid) {
    echo "<pre>";
    foreach ($grid as $row) {
        echo implode(' ', $row) . "\n";
    }
    echo "</pre>";
}

// ====== ΧΡΗΣΗ ======

$words = ['ΜΗΛΟ', 'ΣΠΙΤΙ', 'ΘΑΛΑΣΣΑ', 'ΒΟΥΝΟ', 'ΗΛΙΟΣ'];

$size = 12;
$grid = createEmptyGrid($size);

foreach ($words as $word) {
    placeWord($grid, $word, $size);
}

fillGrid($grid, $size);
printGrid($grid);

?>