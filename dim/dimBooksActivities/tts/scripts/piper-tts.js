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
  var blobCache = {}; // slide text -> generated WAV Blob (a fresh object URL is made per call)

  var BOS = '^';
  var EOS = '$';
  var PAD = '_';

  // Extra padding tokens inserted after a sentence-ending "." "!" "?" (between sentences
  // only). The model renders each PAD as a short silence (~25 ms), so 8 pads ≈ 200 ms of
  // extra breathing room at full stops.
  var SENTENCE_PAUSE_PADS = 8;

  // Extra padding tokens inserted after a comma "," (between clauses only).
  // 3 pads ≈ 75 ms of extra pause at commas.
  var COMMA_PAUSE_PADS = 3;

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

      // stretch the pause when this sentence ended on a full stop and another follows
      var lastPhoneme = sentencePhonemes.length > 0 ? sentencePhonemes[sentencePhonemes.length - 1] : '';
      if (s < textPhonemes.length - 1 && (lastPhoneme === '.' || lastPhoneme === '!' || lastPhoneme === '?')) {
        for (var p = 0; p < SENTENCE_PAUSE_PADS; p++) {
          phonemeIds.push(idMap[PAD][0]);
        }
      }
      // stretch the pause at comma
      if (s < textPhonemes.length - 1 && lastPhoneme === ',') {
        for (var p = 0; p < COMMA_PAUSE_PADS; p++) {
          phonemeIds.push(idMap[PAD][0]);
        }
      }
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

    // Prepend zero-width space to suppress initial vowel artifact ("ΕΕΕ") from Greek voice
    var synthesisText = '\u200B' + text;

    // Serve a repeated slide text from cache: creating an object URL from the stored Blob
    // is instant, so prefetched slides start playing with no synthesis wait. The URL is
    // owned (and revoked) by the caller — the cached Blob itself stays valid.
    if (blobCache[synthesisText]) {
      return URL.createObjectURL(blobCache[synthesisText]);
    }

    var noiseScale = voiceConfig.inference.noise_scale ?? 0.667;
    var lengthScale = voiceConfig.inference.length_scale ?? 1.0;
    var noiseWScale = voiceConfig.inference.noise_w ?? 0.8;

    var phonemeIds = phonemesToIds(voiceConfig.phoneme_id_map, textToPhonemes(synthesisText));

    var feeds = {
      input: new ort.Tensor('int64', new BigInt64Array(phonemeIds.map(function(x) { return BigInt(x); })), [1, phonemeIds.length]),
      input_lengths: new ort.Tensor('int64', BigInt64Array.from([BigInt(phonemeIds.length)]), [1]),
      scales: new ort.Tensor('float32', Float32Array.from([noiseScale, lengthScale, noiseWScale]), [3])
    };

    var results = await session.run(feeds);
    var out = results.output;
    var floatArray = out.data || out.cpuData; // ort-web 1.14 exposes tensor data as .data
    var blob = float32ToWavBlob(floatArray, voiceConfig.audio.sample_rate);
    blobCache[synthesisText] = blob;
    return URL.createObjectURL(blob);
  }

  function prefetch(text) {
    // fire-and-forget warm-up (e.g. the next slide while the current one narrates)
    synthesize('\u200B' + text).catch(function() {});
  }

  if (window.TTS && window.TTS.piperBase) {
    init(window.TTS.piperBase);
  }

  return {
    init: init,
    synthesize: synthesize,
    prefetch: prefetch,
    get state() { return state; },
    get cacheCount() { return Object.keys(blobCache).length; },
    setSentencePause: function(pads) { SENTENCE_PAUSE_PADS = Math.max(0, pads | 0); },
    setCommaPause: function(pads) { COMMA_PAUSE_PADS = Math.max(0, pads | 0); },
    getSentencePause: function() { return SENTENCE_PAUSE_PADS; },
    getCommaPause: function() { return COMMA_PAUSE_PADS; }
  };
})();
