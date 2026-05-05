<script setup lang="ts">
import { ref } from 'vue'
import { useTheme } from '../composable/usetheme.ts'

const props = defineProps<{
    stats: { cs: number; it: number; is: number; lsg: number; all: number }
}>()

const emit = defineEmits(['filterChange'])
const { styles, surface } = useTheme()

// Track which filter is currently active
const activeFilter = ref<string | null>(null)

const filters = [
    { key: 'cs', label: 'Computer Science', color: 'bg-blue-500', role: 'cs_instructor' },
    { key: 'it', label: 'Information Technology', color: 'bg-emerald-500', role: 'it_instructor' },
    { key: 'is', label: 'Information Systems', color: 'bg-amber-500', role: 'is_instructor' },
    { key: 'lsg', label: 'CCIS LSG', color: 'bg-purple-500', role: 'lsg_officer' },
    
]

// Add a variable to hold the timer
let timeoutId: ReturnType<typeof setTimeout> | null = null

const setFilter = (role: string | null) => {
    // 1. Still prevent same-filter spam
    if (activeFilter.value === role) {
        return
    }

    // 2. Update the active filter immediately so the UI button highlights instantly
    activeFilter.value = role

    // 3. If they click another button while the timer is running, cancel the old timer
    if (timeoutId) {
        clearTimeout(timeoutId)
    }

    // 4. Start a new timer. Only emit the query if 300ms pass with NO clicks.
    timeoutId = setTimeout(() => {
        emit('filterChange', role) 
    }, 300) // 300 milliseconds is usually the sweet spot
}
</script>

<template>
    <div 
        class="rounded-xl p-6 shadow-sm flex flex-col transition-colors border"
        :style="{ backgroundColor: surface.cardBg, borderColor: surface.borderSubtle }"
    >
        <div class="flex justify-between items-center mb-5">
            <p class="text-[10px] font-black uppercase tracking-[0.2em]" :style="styles.textSecondary">
                Filter by Topic
            </p>
            <button 
                v-if="activeFilter"
                @click="setFilter(null)"
                class="text-[10px] font-bold text-red-500 hover:text-red-400 transition-colors uppercase tracking-wider"
            >
                Clear
            </button>
        </div>
        
        <div class="space-y-1">
            <button 
                @click="setFilter(null)"
                class="w-full flex items-center justify-between px-3 py-2.5 text-sm rounded-lg transition-all group"
                :class="[
                    activeFilter === null 
                    ? 'bg-black/5 dark:bg-white/10 ring-1 ring-inset ring-black/5 dark:ring-white/10' 
                    : 'hover:bg-black/5 dark:hover:bg-white/5'
                ]"
                :style="{ color: surface.textPrimary }"
            >
                <span class="flex items-center gap-3">
                    <span class="size-2 rounded-full bg-slate-400"></span>
                    <span class="font-medium">All Announcements</span>
                </span>
                <span 
                    class="px-2 py-0.5 rounded text-[10px] font-bold border" 
                    :style="{ backgroundColor: surface.inputBg, borderColor: surface.borderSubtle, color: surface.textSecondary }"
                >
                    {{ stats.all }}
                </span>
            </button>

            <button 
                v-for="filter in filters" 
                :key="filter.key"
                @click="setFilter(filter.role)"
                class="w-full flex items-center justify-between px-3 py-2.5 text-sm rounded-lg transition-all group"
                :class="[
                    activeFilter === filter.role 
                    ? 'bg-black/10 dark:bg-white/10 ring-1 ring-inset ring-black/5 dark:ring-white/20' 
                    : 'hover:bg-black/5 dark:hover:bg-white/5'
                ]"
                :style="{ color: surface.textPrimary }"
            >
                <span class="flex items-center gap-3">
                    <span class="size-2 rounded-full" :class="filter.color"></span>
                    <span 
                        class="font-medium transition-transform"
                        :class="{ 'translate-x-1': activeFilter === filter.role }"
                    >
                        {{ filter.label }}
                    </span>
                </span>
                
                <span 
                    class="px-2 py-0.5 rounded text-[10px] font-bold border" 
                    :style="{ 
                        backgroundColor: activeFilter === filter.role ? surface.cardBg : surface.inputBg, 
                        borderColor: surface.borderSubtle,
                        color: activeFilter === filter.role ? surface.textPrimary : surface.textSecondary 
                    }"
                >
                    {{ stats[filter.key as keyof typeof stats] ?? 0 }}
                </span>
            </button>
        </div>
    </div>
</template>