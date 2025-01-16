import axios from "axios";
import ENV from '../config/env';
const API_URL = ENV.API_URL;
const API_KEY = ENV.API_KEY;

const SsoAxios = axios.create({
    baseURL: `${API_URL}/api`,
    headers: {
        "x-api-key": API_KEY,
    },
});
SsoAxios.interceptors.request.use(
    (config) => {
        if (localStorage.getItem('vuex')) {
            const vuex = JSON.parse(localStorage.getItem('vuex'));
            const token = vuex.auth.clientToken;
            const language = vuex.globalState.lists.language_code;

            // config.headers["Authorization"] = token ? `Bearer ${token}` : '';
            config.headers["x-localization"] = language;
        }
        return config;
    },
    (error) => Promise.reject(error)
);

export default SsoAxios;
