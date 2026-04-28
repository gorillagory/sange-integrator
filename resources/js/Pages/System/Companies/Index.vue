<template>
    <div class="min-h-screen bg-[#0f172a] font-sans text-gray-200">
        <SystemLayout>
            <main class="max-w-7xl mx-auto p-8 mt-4">

                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h2 class="text-3xl font-bold text-white">Tenant Companies</h2>
                        <p class="text-sm text-gray-400 mt-1">Manage global subdomains, databases, and enterprise access.</p>
                    </div>
                    <button class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-bold rounded-xl transition shadow-lg shadow-indigo-500/20">
                        + Deploy New Tenant
                    </button>
                </div>

                <div class="bg-[#1e293b] rounded-2xl border border-white/10 overflow-hidden shadow-xl">

                    <div class="px-6 py-4 border-b border-white/10 bg-black/20 flex justify-between items-center">
                        <div class="relative w-64">
                            <input type="text" placeholder="Search tenants..." class="w-full pl-9 pr-4 py-2 text-sm bg-black/20 border border-white/10 rounded-lg text-white placeholder-gray-500 focus:ring-indigo-500 focus:border-indigo-500">
                            <svg class="w-4 h-4 text-gray-500 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-white/5">
                            <thead class="bg-black/20">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Company & Domain</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Database Architecture</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Industry / Theme</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                            <tr v-for="company in companies.data" :key="company.id" class="hover:bg-white/5 transition-colors">

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 rounded-lg flex items-center justify-center font-bold text-white shadow-sm" :style="{ backgroundColor: company.theme_color || '#3b82f6' }">
                                            {{ company.name.charAt(0) }}
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-bold text-white">{{ company.name }}</div>
                                            <div class="text-xs text-gray-400 mt-0.5 font-mono">https://{{ company.subdomain }}.bayam.test</div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-xs font-mono bg-black/30 text-indigo-300 px-2.5 py-1 rounded border border-indigo-500/20 inline-block">
                                        {{ company.db_name }}
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-300 capitalize">{{ company.industry }}</div>
                                    <div class="flex items-center gap-2 mt-1">
                                        <div class="w-3 h-3 rounded-full border border-white/20" :style="{ backgroundColor: company.theme_color || '#3b82f6' }"></div>
                                        <span class="text-xs text-gray-500 font-mono">{{ company.theme_color || '#3b82f6' }}</span>
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span v-if="company.is_active" class="px-2.5 py-1 text-xs font-bold bg-emerald-500/10 text-emerald-400 rounded-md border border-emerald-500/20">Online</span>
                                    <span v-else class="px-2.5 py-1 text-xs font-bold bg-red-500/10 text-red-400 rounded-md border border-red-500/20">Suspended</span>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button class="text-indigo-400 hover:text-indigo-300 mr-4 transition">Configure</button>
                                    <a :href="`http://${company.subdomain}.bayam.test:8000/dashboard`" target="_blank" class="text-gray-400 hover:text-white transition flex inline-flex items-center gap-1">
                                        Vault <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                    </a>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </main>
        </SystemLayout>



    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import SystemLayout from "../../../Layouts/SystemLayout.vue";

defineProps({
    companies: Object
});
</script>
