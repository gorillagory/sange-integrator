<template>
    <div class="px-5 py-5 sm:px-6">
        <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-3">
                    <div
                        class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-2xl border border-white/10 bg-white/5"
                    >
                        <img
                            v-if="logoUrl"
                            :src="logoUrl"
                            :alt="`${company.name} logo`"
                            class="h-full w-full object-cover"
                        >

                        <div
                            v-else
                            class="text-sm font-black text-white"
                        >
                            {{ companyInitial }}
                        </div>
                    </div>

                    <div class="min-w-0">
                        <div class="truncate text-base font-bold text-white">
                            {{ company.name }}
                        </div>

                        <div class="mt-1 flex flex-wrap items-center gap-2">
                            <span
                                class="rounded-full px-2.5 py-1 text-[11px] font-bold uppercase tracking-[0.18em]"
                                :class="company.is_active
                                    ? 'bg-emerald-500/15 text-emerald-300'
                                    : 'bg-slate-500/15 text-slate-400'"
                            >
                                {{ company.is_active ? 'Active' : 'Inactive' }}
                            </span>

                            <span class="rounded-full bg-white/5 px-2.5 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-300">
                                {{ company.industry }}
                            </span>

                            <span
                                v-if="company.main_group_company?.name"
                                class="rounded-full bg-indigo-500/15 px-2.5 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-indigo-300"
                            >
                                {{ company.main_group_company.name }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="mt-4 grid gap-3 text-sm text-slate-400 md:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <div class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-500">
                            Subdomain
                        </div>
                        <div class="mt-1 font-mono text-slate-200">
                            {{ company.subdomain }}
                        </div>
                    </div>

                    <div>
                        <div class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-500">
                            Database
                        </div>
                        <div class="mt-1 font-mono text-slate-200">
                            {{ company.db_name }}
                        </div>
                    </div>

                    <div>
                        <div class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-500">
                            Registration
                        </div>
                        <div class="mt-1 text-slate-200">
                            {{ company.registration_number || '-' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-500">
                            Theme
                        </div>
                        <div class="mt-1 flex items-center gap-2 text-slate-200">
                            <span
                                class="inline-block h-4 w-4 rounded-full border border-white/10"
                                :style="{ backgroundColor: normalizedThemeColor }"
                            />
                            <span class="font-mono">
                                {{ normalizedThemeColor }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2 xl:justify-end">
                <button
                    type="button"
                    class="inline-flex items-center rounded-2xl border border-white/10 px-4 py-2.5 text-sm font-semibold text-slate-300 transition hover:bg-white/5 hover:text-white"
                    @click="$emit('edit-company', company)"
                >
                    Edit Company
                </button>

                <button
                    v-if="company.main_group_company?.id"
                    type="button"
                    class="inline-flex items-center rounded-2xl border border-white/10 px-4 py-2.5 text-sm font-semibold text-slate-300 transition hover:bg-white/5 hover:text-white"
                    @click="$emit('edit-group', company.main_group_company.id)"
                >
                    Edit Group
                </button>

                <a
                    :href="company.vault_url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-500"
                >
                    Open Vault
                </a>

                <button
                    type="button"
                    class="inline-flex items-center rounded-2xl border border-white/10 px-4 py-2.5 text-sm font-semibold text-slate-300 transition hover:bg-white/5 hover:text-white"
                    @click="copyVaultUrl"
                >
                    Copy URL
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    company: {
        type: Object,
        required: true,
    },
});

defineEmits(['visit-link', 'edit-company', 'edit-group']);

const companyInitial = (props.company.name || 'C').charAt(0).toUpperCase();

const logoUrl = (() => {
    const path = props.company.logo_path;

    if (!path) {
        return null;
    }

    if (String(path).startsWith('http://') || String(path).startsWith('https://')) {
        return path;
    }

    return String(path).startsWith('/storage/')
        ? path
        : `/storage/${String(path).replace(/^storage\//, '')}`;
})();

const normalizedThemeColor = /^#[0-9a-fA-F]{6}$/.test(String(props.company.theme_color || '').trim())
    ? props.company.theme_color
    : '#4f46e5';

const copyVaultUrl = async () => {
    try {
        await navigator.clipboard.writeText(props.company.vault_url);
    } catch {
        window.prompt('Copy vault URL:', props.company.vault_url);
    }
};
</script>
