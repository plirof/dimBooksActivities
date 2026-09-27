# One of Each Activity — Δημιουργία & Παρακολούθηση

## Οδηγίες Υλοποίησης

## Αρχικό Prompt / Οδηγίες

> I want to check these files : dimBooksActivities/TPE-dimA-PEDIO/lessons_seperated/*.html
> Αυτά είναι τα μαθήματα για τις 6 ταξεις του δημοτικού.
> Θέλω για όλα τα μαθήματα όλων των τάξεων να δημιουργήσεις activities για τις activities που βρίσκονται στον φακελο dimBooksActivities/activities-base/
> και συγκεκριμένα για τις : arcade-game.html,drag-order.html,memory.html,canvas-pixels.html,falling-letters.html,qr-scanner.html,click-game.html,grid-game.html,quiz.html,drag-categories.html και guess-number.html
> Θέλω να μου φτιάξεις απευθείας activity json αρχεία με δραστηριότητες.
> Το format των json μπορείς να το βρεις από τα ήδη υπάρχοντα json και στο αρχείο dimBooksActivities/activities-base/json_format.txt.
> Θέλω όλα να είναι στα ελληνικά.
> Τα ονόματα αρχείων θα είναι της μορφής dimX-lesZ-[activity-type]-[3-random-number/letters].json
> επίσης, μέσα στο json το πεδίο id θα είναι και αυτό dimX-lesZ-[activity-type]-[3-random-number/letters]. πχ "id": "dimX-lesZ-hangman-TZ3".
> Επίσης, σαν tags στο json θα βάλεις τα dimX ,lesZ και μία λέξη σχετική με το αντικείμενο του μαθήματος καθώς και το activity type.
> Επειδή, είναι μεγάλη εργασία έχω δημιουργήσει το αρχείο dimBooksActivities/activities-base/one-of-each-activities.md (η φτιάξτο εσύ την πρώτη φορά αν δεν το βρεις).
> Εκεί θα υπάρχει μια λίστα των εργασιών που θα πρέπει να γίνουν και ποιες ολοκληρώθηκαν.
> Η σειρά των εργασιών θα είναι η εξής: Για κάθε τάξη θα διαβάσεις ένα-ένα όλα τα μαθήματα. Για κάθε μάθημα θα δημιουργείς ένα json για κάθε μια από τις δραστηριότητες που ανέφερα πριν. Read each lesson carefully.
> Όταν τελειώνεις με ένα μάθημα, ενημερώνεις το αρχείο one-of-each-activities.md και συνεχίζεις στο επόμενο. Προχωράμε ένα-ενα μάθημα. μην ασχολείσαι παράλληλα με πολλά μαθήματα.
> Κατάλαβες τι πρέπει να κάνεις; Πες μου τι έχει μείνει να κάνουμε για να σου πω πως θα προχωρήσεις




### Σημειώσεις υλοποίησης
- **id/tags fields**: IGNORE per user instruction — τα JSONs δεν περιέχουν id ή tags πεδία
- **Τάξεις (dim key)**: dimA, dimB, dimC, dimD, dimE, dimST
- **Μαθήματα**: les01–les30 (skip lesson00_index)
- **activity types**: arcade-game.html,drag-order.html,memory.html,canvas-pixels.html,falling-letters.html,qr-scanner.html,click-game.html,grid-game.html,quiz.html,drag-categories.html,guess-number.html 
- **Πηγή περιεχομένου**: Τα HTML lessons στο `dimBooksActivities/Pliroforiki_*/lessons_seperated/lessonNN.html`
- **Output φάκελος**: `dimBooksActivities/TPE-dimXX-PEDIO/activities`
- **Plan file**: Ενημερώνεται μετά κάθε ολοκληρωμένο μάθημα


**Πηγές περιεχομένου:**
- Τα HTML lessons στο `TPE-dimX-PEDIO/lessons_seperated/lessonNN.html`
- Τα `summary_X.md` αρχεία στην ρίζα του project

**JSON format reference:** `json_format_activities-base.txt` (στην ρίζα του project)

**Output φάκελος:** `TPE-dimX-PEDIO/activities/`

**Naming convention:**
```
dimX-lesNN-[activity-type]-[3-random-characters].json
```
Π.χ. `dimC-les05-quiz-ABC.json`

**Σειρά εργασιών:**
1. Για κάθε τάξη (dimA → dimB → dimC → dimD → dimE → dimST)
2. Διάβασε ένα-ένα τα μαθήματα (les01–les30, skip lesson00_index)
3. Για κάθε μάθημα, δημιούργησε ένα json για **κάθε τύπο δραστηριότητας** που λείπει
4. Ενημέρωσε το `one-of-each-activities.md` μετά από κάθε ολοκληρωμένο μάθημα

**Σημειώσεις:**
- Όλα στα Ελληνικά
- Δεν χρησιμοποιούμε id ή tags πεδία στα JSON (δεν υπάρχουν στα schemas)
- Τα JSON ακολουθούν τα schemas από `json_format_activities-base.txt`
- Τα sample JSON υπάρχουν στο `activities-base/sample_*.json`

---

## Coverage Report

Goal: Each lesson in each class should have **at least 1** of each age-appropriate activity type.

---

## TPE-dimA-PEDIO — Α' Δημοτικού

Full set (8 types): 📝 quiz | 🏷️ drag-categories | 🔢 drag-order | 🃏 memory | 🎮 grid-game | 🔤 falling-letters | 🎯 click-game | 🎨 canvas-pixels
Target: 8 types × 30 lessons = **240** (one of each type per lesson)

| Lesson | 📝 | 🏷️ | 🔢 | 🃏 | 🎮 | 🔤 | 🎯 | 🎨 | OK? |
|-------|----|----|----|----|----|----|----|----|----|
| les01 | 1✓ |  — | 1✓ | 1✓ |  — |  — |  — |  — | ❌ |
| les02 | 1✓ |  — | 1✓ | 1✓ |  — |  — |  — |  — | ❌ |
| les03 | 2✓ |  — |  — |  — | 1✓ |  — |  — |  — | ❌ |
| les04 | 2✓ |  — |  — |  — |  — |  — |  — | 1✓ | ❌ |
| les05 | 1✓ |  — |  — |  — | 2✓ |  — |  — |  — | ❌ |
| les06 | 1✓ |  — | 1✓ | 1✓ |  — |  — |  — |  — | ❌ |
| les07 | 1✓ |  — | 1✓ | 1✓ |  — |  — |  — |  — | ❌ |
| les08 | 1✓ | 2✓ |  — |  — |  — |  — |  — |  — | ❌ |
| les09 | 1✓ |  — |  — |  — | 2✓ |  — |  — |  — | ❌ |
| les10 | 1✓ | 1✓ |  — | 1✓ |  — |  — |  — |  — | ❌ |
| les11 | 1✓ | 1✓ |  — | 1✓ |  — |  — |  — |  — | ❌ |
| les12 | 1✓ |  — | 1✓ |  — |  — |  — | 1✓ |  — | ❌ |
| les13 | 1✓ | 1✓ |  — |  — |  — |  — | 1✓ |  — | ❌ |
| les14 | 1✓ | 1✓ | 1✓ |  — |  — |  — |  — |  — | ❌ |
| les15 | 1✓ | 2✓ |  — |  — |  — |  — |  — |  — | ❌ |
| les16 | 1✓ |  — | 1✓ |  — |  — |  — | 1✓ |  — | ❌ |
| les17 | 1✓ |  — | 1✓ | 1✓ |  — |  — |  — |  — | ❌ |
| les18 | 1✓ | 1✓ | 1✓ |  — |  — |  — |  — |  — | ❌ |
| les19 | 1✓ | 2✓ |  — |  — |  — |  — |  — |  — | ❌ |
| les20 | 1✓ |  — |  — |  — |  — |  — | 2✓ |  — | ❌ |
| les21 | 1✓ |  — |  — |  — |  — |  — | 2✓ |  — | ❌ |
| les22 | 1✓ | 1✓ | 1✓ |  — |  — |  — |  — |  — | ❌ |
| les23 | 1✓ |  — |  — |  — |  — |  — | 2✓ |  — | ❌ |
| les24 | 1✓ |  — |  — |  — |  — | 2✓ |  — |  — | ❌ |
| les25 | 1✓ |  — |  — |  — |  — | 2✓ |  — |  — | ❌ |
| les26 | 1✓ |  — |  — |  — |  — |  — | 2✓ |  — | ❌ |
| les27 | 1✓ |  — |  — |  — |  — |  — | 1✓ |  — | ❌ |
| les28 | 1✓ | 1✓ |  — |  — |  — |  — |  — | 1✓ | ❌ |
| les29 | 1✓ | 1✓ |  — |  — |  — |  — |  — | 1✓ | ❌ |
| les30 | 1✓ | 1✓ |  — | 1✓ |  — |  — |  — |  — | ❌ |

### Type coverage across all 30 lessons
- 📝 **quiz**: 32 total files, 30/30 lessons covered  ██████████████████████████████
- 🏷️ **drag-categories**: 15 total files, 12/30 lessons covered  ████████████░░░░░░░░░░░░░░░░░░
- 🔢 **drag-order**: 10 total files, 10/30 lessons covered  ██████████░░░░░░░░░░░░░░░░░░░░
- 🃏 **memory**: 8 total files, 8/30 lessons covered  ████████░░░░░░░░░░░░░░░░░░░░░░
- 🎮 **grid-game**: 5 total files, 3/30 lessons covered  ███░░░░░░░░░░░░░░░░░░░░░░░░░░░
- 🔤 **falling-letters**: 4 total files, 2/30 lessons covered  ██░░░░░░░░░░░░░░░░░░░░░░░░░░░░
- 🎯 **click-game**: 12 total files, 8/30 lessons covered  ████████░░░░░░░░░░░░░░░░░░░░░░
- 🎨 **canvas-pixels**: 3 total files, 3/30 lessons covered  ███░░░░░░░░░░░░░░░░░░░░░░░░░░░

**Summary**: 76 type-lesson combos satisfied / 240 target  |  Lessons fully complete: **0/30**  |  **164 new activities needed**

---

## TPE-dimB-PEDIO — Β' Δημοτικού

Full set (8 types): 📝 quiz | 🏷️ drag-categories | 🔢 drag-order | 🃏 memory | 🎮 grid-game | 🔤 falling-letters | 🎯 click-game | 🎨 canvas-pixels
Target: 8 types × 30 lessons = **240** (one of each type per lesson)

| Lesson | 📝 | 🏷️ | 🔢 | 🃏 | 🎮 | 🔤 | 🎯 | 🎨 | OK? |
|-------|----|----|----|----|----|----|----|----|----|
| les01 | 1✓ | 1✓ | 1✓ | 1✓ |  — |  — |  — |  — | ❌ |
| les02 | 1✓ | 1✓ | 1✓ | 1✓ |  — |  — | 1✓ |  — | ❌ |
| les03 | 1✓ | 1✓ |  — | 1✓ | 1✓ |  — |  — |  — | ❌ |
| les04 | 1✓ | 1✓ | 1✓ | 1✓ | 1✓ |  — |  — |  — | ❌ |
| les05 | 1✓ | 1✓ |  — | 1✓ | 2✓ |  — |  — |  — | ❌ |
| les06 | 1✓ | 1✓ | 1✓ | 1✓ |  — |  — |  — |  — | ❌ |
| les07 | 1✓ | 1✓ | 2✓ | 1✓ |  — |  — |  — |  — | ❌ |
| les08 | 1✓ | 1✓ | 1✓ | 1✓ |  — |  — |  — | 1✓ | ❌ |
| les09 | 1✓ | 1✓ |  — | 1✓ |  — |  — |  — |  — | ❌ |
| les10 | 1✓ | 1✓ |  — | 1✓ |  — |  — |  — |  — | ❌ |
| les11 | 1✓ | 2✓ |  — | 1✓ |  — |  — |  — |  — | ❌ |
| les12 | 1✓ | 1✓ |  — | 1✓ |  — |  — |  — |  — | ❌ |
| les13 | 1✓ | 1✓ |  — | 1✓ |  — |  — | 1✓ |  — | ❌ |
| les14 | 1✓ | 1✓ |  — | 1✓ |  — |  — |  — |  — | ❌ |
| les15 | 1✓ | 1✓ | 1✓ | 1✓ |  — |  — |  — |  — | ❌ |
| les16 | 1✓ | 1✓ |  — | 1✓ |  — |  — |  — |  — | ❌ |
| les17 | 1✓ | 1✓ | 1✓ | 1✓ |  — |  — |  — |  — | ❌ |
| les18 | 1✓ | 1✓ | 2✓ | 1✓ |  — |  — |  — |  — | ❌ |
| les19 | 1✓ | 1✓ | 1✓ | 1✓ |  — |  — |  — |  — | ❌ |
| les20 | 1✓ | 1✓ |  — | 1✓ |  — |  — | 1✓ |  — | ❌ |
| les21 | 1✓ | 1✓ |  — | 1✓ |  — | 2✓ |  — |  — | ❌ |
| les22 | 1✓ | 1✓ |  — | 1✓ |  — | 1✓ |  — |  — | ❌ |
| les23 | 1✓ | 1✓ |  — | 1✓ |  — |  — |  — |  — | ❌ |
| les24 | 1✓ | 1✓ |  — | 1✓ |  — |  — |  — | 1✓ | ❌ |
| les25 | 1✓ | 1✓ |  — | 1✓ |  — |  — |  — |  — | ❌ |
| les26 | 1✓ | 1✓ |  — | 1✓ |  — |  — |  — |  — | ❌ |
| les27 | 1✓ | 1✓ | 1✓ | 1✓ |  — |  — |  — |  — | ❌ |
| les28 | 1✓ | 1✓ |  — | 1✓ |  — |  — |  — |  — | ❌ |
| les29 | 1✓ | 1✓ |  — | 1✓ |  — |  — |  — |  — | ❌ |
| les30 | 1✓ | 1✓ | 1✓ | 1✓ |  — |  — |  — |  — | ❌ |

### Type coverage across all 30 lessons
- 📝 **quiz**: 30 total files, 30/30 lessons covered  ██████████████████████████████
- 🏷️ **drag-categories**: 31 total files, 30/30 lessons covered  ██████████████████████████████
- 🔢 **drag-order**: 14 total files, 12/30 lessons covered  ████████████░░░░░░░░░░░░░░░░░░
- 🃏 **memory**: 30 total files, 30/30 lessons covered  ██████████████████████████████
- 🎮 **grid-game**: 4 total files, 3/30 lessons covered  ███░░░░░░░░░░░░░░░░░░░░░░░░░░░
- 🔤 **falling-letters**: 3 total files, 2/30 lessons covered  ██░░░░░░░░░░░░░░░░░░░░░░░░░░░░
- 🎯 **click-game**: 3 total files, 3/30 lessons covered  ███░░░░░░░░░░░░░░░░░░░░░░░░░░░
- 🎨 **canvas-pixels**: 2 total files, 2/30 lessons covered  ██░░░░░░░░░░░░░░░░░░░░░░░░░░░░

**Summary**: 112 type-lesson combos satisfied / 240 target  |  Lessons fully complete: **0/30**  |  **128 new activities needed**

---

## TPE-dimC-PEDIO — Γ' Δημοτικού

Full set (8 types): 📝 quiz | 🏷️ drag-categories | 🔢 drag-order | 🃏 memory | 🎮 grid-game | 🔤 falling-letters | 🎯 click-game | 🎨 canvas-pixels
Target: 8 types × 30 lessons = **240** (one of each type per lesson)

| Lesson | 📝 | 🏷️ | 🔢 | 🃏 | 🎮 | 🔤 | 🎯 | 🎨 | OK? |
|-------|----|----|----|----|----|----|----|----|----|
| les01 | 1✓ | 2✓ | 1✓ | 1✓ | 1✓ | ⊘ | ⊘ | ⊘ | ❌ |
| les02 | 2✓ | 1✓ | 1✓ | 1✓ | ⊘ | ⊘ | ⊘ | ⊘ | ❌ |
| les03 | 1✓ | 1✓ | 2✓ | 1✓ | 1✓ | ⊘ | ⊘ | ⊘ | ❌ |
| les04 | 1✓ | 1✓ | 1✓ | 1✓ | 1✓ | ⊘ | ⊘ | ⊘ | ❌ |
| les05 | 1✓ | 1✓ | 2✓ | 1✓ | 1✓ | ⊘ | ⊘ | ⊘ | ❌ |
| les06 | 1✓ | 1✓ | 2✓ | 1✓ | 1✓ | ⊘ | ⊘ | ⊘ | ❌ |
| les07 | 1✓ | 1✓ | 1✓ | 1✓ | 1✓ | ⊘ | ⊘ | 1✓ | ❌ |
| les08 | 1✓ | 1✓ | 1✓ | 1✓ | 1✓ | ⊘ | ⊘ | ⊘ | ❌ |
| les09 | 1✓ | 1✓ | 1✓ | 1✓ | ⊘ | ⊘ | ⊘ | 2✓ | ❌ |
| les10 | 1✓ | 2✓ | 1✓ | 1✓ | ⊘ | ⊘ | ⊘ | ⊘ | ❌ |
| les11 | 1✓ | 1✓ | 1✓ | 1✓ | ⊘ | ⊘ | ⊘ | ⊘ | ❌ |
| les12 | 1✓ | 1✓ | 1✓ | 1✓ | ⊘ | ⊘ | ⊘ | ⊘ | ❌ |
| les13 | 2✓ | 1✓ | 1✓ | 1✓ | ⊘ | ⊘ | ⊘ | ⊘ | ❌ |
| les14 | 1✓ | 2✓ | 1✓ | 1✓ | ⊘ | ⊘ | ⊘ | ⊘ | ❌ |
| les15 | 1✓ | 2✓ | 1✓ | 1✓ | ⊘ | ⊘ | ⊘ | ⊘ | ❌ |
| les16 | 1✓ | 1✓ | 1✓ | 1✓ |  — |  — |  — |  — | ❌ |
| les17 | 1✓ | 1✓ |  — | 1✓ |  — | 2✓ |  — |  — | ❌ |
| les18 | 1✓ | 1✓ | 1✓ | 1✓ |  — |  — |  — |  — | ❌ |
| les19 | 1✓ | 1✓ | 1✓ | 1✓ |  — |  — |  — |  — | ❌ |
| les20 | 1✓ | 2✓ |  — | 1✓ |  — |  — |  — |  — | ❌ |
| les21 | 1✓ | 1✓ |  — | 1✓ |  — |  — | 1✓ |  — | ❌ |
| les22 | 1✓ | 1✓ |  — | 1✓ |  — |  — |  — | 1✓ | ❌ |
| les23 | 1✓ | 2✓ |  — | 1✓ |  — |  — |  — |  — | ❌ |
| les24 | 1✓ | 1✓ | 1✓ | 1✓ |  — |  — |  — |  — | ❌ |
| les25 | 1✓ | 1✓ |  — | 1✓ |  — |  — |  — |  — | ❌ |
| les26 | 1✓ | 1✓ |  — | 1✓ |  — |  — |  — |  — | ❌ |
| les27 | 1✓ | 1✓ |  — | 1✓ |  — |  — |  — |  — | ❌ |
| les28 | 1✓ | 1✓ |  — | 1✓ |  — |  — |  — |  — | ❌ |
| les29 | 1✓ | 1✓ | 1✓ | 1✓ |  — |  — |  — |  — | ❌ |
| les30 | 2✓ | 1✓ | 1✓ | 1✓ |  — |  — |  — |  — | ❌ |

### Type coverage across all 30 lessons
- 📝 **quiz**: 33 total files, 30/30 lessons covered  ██████████████████████████████
- 🏷️ **drag-categories**: 36 total files, 30/30 lessons covered  ██████████████████████████████
- 🔢 **drag-order**: 24 total files, 21/30 lessons covered  █████████████████████░░░░░░░░░
- 🃏 **memory**: 30 total files, 30/30 lessons covered  ██████████████████████████████
- 🎮 **grid-game**: 7 total files, 7/30 lessons covered  ███████░░░░░░░░░░░░░░░░░░░░░░░
- 🔤 **falling-letters**: 2 total files, 1/30 lessons covered  █░░░░░░░░░░░░░░░░░░░░░░░░░░░░░
- 🎯 **click-game**: 1 total files, 1/30 lessons covered  █░░░░░░░░░░░░░░░░░░░░░░░░░░░░░
- 🎨 **canvas-pixels**: 4 total files, 3/30 lessons covered  ███░░░░░░░░░░░░░░░░░░░░░░░░░░░

> ⊘ = skipped (δεν ταιριάζει θεματικά στο μάθημα)

**Summary**: 137 total files, ~123 type-lesson combos satisfied / 240 target  |  Lessons fully complete: **0/30**  |  **~117 new activities needed**

---

## TPE-dimD-PEDIO — Δ' Δημοτικού

Full set (7 types): 📝 quiz | 🏷️ drag-categories | 🔢 drag-order | 🃏 memory | 🎮 grid-game | 🎯 click-game | 🎨 canvas-pixels
Target: 7 types × 30 lessons = **210** (one of each type per lesson)

| Lesson | 📝 | 🏷️ | 🔢 | 🃏 | 🎮 | 🎯 | 🎨 | OK? |
|-------|----|----|----|----|----|----|----|----|
| les01 | 1✓ | 1✓ | 2✓ | 1✓ | 1✓ | 1✓ | 1✓ | ✅ |
| les02 | 1✓ | 1✓ | 2✓ | 1✓ |  — |  — |  — | ❌ |
| les03 | 1✓ | 1✓ | 1✓ | 1✓ |  — |  — |  — | ❌ |
| les04 | 1✓ | 1✓ | 1✓ | 1✓ |  — |  — |  — | ❌ |
| les05 | 1✓ | 1✓ | 1✓ | 1✓ |  — |  — |  — | ❌ |
| les06 | 1✓ | 1✓ | 1✓ | 1✓ |  — |  — |  — | ❌ |
| les07 | 1✓ | 1✓ | 2✓ | 1✓ |  — |  — |  — | ❌ |
| les08 | 1✓ | 1✓ |  — | 1✓ | 1✓ |  — |  — | ❌ |
| les09 | 1✓ | 1✓ | 1✓ | 1✓ | 1✓ |  — |  — | ❌ |
| les10 | 1✓ | 1✓ | 1✓ | 1✓ | 1✓ |  — |  — | ❌ |
| les11 | 1✓ | 1✓ |  — | 1✓ |  — |  — | 1✓ | ❌ |
| les12 | 1✓ | 1✓ | 1✓ | 1✓ |  — |  — |  — | ❌ |
| les13 | 1✓ | 1✓ |  — | 1✓ |  — |  — |  — | ❌ |
| les14 | 1✓ | 1✓ | 1✓ | 1✓ |  — |  — |  — | ❌ |
| les15 | 1✓ | 1✓ | 1✓ | 1✓ |  — | 1✓ |  — | ❌ |
| les16 | 1✓ | 1✓ | 1✓ | 1✓ |  — |  — |  — | ❌ |
| les17 | 1✓ | 1✓ | 1✓ | 1✓ |  — |  — |  — | ❌ |
| les18 | 1✓ | 1✓ | 1✓ | 1✓ |  — |  — |  — | ❌ |
| les19 | 1✓ | 1✓ | 2✓ | 1✓ |  — |  — |  — | ❌ |
| les20 | 1✓ | 1✓ |  — | 1✓ |  — |  — |  — | ❌ |
| les21 | 1✓ | 1✓ |  — | 1✓ |  — | 1✓ |  — | ❌ |
| les22 | 2✓ | 1✓ |  — | 1✓ |  — |  — |  — | ❌ |
| les23 | 1✓ | 1✓ |  — | 1✓ |  — |  — |  — | ❌ |
| les24 | 1✓ | 1✓ | 1✓ | 1✓ |  — |  — |  — | ❌ |
| les25 | 1✓ | 1✓ |  — | 1✓ |  — | 1✓ |  — | ❌ |
| les26 | 1✓ | 1✓ |  — | 1✓ |  — |  — |  — | ❌ |
| les27 | 1✓ | 1✓ |  — | 1✓ |  — |  — |  — | ❌ |
| les28 | 2✓ | 1✓ |  — | 1✓ |  — |  — |  — | ❌ |
| les29 | 1✓ | 2✓ |  — | 1✓ |  — |  — |  — | ❌ |
| les30 | 1✓ | 1✓ |  — | 1✓ |  — | 1✓ |  — | ❌ |

### Type coverage across all 30 lessons
- 📝 **quiz**: 32 total files, 30/30 lessons covered  ██████████████████████████████
- 🏷️ **drag-categories**: 31 total files, 30/30 lessons covered  ██████████████████████████████
- 🔢 **drag-order**: 21 total files, 17/30 lessons covered  █████████████████░░░░░░░░░░░░░
- 🃏 **memory**: 30 total files, 30/30 lessons covered  ██████████████████████████████
- 🎮 **grid-game**: 4 total files, 4/30 lessons covered  ████░░░░░░░░░░░░░░░░░░░░░░░░░░
- 🎯 **click-game**: 5 total files, 5/30 lessons covered  █████░░░░░░░░░░░░░░░░░░░░░░░░░
- 🎨 **canvas-pixels**: 2 total files, 2/30 lessons covered  ██░░░░░░░░░░░░░░░░░░░░░░░░░░░░

**Summary**: 118 type-lesson combos satisfied / 210 target  |  Lessons fully complete: **1/30**  |  **92 new activities needed**

---

## TPE-dimE-PEDIO — Ε' Δημοτικού

Full set (7 types): 📝 quiz | 🏷️ drag-categories | 🔢 drag-order | 🃏 memory | 🎯 click-game | 🕹️ arcade-game | 📱 qr-scanner
Target: 7 types × 30 lessons = **210** (one of each type per lesson)

| Lesson | 📝 | 🏷️ | 🔢 | 🃏 | 🎯 | 🕹️ | 📱 | OK? |
|-------|----|----|----|----|----|----|----|----|
| les01 | 1✓ | 1✓ |  — | 1✓ |  — |  — |  — | ❌ |
| les02 | 1✓ | 1✓ | 1✓ | 1✓ |  — |  — |  — | ❌ |
| les03 | 1✓ | 2✓ |  — | 1✓ |  — |  — |  — | ❌ |
| les04 | 1✓ | 1✓ | 1✓ | 1✓ |  — | 1✓ |  — | ❌ |
| les05 | 1✓ | 1✓ | 1✓ | 1✓ |  — | 1✓ |  — | ❌ |
| les06 | 1✓ | 1✓ | 1✓ | 1✓ |  — | 1✓ |  — | ❌ |
| les07 | 1✓ | 1✓ | 1✓ | 1✓ |  — | 1✓ |  — | ❌ |
| les08 | 1✓ | 1✓ | 1✓ | 1✓ |  — |  — |  — | ❌ |
| les09 | 1✓ | 1✓ | 1✓ | 1✓ |  — |  — |  — | ❌ |
| les10 | 1✓ | 1✓ | 1✓ | 1✓ |  — |  — |  — | ❌ |
| les11 | 1✓ | 1✓ | 2✓ | 1✓ |  — |  — |  — | ❌ |
| les12 | 1✓ | 1✓ |  — | 1✓ |  — |  — |  — | ❌ |
| les13 | 1✓ | 1✓ |  — | 1✓ |  — |  — |  — | ❌ |
| les14 | 1✓ | 1✓ | 2✓ | 1✓ |  — |  — |  — | ❌ |
| les15 | 1✓ | 2✓ |  — | 1✓ |  — |  — |  — | ❌ |
| les16 | 1✓ | 1✓ |  — | 1✓ |  — |  — |  — | ❌ |
| les17 | 1✓ | 1✓ | 1✓ | 1✓ | 1✓ |  — |  — | ❌ |
| les18 | 1✓ | 1✓ |  — | 1✓ |  — |  — |  — | ❌ |
| les19 | 1✓ | 1✓ |  — | 1✓ |  — |  — |  — | ❌ |
| les20 | 1✓ | 1✓ |  — | 1✓ |  — |  — |  — | ❌ |
| les21 | 1✓ | 2✓ |  — | 1✓ |  — |  — |  — | ❌ |
| les22 | 1✓ | 2✓ |  — | 1✓ |  — |  — |  — | ❌ |
| les23 | 1✓ | 1✓ |  — | 1✓ |  — |  — |  — | ❌ |
| les24 | 2✓ | 1✓ | 1✓ | 1✓ |  — |  — |  — | ❌ |
| les25 | 1✓ | 1✓ | 1✓ | 1✓ |  — |  — |  — | ❌ |
| les26 | 1✓ | 1✓ |  — | 1✓ |  — |  — |  — | ❌ |
| les27 | 1✓ | 1✓ |  — | 1✓ |  — |  — |  — | ❌ |
| les28 | 1✓ | 1✓ |  — | 1✓ |  — |  — | 1✓ | ❌ |
| les29 | 2✓ | 1✓ |  — | 1✓ |  — |  — |  — | ❌ |
| les30 | 2✓ | 1✓ |  — | 1✓ |  — |  — |  — | ❌ |

### Type coverage across all 30 lessons
- 📝 **quiz**: 33 total files, 30/30 lessons covered  ██████████████████████████████
- 🏷️ **drag-categories**: 34 total files, 30/30 lessons covered  ██████████████████████████████
- 🔢 **drag-order**: 15 total files, 13/30 lessons covered  █████████████░░░░░░░░░░░░░░░░░
- 🃏 **memory**: 30 total files, 30/30 lessons covered  ██████████████████████████████
- 🎯 **click-game**: 1 total files, 1/30 lessons covered  █░░░░░░░░░░░░░░░░░░░░░░░░░░░░░
- 🕹️ **arcade-game**: 4 total files, 4/30 lessons covered  ████░░░░░░░░░░░░░░░░░░░░░░░░░░
- 📱 **qr-scanner**: 1 total files, 1/30 lessons covered  █░░░░░░░░░░░░░░░░░░░░░░░░░░░░░

**Summary**: 109 type-lesson combos satisfied / 210 target  |  Lessons fully complete: **0/30**  |  **101 new activities needed**

---

## TPE-dimST-PEDIO — ΣΤ' Δημοτικού

Full set (7 types): 📝 quiz | 🏷️ drag-categories | 🔢 drag-order | 🎯 click-game | 🔢 guess-number | 📱 qr-scanner | 📊 spreadsheet-filter
Target: 7 types × 30 lessons = **210** (one of each type per lesson)

| Lesson | 📝 | 🏷️ | 🔢 | 🎯 | 🔢 | 📱 | 📊 | OK? |
|-------|----|----|----|----|----|----|----|----|
| les01 | 1✓ |  — | 2✓ |  — |  — |  — |  — | ❌ |
| les02 | 1✓ | 1✓ |  — |  — | 1✓ |  — |  — | ❌ |
| les03 | 1✓ |  — | 1✓ |  — |  — |  — |  — | ❌ |
| les04 | 1✓ |  — | 2✓ |  — |  — |  — |  — | ❌ |
| les05 | 1✓ |  — | 1✓ |  — |  — |  — |  — | ❌ |
| les06 | 1✓ |  — | 1✓ |  — | 1✓ |  — |  — | ❌ |
| les07 | 1✓ |  — | 1✓ |  — |  — |  — |  — | ❌ |
| les08 | 1✓ | 1✓ |  — |  — |  — |  — |  — | ❌ |
| les09 | 1✓ |  — |  — |  — |  — |  — |  — | ❌ |
| les10 | 1✓ |  — | 1✓ |  — |  — |  — |  — | ❌ |
| les11 | 1✓ |  — |  — |  — |  — |  — |  — | ❌ |
| les12 | 1✓ | 1✓ |  — |  — |  — |  — |  — | ❌ |
| les13 | 1✓ | 1✓ | 1✓ |  — |  — |  — |  — | ❌ |
| les14 | 1✓ | 2✓ |  — |  — |  — |  — |  — | ❌ |
| les15 | 1✓ | 1✓ |  — |  — |  — |  — |  — | ❌ |
| les16 | 1✓ | 1✓ |  — |  — |  — |  — |  — | ❌ |
| les17 | 2✓ |  — | 1✓ |  — |  — |  — |  — | ❌ |
| les18 | 2✓ | 1✓ |  — |  — |  — |  — |  — | ❌ |
| les19 | 1✓ |  — |  — |  — |  — |  — | 2✓ | ❌ |
| les20 | 1✓ | 1✓ | 1✓ |  — |  — |  — |  — | ❌ |
| les21 | 1✓ | 1✓ |  — |  — |  — | 1✓ |  — | ❌ |
| les22 | 1✓ | 1✓ |  — |  — |  — |  — |  — | ❌ |
| les23 | 1✓ | 1✓ |  — |  — |  — |  — |  — | ❌ |
| les24 | 1✓ | 1✓ |  — |  — |  — |  — |  — | ❌ |
| les25 | 1✓ | 2✓ |  — |  — |  — |  — |  — | ❌ |
| les26 | 1✓ | 1✓ |  — | 1✓ |  — |  — |  — | ❌ |
| les27 | 1✓ |  — |  — |  — |  — |  — |  — | ❌ |
| les28 | 1✓ | 1✓ |  — |  — |  — |  — |  — | ❌ |
| les29 | 2✓ | 1✓ |  — |  — |  — |  — |  — | ❌ |
| les30 | 1✓ | 1✓ |  — |  — |  — |  — |  — | ❌ |

### Type coverage across all 30 lessons
- 📝 **quiz**: 33 total files, 30/30 lessons covered  ██████████████████████████████
- 🏷️ **drag-categories**: 20 total files, 18/30 lessons covered  ██████████████████░░░░░░░░░░░░
- 🔢 **drag-order**: 12 total files, 10/30 lessons covered  ██████████░░░░░░░░░░░░░░░░░░░░
- 🎯 **click-game**: 1 total files, 1/30 lessons covered  █░░░░░░░░░░░░░░░░░░░░░░░░░░░░░
- 🔢 **guess-number**: 2 total files, 2/30 lessons covered  ██░░░░░░░░░░░░░░░░░░░░░░░░░░░░
- 📱 **qr-scanner**: 1 total files, 1/30 lessons covered  █░░░░░░░░░░░░░░░░░░░░░░░░░░░░░
- 📊 **spreadsheet-filter**: 2 total files, 1/30 lessons covered  █░░░░░░░░░░░░░░░░░░░░░░░░░░░░░

**Summary**: 63 type-lesson combos satisfied / 210 target  |  Lessons fully complete: **0/30**  |  **147 new activities needed**

---

## Grand Summary

| Class | Types | Target | Existing | Needed | Full lessons | Coverage |
|---|---|---|---|---|---|---|
| Α' Δημοτικού | 8 | 240 | 76 | **164** | 0/30 | 31% |
| Β' Δημοτικού | 8 | 240 | 112 | **128** | 0/30 | 47% |
| Γ' Δημοτικού | 8 | 240 | 137 | **103** | 0/30 | 57% |
| Δ' Δημοτικού | 7 | 210 | 97 | **113** | 0/30 | 46% |
| Ε' Δημοτικού | 7 | 210 | 109 | **101** | 0/30 | 52% |
| ΣΤ' Δημοτικά | 7 | 210 | 63 | **147** | 0/30 | 30% |
| **TOTAL** | **—** | **1350** | **582** | **768** | **—** | **43%** |
