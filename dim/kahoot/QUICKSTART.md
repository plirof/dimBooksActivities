# 🚀 Quick Start Guide

## 1. Setup (One-time)

```bash
# Navigate to project directory
cd /opt/lampp/htdocs/img/kahoot

# Load sample quizzes (optional but recommended)
php setup_sample_quizzes.php

# Verify files are created
ls -la data/ sessions/
```

## 2. Access the Application

Open your browser:
- Local: `http://localhost/img/kahoot/`
- Or your server URL: `http://your-server/kahoot/`

## 3. Login as Admin

**Default credentials:**
- Username: `admin`
- Password: `admin123`

## 4. Create Student Accounts

1. Go to "Users" → "Create New Student"
2. Enter username and password
3. Repeat for each student
4. Share credentials with students

## 5. Create Your First Quiz

1. Go to "Quizzes" → "Create New Quiz"
2. Enter title: "My First Quiz"
3. Add questions:
   - Question text
   - 4 answer options
   - Select correct answer
   - Set time limit (10-60 seconds)
4. Click "Add Another Question" for more
5. Save the quiz

## 6. Host a Game

1. Go to "Host Game"
2. Select your quiz
3. Click "Generate Game PIN" (e.g., 1234)
4. Show the PIN to students

## 7. Students Join

**Option A: With accounts:**
1. Students login with their credentials
2. Go to "Join Game"
3. Enter the Game PIN

**Option B: Quick join (no account):**
1. Go to `http://localhost/img/kahoot/student/join.php`
2. Enter Game PIN
3. Enter nickname

## 8. Start the Game

1. Teacher: Wait for students to join (see participant count)
2. Click "Start Game"
3. Question appears on teacher's screen
4. Students see question on their devices
5. Students tap the correct answer
6. Results shown after each question
7. Repeat for all questions
8. Final leaderboard displayed

## 💡 Tips

### For Teachers
- Test your quiz before hosting
- Use the waiting room to ensure everyone joined
- Start with sample quizzes to get familiar
- Keep questions short and clear
- Set appropriate time limits (20-30s average)

### For Students
- Read questions carefully
- Answer fast for more points!
- Watch your score after each question
- Check the leaderboard at the end

## 🎮 Sample Quizzes Included

1. **Basic Math Challenge** (5 questions)
   - Addition, subtraction, multiplication, division

2. **World Geography** (5 questions)
   - Countries, capitals, continents

3. **Fun Science Quiz** (5 questions)
   - Planets, nature, animals

## 🐛 Quick Troubleshooting

| Issue | Solution |
|-------|----------|
| Can't login | Check username/password (case-sensitive) |
| Game not found | Verify PIN is correct |
| Students can't join | Check if game is still "waiting" |
| Polling not working | Refresh browser, check internet |
| Can't create quiz | Check if fields are filled correctly |

## 📞 Need Help?

1. Check README.md for detailed documentation
2. Review progress.md for implementation details
3. Test with sample quizzes first
4. Check browser console (F12) for errors

---

**Ready to play! 🎉📚**
