<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { Bell, Menu, Sun, Moon, Search, Loader2, X } from 'lucide-vue-next'
import { useTheme } from '../composable/usetheme.ts'
import SearchResultModal, { type SearchResult } from '../modals/searchresultmodal.vue'

const emit = defineEmits<{
    toggleSidebar: []
}>()
const props = defineProps<{
    userName?: string;
}>()
const { theme, styles, surface, isDark, toggleMode } = useTheme()

// --- INSTANT LOAD: Grab from localStorage first to prevent flashing ---
const userName = ref(localStorage.getItem('cached_user_name') || '')
const userAvatar = ref(localStorage.getItem('cached_profile_pic') || '')
const imageHasError = ref(false)

// --- Search & Mobile State ---
const searchQuery = ref('') 
const searchResults = ref<SearchResult[]>([])
const isSearching = ref(false)
const showDropdown = ref(false)
const isMobileSearchOpen = ref(false) 
const searchContainerRef = ref<HTMLElement | null>(null)
let debounceTimeout: ReturnType<typeof setTimeout> | null = null

// --- Modal State ---
const isModalOpen = ref(false)
const selectedResult = ref<SearchResult | null>(null)

// --- Methods ---
const openModal = (result: SearchResult) => {
    selectedResult.value = result
    isModalOpen.value = true
    showDropdown.value = false // Hide the dropdown when modal opens
}

const handleInput = () => {
    if (debounceTimeout) clearTimeout(debounceTimeout)

    if (!searchQuery.value.trim()) {
        searchResults.value = []
        showDropdown.value = false
        isSearching.value = false
        return
    }

    showDropdown.value = true
    isSearching.value = true

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

const handleClickOutside = (event: MouseEvent) => {
    if (isModalOpen.value) return; 

    if (searchContainerRef.value && !searchContainerRef.value.contains(event.target as Node)) {
        showDropdown.value = false
        isMobileSearchOpen.value = false 
    }
}

const getFileUrl = (path?: string | null) => {
    if (!path) return ''
    let cleanPath = path
    if ((cleanPath.match(/https/g) || []).length > 1) {
        const parts = cleanPath.split('https:/')
        cleanPath = 'https:/' + parts[parts.length - 1]
    }
    cleanPath = cleanPath.replace(/^\/?storage\//, '')
    cleanPath = cleanPath.replace(/^https?:\/([^\/])/, 'https://$1')
    if (cleanPath.startsWith('http')) return cleanPath
    return `https://hahocarxbknajzqjacuk.supabase.co/storage/v1/object/public/avatars/${cleanPath}`
}

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
            
            const fetchedName = userData?.name || 'Unknown User'
            const fetchedAvatar = userData?.profile_picture || ''
            const fetchedId = userData?.id || userData?.email || fetchedName 
            const cachedId = localStorage.getItem('cached_user_id')

            if (cachedId && cachedId !== String(fetchedId)) {
                localStorage.removeItem('cached_user_name')
                localStorage.removeItem('cached_profile_pic')
            }

            userName.value = fetchedName
            userAvatar.value = fetchedAvatar

            localStorage.setItem('cached_user_id', String(fetchedId))
            localStorage.setItem('cached_user_name', fetchedName)
            if (fetchedAvatar) {
                localStorage.setItem('cached_profile_pic', fetchedAvatar)
            }
        }
    } catch (error) {
        console.error('Network error loading navbar data:', error)
    }
}

onMounted(() => {
    fetchUserData()
    document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
})
</script>

<template>
    <header class="relative shrink-0 flex items-center justify-between px-4 md:px-8 py-3 md:py-4 z-30 transition-colors duration-300" :style="styles.headerBg">
        
        <div class="flex items-center gap-3">
            <button
                @click="emit('toggleSidebar')"
                class="md:hidden p-2 -ml-2 rounded-lg transition-colors"
                :style="{ color: surface.textSecondary }"
            >
                <Menu :size="24" />
            </button>
        </div>

        <div 
            class="flex-1 max-w-xl mx-auto absolute sm:relative top-full sm:top-auto left-0 sm:left-auto w-full sm:w-auto px-4 sm:px-4 md:px-8 py-2 sm:py-0 z-50 transition-all duration-200 border-b sm:border-none"
            :class="[isMobileSearchOpen ? 'block' : 'hidden sm:block']"
            :style="[isMobileSearchOpen ? styles.headerBg : {}, { borderColor: isDark ? '#3f3f46' : '#e4e4e7' }]"
            ref="searchContainerRef"
        >
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none" :style="{ color: surface.textSecondary }">
                    <Search :size="18" />
                </div>
                <input
                    v-model="searchQuery"
                    @input="handleInput"
                    @focus="handleInput"
                    type="text"
                    class="block w-full py-2.5 pl-10 pr-4 text-sm rounded-lg transition-all border outline-none focus:ring-4 shadow-lg sm:shadow-none"
                    :style="{
                        backgroundColor: isDark ? '#27272a' : '#f4f4f5',
                        color: surface.textPrimary,
                        borderColor: isDark ? '#3f3f46' : '#e4e4e7',
                        '--tw-ring-color': theme.accent + '50',
                        caretColor: theme.accent
                    }"
                    placeholder="Search announcements, events..."
                />
            </div>

            <div 
                v-if="showDropdown" 
                class="absolute w-[calc(100%-2rem)] sm:w-full mt-2 rounded-xl shadow-2xl overflow-hidden z-50 flex flex-col border transition-colors duration-300"
                :style="styles.cardBg"
                style="max-height: 400px;"
            >
                <div v-if="isSearching" class="p-4 flex items-center justify-center">
                    <Loader2 class="animate-spin mr-2" :size="18" :style="{ color: theme.accent }" />
                    <span class="text-sm" :style="{ color: surface.textSecondary }">Searching Records...</span>
                </div>

                <div v-else-if="searchResults.length === 0 && searchQuery.trim()" class="p-4 text-center text-sm" :style="{ color: surface.textMuted }">
                    No results found in {{ theme.label }}
                </div>

                <div v-else class="overflow-y-auto overflow-x-hidden p-2 space-y-1 custom-scrollbar">
                    <button type="button"
                        v-for="result in searchResults" 
                        :key="result.id"
                        @click="openModal(result)"
                        class="w-full text-left block px-3 py-3 rounded-lg transition-all group"
                        :style="{ '--hover-bg': isDark ? '#3f3f46' : '#e4e4e7' }"
                        onmouseover="this.style.backgroundColor=this.style.getPropertyValue('--hover-bg')"
                        onmouseout="this.style.backgroundColor='transparent'"
                    >
                        <div class="flex flex-col">
                            <span class="text-[9px] font-black uppercase tracking-widest mb-1 px-1.5 py-0.5 rounded w-fit" 
                                :style="styles.badge">
                                {{ result.type }}
                            </span>
                            <span class="font-semibold text-sm transition-colors" 
                                :style="{ color: surface.textPrimary }"
                                :onmouseover="`this.style.color='${theme.accent}'`"
                                :onmouseout="`this.style.color='${surface.textPrimary}'`">
                                {{ result.title }}
                            </span>
                            <span class="text-xs truncate mt-0.5" :style="{ color: surface.textMuted }">
                                {{ result.description }}
                            </span>
                        </div>
                    </button>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2 md:gap-4 shrink-0">
            <button
                @click.stop="isMobileSearchOpen = !isMobileSearchOpen"
                class="sm:hidden p-2 rounded-lg transition-colors"
                :style="{ color: surface.textSecondary }"
                onmouseover="this.style.backgroundColor='rgba(155,155,155,0.1)'"
                onmouseout="this.style.backgroundColor='transparent'"
            >
                <X v-if="isMobileSearchOpen" :size="20" />
                <Search v-else :size="20" />
            </button>

            <button
                @click="toggleMode"
                class="p-2 rounded-lg transition-colors"
                :style="{ color: surface.textSecondary }"
                onmouseover="this.style.backgroundColor='rgba(155,155,155,0.1)'"
                onmouseout="this.style.backgroundColor='transparent'"
            >
                <Sun v-if="isDark" :size="20" />
                <Moon v-else :size="20" />
            </button>
            
            <a :href="theme.profilePath" class="h-8 w-8 md:h-9 md:w-9 shrink-0 rounded-full flex items-center justify-center text-xs font-bold overflow-hidden cursor-pointer ring-2 transition-all hover:scale-105" 
                :style="{ ...styles.avatar, '--tw-ring-color': surface.borderSubtle }">
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
            </a>
        </div>
    </header>

    <SearchResultModal 
        :show="isModalOpen" 
        :result="selectedResult" 
        @close="isModalOpen = false" 
    />
</template>

<style scoped>
input:focus {
    border-color: v-bind('theme.accent');
    box-shadow: 0 0 0 4px v-bind('theme.accent + "50"');
}

.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: rgba(155, 155, 155, 0.5);
    border-radius: 10px;
}
</style>