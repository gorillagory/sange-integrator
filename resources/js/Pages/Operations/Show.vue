<template>
    <TenantLayout>
        <div class="mb-8 flex items-end justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-gray-900">{{ operation.reference_no }}</h1>
                    <span
                        class="rounded-md border px-2.5 py-1 text-xs font-bold"
                        :class="operation.status === 'Draft' ? 'border-gray-200 bg-gray-100 text-gray-600' : 'border-blue-200 bg-blue-50 text-blue-600'"
                    >
                        {{ statusLabel }}
                    </span>
                </div>
                <p class="mt-1 text-sm text-gray-500">Review captured service payloads and lock document routing when the operation is final.</p>
            </div>

            <a v-if="operation.document_no" :href="`/operations/${operation.id}/download-document`" target="_blank" class="flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-emerald-500/20 transition hover:bg-emerald-500">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Download PDF Invoice
            </a>
        </div>

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
            <div class="space-y-6 lg:col-span-8">
                <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                    <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4">
                        <div class="font-bold text-gray-800">Captured Service Payloads</div>
                        <div class="mt-1 text-sm text-gray-500">Every service line keeps its own vector-defined operational data and pricing.</div>
                    </div>

                    <div class="divide-y divide-gray-100">
                        <div v-for="(item, index) in serviceLines" :key="item.id || index" class="p-6">
                            <div class="mb-4 flex items-start justify-between">
                                <div>
                                    <h4 class="text-sm font-bold uppercase text-gray-900">{{ item.service_name || 'Service Item' }}</h4>
                                    <div class="mt-1 text-xs text-gray-500">
                                        {{ item.service_code || 'service' }} · schema v{{ item.schema_version || 1 }}
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-gray-900">RM {{ Number(item.line_total || 0).toFixed(2) }}</div>
                                    <div class="text-xs text-gray-500">Qty: {{ item.qty || 1 }}</div>
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
                                    <div class="mb-2 text-[10px] font-bold uppercase text-gray-500">Extra Payload & Pricing</div>
                                    <div class="space-y-2 text-sm text-gray-700">
                                        <div v-if="hasEntries(item.service_details_extra)">
                                            <div v-for="([key, value], extraIndex) in entriesOf(item.service_details_extra)" :key="`${key}-${extraIndex}`">
                                                <span class="font-semibold capitalize">{{ humanize(key) }}:</span>
                                                {{ renderValue(value) }}
                                            </div>
                                        </div>
                                        <div>
                                            <span class="font-semibold">Supplier Cost:</span> RM {{ Number(item.unit_fare || 0).toFixed(2) }}
                                        </div>
                                        <div>
                                            <span class="font-semibold">Tax:</span> {{ item.tax_type || 'RM' }} {{ Number(item.tax_value || 0).toFixed(2) }}
                                        </div>
                                        <div>
                                            <span class="font-semibold">Line Total:</span> RM {{ Number(item.line_total || 0).toFixed(2) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6 lg:col-span-4">
                <div class="sticky top-6 overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                    <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4"><div class="font-bold text-gray-800">Document Routing</div></div>

                    <form class="space-y-5 p-6" @submit.prevent="lockDocument">
                        <div v-if="operation.document_no" class="mb-4 rounded-xl border border-blue-100 bg-blue-50 p-4">
                            <div class="mb-1 text-[10px] font-bold uppercase tracking-wider text-blue-500">Generated Document</div>
                            <div class="font-mono text-lg font-bold text-blue-900">{{ operation.document_no }}</div>
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
                            </div>
                        </template>

                        <template v-else>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase text-gray-600">1. Select Corporate Client</label>
                                <select v-model="form.client_id" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-[var(--brand-500)]">
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
                                <select v-model="form.contract_no" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-[var(--brand-500)]">
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
                                <button type="submit" :disabled="form.processing || !form.client_id || !form.contract_no" class="flex w-full items-center justify-center gap-2 rounded-xl bg-[var(--brand-600)] py-3 text-sm font-bold text-white shadow-lg shadow-brand-500/20 transition hover:bg-[var(--brand-500)] disabled:opacity-50">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                    Lock & Generate Invoice
                                </button>
                            </div>
                        </template>
                    </form>
                </div>
            </div>
        </div>
    </TenantLayout>
</template>

<script setup>
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import TenantLayout from '../../Layouts/TenantLayout.vue';

const props = defineProps({
    operation: Object,
    clients: Array,
});

const isLocked = computed(() => props.operation.status === 'DocumentLocked');
const statusLabel = computed(() => props.operation.status === 'DocumentLocked' ? 'Locked' : props.operation.status);

const serviceLines = computed(() => {
    return props.operation.service_instances || props.operation.services || [];
});

const form = useForm({
    client_id: props.operation.client_id || null,
    contract_no: props.operation.contract_no || null,
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
    form.put(`/operations/${props.operation.id}/document`);
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
</script>
