import './bootstrap';
import '../css/app.css';
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from 'ziggy-js';
import { Ziggy } from './ziggy'; // ← MODIFIÉ : Import nommé
import i18n from './i18n';

createInertiaApp({
    title: (title) => `${title} - S-Remind`,
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.vue');
        return resolvePageComponent(`./Pages/${name}.vue`, pages);
    },
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue, Ziggy) // ← MODIFIÉ : Passer Ziggy explicitement
            .use(i18n)
            .mount(el);
    },
    progress: {
        color: '#3b82f6',
    },
});