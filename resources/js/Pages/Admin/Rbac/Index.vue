<template>
    <TenantLayout>
        <div class="space-y-6">
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                    <div>
                        <div class="text-xs font-bold uppercase tracking-[0.24em] text-sky-600">
                            RBAC Studio
                        </div>
                        <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900">
                            {{ company?.name || 'Tenant Workspace' }} Access Design
                        </h1>
                        <p class="mt-2 max-w-3xl text-sm text-slate-500">
                            Design role authority, manage who holds each role, and keep the workspace access model easy to read.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                            <div class="font-semibold text-slate-900">
                                {{ roles.length }} roles
                            </div>
                            <div class="mt-1">Editable company roles.</div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                            <div class="font-semibold text-slate-900">
                                {{ memberDirectory.length }} members
                            </div>
                            <div class="mt-1">Company users.</div>
                        </div>

                        <button
                            type="button"
                            class="rounded-2xl bg-sky-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-sky-500"
                            @click="openCreateModal"
                        >
                            New Role
                        </button>
                    </div>
                </div>
            </section>

            <section class="grid gap-6 xl:grid-cols-[1.08fr,0.92fr]">
                <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">
                                Role Deck
                            </h2>
                            <p class="mt-1 text-sm text-slate-500">
                                Compact authority cards with quick edit access and current member coverage.
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 space-y-3">
                        <article
                            v-for="role in roleCards"
                            :key="role.id"
                            class="rounded-3xl border border-slate-200 bg-slate-50 p-5"
                        >
                            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="truncate text-base font-black text-slate-900">
                                            {{ role.name }}
                                        </span>
                                        <span class="rounded-full border border-sky-200 bg-sky-50 px-2.5 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-sky-700">
                                            Company Role
                                        </span>
                                    </div>
                                    <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-slate-500">
                                        <span>{{ role.permissions.length }} permission{{ role.permissions.length === 1 ? '' : 's' }}</span>
                                        <span>{{ role.member_ids.length }} member{{ role.member_ids.length === 1 ? '' : 's' }}</span>
                                    </div>
                                    <div class="mt-4 flex flex-wrap gap-2">
                                        <span
                                            v-for="group in summarizePermissionGroups(role.permissions)"
                                            :key="`${role.id}-${group.title}`"
                                            class="rounded-full border border-white bg-white px-3 py-1 text-xs font-semibold text-slate-700"
                                        >
                                            {{ group.title }} · {{ group.items.length }}
                                        </span>
                                    </div>
                                </div>

                                <div class="flex flex-wrap items-center gap-2 xl:justify-end">
                                    <button
                                        type="button"
                                        class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-white"
                                        @click="openRoleModal(role)"
                                    >
                                        Edit Role
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded-2xl border border-rose-200 px-4 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-50"
                                        @click="destroyRole(role)"
                                    >
                                        Delete Role
                                    </button>
                                </div>
                            </div>
                        </article>
                    </div>
                </article>

                <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">
                                Permission Catalog
                            </h2>
                            <p class="mt-1 text-sm text-slate-500">
                                Permissions grouped by how people think about authority, not raw technical order.
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 space-y-4">
                        <section
                            v-for="group in permissionGroups"
                            :key="group.title"
                            class="rounded-2xl border border-slate-200 bg-slate-50 p-4"
                        >
                            <div class="text-xs font-bold uppercase tracking-[0.22em] text-slate-400">
                                {{ group.title }}
                            </div>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <span
                                    v-for="permission in group.items"
                                    :key="permission"
                                    class="rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700"
                                >
                                    {{ permission }}
                                </span>
                            </div>
                        </section>
                    </div>
                </article>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">
                            Company User Role Table
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Assign role combinations from a singular member table instead of editing each role card one by one.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                        Click a member to manage their roles.
                    </div>
                </div>

                <div class="mt-5 overflow-hidden rounded-3xl border border-slate-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr class="text-left text-xs font-bold uppercase tracking-[0.2em] text-slate-400">
                                    <th class="px-5 py-4">Member</th>
                                    <th class="px-5 py-4">Assigned Roles</th>
                                    <th class="px-5 py-4 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white">
                                <tr
                                    v-for="member in memberStates"
                                    :key="member.id"
                                    class="transition hover:bg-slate-50"
                                >
                                    <td class="px-5 py-4 align-top">
                                        <div class="font-semibold text-slate-900">
                                            {{ member.name }}
                                        </div>
                                        <div class="mt-1 text-sm text-slate-500">
                                            {{ member.email }}
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 align-top">
                                        <div class="flex flex-wrap gap-2">
                                            <span
                                                v-for="roleName in member.roles"
                                                :key="`${member.id}-${roleName}`"
                                                class="rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-700"
                                            >
                                                {{ roleName }}
                                            </span>
                                            <span
                                                v-if="!member.roles.length"
                                                class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-500"
                                            >
                                                Unassigned
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-right align-top">
                                        <button
                                            type="button"
                                            class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                                            @click="openMemberModal(member)"
                                        >
                                            Manage Roles
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>

        <div
            v-if="isRoleModalOpen"
            class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-950/55 p-4 backdrop-blur-sm"
            @click.self="closeRoleModal"
        >
            <div class="max-h-[90vh] w-full max-w-5xl overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl">
                <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-6 py-5">
                    <div>
                        <div class="text-xs font-bold uppercase tracking-[0.22em] text-sky-600">
                            {{ roleModal.mode === 'create' ? 'Create Role' : 'Edit Role' }}
                        </div>
                        <h3 class="mt-2 text-2xl font-black text-slate-900">
                            {{ roleModal.mode === 'create' ? 'New Role' : roleModal.form.name || roleModal.originalName || 'Role' }}
                        </h3>
                        <p class="mt-2 text-sm text-slate-500">
                            Adjust role identity, permissions, and direct assignments in one place.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
                        @click="closeRoleModal"
                    >
                        Close
                    </button>
                </div>

                <div class="max-h-[calc(90vh-88px)] overflow-y-auto px-6 py-6">
                    <div class="grid gap-6 xl:grid-cols-[0.98fr,1.02fr]">
                        <div class="space-y-5">
                            <div>
                                <label class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">
                                    Role Name
                                </label>
                                <input
                                    v-model="roleModal.form.name"
                                    type="text"
                                    class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-100"
                                >
                                <p v-if="roleModal.form.errors.name" class="mt-2 text-sm font-medium text-rose-600">
                                    {{ roleModal.form.errors.name }}
                                </p>
                            </div>

                            <section
                                v-for="group in permissionGroups"
                                :key="`modal-${group.title}`"
                                class="rounded-2xl border border-slate-200 bg-slate-50 p-4"
                            >
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <div class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">
                                            {{ group.title }}
                                        </div>
                                        <div class="mt-1 text-sm text-slate-500">
                                            {{ group.items.length }} permission{{ group.items.length === 1 ? '' : 's' }}
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-3 grid gap-2 sm:grid-cols-2">
                                    <label
                                        v-for="permission in group.items"
                                        :key="`modal-${roleModal.id}-${permission}`"
                                        class="flex items-start gap-3 rounded-2xl border border-white bg-white px-4 py-3 text-sm text-slate-700"
                                    >
                                        <input
                                            :checked="roleModal.form.permissions.includes(permission)"
                                            type="checkbox"
                                            class="mt-0.5 h-4 w-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500"
                                            @change="toggleSelection(roleModal.form.permissions, permission)"
                                        >
                                        <span>{{ permission }}</span>
                                    </label>
                                </div>
                            </section>
                        </div>

                        <div class="space-y-5">
                            <div>
                                <div class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">
                                    Assigned Members
                                </div>
                                <p class="mt-2 text-sm text-slate-500">
                                    Pick the users who should hold this role in the current company.
                                </p>
                            </div>

                            <div class="space-y-2">
                                <label
                                    v-for="member in members"
                                    :key="`role-modal-member-${member.id}`"
                                    class="flex items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 transition hover:border-slate-300"
                                >
                                    <input
                                        :checked="roleModal.form.member_ids.includes(member.id)"
                                        type="checkbox"
                                        class="h-4 w-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500"
                                        @change="toggleSelection(roleModal.form.member_ids, member.id)"
                                    >
                                    <span class="font-medium text-slate-900">{{ member.name }}</span>
                                    <span class="text-slate-500">{{ member.email }}</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 pt-5">
                        <button
                            v-if="roleModal.mode === 'edit'"
                            type="button"
                            class="rounded-2xl border border-rose-200 px-4 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-50"
                            @click="destroyCurrentRole"
                        >
                            Delete Role
                        </button>
                        <div v-else />

                        <div class="flex items-center gap-3">
                            <button
                                type="button"
                                class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
                                @click="closeRoleModal"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                class="rounded-2xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60"
                                :disabled="roleModal.form.processing"
                                @click="submitRoleModal"
                            >
                                {{ roleModal.form.processing ? 'Saving...' : roleModal.mode === 'create' ? 'Create Role' : 'Save Changes' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div
            v-if="isMemberModalOpen"
            class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-950/55 p-4 backdrop-blur-sm"
            @click.self="closeMemberModal"
        >
            <div class="w-full max-w-3xl overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl">
                <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-6 py-5">
                    <div>
                        <div class="text-xs font-bold uppercase tracking-[0.22em] text-sky-600">
                            Member Roles
                        </div>
                        <h3 class="mt-2 text-2xl font-black text-slate-900">
                            {{ memberModal.name || 'User' }}
                        </h3>
                        <p class="mt-2 text-sm text-slate-500">
                            Assign one or more company roles directly from the user perspective.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
                        @click="closeMemberModal"
                    >
                        Close
                    </button>
                </div>

                <div class="px-6 py-6">
                    <div class="space-y-3">
                        <label
                            v-for="role in roleCards"
                            :key="`member-role-${role.id}`"
                            class="flex items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 transition hover:border-slate-300"
                        >
                            <input
                                :checked="memberModal.form.role_ids.includes(role.id)"
                                type="checkbox"
                                class="h-4 w-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500"
                                @change="toggleSelection(memberModal.form.role_ids, role.id)"
                            >
                            <div class="min-w-0">
                                <div class="font-semibold text-slate-900">
                                    {{ role.name }}
                                </div>
                                <div class="mt-1 text-xs text-slate-500">
                                    Company role
                                </div>
                            </div>
                        </label>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-3 border-t border-slate-200 pt-5">
                        <button
                            type="button"
                            class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
                            @click="closeMemberModal"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            class="rounded-2xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60"
                            :disabled="memberModal.form.processing"
                            @click="submitMemberModal"
                        >
                            {{ memberModal.form.processing ? 'Saving...' : 'Save Roles' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </TenantLayout>
</template>

<script setup>
import { computed, reactive, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';

const props = defineProps({
    company: {
        type: Object,
        default: () => ({}),
    },
    roles: {
        type: Array,
        default: () => [],
    },
    members: {
        type: Array,
        default: () => [],
    },
    memberDirectory: {
        type: Array,
        default: () => [],
    },
    permissions: {
        type: Array,
        default: () => [],
    },
});

const permissionGroups = computed(() => {
    const grouped = {
        'View Access': [],
        'Action Authority': [],
        'Access Control': [],
    };

    for (const permission of props.permissions) {
        if (permission.endsWith('.view')) {
            grouped['View Access'].push(permission);
            continue;
        }

        if (permission.startsWith('rbac.')) {
            grouped['Access Control'].push(permission);
            continue;
        }

        grouped['Action Authority'].push(permission);
    }

    return Object.entries(grouped)
        .map(([title, items]) => ({
            title,
            items: [...items].sort((left, right) => left.localeCompare(right)),
        }))
        .filter((group) => group.items.length > 0);
});

const roleCards = computed(() => props.roles
    .map((role) => ({
        ...role,
        permissions: Array.isArray(role.permissions) ? role.permissions : [],
        members: Array.isArray(role.members) ? role.members : [],
        member_ids: Array.isArray(role.member_ids)
            ? role.member_ids
            : (Array.isArray(role.members) ? role.members.map((member) => member.id) : []),
    }))
    .sort((left, right) => left.name.localeCompare(right.name)));

const memberStates = computed(() => props.memberDirectory
    .map((member) => ({
        ...member,
        roles: Array.isArray(member.roles) ? member.roles : [],
        role_ids: Array.isArray(member.role_ids) ? member.role_ids : [],
    }))
    .sort((left, right) => left.name.localeCompare(right.name)));

const isRoleModalOpen = ref(false);
const isMemberModalOpen = ref(false);

const roleModal = reactive({
    mode: 'create',
    id: null,
    originalName: '',
    isProtected: false,
    form: useForm({
        name: '',
        permissions: [],
        member_ids: [],
    }),
});

const memberModal = reactive({
    id: null,
    name: '',
    form: useForm({
        role_ids: [],
    }),
});

function summarizePermissionGroups(rolePermissions) {
    return permissionGroups.value
        .map((group) => ({
            title: group.title,
            items: group.items.filter((permission) => rolePermissions.includes(permission)),
        }))
        .filter((group) => group.items.length > 0);
}

function toggleSelection(collection, value) {
    const index = collection.indexOf(value);

    if (index === -1) {
        collection.push(value);
        return;
    }

    collection.splice(index, 1);
}

function resetRoleModalForm() {
    roleModal.id = null;
    roleModal.originalName = '';
    roleModal.isProtected = false;
    roleModal.form.reset();
    roleModal.form.clearErrors();
}

function openCreateModal() {
    resetRoleModalForm();
    roleModal.mode = 'create';
    isRoleModalOpen.value = true;
}

function openRoleModal(role) {
    resetRoleModalForm();
    roleModal.mode = 'edit';
    roleModal.id = role.id;
    roleModal.originalName = role.name;
    roleModal.isProtected = Boolean(role.is_protected);
    roleModal.form.name = role.name;
    roleModal.form.permissions = [...role.permissions];
    roleModal.form.member_ids = role.members.map((member) => member.id);
    isRoleModalOpen.value = true;
}

function closeRoleModal() {
    isRoleModalOpen.value = false;
}

function submitRoleModal() {
    if (roleModal.mode === 'create') {
        roleModal.form.post('/admin/rbac/roles', {
            preserveScroll: true,
            onSuccess: () => {
                closeRoleModal();
                resetRoleModalForm();
            },
        });
        return;
    }

    roleModal.form.put(`/admin/rbac/roles/${roleModal.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            closeRoleModal();
        },
    });
}

function destroyCurrentRole() {
    if (!roleModal.id) {
        return;
    }

    destroyRole({
        id: roleModal.id,
        name: roleModal.form.name,
    });
}

function destroyRole(role) {
    if (!role?.id) {
        return;
    }

    if (!window.confirm(`Delete role "${role.name}"?`)) {
        return;
    }

    useForm({}).delete(`/admin/rbac/roles/${role.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            closeRoleModal();
        },
    });
}

function openMemberModal(member) {
    memberModal.id = member.id;
    memberModal.name = member.name;
    memberModal.form.reset();
    memberModal.form.clearErrors();
    memberModal.form.role_ids = [...member.role_ids];
    isMemberModalOpen.value = true;
}

function closeMemberModal() {
    isMemberModalOpen.value = false;
}

function submitMemberModal() {
    memberModal.form.put(`/admin/rbac/members/${memberModal.id}/roles`, {
        preserveScroll: true,
        onSuccess: () => {
            closeMemberModal();
        },
    });
}
</script>
