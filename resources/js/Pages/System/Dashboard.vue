<template>
    <SystemLayout>

        <div class="mb-8">
            <h2 class="text-3xl font-bold text-white">System Pulse</h2>
            <p class="text-sm text-gray-400 mt-1">Real-time telemetry across all Tenant Vaults.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-[#1e293b] p-6 rounded-2xl border border-white/10 shadow-xl">
                <div class="text-gray-400 text-sm font-medium mb-2">Active Tenants</div>
                <div class="text-4xl font-bold text-white flex items-center gap-3">
                    <span class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></span>
                    2
                </div>
            </div>

            <div class="bg-[#1e293b] p-6 rounded-2xl border border-white/10 shadow-xl">
                <div class="text-gray-400 text-sm font-medium mb-2">Total System Load</div>
                <div class="text-4xl font-bold text-indigo-400">14%</div>
            </div>

            <div class="bg-[#1e293b] p-6 rounded-2xl border border-white/10 shadow-xl">
                <div class="text-gray-400 text-sm font-medium mb-2">Database Health</div>
                <div class="text-4xl font-bold text-white">Optimal</div>
            </div>
        </div>

    </SystemLayout>
</template>

<script setup>
import SystemLayout from '../../Layouts/SystemLayout.vue';
</script>
