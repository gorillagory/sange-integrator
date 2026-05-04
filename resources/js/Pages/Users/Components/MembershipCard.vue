<template>
    <div class="rounded-2xl border border-white/10 bg-slate-950 p-4">
        <div class="mb-4 flex items-center justify-between">
            <div class="text-sm font-semibold text-white">
                Membership {{ index + 1 }}
            </div>

            <button
                type="button"
                class="rounded-lg px-3 py-2 text-sm font-medium text-red-300 transition hover:bg-red-500/10"
                @click="$emit('remove')"
            >
                Remove
            </button>
        </div>

        <div class="space-y-4">
            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-400">
                    Company
                </label>

                <select
                    :value="membership.company_id"
                    class="w-full rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white outline-none transition focus:border-indigo-500"
                    @change="$emit('update-company', normalizeCompanyId($event.target.value))"
                >
                    <option :value="''">Select company</option>
                    <option
                        v-for="company in companies"
                        :key="company.id"
                        :value="company.id"
                    >
                        {{ company.name }} ({{ company.subdomain }}) - {{ company.industry }}
                    </option>
                </select>

                <p v-if="fieldError('company_id')" class="mt-2 text-sm text-red-400">
                    {{ fieldError('company_id') }}
                </p>
            </div>

            <div>
                <label class="mb-3 block text-xs font-bold uppercase tracking-wider text-slate-400">
                    Tenant Roles
                </label>

                <div class="grid gap-3 sm:grid-cols-2">
                    <label
                        v-for="role in tenantRoles"
                        :key="`${membership.uid}_${role.name}`"
                        class="flex items-center gap-3 rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-sm text-slate-200"
                    >
                        <input
                            :checked="membership.tenant_roles.includes(role.name)"
                            type="checkbox"
                            class="h-4 w-4 rounded border-white/20 bg-slate-900 text-indigo-500"
                            @change="$emit('toggle-tenant-role', role.name)"
                        >
                        <span>{{ role.name }}</span>
                    </label>
                </div>

                <p v-if="fieldError('tenant_roles')" class="mt-2 text-sm text-red-400">
                    {{ fieldError('tenant_roles') }}
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    membership: {
        type: Object,
        required: true,
    },
    index: {
        type: Number,
        required: true,
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
    'remove',
    'update-company',
    'toggle-tenant-role',
]);

const fieldError = (field) => {
    return props.errors[`memberships.${props.index}.${field}`] ?? null;
};

const normalizeCompanyId = (value) => {
    return value ? Number(value) : null;
};
</script>
