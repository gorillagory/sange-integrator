<template>
    <div>
        <h1 class="text-3xl font-bold text-gray-900 tracking-tight mb-2">Vault Dashboard</h1>
        <p class="text-gray-500 mb-8">Welcome back. The system is operational.</p>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200/60 hover:shadow-md transition-shadow">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-4 border bg-[var(--brand-50)] text-[var(--brand-600)] border-[var(--brand-100)]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <h3 class="text-sm font-medium text-gray-500">Active Records</h3>
                <p class="text-3xl font-bold text-gray-900 mt-1">24,591</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import TenantLayout from '../Layouts/TenantLayout.vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

if (page.props.currentCompany?.name?.includes('Bayamedic')) {
    page.props.currentCompany.theme_color = '#0284c7'; // Clinical Blue
} else if (page.props.currentCompany?.name?.includes('Travel')) {
    page.props.currentCompany.theme_color = '#059669'; // Emerald Green
}

defineOptions({
    layout: TenantLayout
});
</script>
