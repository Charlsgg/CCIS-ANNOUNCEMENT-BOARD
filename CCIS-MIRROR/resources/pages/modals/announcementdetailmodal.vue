<script setup lang="ts">
import { ref, watch } from 'vue'
import axios from 'axios'

interface Attachment {
    id: number
    file_path: string
    file_type: string
}

interface Announcement {
    id: number
    title: string
    content: string
    topic: string
    author_name: string
    author_type: string
    announcement_date: string
    attachments: Attachment[]
}

const props = defineProps<{
    show: boolean
    announcementId: number
}>()

const emit = defineEmits<{
    (e: 'close'): void
}>()

const announcement = ref<Announcement | null>(null)
const loading = ref(false)
const activePreview = ref<Attachment | null>(null)

watch(() => props.show, async (newVal) => {
    if (newVal && props.announcementId) {
        loading.value = true
        try {
            const response = await axios.get('search/detail', {
                params: { type: 'Announcement', id: props.announcementId }
            })
            announcement.value = response.data
        } catch (e) {
            console.error("Error loading announcement:", e)
        } finally {
            loading.value = false
        }
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

const getPosBg = (type: string) => ({
    'it_instructor': 'bg-blue-600',
    'is_instructor': 'bg-green-600',
    'cs_instructor': 'bg-red-600',
    'lsg_officer': 'bg-amber-600'
}[type] || 'bg-orange-500')
</script>

<template>
    <Teleport to="body">
        <Transition name="modal-fade">
            <div v-if="show" class="fixed inset-0 z-100 flex items-center justify-center p-4 md:p-6">
                <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm" @click="closeModal"></div>

                <div v-if="loading" class="relative w-full max-w-4xl rounded-3xl bg-white p-12 flex items-center justify-center">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-500"></div>
                </div>

                <div v-else-if="announcement"
                    class="relative w-full max-w-4xl rounded-3xl border border-gray-200 shadow-2xl flex flex-col max-h-[90vh] overflow-hidden modal-scale bg-white">
                    <div class="absolute top-[-20%] left-[-10%] w-[60%] h-[60%] bg-orange-200/40 rounded-full blur-[100px] pointer-events-none"></div>
                    <div class="absolute bottom-[-20%] right-[-10%] w-[60%] h-[60%] bg-blue-200/40 rounded-full blur-[100px] pointer-events-none"></div>

                    <div class="flex items-center justify-between px-8 py-6 border-b border-gray-100 relative z-10 bg-white/80 backdrop-blur-sm">
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-8 bg-orange-500 rounded-full shadow-[0_0_10px_rgba(249,115,22,0.3)]"></div>
                            <h3 class="text-3xl font-light tracking-tight text-gray-900">
                                Announcement <span class="text-orange-500 font-bold">Details</span>
                            </h3>
                        </div>
                        <button @click="closeModal"
                            class="text-gray-400 hover:text-orange-500 hover:rotate-90 transition-all duration-300 rounded-full p-2 hover:bg-gray-100 shrink-0">
                            <span class="material-symbols-outlined block text-[24px]">close</span>
                        </button>
                    </div>

                    <div class="flex-1 overflow-y-auto p-8 md:p-10 custom-scrollbar space-y-10">
                        <h1 class="text-3xl md:text-5xl font-bold tracking-tighter leading-tight text-gray-900 wrap-break-word">
                            {{ announcement.title }}
                        </h1>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex items-center gap-4 bg-gray-50 px-5 py-4 rounded-2xl border border-gray-100">
                                <div :class="['w-12 h-12 rounded-xl flex items-center justify-center font-bold shadow-sm text-white', getPosBg(announcement.author_type)]">
                                    <span class="text-xl">{{ announcement.author_name?.substring(0, 1) }}</span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-[10px] uppercase tracking-widest text-orange-500 font-bold">Posted By</span>
                                    <span class="text-gray-900 font-medium">{{ announcement.author_name }}</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-4 bg-gray-50 px-5 py-4 rounded-2xl border border-gray-100">
                                <div class="bg-blue-100 p-3 rounded-xl">
                                    <span class="material-symbols-outlined text-[24px] text-blue-600 block">info</span>
                                </div>
                                <div class="flex flex-col w-full">
                                    <div class="flex justify-between items-center w-full mb-1">
                                        <span class="text-[10px] uppercase tracking-widest text-blue-600 font-bold">Subject</span>
                                        <span class="font-bold text-gray-800 text-[10px] px-2 py-0.5 rounded bg-white border border-gray-200">{{ announcement.topic }}</span>
                                    </div>
                                    <div class="w-full h-px bg-gray-200 my-1.5"></div>
                                    <div class="flex justify-between items-center w-full">
                                        <span class="text-[10px] uppercase tracking-widest text-gray-500 font-bold">Date</span>
                                        <span class="font-medium text-gray-700 text-xs">{{ announcement.announcement_date }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-6 md:p-8 rounded-2xl border border-gray-200">
                            <h5 class="text-[11px] font-bold tracking-widest uppercase text-gray-500 mb-4 flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                Message Content
                            </h5>
                            <div class="prose wrap-break-word max-w-none text-gray-800 leading-relaxed text-[15px] prose-p:mb-4 prose-a:text-orange-500" v-html="announcement.content"></div>
                        </div>

                        <div v-if="announcement.attachments?.length" class="space-y-4">
                            <div class="flex items-center justify-center mb-6 mt-8">
                                <div class="h-px bg-linear-to-r from-transparent via-orange-500/30 to-transparent w-full"></div>
                            </div>
                            <h5 class="text-[11px] font-bold tracking-widest uppercase text-gray-500 mb-4 flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                Attachments
                            </h5>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div v-for="file in announcement.attachments" :key="file.id" @click="previewFile(file)"
                                    class="group relative rounded-2xl overflow-hidden border border-gray-200 bg-white hover:border-orange-500 transition-all duration-300 cursor-pointer shadow-sm">
                                    <img v-if="file.file_type.includes('image')" :src="file.file_path"
                                        class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-700" />
                                    <div v-else class="p-6 flex items-center gap-4 h-48 justify-center flex-col bg-gray-50">
                                        <span class="material-symbols-outlined text-4xl text-orange-500 group-hover:scale-110 transition-transform duration-300">
                                            {{ file.file_type.includes('pdf') ? 'picture_as_pdf' : 'description' }}
                                        </span>
                                        <p class="text-[10px] font-mono text-gray-600 truncate w-full text-center px-4">{{ file.file_path.split('/').pop() }}</p>
                                    </div>
                                    <div class="absolute inset-0 bg-white/60 backdrop-blur-sm opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity duration-300">
                                        <span class="bg-white text-gray-900 border border-gray-200 p-3 rounded-full material-symbols-outlined shadow-md">visibility</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="px-8 py-5 border-t border-gray-200 bg-gray-50 flex justify-end">
                        <button @click="closeModal"
                            class="px-8 py-3 rounded-xl bg-white border border-gray-200 hover:bg-orange-500 hover:border-orange-500 hover:shadow-[0_0_15px_rgba(249,115,22,0.3)] hover:text-white transition-all duration-300 text-sm font-bold tracking-wide flex items-center gap-2 text-gray-700">
                            <span>Close</span>
                            <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- File Preview Modal -->
        <Transition name="fade">
            <div v-if="activePreview" class="fixed inset-0 z-110 flex items-center justify-center bg-white/95 backdrop-blur-md p-4">
                <button @click="activePreview = null"
                    class="absolute top-6 right-6 z-120 w-12 h-12 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 text-gray-800 transition-all hover:rotate-90 border border-gray-200 shadow-sm">
                    <span class="material-symbols-outlined">close</span>
                </button>
                <div class="w-full h-full flex items-center justify-center">
                    <img v-if="activePreview.file_type.includes('image')" :src="activePreview.file_path"
                        class="max-w-full max-h-full object-contain animate-in zoom-in duration-300 shadow-xl border border-gray-200 rounded-lg" />
                    <iframe v-else-if="activePreview.file_type.includes('pdf')" :src="activePreview.file_path"
                        class="w-full h-full md:w-[85%] md:h-[90%] rounded-2xl border border-gray-200 bg-white shadow-2xl" frameborder="0"></iframe>
                    <div v-else class="text-center bg-white border border-gray-200 p-12 rounded-3xl backdrop-blur-xl shadow-xl">
                        <span class="material-symbols-outlined text-6xl text-orange-500 mb-4 block">draft</span>
                        <p class="text-gray-600 mb-8 font-light tracking-wide">Preview not available for this file type.</p>
                        <a :href="activePreview.file_path" download target="_blank"
                            class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 hover:shadow-[0_0_15px_rgba(249,115,22,0.3)] text-white px-8 py-3 rounded-xl font-bold transition-all duration-300">
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
.custom-scrollbar::-webkit-scrollbar { width: 4px; height: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.3s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
