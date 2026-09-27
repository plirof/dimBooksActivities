<?php
/**
 * crossword_fixgrids.php
 *
 * Regenerates a proper crossword layout for every JSON in
 * admin/activities/crossword/. Each file keeps its own clues and answers
 * (data.words[].clue / .answer), id, title, tags and dates — the grid
 * (data.grid) is rebuilt from scratch and the words get new consistent
 * row / col / number / direction values.
 *
 * Fixes the old generated crosswords where:
 *   - words never crossed each other (146/181 files had zero crossings)
 *   - grid letters did not match the words' declared answers/positions
 *     (the game checks answers against the grid letters, so those files
 *     were unsolvable)
 *   - one file used string rows (breaks the JS renderer for Greek text)
 *
 * How the layout is built (per file, several attempts, best kept):
 *   - longest word placed first (across, centred)
 *   - every other word is placed crossing an already-placed word at a
 *     matching letter, with standard crossword adjacency rules (no
 *     side-by-side touching, no same-direction merging, one cell gap)
 *   - words that cannot cross anything are laid out below the puzzle,
 *     fully separated (still solvable and clean, just not crossed)
 *   - attempts are scored: most crossings, then most compact grid
 *   - numbers are re-assigned in standard crossword order (words sharing
 *     a start cell share a number)
 *
 * Every written file is verified (each answer appears in the grid at its
 * position/direction) before it replaces the original; unfixable files
 * are skipped, never corrupted.
 *
 * Originals are backed up once into backup_/activities_crossword_orig/.
 *
 * Usage: php crossword_fixgrids.php   (from any directory)
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

$activitiesDir = __DIR__ . '/../admin/activities/crossword/';
$backupDir     = __DIR__ . '/../backup_/activities_crossword_orig/';

if (!is_dir($activitiesDir)) {
    die("Activities folder not found: {$activitiesDir}\n");
}

function removeAccents($text) {
    $map = [
        'Ά' => 'Α', 'ά' => 'α', 'Έ' => 'Ε', 'έ' => 'ε', 'Ή' => 'Η', 'ή' => 'η',
        'Ό' => 'Ο', 'ό' => 'ο', 'Ύ' => 'Υ', 'ύ' => 'υ', 'Ϊ' => 'Ι', 'ϊ' => 'ι',
        'ί' => 'ι', 'ΐ' => 'ι', 'Ϋ' => 'Υ', 'ϋ' => 'υ', 'ΰ' => 'υ',
        'Ώ' => 'Ω', 'ώ' => 'ω'
    ];
    return strtr($text, $map);
}

function normalizeAnswer($w) {
    $w = removeAccents(trim($w));
    $w = mb_strtoupper($w);
    // Keep Greek and Latin letters; grid cells hold one letter each.
    $w = preg_replace('/[^Α-ΩA-Z]/u', '', $w);
    return $w;
}

// ===== crossword layout engine =====

define('CANVAS', 40); // working canvas, cropped to bounding box at the end

function emptyCanvas() {
    return array_fill(0, CANVAS, array_fill(0, CANVAS, null));
}

// cellEmpty(): null = empty cell
function cellEmpty($canvas, $r, $c) {
    if ($r < 0 || $c < 0 || $r >= CANVAS || $c >= CANVAS) return true; // off-canvas counts as empty
    return $canvas[$r][$c] === null;
}

function cellLetter($canvas, $r, $c) {
    if ($r < 0 || $c < 0 || $r >= CANVAS || $c >= CANVAS) return null;
    return $canvas[$r][$c];
}

/**
 * Can $word be placed at ($row,$col) in $dir ('across'|'down')?
 * Returns the number of shared letters, or -1 if invalid.
 * Crossing placements require $needOverlap=true (at least one shared letter,
 * and every shared cell must belong to a perpendicular word so two
 * same-direction words never merge into one long run). Floater placements
 * use $needOverlap=false and must not touch anything.
 */
function canPlace($canvas, $word, $row, $col, $dir, $needOverlap) {
    $len = mb_strlen($word);
    if ($row < 0 || $col < 0 || $row + ($dir === 'down' ? $len : 0) > CANVAS || $col + ($dir === 'across' ? $len : 0) > CANVAS) return -1;

    // One empty cell before the start and after the end.
    if ($dir === 'across') {
        if (!cellEmpty($canvas, $row, $col - 1) || !cellEmpty($canvas, $row, $col + $len)) return -1;
    } else {
        if (!cellEmpty($canvas, $row - 1, $col) || !cellEmpty($canvas, $row + $len, $col)) return -1;
    }

    $overlaps = 0;
    for ($i = 0; $i < $len; $i++) {
        $r = $dir === 'across' ? $row : $row + $i;
        $c = $dir === 'across' ? $col + $i : $col;
        $existing = cellLetter($canvas, $r, $c);

        if ($existing !== null) {
            if ($existing !== mb_substr($word, $i, 1)) return -1; // letter conflict
            // The overlap must be a PERPENDICULAR word passing through:
            // a vertical continuation for an across placement (and vice versa).
            if ($dir === 'across') {
                if (cellEmpty($canvas, $r - 1, $c) && cellEmpty($canvas, $r + 1, $c)) return -1;
            } else {
                if (cellEmpty($canvas, $r, $c - 1) && cellEmpty($canvas, $r, $c + 1)) return -1;
            }
            $overlaps++;
        } else {
            // Empty cell: no occupied cell may touch it sideways.
            if ($dir === 'across') {
                if (!cellEmpty($canvas, $r - 1, $c) || !cellEmpty($canvas, $r + 1, $c)) return -1;
            } else {
                if (!cellEmpty($canvas, $r, $c - 1) || !cellEmpty($canvas, $r, $c + 1)) return -1;
            }
        }
    }

    if ($needOverlap && $overlaps === 0) return -1;
    if (!$needOverlap && $overlaps > 0) return -1;
    return $overlaps;
}

function doPlace(&$canvas, $word, $row, $col, $dir) {
    $len = mb_strlen($word);
    for ($i = 0; $i < $len; $i++) {
        $r = $dir === 'across' ? $row : $row + $i;
        $c = $dir === 'across' ? $col + $i : $col;
        $canvas[$r][$c] = mb_substr($word, $i, 1);
    }
}

function bbox($canvas) {
    $minR = CANVAS; $minC = CANVAS; $maxR = -1; $maxC = -1;
    for ($r = 0; $r < CANVAS; $r++) {
        for ($c = 0; $c < CANVAS; $c++) {
            if ($canvas[$r][$c] !== null) {
                $minR = min($minR, $r); $minC = min($minC, $c);
                $maxR = max($maxR, $r); $maxC = max($maxC, $c);
            }
        }
    }
    return [$minR, $minC, $maxR, $maxC];
}

/**
 * Try to build one full layout. Returns
 * ['grid'=>..., 'placed'=>[['word'=>..,'row'=>..,'col'=>..,'dir'=>..],..],
 *  'crossings'=>n, 'floaters'=>n] or null on failure.
 * $words: [['answer'=>.. , ...], ...] sorted longest-first by caller.
 */
function attemptLayout($words, $shuffle) {
    $canvas = emptyCanvas();
    $placed = [];
    $crossings = 0;

    $first = $words[0];
    $len = mb_strlen($first['answer']);
    $row = (int)(CANVAS / 2);
    $col = (int)(CANVAS / 2) - (int)($len / 2);
    doPlace($canvas, $first['answer'], $row, $col, 'across');
    $placed[] = ['word' => $first, 'row' => $row, 'col' => $col, 'dir' => 'across'];

    $rest = array_slice($words, 1);
    $uncrossed = [];

    foreach ($rest as $w) {
        $len = mb_strlen($w['answer']);
        $candidates = [];
        // Enumerate every placement that crosses an existing letter.
        list($minR, $minC, $maxR, $maxC) = bbox($canvas);
        for ($r = $minR; $r <= $maxR; $r++) {
            for ($c = $minC; $c <= $maxC; $c++) {
                $letter = $canvas[$r][$c];
                if ($letter === null) continue;
                for ($i = 0; $i < $len; $i++) {
                    if (mb_substr($w['answer'], $i, 1) !== $letter) continue;
                    // across: word passes horizontally over (r,c) at offset i
                    $ov = canPlace($canvas, $w['answer'], $r, $c - $i, 'across', true);
                    if ($ov >= 0) $candidates[] = [$r, $c - $i, 'across', $ov];
                    // down: word passes vertically over (r,c) at offset i
                    $ov = canPlace($canvas, $w['answer'], $r - $i, $c, 'down', true);
                    if ($ov >= 0) $candidates[] = [$r - $i, $c, 'down', $ov];
                }
            }
        }

        if ($candidates) {
            if ($shuffle) shuffle($candidates);
            // Prefer the most crossings, then the placement closest to the
            // current centre (compact grid).
            $best = null; $bestScore = PHP_INT_MIN;
            $centreR = ($minR + $maxR) / 2; $centreC = ($minC + $maxC) / 2;
            foreach ($candidates as $cand) {
                $score = $cand[3] * 1000 - (abs($cand[0] - $centreR) + abs($cand[1] - $centreC));
                if ($score > $bestScore) { $bestScore = $score; $best = $cand; }
            }
            doPlace($canvas, $w['answer'], $best[0], $best[1], $best[2]);
            $placed[] = ['word' => $w, 'row' => $best[0], 'col' => $best[1], 'dir' => $best[2]];
            $crossings += $best[3];
        } else {
            $uncrossed[] = $w;
        }
    }

    $floaterCount = count($uncrossed);

    // Words that could not join the main cluster get their own cluster,
    // built recursively on a fresh canvas (so they can cross EACH OTHER),
    // pasted just below the main puzzle, left-aligned with it.
    if ($uncrossed) {
        $sub = attemptLayout($uncrossed, $shuffle);
        if ($sub === null) return null;
        list($minR0, $minC0, $maxR0, $maxC0) = bbox($canvas);
        $offR = $maxR0 + 2;
        $subH = count($sub['grid']);
        $subW = count($sub['grid'][0]);
        $offC = max(0, min($minC0, CANVAS - $subW));
        if ($offR + $subH > CANVAS) return null; // canvas full — let the caller retry
        foreach ($sub['grid'] as $sr => $row) {
            foreach ($row as $sc => $ch) {
                if ($ch !== '#') $canvas[$offR + $sr][$offC + $sc] = $ch;
            }
        }
        foreach ($sub['placed'] as $p) {
            $placed[] = ['word' => $p['word'], 'row' => $p['row'] + $offR, 'col' => $p['col'] + $offC, 'dir' => $p['dir']];
        }
        $crossings += $sub['crossings'];
        $floaterCount = $sub['floaters'];
    }

    // Crop to bounding box and build the final grid ('#' = black cell).
    list($minR, $minC, $maxR, $maxC) = bbox($canvas);
    $grid = [];
    for ($r = $minR; $r <= $maxR; $r++) {
        $rowArr = [];
        for ($c = $minC; $c <= $maxC; $c++) {
            $rowArr[] = $canvas[$r][$c] === null ? '#' : $canvas[$r][$c];
        }
        $grid[] = $rowArr;
    }
    foreach ($placed as &$p) {
        $p['row'] -= $minR;
        $p['col'] -= $minC;
    }
    unset($p);

    return [
        'grid' => $grid,
        'placed' => $placed,
        'crossings' => $crossings,
        'floaters' => $floaterCount,
        'area' => count($grid) * count($grid[0])
    ];
}

/**
 * Standard crossword numbering: scan start cells row by row; words that
 * start at the same cell (one across + one down) share its number.
 */
function renumber($placed) {
    $starts = [];
    foreach ($placed as $p) $starts[$p['row'] . ',' . $p['col']] = [$p['row'], $p['col']];
    usort($starts, function ($a, $b) { return $a[0] === $b[0] ? $a[1] - $b[1] : $a[0] - $b[0]; });
    $numbers = [];
    $n = 0;
    foreach ($starts as $s) { $n++; $numbers[$s[0] . ',' . $s[1]] = $n; }
    foreach ($placed as &$p) $p['number'] = $numbers[$p['row'] . ',' . $p['col']];
    unset($p);
    return $placed;
}

// Verify: every answer must appear in the grid at its row/col/direction.
function verifyLayout($grid, $words) {
    $R = count($grid); $C = count($grid[0]);
    foreach ($words as $w) {
        $len = mb_strlen($w['answer']);
        for ($i = 0; $i < $len; $i++) {
            $r = $w['row'] + ($w['direction'] === 'down' ? $i : 0);
            $c = $w['col'] + ($w['direction'] === 'across' ? $i : 0);
            if ($r >= $R || $c >= $C || $grid[$r][$c] !== mb_substr($w['answer'], $i, 1)) return $w['answer'];
        }
    }
    return null;
}

// ===== 1. One-time backup of the originals =====

if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
    $n = 0;
    foreach (glob($activitiesDir . '*.json') as $f) {
        copy($f, $backupDir . basename($f));
        $n++;
    }
    echo "Backed up {$n} original files to {$backupDir}\n\n";
} else {
    echo "Backup already exists, keeping it: {$backupDir}\n\n";
}

// ===== 2. Rebuild every crossword =====

$files = glob($activitiesDir . '*.json');
sort($files);

$fixed = 0; $skipped = 0; $uncrossed = 0;
foreach ($files as $file) {
    $json = json_decode(file_get_contents($file), true);
    if (!is_array($json) || !isset($json['data']['words']) || !is_array($json['data']['words']) || count($json['data']['words']) === 0) {
        echo "SKIP (no data.words to rebuild from): " . basename($file) . "\n";
        $skipped++;
        continue;
    }

    // Normalize answers; keep clues; drop unusable/duplicate entries.
    $words = [];
    foreach ($json['data']['words'] as $w) {
        if (!isset($w['answer']) || !isset($w['clue'])) continue;
        $answer = normalizeAnswer($w['answer']);
        if (mb_strlen($answer) < 2) continue;
        $dup = false;
        foreach ($words as $x) if ($x['answer'] === $answer) $dup = true;
        if ($dup) continue;
        $words[] = ['answer' => $answer, 'clue' => trim($w['clue'])];
    }
    if (count($words) === 0) {
        echo "SKIP (no usable words): " . basename($file) . "\n";
        $skipped++;
        continue;
    }

    // Longest first; several attempts keep the layout with the most
    // crossings (then the most compact one).
    usort($words, function ($a, $b) { return mb_strlen($b['answer']) - mb_strlen($a['answer']); });

    $best = null;
    for ($attempt = 0; $attempt < 300; $attempt++) {
        $layout = attemptLayout($words, $attempt > 0);
        if ($layout === null) continue;
        $score = $layout['crossings'] * 1000 - $layout['floaters'] * 500 - $layout['area'];
        $bestScore = $best === null ? PHP_INT_MIN : $best['crossings'] * 1000 - $best['floaters'] * 500 - $best['area'];
        if ($score > $bestScore) $best = $layout;
        if ($best['floaters'] === 0) break; // every word crossed at least once
    }
    if ($best === null) {
        echo "SKIP (could not build layout): " . basename($file) . "\n";
        $skipped++;
        continue;
    }

    $placed = renumber($best['placed']);
    // across clues first within each number, standard presentation order
    usort($placed, function ($a, $b) {
        if ($a['number'] === $b['number']) return ($a['dir'] === 'across') ? -1 : 1;
        return $a['number'] - $b['number'];
    });

    $outWords = [];
    foreach ($placed as $p) {
        $outWords[] = [
            'number' => $p['number'],
            'direction' => $p['dir'],
            'clue' => $p['word']['clue'],
            'answer' => $p['word']['answer'],
            'row' => $p['row'],
            'col' => $p['col']
        ];
    }

    $fail = verifyLayout($best['grid'], $outWords);
    if ($fail !== null) {
        echo "SKIP (verify failed for '{$fail}'): " . basename($file) . "\n";
        $skipped++;
        continue;
    }

    $json['data']['grid'] = $best['grid'];
    $json['data']['words'] = $outWords;

    $tmp = $file . '.tmp';
    file_put_contents($tmp, json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    rename($tmp, $file);

    $fixed++;
    $R = count($best['grid']); $C = count($best['grid'][0]);
    $note = $best['floaters'] > 0 ? ", {$best['floaters']} uncrossed" : '';
    if ($best['crossings'] === 0) $uncrossed++;
    echo "OK " . basename($file) . ": " . count($outWords) . " words, {$best['crossings']} crossings, {$R}x{$C}{$note}\n";
}

// ===== 3. Final check across all files =====

$bad = 0;
foreach ($files as $file) {
    $json = json_decode(file_get_contents($file), true);
    if (!is_array($json) || !isset($json['data']['grid'], $json['data']['words'])) continue;
    $grid = $json['data']['grid'];
    $ok = true;
    foreach ($grid as $row) if (is_string($row)) $ok = false; // renderer needs arrays
    if ($ok) {
        $fail = verifyLayout($grid, $json['data']['words']);
        if ($fail !== null) $ok = false;
    }
    if (!$ok) {
        $bad++;
        echo "STILL BROKEN " . basename($file) . "\n";
    }
}

echo "\n=== Files: " . count($files) . " | rebuilt: {$fixed} (all-uncrossed: {$uncrossed}) | skipped: {$skipped} | still broken: {$bad} ===\n";
