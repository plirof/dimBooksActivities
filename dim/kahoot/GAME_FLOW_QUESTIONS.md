# 🎯 Game Flow Questions Answered

## Question 1: Auto-Progression

**Q:** Does the next question proceed automatically, or only if I press "Next Question" from the admin interface?

**A:** Currently it's **BROKEN** - it only proceeds if admin clicks "Next Question". Students get stuck on "Waiting for next question..."

**The Problem:**
When admin clicks "Next Question" button in `admin/game_control.php`:
- It increments local variable: `currentQuestion++` (line 338)
- It shows the next question locally: `showQuestion()` (line 339)
- **BUT it does NOT call the API to advance the server state**
- Students are still polling the **old question number**
- Result: Students stuck waiting...

**The Fix:**
Admin needs to call API's `next_question` action to advance server state:

```javascript
// Current (BROKEN):
function nextQuestion() {
    currentQuestion++;
    showQuestion();  // Only local, doesn't update server
}

// Should be:
function nextQuestion() {
    fetch('../api/game.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=next_question&pin=' + gamePin
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            currentQuestion++;  // Increment after confirming
            showQuestion();
        }
    });
}
```

**Note:** The API already has a `next_question` action at `api/game.php` (lines 103-136) that properly advances the server state!

## Question 2: Changing Answer Time

**Q:** Can I change the answer time of quiz questions?

**A:** **YES**, by editing the quiz questions directly.

**How Quiz Questions Work:**
Each question has a configurable `time` field (in seconds):

```json
{
  "id": "sample_math_001",
  "title": "Basic Math Challenge",
  "questions": [
    {
      "id": "698233f58d61d0.99282019",
      "text": "What is 15 + 27?",
      "answers": ["32", "42", "45", "52"],
      "correct": 1,
      "time": 20,        // ← THIS IS THE TIME LIMIT
      "points": 1000
    }
  ]
}
```

**Options to Change Question Time:**

### Option 1: Edit Quiz (Recommended) ✅
1. Go to: `http://localhost/img/kahoot/admin/manage_quizzes.php`
2. Find your quiz
3. Click "Edit" button
4. Change the `time` value for each question
5. Save the quiz
6. **Done!** Next time you host this quiz, new times apply

### Option 2: Edit JSON File Directly
1. Edit: `kahoot/data/quizzes.json`
2. Find your quiz
3. Change `"time": 20` to desired value (10-60 seconds)
4. Save the file
5. **Done!**

**Example Changes:**
```json
// Before:
{
  "text": "What is 15 + 27?",
  "answers": ["32", "42", "45", "52"],
  "correct": 1,
  "time": 20
}

// After (change to 30 seconds):
{
  "text": "What is 15 + 27?",
  "answers": ["32", "42", "45", "52"],
  "correct": 1,
  "time": 30  // ← Changed to 30
}
```

**Valid Range:** 10 to 60 seconds

**Note:** Time changes apply to **all future games** using that quiz. It's quiz-level configuration, not game-level.

## 📋 How the Game Flow Should Work (Once Fixed)

### Admin Side:
1. Admin selects quiz and generates Game PIN
2. Students join via PIN
3. Admin clicks "Start Game"
4. **Question 1 appears on admin screen**
5. Timer counts down automatically
6. Admin sees real-time answer statistics
7. Students submit answers
8. When timer ends OR all students answer:
   - Show correct answer
   - Show answer distribution
   - Show leaderboard
9. **Admin clicks "Next Question"** OR **auto-proceed after delay**
10. **API is called to advance to next question** ← FIX NEEDED HERE
11. Repeat steps 4-10 for all questions
12. Game ends, final leaderboard shown

### Student Side:
1. Student enters PIN and nickname
2. Waits in waiting room
3. Polls for game status
4. When game starts → Sees Question 1
5. Sees question and answer buttons
6. Timer counts down
7. Selects answer
8. Gets correct/incorrect feedback
9. Sees points earned
10. Polls for next question
11. **When admin advances question → Automatically sees Question 2** ← SHOULD WORK
12. Repeat for all questions
13. Game ends, sees final leaderboard

## 🔧 The Fix Needed

**File:** `admin/game_control.php`
**Lines to modify:** 337-340

**Current code (BROKEN):**
```javascript
function nextQuestion() {
    currentQuestion++;
    showQuestion();  // ❌ Only updates local state
}
```

**Should be (FIXED):**
```javascript
function nextQuestion() {
    fetch('../api/game.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=next_question&pin=' + gamePin
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            currentQuestion++;  // ✅ Increment AFTER confirming with API
            showQuestion();
        } else {
            alert('Error advancing question: ' + data.error);
        }
    });
}
```

## 📚 Changing Question Time - Detailed Guide

### Method 1: Using Admin Interface (Best for Beginners)

1. Access admin panel: `http://localhost/img/kahoot/admin/manage_quizzes.php`
2. Find your quiz in the list
3. Click the "✏️ Edit" button
4. On the edit page:
   - Scroll to the question you want to change
   - Find the "⏱️ Time Limit (seconds)" field
   - Change the value (10-60 seconds)
   - Click "💾 Save Changes" button
5. Done! Next time you play this quiz, the new time applies

### Method 2: Editing JSON File (Advanced)

1. Open the file: `kahoot/data/quizzes.json`
2. Find your quiz by ID or title
3. For each question, find the `"time"` field
4. Change the value:
   ```json
   {
     "id": "...",
     "text": "...",
     "answers": [...],
     "correct": 1,
     "time": 20,     // ← Change this
     "points": 1000
   }
   ```
5. Save the file
6. Done!

### Example: Making Easy Questions Faster

**Before:**
```json
{
  "text": "What is 15 + 27?",
  "time": 20  // 20 seconds
}
```

**After (more time):**
```json
{
  "text": "What is 15 + 27?",
  "time": 60  // 60 seconds - gives more thinking time
}
```

**Example: Making Hard Questions Slower**

**Before:**
```json
{
  "text": "Calculate 25 × 47",
  "time": 20
}
```

**After (less time for hard questions):**
```json
{
  "text": "Calculate 25 × 47",
  "time": 30  // Only 30 seconds - adds pressure
}
```

## 💡 Tips for Setting Question Times

| Question Type | Recommended Time | Range |
|--------------|------------------|--------|
| Very easy | 10-15 seconds | 10-60 |
| Easy | 15-20 seconds | 10-60 |
| Medium | 20-30 seconds | 10-60 |
| Hard | 25-40 seconds | 10-60 |
| Very hard | 30-45 seconds | 10-60 |
| Math calculation | 30-40 seconds | 10-60 |
| Reading/Text | 20-30 seconds | 10-60 |

## 🎯 Summary

| Question | Answer | Status |
|----------|--------|--------|
| Auto-progression? | **BROKEN** - only works if admin clicks Next | ✅ Can fix |
| Change answer time? | ✅ YES - by editing quiz questions | ✅ Easy to do |
| Fix needed? | ✅ YES - call API's next_question action | ✅ Simple fix |

## 📁 Files Involved

**For auto-progression fix:**
- `admin/game_control.php` - Update `nextQuestion()` function
- `api/game.php` - Has the `next_question` action (already exists)

**For question time changes:**
- `data/quizzes.json` - Edit question `time` values
- OR use `admin/edit_quiz.php` - Edit in the UI
