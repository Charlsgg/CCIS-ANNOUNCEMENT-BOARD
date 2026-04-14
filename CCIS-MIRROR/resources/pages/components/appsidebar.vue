<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Calendar, User, Home, Megaphone, LogOut, X } from 'lucide-vue-next'
import { useTheme } from '../composable/usetheme'

const emit = defineEmits<{
    close: []
}>()

const { theme, styles, surface } = useTheme()
const currentPath = ref('')

// Fallback logic: Use the prop if available, otherwise look for the window global we set in app.ts
const props = defineProps<{
    isOpen: boolean
    csrfToken?: string 
}>()

// Improved logic to find the token
const effectiveToken = computed(() => {
    return props.csrfToken || 
           window.csrfToken || 
           document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
           '';
})

onMounted(() => {
    currentPath.value = window.location.pathname
})

const navItems = computed(() => [
    { name: 'Home', icon: Home, path: theme.value.homePath },
    { name: 'Events', icon: Calendar, path: theme.value.eventsPath },
    { name: 'Announcements', icon: Megaphone, path: theme.value.announcementPath },
    { name: 'Profile', icon: User, path: theme.value.profilePath },
])
</script>

<template>
    <div v-if="isOpen" @click="emit('close')"
        class="fixed inset-0 z-40 bg-black/50 md:hidden transition-opacity duration-300"></div>

    <aside :class="[
        'fixed inset-y-0 left-0 z-50 w-64 flex flex-col transition-transform duration-300 ease-in-out md:relative md:translate-x-0',
        isOpen ? 'translate-x-0 shadow-2xl shadow-black/50' : '-translate-x-full',
    ]" :style="styles.sidebarBg">
        <div class="p-6 flex flex-col h-full">

            <button @click="emit('close')" class="md:hidden absolute top-6 right-6 transition-colors"
                :style="{ color: surface.textMuted }">
                <X :size="24" />
            </button>

            <div class="flex items-center gap-3 mb-8 mt-2 md:mt-0">
                <div class="h-10 w-10 shrink-0 rounded-full flex items-center justify-center border shadow-lg overflow-hidden"
                    :style="styles.badge">
                    <img :src="theme.image" :alt="theme.abbr" class="w-full h-full object-contain" />
                </div>
                
                <h1 class="text-sm font-bold uppercase tracking-wide leading-tight" :style="styles.textPrimary">
                    {{ theme.label }}
                </h1>
            </div>

            <nav class="flex-1 flex flex-col gap-1 overflow-y-auto pr-2 -mr-2">
                <div class="mt-2 flex flex-col gap-1.5">
                    <a v-for="item in navItems" :key="item.name" :href="item.path"
                        class="relative flex items-center gap-3 px-4 py-3 rounded-lg text-sm transition-all duration-200 cursor-pointer overflow-hidden group"
                        :style="currentPath === item.path
                            ? {
                                backgroundColor: `${theme.primary}1A`,
                                color: theme.primary,
                                fontWeight: 700
                            }
                            : {
                                color: surface.textSecondary,
                                fontWeight: 500
                            }" 
                        @mouseenter="(e: MouseEvent) => {
                            if (currentPath !== item.path) {
                                const el = e.currentTarget as HTMLElement
                                el.style.backgroundColor = surface.hoverBg
                                el.style.color = surface.textPrimary
                            }
                        }" 
                        @mouseleave="(e: MouseEvent) => {
                            if (currentPath !== item.path) {
                                const el = e.currentTarget as HTMLElement
                                el.style.backgroundColor = 'transparent'
                                el.style.color = surface.textSecondary
                            }
                        }">
                        
                        <div v-if="currentPath === item.path" class="absolute left-0 top-0 bottom-0 w-1.5 z-10"
                            :style="{ backgroundColor: theme.primary || '#f97316' }"></div>

                        <component :is="item.icon" :size="20" class="shrink-0 z-20" />

                        <span class="tracking-wide z-20">
                            {{ item.name }}
                        </span>
                    </a>
                </div>
            </nav>

            <div class="mt-auto pt-6 shrink-0" :style="{ borderTop: `1px solid ${surface.borderSubtle}` }">
                <form action="/logout" method="POST">
                    <input type="hidden" name="_token" :value="effectiveToken" />
                    
                    <button type="submit"
                        class="flex items-center gap-3 px-4 py-3 w-full rounded-lg text-red-400 hover:bg-red-500/10 hover:text-red-300 font-medium transition-all border border-transparent hover:border-red-500/20">
                        <LogOut :size="20" /> Logout
                    </button>
                </form>
            </div>
        </div>
    </aside>
</template>