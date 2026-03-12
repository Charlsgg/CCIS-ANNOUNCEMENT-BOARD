import { createApp } from 'vue'
import Login from './components/Login.vue'
import Dashboard from '../pages/body/announcement.vue'
import PublicBoard from './components/PublicBoard.vue' // Create this component

const el = document.getElementById('app')

if (el) {
    const page = el.dataset.page
    const user = JSON.parse(el.dataset.user || '{}')

    if (page === 'announcement-board') {
        createApp(PublicBoard).mount('#app')
    } else if (page === 'dashboard') {
        createApp(Dashboard, { user }).mount('#app')
    } else {
        createApp(Login).mount('#app')
    }
}