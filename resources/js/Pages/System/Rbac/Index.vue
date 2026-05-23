<template>
    <SystemLayout>
        <template #header>
            <div class="min-w-0">
                <h1 class="truncate text-3xl font-black tracking-tight text-white">
                    RBAC Studio
                </h1>
                <p class="mt-1 text-sm text-slate-400">
                    Govern global authority, manage system role holders, and keep tenant access design visible from Sange Central.
                </p>
            </div>
        </template>

        <div class="space-y-6">
            <section class="rounded-3xl border border-white/10 bg-[#0f172a] shadow-xl shadow-black/20">
                <div class="grid gap-4 px-5 py-5 sm:px-6 lg:grid-cols-4">
                    <div class="rounded-2xl border border-white/10 bg-[#111b31] p-5">
                        <div class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">
                            Global Users
                        </div>
                        <div class="mt-3 text-4xl font-black text-white">
                            {{ stats.global_user_count }}
                        </div>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-[#111b31] p-5">
                        <div class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">
                            Active Companies
                        </div>
                        <div class="mt-3 text-4xl font-black text-white">
                            {{ stats.company_count }}
                        </div>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-[#111b31] p-5">
                        <div class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">
                            Tenant Roles
                        </div>
                        <div class="mt-3 text-4xl font-black text-white">
                            {{ stats.tenant_role_count }}
                        </div>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-[#111b31] p-5">
                        <div class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">
                            Tenant Permission Catalog
                        </div>
                        <div class="mt-3 text-4xl font-black text-white">
                            {{ stats.permission_count }}
                        </div>
                    </div>
                </div>

                <div class="border-t border-white/10 px-5 py-5 sm:px-6">
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div>
                            <div class="text-[11px] font-bold uppercase tracking-[0.24em] text-indigo-300">
                                Global Authority
                            </div>
                            <div class="mt-2 text-lg font-bold text-white">
                                System roles with full central reach
                            </div>
                            <p class="mt-1 max-w-3xl text-sm text-slate-400">
                                Use global roles for cross-company governance, central oversight, and platform-wide authority. Tenant roles remain company-scoped below.
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <Link
                                v-if="quickLinks.users"
                                :href="quickLinks.users"
                                class="inline-flex items-center justify-center rounded-2xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-indigo-500"
                            >
                                Personnel Vault
                            </Link>

                            <button
                                type="button"
                                class="inline-flex items-center justify-center rounded-2xl border border-white/10 px-4 py-3 text-sm font-semibold text-slate-300 transition hover:bg-white/5 hover:text-white"
                                @click="openCreateModal"
                            >
                                New Global Role
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <section class="grid gap-6 xl:grid-cols-[1.08fr,0.92fr]">
                <article class="rounded-3xl border border-white/10 bg-[#0f172a] p-6 shadow-xl shadow-black/20">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-bold text-white">
                                Global Role Deck
                            </h2>
                            <p class="mt-1 text-sm text-slate-400">
                                Click any role card to edit access, member coverage, and the protected super admin baseline.
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 space-y-3">
                        <article
                            v-for="role in globalRoleCards"
                            :key="role.id"
                            class="rounded-3xl border border-white/10 bg-[#111b31] p-5"
                        >
                            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="truncate text-base font-black text-white">
                                            {{ role.name }}
                                        </span>
                                        <span
                                            class="rounded-full px-2.5 py-1 text-[11px] font-bold uppercase tracking-[0.18em]"
                                            :class="role.is_protected
                                                ? 'border border-amber-500/30 bg-amber-500/10 text-amber-200'
                                                : 'border border-emerald-500/30 bg-emerald-500/10 text-emerald-200'"
                                        >
                                            {{ role.is_protected ? 'Protected' : 'Editable' }}
                                        </span>
                                    </div>
                                    <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-slate-400">
                                        <span>{{ role.permissions.length }} permission{{ role.permissions.length === 1 ? '' : 's' }}</span>
                                        <span>{{ role.member_ids.length }} member{{ role.member_ids.length === 1 ? '' : 's' }}</span>
                                    </div>
                                    <div class="mt-4 flex flex-wrap gap-2">
                                        <span
                                            v-for="group in summarizePermissionGroups(role.permissions)"
                                            :key="`${role.id}-${group.title}`"
                                            class="rounded-full border border-white/10 bg-black/20 px-3 py-1 text-xs font-semibold text-slate-300"
                                        >
                                            {{ group.title }} · {{ group.items.length }}
                                        </span>
                                    </div>
                                </div>

                                <div class="flex flex-wrap items-center gap-2 xl:justify-end">
                                    <button
                                        type="button"
                                        class="rounded-2xl border border-white/10 px-4 py-2 text-sm font-semibold text-slate-200 transition hover:bg-white/5 hover:text-white"
                                        @click="openRoleModal(role)"
                                    >
                                        Edit Role
                                    </button>
                                    <button
                                        v-if="!role.is_protected"
                                        type="button"
                                        class="rounded-2xl border border-rose-500/30 px-4 py-2 text-sm font-semibold text-rose-300 transition hover:bg-rose-500/10"
                                        @click="destroyRole(role)"
                                    >
                                        Delete Role
                                    </button>
                                    <span
                                        v-else
                                        class="rounded-2xl border border-amber-500/30 bg-amber-500/10 px-4 py-2 text-sm font-semibold text-amber-200"
                                    >
                                        Protected Role
                                    </span>
                                </div>
                            </div>
                        </article>
                    </div>
                </article>

                <article class="rounded-3xl border border-white/10 bg-[#0f172a] p-6 shadow-xl shadow-black/20">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-bold text-white">
                                Global Permission Catalog
                            </h2>
                            <p class="mt-1 text-sm text-slate-400">
                                Grouped by how authority is understood in practice instead of raw key order.
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 space-y-4">
                        <section
                            v-for="group in globalPermissionGroups"
                            :key="group.title"
                            class="rounded-2xl border border-white/10 bg-[#111b31] p-4"
                        >
                            <div class="text-xs font-bold uppercase tracking-[0.22em] text-slate-500">
                                {{ group.title }}
                            </div>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <span
                                    v-for="permission in group.items"
                                    :key="permission"
                                    class="rounded-full border border-white/10 bg-black/20 px-3 py-1 text-xs font-semibold text-slate-300"
                                >
                                    {{ permission }}
                                </span>
                            </div>
                        </section>
                    </div>
                </article>
            </section>

            <section class="rounded-3xl border border-white/10 bg-[#0f172a] p-6 shadow-xl shadow-black/20">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-white">
                            System User Role Table
                        </h2>
                        <p class="mt-1 text-sm text-slate-400">
                            Assign global role combinations from a singular user table instead of editing each role card one by one.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-[#111b31] px-4 py-3 text-sm text-slate-300">
                        Click a member to manage their global roles.
                    </div>
                </div>

                <div class="mt-5 overflow-hidden rounded-3xl border border-white/10">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-white/10">
                            <thead class="bg-black/20">
                                <tr class="text-left text-xs font-bold uppercase tracking-[0.2em] text-slate-500">
                                    <th class="px-5 py-4">Member</th>
                                    <th class="px-5 py-4">Assigned Roles</th>
                                    <th class="px-5 py-4 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/10 bg-[#0f172a]">
                                <tr
                                    v-for="member in globalMemberStates"
                                    :key="member.id"
                                    class="transition hover:bg-white/5"
                                >
                                    <td class="px-5 py-4 align-top">
                                        <div class="font-semibold text-white">
                                            {{ member.name }}
                                        </div>
                                        <div class="mt-1 text-sm text-slate-400">
                                            {{ member.email }}
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 align-top">
                                        <div class="flex flex-wrap gap-2">
                                            <span
                                                v-for="roleName in member.roles"
                                                :key="`${member.id}-${roleName}`"
                                                class="rounded-full border border-sky-500/30 bg-sky-500/10 px-3 py-1 text-xs font-semibold text-sky-100"
                                            >
                                                {{ roleName }}
                                            </span>
                                            <span
                                                v-if="!member.roles.length"
                                                class="rounded-full border border-white/10 bg-black/20 px-3 py-1 text-xs font-semibold text-slate-500"
                                            >
                                                Unassigned
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-right align-top">
                                        <button
                                            type="button"
                                            class="rounded-2xl border border-white/10 px-4 py-2 text-sm font-semibold text-slate-200 transition hover:bg-white/5"
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

            <section class="rounded-3xl border border-white/10 bg-[#0f172a] shadow-xl shadow-black/20">
                <div class="border-b border-white/10 px-5 py-5 sm:px-6">
                    <div class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">
                        Tenant Role Matrix
                    </div>
                    <h2 class="mt-3 text-2xl font-black text-white">
                        Company-scoped operational roles
                    </h2>
                    <p class="mt-1 text-sm text-slate-400">
                        Tenant roles remain company-scoped. Open a tenant workspace below to tune local authority and assignments.
                    </p>
                </div>

                <div class="overflow-x-auto px-5 py-5 sm:px-6">
                    <table class="min-w-full divide-y divide-white/10">
                        <thead>
                            <tr class="text-left text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">
                                <th class="px-0 py-3">Role</th>
                                <th class="px-4 py-3 text-right">Permissions</th>
                                <th class="px-4 py-3 text-right">Assigned Users</th>
                                <th class="px-4 py-3">Grouped Coverage</th>
                                <th class="px-4 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10">
                            <tr
                                v-for="role in tenantRoles"
                                :key="role.name"
                                class="text-sm text-slate-300"
                            >
                                <td class="px-0 py-4 align-top">
                                    <div class="font-semibold text-white">
                                        {{ role.label }}
                                    </div>
                                    <div class="mt-1 text-xs text-slate-500">
                                        Company-scoped operational role
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-right align-top font-semibold text-white">
                                    {{ role.permission_count }}
                                </td>
                                <td class="px-4 py-4 text-right align-top font-semibold text-white">
                                    {{ role.member_count }}
                                </td>
                                <td class="px-4 py-4 align-top">
                                    <div class="flex flex-wrap gap-2">
                                        <span
                                            v-for="group in role.groups"
                                            :key="`${role.name}-${group.name}`"
                                            class="rounded-full border border-white/10 px-3 py-1 text-xs font-semibold text-slate-300"
                                        >
                                            {{ group.name }} · {{ group.permissions.length }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-right align-top">
                                    <button
                                        type="button"
                                        class="rounded-2xl border border-white/10 px-3 py-2 text-sm font-semibold text-slate-300 transition hover:bg-white/5 hover:text-white"
                                        @click="openTenantRoleModal(role)"
                                    >
                                        Manage Role
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="rounded-3xl border border-white/10 bg-[#0f172a] shadow-xl shadow-black/20">
                <div class="border-b border-white/10 px-5 py-5 sm:px-6">
                    <div class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">
                        Company Access Coverage
                    </div>
                    <h2 class="mt-3 text-2xl font-black text-white">
                        Tenant workspaces
                    </h2>
                    <p class="mt-1 text-sm text-slate-400">
                        Review each company’s active user base and jump directly into its tenant RBAC Studio.
                    </p>
                </div>

                <div class="overflow-x-auto px-5 py-5 sm:px-6">
                    <table class="min-w-full divide-y divide-white/10">
                        <thead>
                            <tr class="text-left text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">
                                <th class="px-0 py-3">Company</th>
                                <th class="px-4 py-3">Subdomain</th>
                                <th class="px-4 py-3">Industry</th>
                                <th class="px-4 py-3 text-right">Users</th>
                                <th class="px-4 py-3 text-right">Roles</th>
                                <th class="px-4 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10">
                            <tr
                                v-for="company in companies"
                                :key="company.id"
                                class="text-sm text-slate-300"
                            >
                                <td class="px-0 py-4">
                                    <div class="font-semibold text-white">
                                        {{ company.name }}
                                    </div>
                                </td>
                                <td class="px-4 py-4">{{ company.subdomain }}</td>
                                <td class="px-4 py-4">{{ company.industry || 'n/a' }}</td>
                                <td class="px-4 py-4 text-right font-semibold text-white">
                                    {{ company.user_count }}
                                </td>
                                <td class="px-4 py-4 text-right font-semibold text-white">
                                    {{ company.role_count }}
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <a
                                        :href="company.rbac_href"
                                        class="inline-flex items-center justify-center rounded-2xl border border-white/10 px-3 py-2 text-sm font-semibold text-slate-300 transition hover:bg-white/5 hover:text-white"
                                    >
                                        Open Tenant RBAC
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div
            v-if="isRoleModalOpen"
            class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm"
            @click.self="closeRoleModal"
        >
            <div class="max-h-[90vh] w-full max-w-5xl overflow-hidden rounded-3xl border border-white/10 bg-[#0f172a] shadow-2xl shadow-black/40">
                <div class="flex items-start justify-between gap-4 border-b border-white/10 px-6 py-5">
                    <div>
                        <div class="text-xs font-bold uppercase tracking-[0.22em] text-indigo-300">
                            {{ roleModal.mode === 'create' ? 'Create Global Role' : 'Edit Global Role' }}
                        </div>
                        <h3 class="mt-2 text-2xl font-black text-white">
                            {{ roleModal.mode === 'create' ? 'New Global Role' : roleModal.form.name || roleModal.originalName || 'Role' }}
                        </h3>
                        <p class="mt-2 text-sm text-slate-400">
                            {{ roleModal.isProtected
                                ? 'Super admin stays protected, but you can still review access and member assignment here.'
                                : 'Adjust role identity, permissions, and global member assignment in one place.' }}
                        </p>
                    </div>

                    <button
                        type="button"
                        class="rounded-2xl border border-white/10 px-4 py-2 text-sm font-semibold text-slate-300 transition hover:bg-white/5 hover:text-white"
                        @click="closeRoleModal"
                    >
                        Close
                    </button>
                </div>

                <div class="max-h-[calc(90vh-88px)] overflow-y-auto px-6 py-6">
                    <div class="grid gap-6 xl:grid-cols-[0.98fr,1.02fr]">
                        <div class="space-y-5">
                            <div>
                                <label class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">
                                    Role Name
                                </label>
                                <input
                                    v-model="roleModal.form.name"
                                    type="text"
                                    class="mt-2 w-full rounded-2xl border border-white/10 bg-[#111b31] px-4 py-3 text-sm text-white shadow-sm outline-none transition placeholder:text-slate-500 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500/15 disabled:bg-black/20 disabled:text-slate-500"
                                    :disabled="roleModal.isProtected"
                                >
                                <p v-if="roleModal.form.errors.name" class="mt-2 text-sm font-medium text-rose-400">
                                    {{ roleModal.form.errors.name }}
                                </p>
                            </div>

                            <section
                                v-for="group in globalPermissionGroups"
                                :key="`modal-${group.title}`"
                                class="rounded-2xl border border-white/10 bg-[#111b31] p-4"
                            >
                                <div class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">
                                    {{ group.title }}
                                </div>

                                <div class="mt-3 grid gap-2 sm:grid-cols-2">
                                    <label
                                        v-for="permission in group.items"
                                        :key="`modal-${roleModal.id}-${permission}`"
                                        class="flex items-start gap-3 rounded-2xl border border-white/10 bg-black/20 px-4 py-3 text-sm text-slate-300"
                                    >
                                        <input
                                            :checked="roleModal.form.permissions.includes(permission)"
                                            type="checkbox"
                                            class="mt-0.5 h-4 w-4 rounded border-slate-500 bg-transparent text-indigo-500 focus:ring-indigo-500"
                                            @change="toggleSelection(roleModal.form.permissions, permission)"
                                        >
                                        <span>{{ permission }}</span>
                                    </label>
                                </div>
                            </section>
                        </div>

                        <div class="space-y-5">
                            <div>
                                <div class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">
                                    Assigned Members
                                </div>
                                <p class="mt-2 text-sm text-slate-400">
                                    Pick the global users who should hold this role.
                                </p>
                            </div>

                            <div class="space-y-2">
                                <label
                                    v-for="member in globalMembers"
                                    :key="`role-modal-member-${member.id}`"
                                    class="flex items-center gap-3 rounded-2xl border border-white/10 bg-[#111b31] px-4 py-3 text-sm text-slate-300 transition hover:border-white/20"
                                >
                                    <input
                                        :checked="roleModal.form.member_ids.includes(member.id)"
                                        type="checkbox"
                                        class="h-4 w-4 rounded border-slate-500 bg-transparent text-indigo-500 focus:ring-indigo-500"
                                        @change="toggleSelection(roleModal.form.member_ids, member.id)"
                                    >
                                    <span class="font-medium text-white">{{ member.name }}</span>
                                    <span class="text-slate-400">{{ member.email }}</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-white/10 pt-5">
                        <button
                            v-if="roleModal.mode === 'edit' && !roleModal.isProtected"
                            type="button"
                            class="rounded-2xl border border-rose-500/30 px-4 py-2 text-sm font-semibold text-rose-300 transition hover:bg-rose-500/10"
                            @click="destroyCurrentRole"
                        >
                            Delete Role
                        </button>
                        <div v-else />

                        <div class="flex items-center gap-3">
                            <button
                                type="button"
                                class="rounded-2xl border border-white/10 px-4 py-2 text-sm font-semibold text-slate-300 transition hover:bg-white/5 hover:text-white"
                                @click="closeRoleModal"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                class="rounded-2xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-60"
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
            class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm"
            @click.self="closeMemberModal"
        >
            <div class="w-full max-w-3xl overflow-hidden rounded-3xl border border-white/10 bg-[#0f172a] shadow-2xl shadow-black/40">
                <div class="flex items-start justify-between gap-4 border-b border-white/10 px-6 py-5">
                    <div>
                        <div class="text-xs font-bold uppercase tracking-[0.22em] text-indigo-300">
                            Global Member Roles
                        </div>
                        <h3 class="mt-2 text-2xl font-black text-white">
                            {{ memberModal.name || 'User' }}
                        </h3>
                        <p class="mt-2 text-sm text-slate-400">
                            Assign one or more global roles directly from the user perspective.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="rounded-2xl border border-white/10 px-4 py-2 text-sm font-semibold text-slate-300 transition hover:bg-white/5 hover:text-white"
                        @click="closeMemberModal"
                    >
                        Close
                    </button>
                </div>

                <div class="px-6 py-6">
                    <div class="space-y-3">
                        <label
                            v-for="role in globalRoleCards"
                            :key="`member-role-${role.id}`"
                            class="flex items-center gap-3 rounded-2xl border border-white/10 bg-[#111b31] px-4 py-3 text-sm text-slate-300 transition hover:border-white/20"
                        >
                            <input
                                :checked="memberModal.form.role_ids.includes(role.id)"
                                type="checkbox"
                                class="h-4 w-4 rounded border-slate-500 bg-transparent text-indigo-500 focus:ring-indigo-500"
                                @change="toggleSelection(memberModal.form.role_ids, role.id)"
                            >
                            <div class="min-w-0">
                                <div class="font-semibold text-white">
                                    {{ role.name }}
                                </div>
                                <div class="mt-1 text-xs text-slate-500">
                                    {{ role.is_protected ? 'Protected global role' : 'Editable global role' }}
                                </div>
                            </div>
                        </label>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-3 border-t border-white/10 pt-5">
                        <button
                            type="button"
                            class="rounded-2xl border border-white/10 px-4 py-2 text-sm font-semibold text-slate-300 transition hover:bg-white/5 hover:text-white"
                            @click="closeMemberModal"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            class="rounded-2xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-60"
                            :disabled="memberModal.form.processing"
                            @click="submitMemberModal"
                        >
                            {{ memberModal.form.processing ? 'Saving...' : 'Save Roles' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div
            v-if="isTenantRoleModalOpen"
            class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm"
            @click.self="closeTenantRoleModal"
        >
            <div class="w-full max-w-4xl overflow-hidden rounded-3xl border border-white/10 bg-[#0f172a] shadow-2xl shadow-black/40">
                <div class="flex items-start justify-between gap-4 border-b border-white/10 px-6 py-5">
                    <div>
                        <div class="text-xs font-bold uppercase tracking-[0.22em] text-indigo-300">
                            Tenant Role Management
                        </div>
                        <h3 class="mt-2 text-2xl font-black text-white">
                            {{ tenantRoleModal.label || 'Tenant Role' }}
                        </h3>
                        <p class="mt-2 max-w-2xl text-sm text-slate-400">
                            Tenant operational roles are company-scoped. Choose a workspace below to edit the role access, assignments, or remove custom tenant roles locally.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="rounded-2xl border border-white/10 px-4 py-2 text-sm font-semibold text-slate-300 transition hover:bg-white/5 hover:text-white"
                        @click="closeTenantRoleModal"
                    >
                        Close
                    </button>
                </div>

                <div class="space-y-6 px-6 py-6">
                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="rounded-2xl border border-white/10 bg-[#111b31] p-4">
                            <div class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">
                                Permission Count
                            </div>
                            <div class="mt-3 text-3xl font-black text-white">
                                {{ tenantRoleModal.permission_count }}
                            </div>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-[#111b31] p-4">
                            <div class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">
                                Assigned Users
                            </div>
                            <div class="mt-3 text-3xl font-black text-white">
                                {{ tenantRoleModal.member_count }}
                            </div>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-[#111b31] p-4">
                            <div class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">
                                Companies Using It
                            </div>
                            <div class="mt-3 text-lg font-black text-amber-200">
                                {{ tenantRoleModal.company_count }}
                            </div>
                        </div>
                    </div>

                    <section class="rounded-3xl border border-white/10 bg-[#111b31] p-5">
                        <div class="text-xs font-bold uppercase tracking-[0.22em] text-slate-500">
                            Permission Coverage
                        </div>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <span
                                v-for="group in tenantRoleModal.groups"
                                :key="`${tenantRoleModal.name}-${group.name}`"
                                class="rounded-full border border-white/10 bg-black/20 px-3 py-1 text-xs font-semibold text-slate-300"
                            >
                                {{ group.name }} · {{ group.permissions.length }}
                            </span>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-white/10 bg-[#111b31] p-5">
                        <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
                            <div>
                                <div class="text-xs font-bold uppercase tracking-[0.22em] text-slate-500">
                                    Choose Workspace
                                </div>
                                <div class="mt-2 text-lg font-bold text-white">
                                    Open a tenant RBAC Studio
                                </div>
                                <p class="mt-1 text-sm text-slate-400">
                                    Editing happens inside the tenant because this role is scoped to each company environment.
                                </p>
                            </div>
                        </div>

                        <div class="mt-5 grid gap-3 lg:grid-cols-2">
                            <a
                                v-for="company in companies"
                                :key="`tenant-role-company-${tenantRoleModal.name}-${company.id}`"
                                :href="company.rbac_href"
                                class="rounded-2xl border border-white/10 bg-black/20 px-4 py-4 text-left transition hover:border-white/20 hover:bg-white/5"
                            >
                                <div class="font-semibold text-white">
                                    {{ company.name }}
                                </div>
                                <div class="mt-1 text-sm text-slate-400">
                                    {{ company.subdomain }} · {{ company.industry || 'n/a' }}
                                </div>
                            </a>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </SystemLayout>
</template>

<script setup>
import { computed, reactive, ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import SystemLayout from '@/Layouts/SystemLayout.vue';

const props = defineProps({
    globalRoles: {
        type: Array,
        default: () => [],
    },
    globalMembers: {
        type: Array,
        default: () => [],
    },
    globalMemberDirectory: {
        type: Array,
        default: () => [],
    },
    globalPermissions: {
        type: Array,
        default: () => [],
    },
    tenantRoles: {
        type: Array,
        default: () => [],
    },
    permissionCatalog: {
        type: Array,
        default: () => [],
    },
    companies: {
        type: Array,
        default: () => [],
    },
    quickLinks: {
        type: Object,
        default: () => ({}),
    },
    stats: {
        type: Object,
        default: () => ({}),
    },
});

const globalPermissionGroups = computed(() => {
    const grouped = {
        'View Access': [],
        'Action Authority': [],
        'Access Control': [],
    };

    for (const permission of props.globalPermissions) {
        if (permission.endsWith('.view')) {
            grouped['View Access'].push(permission);
            continue;
        }

        if (permission.startsWith('system.rbac.')) {
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

const globalRoleCards = computed(() => props.globalRoles
    .map((role) => ({
        ...role,
        permissions: Array.isArray(role.permissions) ? role.permissions : [],
        members: Array.isArray(role.members) ? role.members : [],
        member_ids: Array.isArray(role.member_ids)
            ? role.member_ids
            : (Array.isArray(role.members) ? role.members.map((member) => member.id) : []),
    }))
    .sort((left, right) => {
        if (left.is_protected !== right.is_protected) {
            return left.is_protected ? -1 : 1;
        }

        return left.name.localeCompare(right.name);
    }));

const globalMemberStates = computed(() => props.globalMemberDirectory
    .map((member) => ({
        ...member,
        roles: Array.isArray(member.roles) ? member.roles : [],
        role_ids: Array.isArray(member.role_ids) ? member.role_ids : [],
    }))
    .sort((left, right) => left.name.localeCompare(right.name)));

const isRoleModalOpen = ref(false);
const isMemberModalOpen = ref(false);
const isTenantRoleModalOpen = ref(false);

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

const tenantRoleModal = reactive({
    name: '',
    label: '',
    permission_count: 0,
    member_count: 0,
    company_count: 0,
    groups: [],
});

function summarizePermissionGroups(rolePermissions) {
    return globalPermissionGroups.value
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
        roleModal.form.post('/rbac/roles', {
            preserveScroll: true,
            onSuccess: () => {
                closeRoleModal();
                resetRoleModalForm();
            },
        });
        return;
    }

    roleModal.form.put(`/rbac/roles/${roleModal.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            closeRoleModal();
        },
    });
}

function destroyCurrentRole() {
    if (!roleModal.id || roleModal.isProtected) {
        return;
    }

    destroyRole({
        id: roleModal.id,
        name: roleModal.form.name,
        is_protected: roleModal.isProtected,
    });
}

function destroyRole(role) {
    if (!role?.id || role.is_protected) {
        return;
    }

    if (!window.confirm(`Delete role "${role.name}"?`)) {
        return;
    }

    useForm({}).delete(`/rbac/roles/${role.id}`, {
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
    memberModal.form.put(`/rbac/members/${memberModal.id}/roles`, {
        preserveScroll: true,
        onSuccess: () => {
            closeMemberModal();
        },
    });
}

function openTenantRoleModal(role) {
    tenantRoleModal.name = role.name;
    tenantRoleModal.label = role.label;
    tenantRoleModal.permission_count = role.permission_count;
    tenantRoleModal.member_count = role.member_count;
    tenantRoleModal.company_count = role.company_count ?? 0;
    tenantRoleModal.groups = Array.isArray(role.groups) ? role.groups : [];
    isTenantRoleModalOpen.value = true;
}

function closeTenantRoleModal() {
    isTenantRoleModalOpen.value = false;
}
</script>
