// pack_js_activities_filter.js - 
/*
version/changes:
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
//    [bracketed notes] are ignored (v260923): "10-  3 -9b-20[main exercice]"
//    means "10-3-9b-20". Token "Na" is ALWAYS the whole group N ("19a"=="19");
//    "Nb", "Nc", ... pick single sub-items. The CSV is ignored while shown=
//    is present in the URL.
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
    // entries: one per top-level <li> of div1..div6 (badges 1,2,3... - the
    // italic "Δεν υπάρχουν..." placeholders count too, they burn a number),
    // plus one per EXTRAS <li> of div7 (badges 100,101... via #div7 ol).
    // A group wrapper (ul.meli-sub / ul.uniq-sub / ul.tag-sub) counts as ONE
    // number and registers its children as lettered tokens "9a","9b","9c"...
    var entries = [];
    var byToken = {};

    function registerEntry(num, li) {
        var entry = { num: num, li: li, subUl: null, subs: [] };
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
        var n = 0, i;
        for (var d = 1; d <= 6; d++) {
            var div = document.getElementById("div" + d);
            if (!div) continue;
            for (i = 0; i < div.children.length; i++) {
                if (div.children[i].tagName !== "LI") continue;
                n++;
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
    // Returns the key that was applied.
    function applyColumn3(raw) {
        buildMap();
        var val = String(raw || "").replace(/\[[^\]]*\]/g, " ").trim(); // v260923: drop [notes]
        var key = val.toLowerCase();
        if (key === "" || key === "all" || key === "showall") {
            restoreAll();
            return "all";
        }
        var tokens = val.split(/[-,]+/);
        var wantAll = {}, wantSub = {}, matched = 0, i, m;
        for (i = 0; i < tokens.length; i++) {
            var t = tokens[i].trim().toLowerCase();
            m = /^(\d+)([a-z]?)$/.exec(t);
            if (!m) continue;
            if (m[2] === "" || m[2] === "a") {   // v260923: "19a" == "19"
                if (byToken[m[1]]) { wantAll[m[1]] = true; matched++; }
            } else {
                if (byToken[m[1] + m[2]]) { wantSub[m[1] + m[2]] = true; matched++; }
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
            var all = !!wantAll[en.num];
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
    // rows: class,lesson,activities   (activities = 4-7-5-9b-32 | ALL | empty)
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
