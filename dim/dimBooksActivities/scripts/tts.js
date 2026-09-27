var TTS = TTS || {};

(function() {
  var audio = null;
  var isPlaying = false;
  var onEnd = null;
  var muted = false;
  var stopped = false;
  var rate = 0.85;

  function clampRate(v) {
    return Math.min(2.0, Math.max(0.5, v));
  }

  function done() {
    if (stopped) return;
    isPlaying = false;
    if (onEnd) { var cb = onEnd; onEnd = null; cb(); }
  }

  function webSpeechSpeak(text, onDone) {
    if (typeof speechSynthesis === 'undefined') { if (onDone) onDone(); return; }
    var utterance = new SpeechSynthesisUtterance(text);
    utterance.lang = 'el-GR';
    utterance.rate = rate;
    var voices = speechSynthesis.getVoices();
    if (voices.length > 0) {
      for (var i = 0; i < voices.length; i++) {
        if (voices[i].lang.indexOf('el') === 0) {
          utterance.voice = voices[i];
          break;
        }
      }
    }
    utterance.onend = function() { if (onDone) onDone(); };
    utterance.onerror = function() { if (onDone) onDone(); };
    speechSynthesis.speak(utterance);
  }

  TTS.audioBase = '';

  TTS.speak = function(text, callback) {
    TTS.stop();
    stopped = false;
    onEnd = callback || null;
    if (!text || !text.trim() || muted) { done(); return; }
    isPlaying = true;
    webSpeechSpeak(text, function() { done(); });
  };

  TTS.speakWithAudio = function(slideIndex, text, callback) {
    TTS.stop();
    stopped = false;
    onEnd = callback || null;
    if (!text || !text.trim() || muted) { done(); return; }

    if (TTS.audioBase) {
      var audioPath = TTS.audioBase + String(slideIndex).padStart(2, '0') + '.mp3';
      var el = new Audio(audioPath);
      var fellBack = false;

      function finish() {
        audio = null;
        if (!stopped) { isPlaying = false; if (onEnd) { var cb = onEnd; onEnd = null; cb(); } }
      }

      function fallback() {
        if (fellBack) return;
        fellBack = true;
        el = null;
        webSpeechSpeak(text, function() { finish(); });
      }

      el.addEventListener('error', fallback);
      el.addEventListener('ended', function() {
        audio = null;
        finish();
      });
      el.play().then(function() {
        el.playbackRate = rate;
        audio = el;
        isPlaying = true;
      }).catch(function() {
        fallback();
      });
    } else {
      isPlaying = true;
      webSpeechSpeak(text, function() { done(); });
    }
  };

  TTS.stop = function() {
    stopped = true;
    if (audio) { audio.pause(); audio = null; }
    if (typeof speechSynthesis !== 'undefined') speechSynthesis.cancel();
    isPlaying = false;
    onEnd = null;
  };

  Object.defineProperty(TTS, 'isSpeaking', {
    get: function() { return isPlaying; }
  });

  Object.defineProperty(TTS, 'isMuted', {
    get: function() { return muted; },
    set: function(v) { muted = !!v; }
  });

  Object.defineProperty(TTS, 'rate', {
    get: function() { return rate; },
    set: function(v) {
      rate = clampRate(v);
      if (audio) audio.playbackRate = rate;
    }
  });
})();
