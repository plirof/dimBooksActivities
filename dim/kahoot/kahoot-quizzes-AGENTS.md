# Instructions :
## Always update progress to progress.md.
## Make a "backup_" folder where you put the original files before you edit them.Number the files if already exist eg myfile01.php ,myfile02.php etc.
## "backup_" folder will always be ignored. Will only be used to copy backup files.
## In new sessions check progress.md to see that have been done so far. plan.md also contain plans on what to do next.


# Kahoot Quiz Generation — AI Agent Guide

This guide instructs AI agents on how to generate Kahoot-format quiz JSON files
for the Greek elementary school Informatics/ICT curriculum (Πληροφορική Δημοτικού).

The quizzes are used in a Kahoot-style quiz application (KaraQuiz). Each quiz
corresponds to one lesson from the official curriculum (6 classes × 30 lessons = 180 quizzes).

---

## 1. Project Structure

```
./
├── json_quiz_format.txt           ← JSON format specification (READ THIS)
├── kahoot-quizzes-progress.md      ← Progress tracking (UPDATE THIS)
├── data/quiz/                      ← Output folder for quiz JSONs
│   ├── dimA-les01-kahoot-XXX.json
│   ├── dimA-les02-kahoot-YYY.json
│   └── ...

../dimBooksActivities/
├── summary_A.md                       ← Reference only
├── summary_B.md                       ← Reference only
├── ...
├── TPE-dimA-PEDIO/lessons_seperated/  ← Content source (READ THIS)
│   ├── lesson01.html
│   ├── lesson02.html
│   └── ...
├── TPE-dimB-PEDIO/lessons_seperated/  ← Content source (READ THIS)
└── ...
```

---

## 2. Content Sources

The primary sources are the lesson HTML files in `TPE-dimX-PEDIO/lessons_seperated/`.
Each is the full lesson text from the official Greek Ministry of Education ICT textbook.
Read the relevant `lessonNN.html` file to extract key concepts, facts, and terminology.

The files cover these topic areas (with increasing difficulty per class):

1. **Αλγοριθμική–Προγραμματισμός–Ρομποτική**: Algorithms, Scratch, ScratchJr, Edison robots, micro:bit
2. **Υπολογιστικά συστήματα–Ψηφιακές συσκευές–Δίκτυα**: Hardware, software, networks, internet, security
3. **Δεδομένα–Ανάλυση δεδομένων**: Files, folders, concept maps, spreadsheets, AI
4. **Ψηφιακός γραμματισμός**: Keyboard, browser, word processor, presentations, e-me, distance learning
5. **Ψηφιακές τεχνολογίες–Κοινωνία**: Digital citizenship, safety, copyright, gov.gr, GDPR

The `summary_X.md` files in the same directory can be used as a quick reference
to understand the lesson scope before reading the full HTML.

---

## 3. Output Specifications

### Folder
All quiz JSON files go to `data/quiz/` in the kahoot012d project.

### Naming Convention
```
dimX-lesNN-kahoot-[3 random chars].json
```
Examples: `dimA-les01-kahoot-X7K.json`, `dimC-les15-kahoot-M2B.json`

- `dimX`: A, B, C, D, E, ST
- `lesNN`: 01–30 (zero-padded)
- `kahoot`: fixed keyword
- `[3 random chars]`: 3 random alphanumeric characters

### JSON Format

**Use the format from `json_quiz_format.txt` (in the project root).** This file
contains the complete specification: all fields, validation rules, and examples.

⚠️ **IMPORTANT: `json_quiz_format.txt` says `id` and `created_at` are
auto-generated and can be omitted — this is WRONG for files placed in
`data/quiz/`. The app reads these files directly and requires them to be
present. Always include them (see sample files).**

Quick notes for this project:
- `category`: always `"Πληροφορική"`
- `tags`: `"dimX, lesNN, [topic-keyword1], [topic-keyword2], Πληροφορική"`
- `randomize_questions`: `true`
- `randomize_answers`: `true`
- `allow_solo`: `true`

### Required Fields (from real app behavior)

These fields MUST be present in every quiz JSON file. Check sample files in `data/quiz/` for reference:

**Quiz-level:**
| Field | Required | Notes |
|-------|----------|-------|
| `id` | ✅ YES | Must match filename without `.json`, e.g. `dimA-les01-kahoot-X7K` |
| `title` | ✅ YES | Quiz title in Greek |
| `description` | ❌ No | Optional but good to have |
| `questions` | ✅ YES | Array of 10 question objects |
| `allow_solo` | ❌ No | Required for solo play. Set to `true` |
| `randomize_questions` | ❌ No | Set to `true` for shuffle |
| `randomize_answers` | ❌ No | Set to `true` for shuffle |
| `created_at` | ✅ YES | Current date, format: `"YYYY-MM-DD HH:MM:SS"` |

**Question-level:**
| Field | Required | Notes |
|-------|----------|-------|
| `id` | ✅ YES | Unique string per question. Convention: `{quiz-id}-q{NN}`, e.g. `dimA-les01-kahoot-X7K-q01` |
| `text` | ✅ YES | Question text in Greek |
| `answers` | ✅ YES | Array of 4 strings |
| `correct` | ✅ YES | 0-based index of correct answer |
| `explanation` | ❌ No | Shown after answering (supported by UI) |
| `time` | ❌ No | Default 20 if omitted |
| `points` | ❌ No | Default 1000 if omitted |

---

## 4. Quiz Structure Guidelines

Each quiz must have exactly 10 questions. Mix the following question types:

### Type A: Multiple Choice (4 options)
Standard 4-option multiple choice. Most common type.

### Type B: "Which of the following..." (4 options)
A question stem ending with a question, 4 answer choices.

### Difficulty Distribution (per quiz)
- 3-4 Easy questions (recall facts directly from the lesson)
- 4-5 Medium questions (apply concepts, match definitions)
- 1-2 Hard questions (synthesis, distinguish similar concepts)

### Standalone Questions Rule (IMPORTANT)
Every question must be **fully self-contained and understandable without the book open**. Never reference:
- Specific page numbers, figures, or diagrams from the textbook
- Named characters/examples only found in the book (e.g., "Έλλη", "Αστερίας") — use generic terms (e.g., "ο ήρωας", "ο αντίπαλος")
- Exercise numbers or "Βάση της άσκησης του βιβλίου"
- "Στο παράδειγμα του μαθήματος" — rephrase to describe the concept generally

Good: "Πώς μπορεί ένας αντίπαλος να εμφανίζεται σε τυχαία θέση στο Scratch;"
Bad: "Βάση της άσκησης του βιβλίου: Τι κάνει ο Αστερίας όταν εμφανίζεται στο παιχνίδι;"

### No Implementation-Specific Questions (IMPORTANT)
Questions must test **general concepts and principles**, NOT recall of specific implementation details from the book's programming examples. A student who understands the theory must be able to answer every question **without having done the specific programming exercise in class**. Do NOT ask about:
- Which specific button was programmed to do something (e.g., "Ποιο κουμπί εμφανίζει τη θερμοκρασία;")
- Which specific variable name was used (e.g., "Πώς ονομάσαμε τη μεταβλητή;")
- Which specific character/object was chosen (e.g., "Ποιος χαρακτήρας χρησιμοποιήθηκε;")
- Which specific threshold/number was set in the example (e.g., "Ποια τιμή ορίσαμε ως όριο;")
- Which specific algorithm variation was implemented (e.g., "Ποιος διαλέχει τον αριθμό στο παιχνίδι;")

Instead, ask about the **general principle**:
- Good: "Τι κάνει η εντολή επιλογής στο micro:bit όταν συνδυάζεται με αισθητήρα;"
- Bad: "Ποιο κουμπί πατήσαμε για να δούμε τη μέγιστη θερμοκρασία;"
- Good: "Πώς μπορεί ένα παιχνίδι να γίνεται πιο δύσκολο προγραμματιστικά;"
- Bad: "Ποιος διαλέγει τον μυστικό αριθμό στο παιχνίδι;"

### Topic Coverage (per quiz)
Cover the key concepts from the specific lesson. Read the full lesson HTML file to understand the lesson scope better. Don't repeat the same concept
in multiple questions. Use a summary file if you want to make sure you covered the most important items.

---

## 5. Workflow

1. **Read** the lesson HTML from `TPE-dimX-PEDIO/lessons_seperated/lessonNN.html`
2. **Read** the progress file `kahoot-quizzes-progress.md` to confirm this quiz is not yet created
3. **Generate** the 10-question JSON file following `json_quiz_format.txt`
4. **Write** it to `data/quiz/dimX-lesNN-kahoot-XXX.json`
5. **Verify** the JSON is valid (parse it, check all fields)
6. **Update** `kahoot-quizzes-progress.md`:
   - Change status from `—` to `✓`
   - Fill in the filename in the "Filename" column

### Order of generation
Go class by class: dimA → dimB → dimC → dimD → dimE → dimST in the order that the user will tell you each time
Within each class: les01 → les02 → ... → les30

---

## 6. Prompt Template for Generating One Quiz

Use this prompt structure for generating one quiz:

```
Read the lesson HTML file TPE-dimX-PEDIO/lessons_seperated/lessonNN.html
and create a Kahoot-format quiz for lesson NN.

Lesson NN title: [TITLE]

Generate a JSON file with exactly 10 multiple-choice questions in Greek.
Use the format from json_quiz_format.txt (read the file first).

Quiz settings:
- category: "Πληροφορική"
- tags: "dimX, lesNN, [keywords], Πληροφορική"
- randomize_questions: true
- randomize_answers: true
- allow_solo: true

Rules:
- All questions and answers in Greek
- Include explanations for correct answers
- All questions must have exactly 4 answer options
- 4 questions: multiple choice (4 options), easy
- 4 questions: multiple choice (4 options), medium
- 2 questions: multiple choice (4 options), hard
- correct = 0-based index (must be < number of answers)
- time = 20 (default), use 30 or 60 for harder questions
- points = 1000
- DO NOT include updated_at fields
- id field: top-level MUST be the filename (without .json), e.g. "dimA-les01-kahoot-X7K"
- Each question MUST have a unique id field. Convention: "{quiz-id}-q{NN}", e.g. "dimA-les01-kahoot-X7K-q01"
- created_at field: REQUIRED, set to current date in format "YYYY-MM-DD HH:MM:SS"
- Output ONLY valid JSON, no markdown fences, no commentary

Content source (read this HTML file):
[PATH TO lessonNN.html]
```

---

## 7. Importing Generated Quizzes

Once the JSON file is in `data/quiz/`, it is automatically available in the app.
The app reads all `.json` files from `data/quiz/` directory.

To test a quiz:
1. Start the app
2. Log in as admin
3. Go to Manage Quizzes → the quiz should appear in the list
4. "Allow Solo Mode" is enabled, so it can also be played via "Play Solo Quiz"

---

## 8. Quick Reference

| Class | Code | Lessons Source | Lesson Range |
|-------|------|---------------|--------------|
| Α' Δημοτικού | dimA | TPE-dimA-PEDIO/lessons_seperated/ | les01–les30 |
| Β' Δημοτικού | dimB | TPE-dimB-PEDIO/lessons_seperated/ | les01–les30 |
| Γ' Δημοτικού | dimC | TPE-dimC-PEDIO/lessons_seperated/ | les01–les30 |
| Δ' Δημοτικού | dimD | TPE-dimD-PEDIO/lessons_seperated/ | les01–les30 |
| Ε' Δημοτικού | dimE | TPE-dimE-PEDIO/lessons_seperated/ | les01–les30 |
| ΣΤ' Δημοτικού | dimST | TPE-dimST-PEDIO/lessons_seperated/ | les01–les30 |

**Target:** 180 quizzes (6 classes × 30 lessons)
**Per quiz:** 10 questions
**Total questions:** 1800
