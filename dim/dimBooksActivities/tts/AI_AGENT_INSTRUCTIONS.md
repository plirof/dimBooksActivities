# AI Agent Instructions — TTS Lesson Presentation Generator

## Overview

Generate HTML slide presentations with Greek TTS (text-to-speech) audio for all
lessons across all classes (dimA through dimST, ~180 lessons total).

Each lesson = a small HTML file holding only its data (`LESSON` + `slideTexts`).
- **10–14 slides** covering ALL theory (title, big-idea, concept(s), glossary list, worked example, steps, practice, assessment)
- Auto-playing Greek voiceover (pre-recorded MP3 with Web Speech fallback)
- Keyboard controls, speed control, pause/resume

**Shared CSS + runtime JS are NOT inlined in each lesson** — they live in
`tts/scripts/presentation.css` and `tts/scripts/presentation.js` and are loaded
via `<link>`/`<script src>` from `../../tts/scripts/`. Edit those once to change
every lesson. The pause indicator is intentionally invisible (the slide simply
freezes; only the ⏸/▶ button toggles).

## What's in this folder

| File | Purpose |
|------|---------|
| `slide_TTS_template.html` | **Minimal** lesson template — only the data section (`LESSON` + `slideTexts`) + skeleton. Copy for each lesson. |
| `scripts/presentation.css` | Shared slide/controls styling (loaded by every lesson). |
| `scripts/presentation.js` | Shared runtime JS (render, nav, pause, keyboard, TTS wiring). |
| `scripts/tts.js` | Runtime TTS library (audio playback + Web Speech fallback). |
| `pregen.AppImage` | Standalone MP3 generator. No Python/runtime deps needed. |
| `pregen_AppImage.txt` | pregen.AppImage usage reference |
| `AI_AGENT_INSTRUCTIONS.md` | This file |
| `tts_implementation_track.md` | Progress tracker — update after each lesson |

## Where to save generated files

Each lesson file goes into its class's presentations folder:
```
TPE-dimX-PEDIO/presentations/lessonNN.html     (HTML presentation)
TPE-dimX-PEDIO/presentations/audio/lessonNN/   (MP3 files)
```

## Theory Coverage Requirement (full-theory standard, lessons 12+)

A lesson's slides must let the teacher present **all the theory without the book**.
When reading the source (`lessons_seperated/lessonNN.html`), extract and ensure each
of these lands on a slide:

1. **Big idea / why it matters** — one synthesized `concept` slide in plain words.
2. **Every definition & new term** → a glossary `list` slide.
3. **Every distinct concept or principle** → one or more `concept` slides (own words + the book's wording).
4. **Any worked example** the book gives → a `text` slide.
5. **Procedures** → `steps`; **activities** → `practice`; **self-assessment** → `assessment` (last slide).

Budget: **10–14 slides** for theory-heavy lessons (8–10 only for genuinely light lessons).
Never cut a concept to fit a count — raise the count instead (up to ~14).

> **Legacy note:** Lessons **1–11 (all classes)** were generated earlier with a
> lighter summary method (tracker mark `✅B`). They are candidates for a later
> upgrade to the full-theory standard (`✅FT`). Lessons 12+ are `✅FT`.

## Workflow: One Lesson

### Step 1: Read the source content

Source files are in each class folder:
- `TPE-dimX-PEDIO/περιλήψεις.md` — lesson summaries (good overview)
- `TPE-dimX-PEDIO/lessons_seperated/lessonNN.html` — full lesson text

Also check `TPE-dimX-PEDIO/activities/dimX-lesNN-*.json` for activity ideas.

### Step 2: Copy the template

```bash
cp tts/slide_TTS_template.html TPE-dimX-PEDIO/presentations/lessonNN.html
```

### Step 3: Edit the LESSON object

Replace the LESSON object with the lesson's content. The template expects:

```js
var LESSON = {
  number: NN,                                    // lesson number (1-30)
  unit: "Ενότητα X: ...",                        // unit name from περιλήψεις.md
  slides: [
    // ... slide objects (see below)
  ]
};
```

### Step 4: Sync slideTexts array

TTS text is **auto-generated** from slide display content at runtime (no separate
`tts` field needed). But `pregen.AppImage` needs a static `slideTexts` array in
the HTML source for MP3 generation.

To auto-generate `slideTexts` from your LESSON data, run this in Node.js after
filling in LESSON.slides (paste into a terminal):

```bash
node -e "
$(cat << 'SCRIPT'
const fs = require('fs');
// Read the file you just edited
const html = fs.readFileSync('TPE-dimX-PEDIO/presentations/lessonNN.html','utf8');
const m = html.match(/var LESSON = ([\s\S]*?);\n\nvar slideTexts/);
if (!m) { console.log('LESSON not found'); process.exit(1); }
const sandbox = { LESSON: null };
require('vm').createContext(sandbox);
require('vm').runInContext('var LESSON = ' + m[1], sandbox);
const unit = sandbox.LESSON.unit;
sandbox.LESSON.slides.forEach(function(s, i) {
  var t;
  switch (s.type) {
    case 'title': t = unit + '. ' + s.title + (s.sub ? '. ' + s.sub : ''); break;
    case 'text': t = s.text || ''; break;
    case 'steps': t = (s.title ? s.title + '. ' : '') + s.steps.join('. '); break;
    case 'list': t = (s.title ? s.title + '. ' : '') + s.items.join('. '); break;
    case 'concept': t = s.text || ''; break;
    case 'practice': t = (s.title || 'Συζητήστε') + '. ' + s.items.join('. '); break;
    case 'assessment': t = s.check + (s.end ? '. ' + s.end.replace(/[✦✧]/g,'').trim() : '. Τέλος'); break;
    default: t = s.text || s.title || '';
  }
  console.log(i+1 + '|' + t);
});
SCRIPT
)"
```

Then manually replace the `slideTexts` array entries by copying each line's
output after the `|` delimiter.

### Step 5: Generate MP3s

**IMPORTANT: If updating an existing presentation, delete old MP3s first:**
```bash
rm TPE-dimX-PEDIO/presentations/audio/lessonNN/*.mp3
```

The pregen.AppImage tool skips existing files, so old audio will not be updated unless deleted first.

```bash
mkdir -p TPE-dimX-PEDIO/presentations/audio/lessonNN
./tts/pregen.AppImage \
  TPE-dimX-PEDIO/presentations/lessonNN.html \
  TPE-dimX-PEDIO/presentations/audio/lessonNN
```

### Step 6: Update the tracker

Edit `tts/tts_implementation_track.md` and change the row for this lesson:
- HTML column: `✅FT` (full-theory) — use `✅B` only for legacy/basic-summary lessons
- MP3 column: `✅`
- Legend is at the top of the tracker file.

## Slide Types Reference

### title
```js
{ type: "title", title: "Μάθημα 1ο: Πώς λύνω ένα πρόβλημα" }
// Optional: sub: "Υπότιτλος"
```
Displays: LESSON.unit (h1) + title (h2). TTS: "LESSON.unit. title."

### text
```js
{ type: "text", text: "Η Υπατία ετοιμάζει την τσάντα της..." }
```
Displays: paragraph. TTS: same text.

### steps
```js
{ type: "steps", title: "Τα 4 βήματα", steps: ["① ...", "② ...", "③ ...", "④ ..."] }
```
Displays: title (h2) + step cards. TTS: "title. step1. step2. step3. step4."

### list
```js
{ type: "list", title: "Αναλυτικά", items: ["Κοίτα...", "Βρες...", "Βάλε..."] }
```
Displays: title (h2) + bullet list. TTS: "title. item1. item2. item3."

### concept
```js
{ type: "concept", text: "Λύνουμε ευκολότερα ένα πρόβλημα..." }
```
Displays: quoted text in orange. TTS: same text.

### practice
```js
{ type: "practice", title: "Συζητήστε", items: ["Πλύσιμο...", "Βόλτα...", "Φαγητό..."] }
```
Displays: title (h2) + bullet list. TTS: "title. item1. item2. item3."

### assessment
```js
{ type: "assessment", check: "Μπορώ να περιγράφω...", end: "Μπράβο! ✦ Τέλος ✦" }
```
Displays: "Αυτοαξιολόγηση" (h2) + ✓ check text + end text.
TTS: "check. end."

## Key Rules

1. **DO NOT add a `tts` field** to slides. TTS text is auto-generated at runtime
   by the `getSlideText()` function in `scripts/presentation.js`.

2. **Keep `slideTexts[]` in sync** with your slides. This array is read by
   `pregen.AppImage` to generate MP3s. Each entry must match what TTS would
   say for that slide.

3. **8–14 slides per lesson** — cover ALL theory (see "Theory Coverage Requirement"). Theory-heavy lessons may need up to ~14; never cut a concept to stay short.

4. **Use `\n` in text sparingly** — keep paragraphs as a single line.

5. **Always add an assessment slide as the last slide**, with `end: "Μπράβο! ✦ Τέλος ✦"`.

6. **DO NOT re-inline CSS or runtime JS** in a lesson file. Styling lives in
   `scripts/presentation.css` and behaviour in `scripts/presentation.js`; each
   lesson loads both via `<link>`/`<script src>`. Edit those once to change all
   lessons.

## Directory Structure (Relative to Project Root)

```
dimBooksActivities/
├── tts/
│   ├── AI_AGENT_INSTRUCTIONS.md        ← this file
│   ├── tts_implementation_track.md     ← progress tracker
│   ├── slide_TTS_template.html         ← minimal template (data + skeleton only)
│   ├── pregen.AppImage                 ← MP3 generator
│   ├── pregen_AppImage.txt             ← pregen usage docs
│   └── scripts/
│       ├── presentation.css            ← shared slide/controls styling
│       ├── presentation.js             ← shared runtime JS (nav, pause, keyboard…)
│       └── tts.js                      ← runtime TTS library
├── TPE-dimA-PEDIO/
│   ├── περιλήψεις.md
│   ├── lessons_seperated/lesson01-30.html
│   ├── presentations/                  ← generated lesson HTMLs live here
│   │   ├── lesson01.html               ← minimal: <link> + LESSON/slideTexts + skeleton
│   │   ├── ...
│   │   └── audio/lessonNN/01-07.mp3
│   └── activities/
├── TPE-dimB-PEDIO/
│   └── ... (same structure)
├── TPE-dimC-PEDIO/
├── TPE-dimD-PEDIO/
├── TPE-dimE-PEDIO/
└── TPE-dimST-PEDIO/
```

## URL Parameters (Browser Testing)

| Param | Example | Effect |
|-------|---------|--------|
| `?speed=0.7` | `lesson01.html?speed=0.7` | Slower playback (range 0.5–2.0) |
| `?mode=manual` | `lesson01.html?mode=manual` | Manual navigation (no auto-advance) |
| `?speed=1.2&mode=manual` | combined | Both together |

## Keyboard Shortcuts (in the browser)

| Key | Action |
|-----|--------|
| ← / → | Previous / Next slide |
| Space | Pause / Resume autoplay |
| M | Mute / Unmute |
| F | Toggle fullscreen |
| + / = | Increase speed (+0.1) |
| - / _ | Decrease speed (-0.1) |
