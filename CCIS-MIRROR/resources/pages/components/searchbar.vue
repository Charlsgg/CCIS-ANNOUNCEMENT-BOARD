<script setup lang="ts">
import { ref } from 'vue'

defineProps<{
    surface: Record<string, any>
}>()

const emit = defineEmits<{
    (e: 'search', filters: Record<string, string>): void
}>()

const searchQuery = ref('')
const searchType = ref('title') 

const executeSearch = () => {
    const filters: Record<string, string> = {}
    
    if (searchQuery.value.trim() !== '') {
        filters[searchType.value] = searchQuery.value.trim()
    }
    
    emit('search', filters)
}
</script>

<template>
    <div class="relative group">
        <div 
            class="flex items-center rounded-lg px-3 py-1.5 transition-shadow gap-2 border border-transparent focus-within:ring-1 focus-within:ring-opacity-50 focus-within:ring-[var(--focus-ring-color)]"
            :style="{ 
                backgroundColor: surface.hoverBg, 
                '--focus-ring-color': surface.textPrimary 
            }"
        >
            <span class="material-symbols-outlined text-sm opacity-60" :style="{ color: surface.textPrimary }">search</span>
            
            <select 
                v-model="searchType"
                @change="executeSearch"
                class="bg-transparent border-none text-sm font-display outline-none cursor-pointer opacity-80 hover:opacity-100 focus:ring-0 py-0 pl-1 pr-6"
                :style="{ color: surface.textPrimary }"
            >
                <option value="title" class="text-black dark:text-white">Event Title</option>
                <option value="user_name" class="text-black dark:text-white">User Name</option>
            </select>

            <div class="h-4 w-px opacity-20" :style="{ backgroundColor: surface.textPrimary }"></div>

            <input 
                v-model="searchQuery"
                @keyup.enter="executeSearch"
                class="bg-transparent border-none focus:ring-0 text-sm w-full md:w-48 font-display outline-none py-0" 
                :placeholder="searchType === 'title' ? 'Search events...' : 'Search users...'" 
                type="text"
                :style="{ color: surface.textPrimary }"
            />
        </div>
    </div>
</template>