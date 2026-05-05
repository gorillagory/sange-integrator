<template>
    <SystemLayout>
        <template #header>
            <div class="min-w-0">
                <h1 class="truncate text-3xl font-black tracking-tight text-white">
                    User Enrollment
                </h1>
                <p class="mt-1 text-sm text-slate-400">
                    Manage system users, company memberships, and tenant-scoped role assignments.
                </p>
            </div>
        </template>

        <div class="space-y-6">
            <section class="rounded-3xl border border-white/10 bg-[#0f172a] shadow-xl shadow-black/20">
                <div class="border-b border-white/10 px-5 py-5 sm:px-6">
                    <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                        <div class="grid flex-1 gap-4 md:grid-cols-[minmax(0,1fr),auto]">
                            <div>
                                <label class="mb-2 block text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">
                                    Search
                                </label>

                                <input
                                    v-model="search"
                                    type="text"
                                    placeholder="Search by name or email"
                                    class="w-full rounded-2xl border border-white/10 bg-black/30 px-4 py-3 text-sm text-white outline-none transition placeholder:text-slate-500 focus:border-indigo-500"
                                    @keyup.enter="applySearch"
                                />
                            </div>

                            <div class="flex items-end gap-2">
                                <button
                                    type="button"
                                    class="rounded-2xl border border-white/10 px-4 py-3 text-sm font-semibold text-slate-300 transition hover:bg-white/5 hover:text-white"
                                    @click="resetSearch"
                                >
                                    Reset
                                </button>

                                <button
                                    type="button"
                                    class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm font-semibold text-white transition hover:bg-white/10"
                                    @click="applySearch"
                                >
                                    Apply
                                </button>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button
                                type="button"
                                class="inline-flex items-center justify-center rounded-2xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-indigo-500"
                                @click="openCreate"
                            >
                                New User
                            </button>
                        </div>
                    </div>
                </div>

                <div class="grid gap-4 px-5 py-5 sm:grid-cols-2 sm:px-6 xl:grid-cols-4">
                    <div class="rounded-2xl border border-white/10 bg-[#111b31] p-5">
                        <div class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">
                            Total Users
                        </div>
                        <div class="mt-3 text-4xl font-black text-white">
                            {{ users.total }}
                        </div>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-[#111b31] p-5">
                        <div class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">
                            Companies
                        </div>
                        <div class="mt-3 text-4xl font-black text-white">
                            {{ companies.length }}
                        </div>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-[#111b31] p-5">
                        <div class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">
                            Global Roles
                        </div>
                        <div class="mt-3 text-4xl font-black text-white">
                            {{ globalRoles.length }}
                        </div>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-[#111b31] p-5">
                        <div class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">
                            Tenant Roles
                        </div>
                        <div class="mt-3 text-4xl font-black text-white">
                            {{ tenantRoles.length }}
                        </div>
                    </div>
                </div>
            </section>

            <UserTable
                :users="users"
                @edit-user="openEdit"
                @visit-link="visitLink"
            />

            <UserFormModal
                :open="modalOpen"
                :mode="modalMode"
                :user="selectedUser"
                :companies="companies"
                :global-roles="globalRoles"
                :tenant-roles="tenantRoles"
                @close="closeModal"
            />
        </div>
    </SystemLayout>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import SystemLayout from '@/Layouts/SystemLayout.vue';
import UserFormModal from './Components/UserFormModal.vue';
import UserTable from './Components/UserTable.vue';

const props = defineProps({
    users: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
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

const search = ref(props.filters.search ?? '');
const modalOpen = ref(false);
const modalMode = ref('create');
const selectedUser = ref(null);

watch(
    () => props.filters.search,
    (value) => {
        search.value = value ?? '';
    }
);

const users = computed(() => props.users);

const applySearch = () => {
    router.get(
        '/users',
        { search: search.value || undefined },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
};

const resetSearch = () => {
    search.value = '';
    applySearch();
};

const visitLink = (url) => {
    if (!url) {
        return;
    }

    router.visit(url, {
        preserveState: true,
        preserveScroll: true,
    });
};

const openCreate = () => {
    modalMode.value = 'create';
    selectedUser.value = null;
    modalOpen.value = true;
};

const openEdit = (user) => {
    modalMode.value = 'edit';
    selectedUser.value = user;
    modalOpen.value = true;
};

const closeModal = () => {
    modalOpen.value = false;
    selectedUser.value = null;
};
</script>
