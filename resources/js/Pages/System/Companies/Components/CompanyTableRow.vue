<template>
    <tr class="transition hover:bg-white/[0.03]">
        <td class="px-6 py-5 align-top">
            <div class="flex items-start gap-4">
                <div class="h-12 w-12 overflow-hidden rounded-xl border border-white/10 bg-slate-950">
                    <img
                        v-if="logoUrl"
                        :src="logoUrl"
                        alt="Company logo"
                        class="h-full w-full object-cover"
                    >
                    <div v-else class="flex h-full w-full items-center justify-center text-xs font-bold text-slate-500">
                        N/A
                    </div>
                </div>

                <div>
                    <div class="font-semibold text-white">{{ company.name }}</div>
                    <div class="mt-1 text-sm text-slate-400">{{ company.registration_number || 'No registration number' }}</div>
                    <div class="mt-2 text-xs text-slate-500">{{ company.subdomain }}.bayam.test</div>
                </div>
            </div>
        </td>

        <td class="px-6 py-5 align-top">
            <div class="font-medium text-white">
                {{ company.main_group_company?.name || 'Unlinked' }}
            </div>
        </td>

        <td class="px-6 py-5 align-top">
            <span class="rounded-full border border-indigo-500/20 bg-indigo-500/10 px-3 py-1 text-xs font-semibold uppercase text-indigo-300">
                {{ company.industry }}
            </span>

            <div v-if="company.enterprise_types?.length" class="mt-3 flex flex-wrap gap-2">
                <span
                    v-for="type in company.enterprise_types"
                    :key="`${company.id}_${type}`"
                    class="rounded-full border border-white/10 px-2 py-1 text-xs text-slate-300"
                >
                    {{ type }}
                </span>
            </div>
        </td>

        <td class="px-6 py-5 align-top">
            <div class="font-mono text-sm text-white">{{ company.db_name }}</div>
        </td>

        <td class="px-6 py-5 align-top">
            <div v-if="primaryPhone" class="text-sm text-white">
                {{ primaryPhone.number }}
            </div>
            <div v-if="primaryPhone?.label || primaryPhone?.type" class="mt-1 text-xs text-slate-500">
                {{ [primaryPhone?.label, primaryPhone?.type].filter(Boolean).join(' · ') }}
            </div>
            <div v-else class="text-sm text-slate-500">
                No phone
            </div>
        </td>

        <td class="px-6 py-5 align-top">
            <span
                class="rounded-full px-3 py-1 text-xs font-semibold"
                :class="company.is_active
                    ? 'border border-emerald-500/20 bg-emerald-500/10 text-emerald-300'
                    : 'border border-rose-500/20 bg-rose-500/10 text-rose-300'"
            >
                {{ company.is_active ? 'Active' : 'Inactive' }}
            </span>
        </td>
    </tr>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    company: {
        type: Object,
        required: true,
    },
});

const primaryPhone = computed(() => {
    return Array.isArray(props.company.phones) && props.company.phones.length
        ? props.company.phones[0]
        : null;
});

const logoUrl = computed(() => {
    return props.company.logo_path ? `/storage/${props.company.logo_path}` : null;
});
</script>
