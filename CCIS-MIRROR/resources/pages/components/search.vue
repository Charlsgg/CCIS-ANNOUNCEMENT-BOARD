<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useTheme } from '../composable/usetheme' 

// Expanded to match your PHP backend's response
interface Attachment {
    id: string;
    file_path: string;
    file_type: string;
}

interface SearchResult {
    id: string;
    type: 'User' | 'Announcement' | 'Event';
    title: string;
    description: string;
    content?: string;
    url: string; 
    // Announcement specific
    author_name?: string;
    author_avatar?: string;
    date?: string;
    attachments?: Attachment[];
    // Event specific
    venue?: string;
    start_time?: string;
    end_time?: string;
}

const { theme, styles, isDark, initTheme } = useTheme()

const searchResults = ref<SearchResult[]>([])
const isLoading = ref(false)
const currentQuery = ref('')

// Modal State
const isModalOpen = ref(false)
const selectedResult = ref<SearchResult | null>(null)

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

// Modal Handlers
const openModal = (result: SearchResult) => {
    selectedResult.value = result
    isModalOpen.value = true
    document.body.style.overflow = 'hidden' // Prevent background scrolling
}

const closeModal = () => {
    isModalOpen.value = false
    document.body.style.overflow = 'auto'
    // Optional: wait for transition to finish before clearing data
    setTimeout(() => {
        selectedResult.value = null
    }, 300)
}

// Helper to format dates for events
const formatDateTime = (dateString?: string) => {
    if (!dateString) return ''
    return new Date(dateString).toLocaleString()
}

onMounted(() => {
    initTheme()
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
                <p :style="styles.textSecondary" class="text-lg">No results found.</p>
                <p :style="styles.textMuted" class="mt-2">Try searching within {{ theme.abbr }} records.</p>
            </div>
            
            <ul v-else class="space-y-4">
                <li v-for="result in searchResults" :key="result.id" 
                    :style="styles.cardBg"
                    class="p-5 rounded-xl transition-all shadow-sm border group hover:shadow-md cursor-pointer"
                    @click="openModal(result)">
                    
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
                    
                    <button class="inline-flex items-center text-sm font-bold transition-transform group-hover:translate-x-1"
                            :style="styles.iconColor">
                        View Details
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <Teleport to="body">
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6">
            <div class="absolute inset-0 bg-gray-900 bg-opacity-60 transition-opacity" @click="closeModal"></div>
            
            <div v-if="selectedResult" 
                 :style="styles.cardBg"
                 class="relative w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-xl shadow-2xl p-6 sm:p-8 transform transition-all border"
                 :class="isDark ? 'border-gray-700' : 'border-gray-200'">
                
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <span :style="styles.badge" class="text-[10px] uppercase tracking-widest font-bold px-2.5 py-1 rounded-md mb-2 inline-block">
                            {{ selectedResult.type }}
                        </span>
                        <h3 :style="styles.textPrimary" class="text-2xl font-bold mt-1">
                            {{ selectedResult.title }}
                        </h3>
                    </div>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors p-1">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div :style="styles.textSecondary" class="text-sm mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
                    <template v-if="selectedResult.type === 'Announcement'">
                        <div class="flex items-center gap-2">
                            <img v-if="selectedResult.author_avatar" :src="selectedResult.author_avatar" class="w-6 h-6 rounded-full object-cover">
                            <span>Posted by <strong :style="styles.textPrimary">{{ selectedResult.author_name }}</strong> &bull; {{ selectedResult.date }}</span>
                        </div>
                    </template>
                    <template v-else-if="selectedResult.type === 'Event'">
                        <p>📍 <strong>Venue:</strong> {{ selectedResult.venue }}</p>
                        <p class="mt-1">🗓️ <strong>Starts:</strong> {{ formatDateTime(selectedResult.start_time) }}</p>
                    </template>
                </div>

                <div :style="styles.textPrimary" 
                     class="prose prose-sm sm:prose-base dark:prose-invert max-w-none mb-6"
                     v-html="selectedResult.content || selectedResult.description">
                </div>

                <div v-if="selectedResult.attachments && selectedResult.attachments.length > 0" 
                     class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <h4 :style="styles.textPrimary" class="text-sm font-bold mb-3">Attachments</h4>
                    <div class="flex flex-wrap gap-2">
                        <a v-for="file in selectedResult.attachments" 
                           :key="file.id"
                           :href="file.file_path" 
                           target="_blank"
                           class="inline-flex items-center px-3 py-1.5 text-sm rounded-lg border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                           :style="styles.textPrimary">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                            View File
                        </a>
                    </div>
                </div>
                
            </div>
        </div>
    </Teleport>
</template>