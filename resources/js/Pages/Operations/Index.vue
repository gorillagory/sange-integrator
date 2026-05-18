<template>
    <TenantLayout>
        <template #breadcrumbs>
            <Breadcrumbs :items="[
                { label: 'Operations', url: null },
                { label: 'Service Operations', url: null },
            ]" />
        </template>

        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Service Operations</h1>
            <Link href="/operations/create" class="flex items-center gap-2 rounded-xl bg-[var(--brand-600)] px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-brand-500/20 transition hover:bg-[var(--brand-500)]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                New Operation
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
                        <tr v-if="operations.data.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <div class="mb-2"><svg class="mx-auto h-8 w-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg></div>
                                No operations captured yet. Click "New Operation" to start.
                            </td>
                        </tr>
                        <tr v-for="operation in operations.data" :key="operation.id" class="group transition-colors hover:bg-blue-50/30">
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="font-bold text-gray-900">{{ operation.reference_no }}</div>
                                <div v-if="operation.document_no" class="mt-0.5 inline-block rounded border border-[var(--brand-200)] bg-[var(--brand-50)] px-1.5 text-[10px] font-mono text-[var(--brand-600)]">{{ operation.document_no }}</div>
                                <div v-else class="mt-0.5 text-[10px] italic text-gray-400">Document pending</div>
                            </td>

                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="font-medium text-gray-900">{{ operation.client?.name || 'No client locked yet' }}</div>
                            </td>

                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="font-bold text-gray-700">RM {{ parseFloat(operation.total_amount).toFixed(2) }}</div>
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                {{ new Date(operation.created_at).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4">
                                <span v-if="operation.status === 'Draft'" class="rounded-md border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700 shadow-sm">Draft</span>
                                <span v-else-if="operation.status === 'DocumentLocked'" class="inline-flex items-center gap-1 rounded-md border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 shadow-sm">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Locked
                                </span>
                                <span v-else class="rounded-md border border-gray-200 bg-gray-100 px-2.5 py-1 text-xs font-bold text-gray-700">{{ operation.status }}</span>
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                <Link :href="`/operations/${operation.id}`" class="font-bold text-[var(--brand-600)] opacity-0 transition-opacity hover:text-[var(--brand-800)] group-hover:opacity-100">Manage &rarr;</Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="operations.total > 0" class="border-t border-gray-100 bg-gray-50 p-4 text-center text-xs text-gray-500">
                Displaying {{ operations.data.length }} of {{ operations.total }} operational records.
            </div>
        </div>
    </TenantLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import TenantLayout from '../../Layouts/TenantLayout.vue';
import Breadcrumbs from '../../Components/UI/Breadcrumbs.vue';

defineProps({
    operations: Object,
});
</script>
