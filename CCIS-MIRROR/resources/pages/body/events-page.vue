<script lang="ts">
export default { layout: null }
</script>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useTheme } from '../composable/usetheme.ts'
import AppSidebar from '../components/appsidebar.vue'
import AppNavbar from '../components/appnavbar.vue'

const props = defineProps<{
    user?: { name: string; email: string; user_type: string }
}>()

const { theme, styles, surface, isDark, setUserType, initTheme } = useTheme()

const isSidebarOpen = ref(false)
const csrfToken = ref('')

// Modal state managers
const showCreateModal = ref(false)
const showEventDetailModal = ref(false)

onMounted(() => {
    initTheme()
    if (props.user?.user_type) {
        setUserType(props.user.user_type)
    }
    
    // Grab CSRF token for the logout form in the sidebar
    const tokenTag = document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement
    if (tokenTag) {
        csrfToken.value = tokenTag.content
    }
})

// Helper array for days of the week
const daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']
</script>

<template>
    <div
        class="fixed inset-0 w-full h-full overflow-hidden font-['Space_Grotesk'] flex transition-colors duration-300"
        :style="{ ...styles.pageBg, color: surface.textPrimary }"
    >
        <div
            v-if="isSidebarOpen"
            @click="isSidebarOpen = false"
            class="absolute inset-0 z-40 md:hidden backdrop-blur-sm transition-opacity"
            :style="{ backgroundColor: surface.overlayBg }"
        ></div>

        <AppSidebar
            :is-open="isSidebarOpen"
            :csrf-token="csrfToken"
            @close="isSidebarOpen = false"
        />

        <main class="flex-1 flex flex-col h-full overflow-hidden min-w-0">
            <AppNavbar
                :user-name="user?.name"
                @toggle-sidebar="isSidebarOpen = true"
            />

            <div class="flex-1 overflow-y-auto p-4 md:p-8 w-full no-scrollbar">
                <div class="max-w-7xl mx-auto pb-12">
                    
                    <div class="mb-6 md:mb-8 flex flex-col xl:flex-row xl:items-center justify-between gap-4">
                        <div class="flex flex-col gap-2">
                            <h3 class="text-2xl md:text-3xl font-bold tracking-tight" :style="styles.textPrimary">
                                Academic Calendar
                            </h3>
                            <p :style="styles.textSecondary" class="text-sm md:text-base max-w-2xl">
                                Manage and track upcoming university events, exams, and holidays.
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <div 
                                class="flex items-center gap-2 p-1 rounded-xl border"
                                :style="{ backgroundColor: surface.hoverBg, borderColor: surface.borderMedium }"
                            >
                                <select 
                                    class="bg-transparent border-none outline-none focus:ring-0 text-sm font-bold cursor-pointer py-1.5 pl-3 pr-8 appearance-none"
                                    :style="{ color: theme.accent }"
                                >
                                    <option value="0" selected>Month</option>
                                    <option value="1">January</option>
                                    <option value="10">October</option>
                                </select>
                                <div class="w-px h-4" :style="{ backgroundColor: surface.borderStrong }"></div>
                                <select 
                                    class="bg-transparent border-none outline-none focus:ring-0 text-sm font-bold cursor-pointer py-1.5 pl-3 pr-8 appearance-none"
                                    :style="{ color: theme.accent }"
                                >
                                    <option selected>Year</option>
                                    <option>2023</option>
                                    <option>2024</option>
                                </select>
                            </div>

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

                            <button 
                                @click="showCreateModal = true" 
                                class="flex items-center gap-2 px-4 h-10 rounded-lg font-semibold text-sm transition-all"
                                :style="styles.button"
                                @mouseenter="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.opacity = '0.9'"
                                @mouseleave="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.opacity = '1'"
                            >
                                <span class="material-symbols-outlined text-[20px]">add</span>
                                Post New Event
                            </button>
                        </div>
                    </div>

                    <div class="flex flex-col xl:flex-row gap-6 items-start">
                        
                        <section 
                            class="flex-1 w-full rounded-2xl overflow-hidden shadow-lg shadow-black/5" 
                            :style="styles.cardBg"
                        >
                            <div class="grid grid-cols-7 gap-px" :style="{ backgroundColor: surface.borderSubtle }">
                                <div 
                                    v-for="day in daysOfWeek" 
                                    :key="day" 
                                    class="p-4 text-center font-bold text-xs uppercase tracking-wider"
                                    :style="{ backgroundColor: surface.hoverBg, color: theme.accent }"
                                >
                                    {{ day }}
                                </div>

                                <div 
                                    v-for="d in [24,25,26,27,28,29,30]" 
                                    :key="'prev-'+d" 
                                    class="min-h-30 p-2 opacity-40 transition-colors" 
                                    :style="{ backgroundColor: surface.cardBg, color: surface.textMuted }"
                                >
                                    {{ d }}
                                </div>

                                <div 
                                    v-for="d in [1,2]" 
                                    :key="'d-'+d" 
                                    @click="showEventDetailModal = true" 
                                    class="min-h-30 p-2 cursor-pointer transition-colors" 
                                    :style="{ backgroundColor: surface.cardBg }"
                                    @mouseenter="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.backgroundColor = surface.hoverBg"
                                    @mouseleave="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.backgroundColor = surface.cardBg"
                                >
                                    {{ d }}
                                </div>

                                <div 
                                    class="min-h-30 p-2 cursor-pointer transition-colors border-t-2" 
                                    :style="{ backgroundColor: surface.cardBg, borderTopColor: theme.accent }" 
                                    @click="showEventDetailModal = true"
                                    @mouseenter="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.backgroundColor = surface.hoverBg"
                                    @mouseleave="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.backgroundColor = surface.cardBg"
                                >
                                    <span class="font-bold">3</span>
                                    <div 
                                        class="mt-2 p-1.5 text-[10px] rounded leading-tight font-semibold border-l-2"
                                        :style="{ backgroundColor: theme.accent + '20', color: theme.accent, borderLeftColor: theme.accent }"
                                    >
                                        Midterm Exam Week
                                    </div>
                                </div>

                                <div 
                                    class="min-h-30 p-2 cursor-pointer transition-colors border-t-2" 
                                    :style="{ backgroundColor: surface.cardBg, borderTopColor: theme.accent }" 
                                    @click="showEventDetailModal = true"
                                    @mouseenter="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.backgroundColor = surface.hoverBg"
                                    @mouseleave="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.backgroundColor = surface.cardBg"
                                >
                                    <span class="font-bold">4</span>
                                    <div 
                                        class="mt-2 p-1.5 text-[10px] rounded leading-tight font-semibold border-l-2"
                                        :style="{ backgroundColor: theme.accent + '20', color: theme.accent, borderLeftColor: theme.accent }"
                                    >
                                        Midterm Exam Week
                                    </div>
                                </div>

                                <div 
                                    class="min-h-30 p-2 cursor-pointer transition-colors border-t-2" 
                                    :style="{ backgroundColor: surface.cardBg, borderTopColor: theme.accent }" 
                                    @click="showEventDetailModal = true"
                                    @mouseenter="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.backgroundColor = surface.hoverBg"
                                    @mouseleave="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.backgroundColor = surface.cardBg"
                                >
                                    <span class="font-bold">5</span>
                                    <div 
                                        class="mt-2 p-1.5 text-[10px] rounded leading-tight font-semibold border-l-2 mb-1"
                                        :style="{ backgroundColor: theme.accent + '20', color: theme.accent, borderLeftColor: theme.accent }"
                                    >
                                        Midterm Exam Week
                                    </div>
                                    <div class="p-1.5 bg-blue-500/20 text-blue-500 text-[10px] rounded leading-tight font-semibold border-l-2 border-blue-500">
                                        Research Colloquium
                                    </div>
                                </div>

                                <div 
                                    v-for="d in [6,7,8,9]" 
                                    :key="'d-'+d" 
                                    @click="showEventDetailModal = true" 
                                    class="min-h-30 p-2 cursor-pointer transition-colors" 
                                    :style="{ backgroundColor: surface.cardBg }"
                                    @mouseenter="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.backgroundColor = surface.hoverBg"
                                    @mouseleave="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.backgroundColor = surface.cardBg"
                                >
                                    {{ d }}
                                </div>

                                <div 
                                    class="min-h-30 p-2 cursor-pointer transition-colors border-t-2" 
                                    :style="{ backgroundColor: surface.cardBg, borderTopColor: theme.accent }" 
                                    @click="showEventDetailModal = true"
                                    @mouseenter="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.backgroundColor = surface.hoverBg"
                                    @mouseleave="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.backgroundColor = surface.cardBg"
                                >
                                    <span class="font-bold">10</span>
                                    <div 
                                        class="mt-2 p-1.5 text-[10px] rounded leading-tight font-semibold border-l-2"
                                        :style="{ backgroundColor: theme.accent + '20', color: theme.accent, borderLeftColor: theme.accent }"
                                    >
                                        Tech Summit 2023
                                    </div>
                                </div>

                                <div 
                                    v-for="d in 21" 
                                    :key="'fill-'+d" 
                                    @click="showEventDetailModal = true" 
                                    class="min-h-30 p-2 cursor-pointer transition-colors" 
                                    :style="{ backgroundColor: surface.cardBg }"
                                    @mouseenter="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.backgroundColor = surface.hoverBg"
                                    @mouseleave="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.backgroundColor = surface.cardBg"
                                >
                                    {{ d + 10 }}
                                </div>
                            </div>
                        </section>

                        <aside 
                            class="w-full xl:w-80 shrink-0 rounded-2xl overflow-hidden shadow-lg shadow-black/5 flex flex-col" 
                            :style="styles.cardBg"
                        >
                            <div class="p-6 border-b" :style="{ borderColor: surface.borderSubtle, backgroundColor: surface.hoverBg }">
                                <h3 class="text-lg font-bold" :style="styles.textPrimary">Upcoming Events</h3>
                                <p class="text-xs" :style="styles.textMuted">Chronological feed of announcements</p>
                            </div>
                            
                            <div class="p-4 space-y-4">
                                <div 
                                    v-for="event in [
                                        { dates: 'Oct 10-12', posted: 'Oct 1', title: 'Tech Summit 2023: Innovations in AI', loc: 'Grand Auditorium', desc: 'Three-day event featuring keynote speakers from industry leaders.' },
                                        { dates: 'Oct 15', posted: 'Sep 28', title: 'Career Orientation Seminar', loc: 'Seminar Hall B', desc: 'A guide for graduating students on how to navigate the job market.' },
                                        { dates: 'Oct 20', posted: 'Oct 2', title: 'Hackathon 2023 Kick-off', loc: 'IT Lab 4 & 5', desc: 'Join us for the opening ceremony of the annual 48-hour coding marathon.' }
                                    ]"
                                    :key="event.title"
                                    class="p-4 rounded-xl transition-all cursor-pointer border"
                                    :style="{ backgroundColor: surface.inputBg, borderColor: surface.borderSubtle }"
                                    @click="showEventDetailModal = true"
                                    @mouseenter="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.borderColor = surface.borderStrong"
                                    @mouseleave="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.borderColor = surface.borderSubtle"
                                >
                                    <div class="flex justify-between items-start mb-2">
                                        <span 
                                            class="px-2 py-1 text-[10px] font-bold rounded uppercase"
                                            :style="{ backgroundColor: theme.accent + '20', color: theme.accent }"
                                        >
                                            {{ event.dates }}
                                        </span>
                                        <span class="text-[10px]" :style="styles.textMuted">Posted: {{ event.posted }}</span>
                                    </div>
                                    <h4 class="font-bold text-sm mb-1 leading-snug" :style="styles.textPrimary">{{ event.title }}</h4>
                                    <div class="flex items-center gap-1 text-[11px] mb-2" :style="styles.textMuted">
                                        <span class="material-symbols-outlined text-[14px]">location_on</span>
                                        {{ event.loc }}
                                    </div>
                                    <p class="text-xs line-clamp-2" :style="styles.textSecondary">{{ event.desc }}</p>
                                </div>
                            </div>
                        </aside>
                    </div>

                </div>
            </div>
        </main>

        <Teleport to="body">
            
            <div 
                v-if="showCreateModal" 
                class="fixed inset-0 backdrop-blur-sm z-100 flex items-center justify-center p-4 transition-opacity"
                :style="{ backgroundColor: surface.overlayBg }"
            >
                <div class="w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden" :style="styles.cardBg">
                    <div class="p-6 border-b flex justify-between items-center" :style="{ borderColor: surface.borderSubtle, backgroundColor: surface.hoverBg }">
                        <h3 class="text-xl font-bold" :style="styles.textPrimary">Post New Event</h3>
                        <button 
                            @click="showCreateModal = false" 
                            class="size-8 rounded-full flex items-center justify-center transition-colors" 
                            :style="styles.textSecondary"
                            @mouseenter="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.backgroundColor = surface.borderSubtle"
                            @mouseleave="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.backgroundColor = 'transparent'"
                        >
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>
                    
                    <div class="p-6 space-y-4" :style="styles.textPrimary">
                        <div class="space-y-1">
                            <label class="text-xs font-bold uppercase" :style="{ color: theme.accent }">Event Title</label>
                            <input 
                                class="w-full rounded-lg font-display outline-none p-2 border" 
                                placeholder="e.g., Computer Science Night" type="text"
                                :style="{ backgroundColor: surface.inputBg, borderColor: surface.inputBorder, color: surface.textPrimary }"
                            />
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <div class="space-y-1">
                                <label class="text-xs font-bold uppercase" :style="{ color: theme.accent }">Month</label>
                                <select 
                                    class="w-full rounded-lg font-display outline-none p-2 border appearance-none"
                                    :style="{ backgroundColor: surface.inputBg, borderColor: surface.inputBorder, color: surface.textPrimary }"
                                >
                                    <option>January</option>
                                    <option selected>October</option>
                                </select>
                            </div>
                            <div class="space-y-1 col-span-2">
                                <label class="text-xs font-bold uppercase" :style="{ color: theme.accent }">Day Range</label>
                                <input 
                                    class="w-full rounded-lg font-display outline-none p-2 border" 
                                    placeholder="e.g., 3-10 or 15" type="text"
                                    :style="{ backgroundColor: surface.inputBg, borderColor: surface.inputBorder, color: surface.textPrimary }"
                                />
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-xs font-bold uppercase" :style="{ color: theme.accent }">Year</label>
                                <input 
                                    class="w-full rounded-lg font-display outline-none p-2 border" 
                                    type="number" value="2023"
                                    :style="{ backgroundColor: surface.inputBg, borderColor: surface.inputBorder, color: surface.textPrimary }"
                                />
                            </div>
                            <div class="space-y-1">
                                <label class="text-xs font-bold uppercase" :style="{ color: theme.accent }">Venue</label>
                                <input 
                                    class="w-full rounded-lg font-display outline-none p-2 border" 
                                    placeholder="Room or Hall" type="text"
                                    :style="{ backgroundColor: surface.inputBg, borderColor: surface.inputBorder, color: surface.textPrimary }"
                                />
                            </div>
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold uppercase" :style="{ color: theme.accent }">Brief Description</label>
                            <textarea 
                                class="w-full rounded-lg font-display outline-none p-3 border resize-none" 
                                placeholder="Describe the event..." rows="3"
                                :style="{ backgroundColor: surface.inputBg, borderColor: surface.inputBorder, color: surface.textPrimary }"
                            ></textarea>
                        </div>
                    </div>
                    
                    <div class="p-6 flex gap-3 justify-end border-t" :style="{ borderColor: surface.borderSubtle, backgroundColor: surface.hoverBg }">
                        <button 
                            @click="showCreateModal = false" 
                            class="px-6 py-2 rounded-lg font-semibold text-sm transition-colors border" 
                            :style="{ borderColor: surface.borderSubtle, color: surface.textPrimary }"
                            @mouseenter="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.backgroundColor = surface.borderSubtle"
                            @mouseleave="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.backgroundColor = 'transparent'"
                        >
                            Cancel
                        </button>
                        <button 
                            class="px-6 py-2 rounded-lg font-semibold text-sm transition-opacity"
                            :style="styles.button"
                            @mouseenter="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.opacity = '0.9'"
                            @mouseleave="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.opacity = '1'"
                        >
                            Publish Event
                        </button>
                    </div>
                </div>
            </div>

            <div 
                v-if="showEventDetailModal" 
                class="fixed inset-0 backdrop-blur-sm z-101 flex items-center justify-center p-4 transition-opacity"
                :style="{ backgroundColor: surface.overlayBg }"
            >
                <div class="w-full max-w-md rounded-2xl shadow-2xl overflow-hidden" :style="styles.cardBg">
                    <div class="relative h-32 flex items-center justify-center" :style="{ backgroundColor: theme.accent }">
                        <span class="material-symbols-outlined text-white text-6xl opacity-30">event_note</span>
                        <button 
                            @click="showEventDetailModal = false" 
                            class="absolute top-4 right-4 size-8 rounded-full bg-black/20 text-white flex items-center justify-center hover:bg-black/40 transition-colors"
                        >
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>
                    <div class="p-8 -mt-10">
                        <div class="p-6 rounded-xl shadow-xl border" :style="{ backgroundColor: surface.cardBg, borderColor: surface.borderSubtle }">
                            <div class="font-bold text-sm mb-1" :style="{ color: theme.accent }">October 3-10, 2023</div>
                            <h3 class="text-2xl font-bold mb-4" :style="styles.textPrimary">Midterm Exam Week</h3>
                            
                            <div class="space-y-4">
                                <div class="flex items-start gap-3">
                                    <span class="material-symbols-outlined mt-0.5" :style="{ color: theme.accent }">location_on</span>
                                    <div>
                                        <p class="text-xs font-bold uppercase" :style="styles.textMuted">Venue</p>
                                        <p class="text-sm" :style="styles.textPrimary">Main Campus Building, All Lecture Halls</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <span class="material-symbols-outlined mt-0.5" :style="{ color: theme.accent }">description</span>
                                    <div>
                                        <p class="text-xs font-bold uppercase" :style="styles.textMuted">Description</p>
                                        <p class="text-sm leading-relaxed" :style="styles.textSecondary">Mandatory evaluation period for all CCIS students. Please refer to your specific department for detailed schedules per subject. Bring valid school ID and permit.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-8 flex gap-2">
                                <button 
                                    class="flex-1 py-2.5 rounded-lg font-bold text-sm transition-opacity"
                                    :style="{ backgroundColor: theme.accent + '20', color: theme.accent }"
                                    @mouseenter="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.opacity = '0.8'"
                                    @mouseleave="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.opacity = '1'"
                                >
                                    Edit Event
                                </button>
                                <button 
                                    class="flex-1 py-2.5 rounded-lg border text-red-500 font-bold text-sm transition-colors"
                                    :style="{ borderColor: 'rgba(239, 68, 68, 0.2)' }"
                                    @mouseenter="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.backgroundColor = 'rgba(239, 68, 68, 0.1)'"
                                    @mouseleave="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.backgroundColor = 'transparent'"
                                >
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </Teleport>

    </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap');
@import url('https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap');

.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }

.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>