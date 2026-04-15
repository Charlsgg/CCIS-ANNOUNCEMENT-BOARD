<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useTheme } from '../composable/usetheme' 

interface SearchResult {
    id: string;
    type: 'User' | 'Announcement' | 'Event';
    title: string;
    description: string;
    url: string; // Original URL from backend
}

const { theme, styles, isDark, initTheme } = useTheme()

const searchResults = ref<SearchResult[]>([])
const isLoading = ref(false)
const currentQuery = ref('')

// Mapping search types to your theme's specific paths
const getDynamicUrl = (type: string) => {
    switch (type) {
        case 'Event': return theme.value.eventsPath;
        case 'Announcement': return theme.value.announcementPath;
        case 'User': return theme.value.profilePath;
        default: return '#';
    }
}

const performGlobalSearch = async (query: string) => {
    if (!query) return
    isLoading.value = true
    try {
        const response = await fetch(`/global-search?q=${encodeURIComponent(query)}`, {
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
        isLoading.value = false
    }
}

onMounted(() => {
    initTheme() // Ensure theme is loaded from localStorage
    const urlParams = new URLSearchParams(window.location.search);
    const q = urlParams.get('q');
    if (q) {
        currentQuery.value = q;
        performGlobalSearch(q);
    }
})
</script>

<template>
    <div :style="styles.pageBg" class="min-h-screen p-8 transition-colors duration-300">
        <div class="max-w-4xl mx-auto w-full">
            
            <h1 :style="styles.textPrimary" class="text-2xl font-bold mb-6">
                Search Results for 
                <span :style="styles.iconColor">"{{ currentQuery }}"</span>
            </h1>
            
            <div v-if="isLoading" class="flex items-center justify-center py-12">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2" :style="{ borderColor: theme.accent }"></div>
                <span :style="styles.textSecondary" class="ml-3">Searching {{ theme.label }}...</span>
            </div>
            
            <div v-else-if="searchResults.length === 0" class="text-center py-12">
                <p :style="styles.textSecondary" class="text-lg">No results found for "{{ currentQuery }}".</p>
                <p :style="styles.textMuted" class="mt-2">Try searching within {{ theme.abbr }} records.</p>
            </div>
            
            <ul v-else class="space-y-4">
                <li v-for="result in searchResults" :key="result.id" 
                    :style="styles.cardBg"
                    class="p-5 rounded-xl transition-all shadow-sm border group hover:shadow-md">
                    
                    <span :style="styles.badge" class="text-[10px] uppercase tracking-widest font-bold px-2.5 py-1 rounded-md mb-3 inline-block">
                        {{ result.type }}
                    </span>
                    
                    <h2 :style="styles.textPrimary" class="text-xl font-semibold mb-2">
                        {{ result.title }}
                    </h2>
                    
                    <p :style="styles.textSecondary" class="text-sm line-clamp-2 mb-4">
                        {{ result.description }}
                    </p>
                    
                    <hr :style="styles.cardDivider" class="mb-4" />
                    
                    <a :href="getDynamicUrl(result.type)" 
                       class="inline-flex items-center text-sm font-bold transition-transform hover:translate-x-1"
                       :style="styles.iconColor">
                        View in {{ theme.abbr }} {{ result.type }}s
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</template>