<?php
/*
-v021c-260921 - "Show all weeks" checkbox is back, as a ~6-line INLINE helper
                (still no external JS file). The collapse itself stays the
                v021b zero-JS trick: PHP emits "collapsed" on non-current week
                tbodies and CSS hides their rows/boxes. The checkbox handler
                only adds/removes that same class on demand (untick = collapse
                all non-current again). ?expand=all still works AND pre-ticks
                the checkbox so the state matches the URL.

-v021b-260921 - Zero-JS collapse (simplification of v021a). PHP now emits the
                "collapsed" class directly on every non-current week tbody at
                render time (skipped when ?expand=all), so the pure-CSS rule
                tbody.week.collapsed tr.wk-row{display:none} does all the work:
                only the selected/current week is open, the others show just
                their heading line (pdf/presentations/Melispi boxes hidden by
                the existing CSS). week_collapse.js and the "Show all weeks"
                checkbox are REMOVED - ?expand=all is the show-everything
                option (same URL-param pattern as ?week=NN). Also dropped the
                now-dead click affordances (heading-row cursor/title, .wk-ind
                arrows). No other behavior change.

-v021a-260921 - Collapsible weeks. Every week now renders as its own
                <tbody class="week" data-week="NN"> (heading row = tr.wk-head,
                content rows = tr.wk-row); the selected week keeps the existing
                "cw" class + styling and also gets class "current". On page
                load (week_collapse.js) every week except the selected one is
                collapsed: only its heading line stays visible (content rows
                AND the JS-inserted pdf/presentations/Melispi boxes are hidden
                via CSS). Clicking a week's heading row toggles it (clicks on
                links/<details> chips don't toggle). A "Show all weeks"
                checkbox above the table expands everything; URL param
                ?expand=all pre-checks it. No-JS behaviour unchanged (all
                weeks fully open, as before). No changes to week-window,
                link-expansion or pdfpage_links.js logic.

-v020-260906 - Melispi activities section (3rd per-week link row). Reads
                ./melispi_links.csv (built from melispi_links.md; 385 links,
                cols: class,lesson,url,title,unit,unit_title,lesson_title) and
                exposes window.MELISPI = {CLASS:{LESSON:[{u,t},...]}} to
                pdfpage_links.js (now v003), which appends a light-blue
                "Melispi activities:" box under each regular week heading,
                right after the Teacher Presentations row. Per-class
                expandable chips ("Proposal B" from test.html): each class
                renders as a "• Class X (n)" chip; clicking it expands only
                that class's links; 0-link classes are greyed out (7 class/
                lesson combos have no links, e.g. D23/D26/D27, C11/C16,
                ST24/ST26). Lesson-00 (special/event) weeks are skipped like
                the PDF/presentation boxes. Missing CSV -> box simply not
                shown (no PHP warning).

-v017h-260826 - Fallback week window. When the SELECTED week has no
                "adjustWeekFinalNum_for_week NN" entry in john_start_kill_apps.sh
                (no numeric entries at all, entries only for other weeks, or the
                file missing/deleted), a synthetic window is generated for the
                selected week +/- the configured prev/next weeks: each week gets a
                heading line, regular weeks also one line with the 6 pack_Book.php
                links, so the fixed entries (pack_Book tabs + TEACHER PDF pages +
                Teacher Presentations via pdfpage_links.js) are ALWAYS shown.
                Lesson-00 (special/event) weeks get the heading only (same
                convention as the .sh). When the selected week's entry EXISTS,
                behavior is 100% unchanged. File read is now guarded with
                is_readable() so a deleted .sh causes no PHP warning.

-v017g-260823 - Teacher Presentations links. New config $_PRES_BASE_URL (default
                '../dim/dimBooksActivities/', the same base pack_Book.php uses for
                $dimBase) is emitted to pdfpage_links.js as window.PRES_BASE_URL.
                The JS (now v002) appends a "Teacher Presentations :" line with 6
                links (classes A,B,C,D,E,ST) under each regular week heading:
                <base>/TPE-dimXX-PEDIO/presentations/lessonYY.html where XX is the
                class and YY the week's lesson number (data-lesson, zero-padded).
                Lesson-00 weeks are skipped like the PDF box; links are always
                shown (404 until the lessonYY.html files are added).

-v017f-260818 - ShowPDF path is now variable and is now in BOOKS_TPE_PDF/ShowPDFPages/ (xampp_newBOOKS_v002ShowPDF_v07b-260813_.squashfs)
-v017e-260813 - autolink() now also linkifies the per-week pack_Book.php URLs
                that the general (scheme-required) regex misses when
                $PACKBOOK_prefix is RELATIVE (e.g. './'). The new branch is
                scoped to EXACTLY <$PACKBOOK_prefix>pack_Book.php? and only fires
                when the prefix has no scheme, so it cannot false-positive (after
                expand_bookurls() that literal only appears in bookurls6_php()'s
                generated URLs) and never double-wraps absolute prefixes.

-v017d-260813 - Wrap the current/selected week's rows (heading + its rows) in a
                bordered <tbody class="cw"> so it visually stands out from the
                previous/next weeks. Red cell borders + warm tint + red heading
                text. The "current week" = the week whose
                "adjustWeekFinalNum_for_week NN" heading matches $weekofyear
                (real ISO week, or forced via ?week=NN). No logic changes; the
                heading's data-lesson attribute (used by pdfpage_links.js) is kept.

-v017c-260813 - Moved configurations to start of script
-v017b-260813 - Per-week PDF-page links. Reads ./DimBooksPDFPages_...csv once and
               exposes it to the new pdfpage_links.js as window.PDFPAGES
               ($pdfData[CLASS][LESSON] = {file,pages}; CLASS auto-detected from the
               "Pliroforiki_X-Dimotikou" filename so the header/offsets row is skipped;
               LESSON zero-padded 01..30). The "pages" cell is stored verbatim, so it
               can be a single int ("11") today or a range ("11-20") later with no code
               change. pdfpage_links.js appends 6 ../ShowPDFPages/show_pdf.html links
               (classes A,B,C,D,E,ST) under each week heading for regular teaching
               weeks; special/Lesson-00 weeks are skipped. To hook the JS in,
               remove_unwanted_lines() now also emits data-lesson="NN" on the week <h3>.
               pdfpath uses the full LAN URL http://192.168.1.200/imgMJT/BOOKS_TPE_PDF/<file>.

-v016b-260805 - FIX: ?week=N window was wrong for N in {7,9,10}. get_string_between does a
                 literal substring search for "adjustWeekFinalNum_for_week (N-2)\n", and a
                 commented-out heading ("#adjustWeekFinalNum_for_week 8" at line 628 of the
                 .sh) was matched before the real one, pulling the slice start back to that
                 comment and producing a far-too-large table. Now strips bash full-line
                 comments from $string_modified BEFORE slicing via preg_replace('/^[ \t]*#.*$/m').

-v016a-260805 - Option A: always render 6 pack_Book.php class links (A,B,C,D,E,ST) right under
              the top "Week X -> Lesson NN" banner, using the already-computed $lessonofweek
              via the existing bookurls6_php() helper. Independent of whether
              john_start_kill_apps.sh contains ${BOOKURLS[@]} tokens. Auto-skipped on
              Lesson 00 (special/event) weeks. Moved $PACKBOOK_prefix definition above the
              banner so it is in scope.

-v015a-260621 - Handle "${BOOKURLS[@]}" token introduced in john_start_kill_apps.sh (#260621 update):
              expands it into 6 pack_Book.php URLs (classes A,B,C,D,E,ST) using the lesson number
              computed per displayed week (PHP port of bash bookurls6 / compute_lesson_for_week).
              Special/event weeks expand to nothing (matches bash). Adds a "Week X -> Lesson NN"
              banner at the top and a "(Lesson NN)" tag on each per-week heading.
              NEW: ?week=NN URL parameter forces a specific ISO week (1..53) for test/debug.


-v014e-250525  Show until the end or adjustWeekFinalNum_for_week 999 (after last week 23,24 )
-v014c-250115  $show_next_week_num
//v014d-230124 fixed: Show previous and nect entries
//v014c-230122 - Replacing $SERVER variable also
//v014b - added remove_unwanted_lines() ,$ignore_bash_script_unwanted_lines=true
//v014a - added <li> (maybe not usefull)
//v013b get adjest week
//v011 autolink !!


  USAGE
  -----
    csv2html.php                  preview current ISO week (+/- a few weeks);
                                  only the current week is expanded, the others
                                  are collapsed to their heading (v021c)
    csv2html.php?expand=all       start with ALL weeks expanded (pre-ticks the
                                  "Show all weeks" checkbox)
    csv2html.php?week=41          preview as if today were ISO week 41
    csv2html.php?week=41&expand=all  combine both
    csv2html.php?week=50          preview an event week (Lesson 00, no pack_Book tabs)
    csv2html.php?week=abc         invalid input is ignored -> falls back to current week
    (any invalid / missing / out-of-range value falls back to the real current week)
    csv2html.php                  when the .sh is missing or has NO entry for the
                                   selected week, a DEFAULT window is synthesized
                                   (per-week headings + pack_Book tabs; PDF-pages/
                                   presentations boxes come from pdfpage_links.js)


// Desc: Convert john_start_kill bash to html links (shows only near weeks)
*/

/*

ALSO try these : 
http://code.seebz.net/p/autolink-php/  ****
javascript http://code.seebz.net/p/autolink-js/

*/

# =====================================================================
# CONFIGURATION  --  edit these in one place
# =====================================================================
$file_to_parse = "./john_start_kill_apps.sh";          # bash launcher parsed by this script
#$file_to_parse = "./order_lesson.txt";                # (alternative input)

$show_prev_week_num = 2;          # how many previous weeks to show
$show_next_week_num = 4;          # how many upcoming weeks to show
$check_week = true;               # true = show only the current week window
$ignore_bash_script_unwanted_lines = true;  # ignore lines starting with # in the .sh

$delimiters = array('|_|');       # how each line is divided before str_getcsv

# pack_Book.php link prefix (top all-classes banner + expand_bookurls)
$PACKBOOK_prefix = './';          # alternative: 'http://192.168.1.200/img/jonstart/'

# --- PDF-page links (v017b) ---
$_pdfCsvPath   = './DimBooksPDFPages_chatgpt_all_lessons_realPages_v03updatedPDFStartPages.csv';
$_PDF_BASE_URL = '../BOOKS_TPE_PDF/'; //original before moved ShowPDF script
$_PDF_BASE_URL = '../';
$_ShowPDFPages ='../BOOKS_TPE_PDF/ShowPDFPages/';

# --- Teacher Presentations links (v017g) ---
# Base of the dimBooksActivities folder (same base pack_Book.php uses for $dimBase).
$_PRES_BASE_URL  = '../dim/dimBooksActivities/';

# --- Melispi activities links (v020) ---
# CSV built from melispi_links.md (class,lesson,url,title,unit,unit_title,lesson_title).
# Passed to pdfpage_links.js as window.MELISPI: { CLASS: { LESSON: [ {u,t}, ... ] } }
$_MELISPI_CSV = './melispi_links.csv';

$count = 0;   # legacy (unused)
$weekofyear = date("W");
// Allow forcing a specific ISO week via URL for test/debug: ?week=NN (1..53)
if (isset($_GET['week']) && ctype_digit($_GET['week']) && (int)$_GET['week'] >= 1 && (int)$_GET['week'] <= 53) {
    $weekofyear = sprintf("%02d", (int)$_GET['week']);
}

// -v021b: ?expand=all shows ALL weeks fully expanded (no per-week "collapsed"
// classes are emitted). Any other value = default (only the selected/current
// week expanded).
$_expandAll = (isset($_GET['expand']) && strtolower(trim($_GET['expand'])) === 'all');

// Read the .sh file ONCE up front (also needed to detect packBOOKS_ENABLED)
// -v017h: guarded with is_readable() so a deleted/missing .sh yields '' instead
// of a PHP warning; the fallback window below then takes over.
$string_of_file = '';
if (is_readable($file_to_parse)) {
    $_raw = file_get_contents($file_to_parse, true);
    if (is_string($_raw)) $string_of_file = $_raw;
}

// Detect packBOOKS_ENABLED master flag from the .sh (default 1 if missing)
$packBooksEnabled = 1;
if (preg_match('/^packBOOKS_ENABLED\s*=\s*(\d+)/m', $string_of_file, $_m)) {
    $packBooksEnabled = (int)$_m[1];
}

// $PACKBOOK_prefix is defined above in the CONFIGURATION block (used by the banner below + expand_bookurls).

// Compute dynamic special-week numbers (mirrors john_start_kill_apps.sh:126-143)
$currentYear           = (int)date("Y");
$easterWeekThisYear    = get_easter_week_php($currentYear);
$APOKRIES_PREWEEK_01   = sprintf("%02d", $easterWeekThisYear - 8);
$APOKRIES_PREWEEK_02   = sprintf("%02d", $easterWeekThisYear - 7);
$EASTER_PREWEEK_01     = $easterWeekThisYear - 2;
$EASTER_PREWEEK_02     = $easterWeekThisYear - 1;

// Lesson number for the currently displayed week (shown in the top banner)
$lessonofweek = compute_lesson_for_week_php($weekofyear, $APOKRIES_PREWEEK_01, $APOKRIES_PREWEEK_02, $EASTER_PREWEEK_01, $EASTER_PREWEEK_02);
$_lessonBannerSuffix = ($lessonofweek === "00") ? " (special/event week - no pack_Book tabs)" : "";
echo "<h2>Week $weekofyear &rarr; Lesson $lessonofweek$_lessonBannerSuffix</h2>";

// -v016-260805 Option A: always render 6 pack_Book.php class links for the displayed week,
// independent of whether john_start_kill_apps.sh contains ${BOOKURLS[@]} tokens.
// Skipped automatically on Lesson 00 (special/event) weeks. Reuses bookurls6_php()
// (URLs in A,B,C,D,E,ST order, with &timer2&probeserver suffix matching the launcher).
if ($lessonofweek !== "00") {
    $_cls  = array('A','B','C','D','E','ST');
    $_urls = bookurls6_php($lessonofweek, $PACKBOOK_prefix);
    echo '<h3 style="margin:2px 0 8px 0;">Open this week (Lesson '.$lessonofweek.') for all classes: ';
    foreach ($_urls as $_k => $_u) {
        if ($_k > 0) echo ' | ';
        echo '<a target="_blank" href="'.htmlspecialchars($_u, ENT_QUOTES).'">Class '.$_cls[$_k].'</a>';
    }
    echo '</h3>';
}

//REPLACEMENTS
//$SERVER_current="http://192.168.1.200/"; // doesn't work check function replaceDelimiters()


function remove_unwanted_lines($str){
    //Pass1: Remove lines starting with #
    //pass2: Keep lines with adjust and http
    // if ($ignore_bash_script_unwanted_liness) 
    //if($data[$c][0]"#")echo "HELLO";//NOT WORKING
    //$str=$data[$c]
    //echo("<h3>$str</h3><hr size=10>");


    if(strpos($str,"adjustWeekFinalNum")!==false){
            //return "HELLO"    ;
            // Tag heading with "(Lesson NN)" so per-week BOOKURLS correctness is
            // verifiable at a glance. Done here (not in expand_bookurls) so the
            // raw heading line stays intact for get_string_between's lookup.
            $_lessonAttr = '';
            if (preg_match('/adjustWeekFinalNum_for_week\s+(\S+)/', $str, $_wm)) {
                $_wStr = ltrim($_wm[1], '0');
                if ($_wStr !== '' && ctype_digit($_wStr) && (int)$_wStr >= 1 && (int)$_wStr <= 53) {
                    global $APOKRIES_PREWEEK_01, $APOKRIES_PREWEEK_02, $EASTER_PREWEEK_01, $EASTER_PREWEEK_02;
                    $_lesson = compute_lesson_for_week_php((int)$_wStr, $APOKRIES_PREWEEK_01, $APOKRIES_PREWEEK_02, $EASTER_PREWEEK_01, $EASTER_PREWEEK_02);
                    $_label = ($_lesson === "00") ? "(Lesson 00 - special/event week)" : "(Lesson $_lesson)";
                    $str = $str . " " . $_label;
                    $_lessonAttr = ' data-lesson="'.htmlspecialchars($_lesson, ENT_QUOTES).'"';
                }
            }
            return '<h3'.$_lessonAttr.'>'.$str.'</h3>';

    } 
    if(strpos($str,"#")===0 )return "IGNORED";

        if(strpos($str,"http")!==false){
            //return "HELLO"    ;
            return $str;

        }

    // -v017h: also keep relative pack_Book.php URL lines (they have no scheme,
    // so the "http" test above misses them). Such lines only come from
    // bookurls6_php() via expand_bookurls() or the synthesized fallback window.
    if(strpos($str,"pack_Book.php")!==false){
        return $str;
    }

    if(strpos($str,"#")!=0 || strpos($str,"#")===false ){
        if(strpos($str,"http")!==false){
            //return "HELLO"    ;
            return $str;

        }
           
    }// END of if(strpos($str,"#")==0){
    return "IGNORED";
    return $str;       

}


function autolink($str, $attributes=array()) {
    $attrs = '';
    foreach ($attributes as $attribute => $value) {
        $attrs .= " {$attribute}=\"{$value}\"";
    }

    $str = ' ' . $str;

    $replacement_string='<li>'.'$1<a target=_blank href="$2"'.$attrs.'>$2</a></li>';
    //$replacement_string=$replacement_string."\n";

 // ORIG ok working jon 210525
     $str = preg_replace(
        '`([^"=\'>])((http|https|ftp)://[^\s<]+[^\s<\.)])`i',
         $replacement_string,
         $str
     );

    // -v017e-260813 Also linkify the per-week pack_Book.php URLs that the
    // general (scheme-required) regex above misses when $PACKBOOK_prefix is
    // RELATIVE (e.g. './'). Scoped to EXACTLY <$PACKBOOK_prefix>pack_Book.php?
    // so it cannot false-positive: after expand_bookurls() the only place that
    // literal appears is the URLs bookurls6_php() generated. Skipped when the
    // prefix already has a scheme (http://...) -- then the regex above already
    // linked them (avoids double-wrap).
    global $PACKBOOK_prefix;
    if (!preg_match('#^[a-z]+://#i', $PACKBOOK_prefix)) {
        $_pb = preg_quote($PACKBOOK_prefix, '`');
        $str = preg_replace(
            '`([^"=\'>])(' . $_pb . 'pack_Book\.php\?[^\s<]+[^\s<\.)])`i',
            '<li>$1<a target=_blank href="$2">$2</a></li>',
            $str
        );
    }

    $str=$str."\n";
    $str = substr($str, 1);
    
    return $str;
}



//$string_of_file = file_get_contents('./order_lesson.txt', true);

// $string_of_file is read up front (needed for packBOOKS_ENABLED detection + lesson banner)


$string_modified=replaceDelimiters($string_of_file); //replaces delimites and bash variables

// Expand "${BOOKURLS[@]}" tokens per-week BEFORE get_string_between slices the window.
// Mirrors bash bookurls6(): 6 pack_Book.php URLs for regular weeks, nothing for special weeks.
// (Per-week "(Lesson NN)" heading tags are added later, inside remove_unwanted_lines().)
$string_modified = expand_bookurls($string_modified, $APOKRIES_PREWEEK_01, $APOKRIES_PREWEEK_02, $EASTER_PREWEEK_01, $EASTER_PREWEEK_02, $PACKBOOK_prefix, $packBooksEnabled);
//echo "$string_modified";
//$string_modified=makeHref($string_modified); //problematic

//if ($ignore_bash_script_unwanted_lines) $string_modified=remove_unwanted_lines($string_modified);

//$string_modified=autolink($string_modified); //Seems to work !!!!!!!!


// v016.1-260805 FIX: strip bash full-line comments (#...) BEFORE the week-window slice.
// Otherwise a commented-out heading earlier in the file (e.g. "#adjustWeekFinalNum_for_week 8"
// at line 628) is matched by get_string_between's literal substring search and pulls the slice
// start back to that comment, producing a far-too-large window (e.g. ?week=10 showed weeks
// 38..13 instead of 9..13). Strips only lines whose first non-whitespace char is '#' (matches
// the per-row filter in remove_unwanted_lines()); inline comments are left untouched.
$string_modified = preg_replace('/^[ \t]*#.*$/m', '', $string_modified);


// -v017h-260826 Fallback trigger: does the SELECTED week have its own
// "adjustWeekFinalNum_for_week NN" heading in the (comment-stripped) .sh content?
// Accepts zero-padded and bare week numbers. Cannot match the function definition
// ("adjustWeekFinalNum_for_week() {" - no number), the "$VAR" dynamic headings
// (start with '$') or the "999" sentinel; commented-out headings were already
// stripped above (v016b). Entry exists -> exactly the pre-v017h code path runs.
$_selectedWeekHasEntry = preg_match(
    '/^adjustWeekFinalNum_for_week[ \t]+0*' . (int)$weekofyear . '[ \t]*\r?$/m',
    $string_modified
) === 1;

if (!$_selectedWeekHasEntry) {
    // -v017h: file missing / no numeric entries / selected week not covered ->
    // synthesize the whole week window (same line format the .sh uses) so the
    // fixed default entries (per-week pack_Book tabs + the TEACHER PDF pages and
    // Teacher Presentations boxes pdfpage_links.js appends) are always shown for
    // the selected week and the weeks before/after it.
    echo "<!-- csv2html: fallback for week $weekofyear - no 'adjustWeekFinalNum_for_week $weekofyear' entry in $file_to_parse -->\n";
    echo '<p style="margin:2px 0 8px 0;"><small>(no week ' . htmlspecialchars($weekofyear, ENT_QUOTES)
       . ' entry in john_start_kill_apps.sh - showing default entries: pack_Book tabs + TEACHER PDF pages + Teacher Presentations)</small></p>' . "\n";
    $string_modified = synthesize_week_window(
        $weekofyear,
        $show_prev_week_num,
        $show_next_week_num,
        $APOKRIES_PREWEEK_01, $APOKRIES_PREWEEK_02,
        $EASTER_PREWEEK_01,  $EASTER_PREWEEK_02,
        $PACKBOOK_prefix
    );
}

if ($_selectedWeekHasEntry && $check_week){
    $substring = "adjustWeekFinalNum_for_week";
    //$result = get_string_between($string_modified , $substring." ".($weekofyear-1), $substring." ".($weekofyear+2) //Orig 
	///$result = get_string_between($string_modified , $substring." ".($weekofyear-1)."\n", $substring." ".($weekofyear+2)."\n" ); // 230124 Show previous and next entries
    $result = get_string_between($string_modified , $substring." ".($weekofyear-$show_prev_week_num)."\n", $substring." ".($weekofyear+$show_next_week_num)."\n" ); // 230124 

    //echo "<h1> <hr>".$substring." ".($weekofyear-1)."<hr>".$substring." ".($weekofyear+2)."<hr>".$result."</h1>" ;
    $string_modified=$result;
}

// -v017-260805 Diagnostic: emit an HTML comment when the week window came back empty,
// so future blanks (e.g. a renamed/missing heading) are explainable via View Source.
// (-v017h: only on the normal path - the fallback path never produces an empty window.)
if ($_selectedWeekHasEntry && $check_week && $string_modified === '') {
    echo "<!-- csv2html: empty window for week $weekofyear "
       . "(looked for 'adjustWeekFinalNum_for_week " . ($weekofyear-$show_prev_week_num)
       . "' .. 'adjustWeekFinalNum_for_week " . ($weekofyear+$show_next_week_num) . "') -->";
}



//$string_modified=formatUrlsInText($string_modified); //Seems to work ok issues with some splitting


//str_replace($search, $replace, $subject);

//$string_modified=str_replace('[', '<BR>', $string_modified); echo("ZZZZZZZZZ".$string_modified); //DEBUG
//echo "<hr size =100>";



$AllData = str_getcsv($string_modified, "\n"); //parse the rows
//print_r($AllData);


// -v021c: "Show all weeks" checkbox. The collapse itself is still the v021b
// zero-JS trick (PHP emits the collapsed class, CSS hides rows); this one tiny
// inline helper just adds/removes that same class when the box is ticked.
// ?expand=all pre-ticks it so the checkbox matches the URL-provided state.
$_cbChecked = $_expandAll ? ' checked' : '';
echo '<div style="margin:2px 0 8px 0;">'
   . '<label style="cursor:pointer;">'
   . '<input type="checkbox" id="expandAllWeeks" onchange="expandAllWeeks(this)"'.$_cbChecked.'> '
   . '<b>Show all weeks</b> <small>(tick to expand every week, untick to show only the current week)</small>'
   . '</label></div>' . "\n";
echo '<script>' . "\n"
   . 'function expandAllWeeks(cb){' . "\n"
   . '  document.querySelectorAll("tbody.week").forEach(function(t){' . "\n"
   . '    t.classList.toggle("collapsed", !cb.checked && !t.classList.contains("current"));' . "\n"
   . '  });' . "\n"
   . '}' . "\n"
   . '</script>' . "\n";

echo '<ol type="1"> <table border="1"> ';

// -v017d-260813 Style the current/selected week's block (tbody.cw) with a
// warm tint + red cell borders so the whole group reads as one bordered zone.
// -v021b Collapsible weeks, pure CSS: PHP puts class "collapsed" on every
// non-current tbody.week, these rules hide its content rows AND the boxes
// pdfpage_links.js inserts inside the heading cell.
echo '<style>'
   . 'tbody.cw td{background:lightgrey;border:2px solid #d00;}'
   . 'tbody.cw h3{color:#b00;margin:0;}'
   . 'tbody.week.collapsed tr.wk-row{display:none;}'
   . 'tbody.week.collapsed .pdfpagelinks,'
   . 'tbody.week.collapsed .presentationlinks,'
   . 'tbody.week.collapsed .melispilinks{display:none;}'
   . '</style>';

//exit ();
//=============== FORMAT to tables ===========================
$row = 2;





//foreach($AllData as &$Row) $Row = str_getcsv($Row, "[") {

// -v021a Each week renders as its own <tbody class="week" data-week="NN">: a new
// tbody opens at every "adjustWeekFinalNum_for_week NN" heading row and holds
// that week's heading (tr.wk-head) + content rows (tr.wk-row). The selected/
// current week's tbody keeps the v017d "cw" class (tint + red borders) and also
// gets "current". -v021b: every OTHER week gets "collapsed" straight from PHP
// (skipped when ?expand=all) and the CSS in the <style> block above does the
// actual hiding - no JavaScript involved.
$_dispWeekInt = (int)$weekofyear;
$_curWeek     = null;   // week number of the tbody currently being rendered
$_tbodyOpen   = false;

foreach($AllData as $data1) {
         //echo "<h1>$data1</h1>";

        // Detect week-heading rows on the RAW text (before remove_unwanted_lines
        // turns them into <h3>); each one starts a new per-week tbody.
        $_isHead = preg_match('/adjustWeekFinalNum_for_week\s+(\d+)/', $data1, $_hm) === 1;
        if ($_isHead) {
            $_curWeek = (int)$_hm[1];
        }


        if ($ignore_bash_script_unwanted_lines) $data1=remove_unwanted_lines($data1);

        if($data1=="IGNORED") continue;
        $data1=autolink($data1); //Seems to work !!!!!!!!
        $data = str_getcsv($data1, "[")   ;

        //print_r($data);


        $num = count($data);
        //echo "<h1>num=$num ________  data1=$data1</h1>";


        if ($row == 1) {
            echo '<thead><tr>';
        }else{
            // -v021a Open a fresh per-week tbody at each week heading (rows
            // before the first heading get a plain tbody of their own).
            // -v021b non-current weeks start collapsed (?expand=all -> don't).
            if ($_isHead || !$_tbodyOpen) {
                if ($_tbodyOpen) echo '</tbody>';
                $_isCur = ($_curWeek === $_dispWeekInt);
                $_state = $_isCur ? ' cw current' : ($_expandAll ? '' : ' collapsed');
                $_wAttr = ($_isHead && $_curWeek !== null) ? ' data-week="'.$_curWeek.'"' : '';
                echo '<tbody class="week'.$_state.'"'.$_wAttr.'>';
                $_tbodyOpen = true;
            }
            if ($_isHead) {
                echo '<tr class="wk-head">';
            }else{
                echo '<tr class="wk-row">';
            }
        }

        for ($c=0; $c < $num; $c++) {
           // if ($ignore_bash_script_unwanted_liness) if($data[$c][0]"#")echo "HELLO";//NOT WORKING
            //if ($ignore_bash_script_unwanted_lines) $data[$c]=remove_unwanted_lines($data[$c]);
            //if($data[$c]=="IGNORED") continue;
            //echo $data[$c] . "<br />\n";
            if(empty($data[$c])) {
               $value = "&nbsp;";
            }else{
               $value = $data[$c];
            }
            if ($row == 1) {
                echo '<th>'.$value.'</th>';
            }else{
                echo '<td>'.$value.'</td>'."\n";
            }
        }

        if ($row == 1) {
            echo '</tr></thead><tbody>';
            $_tbodyOpen = true;
        }else{
            echo '</tr>';
        }
        $row++;

}

// -v021a Close the last per-week tbody (the table itself stays open, as before).
if ($_tbodyOpen) echo '</tbody>' . "\n";
 //parse the items in rows

// $_pdfCsvPath and $_PDF_BASE_URL are defined above in the CONFIGURATION block.
$pdfData = array();
if (is_file($_pdfCsvPath) && ($_fh = fopen($_pdfCsvPath, 'r')) !== false) {
    while (($_row = fgetcsv($_fh)) !== false) {
        if (!isset($_row[0], $_row[1])) continue;
        $_file = trim($_row[0]);
        // Detect the class from the textbook filename (A,B,C,D,E,ST).
        if (!preg_match('/Pliroforiki_([A-Z]{1,2})-Dimotikou/i', $_file, $_cm)) continue;
        $_cls = strtoupper($_cm[1]);
        if (!in_array($_cls, array('A','B','C','D','E','ST'), true)) continue;
        $_lesRaw = trim($_row[1]);
        if ($_lesRaw === '' || !ctype_digit($_lesRaw)) continue;
        $_lesInt = (int)$_lesRaw;
        if ($_lesInt < 1 || $_lesInt > 30) continue; // skip lesson 0 (header/offsets row)
        $_les   = str_pad((string)$_lesInt, 2, '0', STR_PAD_LEFT);
        $_pages = isset($_row[2]) ? trim($_row[2]) : '';
        if ($_pages === '') continue; // pass through verbatim (single int OR "11-20")
        $pdfData[$_cls][$_les] = array('file' => $_file, 'pages' => $_pages);
    }
    fclose($_fh);
}

// -v020-260906 Melispi activities: read ./melispi_links.csv once and expose it
// to pdfpage_links.js as window.MELISPI ($melispiData[CLASS][LESSON] = list of
// {u: url, t: title}). Header row is skipped by name; rows are keyed like the
// PDF CSV (CLASS in A,B,C,D,E,ST; LESSON zero-padded "01".."30"). A missing or
// empty CSV just leaves $melispiData empty -> the JS shows no Melispi box.
$melispiData = array();
if (is_file($_MELISPI_CSV) && ($_fh = fopen($_MELISPI_CSV, 'r')) !== false) {
    while (($_row = fgetcsv($_fh)) !== false) {
        if (!isset($_row[0]) || trim($_row[0]) === 'class') continue; // header row
        if (!isset($_row[1], $_row[2], $_row[3])) continue;
        $_cls = strtoupper(trim($_row[0]));
        if (!in_array($_cls, array('A','B','C','D','E','ST'), true)) continue;
        $_lesRaw = trim($_row[1]);
        if ($_lesRaw === '' || !ctype_digit($_lesRaw)) continue;
        $_lesInt = (int)$_lesRaw;
        if ($_lesInt < 1 || $_lesInt > 30) continue;
        $_les   = str_pad((string)$_lesInt, 2, '0', STR_PAD_LEFT);
        $_murl  = trim($_row[2]);
        $_mtitle = trim($_row[3]);
        if ($_murl === '' || $_mtitle === '') continue;
        $melispiData[$_cls][$_les][] = array('u' => $_murl, 't' => $_mtitle);
    }
    fclose($_fh);
}
echo '<script>' . "\n";
echo 'window.PDFPAGES = ' . json_encode($pdfData) . ';' . "\n";
echo 'window.MELISPI  = ' . json_encode($melispiData) . ';' . "\n";
echo 'window.PDF_BASE_URL = ' . json_encode($_PDF_BASE_URL) . ';' . "\n";
//echo 'window.SHOWPDF_LINK = "../ShowPDFPages/show_pdf.html";' . "\n";
echo 'window.SHOWPDF_LINK = "'.$_ShowPDFPages.'show_pdf.html";' . "\n";
echo 'window.PRES_BASE_URL = ' . json_encode($_PRES_BASE_URL) . ';' . "\n";
echo '</script>' . "\n";
echo '<script src="pdfpage_links.js"></script>' . "\n";







/**
 * Will replace a number of CSV delimiters to one specific character 
 * AND replaces bash variables
 * @param $file     CSV file
 */
//function replaceDelimiters($str,$prefixserverurl='http://192.168.1.200/')
function replaceDelimiters($str,$prefixserverurl='http://192.168.1.200/')
{
    // Delimiters to be replaced: pipe, comma, semicolon, caret, tabs
   //$delimiters = array('|', ';', '^', "\t");
 
    //$delimiters = array('|_|');
    global $delimiters;
    $delimiter = '[';
    //str_replace($search, $replace, $subject);
    //$str = file_get_contents($file);
    $str = str_replace($delimiters, $delimiter, $str);


    $str = str_replace('""$SWFlocal"', $prefixserverurl."swf/", $str);
    //$str = str_replace(' "$SWFlocal"', " http://192.168.1.200/swf/", $str);
    $str = str_replace(' "$SWFlocal"', " http://192.168.1.200/swf/", $str);
    $str = str_replace('"$SWFpath"', $prefixserverurl."swf/", $str);
    $str = str_replace('$SWFgiortes"', $prefixserverurl."swf/swf_giortes/", $str);
    $str = str_replace('$RAMKIDpathprefix"', $prefixserverurl."ramkid/", $str);
    $str = str_replace('lightbot_iron_browser "', $prefixserverurl."gamesedu/lightbot_haan/index.html?map=", $str);
    $str = str_replace('""$GAMESEDU"', $prefixserverurl."gamesedu/", $str);
    $str = str_replace('"$GAMESEDU"', $prefixserverurl."gamesedu/", $str);
    $str = str_replace('"$SERVER"', $prefixserverurl, $str);

    $str = str_replace('ironstart ', "", $str);
    $str = str_replace('ironstartincognito ', "", $str);


    $str = str_replace('\&', "&", $str);
    $str = str_replace('"', " ", $str);
    

    $str = str_replace('html"', "html", $str); // IMPORTANT !!!



    



    $str = str_replace('aaaaaaa', "", $str);
    $str = str_replace('aaaaaaa', "", $str);
    $str = str_replace('aaaaaaa', "", $str);


    $delimiters = array('|_|');
    $delimiter = '[';



    //file_put_contents($file, $str);
    return $str;
}



/*


function get_string_between($string, $start, $end){
    global $weekofyear,$check_week;



    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}

*/


/*
//shows all the rest of the entries if not found END week
function get_string_between($string, $start, $end){
    global $weekofyear, $check_week;

    $string = ' ' . $string;

    $ini = strpos($string, $start);
    if ($ini === false) {
        // Start not found
        return '';
    }
    $ini += strlen($start);

    // Attempt to find the $end
    $pos_end = strpos($string, $end, $ini);

    if ($pos_end === false) {
        // $end not found; extract until the end of the string
        return trim(substr($string, $ini));
    } else {
        // Extract between start and end
        return trim(substr($string, $ini, $pos_end - $ini));
    }
}
*/

function get_string_between($string, $start, $end){
    global $weekofyear, $check_week;

    $string = ' ' . $string;

    $ini = strpos($string, $start);
    if ($ini === false) {
        // -v018-260805 Start marker missing (e.g. ?week=38 or ?week=39 fall before the
        // first numeric heading "adjustWeekFinalNum_for_week 38" in john_start_kill_apps.sh).
        // Fall back to the FIRST "adjustWeekFinalNum_for_week <digit>" heading in the
        // file so we display only real week sections -- NOT the script preamble
        // (SERVER=, CODEorg=, function defs) and NOT the $VAR special-week blocks
        // (adjustWeekFinalNum_for_week $EASTER_PREWEEK_01). The \s+\d rule rejects
        // the "adjustWeekFinalNum_for_week() {" (function def, followed by "(")
        // and "adjustWeekFinalNum_for_week $VAR" (followed by "$") forms, which are
        // for other purposes and must not be shown.
        if (preg_match('/^adjustWeekFinalNum_for_week\s+\d[^\n]*\n/m', $string, $_m, PREG_OFFSET_CAPTURE)) {
            $ini = $_m[0][1];   // start OF the heading line (include the heading, see v019 note below)
        } else {
            return '';   // no numeric heading at all -> empty + diagnostic comment fires
        }
    }
    // -v019-260805 $ini intentionally points AT the start heading (not past it), in
    // BOTH the normal and fallback paths, so the first week's
    // "adjustWeekFinalNum_for_week ##" line is included in the slice and renders as
    // <h3> (with its "(Lesson NN)" tag) just like every later week heading inside the
    // window. Previously the start marker was consumed via $ini += strlen($start),
    // hiding the first heading (e.g. week 38's content showed with no label).

    // Attempt to find the $end
    $pos_end = strpos($string, $end, $ini);

    // If $end is not found, default to 'adjustWeekFinalNum_for_week 999'
    if ($pos_end === false) {
        $end = 'adjustWeekFinalNum_for_week 999';
        $pos_end = strpos($string, $end, $ini);
        if ($pos_end === false) {
            // Still not found; return empty
            return '';
        }
    }

    $len = $pos_end - $ini;

    return substr($string, $ini, $len);
}


# =====================================================================
# pack_Book helpers - PHP port of john_start_kill_apps.sh:80-204
# (get_easter_week, get_school_start_week, compute_lesson_for_week, bookurls6)
# Used to expand "${BOOKURLS[@]}" tokens for the preview.
# =====================================================================

# PHP port of bash get_easter_week() - static case table (john_start_kill_apps.sh:80-96)
function get_easter_week_php($year) {
    switch ((int)$year) {
        case 2029:
        case 2032: return 14;
        case 2026:
        case 2037: return 15;
        case 2025:
        case 2028:
        case 2031:
        case 2033:
        case 2034:
        case 2036:
        case 2039: return 16;
        case 2030:
        case 2038: return 17;
        case 2024:
        case 2027:
        case 2035: return 18;
        default:   return 16;
    }
}

# PHP port of bash get_school_start_week() (john_start_kill_apps.sh:158-162)
# ISO week (1..53) that contains Sep 11 of the current school year.
# Jan-Jun belong to last Sep's school year.
function get_school_start_week_php() {
    $mon = (int)date("n");   // 1..12, no leading zero
    $yr  = (int)date("Y");
    if ($mon < 7) $yr = $yr - 1;
    return (int)date("W", strtotime("$yr-09-11"));
}

# PHP port of bash compute_lesson_for_week() (john_start_kill_apps.sh:166-188)
# Returns "01".."30" for regular teaching weeks, "00" for the intro week,
# special-event weeks, or weeks outside the school year. Special-week set:
# startwk + (43,44,49,50,51,52,1) + 4 dynamic Easter/Apokries weeks.
function compute_lesson_for_week_php($current, $apo1, $apo2, $easter1, $easter2) {
    $current = (int)ltrim((string)$current, '0');
    $startwk = (int)get_school_start_week_php();

    $special = array(
        $startwk,
        43, 44, 49, 50, 51, 52, 1,
        (int)ltrim((string)$apo1, '0'),
        (int)ltrim((string)$apo2, '0'),
        (int)ltrim((string)$easter1, '0'),
        (int)ltrim((string)$easter2, '0'),
    );
    $special = array_map('intval', $special);

    $lesson = 0;
    $w = $startwk;
    for ($i = 0; $i < 50; $i++) {
        $isspec = in_array($w, $special, true);
        if (!$isspec) {
            $lesson++;
            if ($lesson > 30) $lesson = 30;
        }
        if ($w === $current) {
            if ($isspec) return "00";
            return str_pad((string)$lesson, 2, "0", STR_PAD_LEFT);
        }
        $w++;
        if ($w > 52) $w = 1;
    }
    return "00";
}

# PHP port of bash bookurls6() (john_start_kill_apps.sh:195-204)
# Returns the 6 pack_Book.php URLs (classes A,B,C,D,E,ST) for the given lesson,
# or an empty array when lesson is 0 (matches bash leaving BOOKURLS empty).
function bookurls6_php($lesson, $packbookPrefix) {
    $lessonInt = (int)ltrim((string)$lesson, '0');
    if ($lessonInt === 0) return array();
    $lessonStr = str_pad((string)$lessonInt, 2, "0", STR_PAD_LEFT);
    $urls = array();
    foreach (array('A','B','C','D','E','ST') as $c) {
        #$urls[] = $packbookPrefix . "pack_Book.php?c=$c&l=$lessonStr&timer2&probeserver";
        $urls[] = $packbookPrefix . "pack_Book.php?c=$c&l=$lessonStr&probeserver";
    }
    return $urls;
}

# Walks the (already delimiter-replaced) bash content line-by-line, tracking the
# "current week" via "adjustWeekFinalNum_for_week N" headings, and replaces every
# "${BOOKURLS[@]}" token with the 6 pack_Book URLs (joined by spaces) for that
# week's lesson - or with empty for special weeks / when packBOOKS_ENABLED != 1,
# exactly mirroring bash bookurls6().
#
# NOTE: this function intentionally does NOT modify the heading lines themselves
# (get_string_between later looks them up by their literal text
# "adjustWeekFinalNum_for_week N\n"). The "(Lesson NN)" tag is added at display
# time inside remove_unwanted_lines() instead.
function expand_bookurls($content, $apo1, $apo2, $easter1, $easter2, $packbookPrefix, $packBooksEnabled) {
    # Pre-compute the lesson for every ISO week 1..53 once (cheap; avoids
    # recomputing for every block).
    $lessonsByWeek = array();
    for ($w = 1; $w <= 53; $w++) {
        $lessonsByWeek[$w] = compute_lesson_for_week_php($w, $apo1, $apo2, $easter1, $easter2);
    }

    $lines = explode("\n", $content);
    $out = array();
    $currentWeek  = null;   // null while outside any recognised week block
    $currentLesson = null;

    foreach ($lines as $line) {
        # Detect week-heading lines like "adjustWeekFinalNum_for_week 41".
        # Comments ("# adjustWeekFinalNum...") and the function definition
        # ("adjustWeekFinalNum_for_week() {") do NOT match thanks to ^\s* + the
        # required whitespace + week-number capture. The heading is LEFT UNCHANGED
        # so get_string_between can still find it.
        if (preg_match('/^\s*adjustWeekFinalNum_for_week\s+(\S+)/', $line, $_m)) {
            $wStr = ltrim($_m[1], '0');
            if ($wStr !== '' && ctype_digit($wStr) && (int)$wStr >= 1 && (int)$wStr <= 53) {
                $currentWeek   = (int)$wStr;
                $currentLesson = $lessonsByWeek[$currentWeek];
            } else {
                # Sentinel ("999", "000...NOT USED") - leave block context untouched.
                $currentWeek   = null;
                $currentLesson = null;
            }
            $out[] = $line;
            continue;
        }

        # Replace any "${BOOKURLS[@]}" token on this line.
        if (strpos($line, '${BOOKURLS[@]}') !== false) {
            $urls = '';
            if ($packBooksEnabled === 1 && $currentLesson !== null && $currentLesson !== "00") {
                $urls = implode(' ', bookurls6_php($currentLesson, $packbookPrefix));
            }
            $line = str_replace('${BOOKURLS[@]}', $urls, $line);
        }
        $out[] = $line;
    }
    return implode("\n", $out);
}

# =====================================================================
# v017h-260826 Fallback week-window synthesizer
# =====================================================================
# Builds a synthetic "week window" (the same line format the .sh uses) when the
# selected week has no "adjustWeekFinalNum_for_week NN" entry in
# john_start_kill_apps.sh (or the file itself is missing). For every week in the
# selected +/- window (wrapped 1..52 like the bash loops) it emits the heading
# line "adjustWeekFinalNum_for_week NN"; regular teaching weeks also get one
# line with the 6 pack_Book.php URLs (bookurls6_php). Lesson-00 (special/event)
# weeks get the heading only, matching the .sh convention. Downstream nothing
# changes: remove_unwanted_lines() turns the headings into <h3 data-lesson="NN">
# (+ "(Lesson NN)" tag), the tbody.cw highlight marks the selected week,
# autolink() linkifies the pack_Book URLs and pdfpage_links.js appends the
# TEACHER PDF pages + Teacher Presentations boxes under each non-00 heading.
function synthesize_week_window($selWeek, $prevWeeks, $nextWeeks, $apo1, $apo2, $easter1, $easter2, $packbookPrefix) {
    $selWeekInt = (int)ltrim((string)$selWeek, '0');
    $total = $prevWeeks + $nextWeeks + 1;

    $w = $selWeekInt - $prevWeeks;
    if ($w < 1) $w += 52;   // wrap like the bash loops (weeks live in 1..52)

    $lines = array();
    for ($i = 0; $i < $total; $i++) {
        $lines[] = 'adjustWeekFinalNum_for_week ' . sprintf('%02d', $w);
        $lesson = compute_lesson_for_week_php($w, $apo1, $apo2, $easter1, $easter2);
        if ($lesson !== '00') {
            $lines[] = implode(' ', bookurls6_php($lesson, $packbookPrefix));
        }
        $w++;
        if ($w > 52) $w = 1;
    }
    return implode("\n", $lines) . "\n";
}

?>
