<?php
// Configurable defaults appended to every generated link
$speed = 0.9;
$mode  = 'auto';

// Class map: key (used in URL ?c=) => [display name, directory prefix]
$classes = [
    'A'  => ["Α' Δημοτικού", 'TPE-dimA-PEDIO'],
    'B'  => ["Β' Δημοτικού", 'TPE-dimB-PEDIO'],
    'C'  => ["Γ' Δημοτικού", 'TPE-dimC-PEDIO'],
    'D'  => ["Δ' Δημοτικού", 'TPE-dimD-PEDIO'],
    'E'  => ["Ε' Δημοτικού", 'TPE-dimE-PEDIO'],
    'ST' => ["ΣΤ' Δημοτικού", 'TPE-dimST-PEDIO'],
];

// Count lessons per class so we can build the lesson filter dropdown
$maxLesson = 0;
foreach ($classes as $key => $info) {
    $dir = __DIR__ . DIRECTORY_SEPARATOR . $info[1] . DIRECTORY_SEPARATOR . 'presentations';
    if (!is_dir($dir)) continue;
    $found = glob($dir . DIRECTORY_SEPARATOR . 'lesson??.html');
    $cnt = 0;
    foreach ($found as $f) {
        if (preg_match('/lesson(\d+)\.html$/', $f, $m)) $cnt = max($cnt, (int)$m[1]);
    }
    if ($cnt > $maxLesson) $maxLesson = $cnt;
}

// Selection from URL parameters (default: empty = all)
$selC = isset($_GET['c']) ? strtoupper(trim($_GET['c'])) : '';
$selL = isset($_GET['l']) && $_GET['l'] !== '' ? (int)$_GET['l'] : 0;

// Collect lesson files per class
$items = [];
foreach ($classes as $key => $info) {
    $dir = __DIR__ . DIRECTORY_SEPARATOR . $info[1] . DIRECTORY_SEPARATOR . 'presentations';
    if (!is_dir($dir)) continue;
    foreach (glob($dir . DIRECTORY_SEPARATOR . 'lesson??.html') as $f) {
        if (!preg_match('/lesson(\d+)\.html$/', $f, $m)) continue;
        $items[] = [
            'class'  => $key,
            'name'   => $info[0],
            'lesson' => (int)$m[1],
            'url'    => $info[1] . '/presentations/lesson' . $m[1] . '.html',
        ];
    }
}

// Filtering
$filtered = array_filter($items, function ($it) use ($selC, $selL) {
    if ($selC !== '' && $it['class'] !== $selC) return false;
    if ($selL && $it['lesson'] !== $selL) return false;
    return true;
});
usort($filtered, function ($a, $b) {
    return strcmp($a['class'], $b['class']) ?: $a['lesson'] - $b['lesson'];
});

// Build a URL with the given query params preserved
function filterUrl($overrides) {
    $q = $_GET;
    foreach ($overrides as $k => $v) {
        if ($v === '' || $v === null) unset($q[$k]);
        else $q[$k] = $v;
    }
    return '?' . http_build_query($q);
}
?>
<!DOCTYPE html>
<html lang="el">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Παρουσιάσεις TTS</title>
<style>
    body { font-family: system-ui, sans-serif; margin: 2rem; background: #f5f5f5; color: #222; }
    h1 { font-size: 1.5rem; }
    .filters { display: flex; gap: 1rem; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; }
    .filters label { font-weight: 600; }
    select { padding: .4rem .6rem; font-size: 1rem; }
    a { color: #1a237e; text-decoration: none; }
    a:hover { text-decoration: underline; }
    .cls { font-weight: 700; }
    .count { margin-bottom: 1rem; color: #555; }
    .class-section {
        background: #fff;
        border: 1px solid #bbb;
        border-radius: .6rem;
        overflow: hidden;
        box-shadow: 0 1px 4px rgba(0,0,0,.1);
        margin-bottom: 1.5rem;
        max-width: 950px;
    }
    .class-section .header {
        background: #eef;
        font-weight: 700;
        padding: .6rem .8rem;
        border-bottom: 2px solid #1a237e;
    }
    .class-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: .7rem;
        padding: .8rem;
    }
    .lesson-card {
        display: flex;
        align-items: baseline;
        gap: .5rem;
        padding: .5rem .6rem;
        border: 2px solid #555;
        border-radius: .45rem;
        background: #fff;
        overflow: hidden;
        white-space: nowrap;
    }
    .lesson-card .cls { flex: 0 0 auto; }
    .lesson-card .lesson-num { color: #555; flex: 0 0 auto; }
    .lesson-card a {
        color: #1a237e;
        font-size: .85rem;
        overflow: hidden;
        text-overflow: ellipsis;
        min-width: 0;
    }
    @media (max-width: 480px) {
        body { margin: 1rem; }
        .class-grid { grid-template-columns: 1fr; }
        .lesson-card { display: flex; flex-wrap: wrap; }
        .lesson-card a { font-size: 1rem; }
    }
</style>
</head>
<body>
<h1>Παρουσιάσεις TTS ανά Τάξη</h1>

<form class="filters" method="get">
    <label>Τάξη:
        <select name="c" onchange="this.form.submit()">
            <option value="">Όλες</option>
            <?php foreach ($classes as $key => $info): ?>
                <option value="<?= htmlspecialchars($key) ?>" <?= $selC === $key ? 'selected' : '' ?>><?= htmlspecialchars($info[0]) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Μάθημα:
        <select name="l" onchange="this.form.submit()">
            <option value="">Όλα</option>
            <?php for ($n = 1; $n <= $maxLesson; $n++): ?>
                <option value="<?= $n ?>" <?= $selL === $n ? 'selected' : '' ?>><?= $n ?></option>
            <?php endfor; ?>
        </select>
    </label>
    <a href="<?= htmlspecialchars(filterUrl(['c' => '', 'l' => ''])) ?>">Καθαρισμός</a>
</form>

<div class="count"><?= count($filtered) ?> παρουσίαση(-εις) βρέθηκαν.</div>

<?php if (!$filtered): ?>
    <p>Δεν βρέθηκαν παρουσιάσεις.</p>
<?php endif; ?>
<?php
// Group items by class
$byClass = [];
foreach ($filtered as $it) {
    $byClass[$it['class']][] = $it;
}
$classKeys = array_keys($byClass);
// Sort by original order (A, B, C, D, E, ST)
$order = ['A', 'B', 'C', 'D', 'E', 'ST'];
usort($classKeys, function($a, $b) use ($order) {
    $pa = array_search($a, $order);
    $pb = array_search($b, $order);
    return ($pa !== false && $pb !== false) ? $pa - $pb : strcmp($a, $b);
});

foreach ($classKeys as $ck) {
    $clsItems = $byClass[$ck];
    $clsName = $classes[$ck][0] ?? $ck;
    ?>
    <div class="class-section">
        <div class="header"><?= htmlspecialchars($clsName) ?></div>
        <div class="class-grid">
        <?php foreach ($clsItems as $it): ?>
            <div class="lesson-card">
                <span class="cls"><?= htmlspecialchars($it['class']) ?></span>
                <span class="lesson-num"><?= $it['lesson'] ?></span>
                <a href="<?= htmlspecialchars($it['url']) ?>?speed=<?= urlencode($speed) ?>&mode=<?= urlencode($mode) ?>">lesson<?= $it['lesson'] ?>.html</a>
            </div>
        <?php endforeach; ?>
        </div>
    </div>
    <?php
}
?>

</body>
</html>
