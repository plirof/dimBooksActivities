// pack_js_activities_filter.js - 
/*
version/changes:
- v261002a - Pseudo-category "random*5": a token "random*5" in the CSV row /
              shown= URL (bare, or bracketed "[random*5]"; bare "random" = 1)
              shows N MORE categories picked at random from the ones the row
              does NOT list. Pool = whole groups with badge 1..49 that carry
              a data-tag (zone 2 / EXTRAS are always visible anyway, and the
              "Δεν υπάρχουν..." placeholders have no data-tag, so neither can
              be picked); a picked group shows whole, subs included. The pick
              is SEEDED with the ISO week of year (same as PHP date("W")),
              so the whole week shows the same "random" selection and it
              changes by itself when the week rolls over. The seed scope is
              the hardcoded constant RANDOM_SEED_MODE (top of this file):
              "week" (default - identical picks on every class/lesson),
              "week+class" or "week+class+lesson" for per class/lesson picks.
              The week comes from pack_Book.php (var PAF_WEEK printed from
              date("W") right before this script), falls back to the client
              clock, and ?week=NN in the URL overrides both for testing.
              Fewer pool categories than N -> all of them show.
- v260930d - Category NAMES in the token list: a bracketed token that names a
              category (the [tag] label / $fixedNumbers key) selects the WHOLE
              group, same as writing its badge number - "['spot-error']" or
              "[spot-error]" == "22". Works in the CSV row AND the shown=
              URL parameter. Quotes around the name are optional, spaces are
              tolerated, several names can be listed ("[spot-error],[maze]")
              or mixed with numbers ("4,['spot-error']-9b"), matching is
              case-insensitive, and a name selects EVERY section that uses
              the tag ("[quiz]" = base quiz 2 AND wordboard QUIZ 38).
              pack_Book.php v260930d stamps data-tag on every pinned <li>,
              buildMap() indexes it (byTag). Bracketed text that is NOT a
              known category stays an ignored note, exactly as in v260923
              (so "10[main exercice]-3" still means 10-3). Names must be
              extracted BEFORE the dash/comma split (tags like "spot-error"
              contain dashes).
- v260930c - Zone 2 never filtered: every entry with badge number >= 50 (the
              pack_Book.php v260930c auto-numbered "Λοιπές Δραστηριότητες"
              zone, and still the EXTRAS 100+) stays visible regardless of the
              CSV row or the shown= URL parameter. Rows/tokens decide 1..49.
- v260928b - Badges are FIXED numbers pinned by pack_Book.php (each top-level
              <li> carries data-num + an inline counter-reset): buildMap()
              now reads data-num instead of counting top-level <li> - fixed
              numbering leaves holes where a category is absent from the
              lesson, and counting would mislabel every token. Counting
              fallback kept for pages without pinning.
- v260928 - The EXTRAS section (div7, badges 100+) is NEVER filtered away:
              every entry with badge number >= 100 stays visible regardless of
              the CSV row or the shown= URL parameter (and keeps its original
              number stamp). Rows/tokens only decide badges 1..99.
- v260924 - While filtered, the children of a FULLY shown group (token "9" or
              "9a") kept the plain CSS counter label - and hidden <li> no
              longer increment that counter, so counter(list) counted only the
              VISIBLE top-level items and the unique group shown as the 3rd
              visible item was labelled "3a","3b" instead of "9a","9b"
              (dimC,2,9a-9b-12-6-4-16-18). Every visible sub <li> now gets the
              attr() stamp with its full "Na" label (same mechanism as the
              top-level badges); partial groups read "9b." instead of bare
              "b." too. No CSS counter can do this: display:none items never
              increment counters and attr() cannot read an ancestor.
- v260923a - Sub-numbering for ALL sections (pack_Book.php groups same-tag
              activities into class="tag-sub" wrappers): recognized next to
              meli-sub / uniq-sub. Sequence parsing: spaces and [bracketed
              notes] are ignored ("10-  3 -9b-20[main exercice]-21[text text]"
              means 10-3-9b-20-21), and a lettered token "Na" is ALWAYS the
              whole group N ("19a" == "19", even when 19 is a plain single
              activity); "Nb", "Nc", ... still pick single sub-items.
- v260922a - Optional per-class/lesson activity filter for pack_Book.php.
//

*/
//--------------------------------------------------------------------------
// INSTRUCTION/INFO:
// Optional per-class/lesson activity filter for pack_Book.php.
//
// pack_refresh_activities_per_class.csv (same folder) tells the page which
// numbered activities to show for the open class+lesson, e.g.
//
//     dimB,3,4-7-5-9b-32
//
// Non-listed activities are hidden IN PLACE: the visible ones keep their
// original grey badge numbers. The badges come from CSS counters, which
// renumber automatically once <li> are hidden - so while a filter is active
// every visible <li> gets its original number (or letter) stamped
// explicitly, and the stamps are removed again on show-all.
//
// Two ways to activate:
//
// 1) URL parameter (no polling needed, works without probeserver):
//        pack_Book.php?c=C&l=05&shown=4, 5, 7, 9b, 32
//    Commas or dashes both work as separators ("4-7-5-9b-32" too). Spaces and
//    unknown [bracketed notes] are ignored (v260923): "10-  3 -9b-20[main exercice]"
//    means "10-3-9b-20". Token "Na" is ALWAYS the whole group N ("19a"=="19");
//    "Nb", "Nc", ... pick single sub-items. The CSV is ignored while shown=
//    is present in the URL.
//
//    v260930d - a category NAME in [brackets] selects the whole category
//    instead of its number: shown=['spot-error'] == shown=22. Quotes optional
//    ([spot-error] too), case-insensitive, mixable with numbers/letters
//    ("4,['spot-error']-9b"), several names allowed ("[spot-error],[maze]").
//    A name covers every section using that tag ([quiz] = base quiz 2 AND
//    wordboard QUIZ 38). Bracket text that is not a known category is still
//    an ignored note. The valid names are the [tag] labels shown in the list
//    (same keys as $fixedNumbers in pack_Book.php: quiz, 0quiz_penalty,
//    unique, matching-lines, spot-error, ..., melispi, chess).
//
//    v261002a - the pseudo-category "random*5" shows 5 categories picked at
//    random from the ones the row does NOT list (whole groups, badges 1..49).
//    The pick is seeded with the WEEK OF YEAR: the same "random" selection
//    for the whole week, changing automatically on Monday. Write it bare
//    ("4-random*5-9b") or bracketed ("[random*5]"); bare "random" means 1.
//    The hardcoded RANDOM_SEED_MODE (top of this file) picks the seed scope:
//    "week" (default, identical picks on every class/lesson), "week+class",
//    "week+class+lesson". pack_Book.php supplies the server week (date("W"))
//    as var PAF_WEEK; ?week=NN in the URL overrides it for testing.
//
// 2) CSV polling (like pack_refresh_browser.txt): active only when the URL
//    contains "probeserver". The CSV is re-fetched every 30 seconds, so
//    editing it on the server updates every open page without a reload.
//
// If this file is missing, or the list is empty / ALL / ShowAll, the page
// shows the full list exactly as before.
//
// The "ignore activity filters" checkbox in pack_Book.php reloads the page
// with &no_act_filter=true, which disables ALL of this filtering (CSV and
// shown=) for that page view; other probeserver features are not affected.
//
// Console helper (CSV mode): paf_refresh() forces an immediate re-fetch.
//--------------------------------------------------------------------------
(function () {
    "use strict";

    var CSV_FILE = "pack_refresh_activities_per_class.csv";
    var POLL_MS  = 30000;                 // re-fetch every 30 seconds

    // v261002a - seed scope of the random*N pseudo-category. "week" -> the
    // same random picks on every class and lesson for the whole week;
    // "week+class" -> different per class, same for every lesson of it;
    // "week+class+lesson" -> different per class AND per lesson. Every mode
    // stays constant within the week and changes on its own at the rollover.
    var RANDOM_SEED_MODE = "week";        // "week" | "week+class" | "week+class+lesson"

    var CLS_NUM = "paf-num";              // stamped on visible top-level <li>
    var CLS_SUB = "paf-sub";              // stamped on visible lettered sub <li>
    var A_NUM   = "data-paf-num";         // original badge number of the <li>
    var A_LET   = "data-paf-letter";      // original full label ("9a", "9b"...) of the sub <li>
    var A_HID   = "data-paf-hidden";      // marks anything WE hid (never touch other logic's display)

    //-- gate: ?shown=... (manual link) OR "probeserver" (CSV polling) -------
    function getParam(name) {
        var m = new RegExp("[?&]" + name + "=([^&]*)").exec(location.search);
        return m ? decodeURIComponent(m[1]).replace(/\+/g, " ") : null; // null = absent
    }
    // "ignore activity filters" checkbox (pack_Book.php) reloads the page
    // with ?no_act_filter=true: skip ALL of our filtering (CSV and shown=)
    // and leave the full list as-is. Other probeserver features are not
    // touched (they live in other scripts).
    if ((getParam("no_act_filter") || "").toLowerCase() === "true") return;
    var urlShown = getParam("shown");
    var urlMode = urlShown !== null;
    if (!urlMode && location.search.indexOf("probeserver") === -1) return;

    //-- this page's class + lesson (defaults A/1, same as pack_Book.php) ----
    function normClass(s) { return String(s || "").trim().toLowerCase().replace(/^dim/, ""); }
    var myClass  = normClass(getParam("c")) || "a";
    var myLesson = parseInt(getParam("l"), 10) || 1;

    //-- v261002a - week of year + seeded random, for the random*N token ----
    // 32-bit multiply - Math.imul where available, ES5 fallback for old
    // browsers (the hash and the PRNG below need wrap-around 32-bit math).
    var imul = Math.imul || function (a, b) {
        var ah = (a >>> 16) & 0xffff, al = a & 0xffff;
        var bh = (b >>> 16) & 0xffff, bl = b & 0xffff;
        return ((al * bl) + (((ah * bl + al * bh) << 16) >>> 0)) | 0;
    };
    // ISO week number (Monday = first day, week 1 holds the first Thursday) -
    // identical to PHP date("W"), e.g. 2026-10-02 -> 40.
    function isoWeekOfDate(d) {
        var t = new Date(Date.UTC(d.getFullYear(), d.getMonth(), d.getDate()));
        var day = t.getUTCDay() || 7;             // ISO: Mon=1 ... Sun=7
        t.setUTCDate(t.getUTCDate() + 4 - day);   // hop to this week's Thursday
        var yearStart = new Date(Date.UTC(t.getUTCFullYear(), 0, 1));
        return Math.ceil(((t - yearStart) / 86400000 + 1) / 7);
    }
    // Week for the random seed: pack_Book.php prints the SERVER week as
    // var PAF_WEEK (date("W")) right before this script - authoritative, so
    // a wrong device clock cannot reshuffle the page. ?week=NN overrides
    // both (testing other weeks); the client clock is the last fallback.
    function weekOfYear() {
        var w = parseInt(getParam("week"), 10);
        if (w >= 1 && w <= 53) return w;
        if (typeof window.PAF_WEEK === "number" && window.PAF_WEEK >= 1 && window.PAF_WEEK <= 53) {
            return window.PAF_WEEK;
        }
        return isoWeekOfDate(new Date());
    }
    // FNV-1a: seed string ("40", "40|b|3") -> 32-bit integer
    function strHash(s) {
        var h = 2166136261;
        for (var i = 0; i < s.length; i++) {
            h ^= s.charCodeAt(i);
            h = imul(h, 16777619);
        }
        return h >>> 0;
    }
    // mulberry32: tiny deterministic PRNG - same seed, same sequence
    function mulberry32(a) {
        return function () {
            a |= 0; a = (a + 0x6D2B79F5) | 0;
            var t = imul(a ^ (a >>> 15), 1 | a);
            t = (t + imul(t ^ (t >>> 7), 61 | t)) ^ t;
            return ((t ^ (t >>> 14)) >>> 0) / 4294967296;
        };
    }
    function seededShuffle(arr, rng) {        // in-place Fisher-Yates
        for (var i = arr.length - 1; i > 0; i--) {
            var j = Math.floor(rng() * (i + 1));
            var tmp = arr[i]; arr[i] = arr[j]; arr[j] = tmp;
        }
        return arr;
    }
    // Seed of the random*N picks, per the hardcoded RANDOM_SEED_MODE above.
    function randomSeed() {
        var mode = String(RANDOM_SEED_MODE || "week").toLowerCase();
        var s = String(weekOfYear());
        if (mode === "week+class" || mode === "week+class+lesson") s += "|" + myClass;
        if (mode === "week+class+lesson") s += "|" + myLesson;
        return strHash(s);
    }

    var styleTag = null;
    var touched  = [];     // elements we set display:none on
    var stamped  = [];     // elements carrying our badge overrides
    var appliedKey = null; // column-3 text currently applied ("all" or the token list)

    //-- CSS that keeps the original badge numbers while filtered ------------
    // (hidden <li> no longer increment the CSS counters, so the counters would
    // renumber the remaining items; attr() restores the original number/letter)
    function ensureStyle() {
        if (styleTag) return;
        styleTag = document.createElement("style");
        styleTag.textContent =
            "li." + CLS_NUM + "::before{content:attr(" + A_NUM + ') " " !important;}' +
            ".meli-sub li." + CLS_SUB + "::before,.uniq-sub li." + CLS_SUB +
            "::before,.tag-sub li." + CLS_SUB +
            "::before{content:attr(" + A_LET + ')". " !important;}';
        document.getElementsByTagName("head")[0].appendChild(styleTag);
    }

    //---------------- NUMBERING MAP (mirrors the CSS counters) --------------
    // v260928b: badges are PHP-pinned fixed numbers - each top-level <li>
    // carries data-num, which is authoritative (holes are expected where a
    // category is absent from the lesson).
    // entries: one per top-level <li> of div1..div6 (the italic "Δεν
    // υπάρχουν..." placeholders count too, they burn a number), plus one per
    // EXTRAS <li> of div7 (badges 100,101... via #div7 ol). A group wrapper
    // (ul.meli-sub / ul.uniq-sub / ul.tag-sub) counts as ONE number and
    // registers its children as lettered tokens "9a","9b","9c"...
    var entries = [];
    var byToken = {};
    var byTag = {};   // v260930d: category name (data-tag) -> [entries] - a
                      // name may be used by several sections ([quiz]: div1=2
                      // AND div5=38), so the value is a LIST of entries

    function registerEntry(num, li) {
        var entry = { num: num, li: li, subUl: null, subs: [] };
        // v260930d - index the entry under its category name (data-tag,
        // stamped by pack_Book.php next to data-num) so the CSV / shown=
        // list can use "['spot-error']" instead of 22.
        var tg = (li.getAttribute("data-tag") || "").trim().toLowerCase();
        if (tg) { if (!byTag[tg]) byTag[tg] = []; byTag[tg].push(entry); }
        for (var c = 0; c < li.children.length; c++) {
            var ch = li.children[c];
            if (ch.tagName === "UL" && /(^|\s)(meli-sub|uniq-sub|tag-sub)(\s|$)/.test(ch.className)) {
                entry.subUl = ch;
                var letters = "abcdefghijklmnopqrstuvwxyz", k = 0;
                for (var s = 0; s < ch.children.length; s++) {
                    if (ch.children[s].tagName !== "LI") continue;
                    var sub = { letter: letters.charAt(k) || String(k + 1), li: ch.children[s] };
                    k++;
                    entry.subs.push(sub);
                    byToken[num + sub.letter] = sub;
                }
                break;
            }
        }
        entries.push(entry);
        byToken[num] = entry;
    }

    function buildMap() {
        entries = [];
        byToken = {};
        byTag = {};   // v260930d - rebuilt on every (re)apply
        var n = 0, i;
        for (var d = 1; d <= 6; d++) {
            var div = document.getElementById("div" + d);
            if (!div) continue;
            for (i = 0; i < div.children.length; i++) {
                if (div.children[i].tagName !== "LI") continue;
                // v260928b - read the pinned badge number (fixed numbering
                // with holes); counting is only a fallback for unpinned pages
                var dn = parseInt(div.children[i].getAttribute("data-num"), 10);
                n = isNaN(dn) ? n + 1 : dn;
                registerEntry(String(n), div.children[i]);
            }
        }
        var div7 = document.getElementById("div7");
        if (div7) {
            var ol7 = div7.getElementsByTagName("ol")[0];
            if (ol7) {
                var e = 100; // #div7 ol{counter-reset:list 99} -> first badge is 100
                for (i = 0; i < ol7.children.length; i++) {
                    if (ol7.children[i].tagName !== "LI") continue;
                    registerEntry(String(e), ol7.children[i]);
                    e++;
                }
            }
        }
    }

    //---------------------- show / hide / stamp -----------------------------
    function isHiddenByUs(el) { return el.getAttribute(A_HID) === "1"; }

    function hide(el) {
        if (isHiddenByUs(el)) return;
        el.setAttribute(A_HID, "1");
        el.style.display = "none";
        touched.push(el);
    }
    function unhide(el) {
        if (!isHiddenByUs(el)) return;
        el.removeAttribute(A_HID);
        el.style.display = "";
        var i = touched.indexOf(el);
        if (i !== -1) touched.splice(i, 1);
    }
    function stampNum(li, num) {
        li.classList.add(CLS_NUM);
        li.setAttribute(A_NUM, num);
        if (stamped.indexOf(li) === -1) stamped.push(li);
    }
    function stampLetter(li, letter) {
        li.classList.add(CLS_SUB);
        li.setAttribute(A_LET, letter);
        if (stamped.indexOf(li) === -1) stamped.push(li);
    }
    function restoreAll() {
        while (touched.length) unhide(touched[0]);
        for (var i = 0; i < stamped.length; i++) {
            stamped[i].classList.remove(CLS_NUM, CLS_SUB);
            stamped[i].removeAttribute(A_NUM);
            stamped[i].removeAttribute(A_LET);
        }
        stamped = [];
    }

    //----------- hide headings of sections that are filtered away -----------
    // Runs on the big <ol> (section headings between the divs) and inside
    // each div (Μελίσπη / Κουίζ sub-headings). #probeserver is never touched.
    function directLis(el) {
        var out = [];
        for (var i = 0; i < el.children.length; i++)
            if (el.children[i].tagName === "LI") out.push(el.children[i]);
        return out;
    }
    function collectLis(items) {
        var out = [];
        for (var i = 0; i < items.length; i++) {
            var el = items[i];
            if (el.tagName === "LI") out.push(el);
            else if ((el.tagName === "DIV" || el.tagName === "OL") && el.id !== "probeserver")
                out = out.concat(directLis(el));
        }
        return out;
    }
    function scanHeaders(container) {
        var kids = container.children, i = 0;
        while (i < kids.length) {
            if (kids[i].tagName === "HR" || kids[i].tagName === "B") {
                var heads = [];
                while (i < kids.length && (kids[i].tagName === "HR" || kids[i].tagName === "B")) { heads.push(kids[i]); i++; }
                var items = [];
                while (i < kids.length && kids[i].tagName !== "HR" && kids[i].tagName !== "B") { items.push(kids[i]); i++; }
                var lis = collectLis(items);
                if (lis.length > 0) {
                    var anyVisible = false;
                    for (var k = 0; k < lis.length; k++)
                        if (!isHiddenByUs(lis[k])) { anyVisible = true; break; }
                    for (k = 0; k < heads.length; k++) {
                        if (anyVisible) unhide(heads[k]); else hide(heads[k]);
                    }
                }
            } else {
                i++;
            }
        }
    }
    function updateHeaders() {
        var div1 = document.getElementById("div1");
        if (!div1) return;
        scanHeaders(div1.parentNode);            // the big <ol>: div1..div6 sections
        for (var d = 1; d <= 7; d++) {
            var dv = document.getElementById("div" + d);
            if (dv) scanHeaders(dv);             // Μελίσπη/Κουίζ inside div1, EXTRAS in div7
        }
    }

    //--------------------------- applying a row -----------------------------
    // raw = column 3 of the CSV row, or the shown= URL value.
    // Separators: '-' and ',' both work ("4-7-5-9b-32", "4, 5, 7, 9b, 32").
    // v260923: spaces and [bracketed notes] are ignored BEFORE splitting, so
    // "10-  3 -9b-20[main exercice]-21[text text]" means 10-3-9b-20-21, and a
    // token "Na" is always the whole group N ("19a" == "19", even when 19 is
    // a plain single activity); "Nb", "Nc", ... pick single sub-items.
    // v260930d: a bracketed CATEGORY NAME selects the whole category instead
    // of its number ("['spot-error']" / "[spot-error]" == "22"). Quotes are
    // optional, matching is case-insensitive, several names can share one
    // bracket ("[spot-error,maze]") and names mix freely with numbers
    // ("4,['spot-error']-9b"). Names MUST be pulled out before the dash/comma
    // split - tags like "spot-error" contain dashes. A bracket group none of
    // whose parts names a known category is still an ignored note (v260923).
    // Returns the key that was applied.
    function applyColumn3(raw) {
        buildMap();
        // v260930d - key from the RAW text: rows that differ only in their
        // notes (or that use names vs numbers) must not compare equal.
        var val = String(raw || "");
        var key = val.trim().toLowerCase();
        if (key === "" || key === "all" || key === "showall") {
            restoreAll();
            return "all";
        }
        // extract [category-name] tokens (known names harvested, ALL bracket
        // groups removed from the text - unknown ones stay ignored notes).
        // v261002a: "[random*5]" inside a bracket counts too.
        var nameTokens = [];
        var randomCount = 0;              // v261002a - total N of random* tokens
        val = val.replace(/\[([^\]]*)\]/g, function (_m, inner) {
            var pieces = String(inner).split(",");
            for (var p = 0; p < pieces.length; p++) {
                var nm = pieces[p].trim().replace(/^['"]+|['"]+$/g, "").toLowerCase();
                var rm = /^random(?:\*(\d+))?$/.exec(nm);
                if (rm) { randomCount += rm[1] ? parseInt(rm[1], 10) : 1; continue; }
                if (nm !== "" && byTag[nm]) nameTokens.push(nm);
            }
            return " ";
        }).trim();
        var tokens = val.split(/[-,]+/);
        var wantAll = {}, wantSub = {}, matched = 0, i, m;
        for (i = 0; i < tokens.length; i++) {
            var t = tokens[i].trim().toLowerCase();
            var rr = /^random(?:\*(\d+))?$/.exec(t);   // v261002a: bare "random*5"
            if (rr) { randomCount += rr[1] ? parseInt(rr[1], 10) : 1; continue; }
            m = /^(\d+)([a-z]?)$/.exec(t);
            if (!m) continue;
            if (m[2] === "" || m[2] === "a") {   // v260923: "19a" == "19"
                if (byToken[m[1]]) { wantAll[m[1]] = true; matched++; }
            } else {
                if (byToken[m[1] + m[2]]) { wantSub[m[1] + m[2]] = true; matched++; }
            }
        }
        // v260930d - a name token shows every entry carrying that tag (whole
        // group, all lettered children) - same as listing its badge number
        for (i = 0; i < nameTokens.length; i++) {
            var tgEntries = byTag[nameTokens[i]];
            for (var k = 0; k < tgEntries.length; k++) {
                wantAll[String(tgEntries[k].num)] = true;
                matched++;
            }
        }
        // v261002a - random*N: pick N categories seeded by the week of year
        // (see randomSeed/RANDOM_SEED_MODE), from the ones the row does NOT
        // show. Pool = whole groups with badge 1..49 that carry a data-tag:
        // zone 2 + EXTRAS (>= 50) are always visible anyway, and the "Δεν
        // υπάρχουν..." placeholders carry no data-tag, so neither can be
        // picked. A picked group shows whole (subs included). Fewer pool
        // entries than N -> all of them show. Same seed -> same picks all
        // week, and the 30s polls cannot flicker (the row text never changes).
        if (randomCount > 0) {
            var pool = [];
            for (i = 0; i < entries.length; i++) {
                var en = entries[i];
                if (parseInt(en.num, 10) < 50 && !wantAll[en.num] &&
                    (en.li.getAttribute("data-tag") || "").trim() !== "") {
                    pool.push(en.num);
                }
            }
            seededShuffle(pool, mulberry32(randomSeed()));
            for (i = 0; i < pool.length && i < randomCount; i++) {
                wantAll[pool[i]] = true;
                matched++;
            }
        }
        if (matched === 0) {                 // safety: a typo must never blank the list
            restoreAll();
            return "all";
        }
        ensureStyle();
        restoreAll();                        // start clean, then mark the subset
        for (i = 0; i < entries.length; i++) {
            var en = entries[i];
            // v260930: zone 2 (auto-numbered tags, badges 50+, incl. the EXTRAS
            // 100+) is ALWAYS shown, whatever the CSV row or shown= lists -
            // parseInt(num) >= 50 counts as "all". Rows/tokens decide 1..49.
            var all = !!wantAll[en.num] || parseInt(en.num, 10) >= 50;
            var subsWanted = 0, s;
            for (s = 0; s < en.subs.length; s++)
                if (wantSub[en.num + en.subs[s].letter]) subsWanted++;
            if (all || subsWanted > 0) {
                unhide(en.li);
                stampNum(en.li, en.num);     // keep the original badge number
            } else {
                hide(en.li);
                continue;
            }
            if (!en.subUl) continue;
            // v260924: stamp EVERY visible child with its full "Na" label,
            // whole and partial groups alike. Hidden <li> no longer increment
            // the CSS "list" counter, so inside a shown group counter(list)
            // only counts VISIBLE top-level items (group 9 shown as the 3rd
            // visible item numbered its children "3a","3b" instead of
            // "9a","9b"). attr() stamps keep the original number whatever is
            // hidden - same mechanism as the top-level badges above.
            for (s = 0; s < en.subs.length; s++) {
                var sb = en.subs[s];
                if (all || wantSub[en.num + sb.letter]) {
                    unhide(sb.li);
                    stampLetter(sb.li, en.num + sb.letter);   // "9a", "9b", ...
                } else {
                    hide(sb.li);
                }
            }
        }
        updateHeaders();
        return key;
    }

    //----------------------------- CSV parsing ------------------------------
    // rows: class,lesson,activities   (activities = 4-7-5-9b-32 | ['spot-error']
    //       | 4,['spot-error']-9b | ALL | empty)
    // '#' lines and a literal "class,..." header line are ignored.
    function parseCsv(text) {
        var rows = [];
        var lines = String(text || "").split(/\r?\n/);
        for (var i = 0; i < lines.length; i++) {
            var line = lines[i].trim();
            if (line === "" || line.charAt(0) === "#") continue;
            var parts = line.split(",");
            if (parts.length < 2) continue;
            if (parts[0].trim().toLowerCase() === "class") continue; // header line
            rows.push({
                cls: normClass(parts[0]),
                les: parseInt(parts[1], 10),
                act: parts.length > 2 ? parts.slice(2).join(",").trim() : ""
            });
        }
        return rows;
    }

    function handleCsvText(text) {
        var rows = parseCsv(text);
        var match = null;
        for (var i = 0; i < rows.length; i++)
            if (rows[i].cls === myClass && rows[i].les === myLesson) match = rows[i]; // LAST matching row wins
        if (!match) {
            if (appliedKey !== "all") appliedKey = applyColumn3(""); // no row for us -> show everything
            return;
        }
        var key = match.act.trim().toLowerCase();
        if (key === appliedKey) return;          // unchanged since the last poll
        appliedKey = applyColumn3(match.act);
    }

    //--------------------------- 30-second poll -----------------------------
    function fetchTick() {
        try {
            var xhr = new XMLHttpRequest();
            xhr.open("GET", CSV_FILE + "?" + Math.random(), true); // cache-buster, as in pack_js_footer.js
            xhr.onreadystatechange = function () {
                if (xhr.readyState !== 4) return;
                if (xhr.status !== 200) return;  // missing file / server hiccup: keep current state
                try { handleCsvText(xhr.responseText); } catch (e) {}
            };
            xhr.send();
        } catch (e) {}
    }

    function boot() {
        try {
            if (!document.getElementById("div1")) return; // only on pack_Book pages
            if (urlMode) {
                appliedKey = applyColumn3(urlShown); // manual link: one-shot, CSV ignored
                return;
            }
            window.paf_refresh = fetchTick;      // console helper for forced re-fetch
            fetchTick();
            setInterval(fetchTick, POLL_MS);
        } catch (e) {}
    }
    if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", boot);
    else boot();
})();
