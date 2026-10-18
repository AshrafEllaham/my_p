import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

const apiBaseUrl = (() => {
    try {
        return (window.localStorage.getItem('saey_api_base') || import.meta.env.VITE_API_BASE_URL || `${window.location.origin}/api`).replace(/\/$/, '');
    } catch {
        return (import.meta.env.VITE_API_BASE_URL || `${window.location.origin}/api`).replace(/\/$/, '');
    }
})();
const accessToken = (() => {
    try {
        return window.localStorage.getItem('saey_api_token') || '';
    } catch {
        return '';
    }
})();

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST || window.location.hostname,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    authEndpoint: `${apiBaseUrl}/broadcasting/auth`,
    auth: {
        headers: accessToken ? { Authorization: `Bearer ${accessToken}` } : {},
    },
});

window.dispatchEvent(new Event('saey:echo-ready'));
