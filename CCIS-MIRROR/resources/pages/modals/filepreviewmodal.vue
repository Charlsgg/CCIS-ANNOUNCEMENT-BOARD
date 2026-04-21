<script setup lang="ts">
import { watch } from 'vue'

const props = defineProps<{
    file: any | null
}>()

const emit = defineEmits<{
    (e: 'close'): void
}>()

// Prevent body scrolling when modal is open
watch(() => props.file, (newVal) => {
    document.body.style.overflow = newVal ? 'hidden' : 'auto'
})

// URL and Type Handlers (Same universal logic)
const getFileUrl = (path?: string | null) => {
    if (!path) return '#'
    let cleanPath = path.replace(/^\/?storage\//, '').replace(/^https?:\/([^\/])/, 'https://$1').replace(/^announcements\//, '').replace(/^\/+/, '')
    if (cleanPath.startsWith('http')) return cleanPath
    return `https://hahocarxbknajzqjacuk.supabase.co/storage/v1/object/public/announcements/${cleanPath}`
}

const isImage = (type: string | null) => type ? type.startsWith('image/') || /\.(jpg|jpeg|png|webp|gif)$/i.test(type) : false
const isPdf = (type: string | null) => type ? type === 'application/pdf' || /\.(pdf)$/i.test(type) : false
</script>

<template>
    <Teleport to="body">
        <Transition name="fade">
            <div v-if="file"
                 class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/95 backdrop-blur-md p-4"
                 @click.self="$emit('close')">

                <button @click="$emit('close')"
                        class="absolute top-6 right-6 z-[10000] w-12 h-12 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white transition-all shadow-xl hover:rotate-90">
                    <span class="material-symbols-outlined">close</span>
                </button>

                <div class="w-full h-full flex items-center justify-center pointer-events-none">
                    
                    <img v-if="isImage(file.file_type)" :src="getFileUrl(file.file_path)"
                         class="max-w-full max-h-full object-contain pointer-events-auto shadow-2xl rounded-sm animate-in zoom-in duration-300" />

                    <iframe v-else-if="isPdf(file.file_type)" :src="getFileUrl(file.file_path)"
                            class="w-full h-full md:w-[85%] md:h-[90%] rounded-xl border border-white/10 bg-white pointer-events-auto shadow-2xl"
                            frameborder="0">
                    </iframe>

                    <div v-else class="text-center pointer-events-auto bg-white/5 p-10 rounded-3xl backdrop-blur-lg border border-white/10">
                        <span class="material-symbols-outlined text-7xl text-orange-500 mb-4">draft</span>
                        <p class="text-white text-lg font-bold mb-6">Preview unavailable for this file.</p>
                        <a :href="getFileUrl(file.file_path)" download target="_blank"
                           class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-400 text-black px-8 py-3 rounded-xl font-black transition-all transform active:scale-95">
                            <span class="material-symbols-outlined">download</span> Download Now
                        </a>
                    </div>

                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.35s cubic-bezier(0.4, 0, 0.2, 1); }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>