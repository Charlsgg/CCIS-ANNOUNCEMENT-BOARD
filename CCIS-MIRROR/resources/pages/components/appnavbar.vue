<script setup lang="ts">
import { Terminal, Bell, Menu, Sun, Moon } from 'lucide-vue-next'
import { useTheme } from '../composable/usetheme.ts'

const props = defineProps<{
    userName?: string
}>()

const emit = defineEmits<{
    toggleSidebar: []
}>()

const { theme, styles, surface, isDark, toggleMode } = useTheme()
</script>

<template>
    <header
        class="shrink-0 flex items-center justify-between px-4 md:px-8 py-3 md:py-4 z-30"
        :style="styles.headerBg"
    >
        <!-- Left -->
        <div class="flex items-center gap-3">
            <button
                @click="emit('toggleSidebar')"
                class="md:hidden p-2 -ml-2 rounded-lg transition-colors"
                :style="{ color: surface.textSecondary }"
                @mouseenter="(e: MouseEvent) => {
                    const el = e.currentTarget as HTMLElement
                    el.style.backgroundColor = surface.hoverBg
                    el.style.color = surface.textPrimary
                }"
                @mouseleave="(e: MouseEvent) => {
                    const el = e.currentTarget as HTMLElement
                    el.style.backgroundColor = 'transparent'
                    el.style.color = surface.textSecondary
                }"
            >
                <Menu :size="24" />
            </button>
            <div class="p-2 rounded-lg hidden sm:flex shadow-inner" :style="styles.iconBg">
                <Terminal :size="20" />
            </div>
            
        </div>

        <!-- Right -->
        <div class="flex items-center gap-2 md:gap-4">
            <!-- Dark / Light Toggle -->
            <button
                @click="toggleMode"
                class="p-2 rounded-lg transition-colors"
                :style="{ color: surface.textSecondary }"
                @mouseenter="(e: MouseEvent) => {
                    const el = e.currentTarget as HTMLElement
                    el.style.backgroundColor = surface.hoverBg
                    el.style.color = theme.accent
                }"
                @mouseleave="(e: MouseEvent) => {
                    const el = e.currentTarget as HTMLElement
                    el.style.backgroundColor = 'transparent'
                    el.style.color = surface.textSecondary
                }"
                :title="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
            >
                <Sun v-if="isDark" :size="20" />
                <Moon v-else :size="20" />
            </button>
            <div
                class="h-8 w-8 md:h-9 md:w-9 shrink-0 rounded-full flex items-center justify-center text-xs font-bold"
                :style="styles.avatar"
            >
                {{ userName?.charAt(0) || 'U' }}
            </div>
        </div>
    </header>
</template>