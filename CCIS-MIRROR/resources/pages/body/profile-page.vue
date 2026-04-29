<script setup lang="ts">
import { ref, onMounted, reactive } from 'vue'
import { useTheme } from '../composable/usetheme.ts'

const props = defineProps<{
    user?: { name: string; email: string; user_type: string }
    csrfToken?: string;
}>()

const { theme, styles, surface, isDark, setUserType, initTheme } = useTheme()

const csrfToken = ref('')

// Password Modal State
const showPasswordModal = ref(false)
const currentPassword = ref('')
const newPassword = ref('')
const confirmPassword = ref('')
const isUpdatingPassword = ref(false)
const passwordError = ref('')

// Independent visibility states
const visibility = reactive({
    current: false,
    new: false,
    confirm: false
})

const toggleVisibility = (field: 'current' | 'new' | 'confirm') => {
    visibility[field] = !visibility[field]
}

const closePasswordModal = () => {
    showPasswordModal.value = false
    // Reset fields and visibility when closing
    currentPassword.value = ''
    newPassword.value = ''
    confirmPassword.value = ''
    visibility.current = false
    visibility.new = false
    visibility.confirm = false
}

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
            closePasswordModal()
        } else {
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

// Profile Info State
const name = ref('')
const email = ref('')
const profilePictureUrl = ref('https://ui-avatars.com/api/?name=User&size=200&background=random')
const isSaving = ref(false)
const fileInput = ref<HTMLInputElement | null>(null)
const selectedFile = ref<File | null>(null)

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
            profilePictureUrl.value = data.user.profile_picture || `https://ui-avatars.com/api/?name=${encodeURIComponent(data.user.name)}&size=200&background=random`
        }
    } catch (error) {
        console.error('Failed to fetch profile:', error)
    }
}

const triggerFileInput = () => fileInput.value?.click()

const handleFileUpload = (event: Event) => {
    const target = event.target as HTMLInputElement
    if (target.files && target.files.length > 0) {
        selectedFile.value = target.files[0]
        profilePictureUrl.value = URL.createObjectURL(selectedFile.value)
    }
}

const saveProfile = async () => {
    isSaving.value = true
    const formData = new FormData()
    formData.append('name', name.value)
    formData.append('email', email.value)
    if (selectedFile.value) formData.append('profile_picture', selectedFile.value)

    try {
        const response = await fetch('/api/profile', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken.value
            },
            body: formData
        })
        const data = await response.json()
        if (response.ok) {
            alert('Profile updated successfully!')
            if (data.profile_picture) profilePictureUrl.value = data.profile_picture
            selectedFile.value = null
        } else {
            alert(data.errors ? Object.values(data.errors).flat().join('\n') : (data.message || 'Failed to update profile.'))
        }
    } catch (error) {
        console.error('Error saving profile:', error)
        alert('A network error occurred.')
    } finally {
        isSaving.value = false
    }
}

onMounted(() => {
    initTheme()
    if (props.user?.user_type) setUserType(props.user.user_type)
    const tokenTag = document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement
    if (tokenTag) csrfToken.value = tokenTag.content
    fetchProfileData()
})
</script>

<template>
    <div class="max-w-7xl mx-auto pb-12 w-full min-w-0">
        <div class="mb-10">
            <h1 class="text-3xl lg:text-4xl font-black tracking-tight" :style="styles.textPrimary">Edit Profile</h1>
            <p class="mt-2 text-lg" :style="styles.textSecondary">Update your professional information.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-1">
                <div class="rounded-2xl shadow-sm p-8 flex flex-col items-center text-center transition-colors" :style="styles.cardBg">
                    <div class="relative mb-6">
                        <div class="h-40 w-40 rounded-full ring-4 overflow-hidden flex items-center justify-center"
                            :style="{ backgroundColor: surface.inputBg, '--tw-ring-color': surface.borderSubtle }">
                            <img class="h-full w-full object-cover" alt="Profile Avatar" :src="profilePictureUrl" />
                        </div>
                        <button @click="triggerFileInput"
                            class="absolute bottom-2 right-2 p-2.5 rounded-full shadow-lg border-4 hover:scale-110 transition-transform flex items-center justify-center text-white"
                            :style="{ backgroundColor: theme.accent, borderColor: surface.cardBg }">
                            <span class="material-symbols-outlined text-[20px]">photo_camera</span>
                        </button>
                    </div>
                    <h2 class="text-2xl font-bold" :style="styles.textPrimary">{{ name }}</h2>
                    <p class="text-sm uppercase font-bold mt-1" :style="{ color: theme.accent }">{{ theme.abbr }}</p>
                    <div class="w-full border-t my-6" :style="{ borderColor: surface.borderSubtle }"></div>
                    <input type="file" ref="fileInput" class="hidden" accept="image/jpeg, image/png, image/jpg" @change="handleFileUpload" />
                    <button @click="triggerFileInput"
                        class="w-full flex items-center justify-center gap-2 px-6 py-3 rounded-xl font-bold text-sm transition-all border"
                        :style="{ backgroundColor: surface.inputBg, borderColor: surface.borderSubtle, color: surface.textPrimary }">
                        <span class="material-symbols-outlined text-[20px]">upload_file</span>
                        Change Photo
                    </button>
                </div>
            </div>

            <div class="lg:col-span-2 flex flex-col gap-8">
                <div class="rounded-2xl shadow-sm p-8 transition-colors" :style="styles.cardBg">
                    <h3 class="text-lg font-bold mb-6 flex items-center gap-2" :style="styles.textPrimary">
                        <span class="material-symbols-outlined" :style="{ color: theme.accent }">person</span>
                        Personal Information
                    </h3>

                    <form @submit.prevent="saveProfile" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-bold" :style="styles.textSecondary">Full Name</label>
                                <input v-model="name" class="w-full px-4 py-3.5 rounded-xl border focus:ring-2 focus:outline-none"
                                    type="text" required :style="{ backgroundColor: surface.inputBg, borderColor: surface.inputBorder, color: surface.textPrimary }" />
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-bold" :style="styles.textSecondary">Email Address</label>
                                <input v-model="email" class="w-full px-4 py-3.5 rounded-xl border focus:ring-2 focus:outline-none"
                                    type="email" required :style="{ backgroundColor: surface.inputBg, borderColor: surface.inputBorder, color: surface.textPrimary }" />
                            </div>
                        </div>

                        <div class="pt-8 border-t mt-8" :style="{ borderColor: surface.borderSubtle }">
                            <h3 class="text-lg font-bold mb-6 flex items-center gap-2" :style="styles.textPrimary">
                                <span class="material-symbols-outlined" :style="{ color: theme.accent }">security</span>
                                Security & Access
                            </h3>
                            <div class="flex flex-col gap-2 max-w-sm">
                                <label class="text-sm font-bold" :style="styles.textSecondary">Password</label>
                                <button @click="showPasswordModal = true" type="button"
                                    class="flex items-center justify-between w-full px-4 py-3.5 rounded-xl border transition-all"
                                    :style="{ backgroundColor: surface.inputBg, borderColor: surface.inputBorder }">
                                    <span :style="styles.textMuted">••••••••••••</span>
                                    <span class="text-xs font-bold" :style="{ color: theme.accent }">Update</span>
                                </button>
                            </div>
                        </div>
                        
                        <div class="pt-8 flex justify-end gap-4">
                            <button class="py-3.5 px-10 rounded-xl font-bold shadow-lg transition-all active:scale-[0.98] disabled:opacity-50" 
                                type="submit" :disabled="isSaving" :style="styles.button">
                                {{ isSaving ? 'Saving...' : 'Save Changes' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <Teleport to="body">
        <div v-if="showPasswordModal" class="fixed inset-0 z-100 flex items-center justify-center p-4 backdrop-blur-sm"
            :style="{ backgroundColor: surface.overlayBg }">
            <div class="w-full max-w-md p-8 rounded-2xl shadow-xl transition-colors" :style="styles.cardBg">
                <h3 class="text-xl font-bold mb-6" :style="styles.textPrimary">Change Password</h3>
                <form @submit.prevent="updatePassword" class="space-y-4">
                    <div v-if="passwordError" class="p-3 rounded-lg text-sm bg-red-100 text-red-700 border border-red-200">
                        {{ passwordError }}
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-bold" :style="styles.textSecondary">Current Password</label>
                        <div class="relative">
                            <input v-model="currentPassword" :type="visibility.current ? 'text' : 'password'" required
                                class="w-full px-4 py-3 rounded-xl border focus:ring-2 focus:outline-none pr-12"
                                :style="{ backgroundColor: surface.inputBg, borderColor: surface.inputBorder, color: surface.textPrimary }" />
                            <button type="button" @click="toggleVisibility('current')"
                                class="absolute right-3 top-1/2 -translate-y-1/2 p-1 flex items-center justify-center opacity-60 hover:opacity-100 transition-opacity">
                                <span class="material-symbols-outlined text-[20px]" :style="{ color: surface.textPrimary }">
                                    {{ visibility.current ? 'visibility_off' : 'visibility' }}
                                </span>
                            </button>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-bold" :style="styles.textSecondary">New Password</label>
                        <div class="relative">
                            <input v-model="newPassword" :type="visibility.new ? 'text' : 'password'" required minlength="8"
                                class="w-full px-4 py-3 rounded-xl border focus:ring-2 focus:outline-none pr-12"
                                :style="{ backgroundColor: surface.inputBg, borderColor: surface.inputBorder, color: surface.textPrimary }" />
                            <button type="button" @click="toggleVisibility('new')"
                                class="absolute right-3 top-1/2 -translate-y-1/2 p-1 flex items-center justify-center opacity-60 hover:opacity-100 transition-opacity">
                                <span class="material-symbols-outlined text-[20px]" :style="{ color: surface.textPrimary }">
                                    {{ visibility.new ? 'visibility_off' : 'visibility' }}
                                </span>
                            </button>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-bold" :style="styles.textSecondary">Confirm New Password</label>
                        <div class="relative">
                            <input v-model="confirmPassword" :type="visibility.confirm ? 'text' : 'password'" required minlength="8"
                                class="w-full px-4 py-3 rounded-xl border focus:ring-2 focus:outline-none pr-12"
                                :style="{ backgroundColor: surface.inputBg, borderColor: surface.inputBorder, color: surface.textPrimary }" />
                            <button type="button" @click="toggleVisibility('confirm')"
                                class="absolute right-3 top-1/2 -translate-y-1/2 p-1 flex items-center justify-center opacity-60 hover:opacity-100 transition-opacity">
                                <span class="material-symbols-outlined text-[20px]" :style="{ color: surface.textPrimary }">
                                    {{ visibility.confirm ? 'visibility_off' : 'visibility' }}
                                </span>
                            </button>
                        </div>
                    </div>

                    <div class="pt-4 flex justify-end gap-3">
                        <button type="button" @click="closePasswordModal" class="px-6 py-2.5 font-bold rounded-xl" :style="styles.textSecondary">Cancel</button>
                        <button type="submit" :disabled="isUpdatingPassword" class="px-6 py-2.5 font-bold rounded-xl shadow-md disabled:opacity-50" :style="styles.button">
                            {{ isUpdatingPassword ? 'Updating...' : 'Save Password' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Teleport>
</template>

<style>
/* Removed global font imports assuming they are now inside MainLayout.vue or index.html */
</style>