import { ref } from 'vue';

const isDark = ref(false);
let initialized = false;

function apply(dark) {
    document.documentElement.classList.toggle('dark', dark);
}

function init() {
    if (initialized) {
        return;
    }
    initialized = true;

    const stored = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    isDark.value = stored ? stored === 'dark' : prefersDark;
    apply(isDark.value);
}

function toggle() {
    isDark.value = !isDark.value;
    localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
    apply(isDark.value);
}

export function useTheme() {
    init();

    return { isDark, toggle };
}
