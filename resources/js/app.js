import './bootstrap';
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';

// 1. Look for the special Inertia div injected by the @inertia directive
const inertiaElement = document.getElementById('app');

// 2. Only ignite the Vue engine if we are inside a Tenant Vault
if (inertiaElement) {
    createInertiaApp({
        resolve: name => {
            const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
            return pages[`./Pages/${name}.vue`];
        },
        setup({ el, App, props, plugin }) {
            createApp({ render: () => h(App, props) })
                .use(plugin)
                .mount(el);
        },
    });
}
