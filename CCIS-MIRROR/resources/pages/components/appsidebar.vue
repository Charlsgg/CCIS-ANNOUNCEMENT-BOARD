<script setup lang="ts">
import { Calendar, LayoutDashboard,User, Home, Megaphone, LogOut, X } from 'lucide-vue-next'
import { useTheme } from '../composable/usetheme'

const props = defineProps<{
    isOpen: boolean
    csrfToken: string
}>()

const emit = defineEmits<{
    close: []
}>()

const { theme, styles, surface, isDark } = useTheme()
</script>

<template>
    <aside
        :class="[
            'absolute inset-y-0 left-0 z-50 w-64 flex flex-col transition-transform duration-300 ease-in-out md:relative md:translate-x-0',
            isOpen ? 'translate-x-0 shadow-2xl shadow-black/50' : '-translate-x-full',
        ]"
        :style="styles.sidebarBg"
    >
        <div class="p-6 flex flex-col h-full">
            <!-- Close (mobile) -->
            <button
                @click="emit('close')"
                class="md:hidden absolute top-6 right-6 transition-colors"
                :style="{ color: surface.textMuted }"
            >
                <X :size="24" />
            </button>

            <!-- Brand -->
            <div class="flex items-center gap-3 mb-8 mt-2 md:mt-0">
                <div
                    class="h-10 w-10 shrink-0 rounded-full flex items-center justify-center border shadow-lg"
                    :style="styles.badge"
                >
                    <span class="font-black text-sm tracking-wider">{{ theme.abbr }}</span>
                </div>
                <h1
                    class="text-sm font-bold uppercase tracking-wide leading-tight"
                    :style="styles.textPrimary"
                >
                    {{ theme.label }}
                </h1>
            </div>

            <!-- Nav -->
            <nav class="flex-1 flex flex-col gap-1 overflow-y-auto pr-2 -mr-2">
                <div class="mt-2">
                    <!-- Active item -->
                    <div
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-white shadow-lg mb-2"
                        :style="styles.sidebarActive"
                    >
                        <Home :size="20" />
                        <span class="text-sm font-bold tracking-wide">Home</span>
                    </div>

                    <!-- Sub-items -->
                    <div
                        class="ml-5 mt-2 flex flex-col gap-1.5 border-l pl-4 py-1"
                        :style="styles.sidebarBorderLeft"
                    >
                        <a
                            href="#"
                            class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors"
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
                            <Calendar :size="18" /> Events
                        </a>
                        <a
                            href="#"
                            class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors"
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
                            <Megaphone :size="18" /> Announcements
                        </a>
                        <a
                            href="#"
                            class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors"
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
                            <User :size="18" /> Profile
                        </a>
                    </div>
                </div>
            </nav>

            <!-- Logout -->
            <div class="mt-auto pt-6 shrink-0" :style="{ borderTop: `1px solid ${surface.borderSubtle}` }">
                <form action="/logout" method="POST">
                    <input type="hidden" name="_token" :value="csrfToken" />
                    <button
                        type="submit"
                        class="flex items-center gap-3 px-4 py-3 w-full rounded-lg text-red-400 hover:bg-red-500/10 hover:text-red-300 font-medium transition-all border border-transparent hover:border-red-500/20"
                    >
                        <LogOut :size="20" /> Logout
                    </button>
                </form>
            </div>
        </div>
    </aside>
</template>