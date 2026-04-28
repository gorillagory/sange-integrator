<template>
    <TenantLayout>
        <template #breadcrumbs>
            <Breadcrumbs :items="[
                { label: 'Operations', url: null },
                { label: 'Master Bookings', url: null }
            ]" />
        </template>

        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Master Bookings</h1>
            <Link href="/bookings/create" class="px-5 py-2.5 bg-[var(--brand-600)] text-white text-sm font-bold rounded-xl hover:bg-[var(--brand-500)] transition shadow-lg shadow-brand-500/20 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                New Booking
            </Link>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Reference / Invoice</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Corporate Client</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Total Value</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                    <tr v-if="bookings.data.length === 0">
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            <div class="mb-2"><svg class="w-8 h-8 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg></div>
                            No active bookings found. Click 'New Booking' to initiate an order.
                        </td>
                    </tr>
                    <tr v-for="booking in bookings.data" :key="booking.id" class="hover:bg-blue-50/30 transition-colors group">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-bold text-gray-900">{{ booking.reference_no }}</div>
                            <div v-if="booking.invoice_no" class="text-[10px] font-mono text-[var(--brand-600)] mt-0.5 border border-[var(--brand-200)] bg-[var(--brand-50)] inline-block px-1.5 rounded">{{ booking.invoice_no }}</div>
                            <div v-else class="text-[10px] text-gray-400 mt-0.5 italic">Pending Invoice</div>
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium text-gray-900">{{ booking.client?.name || 'Unassigned / Walk-in' }}</div>
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-bold text-gray-700">RM {{ parseFloat(booking.total_amount).toFixed(2) }}</div>
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ new Date(booking.created_at).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) }}
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap">
                            <span v-if="booking.status === 'Draft'" class="px-2.5 py-1 text-xs font-bold bg-amber-50 text-amber-700 rounded-md border border-amber-200 shadow-sm">Draft</span>
                            <span v-else-if="booking.status === 'Invoiced'" class="px-2.5 py-1 text-xs font-bold bg-emerald-50 text-emerald-700 rounded-md border border-emerald-200 shadow-sm flex items-center gap-1 inline-flex">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Locked
                                </span>
                            <span v-else class="px-2.5 py-1 text-xs font-bold bg-gray-100 text-gray-700 rounded-md border border-gray-200">{{ booking.status }}</span>
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <Link :href="`/bookings/${booking.id}`" class="text-[var(--brand-600)] hover:text-[var(--brand-800)] opacity-0 group-hover:opacity-100 transition-opacity font-bold">Manage &rarr;</Link>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="bookings.total > 0" class="p-4 border-t border-gray-100 bg-gray-50 text-xs text-gray-500 text-center">
                Displaying {{ bookings.data.length }} of {{ bookings.total }} operational records.
            </div>
        </div>
    </TenantLayout>
</template>

<script setup>
import TenantLayout from '../../Layouts/TenantLayout.vue';
import Breadcrumbs from '../../Components/UI/Breadcrumbs.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    bookings: Object
});
</script>
