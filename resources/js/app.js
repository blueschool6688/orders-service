/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */
import { createApp } from 'vue';
import DefaultComponent from "./components/DefaultComponent";
import router from './router';
import store from './store';
import axios from 'axios';
import i18n from "./i18n";
import Toast from "vue-toastification";
import "vue-toastification/dist/index.css";
import VueSimpleAlert from "vue3-simple-alert";
import VueNextSelect from 'vue-next-select';
import 'vue-next-select/dist/index.css';
import ENV from './config/env';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';


/* Start tooltip alert code */
const options = {
    timeout: 2000,
    closeOnClick: true,
    pauseOnFocusLoss: true,
    pauseOnHover: true,
    draggable: true,
    draggablePercent: 0.6,
    showCloseButtonOnHover: false,
    hideProgressBar: false,
    closeButton: "button",
    icon: true,
    rtl: false
};
/* End tooltip alert code */


/* Start axios code*/
const API_URL = ENV.API_URL;
const API_KEY = ENV.API_KEY;

/* PUSHER & BROADCAST */
const PUSHER_APP_KEY = window.APP_CONFIG?.PUSHER_APP_KEY || 'ac31785db1e8f2394e9c';
const PUSHER_APP_CLUSTER = window.APP_CONFIG?.PUSHER_APP_CLUSTER || 'ap1'
const PUSHER_HOST = ENV.PUSHER_HOST
const PUSHER_PORT = ENV.PUSHER_PORT
const PUSHER_SCHEME = ENV.PUSHER_SCHEME
const BANK_NUMBER = ENV.BANK_NUMBER
const BANK_NAME = ENV.BANK_NAME
const BANK_PREFIX = ENV.BANK_PREFIX
const BANK_CONTENT = ENV.BANK_CONTENT
axios.defaults.baseURL = API_URL + '/api';
axios.interceptors.request.use(
    config => {
        config.headers['x-api-key'] = API_KEY;
        if (localStorage.getItem('vuex')) {
            const vuex = JSON.parse(localStorage.getItem('vuex'));
            const token = vuex.auth.authToken;
            const language = vuex.globalState.lists.language_code;
            config.headers['Authorization'] = token ? `Bearer ${token}` : '';
            config.headers['x-localization'] = language;
        }
        return config;
    },
    error => Promise.reject(error),
);
/* End axios code */

/* PUSHER & BROADCAST */

window.Pusher = Pusher;

;
// ... tương tự cho các biến khác

window.Echo = new Echo({
    broadcaster: 'pusher',
    cluster: PUSHER_APP_CLUSTER,
    key: PUSHER_APP_KEY,
    wsHost: window.APP_CONFIG?.PUSHER_HOST ?? `ws-${PUSHER_APP_CLUSTER}.pusher.com`,
    wsPort: window.APP_CONFIG?.PUSHER_PORT ?? 80,
    wssPort: window.APP_CONFIG?.PUSHER_PORT ?? 443,
    forceTLS: (window.APP_CONFIG?.PUSHER_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});



const app = createApp({});
app.component('default-component', DefaultComponent);
app.component('vue-select', VueNextSelect)
app.use(router)
app.use(store)
app.use(VueSimpleAlert)
app.use(Toast, options)
app.use(i18n)
app.mount('#app');
