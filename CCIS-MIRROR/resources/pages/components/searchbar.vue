    <script setup lang="ts">
    defineProps<{
        surface: Record<string, any>
    }>()
    </script>

    <template>
        <div class="relative group">
            <div 
                class="flex items-center rounded-lg px-3 py-1.5 transition-shadow"
                :style="{ backgroundColor: surface.hoverBg }"
            >
                <span class="material-symbols-outlined text-sm opacity-60">search</span>
                <input 
                    class="bg-transparent border-none focus:ring-0 text-sm w-full md:w-48 font-display outline-none ml-2" 
                    placeholder="Search events..." 
                    type="text"
                    :style="{ color: surface.textPrimary }"
                />
            </div>
        </div>
    </template>