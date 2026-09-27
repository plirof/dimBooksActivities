Wordboard - Activity Filtering
========================

INDEX.PHP - Public Activity Page
---------------------------

The main index.php page displays all activities publicly without requiring login.
It supports filtering via URL parameters.


URL PARAMETERS
------------

Two query parameters are supported:

1. type (optional)
   - Filters by activity type
   - Values: quiz, match, wheel, crossword, wordsearch, missingword, groupsort, all
   - Default: all

2. search (optional)
   - Filters by tags or title
   - Multiple tags use AND logic (all must match)
   - Tags separated by comma
   - Case-insensitive matching


EXAMPLES
--------

1. Show all activities:
   index.php

2. Show only quizzes:
   index.php?type=quiz

3. Show activities tagged with "dimA":
   index.php?search=dimA

4. Show activities with BOTH "dimA" AND "lesson01" tags:
   index.php?search=dimA,lesson01

5. Show quizzes tagged with "math":
   index.php?type=quiz&search=math

6. Show wordsearch activities for dimB + lesson05:
   index.php?type=wordsearch&search=dimB,lesson05


TAGS SYSTEM
----------

When creating/editing activities in the admin dashboard, you can add tags:
- Tags are comma-separated (e.g., dimA, lesson01, math)
- Tags are optional
- Multiple tags can be added to one activity
- Tags are searchable on the public page


ACTIVITY STRUCTURE
----------------

Each activity JSON file contains:
{
    "id": "activity_...",
    "title": "Activity Name",
    "type": "quiz",
    "tags": ["dimA", "lesson01", "math"],
    "created_by": "admin",
    "created_date": "2026-02-03",
    "data": { ... }
}


SUPPORTED ACTIVITY TYPES
--------------------

1. Quiz
2. Match (Word Pairs)
3. Wheel (Spin the Wheel)
4. Crossword
5. Word Search
6. Missing Word
7. Group Sort


BACKWARD COMPATIBILITY
-------------------

- Single tag search still works: ?search=dimA
- No parameters: Shows all activities
- Manual search box and filter buttons work normally
- Pre-filtered results work with JavaScript filtering

Last updated: 2026-04-22