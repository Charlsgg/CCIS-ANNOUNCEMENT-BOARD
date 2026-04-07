import { createApp } from 'vue'
import axios from 'axios'

declare global {
    interface Window {
        axios: typeof axios;
    }
}

// 2. SET UP AXIOS FOR VERCEL & SANCTUM
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.withCredentials = true; // Send secure cookies
window.axios.defaults.baseURL = '/api';       // Force routes to use /api

// 3. Imports
import Login from '../pages/authpages/login.vue'
import Home from '../pages/body/home-page.vue'
import Events from '../pages/body/events-page.vue'
import Announcements from '../pages/body/announcement-page.vue'
import Profile from '../pages/body/profile-page.vue'
import PublicBoard from '../pages/boards/publicboard.vue' 
import PublicEvents from '../pages/boards/publicevents.vue'
import Signup from '../pages/authpages/signup.vue'
import ForgotPassword from '../pages/authpages/forgot-password.vue'

const el = document.getElementById('app')

if (el) {
    const page = el.dataset.page
    const user = JSON.parse(el.dataset.user || '{}')

    if (page === 'announcement-board') {
        createApp(PublicBoard).mount('#app')
    } else if (page === 'announcements-events') {
        createApp(PublicEvents).mount('#app')
    } else if (page === 'home-page') {
        createApp(Home, { user }).mount('#app')
    } else if (page === 'events-page') {
        createApp(Events, { user }).mount('#app')
    } else if (page === 'announcement-page') {
        createApp(Announcements, { user }).mount('#app')
    } else if (page === 'profile-page') {
        createApp(Profile, { user }).mount('#app')
    } else if (page === 'signup') {
        createApp(Signup).mount('#app')
    } else if (page === 'forgot-password') {
        createApp(ForgotPassword).mount('#app')
    } else {
        createApp(Login).mount('#app')
    }
}