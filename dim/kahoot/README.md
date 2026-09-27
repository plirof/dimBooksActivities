# 🎮 Quiz Game - Kahoot Clone

A fun, interactive quiz game for classroom use, similar to Kahoot! Built with HTML, JavaScript, and PHP with flat file storage.

## 🌟 Features

- **👥 User Management**: Create student accounts with passwords
- **📝 Quiz Builder**: Create quizzes with multiple-choice questions
- **🎯 Game Hosting**: Host live games with unique PINs
- **⚡ Real-time Updates**: Polling-based real-time communication
- **🏆 Scoring System**: Points based on speed + accuracy
- **🎨 Kid-Friendly UI**: Colorful, fun design with emojis
- **📱 Responsive**: Works on desktop and tablets

## 📋 Requirements

- PHP 7.x or higher
- Apache web server (or any PHP-compatible server)
- Modern web browser (tested with SRWare Iron 61 / Chrome 61+)

## 🚀 Installation

1. **Clone or download this project** to your web server directory:
   ```bash
   cd /opt/lampp/htdocs/dim/kahoot
   ```

2. **Set permissions** (if needed):
   ```bash
   chmod 755 .
   chmod 755 admin api css data js student sessions
   chmod 644 *.php *.md
   ```

3. **Load sample quizzes** (optional):
   ```bash
   php setup_sample_quizzes.php
   ```
   This will add 3 sample quizzes with 5 questions each.

4. **Access the application**:
   - Open your browser and navigate to: `http://localhost/img/dim/kahoot/`
   - Or use your server's URL: `http://your-server/kahoot/`

## 🔐 Default Login

**Admin Account:**
- Username: `admin`
- Password: `admin123`

> ⚠️ **Important**: Change the admin password in `data/users.json` or create a new admin account for production use!

## 📖 How to Use

### For Teachers (Admins)

1. **Login** with admin credentials
2. **Create student accounts**:
   - Go to "Users" → "Create New Student"
   - Enter username and password
   - Share credentials with students
3. **Create quizzes**:
   - Go to "Quizzes" → "Create New Quiz"
   - Add title and description
   - Add questions with 4 answer options each
   - Set correct answer and time limit
   - Save the quiz
4. **Host a game**:
   - Go to "Host Game"
   - Select a quiz
   - Click "Generate Game PIN"
   - Share the PIN with students
   - Wait for students to join
   - Click "Start Game" when ready
5. **Control the game**:
   - Questions appear automatically
   - View real-time answer statistics
   - See results after each question
   - View final leaderboard

### For Students

1. **Join a game**:
   - Enter the Game PIN from your teacher
   - Enter your nickname (3-20 characters)
   - Wait for the game to start
2. **Play**:
   - Read each question carefully
   - Tap the correct answer button
   - Answer quickly for more points!
   - See your score after each question
3. **Results**:
   - View correct/incorrect feedback
   - See your total score
   - Check the leaderboard at the end

## 🎮 Game Features

### Quiz System
- Multiple choice questions (2-4 answers)
- Configurable time limit per question (10-60 seconds)
- Up to 1000 points per question
- Points calculated based on: accuracy + speed

### Real-time Features
- Live participant list in waiting room
- Real-time answer statistics
- Leaderboard updates
- Game state synchronization

### Scoring System
- Base points: 1000 per question
- Speed multiplier: faster answers = more points
- Total score = sum of all correct answers

## 📁 Project Structure

```
kahoot/
├── admin/              # Admin pages
│   ├── dashboard.php       # Admin dashboard
│   ├── create_user.php     # Create student accounts
│   ├── manage_users.php    # List/delete users
│   ├── create_quiz.php     # Create quizzes
│   ├── edit_quiz.php       # Edit quizzes
│   ├── manage_quizzes.php  # List/delete quizzes
│   ├── host_game.php       # Select quiz to host
│   └── game_control.php    # Game control panel
├── student/            # Student pages
│   ├── join.php            # Join game with PIN
│   ├── process_join.php    # Process join request
│   ├── waiting.php         # Waiting room
│   ├── game.php            # Main game interface
│   └── dashboard.php       # Student dashboard
├── api/                # API endpoints
│   ├── auth.php            # Authentication
│   ├── quiz.php            # Quiz CRUD operations
│   ├── game.php            # Game state management
│   ├── poll.php            # Real-time polling
│   └── game_status.php     # Game status
├── css/                # Stylesheets
│   └── style.css           # Main stylesheet
├── data/               # Data files (auto-created)
│   ├── users.json          # User accounts
│   ├── quizzes.json        # Quiz data
│   └── games.json         # Active games
├── sessions/           # Game sessions (auto-created)
│   └── *.json             # Per-game data
├── config.php          # Configuration and utilities
├── index.php           # Login page
├── setup_sample_quizzes.php  # Load sample quizzes
├── plan.md            # Original requirements
├── implementation_plan.md   # Implementation plan
├── progress.md        # Progress tracking
└── README.md          # This file
```

## 🔧 Configuration

Edit `config.php` to customize settings:

```php
define('DATA_DIR', __DIR__ . '/data');        // Data directory
define('SESSION_DIR', __DIR__ . '/sessions');  // Session directory
define('DEFAULT_ADMIN_USERNAME', 'admin');      // Default admin
define('DEFAULT_ADMIN_PASSWORD', 'admin123');   // Default password
define('POLL_INTERVAL', 1500);                 // Polling interval (ms)
define('GAME_PIN_LENGTH', 4);                  // Game PIN digits
```

## 🎨 Customization

### Change Colors
Edit `css/style.css` to customize:
- Primary colors (purple, blue, green, yellow)
- Background gradients
- Button styles
- Animations

### Add Sample Quizzes
Run: `php setup_sample_quizzes.php`

Or create your own using the admin interface.

## 🔧 Bug Fixes (ALL RESOLVED ✅)

All redirect bugs have been fixed and verified:

1. **Admin Login Redirect Bug**
   - Fixed `api/auth.php` to use `../` prefix for admin/student redirects
   - Enhanced `redirect()` function to handle paths correctly

2. **Student Join Redirect Bug**
   - Fixed all same-directory redirects in `student/` files
   - Changed `redirect('join.php')` to `redirect('./join.php')`
   - Changed `redirect('waiting.php')` to `redirect('./waiting.php')`

3. **Admin Same-Directory Redirects**
   - Fixed all same-directory redirects in `admin/` files
   - Changed all to use `./` prefix for clarity

**Impact:**
- ✅ Admin login now redirects to `admin/dashboard.php` (not `api/admin/dashboard.php`)
- ✅ Student join now redirects to `student/join.php` or `student/waiting.php` (not `kahoot/join.php`)
- ✅ All 22 redirect paths verified and working correctly

**See detailed reports:**
- `BUGFIX_REDIRECT.md` - Admin login fix details
- `BUGFIX_STUDENT_JOIN.md` - Student join fix details

## 🔒 Security

- Passwords are hashed using PHP's `password_hash()`
- Input sanitization with `htmlspecialchars()`
- Session-based authentication
- File locking for concurrent access
- No SQL injection (flat file storage)

## 🌐 Browser Compatibility

Tested and compatible with:
- ✅ SRWare Iron 61 (Chromium 61)
- ✅ Chrome 61+
- ✅ Firefox 55+
- ✅ Safari 11+
- ✅ Edge 15+

JavaScript compatibility:
- ✅ ES5 features (100%)
- ✅ ES6 features (100%)
- ❌ ES2019+ features (not used)

## 🐛 Troubleshooting

### "Failed to open stream" error
- Check file permissions
- Ensure directories exist: `data/`, `sessions/`

### "Game not found" error
- Check if Game PIN is correct
- Game may have expired

### Polling not working
- Check internet connection
- Verify PHP is running
- Check browser console for errors

### Session errors
- Clear browser cookies
- Check PHP session configuration
- Ensure `session_start()` is called

## 📝 Example Quiz Creation

1. Go to "Quizzes" → "Create New Quiz"
2. Enter title: "Math Quiz"
3. Add questions:
   ```
   Q1: What is 5 + 7?
   Answers: 10, 12, 14, 15
   Correct: 12 (Option 2)
   Time: 20 seconds
   ```
4. Click "Add Another Question" for more questions
5. Click "Save Quiz"

## 🎯 Tips for Teachers

- Keep questions short and clear
- Use age-appropriate content
- Set appropriate time limits (20-30s for most questions)
- Test your quiz before hosting
- Use the waiting room to ensure all students joined
- Review questions after the game

## 🚀 Performance Tips

- Keep quizzes under 20 questions for best performance
- Limit concurrent games to 3-5 (flat file storage limitation)
- Clear old game sessions regularly
- Monitor `sessions/` directory size

## 📄 License

This project is for educational use. Feel free to modify and use in your classroom!

## 🙏 Credits

Inspired by Kahoot! - The original game-based learning platform.

## 📞 Support

For issues or questions:
1. Check the progress.md for implementation details
2. Review the code comments
3. Test with sample quizzes first
4. Check browser console for errors

---

**Happy Quizzing! 🎉📚**
