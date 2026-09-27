<?php
session_start();

$csvFile = __DIR__ . '/issue_report.csv';
$csvFile = '../dimBook_issue_report/issue_report.csv';
$categories = [
    'bug'               => 'Σφάλμα / Bug',
    'wrong-spelling'    => 'Λανθασμένη ορθογραφία',
    'meaningless'       => 'Ανούσιες εγγραφές',
    'not-educational'   => 'Μη εκπαιδευτικό',
    'meaningless-activity' => 'Ανούσια δραστηριότητα',
];

$errors = [];
$success = false;

function generateCaptcha() {
    $a = rand(1, 20);
    $b = rand(1, 20);
    $_SESSION['captcha_answer'] = $a + $b;
    return "$a + $b";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $url = trim($_POST['url'] ?? '');
    $category = $_POST['category'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $captcha = trim($_POST['captcha'] ?? '');

    if (empty($url)) {
        $errors[] = 'Παρακαλώ συμπληρώστε το URL της δραστηριότητας.';
    } elseif (!filter_var($url, FILTER_VALIDATE_URL)) {
        $errors[] = 'Παρακαλώ εισάγετε έγκυρο URL.';
    }

    if (!isset($categories[$category])) {
        $errors[] = 'Παρακαλώ επιλέξτε κατηγορία σφάλματος.';
    }

    if ((string)$captcha !== (string)($_SESSION['captcha_answer'] ?? '')) {
        $errors[] = 'Λανθασμένη απάντηση στη μαθηματική επαλήθευση.';
    }

    if (empty($errors)) {
        $csvUrl = str_replace(["\r", "\n"], '', $url);
        $csvCategory = $category;
        $csvDesc = str_replace(["\r", "\n"], ' ', $description);
        $csvDesc = str_replace('"', '""', $csvDesc);
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';

        $line = implode(',', [
            date('Y-m-d H:i:s'),
            '"' . $csvUrl . '"',
            '"' . $csvCategory . '"',
            '"' . $csvDesc . '"',
            '"' . $ip . '"',
        ]) . "\n";

        $exists = file_exists($csvFile);
        $fp = @fopen($csvFile, 'a');
        if ($fp) {
            if (!$exists) {
                fwrite($fp, "timestamp,activity_url,error_category,description,ip_address\n");
            }
            fwrite($fp, $line);
            fclose($fp);
            $success = true;
        } else {
            $errors[] = 'Σφάλμα εγγραφής στο αρχείο αναφοράς. Παρακαλώ δοκιμάστε ξανά.';
        }
    }

    $captchaExpr = generateCaptcha();
} else {
    $captchaExpr = generateCaptcha();
    $url = '';
    $category = '';
    $description = '';
}
?>
<!DOCTYPE html>
<html lang="el">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Αναφορά Προβλήματος</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:#f5f7fa;color:#333;line-height:1.6;min-height:100vh;display:flex;align-items:center;justify-content:center}
.wrap{width:100%;max-width:540px;padding:1.5rem}
.card{background:#fff;border-radius:12px;padding:2rem;box-shadow:0 2px 12px rgba(0,0,0,.08)}
h1{font-size:1.3rem;color:#1a237e;margin-bottom:.3rem}
.sub{font-size:.85rem;color:#888;margin-bottom:1.5rem}
.field{margin-bottom:1.2rem}
label{display:block;font-size:.85rem;font-weight:600;color:#444;margin-bottom:.35rem}
label .req{color:#e53935}
.input{width:100%;padding:.6rem .8rem;border:1px solid #ddd;border-radius:6px;font-size:.9rem;outline:none;transition:border .2s}
.input:focus{border-color:#1a237e}
textarea.input{resize:vertical;min-height:90px}
select.input{background:#fff}
.captcha-row{display:flex;gap:.6rem;align-items:center}
.captcha-q{background:#e8eaf6;padding:.6rem 1rem;border-radius:6px;font-size:1rem;font-weight:700;color:#1a237e;white-space:nowrap}
.captcha-row .input{width:100px;flex-shrink:0}
.help{font-size:.75rem;color:#999;margin-top:.25rem}
.btn{background:#1a237e;color:#fff;border:none;border-radius:6px;padding:.7rem 1.5rem;font-size:.9rem;font-weight:600;cursor:pointer;width:100%;transition:background .2s}
.btn:hover{background:#283593}
.error-box{background:#ffebee;color:#c62828;padding:.7rem 1rem;border-radius:6px;margin-bottom:1.2rem;font-size:.85rem}
.error-box ul{margin:0;padding-left:1.2rem}
.success-box{background:#e8f5e9;color:#2e7d32;padding:1.5rem;border-radius:6px;text-align:center}
.success-box .tick{font-size:2rem;margin-bottom:.5rem}
.success-box a{color:#1a237e}
.back-link{display:inline-block;margin-top:1rem;color:#1a237e;text-decoration:none;font-size:.85rem}
.back-link:hover{text-decoration:underline}
</style>
</head>
<body>
<div class="wrap">
<?php if ($success): ?>
<div class="card">
    <div class="success-box">
        <div class="tick">&#10004;</div>
        <strong>Η αναφορά σας υποβλήθηκε επιτυχώς.</strong>
        <p style="margin-top:.5rem;font-size:.85rem">Ευχαριστούμε για τη συμβολή σας!</p>
    </div>
    <a href="issue_report.php" class="back-link">&larr; Υποβολή νέας αναφοράς</a>
</div>
<?php else: ?>
<div class="card">
    <h1>Αναφορά Προβλήματος</h1>
    <p class="sub">Αναφέρετε ένα σφάλμα ή πρόβλημα σε μια δραστηριότητα</p>

    <?php if (!empty($errors)): ?>
    <div class="error-box">
        <ul>
        <?php foreach ($errors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <form method="post">
        <div class="field">
            <label for="url">URL δραστηριότητας <span class="req">*</span></label>
            <input type="url" id="url" name="url" class="input" placeholder="https://..." value="<?= htmlspecialchars($url) ?>" required>
        </div>

        <div class="field">
            <label for="category">Κατηγορία σφάλματος <span class="req">*</span></label>
            <select id="category" name="category" class="input" required>
                <option value="">-- Επιλέξτε --</option>
                <?php foreach ($categories as $key => $label): ?>
                <option value="<?= $key ?>" <?= $category === $key ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="field">
            <label for="description">Περιγραφή (προαιρετικά)</label>
            <textarea id="description" name="description" class="input" placeholder="Περιγράψτε το πρόβλημα..."><?= htmlspecialchars($description) ?></textarea>
        </div>

        <div class="field">
            <label>Μαθηματική επαλήθευση <span class="req">*</span></label>
            <div class="captcha-row">
                <span class="captcha-q"><?= htmlspecialchars($captchaExpr) ?> =</span>
                <input type="text" name="captcha" class="input" placeholder="?" required autocomplete="off">
            </div>
            <div class="help">Αντιστοιχίστε το αποτέλεσμα για αποφυγή αυτοματισμών</div>
        </div>

        <button type="submit" class="btn">Υποβολή αναφοράς</button>
    </form>

    <a href="index.php" class="back-link">&larr; Επιστροφή στις δραστηριότητες</a>
</div>
<?php endif; ?>
</div>
</body>
</html>
