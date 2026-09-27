<?php
/**
 * generate_wordsearch_v2.php  (fixed 2026-09-17, original in backup_/generate_wordsearch_v201.php)
 *
 * Generates wordsearch activity JSONs for all class lesson HTML files.
 *
 * Fixes over the original version:
 *   - words longer than the grid: grid now grows to fit the longest selected
 *     word (min 12), so 13-17 letter keywords are actually placeable
 *   - placement is exhaustive (every start position tried in random order,
 *     longest words first, with full-restart retries) and VERIFIED before
 *     writing — a JSON can no longer list a word that is missing from its
 *     grid (the old version did that in ~40% of files, making those puzzles
 *     impossible to finish)
 *   - importantwords.txt: guarded with file_exists() instead of warning on
 *     a missing file; also looked up next to this script via __DIR__
 *   - paths anchored to the script location (works from any CWD)
 *   - output goes to admin/activities/wordsearch/ with the app naming
 *     scheme dimX-lesNN-wordsearch-XXX.json and admin/activities/index.json
 *     is updated (old version dumped files into the CWD with a
 *     dimX-lesNN-<12hex> id that the app's findActivityFile() cannot use)
 *   - removeAccents() now also maps ϊ Ϋ ϋ ΰ
 *
 * Requires the lesson HTML sources (HTML/Pliroforiki_XXX/lessons_seperated
 * folders) and importantwords.txt next to this script.
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

$baseDir = dirname(__DIR__); // wordboard root
$outputDir = $baseDir . '/admin/activities/wordsearch/';
$indexPath = $baseDir . '/admin/activities/index.json';

function removeAccents($text) {
    $map = [
        'Ά' => 'Α', 'ά' => 'α', 'Έ' => 'Ε', 'έ' => 'ε', 'Ή' => 'Η', 'ή' => 'η',
        'Ό' => 'Ο', 'ό' => 'ο', 'Ύ' => 'Υ', 'ύ' => 'υ', 'Ϊ' => 'Ι', 'ϊ' => 'ι',
        'ί' => 'ι', 'ΐ' => 'ι', 'Ϋ' => 'Υ', 'ϋ' => 'υ', 'ΰ' => 'υ',
        'Ώ' => 'Ω', 'ώ' => 'ω'
    ];
    return strtr($text, $map);
}

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

// Enumerates every possible starting position and tries them in random
// order — if the word fits anywhere on the grid, it gets placed.
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

function fillGrid(&$grid, $size) {
    for ($i = 0; $i < $size; $i++) {
        for ($j = 0; $j < $size; $j++) {
            if ($grid[$i][$j] === '') $grid[$i][$j] = getRandomGreekLetter();
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

function normalizeImportant($w) {
    $w = removeAccents(trim($w));
    $w = mb_strtoupper($w);
    $w = preg_replace('/[^Α-Ω]/u', '', $w);
    return $w;
}

// 3-char random suffix, same scheme as api/save_activity.php
function generateSuffix() {
    $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
    $suffix = '';
    for ($i = 0; $i < 3; $i++) $suffix .= $chars[mt_rand(0, strlen($chars) - 1)];
    return $suffix;
}

function extractKeyWords($html) {
    $words = [];
    $text = strip_tags($html);
    $text = removeAccents($text);
    $text = preg_replace('/[^α-ωΑ-Ω\s]/u', ' ', $text);
    $text = preg_replace('/\s+/', ' ', $text);
    $allWords = preg_split('/\s+/', trim($text), -1, PREG_SPLIT_NO_EMPTY);
    $excludeWords = ['ΜΑΘΗΜΑ', 'ΜΑΘΗΜΑΟ', 'ΜΑΘΗΜΑΤΑ', 'ΜΑΘΗΜΑΤΟΣ'];
    foreach ($allWords as $word) {
        $word = mb_strtoupper(trim($word)); // text is mixed case; uppercase before testing
        $len = mb_strlen($word);
        if ($len >= 4 && $len <= 15 && preg_match('/^[Α-Ω]+$/u', $word)) {
            if (!in_array($word, $excludeWords)) $words[$word] = $word;
        }
    }
    if (preg_match_all('/<b>([^<]+)<\/b>/u', $html, $matches)) {
        foreach ($matches[1] as $match) {
            $match = removeAccents($match);
            $match = preg_replace('/[^α-ωΑ-Ω]/u', '', $match);
            $match = mb_strtoupper($match);
            $len = mb_strlen($match);
            if ($len >= 3 && $len <= 15 && !in_array($match, $excludeWords)) $words[$match] = $match;
        }
    }
    return array_values($words);
}

function getLessonTitle($html, $num) {
    if (preg_match("/<title>[^-]*—[[:space:]]*Μάθημα[[:space:]]*{$num}:[[:space:]]*([^<]+)/u", $html, $m)) {
        return trim($m[1]);
    }
    if (preg_match("/Μάθημα[[:space:]]*{$num}[ο°]?[:]?[[:space:]]*([^<\\.\\-]+)/u", $html, $m)) {
        return trim($m[1]);
    }
    return "Μάθημα {$num}";
}

$classDirs = [
    'A' => $baseDir . '/HTML/Pliroforiki_A-Dimotikou_pdf-web_v6.0_PEDIO/lessons_seperated/',
    'B' => $baseDir . '/HTML/Pliroforiki_B-Dimotikou_pdf-web_v6.0_PEDIO/lessons_seperated/',
    'C' => $baseDir . '/HTML/Pliroforiki_C-Dimotikou_pdf-web_v.6.0_PEDIO/lessons_seperated/',
    'D' => $baseDir . '/HTML/Pliroforiki_D-dimotikou_light-web_v.5.0_PEDIO/lessons_seperated/',
    'E' => $baseDir . '/HTML/Pliroforiki_E-Dimotikou_vivlioMathiti-tetradioergasiwn_pdf-web_v6.0_PEDIO/lessons_seperated/',
    'ST' => $baseDir . '/HTML/Pliroforiki_ST-Dimotikou_pdf-web_v.7.0_PEDIO/lessons_seperated/'
];

function loadImportantWords($file) {
    if (!file_exists($file)) {
        echo "WARNING: {$file} not found - continuing without important-word overrides.\n";
        return [];
    }
    $lines = file($file);
    if (!is_array($lines)) {
        echo "WARNING: could not read {$file} - continuing without important-word overrides.\n";
        return [];
    }
    $keywords = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || !preg_match("/^(dim[A-Z]+-les\\d+)/", $line, $m)) continue;
        $parts = explode("|", $line);
        if (count($parts) >= 4) {
            $kw = trim($parts[2]);
            $kw = explode(",", $kw);
            $keywords[$m[1]] = trim($kw[0]);
        }
    }
    return $keywords;
}

function loadIndex($indexPath) {
    if (file_exists($indexPath)) {
        $index = json_decode(file_get_contents($indexPath), true);
        if (is_array($index)) return $index;
    }
    return [];
}

if (!is_dir($outputDir)) mkdir($outputDir, 0755, true);
$importantWords = loadImportantWords(__DIR__ . '/importantwords.txt');
$index = loadIndex($indexPath);

$total = 0; $skipped = 0;
foreach ($classDirs as $class => $dir) {
    echo "=== Processing Class {$class} ===\n";
    for ($i = 1; $i <= 30; $i++) {
        $file = $dir . 'lesson' . sprintf('%02d', $i) . '.html';
        if (file_exists($file)) {
            $html = file_get_contents($file);
            $title = getLessonTitle($html, $i);
            $words = extractKeyWords($html);

            $cleanTitle = removeAccents($title);
            $cleanTitle = preg_replace('/[^α-ωΑ-Ω\s]/u', '', $cleanTitle);
            $tag = mb_strtolower(trim(preg_replace('/\s+/', ' ', $cleanTitle)));
            if (mb_strlen($tag) > 20) $tag = mb_substr($tag, 0, 20);

            $lessonId = "dim{$class}-les" . sprintf('%02d', $i);
            $fullTitle = "{$title} - Αναζητηση Λεξεων";
            $tags = ["dim{$class}", "les" . sprintf('%02d', $i), $tag];

            if (isset($importantWords[$lessonId])) {
                $important = normalizeImportant($importantWords[$lessonId]);
                $selectedWords = $important !== '' ? [$important] : [];
            } else {
                $selectedWords = [];
            }

            if (count($selectedWords) < 8) {
                foreach ($words as $w) {
                    if (count($selectedWords) >= 8) break;
                    if (!in_array($w, $selectedWords)) $selectedWords[] = $w;
                }
            }
            if (count($selectedWords) < 6) {
                $selectedWords = array_merge($selectedWords, ['ΑΛΓΟΡΙΘΜΟΣ', 'ΠΡΟΒΛΗΜΑ', 'ΛΥΣΗ', 'ΕΝΤΟΛΗ']);
            }
            $selectedWords = array_values(array_unique($selectedWords));
            $selectedWords = array_slice($selectedWords, 0, 8);

            if (empty($selectedWords)) {
                echo "  SKIP lesson{$i}: no words to place\n";
                $skipped++;
                continue;
            }

            // Grid big enough for the longest word — words longer than the
            // grid can never be placed (the old fixed-12 bug).
            $maxLen = 0;
            foreach ($selectedWords as $w) $maxLen = max($maxLen, mb_strlen($w));
            $size = max(12, $maxLen);

            $grid = buildGrid($selectedWords, $size);
            if ($grid === null) {
                echo "  SKIP lesson{$i}: could not place all words in {$size}x{$size} grid\n";
                $skipped++;
                continue;
            }

            // Never write a JSON that lists a word missing from its grid.
            foreach ($selectedWords as $w) {
                if (!gridContainsWord($grid, $w)) {
                    echo "  SKIP lesson{$i}: verification failed for '{$w}'\n";
                    $grid = null;
                    break;
                }
            }
            if ($grid === null) { $skipped++; continue; }

            fillGrid($grid, $size);

            // App naming scheme: dimX-lesNN-wordsearch-XXX.json
            $id = $lessonId . '-wordsearch-' . generateSuffix();
            $attempt = 0;
            while (file_exists($outputDir . $id . '.json') && $attempt < 50) {
                $id = $lessonId . '-wordsearch-' . generateSuffix();
                $attempt++;
            }
            $filename = $id . '.json';

            $json = [
                'id' => $id,
                'title' => $fullTitle,
                'type' => 'wordsearch',
                'created_by' => 'admin',
                'created_date' => date('Y-m-d'),
                'data' => [
                    'grid' => $grid,
                    'words' => $selectedWords
                ],
                'tags' => $tags
            ];

            file_put_contents($outputDir . $filename, json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

            $index[$id] = [
                'title' => $fullTitle,
                'type' => 'wordsearch',
                'tags' => $tags,
                'created_by' => 'admin',
                'created_date' => date('Y-m-d')
            ];

            echo "  {$filename} ({$size}x{$size}): " . implode(',', $selectedWords) . "\n";
            $total++;
        }
    }
}

file_put_contents($indexPath, json_encode($index, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
echo "\n=== Total: {$total} files created, {$skipped} skipped, index.json updated ===\n";
