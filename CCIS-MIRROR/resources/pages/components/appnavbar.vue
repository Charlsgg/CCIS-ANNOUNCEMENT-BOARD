<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Terminal, Bell, Menu, Sun, Moon, Search } from 'lucide-vue-next' // Added Search icon
import { useTheme } from '../composable/usetheme.ts'

// Emits
const emit = defineEmits<{
    toggleSidebar: []
    search: [query: string] // Added optional search emit
}>()

// Composables
const { theme, styles, surface, isDark, toggleMode } = useTheme()

// Local State
const userName = ref('')
const userAvatar = ref('')
const imageHasError = ref(false)
const searchQuery = ref('') // Added search state

// Triggered when user presses Enter in the search bar
const handleSearch = () => {
    if (searchQuery.value.trim()) {
        emit('search', searchQuery.value)
    }
}

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
            
            console.log("Navbar API Data:", data)
            
            const userData = data.user ? data.user : data
            
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

        <div class="flex-1 max-w-md mx-4 md:mx-8 hidden sm:block">
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none" 
                     :style="{ color: surface.textSecondary }">
                    <Search :size="18" />
                </div>
                <input
                    v-model="searchQuery"
                    @keyup.enter="handleSearch"
                    type="text"
                    class="block w-full py-2 pl-10 pr-4 text-sm rounded-lg transition-all outline-none focus:ring-2 focus:ring-opacity-50"
                    :style="{
                        backgroundColor: isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.03)',
                        color: surface.textPrimary,
                        '--tw-ring-color': theme.accent
                    }"
                    placeholder="Search..."
                />
            </div>
        </div>

        <div class="flex items-center gap-2 md:gap-4 shrink-0">
            <button class="sm:hidden p-2 rounded-lg transition-colors" :style="{ color: surface.textSecondary }">
                <Search :size="20" />
            </button>

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
                class="h-8 w-8 md:h-9 md:w-9 shrink-0 rounded-full flex items-center justify-center text-xs font-bold overflow-hidden cursor-pointer"
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