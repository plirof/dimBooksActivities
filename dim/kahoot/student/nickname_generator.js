var greekNames = [
    'Αλέξανδρος', 'Γεώργιος', 'Κωνσταντίνος', 'Ελένης',
    'Δημήτρης', 'Νικόλαος', 'Παναγιώτης', 'Χρήστος',
    'Ευαγγελία', 'Σπυρίδων', 'Μιχαήλ', 'Αθανάσιος',
    'Λάμπρος', 'Φώτιος', 'Ανδρέας', 'Ευάγγελος',
    'Κυριάκης', 'Μιχάλης', 'Παπαδόπουλος', 'Κωνσταντίνου',
    'Στέφανος', 'Εμμανουήλ', 'Αναστασίου', 'Πέτρου'
];

var englishNames = [
    'Luna', 'Stella', 'Leo', 'Nova', 'Felix',
    'Zoe', 'Arthur', 'Ivy', 'Lucas', 'Mia',
    'Sophia', 'Oliver', 'Emma', 'Henry', 'Olivia',
    'Jack', 'Ava', 'Ethan', 'Charlotte', 'William'
];

function generateNickname() {
    var langSelector = document.querySelector('input[name="nickname_lang"]:checked');
    var useGreek = langSelector && langSelector.value === 'greek';
    var names = useGreek ? greekNames : englishNames;
    var randomName = names[Math.floor(Math.random() * names.length)];
    
    document.getElementById('username').value = randomName;
    document.getElementById('username').focus();
}
