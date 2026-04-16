<template>
  <Transition name="modal-fade">
    <div v-if="show" class="fixed inset-0 z-100 flex items-center justify-center p-4 sm:p-6">
      
      <div 
        class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity" 
        @click="$emit('close')"
      ></div>

      <div 
        class="relative w-full max-w-lg rounded-2xl border border-gray-200 shadow-2xl bg-white flex flex-col max-h-[90vh] overflow-hidden modal-scale"
      >
        <div class="absolute top-[-20%] left-[-10%] w-[50%] h-[50%] bg-orange-200/40 rounded-full blur-[80px] pointer-events-none"></div>
        <div class="absolute bottom-[-20%] right-[-10%] w-[50%] h-[50%] bg-blue-200/40 rounded-full blur-[80px] pointer-events-none"></div>

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

        <div class="overflow-y-auto custom-scrollbar p-6 space-y-8 flex-1 relative z-10 bg-white">
          <div v-if="!events || events.length === 0" class="text-center py-8 text-gray-400 italic">
            No details available for this day.
          </div>

          <div v-else v-for="(event, idx) in events" :key="idx" class="group animate-in fade-in slide-in-from-bottom-2 w-full overflow-hidden" :style="{ animationDelay: `${idx * 100}ms`, animationFillMode: 'both' }">
            
            <h4 class="text-xl font-bold text-gray-900 mb-3 group-hover:text-orange-500 transition-colors wrap-break-word">
              {{ event.title }}
            </h4>
            
            <div class="flex flex-col gap-2.5 mb-4 text-sm text-gray-600">
              
              <div class="flex items-start gap-3 bg-gray-50 px-3 py-2 rounded-lg border border-gray-100">
                <span class="material-symbols-outlined text-[18px] text-orange-500 mt-0.5 shrink-0">schedule</span>
                <div class="flex flex-col">
                  <span class="font-medium text-gray-800">Start: <span class="font-normal text-gray-600">{{ formatDateTime(event.start_time) }}</span></span>
                  <span v-if="event.end_time" class="font-medium text-gray-800">End: <span class="font-normal text-gray-600">{{ formatDateTime(event.end_time) }}</span></span>
                </div>
              </div>

              <div class="flex items-start gap-3 bg-gray-50 px-3 py-2 rounded-lg border border-gray-100">
                <span class="material-symbols-outlined text-[18px] text-blue-600 mt-0.5 shrink-0">location_on</span>
                <span class="text-gray-800 wrap-break-word flex-1">{{ event.venue || 'TBA' }}</span>
              </div>

            </div>
            
            <div class="mt-4">
              <h5 class="text-[10px] font-bold tracking-widest uppercase text-gray-500 mb-2">About this event</h5>
              <div 
                class="text-sm text-gray-700 leading-relaxed font-light wrap-break-word prose max-w-none" 
                v-html="event.description || '<span class=\'italic text-gray-400\'>No description provided.</span>'"
              ></div>
            </div>
            
            <div v-if="idx < events.length - 1" class="flex items-center justify-center mt-8">
              <div class="h-px bg-linear-to-r from-transparent via-gray-200 to-transparent w-3/4"></div>
            </div>
          </div>
        </div>

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
  theme?: Record<string, any>
  surface?: Record<string, any>
  styles?: Record<string, any>
}>()

defineEmits<{
  (e: 'close'): void
}>()

// Helper to format the MySQL/ISO date strings into a readable format
const formatDateTime = (dateString?: string | null): string => {
  if (!dateString) return 'TBA'
  try {
    const date = new Date(dateString)
    return date.toLocaleString('en-US', {
      weekday: 'short',
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

/* Light Theme Scrollbar specifically for the modal body */
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(0, 0, 0, 0.15); /* Darker thumb for light theme */
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(249, 115, 22, 0.6); /* Orange hover */
}
</style>