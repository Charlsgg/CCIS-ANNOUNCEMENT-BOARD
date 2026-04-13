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

    // 1. RECOVERY LOGIC: If the path contains "https" twice, 
    // it means your backend accidentally prefixed it.
    // We will extract everything starting from the SECOND 'https'
    if ((cleanPath.match(/https/g) || []).length > 1) {
        const parts = cleanPath.split('https:/')
        // Grab the last part and fix the protocol
        cleanPath = 'https:/' + parts[parts.length - 1]
    }

    // 2. Strip out rogue Laravel "/storage/" or "storage/" prefixes
    cleanPath = cleanPath.replace(/^\/?storage\//, '')

    // 3. Fix the "https:/" vs "https://" issue (single vs double slash)
    cleanPath = cleanPath.replace(/^https?:\/([^\/])/, 'https://$1')

    // 4. If it's a valid absolute URL now, return it!
    if (cleanPath.startsWith('http')) {
        return cleanPath
    }

    // 5. Fallback for relative paths
    return `https://hahocarxbknajzqjacuk.supabase.co/storage/v1/object/public/avatars/${cleanPath}`
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