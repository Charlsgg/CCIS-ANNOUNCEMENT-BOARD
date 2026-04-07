import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// 1. CRITICAL: Tell Axios to send secure cookies with every request
window.axios.defaults.withCredentials = true;

// 2. OPTIONAL BUT RECOMMENDED: Tell Axios to use your current Vercel domain automatically
window.axios.defaults.baseURL = '/';