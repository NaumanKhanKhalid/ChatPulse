import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
window.Pusher = Pusher;

// Suppress noisy WebSocket retry logs in console
Pusher.logToConsole = false;

try {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST,
        wsPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
        wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https',
        enabledTransports: ['ws', 'wss'],
        disableStats: true,
        namespace: 'App.Events',
    });
} catch (e) {
    console.warn('Reverb WebSocket not available:', e.message);
    window.Echo = null;
}
