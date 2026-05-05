<template>
    <div class="space-y-4">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h3 class="text-sm font-bold uppercase tracking-[0.2em] text-slate-300">
                    Company Memberships
                </h3>
                <p class="mt-1 text-sm text-slate-400">
                    Attach the user to companies and assign tenant roles.
                </p>
            </div>

            <button
                type="button"
                class="rounded-2xl border border-indigo-500/30 bg-indigo-500/10 px-4 py-2 text-sm font-semibold text-indigo-300 transition hover:bg-indigo-500/20"
                @click="$emit('add-membership')"
            >
                Add Company
            </button>
        </div>

        <div
            v-if="!memberships.length"
            class="rounded-2xl border border-dashed border-white/10 bg-slate-950/40 px-4 py-10 text-center text-sm text-slate-500"
        >
            No company memberships added yet.
        </div>

        <div v-else class="space-y-4">
            <MembershipCard
                v-for="(membership, index) in memberships"
                :key="membership.uid"
                :membership="membership"
                :index="index"
                :companies="availableCompanies(index)"
                :tenant-roles="tenantRoles"
                :errors="errors"
                @remove="$emit('remove-membership', index)"
                @update-company="$emit('update-company', { index, companyId: $event })"
                @toggle-tenant-role="$emit('toggle-tenant-role', { index, roleName: $event })"
            />
        </div>

        <p v-if="errors.memberships" class="text-sm text-red-400">
            {{ errors.memberships }}
        </p>
    </div>
</template>

<script setup>
import MembershipCard from './MembershipCard.vue';

const props = defineProps({
    memberships: {
        type: Array,
        default: () => [],
    },
    companies: {
        type: Array,
        default: () => [],
    },
    tenantRoles: {
        type: Array,
        default: () => [],
    },
    errors: {
        type: Object,
        default: () => ({}),
    },
});

defineEmits([
    'add-membership',
    'remove-membership',
    'update-company',
    'toggle-tenant-role',
]);

const availableCompanies = (index) => {
    const selectedIds = props.memberships
        .map((membership, membershipIndex) => (membershipIndex === index ? null : membership.company_id))
        .filter(Boolean);

    return props.companies.filter((company) => !selectedIds.includes(company.id));
};
</script>
