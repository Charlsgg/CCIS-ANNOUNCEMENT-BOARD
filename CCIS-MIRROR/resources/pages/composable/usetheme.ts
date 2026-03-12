import { ref, computed, watch, onMounted } from 'vue'

// ─── Types ───────────────────────────────────────────────────────
export interface ThemeColors {
    label: string
    abbr: string
    accent: string
    dashTitle: string
    announcementHeading: string
    announcementSubheading: string
    dashboardPath: string
}

export type Mode = 'light' | 'dark'

// ─── Shared Reactive State (singleton across components) ────────
const mode = ref<Mode>('dark')
const userType = ref<string>('is_instructor')

// ─── Theme Map ──────────────────────────────────────────────────
const themeMap: Record<string, ThemeColors> = {
    it_instructor: {
        label: 'Information Technology Society',
        abbr: 'ITS',
        accent: '#3b82f6',
        dashTitle: 'ITS Dashboard',
        announcementHeading: 'Information Technology Announcements',
        announcementSubheading: 'Stay updated with the latest IT academic updates.',
        dashboardPath: '/it/dashboard',
    },
    cs_instructor: {
        label: 'Computer Science',
        abbr: 'CSS',
        accent: '#9a0303',
        dashTitle: 'CSS Dashboard',
        announcementHeading: 'Computer Science Announcements',
        announcementSubheading: 'Stay updated with the latest CS academic updates.',
        dashboardPath: '/cs/dashboard',
    },
    is_instructor: {
        label: 'Information System',
        abbr: 'ISS',
        accent: '#32DD41',
        dashTitle: 'ISS Dashboard',
        announcementHeading: 'Information System Announcements',
        announcementSubheading: 'Stay updated with the latest IS academic updates.',
        dashboardPath: '/is/dashboard',
    },
    lsg_officer: {
        label: 'CCIS Local Student Government',
        abbr: 'LSG',
        accent: '#ec5b13',
        dashTitle: 'LSG Dashboard',
        announcementHeading: 'LSG Announcements',
        announcementSubheading: 'Stay updated with the latest student government updates.',
        dashboardPath: '/lsg/dashboard',
    },
}

// ─── Composable ─────────────────────────────────────────────────
export function useTheme() {
    const theme = computed<ThemeColors>(() => {
        return themeMap[userType.value] ?? themeMap['is_instructor']
    })

    const isDark = computed(() => mode.value === 'dark')

    // ─── Surface Colors (dark vs light) ─────────────────────────
    const surface = computed(() => {
        const accent = theme.value.accent
        if (isDark.value) {
            return {
                pageBg: '#0f0f0f',
                sidebarBg: '#141414',
                cardBg: '#1a1a1a',
                headerBg: '#141414',
                textPrimary: '#f1f5f9',
                textSecondary: '#94a3b8',
                textMuted: '#64748b',
                borderSubtle: accent + '10',
                borderMedium: accent + '20',
                borderStrong: accent + '30',
                hoverBg: accent + '10',
                inputBg: '#1a1a1a',
                inputBorder: accent + '20',
                overlayBg: 'rgba(0,0,0,0.8)',
            }
        }
        return {
            pageBg: '#f8fafc',
            sidebarBg: '#ffffff',
            cardBg: '#ffffff',
            headerBg: '#ffffff',
            textPrimary: '#0f172a',
            textSecondary: '#475569',
            textMuted: '#94a3b8',
            borderSubtle: '#e2e8f0',
            borderMedium: '#cbd5e1',
            borderStrong: accent + '40',
            hoverBg: accent + '08',
            inputBg: '#f8fafc',
            inputBorder: '#e2e8f0',
            overlayBg: 'rgba(0,0,0,0.5)',
        }
    })

    // ─── Precomputed Style Objects ──────────────────────────────
    const styles = computed(() => {
        const accent = theme.value.accent
        const s = surface.value

        return {
            // Backgrounds
            pageBg: { backgroundColor: s.pageBg },
            sidebarBg: { backgroundColor: s.sidebarBg, borderRight: `1px solid ${s.borderSubtle}` },
            cardBg: { backgroundColor: s.cardBg, border: `1px solid ${s.borderMedium}` },
            headerBg: { backgroundColor: s.headerBg, borderBottom: `1px solid ${s.borderSubtle}` },

            // Badge
            badge: {
                backgroundColor: accent + '15',
                borderColor: accent + '30',
                color: accent,
                boxShadow: `0 4px 12px ${accent}15`,
            },

            // Sidebar active
            sidebarActive: {
                backgroundColor: accent,
                color: '#ffffff',
                boxShadow: `0 4px 12px ${accent}33`,
            },
            sidebarBorderLeft: { borderColor: accent + '20' },

            // Icon
            iconBg: { backgroundColor: accent + '10', color: accent },
            iconColor: { color: accent },

            // Button
            button: {
                backgroundColor: accent,
                color: '#ffffff',
                boxShadow: `0 4px 12px ${accent}33`,
            },
            buttonHover: {
                backgroundColor: accent + 'dd',
            },

            // Avatar
            avatar: {
                backgroundColor: accent,
                color: '#ffffff',
                boxShadow: `0 4px 8px ${accent}33`,
                border: '2px solid rgba(255,255,255,0.1)',
            },

            // Dot
            dot: { backgroundColor: accent },

            // Card icon
            cardIcon: {
                backgroundColor: isDark.value ? s.pageBg : accent + '08',
                borderColor: accent + '20',
                color: accent,
            },

            // Text
            textPrimary: { color: s.textPrimary },
            textSecondary: { color: s.textSecondary },
            textMuted: { color: s.textMuted },

            // Input
            input: {
                backgroundColor: 'transparent',
                color: s.textPrimary,
                borderBottom: `1px solid ${s.borderMedium}`,
            },

            // Composer border
            composerBorder: { borderTop: `1px solid ${s.borderSubtle}` },

            // Announcement card border
            cardBorder: `1px solid ${s.borderSubtle}`,
            cardBorderHover: `1px solid ${s.borderStrong}`,

            // Divider inside card
            cardDivider: { borderBottom: `1px solid ${s.borderSubtle}` },
        }
    })

    // ─── Toggle Mode ────────────────────────────────────────────
    const toggleMode = () => {
        mode.value = mode.value === 'dark' ? 'light' : 'dark'
        localStorage.setItem('theme-mode', mode.value)
    }

    // ─── Set User Type ──────────────────────────────────────────
    const setUserType = (type: string) => {
        userType.value = type
    }

    // ─── Initialize from localStorage ──────────────────────────
    const initTheme = () => {
        const saved = localStorage.getItem('theme-mode') as Mode | null
        if (saved) {
            mode.value = saved
        } else {
            // Respect system preference
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
            mode.value = prefersDark ? 'dark' : 'light'
        }
    }

    return {
        mode,
        isDark,
        theme,
        surface,
        styles,
        userType,
        toggleMode,
        setUserType,
        initTheme,
    }
}