<template>
    <teleport to="body">
        <div
            v-if="open"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 p-4 backdrop-blur-sm"
            @keydown.esc.prevent.stop="handleRequestClose"
        >
            <div
                ref="panelRef"
                class="max-h-[92vh] w-full max-w-5xl overflow-hidden rounded-3xl border border-white/10 bg-slate-900 shadow-2xl shadow-black/40"
                role="dialog"
                aria-modal="true"
                tabindex="-1"
            >
                <div class="flex items-start justify-between gap-4 border-b border-white/10 px-6 py-5">
                    <div>
                        <h2 class="text-3xl font-black tracking-tight text-white">
                            {{ isEdit ? 'Edit User' : 'Enroll User' }}
                        </h2>
                        <p class="mt-2 text-sm text-slate-400">
                            {{ isEdit ? 'Update user details, global roles, and memberships.' : 'Create a user and assign memberships.' }}
                        </p>
                    </div>

                    <button
                        type="button"
                        class="rounded-xl px-3 py-2 text-sm font-semibold text-slate-300 transition hover:bg-white/5 hover:text-white"
                        @click="handleRequestClose"
                    >
                        Close
                    </button>
                </div>

                <form class="flex max-h-[calc(92vh-88px)] flex-col" @submit.prevent="submit">
                    <div class="flex-1 overflow-y-auto px-6 py-6">
                        <div class="grid gap-6 xl:grid-cols-[1.1fr,1fr]">
                            <section class="space-y-5">
                                <div>
                                    <label class="mb-2 block text-xs font-bold uppercase tracking-[0.2em] text-slate-400">
                                        Name
                                    </label>
                                    <input
                                        v-model="form.name"
                                        type="text"
                                        class="w-full rounded-2xl border border-white/10 bg-slate-950 px-4 py-3 text-white outline-none transition focus:border-indigo-500"
                                        placeholder="Jane Doe"
                                    />
                                    <p v-if="form.errors.name" class="mt-2 text-sm text-red-400">
                                        {{ form.errors.name }}
                                    </p>
                                </div>

                                <div>
                                    <label class="mb-2 block text-xs font-bold uppercase tracking-[0.2em] text-slate-400">
                                        Email
                                    </label>
                                    <input
                                        v-model="form.email"
                                        type="email"
                                        class="w-full rounded-2xl border border-white/10 bg-slate-950 px-4 py-3 text-white outline-none transition focus:border-indigo-500"
                                        placeholder="jane@company.test"
                                    />
                                    <p v-if="form.errors.email" class="mt-2 text-sm text-red-400">
                                        {{ form.errors.email }}
                                    </p>
                                </div>

                                <div class="grid gap-4 md:grid-cols-2">
                                    <div>
                                        <label class="mb-2 block text-xs font-bold uppercase tracking-[0.2em] text-slate-400">
                                            Password
                                        </label>
                                        <input
                                            v-model="form.password"
                                            type="password"
                                            class="w-full rounded-2xl border border-white/10 bg-slate-950 px-4 py-3 text-white outline-none transition focus:border-indigo-500"
                                            :placeholder="isEdit ? 'Leave blank to keep current password' : 'Password'"
                                        />
                                        <p v-if="form.errors.password" class="mt-2 text-sm text-red-400">
                                            {{ form.errors.password }}
                                        </p>
                                    </div>

                                    <div>
                                        <label class="mb-2 block text-xs font-bold uppercase tracking-[0.2em] text-slate-400">
                                            Confirm Password
                                        </label>
                                        <input
                                            v-model="form.password_confirmation"
                                            type="password"
                                            class="w-full rounded-2xl border border-white/10 bg-slate-950 px-4 py-3 text-white outline-none transition focus:border-indigo-500"
                                            placeholder="Confirm password"
                                        />
                                    </div>
                                </div>

                                <section>
                                    <div class="mb-3">
                                        <h3 class="text-sm font-bold uppercase tracking-[0.2em] text-slate-300">
                                            Global Roles
                                        </h3>
                                        <p class="mt-1 text-sm text-slate-400">
                                            System-level access for central administration.
                                        </p>
                                    </div>

                                    <div v-if="globalRoles.length" class="grid gap-3 md:grid-cols-2">
                                        <button
                                            v-for="role in globalRoles"
                                            :key="role.name"
                                            type="button"
                                            class="rounded-2xl border px-4 py-4 text-left transition"
                                            :class="isGlobalRoleSelected(role.name)
                                                ? 'border-indigo-500 bg-indigo-500/15 text-white'
                                                : 'border-white/10 bg-slate-950 text-slate-300 hover:border-indigo-500/40 hover:bg-white/5'"
                                            @click="toggleGlobalRole(role.name)"
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
                                                    :class="isGlobalRoleSelected(role.name)
                                                        ? 'bg-indigo-500 text-white'
                                                        : 'bg-white/10 text-slate-300'"
                                                >
                                                    {{ isGlobalRoleSelected(role.name) ? 'ON' : 'OFF' }}
                                                </span>
                                            </div>
                                        </button>
                                    </div>

                                    <div
                                        v-else
                                        class="rounded-2xl border border-dashed border-white/10 bg-slate-950 px-4 py-6 text-sm text-slate-500"
                                    >
                                        No global roles available.
                                    </div>

                                    <p v-if="form.errors.global_roles" class="mt-2 text-sm text-red-400">
                                        {{ form.errors.global_roles }}
                                    </p>
                                </section>
                            </section>

                            <section>
                                <MembershipEditor
                                    :memberships="form.memberships"
                                    :companies="companies"
                                    :tenant-roles="tenantRoles"
                                    :errors="form.errors"
                                    @add-membership="addMembership"
                                    @remove-membership="removeMembership"
                                    @update-company="updateCompany"
                                    @toggle-tenant-role="toggleTenantRole"
                                />
                            </section>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-white/10 px-6 py-5">
                        <button
                            type="button"
                            class="rounded-2xl border border-white/10 px-4 py-3 text-sm font-semibold text-slate-300 transition hover:bg-white/5"
                            @click="handleRequestClose"
                        >
                            Cancel
                        </button>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-2xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            {{ form.processing ? 'Saving...' : isEdit ? 'Update User' : 'Create User' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </teleport>
</template>

<script setup>
import { computed, nextTick, ref, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import MembershipEditor from './MembershipEditor.vue';

const props = defineProps({
    open: {
        type: Boolean,
        default: false,
    },
    mode: {
        type: String,
        default: 'create',
    },
    user: {
        type: Object,
        default: null,
    },
    companies: {
        type: Array,
        default: () => [],
    },
    globalRoles: {
        type: Array,
        default: () => [],
    },
    tenantRoles: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['close']);

const panelRef = ref(null);

const isEdit = computed(() => props.mode === 'edit');

const blankMembership = () => ({
    uid: `${Date.now()}_${Math.random().toString(36).slice(2, 8)}`,
    company_id: null,
    tenant_roles: [],
});

const cloneMembership = (membership) => ({
    uid: `${membership.company_id ?? 'new'}_${Math.random().toString(36).slice(2, 8)}`,
    company_id: membership.company_id ? Number(membership.company_id) : null,
    tenant_roles: Array.isArray(membership.tenant_roles) ? [...membership.tenant_roles] : [],
});

const buildFormData = (user = null) => {
    if (!user) {
        return {
            name: '',
            email: '',
            password: '',
            password_confirmation: '',
            global_roles: [],
            memberships: [],
        };
    }

    return {
        name: user.name ?? '',
        email: user.email ?? '',
        password: '',
        password_confirmation: '',
        global_roles: Array.isArray(user.global_roles) ? [...user.global_roles] : [],
        memberships: Array.isArray(user.memberships)
            ? user.memberships.map((membership) => cloneMembership(membership))
            : [],
    };
};

const form = useForm(buildFormData());

watch(
    () => props.open,
    async (value) => {
        if (!value) {
            return;
        }

        form.defaults(buildFormData(props.user));
        form.reset();
        form.clearErrors();

        await nextTick();

        panelRef.value?.focus();
    },
    { immediate: true }
);

watch(
    () => props.user,
    (user) => {
        if (!props.open) {
            return;
        }

        form.defaults(buildFormData(user));
        form.reset();
        form.clearErrors();
    }
);

const isDirty = computed(() => form.isDirty);

const humanizeRole = (value) => {
    return String(value ?? '')
        .split('_')
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
        .join(' ');
};

const isGlobalRoleSelected = (roleName) => {
    return form.global_roles.includes(roleName);
};

const toggleGlobalRole = (roleName) => {
    if (form.global_roles.includes(roleName)) {
        form.global_roles = form.global_roles.filter((role) => role !== roleName);
        return;
    }

    form.global_roles = [...form.global_roles, roleName];
};

const addMembership = () => {
    form.memberships = [...form.memberships, blankMembership()];
};

const removeMembership = (index) => {
    form.memberships = form.memberships.filter((_, membershipIndex) => membershipIndex !== index);
};

const updateCompany = ({ index, companyId }) => {
    form.memberships[index].company_id = companyId;
};

const toggleTenantRole = ({ index, roleName }) => {
    const currentRoles = form.memberships[index]?.tenant_roles ?? [];

    if (currentRoles.includes(roleName)) {
        form.memberships[index].tenant_roles = currentRoles.filter((role) => role !== roleName);
        return;
    }

    form.memberships[index].tenant_roles = [...currentRoles, roleName];
};

const payload = () => ({
    name: form.name,
    email: form.email,
    password: form.password,
    password_confirmation: form.password_confirmation,
    global_roles: form.global_roles,
    memberships: form.memberships
        .filter((membership) => membership.company_id)
        .map((membership) => ({
            company_id: membership.company_id,
            tenant_roles: membership.tenant_roles,
        })),
});

const handleRequestClose = () => {
    emit('close');
};

const submit = () => {
    if (isEdit.value && props.user?.id) {
        form.transform(() => payload()).put(`/users/${props.user.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                emit('close');
                router.reload({
                    preserveScroll: true,
                    only: ['users', 'filters', 'companies', 'globalRoles', 'tenantRoles'],
                });
            },
        });

        return;
    }

    form.transform(() => payload()).post('/users', {
        preserveScroll: true,
        onSuccess: () => {
            emit('close');
            router.reload({
                preserveScroll: true,
                only: ['users', 'filters', 'companies', 'globalRoles', 'tenantRoles'],
            });
        },
    });
};
</script>
