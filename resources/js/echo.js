import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

console.log('window.reverbConfig =', window.reverbConfig);

const cfg = window.reverbConfig;

if (!cfg || !cfg.key) {
    throw new Error('reverbConfig missing or key missing');
}

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: cfg.key,
    wsHost: cfg.host,
    wsPort: Number(cfg.port),
    wssPort: Number(cfg.port),
    forceTLS: cfg.scheme === 'https',
    enabledTransports: ['ws', 'wss'],
});

console.log('Echo initialized');