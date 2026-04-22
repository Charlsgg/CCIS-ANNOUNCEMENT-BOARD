<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useTheme } from '../composable/usetheme.ts'

// --- Types ---
interface Attachment {
  id?: number | string
  attachment_id?: number | string
  file_path: string
  file_type: string
}

interface Announcement {
  id: number | string
  title: string
  content: string
  topic?: string
  date: string
  author_name?: string
  author_type?: string // <-- Added author_type
  author_avatar?: string | null
  likes_count?: number
  attachments?: Attachment[]
}

const props = withDefaults(defineProps<{
  post: Announcement;
  showActions?: boolean;
}>(), {
  showActions: false
})

const emit = defineEmits<{
  (e: 'preview', file: Attachment): void
  (e: 'edit', post: Announcement): void
  (e: 'delete', id: number | string): void
}>()

const { theme, styles, surface, isDark } = useTheme()

// --- State ---
const isModalOpen = ref(false)
const activePreview = ref<Attachment | null>(null)

// Lock body scroll when modal is open
watch(isModalOpen, (newVal) => {
  document.body.style.overflow = newVal ? 'hidden' : 'auto'
})

// --- Modal Handlers ---
const closeModal = () => {
  isModalOpen.value = false
  activePreview.value = null
}

const previewFile = (file: Attachment) => {
  activePreview.value = file
}

// --- Universal Helpers ---
const getFileUrl = (path?: string | null) => {
  if (!path || path === 'undefined' || path === 'null') return '#'
  let cleanPath = path.replace(/^\/?storage\//, '').replace(/^https?:\/([^\/])/, 'https://$1').replace(/^announcements\//, '').replace(/^\/+/, '')
  if (cleanPath.startsWith('http')) return cleanPath
  return `https://hahocarxbknajzqjacuk.supabase.co/storage/v1/object/public/announcements/${cleanPath}`
}

const isImage = (type: string | null) => type ? type.startsWith('image/') || /\.(jpg|jpeg|png|webp|gif)$/i.test(type) : false
const isPdf = (type: string | null) => type ? type === 'application/pdf' || /\.(pdf)$/i.test(type) : false

const getFileName = (path?: string | null) => {
  if (!path) return 'Download File'
  const name = path.split('/').pop()?.split('?')[0] || 'Download File'
  return name.replace('announcement-files/', '')
}

// --- Role Color Mapping Logic ---
const getRoleColor = (type?: string) => {
  // Map specific author_types to hex colors based on your API
  const roleColors: Record<string, string> = {
    'it_instructor': '3b82f6',
    'is_instructor': '008000',
    'cs_instructor': '9a0303',
    'lsg_officer': 'ec5b13',
  }

  if (type && roleColors[type.toLowerCase()]) {
    return roleColors[type.toLowerCase()]
  }

  // Default fallback color if type is missing or unknown (Gray)
  return '6B7280'
}

const getDefaultAvatar = (name?: string, type?: string) => {
  const bgColor = getRoleColor(type)
  const initial = (name?.charAt(0) || 'U').toUpperCase()

  return `https://ui-avatars.com/api/?background=${bgColor}&color=fff&name=${encodeURIComponent(initial)}`

}

// --- Image Extraction Logic (For Card Cover) ---
const coverImage = computed(() => {
  if (!props.post.attachments) return null
  return props.post.attachments.find(file => isImage(file.file_type)) || null
})

// --- Modal Dynamic Data Mappers ---
const authorAvatar = computed(() => {
  if (props.post.author_avatar) {
    return getFileUrl(props.post.author_avatar);
  }
  return getDefaultAvatar(props.post.author_name, props.post.author_type);
});

const metaDetails = computed(() => {
  return [
    { label: 'Posted By', date: props.post.author_name || 'System', sub: 'Author', icon: 'person' },
    { label: 'Date', date: props.post.date || 'Recently', sub: 'Published', icon: 'calendar_month' },
    { label: 'Subject Type', date: props.post.topic || 'Announcement', sub: 'Subject', icon: 'campaign' }
  ];
})
</script>

<template>
  <article class="flex flex-col md:flex-row overflow-hidden rounded-xl border transition-all group hover:shadow-md"
    :style="{ backgroundColor: surface.cardBg, borderColor: surface.borderSubtle }">

    <div v-if="coverImage"
      class="w-full md:w-1/3 h-48 md:h-auto overflow-hidden shrink-0 border-b md:border-b-0 md:border-r self-stretch"
      :style="{ borderColor: surface.borderSubtle }">
      <div
        class="w-full h-full bg-center bg-no-repeat bg-cover group-hover:scale-105 transition-transform duration-500 cursor-pointer"
        :style="{ backgroundImage: `url('${getFileUrl(coverImage.file_path)}')` }" @click="previewFile(coverImage)"
        title="Click to view image">
      </div>
    </div>

    <div class="flex-1 p-5 md:p-6 flex flex-col justify-between min-w-0">
      <div>
        <div class="flex justify-between items-start mb-3">
          <div class="flex items-center gap-2 flex-wrap">
            <span v-if="post.topic" class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border"
              :style="{ color: theme.accent, backgroundColor: surface.inputBg, borderColor: surface.borderSubtle }">
              {{ post.topic }}
            </span>
            <span v-else class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border"
              :style="{ color: theme.accent, backgroundColor: surface.inputBg, borderColor: surface.borderSubtle }">
              Announcement
            </span>
            <span class="text-xs font-medium uppercase tracking-widest" :style="styles.textSecondary">
              • {{ post.date }}
            </span>
          </div>

          <div v-if="showActions" class="flex items-center gap-2 shrink-0 ml-4">
            <button @click.prevent.stop="$emit('edit', post)"
              class="p-1.5 flex items-center justify-center rounded-lg border transition-transform hover:scale-105"
              :style="{ backgroundColor: surface.inputBg, borderColor: surface.borderSubtle, color: theme.accent }"
              title="Edit">
              <span class="material-symbols-outlined text-[16px]">edit</span>
            </button>
            <button @click.prevent.stop="$emit('delete', post.id)"
              class="p-1.5 flex items-center justify-center rounded-lg border transition-colors hover:bg-red-50/10 text-red-500"
              :style="{ backgroundColor: surface.inputBg, borderColor: surface.borderSubtle }" title="Delete">
              <span class="material-symbols-outlined text-[16px]">delete</span>
            </button>
          </div>
        </div>

        <h3 class="text-xl md:text-2xl font-bold mb-2 transition-colors cursor-pointer wrap-break-word"
          :style="{ color: styles.textPrimary.color }" @click="isModalOpen = true">
          {{ post.title }}
        </h3>

        <div class="text-sm md:text-base mb-4 leading-relaxed whitespace-pre-wrap wrap-break-word line-clamp-2"
          :style="styles.textSecondary" v-html="post.content">
        </div>
      </div>

      <div class="flex items-center justify-between mt-4 pt-4 border-t" :style="{ borderColor: surface.borderSubtle }">
        <div class="flex items-center gap-3 min-w-0">
          <img class="size-8 rounded-full object-cover border shrink-0 shadow-sm"
            :style="{ borderColor: surface.borderSubtle }"
            :src="post.author_avatar ? getFileUrl(post.author_avatar) : getDefaultAvatar(post.author_name, post.author_type)"
            @error="(e) => (e.target as HTMLImageElement).src = getDefaultAvatar(post.author_name, post.author_type)"
            alt="Author avatar" />
          <p class="text-sm font-semibold truncate max-w-30 sm:max-w-50" :style="styles.textPrimary">
            {{ post.author_name || 'System' }}
          </p>

          <button v-if="post.likes_count !== undefined"
            class="flex items-center gap-1.5 text-xs ml-2 hover:text-red-500 transition-colors shrink-0"
            :style="styles.textSecondary">
            <span class="material-symbols-outlined text-sm">favorite</span>
            {{ post.likes_count }}
          </button>
        </div>

        <button @click="isModalOpen = true"
          class="flex items-center gap-1 text-sm font-bold hover:underline shrink-0 pl-2"
          :style="{ color: theme.accent }">
          Read More
          <span class="material-symbols-outlined text-sm">open_in_new</span>
        </button>
      </div>
    </div>
  </article>

  <Teleport to="body">
    <Transition name="fade">
      <div v-if="isModalOpen"
        class="fixed inset-0 z-100 flex items-center justify-center p-2 sm:p-4 md:p-6 transition-all duration-300 backdrop-blur-sm"
        :style="{ backgroundColor: surface.overlayBg || 'rgba(0,0,0,0.5)' }">

        <div class="absolute inset-0" @click="closeModal"></div>

        <div
          class="relative w-full max-w-4xl h-full max-h-[95vh] md:h-auto md:max-h-[85vh] rounded-xl shadow-2xl overflow-hidden flex flex-col border transition-all duration-300"
          :style="[styles.cardBg, { borderColor: theme.accent + '20' }]">

          <div
            class="relative h-40 md:h-48 w-full flex items-end overflow-hidden shrink-0 transition-colors duration-500"
            :style="{ backgroundColor: isDark ? theme.accent + 'e6' : theme.accent }">

            <button @click="closeModal"
              class="absolute top-3 right-3 md:top-4 md:right-4 z-20 w-8 h-8 md:w-10 md:h-10 flex items-center justify-center rounded-full bg-black/20 hover:bg-black/40 text-white transition-colors">
              <span class="material-symbols-outlined text-lg md:text-xl">close</span>
            </button>

            <div
              class="relative z-10 p-4 md:p-6 flex items-center gap-4 w-full bg-linear-to-t from-black/60 to-transparent">
              <div class="shrink-0 rounded-xl shadow-lg p-1" :style="styles.cardBg">
                <img :src="authorAvatar"
                  @error="(e) => (e.target as HTMLImageElement).src = getDefaultAvatar(post.author_name, post.author_type)"
                  alt="Author Profile" class="w-12 h-12 md:w-16 md:h-16 rounded-lg object-cover" />
              </div>

              <div class="flex-1 min-w-0">
                <nav class="flex gap-2 mb-1">
                  <span
                    class="px-2 py-0.5 rounded bg-white/20 text-white text-[9px] md:text-[10px] font-bold uppercase tracking-widest truncate">
                    Announcement
                  </span>
                </nav>
                <h1
                  class="text-white text-lg sm:text-xl md:text-2xl font-bold leading-tight drop-shadow-sm line-clamp-2">
                  {{ post.title }}
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
                    <span class="material-symbols-outlined text-[20px]" :style="{ color: theme.accent }">{{ item.icon
                    }}</span>
                  </div>
                  <div class="min-w-0">
                    <h4 class="text-[10px] font-bold uppercase tracking-widest mb-0.5" :style="styles.textMuted">
                      {{ item.label }}
                    </h4>
                    <p class="font-bold text-sm leading-tight truncate" :style="styles.textPrimary">
                      {{ item.date }}
                    </p>
                    <p class="text-[11px] font-medium mt-0.5 truncate" :style="{ color: theme.accent }">{{ item.sub }}
                    </p>
                  </div>
                </div>
              </div>

              <div class="max-w-3xl pb-4">
                <h3 class="text-lg font-bold mb-3 md:mb-4 flex items-center gap-2" :style="styles.textPrimary">
                  Content Details
                </h3>
                <div class="prose dark:prose-invert max-w-none space-y-4">
                  <div class="text-sm md:text-base leading-relaxed border-l-4 pl-4 whitespace-pre-wrap wrap-break-word"
                    :style="{ color: isDark ? '#cbd5e1' : '#334155', borderColor: theme.accent + '4d' }"
                    v-html="post.content || 'No additional information provided.'">
                  </div>
                </div>
              </div>

              <div v-if="post.attachments && post.attachments.length > 0" class="max-w-3xl pt-4 border-t"
                :style="{ borderColor: surface.borderSubtle }">
                <h3 class="text-lg font-bold mb-3 md:mb-4 flex items-center gap-2 mt-4" :style="styles.textPrimary">
                  Attachments
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                  <div v-for="file in post.attachments" :key="file.id" @click="previewFile(file)"
                    class="group relative rounded-xl overflow-hidden border transition-all duration-300 cursor-pointer shadow-sm hover:border-orange-500"
                    :style="{ borderColor: theme.accent + '33', backgroundColor: surface.cardBg }">
                    <img v-if="isImage(file.file_type)" :src="getFileUrl(file.file_path)"
                      class="w-full h-32 object-cover group-hover:scale-105 transition-transform duration-500" />
                    <div v-else class="p-4 flex items-center gap-3 h-32 justify-center flex-col"
                      :style="{ backgroundColor: isDark ? '#3f3f46' : '#f9fafb' }">
                      <span
                        class="material-symbols-outlined text-3xl group-hover:scale-110 transition-transform duration-300"
                        :style="{ color: theme.accent }">
                        {{ isPdf(file.file_type) ? 'picture_as_pdf' : 'description' }}
                      </span>
                      <p class="text-[10px] font-mono truncate w-full text-center px-2" :style="styles.textMuted">{{
                        getFileName(file.file_path) }}</p>
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
      <div v-if="activePreview"
        class="fixed inset-0 z-110 flex items-center justify-center p-4 transition-all duration-300 backdrop-blur-md"
        :style="{ backgroundColor: surface.overlayBg || 'rgba(0,0,0,0.7)' }">
        <button @click="activePreview = null"
          class="absolute top-6 right-6 z-120 w-12 h-12 flex items-center justify-center rounded-full transition-all hover:rotate-90 border shadow-sm"
          :style="{ backgroundColor: surface.cardBg, borderColor: theme.accent + '33', color: styles.textPrimary.color }">
          <span class="material-symbols-outlined text-xl">close</span>
        </button>
        <div class="w-full h-full flex items-center justify-center">
          <img v-if="isImage(activePreview.file_type)" :src="getFileUrl(activePreview.file_path)"
            class="max-w-full max-h-full object-contain animate-in zoom-in duration-300 shadow-xl border rounded-lg"
            :style="{ borderColor: theme.accent + '33' }" />
          <iframe v-else-if="isPdf(activePreview.file_type)" :src="getFileUrl(activePreview.file_path)"
            class="w-full h-full md:w-[85%] md:h-[90%] rounded-2xl border shadow-2xl"
            :style="{ backgroundColor: surface.cardBg, borderColor: theme.accent + '33' }" frameborder="0"></iframe>
          <div v-else class="text-center border p-12 rounded-3xl shadow-xl mx-4"
            :style="{ backgroundColor: surface.cardBg, borderColor: theme.accent + '33' }">
            <span class="material-symbols-outlined text-6xl mb-4 block" :style="{ color: theme.accent }">draft</span>
            <p class="mb-8 font-light tracking-wide wrap-break-word max-w-md" :style="styles.textPrimary">Preview not
              available for this file
              type.</p>
            <a :href="getFileUrl(activePreview.file_path)" download target="_blank"
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
/* Scrollbar */
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Transitions */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Typography Constraints */
.break-words {
  word-wrap: break-word;
  overflow-wrap: break-word;
  word-break: break-word;
}

/* Tailwind Line Clamp Native Support */
.line-clamp-2 {
  display: -webkit-box;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>