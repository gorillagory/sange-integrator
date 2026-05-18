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
                                    Search Companies And Groups
                                </label>

                                <input
                                    v-model="search"
                                    type="text"
                                    placeholder="Search by group, company, subdomain, database, or industry"
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
                        Group Directory
                    </h2>
                    <p class="mt-1 text-sm text-slate-400">
                        Each main group keeps its own expandable roster so super admins can review the full umbrella at a glance.
                    </p>
                </div>

                <div v-if="mainGroupCompanies.length" class="space-y-4 px-5 py-5 sm:px-6">
                    <article
                        v-for="group in mainGroupCompanies"
                        :key="group.id"
                        class="overflow-hidden rounded-3xl border border-white/10 bg-[#111b31]"
                    >
                        <button
                            type="button"
                            class="flex w-full flex-col gap-5 px-5 py-5 text-left transition hover:bg-white/[0.03] sm:px-6"
                            @click="toggleGroup(group.id)"
                        >
                            <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
                                <div class="flex min-w-0 flex-1 items-start gap-4">
                                    <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-white/10 bg-white/5">
                                        <img
                                            v-if="resolveImageUrl(group.logo_path)"
                                            :src="resolveImageUrl(group.logo_path)"
                                            :alt="`${group.name} logo`"
                                            class="h-full w-full object-cover"
                                        >

                                        <div
                                            v-else
                                            class="text-lg font-black text-white"
                                        >
                                            {{ buildInitials(group.name) }}
                                        </div>
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-3">
                                            <h3 class="truncate text-xl font-black text-white">
                                                {{ group.name }}
                                            </h3>

                                            <span class="rounded-full bg-indigo-500/15 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-indigo-300">
                                                {{ group.companies_count }} Companies
                                            </span>

                                            <span class="rounded-full bg-emerald-500/15 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-emerald-300">
                                                {{ group.active_companies_count }} Active
                                            </span>
                                        </div>

                                        <div class="mt-2 flex flex-wrap items-center gap-3 text-sm text-slate-400">
                                            <span v-if="group.registration_number">Reg: {{ group.registration_number }}</span>
                                            <span v-if="primaryPhone(group.phones)">{{ primaryPhone(group.phones) }}</span>
                                            <span v-if="formatAddress(group.address)">{{ formatAddress(group.address) }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center justify-end gap-2">
                                    <button
                                        type="button"
                                        class="inline-flex items-center rounded-2xl border border-white/10 px-4 py-2.5 text-sm font-semibold text-slate-300 transition hover:bg-white/5 hover:text-white"
                                        @click.stop="openEditGroup(group.id)"
                                    >
                                        Edit Group
                                    </button>

                                    <span
                                        class="inline-flex items-center rounded-2xl border border-white/10 px-4 py-2.5 text-sm font-semibold text-white"
                                    >
                                        {{ isGroupExpanded(group.id) ? 'Minimize' : 'View Companies' }}
                                    </span>
                                </div>
                            </div>
                        </button>

                        <div
                            v-if="isGroupExpanded(group.id)"
                            class="border-t border-white/10 bg-[#0d1528]"
                        >
                            <div
                                v-if="group.companies?.length"
                                class="divide-y divide-white/10"
                            >
                                <CompanyTableRow
                                    v-for="company in group.companies"
                                    :key="company.id"
                                    :company="company"
                                    @edit-company="openEditCompany"
                                    @edit-group="openEditGroup"
                                />
                            </div>

                            <div
                                v-else
                                class="px-6 py-10 text-sm text-slate-500"
                            >
                                No companies are linked to this group yet.
                            </div>
                        </div>
                    </article>
                </div>

                <div
                    v-else
                    class="px-6 py-16 text-center text-sm text-slate-500"
                >
                    No group roster found.
                </div>
            </section>

            <section
                v-if="ungroupedCompanies.length"
                class="overflow-hidden rounded-3xl border border-white/10 bg-[#0f172a] shadow-xl shadow-black/20"
            >
                <div class="flex items-center justify-between gap-4 border-b border-white/10 px-5 py-4 sm:px-6">
                    <div>
                        <h2 class="text-lg font-bold text-white">
                            Independent Companies
                        </h2>
                        <p class="mt-1 text-sm text-slate-400">
                            Companies without an umbrella group still stay easy to audit and edit from one place.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="inline-flex items-center rounded-2xl border border-white/10 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-white/5"
                        @click="ungroupedExpanded = !ungroupedExpanded"
                    >
                        {{ ungroupedExpanded ? 'Minimize' : 'View Companies' }}
                    </button>
                </div>

                <div
                    v-if="ungroupedExpanded"
                    class="divide-y divide-white/10"
                >
                    <CompanyTableRow
                        v-for="company in ungroupedCompanies"
                        :key="company.id"
                        :company="company"
                        @edit-company="openEditCompany"
                        @edit-group="openEditGroup"
                    />
                </div>
            </section>

            <section class="overflow-hidden rounded-3xl border border-white/10 bg-[#0f172a] shadow-xl shadow-black/20">
                <div class="border-b border-white/10 px-5 py-4 sm:px-6">
                    <h2 class="text-lg font-bold text-white">
                        Direct Company Access
                    </h2>
                    <p class="mt-1 text-sm text-slate-400">
                        Keep the paginated vault list for quick jumps when you already know the tenant you need.
                    </p>
                </div>

                <div v-if="companies.data.length" class="divide-y divide-white/10">
                    <CompanyTableRow
                        v-for="company in companies.data"
                        :key="company.id"
                        :company="company"
                        @edit-company="openEditCompany"
                        @edit-group="openEditGroup"
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

            <EditCompanyModal
                :open="editCompanyOpen"
                :company="selectedCompany"
                :main-group-companies="mainGroupCompanies"
                :industries="industries"
                @close="closeEditCompanyModal"
            />

            <EditMainGroupModal
                :open="editGroupOpen"
                :main-group="selectedMainGroup"
                @close="closeEditGroupModal"
            />
        </div>
    </SystemLayout>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import SystemLayout from '@/Layouts/SystemLayout.vue';
import CompanyFormModal from './Components/CompanyFormModal.vue';
import EditCompanyModal from './Components/EditCompanyModal.vue';
import EditMainGroupModal from './Components/EditMainGroupModal.vue';
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
    ungroupedCompanies: {
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
    metrics: {
        type: Object,
        default: () => ({}),
    },
});

const search = ref(props.filters?.search ?? '');
const modalOpen = ref(false);
const editCompanyOpen = ref(false);
const editGroupOpen = ref(false);
const selectedCompany = ref(null);
const selectedMainGroup = ref(null);
const expandedGroupIds = ref([]);
const ungroupedExpanded = ref(true);

watch(
    () => props.filters?.search,
    (value) => {
        search.value = value ?? '';
    }
);

const companies = computed(() => props.companies);

const activeCompanyCount = computed(() => {
    return Number(props.metrics?.active_company_count ?? 0);
});

watch(
    [() => props.mainGroupCompanies, () => search.value],
    ([groups, currentSearch]) => {
        if (!Array.isArray(groups) || groups.length === 0) {
            expandedGroupIds.value = [];
            return;
        }

        if (currentSearch) {
            expandedGroupIds.value = groups.map((group) => group.id);
            ungroupedExpanded.value = true;
            return;
        }

        const existing = new Set(expandedGroupIds.value);
        const next = groups
            .map((group) => group.id)
            .filter((groupId) => existing.has(groupId));

        expandedGroupIds.value = next.length ? next : [groups[0].id];
    },
    { immediate: true, deep: true }
);

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

const resolveImageUrl = (path) => {
    if (!path) {
        return null;
    }

    if (String(path).startsWith('http://') || String(path).startsWith('https://')) {
        return path;
    }

    return String(path).startsWith('/storage/')
        ? path
        : `/storage/${String(path).replace(/^storage\//, '')}`;
};

const buildInitials = (value) => {
    const label = String(value || 'G').trim();

    return label
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join('');
};

const formatAddress = (address) => {
    if (!address || typeof address !== 'object') {
        return '';
    }

    return [
        address.city,
        address.state,
        address.country,
    ]
        .map((part) => String(part || '').trim())
        .filter(Boolean)
        .join(', ');
};

const primaryPhone = (phones) => {
    if (!Array.isArray(phones)) {
        return '';
    }

    const phone = phones.find((entry) => entry?.number);

    return phone?.number ?? '';
};

const isGroupExpanded = (groupId) => {
    return expandedGroupIds.value.includes(groupId);
};

const toggleGroup = (groupId) => {
    if (isGroupExpanded(groupId)) {
        expandedGroupIds.value = expandedGroupIds.value.filter((id) => id !== groupId);
        return;
    }

    expandedGroupIds.value = [...expandedGroupIds.value, groupId];
};

const openEditCompany = (company) => {
    selectedCompany.value = company;
    editCompanyOpen.value = true;
};

const closeEditCompanyModal = () => {
    editCompanyOpen.value = false;
    selectedCompany.value = null;
};

const openEditGroup = (groupId) => {
    const target = props.mainGroupCompanies.find((group) => group.id === groupId) ?? null;

    if (!target) {
        return;
    }

    selectedMainGroup.value = target;
    editGroupOpen.value = true;
};

const closeEditGroupModal = () => {
    editGroupOpen.value = false;
    selectedMainGroup.value = null;
};
</script>
