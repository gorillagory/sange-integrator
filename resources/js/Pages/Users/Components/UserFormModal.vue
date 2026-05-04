<template>
    <div
        v-if="open"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4"
        @click.self="emit('close')"
    >
        <div class="w-full max-w-5xl rounded-2xl border border-white/10 bg-slate-900 shadow-2xl">
            <div class="flex items-center justify-between border-b border-white/10 px-6 py-4">
                <div>
                    <h2 class="text-xl font-bold text-white">
                        {{ isEdit ? 'Edit User' : 'Enroll User' }}
                    </h2>
                    <p class="mt-1 text-sm text-slate-400">
                        {{ isEdit ? 'Update memberships and role assignments.' : 'Create a user and assign memberships.' }}
                    </p>
                </div>

                <button
                    type="button"
                    class="rounded-lg px-3 py-2 text-sm font-medium text-slate-300 transition hover:bg-white/5 hover:text-white"
                    @click="emit('close')"
                >
                    Close
                </button>
            </div>

            <form @submit.prevent="submit" class="px-6 py-6">
                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="space-y-4">
                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-400">
                                Name
                            </label>
                            <input
                                v-model="form.name"
                                type="text"
                                class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-white outline-none transition focus:border-indigo-500"
                                placeholder="Jane Doe"
                            />
                            <p v-if="form.errors.name" class="mt-2 text-sm text-red-400">
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-400">
                                Email
                            </label>
                            <input
                                v-model="form.email"
                                type="email"
                                class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-white outline-none transition focus:border-indigo-500"
                                placeholder="jane@company.test"
                            />
                            <p v-if="form.errors.email" class="mt-2 text-sm text-red-400">
                                {{ form.errors.email }}
                            </p>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-400">
                                    Password
                                </label>
                                <input
                                    v-model="form.password"
                                    type="password"
                                    class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-white outline-none transition focus:border-indigo-500"
                                    :placeholder="isEdit ? 'Leave blank to keep current password' : 'Password'"
                                />
                                <p v-if="form.errors.password" class="mt-2 text-sm text-red-400">
                                    {{ form.errors.password }}
                                </p>
                            </div>

                            <div>
                                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-400">
                                    Confirm Password
                                </label>
                                <input
                                    v-model="form.password_confirmation"
                                    type="password"
                                    class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-white outline-none transition focus:border-indigo-500"
                                    placeholder="Confirm password"
                                />
                            </div>
                        </div>

                        <div>
                            <label class="mb-3 block text-xs font-bold uppercase tracking-wider text-slate-400">
                                Global Roles
                            </label>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <label
                                    v-for="role in globalRoles"
                                    :key="role.name"
                                    class="flex items-center gap-3 rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-sm text-slate-200"
                                >
                                    <input
                                        :checked="form.global_roles.includes(role.name)"
                                        type="checkbox"
                                        class="h-4 w-4 rounded border-white/20 bg-slate-900 text-indigo-500"
                                        @change="toggleGlobalRole(role.name)"
                                    >
                                    <span>{{ role.name }}</span>
                                </label>
                            </div>

                            <p v-if="form.errors.global_roles" class="mt-2 text-sm text-red-400">
                                {{ form.errors.global_roles }}
                            </p>
                        </div>
                    </div>

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
                </div>

                <div class="mt-8 flex items-center justify-end gap-3 border-t border-white/10 pt-6">
                    <button
                        type="button"
                        class="rounded-xl border border-white/10 px-4 py-3 text-sm font-semibold text-slate-300 transition hover:bg-white/5"
                        @click="emit('close')"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        {{ form.processing ? 'Saving...' : isEdit ? 'Update User' : 'Create User' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
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

const isEdit = computed(() => props.mode === 'edit');

const blankMembership = () => ({
    uid: `${Date.now()}_${Math.random().toString(36).slice(2, 8)}`,
    company_id: null,
    tenant_roles: [],
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
            ? user.memberships.map((membership) => ({
                uid: `${membership.company_id}_${Math.random().toString(36).slice(2, 8)}`,
                company_id: membership.company_id,
                tenant_roles: Array.isArray(membership.tenant_roles) ? [...membership.tenant_roles] : [],
            }))
            : [],
    };
};

const form = useForm(buildFormData());

watch(
    () => props.open,
    (value) => {
        if (!value) {
            return;
        }

        form.defaults(buildFormData(props.user));
        form.reset();
        form.clearErrors();
    },
    { immediate: true }
);

const toggleGlobalRole = (roleName) => {
    if (form.global_roles.includes(roleName)) {
        form.global_roles = form.global_roles.filter((role) => role !== roleName);
        return;
    }

    form.global_roles = [...form.global_roles, roleName];
};

const addMembership = () => {
    form.memberships.push(blankMembership());
};

const removeMembership = (index) => {
    form.memberships.splice(index, 1);
};

const updateCompany = ({ index, companyId }) => {
    form.memberships[index].company_id = companyId;
};

const toggleTenantRole = ({ index, roleName }) => {
    const current = form.memberships[index]?.tenant_roles ?? [];

    if (current.includes(roleName)) {
        form.memberships[index].tenant_roles = current.filter((role) => role !== roleName);
        return;
    }

    form.memberships[index].tenant_roles = [...current, roleName];
};

const payload = () => ({
    name: form.name,
    email: form.email,
    password: form.password,
    password_confirmation: form.password_confirmation,
    global_roles: form.global_roles,
    memberships: form.memberships.map((membership) => ({
        company_id: membership.company_id,
        tenant_roles: membership.tenant_roles,
    })),
});

const submit = () => {
    if (isEdit.value && props.user?.id) {
        form.transform(() => payload()).put(`/users/${props.user.id}`, {
            preserveScroll: true,
            onSuccess: () => emit('close'),
        });
        return;
    }

    form.transform(() => payload()).post('/users', {
        preserveScroll: true,
        onSuccess: () => emit('close'),
    });
};
</script>
