<template>
  <Transition name="modal-fade">
    <div v-if="show" class="fixed inset-0 z-100 flex items-center justify-center p-4 sm:p-6">
      
      <div 
        class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity" 
        @click="$emit('close')"
      ></div>

      <div 
        class="relative w-full max-w-lg rounded-2xl border border-white/10 shadow-[0_0_40px_rgba(0,0,0,0.5)] backdrop-blur-xl flex flex-col max-h-[90vh] overflow-hidden modal-scale"
        style="background: linear-gradient(145deg, rgba(24,24,27,0.9) 0%, rgba(9,9,11,0.95) 100%);"
      >
        <div class="absolute top-[-20%] left-[-10%] w-[50%] h-[50%] bg-orange-600/10 rounded-full blur-[80px] pointer-events-none"></div>
        <div class="absolute bottom-[-20%] right-[-10%] w-[50%] h-[50%] bg-blue-600/10 rounded-full blur-[80px] pointer-events-none"></div>

        <div class="flex items-center justify-between px-6 py-5 border-b border-white/10 relative z-10">
          <h3 class="text-2xl font-light tracking-tight text-white">
            Event <span class="text-orange-500 font-bold">Details</span>
          </h3>
          <button 
            @click="$emit('close')" 
            class="text-white/50 hover:text-orange-500 transition-colors rounded-full p-1.5 hover:bg-white/5 shrink-0"
          >
            <span class="material-symbols-outlined block text-[20px]">close</span>
          </button>
        </div>

        <div class="overflow-y-auto custom-scrollbar p-6 space-y-8 flex-1 relative z-10">
          <div v-if="!events || events.length === 0" class="text-center py-8 text-white/50 italic">
            No details available for this day.
          </div>

          <div v-else v-for="(event, idx) in events" :key="idx" class="group animate-in fade-in slide-in-from-bottom-2 w-full overflow-hidden" :style="{ animationDelay: `${idx * 100}ms`, animationFillMode: 'both' }">
            
            <h4 class="text-xl font-bold text-white mb-3 group-hover:text-orange-400 transition-colors wrap-break-word">
              {{ event.title }}
            </h4>
            
            <div class="flex flex-col gap-2.5 mb-4 text-sm text-white/70">
              
              <div class="flex items-start gap-3 bg-white/5 px-3 py-2 rounded-lg border border-white/5">
                <span class="material-symbols-outlined text-[18px] text-orange-500 mt-0.5 shrink-0">schedule</span>
                <div class="flex flex-col">
                  <span class="font-medium text-white/90">Start: <span class="font-normal text-white/70">{{ formatDateTime(event.start_time) }}</span></span>
                  <span v-if="event.end_time" class="font-medium text-white/90">End: <span class="font-normal text-white/70">{{ formatDateTime(event.end_time) }}</span></span>
                </div>
              </div>

              <div class="flex items-start gap-3 bg-white/5 px-3 py-2 rounded-lg border border-white/5">
                <span class="material-symbols-outlined text-[18px] text-blue-400 mt-0.5 shrink-0">location_on</span>
                <span class="text-white/90 wrap-break-word flex-1">{{ event.venue || 'TBA' }}</span>
              </div>

            </div>
            
            <div class="mt-4">
              <h5 class="text-[10px] font-bold tracking-widest uppercase text-white/40 mb-2">About this event</h5>
              <div 
                class="text-sm text-white/80 leading-relaxed font-light wrap-break-word prose prose-invert max-w-none" 
                v-html="event.description || '<span class=\'italic opacity-50\'>No description provided.</span>'"
              ></div>
            </div>
            
            <div v-if="idx < events.length - 1" class="flex items-center justify-center mt-8">
              <div class="h-px bg-linear-to-r from-transparent via-white/20 to-transparent w-3/4"></div>
            </div>
          </div>
        </div>

        <div class="px-6 py-4 border-t border-white/10 bg-black/20 flex justify-end relative z-10">
          <button 
            @click="$emit('close')" 
            class="px-6 py-2 rounded-full bg-white/10 hover:bg-orange-500 hover:text-black transition-all duration-300 text-sm font-bold tracking-wide"
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

/* Scrollbar specifically for the modal body */
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(249, 115, 22, 0.5); /* Orange hover */
}
</style>