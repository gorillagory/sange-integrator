<template>
    <TenantLayout>
        <template #breadcrumbs>
            <Breadcrumbs :items="[
                { label: 'Service Records', url: null },
                { label: 'Dynamic Service Capture', url: null },
            ]" />
        </template>

        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Service Records</h1>
                <p class="mt-1 text-sm text-gray-500">Capture and manage vector-driven service records with document-ready finance.</p>
            </div>

            <Link href="/service-records/create" class="flex items-center gap-2 rounded-xl bg-[var(--brand-600)] px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-brand-500/20 transition hover:bg-[var(--brand-500)]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                New Service Record
            </Link>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="border-b border-gray-100 bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Reference / Document</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Corporate Client</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Total Value</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Date</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Status</th>
                            <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-gray-500">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 bg-white">
                        <tr v-if="records.data.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <div class="mb-2"><svg class="mx-auto h-8 w-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg></div>
                                No service records captured yet. Click "New Service Record" to start.
                            </td>
                        </tr>

                        <tr v-for="serviceRecord in records.data" :key="serviceRecord.id" class="group transition-colors hover:bg-blue-50/30">
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="font-bold text-gray-900">{{ serviceRecord.reference_no }}</div>
                                <div v-if="serviceRecord.document_no" class="mt-0.5 inline-block rounded border border-[var(--brand-200)] bg-[var(--brand-50)] px-1.5 text-[10px] font-mono text-[var(--brand-600)]">{{ serviceRecord.document_no }}</div>
                                <div v-else class="mt-0.5 text-[10px] italic text-gray-400">Document pending</div>
                            </td>

                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="font-medium text-gray-900">{{ serviceRecord.client?.name || 'No client locked yet' }}</div>
                                <div class="mt-1 text-[10px] text-gray-500">{{ serviceRecord.contract_no || 'No contract selected' }}</div>
                            </td>

                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="font-bold text-gray-700">RM {{ parseFloat(serviceRecord.total_amount || 0).toFixed(2) }}</div>
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                {{ new Date(serviceRecord.created_at).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4">
                                <span v-if="serviceRecord.status === 'Draft'" class="rounded-md border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700 shadow-sm">Draft</span>
                                <span v-else-if="serviceRecord.status === 'DocumentLocked'" class="inline-flex items-center gap-1 rounded-md border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 shadow-sm">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Locked
                                </span>
                                <span v-else class="rounded-md border border-gray-200 bg-gray-100 px-2.5 py-1 text-xs font-bold text-gray-700">{{ serviceRecord.status }}</span>
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                <Link :href="`/service-records/${serviceRecord.id}`" class="font-bold text-[var(--brand-600)] opacity-0 transition-opacity hover:text-[var(--brand-800)] group-hover:opacity-100">Manage &rarr;</Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="records.total > 0" class="border-t border-gray-100 bg-gray-50 p-4 text-center text-xs text-gray-500">
                Displaying {{ records.data.length }} of {{ records.total }} service records.
            </div>
        </div>
    </TenantLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import TenantLayout from '../../Layouts/TenantLayout.vue';
import Breadcrumbs from '../../Components/UI/Breadcrumbs.vue';

const props = defineProps({
    serviceRecords: {
        type: Object,
        default: null,
    },
    operations: {
        type: Object,
        default: null,
    },
});

const records = computed(() => props.serviceRecords || props.operations || { data: [], total: 0 });
</script>
