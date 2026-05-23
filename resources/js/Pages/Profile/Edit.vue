<template>
    <component :is="layoutComponent">
        <template #header>
            <div class="min-w-0">
                <h1 :class="isSystem ? 'text-3xl font-black tracking-tight text-white' : 'text-3xl font-black tracking-tight text-slate-900'">
                    Self Maintenance
                </h1>
                <p :class="isSystem ? 'mt-1 text-sm text-slate-400' : 'mt-1 text-sm text-slate-500'">
                    Keep your identity, credentials, and profile image current.
                </p>
            </div>
        </template>

        <Head :title="pageTitle" />

        <div class="space-y-6">
            <section :class="cardClass">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                    <div class="flex items-start gap-5">
                        <div :class="avatarShellClass">
                            <img
                                v-if="avatarPreview"
                                :src="avatarPreview"
                                :alt="`${profile.name} profile image`"
                                class="h-full w-full object-cover"
                            >
                            <div
                                v-else
                                :class="isSystem ? 'text-2xl font-black text-indigo-200' : 'text-2xl font-black text-slate-700'"
                            >
                                {{ initials }}
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div>
                                <div :class="labelClass">Digital ID</div>
                                <div :class="valueClass">{{ profile.digital_id }}</div>
                            </div>

                            <div>
                                <div :class="labelClass">Role Badges</div>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <span
                                        v-for="badge in allBadges"
                                        :key="badge"
                                        :class="badgeClass"
                                    >
                                        {{ badge }}
                                    </span>
                                    <span v-if="!allBadges.length" :class="mutedClass">No active role badge</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div :class="isSystem ? 'rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-right' : 'rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-right'">
                        <div :class="labelClass">Read-only identity</div>
                        <div :class="valueClass">{{ profile.email }}</div>
                        <div :class="mutedClass">@{{ profile.username }}</div>
                    </div>
                </div>
            </section>

            <section :class="cardClass">
                <div class="mb-5">
                    <h2 :class="sectionTitleClass">Profile & Username</h2>
                    <p :class="sectionSubtitleClass">Update your public-facing identity and personal image.</p>
                </div>

                <form class="space-y-5" @submit.prevent="submitProfile">
                    <div class="grid gap-6 lg:grid-cols-[180px_minmax(0,1fr)]">
                        <div class="space-y-3">
                            <div :class="avatarShellLargeClass">
                                <img
                                    v-if="avatarPreview"
                                    :src="avatarPreview"
                                    :alt="`${profile.name} profile image`"
                                    class="h-full w-full object-cover"
                                >
                                <div
                                    v-else
                                    :class="isSystem ? 'text-2xl font-black text-indigo-200' : 'text-2xl font-black text-slate-700'"
                                >
                                    {{ initials }}
                                </div>
                            </div>

                            <label :class="uploadButtonClass">
                                <input
                                    type="file"
                                    accept="image/*"
                                    class="hidden"
                                    @change="handleImageChange"
                                >
                                {{ selectedImageLabel }}
                            </label>
                            <p :class="helperClass">Square images work best for the shell and documents.</p>
                            <p v-if="profileForm.errors.image" class="text-xs font-medium text-rose-500">{{ profileForm.errors.image }}</p>
                        </div>

                        <div class="grid gap-4 lg:grid-cols-2">
                            <div class="lg:col-span-2">
                                <label :class="labelClass">Display Name</label>
                                <input v-model="profileForm.name" type="text" :class="inputClass">
                                <p v-if="profileForm.errors.name" class="text-xs font-medium text-rose-500">{{ profileForm.errors.name }}</p>
                            </div>

                            <div>
                                <label :class="labelClass">Username</label>
                                <input v-model="profileForm.username" type="text" :class="inputClass">
                                <p :class="helperClass">Letters, numbers, dots, dashes, and underscores only.</p>
                                <p v-if="profileForm.errors.username" class="text-xs font-medium text-rose-500">{{ profileForm.errors.username }}</p>
                            </div>

                            <div>
                                <label :class="labelClass">Email</label>
                                <input v-model="profileForm.email" type="email" :class="inputClass">
                                <p v-if="profileForm.errors.email" class="text-xs font-medium text-rose-500">{{ profileForm.errors.email }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" :disabled="profileForm.processing" :class="primaryButtonClass">
                            {{ profileForm.processing ? 'Saving...' : 'Save Profile' }}
                        </button>
                    </div>
                </form>
            </section>

            <section :class="cardClass">
                <div class="mb-5">
                    <h2 :class="sectionTitleClass">Password Change</h2>
                    <p :class="sectionSubtitleClass">This action is audited for security and accountability.</p>
                </div>

                <form class="grid gap-4 lg:grid-cols-3" @submit.prevent="submitPassword">
                    <div>
                        <label :class="labelClass">Current Password</label>
                        <input v-model="passwordForm.current_password" type="password" :class="inputClass">
                        <p v-if="passwordForm.errors.current_password" class="text-xs font-medium text-rose-500">{{ passwordForm.errors.current_password }}</p>
                    </div>

                    <div>
                        <label :class="labelClass">New Password</label>
                        <input v-model="passwordForm.password" type="password" :class="inputClass">
                        <p v-if="passwordForm.errors.password" class="text-xs font-medium text-rose-500">{{ passwordForm.errors.password }}</p>
                    </div>

                    <div>
                        <label :class="labelClass">Confirm Password</label>
                        <input v-model="passwordForm.password_confirmation" type="password" :class="inputClass">
                        <p v-if="passwordForm.errors.password_confirmation" class="text-xs font-medium text-rose-500">{{ passwordForm.errors.password_confirmation }}</p>
                    </div>

                    <div class="md:col-span-3 flex justify-end">
                        <button type="submit" :disabled="passwordForm.processing" :class="primaryButtonClass">
                            {{ passwordForm.processing ? 'Updating...' : 'Change Password' }}
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </component>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import SystemLayout from '@/Layouts/SystemLayout.vue';
import TenantLayout from '@/Layouts/TenantLayout.vue';

const props = defineProps({
    profile: {
        type: Object,
        required: true,
    },
    roleBadges: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const isSystem = computed(() => Boolean(page.props?.brand?.is_system));
const layoutComponent = computed(() => (isSystem.value ? SystemLayout : TenantLayout));
const pageTitle = computed(() => `Profile | ${isSystem.value ? 'Sange Central' : page.props?.brand?.tenant?.name || 'Workspace'}`);
const avatarPreview = ref(props.profile.image_url || null);
const selectedImageName = ref('');

const profileForm = useForm({
    name: props.profile.name || '',
    username: props.profile.username || '',
    email: props.profile.email || '',
    image: null,
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const allBadges = computed(() => [...(props.roleBadges.global || []), ...(props.roleBadges.tenant || [])]);

const initials = computed(() => (props.profile.name || 'User')
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part.charAt(0).toUpperCase())
    .join(''));

const cardClass = computed(() => isSystem.value
    ? 'rounded-3xl border border-white/10 bg-[#0f172a] p-6 shadow-xl shadow-black/20'
    : 'rounded-3xl border border-slate-200 bg-white p-6 shadow-sm');
const sectionTitleClass = computed(() => isSystem.value ? 'text-xl font-black text-white' : 'text-xl font-black text-slate-900');
const sectionSubtitleClass = computed(() => isSystem.value ? 'mt-1 text-sm text-slate-400' : 'mt-1 text-sm text-slate-500');
const labelClass = computed(() => isSystem.value ? 'mb-1 block text-xs font-bold uppercase tracking-[0.16em] text-slate-400' : 'mb-1 block text-xs font-bold uppercase tracking-[0.16em] text-slate-500');
const valueClass = computed(() => isSystem.value ? 'text-lg font-bold text-white' : 'text-lg font-bold text-slate-900');
const mutedClass = computed(() => isSystem.value ? 'text-sm text-slate-400' : 'text-sm text-slate-500');
const helperClass = computed(() => isSystem.value ? 'text-xs text-slate-500' : 'text-xs text-slate-400');
const badgeClass = computed(() => isSystem.value
    ? 'inline-flex items-center rounded-full border border-indigo-500/30 bg-indigo-500/15 px-3 py-1 text-xs font-bold uppercase tracking-[0.14em] text-indigo-200'
    : 'inline-flex items-center rounded-full border border-[var(--brand-200)] bg-[var(--brand-50)] px-3 py-1 text-xs font-bold uppercase tracking-[0.14em] text-[var(--brand-700)]');
const avatarShellClass = computed(() => isSystem.value
    ? 'flex h-28 w-28 items-center justify-center overflow-hidden rounded-3xl border border-white/10 bg-white/5'
    : 'flex h-28 w-28 items-center justify-center overflow-hidden rounded-3xl border border-slate-200 bg-slate-50');
const avatarShellLargeClass = computed(() => isSystem.value
    ? 'flex h-40 w-40 items-center justify-center overflow-hidden rounded-3xl border border-white/10 bg-white/5 shadow-inner'
    : 'flex h-40 w-40 items-center justify-center overflow-hidden rounded-3xl border border-slate-200 bg-slate-50 shadow-sm');
const inputClass = computed(() => isSystem.value
    ? 'w-full rounded-2xl border border-white/10 bg-black/20 px-4 py-3 text-sm text-white outline-none transition placeholder:text-slate-500 focus:border-indigo-500'
    : 'w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-[var(--brand-500)]');
const primaryButtonClass = computed(() => isSystem.value
    ? 'inline-flex items-center justify-center rounded-2xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-60'
    : 'inline-flex items-center justify-center rounded-2xl bg-[var(--brand-600)] px-5 py-3 text-sm font-bold text-white transition hover:bg-[var(--brand-500)] disabled:cursor-not-allowed disabled:opacity-60');
const uploadButtonClass = computed(() => isSystem.value
    ? 'inline-flex w-full cursor-pointer items-center justify-center rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm font-semibold text-white transition hover:bg-white/10'
    : 'inline-flex w-full cursor-pointer items-center justify-center rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50');
const selectedImageLabel = computed(() => selectedImageName.value || 'Upload Profile Image');

const handleImageChange = (event) => {
    const [file] = event.target.files || [];
    profileForm.image = file || null;
    selectedImageName.value = file?.name || '';

    if (file) {
        avatarPreview.value = URL.createObjectURL(file);
    }
};

const submitProfile = () => {
    profileForm
        .transform((data) => ({
            ...data,
            _method: 'patch',
        }))
        .post('/profile', {
            forceFormData: true,
            preserveScroll: true,
        });
};

const submitPassword = () => {
    passwordForm.put('/password', {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
        },
    });
};
</script>
