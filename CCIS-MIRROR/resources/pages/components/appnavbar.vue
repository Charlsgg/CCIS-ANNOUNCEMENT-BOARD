<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { Terminal, Bell, Menu, Sun, Moon, Search, Loader2 } from 'lucide-vue-next'
import { useTheme } from '../composable/usetheme.ts'

const emit = defineEmits<{
    toggleSidebar: []
}>()

const { theme, styles, surface, isDark, toggleMode } = useTheme()

const userName = ref('')
const userAvatar = ref('')
const imageHasError = ref(false)

// --- Search State ---
const searchQuery = ref('') 
const searchResults = ref<any[]>([])
const isSearching = ref(false)
const showDropdown = ref(false)
const searchContainerRef = ref<HTMLElement | null>(null)
let debounceTimeout: ReturnType<typeof setTimeout> | null = null

// --- Methods ---

// Triggers automatically as the user types
const handleInput = () => {
    // Clear previous timeout if user is still typing
    if (debounceTimeout) clearTimeout(debounceTimeout)

    // If input is empty, close dropdown and clear results
    if (!searchQuery.value.trim()) {
        searchResults.value = []
        showDropdown.value = false
        isSearching.value = false
        return
    }

    showDropdown.value = true
    isSearching.value = true

    // Wait 300ms after user stops typing before calling the API
    debounceTimeout = setTimeout(async () => {
        try {
            const response = await fetch(`/global-search?q=${encodeURIComponent(searchQuery.value)}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            
            if (response.ok) {
                const data = await response.json()
                searchResults.value = data.results
            }
        } catch (error) {
            console.error("Search API error:", error)
        } finally {
            isSearching.value = false
        }
    }, 300)
}

// Close dropdown when clicking outside of it
const handleClickOutside = (event: MouseEvent) => {
    if (searchContainerRef.value && !searchContainerRef.value.contains(event.target as Node)) {
        showDropdown.value = false
    }
}

// File URL Helper
const getFileUrl = (path?: string | null) => {
    if (!path) return ''
    let cleanPath = path
    if ((cleanPath.match(/https/g) || []).length > 1) {
        const parts = cleanPath.split('https:/')
        cleanPath = 'https:/' + parts[parts.length - 1]
    }
    cleanPath = cleanPath.replace(/^\/?storage\//, '')
    cleanPath = cleanPath.replace(/^https?:\/([^\/])/, 'https://$1')
    if (cleanPath.startsWith('http')) {
        return cleanPath
    }
    return `https://hahocarxbknajzqjacuk.supabase.co/storage/v1/object/public/avatars/${cleanPath}`
}

// User Data Fetcher
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
            const userData = data.user ? data.user : data
            userName.value = userData?.name || 'Unknown User'
            userAvatar.value = userData?.profile_picture || null
        }
    } catch (error) {
        console.error('Network error loading navbar data:', error)
    }
}

// Lifecycle Hooks
onMounted(() => {
    fetchUserData()
    document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
})
</script>

<template>
    <header class="shrink-0 flex items-center justify-between px-4 md:px-8 py-3 md:py-4 z-30" :style="styles.headerBg">
        <div class="flex items-center gap-3">
            <button
                @click="emit('toggleSidebar')"
                class="md:hidden p-2 -ml-2 rounded-lg transition-colors"
                :style="{ color: surface.textSecondary }"
            >
                <Menu :size="24" />
            </button>
        </div>

        <div class="flex-1 max-w-xl mx-4 md:mx-8 hidden sm:block relative" ref="searchContainerRef">
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none" :style="{ color: surface.textSecondary }">
                    <Search :size="18" />
                </div>
                <input
                    v-model="searchQuery"
                    @input="handleInput"
                    @focus="handleInput"
                    type="text"
                    class="block w-full py-2.5 pl-10 pr-4 text-sm rounded-lg transition-all outline-none focus:ring-2 focus:ring-opacity-50 border border-transparent focus:border-gray-300 dark:focus:border-gray-600"
                    :style="{
                        backgroundColor: isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.03)',
                        color: surface.textPrimary,
                        '--tw-ring-color': theme.accent
                    }"
                    placeholder="Search users, announcements, events..."
                />
            </div>

            <div 
                v-if="showDropdown" 
                class="absolute w-full mt-2 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-100 dark:border-gray-700 overflow-hidden z-50 flex flex-col"
                style="max-height: 400px;"
            >
                <div v-if="isSearching" class="p-4 flex items-center justify-center text-gray-500">
                    <Loader2 class="animate-spin mr-2" :size="18" />
                    <span class="text-sm">Searching...</span>
                </div>

                <div v-else-if="searchResults.length === 0 && searchQuery.trim()" class="p-4 text-center text-gray-500 text-sm">
                    No results found for "{{ searchQuery }}"
                </div>

                <div v-else class="overflow-y-auto overflow-x-hidden p-2 space-y-1">
                    <a 
                        v-for="result in searchResults" 
                        :key="result.id"
                        :href="result.url"
                        class="block px-3 py-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group"
                    >
                        <div class="flex flex-col">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400 mb-1">
                                {{ result.type }}
                            </span>
                            <span class="font-medium text-gray-900 dark:text-white text-sm group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                {{ result.title }}
                            </span>
                            <span class="text-xs text-gray-500 dark:text-gray-400 truncate mt-0.5">
                                {{ result.description }}
                            </span>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2 md:gap-4 shrink-0">
            <button
                @click="toggleMode"
                class="p-2 rounded-lg transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
                :style="{ color: surface.textSecondary }"
                :title="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
            >
                <Sun v-if="isDark" :size="20" />
                <Moon v-else :size="20" />
            </button>
            
            <div class="h-8 w-8 md:h-9 md:w-9 shrink-0 rounded-full flex items-center justify-center text-xs font-bold overflow-hidden cursor-pointer" :style="styles.avatar">
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