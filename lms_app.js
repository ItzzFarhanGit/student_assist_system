var buttons = document.querySelectorAll('.nav-btn');
var content = document.getElementById('page-content');
var title = document.getElementById('page-title');

function loadPage(pageName, buttonText) {
    content.innerHTML = '<div class="loader">Loading...</div>';
    title.textContent = buttonText;

    fetch(pageName)
        .then(function (response) {
            if (!response.ok) {
                throw new Error('Page could not be loaded');
            }
            return response.text();
        })
        .then(function (html) {
            content.innerHTML = html;
        })
        .catch(function () {
            content.innerHTML = '<div class="glass-card"><h3>Something went wrong</h3><p>Please check that the page file exists.</p></div>';
        });
}

function loadSubjectDetails(form) {
    var params = new URLSearchParams(new FormData(form)).toString();

    fetch('lms_subject_viewer.php?' + params)
        .then(function (response) {
            return response.text();
        })
        .then(function (html) {
            content.innerHTML = html;
        });
}

buttons.forEach(function (button) {
    button.addEventListener('click', function () {
        buttons.forEach(function (item) {
            item.classList.remove('active');
        });

        button.classList.add('active');
        loadPage(button.getAttribute('data-page'), button.textContent);
    });
});

loadPage('lms_subject_viewer.php', 'Subject Selection');
