document.addEventListener('DOMContentLoaded', function() {
    const themeSelect = document.getElementById('theme');
    if (themeSelect) {
        themeSelect.addEventListener('change', function() {
            const theme = this.value;
            updateTheme(theme);
        });
    }
    
    // Check for dark mode toggle in settings page
    const darkModeToggle = document.querySelector('.theme-toggle');
    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', function() {
            const currentTheme = document.body.classList.contains('theme-dark') ? 'dark' : 'light';
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            updateTheme(newTheme);
        });
    }

    // Debug mode persistence
    const debugToggle = document.querySelector('#debugMode');
    if (debugToggle) {
        // Set initial state from localStorage
        const debugEnabled = localStorage.getItem('debugMode') === 'true';
        debugToggle.checked = debugEnabled;
        document.body.classList.toggle('debug-mode', debugEnabled);

        // Listen for changes
        debugToggle.addEventListener('change', function() {
            const isDebug = this.checked;
            localStorage.setItem('debugMode', isDebug);
            document.body.classList.toggle('debug-mode', isDebug);
            
            // Update via AJAX
            fetch('json/update_debug.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'debug=' + (isDebug ? '1' : '0')
            })
            .then(response => response.json())
            .catch(error => console.error('Error updating debug mode:', error));
        });
    }
});

function updateTheme(theme) {
    // Update body class immediately for instant feedback
    document.body.classList.remove('theme-light', 'theme-dark');
    document.body.classList.add('theme-' + theme);

    // Save the theme preference
    fetch('json/update_theme.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'theme=' + encodeURIComponent(theme)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (!data.success) {
            console.error('Failed to save theme preference:', data.error);
        }
    })
    .catch(error => {
        console.error('Error saving theme preference:', error);
    });
} 