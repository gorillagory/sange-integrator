<template>
    <TenantLayout>
        <template #breadcrumbs>
            <Breadcrumbs
                :items="[
                    { label: 'Service Records', url: null },
                    { label: 'Reports', url: null },
                ]"
            />
        </template>

        <section class="mb-8 overflow-hidden rounded-[28px] border border-slate-200 bg-gradient-to-br from-slate-950 via-slate-900 to-sky-950 px-7 py-8 text-white shadow-xl shadow-slate-900/10">
            <div class="grid gap-8 lg:grid-cols-[1.3fr_0.7fr]">
                <div>
                    <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-sky-200">
                        Service Records Command View
                    </div>
                    <h1 class="max-w-2xl text-3xl font-black tracking-tight">
                        Service records at a glance
                    </h1>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-300">
                        Monitor service-record flow, draft pipeline exposure, and locked revenue without leaving the tenant workspace.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3 self-start">
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur">
                        <div class="text-[11px] font-bold uppercase tracking-wide text-slate-300">Draft Pipeline</div>
                        <div class="mt-2 text-2xl font-black text-amber-300">{{ formatMoney(stats.draft_pipeline_value) }}</div>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur">
                        <div class="text-[11px] font-bold uppercase tracking-wide text-slate-300">Recognized Revenue</div>
                        <div class="mt-2 text-2xl font-black text-emerald-300">{{ formatMoney(stats.recognized_revenue) }}</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mb-8 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                <div class="text-[11px] font-bold uppercase tracking-wide text-gray-500">Total Service Records</div>
                <div class="mt-2 text-3xl font-black text-gray-900">{{ stats.total_operations }}</div>
                <div class="mt-2 text-sm text-gray-500">All service records in this tenant environment.</div>
            </article>

            <article class="rounded-2xl border border-amber-100 bg-amber-50/60 p-5 shadow-sm">
                <div class="text-[11px] font-bold uppercase tracking-wide text-amber-700">Drafts</div>
                <div class="mt-2 text-3xl font-black text-amber-900">{{ stats.draft_operations }}</div>
                <div class="mt-2 text-sm text-amber-800/80">Service records waiting for document lock and final routing.</div>
            </article>

            <article class="rounded-2xl border border-emerald-100 bg-emerald-50/70 p-5 shadow-sm">
                <div class="text-[11px] font-bold uppercase tracking-wide text-emerald-700">Locked</div>
                <div class="mt-2 text-3xl font-black text-emerald-900">{{ stats.locked_operations }}</div>
                <div class="mt-2 text-sm text-emerald-800/80">Service records locked and ready for downstream billing.</div>
            </article>

            <article class="rounded-2xl border border-sky-100 bg-sky-50/70 p-5 shadow-sm">
                <div class="text-[11px] font-bold uppercase tracking-wide text-sky-700">Active Contracts</div>
                <div class="mt-2 text-3xl font-black text-sky-900">{{ stats.active_contracts }}</div>
                <div class="mt-2 text-sm text-sky-800/80">Tenant-scoped client contracts currently available for routing.</div>
            </article>
        </section>

        <section class="grid gap-8 xl:grid-cols-[1.2fr_0.8fr]">
            <article class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h2 class="text-lg font-bold text-gray-900">Recent Service Record Activity</h2>
                    <p class="mt-1 text-sm text-gray-500">Latest service-record flow across draft and locked records.</p>
                </div>

                <div
                    v-if="recentOperations.length === 0"
                    class="px-6 py-12 text-center text-sm text-gray-500"
                >
                    No service-record activity recorded yet.
                </div>

                <div
                    v-else
                    class="divide-y divide-gray-100"
                >
                    <div
                        v-for="operation in recentOperations"
                        :key="operation.id"
                        class="grid gap-3 px-6 py-4 md:grid-cols-[1.1fr_1fr_auto]"
                    >
                        <div>
                            <div class="font-bold text-gray-900">{{ operation.reference_no }}</div>
                            <div class="mt-1 text-sm text-gray-500">
                                {{ operation.client_name || 'No client locked yet' }}
                            </div>
                        </div>

                        <div>
                            <div class="text-sm font-semibold text-gray-700">
                                {{ operation.document_no || 'Document pending' }}
                            </div>
                            <div class="mt-1 text-xs text-gray-500">
                                {{ formatDate(operation.created_at) }}
                            </div>
                        </div>

                        <div class="text-right">
                            <div class="text-base font-black text-gray-900">{{ formatMoney(operation.total_amount) }}</div>
                            <div
                                class="mt-2 inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold"
                                :class="operation.status === 'DocumentLocked'
                                    ? 'bg-emerald-100 text-emerald-700'
                                    : 'bg-amber-100 text-amber-700'"
                            >
                                {{ operation.status }}
                            </div>
                        </div>
                    </div>
                </div>
            </article>

            <article class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h2 class="text-lg font-bold text-gray-900">Monthly Locked Revenue</h2>
                    <p class="mt-1 text-sm text-gray-500">Last six recorded months based on document lock activity.</p>
                </div>

                <div
                    v-if="monthlyRevenue.length === 0"
                    class="px-6 py-12 text-center text-sm text-gray-500"
                >
                    No locked revenue has been recorded yet.
                </div>

                <div
                    v-else
                    class="space-y-4 px-6 py-5"
                >
                    <div
                        v-for="entry in monthlyRevenue"
                        :key="entry.month"
                    >
                        <div class="mb-2 flex items-center justify-between gap-3 text-sm">
                            <span class="font-bold text-gray-800">{{ formatMonth(entry.month) }}</span>
                            <span class="text-gray-500">{{ entry.count }} lock{{ entry.count === 1 ? '' : 's' }}</span>
                        </div>

                        <div class="h-3 overflow-hidden rounded-full bg-slate-100">
                            <div
                                class="h-full rounded-full bg-gradient-to-r from-sky-500 via-cyan-500 to-emerald-400"
                                :style="{ width: `${barWidth(entry.amount)}%` }"
                            />
                        </div>

                        <div class="mt-2 text-sm font-semibold text-gray-700">{{ formatMoney(entry.amount) }}</div>
                    </div>
                </div>
            </article>
        </section>
    </TenantLayout>
</template>

<script setup>
import TenantLayout from '../../Layouts/TenantLayout.vue';
import Breadcrumbs from '../../Components/UI/Breadcrumbs.vue';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            total_operations: 0,
            draft_operations: 0,
            locked_operations: 0,
            active_contracts: 0,
            draft_pipeline_value: 0,
            recognized_revenue: 0,
        }),
    },
    recentOperations: {
        type: Array,
        default: () => [],
    },
    monthlyRevenue: {
        type: Array,
        default: () => [],
    },
});

function formatMoney(value) {
    return `RM ${Number(value || 0).toFixed(2)}`;
}

function formatDate(value) {
    if (!value) {
        return 'Unknown date';
    }

    return new Date(value).toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
}

function formatMonth(value) {
    if (!value) {
        return 'Unknown';
    }

    const [year, month] = value.split('-');
    const date = new Date(Number(year), Number(month) - 1, 1);

    return date.toLocaleDateString('en-GB', {
        month: 'short',
        year: 'numeric',
    });
}

function barWidth(amount) {
    const max = Math.max(...props.monthlyRevenue.map((entry) => Number(entry.amount || 0)), 0);

    if (max <= 0) {
        return 0;
    }

    return Math.max(8, Math.round((Number(amount || 0) / max) * 100));
}
</script>
