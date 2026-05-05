<template>
    <SystemLayout>
        <template #header>
            <div class="min-w-0">
                <h1 class="truncate text-3xl font-black tracking-tight text-white">
                    Genesis Roster
                </h1>
                <p class="mt-1 text-sm text-slate-400">
                    Provision companies, review tenant identity, and jump straight into vault dashboards.
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
                                    Search Companies
                                </label>

                                <input
                                    v-model="search"
                                    type="text"
                                    placeholder="Search by company, subdomain, database, or industry"
                                    class="w-full rounded-2xl border border-white/10 bg-black/30 px-4 py-3 text-sm text-white outline-none transition placeholder:text-slate-500 focus:border-indigo-500"
                                    @keyup.enter="applySearch"
                                >
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
                                New Company
                            </button>
                        </div>
                    </div>
                </div>

                <div class="grid gap-4 px-5 py-5 sm:grid-cols-2 sm:px-6 xl:grid-cols-4">
                    <div class="rounded-2xl border border-white/10 bg-[#111b31] p-5">
                        <div class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">
                            Total Companies
                        </div>
                        <div class="mt-3 text-4xl font-black text-white">
                            {{ companies.total }}
                        </div>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-[#111b31] p-5">
                        <div class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">
                            Main Groups
                        </div>
                        <div class="mt-3 text-4xl font-black text-white">
                            {{ mainGroupCompanies.length }}
                        </div>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-[#111b31] p-5">
                        <div class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">
                            Active Tenants
                        </div>
                        <div class="mt-3 text-4xl font-black text-white">
                            {{ activeCompanyCount }}
                        </div>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-[#111b31] p-5">
                        <div class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">
                            Industries
                        </div>
                        <div class="mt-3 text-4xl font-black text-white">
                            {{ industries.length }}
                        </div>
                    </div>
                </div>
            </section>

            <section class="overflow-hidden rounded-3xl border border-white/10 bg-[#0f172a] shadow-xl shadow-black/20">
                <div class="border-b border-white/10 px-5 py-4 sm:px-6">
                    <h2 class="text-lg font-bold text-white">
                        Company Vaults
                    </h2>
                    <p class="mt-1 text-sm text-slate-400">
                        Super admins can jump directly into any tenant vault from here.
                    </p>
                </div>

                <div v-if="companies.data.length" class="divide-y divide-white/10">
                    <CompanyTableRow
                        v-for="company in companies.data"
                        :key="company.id"
                        :company="company"
                    />
                </div>

                <div
                    v-else
                    class="px-6 py-16 text-center text-sm text-slate-500"
                >
                    No companies found.
                </div>
            </section>

            <div
                v-if="companies.links?.length"
                class="flex flex-wrap items-center gap-2"
            >
                <button
                    v-for="link in companies.links"
                    :key="`${link.label}-${link.url}`"
                    type="button"
                    class="rounded-xl px-3 py-2 text-sm transition"
                    :class="link.active
                        ? 'bg-indigo-600 font-semibold text-white'
                        : 'border border-white/10 bg-[#0f172a] text-slate-300 hover:bg-white/5'"
                    :disabled="!link.url"
                    @click="visitLink(link.url)"
                    v-html="link.label"
                />
            </div>

            <CompanyFormModal
                :open="modalOpen"
                :main-group-companies="mainGroupCompanies"
                :industries="industries"
                @close="closeModal"
            />
        </div>
    </SystemLayout>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import SystemLayout from '@/Layouts/SystemLayout.vue';
import CompanyFormModal from './Components/CompanyFormModal.vue';
import CompanyTableRow from './Components/CompanyTableRow.vue';

const props = defineProps({
    companies: {
        type: Object,
        required: true,
    },
    mainGroupCompanies: {
        type: Array,
        default: () => [],
    },
    industries: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const search = ref(props.filters?.search ?? '');
const modalOpen = ref(false);

watch(
    () => props.filters?.search,
    (value) => {
        search.value = value ?? '';
    }
);

const companies = computed(() => props.companies);

const activeCompanyCount = computed(() => {
    return props.companies.data.filter((company) => company.is_active).length;
});

const applySearch = () => {
    router.get(
        '/companies',
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
        preserveScroll: true,
        preserveState: true,
    });
};

const openCreate = () => {
    modalOpen.value = true;
};

const closeModal = () => {
    modalOpen.value = false;
};
</script>
