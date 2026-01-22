/**
 * plugins/index.ts
 *
 * Automatically included in `./src/main.ts`
 */

// Plugins
import pinia from '@/stores';
import router from '@/router';
import { PerfectScrollbarPlugin } from 'vue3-perfect-scrollbar';
import axiosInstance from './axios';

// Types
import type { App } from 'vue';

export function registerPlugins(app: App) {
  app
    .use(router)
    .use(pinia)
    .provide('axios', axiosInstance)
    .use(PerfectScrollbarPlugin)
}
