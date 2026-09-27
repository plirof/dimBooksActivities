# Kahoot Clone Implementation Plan

## Project Overview
Create a Kahoot-like quiz application for local classroom use using HTML, JavaScript, and PHP with flat file storage. Target browser: SRWare Iron 61.

## Technology Stack
- **Frontend**: HTML5, ES5/ES6 JavaScript
- **Backend**: PHP 7.x
- **Storage**: Flat files (JSON format)
- **Communication**: AJAX polling (no WebSockets due to Apache config)
- **Browser**: SRWare Iron 61 (Chromium 61 - ES5/ES6 compatible)

## Architecture Overview

```
kahoot/
├── index.php              # Main entry point (login screen)
├── admin/
│   ├── dashboard.php      # Admin dashboard
│   ├── create_quiz.php    # Create/edit quiz
│   ├── manage_users.php   # Manage student accounts
│   ├── game_control.php   # Host game control panel
│   └── results.php        # View game results
├── student/
│   ├── join.php           # Join game screen
│   ├── waiting.php        # Waiting room
│   ├── game.php           # Main game interface
│   └── results.php        # Student results
├── api/
│   ├── auth.php           # Authentication API
│   ├── quiz.php           # Quiz CRUD operations
│   ├── game.php           # Game state management
│   └── poll.php           # Polling endpoint for real-time updates
├── data/
│   ├── users.json         # User accounts
│   ├── quizzes.json       # Quiz data
│   ├── games.json         # Active game sessions
│   └── sessions/          # Per-game session files
├── js/
│   ├── common.js          # Shared utilities
│   ├── admin.js           # Admin functionality
│   ├── student.js         # Student functionality
│   └── game.js            # Game logic
├── css/
│   ├── style.css          # Main stylesheet
│   ├── admin.css          # Admin styles
│   └── game.css           # Game-specific styles
└── config.php             # Configuration file
```

## Core Features

### 1. User Management System
**Admin Account**
- Default admin: admin/admin123
- Can create student accounts
- Cannot be deleted
- Full access to all features

**Student Accounts**
- Created by admin only
- Username and password (fixed, cannot be changed)
- Can join games
- Can view personal results
- Cannot create quizzes

### 2. Quiz Management
**Quiz Structure**
- Quiz title and description
- Multiple choice questions (2-4 answers each)
- Time limit per question (10-60 seconds)
- Points per question (100-1000 based on speed + accuracy)

**Question Types**
- Standard multiple choice
- Image support for questions (optional)
- Correct answer indicator
- Optional explanation text

### 3. Game Flow
**Phase 1: Game Setup**
- Admin selects quiz
- Generates unique game PIN (4-digit)
- Admin shows PIN to students

**Phase 2: Joining**
- Students enter username and PIN
- Students placed in waiting room
- Real-time participant list updates

**Phase 3: Gameplay**
- Admin shows question
- Timer counts down
- Students select answer
- Answer locked when submitted or timer ends
- Students cannot see others' answers

**Phase 4: Results**
- Show correct answer
- Show answer distribution
- Show points earned
- Update leaderboard
- Continue to next question

**Phase 5: Final Results**
- Show leaderboard (top 5)
- Show top score
- Award podium display
- Option to play again

### 4. Real-time Communication
**Polling Mechanism**
- Frontend polls every 1-2 seconds
- Lightweight JSON responses
- Game state synchronization
- Participant updates

**States to Track**
- Game phase (waiting, question, results, final)
- Current question index
- Timer value
- Participant count
- Individual student answers

### 5. File Storage Strategy

**users.json**
```json
{
  "users": [
    {"username": "admin", "password": "admin123", "role": "admin"},
    {"username": "student1", "password": "pass1", "role": "student"}
  ]
}
```

**quizzes.json**
```json
{
  "quizzes": [
    {
      "id": "quiz_001",
      "title": "Math Basics",
      "questions": [
        {
          "id": "q1",
          "text": "2 + 2 = ?",
          "answers": ["3", "4", "5", "6"],
          "correct": 1,
          "time": 20,
          "points": 1000
        }
      ]
    }
  ]
}
```

**games.json**
```json
{
  "active_games": {
    "GAME1234": {
      "quiz_id": "quiz_001",
      "host": "admin",
      "status": "waiting",
      "current_question": 0,
      "participants": {}
    }
  }
}
```

**sessions/GAME1234.json**
```json
{
  "answers": {},
  "scores": {},
  "question_results": {}
}
```

### 6. File Locking Strategy
Use `flock()` for concurrent access:
- Read-lock for reading data
- Write-lock for writing data
- Timeout after 5 seconds
- Retry on failure

## Implementation Phases

### Phase 1: Foundation (Priority: HIGH)
1. Create directory structure
2. Setup config.php
3. Implement file locking utility functions
4. Create user authentication system
5. Build admin login page
6. Create admin dashboard

### Phase 2: Quiz Management (Priority: HIGH)
1. Create quiz list view
2. Implement quiz creation form
3. Add question builder (add/edit/delete questions)
4. Quiz editing functionality
5. Quiz deletion
6. Add sample quizzes

### Phase 3: Game Hosting (Priority: HIGH)
1. Generate game PIN system
2. Create waiting room (admin view)
3. Start game functionality
4. Question display with timer
5. Answer collection system
6. Results display between questions
7. Final leaderboard

### Phase 4: Student Experience (Priority: HIGH)
1. Student login page
2. Join game page
3. Waiting room (student view)
4. Game interface (question + answer buttons)
5. Feedback on answer (correct/incorrect)
6. Score display
7. Final results

### Phase 5: Polish & Enhancements (Priority: MEDIUM)
1. Add sound effects (optional)
2. Improve visual design
3. Add image support for questions
4. Player avatar/nickname selection
5. Game history/replay
6. Export results to CSV

## Security Considerations
1. Password hashing (use password_hash()/password_verify())
2. Game PIN validation
3. Session management
4. Input sanitization (htmlspecialchars)
5. File permission restrictions
6. Prevent directory traversal
7. Rate limiting for game joining

## Browser Compatibility Notes (SRWare Iron 61)
- Use ES5 syntax for maximum compatibility
- Avoid ES2019+ features (flat(), optional chaining, etc.)
- ES6 features are safe: arrow functions, let/const, classes
- No dynamic import() - use traditional script loading
- No Promise.finally() - use .then().catch() pattern
- Use vanilla JavaScript, no frameworks

## Data Validation Rules
1. **Usernames**: 3-20 characters, alphanumeric + underscore
2. **Passwords**: 6-50 characters
3. **Quiz titles**: 5-100 characters
4. **Questions**: 5-500 characters
5. **Answers**: 1-100 characters each
6. **Game PIN**: 4-digit number (1000-9999)

## Testing Plan
1. Test admin account creation
2. Test quiz creation/editing
3. Test game hosting from admin side
4. Test student joining
5. Test game flow (all questions)
6. Test multiple concurrent students (10+)
7. Test file locking under load
8. Test browser compatibility (SRWare Iron 61)
9. Test session persistence
10. Test error handling

## Known Limitations
- No real-time WebSocket support (polling-based only)
- Flat file storage (not scalable for 1000+ users)
- No database (limited query capabilities)
- No built-in analytics
- No mobile app version (web-only)
- Single server limitation (no horizontal scaling)

## Future Enhancements (Out of Scope for MVP)
- Multiple game types (jumble, true/false)
- Team mode
- PowerPoint import
- Image library
- Video support
- Advanced analytics dashboard
- CSV/Excel import for questions
- Custom themes/colors

## Success Criteria
✅ Admin can create student accounts
✅ Admin can create quizzes with multiple questions
✅ Students can join games using PIN
✅ Game runs smoothly with 10+ concurrent students
✅ Scores calculated correctly based on speed + accuracy
✅ Leaderboard updates in real-time
✅ Works on SRWare Iron 61 without errors
✅ No data corruption under concurrent access
✅ All CRUD operations work correctly
