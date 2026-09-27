/*
  pdfpage_links.js  -v002-260823

  Changes :	
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
  that is a regular teaching week (NN != "00"), appends 6 links (classes
  A,B,C,D,E,ST) that open ../ShowPDFPages/show_pdf.html showing that lesson's
  PDF pages for each class's textbook.

  Data source:
    window.PDFPAGES     -> { CLASS: { "LESSON": { file, pages }, ... }, ... }
                           (CLASS in A,B,C,D,E,ST; LESSON zero-padded "01".."30")
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
            }
        });
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init);
    } else {
        init();
    }
})();
