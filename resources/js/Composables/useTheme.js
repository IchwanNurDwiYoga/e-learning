import { computed, ref } from 'vue';

const THEME_STORAGE_KEY = 'theme-preference';
const VALID_THEMES = ['light', 'dark', 'system'];
const theme = ref('system');

let initialized = false;
let mediaQuery = null;
let mediaChangeHandler = null;

const getStoredTheme = () => {
    const storedTheme = window.localStorage.getItem(THEME_STORAGE_KEY);
    return VALID_THEMES.includes(storedTheme) ? storedTheme : null;
};

const resolveTheme = (value) => {
    if (value === 'system') {
        return mediaQuery?.matches ? 'dark' : 'light';
    }

    return value;
};

const applyTheme = () => {
    const resolvedTheme = resolveTheme(theme.value);
    const root = document.documentElement;

    root.classList.toggle('dark', resolvedTheme === 'dark');
    root.style.colorScheme = resolvedTheme;
    root.setAttribute('data-theme', resolvedTheme);
};

const attachMediaListener = () => {
    mediaChangeHandler = () => {
        if (theme.value === 'system') {
            applyTheme();
        }
    };

    if (typeof mediaQuery.addEventListener === 'function') {
        mediaQuery.addEventListener('change', mediaChangeHandler);
        return;
    }

    mediaQuery.addListener(mediaChangeHandler);
};

export const initializeTheme = () => {
    if (initialized || typeof window === 'undefined') {
        return;
    }

    mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    theme.value = getStoredTheme() ?? 'system';
    applyTheme();
    attachMediaListener();

    initialized = true;
};

export const setTheme = (value) => {
    if (!VALID_THEMES.includes(value)) {
        return;
    }

    theme.value = value;

    if (value === 'system') {
        window.localStorage.removeItem(THEME_STORAGE_KEY);
    } else {
        window.localStorage.setItem(THEME_STORAGE_KEY, value);
    }

    applyTheme();
};

export const toggleTheme = () => {
    const nextTheme = resolveTheme(theme.value) === 'dark' ? 'light' : 'dark';
    setTheme(nextTheme);
};

export const resetThemeToSystem = () => {
    setTheme('system');
};

export const useTheme = () => {
    initializeTheme();

    const resolvedTheme = computed(() => resolveTheme(theme.value));
    const isSystemTheme = computed(() => theme.value === 'system');

    return {
        theme,
        resolvedTheme,
        isSystemTheme,
        setTheme,
        toggleTheme,
        resetThemeToSystem,
        options: VALID_THEMES,
    };
};
