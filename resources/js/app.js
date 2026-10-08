import './bootstrap';

const updateThemeButtons = () => {
    const isDark = document.documentElement.classList.contains('dark');

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.textContent = isDark ? 'Use light theme' : 'Use dark theme';
        button.setAttribute('aria-pressed', String(isDark));
    });
};

document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const isDark = document.documentElement.classList.toggle('dark');
        localStorage.setItem('helpdesk-theme', isDark ? 'dark' : 'light');
        updateThemeButtons();
    });
});

updateThemeButtons();
