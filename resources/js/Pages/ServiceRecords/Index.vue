<template>
    <TenantLayout>
        <template #breadcrumbs>
            <Breadcrumbs :items="[
                { label: 'Service Records', url: null },
                { label: 'Dynamic Service Capture', url: null },
            ]" />
        </template>

        <div class="space-y-6">
            <section class="flex flex-col gap-4 rounded-[28px] border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-200/50 lg:flex-row lg:items-start lg:justify-between lg:p-7">
                <div class="max-w-3xl">
                    <p class="text-xs font-bold uppercase tracking-[0.35em] text-[var(--brand-500)]">Service Desk</p>
                    <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900">Service Record Table</h1>
                    <p class="mt-2 text-sm leading-6 text-slate-500">
                        Work by client, search quickly across references and remarks, and keep the whole booking capture trail visible from one place.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-white"
                        @click="resetFilters"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m14.836 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-14.837-2m14.837 2H15" />
                        </svg>
                        Reset View
                    </button>

                    <Link
                        v-if="canCreateDraft"
                        href="/service-records/create"
                        class="inline-flex items-center gap-2 rounded-2xl bg-[var(--brand-600)] px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-brand-500/20 transition hover:bg-[var(--brand-500)]"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        New Service Record
                    </Link>
                </div>
            </section>

            <div class="grid gap-6 xl:grid-cols-[300px_minmax(0,1fr)]">
                <aside class="space-y-4">
                    <section class="overflow-hidden rounded-[28px] border border-slate-200/80 bg-white shadow-sm shadow-slate-200/50">
                        <div class="border-b border-slate-100 px-5 py-4">
                            <p class="text-xs font-bold uppercase tracking-[0.3em] text-slate-400">Client Directory</p>
                            <div class="relative mt-3">
                                <input
                                    v-model="clientSearch"
                                    type="text"
                                    placeholder="Search clients..."
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 pl-10 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-[var(--brand-400)] focus:bg-white focus:ring-4 focus:ring-brand-100/60"
                                >
                                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
                                </svg>
                            </div>
                        </div>

                        <div class="max-h-[70vh] overflow-y-auto p-3">
                            <button
                                type="button"
                                class="mb-2 flex w-full items-start gap-3 rounded-2xl border px-3 py-3 text-left transition"
                                :class="form.client_id === 'all'
                                    ? 'border-emerald-200 bg-emerald-50 shadow-sm'
                                    : 'border-transparent bg-slate-50 hover:border-slate-200 hover:bg-white'"
                                @click="setClientFilter('all')"
                            >
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-slate-900 text-xs font-black uppercase tracking-[0.2em] text-white">
                                    All
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="truncate text-sm font-bold text-slate-900">All Clients</p>
                                        <span class="rounded-full bg-white/80 px-2 py-0.5 text-[11px] font-bold text-slate-600">
                                            {{ metrics.records }}
                                        </span>
                                    </div>
                                    <p class="mt-1 text-xs text-slate-500">Show every service record in this tenant workspace.</p>
                                    <p class="mt-2 text-xs font-semibold text-emerald-700">{{ formatCurrency(metrics.total_value) }}</p>
                                </div>
                            </button>

                            <button
                                v-for="client in visibleClients"
                                :key="client.id"
                                type="button"
                                class="mb-2 flex w-full items-start gap-3 rounded-2xl border px-3 py-3 text-left transition last:mb-0"
                                :class="String(client.id) === form.client_id
                                    ? 'border-[var(--brand-200)] bg-[var(--brand-50)] shadow-sm'
                                    : 'border-transparent bg-slate-50 hover:border-slate-200 hover:bg-white'"
                                @click="setClientFilter(String(client.id))"
                            >
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-[var(--brand-500)] to-sky-500 text-sm font-black uppercase tracking-[0.18em] text-white">
                                    {{ clientInitials(client.name) }}
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="truncate text-sm font-bold text-slate-900">{{ client.name }}</p>
                                        <span class="rounded-full bg-white/80 px-2 py-0.5 text-[11px] font-bold text-slate-600">
                                            {{ client.record_count }}
                                        </span>
                                    </div>
                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ client.record_count === 1 ? '1 record' : `${client.record_count} records` }}
                                    </p>
                                    <p class="mt-2 text-xs font-semibold text-slate-700">{{ formatCurrency(client.total_value) }}</p>
                                </div>
                            </button>

                            <div
                                v-if="visibleClients.length === 0"
                                class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500"
                            >
                                No clients match this directory search.
                            </div>
                        </div>
                    </section>
                </aside>

                <section class="space-y-5">
                    <div class="grid gap-4 md:grid-cols-2 2xl:grid-cols-4">
                        <article class="rounded-[26px] border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-200/50">
                            <p class="text-[11px] font-bold uppercase tracking-[0.28em] text-slate-400">Records In View</p>
                            <p class="mt-3 text-3xl font-black tracking-tight text-slate-900">{{ metrics.records }}</p>
                            <p class="mt-2 text-sm text-slate-500">{{ selectedClientLabel }}</p>
                        </article>

                        <article class="rounded-[26px] border border-emerald-200/80 bg-emerald-50/70 p-5 shadow-sm shadow-emerald-100/70">
                            <p class="text-[11px] font-bold uppercase tracking-[0.28em] text-emerald-700">Portfolio Value</p>
                            <p class="mt-3 text-3xl font-black tracking-tight text-emerald-900">{{ formatCurrency(metrics.total_value) }}</p>
                            <p class="mt-2 text-sm text-emerald-700/80">Current filtered value across the visible table.</p>
                        </article>

                        <article class="rounded-[26px] border border-sky-200/80 bg-sky-50/70 p-5 shadow-sm shadow-sky-100/70">
                            <p class="text-[11px] font-bold uppercase tracking-[0.28em] text-sky-700">Document Locked</p>
                            <p class="mt-3 text-3xl font-black tracking-tight text-sky-900">{{ metrics.locked }}</p>
                            <p class="mt-2 text-sm text-sky-700/80">Records already routed and ready for output.</p>
                        </article>

                        <article class="rounded-[26px] border border-amber-200/80 bg-amber-50/70 p-5 shadow-sm shadow-amber-100/70">
                            <p class="text-[11px] font-bold uppercase tracking-[0.28em] text-amber-700">Draft Queue</p>
                            <p class="mt-3 text-3xl font-black tracking-tight text-amber-900">{{ metrics.draft }}</p>
                            <p class="mt-2 text-sm text-amber-700/80">{{ metrics.active_clients }} active clients in the current working set.</p>
                        </article>
                    </div>

                    <section class="overflow-hidden rounded-[30px] border border-slate-200/80 bg-white shadow-sm shadow-slate-200/50">
                        <div class="border-b border-slate-100 px-5 py-5 sm:px-6">
                            <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-[0.32em] text-slate-400">Service Records Table</p>
                                    <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-900">Operational register</h2>
                                    <p class="mt-1 text-sm text-slate-500">Search by reference, document number, client, contract, remarks, or status.</p>
                                </div>

                                <div class="grid gap-3 md:grid-cols-[minmax(0,1.5fr)_220px] xl:min-w-[620px]">
                                    <div class="relative">
                                        <input
                                            v-model="form.search"
                                            type="text"
                                            placeholder="Search records, clients, contract, remarks..."
                                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 pl-11 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-[var(--brand-400)] focus:bg-white focus:ring-4 focus:ring-brand-100/60"
                                        >
                                        <svg class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
                                        </svg>
                                    </div>

                                    <select
                                        v-model="form.status"
                                        @change="applyFilters(true)"
                                        class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700 outline-none transition focus:border-[var(--brand-400)] focus:bg-white focus:ring-4 focus:ring-brand-100/60"
                                    >
                                        <option value="all">All statuses</option>
                                        <option
                                            v-for="status in statusOptions"
                                            :key="status"
                                            :value="status"
                                        >
                                            {{ prettifyStatus(status) }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="mt-4 flex flex-wrap items-center gap-2">
                                <button
                                    type="button"
                                    class="rounded-full px-3 py-1.5 text-xs font-bold transition"
                                    :class="form.status === 'all'
                                        ? 'bg-slate-900 text-white'
                                        : 'border border-slate-200 bg-slate-50 text-slate-600 hover:bg-white'"
                                    @click="setStatusFilter('all')"
                                >
                                    All <span class="ml-1 opacity-70">{{ statusCounts.all }}</span>
                                </button>

                                <button
                                    type="button"
                                    class="rounded-full px-3 py-1.5 text-xs font-bold transition"
                                    :class="form.status === 'Draft'
                                        ? 'bg-amber-500 text-white'
                                        : 'border border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100'"
                                    @click="setStatusFilter('Draft')"
                                >
                                    Draft <span class="ml-1 opacity-70">{{ statusCounts.draft }}</span>
                                </button>

                                <button
                                    type="button"
                                    class="rounded-full px-3 py-1.5 text-xs font-bold transition"
                                    :class="form.status === 'DocumentLocked'
                                        ? 'bg-emerald-600 text-white'
                                        : 'border border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100'"
                                    @click="setStatusFilter('DocumentLocked')"
                                >
                                    Locked <span class="ml-1 opacity-70">{{ statusCounts.locked }}</span>
                                </button>

                                <span
                                    v-if="statusCounts.other > 0"
                                    class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-600"
                                >
                                    Other {{ statusCounts.other }}
                                </span>
                            </div>
                        </div>

                        <div v-if="records.data.length" class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-100">
                                <thead class="bg-slate-50/80">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-[0.26em] text-slate-400">Document</th>
                                        <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-[0.26em] text-slate-400">Client</th>
                                        <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-[0.26em] text-slate-400">Service Mix</th>
                                        <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-[0.26em] text-slate-400">Remarks</th>
                                        <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-[0.26em] text-slate-400">Captured</th>
                                        <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-[0.26em] text-slate-400">Amount</th>
                                        <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-[0.26em] text-slate-400">Status</th>
                                        <th class="px-6 py-4 text-right text-xs font-black uppercase tracking-[0.26em] text-slate-400">Action</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-slate-100 bg-white">
                                    <tr
                                        v-for="serviceRecord in records.data"
                                        :key="serviceRecord.id"
                                        class="group align-top transition hover:bg-[var(--brand-50)]/40"
                                    >
                                        <td class="px-6 py-5">
                                            <div class="flex items-start gap-3">
                                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-slate-900 text-sm font-black uppercase tracking-[0.22em] text-white">
                                                    {{ recordBadge(serviceRecord) }}
                                                </div>

                                                <div class="min-w-0">
                                                    <p class="truncate text-sm font-black text-slate-900">{{ serviceRecord.reference_no }}</p>
                                                    <p v-if="serviceRecord.document_no" class="mt-1 inline-flex rounded-full border border-[var(--brand-200)] bg-[var(--brand-50)] px-2 py-0.5 text-[11px] font-bold text-[var(--brand-700)]">
                                                        {{ serviceRecord.document_no }}
                                                    </p>
                                                    <p v-else class="mt-1 text-xs italic text-slate-400">Document routing still pending</p>
                                                    <p class="mt-2 text-xs font-medium text-slate-500">
                                                        {{ prettifyServiceGroup(serviceRecord.service_group_key) }}
                                                    </p>
                                                    <div class="mt-2 space-y-1 text-[11px] text-slate-500">
                                                        <p><span class="font-semibold text-slate-600">Author:</span> {{ serviceRecord.author?.name || 'Legacy / System' }}</p>
                                                        <p><span class="font-semibold text-slate-600">Assigned:</span> {{ serviceRecord.assigned_user?.name || 'Unassigned' }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="px-6 py-5">
                                            <p class="text-sm font-bold text-slate-900">{{ serviceRecord.client?.name || 'No client locked yet' }}</p>
                                            <p class="mt-1 text-xs text-slate-500">{{ serviceRecord.contract_no || 'No contract selected' }}</p>
                                        </td>

                                        <td class="px-6 py-5">
                                            <p class="text-sm font-bold text-slate-900">
                                                {{ serviceRecord.rows_count }} {{ serviceRecord.rows_count === 1 ? 'service row' : 'service rows' }}
                                            </p>
                                            <p class="mt-1 text-xs text-slate-500">Vector-driven service lines captured in this record.</p>
                                        </td>

                                        <td class="px-6 py-5">
                                            <p v-if="serviceRecord.remarks" class="max-w-[260px] text-sm leading-6 text-slate-700">
                                                {{ truncate(serviceRecord.remarks, 120) }}
                                            </p>
                                            <p v-else class="text-xs italic text-slate-400">No extra client remarks attached.</p>
                                        </td>

                                        <td class="px-6 py-5">
                                            <p class="text-sm font-semibold text-slate-800">{{ formatDate(serviceRecord.created_at) }}</p>
                                            <p class="mt-1 text-xs text-slate-500">{{ daysOpen(serviceRecord.created_at) }}</p>
                                        </td>

                                        <td class="px-6 py-5">
                                            <p class="text-sm font-black text-slate-900">{{ formatCurrency(serviceRecord.total_amount) }}</p>
                                        </td>

                                        <td class="px-6 py-5">
                                            <span
                                                class="inline-flex rounded-full px-2.5 py-1 text-xs font-black"
                                                :class="statusBadgeClass(serviceRecord.status)"
                                            >
                                                {{ prettifyStatus(serviceRecord.status) }}
                                            </span>
                                        </td>

                                        <td class="px-6 py-5 text-right">
                                            <Link
                                                :href="`/service-records/${serviceRecord.id}`"
                                                class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-3.5 py-2 text-sm font-bold text-[var(--brand-700)] shadow-sm transition hover:border-[var(--brand-200)] hover:bg-[var(--brand-50)]"
                                            >
                                                Manage
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                </svg>
                                            </Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div
                            v-else
                            class="px-6 py-20 text-center"
                        >
                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-3xl bg-slate-100 text-slate-400">
                                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <h3 class="mt-4 text-lg font-black text-slate-900">No service records match this view.</h3>
                            <p class="mt-2 text-sm text-slate-500">
                                Try a different client, clear the search, or start a fresh service record.
                            </p>
                        </div>

                        <div class="border-t border-slate-100 bg-slate-50/70 px-5 py-4 sm:px-6">
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                <p class="text-sm text-slate-500">
                                    Showing {{ records.from || 0 }}-{{ records.to || records.data.length }} of {{ records.total }} service records.
                                </p>

                                <div v-if="records.links?.length" class="flex flex-wrap items-center gap-2">
                                    <button
                                        v-for="link in records.links"
                                        :key="`${link.label}-${link.url}`"
                                        type="button"
                                        class="rounded-2xl px-3 py-2 text-sm transition"
                                        :class="link.active
                                            ? 'bg-[var(--brand-600)] font-semibold text-white'
                                            : 'border border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50'"
                                        :disabled="!link.url"
                                        @click="visitLink(link.url)"
                                        v-html="link.label"
                                    />
                                </div>
                            </div>
                        </div>
                    </section>
                </section>
            </div>
        </div>
    </TenantLayout>
</template>

<script setup>
import { computed, onBeforeUnmount, reactive, ref, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import TenantLayout from '../../Layouts/TenantLayout.vue';
import Breadcrumbs from '../../Components/UI/Breadcrumbs.vue';

const props = defineProps({
    serviceRecords: {
        type: Object,
        required: true,
    },
    clients: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            client_id: 'all',
            status: 'all',
        }),
    },
    metrics: {
        type: Object,
        default: () => ({
            records: 0,
            total_value: 0,
            locked: 0,
            draft: 0,
            active_clients: 0,
        }),
    },
    statusCounts: {
        type: Object,
        default: () => ({
            all: 0,
            draft: 0,
            locked: 0,
            other: 0,
        }),
    },
    statusOptions: {
        type: Array,
        default: () => [],
    },
});

const records = computed(() => props.serviceRecords);
const metrics = computed(() => props.metrics);
const statusCounts = computed(() => props.statusCounts);
const statusOptions = computed(() => props.statusOptions);
const clients = computed(() => props.clients);
const page = usePage();
const tenantRoles = computed(() => page.props?.auth?.rbac?.tenant_roles ?? []);
const isSuperAdmin = computed(() => Boolean(page.props?.auth?.rbac?.is_super_admin));
const canCreateDraft = computed(() => isSuperAdmin.value || ['agency_admin', 'booking_manager', 'travel_agent'].some((role) => tenantRoles.value.includes(role)));

const form = reactive({
    search: props.filters.search ?? '',
    client_id: String(props.filters.client_id ?? 'all'),
    status: props.filters.status ?? 'all',
});

const clientSearch = ref('');
let searchDebounceTimer = null;
let suppressNextSearchApply = false;

const visibleClients = computed(() => {
    if (!clientSearch.value.trim()) {
        return clients.value;
    }

    const needle = clientSearch.value.trim().toLowerCase();

    return clients.value.filter((client) => client.name.toLowerCase().includes(needle));
});

const selectedClient = computed(() => clients.value.find((client) => String(client.id) === form.client_id) ?? null);
const selectedClientLabel = computed(() => selectedClient.value
    ? `Focused on ${selectedClient.value.name}`
    : 'Showing the full tenant-wide register.');

watch(() => form.search, () => {
    if (suppressNextSearchApply) {
        suppressNextSearchApply = false;
        return;
    }

    if (searchDebounceTimer) {
        clearTimeout(searchDebounceTimer);
    }

    searchDebounceTimer = setTimeout(() => {
        applyFilters(true);
    }, 250);
});

onBeforeUnmount(() => {
    if (searchDebounceTimer) {
        clearTimeout(searchDebounceTimer);
    }
});

function applyFilters(replace = false) {
    router.get('/service-records', {
        search: form.search || undefined,
        client_id: form.client_id !== 'all' ? form.client_id : undefined,
        status: form.status !== 'all' ? form.status : undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace,
    });
}

function setClientFilter(clientId) {
    if (form.client_id === clientId) {
        return;
    }

    form.client_id = clientId;
    applyFilters(true);
}

function setStatusFilter(status) {
    if (form.status === status) {
        return;
    }

    form.status = status;
    applyFilters(true);
}

function resetFilters() {
    suppressNextSearchApply = true;
    form.search = '';
    form.client_id = 'all';
    form.status = 'all';
    clientSearch.value = '';
    applyFilters(true);
}

function visitLink(url) {
    if (!url) {
        return;
    }

    router.visit(url, {
        preserveScroll: true,
        preserveState: true,
    });
}

function formatCurrency(value) {
    const amount = Number(value || 0);

    return new Intl.NumberFormat('en-MY', {
        style: 'currency',
        currency: 'MYR',
        minimumFractionDigits: 2,
    }).format(amount);
}

function formatDate(value) {
    if (!value) {
        return 'Date unavailable';
    }

    return new Date(value).toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
}

function daysOpen(value) {
    if (!value) {
        return 'No age available';
    }

    const start = new Date(value);
    const today = new Date();
    const diff = Math.max(0, Math.floor((today - start) / 86400000));

    if (diff === 0) {
        return 'Captured today';
    }

    return diff === 1 ? '1 day old' : `${diff} days old`;
}

function prettifyStatus(status) {
    if (!status) {
        return 'Unknown';
    }

    if (status === 'DocumentLocked') {
        return 'Document Locked';
    }

    return status.replace(/([a-z])([A-Z])/g, '$1 $2');
}

function prettifyServiceGroup(key) {
    if (!key) {
        return 'General service workflow';
    }

    return key
        .replace(/[_-]+/g, ' ')
        .replace(/\b\w/g, (char) => char.toUpperCase());
}

function truncate(value, max = 120) {
    if (!value || value.length <= max) {
        return value;
    }

    return `${value.slice(0, max - 1)}…`;
}

function clientInitials(name) {
    if (!name) {
        return 'CL';
    }

    return name
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0])
        .join('')
        .toUpperCase();
}

function recordBadge(serviceRecord) {
    const label = serviceRecord.document_no || serviceRecord.reference_no || 'SR';

    return label.slice(0, 2).toUpperCase();
}

function statusBadgeClass(status) {
    if (status === 'DocumentLocked') {
        return 'border border-emerald-200 bg-emerald-50 text-emerald-700';
    }

    if (status === 'Draft') {
        return 'border border-amber-200 bg-amber-50 text-amber-700';
    }

    return 'border border-slate-200 bg-slate-100 text-slate-700';
}
</script>
