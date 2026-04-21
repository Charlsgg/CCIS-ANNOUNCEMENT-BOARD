<script setup lang="ts">
import { ref, computed } from 'vue'
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

const { theme, styles, surface } = useTheme()
const isExpanded = ref(false)

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

const getDefaultAvatar = (name?: string) => `https://ui-avatars.com/api/?background=random&color=fff&name=${encodeURIComponent(name || 'User')}`

// --- Image Extraction Logic ---
const coverImage = computed(() => {
  if (!props.post.attachments) return null
  return props.post.attachments.find(file => isImage(file.file_type)) || null
})

// Safely get remaining files using object reference comparison 
const remainingAttachments = computed(() => {
  if (!props.post.attachments) return []
  if (!coverImage.value) return props.post.attachments

  return props.post.attachments.filter(file => file !== coverImage.value)
})
</script>

<template>
  <article class="flex flex-col md:flex-row overflow-hidden rounded-xl border transition-all group hover:shadow-md"
    :style="{ backgroundColor: surface.cardBg, borderColor: surface.borderSubtle }">

    <div v-if="coverImage"
      class="w-full md:w-1/3 aspect-video md:aspect-auto overflow-hidden shrink-0 border-b md:border-b-0 md:border-r self-start md:h-full max-h-[300px] md:max-h-none"
      :style="{ borderColor: surface.borderSubtle }">
      <div
        class="w-full h-full min-h-[200px] md:min-h-[300px] bg-center bg-no-repeat bg-cover group-hover:scale-105 transition-transform duration-500 cursor-pointer"
        :style="{ backgroundImage: `url('${getFileUrl(coverImage.file_path)}')` }" @click="$emit('preview', coverImage)"
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

        <h3 class="text-xl md:text-2xl font-bold mb-2 transition-colors cursor-pointer"
          :style="{ color: isExpanded ? theme.accent : styles.textPrimary.color }" @click="isExpanded = !isExpanded">
          {{ post.title }}
        </h3>

        <div class="text-sm md:text-base mb-4 leading-relaxed whitespace-pre-wrap break-words"
          :class="{ 'line-clamp-2': !isExpanded }" :style="styles.textSecondary" v-html="post.content">
        </div>

        <div v-if="isExpanded && remainingAttachments.length > 0" class="mb-4 pt-4 border-t"
          :style="{ borderColor: surface.borderSubtle }">
          <p class="text-xs font-bold mb-3 uppercase tracking-wider" :style="styles.textSecondary">
            {{ remainingAttachments.length }} Attached File{{ remainingAttachments.length > 1 ? 's' : '' }}
          </p>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <template v-for="file in remainingAttachments" :key="file.file_path">

              <div v-if="isImage(file.file_type)"
                class="h-32 w-full rounded-lg bg-cover bg-center border cursor-pointer hover:opacity-90 relative group overflow-hidden"
                :style="{ backgroundImage: `url('${getFileUrl(file.file_path)}')`, borderColor: surface.borderSubtle }"
                @click="$emit('preview', file)">
                <div
                  class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                  <span
                    class="bg-white/20 backdrop-blur-sm text-white p-2 rounded-full material-symbols-outlined">visibility</span>
                </div>
              </div>

              <div v-else
                class="rounded-lg p-3 flex items-center justify-between border cursor-pointer transition-colors hover:opacity-80"
                :style="{ backgroundColor: surface.inputBg, borderColor: surface.borderSubtle }"
                @click="$emit('preview', file)">
                <div class="flex items-center gap-2 overflow-hidden">
                  <span :style="{ color: theme.accent }" class="material-symbols-outlined text-[20px]">
                    {{ isPdf(file.file_type) ? 'picture_as_pdf' : 'description' }}
                  </span>
                  <span class="text-xs font-bold truncate max-w-[150px]" :style="styles.textPrimary">
                    {{ getFileName(file.file_path) }}
                  </span>
                </div>
                <span :style="{ color: theme.accent }" class="material-symbols-outlined text-[18px]">visibility</span>
              </div>

            </template>
          </div>
        </div>
      </div>

      <div class="flex items-center justify-between mt-4 pt-4 border-t" :style="{ borderColor: surface.borderSubtle }">
        <div class="flex items-center gap-3">
          <img class="size-8 rounded-full object-cover border shrink-0 shadow-sm"
            :style="{ borderColor: surface.borderSubtle }"
            :src="post.author_avatar ? getFileUrl(post.author_avatar) : getDefaultAvatar(post.author_name)"
            @error="(e) => (e.target as HTMLImageElement).src = getDefaultAvatar(post.author_name)"
            alt="Author avatar" />
          <p class="text-sm font-semibold truncate max-w-[120px] sm:max-w-[200px]" :style="styles.textPrimary">
            {{ post.author_name || 'System' }}
          </p>

          <button v-if="post.likes_count !== undefined"
            class="flex items-center gap-1.5 text-xs ml-2 hover:text-red-500 transition-colors"
            :style="styles.textSecondary">
            <span class="material-symbols-outlined text-sm">favorite</span>
            {{ post.likes_count }}
          </button>
        </div>

        <button @click="isExpanded = !isExpanded"
          class="flex items-center gap-1 text-sm font-bold hover:underline shrink-0" :style="{ color: theme.accent }">
          {{ isExpanded ? 'Show Less' : 'Read More' }}
          <span class="material-symbols-outlined text-sm">{{ isExpanded ? 'arrow_upward' : 'arrow_forward' }}</span>
        </button>
      </div>

    </div>
  </article>
</template>

<style scoped>
.break-words {
  word-wrap: break-word;
  overflow-wrap: break-word;
}

/* Tailwind Line Clamp Native Support */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>