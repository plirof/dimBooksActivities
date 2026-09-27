# 📍 Path Reference Guide

This document clearly shows the correct file paths and how files reference each other.

## Directory Structure

```
/opt/lampp/htdocs/img/kahoot/
│
├── admin/                   # Admin-facing pages
│   ├── dashboard.php         # Admin dashboard
│   ├── create_user.php       # Create student accounts
│   ├── manage_users.php      # List and delete users
│   ├── create_quiz.php       # Create new quizzes
│   ├── edit_quiz.php         # Edit existing quizzes
│   ├── manage_quizzes.php    # List and delete quizzes
│   ├── host_game.php        # Select quiz to host
│   └── game_control.php     # Live game control panel
│
├── student/                 # Student-facing pages
│   ├── dashboard.php         # Student dashboard
│   ├── join.php            # Join game with PIN
│   ├── process_join.php     # Process join request
│   ├── waiting.php          # Waiting room
│   └── game.php           # Main game interface
│
├── api/                    # Backend API endpoints
│   ├── auth.php            # Authentication (login/logout)
│   ├── quiz.php            # Quiz CRUD operations
│   ├── game.php            # Game state management
│   ├── poll.php            # Real-time polling
│   └── game_status.php     # Game status and participants
│
├── css/                    # Stylesheets
│   └── style.css          # Main stylesheet
│
├── data/                   # Data storage (auto-created)
│   ├── users.json         # User accounts
│   ├── quizzes.json       # Quiz data
│   └── games.json        # Active game sessions
│
├── sessions/               # Game sessions (auto-created)
│   └── *.json            # Per-game session data
│
├── config.php              # Configuration and utilities
├── index.php               # Main login page
└── [documentation files]    # README, etc.
```

## Path Reference Examples

### From Admin Pages (in `admin/` directory)

**To other admin pages:**
```php
<!-- Correct -->
<a href="dashboard.php">Dashboard</a>
<a href="manage_users.php">Users</a>
<a href="manage_quizzes.php">Quizzes</a>

<!-- Incorrect -->
<a href="../admin/dashboard.php">Dashboard</a>  <!-- Wrong! -->
<a href="admin/dashboard.php">Dashboard</a>      <!-- Wrong! -->
```

**To API endpoints:**
```javascript
// Correct
fetch('../api/auth.php')
fetch('../api/poll.php')
fetch('../api/game.php')

// Incorrect
fetch('api/auth.php')              // Wrong!
fetch('../api/admin/auth.php')       // Wrong!
fetch('/api/auth.php')              // Wrong (unless using absolute path)
```

**To root files:**
```php
<!-- To go back to root -->
<a href="../index.php">Home</a>
```

### From Student Pages (in `student/` directory)

**To other student pages:**
```php
<!-- Correct -->
<a href="dashboard.php">Dashboard</a>
<a href="join.php">Join Game</a>

<!-- Incorrect -->
<a href="../student/dashboard.php">Dashboard</a>  <!-- Wrong! -->
```

**To API endpoints:**
```javascript
// Correct
fetch('../api/poll.php')
fetch('../api/auth.php')

// Incorrect
fetch('api/poll.php')               // Wrong!
fetch('../api/student/poll.php')      // Wrong!
```

### From Root Files (in main directory)

**To API endpoints:**
```php
<!-- Correct -->
<form action="api/auth.php" method="POST">

<!-- Incorrect -->
<form action="../api/auth.php">  <!-- Wrong! -->
```

**To student pages:**
```php
<!-- Correct -->
<a href="student/join.php">Join Game</a>

<!-- Incorrect -->
<a href="join.php">Join Game</a>  <!-- Wrong! -->
```

## File Location Verification

| File | Location | Access URL |
|-------|-----------|------------|
| **Admin Dashboard** | `admin/dashboard.php` | `/kahoot/admin/dashboard.php` |
| **Student Dashboard** | `student/dashboard.php` | `/kahoot/student/dashboard.php` |
| **Login Page** | `index.php` | `/kahoot/` |
| **Auth API** | `api/auth.php` | `/kahoot/api/auth.php` |
| **Polling API** | `api/poll.php` | `/kahoot/api/poll.php` |

## Common Path Mistakes to Avoid

### ❌ Wrong: Adding directory prefix when already in directory
```php
<!-- In admin/dashboard.php -->
<a href="admin/dashboard.php">Dashboard</a>  <!-- Wrong! -->
```
**Correct:** `<a href="dashboard.php">Dashboard</a>`

### ❌ Wrong: Not using enough ../ to go up directories
```javascript
<!-- In student/game.php -->
fetch('api/poll.php')  <!-- Wrong! -->
```
**Correct:** `fetch('../api/poll.php')`

### ❌ Wrong: Using wrong parent directory
```javascript
<!-- In admin/game_control.php -->
fetch('../../api/poll.php')  <!-- Wrong! -->
```
**Correct:** `fetch('../api/poll.php')`

### ❌ Wrong: Confusing admin vs api
```php
<!-- API endpoint is NOT in admin/ directory -->
<a href="../admin/api/auth.php">Logout</a>  <!-- Wrong! -->
```
**Correct:** `<a href="../api/auth.php">Logout</a> OR `<form action="../api/auth.php">`

## Quick Reference Cheat Sheet

| You are here | To get to: | Use path: |
|--------------|--------------|------------|
| `admin/` | Another admin page | `filename.php` |
| `admin/` | API endpoint | `../api/filename.php` |
| `admin/` | Root | `../filename.php` |
| `student/` | Another student page | `filename.php` |
| `student/` | API endpoint | `../api/filename.php` |
| `student/` | Root | `../filename.php` |
| `root/` | API endpoint | `api/filename.php` |
| `root/` | Admin page | `admin/filename.php` |
| `root/` | Student page | `student/filename.php` |

## URL Examples for Browser

If your server is at `http://localhost/img/kahoot/`:

| Page | Browser URL |
|-------|-------------|
| Login | `http://localhost/img/kahoot/` |
| Admin Dashboard | `http://localhost/img/kahoot/admin/dashboard.php` |
| Student Dashboard | `http://localhost/img/kahoot/student/dashboard.php` |
| Join Game | `http://localhost/img/kahoot/student/join.php` |
| Student Waiting | `http://localhost/img/kahoot/student/waiting.php` |
| Student Game | `http://localhost/img/kahoot/student/game.php` |

## Summary

✅ **All paths in the codebase are CORRECT**

- Admin pages use relative paths to other admin pages
- Student pages use relative paths to other student pages  
- Both use `../api/` to access API directory (correct!)
- Root pages use `api/` to access API directory (correct!)
- Root pages use `student/` to access student directory (correct!)

**No path corrections needed!** 🎉
