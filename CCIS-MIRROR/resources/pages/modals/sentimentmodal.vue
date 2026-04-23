<template>
  <Transition name="modal">
    <div v-if="isOpen" class="fixed inset-0 z-999 flex items-center justify-center px-4 perspective-1000">
      
      <div 
        class="absolute inset-0 bg-gray-900/40 backdrop-blur-md"
        @click="close"
      ></div>

      <div class="modal-card relative bg-white rounded-3xl shadow-2xl p-8 max-w-sm w-full text-center overflow-hidden border border-orange-100">
        
        <div class="absolute top-0 left-0 h-2 bg-linear-to-r from-orange-400 to-orange-600 top-bar-anim"></div>
        
        <button 
          @click="close" 
          class="absolute top-4 right-4 text-gray-400 hover:text-orange-500 transition-colors bg-gray-50 hover:bg-orange-50 rounded-full p-1 z-10"
        >
          <span class="material-symbols-outlined text-lg">close</span>
        </button>

        <div v-if="sentiment" class="flex flex-col items-center gap-3 mt-4">
          <span class="text-7xl filter drop-shadow-lg stagger-1">{{ sentiment.icon }}</span>
          
          <h3 class="text-xl font-black text-gray-800 uppercase tracking-wide stagger-2">
            {{ sentiment.label }}
          </h3>
          
          <p class="text-sm text-gray-600 font-medium leading-relaxed px-2 py-4 stagger-3">
            {{ sentiment.message }}
          </p>
          
          <div class="mt-2 bg-orange-50 px-4 py-2 rounded-xl text-xs text-orange-600 font-bold border border-orange-100 shadow-inner w-full flex justify-between items-center stagger-4">
            <span>Campus Vibe Check:</span>
            <span class="bg-white px-2 py-1 rounded text-orange-500 shadow-sm">{{ averageVibe }}</span>
          </div>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { watch, onBeforeUnmount } from 'vue'

const props = defineProps({
  isOpen: Boolean,
  sentiment: Object,
  averageVibe: String
})

const emit = defineEmits(['close'])

let autoCloseTimer = null

const close = () => {
  emit('close')
}

watch(() => props.isOpen, (isOpen) => {
  if (isOpen) {
    if (autoCloseTimer) clearTimeout(autoCloseTimer)
    autoCloseTimer = setTimeout(() => {
      close()
    }, 10000)
  } else {
    if (autoCloseTimer) clearTimeout(autoCloseTimer)
  }
})

onBeforeUnmount(() => {
  if (autoCloseTimer) clearTimeout(autoCloseTimer)
})
</script>

<style scoped>
/* Add perspective to the container for 3D rotation effects */
.perspective-1000 {
  perspective: 1000px;
}

/* 1. Backdrop Transition */
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.6s cubic-bezier(0.22, 1, 0.36, 1);
}
.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

/* 2. Modal Card Enter/Leave Animations */
.modal-enter-active .modal-card {
  animation: cinematic-enter 0.8s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
}

.modal-leave-active .modal-card {
  animation: cinematic-leave 0.5s cubic-bezier(0.55, 0.085, 0.68, 0.53) forwards;
}

/* 3. The Cinematic Modal Keyframes */
@keyframes cinematic-enter {
  0% { 
    opacity: 0; 
    transform: translateY(80px) scale(0.6) rotateX(20deg) rotateY(-10deg); 
  }
  100% { 
    opacity: 1; 
    transform: translateY(0) scale(1) rotateX(0) rotateY(0); 
  }
}

@keyframes cinematic-leave {
  0% { 
    opacity: 1; 
    transform: translateY(0) scale(1) rotateX(0); 
  }
  100% { 
    opacity: 0; 
    transform: translateY(60px) scale(0.8) rotateX(-20deg); 
  }
}

/* 4. Motion Graphic Staggered Sequences */
/* These will automatically run when the v-if mounts the elements */

/* Top bar slides in from left to right */
.top-bar-anim {
  width: 0;
  animation: slide-width 0.8s cubic-bezier(0.22, 1, 0.36, 1) 0.2s forwards;
}

/* Stagger 1: Emoji drops in with an elastic bounce */
.stagger-1 {
  opacity: 0;
  animation: elastic-drop 0.8s cubic-bezier(0.34, 1.56, 0.64, 1) 0.3s forwards;
}

/* Stagger 2: Title blurs/slides in from the left */
.stagger-2 {
  opacity: 0;
  animation: slide-fade-right 0.6s cubic-bezier(0.22, 1, 0.36, 1) 0.4s forwards;
}

/* Stagger 3: Paragraph text fades and slides up */
.stagger-3 {
  opacity: 0;
  animation: fade-slide-up 0.6s cubic-bezier(0.22, 1, 0.36, 1) 0.5s forwards;
}

/* Stagger 4: The bottom pill pops open */
.stagger-4 {
  opacity: 0;
  animation: pop-in 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) 0.6s forwards;
}

/* --- Inner Keyframes --- */
@keyframes slide-width {
  to { width: 100%; }
}

@keyframes elastic-drop {
  0% { opacity: 0; transform: translateY(-40px) scale(0.5); }
  100% { opacity: 1; transform: translateY(0) scale(1); }
}

@keyframes slide-fade-right {
  0% { opacity: 0; transform: translateX(-30px); filter: blur(4px); }
  100% { opacity: 1; transform: translateX(0); filter: blur(0); }
}

@keyframes fade-slide-up {
  0% { opacity: 0; transform: translateY(20px); }
  100% { opacity: 1; transform: translateY(0); }
}

@keyframes pop-in {
  0% { opacity: 0; transform: scale(0.8); }
  100% { opacity: 1; transform: scale(1); }
}
</style>