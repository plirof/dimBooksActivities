/* presentation.js — shared runtime for all TPE-dim lesson presentations.
   Loaded by every lessonNN.html AFTER tts.js and AFTER the inline script that
   defines `LESSON` (and `slideTexts`, which is for pregen.py only).
   Functions are declared at top level so inline onclick="navigate()/togglePlay()/toggleMute()"
   handlers in the lesson HTML keep working. */

var slides = [];
var total = 0;
var current = 0;
var isPaused = false;
var autoPlay = true;

(function() {
  var m = window.location.search.match(/[?&]speed=([\d.]+)/);
  if (m && parseFloat(m[1]) >= 0.5 && parseFloat(m[1]) <= 2.0) {
    TTS.rate = parseFloat(m[1]);
  }
  if (window.location.search.indexOf('mode=manual') >= 0) autoPlay = false;
})();

var pad = Math.max(2, String(LESSON.slides.length).length);
TTS.audioBase = 'audio/lesson' + String(LESSON.number).padStart(2, '0') + '/';
// ?tts=web — skip the MP3s and let the browser's Web Speech engine narrate (tts.js treats
// an empty audioBase as "no audio files"): needs a Greek system voice on Linux
// (speech-dispatcher + espeak-ng; Firefox natively, Chromium with --enable-speech-dispatcher)
if (window.location.search.indexOf('tts=web') >= 0) TTS.audioBase = '';

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

  // Parse pause params after PiperTTS loads
  var mSentence = window.location.search.match(/[?&]piperSentencePause=(\d+)/);
  var mComma = window.location.search.match(/[?&]piperCommaPause=(\d+)/);
  var checkPiperReady = setInterval(function() {
    if (window.PiperTTS && window.PiperTTS.state === 'ready') {
      clearInterval(checkPiperReady);
      if (mSentence) PiperTTS.setSentencePause(parseInt(mSentence[1], 10));
      if (mComma) PiperTTS.setCommaPause(parseInt(mComma[1], 10));
    }
  }, 100);
}

function renderSlide(s, i) {
  var div = document.createElement('div');
  div.className = 'slide fade slide-' + s.type;
  var idx = '_' + i;

  switch (s.type) {
    case 'title':
      div.innerHTML = '<h1>' + LESSON.unit + '</h1>'
        + (s.sub ? '<div class="sub">' + s.sub + '</div>' : '')
        + '<h2>' + s.title + '</h2>';
      break;
    case 'text':
      div.innerHTML = '<p>' + s.text + '</p>';
      break;
    case 'steps':
      var html = s.title ? '<h2>' + s.title + '</h2><div class="steps">' : '<div class="steps">';
      s.steps.forEach(function(st) { html += '<div class="step">' + st + '</div>'; });
      html += '</div>';
      div.innerHTML = html;
      break;
    case 'list':
      var lh = s.title ? '<h2>' + s.title + '</h2><ul>' : '<ul>';
      s.items.forEach(function(it) { lh += '<li>' + it + '</li>'; });
      lh += '</ul>';
      div.innerHTML = lh;
      break;
    case 'concept':
      div.innerHTML = '<p class="concept-text">«' + s.text + '»</p>';
      break;
    case 'practice':
      var ph = '<h2>' + (s.title || 'Συζητήστε') + '</h2><ul>';
      s.items.forEach(function(it) { ph += '<li>' + it + '</li>'; });
      ph += '</ul>';
      div.innerHTML = ph;
      break;
    case 'assessment':
      div.innerHTML = '<h2>Αυτοαξιολόγηση</h2>'
        + '<p class="check">✓ ' + s.check + '</p>'
        + (s.end ? '<p class="end">' + s.end + '</p>' : '<p class="end">Τέλος</p>');
      break;
    default:
      div.innerHTML = '<p>' + (s.text || s.title || '') + '</p>';
  }
  return div;
}

LESSON.slides.forEach(function(s, i) {
  var el = renderSlide(s, i);
  document.getElementById('slides').appendChild(el);
  slides.push(el);
});

total = slides.length;

(function() {
  var dots = document.getElementById('dots');
  for (var i = 0; i < total; i++) {
    var d = document.createElement('button');
    d.className = 'dot' + (i === 0 ? ' active' : '');
    d.onclick = (function(idx) { return function() { goToSlide(idx); }; })(i);
    dots.appendChild(d);
  }
})();

function updateDots() {
  var ds = document.querySelectorAll('.dot');
  ds.forEach(function(d, i) { d.classList.toggle('active', i === current); });
}

function getSlideText(s) {
  switch (s.type) {
    case 'title':
      return LESSON.unit + '. ' + s.title + (s.sub ? '. ' + s.sub : '');
    case 'text':
      return s.text || '';
    case 'steps':
      return (s.title ? s.title + '. ' : '') + s.steps.join('. ');
    case 'list':
      return (s.title ? s.title + '. ' : '') + s.items.join('. ');
    case 'concept':
      return s.text || '';
    case 'practice':
      return (s.title || 'Συζητήστε') + '. ' + s.items.join('. ');
    case 'assessment':
      return s.check + (s.end ? '. ' + s.end.replace(/[✦✧]/g, '').trim() : '. Τέλος');
    default:
      return s.text || s.title || '';
  }
}

var advanceTimer = null;

function speakCurrent() {
  var text = getSlideText(LESSON.slides[current]);
  if (!text) return;
  var adv = autoPlay && !isPaused ? function() {
    var delay = Math.round(1800 / TTS.rate);
    advanceTimer = setTimeout(function() { if (!isPaused && autoPlay) goToSlide(current + 1); }, delay);
  } : null;
  TTS.speakWithAudio(current + 1, text, adv);
  // Warm the next slide's narration while the current one plays, so advancing sounds instant
  if (TTS.engine === 'piper' && window.PiperTTS && LESSON.slides[current + 1]) {
    PiperTTS.prefetch(getSlideText(LESSON.slides[current + 1]));
  }
}

function goToSlide(i) {
  // a pending auto-advance must never fire into the newly selected slide
  if (advanceTimer) { clearTimeout(advanceTimer); advanceTimer = null; }
  if (i < 0 || i >= total) return;
  TTS.stop();
  isPaused = false;
  document.getElementById('playBtn').textContent = '⏸';
  slides.forEach(function(s) { s.classList.remove('active'); });
  slides[i].classList.add('active');
  current = i;
  document.getElementById('counter').textContent = (i + 1) + ' / ' + total;
  updateDots();
  document.getElementById('prevBtn').disabled = (i === 0);
  document.getElementById('nextBtn').disabled = (i === total - 1);
  if (autoPlay && !isPaused) speakCurrent();
}

function navigate(dir) { goToSlide(current + dir); }

function togglePlay() {
  if (isPaused) {
    isPaused = false;
    document.getElementById('playBtn').textContent = '⏸';
    speakCurrent();
  } else {
    isPaused = true;
    if (advanceTimer) { clearTimeout(advanceTimer); advanceTimer = null; }
    TTS.stop();
    document.getElementById('playBtn').textContent = '▶';
  }
}

function toggleMute() {
  TTS.isMuted = !TTS.isMuted;
  document.getElementById('muteBtn').textContent = TTS.isMuted ? '🔇' : '🔊';
}

function showSpeed() {
  var badge = document.getElementById('speedBadge');
  badge.textContent = 'Ταχύτητα: ' + TTS.rate.toFixed(2) + '×';
  badge.classList.add('show');
  clearTimeout(badge._hide);
  badge._hide = setTimeout(function() { badge.classList.remove('show'); }, 1500);
}

document.addEventListener('keydown', function(e) {
  if (e.key === 'ArrowLeft') { e.preventDefault(); navigate(-1); }
  else if (e.key === 'ArrowRight') { e.preventDefault(); navigate(1); }
  else if (e.key === ' ') { e.preventDefault(); if (autoPlay) togglePlay(); }
  else if (e.key === 'm' || e.key === 'M') { toggleMute(); }
  else if (e.key === 'f' || e.key === 'F') {
    if (!document.fullscreenElement) document.documentElement.requestFullscreen();
    else document.exitFullscreen();
  }
  else if (e.key === '+' || e.key === '=') {
    TTS.rate = Math.min(2.0, TTS.rate + 0.1);
    showSpeed();
  }
  else if (e.key === '-' || e.key === '_') {
    TTS.rate = Math.max(0.5, TTS.rate - 0.1);
    showSpeed();
  }
});

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

goToSlide(0);
