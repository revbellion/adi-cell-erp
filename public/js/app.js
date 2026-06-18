function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('show');
}

function toggleDarkMode() {
    document.body.classList.toggle('dark-mode');
    var isDark = document.body.classList.contains('dark-mode');
    localStorage.setItem('darkMode', isDark);
    document.getElementById('darkModeIcon').className = isDark ? 'fas fa-sun' : 'fas fa-moon';
}

function setTheme(theme) {
    document.body.classList.remove('theme-emerald', 'theme-purple', 'theme-rose', 'theme-orange', 'theme-cyan');
    if (theme) {
        document.body.classList.add('theme-' + theme);
    }
    localStorage.setItem('theme', theme);
    document.querySelectorAll('.theme-dot').forEach(function(dot) {
        dot.classList.toggle('active', dot.getAttribute('data-theme') === theme);
    });
}

(function() {
    if (localStorage.getItem('darkMode') === 'true') {
        document.body.classList.add('dark-mode');
        document.getElementById('darkModeIcon').className = 'fas fa-sun';
    }
    var savedTheme = localStorage.getItem('theme') || '';
    setTheme(savedTheme);
    document.querySelectorAll('.theme-dot').forEach(function(dot) {
        dot.addEventListener('click', function() {
            setTheme(this.getAttribute('data-theme'));
        });
    });
})();
