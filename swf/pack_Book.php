<?php
/*
 * pack_Book.php — Dynamic lesson activity pack for all elementary school classes
 *
 *
 *VERSION HISTORY:
 *v260923c - SRWare Iron 61 (Chromium 61) fix: :has() is not supported there,
 *             so the wrapper <li> now gets class="subwrap" in the PHP
 *             (groupTaggedItems + melispi group) and the bare number is
 *             hidden with li.subwrap::before{display:none}. Same rendering
 *             as v260923b on modern browsers, now also on old ones.
 *v260923b - Sub-list labels show the parent number too: a., b., c. become
 *             9a., 9b., 9c. ... Pure CSS - counter(list) from the page <ol>
 *             is inherited by the nested sub-list <li> and prepended in the
 *             ::before content. The wrapper <li>'s bare number ("9" on its
 *             own line) is hidden via li:has(> ul.*-sub)::before{display:none}
 *             (:has() - old browsers keep showing it). No JS change needed;
 *             "19a"->"19" filter still works, DOM unchanged.
 *v260923a - Sub-numbering for ALL sections: activities sharing the same tag
 *             ([quiz], [0quiz_penalty], [matching-lines], [MATCH], ...) are
 *             ONE numbered entry with a., b., c. children (the [unique] /
 *             Μελίσπη idiom, class="tag-sub"). A tag with a single activity
 *             stays a plain number - no subcategory. NOTE: 0quiz_basket,
 *             0quiz_penalty, ... are DIFFERENT tags, each groups only its own
 *             duplicates. div3/div4 are now split by tag-group, not per item.
 *             "19a" is always the same as "19" (see pack_js_activities_filter.js).
 *v260922a - "ignore activity filters" checkbox at the end of the v260921
 *             selector window. Checking it reloads the page with
 *             &no_act_filter=true -> pack_js_activities_filter.js skips the
 *             CSV/shown filtering entirely (all activities shown, no 30s
 *             polling); unchecking reloads without the parameter. Checkbox
 *             state is rendered from $_GET, nothing stored locally. Other
 *             probeserver features are not affected.
 *v260921 - Upper-left header block (class name, "Μάθημα NN — title", Reload
 *             link, class tabs A-ST, lesson numbers 01-30) is wrapped in a
 *             small fixed-height scrollable window (same overflow:auto idiom
 *             as the activities list below it). Only the text up to "🔄
 *             Reload" is visible initially; scroll the box to reach the class
 *             tabs and lesson numbers - so they are not confused with the
 *             numbered activity list in the lower div. Pure layout wrapper:
 *             same links, same ids, no PHP/JS logic touched.
 *v260917b - EXTRAS section at end of div7 from pack_books.csv (cols:
 *             class,lesson,comment,url1,url2,...), numbered from 100.
 *             Missing CSV or no matching rows -> "NO EXTRA ACTIVITIES FOUND".
 *v260915 - [Μελίσπη] section occupies 1 number in the list with eg 1a,1b,...
 *v260906 — div1 starts with the "Μελίσπη" section (melispi_links.csv rows
 *             matching the current class+lesson, open in sideframe1), followed
 *             by the "Κουίζ" heading + quiz links - both headings now live
 *             INSIDE div1. Missing CSV or no matching rows -> no Μελίσπη
 *             section, page unchanged.
 *v260818 — fixed paths for SCH
 *v260519 — div1=quiz+0quiz, div2=unique(custom+act), div3/4=base split, detect custom unique files
 *v260504 — Initial version (generic, all 6 classes)
 *
 *
 * USAGE:
 *   pack_Book.php?c={CLASS}&l={LESSON}[&timer3][&showdivN][&probeserver][&noopengame]
 *
 * PARAMETERS:
 *   c  = Class letter: A, B, C, D, E, ST (default: A)
 *   l  = Lesson number: 01-30 (default: 01)
 *
 * OPTIONAL PARAMETERS (from pack_js_footer.js):
 *   timer / timer3 / timer5  = Timed mode: only div1 visible, divs unlock every 5 min
 *   showdivN                 = Show only div N (e.g. showdiv2 hides all except div2)
 *   probeserver              = Enable remote control via pack_refresh_browser.txt
 *   no_act_filter=true       = "ignore activity filters": disable the per-class
 *                              CSV/shown activity filtering (see v260922)
 *   opengame                 = Prefix all .swf links with opengame.php
 *   noopengame               = Disable opengame prefix
 *   norightclick             = Disable right-click in main page and iframe
 *
 * EXAMPLES:
 *   pack_Book.php?c=E&l=01              Class E, Lesson 1 (all activities visible)
 *   pack_Book.php?c=ST&l=15&timer3      Class ST, Lesson 15, timed mode (5-min intervals)
 *   pack_Book.php?c=A&l=10&showdiv2     Class A, Lesson 10, show only div2
 *   pack_Book.php?c=D&l=20&probeserver  Class D, Lesson 20, with probeserver enabled
 *   pack_BookE.php?l=05                 Shortcut: same as ?c=E&l=05
 *
 * ACTIVITY SOURCES (auto-discovered per lesson):
 *   1. activities-unique/  → Custom self-contained HTML per class/lesson (highest priority)
 *   2. activities-base/    → Generic HTML skeletons loaded with per-lesson JSON data
 *        Activity type is extracted from the JSON filename (dimX-lesNN-TYPE-suffix.json)
 *        and matched to the corresponding TYPE.html skeleton in activities-base/.
 *   3. wordboard/          → Wordboard PHP system (quiz, match, wheel, crossword, etc.)
 *
 * DIV MAPPING:
 *   div1 = unique act1 if exists, else 1st base JSON activity
 *   div2 = unique act2 if exists, else 2nd base JSON activity
 *   div3 = unique act3+ (all extras) if any exist, else 3rd base JSON activity
 *         (unique acts 4,5,... are all listed here; if no unique at all, the
 *          first 3 base JSON activities fill div1-3 via promotion)
 *   div4 = remaining dimBooksActivities base JSON activities (not promoted to div1-3)
 *   div5 = All wordboard games for this lesson (quiz, match, missingword, wheel,
 *          crossword, groupsort, wordsearch)
 *   div6 = Fun activities (chess)
 *   div7 = (empty)
 *
 * ADDING NEW ACTIVITIES:
 *   - activities-unique/: add file like dimE_les05act1.html → auto-appears in div1-3
 *   - activities-base/:   add JSON like dimE-les05-*.json in TPE-dimE-PEDIO/activities/
 *                          → auto-appears in div4 (type auto-detected from filename)
 *   - wordboard/:         add JSON like dimE-les05-*.json in admin/activities/{type}/
 *                          → auto-appears in div5
 *
 * BACKWARD COMPATIBILITY:
 *   pack_BookE.php?l=01 → redirects to pack_Book.php?c=E&l=01
 *
 */

$basePath = __DIR__;
$basePath = "../dim";
$dimBase = $basePath . '/dimBooksActivities';
$wordboardDir = $basePath . '/wordboard';
$debug=false; //shows all the URLs of this script

$classes = [
    'A' => [
        'name' => "Α' Δημοτικού",
        'dir' => 'TPE-dimA-PEDIO',
        'dimTag' => 'dimA',
        'lessons' => [
            1 => 'Πώς λύνω ένα πρόβλημα',
            2 => 'Η σειρά έχει σημασία!',
            3 => 'Φτιάχνω το πρώτο μου πρόγραμμα',
            4 => 'Πού θα φτάσει η σαρανταποδαρούσα;',
            5 => 'Σχεδιάζοντας το μονοπάτι',
            6 => 'Προγραμματίζω στον υπολογιστή!',
            7 => 'Η πρώτη μου ιστορία: Ταξίδι στο φεγγάρι',
            8 => 'Μαθαίνω για τα ρομπότ!',
            9 => 'Προγραμμάτισε το ρομπότ-πασχαλίτσα',
            10 => 'Ο υπολογιστής',
            11 => 'Οι συσκευές του υπολογιστή',
            12 => 'Πώς χρησιμοποιώ τον υπολογιστή;',
            13 => 'Δίκτυα υπολογιστών',
            14 => 'Τα ψηφιακά δίκτυα στη ζωή μας',
            15 => 'Αρχεία και φάκελοι',
            16 => 'Αποθήκευση και αρχεία',
            17 => 'Ο πρώτος μου εννοιολογικός χάρτης',
            18 => 'Μεγαλώνω τον εννοιολογικό μου χάρτη',
            19 => 'Μαθαίνω το ποντίκι του υπολογιστή',
            20 => 'Ο δείκτης του ποντικιού',
            21 => 'Κάνω κλικ με το ποντίκι του υπολογιστή',
            22 => 'Το δεξί κλικ, η ροδέλα κύλισης και η ενέργεια «σύρε»',
            23 => 'Γνωρίζω το πληκτρολόγιο',
            24 => 'Πληκτρολογώ κεφαλαία γράμματα και αριθμούς',
            25 => 'Γράφω τις πρώτες μου λέξεις με το πληκτρολόγιο',
            26 => 'Ιστοσελίδες και ιστότοποι',
            27 => 'Σελιδοδείκτες και σχολιασμός σε ιστοσελίδες',
            28 => 'Η πρώτη μου ζωγραφιά',
            29 => 'Περισσότερα εργαλεία στο πρόγραμμα ζωγραφικής',
            30 => 'Το Φωτόδεντρο και η Εκπαιδευτική τηλεόραση',
        ],
    ],
    'B' => [
        'name' => "Β' Δημοτικού",
        'dir' => 'TPE-dimB-PEDIO',
        'dimTag' => 'dimB',
        'lessons' => [
            1 => 'Επαναλήψεις',
            2 => 'Ανακάλυψε το μοτίβο',
            3 => 'Η εντολή επανάληψης',
            4 => 'Μοτίβα και εντολή επανάληψης',
            5 => 'Διαβάζω και διορθώνω προγράμματα με την εντολή επανάληψης',
            6 => 'Προγραμματίζω με την εντολή επανάληψης',
            7 => 'Πάρτι χορού',
            8 => 'Γνωρίζω το micro:bit',
            9 => 'Τα γεγονότα',
            10 => 'Ο υπολογιστής στη ζωή μας',
            11 => 'Ο υπολογιστής είναι ψηφιακή συσκευή',
            12 => 'Οι συσκευές του υπολογιστή',
            13 => 'Πώς χρησιμοποιώ τις εφαρμογές του υπολογιστή;',
            14 => 'Το διαδίκτυο: Ένα μεγάλο δίκτυο υπολογιστών',
            15 => 'Αποθήκευση και άνοιγμα αρχείων',
            16 => 'Αποθήκευση αρχείων σε φακέλους',
            17 => 'Ο πρώτος μου εννοιολογικός χάρτης με τον υπολογιστή',
            18 => 'Ο δεύτερος εννοιολογικός μου χάρτης με τον υπολογιστή',
            19 => 'Ο φυλλομετρητής',
            20 => 'Οι ψηφιακοί πόροι μιας ιστοσελίδας',
            21 => 'Εντοπίζω περισσότερους χαρακτήρες στο πληκτρολόγιο',
            22 => 'Γράφω περισσότερα στον υπολογιστή',
            23 => 'Επεξεργάζομαι αντικείμενα με το ποντίκι',
            24 => 'Χειρίζομαι περισσότερα εργαλεία στη ζωγραφική',
            25 => 'Η ψηφιακή πλατφόρμα e-me',
            26 => 'Η ψηφιακή πλατφόρμα e-me – Κυψέλη, τοίχος και εκπαιδευτικό περιεχόμενο',
            27 => 'Ηλεκτρονικά μηνύματα',
            28 => 'Λαμβάνω και στέλνω ηλεκτρονικά μηνύματα στην e-me',
            29 => 'Τα διαδραστικά βιβλία',
            30 => 'Ψηφιακή πολιτειότητα',
        ],
    ],
    'C' => [
        'name' => "Γ' Δημοτικού",
        'dir' => 'TPE-dimC-PEDIO',
        'dimTag' => 'dimC',
        'lessons' => [
            1 => 'Συνθήκες και αποφάσεις',
            2 => 'Αλήθεια ή ψέματα;',
            3 => 'Οι υπολογιστές παίρνουν αποφάσεις',
            4 => 'Γνωριμία με το περιβάλλον του Scratch',
            5 => 'Εντολή επιλογής στο Scratch (Μέρος Α\')',
            6 => 'Εντολή επιλογής στο Scratch (Μέρος Β\')',
            7 => 'Γνωριμία με το ρομπότ εδάφους (Έντισον)',
            8 => 'Παιχνίδι με το ρομπότ: Παγίδευσε το ρομπότ σου!',
            9 => 'Πώς αποθηκεύεται η ψηφιακή εικόνα;',
            10 => 'Τα εξαρτήματα και οι συσκευές του υπολογιστή',
            11 => 'Οι εφαρμογές του υπολογιστή',
            12 => 'Η δικτύωση υπολογιστών και ψηφιακών συσκευών',
            13 => 'Προστασία από κακόβουλα λογισμικά και κανόνες ασφαλείας',
            14 => 'Αρχεία και φάκελοι',
            15 => 'Είδη εννοιολογικών χαρτών',
            16 => 'Λύνω ένα πρόβλημα με εννοιολογικό χάρτη',
            17 => 'Περισσότερα εργαλεία στο πληκτρολόγιο',
            18 => 'Δικτυακός τόπος και ιστοσελίδες',
            19 => 'Τα βασικά χαρακτηριστικά ενός δικτυακού τόπου',
            20 => 'Μηχανές αναζήτησης',
            21 => 'Επιλέγω ό,τι είναι κατάλληλο από τα αποτελέσματα αναζήτησης',
            22 => 'Φτιάχνω κινούμενα σχέδια από εικόνες',
            23 => 'Επεξεργάζομαι το προφίλ μου στην e-me',
            24 => 'Τα ιστολόγια της e-me',
            25 => 'Τα άρθρα ενός ιστολογίου της e-me',
            26 => 'Αναζητώ και σχολιάζω άρθρα σε ιστολόγιο της e-me',
            27 => 'Διαμορφώνω το προσωπικό μου περιβάλλον στην e-me',
            28 => 'Χρησιμοποιώ μαθησιακά αντικείμενα εκπαιδευτικών αποθετηρίων',
            29 => 'Η υπερβολική χρήση του διαδικτύου',
            30 => 'Κανόνες και όρια για τη χρήση ψηφιακών συσκευών',
        ],
    ],
    'D' => [
        'name' => "Δ' Δημοτικού",
        'dir' => 'TPE-dimD-PEDIO',
        'dimTag' => 'dimD',
        'lessons' => [
            1 => 'Οι αλγόριθμοι',
            2 => 'Μία νέα εντολή επιλογής και μία νέα εντολή επανάληψης',
            3 => 'Ξανά και ξανά…',
            4 => 'Αριθμητικές πράξεις',
            5 => 'Επανάλαβε ώσπου…',
            6 => 'Αλλάζοντας ενδυμασίες',
            7 => 'Αφήγηση ιστοριών με προγραμματισμό',
            8 => 'Οδήγηση σε πίστα',
            9 => 'Αναγνωρίζοντας τις μαύρες γραμμές',
            10 => 'Οδηγώντας το ρομπότ στον λαβύρινθο',
            11 => 'Ψηφιακά δεδομένα',
            12 => 'Ποιον υπολογιστή να διαλέξω;',
            13 => 'Οι εφαρμογές του υπολογιστή',
            14 => 'Το ταξίδι των δεδομένων σε ένα δίκτυο',
            15 => 'Προστατεύομαι από διαδικτυακές επιθέσεις',
            16 => 'Δημιουργία ψηφιακού περιεχομένου',
            17 => 'Μεγέθη και διαχείριση αρχείων',
            18 => 'Επεξεργασία δεδομένων',
            19 => 'Προσθέτω εικόνες και συνδέσεις στον εννοιολογικό μου χάρτη',
            20 => 'Χρήση λειτουργιών του φυλλομετρητή',
            21 => 'Διάκριση αποτελεσμάτων αναζήτησης',
            22 => 'Διάκριση ηλεκτρονικού και συμβατικού ταχυδρομείου',
            23 => 'Χρήση ηλεκτρονικού ταχυδρομείου',
            24 => 'Δημιουργία παρουσίασης – Αντιγραφή και αποθήκευση',
            25 => 'Βασικές δυνατότητες επεξεργαστή κειμένου',
            26 => 'Διαχείριση επαφών και επικοινωνία',
            27 => 'Συνεργασία και επικοινωνία μέσω εκπαιδευτικής πλατφόρμας',
            28 => 'Πνευματικά δικαιώματα στο διαδίκτυο',
            29 => 'Κανόνες συμπεριφοράς (Netiquette) και ψηφιακό αποτύπωμα',
            30 => 'Κριτήρια αξιολόγησης πηγών στο διαδίκτυο',
        ],
    ],
    'E' => [
        'name' => "Ε' Δημοτικού",
        'dir' => 'TPE-dimE-PEDIO',
        'dimTag' => 'dimE',
        'lessons' => [
            1 => 'Ο αλγόριθμος του Καίσαρα',
            2 => 'Ο αλγόριθμος του Καίσαρα (συνέχεια)',
            3 => 'Σύνθετες λογικές εκφράσεις',
            4 => 'Κατασκευή απλού παιχνιδιού στο Scratch',
            5 => 'Αυξάνοντας τους πόντους και χάνοντας ζωές',
            6 => 'Η συνθήκη τέλους του παιχνιδιού',
            7 => 'Νίκη ή ήττα',
            8 => 'Υποπρογράμματα',
            9 => 'Παράλληλες ακολουθίες εντολών',
            10 => 'Μηνύματα',
            11 => 'Χαρούμενα γενέθλια! (micro:bit)',
            12 => 'Ψηφιοποίηση κειμένου',
            13 => 'Το υλικό και οι χρήστες του υπολογιστή',
            14 => 'Το ταξίδι των δεδομένων σε ένα δίκτυο',
            15 => 'Προσέχω ποια λογισμικά εγκαθιστώ',
            16 => 'Συγκέντρωση δεδομένων, υπολογιστικό φύλλο',
            17 => 'Υπολογιστικό φύλλο: Οργάνωση δεδομένων',
            18 => 'Αριθμητικές πράξεις στο υπολογιστικό φύλλο',
            19 => 'Υπολογιστικό φύλλο: οργάνωση δεδομένων σε διαγράμματα',
            20 => 'Τεχνητή νοημοσύνη και μηχανική μάθηση',
            21 => 'Γραμμή διευθύνσεων – Κριτήρια αξιολόγησης',
            22 => 'Καθορισμός φίλτρων αναζήτησης',
            23 => 'Αποθήκευση και ανάκτηση ψηφιακών αρχείων',
            24 => 'Δημιουργώ εντυπωσιακές παρουσιάσεις',
            25 => 'Δημιουργία κειμένων, παρουσιάσεων',
            26 => 'Αξιοποίηση της εφαρμογής e-me assignments',
            27 => 'Αξιοποίηση ψηφιακών μουσείων, εγκυκλοπαιδειών',
            28 => 'Ψηφιακές υπηρεσίες του πολίτη',
            29 => 'Επιδράσεις από τη χρήση του διαδικτύου',
            30 => 'Οι άδειες χρήσης CC Creative Commons',
        ],
    ],
    'ST' => [
        'name' => "ΣΤ' Δημοτικού",
        'dir' => 'TPE-dimST-PEDIO',
        'dimTag' => 'dimST',
        'lessons' => [
            1 => 'Προβλήματα και αλγόριθμοι',
            2 => 'Το παιχνίδι «Μάντεψε τον αριθμό»',
            3 => 'Είσοδος και έξοδος στο Scratch',
            4 => 'Υποπρογράμματα με μεταβλητές εισόδου',
            5 => 'Περισσότερα για τα υποπρογράμματα',
            6 => 'Μάντεψε τον αριθμό',
            7 => 'Επίπεδα δυσκολίας, ηχητικά και γραφικά εφέ',
            8 => 'Αισθητήρες φωτός – Μέρα ή νύχτα',
            9 => 'Ηχο-γράφημα',
            10 => 'Θερμόμετρο - Μέγιστη θερμοκρασία',
            11 => 'Καταγραφή δεδομένων και εκτέλεση πειραμάτων',
            12 => 'Το υλικό του υπολογιστή και τα χαρακτηριστικά του',
            13 => 'Ρυθμίσεις υλικού-λογισμικού και αντιμετώπιση προβλημάτων',
            14 => 'Διασύνδεση συσκευών στο διαδίκτυο',
            15 => 'Οι θετικές και αρνητικές επιπτώσεις του διαδικτύου',
            16 => 'Ερωτήματα και φόρμες συλλογής δεδομένων',
            17 => 'Οργάνωση και διαχείριση δεδομένων',
            18 => 'Συγκέντρωση, διαχείριση και ταξινόμηση δεδομένων',
            19 => 'Ανάλυση δεδομένων με χρήση φίλτρων',
            20 => 'Ερωτήματα και ανάλυση δεδομένων',
            21 => 'Εντοπισμός περιεχομένου στο διαδίκτυο',
            22 => 'Συμμετοχή - δημιουργία - διαχείριση συζήτησης',
            23 => 'Διαχείριση αναρτήσεων σε ιστολόγια',
            24 => 'Χρήση ετικετών για δημοσίευση και αναζήτηση',
            25 => 'Σύνθετες δυνατότητες του επεξεργαστή κειμένου',
            26 => 'Εκπαιδευτικές δυνατότητες των εργαλείων τηλεκπαίδευσης',
            27 => 'Χρήση υπηρεσιών βίντεο',
            28 => 'Βασικές ψηφιακές υπηρεσίες στη ζωή μας',
            29 => 'Τα προσωπικά δεδομένα και ο ΓΚΠΔ (GDPR)',
            30 => 'Το δημιουργικό διαδίκτυο ως εναλλακτικό εργαλείο',
        ],
    ],
];

$classKey = isset($_GET['c']) ? strtoupper(trim($_GET['c'])) : 'A';
if (!isset($classes[$classKey])) $classKey = 'A';
$class = $classes[$classKey];

$lesson = isset($_GET['l']) ? intval($_GET['l']) : 1;
if ($lesson < 1 || $lesson > 30) $lesson = 1;
$lessonPad = str_pad($lesson, 2, '0', STR_PAD_LEFT);

$noActFilter = isset($_GET['no_act_filter']) && $_GET['no_act_filter'] === 'true';

# --- Melispi links (v260906) ---
# Optional melispi_links.csv in this folder (cols: class,lesson,url,title,...).
# Keeps only the rows matching the current class + lesson. A missing file or no
# matching rows simply leaves the list empty -> page renders unchanged.
$melispiLinks = array();
$melispiCsvPath = __DIR__ . '/melispi_links.csv';
if (is_file($melispiCsvPath) && ($_mh = fopen($melispiCsvPath, 'r')) !== false) {
    while (($_mrow = fgetcsv($_mh)) !== false) {
        if (!isset($_mrow[0]) || strcasecmp(trim($_mrow[0]), 'class') === 0) continue; // header row
        if (!isset($_mrow[1], $_mrow[2], $_mrow[3])) continue;
        if (strtoupper(trim($_mrow[0])) !== $classKey) continue;
        if ((int)trim($_mrow[1]) !== $lesson) continue;
        $_murl = trim($_mrow[2]);
        $_mtitle = trim($_mrow[3]);
        if ($_murl === '' || $_mtitle === '') continue;
        $melispiLinks[] = array('href' => $_murl, 'title' => $_mtitle);
    }
    fclose($_mh);
}


# --- Extras links (v260917) ---
# Optional pack_books.csv in this folder (cols: class,lesson,comment,url1,url2,...).
# Keeps only the rows matching the current class + lesson; every URL column of a
# matching row becomes one EXTRAS entry. The comment column is not rendered.
# A missing file or no matching rows -> empty list -> "NO EXTRA ACTIVITIES FOUND".
$extraLinks = array();
$booksCsvPath = __DIR__ . '/pack_books.csv';
if (is_file($booksCsvPath) && ($_bh = fopen($booksCsvPath, 'r')) !== false) {
    while (($_brow = fgetcsv($_bh)) !== false) {
        if (!isset($_brow[0]) || strcasecmp(trim($_brow[0]), 'class') === 0) continue; // header row
        if (!isset($_brow[1])) continue;
        if (strtoupper(trim($_brow[0])) !== $classKey) continue;
        if ((int)trim($_brow[1]) !== $lesson) continue;
        foreach (array_slice($_brow, 3) as $_burl) {
            $_burl = trim($_burl);
            if ($_burl === '') continue;
            $_bpos = strrpos($_burl, '/');
            $_blabel = ($_bpos === false) ? $_burl : substr($_burl, $_bpos + 1);
            if ($_blabel === '') $_blabel = $_burl;
            $extraLinks[] = array('href' => $_burl, 'label' => $_blabel);
        }
    }
    fclose($_bh);
}


$activitiesUniqueDir = $dimBase . '/activities-unique';
$classActivitiesDir = $dimBase . '/TPE-dim' . $classKey . '-PEDIO/activities';


$oquizFiles = glob($dimBase . '/activities-base/0quiz*.html');

$lessonTitle = isset($class['lessons'][$lesson]) ? $class['lessons'][$lesson] : '';

function detectType($data) {
    if (isset($data['questions']))      return 'quiz';
    if (isset($data['categories']))     return 'drag-categories';
    if (isset($data['steps']))          return 'drag-order';
    if (isset($data['pairs']) && !isset($data['speed'])) return 'memory';
    if (isset($data['gridSize']))       return 'grid-game';
    if (isset($data['targets']))        return 'click-game';
    if (isset($data['items']) && isset($data['speed'])) return 'falling-letters';
    if (isset($data['palette']))        return 'canvas-pixels';
    if (isset($data['winScore']))       return 'arcade-game';
    if (isset($data['rounds']) && isset($data['range'])) return 'guess-number';
    if (isset($data['qrItems']))        return 'qr-scanner';
    if (isset($data['filterMode']))     return 'spreadsheet-filter';
    return 'unknown';
}

// v260923 - group tagged items per section. A tag with MORE than one item
// renders as ONE numbered <li> holding a nested <ul class="$subClass"> whose
// children are lettered a., b., c. by CSS (same scheme as meli-sub/uniq-sub).
// A tag with a single item stays a plain <li> - no subcategory, its number is
// the plain badge number. $items: list of ['tag'=>tag, 'html'=>'<li>...</li>'].
// Tags keep first-appearance order; "19a" resolves to "19" in the filter JS.
function groupTaggedItems(array $items, $subClass) {
    $byTag = array();
    $order = array();
    foreach ($items as $it) {
        if (!isset($byTag[$it['tag']])) { $byTag[$it['tag']] = array(); $order[] = $it['tag']; }
        $byTag[$it['tag']][] = $it['html'];
    }
    $out = array();
    foreach ($order as $tag) {
        $lis = $byTag[$tag];
        if (count($lis) === 1) { $out[] = $lis[0]; continue; }
        // v260923c - class="subwrap" lets CSS hide the wrapper's bare number
        // (li.subwrap::before) without :has(), which old engines like
        // SRWare Iron 61 (Chromium 61) do not support.
        $out[] = '<li class="subwrap"><ul class="' . $subClass . '">' . "\n" . implode("\n", $lis) . "\n</ul></li>";
    }
    return $out;
}

$uniqueActs = [];
$baseActs = [];
$quizActs = [];

if (is_dir($activitiesUniqueDir)) {
    $pattern1 = $activitiesUniqueDir . '/dim' . $classKey . '_les' . $lessonPad . '*.html';
    $pattern2 = $activitiesUniqueDir . '/dim' . $classKey . '-les' . $lessonPad . '-*.html';
    $uniqueFiles = array_merge(glob($pattern1), glob($pattern2));

    usort($uniqueFiles, function($a, $b) {
        $ba = basename($a);
        $bb = basename($b);
        $hasActA = preg_match('/act(\d+)/', $ba, $ma);
        $hasActB = preg_match('/act(\d+)/', $bb, $mb);
        if ($hasActA && !$hasActB) return -1;
        if (!$hasActA && $hasActB) return 1;
        if ($hasActA && $hasActB) return intval($ma[1]) - intval($mb[1]);
        return strcmp($ba, $bb);
    });

    foreach ($uniqueFiles as $file) {
        $filename = basename($file);
        $title = '';
        $content = @file_get_contents($file, false, null, 0, 2000);
        if (preg_match('/<title>(.*?)<\/title>/i', $content, $tm)) {
            $title = html_entity_decode(trim($tm[1]), ENT_QUOTES | ENT_HTML5);
        }
        $uniqueActs[] = [
            'href' => $dimBase.'/activities-unique/' . $filename,
            'title' => $title ? $title : 'Δραστηριότητα',
        ];
    }
}

if (is_dir($classActivitiesDir)) {
    $jsonPattern = $classActivitiesDir . '/dim' . $classKey . '-les' . $lessonPad . '-*.json';
    $jsonFiles = glob($jsonPattern);
    usort($jsonFiles, function($a, $b) {
        return strcmp(basename($a), basename($b));
    });
    foreach ($jsonFiles as $file) {
        $filename = basename($file);
        $json = @json_decode(file_get_contents($file), true);
        if (!$json) continue;

        if (preg_match('/dim\w+-les\d+-(.+)-\w+\.json/', $filename, $fm)) {
            $type = $fm[1];
        } else {
            $type = detectType($json);
        }
        $title = isset($json['title']) ? $json['title'] : '';
        $subtitle = isset($json['subtitle']) ? $json['subtitle'] : '';

        //$jsonRelPath = '../TPE-dim' . $classKey . '-PEDIO/activities/' . $filename;
        $classActivitiesDir2 = substr($classActivitiesDir, 2); // v260818
        $jsonRelPath = $classActivitiesDir2.'/'. $filename;    // v260818


        if ($type === 'quiz') {
            $quizActs[] = [
                'href' => $dimBase.'/activities-base/quiz.html?data=' . urlencode($jsonRelPath),
                'title' => $title ? $title : 'Κουίζ',
                'subtitle' => $subtitle,
                'type' => 'quiz',
            ];
            foreach ($oquizFiles as $oqFile) {
                $oqBasename = basename($oqFile, '.html');
                $oqTitle = $title ? $title : 'Κουίζ';
                $quizActs[] = [
                    'href' => $dimBase.'/activities-base/' . $oqBasename . '.html?data=' . urlencode($jsonRelPath),
                    'title' => $oqTitle,
                    'subtitle' => $subtitle,
                    'type' => $oqBasename,
                ];
            }
        } else {
            $href = $dimBase.'/activities-base/' . $type . '.html?data=' . urlencode($jsonRelPath);
            $baseActs[] = [
                'href' => $href,
                'title' => $title ? $title : 'Δραστηριότητα',
                'subtitle' => $subtitle,
                'type' => $type,
            ];
        }
    }
}

$wordboardTypes = ['quiz', 'match', 'missingword', 'wheel', 'crossword', 'groupsort', 'wordsearch'];
$wordboardGames = [];
foreach ($wordboardTypes as $type) {
    $wwPattern = $wordboardDir . '/admin/activities/' . $type . '/' . $class['dimTag'] . '-les' . $lessonPad . '-*.json';
    $wwFiles = glob($wwPattern);
    foreach ($wwFiles as $file) {
        $json = @json_decode(file_get_contents($file), true);
        if (!$json) continue;
        $id = isset($json['id']) ? $json['id'] : '';
        $title = isset($json['title']) ? $json['title'] : $type;
        $wordboardGames[] = [
            'type' => $type,
            'id' => $id,
            'title' => $title,
            'href' => $wordboardDir.'/games/' . $type . '/' . $type . '.html?id=' . urlencode($id),
        ];
    }
}

$divs = [1 => '', 2 => '', 3 => '', 4 => '', 5 => '', 6 => '', 7 => ''];

// div1 = "Μελίσπη" section FIRST (melispi_links.csv, v260906), then the
// "Κουίζ" section (the Κουίζ heading belongs to the quiz links only).
// Both headings live INSIDE div1 so the timer/showdiv logic is untouched.
// v260923 - quiz items are grouped per tag via groupTaggedItems(): same-tag
// items ([quiz], [0quiz_penalty], ...) share ONE number with a., b., c.
// children; a single-item tag stays a plain number.
$quizItems = [];
foreach ($quizActs as $a) {
    $label = htmlspecialchars($a['title']);
    if ($a['subtitle']) $label .= ' <small>(' . htmlspecialchars($a['subtitle']) . ')</small>';
    $quizItems[] = array(
        'tag' => $a['type'],
        'html' => '<li><span style="color:#2e7d32;font-size:.7rem">[' . htmlspecialchars($a['type']) . ']</span> <a href="' . htmlspecialchars($a['href']) . '" target="sideframe1">' . $label . '</a></li>',
    );
}

$div1Parts = [];
if (!empty($melispiLinks)) {
    // v260916 - whole Μελίσπη group = ONE <ol> number: a single wrapper <li>
    // with a nested <ul class="meli-sub"> (sub-items labelled 1a, 1b via CSS),
    // so quiz items continue from 2 automatically.
    // v260923 - a single melispi link renders as a plain <li> (no subletter).
    $meliSubs = [];
    foreach ($melispiLinks as $m) {
        $meliSubs[] = '<li><span style="color:#4a148c;font-size:.7rem">[ΜΕΛΙΣΠΗ]</span> <a href="' . htmlspecialchars($m['href']) . '" target="sideframe1">' . htmlspecialchars($m['title']) . '</a></li>';
    }
    $div1Parts[] = '<hr><b>Μελίσπη</b>';
    if (count($meliSubs) === 1) {
        $div1Parts[] = $meliSubs[0];
    } else {
        // v260923c - wrapper gets class="subwrap" too (see groupTaggedItems)
    $div1Parts[] = '<li class="subwrap"><ul class="meli-sub">' . "\n" . implode("\n", $meliSubs) . "\n" . '</ul></li>';
    }
}
if (!empty($quizItems)) {
    $div1Parts[] = '<hr><b>Κουίζ</b>';
    $div1Parts = array_merge($div1Parts, groupTaggedItems($quizItems, 'tag-sub'));
}
$divs[1] = implode("\n", $div1Parts);
if ($divs[1] === '') {
    $divs[1] = '<li style="color:#999;font-style:italic">Δεν υπάρχουν δραστηριότητες κουίζ</li>';
}

// div2 = all unique activities (old act* + new custom*)
// v260923 - all unique items share the tag "unique": >1 item -> ONE number
// with a., b., c. children (as before); exactly 1 item -> plain number.
$uniqueItems = [];
foreach ($uniqueActs as $a) {
    $uniqueItems[] = array(
        'tag' => 'unique',
        'html' => '<li><span style="color:#880e4f;font-size:.7rem">[unique]</span> <a href="' . htmlspecialchars($a['href']) . '" target="sideframe1">' . htmlspecialchars($a['title']) . '</a></li>',
    );
}
if (empty($uniqueItems)) {
    $divs[2] = '<li style="color:#999;font-style:italic">Δεν υπάρχουν μοναδικές δραστηριότητες</li>';
} else {
    $divs[2] = implode("\n", groupTaggedItems($uniqueItems, 'uniq-sub'));
}

// div3 + div4 = split remaining base activities
// v260923 - grouped per tag first ([drag-categories], [matching-lines], ...);
// the div3/div4 split now cuts between whole tag-groups.
$baseItems = [];
foreach ($baseActs as $a) {
    $label = htmlspecialchars($a['title']);
    if ($a['subtitle']) $label .= ' <small>(' . htmlspecialchars($a['subtitle']) . ')</small>';
    $baseItems[] = array(
        'tag' => $a['type'],
        'html' => '<li><span style="color:#0d47a1;font-size:.7rem">[' . htmlspecialchars($a['type']) . ']</span> <a href="' . htmlspecialchars($a['href']) . '" target="sideframe1">' . $label . '</a></li>',
    );
}
if (empty($baseItems)) {
    $divs[3] = '<li style="color:#999;font-style:italic">Δεν υπάρχουν βασικές δραστηριότητες</li>';
    $divs[4] = '';
} else {
    $baseUnits = groupTaggedItems($baseItems, 'tag-sub');
    $mid = ceil(count($baseUnits) / 2);
    $divs[3] = implode("\n", array_slice($baseUnits, 0, $mid));
    $divs[4] = implode("\n", array_slice($baseUnits, $mid));
}

// v260923 - wordboard items grouped per type as well ([QUIZ], [MATCH], ...).
$wwItems = [];
foreach ($wordboardGames as $wg) {
    $typeLabel = strtoupper($wg['type']);
    $wwItems[] = array(
        'tag' => $wg['type'],
        'html' => '<li><span style="color:#bf360c;font-size:.7rem">[' . $typeLabel . ']</span> <a href="' . htmlspecialchars($wg['href']) . '" target="sideframe1">' . htmlspecialchars($wg['title']) . '</a></li>',
    );
}
if (empty($wwItems)) {
    $divs[5] = '<li style="color:#999;font-style:italic">Δεν υπάρχουν wordboard δραστηριότητες</li>';
} else {
    $divs[5] = implode("\n", groupTaggedItems($wwItems, 'tag-sub'));
}

$divs[6] = '<li><a href="./chess--great-mate-master__chess_problems_GREEK02_NoNavUrl.swf" target="sideframe1">chess--great-mate-master<BR> chess_exercises <BR>GREEK02</a></li>';


if($debug)$debugUrl = '?' . http_build_query(array_merge($_GET, ['debug' => '1']));
if($debug)$divs[7] = '<li><a href="' . htmlspecialchars($debugUrl) . '" target="_blank">📋 Όλες οι δραστηριότητες (raw)</a></li>';

// v260917 - EXTRAS section (pack_books.csv) at the END of div7. Badge numbers
// (100, 101, 102…) are produced by the #div7 ol CSS override in the <style>
// block below; value="100" on the first <li> is only a no-CSS fallback.
if (!empty($extraLinks)) {
    $extraItems = [];
    foreach ($extraLinks as $i => $e) {
        $valueAttr = ($i === 0) ? ' value="100"' : '';
        $extraItems[] = '<li' . $valueAttr . '><a href="' . htmlspecialchars($e['href']) . '" target="sideframe1">' . htmlspecialchars($e['label']) . '</a></li>';
    }
    $divs[7] .= "\n<hr><b>EXTRAS</b>\n<ol>" . implode("\n", $extraItems)."</ol>";
} else {
    $divs[7] .= "\n<hr><b>EXTRAS</b>\n" . '<div style="color:#999;font-style:italic">NO EXTRA ACTIVITIES FOUND</div>';
}

if (isset($_GET['debug'])) {
    header('Content-Type: text/html; charset=UTF-8');
    echo '<pre>';
    for ($d = 1; $d <= 7; $d++) {
        echo htmlspecialchars('<div id="div' . $d . '">') . "\n";
        echo htmlspecialchars($divs[$d]) . "\n";
        echo htmlspecialchars('</div>') . "\n\n";
    }
    echo '</pre>';
    exit;
}

$pageTitle = "Pack Book {$classKey} - Μάθημα {$lessonPad}";
if ($lessonTitle) $pageTitle .= ": $lessonTitle";

function buildUrl($c, $l) {
    $params = $_GET;
    $params['c'] = $c;
    $params['l'] = str_pad($l, 2, '0', STR_PAD_LEFT);
    return '?' . http_build_query($params);
}
?>
<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title><?= htmlspecialchars($pageTitle) ?></title>

<script src="pack_js_header.js" type="text/javascript"></script>

<style>
.cls-tabs{margin-bottom:2px;display:flex;flex-wrap:wrap;gap:2px;}
.cls-tab{display:inline-block;padding:2px 6px;font-size:.7rem;font-weight:bold;border-radius:4px;text-decoration:none;color:#fff;background:#888;}
.cls-tab:hover{opacity:.85;}
.cls-tab.sel{background:linear-gradient(135deg,#667eea,#764ba2);box-shadow:0 1px 4px rgba(102,126,234,.4);}
/* Section sub-lists (Μελίσπη, Μοναδικές, v260923 same-tag groups): the wrapper
   <li> gets its number from the page <ol>; the nested <li> are display:block
   so they do NOT advance the <ol> counter (list-style:none alone still
   increments it) and get their own a., b., c. labels from the per-list
   "subitem" counter */
.meli-sub,.uniq-sub,.tag-sub{list-style:none;margin:0;padding:0;counter-reset:subitem;}
.meli-sub li,.uniq-sub li,.tag-sub li{display:block;list-style:none;counter-increment:none;}
/* v260923b - counter(list) is visible here (counters are inherited): it holds
   the wrapper li's number, so children read 9a., 9b., ... instead of a., b. */
.meli-sub li::before,.uniq-sub li::before,.tag-sub li::before{counter-increment:subitem;content:counter(list) counter(subitem,lower-alpha) ". ";font-weight:bold;}
/* v260923c - grouped items: hide the wrapper <li>'s bare number (the wrapper
   holds only the sub-list, children carry 9a., 9b. themselves). Class-based
   selector (no :has()) so old Chromium engines apply it too - SRWare Iron 61
   dropped the previous li:has(> ul.*-sub)::before rule entirely. */
li.subwrap::before{display:none;}
/* EXTRAS (v260917): pack_js_header.js badges every <li> with counters(list,"."),
   and the main <ol>'s counter scope reaches div7 (later sibling), so extras
   badges render as "33.1", "33.2"… Override inside div7: restart the counter
   at 99 (first badge increments to 100) and print only the innermost value. */
#div7 ol{counter-reset:list 99;}
#div7 ol li::before{content:counter(list) " ";}
</style>

</head>
<body  onload="init_links()">
<!--
v260519 - div1=quiz+0quiz, div2=unique, div3/4=base split
-->
<TABLE BORDER="2" CELLPADDING="2" CELLSPACING="2"  style="table-layout: fixed;" WIDTH="100%" height=100%>
<tbody><tr style="vertical-align:top" valign="top">
<td style="width: 120px;word-wrap:break-word;" valign="top">

<!-- v260921 - reduced-size scrollable window for the class/lesson selector:
     shows only the header text (class, lesson, Reload); scroll to see the
     class tabs and lesson numbers (keeps them apart from the numbered
     activities list of the lower div). -->
<div id="left_box_upper" style="height: 88px; overflow: auto">

<b><?= htmlspecialchars($class['name']) ?></b><br>
<b>Μάθημα <?= $lessonPad ?><?= $lessonTitle ? ' — ' . htmlspecialchars($lessonTitle) : '' ?></b>
<br><small><a href="<?= htmlspecialchars(buildUrl($classKey, $lesson)) ?>" style="font-size:.8rem">🔄 Reload</a></small>

<div class="cls-tabs">
<?php foreach ($classes as $ck => $ci): ?>
<a href="<?= htmlspecialchars(buildUrl($ck, $lesson)) ?>" class="cls-tab<?= $ck === $classKey ? ' sel' : '' ?>"><?= $ck ?></a>
<?php endforeach; ?>
</div>

<div style="margin-top:2px">
<small>
<?php for ($i = 1; $i <= 30; $i++):
    $sel = ($i === $lesson) ? ' style="font-weight:bold;color:#5b3ea8;text-decoration:underline"' : '';
?>
<a href="<?= htmlspecialchars(buildUrl($classKey, $i)) ?>"<?= $sel ?>><?= str_pad($i, 2, '0', STR_PAD_LEFT) ?></a>
<?php endfor; ?>
</small>
</div>

<div style="margin-top:2px">
<label style="font-size:.8rem">
<input type="checkbox" id="no_act_filter"<?= $noActFilter ? ' checked' : '' ?>
       onchange="var p=new URLSearchParams(location.search); if(this.checked){p.set('no_act_filter','true');}else{p.delete('no_act_filter');} location.search=p.toString();">
ignore activity filters
</label>
</div>

</div><!-- v260921 end of scrollable class/lesson selector window -->
<hr>
<div  id="left_box_bottom" style="height: 550px; overflow: auto">
<ol>
<!--   MAIN MENU LINKS ############################### -->      
<hr><div id="probeserver"></div><hr>     

<div id="div1" onClick="showItTimer(); return true;">
<?= $divs[1] ?>
</div>

<hr><b>Μοναδικές Δραστηριότητες</b>
<div id="div2">
<?= $divs[2] ?>
</div>

<hr><b>Βασικές Δραστηριότητες</b>
<div id="div3">
<?= $divs[3] ?>
</div>

<div id="div4">
<?= $divs[4] ?>
</div>

<hr><b>Wordboard</b>
<div id="div5">
<?= $divs[5] ?>
</div>

<div id="div6">
<?= $divs[6] ?>
</div>
</ol>

<!-- v260917 - div7 sits OUTSIDE the page <ol>; its EXTRAS <ol> is numbered
     100, 101, 102… by the #div7 ol CSS override in the <style> block above. -->
<div id="div7">
<?= $divs[7] ?>
</div>

<!--   MAIN MENU LINKS ############################### -->
</div>
</TD>

<TD style="vertical-align:top;height:100%"  valign="top">
<iframe name="sideframe1" src="" allowfullscreen="" height="98%" frameborder="0" width="98%"></iframe>

</TD>
</TR>

</TABLE>

<!-- FUNCTIONS #################### -->

<script src="pack_js_footer.js" type="text/javascript"></script>
<!-- v260921 - optional per-class activity list filter (pack_refresh_activities_per_class.csv,
     active only with &probeserver). If the file below is missing the page works as before.
     v260924 - ?v260924 busts browser cache so the fixed sub-numbering JS loads. -->
<script src="pack_js_activities_filter.js?v260924" type="text/javascript"></script>
<script>
    if(typeof(first_click) === 'undefined') {
    document.write('<script src="https://cdn.jsdelivr.net/gh/plirof/dim-lesson-pack-planner/lesson_packs/pack_js_footer.js"><\/script>')
    }
</script>

</body></html>
