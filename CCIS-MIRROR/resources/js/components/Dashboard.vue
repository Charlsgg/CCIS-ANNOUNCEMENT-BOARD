<script lang="ts">
export default { layout: null }
</script>

<script setup lang="ts">
import { ref, shallowRef, onMounted } from 'vue'
// REMOVED: Quill imports

import { 
    Terminal, Bell, User, Home, Megaphone, LogOut, 
    Send, Menu, X 
} from 'lucide-vue-next'

const props = defineProps<{
    user?: { name: string; email: string; user_type: string }
}>()

const isSidebarOpen = ref(false)

// Composer State
const newTitle = ref('')
const newContent = ref('')
const announcements = ref<any[]>([])
const csrfToken = ref('')

onMounted(() => {
    fetchAnnouncements()
    const tokenTag = window.document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement
    if (tokenTag) {
        csrfToken.value = tokenTag.content
    }
})

const fetchAnnouncements = async () => {
    try {
        const response = await fetch('/api/announcements')
        if (response.ok) {
            const data = await response.json()
            announcements.value = data.map((post: any) => ({
                id: post.announcement_id,
                title: post.title,
                content: post.content,
                date: new Date(post.created_at).toLocaleDateString(undefined, { 
                    month: 'short', day: 'numeric', year: 'numeric' 
                }),
                icon: shallowRef(Megaphone)
            }))
        }
    } catch (error) {
        console.error("Error fetching announcements:", error)
    }
}

const postAnnouncement = async () => {
    // Simplified validation since it's now a plain textarea
    if (!newTitle.value.trim() || !newContent.value.trim()) {
        alert('Please enter both a title and a message.')
        return
    }

    try {
        const response = await fetch('/api/announcements', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken.value
            },
            body: JSON.stringify({
                title: newTitle.value,
                content: newContent.value,
                board_id: 1
            })
        })

        if (response.ok) {
            const savedPost = await response.json()
            announcements.value.unshift({
                id: savedPost.announcement_id,
                title: savedPost.title,
                content: savedPost.content,
                date: 'Just now',
                icon: shallowRef(Megaphone)
            })
            newTitle.value = ''
            newContent.value = ''
        } else {
            const errorData = await response.json()
            alert(errorData.message || 'Failed to save announcement.')
        }
    } catch (error) {
        console.error("Error posting announcement:", error)
        alert('A network error occurred.')
    }
}
</script>

<template>
    <div class="fixed inset-0 w-full h-full overflow-hidden bg-[#2a1c15] font-['Space_Grotesk'] text-slate-100 flex">
        
        <div v-if="isSidebarOpen" @click="isSidebarOpen = false" class="absolute inset-0 bg-black/80 z-40 md:hidden backdrop-blur-sm transition-opacity"></div>

        <aside :class="['absolute inset-y-0 left-0 z-50 w-64 bg-[#221610] border-r border-[#ec5b13]/10 flex flex-col transition-transform duration-300 ease-in-out md:relative md:translate-x-0', isSidebarOpen ? 'translate-x-0 shadow-2xl shadow-black/50' : '-translate-x-full']">
            <div class="p-6 flex flex-col h-full">
                <button @click="isSidebarOpen = false" class="md:hidden absolute top-6 right-6 text-slate-500 hover:text-white transition-colors">
                    <X :size="24" />
                </button>

                <div class="flex items-center gap-3 mb-8 mt-2 md:mt-0">
                    <div class="h-10 w-10 shrink-0 rounded-full bg-[#ec5b13]/20 flex items-center justify-center border border-[#ec5b13]/30 shadow-lg shadow-[#ec5b13]/10">
                        <span class="text-[#ec5b13] font-black text-sm tracking-wider">ISS</span>
                    </div>
                    <h1 class="text-sm font-bold text-white uppercase tracking-wide leading-tight">Information System Society</h1>
                </div>

                <nav class="flex-1 flex flex-col gap-1 overflow-y-auto pr-2 -mr-2">
                    <div class="mt-2">
                        <div class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-[#ec5b13] text-white shadow-lg shadow-[#ec5b13]/20 mb-2">
                            <User :size="20" />
                            <span class="text-sm font-bold tracking-wide">PROFILE</span>
                        </div>
                        <div class="ml-5 mt-2 flex flex-col gap-1.5 border-l border-[#ec5b13]/20 pl-4 py-1">
                            <a href="#" class="flex items-center gap-3 px-3 py-2 text-slate-300 hover:text-white hover:bg-[#ec5b13]/10 rounded-lg text-sm font-medium transition-colors">
                                <Home :size="18" /> Home
                            </a>
                            <a href="#" class="flex items-center gap-3 px-3 py-2 text-slate-300 hover:text-white hover:bg-[#ec5b13]/10 rounded-lg text-sm font-medium transition-colors">
                                <Megaphone :size="18" /> Announcements
                            </a>
                        </div>
                    </div>
                </nav>

                <div class="mt-auto pt-6 border-t border-[#ec5b13]/10 shrink-0">
                    <form action="/logout" method="POST">
                        <input type="hidden" name="_token" :value="csrfToken">
                        <button type="submit" class="flex items-center gap-3 px-4 py-3 w-full rounded-lg text-red-400 hover:bg-red-500/10 hover:text-red-300 font-medium transition-all border border-transparent hover:border-red-500/20">
                            <LogOut :size="20" /> Logout
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <main class="flex-1 flex flex-col h-full overflow-hidden min-w-0">
            <header class="shrink-0 flex items-center justify-between px-4 md:px-8 py-3 md:py-4 border-b border-[#ec5b13]/10 bg-[#221610] z-30">
                <div class="flex items-center gap-3">
                    <button @click="isSidebarOpen = true" class="md:hidden text-slate-300 hover:text-white p-2 -ml-2 hover:bg-[#ec5b13]/20 rounded-lg transition-colors">
                        <Menu :size="24" />
                    </button>
                    <div class="p-2 rounded-lg bg-[#ec5b13]/10 text-[#ec5b13] hidden sm:flex shadow-inner">
                        <Terminal :size="20" />
                    </div>
                    <h2 class="text-lg md:text-xl font-bold tracking-tight text-white truncate">ISS Dashboard</h2>
                </div>
                
                <div class="flex items-center gap-3 md:gap-5">
                    <button class="p-2 rounded-lg hover:bg-[#ec5b13]/10 transition-colors text-slate-400 hover:text-[#ec5b13] relative">
                        <Bell :size="20" />
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-[#ec5b13]"></span>
                    </button>
                    <div class="h-8 w-8 md:h-9 md:w-9 shrink-0 rounded-full bg-[#ec5b13] flex items-center justify-center text-xs font-bold text-white shadow-md shadow-[#ec5b13]/20 border border-white/10">
                        {{ user?.name?.charAt(0) || 'U' }}
                    </div>
                </div>
            </header>

            <div class="flex-1 overflow-y-auto p-4 md:p-8 w-full">
                <div class="max-w-250 mx-auto pb-12">
                    
                    <div class="mb-6 md:mb-8 flex flex-col gap-2">
                        <h3 class="text-2xl md:text-3xl font-bold tracking-tight text-white">Information System Announcements</h3>
                        <p class="text-slate-400 text-sm md:text-base max-w-2xl">Stay updated with the latest academic updates.</p>
                    </div>

                    <div class="mb-10 rounded-2xl bg-[#221610] border border-[#ec5b13]/30 shadow-lg shadow-black/20 overflow-hidden flex flex-col focus-within:border-[#ec5b13] transition-colors p-4 md:p-6">
                        
                        <input 
                            v-model="newTitle"
                            type="text" 
                            placeholder="Announcement Title" 
                            class="w-full bg-transparent border-none focus:ring-0 text-white text-xl md:text-2xl font-bold placeholder-slate-600 px-0 pb-4 outline-none border-b border-[#ec5b13]/10 mb-4"
                        />

                        <textarea 
                            v-model="newContent"
                            placeholder="What's the latest update?"
                            class="w-full min-h-32 bg-transparent border-none focus:ring-0 text-white text-base placeholder-slate-600 outline-none resize-none"
                        ></textarea>
                        
                        <div class="mt-6 flex flex-col sm:flex-row items-end justify-end gap-4 border-t border-[#ec5b13]/10 pt-4">
                            <button @click="postAnnouncement" class="w-full sm:w-auto px-8 py-2.5 bg-[#ec5b13] hover:bg-[#d44c0b] text-white text-sm font-bold rounded-lg transition-all shadow-lg shadow-[#ec5b13]/20 flex items-center justify-center gap-2">
                                Post Announcement
                                <Send :size="16" />
                            </button>
                        </div>
                    </div>

                    <div class="flex flex-col gap-6">
                        <div v-if="announcements.length === 0" class="text-center py-12 text-slate-500 italic">
                            No announcements posted yet.
                        </div>
                        <div 
                            v-for="post in announcements" 
                            :key="post.id"
                            class="p-5 md:p-6 rounded-2xl bg-[#221610] border border-[#ec5b13]/10 hover:border-[#ec5b13]/30 transition-all flex flex-col sm:flex-row gap-5 md:gap-6 group"
                        >
                            <div class="h-16 w-16 md:h-20 md:w-20 rounded-xl bg-[#2a1c15] border border-[#ec5b13]/20 shrink-0 flex items-center justify-center text-[#ec5b13] group-hover:scale-105 transition-transform shadow-inner">
                                <component :is="post.icon" :size="32" />
                            </div>
                            
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-3 gap-1 border-b border-[#ec5b13]/10 pb-2">
                                    <h5 class="text-base md:text-xl font-bold text-white truncate">{{ post.title }}</h5>
                                    <span class="text-xs font-medium text-slate-400 shrink-0">{{ post.date }}</span>
                                </div>
                                
                                <div 
                                    class="text-slate-300 text-sm mb-4 leading-relaxed px-0 py-0 whitespace-pre-wrap"
                                    v-html="post.content"
                                ></div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </main>
    </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap');

/* REMOVED ALL QUILL SPECIFIC STYLING */
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

.whitespace-pre-wrap { white-space: pre-wrap; }
</style>