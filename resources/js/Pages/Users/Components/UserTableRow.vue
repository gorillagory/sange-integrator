<template>
    <tr class="transition hover:bg-white/[0.03]">
        <td class="px-6 py-5 align-top">
            <div class="font-semibold text-white">{{ user.name }}</div>
            <div class="mt-1 text-sm text-slate-400">{{ user.email }}</div>
            <div class="mt-2 text-xs text-slate-500">#{{ user.id }}</div>
        </td>

        <td class="px-6 py-5 align-top">
            <div v-if="user.global_roles?.length" class="flex flex-wrap gap-2">
                <span
                    v-for="role in user.global_roles"
                    :key="`${user.id}_global_${role}`"
                    class="rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-300"
                >
                    {{ role }}
                </span>
            </div>

            <div v-else class="text-sm text-slate-500">
                No global roles
            </div>
        </td>

        <td class="px-6 py-5 align-top">
            <div v-if="user.memberships?.length" class="space-y-3">
                <div
                    v-for="membership in user.memberships"
                    :key="`${user.id}_company_${membership.company_id}`"
                    class="rounded-xl border border-white/10 bg-slate-950 px-4 py-3"
                >
                    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                        <div class="font-medium text-white">
                            {{ membership.company_name }}
                        </div>
                        <div class="text-xs text-slate-500">
                            {{ membership.subdomain }} · {{ membership.industry }}
                        </div>
                    </div>

                    <div class="mt-3 flex flex-wrap gap-2">
                        <span
                            v-for="role in membership.tenant_roles"
                            :key="`${user.id}_${membership.company_id}_${role}`"
                            class="rounded-full border border-indigo-500/20 bg-indigo-500/10 px-3 py-1 text-xs font-semibold text-indigo-300"
                        >
                            {{ role }}
                        </span>

                        <span
                            v-if="!membership.tenant_roles?.length"
                            class="text-xs text-slate-500"
                        >
                            No tenant roles
                        </span>
                    </div>
                </div>
            </div>

            <div v-else class="text-sm text-slate-500">
                No company memberships
            </div>
        </td>

        <td class="px-6 py-5 align-top text-sm text-slate-400">
            {{ formatDate(user.created_at) }}
        </td>

        <td class="px-6 py-5 align-top text-right">
            <button
                type="button"
                class="rounded-xl border border-white/10 px-4 py-2 text-sm font-semibold text-slate-300 transition hover:bg-white/5 hover:text-white"
                @click="$emit('edit')"
            >
                Edit
            </button>
        </td>
    </tr>
</template>

<script setup>
defineProps({
    user: {
        type: Object,
        required: true,
    },
});

defineEmits(['edit']);

const formatDate = (value) => {
    if (!value) {
        return '-';
    }

    return new Date(value).toLocaleString();
};
</script>
