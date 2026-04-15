<script setup lang="ts">
import { ref, onMounted } from 'vue'

interface SearchResult {
    id: string;
    type: string;
    title: string;
    description: string;
    url: string;
}

const searchResults = ref<SearchResult[]>([])
const isLoading = ref(false)
const currentQuery = ref('') // We store the query here instead of route.query.q

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
        } else {
            console.error("Failed to fetch results:", response.statusText)
        }
    } catch (error) {
        console.error("Search API error:", error)
    } finally {
        isLoading.value = false
    }
}

onMounted(() => {
    // 1. Grab the URL search parameters natively
    const urlParams = new URLSearchParams(window.location.search);
    const q = urlParams.get('q');
    
    // 2. If a query exists, run the search
    if (q) {
        currentQuery.value = q;
        performGlobalSearch(q);
    }
})
</script>

<template>
    <div class="p-8 max-w-4xl mx-auto w-full">
        <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-white">
            Search Results for "<span class="text-blue-600 dark:text-blue-400">{{ currentQuery }}</span>"
        </h1>
        
        <div v-if="isLoading" class="flex items-center justify-center py-12">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
            <span class="ml-3 text-gray-500 dark:text-gray-400">Searching the database...</span>
        </div>
        
        <div v-else-if="searchResults.length === 0" class="text-center py-12">
            <p class="text-gray-500 dark:text-gray-400 text-lg">No results found for "{{ currentQuery }}".</p>
            <p class="text-gray-400 dark:text-gray-500 mt-2">Try checking your spelling or using different keywords.</p>
        </div>
        
        <ul v-else class="space-y-4">
            <li v-for="result in searchResults" :key="result.id" 
                class="p-5 border border-gray-200 dark:border-gray-700 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors shadow-sm">
                
                <span class="text-xs font-bold px-2.5 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full mb-3 inline-block">
                    {{ result.type }}
                </span>
                
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                    {{ result.title }}
                </h2>
                
                <p class="text-sm text-gray-600 dark:text-gray-300 mt-2 line-clamp-2">
                    {{ result.description }}
                </p>
                
                <a :href="result.url" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 hover:underline text-sm font-medium mt-3 inline-block">
                    View Profile &rarr;
                </a>
            </li>
        </ul>
    </div>
</template>