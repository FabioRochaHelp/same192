import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const key = import.meta.env.VITE_REVERB_APP_KEY;
const host = import.meta.env.VITE_REVERB_HOST ?? window.location.hostname;
const port = import.meta.env.VITE_REVERB_PORT ?? 8080;
const scheme = import.meta.env.VITE_REVERB_SCHEME ?? 'http';

if (key) {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key,
        wsHost: host,
        wsPort: port,
        wssPort: port,
        forceTLS: scheme === 'https',
        enabledTransports: ['ws', 'wss'],
    });
}
