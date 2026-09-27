# `?tts=piper` — Local Neural Greek TTS in the Browser (Implementation Instructions)

> Replication guide, written 2026-09-06 from a completed, verified implementation
> (test repo: `dimBooksActivities`). Give this file to an AI agent to reproduce the
> feature in the main repo. No question-asking needed; every file, URL and gotcha is below.
>
> **⚠ Apply the `Changelog — 2026-09-06` section at the end too — it contains
> post-implementation fixes (overlap, autoplay-block, immediate stop, cache headers)
> that the Steps above do not yet include.**

## What this feature does

Adds an opt-in URL parameter `?tts=piper` to every lesson presentation. With it, the
page synthesizes the Greek narration **live in the browser** with the Piper neural
voice `el_GR-rapunzelina-low` — fully offline (all engine files served same-origin from
the repo; nothing is fetched from the internet at runtime), **no MP3 files fetched**.
Without the parameter, behaviour is unchanged: pre-generated MP3s remain the default.

- Target browser: **Chrome** (onnxruntime-web WASM; Firefox deliberately not supported/ignored per project decision).
- Composes with the existing `?speed=` and `?mode=manual` parameters.
- No lesson HTML files are touched — only the shared runtime (`tts/scripts/`) plus a new asset folder (`tts/piper/`).

## Assumed project layout (same as this repo)

```
<root>/tts/scripts/tts.js            — audio/Web-Speech playback engine
<root>/tts/scripts/presentation.js   — slide runtime; sets TTS.audioBase per lesson
<root>/TPE-dimX-PEDIO/presentations/lessonNN.html   — lessons (load ../../tts/scripts/*.js)
```

Key existing mechanism this builds on: `tts.js`'s `TTS.speakWithAudio()` plays
`TTS.audioBase + NN.mp3` and calls its callback when audio ends (auto-advance).
Setting `TTS.audioBase = ''` skips MP3s entirely (that's what `?tts=web` already does
for Web Speech fallback).

---

## Step 1 — Vendor the engine files (one-time downloads; runtime stays offline)

Create the folder and download (≈ 71 MB total). Exact URLs, versions and expected sizes:

```bash
mkdir -p tts/piper/ort tts/piper/voices
cd tts/piper/ort
# onnxruntime-web 1.14.0 (pinned; only these two files are needed — Chrome without
# COOP/COEP headers cannot use the threaded variants, and SIMD is universal in Chrome)
curl -sL -o ort.min.js           https://cdn.jsdelivr.net/npm/onnxruntime-web@1.14.0/dist/ort.min.js           # 559,484 B
curl -sL -o ort-wasm-simd.wasm   https://cdn.jsdelivr.net/npm/onnxruntime-web@1.14.0/dist/ort-wasm-simd.wasm   # 10,014,674 B
cd ..
# espeak-ng phonemizer compiled to WASM (from the official piper-samples demo repo)
curl -sL -o espeakng.worker.js   https://raw.githubusercontent.com/rhasspy/piper-samples/master/resources/espeakng.worker.js   # 134,698 B
curl -sL -o espeakng.worker.wasm https://raw.githubusercontent.com/rhasspy/piper-samples/master/resources/espeakng.worker.wasm # 361,999 B
# !! the espeak-ng language DATA package lives at the REPO ROOT, not in resources/ —
# !! it is mandatory (24 MB). Without it the engine hangs with a 404 (see Gotchas):
curl -sL -o espeakng.worker.data https://raw.githubusercontent.com/rhasspy/piper-samples/master/espeakng.worker.data # 24,183,288 B
# Greek voice: the only native Greek voice in the piper-voices catalog
curl -sL -o voices/el_GR-rapunzelina-low.onnx     "https://huggingface.co/rhasspy/piper-voices/resolve/main/el/el_GR/rapunzelina/low/el_GR-rapunzelina-low.onnx?download=true"     # 63,104,526 B
curl -sL -o voices/el_GR-rapunzelina-low.onnx.json "https://huggingface.co/rhasspy/piper-voices/resolve/main/el/el_GR/rapunzelina/low/el_GR-rapunzelina-low.onnx.json"                # ~4 KB
```

Resulting layout:

```
tts/piper/ort/ort.min.js
tts/piper/ort/ort-wasm-simd.wasm
tts/piper/espeakng.worker.js
tts/piper/espeakng.worker.wasm
tts/piper/espeakng.worker.data          <- 24 MB, easy to miss, mandatory
tts/piper/voices/el_GR-rapunzelina-low.onnx        <- 63 MB
tts/piper/voices/el_GR-rapunzelina-low.onnx.json
```

Note: if you want other languages later, swap the two `voices/` files for any
`<lang>_P-<name>-<quality>.onnx(.json)` from `rhasspy/piper-voices`; the runtime is
language-agnostic (it reads `espeak.voice` from the config JSON).

## Step 2 — `tts/piper/.htaccess` (wasm MIME type for Apache/XAMPP)

```apache
# Chrome streams WebAssembly only with the right MIME type; without this line
# ort/espeak fall back to (slower, noisier) ArrayBuffer instantiation.
AddType application/wasm .wasm
```

## Step 3 — Create `tts/scripts/piper-tts.js` (new file, full contents)

Classic script (no bundler, no imports at top level), adapted from the MIT-licensed
`rhasspy/piper-samples` `resources/piper.js`:

```javascript
/* piper-tts.js — optional local neural TTS engine for ?tts=piper (Chrome).
   Loaded on demand by presentation.js when the URL param is present. Synthesis runs
   fully offline in the browser: text → espeak-ng phonemes (WASM) → VITS ONNX model →
   WAV blob. Engine files live in tts/piper/ and are served from the same origin;
   nothing is fetched from the internet at runtime.

   Adapted from rhasspy/piper-samples resources/piper.js (MIT license), flattened
   into a classic script so it needs no bundler. Requires window.ort
   (onnxruntime-web, loaded here from tts/piper/ort/). */

var PiperTTS = (function() {
  var base = '';
  var voiceFile = 'voices/el_GR-rapunzelina-low.onnx';

  var readyPromise = null;
  var state = 'idle'; // idle | loading | ready | error
  var session = null;
  var voiceConfig = null;
  var espeak = null;

  var BOS = '^';
  var EOS = '$';
  var PAD = '_';

  var AUDIO_OUTPUT_SYNCHRONOUS = 2;
  var espeakCHARS_AUTO = 0;

  var CLAUSE_INTONATION_FULL_STOP = 0x00000000;
  var CLAUSE_INTONATION_COMMA = 0x00001000;
  var CLAUSE_INTONATION_QUESTION = 0x00002000;
  var CLAUSE_INTONATION_EXCLAMATION = 0x00003000;

  var CLAUSE_TYPE_CLAUSE = 0x00040000;
  var CLAUSE_TYPE_SENTENCE = 0x00080000;

  var CLAUSE_PERIOD = 40 | CLAUSE_INTONATION_FULL_STOP | CLAUSE_TYPE_SENTENCE;
  var CLAUSE_COMMA = 20 | CLAUSE_INTONATION_COMMA | CLAUSE_TYPE_CLAUSE;
  var CLAUSE_QUESTION = 40 | CLAUSE_INTONATION_QUESTION | CLAUSE_TYPE_SENTENCE;
  var CLAUSE_EXCLAMATION = 45 | CLAUSE_INTONATION_EXCLAMATION | CLAUSE_TYPE_SENTENCE;
  var CLAUSE_COLON = 30 | CLAUSE_INTONATION_FULL_STOP | CLAUSE_TYPE_CLAUSE;
  var CLAUSE_SEMICOLON = 30 | CLAUSE_INTONATION_COMMA | CLAUSE_TYPE_CLAUSE;

  function loadScript(src) {
    return new Promise(function(resolve, reject) {
      var s = document.createElement('script');
      s.src = src;
      s.onload = resolve;
      s.onerror = function() { reject(new Error('Failed to load ' + src)); };
      document.head.appendChild(s);
    });
  }

  function init(baseUrl) {
    if (baseUrl) base = baseUrl;
    if (readyPromise) return readyPromise;
    state = 'loading';
    readyPromise = (async function() {
      if (typeof ort === 'undefined') {
        await loadScript(base + 'ort/ort.min.js');
      }
      voiceConfig = await (await fetch(base + voiceFile + '.json')).json();

      var EspeakModule = (await import(base + 'espeakng.worker.js')).default;
      // locateFile keeps the wasm AND the espeak-ng data package (espeakng.worker.data)
      // next to the module — without it the data file is fetched relative to the page and 404s.
      espeak = await EspeakModule({ locateFile: function(p) { return base + p; } });
      espeak._espeak_Initialize(AUDIO_OUTPUT_SYNCHRONOUS, 0, 0, 0);

      session = await ort.InferenceSession.create(base + voiceFile);
      state = 'ready';
    })();
    readyPromise.catch(function() { state = 'error'; });
    return readyPromise;
  }

  function textToPhonemes(text) {
    var voice = voiceConfig.espeak.voice;

    var voicePtr = espeak._malloc(espeak.lengthBytesUTF8(voice) + 1);
    espeak.stringToUTF8(voice, voicePtr, espeak.lengthBytesUTF8(voice) + 1);
    espeak._espeak_SetVoiceByName(voicePtr);
    espeak._free(voicePtr);

    var textPtr = espeak._malloc(espeak.lengthBytesUTF8(text) + 1);
    espeak.stringToUTF8(text, textPtr, espeak.lengthBytesUTF8(text) + 1);

    var textPtrPtr = espeak._malloc(4);
    espeak.setValue(textPtrPtr, textPtr, '*');

    var terminatorPtr = espeak._malloc(4);

    var textPhonemes = [];
    var sentencePhonemes = [];

    while (true) {
      var phonemesPtr = espeak._espeak_TextToPhonemesWithTerminator(
        textPtrPtr,
        espeakCHARS_AUTO,
        /* IPA */ 0x02,
        terminatorPtr
      );
      var clausePhonemes = espeak.UTF8ToString(phonemesPtr);
      sentencePhonemes.push(clausePhonemes);

      var terminator = espeak.getValue(terminatorPtr, 'i32');
      var punctuation = terminator & 0x000fffff;

      if (punctuation === CLAUSE_PERIOD) {
        sentencePhonemes.push('.');
      } else if (punctuation === CLAUSE_QUESTION) {
        sentencePhonemes.push('?');
      } else if (punctuation === CLAUSE_EXCLAMATION) {
        sentencePhonemes.push('!');
      } else if (punctuation === CLAUSE_COMMA) {
        sentencePhonemes.push(', ');
      } else if (punctuation === CLAUSE_COLON) {
        sentencePhonemes.push(': ');
      } else if (punctuation === CLAUSE_SEMICOLON) {
        sentencePhonemes.push('; ');
      }

      if ((terminator & CLAUSE_TYPE_SENTENCE) === CLAUSE_TYPE_SENTENCE) {
        textPhonemes.push(sentencePhonemes);
        sentencePhonemes = [];
      }

      var nextTextPtr = espeak.getValue(textPtrPtr, '*');
      if (nextTextPtr === 0) {
        break;
      }
      espeak.setValue(textPtrPtr, nextTextPtr, '*');
    }

    espeak._free(textPtr);
    espeak._free(textPtrPtr);
    espeak._free(terminatorPtr);

    if (sentencePhonemes.length > 0) {
      textPhonemes.push(sentencePhonemes);
    }

    for (var i = 0; i < textPhonemes.length; i++) {
      textPhonemes[i] = Array.from(textPhonemes[i].join('').normalize('NFD'));
    }

    return textPhonemes;
  }

  function phonemesToIds(idMap, textPhonemes) {
    var phonemeIds = [];

    for (var s = 0; s < textPhonemes.length; s++) {
      phonemeIds.push(idMap[BOS][0]);
      phonemeIds.push(idMap[PAD][0]);

      var sentencePhonemes = textPhonemes[s];
      for (var i = 0; i < sentencePhonemes.length; i++) {
        var phoneme = sentencePhonemes[i];
        if (!(phoneme in idMap)) {
          continue;
        }
        phonemeIds.push(idMap[phoneme][0]);
        phonemeIds.push(idMap[PAD][0]);
      }

      phonemeIds.push(idMap[EOS][0]);
    }

    return phonemeIds;
  }

  function float32ToWavBlob(floatArray, sampleRate) {
    var int16 = new Int16Array(floatArray.length);
    for (var i = 0; i < floatArray.length; i++) {
      int16[i] = Math.max(-1, Math.min(1, floatArray[i])) * 32767;
    }

    var buffer = new ArrayBuffer(44 + int16.length * 2);
    var view = new DataView(buffer);

    var writeStr = function(offset, str) {
      for (var i = 0; i < str.length; i++) {
        view.setUint8(offset + i, str.charCodeAt(i));
      }
    };

    writeStr(0, 'RIFF');
    view.setUint32(4, 36 + int16.length * 2, true);
    writeStr(8, 'WAVE');
    writeStr(12, 'fmt ');
    view.setUint32(16, 16, true);
    view.setUint16(20, 1, true); // PCM
    view.setUint16(22, 1, true); // mono
    view.setUint32(24, sampleRate, true);
    view.setUint32(28, sampleRate * 2, true);
    view.setUint16(32, 2, true);
    view.setUint16(34, 16, true);
    writeStr(36, 'data');
    view.setUint32(40, int16.length * 2, true);
    for (var j = 0; j < int16.length; j++) {
      view.setInt16(44 + j * 2, int16[j], true);
    }

    return new Blob([view], { type: 'audio/wav' });
  }

  async function synthesize(text) {
    await init();
    if (state !== 'ready') {
      throw new Error('Piper engine is not ready');
    }

    var noiseScale = voiceConfig.inference.noise_scale ?? 0.667;
    var lengthScale = voiceConfig.inference.length_scale ?? 1.0;
    var noiseWScale = voiceConfig.inference.noise_w ?? 0.8;

    var phonemeIds = phonemesToIds(voiceConfig.phoneme_id_map, textToPhonemes(text));

    var feeds = {
      input: new ort.Tensor('int64', new BigInt64Array(phonemeIds.map(function(x) { return BigInt(x); })), [1, phonemeIds.length]),
      input_lengths: new ort.Tensor('int64', BigInt64Array.from([BigInt(phonemeIds.length)]), [1]),
      scales: new ort.Tensor('float32', Float32Array.from([noiseScale, lengthScale, noiseWScale]), [3])
    };

    var results = await session.run(feeds);
    var out = results.output;
    var floatArray = out.data || out.cpuData; // ort-web 1.14 exposes tensor data as .data
    var blob = float32ToWavBlob(floatArray, voiceConfig.audio.sample_rate);
    return URL.createObjectURL(blob);
  }

  if (window.TTS && window.TTS.piperBase) {
    init(window.TTS.piperBase);
  }

  return {
    init: init,
    synthesize: synthesize,
    get state() { return state; }
  };
})();
```

## Step 4 — Modify `tts/scripts/tts.js`

Inside `TTS.speakWithAudio = function(slideIndex, text, callback) { ... }`, immediately
AFTER the `if (!text || !text.trim() || muted) { done(); return; }` line and BEFORE the
existing `if (TTS.audioBase) {` MP3 branch, insert:

```javascript
    if (TTS.engine === 'piper' && window.PiperTTS) {
      window.PiperTTS.synthesize(text).then(function(url) {
        if (stopped) { URL.revokeObjectURL(url); return; }
        var el = new Audio(url);
        function finish() {
          audio = null;
          URL.revokeObjectURL(url);
          if (!stopped) { isPlaying = false; if (onEnd) { var cb = onEnd; onEnd = null; cb(); } }
        }
        el.addEventListener('error', finish);
        el.addEventListener('ended', finish);
        el.play().then(function() {
          el.playbackRate = rate;
          audio = el;
          isPlaying = true;
        }).catch(finish);
      }).catch(function() {
        // Engine missing or failed to load — narrate with Web Speech instead
        isPlaying = true;
        webSpeechSpeak(text, function() { done(); });
      });
      return;
    }
```

(`stopped`, `audio`, `isPlaying`, `onEnd`, `done`, `webSpeechSpeak`, `rate` are the
module-private members already in tts.js. Storing the element in `audio` keeps
`TTS.stop()`/pause/mute working; the onEnd callback drives the existing auto-advance.)

## Step 5 — Modify `tts/scripts/presentation.js`

Immediately after the line that sets `TTS.audioBase` (`TTS.audioBase = 'audio/lesson' + …;`)
and after any existing `?tts=web` block, add:

```javascript
// ?tts=piper — skip the MP3s and synthesize narration locally in the browser with the
// Piper neural voice (tts/piper/, el_GR-rapunzelina). Chrome only; engine script is
// injected here so lesson files stay untouched. OnInit failure tts.js falls back to Web Speech.
if (window.location.search.indexOf('tts=piper') >= 0) {
  TTS.audioBase = '';
  TTS.engine = 'piper';
  TTS.piperBase = '../../tts/piper/';
  (function() {
    var s = document.createElement('script');
    s.src = '../../tts/scripts/piper-tts.js';
    document.head.appendChild(s);
  })();
}
```

(If lesson files live at a different depth in the main repo, adjust `'../../tts/piper/'`
— it must resolve from `TPE-dimX-PEDIO/presentations/lessonNN.html` to the `tts/piper/`
folder. piper-tts.js self-inits when it sees `TTS.piperBase`.)

---

## Gotchas (each one actually broke during implementation — do not skip)

1. **`espeakng.worker.data` 404 → engine hangs forever.** The phonemizer is an
   emscripten build with a preloaded data package. Without a custom `locateFile`, the
   `.data` file is fetched **relative to the page** (`presentations/espeakng.worker.data`)
   → 404 → the `EspeakModule()` factory promise never resolves → `PiperTTS.state`
   sticks at `'loading'`. The Step-3 code fixes this by passing
   `EspeakModule({ locateFile: p => base + p })`. The file must exist at
   `tts/piper/espeakng.worker.data` (repo root of piper-samples — NOT in `resources/`).
2. **Wrong `.wasm` MIME type on Apache/XAMPP.** Symptom in console:
   `wasm streaming compile failed: TypeError: Failed to execute 'compile' on
   'WebAssembly': Incorrect response MIME type. Expected 'application/wasm'`
   followed by `falling back to ArrayBuffer instantiation`. Works, but slower and
   noisy. Fixed by the `.htaccess` (Step 2).
3. **`results.output.cpuData` is undefined on ort-web 1.14** →
   `TypeError: Cannot read properties of undefined (reading 'length')` inside
   `float32ToWavBlob`. Use `results.output.data || results.output.cpuData` (Step 3 already does).
4. **Model is 63 MB**, not the ~20 MB typical of piper "low" voices (Greek rapunzelina is big).
   Over localhost this loads in ~1–2 s; first `InferenceSession.create` adds ~1–2 s.
   The engine caches nothing itself; browser HTTP cache handles repeat loads.
5. **Dynamic `import()` from a classic script** is used to load the ES-module
   `espeakng.worker.js` — works in Chrome, but only over `http(s)` (never `file://`).
6. **Voice quality / language mixing**: rapunzelina is a "low"-tier community voice —
   audibly below pre-generated neural MP3s (e.g. el-GR-NestorasNeural). English words
   inside Greek text can garble (known piper issue rhasspy/piper#696). Greek-only text is fine.

## Verification checklist (Chrome, served over http://localhost)

1. `http://localhost/<path>/TPE-dimA-PEDIO/presentations/lesson05.html?tts=piper&mode=manual`
   → DevTools console shows no errors; in the console `PiperTTS.state` becomes `'ready'`
   after a few seconds (63 MB model + ort init).
2. Synthesis smoke test (console):
   `const u = await PiperTTS.synthesize('Καλημέρα.'); const b = await (await fetch(u)).blob();`
   → `b.type === 'audio/wav'`, `b.size` > 50000, and it takes well under a second.
3. Autoplay: open the lesson with `?tts=piper` (no `mode=manual`) → narration plays per
   slide and the deck auto-advances through to the last slide.
4. Network tab with `?tts=piper`: **zero** requests to `.mp3` files and **zero** requests
   to external hosts (no cdn.jsdelivr.net, no huggingface.co) — everything same-origin.
5. Default mode (no param): `TTS.audioBase` is `'audio/lessonNN/'`, `window.PiperTTS` is
   `undefined` (script not injected), and the slide-1 MP3 is fetched as before.

---

## Changelog — 2026-09-06 (post-implementation fixes; apply ON TOP of the steps above)

### 1. Overlap fix — previous slide's voice kept reading after navigating

Root cause: `PiperTTS.synthesize()` is **async** (~0.3–0.5 s). If the user navigates
while a synthesis is in flight, `TTS.stop()` has already reset the shared `stopped`
flag by the time the stale result arrives, so the old slide's audio started *on top of*
the new slide's. Fix: a monotonic generation token in `tts.js`.

In `tts.js`, add a module variable (next to `var liveUtterance = null;`):

```javascript
  var speakToken = 0;
```

`TTS.stop()` bumps the token and revokes the interrupted slide's blob URL:

```javascript
  TTS.stop = function() {
    speakToken++; // invalidates any in-flight Piper synthesis for the interrupted slide
    stopped = true;
    if (audio) {
      if (audio._piperUrl) URL.revokeObjectURL(audio._piperUrl);
      audio.pause();
      audio = null;
    }
    if (typeof speechSynthesis !== 'undefined') speechSynthesis.cancel();
    isPlaying = false;
    onEnd = null;
  };
```

The piper branch of `TTS.speakWithAudio` captures the token and discards stale results
(replace the whole branch added in Step 4 with this):

```javascript
    if (TTS.engine === 'piper' && window.PiperTTS) {
      // TTS.stop() above bumped speakToken; synthesis is async, so a later stop/navigation
      // invalidates this result (prevents the previous slide's voice playing over the new one)
      var token = speakToken;
      window.PiperTTS.synthesize(text).then(function(url) {
        if (token !== speakToken) { URL.revokeObjectURL(url); return; }
        var el = new Audio(url);
        var settled = false;
        function finish() {
          if (settled) return;
          settled = true;
          audio = null;
          URL.revokeObjectURL(url);
          if (token === speakToken && !stopped) { isPlaying = false; if (onEnd) { var cb = onEnd; onEnd = null; cb(); } }
        }
        el.addEventListener('error', finish);
        el.addEventListener('ended', finish);
        el.play().then(function() {
          if (token !== speakToken) { el.pause(); URL.revokeObjectURL(url); return; }
          el.playbackRate = rate;
          el._piperUrl = url; // TTS.stop() revokes it when a slide is interrupted mid-speech
          audio = el;
          isPlaying = true;
        }).catch(function(e) {
          if (e && e.name === 'NotAllowedError' && TTS.onAutoplayBlocked) {
            // Chrome blocks sound until the first click/keypress: wait for it instead of
            // silently skipping through every slide
            audio = null;
            URL.revokeObjectURL(url);
            isPlaying = false;
            TTS.onAutoplayBlocked();
            return;
          }
          finish();
        });
      }).catch(function() {
        // Engine missing or failed to load — narrate with Web Speech instead
        isPlaying = true;
        webSpeechSpeak(text, function() { done(); });
      });
      return;
    }
```

### 2. Main-Chrome silence — autoplay policy (and safety net)

Diagnosed in the field: Chrome **refuses `audio.play()` until the first user gesture**
(on a fresh page load the whole deck can silently skip through; the ZCode test browser
disables that policy, which masked it). The user solved the immediate case via Chrome's
site autoplay setting, but the runtime now also handles it gracefully:

- `tts.js`: both play paths catch `NotAllowedError` and call `TTS.onAutoplayBlocked()`
  instead of advancing/falling back (piper branch shown above; MP3 branch: replace its
  `.catch(function() { fallback(); })` with the same `NotAllowedError` check first).
- `presentation.js` (add right before the final `goToSlide(0);`):

```javascript
// Chrome refuses to play audio until the first user gesture: when a slide's sound is
// refused (NotAllowedError), wait for a click/keypress and narrate the current slide
// from there instead of silently skipping through the deck.
var narrationBlocked = false;
TTS.onAutoplayBlocked = function() { narrationBlocked = true; };
function unlockNarration() {
  if (!narrationBlocked) return;
  narrationBlocked = false;
  if (autoPlay && !isPaused) speakCurrent();
}
document.addEventListener('pointerdown', unlockNarration, true);
document.addEventListener('keydown', unlockNarration, true);
```

### 3. Immediate stop on ←/→ — cancel the pending auto-advance

After a slide's audio ends, a 1.8 s timer advances the deck. If the user navigates
during that window, the stale timer fired *into the new slide* (skipping it mid-speech),
which made stop feel delayed. `presentation.js`: declare `var advanceTimer = null;`
above `speakCurrent`, store the timeout id in it, and clear it at the top of
`goToSlide` and in `togglePlay`'s pause branch:

```javascript
var advanceTimer = null;

function speakCurrent() {
  var text = getSlideText(LESSON.slides[current]);
  if (!text) return;
  var adv = autoPlay && !isPaused ? function() {
    var delay = Math.round(1800 / TTS.rate);
    advanceTimer = setTimeout(function() { if (!isPaused && autoPlay) goToSlide(current + 1); }, delay);
  } : null;
  TTS.speakWithAudio(current + 1, text, adv);
}

function goToSlide(i) {
  // a pending auto-advance must never fire into the newly selected slide
  if (advanceTimer) { clearTimeout(advanceTimer); advanceTimer = null; }
  if (i < 0 || i >= total) return;
  // … rest of the existing function unchanged
```

and in `togglePlay`'s pause branch: add the same two lines before `TTS.stop();`.

### 4. Cache headers — stale cached wasm/JS survived server updates

Chrome kept serving the `.wasm` with the pre-`.htaccess` MIME type (and old runtime JS)
from cache, so console errors persisted after the server was already fixed. Add
`Cache-Control: no-cache` to both folders:

`tts/piper/.htaccess` (final version):

```apache
# Chrome streams WebAssembly only with the right MIME type; without this line
# ort/espeak fall back to (slower, noisier) ArrayBuffer instantiation.
AddType application/wasm .wasm
# The engine files are large but must never be served stale: an old cached .wasm
# keeps the wrong MIME type (and old code) alive across server updates.
<IfModule mod_headers.c>
  Header set Cache-Control "no-cache, must-revalidate"
</IfModule>
```

`tts/scripts/.htaccess` (new file):

```apache
# Shared runtime shared by all 180 lessons: when these files are updated, classroom
# browsers must pick up the new version immediately instead of serving a cached copy.
<IfModule mod_headers.c>
  Header set Cache-Control "no-cache, must-revalidate"
</IfModule>
```

### 5. Field notes

- Firefox runs the piper engine fine too (onnxruntime-web is cross-browser) even though
  Chrome is the supported target — a usable bonus, not a guarantee.
- `?mode=manual` narrates **nothing** by design (all engines) — only autoplay narrates;
  the params table in `tts_VoicePresentationsSummer2026.md` was updated to say so.
- - Verified after these fixes: navigating mid-speech leaves exactly one audio playing
  (the new slide), the counter never jumps twice, and a full autoplay run reaches the
  last slide with narration on every slide.

### 6. Longer pauses at full stops (no slide-text changes)

The user wants noticeably longer silence at sentence boundaries. Implemented **purely at
the phoneme-ID level inside the piper engine** — the slide text itself (what is displayed,
`LESSON.slides`, `slideTexts[]`, and the MP3/Web-Speech modes) is never modified.

In `tts/scripts/piper-tts.js`:

- Add next to the `BOS`/`EOS`/`PAD` constants:
  ```javascript
  // Extra padding tokens inserted after a sentence-ending "." "!" "?" (between sentences
  // only). The model renders each PAD as a short silence (~25 ms), so 8 pads ≈ 200 ms of
  // extra breathing room at full stops.
  var SENTENCE_PAUSE_PADS = 8;
  ```
- In `phonemesToIds`, after `phonemeIds.push(idMap[EOS][0]);` add:
  ```javascript
      // stretch the pause when this sentence ended on a full stop and another follows
      var lastPhoneme = sentencePhonemes.length > 0 ? sentencePhonemes[sentencePhonemes.length - 1] : '';
      if (s < textPhonemes.length - 1 && (lastPhoneme === '.' || lastPhoneme === '!' || lastPhoneme === '?')) {
        for (var p = 0; p < SENTENCE_PAUSE_PADS; p++) {
          phonemeIds.push(idMap[PAD][0]);
        }
      }
  ```

Rationale: the VITS model renders every PAD token as a short silence, so extra pads
lengthen the inter-sentence gap. Pads are added **only between sentences** (not after the
last one) so slide ends don't drag, and they apply to `.` `!` `?` alike. Tuning: raise or
lower `SENTENCE_PAUSE_PADS` to taste.

Verified: "Η Μαρία πήγε σχολείο. Εκεί έμαθε πολλά." → 3.81 s vs the comma-joined twin
"Η Μαρία πήγε σχολείο, εκεί έμαθε πολλά." → 3.04 s (≈ +0.77 s from the real sentence
break, which includes the extra pads plus normal two-sentence prosody).

### 7. Instant narration on slide change — Blob cache + next-slide prefetch

The slide already became visible **before** synthesis started (in `goToSlide`, the DOM
update precedes `speakCurrent()`), so the few-seconds delay a user noticed was purely
ONNX inference time for the new slide's text (scales with text length: ~0.4 s short,
1–3 s long slides on CPU). Fix: pre-generate the next slide's audio while the current
one narrates, and cache results.

`tts/scripts/piper-tts.js`:

- Add with the other module vars: `var blobCache = {}; // slide text -> generated WAV Blob (a fresh object URL is made per call)`
- In `synthesize(text)`, after the ready check:
  ```javascript
    // Serve a repeated slide text from cache: creating an object URL from the stored Blob
    // is instant, so prefetched slides start playing with no synthesis wait. The URL is
    // owned (and revoked) by the caller — the cached Blob itself stays valid.
    if (blobCache[text]) {
      return URL.createObjectURL(blobCache[text]);
    }
  ```
  and store the result before returning: `blobCache[text] = blob;`
  (Storing the **Blob**, not the URL, matters: tts.js revokes object URLs freely and
  the cache must survive that.)
- Add a fire-and-forget warm-up and expose the cache size:
  ```javascript
  function prefetch(text) {
    // fire-and-forget warm-up (e.g. the next slide while the current one narrates)
    synthesize(text).catch(function() {});
  }
  ```
  and extend the returned object with `prefetch: prefetch,` and
  `get cacheCount() { return Object.keys(blobCache).length; }`.

`tts/scripts/presentation.js` — in `speakCurrent()`, right after the
`TTS.speakWithAudio(...)` call (the one non-piper-file hook, needed because slide texts
live here; it no-ops unless the piper engine is active):

```javascript
  // Warm the next slide's narration while the current one plays, so advancing sounds instant
  if (TTS.engine === 'piper' && window.PiperTTS && LESSON.slides[current + 1]) {
    PiperTTS.prefetch(getSlideText(LESSON.slides[current + 1]));
  }
```

Effect: sequential navigation (→ or auto-advance) always lands on ready audio —
cache hit measured at **0 ms** vs 444 ms fresh synthesis (short text; longer slides
save more). Memory cost: cached WAVs ≈ 0.2–0.6 MB per slide (a few MB per lesson),
freed on page unload. Remaining wait in the whole deck: only the very first slide
after page load (model init). Note: jumping ahead faster than the background
generation can still briefly wait — inference is single-threaded and requests queue;
sequential presentation flow is the case this optimizes.

Verified: with `?tts=piper`, cache grows as slides play (`cacheCount`), mid-speech
navigation still stops the old voice instantly (token fix), the deck auto-advances
through, and cached synthesis responds in 0 ms.
