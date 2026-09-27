<?php
$fontsDir = __DIR__ . '/fonts/';
$fontFiles = glob($fontsDir . '*.ttf');

$emojis = [
    ['←', 'U+2190', 'Leftwards Arrow'],
    ['↑', 'U+2191', 'Upwards Arrow'],
    ['→', 'U+2192', 'Rightwards Arrow'],
    ['↓', 'U+2193', 'Downwards Arrow'],
    ['⌨️', 'U+2328 FE0F', 'Keyboard'],
    ['⏱️', 'U+23F1 FE0F', 'Stopwatch'],
    ['■', 'U+25A0', 'Black Square'],
    ['▲', 'U+25B2', 'Black Up Triangle'],
    ['▶', 'U+25B6', 'Play Button'],
    ['◆', 'U+25C6', 'Black Diamond'],
    ['◇', 'U+25C7', 'White Diamond'],
    ['●', 'U+25CF', 'Black Circle'],
    ['★', 'U+2605', 'Black Star'],
    ['♦', 'U+2666', 'Diamond Suit'],
    ['⚠️', 'U+26A0 FE0F', 'Warning Sign'],
    ['✅', 'U+2705', 'Check Mark Button'],
    ['✓', 'U+2713', 'Check Mark'],
    ['✔', 'U+2714', 'Heavy Check Mark'],
    ['✗', 'U+2717', 'Ballot X'],
    ['✘', 'U+2718', 'Heavy Ballot X'],
    ['❌', 'U+274C', 'Cross Mark'],
    ['❤️', 'U+2764 FE0F', 'Red Heart'],
    ['⬇', 'U+2B07', 'Down Arrow'],
    ['⭐', 'U+2B50', 'Star'],
    ['🌟', 'U+1F31F', 'Glowing Star'],
    ['🌵', 'U+1F335', 'Cactus'],
    ['🍕', 'U+1F355', 'Pizza'],
    ['🎈', 'U+1F388', 'Balloon'],
    ['🎉', 'U+1F389', 'Party Popper'],
    ['🎡', 'U+1F3A1', 'Ferris Wheel'],
    ['🎥', 'U+1F3A5', 'Movie Camera'],
    ['🎮', 'U+1F3AE', 'Game Controller'],
    ['🎯', 'U+1F3AF', 'Direct Hit'],
    ['🏆', 'U+1F3C6', 'Trophy'],
    ['🏎️', 'U+1F3CE FE0F', 'Racing Car'],
    ['🏠', 'U+1F3E0', 'House'],
    ['👁️', 'U+1F441 FE0F', 'Eye'],
    ['👍', 'U+1F44D', 'Thumbs Up'],
    ['💎', 'U+1F48E', 'Gem Stone'],
    ['💡', 'U+1F4A1', 'Light Bulb'],
    ['💣', 'U+1F4A3', 'Bomb'],
    ['💪', 'U+1F4AA', 'Flexed Biceps'],
    ['💻', 'U+1F4BB', 'Computer'],
    ['💾', 'U+1F4BE', 'Floppy Disk'],
    ['💿', 'U+1F4BF', 'Optical Disc'],
    ['📅', 'U+1F4C5', 'Calendar'],
    ['📊', 'U+1F4CA', 'Bar Chart'],
    ['📋', 'U+1F4CB', 'Clipboard'],
    ['📚', 'U+1F4DA', 'Books'],
    ['📠', 'U+1F4E0', 'Fax Machine'],
    ['📢', 'U+1F4E2', 'Loudspeaker'],
    ['📦', 'U+1F4E6', 'Package'],
    ['📱', 'U+1F4F1', 'Mobile Phone'],
    ['📷', 'U+1F4F7', 'Camera'],
    ['📺', 'U+1F4FA', 'Television'],
    ['🔄', 'U+1F504', 'Anticlockwise Arrows'],
    ['🔋', 'U+1F50B', 'Battery'],
    ['🔌', 'U+1F50C', 'Electric Plug'],
    ['🔍', 'U+1F50D', 'Magnifying Glass'],
    ['🔐', 'U+1F510', 'Closed Lock'],
    ['🔗', 'U+1F517', 'Link'],
    ['🔢', 'U+1F522', 'Input Numbers'],
    ['🔴', 'U+1F534', 'Red Circle'],
    ['🔵', 'U+1F535', 'Blue Circle'],
    ['🖤', 'U+1F5A4', 'Black Heart'],
    ['🖥️', 'U+1F5A5 FE0F', 'Desktop Computer'],
    ['🖨️', 'U+1F5A8 FE0F', 'Printer'],
    ['🖱️', 'U+1F5B1 FE0F', 'Mouse'],
    ['🖲️', 'U+1F5B2 FE0F', 'Trackball'],
    ['🗑️', 'U+1F5D1 FE0F', 'Wastebasket'],
    ['🗺️', 'U+1F5FA FE0F', 'World Map'],
    ['😊', 'U+1F60A', 'Smiling Face'],
    ['😐', 'U+1F610', 'Neutral Face'],
    ['😢', 'U+1F622', 'Crying Face'],
    ['😤', 'U+1F624', 'Triumph Face'],
    ['😭', 'U+1F62D', 'Loudly Crying'],
    ['😰', 'U+1F630', 'Cold Sweat'],
    ['🙈', 'U+1F648', 'See-No-Evil'],
    ['🚀', 'U+1F680', 'Rocket'],
    ['🚕', 'U+1F695', 'Taxi'],
    ['🚗', 'U+1F697', 'Automobile'],
    ['🚧', 'U+1F6A7', 'Construction'],
    ['🚪', 'U+1F6AA', 'Door'],
    ['🟠', 'U+1F7E0', 'Orange Circle'],
    ['🟡', 'U+1F7E1', 'Yellow Circle'],
    ['🟢', 'U+1F7E2', 'Green Circle'],
    ['🤖', 'U+1F916', 'Robot'],
    ['🧩', 'U+1F9E9', 'Puzzle Piece'],
    ['🧮', 'U+1F9EE', 'Abacus'],
    ['🧱', 'U+1F9F1', 'Brick'],
    ['🧺', 'U+1F9FA', 'Basket'],
];

$canvasEmojis = ['⌨️','🖥️','🖱️','🖨️','💾','🔌','📷','🎮','🔋','💿','🤖','🧱','📦','🚧','🌵','🎯','🎥','💎','🏎️','🏠'];

$jsFontNames = array();
$jsFontFileNames = array();
$cssFontFaces = '';
$summaryCards = '';
$tableHeaders = '';

for ($i = 0; $i < count($fontFiles); $i++) {
    $basename = basename($fontFiles[$i]);
    $fontName = 'TestFont' . $i;
    $relative = 'fonts/' . rawurlencode($basename);
    $fileSize = filesize($fontFiles[$i]);
    $sizeStr = $fileSize > 1048576 ? round($fileSize / 1048576, 1) . ' MB' : round($fileSize / 1024) . ' KB';
    $shortName = strlen($basename) > 20 ? substr($basename, 0, 18) . '...' : $basename;

    $jsFontNames[] = $fontName;
    $jsFontFileNames[] = $basename;

    $cssFontFaces .= "@font-face {\n    font-family: '{$fontName}';\n    src: url('{$relative}') format('truetype');\n}\n";

    $summaryCards .= '<div class="font-card pending" id="card-' . $i . '">'
        . '<div class="fname">' . htmlspecialchars($basename) . '</div>'
        . '<div class="fsize">' . $sizeStr . '</div>'
        . '<div class="fstatus"><span class="pending-label" id="status-' . $i . '">&#8987; Checking...</span></div>'
        . '</div>' . "\n";

    $tableHeaders .= '<th>' . htmlspecialchars($shortName) . '<br><span class="col-name" id="th-status-' . $i . '"></span></th>' . "\n";
}

$tableRows = '';
foreach ($emojis as $e) {
    $row = '<tr>'
        . '<td><span style="font-size:1em">' . $e[0] . '</span> <span style="font-size:.7em;color:#888">' . $e[1] . ' ' . $e[2] . '</span></td>'
        . '<td class="default-cell" data-emoji="' . htmlspecialchars($e[0]) . '">' . $e[0] . '</td>';
    for ($i = 0; $i < count($fontFiles); $i++) {
        $fontName = 'TestFont' . $i;
        $row .= '<td class="font-cell font-col" style="--font-family: \'' . $fontName . '\',\'Segoe UI Emoji\',\'Apple Color Emoji\',\'Noto Color Emoji\',sans-serif" data-emoji="' . htmlspecialchars($e[0]) . '" data-font-index="' . $i . '">' . $e[0] . '</td>';
    }
    $row .= '</tr>';
    $tableRows .= $row . "\n";
}

$fontCountJS = json_encode(count($fontFiles));
$fontNamesJS = json_encode($jsFontNames);
$fontFileNamesJS = json_encode($jsFontFileNames);
$canvasEmojisJS = json_encode($canvasEmojis);
$allEmojisJS = json_encode(array_column($emojis, 0));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Emoji Font Checks</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;background:#f0f2f5;color:#333;padding:20px}
h1{text-align:center;color:#1a73e8;margin-bottom:5px;font-size:1.8em}
h2{color:#555;margin:20px 0 10px;font-size:1.3em;border-bottom:2px solid #1a73e8;padding-bottom:5px}
h3{color:#666;margin:15px 0 8px;font-size:1.1em}
.desc{text-align:center;color:#888;margin-bottom:20px;font-size:.95em}

.summary-box{display:flex;flex-wrap:wrap;gap:12px;margin:15px 0}
.font-card{flex:1;min-width:220px;background:#fff;border-radius:12px;padding:15px;box-shadow:0 2px 8px rgba(0,0,0,.1);border-left:4px solid #ccc}
.font-card.pass{border-left-color:#4caf50}
.font-card.fail{border-left-color:#f44336}
.font-card.pending{border-left-color:#ff9800}
.font-card .fname{font-weight:700;font-size:1em;margin-bottom:4px;word-break:break-all}
.font-card .fstatus{font-size:.85em}
.font-card .fsize{font-size:.8em;color:#999}
.font-card .pass-label{color:#4caf50;font-weight:600}
.font-card .fail-label{color:#f44336;font-weight:600}
.font-card .pending-label{color:#ff9800;font-weight:600}

.legend{display:flex;gap:20px;margin:10px 0;font-size:.85em;color:#666;flex-wrap:wrap}
.legend span{display:flex;align-items:center;gap:4px}
.legend .swatch{width:16px;height:16px;border-radius:3px;border:1px solid #ccc}

.controls{margin:15px 0;display:flex;flex-wrap:wrap;gap:10px;align-items:center}
.controls button{padding:8px 18px;border:none;border-radius:8px;cursor:pointer;font-size:.95em;font-weight:600;transition:transform .1s}
.controls button:hover{transform:scale(1.03)}
.btn-test{background:#1a73e8;color:#fff}
.btn-canvas{background:#7b1fa2;color:#fff}
.btn-recheck{background:#ff9800;color:#fff}
.btn-canvas-all{background:#333;color:#fff}

.table-wrap{overflow-x:auto;margin:10px 0;background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.08)}
table{border-collapse:collapse;width:100%;min-width:600px}
th{background:#1a73e8;color:#fff;padding:10px 8px;font-size:.85em;position:sticky;top:0;z-index:1;text-align:center}
th:first-child{text-align:left;padding-left:12px}
td{padding:8px;text-align:center;border-bottom:1px solid #eee;font-size:1.6em;vertical-align:middle}
td:first-child{text-align:left;font-size:.85em;padding-left:12px;white-space:nowrap}
tr:hover{background:#f5f8ff}
.font-col{font-family:var(--font-family, sans-serif) !important}
.col-name{font-size:.75em !important;font-weight:600;color:#555}

.canvas-section{margin:20px 0;background:#fff;border-radius:12px;padding:20px;box-shadow:0 2px 8px rgba(0,0,0,.08)}
canvas{border:1px solid #ddd;border-radius:8px;display:block;margin:10px 0;max-width:100%}
.canvas-results{display:flex;flex-wrap:wrap;gap:15px;margin-top:15px}
.canvas-result{min-width:200px;padding:10px;background:#f9f9f9;border-radius:8px;border:1px solid #eee}
.canvas-result .cr-title{font-weight:600;font-size:.9em;margin-bottom:5px}
.canvas-result .cr-status{font-size:.85em}

.hidden{display:none}

@media(max-width:768px){
    body{padding:10px}
    td{font-size:1.3em;padding:6px 4px}
    th{padding:8px 4px;font-size:.75em}
    .font-card{min-width:160px}
}
</style>
<style>
<?php echo $cssFontFaces; ?>
</style>
</head>
<body>
<h1>Emoji Font Checker</h1>
<p class="desc">Tests which locally-hosted emoji fonts render correctly in your browser. Checks <?php echo count($emojis); ?> emoji characters across <?php echo count($fontFiles); ?> font files.</p>

<div class="legend">
    <span><span class="swatch" style="background:#fff"></span> = Empty rectangle = font does NOT support this emoji</span>
    <span><span class="swatch" style="background:#4caf50;color:#fff;text-align:center;font-size:10px;line-height:16px">&#10003;</span> = Visible icon = font supports this emoji</span>
</div>

<h2>1. Font Loading Status</h2>
<div class="controls">
    <button class="btn-recheck" onclick="checkFontLoading()">&#8635; Re-check Fonts</button>
</div>
<div class="summary-box" id="summaryBox">
<?php echo $summaryCards; ?>
</div>

<h2>2. Emoji Rendering Comparison</h2>
<div class="controls">
    <button class="btn-test" onclick="runVisualTest()">&#128269; Run Visual Detection</button>
    <span style="font-size:.85em;color:#888">Compares each font cell against the default column to detect blank/missing rendering.</span>
</div>
<div id="visualResults" class="hidden" style="margin:10px 0;padding:10px;background:#fff;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,.08)"></div>

<div class="table-wrap">
<table id="emojiTable">
<thead>
<tr>
    <th>Emoji / Name</th>
    <th>Default<br><span class="col-name">(no custom font)</span></th>
<?php echo $tableHeaders; ?>
</tr>
</thead>
<tbody>
<?php echo $tableRows; ?>
</tbody>
</table>
</div>

<h2>3. Canvas Rendering Test</h2>
<p style="font-size:.9em;color:#666;margin-bottom:10px">Tests emoji rendering on HTML5 Canvas (used by runner-game.html and similar canvas-based activities).</p>
<div class="controls">
    <button class="btn-canvas" onclick="runCanvasTest()">&#127912; Test Canvas (first 5 emojis per font)</button>
    <button class="btn-canvas-all" onclick="runCanvasTestAll()">&#127912; Test Canvas (all emojis)</button>
</div>
<div class="canvas-section" id="canvasSection">
    <canvas id="testCanvas" width="700" height="80"></canvas>
    <div class="canvas-results" id="canvasResults"></div>
</div>

<script>
var fontCount = <?php echo $fontCountJS; ?>;
var fontNames = <?php echo $fontNamesJS; ?>;
var fontFileNames = <?php echo $fontFileNamesJS; ?>;
var fontUrls = [<?php for ($i = 0; $i < count($fontFiles); $i++) echo "'" . 'fonts/' . rawurlencode(basename($fontFiles[$i])) . "'" . ($i < count($fontFiles)-1 ? ',' : ''); ?>];
var canvasEmojis = <?php echo $canvasEmojisJS; ?>;
var allEmojis = <?php echo $allEmojisJS; ?>;

function checkFontLoading() {
    var promises = fontNames.map(function(fn, i) {
        return document.fonts.load('48px "' + fn + '"').then(function(loaded) {
            var card = document.getElementById('card-' + i);
            var status = document.getElementById('status-' + i);
            if (loaded.length > 0) {
                card.className = 'font-card pass';
                status.innerHTML = '<span class="pass-label">&#10003; Loaded</span>';
                document.getElementById('th-status-' + i).innerHTML = '<span style="color:#4caf50">&#10003;</span>';
            } else {
                return tryFontFaceAPI(i);
            }
        }).catch(function() {
            return tryFontFaceAPI(i);
        });
    });
    return Promise.all(promises);
}

function tryFontFaceAPI(i) {
    var card = document.getElementById('card-' + i);
    var status = document.getElementById('status-' + i);
    var face = new FontFace(fontNames[i], 'url("' + fontUrls[i] + '")', {});
    return face.load().then(function(loaded) {
        document.fonts.add(loaded);
        card.className = 'font-card pass';
        status.innerHTML = '<span class="pass-label">&#10003; Loaded (via FontFace API)</span>';
        document.getElementById('th-status-' + i).innerHTML = '<span style="color:#4caf50">&#10003;</span>';
    }).catch(function(err) {
        card.className = 'font-card fail';
        var errMsg = err.message || err.toString();
        if (errMsg.length > 80) errMsg = errMsg.substring(0, 77) + '...';
        status.innerHTML = '<span class="fail-label">&#10007; Failed</span><br><span style="font-size:.75em;color:#999">' + errMsg + '</span>';
        document.getElementById('th-status-' + i).innerHTML = '<span style="color:#f44336">&#10007;</span>';
    });
}

function getRenderFingerprint(el) {
    try {
        var cvs = document.createElement('canvas');
        cvs.width = 48;
        cvs.height = 48;
        var ctx = cvs.getContext('2d');
        var style = window.getComputedStyle(el);
        ctx.font = style.font;
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(el.textContent.trim(), 24, 24);
        var data = ctx.getImageData(0, 0, 48, 48).data;
        var nonEmpty = 0;
        for (var i = 3; i < data.length; i += 4) {
            if (data[i] > 10) nonEmpty++;
        }
        return nonEmpty;
    } catch (e) {
        return -1;
    }
}

function runVisualTest() {
    var rows = document.querySelectorAll('#emojiTable tbody tr');
    var fontResults = {};
    for (var i = 0; i < fontCount; i++) fontResults[i] = {pass: 0, fail: 0, total: 0};

    for (var r = 0; r < rows.length; r++) {
        var row = rows[r];
        var fontCells = row.querySelectorAll('.font-cell');
        for (var c = 0; c < fontCells.length; c++) {
            var cell = fontCells[c];
            var fi = parseInt(cell.dataset.fontIndex);
            fontResults[fi].total++;
            var cellFP = getRenderFingerprint(cell);
            if (cellFP > 5) {
                fontResults[fi].pass++;
                cell.style.backgroundColor = '#e8f5e9';
            } else {
                fontResults[fi].fail++;
                cell.style.backgroundColor = '#ffebee';
            }
        }
    }

    var html = '<h3>Visual Detection Results</h3>';
    for (var i = 0; i < fontCount; i++) {
        var res = fontResults[i];
        var pct = res.total > 0 ? Math.round(res.pass / res.total * 100) : 0;
        var color = pct >= 90 ? '#4caf50' : pct >= 50 ? '#ff9800' : '#f44336';
        html += '<div style="margin:8px 0;padding:8px;background:#f9f9f9;border-radius:6px;border-left:4px solid ' + color + '">';
        html += '<strong>' + fontFileNames[i] + '</strong>: ';
        html += '<span style="color:' + color + ';font-weight:700">' + pct + '%</span>';
        html += ' (' + res.pass + '/' + res.total + ' emojis rendered)';
        html += '</div>';
    }
    html += '<p style="font-size:.8em;color:#999;margin-top:8px">Green cells = emoji rendered visibly. Red cells = empty/blank rendering. Results depend on browser emoji support + font compatibility.</p>';
    var container = document.getElementById('visualResults');
    container.innerHTML = html;
    container.classList.remove('hidden');
}

function runCanvasTest() {
    _runCanvas(canvasEmojis.slice(0, 5));
}

function runCanvasTestAll() {
    _runCanvas(canvasEmojis);
}

function _runCanvas(emojis) {
    var resultsDiv = document.getElementById('canvasResults');
    resultsDiv.innerHTML = '';

    var allTestFonts = [];
    allTestFonts.push({name: 'Default (sans-serif)', family: 'sans-serif'});
    for (var i = 0; i < fontCount; i++) {
        allTestFonts.push({name: fontFileNames[i], family: "'" + fontNames[i] + "', sans-serif"});
    }

    for (var t = 0; t < allTestFonts.length; t++) {
        var tf = allTestFonts[t];
        var wrapper = document.createElement('div');
        wrapper.className = 'canvas-result';
        wrapper.innerHTML = '<div class="cr-title">' + tf.name + '</div>';
        var cvs = document.createElement('canvas');
        var cols = emojis.length;
        cvs.width = Math.max(cols * 48, 200);
        cvs.height = 56;
        cvs.style.maxWidth = '100%';
        var ctx = cvs.getContext('2d', { willReadFrequently: true });
        ctx.fillStyle = '#fff';
        ctx.fillRect(0, 0, cvs.width, cvs.height);
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';

        var rendered = 0;
        for (var idx = 0; idx < emojis.length; idx++) {
            ctx.font = '32px ' + tf.family;
            ctx.fillStyle = '#333';
            ctx.fillText(emojis[idx], idx * 48 + 24, 28);

            var imgData = ctx.getImageData(idx * 48, 0, 48, 56).data;
            var nonEmpty = 0;
            for (var p = 3; p < imgData.length; p += 4) {
                if (imgData[p] > 10) nonEmpty++;
            }
            if (nonEmpty > 5) rendered++;
        }

        var pct = emojis.length > 0 ? Math.round(rendered / emojis.length * 100) : 0;
        var color = pct >= 90 ? '#4caf50' : pct >= 50 ? '#ff9800' : '#f44336';

        var statusDiv = document.createElement('div');
        statusDiv.className = 'cr-status';
        statusDiv.innerHTML = '<span style="color:' + color + ';font-weight:700">' + pct + '%</span> (' + rendered + '/' + emojis.length + ' rendered)';

        wrapper.appendChild(cvs);
        wrapper.appendChild(statusDiv);
        resultsDiv.appendChild(wrapper);
    }
}

window.addEventListener('load', function() {
    checkFontLoading();
    setTimeout(function() {
        runCanvasTest();
    }, 500);
});
</script>
</body>
</html>
