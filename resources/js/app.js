import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from 'ziggy-js';
import Toast from 'vue-toastification';
import 'vue-toastification/dist/index.css';
<<<<<<< HEAD
import VueApexCharts from 'vue3-apexcharts';
=======
>>>>>>> 4cc2369dd6308ae1ae71aa0d33eaadf0ecc9b0cc

const appName = import.meta.env.VITE_APP_NAME || 'VMS';

createInertiaApp({
    title: (title) => `${appName}`,
    resolve: (name) => resolvePageComponent(
        `./Pages/${name}.vue`,
        import.meta.glob('./Pages/**/*.vue')
    ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(Toast, { position: 'top-right', timeout: 3000 })
<<<<<<< HEAD
            .use(VueApexCharts)
=======
>>>>>>> 4cc2369dd6308ae1ae71aa0d33eaadf0ecc9b0cc
            .mount(el);
    },
    progress: { color: '#3B82F6' },
});
