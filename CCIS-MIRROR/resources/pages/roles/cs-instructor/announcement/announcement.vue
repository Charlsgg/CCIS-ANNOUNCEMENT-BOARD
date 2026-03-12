<script setup lang="ts">
import { ref, shallowRef, onMounted } from 'vue'
import { Megaphone, Send } from 'lucide-vue-next'
import Layout from "../../../components/layout.vue"; 
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
    <Layout :user="$attrs.user"> <div class="max-w-250 mx-auto pb-12">
            
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
    </Layout>
</template>