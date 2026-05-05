<template>
  <Transition name="modal-fade">
    <div v-if="show" class="fixed inset-0 z-100 flex items-center justify-center p-4 sm:p-6">
      
      <!-- Backdrop -->
      <div 
        class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity" 
        @click="$emit('close')"
      ></div>

      <!-- Modal Container (Made wider: max-w-4xl to fit the sidebar) -->
      <div 
        class="relative w-full max-w-4xl rounded-2xl border border-gray-200 shadow-2xl bg-white flex flex-col max-h-[90vh] overflow-hidden modal-scale"
      >
        <!-- Background Blurs -->
        <div class="absolute top-[-20%] left-[-10%] w-[50%] h-[50%] bg-orange-200/40 rounded-full blur-[80px] pointer-events-none"></div>
        <div class="absolute bottom-[-20%] right-[-10%] w-[50%] h-[50%] bg-blue-200/40 rounded-full blur-[80px] pointer-events-none"></div>

        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 relative z-10 bg-white/80 backdrop-blur-sm">
          <h3 class="text-2xl font-light tracking-tight text-gray-900">
            Event <span class="text-orange-500 font-bold">Details</span>
          </h3>
          <button 
            @click="$emit('close')" 
            class="text-gray-400 hover:text-orange-500 transition-colors rounded-full p-1.5 hover:bg-gray-100 shrink-0"
          >
            <span class="material-symbols-outlined block text-[20px]">close</span>
          </button>
        </div>

        <!-- Body Layout (Sidebar + Main Content) -->
        <div class="flex flex-col md:flex-row flex-1 overflow-hidden relative z-10 bg-white/50">
          
          <!-- SIDEBAR: List of Events -->
          <aside class="w-full md:w-64 lg:w-72 border-b md:border-b-0 md:border-r border-gray-100 bg-gray-50/50 flex flex-col shrink-0">
            <div class="p-4 border-b border-gray-100 hidden md:block bg-gray-50">
              <h4 class="text-xs font-bold uppercase tracking-widest text-gray-500">Events on this day</h4>
            </div>
            
            <!-- Horizontal scroll on mobile, Vertical on desktop -->
            <div class="flex flex-row md:flex-col overflow-x-auto md:overflow-y-auto custom-scrollbar p-3 gap-2">
              <button 
                v-for="(event, idx) in events" 
                :key="idx"
                @click="activeIndex = idx"
                class="shrink-0 w-60 md:w-auto text-left p-3 rounded-xl transition-all duration-200 border group"
                :class="activeIndex === idx 
                  ? 'bg-orange-50 border-orange-200 shadow-sm ring-1 ring-orange-500/20' 
                  : 'bg-white border-transparent hover:border-gray-200 hover:bg-gray-50'"
              >
                <div class="flex flex-col gap-1">
                  <span 
                    class="text-[11px] font-bold tracking-wide"
                    :class="activeIndex === idx ? 'text-orange-500' : 'text-gray-400 group-hover:text-gray-500'"
                  >
                    {{ formatTime(event.start_time) }}
                  </span>
                  <span 
                    class="text-sm font-bold leading-tight truncate"
                    :class="activeIndex === idx ? 'text-gray-900' : 'text-gray-600'"
                  >
                    {{ event.title }}
                  </span>
                </div>
              </button>
            </div>
          </aside>

          <!-- MAIN CONTENT: Selected Event Details -->
          <main class="flex-1 overflow-y-auto custom-scrollbar p-6 bg-white">
            <div v-if="!activeEvent" class="h-full flex items-center justify-center text-gray-400 italic">
              No event selected.
            </div>

            <div v-else class="animate-in fade-in slide-in-from-bottom-2 duration-300 max-w-2xl" :key="activeIndex">
              <h4 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-6 wrap-break-word">
                {{ activeEvent.title }}
              </h4>
              
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-8 text-sm text-gray-600">
                <!-- Time Block -->
                <div class="flex items-start gap-3 bg-gray-50 px-4 py-3 rounded-xl border border-gray-100 shadow-sm">
                  <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[18px] text-orange-500">schedule</span>
                  </div>
                  <div class="flex flex-col">
                    <span class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-0.5">Time</span>
                    <span class="font-medium text-gray-800">{{ formatDateTime(activeEvent.start_time) }}</span>
                    <span v-if="activeEvent.end_time" class="text-gray-500 text-xs mt-0.5">until {{ formatDateTime(activeEvent.end_time) }}</span>
                  </div>
                </div>

                <!-- Location Block -->
                <div class="flex items-start gap-3 bg-gray-50 px-4 py-3 rounded-xl border border-gray-100 shadow-sm">
                  <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[18px] text-blue-600">location_on</span>
                  </div>
                  <div class="flex flex-col flex-1 min-w-0">
                    <span class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-0.5">Venue</span>
                    <span class="font-medium text-gray-800 wrap-break-word">{{ activeEvent.venue || 'TBA' }}</span>
                  </div>
                </div>
              </div>
              
              <!-- Description -->
              <div class="mt-4">
                <h5 class="text-[11px] font-bold tracking-widest uppercase text-gray-400 mb-3 flex items-center gap-2">
                  About this event
                  <div class="h-px bg-gray-100 flex-1"></div>
                </h5>
                <div 
                  class="text-sm md:text-base text-gray-700 leading-relaxed font-light wrap-break-word prose max-w-none" 
                  v-html="activeEvent.description || '<span class=\'italic text-gray-400\'>No description provided.</span>'"
                ></div>
              </div>
            </div>
          </main>
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex justify-end relative z-10">
          <button 
            @click="$emit('close')" 
            class="px-6 py-2 rounded-full bg-white border border-gray-200 text-gray-700 hover:bg-orange-500 hover:border-orange-500 hover:text-white hover:shadow-[0_0_15px_rgba(249,115,22,0.3)] transition-all duration-300 text-sm font-bold tracking-wide"
          >
            Close
          </button>
        </div>

      </div>
    </div>
  </Transition>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'

interface FormattedEvent {
  title: string
  venue: string
  description: string
  start_time: string
  end_time?: string | null
}

const props = defineProps<{
  show: boolean
  events: FormattedEvent[]
}>()

const emit = defineEmits<{
  (e: 'close'): void
}>()

// State for the sidebar
const activeIndex = ref<number>(0)

// Reset selection when modal opens
watch(() => props.show, (isShowing) => {
  if (isShowing) {
    activeIndex.value = 0
  }
})

// Get the currently selected event data
const activeEvent = computed(() => props.events[activeIndex.value] || null)

// Helpers
const formatTime = (dateString?: string | null): string => {
  if (!dateString) return '--:--'
  try {
    return new Date(dateString).toLocaleTimeString('en-US', {
      hour: 'numeric',
      minute: '2-digit',
      hour12: true
    })
  } catch (e) {
    return 'Time TBA'
  }
}

const formatDateTime = (dateString?: string | null): string => {
  if (!dateString) return 'TBA'
  try {
    const date = new Date(dateString)
    return date.toLocaleString('en-US', {
      month: 'short', 
      day: 'numeric',
      year: 'numeric',
      hour: 'numeric',
      minute: '2-digit',
      hour12: true
    })
  } catch (e) {
    return dateString
  }
}
</script>

<style scoped>
/* Modal Transitions */
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.3s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

.modal-fade-enter-active .modal-scale {
  transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.modal-fade-leave-active .modal-scale {
  transition: all 0.2s ease-in;
}
.modal-fade-enter-from .modal-scale {
  opacity: 0;
  transform: scale(0.95) translateY(10px);
}
.modal-fade-leave-to .modal-scale {
  opacity: 0;
  transform: scale(0.95) translateY(10px);
}

/* Scrollbar */
.custom-scrollbar::-webkit-scrollbar {
  width: 5px;
  height: 5px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(0, 0, 0, 0.1);
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(249, 115, 22, 0.4); /* Orange hover */
}
</style>