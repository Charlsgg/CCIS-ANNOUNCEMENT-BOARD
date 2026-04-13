<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Terminal, Bell, Menu, Sun, Moon } from 'lucide-vue-next'
import { useTheme } from '../composable/usetheme.ts'

// Emits
const emit = defineEmits<{
    toggleSidebar: []
}>()

// Composables
const { theme, styles, surface, isDark, toggleMode } = useTheme()

// Local State
const userName = ref('')
const userAvatar = ref('')
const imageHasError = ref(false)

const getFileUrl = (path?: string | null) => {
    if (!path) return ''

    let cleanPath = path

    // 1. Log what we received from the DB
    console.log("Raw path from DB:", path)

    cleanPath = cleanPath.replace(/^\/?storage\//, '')
    cleanPath = cleanPath.replace(/^https?:\/([^\/])/, 'https://$1')

    if (cleanPath.startsWith('http')) {
        return cleanPath
    }

    // 2. This is the path we are building
    const finalUrl = `https://hahocarxbknajzqjacuk.supabase.co/storage/v1/object/public/avatars/${cleanPath}`
    console.log("Constructed URL:", finalUrl)
    
    return finalUrl
}
// --- Methods ---
const fetchUserData = async () => {
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        
        const response = await fetch('/api/navbar/user', {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        
        if (response.ok) {
            const data = await response.json()
            
            // 1. Let's see exactly what the backend sent in your console (F12)
            console.log("Navbar API Data:", data)
            
            // 2. BULLETPROOF FIX: Check if it's wrapped in '.user' or if it's direct
            const userData = data.user ? data.user : data
            
            // 3. Safely assign the values (the ? prevents crashes if a field is missing)
            userName.value = userData?.name || 'Unknown User'
            userAvatar.value = userData?.profile_picture || null
            
        } else {
            console.error('Failed to fetch user data:', response.statusText)
        }
    } catch (error) {
        console.error('Network error loading navbar data:', error)
    }
}

// Lifecycle Hooks
onMounted(() => {
    fetchUserData()
})
</script>

<template>
    <header
        class="shrink-0 flex items-center justify-between px-4 md:px-8 py-3 md:py-4 z-30"
        :style="styles.headerBg"
    >
        <div class="flex items-center gap-3">
            <button
                @click="emit('toggleSidebar')"
                class="md:hidden p-2 -ml-2 rounded-lg transition-colors"
                :style="{ color: surface.textSecondary }"
                @mouseenter="(e: MouseEvent) => {
                    const el = e.currentTarget as HTMLElement
                    el.style.backgroundColor = surface.hoverBg
                    el.style.color = surface.textPrimary
                }"
                @mouseleave="(e: MouseEvent) => {
                    const el = e.currentTarget as HTMLElement
                    el.style.backgroundColor = 'transparent'
                    el.style.color = surface.textSecondary
                }"
            >
                <Menu :size="24" />
            </button>
        </div>

        <div class="flex items-center gap-2 md:gap-4">
            <button
                @click="toggleMode"
                class="p-2 rounded-lg transition-colors"
                :style="{ color: surface.textSecondary }"
                @mouseenter="(e: MouseEvent) => {
                    const el = e.currentTarget as HTMLElement
                    el.style.backgroundColor = surface.hoverBg
                    el.style.color = theme.accent
                }"
                @mouseleave="(e: MouseEvent) => {
                    const el = e.currentTarget as HTMLElement
                    el.style.backgroundColor = 'transparent'
                    el.style.color = surface.textSecondary
                }"
                :title="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
            >
                <Sun v-if="isDark" :size="20" />
                <Moon v-else :size="20" />
            </button>
            
            <div
                class="h-8 w-8 md:h-9 md:w-9 shrink-0 rounded-full flex items-center justify-center text-xs font-bold overflow-hidden"
                :style="styles.avatar"
            >
                <img 
                    v-if="userAvatar && !imageHasError" 
                    :src="getFileUrl(userAvatar)" 
                    :alt="userName || 'User'"
                    class="h-full w-full object-cover"
                    @error="imageHasError = true"
                />
                <span v-else>
                    {{ userName?.charAt(0) || 'U' }}
                </span>
            </div>
        </div>
    </header>
</template>