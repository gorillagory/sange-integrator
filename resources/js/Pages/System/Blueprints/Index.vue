<template>
    <div class="min-h-screen bg-[#0f172a] font-sans text-gray-200">
        <SystemLayout>

            <div class="flex justify-between items-center mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-white">The Blueprint Forge</h2>
                    <p class="text-sm text-gray-400 mt-1">Manage dynamic JSON schemas and operational modules for tenants.</p>
                </div>

                <Link href="/blueprints/create" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-bold rounded-xl transition shadow-lg shadow-indigo-500/20 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                    Forge New Blueprint
                </Link>
            </div>

            <div class="bg-[#1e293b] rounded-2xl border border-white/10 overflow-hidden shadow-xl">
                <div class="px-6 py-4 border-b border-white/10 bg-black/20 flex justify-between items-center">
                    <div class="relative w-64">
                        <input type="text" placeholder="Search schemas..." class="w-full pl-9 pr-4 py-2 text-sm bg-black/20 border border-white/10 rounded-lg text-white placeholder-gray-500 focus:ring-indigo-500 focus:border-indigo-500">
                        <svg class="w-4 h-4 text-gray-500 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-white/5">
                        <thead class="bg-black/20">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Service / Payload Key</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Target Industry</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Form Architecture</th>
                            <th class="px-6 py-3 text-right text-xs font-bold text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                        <tr v-for="schema in schemas" :key="schema.id" class="hover:bg-white/5 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 rounded-lg flex items-center justify-center font-bold text-indigo-400 bg-indigo-500/10 border border-indigo-500/20 shadow-sm">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-bold text-white">{{ schema.display_name }}</div>
                                        <div class="text-xs text-indigo-400 mt-0.5 font-mono">key: {{ schema.service_type }}</div>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 text-xs font-bold bg-gray-500/10 text-gray-400 rounded-md border border-gray-500/20 uppercase tracking-wider">
                                        {{ schema.industry }}
                                    </span>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2 text-sm text-gray-300">
                                    <span class="font-mono text-emerald-400">{{ schema.schema_payload?.fields?.length || 0 }}</span> Input Fields
                                </div>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <Link :href="`/blueprints/${schema.id}/edit`" class="text-indigo-400 hover:text-indigo-300 transition flex inline-flex items-center gap-1 ml-auto">
                                    Edit Engine <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                </Link>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </SystemLayout>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import SystemLayout from "../../../Layouts/SystemLayout.vue";

defineProps({
    schemas: Array
});
</script>
