<?php
/**
 * wordserach3_fixgrids.php
 *
 * Regenerates a correct grid for every JSON in admin/activities/wordsearch/.
 * Each file keeps its own words (data.words), id, title, tags and dates —
 * only data.grid is rebuilt (plus a cleaned copy of data.words: accents
 * removed, uppercased, deduplicated).
 *
 * Fixes the generate_wordsearch_v2.php bugs where words were listed without
 * being placed in the grid (such puzzles can never be completed):
 *   - grid grows from 12 up to the longest word when a word cannot fit
 *   - placement is exhaustive: every start position is tried in random order,
 *     so if a word fits anywhere it WILL be placed
 *   - words are placed longest-first, with full-restart retries
 *   - every generated grid is verified (all words findable H/V/D) before
 *     the file is written; unfixable files are skipped, never corrupted
 *
 * Originals are backed up once into backup_/activities_wordsearch_orig/
 * (re-running the script never overwrites that first backup).
 *
 * Usage: php wordserach3_fixgrids.php   (from any directory)
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

$activitiesDir = __DIR__ . '/../admin/activities/wordsearch/';
$backupDir     = __DIR__ . '/../backup_/activities_wordsearch_orig/';

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

function normalizeWord($w) {
    $w = removeAccents(trim($w));
    $w = mb_strtoupper($w);
    // Keep Greek and Latin letters: most puzzles are Greek, but user-created
    // ones (gen-000-*) can have English words like PINEAPPLE.
    $w = preg_replace('/[^Α-ΩA-Z]/u', '', $w);
    return $w;
}

function createEmptyGrid($size) {
    $grid = [];
    for ($i = 0; $i < $size; $i++) {
        $grid[$i] = array_fill(0, $size, '');
    }
    return $grid;
}

function getRandomLetter($alphabet) {
    $letters = preg_split('//u', $alphabet, -1, PREG_SPLIT_NO_EMPTY);
    return $letters[array_rand($letters)];
}

function canPlaceWord($grid, $word, $row, $col, $dir, $size) {
    $len = mb_strlen($word);
    for ($i = 0; $i < $len; $i++) {
        $r = $row; $c = $col;
        switch ($dir) {
            case 'H': $c += $i; break;
            case 'V': $r += $i; break;
            case 'D': $r += $i; $c += $i; break;
        }
        if ($r >= $size || $c >= $size) return false;
        if ($grid[$r][$c] !== '' && $grid[$r][$c] !== mb_substr($word, $i, 1)) return false;
    }
    return true;
}

// Unlike the v2 random-position version, this enumerates every possible
// starting position and tries them in random order — if the word can be
// placed anywhere on the grid, it gets placed.
function placeWord(&$grid, $word, $size) {
    $len = mb_strlen($word);
    if ($len > $size) return false;
    $candidates = [];
    for ($r = 0; $r < $size; $r++)
        for ($c = 0; $c + $len <= $size; $c++)
            $candidates[] = ['H', $r, $c];
    for ($c = 0; $c < $size; $c++)
        for ($r = 0; $r + $len <= $size; $r++)
            $candidates[] = ['V', $r, $c];
    for ($r = 0; $r + $len <= $size; $r++)
        for ($c = 0; $c + $len <= $size; $c++)
            $candidates[] = ['D', $r, $c];
    shuffle($candidates);
    foreach ($candidates as $cand) {
        list($dir, $row, $col) = $cand;
        if (canPlaceWord($grid, $word, $row, $col, $dir, $size)) {
            for ($i = 0; $i < $len; $i++) {
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

function buildGrid($words, $size) {
    // Longest words first: they have the fewest valid positions.
    $sorted = $words;
    usort($sorted, function ($a, $b) { return mb_strlen($b) - mb_strlen($a); });
    for ($restart = 0; $restart < 50; $restart++) {
        $grid = createEmptyGrid($size);
        $allPlaced = true;
        foreach ($sorted as $word) {
            if (!placeWord($grid, $word, $size)) { $allPlaced = false; break; }
        }
        if ($allPlaced) return $grid;
    }
    return null;
}

function fillGrid(&$grid, $size, $alphabet) {
    for ($i = 0; $i < $size; $i++) {
        for ($j = 0; $j < $size; $j++) {
            if ($grid[$i][$j] === '') $grid[$i][$j] = getRandomLetter($alphabet);
        }
    }
}

function gridContainsWord($grid, $word) {
    $n = count($grid);
    $len = mb_strlen($word);
    if ($len > $n) return false;
    for ($r = 0; $r < $n; $r++) {
        for ($c = 0; $c < $n; $c++) {
            foreach ([['H', 0, 1], ['V', 1, 0], ['D', 1, 1]] as $d) {
                $ok = true;
                for ($i = 0; $i < $len; $i++) {
                    $rr = $r + $i * $d[1];
                    $cc = $c + $i * $d[2];
                    if ($rr >= $n || $cc >= $n || $grid[$rr][$cc] !== mb_substr($word, $i, 1)) { $ok = false; break; }
                }
                if ($ok) return true;
            }
        }
    }
    return false;
}

function verifyGrid($grid, $words) {
    $missing = [];
    foreach ($words as $w) {
        if (!gridContainsWord($grid, $w)) $missing[] = $w;
    }
    return $missing;
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

// ===== 2. Regenerate every grid =====

$files = glob($activitiesDir . '*.json');
sort($files);

$fixed = 0; $grown = 0; $skipped = 0;
foreach ($files as $file) {
    $json = json_decode(file_get_contents($file), true);
    if (!is_array($json) || !isset($json['data']['grid']) || !isset($json['data']['words'])) {
        echo "SKIP (invalid JSON structure): " . basename($file) . "\n";
        $skipped++;
        continue;
    }

    // Same words, cleaned: no accents, uppercase, deduplicated.
    $words = [];
    foreach ($json['data']['words'] as $w) {
        $w = normalizeWord($w);
        if (mb_strlen($w) >= 2 && !in_array($w, $words)) $words[] = $w;
    }
    if (empty($words)) {
        echo "SKIP (no usable words): " . basename($file) . "\n";
        $skipped++;
        continue;
    }

    $maxLen = 0;
    foreach ($words as $w) $maxLen = max($maxLen, mb_strlen($w));

    // Fill letters follow the words' alphabet (Greek normally, Latin for
    // user-created English puzzles, both if mixed).
    $hasGreek = false; $hasLatin = false;
    foreach ($words as $w) {
        if (preg_match('/[Α-Ω]/u', $w)) $hasGreek = true;
        if (preg_match('/[A-Z]/', $w)) $hasLatin = true;
    }
    $fillAlphabet = ($hasGreek ? 'ΑΒΓΔΕΖΗΘΙΚΛΜΝΞΟΠΡΣΤΥΦΧΨΩ' : '') . ($hasLatin ? 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' : '');
    if ($fillAlphabet === '') $fillAlphabet = 'ΑΒΓΔΕΖΗΘΙΚΛΜΝΞΟΠΡΣΤΥΦΧΨΩ';

    // Original grids can be non-square (user-created): take the larger side.
    $origSize = count($json['data']['grid']);
    foreach ($json['data']['grid'] as $row) $origSize = max($origSize, count($row));
    $size = max(12, $origSize, $maxLen);

    $grid = null;
    while ($size <= 22 && $grid === null) {
        $grid = buildGrid($words, $size);
        if ($grid === null) $size += 2; // safety valve, should never trigger
    }
    if ($grid === null) {
        echo "SKIP (could not place words even at {$size}x{$size}): " . basename($file) . "\n";
        $skipped++;
        continue;
    }

    // Verify BEFORE filling: every listed word must really be in the grid.
    $missing = verifyGrid($grid, $words);
    if (!empty($missing)) {
        echo "SKIP (verify failed: " . implode(',', $missing) . "): " . basename($file) . "\n";
        $skipped++;
        continue;
    }

    fillGrid($grid, $size, $fillAlphabet);

    $json['data']['grid'] = $grid;
    $json['data']['words'] = $words;

    $tmp = $file . '.tmp';
    file_put_contents($tmp, json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    rename($tmp, $file);

    $fixed++;
    $note = '';
    if ($size > $origSize) { $note = " (grid grown {$origSize}->{$size} for long words)"; $grown++; }
    echo "OK " . basename($file) . ": " . count($words) . " words, {$size}x{$size}{$note}\n";
}

// ===== 3. Final check across all files =====

$broken = 0;
foreach ($files as $file) {
    $json = json_decode(file_get_contents($file), true);
    if (!is_array($json) || !isset($json['data']['grid']) || !isset($json['data']['words'])) continue;
    $m = verifyGrid($json['data']['grid'], $json['data']['words']);
    if (!empty($m)) {
        $broken++;
        echo "STILL BROKEN " . basename($file) . ": " . implode(',', $m) . "\n";
    }
}

echo "\n=== Files: " . count($files) . " | regenerated: {$fixed} (grid grown: {$grown}) | skipped: {$skipped} | still broken: {$broken} ===\n";
if ($broken === 0 && $skipped === 0) echo "All wordsearch puzzles are now solvable.\n";
