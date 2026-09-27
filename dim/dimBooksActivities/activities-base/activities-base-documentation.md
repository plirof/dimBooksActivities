# Activities-Base Documentation

## Επισκόπηση

Ο φάκελος `activities-base/` περιέχει **8 self-contained HTML skeletons** (σκευετοί) για τη δημιουργία εκπαιδευτικών δραστηριοτήτων HTML/JS στα Ελληνικά. Κάθε σκελετός είναι ένα πλήρες, αυτόνομο HTML αρχείο (HTML + CSS + JS) που δεν απαιτεί εξωτερικές βιβλιοθήκες ή CDN.

**Βασική αρχή:** Κάθε σκελετός φορτώνει τα δεδομένα του από ένα **εξωτερικό JSON αρχείο** μέσω query parameter `?data=path/to/file.json`. Αυτό σημαίνει ότι ο ίδιος σκελετός επαναχρησιμοποιείται για δεκάδες διαφορετικές δραστηριότητες — αλλάζει μόνο το JSON αρχείο με το περιεχόμενο.

---

## Πώς λειτουργεί

### Κλήση σκελετού

```
quiz.html?data=../TPE-dimC-PEDIO/activities/dimC-les01-quiz-XXX.json
```

- Το HTML διαβάζει το `?data=` query parameter
- Κάνει `fetch()` στο JSON αρχείο
- Δημιουργεί δυναμικά το UI βάσει των δεδομένων

### Αρχεία ανά τάξη

Τα JSON δεδομένων μπαίνουν στον φάκελο `activities/` μέσα στον φάκελο κάθε τάξης:

```
TPE-dimC-PEDIO/
├── activities/
│   ├── dimC-les01-quiz-XXX.json
│   ├── dimC-les01-memory-XXX.json
│   ├── dimC-les01-drag-order-XXX.json
│   ├── ...
```

---

## Κατάλογος Σκελετών

### 1. `quiz.html` — Κουίζ (Πολλαπλής επιλογής & Σωστό/Λάθος)

**Περιγραφή:** Ερωτήσεις μία-μια, με feedback ανά ερώτηση, τελικό σκορ.

**Δομή JSON:**
```json
{
  "title": "Τίτλος μαθήματος",
  "subtitle": "Υπότιτλος / οδηγία",
  "questions": [
    {
      "text": "Κείμενο ερώτησης;",
      "type": "tf",
      "answer": true,
      "explanation": "Επεξήγηση (προαιρετικό)"
    },
    {
      "text": "Ερώτηση πολλαπλής επιλογής;",
      "type": "mc",
      "options": ["Επιλογή Α", "Επιλογή Β", "Επιλογή Γ", "Επιλογή Δ"],
      "correctIndex": 1,
      "explanation": "Επεξήγηση (προαιρετικό)"
    }
  ]
}
```

**Πεδία:**
- `title` (string) — Τίτλος δραστηριότητας
- `subtitle` (string, προαιρετικό) — Υπότιτλος
- `questions` (array) — Πίνακας ερωτήσεων
  - `text` (string) — Κείμενο ερώτησης
  - `type` (string) — `"tf"` για Σωστό/Λάθος ή `"mc"` για πολλαπλής επιλογής
  - `answer` (boolean) — Μόνο για type `"tf"`: `true` ή `false`
  - `options` (array of strings) — Μόνο για type `"mc"`: οι επιλογές
  - `correctIndex` (number) — Μόνο για type `"mc"`: index σωστής απάντησης (0-based)
  - `explanation` (string, προαιρετικό) — Επεξήγηση μετά την απάντηση

**Sample:** `sample_quiz.json`

---

### 2. `drag-categories.html` — Drag σε Κατηγορίες

**Περιγραφή:** Ο χρήστης σέρνει στοιχεία (items) και τα τοποθετεί σε κατηγορίες (columns/boxes). Έλεγχος στο τέλος.

**Δομή JSON:**
```json
{
  "title": "Τίτλος",
  "subtitle": "Οδηγία",
  "categories": [
    { "id": "cat1", "name": "Όνομα Κατηγορίας", "color": "#4CAF50" },
    { "id": "cat2", "name": "Άλλη Κατηγορία", "color": "#2196F3" }
  ],
  "items": [
    { "id": "item1", "text": "⌨️ Πληκτρολόγιο", "category": "cat1" },
    { "id": "item2", "text": "🖥️ Οθόνη", "category": "cat2" }
  ]
}
```

**Πεδία:**
- `categories` (array) — Κατηγορίες με `id`, `name`, `color` (hex color)
- `items` (array) — Στοιχεία με `id`, `text` (υποστηρίζει emoji), `category` (αντιστοιχεί σε category id)
- Τα items shuffled αυτόματα

**Sample:** `sample_drag-categories.json`

---

### 3. `drag-order.html` — Σειροθέτηση Βημάτων

**Περιγραφή:** Ο χρήστης σέρνει βήματα πάνω-κάτω για να τα βάλει στη σωστή σειρά.

**Δομή JSON:**
```json
{
  "title": "Τίτλος",
  "subtitle": "Οδηγία",
  "steps": [
    { "text": "Πρώτο βήμα" },
    { "text": "Δεύτερο βήμα" },
    { "text": "Τρίτο βήμα" }
  ],
  "successMessage": "Μπράβο! (προαιρετικό)"
}
```

**Πεδία:**
- `steps` (array) — Βήματα στη **σωστή σειρά**. Shuffled αυτόματα.
  - `text` (string) — Κείμενο βήματος
- `successMessage` (string, προαιρετικό) — Μήνυμα επιτυχίας

**Sample:** `sample_drag-order.json`

---

### 4. `memory.html` — Memory / Ταίριασμα Ζευγαριών

**Περιγραφή:** Κάρτες ανάποδες — ο χρήστης αναποδά δύο κάθε φορά για να βρει ζευγάρια.

**Δομή JSON:**
```json
{
  "title": "Τίτλος",
  "subtitle": "Οδηγία",
  "columns": 4,
  "pairs": [
    { "cardA": "⌨️ Πληκτρολόγιο", "cardB": "Γράφω γράμματα" },
    { "cardA": "🖱️ Ποντίκι", "cardB": "Επιλέγω στην οθόνη" }
  ]
}
```

**Πεδία:**
- `columns` (number, προαιρετικό, default: 4) — Στήλες grid
- `pairs` (array) — Ζευγάρια καρτών
  - `cardA` (string) — Κείμενο πρώτης κάρτας (υποστηρίζει emoji)
  - `cardB` (string) — Κείμενο δεύτερης κάρτας
- Οι κάρτες shuffled αυτόματα

**Sample:** `sample_memory.json`

---

### 5. `grid-game.html` — Πλέγμα & Εντολές Κίνησης

**Περιγραφή:** Grid με χαρακτήρα, εμπόδια, στόχο. Ο χρήστης δίνει εντολές (↑↓←→) και πατά "Εκτέλεση" για να δει αν φτάνει στον στόχο.

**Δομή JSON:**
```json
{
  "title": "Τίτλος",
  "subtitle": "Οδηγία",
  "gridSize": 6,
  "start": { "row": 5, "col": 0 },
  "goal": { "row": 0, "col": 5 },
  "obstacles": [
    { "row": 2, "col": 1 },
    { "row": 2, "col": 2 }
  ]
}
```

**Πεδία:**
- `gridSize` (number, default: 8) — Μέγεθος grid (NxN)
- `start` (object) — Αφετηρία: `{row, col}` (0-based, row=0 είναι πάνω)
- `goal` (object) — Στόχος: `{row, col}`
- `obstacles` (array) — Εμπόδια: `[{row, col}]`

**Sample:** `sample_grid-game.json`

---

### 6. `falling-letters.html` — Πληκτρολόγηση (Falling)

**Περιγραφή:** Γράμματα/λέξεις πέφτουν — ο χρήστης πληκτρολογεί πριν φτάσουν κάτω. 3 ζωές.

**Δομή JSON:**
```json
{
  "title": "Τίτλος",
  "subtitle": "Οδηγία",
  "instructions": "Κείμενο οδηγιών στην αρχική οθόνη",
  "speed": 2,
  "lives": 3,
  "spawnInterval": 1800,
  "items": [
    { "text": "Α", "key": "α" },
    { "text": "Β", "key": "β" }
  ]
}
```

**Πεδία:**
- `instructions` (string, προαιρετικό) — Οδηγίες στην αρχική οθόνη
- `speed` (number, default: 2) — Ταχύτητα πτώσης (pixels/frame)
- `lives` (number, default: 3) — Αριθμός ζωών
- `spawnInterval` (number, default: 2000) — Χιλιοστά ανά νέο αντικείμενο
- `items` (array) — Αντικείμενα που πέφτουν
  - `text` (string) — Κείμενο που εμφανίζεται
  - `key` (string) — Τι πληκτρολογεί ο χρήστης (case-insensitive)

**Sample:** `sample_falling-letters.json`

---

### 7. `click-game.html` — Κλικ σε Στόχους

**Περιγραφή:** Στόχοι εμφανίζονται τυχαία — ο χρήστης κάνει κλικ πριν εξαφανιστούν. Χρονομέτρηση.

**Δομή JSON:**
```json
{
  "title": "Τίτλος",
  "subtitle": "Οδηγία",
  "instructions": "Οδηγίες (προαιρετικό)",
  "duration": 30,
  "maxTargets": 3,
  "targetLife": 2000,
  "targets": [
    { "label": "🍎", "color": "#ffcdd2" },
    { "label": "🍊", "color": "#ffe0b2" }
  ]
}
```

**Πεδία:**
- `duration` (number, default: 30) — Δευτερόλεπτα παιχνιδιού
- `maxTargets` (number, default: 3) — Μέγιστοι στόχοι ταυτόχρονα
- `targetLife` (number, default: 2000) — Χιλιοστά ζωής στόχου
- `targets` (array) — Διαθέσιμοι στόχοι
  - `label` (string) — Κείμενο/emoji στόχου
  - `color` (string) — Χρώμα φόντου (hex)

**Sample:** `sample_click-game.json`

---

### 8. `canvas-pixels.html` — Pixel Art / Χρωματισμός Grid

**Περιγραφή:** Grid pixels με παλέτα χρωμάτων — ο χρήστης χρωματίζει. Αν υπάρχει `solution`, προστίθενται κουμπιά ελέγχου & εμφάνισης λύσης.

**Δομή JSON:**
```json
{
  "title": "Τίτλος",
  "subtitle": "Οδηγία",
  "rows": 8,
  "cols": 8,
  "palette": ["#FFFFFF", "#000000", "#FF0000", "#FFFF00", "#8B4513"],
  "solution": [
    [0,0,1,1,1,1,0,0],
    [0,1,2,2,2,2,1,0],
    [1,2,2,1,1,2,2,1]
  ]
}
```

**Πεδία:**
- `rows` (number, default: 8) — Γραμμές grid
- `cols` (number, default: 8) — Στήλες grid
- `palette` (array of strings) — Hex χρώματα. Index 0 = άσπρο (σβήσιμο).
- `solution` (array of arrays, προαιρετικό) — 2D array με indexes στο `palette`. `solution[row][col]` = index στο palette array. Αν υπάρχει, εμφανίζεται κουμπί "Έλεγχος" & "Λύση".

**Sample:** `sample_canvas-pixels.json`

---

## 9. `arcade-game.html` — Arcade Παιχνίδι (Δύτης & Αστερίες)

**Περιγραφή:** Canvas-based παιχνίδι όπου ένας δύτης μαζεύει ⭐ αστερίες και αποφεύγει 🐡 αλιγκάτορ. Υποστηρίζει ρυθμιστές (sliders), επίπεδα (levels) και εμφάνιση συνθήκης τέλους.

**Δομή JSON:**
```json
{
  "title": "Τίτλος",
  "subtitle": "Οδηγία",
  "winScore": 10,
  "maxLives": 3,
  "initialStars": 4,
  "initialPuffers": 1,
  "maxPuffers": 2,
  "pufferSpeed": 1.5,
  "canvasW": 600,
  "canvasH": 400,
  "hasSliders": false,
  "sliderMin": 3,
  "sliderMax": 20,
  "hasLevels": false,
  "levelThemes": [["#0d47a1","#1565c0","#26c6da"]],
  "hasConditionBox": false,
  "conditionText": ""
}
```

**Πεδία:**
- `winScore` (number) — Πόντοι για νίκη
- `maxLives` (number) — Αρχικές ζωές
- `initialStars` (number) — Αρχικός αριθμός αστεριών
- `initialPuffers` (number) — Αρχικός αριθμός αλιγκάτορ
- `maxPuffers` (number) — Μέγιστος αριθμός αλιγκάτορ
- `pufferSpeed` (number) — Βασική ταχύτητα αλιγκάτορ
- `canvasW/canvasH` (number) — Μέγεθος καμβά
- `hasSliders` (boolean) — Εμφάνιση ρυθμιστών winScore/lives
- `hasLevels` (boolean) — 3 επίπεδα με αυξανόμενη δυσκολία
- `levelThemes` (array) — Χρώματα φόντου ανά επίπεδο
- `hasConditionBox` (boolean) — Εμφάνιση block συνθήκης τέλους
- `conditionText` (string) — Κείμενο συνθήκης

---

### 10. `guess-number.html` — Μάντεψε τον Αριθμό

**Περιγραφή:** Ο υπολογιστής διαλέγει τυχαίο αριθμό. Ο παίκτης μαντεύει με υποδείξεις «μεγαλύτερο/μικρότερο». Πολλαπλοί γύροι με καταγραφή και μέσο όρο.

**Δομή JSON:**
```json
{
  "title": "Τίτλος",
  "subtitle": "Οδηγία",
  "range": 10,
  "rounds": 5,
  "showTable": true,
  "showAverage": true,
  "showStars": false
}
```

**Πεδία:**
- `range` (number) — Μέγιστος αριθμός (1-range)
- `rounds` (number) — Αριθμός γύρων
- `showTable` (boolean) — Εμφάνιση πίνακα καταγραφής
- `showAverage` (boolean) — Υπολογισμός μέσου όρου
- `showStars` (boolean) — Αξιολόγηση με αστέρια

---

### 11. `qr-scanner.html` — QR Scanner Simulation

**Περιγραφή:** Προσομοιωμένο QR scanner. Κάρτες QR εμφανίζονται, ο χρήστης σκανάρει και μαντεύει τον τύπο περιεχομένου.

**Δομή JSON:**
```json
{
  "title": "Τίτλος",
  "subtitle": "Οδηγία",
  "cardsPerRound": 4,
  "qrItems": [
    {
      "type": "url",
      "icon": "🌐",
      "label": "Ιστοσελίδα",
      "content": "https://gov.gr",
      "hint": "Επίσημη πύλη...",
      "options": ["gov.gr", "Σχολείο", "Παιχνίδι"],
      "correctIndex": 0
    }
  ]
}
```

**Πεδία:**
- `cardsPerRound` (number) — Κάρτες ανά γύρο
- `qrItems` (array) — Διαθέσιμα QR αντικείμενα
  - `type/icon/label/content/hint` — Πληροφορίες QR
  - `options` (array) — 3 επιλογές απάντησης
  - `correctIndex` (number) — Index σωστής απάντησης

---

### 12. `spreadsheet-filter.html` — Φίλτρο Spreadsheet

**Περιγραφή:** Προσομοιωμένο spreadsheet με δεδομένα και φίλτρα (dropdown ή checkbox).

**Δομή JSON:**
```json
{
  "title": "Τίτλος",
  "subtitle": "Οδηγία",
  "columns": [
    {"name": "Πόλη", "type": "text"},
    {"name": "Καιρός", "type": "category"}
  ],
  "data": [["Αθήνα","ΗΛΙΟΣ"],["Ιωάννινα","ΒΡΟΧΗ"]],
  "filterMode": "dropdown",
  "filterableColumns": [1],
  "multiSelect": false
}
```

**Πεδία:**
- `columns` (array) — Στήλες με `name` και `type` (text/number/category)
- `data` (array of arrays) — Γραμμές δεδομένων
- `filterMode` (string) — `"dropdown"` ή `"checkbox"`
- `filterableColumns` (array) — Indexes στηλών που φιλτράρονται
- `multiSelect` (boolean) — Πολλαπλή επιλογή (μόνο για checkbox mode)

---

## Κοινά Χαρακτηριστικά όλων των Σκελετών

- **Self-contained:** HTML + CSS + JS σε ένα αρχείο. Καμία εξωτερική dependency.
- **Ελληνικά:** Όλα τα labels, buttons, μηνύματα στα Ελληνικά.
- **Responsive:** Λειτουργούν σε desktop, tablet, κινητό.
- **Kid-friendly UI:** Μεγάλα κουμπιά, χρωματιστά gradients, emoji.
- **Score/Feedback:** Όλοι εμφανίζουν σκορ/αποτέλεσμα στο τέλος.
- **Restart:** Κουμπί "Ξαναδοκίμασε!" σε όλους.
- **Error handling:** Εμφανίζει μήνυμα αν δεν βρει το JSON αρχείο.

---

## Πώς δημιουργείς νέα δραστηριότητα

1. Διάλεξε τον κατάλληλο σκελετό βάσει τύπου (quiz, drag-categories, κ.λπ.)
2. Δημιούργησε ένα JSON αρχείο με τη δομή που περιγράφεται παραπάνω
3. Αποθήκευσε το JSON στον φάκελο `activities/` της τάξης
4. Κλήσε το σκελετό: `skeleton.html?data=relative/path/to/file.json`

### Παράδειγμα: Νέο quiz για Γ' Δημοτικού, Μάθημα 5

1. Δημιούργησε αρχείο: `TPE-dimC-PEDIO/activities/dimC-les05-quiz-XXX.json`
2. Γράψε JSON με ερωτήσεις βασισμένες στο `summary_C.md` Μάθημα 5
3. Κλήση: `quiz.html?data=../TPE-dimC-PEDIO/activities/dimC-les05-quiz-ABC.json`

### Μονό αρχείο HTML (self-contained)

Αν θέλεις δραστηριότητα που ΔΕΝ ταιριάζει σε κανέναν σκελετό, μπορείς να φτιάξεις ξεχωριστό HTML αρχείο. Σε αυτή την περίπτωση ονομάστε το σύμφωνα με το convention: `dimC-les05-quiz-XXX.html` και βάλε το στον φάκελο `activities-unique/` της τάξης.

---

## Αντιστοίχιση Σκελετών — Δραστηριότητες

Από τα `activities_X.md` αρχεία, η αντιστοίχιση είναι:

| Τύπος στο activities_X.md | Σκελετός |
|---------------------------|----------|
| `quiz` | `quiz.html` |
| `game` (drag σε κατηγορίες) | `drag-categories.html` |
| `game` (σειροθέτηση) | `drag-order.html` |
| `game` (memory) | `memory.html` |
| `game` (grid/εντολές) | `grid-game.html` |
| `game` (falling/πληκτρολόγηση) | `falling-letters.html` |
| `game` (κλικ στόχων) | `click-game.html` |
| `game` (pixel art / χρωματισμός) | `canvas-pixels.html` |

Σημείωση: Ο τύπος "game" στα `activities_X.md` αντιστοιχεί σε διαφορετικό σκελετό ανάλογα με την περιγραφή. Διάβασε την περιγραφή για να καταλάβεις ποιος σκελετός χρειάζεται.

---

## File Listing

```
activities-base/
├── quiz.html
├── drag-categories.html
├── drag-order.html
├── memory.html
├── grid-game.html
├── falling-letters.html
├── click-game.html
├── canvas-pixels.html
├── arcade-game.html
├── guess-number.html
├── qr-scanner.html
├── spreadsheet-filter.html
├── sample_quiz.json
├── sample_drag-categories.json
├── sample_drag-order.json
├── sample_memory.json
├── sample_grid-game.json
├── sample_falling-letters.json
├── sample_click-game.json
├── sample_canvas-pixels.json
└── activities-base-documentation.md   (αυτό το αρχείο)
```
