# Wordboard Activity JSON Documentation

This document describes the JSON format used for Wordboard activities, enabling AI agents to generate activities programmatically.

## Activity ID Format

Activities follow this naming convention:

```
{identifier}-{type_code}.json
```

| Activity Type | Type Code | File Extension |
|--------------|----------|----------------|
| Missing Word | c6f1 | .json |
| Crossword | 8x1 | .json |
| Wheel | 7w1 | .json |
| Wordsearch | (various) | .json |
| Quiz | 3q1 | .json |
| Match | 4m1 | .json |
| Group Sort | 9g1 | .json |

Example: `dimA-les30-6def030c6f1.json`

- `dimA` - Dimension/grade
- `les30` - Lesson number
- `6def030` - Content hash
- `c6f1` - Type code

## Common Activity Structure

All activities share this base structure:

```json
{
    "id": "unique-activity-id",
    "title": "Activity Title - Type",
    "type": "activity_type",
    "created_by": "admin",
    "created_date": "YYYY-MM-DD",
    "data": { /* type-specific data */ },
    "tags": ["dimension", "lesson", "topic"]
}
```

---

## Activity Types

### 1. Missing Word (type: "missingword")

Fill in the blank exercises with multiple choice options.

```json
{
    "id": "dimA-les30-6def030c6f1",
    "title": "Title - Κενά",
    "type": "missingword",
    "created_by": "admin",
    "created_date": "2026-04-23",
    "data": {
        "sentences": [
            {
                "sentence": "Sentence with ___ to mark blank position",
                "options": ["Option1", "Option2", "Option3", "Option4"],
                "correct": "Option1"
            }
        ]
    },
    "tags": ["dimA", "les30", "topic"]
}
```

**Requirements:**
- Each sentence must have exactly 4 options
- Exactly one correct answer
- Use `___` (3 underscores) to mark the blank position in the sentence

---

### 2. Crossword (type: "crossword")

Crossword puzzle with grid and clues.

```json
{
    "id": "dimA-les30-6def030c8x1",
    "title": "Title - Σταυρόλεξο",
    "type": "crossword",
    "created_by": "admin",
    "created_date": "2026-04-23",
    "data": {
        "grid": [
            ["Π","#","Α","#"],
            ["Α","#","Ι","#"],
            ["Χ","Ν","Χ","Ι"],
            ["#","#","#","Δ","Ι"]
        ],
        "words": [
            { "number": 1, "direction": "across", "clue": "Clue text", "answer": "ΠΑΙΧΝΙΔΙ", "row": 0, "col": 0 }
        ]
    },
    "tags": ["dimA", "les30", "topic"]
}
```

**Requirements:**
- Grid uses `#` for empty cells
- Words include start position (row, col) and direction ("across")
- Use Greek uppercase letters (Α-Ω)

---

### 3. Wheel (type: "wheel")

Spinning wheel with segments for random selection.

```json
{
    "id": "dimA-les30-6def030c7w1",
    "title": "Title - Τροχός",
    "type": "wheel",
    "created_by": "admin",
    "created_date": "2026-04-23",
    "data": {
        "segments": ["Item1", "Item2", "Item3", "Item4", "Item5", "Item6", "Item7", "Item8"]
    },
    "tags": ["dimA", "les30", "topic"]
}
```

**Requirements:**
- Between 4-12 segments recommended
- Each segment is a string

---

### 4. Wordsearch (type: "wordsearch")

> **IMPORTANT**: For wordsearch activities, you MUST use the PHP script below to generate the grid. Do NOT create the grid manually.

Word search puzzle with letter grid and hidden words.

```json
{
    "id": "dimA-les30-a82771aef531",
    "title": "Title - Αναζητηση Λεξεων",
    "type": "wordsearch",
    "created_by": "admin",
    "created_date": "2026-04-23",
    "data": {
        "grid": [
            ["Α","Β","Γ","Δ","Ε","Ζ","Η","Θ","Ι","Κ","Λ","Μ"],
            ["Ν","Ξ","Ο","Π","Ρ","Σ","Τ","Υ","Φ","Χ","Ψ","Ω"]
        ],
        "words": ["WORD1", "WORD2", "WORD3"]
    },
    "tags": ["dimA", "les30", "topic"]
}
```

**Requirements:**
- **MUST use wordsearch_generator.php script** to generate the grid (see section below for full instructions)
- Grid is a 2D array of uppercase Greek letters
- Words array lists all words to find (uppercase, no accents)
- Grid size should accommodate all words (typically 10x10 to 15x15)

---

### 5. Quiz (type: "quiz")

Multiple choice questions with single correct answer.

```json
{
    "id": "dimA-les30-6def030c3q1",
    "title": "Title - Quiz",
    "type": "quiz",
    "created_by": "admin",
    "created_date": "2026-04-23",
    "data": {
        "questions": [
            {
                "question": "Question text",
                "options": ["Option1", "Option2", "Option3", "Option4"],
                "correct": 0
            }
        ]
    },
    "tags": ["dimA", "les30", "topic"]
}
```

**Requirements:**
- Each question has exactly 4 options
- `correct` is the 0-based index of the correct option
- Options should be ordered logically (e.g., alphabetical for numbers/dates)

---

### 6. Match (type: "match")

Matching pairs between two columns.

```json
{
    "id": "dimA-les30-6def030c4m1",
    "title": "Title - Αντιστοίχιση",
    "type": "match",
    "created_by": "admin",
    "created_date": "2026-04-23",
    "data": {
        "pairs": [
            { "left": "Left item", "right": "Right item" }
        ]
    },
    "tags": ["dimA", "les30", "topic"]
}
```

**Requirements:**
- Each pair has left and right strings
- All left items should be one category, all right items another category
- Minimum 3 pairs recommended

---

### 7. Group Sort (type: "groupsort")

Categorize items into groups.

```json
{
    "id": "dimA-les30-6def030c9g1",
    "title": "Title - Ομαδοποίηση",
    "type": "groupsort",
    "created_by": "admin",
    "created_date": "2026-04-23",
    "data": {
        "groups": [
            {
                "name": "Group Name",
                "items": ["Item1", "Item2", "Item3", "Item4"]
            }
        ]
    },
    "tags": ["dimA", "les30", "topic"]
}
```

**Requirements:**
- Each group has a name and array of items
- 2-4 groups recommended
- Each group should have 3-6 items

---

## Using wordsearch_generator.php to Generate Wordsearch JSON

> **CRITICAL**: When generating wordsearch activities, you MUST use the PHP script at `/opt/lampp/htdocs/img/wordboard/wordseach_generatorPHP/wordsearch_generator.php` to create the letter grid. Do NOT manually create the grid - always use this generator script.

The PHP script at `/opt/lampp/htdocs/img/wordboard/wordseach_generatorPHP/wordsearch_generator.php` generates wordsearch grids automatically.

### Basic Usage

```php
<?php
// Include or require the generator file
require_once 'wordsearch_generator.php';

$words = ['ΜΗΛΟ', 'ΣΠΙΤΙ', 'ΘΑΛΑΣΣΑ', 'ΒΟΥΝΟ', 'ΗΛΙΟΣ'];
$size = 12;
$grid = createEmptyGrid($size);

foreach ($words as $word) {
    placeWord($grid, $word, $size);
}

fillGrid($grid, $size);
printGrid($grid);
?>
```

### Output Format

The generator outputs a 2D array representing the letter grid and fills empty cells with random Greek letters.

### Generating JSON for Wordboard

After generating the grid, create the JSON file:

```php
$json = json_encode([
    "id" => "activity-id",
    "title" => "Activity Title - Αναζητηση Λεξεων",
    "type" => "wordsearch",
    "created_by" => "admin",
    "created_date" => date("Y-m-d"),
    "data" => [
        "grid" => $grid,
        "words" => array_map('mb_strtoupper', $words)
    ],
    "tags" => ["dimX", "lesYY", "topic"]
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

file_put_contents('output.json', $json);
```

### Notes

- Greek words must be uppercase without diacritics
- Size should be at least 10x10, preferably 12x12 or larger
- The generator places words horizontally, vertically, or diagonally
- If a word cannot fit after 100 attempts, it skips that word

---

## File Naming Convention

| Type | Code Pattern |
|------|-------------|
| dimA | 6def + lesson + type |
| dimB | 6b + lesson + type |
| dimC | 6abc + lesson + type |
| dimD | 6abc + lesson + type |
| dimE | 6abc + lesson + type |

---

## Tips for AI Agents

1. **Use Greek Uppercase**: All text for display (answers, grid) should be Greek uppercase (Α-Ω)
2. **Remove Diacritics**: When creating grids/clues, remove accents (ά → Α, έ → Ε, etc.)
3. **No Special Characters**: Avoid punctuation in grid cells and answers
4. **Date Format**: Use YYYY-MM-DD (e.g., "2026-04-23")
5. **Valid ID**: Use a unique ID matching the naming convention
6. **Tags**: Include dimension (dimX), lesson number (lesYY), and topic tag
7. **MANDATORY for Wordsearch**: You MUST use the `wordsearch_generator.php` script to generate wordsearch grids. Execute the PHP script to create the grid, then use the output grid in your JSON file.