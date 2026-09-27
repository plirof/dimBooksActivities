# Questions That May Reference Specific Examples (Audit Results)

---

## Common Violation Patterns

1. **Named character recall** (~30 violations): "Έλλη", "Αστερίας", "φουσκωτό ψάρι", "Αλεξία", "Αντρέας", "Μπάλα", "Καλάθι", "γατούλα", "σκυλάκι", "σπιτάκι" — questions answerable only by knowing which character played which role in the exercise.

2. **"Στο μάθημα / στο παιχνίδι / στο πρόγραμμα" framing** (~20 violations): Explicit references tying the question to the specific classroom exercise.

3. **Specific value/threshold recall** (~15 violations): "πόντοι = 10", "ζωές = 0", threshold "100", "5 προσπάθειες", stage width "200", formula "Επίπεδο * 10", "3 lives", "255".

4. **Specific algorithm/variable names** (~10 violations): "Αλγόριθμος 1", "Αλγόριθμος 2", "Αλγόριθμος 3", "Ρυθμός", "μέγιστη_θερμοκρασία", "στο κομπιουτεράκι".

5. **"χρησιμοποιήσαμε / φτιάξαμε / δημιουργήσαμε"** (~8 violations): First person plural referencing what was done in the specific exercise.

6. deep-review all lessons to find out if a quiz question does not refer to theory and refers to specific examples of this lesson

---

## Next Steps

1. User reviews this list and identifies which questions to fix
2. For each flagged question, replace with a general-concept version that tests the same underlying knowledge
3. Update quiz JSON files in `data/quiz/`
4. Update this file to mark fixed questions
