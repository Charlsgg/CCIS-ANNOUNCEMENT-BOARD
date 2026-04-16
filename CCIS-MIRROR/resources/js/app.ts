import { createApp, h, Component } from 'vue'
import axios from 'axios'

declare global {
    interface Window {
        axios: typeof axios;
        csrfToken?: string; 
    }
}

// 1. SET UP AXIOS
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.withCredentials = true; 
window.axios.defaults.baseURL = '/api';

// 2. Imports
import MainLayout from '../pages/layout/mainlayout.vue' 
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
    const csrfToken = (window as any).csrfToken; 

    // Helper 1: Mount standalone pages (NO navbar/sidebar)
    const mountApp = (component: Component, props = {}) => {
        createApp(component, { 
            ...props, 
            csrfToken: csrfToken 
        }).mount('#app');
    };

    // Helper 2: Mount pages INSIDE the MainLayout automatically
    const mountWithLayout = (component: Component, props: any = {}) => {
        createApp({
            render() {
                // This tells Vue to wrap the requested page inside MainLayout's <slot />
                return h(MainLayout, { user: props.user }, {
                    default: () => h(component, { ...props, csrfToken: csrfToken })
                })
            }
        }).mount('#app');
    };

    // 3. Routing Logic
    // --- NO LAYOUT PAGES ---
    if (page === 'announcements-board') {
        mountApp(PublicBoard);
    } else if (page === 'announcements-events') {
        mountApp(PublicEvents);
    } else if (page === 'signup') {
        mountApp(Signup);
    } else if (page === 'forgot-password') {
        mountApp(ForgotPassword);
    } else if (page === 'login') {
        mountApp(Login);
    } 
    // --- PAGES WITH LAYOUT (Uses mountWithLayout) ---
    else if (page === 'home-page') {
        mountWithLayout(Home, { user });
    } else if (page === 'events-page') {
        mountWithLayout(Events, { user });
    } else if (page === 'announcement-page') {
        mountWithLayout(Announcements, { user });
    } else if (page === 'profile-page') {
        mountWithLayout(Profile, { user });
    } else {
        mountApp(Login); 
    }
}