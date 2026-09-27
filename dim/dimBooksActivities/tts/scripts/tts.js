var TTS = TTS || {};

(function() {
  var audio = null;
  var isPlaying = false;
  var onEnd = null;
  var muted = false;
  var stopped = false;
  var rate = 0.85;
  var liveUtterance = null;
  var speakToken = 0;

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
    utterance.onend = function() { liveUtterance = null; if (onDone) onDone(); };
    utterance.onerror = function() { liveUtterance = null; if (onDone) onDone(); };
    // Keep a reference for the whole utterance lifetime: Chrome garbage-collects
    // locally scoped utterances and cuts off speech mid-way when it does.
    liveUtterance = utterance;
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
          el.pause();
          audio = null;
          URL.revokeObjectURL(url);
          if (token === speakToken && !stopped) { isPlaying = false; if (onEnd) { var cb = onEnd; onEnd = null; cb(); } }
        }
        el.addEventListener('error', finish);
        el.addEventListener('ended', finish);
        el.playbackRate = rate;
        el._piperUrl = url;
        audio = el;
        el.play().then(function() {
          if (token !== speakToken) { el.pause(); URL.revokeObjectURL(url); return; }
          isPlaying = true;
        }).catch(function(e) {
          if (e && e.name === 'NotAllowedError' && TTS.onAutoplayBlocked) {
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
      }).catch(function(e) {
        if (e && e.name === 'NotAllowedError' && TTS.onAutoplayBlocked) {
          // Chrome blocks sound until the first click/keypress — wait instead of falling back
          audio = null;
          isPlaying = false;
          TTS.onAutoplayBlocked();
          return;
        }
        fallback();
      });
    } else {
      isPlaying = true;
      webSpeechSpeak(text, function() { done(); });
    }
  };

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
    stopped = false;
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

  // Ask for the voice list once at load: Chromium populates it asynchronously, and the
  // first slide may start speaking before getVoices() would otherwise return anything.
  if (typeof speechSynthesis !== 'undefined') {
    speechSynthesis.getVoices();
    speechSynthesis.onvoiceschanged = function() { speechSynthesis.getVoices(); };
  }
})();
