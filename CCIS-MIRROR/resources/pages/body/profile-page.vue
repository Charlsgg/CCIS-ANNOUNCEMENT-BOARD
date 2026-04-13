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

// Add these to your existing refs
const showPasswordModal = ref(false)
const currentPassword = ref('')
const newPassword = ref('')
const confirmPassword = ref('')
const isUpdatingPassword = ref(false)
const passwordError = ref('')

const showPassword = ref(false)

const updatePassword = async () => {
    isUpdatingPassword.value = true
    passwordError.value = ''

    if (newPassword.value !== confirmPassword.value) {
        passwordError.value = 'New passwords do not match.'
        isUpdatingPassword.value = false
        return
    }

    try {
        const response = await fetch('/api/profile/password', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken.value
            },
            body: JSON.stringify({
                current_password: currentPassword.value,
                password: newPassword.value,
                password_confirmation: confirmPassword.value
            })
        })

        const data = await response.json()

        if (response.ok) {
            alert('Password updated successfully!')
            showPasswordModal.value = false
            currentPassword.value = ''
            newPassword.value = ''
            confirmPassword.value = ''
        } else {
            // Handle validation errors from Laravel
            if (data.errors) {
                const firstErrorKey = Object.keys(data.errors)[0]
                passwordError.value = data.errors[firstErrorKey][0]
            } else {
                passwordError.value = data.message || 'Failed to update password.'
            }
        }
    } catch (error) {
        console.error('Error updating password:', error)
        passwordError.value = 'A network error occurred.'
    } finally {
        isUpdatingPassword.value = false
    }
}
const name = ref('')
const email = ref('')
const profilePictureUrl = ref('https://ui-avatars.com/api/?name=User&size=200&background=random')
const isSaving = ref(false)

// File Upload State
const fileInput = ref<HTMLInputElement | null>(null)
const selectedFile = ref<File | null>(null)

// 1. Fetch profile data when the page loads
const fetchProfileData = async () => {
    try {
        const response = await fetch('/api/profile', {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken.value
            }
        })

        if (response.ok) {
            const data = await response.json()
            name.value = data.user.name
            email.value = data.user.email
            if (data.user.profile_picture) {
                profilePictureUrl.value = data.user.profile_picture
            } else {
                profilePictureUrl.value = `https://ui-avatars.com/api/?name=${encodeURIComponent(data.user.name)}&size=200&background=random`
            }
        }
    } catch (error) {
        console.error('Failed to fetch profile:', error)
    }
}

// 2. Handle file selection for local preview
const triggerFileInput = () => {
    fileInput.value?.click()
}

const handleFileUpload = (event: Event) => {
    const target = event.target as HTMLInputElement
    if (target.files && target.files.length > 0) {
        selectedFile.value = target.files[0]
        // Create a temporary URL to show the image preview immediately
        profilePictureUrl.value = URL.createObjectURL(selectedFile.value)
    }
}

// 3. Submit form data to the Laravel API
const saveProfile = async () => {
    isSaving.value = true

    const formData = new FormData()
    formData.append('name', name.value)
    formData.append('email', email.value)

    // Crucial: Only append the file if the user actually picked a new one
    if (selectedFile.value) {
        formData.append('profile_picture', selectedFile.value)
    }

    try {
        const response = await fetch('/api/profile', {
            method: 'POST', // Matches your public function update(Request $request)
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken.value
            },
            body: formData
        })

        const data = await response.json()

        if (response.ok) {
            alert('Profile updated successfully!')

            // If the backend returns the new Supabase URL, update the view
            if (data.profile_picture) {
                profilePictureUrl.value = data.profile_picture
            }

            // Clear the file selection so we don't send the same file twice if they click save again
            selectedFile.value = null
        } else {
            // Handle Laravel validation errors (e.g., email already taken)
            if (data.errors) {
                const errorMessage = Object.values(data.errors).flat().join('\n')
                alert(errorMessage)
            } else {
                alert(data.message || 'Failed to update profile.')
            }
        }
    } catch (error) {
        console.error('Error saving profile:', error)
        alert('A network error occurred. Please try again.')
    } finally {
        isSaving.value = false
    }
}

onMounted(() => {
    initTheme()
    if (props.user?.user_type) {
        setUserType(props.user.user_type)
    }

    const tokenTag = document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement
    if (tokenTag) {
        csrfToken.value = tokenTag.content
    }

    fetchProfileData()
})
</script>

<template>
    <div class="fixed inset-0 w-full h-full overflow-hidden font-['Space_Grotesk'] flex transition-colors duration-300"
        :style="{ ...styles.pageBg, color: surface.textPrimary }">
        <div v-if="isSidebarOpen" @click="isSidebarOpen = false"
            class="absolute inset-0 z-40 md:hidden backdrop-blur-sm transition-opacity"
            :style="{ backgroundColor: surface.overlayBg }"></div>

        <AppSidebar :is-open="isSidebarOpen" :csrf-token="csrfToken" @close="isSidebarOpen = false" />

        <main class="flex-1 flex flex-col h-full overflow-hidden min-w-0">
            <AppNavbar @toggle-sidebar="isSidebarOpen = true" />

            <div class="flex-1 overflow-y-auto p-4 md:p-8 w-full no-scrollbar">
                <div class="max-w-7xl mx-auto pb-12">

                    <div class="mb-10">
                        <h1 class="text-3xl lg:text-4xl font-black tracking-tight" :style="styles.textPrimary">Edit
                            Profile</h1>
                        <p class="mt-2 text-lg" :style="styles.textSecondary">Update your professional information for
                            the faculty directory.</p>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                        <div class="lg:col-span-1">
                            <div class="rounded-2xl shadow-sm p-8 flex flex-col items-center text-center transition-colors"
                                :style="styles.cardBg">

                                <div class="relative mb-6">
                                    <div class="h-40 w-40 rounded-full ring-4 overflow-hidden flex items-center justify-center"
                                        :style="{ backgroundColor: surface.inputBg, '--tw-ring-color': surface.borderSubtle }">
                                        <img class="h-full w-full object-cover" alt="Profile Avatar"
                                            :src="profilePictureUrl" />
                                    </div>
                                    <button @click="triggerFileInput"
                                        class="absolute bottom-2 right-2 p-2.5 rounded-full shadow-lg border-4 hover:scale-110 transition-transform flex items-center justify-center text-white"
                                        :style="{ backgroundColor: theme.accent, borderColor: surface.cardBg }">
                                        <span class="material-symbols-outlined text-[20px]">photo_camera</span>
                                    </button>
                                </div>

                                <h2 class="text-2xl font-bold" :style="styles.textPrimary">{{ name }}</h2>
                                <p class="text-sm uppercase font-bold mt-1" :style="{ color: theme.accent }">{{
                                    theme.abbr }}</p>

                                <div class="w-full border-t my-6" :style="{ borderColor: surface.borderSubtle }"></div>

                                <input type="file" ref="fileInput" class="hidden"
                                    accept="image/jpeg, image/png, image/jpg" @change="handleFileUpload" />

                                <button @click="triggerFileInput"
                                    class="w-full flex items-center justify-center gap-2 px-6 py-3 rounded-xl font-bold text-sm transition-all border"
                                    :style="{ backgroundColor: surface.inputBg, borderColor: surface.borderSubtle, color: surface.textPrimary }"
                                    @mouseenter="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.borderColor = theme.accent"
                                    @mouseleave="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.borderColor = surface.borderSubtle">
                                    <span class="material-symbols-outlined text-[20px]">upload_file</span>
                                    Change Photo
                                </button>
                            </div>
                        </div>

                        <div class="lg:col-span-2 flex flex-col gap-8">

                            <div class="rounded-2xl shadow-sm p-8 transition-colors" :style="styles.cardBg">

                                <h3 class="text-lg font-bold mb-6 flex items-center gap-2" :style="styles.textPrimary">
                                    <span class="material-symbols-outlined"
                                        :style="{ color: theme.accent }">person</span>
                                    Personal Information
                                </h3>

                                <form @submit.prevent="saveProfile" class="space-y-6">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="flex flex-col gap-2">
                                            <label class="text-sm font-bold" :style="styles.textSecondary">Full
                                                Name</label>
                                            <input v-model="name"
                                                class="w-full px-4 py-3.5 rounded-xl border focus:ring-2 focus:outline-none transition-all"
                                                type="text" required
                                                :style="{ backgroundColor: surface.inputBg, borderColor: surface.inputBorder, color: surface.textPrimary }"
                                                @focus="(e: FocusEvent) => (e.currentTarget as HTMLElement).style.borderColor = theme.accent"
                                                @blur="(e: FocusEvent) => (e.currentTarget as HTMLElement).style.borderColor = surface.inputBorder" />
                                        </div>
                                        <div class="flex flex-col gap-2">
                                            <label class="text-sm font-bold" :style="styles.textSecondary">Email
                                                Address</label>
                                            <input v-model="email"
                                                class="w-full px-4 py-3.5 rounded-xl border focus:ring-2 focus:outline-none transition-all"
                                                type="email" required
                                                :style="{ backgroundColor: surface.inputBg, borderColor: surface.inputBorder, color: surface.textPrimary }"
                                                @focus="(e: FocusEvent) => (e.currentTarget as HTMLElement).style.borderColor = theme.accent"
                                                @blur="(e: FocusEvent) => (e.currentTarget as HTMLElement).style.borderColor = surface.inputBorder" />
                                        </div>
                                    </div>
                                    <div v-if="showPasswordModal"
                                        class="fixed inset-0 z-50 flex items-center justify-center p-4 backdrop-blur-sm"
                                        :style="{ backgroundColor: surface.overlayBg }">
                                        <div class="w-full max-w-md p-8 rounded-2xl shadow-xl transition-colors"
                                            :style="styles.cardBg">
                                            <h3 class="text-xl font-bold mb-6" :style="styles.textPrimary">Change
                                                Password</h3>

                                            <form @submit.prevent="updatePassword" class="space-y-4">
                                                <div v-if="passwordError"
                                                    class="p-3 rounded-lg text-sm bg-red-100 text-red-700 border border-red-200">
                                                    {{ passwordError }}
                                                </div>

                                                <div class="flex flex-col gap-2">
                                                    <label class="text-sm font-bold"
                                                        :style="styles.textSecondary">Current Password</label>
                                                    <div class="relative">
                                                        <input v-model="currentPassword"
                                                            :type="showPassword ? 'text' : 'password'" required
                                                            class="w-full px-4 py-3 rounded-xl border focus:ring-2 focus:outline-none pr-12"
                                                            :style="{ backgroundColor: surface.inputBg, borderColor: surface.inputBorder, color: surface.textPrimary }" />
                                                        <button type="button" @click="showPassword = !showPassword"
                                                            class="absolute right-3 top-1/2 -translate-y-1/2 p-1 flex items-center justify-center opacity-60 hover:opacity-100 transition-opacity"
                                                            :style="{ color: surface.textPrimary }">
                                                            <span class="material-symbols-outlined text-[20px]">
                                                                {{ showPassword ? 'visibility_off' : 'visibility' }}
                                                            </span>
                                                        </button>
                                                    </div>
                                                </div>

                                                <div class="flex flex-col gap-2">
                                                    <label class="text-sm font-bold" :style="styles.textSecondary">New
                                                        Password</label>
                                                    <div class="relative">
                                                        <input v-model="newPassword"
                                                            :type="showPassword ? 'text' : 'password'" required
                                                            minlength="8"
                                                            class="w-full px-4 py-3 rounded-xl border focus:ring-2 focus:outline-none pr-12"
                                                            :style="{ backgroundColor: surface.inputBg, borderColor: surface.inputBorder, color: surface.textPrimary }" />
                                                    </div>
                                                </div>

                                                <div class="flex flex-col gap-2">
                                                    <label class="text-sm font-bold"
                                                        :style="styles.textSecondary">Confirm New Password</label>
                                                    <div class="relative">
                                                        <input v-model="confirmPassword"
                                                            :type="showPassword ? 'text' : 'password'" required
                                                            minlength="8"
                                                            class="w-full px-4 py-3 rounded-xl border focus:ring-2 focus:outline-none pr-12"
                                                            :style="{ backgroundColor: surface.inputBg, borderColor: surface.inputBorder, color: surface.textPrimary }" />
                                                    </div>
                                                </div>

                                                <div class="pt-4 flex justify-end gap-3">
                                                    <button type="button" @click="showPasswordModal = false"
                                                        class="px-6 py-2.5 font-bold rounded-xl"
                                                        :style="styles.textSecondary">
                                                        Cancel
                                                    </button>
                                                    <button type="submit" :disabled="isUpdatingPassword"
                                                        class="px-6 py-2.5 font-bold rounded-xl shadow-md disabled:opacity-50"
                                                        :style="styles.button">
                                                        {{ isUpdatingPassword ? 'Updating...' : 'Save Password' }}
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="pt-8 border-t mt-8" :style="{ borderColor: surface.borderSubtle }">
                                        <h3 class="text-lg font-bold mb-6 flex items-center gap-2"
                                            :style="styles.textPrimary">
                                            <span class="material-symbols-outlined"
                                                :style="{ color: theme.accent }">security</span>
                                            Security & Access
                                        </h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div class="flex flex-col gap-2">
                                                <label class="text-sm font-bold"
                                                    :style="styles.textSecondary">Password</label>
                                                <button @click="showPasswordModal = true"
                                                    class="flex items-center justify-between w-full px-4 py-3.5 rounded-xl border transition-all text-left"
                                                    type="button"
                                                    :style="{ backgroundColor: surface.inputBg, borderColor: surface.inputBorder }">
                                                    <span :style="styles.textMuted">••••••••••••</span>
                                                    <span class="text-xs font-bold"
                                                        :style="{ color: theme.accent }">Update</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="pt-8 flex justify-end gap-4">
                                        <button class="px-8 py-3.5 font-bold rounded-xl transition-all" type="button"
                                            :style="styles.textSecondary"
                                            @mouseenter="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.backgroundColor = surface.hoverBg"
                                            @mouseleave="(e: MouseEvent) => (e.currentTarget as HTMLElement).style.backgroundColor = 'transparent'">
                                            Cancel
                                        </button>
                                        <button
                                            class="py-3.5 px-10 rounded-xl font-bold shadow-lg transition-all active:scale-[0.98] disabled:opacity-50"
                                            type="submit" :disabled="isSaving" :style="styles.button"
                                            @mouseenter="(e: MouseEvent) => { if (!isSaving) (e.currentTarget as HTMLElement).style.opacity = '0.9' }"
                                            @mouseleave="(e: MouseEvent) => { if (!isSaving) (e.currentTarget as HTMLElement).style.opacity = '1' }">
                                            {{ isSaving ? 'Saving...' : 'Save Changes' }}
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <div class="p-6 rounded-2xl border flex items-start gap-4 transition-colors"
                                :style="{ backgroundColor: theme.accent + '10', borderColor: theme.accent + '20' }">
                                <span class="material-symbols-outlined mt-0.5"
                                    :style="{ color: theme.accent }">info</span>
                                <div>
                                    <h4 class="text-sm font-bold" :style="styles.textPrimary">Need to update more info?
                                    </h4>
                                    <p class="text-sm mt-1 leading-relaxed" :style="styles.textSecondary">
                                        For changes to Department, Rank, or Employee ID, please contact the
                                        administrative office directly.
                                    </p>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </main>
    </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap');
@import url('https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap');

.material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
}

.no-scrollbar::-webkit-scrollbar {
    display: none;
}

.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>