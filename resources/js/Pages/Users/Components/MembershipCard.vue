<template>
    <div class="rounded-2xl border border-white/10 bg-slate-950 p-4">
        <div class="mb-4 flex items-center justify-between gap-4">
            <div>
                <div class="text-sm font-semibold text-white">
                    Membership {{ index + 1 }}
                </div>
                <div class="mt-1 text-xs uppercase tracking-[0.18em] text-slate-500">
                    Tenant access scope
                </div>
            </div>

            <button
                type="button"
                class="rounded-xl px-3 py-2 text-sm font-medium text-red-300 transition hover:bg-red-500/10"
                @click="$emit('remove')"
            >
                Remove
            </button>
        </div>

        <div class="space-y-4">
            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-[0.2em] text-slate-400">
                    Company
                </label>

                <select
                    :value="membership.company_id ?? ''"
                    class="w-full rounded-2xl border border-white/10 bg-slate-900 px-4 py-3 text-white outline-none transition focus:border-indigo-500"
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
                <div class="mb-2 block text-xs font-bold uppercase tracking-[0.2em] text-slate-400">
                    Tenant Roles
                </div>

                <div
                    v-if="membership.company_id && !tenantRoles.length"
                    class="mb-3 rounded-2xl border border-dashed border-white/10 bg-slate-900 px-4 py-4 text-sm text-slate-500"
                >
                    No tenant roles are configured for this company yet.
                </div>

                <div v-if="tenantRoles.length" class="grid gap-3 md:grid-cols-2">
                    <button
                        v-for="role in tenantRoles"
                        :key="role.name"
                        type="button"
                        class="rounded-2xl border px-4 py-4 text-left transition"
                        :class="isSelected(role.name)
                            ? 'border-emerald-500 bg-emerald-500/15 text-white'
                            : 'border-white/10 bg-slate-900 text-slate-300 hover:border-emerald-500/40 hover:bg-white/5'"
                        @click="$emit('toggle-tenant-role', role.name)"
                    >
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <div class="text-sm font-semibold">
                                    {{ humanizeRole(role.name) }}
                                </div>
                                <div class="mt-1 text-xs uppercase tracking-[0.18em] text-slate-500">
                                    {{ role.name }}
                                </div>
                            </div>

                            <span
                                class="inline-flex h-6 min-w-6 items-center justify-center rounded-full px-2 text-xs font-bold"
                                :class="isSelected(role.name)
                                    ? 'bg-emerald-500 text-white'
                                    : 'bg-white/10 text-slate-300'"
                            >
                                {{ isSelected(role.name) ? 'ON' : 'OFF' }}
                            </span>
                        </div>
                    </button>
                </div>

                <div
                    v-else
                    class="rounded-2xl border border-dashed border-white/10 bg-slate-900 px-4 py-6 text-sm text-slate-500"
                >
                    No tenant roles available.
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

const isSelected = (roleName) => {
    return Array.isArray(props.membership.tenant_roles) && props.membership.tenant_roles.includes(roleName);
};

const humanizeRole = (value) => {
    return String(value ?? '')
        .split('_')
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
        .join(' ');
};
</script>
