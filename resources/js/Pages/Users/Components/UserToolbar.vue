<template>
    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-3xl font-black tracking-tight text-white">
                    User Enrollment
                </h1>
                <p class="mt-2 text-sm text-slate-400">
                    Manage system users, company memberships, and tenant-scoped role assignments.
                </p>
            </div>

            <button
                type="button"
                class="inline-flex items-center justify-center rounded-2xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-indigo-500"
                @click="$emit('create-user')"
            >
                New User
            </button>
        </div>

        <div class="rounded-2xl border border-white/10 bg-slate-900 p-4">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="w-full lg:max-w-md">
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-400">
                        Search
                    </label>
                    <input
                        :value="search"
                        type="text"
                        class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-white outline-none transition focus:border-indigo-500"
                        placeholder="Search by name or email"
                        @input="$emit('update:search', $event.target.value)"
                        @keyup.enter="$emit('apply-search')"
                    >
                </div>

                <div class="flex gap-3">
                    <button
                        type="button"
                        class="rounded-xl border border-white/10 px-4 py-3 text-sm font-semibold text-slate-300 transition hover:bg-white/5"
                        @click="$emit('reset-search')"
                    >
                        Reset
                    </button>

                    <button
                        type="button"
                        class="rounded-xl bg-slate-800 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-700"
                        @click="$emit('apply-search')"
                    >
                        Apply
                    </button>
                </div>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-4">
            <div class="rounded-2xl border border-white/10 bg-slate-900 p-5">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Users</div>
                <div class="mt-3 text-3xl font-black text-white">{{ userCount }}</div>
            </div>

            <div class="rounded-2xl border border-white/10 bg-slate-900 p-5">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Companies</div>
                <div class="mt-3 text-3xl font-black text-white">{{ companyCount }}</div>
            </div>

            <div class="rounded-2xl border border-white/10 bg-slate-900 p-5">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Global Roles</div>
                <div class="mt-3 text-3xl font-black text-white">{{ globalRoleCount }}</div>
            </div>

            <div class="rounded-2xl border border-white/10 bg-slate-900 p-5">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Tenant Roles</div>
                <div class="mt-3 text-3xl font-black text-white">{{ tenantRoleCount }}</div>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    search: {
        type: String,
        default: '',
    },
    userCount: {
        type: Number,
        default: 0,
    },
    companyCount: {
        type: Number,
        default: 0,
    },
    globalRoleCount: {
        type: Number,
        default: 0,
    },
    tenantRoleCount: {
        type: Number,
        default: 0,
    },
});

defineEmits([
    'update:search',
    'apply-search',
    'reset-search',
    'create-user',
]);
</script>
