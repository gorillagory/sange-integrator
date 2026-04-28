<template>
    <TenantLayout>
        <div class="mb-8 flex justify-between items-end">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-gray-900">{{ booking.reference_no }}</h1>
                    <span class="px-2.5 py-1 text-xs font-bold rounded-md border"
                          :class="booking.status === 'Draft' ? 'bg-gray-100 text-gray-600 border-gray-200' : 'bg-blue-50 text-blue-600 border-blue-200'">
                        {{ booking.status }}
                    </span>
                </div>
                <p class="text-sm text-gray-500 mt-1">Review operational items and lock B2B billing parameters.</p>
            </div>

            <a v-if="booking.invoice_no" :href="`/bookings/${booking.id}/download-invoice`" target="_blank" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-bold rounded-xl transition shadow-lg shadow-emerald-500/20 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Download PDF Invoice
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

            <div class="lg:col-span-8 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50"><div class="font-bold text-gray-800">Operational Line Items</div></div>

                    <div class="divide-y divide-gray-100">
                        <div v-for="(item, index) in booking.cart_payload" :key="index" class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h4 class="font-bold text-gray-900 uppercase text-sm">{{ item.service_name || 'Service Item' }}</h4>
                                    <div class="text-xs text-gray-500 mt-1 space-y-0.5">
                                        <div v-for="(value, key) in item.details" :key="key">
                                            <span class="font-semibold capitalize">{{ key.replace('_', ' ') }}:</span> {{ value }}
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-gray-900">RM {{ item.price?.toFixed(2) }}</div>
                                    <div class="text-xs text-gray-500">Qty: {{ item.qty || 1 }}</div>
                                </div>
                            </div>

                            <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                                <label class="block text-xs font-semibold text-[var(--brand-600)] uppercase mb-2">Assign Passenger / Guest Name</label>
                                <input
                                    v-model="form.passenger_details[index]"
                                    :disabled="isLocked"
                                    type="text"
                                    class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-900 focus:border-[var(--brand-500)] disabled:bg-gray-100 disabled:text-gray-500"
                                    placeholder="e.g. Mr. Mohd Nazri Bin Ariffin">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-4 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden sticky top-6">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50"><div class="font-bold text-gray-800">B2B Invoice Routing</div></div>

                    <form @submit.prevent="generateInvoice" class="p-6 space-y-5">

                        <div v-if="booking.invoice_no" class="p-4 bg-blue-50 border border-blue-100 rounded-xl mb-4">
                            <div class="text-[10px] font-bold text-blue-500 uppercase tracking-wider mb-1">Generated Document</div>
                            <div class="text-lg font-bold text-blue-900 font-mono">{{ booking.invoice_no }}</div>
                        </div>

                        <template v-if="isLocked">
                            <div class="space-y-4">
                                <div>
                                    <div class="text-xs font-semibold text-gray-500 uppercase mb-1">Billed To</div>
                                    <div class="font-bold text-gray-900">{{ selectedClient?.name }}</div>
                                    <div class="text-xs text-gray-500">Reg: {{ selectedClient?.registration_number }}</div>
                                </div>
                                <div class="p-4 bg-gray-50 border border-gray-100 rounded-xl">
                                    <div class="text-xs font-semibold text-[var(--brand-600)] uppercase mb-1">Active Contract</div>
                                    <div class="font-mono font-bold text-gray-900">{{ selectedContract?.contract_no }}</div>
                                    <div class="text-xs text-gray-700 mt-1">{{ selectedContract?.title }}</div>
                                    <div class="text-[10px] text-gray-500 mt-2 border-t border-gray-200 pt-2">{{ selectedContract?.billing_address }}</div>
                                </div>
                                <div class="text-xs text-amber-600 bg-amber-50 p-3 rounded-lg border border-amber-100 flex gap-2">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                    This invoice is locked. To modify routing, contact a System Administrator.
                                </div>
                            </div>
                        </template>

                        <template v-else>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">1. Select Corporate Client</label>
                                <select v-model="form.client_id" class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-900 focus:border-[var(--brand-500)] shadow-sm">
                                    <option :value="null" disabled>Select a client...</option>
                                    <option v-for="client in clients" :key="client.id" :value="client.id">{{ client.name }}</option>
                                </select>

                                <div v-if="selectedClient" class="mt-2 text-xs text-gray-500 px-1 flex justify-between">
                                    <span>Reg: {{ selectedClient.registration_number || 'N/A' }}</span>
                                    <span>HQ: {{ selectedClient.hq_contact_person || 'N/A' }}</span>
                                </div>
                            </div>

                            <div v-if="form.client_id">
                                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">2. Assign Contract</label>
                                <select v-model="form.contract_no" class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-900 focus:border-[var(--brand-500)] shadow-sm">
                                    <option :value="null" disabled>Select active contract...</option>
                                    <option v-for="contract in availableContracts" :key="contract.contract_no" :value="contract.contract_no">
                                        {{ contract.contract_no }} - {{ contract.title }}
                                    </option>
                                </select>

                                <div v-if="selectedContract" class="mt-3 p-3 bg-gray-50 border border-gray-100 rounded-lg text-xs space-y-1">
                                    <div class="flex justify-between items-start">
                                        <span class="font-bold text-gray-700">Payment Terms:</span>
                                        <span class="text-[var(--brand-600)] font-bold">{{ selectedContract.payment_terms }}</span>
                                    </div>
                                    <div class="text-gray-500 pt-1 border-t border-gray-200 mt-2">{{ selectedContract.billing_address }}</div>
                                </div>
                            </div>

                            <div class="pt-4 border-t border-gray-100 mt-6">
                                <button type="submit" :disabled="form.processing || !form.client_id || !form.contract_no" class="w-full py-3 bg-[var(--brand-600)] hover:bg-[var(--brand-500)] text-white text-sm font-bold rounded-xl transition shadow-lg shadow-brand-500/20 disabled:opacity-50 flex justify-center items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
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
    booking: Object,
    clients: Array
});

// Determine if the form should be immutable
const isLocked = computed(() => props.booking.status === 'Invoiced');

// Initialize Form
const initialPassengers = props.booking.passenger_details || {};
const form = useForm({
    client_id: props.booking.client_id || null,
    contract_no: props.booking.contract_no || null,
    passenger_details: initialPassengers
});

// 🧠 Smart Finders for Visual Verification Cards
const selectedClient = computed(() => {
    return props.clients.find(c => c.id === form.client_id) || null;
});

const availableContracts = computed(() => {
    return selectedClient.value ? (selectedClient.value.contracts || []) : [];
});

const selectedContract = computed(() => {
    return availableContracts.value.find(c => c.contract_no === form.contract_no) || null;
});

// 🧠 Smart Auto-Selection Logic
watch(() => form.client_id, (newClientId) => {
    if (!newClientId || isLocked.value) return;

    // Clear the contract dropdown when changing clients
    form.contract_no = null;

    // If the newly selected client only has ONE contract, auto-select it!
    const client = props.clients.find(c => c.id === newClientId);
    if (client && client.contracts && client.contracts.length === 1) {
        form.contract_no = client.contracts[0].contract_no;
    }
});

const generateInvoice = () => {
    form.put(`/bookings/${props.booking.id}/invoice`);
};
</script>
