import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const vitePort = Number(env.VITE_PORT || 5173);
    const devServerHost = env.VITE_DEV_SERVER_HOST || 'localhost';
    const devServerProtocol = env.VITE_DEV_SERVER_PROTOCOL || 'http';
    const hmrHost = env.VITE_HMR_HOST || '';
    const baseDomain = (env.APP_BASE_DOMAIN || '')
        .trim()
        .toLowerCase()
        .replace(/^\.+/, '');
    const additionalAllowedHosts = (env.VITE_ALLOWED_HOSTS || '')
        .split(',')
        .map((host) => host.trim().toLowerCase())
        .filter(Boolean);
    const allowedHosts = Array.from(new Set([
        ...(baseDomain ? [`.${baseDomain}`] : []),
        ...additionalAllowedHosts,
    ]));

    return {
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.js'],
                refresh: true,
            }),
            vue({
                template: {
                    transformAssetUrls: {
                        base: null,
                        includeAbsolute: false,
                    },
                },
            }),
            tailwindcss(),
        ],
        server: {
            host: '0.0.0.0',
            port: vitePort,
            strictPort: true,
            allowedHosts,
            cors: true,
            origin: `${devServerProtocol}://${devServerHost}:${vitePort}`,
            hmr: {
                clientPort: vitePort,
                ...(hmrHost ? { host: hmrHost } : {}),
            },
            watch: { usePolling: true },
        },
    };
});
