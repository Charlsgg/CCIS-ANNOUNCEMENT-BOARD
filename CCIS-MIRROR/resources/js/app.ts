import { createApp, Component } from 'vue'
import axios from 'axios'

declare global {
    interface Window {
        axios: typeof axios;
        csrfToken?: string; // Adding this to the global interface
    }
}

// 1. SET UP AXIOS
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.withCredentials = true; 
window.axios.defaults.baseURL = '/api';

// 2. Imports
import Login from '../pages/authpages/login.vue'
import Home from '../pages/body/home-page.vue'
import Events from '../pages/body/events-page.vue'
import Announcements from '../pages/body/announcement-page.vue'
import Profile from '../pages/body/profile-page.vue'
import PublicBoard from '../pages/boards/publicboard.vue' 
import PublicEvents from '../pages/boards/publicevents.vue'
import Signup from '../pages/authpages/signup.vue'
import ForgotPassword from '../pages/authpages/forgot-password.vue'

const el = document.getElementById('app');

if (el) {
    const page = el.dataset.page;
    const user = JSON.parse(el.dataset.user || '{}');
    const csrfToken = (window as any).csrfToken; // Grab token from window

    // Helper function to mount with global props
    const mountApp = (component: Component, props = {}) => {
        createApp(component, { 
            ...props, 
            csrfToken: csrfToken // Passes token to every page
        }).mount('#app');
    };

    // 3. Routing Logic
    if (page === 'announcement-board') {
        mountApp(PublicBoard);
    } else if (page === 'announcements-events') {
        mountApp(PublicEvents);
    } else if (page === 'home-page') {
        mountApp(Home, { user });
    } else if (page === 'events-page') {
        mountApp(Events, { user });
    } else if (page === 'announcement-page') {
        mountApp(Announcements, { user });
    } else if (page === 'profile-page') {
        mountApp(Profile, { user });
    } else if (page === 'signup') {
        mountApp(Signup);
    } else if (page === 'forgot-password') {
        mountApp(ForgotPassword);
    } else {
        mountApp(Login);
    }
}