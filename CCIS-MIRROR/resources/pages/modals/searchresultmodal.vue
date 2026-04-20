<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useTheme } from '../composable/usetheme.ts'

interface Attachment {
    id: string | number;
    file_path: string;
    file_type: string;
}

export interface SearchResult {
    id: string;
    type: 'User' | 'Announcement' | 'Event';
    title: string;
    description: string;
    content?: string;
    author_name?: string;
    author_avatar?: string;
    date?: string;
    attachments?: Attachment[];
    venue?: string;
    start_time?: string;
    end_time?: string;
}

const props = defineProps<{
    show: boolean;
    result: SearchResult | null;
}>()

const emit = defineEmits<{
    (e: 'close'): void
}>()

const { theme, styles, surface, isDark } = useTheme()
const activePreview = ref<Attachment | null>(null)

watch(() => props.show, (newVal) => {
    if (newVal) {
        document.body.style.overflow = 'hidden'
    } else {
        document.body.style.overflow = 'auto'
    }
})

const closeModal = () => {
    emit('close')
    activePreview.value = null
}

const previewFile = (file: Attachment) => {
    activePreview.value = file
}

// --- Date Helpers ---
const getMonth = (dateStr?: string) => {
    if (!dateStr) return '---'
    const d = new Date(dateStr)
    return isNaN(d.getTime()) ? '---' : d.toLocaleDateString('en-US', { month: 'short' })
}

const getDay = (dateStr?: string) => {
    if (!dateStr) return '--'
    const d = new Date(dateStr)
    return isNaN(d.getTime()) ? '--' : d.getDate()
}

const formatFullDate = (dateStr?: string | null) => {
    if (!dateStr) return 'TBA'
    const d = new Date(dateStr)
    return isNaN(d.getTime()) ? dateStr : d.toLocaleDateString('en-US', {
        month: 'short', day: 'numeric', year: 'numeric'
    })
}

const formatTime = (dateStr?: string | null) => {
    if (!dateStr) return '--:--'
    const d = new Date(dateStr)
    return isNaN(d.getTime()) ? '' : d.toLocaleTimeString('en-US', {
        hour: '2-digit', minute: '2-digit'
    })
}

// --- Dynamic Data Mappers ---
const headerBadge = computed(() => {
    if (!props.result) return { top: '---', bottom: '--' }
    
    if (props.result.type === 'Event') {
        return {
            top: getMonth(props.result.start_time),
            bottom: getDay(props.result.start_time).toString()
        }
    } else {
        // For announcements, show author initial or 'ANN'
        return {
            top: 'POST',
            bottom: props.result.author_name?.charAt(0).toUpperCase() || 'i'
        }
    }
})

const metaDetails = computed(() => {
    const res = props.result;
    if (!res) return [];

    if (res.type === 'Event') {
        let endDateStr = 'TBA';
        let endTimeStr = '--:--';
        
        if (res.end_time) {
            const startStr = new Date(res.start_time || '').toDateString();
            const endStr = new Date(res.end_time).toDateString();
            endDateStr = startStr === endStr ? 'Same Day' : formatFullDate(res.end_time);
            endTimeStr = formatTime(res.end_time);
        }

        return [
            { label: 'Start', date: formatFullDate(res.start_time), sub: formatTime(res.start_time), icon: 'event_upcoming' },
            { label: 'Ends', date: endDateStr, sub: endTimeStr, icon: 'event_available' },
            { label: 'Venue', date: res.venue || 'TBA', sub: '', icon: 'location_on' }
        ];
    } else {
        return [
            { label: 'Posted By', date: res.author_name || 'System', sub: 'Author', icon: 'person' },
            { label: 'Date', date: res.date || 'Recently', sub: 'Published', icon: 'calendar_month' },
            { label: 'Record Type', date: 'Announcement', sub: 'Information', icon: 'campaign' }
        ];
    }
})
</script>

<template>
    <Teleport to="body">
        <Transition name="fade">
            <div v-if="show && result"
                class="fixed inset-0 z-[100] flex items-center justify-center p-2 sm:p-4 md:p-6 transition-all duration-300 backdrop-blur-sm"
                :style="{ backgroundColor: surface.overlayBg }">
                
                <div class="absolute inset-0" @click="closeModal"></div>

                <div class="relative w-full max-w-4xl h-[85vh] md:h-[40rem] rounded-xl shadow-2xl overflow-hidden flex flex-col border transition-all duration-300"
                    :style="[styles.cardBg, { borderColor: theme.accent + '20' }]">
                    
                    <div class="relative h-40 md:h-48 w-full flex items-end overflow-hidden shrink-0 transition-colors duration-500"
                        :style="{ backgroundColor: isDark ? theme.accent + 'e6' : theme.accent }">

                        <button @click="closeModal"
                            class="absolute top-3 right-3 md:top-4 md:right-4 z-20 w-8 h-8 md:w-10 md:h-10 flex items-center justify-center rounded-full bg-black/20 hover:bg-black/40 text-white transition-colors">
                            <span class="material-symbols-outlined text-lg md:text-xl">close</span>
                        </button>

                        <div class="relative z-10 p-4 md:p-6 flex items-center gap-4 w-full bg-gradient-to-t from-black/60 to-transparent">
                            <div class="flex flex-col items-center justify-center p-2 rounded-xl shadow-lg min-w-[4rem]"
                                :style="styles.cardBg">
                                <span class="text-[10px] md:text-xs font-bold uppercase tracking-wider"
                                    :style="{ color: theme.accent }">
                                    {{ headerBadge.top }}
                                </span>
                                <span class="text-xl md:text-2xl font-black" :style="styles.textPrimary">
                                    {{ headerBadge.bottom }}
                                </span>
                            </div>
                            
                            <div class="flex-1 min-w-0">
                                <nav class="flex gap-2 mb-1">
                                    <span class="px-2 py-0.5 rounded bg-white/20 text-white text-[9px] md:text-[10px] font-bold uppercase tracking-widest truncate">
                                        {{ result.type }}
                                    </span>
                                </nav>
                                <h1 class="text-white text-lg sm:text-xl md:text-2xl font-bold leading-tight drop-shadow-sm line-clamp-2">
                                    {{ result.title }}
                                </h1>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-1 overflow-hidden flex-col">
                        <main class="flex-1 overflow-y-auto custom-scrollbar p-4 md:p-6 bg-transparent">
                            
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 md:gap-4 mb-6 md:mb-8">
                                <div v-for="item in metaDetails" :key="item.label"
                                    class="p-3 md:p-4 rounded-xl border shadow-sm flex sm:block items-center sm:items-start gap-4 sm:gap-0 transition-shadow"
                                    :style="{ backgroundColor: surface.cardBg, borderColor: theme.accent + '1a' }">
                                    <div class="w-8 h-8 md:w-10 md:h-10 shrink-0 rounded-lg flex items-center justify-center sm:mb-2"
                                        :style="{ backgroundColor: theme.accent + '33' }">
                                        <span class="material-symbols-outlined text-[20px]"
                                            :style="{ color: theme.accent }">{{ item.icon }}</span>
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="text-[10px] font-bold uppercase tracking-widest mb-0.5"
                                            :style="styles.textMuted">
                                            {{ item.label }}
                                        </h4>
                                        <p class="font-bold text-sm leading-tight truncate" :style="styles.textPrimary">
                                            {{ item.date }}
                                        </p>
                                        <p class="text-[11px] font-medium mt-0.5 truncate"
                                            :style="{ color: theme.accent }">{{ item.sub }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="max-w-3xl pb-4">
                                <h3 class="text-lg font-bold mb-3 md:mb-4 flex items-center gap-2"
                                    :style="styles.textPrimary">
                                    Content Details
                                </h3>
                                <div class="prose dark:prose-invert max-w-none space-y-4">
                                    <div class="text-sm md:text-base leading-relaxed border-l-4 pl-4 whitespace-pre-wrap break-words"
                                        :style="{ color: isDark ? '#cbd5e1' : '#334155', borderColor: theme.accent + '4d' }"
                                        v-html="result.content || result.description || 'No additional information provided.'">
                                    </div>
                                </div>
                            </div>

                            <div v-if="result.attachments && result.attachments.length > 0" class="max-w-3xl pt-4">
                                <h3 class="text-lg font-bold mb-3 md:mb-4 flex items-center gap-2"
                                    :style="styles.textPrimary">
                                    Attachments
                                </h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                                    <div v-for="file in result.attachments" :key="file.id" @click="previewFile(file)"
                                        class="group relative rounded-xl overflow-hidden border transition-all duration-300 cursor-pointer shadow-sm hover:border-orange-500"
                                        :style="{ borderColor: theme.accent + '33', backgroundColor: surface.cardBg }">
                                        <img v-if="file.file_type.includes('image')" :src="file.file_path"
                                            class="w-full h-32 object-cover group-hover:scale-105 transition-transform duration-500" />
                                        <div v-else class="p-4 flex items-center gap-3 h-32 justify-center flex-col" :style="{ backgroundColor: isDark ? '#3f3f46' : '#f9fafb' }">
                                            <span class="material-symbols-outlined text-3xl group-hover:scale-110 transition-transform duration-300" :style="{ color: theme.accent }">
                                                {{ file.file_type.includes('pdf') ? 'picture_as_pdf' : 'description' }}
                                            </span>
                                            <p class="text-[10px] font-mono truncate w-full text-center px-2" :style="styles.textMuted">{{ file.file_path.split('/').pop() }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </main>
                    </div>
                </div>
            </div>
        </Transition>

        <Transition name="fade">
            <div v-if="activePreview" class="fixed inset-0 z-[110] flex items-center justify-center p-4 transition-all duration-300 backdrop-blur-md"
                 :style="{ backgroundColor: surface.overlayBg }">
                <button @click="activePreview = null"
                    class="absolute top-6 right-6 z-[120] w-12 h-12 flex items-center justify-center rounded-full transition-all hover:rotate-90 border shadow-sm"
                    :style="{ backgroundColor: surface.cardBg, borderColor: theme.accent + '33', color: surface.textPrimary }">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
                <div class="w-full h-full flex items-center justify-center">
                    <img v-if="activePreview.file_type.includes('image')" :src="activePreview.file_path"
                        class="max-w-full max-h-full object-contain animate-in zoom-in duration-300 shadow-xl border rounded-lg"
                        :style="{ borderColor: theme.accent + '33' }" />
                    <iframe v-else-if="activePreview.file_type.includes('pdf')" :src="activePreview.file_path"
                        class="w-full h-full md:w-[85%] md:h-[90%] rounded-2xl border shadow-2xl" 
                        :style="{ backgroundColor: surface.cardBg, borderColor: theme.accent + '33' }" frameborder="0"></iframe>
                    <div v-else class="text-center border p-12 rounded-3xl shadow-xl"
                         :style="{ backgroundColor: surface.cardBg, borderColor: theme.accent + '33' }">
                        <span class="material-symbols-outlined text-6xl mb-4 block" :style="{ color: theme.accent }">draft</span>
                        <p class="mb-8 font-light tracking-wide" :style="styles.textPrimary">Preview not available for this file type.</p>
                        <a :href="activePreview.file_path" download target="_blank"
                            class="inline-flex items-center gap-2 text-white px-8 py-3 rounded-xl font-bold transition-all duration-300 shadow-md hover:shadow-lg"
                            :style="{ backgroundColor: theme.accent }">
                            <span class="material-symbols-outlined text-[20px]">download</span>
                            Download File
                        </a>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

/* Safe handling of prose breaks */
.break-words {
    word-break: break-word;
    overflow-wrap: break-word;
}
</style>