/*
  pdfpage_links.js  -v003-260906

  Changes :
  v003-260906: also appends a light-blue "Melispi activities :" box (3rd row)
  under each regular week heading, right after Teacher Presentations. Data
  comes from window.MELISPI = {CLASS:{LESSON:[{u,t},...]}} (csv2html.php v020
  reading melispi_links.csv). Each class renders as an expandable chip
  "Class X (n)" (native <details>/<summary>): clicking a chip expands only
  that class's links (opening one closes the others); classes with 0 links
  are greyed out and not clickable. Each expanded link is prefixed with a
  bullet. Nothing is shown when a lesson has no Melispi links at all.
  v002-260823: also appends a "Teacher Presentations :" line (always all 6
  classes) pointing at <window.PRES_BASE_URL>/TPE-dimXX-PEDIO/presentations/
  lessonYY.html (XX = class, YY = the week's lesson number, zero-padded).
  Links are shown even for lessons whose .html files do not exist yet (404
  until added). Insertion order per heading: PDF box first, then presentations.
  v001-260813 : Initial version. For every per-week heading (<h3 data-lesson="NN">)
  that is a regular teaching week (NN != "00"), appends 6 links (classes
  A,B,C,D,E,ST) that open ../ShowPDFPages/show_pdf.html showing that lesson's
  PDF pages for each class's textbook.

  Info : Helper script - Loaded by csv2html.php. For every per-week heading (<h3 data-lesson="NN">)
  that is a regular teaching week (NN != "00"), appends the per-week link boxes:
  TEACHER PDF pages (orange), Teacher Presentations (green), Melispi activities
  (light-blue, v003).

  Data source:
    window.PDFPAGES     -> { CLASS: { "LESSON": { file, pages }, ... }, ... }
                           (CLASS in A,B,C,D,E,ST; LESSON zero-padded "01".."30")
    window.MELISPI      -> { CLASS: { "LESSON": [ {u: url, t: title}, ... ] }, ... }
                           (same CLASS/LESSON keys; only entries that HAVE links)
    window.PDF_BASE_URL -> full LAN URL prefix for the BOOKS_TPE_PDF folder
    window.SHOWPDF_LINK -> "../ShowPDFPages/show_pdf.html"
    window.PRES_BASE_URL-> "../dim/dimBooksActivities/" (csv2html.php $_PRES_BASE_URL)

  The "pages" value is taken VERBATIM from the CSV cell: today a single int
  ("11"); if a cell later holds a range ("11-20") it is passed through
  unchanged (encodeURIComponent keeps the "-" intact).
*/
(function () {
    "use strict";

    function buildUrl(showPdf, pages, baseUrl, file) {
        return showPdf
            + "?pages=" + encodeURIComponent(pages)
            + "&pdfpath=" + encodeURIComponent(baseUrl + file);
    }

    // Escape a data string (Melispi titles/URLs come from a CSV) for safe
    // use inside innerHTML attribute/text positions.
    function escH(s) {
        return String(s).replace(/&/g, "&amp;").replace(/</g, "&lt;")
                        .replace(/>/g, "&gt;").replace(/"/g, "&quot;");
    }

    function init() {
        var data = window.PDFPAGES;
        if (!data) return;

        var baseUrl = window.PDF_BASE_URL || "http://192.168.1.200/imgMJT/BOOKS_TPE_PDF/";
        var showPdf = window.SHOWPDF_LINK || "../ShowPDFPages/show_pdf.html";
        var presBase = window.PRES_BASE_URL || "../dim/dimBooksActivities/";
        var classes = ["A", "B", "C", "D", "E", "ST"];
        var presentationParams=window.PRES_URL_PARAMS || "speed=0.9&mode=auto"; //not implemented yet as csv2html URL param

        var heads = document.querySelectorAll("h3[data-lesson]");
        if (!heads || !heads.length) return;

        Array.prototype.forEach.call(heads, function (h3) {
            var raw = h3.getAttribute("data-lesson");
            if (!raw) return;

            // Normalise to zero-padded "01".."30" (matches PHP json_encode keys).
            var n = parseInt(raw, 10);
            if (isNaN(n)) return;
            var L = (n < 10 ? "0" : "") + n;
            if (L === "00") return; // special/event week -> skip (no PDF/presentation links)

            var links = [];
            for (var i = 0; i < classes.length; i++) {
                var c = classes[i];
                var row = data[c] && data[c][L];
                if (!row) continue;
                var url = buildUrl(showPdf, row.pages, baseUrl, row.file);
                links.push('<a target="_blank" href="' + url + '">Class ' + c + '</a>');
            }

            // Teacher Presentations: always all 6 classes, even when the
            // lessonYY.html file does not exist yet (404 until added).
            var presLinks = [];
            for (var j = 0; j < classes.length; j++) {
                var pc = classes[j];
                var purl = presBase + "TPE-dim" + pc + "-PEDIO/presentations/lesson" + L + ".html?"+presentationParams;
                presLinks.push('<a target="_blank" href="' + purl + '">Class ' + pc + '</a>');
            }

            // Insert in order: heading -> TEACHER PDF pages -> Teacher Presentations.
            var prev = h3;
            if (links.length) {
                var box = document.createElement("div");
                box.className = "pdfpagelinks";
                box.style.margin = "2px 0 8px 0";
                box.innerHTML = '<small style="background-color: orange;" ><b>TEACHER PDF pages:</b> ' + links.join(" | ") + '</small>';
                prev.insertAdjacentElement("afterend", box);
                prev = box;
            }
            if (presLinks.length) {
                var pbox = document.createElement("div");
                pbox.className = "presentationlinks";
                pbox.style.margin = "2px 0 8px 0";
                pbox.innerHTML = '<small style="background-color: lightgreen;" ><b>Teacher Presentations :</b> ' + presLinks.join(" | ") + '</small>';
                prev.insertAdjacentElement("afterend", pbox);
                prev = pbox;
            }

            // v003: Melispi activities - per-class expandable chips (light-blue box).
            // window.MELISPI[CLASS][LESSON] = [{u: url, t: title}, ...]
            var mel = window.MELISPI;
            if (mel) {
                var chips = [];
                var melTotal = 0;
                for (var k = 0; k < classes.length; k++) {
                    var mc = classes[k];
                    var items = (mel[mc] && mel[mc][L]) ? mel[mc][L] : [];
                    melTotal += items.length;
                    if (!items.length) {
                        chips.push('<span style="color:#888;">Class ' + mc + ' (0)</span>');
                        continue;
                    }
                    var mrows = [];
                    for (var m = 0; m < items.length; m++) {
                        mrows.push('<div>&bull; <a target="_blank" href="' + escH(items[m].u) + '">' + escH(items[m].t) + '</a></div>');
                    }
                    chips.push(
                        '<details class="melispichip" style="display:inline-block;vertical-align:top;margin:0 2px;">'
                        + '<summary style="cursor:pointer;color:#0000cc;text-decoration:underline;white-space:nowrap;">Class ' + mc + ' (' + items.length + ')</summary>'
                        + '<div style="background:#e8f4ff;border:1px solid #6699cc;padding:4px 8px;margin-top:3px;min-width:340px;max-width:420px;">' + mrows.join('') + '</div>'
                        + '</details>');
                }
                if (melTotal) {
                    var mbox = document.createElement("div");
                    mbox.className = "melispilinks";
                    mbox.style.margin = "2px 0 8px 0";
                    mbox.innerHTML = '<small style="background-color: lightblue;" ><b>Melispi activities :</b> ' + chips.join(' ') + '</small>';
                    prev.insertAdjacentElement("afterend", mbox);
                    // Opening one class's chip closes the others (as in the test.html demo).
                    var chipEls = mbox.querySelectorAll("details.melispichip");
                    Array.prototype.forEach.call(chipEls, function (d) {
                        d.addEventListener("toggle", function () {
                            if (!d.open) return;
                            Array.prototype.forEach.call(chipEls, function (o) {
                                if (o !== d) o.open = false;
                            });
                        });
                    });
                }
            }
        });
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init);
    } else {
        init();
    }
})();
