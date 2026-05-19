<template>
    <TenantLayout>
        <div class="mb-8 flex items-end justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-gray-900">{{ record.reference_no }}</h1>
                    <span
                        class="rounded-md border px-2.5 py-1 text-xs font-bold"
                        :class="record.status === 'Draft' ? 'border-gray-200 bg-gray-100 text-gray-600' : 'border-blue-200 bg-blue-50 text-blue-600'"
                    >
                        {{ statusLabel }}
                    </span>
                </div>
                <p class="mt-1 text-sm text-gray-500">Review captured service rows and lock document routing when the record is final.</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <Link
                    v-if="canEditDraft && !isLocked"
                    :href="`/service-records/${record.id}/edit`"
                    class="flex items-center gap-2 rounded-xl border border-[var(--brand-200)] bg-[var(--brand-50)] px-5 py-2.5 text-sm font-bold text-[var(--brand-700)] shadow-sm transition hover:bg-[var(--brand-100)]"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    Edit Draft
                </Link>

                <a v-if="record.document_no && isLocked" :href="`/service-records/${record.id}/download-document`" target="_blank" class="flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-emerald-500/20 transition hover:bg-emerald-500">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Download PDF
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
            <div class="space-y-6 lg:col-span-8">
                <div v-if="record.remarks" class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                    <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4">
                        <div class="font-bold text-gray-800">Client Remarks Snapshot</div>
                        <div class="mt-1 text-sm text-gray-500">These notes were stored on the service record at capture time.</div>
                    </div>

                    <div class="px-6 py-5">
                        <div
                            v-if="record.client_remark_preset?.title"
                            class="mb-3 inline-flex rounded-full border border-[var(--brand-200)] bg-[var(--brand-50)] px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-[var(--brand-700)]"
                        >
                            {{ record.client_remark_preset.title }}
                        </div>

                        <div class="whitespace-pre-line rounded-xl border border-gray-100 bg-gray-50 p-4 text-sm text-gray-700">
                            {{ record.remarks }}
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                    <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4">
                        <div class="font-bold text-gray-800">Captured Service Rows</div>
                        <div class="mt-1 text-sm text-gray-500">Each row preserves its own schema vector, structured payload, and finance state.</div>
                    </div>

                    <div class="divide-y divide-gray-100">
                        <div v-for="(item, index) in serviceRows" :key="item.id || index" class="p-6">
                            <div class="mb-4 flex items-start justify-between">
                                <div>
                                    <h4 class="text-sm font-bold uppercase text-gray-900">{{ item.service_name || 'Service Row' }}</h4>
                                    <div class="mt-1 text-xs text-gray-500">
                                        {{ item.service_code || 'service' }} · vector v{{ item.schema_version || 1 }}
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-gray-900">RM {{ Number(item.line_total || 0).toFixed(2) }}</div>
                                    <div class="text-xs text-gray-500">Qty: {{ item.qty || 1 }} {{ item.unit_name || '' }}</div>
                                </div>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                                    <div class="mb-2 text-[10px] font-bold uppercase text-gray-500">Primary Payload</div>
                                    <div v-if="hasEntries(item.service_details)" class="space-y-2 text-sm text-gray-700">
                                        <div v-for="([key, value], detailIndex) in entriesOf(item.service_details)" :key="`${key}-${detailIndex}`">
                                            <span class="font-semibold capitalize">{{ humanize(key) }}:</span>
                                            {{ renderValue(value) }}
                                        </div>
                                    </div>
                                    <div v-else class="text-sm text-gray-500">No structured values were stored in the primary payload.</div>
                                </div>

                                <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                                    <div class="mb-2 text-[10px] font-bold uppercase text-gray-500">Extra Payload & Finance</div>
                                    <div class="space-y-2 text-sm text-gray-700">
                                        <div v-if="hasEntries(item.service_details_extra)">
                                            <div v-for="([key, value], extraIndex) in entriesOf(item.service_details_extra)" :key="`${key}-${extraIndex}`">
                                                <span class="font-semibold capitalize">{{ humanize(key) }}:</span>
                                                {{ renderValue(value) }}
                                            </div>
                                        </div>
                                        <div><span class="font-semibold">Unit:</span> {{ item.unit_name || 'unit' }}</div>
                                        <div><span class="font-semibold">Base Cost:</span> RM {{ Number((item.base_cost ?? item.unit_fare) || 0).toFixed(2) }}</div>
                                        <div><span class="font-semibold">Supplier Cost:</span> RM {{ Number((item.supplier_cost ?? item.base_cost ?? item.unit_fare) || 0).toFixed(2) }}</div>
                                        <div><span class="font-semibold">Discount:</span> {{ item.discount_type || 'RM' }} {{ Number(item.discount_value || 0).toFixed(2) }}</div>
                                        <div><span class="font-semibold">Tax:</span> {{ item.tax_type || 'RM' }} {{ Number(item.tax_value || 0).toFixed(2) }}</div>
                                        <div><span class="font-semibold">Sell Price:</span> RM {{ Number((item.sell_price ?? item.client_price) || 0).toFixed(2) }}</div>
                                        <div><span class="font-semibold">Line Total:</span> RM {{ Number(item.line_total || 0).toFixed(2) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                    <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4">
                        <div class="font-bold text-gray-800">Document Timeline</div>
                        <div class="mt-1 text-sm text-gray-500">Creation, edits, and status transitions are captured with actor and data context.</div>
                    </div>

                    <div v-if="timelineItems.length" class="divide-y divide-gray-100">
                        <div v-for="item in timelineItems" :key="item.id" class="p-6">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <div class="text-sm font-bold text-gray-900">{{ item.label }}</div>
                                    <div class="mt-1 text-xs text-gray-500">
                                        {{ item.actor.name }}
                                        <span v-if="item.actor.email">· {{ item.actor.email }}</span>
                                    </div>
                                </div>

                                <div class="text-right text-xs text-gray-500">
                                    <div>{{ formatDateTime(item.created_at) }}</div>
                                    <div class="mt-1 font-mono text-[10px] uppercase text-gray-400">{{ humanizeAction(item.action) }}</div>
                                </div>
                            </div>

                            <div v-if="timelineSummary(item).length" class="mt-4 flex flex-wrap gap-2">
                                <div
                                    v-for="entry in timelineSummary(item)"
                                    :key="entry.label"
                                    class="rounded-full border border-gray-200 bg-gray-50 px-3 py-1 text-[11px] text-gray-700"
                                >
                                    <span class="font-bold">{{ entry.label }}:</span> {{ entry.value }}
                                </div>
                            </div>

                            <div class="mt-4 grid gap-4 xl:grid-cols-2">
                                <div v-if="payloadPreview(item.old_values).length" class="rounded-xl border border-amber-100 bg-amber-50/60 p-4">
                                    <div class="mb-2 text-[10px] font-bold uppercase tracking-[0.18em] text-amber-700">Before</div>
                                    <div class="space-y-1 text-sm text-amber-900">
                                        <div v-for="entry in payloadPreview(item.old_values)" :key="entry.label">
                                            <span class="font-semibold">{{ entry.label }}:</span> {{ entry.value }}
                                        </div>
                                    </div>
                                </div>

                                <div v-if="payloadPreview(item.new_values).length" class="rounded-xl border border-emerald-100 bg-emerald-50/60 p-4">
                                    <div class="mb-2 text-[10px] font-bold uppercase tracking-[0.18em] text-emerald-700">After</div>
                                    <div class="space-y-1 text-sm text-emerald-900">
                                        <div v-for="entry in payloadPreview(item.new_values)" :key="entry.label">
                                            <span class="font-semibold">{{ entry.label }}:</span> {{ entry.value }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-if="rowSummary(item).length" class="mt-4 rounded-xl border border-gray-100 bg-gray-50 p-4">
                                <div class="mb-2 text-[10px] font-bold uppercase tracking-[0.18em] text-gray-500">Service Row Snapshot</div>
                                <div class="space-y-2 text-sm text-gray-700">
                                    <div v-for="row in rowSummary(item)" :key="row.key" class="rounded-lg border border-white bg-white px-3 py-2">
                                        <span class="font-semibold text-gray-900">{{ row.name }}</span>
                                        <span class="mx-1 text-gray-400">·</span>
                                        <span>{{ row.qty }} {{ row.unit }}</span>
                                        <span class="mx-1 text-gray-400">·</span>
                                        <span>{{ row.total }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="px-6 py-12 text-center text-sm text-gray-500">
                        Timeline data will appear here once the record starts moving through its lifecycle.
                    </div>
                </div>
            </div>

            <div class="space-y-6 lg:col-span-4">
                <div class="sticky top-6 overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                    <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4"><div class="font-bold text-gray-800">Document Routing</div></div>

                    <form class="space-y-5 p-6" @submit.prevent="lockDocument">
                        <div v-if="record.document_no" class="mb-4 rounded-xl border border-blue-100 bg-blue-50 p-4">
                            <div class="mb-1 text-[10px] font-bold uppercase tracking-wider text-blue-500">Generated Document</div>
                            <div class="font-mono text-lg font-bold text-blue-900">{{ record.document_no }}</div>
                        </div>

                        <template v-if="isLocked">
                            <div class="space-y-4">
                                <div>
                                    <div class="mb-1 text-xs font-semibold uppercase text-gray-500">Billed To</div>
                                    <div class="font-bold text-gray-900">{{ selectedClient?.name }}</div>
                                    <div class="text-xs text-gray-500">Reg: {{ selectedClient?.registration_number }}</div>
                                </div>
                                <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                                    <div class="mb-1 text-xs font-semibold uppercase text-[var(--brand-600)]">Active Contract</div>
                                    <div class="font-mono font-bold text-gray-900">{{ selectedContract?.contract_no }}</div>
                                    <div class="mt-1 text-xs text-gray-700">{{ selectedContract?.title }}</div>
                                    <div class="mt-2 border-t border-gray-200 pt-2 text-[10px] text-gray-500">{{ selectedContract?.billing_address }}</div>
                                </div>
                                <div class="flex gap-2 rounded-lg border border-amber-100 bg-amber-50 p-3 text-xs text-amber-600">
                                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                    This document is locked. Update routing through an administrator if it needs to change.
                                </div>

                                <button
                                    v-if="canManageDocumentStatus"
                                    type="button"
                                    :disabled="unlockForm.processing"
                                    class="w-full rounded-xl border border-amber-200 bg-white py-3 text-sm font-bold text-amber-700 transition hover:bg-amber-50 disabled:opacity-50"
                                    @click="unlockDocument"
                                >
                                    Return To Draft For Editing
                                </button>
                            </div>
                        </template>

                        <template v-else>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase text-gray-600">1. Select Corporate Client</label>
                                <select v-model="form.client_id" :disabled="!canManageDocumentStatus" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-[var(--brand-500)] disabled:bg-gray-100 disabled:text-gray-400">
                                    <option :value="null" disabled>Select a client...</option>
                                    <option v-for="client in clients" :key="client.id" :value="client.id">{{ client.name }}</option>
                                </select>

                                <div v-if="selectedClient" class="mt-2 flex justify-between px-1 text-xs text-gray-500">
                                    <span>Reg: {{ selectedClient.registration_number || 'N/A' }}</span>
                                    <span>HQ: {{ selectedClient.hq_contact_person || 'N/A' }}</span>
                                </div>
                            </div>

                            <div v-if="form.client_id">
                                <label class="mb-1 block text-xs font-semibold uppercase text-gray-600">2. Assign Contract</label>
                                <select v-model="form.contract_no" :disabled="!canManageDocumentStatus" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-[var(--brand-500)] disabled:bg-gray-100 disabled:text-gray-400">
                                    <option :value="null" disabled>Select active contract...</option>
                                    <option v-for="contract in availableContracts" :key="contract.contract_no" :value="contract.contract_no">
                                        {{ contract.contract_no }} - {{ contract.title }}
                                    </option>
                                </select>

                                <div v-if="selectedContract" class="mt-3 space-y-1 rounded-lg border border-gray-100 bg-gray-50 p-3 text-xs">
                                    <div class="flex items-start justify-between">
                                        <span class="font-bold text-gray-700">Payment Terms:</span>
                                        <span class="font-bold text-[var(--brand-600)]">{{ selectedContract.payment_terms }}</span>
                                    </div>
                                    <div class="mt-2 border-t border-gray-200 pt-1 text-gray-500">{{ selectedContract.billing_address }}</div>
                                </div>
                            </div>

                            <div class="mt-6 border-t border-gray-100 pt-4">
                                <button v-if="canManageDocumentStatus" type="submit" :disabled="form.processing || !form.client_id || !form.contract_no" class="flex w-full items-center justify-center gap-2 rounded-xl bg-[var(--brand-600)] py-3 text-sm font-bold text-white shadow-lg shadow-brand-500/20 transition hover:bg-[var(--brand-500)] disabled:opacity-50">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                    Lock & Generate Document
                                </button>
                                <div v-else class="rounded-xl border border-dashed border-gray-200 bg-gray-50 px-4 py-3 text-xs text-gray-500">
                                    Only Agency Admin, Document Manager, or Super Admin can lock document routing.
                                </div>
                            </div>
                        </template>
                    </form>
                </div>

                <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                    <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4">
                        <div class="font-bold text-gray-800">Status Authority</div>
                        <div class="mt-1 text-sm text-gray-500">Who can move the record between working and final document states.</div>
                    </div>

                    <div class="space-y-4 p-6">
                        <div
                            v-for="entry in statusAuthority"
                            :key="entry.status"
                            class="rounded-xl border border-gray-100 bg-gray-50 p-4"
                        >
                            <div class="text-sm font-bold text-gray-900">{{ entry.label }}</div>
                            <div class="mt-1 text-xs text-gray-500">{{ entry.description }}</div>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <span
                                    v-for="role in entry.roles"
                                    :key="role"
                                    class="rounded-full border border-gray-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-gray-700"
                                >
                                    {{ role }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </TenantLayout>
</template>

<script setup>
import { computed, watch } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import TenantLayout from '../../Layouts/TenantLayout.vue';

const props = defineProps({
    serviceRecord: {
        type: Object,
        default: null,
    },
    operation: {
        type: Object,
        default: null,
    },
    clients: Array,
    timeline: {
        type: Array,
        default: () => [],
    },
    capabilities: {
        type: Object,
        default: () => ({}),
    },
    statusAuthority: {
        type: Array,
        default: () => [],
    },
});

const record = computed(() => props.serviceRecord || props.operation || {});
const isLocked = computed(() => record.value.status === 'DocumentLocked');
const statusLabel = computed(() => record.value.status === 'DocumentLocked' ? 'Locked' : record.value.status);
const timelineItems = computed(() => props.timeline || []);
const canEditDraft = computed(() => Boolean(props.capabilities?.can_edit_draft));
const canManageDocumentStatus = computed(() => Boolean(props.capabilities?.can_manage_document_status));

const serviceRows = computed(() => {
    return record.value.service_rows || record.value.rows || record.value.service_instances || record.value.services || [];
});

const form = useForm({
    client_id: record.value.client_id || null,
    contract_no: record.value.contract_no || null,
});
const unlockForm = useForm({
    action: 'unlock',
});

const selectedClient = computed(() => {
    return props.clients.find((client) => client.id === form.client_id) || null;
});

const availableContracts = computed(() => {
    return selectedClient.value ? (selectedClient.value.contracts || []) : [];
});

const selectedContract = computed(() => {
    return availableContracts.value.find((contract) => contract.contract_no === form.contract_no) || null;
});

watch(() => form.client_id, (newClientId) => {
    if (!newClientId || isLocked.value) {
        return;
    }

    form.contract_no = null;

    const client = props.clients.find((currentClient) => currentClient.id === newClientId);
    if (client && client.contracts && client.contracts.length === 1) {
        form.contract_no = client.contracts[0].contract_no;
    }
});

function lockDocument() {
    form.put(`/service-records/${record.value.id}/document`);
}

function unlockDocument() {
    unlockForm.put(`/service-records/${record.value.id}/document`, {
        preserveScroll: true,
    });
}

function entriesOf(value) {
    return Object.entries(value || {});
}

function hasEntries(value) {
    return entriesOf(value).length > 0;
}

function renderValue(value) {
    if (Array.isArray(value)) {
        return value.join(', ');
    }

    if (value && typeof value === 'object') {
        return Object.entries(value).map(([key, nestedValue]) => `${humanize(key)}: ${nestedValue}`).join(' | ');
    }

    return value || 'Not set';
}

function humanize(value) {
    return String(value || '')
        .replace(/_/g, ' ')
        .replace(/\b\w/g, (char) => char.toUpperCase());
}

function humanizeAction(value) {
    return String(value || '').replace(/[._]/g, ' ');
}

function formatDateTime(value) {
    if (!value) {
        return 'Unknown time';
    }

    return new Date(value).toLocaleString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function timelineSummary(item) {
    const payload = item.new_values || {};
    const summary = [];

    if (payload.status) {
        summary.push({ label: 'Status', value: payload.status });
    }

    if (payload.client?.name) {
        summary.push({ label: 'Client', value: payload.client.name });
    }

    if (payload.contract_no) {
        summary.push({ label: 'Contract', value: payload.contract_no });
    }

    if (payload.total_amount !== undefined) {
        summary.push({ label: 'Total', value: `RM ${Number(payload.total_amount || 0).toFixed(2)}` });
    }

    if (payload.rows_count !== undefined) {
        summary.push({ label: 'Rows', value: String(payload.rows_count) });
    }

    return summary;
}

function payloadPreview(payload) {
    if (!payload || typeof payload !== 'object') {
        return [];
    }

    const preview = [];

    if (payload.reference_no) {
        preview.push({ label: 'Reference', value: payload.reference_no });
    }

    if (payload.document_no) {
        preview.push({ label: 'Document No', value: payload.document_no });
    }

    if (payload.client?.name) {
        preview.push({ label: 'Client', value: payload.client.name });
    }

    if (payload.contract_no) {
        preview.push({ label: 'Contract', value: payload.contract_no });
    }

    if (payload.remarks) {
        preview.push({ label: 'Remarks', value: payload.remarks });
    }

    if (payload.total_amount !== undefined) {
        preview.push({ label: 'Total', value: `RM ${Number(payload.total_amount || 0).toFixed(2)}` });
    }

    return preview;
}

function rowSummary(item) {
    const rows = item.new_values?.rows || [];

    return rows.slice(0, 6).map((row, index) => ({
        key: `${item.id}-${index}`,
        name: row.service_name || row.service_code || `Row ${index + 1}`,
        qty: row.qty || 0,
        unit: row.unit_name || 'unit',
        total: `RM ${Number(row.line_total || 0).toFixed(2)}`,
    }));
}
</script>
