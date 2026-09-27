function filterQuizzes() {
    var categoryFilter = document.getElementById('category-filter').value;
    var quizRows = document.querySelectorAll('.quiz-row');
    
    quizRows.forEach(function(row) {
        if (!categoryFilter) {
            row.style.display = 'table-row';
            return;
        }
        
        var quizCategory = row.getAttribute('data-category');
        if (quizCategory === categoryFilter) {
            row.style.display = 'table-row';
        } else {
            row.style.display = 'none';
        }
    });
}
