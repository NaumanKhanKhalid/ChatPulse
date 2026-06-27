
console.log('ENV =', import.meta.env);
console.log('KEY =', import.meta.env.VITE_REVERB_APP_KEY);
console.log('HOST =', import.meta.env.VITE_REVERB_HOST);
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

try {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST,
        wsPort: Number(import.meta.env.VITE_REVERB_PORT || 443),
        wssPort: Number(import.meta.env.VITE_REVERB_PORT || 443),
        forceTLS: import.meta.env.VITE_REVERB_SCHEME === 'https',
        enabledTransports: ['ws', 'wss'],
    });

    console.log('Echo initialized', window.Echo);

} catch (e) {
    console.error('FULL ERROR:', e);
}