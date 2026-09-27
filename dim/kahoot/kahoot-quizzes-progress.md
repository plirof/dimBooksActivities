# Kahoot Quiz Generation — Progress Tracking

## Overview

Generate one Kahoot-format quiz JSON per lesson, for all 6 classes of the Greek
elementary school Informatics/ICT curriculum. Each quiz = 10 questions.

**Source content:** `lesson*.html` files from `../dimBooksActivities/TPE-dim*-PEDIO/lessons_seperated/`
**Output folder:** `data/quiz/`
**Format spec:** `json_quiz_format.txt`
**Total target:** 6 classes × 30 lessons = **180 quizzes** = **1800 questions**

---

## Quiz Specs (applied to all)

| Field | Value |
|---|---|
| `category` | `"Πληροφορική"` |
| `tags` | `"dimX, lesNN, [topic-keyword], Πληροφορική"` |
| `questions` | Exactly 10 per quiz |
| `randomize_questions` | `true` |
| `randomize_answers` | `true` |
| `allow_solo` | `true` |
| Language | Greek (Ελληνικά) |
| Answers per question | 4 |
| Time per question | 20 (default) |
| `points` | 1000 |

⚠️ **Standalone Questions Rule:** Questions must NOT reference specific book examples, characters, pages, or exercises. They must be understandable without the book. See AGENTS.md section 4 for details.

⚠️ **No Implementation-Specific Questions:** Questions must test **general concepts and principles**, NOT recall of specific implementation details from the book's examples. Do NOT ask: "Which button did we program to do X?", "What variable name did we use?", "Which character/object did we use?", "What was the specific threshold value we set?". A student who understands the theory must be able to answer every question without having done the specific programming exercise.

⚠️ **Required fields (check AGENTS.md for details):**
- Top-level `id` (filename without `.json`), `created_at` (current date)
- Per-question `id` — convention: `{quiz-id}-q{NN}` e.g. `dimA-les01-kahoot-X7K-q01`
- These are NOT auto-generated — the app reads them directly from the JSON files

---

## Naming Convention

```
dimX-lesNN-kahoot-[3 random chars].json
```

Example: `dimC-les05-kahoot-A7K.json`

---

## Coverage Legend

| Symbol | Meaning |
|---|---|
| `—` | Not yet created |
| `✓` | File exists in `data/quiz/` |
| `✅` | File exists + verified JSON valid |

---

## dimA — Α' Δημοτικού

**Target:** 30 lessons × 1 quiz = **30 quizzes**

| Lesson | Title | Status | Filename |
|--------|-------|--------|----------|
 | les01 | Πώς λύνω ένα πρόβλημα | ✓ | dimA-les01-kahoot-X7K.json |
| les02 | Η σειρά έχει σημασία! | ✓ | dimA-les02-kahoot-M3N.json |
| les03 | Φτιάχνω το πρώτο μου πρόγραμμα | ✓ | dimA-les03-kahoot-P9Q.json |
| les04 | Πού θα φτάσει η σαρανταποδαρούσα; | ✓ | dimA-les04-kahoot-R2S.json |
| les05 | Σχεδιάζοντας το μονοπάτι | ✓ | dimA-les05-kahoot-T4U.json |
| les06 | Προγραμματίζω στον υπολογιστή! | ✓ | dimA-les06-kahoot-V5W.json |
| les07 | Η πρώτη μου ιστορία: Ταξίδι στο φεγγάρι | ✓ | dimA-les07-kahoot-C6D.json |
| les08 | Μαθαίνω για τα ρομπότ! | ✓ | dimA-les08-kahoot-E7F.json |
| les09 | Προγραμμάτισε το ρομπότ-πασχαλίτσα | ✓ | dimA-les09-kahoot-G8H.json |
| les10 | Ο υπολογιστής | ✓ | dimA-les10-kahoot-J9K.json |
| les11 | Οι συσκευές του υπολογιστή | — | |
| les12 | Πώς χρησιμοποιώ τον υπολογιστή; | — | |
| les13 | Δίκτυα υπολογιστών | — | |
| les14 | Τα ψηφιακά δίκτυα στη ζωή μας | — | |
| les15 | Αρχεία και φάκελοι | — | |
| les16 | Αποθήκευση και αρχεία | — | |
| les17 | Ο πρώτος μου εννοιολογικός χάρτης | — | |
| les18 | Μεγαλώνω τον εννοιολογικό μου χάρτη | — | |
| les19 | Μαθαίνω το ποντίκι του υπολογιστή | — | |
| les20 | Ο δείκτης του ποντικιού | — | |
| les21 | Κάνω κλικ με το ποντίκι του υπολογιστή | — | |
| les22 | Το δεξί κλικ, η ροδέλα κύλισης και η ενέργεια "σύρε" | — | |
| les23 | Γνωρίζω το πληκτρολόγιο | — | |
| les24 | Πληκτρολογώ κεφαλαία γράμματα και αριθμούς | — | |
| les25 | Γράφω τις πρώτες μου λέξεις με το πληκτρολόγιο | — | |
| les26 | Ιστοσελίδες και ιστότοποι | — | |
| les27 | Σελιδοδείκτες και σχολιασμός σε ιστοσελίδες | — | |
| les28 | Η πρώτη μου ζωγραφιά | — | |
| les29 | Περισσότερα εργαλεία στο πρόγραμμα ζωγραφικής | — | |
| les30 | Το Φωτόδεντρο και η Εκπαιδευτική τηλεόραση | — | |

**dimA Progress:** 10/30 quizzes complete

---

## dimB — Β' Δημοτικού

**Target:** 30 lessons × 1 quiz = **30 quizzes**

| Lesson | Title | Status | Filename |
|--------|-------|--------|----------|
| les01 | Επαναλήψεις | ✓ | dimB-les01-kahoot-X7K.json |
| les02 | Ανακάλυψε το μοτίβο | ✓ | dimB-les02-kahoot-M3N.json |
| les03 | Η εντολή επανάληψης | ✓ | dimB-les03-kahoot-P9Q.json |
| les04 | Μοτίβα και εντολή επανάληψης | ✓ | dimB-les04-kahoot-R2S.json |
| les05 | Διαβάζω και διορθώνω προγράμματα με την εντολή επανάληψης | ✓ | dimB-les05-kahoot-T4U.json |
| les06 | Προγραμματίζω με την εντολή επανάληψης | ✓ | dimB-les06-kahoot-V5W.json |
| les07 | Πάρτι χορού | ✓ | dimB-les07-kahoot-C6D.json |
| les08 | Γνωρίζω το micro:bit | ✓ | dimB-les08-kahoot-E7F.json |
| les09 | Τα γεγονότα | ✓ | dimB-les09-kahoot-G8H.json |
| les10 | Ο υπολογιστής στη ζωή μας | ✓ | dimB-les10-kahoot-J9K.json |
| les11 | Ο υπολογιστής είναι ψηφιακή συσκευή | ✓ | dimB-les11-kahoot-A3B.json |
| les12 | Οι συσκευές του υπολογιστή | ✓ | dimB-les12-kahoot-C4D.json |
| les13 | Πώς χρησιμοποιώ τις εφαρμογές του υπολογιστή; | ✓ | dimB-les13-kahoot-E5F.json |
| les14 | Το διαδίκτυο: Ένα μεγάλο δίκτυο υπολογιστών | ✓ | dimB-les14-kahoot-G6H.json |
| les15 | Αποθήκευση και άνοιγμα αρχείων | ✓ | dimB-les15-kahoot-I7J.json |
| les16 | Αποθήκευση αρχείων σε φακέλους | ✓ | dimB-les16-kahoot-K9L.json |
| les17 | Ο πρώτος μου εννοιολογικός χάρτης με τον υπολογιστή | ✓ | dimB-les17-kahoot-M2N.json |
| les18 | Ο δεύτερος εννοιολογικός μου χάρτης με τον υπολογιστή | ✓ | dimB-les18-kahoot-N3O.json |
| les19 | Ο φυλλομετρητής | ✓ | dimB-les19-kahoot-P4Q.json |
| les20 | Οι ψηφιακοί πόροι μιας ιστοσελίδας | ✓ | dimB-les20-kahoot-Q5R.json |
| les21 | Εντοπίζω περισσότερους χαρακτήρες στο πληκτρολόγιο | ✓ | dimB-les21-kahoot-G3H.json |
| les22 | Γράφω περισσότερα στον υπολογιστή | ✓ | dimB-les22-kahoot-H4I.json |
| les23 | Επεξεργάζομαι αντικείμενα με το ποντίκι | ✓ | dimB-les23-kahoot-J5K.json |
| les24 | Χειρίζομαι περισσότερα εργαλεία στη ζωγραφική | ✓ | dimB-les24-kahoot-K6L.json |
| les25 | Η ψηφιακή πλατφόρμα e-me | ✓ | dimB-les25-kahoot-L7M.json |
| les26 | Η ψηφιακή πλατφόρμα e-me – Κυψέλη, τοίχος και εκπαιδευτικό περιεχόμενο | ✓ | dimB-les26-kahoot-M8N.json |
| les27 | Ηλεκτρονικά μηνύματα | ✓ | dimB-les27-kahoot-N9O.json |
| les28 | Λαμβάνω και στέλνω ηλεκτρονικά μηνύματα στην e-me | ✓ | dimB-les28-kahoot-O1P.json |
| les29 | Τα διαδραστικά βιβλία | ✓ | dimB-les29-kahoot-P2Q.json |
| les30 | Ψηφιακή πολιτειότητα | ✓ | dimB-les30-kahoot-Q3R.json |

**dimB Progress:** 30/30 quizzes complete

---

## dimC — Γ' Δημοτικού

**Target:** 30 lessons × 1 quiz = **30 quizzes**

| Lesson | Title | Status | Filename |
|--------|-------|--------|----------|
| les01 | Συνθήκες και αποφάσεις | ✓ | dimC-les01-kahoot-A1B.json |
| les02 | Αλήθεια ή ψέματα; | ✓ | dimC-les02-kahoot-C2D.json |
| les03 | Οι υπολογιστές παίρνουν αποφάσεις | ✓ | dimC-les03-kahoot-A3K.json |
| les04 | Γνωριμία με το περιβάλλον του Scratch | ✓ | dimC-les04-kahoot-G4H.json |
| les05 | Εντολή επιλογής στο Scratch (Μέρος Α') | ✓ | dimC-les05-kahoot-B5L.json |
| les06 | Εντολή επιλογής στο Scratch (Μέρος Β') | ✓ | dimC-les06-kahoot-C6M.json |
| les07 | Γνωριμία με το ρομπότ εδάφους (Έντισον) | ✓ | dimC-les07-kahoot-M7B.json |
| les08 | Παιχνίδι με το ρομπότ: Παγίδευσε το ρομπότ σου! | ✓ | dimC-les08-kahoot-D8N.json |
| les09 | Πώς αποθηκεύεται η ψηφιακή εικόνα; | ✓ | dimC-les09-kahoot-J9C.json |
| les10 | Τα εξαρτήματα και οι συσκευές του υπολογιστή | ✓ | dimC-les10-kahoot-A0F.json |
| les11 | Οι εφαρμογές του υπολογιστή | ✓ | dimC-les11-kahoot-K7B.json |
| les12 | Η δικτύωση υπολογιστών και ψηφιακών συσκευών | ✓ | dimC-les12-kahoot-M2N.json |
| les13 | Προστασία από κακόβουλα λογισμικά και κανόνες ασφαλείας στο διαδίκτυο | ✓ | dimC-les13-kahoot-P9Q.json |
| les14 | Αρχεία και φάκελοι | ✓ | dimC-les14-kahoot-R3T.json |
| les15 | Είδη εννοιολογικών χαρτών | ✓ | dimC-les15-kahoot-W5X.json |
| les16 | Λύνω ένα πρόβλημα με εννοιολογικό χάρτη | ✓ | dimC-les16-kahoot-F1A.json |
| les17 | Περισσότερα εργαλεία στο πληκτρολόγιο | ✓ | dimC-les17-kahoot-C2B.json |
| les18 | Δικτυακός τόπος και ιστοσελίδες | ✓ | dimC-les18-kahoot-D4E.json |
| les19 | Τα βασικά χαρακτηριστικά ενός δικτυακού τόπου | ✓ | dimC-les19-kahoot-G5H.json |
| les20 | Μηχανές αναζήτησης | ✓ | dimC-les20-kahoot-J6K.json |
| les21 | Επιλέγω ό,τι είναι κατάλληλο από τα αποτελέσματα μίας αναζήτησης | ✓ | dimC-les21-kahoot-L7M.json |
| les22 | Φτιάχνω κινούμενα σχέδια από εικόνες | ✓ | dimC-les22-kahoot-E2O.json |
| les23 | Επεξεργάζομαι το προφίλ μου στην e-me | ✓ | dimC-les23-kahoot-Q9R.json |
| les24 | Τα ιστολόγια της e-me | ✓ | dimC-les24-kahoot-S1T.json |
| les25 | Τα άρθρα ενός ιστολογίου της e-me | ✓ | dimC-les25-kahoot-V2W.json |
| les26 | Αναζητώ και σχολιάζω άρθρα σε ιστολόγιο της e-me | ✓ | dimC-les26-kahoot-B3K.json |
| les27 | Διαμορφώνω το προσωπικό μου περιβάλλον στην e-me και επικοινωνώ με άλλους | ✓ | dimC-les27-kahoot-X9M.json |
| les28 | Χρησιμοποιώ μαθησιακά αντικείμενα εκπαιδευτικών αποθετηρίων | ✓ | dimC-les28-kahoot-P5Q.json |
| les29 | Η υπερβολική χρήση του διαδικτύου σε σχέση με τη σωματική και την ψυχική υγεία | ✓ | dimC-les29-kahoot-D7J.json |
| les30 | Κανόνες και όρια για τη χρήση ψηφιακών συσκευών στο διαδίκτυο | ✓ | dimC-les30-kahoot-F2N.json |

**dimC Progress:** 30/30 quizzes complete

---

## dimD — Δ' Δημοτικού

**Target:** 30 lessons × 1 quiz = **30 quizzes**

| Lesson | Title | Status | Filename |
|--------|-------|--------|----------|
| les01 | Οι αλγόριθμοι | ✓ | dimD-les01-kahoot-A1B.json |
| les02 | Μία νέα εντολή επιλογής και μία νέα εντολή επανάληψης | ✓ | dimD-les02-kahoot-D2A.json |
| les03 | Ξανά και ξανά… | ✓ | dimD-les03-kahoot-D3B.json |
| les04 | Αριθμητικές πράξεις | ✓ | dimD-les04-kahoot-D4C.json |
| les05 | Επανάλαβε ώσπου | ✓ | dimD-les05-kahoot-I5J.json |
| les06 | Αλλάζοντας ενδυμασίες | ✓ | dimD-les06-kahoot-D6D.json |
| les07 | Αφήγηση ιστοριών με προγραμματισμό | ✓ | dimD-les07-kahoot-M7N.json |
| les08 | Οδήγηση σε πίστα | ✓ | dimD-les08-kahoot-O8P.json |
| les09 | Αναγνωρίζοντας τις μαύρες γραμμές | ✓ | dimD-les09-kahoot-Q9R.json |
| les10 | Οδηγώντας το ρομπότ στον λαβύρινθο | ✓ | dimD-les10-kahoot-S0T.json |
| les11 | Ψηφιακά δεδομένα | ✓ | dimD-les11-kahoot-U1V.json |
| les12 | Ποιον υπολογιστή να διαλέξω; | ✓ | dimD-les12-kahoot-W2X.json |
| les13 | Οι εφαρμογές του υπολογιστή | ✓ | dimD-les13-kahoot-Y3Z.json |
| les14 | Το ταξίδι των δεδομένων σε ένα δίκτυο | ✓ | dimD-les14-kahoot-A4B.json |
| les15 | Προστατεύομαι από διαδικτυακές επιθέσεις | ✓ | dimD-les15-kahoot-C5D.json |
| les16 | Δημιουργία ψηφιακού περιεχομένου | ✓ | dimD-les16-kahoot-E6F.json |
| les17 | Μεγέθη και διαχείριση αρχείων | ✓ | dimD-les17-kahoot-G7H.json |
| les18 | Επεξεργασία δεδομένων | ✓ | dimD-les18-kahoot-I8J.json |
| les19 | Προσθέτω εικόνες και συνδέσεις στον εννοιολογικό μου χάρτη | ✓ | dimD-les19-kahoot-K9L.json |
| les20 | Χρήση λειτουργιών του φυλλομετρητή και αναζήτηση με λέξεις-κλειδιά | ✓ | dimD-les20-kahoot-M0N.json |
| les21 | Διάκριση αποτελεσμάτων αναζήτησης – Εφαρμογή κριτηρίων αξιολόγησης διαδικτυακού περιεχομένου | ✓ | dimD-les21-kahoot-N2O.json |
| les22 | Διάκριση ηλεκτρονικού και συμβατικού ταχυδρομείου – Λογαριασμός χρήστη | ✓ | dimD-les22-kahoot-P3Q.json |
| les23 | Χρήση ηλεκτρονικού ταχυδρομείου | ✓ | dimD-les23-kahoot-R4S.json |
| les24 | Δημιουργία παρουσίασης – Αντιγραφή και αποθήκευση περιεχομένου | ✓ | dimD-les24-kahoot-T5U.json |
| les25 | Βασικές δυνατότητες επεξεργαστή κειμένου | ✓ | dimD-les25-kahoot-V6W.json |
| les26 | Διαχείριση επαφών και επικοινωνία μέσω εκπαιδευτικής πλατφόρμας | ✓ | dimD-les26-kahoot-X7Y.json |
| les27 | Συνεργασία και επικοινωνία μέσω εκπαιδευτικής πλατφόρμας – Συνεδρία σύγχρονης τηλεκπαίδευσης | ✓ | dimD-les27-kahoot-Z8A.json |
| les28 | Πνευματικά δικαιώματα στο διαδίκτυο και υπεύθυνη ψηφιακή συμπεριφορά | ✓ | dimD-les28-kahoot-B9C.json |
| les29 | Κανόνες συμπεριφοράς (Netiquette) και ψηφιακό αποτύπωμα στο διαδίκτυο | ✓ | dimD-les29-kahoot-D0E.json |
| les30 | Κριτήρια αξιολόγησης πηγών στο διαδίκτυο | ✓ | dimD-les30-kahoot-F1G.json |

**dimD Progress:** 30/30 quizzes complete

---

## dimE — Ε' Δημοτικού

**Target:** 30 lessons × 1 quiz = **30 quizzes**

| Lesson | Title | Status | Filename |
|--------|-------|--------|----------|
| les01 | Ο αλγόριθμος του Καίσαρα | ✓ | dimE-les01-kahoot-R2Q.json |
| les02 | Ο αλγόριθμος του Καίσαρα (συνέχεια) | ✓ | dimE-les02-kahoot-X7K.json |
| les03 | Σύνθετες λογικές εκφράσεις | ✓ | dimE-les03-kahoot-P9C.json |
| les04 | Κατασκευή απλού παιχνιδιού στο Scratch | ✓ | dimE-les04-kahoot-X7K.json |
| les05 | Αυξάνοντας τους πόντους και χάνοντας ζωές – Οι μεταβλητές | ✓ | dimE-les05-kahoot-X7K.json |
| les06 | Η συνθήκη τέλους του παιχνιδιού – Μία λογική έκφραση | ✓ | dimE-les06-kahoot-X7K.json |
| les07 | Νίκη ή ήττα | ✓ | dimE-les07-kahoot-X7K.json |
| les08 | Υποπρογράμματα | ✓ | dimE-les08-kahoot-X7K.json |
| les09 | Παράλληλες ακολουθίες εντολών | ✓ | dimE-les09-kahoot-X7K.json |
| les10 | Μηνύματα | ✓ | dimE-les10-kahoot-X7K.json |
| les11 | Χαρούμενα γενέθλια! (micro:bit) | ✓ | dimE-les11-kahoot-A7K.json |
| les12 | Ψηφιοποίηση κειμένου | ✓ | dimE-les12-kahoot-H3X.json |
| les13 | Το υλικό και οι χρήστες του υπολογιστή | ✓ | dimE-les13-kahoot-F5J.json |
| les14 | Το ταξίδι των δεδομένων σε ένα δίκτυο | ✓ | dimE-les14-kahoot-B8M.json |
| les15 | Προσέχω ποια λογισμικά εγκαθιστώ... | ✓ | dimE-les15-kahoot-C4N.json |
| les16 | Συγκέντρωση δεδομένων, υπολογιστικό φύλλο | ✓ | dimE-les16-kahoot-D2P.json |
| les17 | Υπολογιστικό φύλλο: Οργάνωση δεδομένων σε πίνακα | ✓ | dimE-les17-kahoot-G6R.json |
| les18 | Αριθμητικές πράξεις στο υπολογιστικό φύλλο | ✓ | dimE-les18-kahoot-K9S.json |
| les19 | Υπολογιστικό φύλλο: οργάνωση δεδομένων σε διαγράμματα | ✓ | dimE-les19-kahoot-M4T.json |
| les20 | Τεχνητή νοημοσύνη και μηχανική μάθηση | ✓ | dimE-les20-kahoot-W3B.json |
| les21 | Γραμμή διευθύνσεων – Κριτήρια αξιολόγησης πληροφοριών | ✓ | dimE-les21-kahoot-K7M.json |
| les22 | Καθορισμός φίλτρων αναζήτησης – Αναγνώριση αποτελεσμάτων | ✓ | dimE-les22-kahoot-P4N.json |
| les23 | Αποθήκευση και ανάκτηση ψηφιακών αρχείων | ✓ | dimE-les23-kahoot-J8R.json |
| les24 | Δημιουργώ εντυπωσιακές παρουσιάσεις | ✓ | dimE-les24-kahoot-S2T.json |
| les25 | Δημιουργία κειμένων, παρουσιάσεων με χρήση ψηφιακού περιεχομένου | ✓ | dimE-les25-kahoot-F6W.json |
| les26 | Αξιοποίηση της εφαρμογής e-me assignments | ✓ | dimE-les26-kahoot-H9X.json |
| les27 | Αξιοποίηση ψηφιακών μουσείων, εγκυκλοπαιδειών και λεξικών | ✓ | dimE-les27-kahoot-V3Y.json |
| les28 | Ψηφιακές υπηρεσίες του πολίτη | ✓ | dimE-les28-kahoot-N5Z.json |
| les29 | Επιδράσεις από τη χρήση του διαδικτύου | ✓ | dimE-les29-kahoot-L1B.json |
| les30 | Οι άδειες χρήσης Creative Commons | ✓ | dimE-les30-kahoot-Q8C.json |

**dimE Progress:** 30/30 quizzes complete

---

## dimST — ΣΤ' Δημοτικού

**Target:** 30 lessons × 1 quiz = **30 quizzes**

| Lesson | Title | Status | Filename |
|--------|-------|--------|----------|
| les01 | Προβλήματα και αλγόριθμοι | ✓ | dimST-les01-kahoot-K3M.json |
| les02 | Το παιχνίδι "Μάντεψε τον αριθμό" | ✓ | dimST-les02-kahoot-S2E.json |
| les03 | Είσοδος και έξοδος στο Scratch | ✓ | dimST-les03-kahoot-S3F.json |
| les04 | Υποπρογράμματα με μεταβλητές εισόδου | ✓ | dimST-les04-kahoot-W2Q.json |
| les05 | Περισσότερα για τα υποπρογράμματα με μεταβλητές εισόδου | ✓ | dimST-les05-kahoot-S5G.json |
| les06 | Μάντεψε τον αριθμό | ✓ | dimST-les06-kahoot-S6H.json |
| les07 | Επίπεδα δυσκολίας, ηχητικά και γραφικά εφέ | ✓ | dimST-les07-kahoot-S7I.json |
| les08 | Αισθητήρες φωτός – Μέρα ή νύχτα | ✓ | dimST-les08-kahoot-H9P.json |
| les09 | Ηχο-γράφημα | ✓ | dimST-les09-kahoot-L3W.json |
| les10 | Θερμόμετρο - Μέγιστη θερμοκρασία | ✓ | dimST-les10-kahoot-S1J.json |
| les11 | Καταγραφή δεδομένων και εκτέλεση πειραμάτων | ✓ | dimST-les11-kahoot-Q3R.json |
| les12 | Το υλικό του υπολογιστή και τα χαρακτηριστικά του | ✓ | dimST-les12-kahoot-T5W.json |
| les13 | Ρυθμίσεις υλικού-λογισμικού και αντιμετώπιση προβλημάτων λειτουργίας | ✓ | dimST-les13-kahoot-V8X.json |
| les14 | Διασύνδεση συσκευών στο διαδίκτυο: Πάροχοι και πρωτόκολλα | ✓ | dimST-les14-kahoot-Y2K.json |
| les15 | Οι θετικές και αρνητικές επιπτώσεις από τη χρήση του διαδικτύου στην καθημερινή ζωή | ✓ | dimST-les15-kahoot-B6N.json |
| les16 | Ερωτήματα και φόρμες συλλογής δεδομένων | ✓ | dimST-les16-kahoot-D9P.json |
| les17 | Οργάνωση και διαχείριση δεδομένων έρευνας στο υπολογιστικό φύλλο | ✓ | dimST-les17-kahoot-F4J.json |
| les18 | Συγκέντρωση, διαχείριση και ταξινόμηση δεδομένων στο υπολογιστικό φύλλο | ✓ | dimST-les18-kahoot-H7M.json |
| les19 | Ανάλυση δεδομένων με χρήση φίλτρων στο υπολογιστικό φύλλο | ✓ | dimST-les19-kahoot-K1Z.json |
| les20 | Ερωτήματα και ανάλυση δεδομένων | ✓ | dimST-les20-kahoot-M3L.json |
| les21 | Εντοπισμός περιεχομένου στο διαδίκτυο με εναλλακτικούς τρόπους | ✓ | dimST-les21-kahoot-P9Q.json |
| les22 | Συμμετοχή - δημιουργία - διαχείριση συζήτησης | ✓ | dimST-les22-kahoot-M4B.json |
| les23 | Διαχείριση αναρτήσεων σε ιστολόγια | ✓ | dimST-les23-kahoot-D9N.json |
| les24 | Χρήση ετικετών για δημοσίευση και αναζήτηση αναρτήσεων ιστολογίου | ✓ | dimST-les24-kahoot-F4J.json |
| les25 | Σύνθετες δυνατότητες του επεξεργαστή κειμένου – Διαφορά μεταξύ εγγράφου και παρουσίασης | ✓ | dimST-les25-kahoot-H7M.json |
| les26 | Εκπαιδευτικές δυνατότητες των εργαλείων σύγχρονης τηλεκπαίδευσης | ✓ | dimST-les26-kahoot-K1Z.json |
| les27 | Χρήση υπηρεσιών βίντεο | ✓ | dimST-les27-kahoot-M3L.json |
| les28 | Βασικές ψηφιακές υπηρεσίες στη ζωή μας | ✓ | dimST-les28-kahoot-V8X.json |
| les29 | Τα προσωπικά δεδομένα και ο Γενικός Κανονισμός για την Προστασία Δεδομένων (ΓΚΠΔ/GDPR) της Ευρωπαϊκής Ένωσης (ΕΕ) - Δυσλειτουργικές διαδικτυακές συμπεριφορές | ✓ | dimST-les29-kahoot-Y2K.json |
| les30 | Το δημιουργικό διαδίκτυο ως εναλλακτικό εργαλείο μάθησης και ψυχαγωγίας | ✓ | dimST-les30-kahoot-B6N.json |

**dimST Progress:** 30/30 quizzes complete

---

## Grand Summary

 | Class | Target | Created | Verified | Remaining | Coverage |
|---|---|---|---|---|---|
| Α' Δημοτικού (dimA) | 30 | 10 | 0 | 20 | 33% |
| Β' Δημοτικού (dimB) | 30 | 30 | 0 | 0 | 100% |
| Γ' Δημοτικού (dimC) | 30 | 30 | 0 | 0 | 100% |
| Δ' Δημοτικού (dimD) | 30 | 30 | 0 | 0 | 100% |
| Ε' Δημοτικού (dimE) | 30 | 30 | 0 | 0 | 100% |
| ΣΤ' Δημοτικού (dimST) | 30 | 30 | 0 | 0 | 100% |
| **TOTAL** | **180** | **160** | **0** | **20** | **89%** |
