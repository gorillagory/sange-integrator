<template>
    <SystemLayout>
        <template #header>
            <div class="min-w-0">
                <h1 class="truncate text-3xl font-black tracking-tight text-white">
                    Audit Logs
                </h1>
                <p class="mt-1 text-sm text-slate-400">
                    Inspect system activity, access events, and change history.
                </p>
            </div>
        </template>

        <div class="space-y-6">
            <section class="rounded-3xl border border-white/10 bg-[#0f172a] p-5 shadow-xl shadow-black/20 sm:p-6">
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <MetricCard label="Total Events" :value="metrics.total" />
                    <MetricCard label="Last 24 Hours" :value="metrics.last_24h" />
                    <MetricCard label="Auth Events" :value="metrics.auth" />
                    <MetricCard label="Access Denied" :value="metrics.access_denied" />
                </div>
            </section>

            <section class="rounded-3xl border border-white/10 bg-[#0f172a] p-5 shadow-xl shadow-black/20 sm:p-6">
                <div class="mb-4 text-[11px] font-bold uppercase tracking-[0.24em] text-slate-500">
                    Segment Volume
                </div>

                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
                    <button
                        v-for="segment in segmentOptions"
                        :key="segment"
                        type="button"
                        class="rounded-2xl border px-4 py-4 text-left transition"
                        :class="activeSegmentClass(segment)"
                        @click="toggleSegment(segment)"
                    >
                        <div class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400">
                            {{ segment }}
                        </div>
                        <div class="mt-2 text-3xl font-black text-white">
                            {{ segmentCounts[segment] ?? 0 }}
                        </div>
                    </button>
                </div>
            </section>

            <section class="rounded-3xl border border-white/10 bg-[#0f172a] p-5 shadow-xl shadow-black/20 sm:p-6">
                <div class="grid gap-4 xl:grid-cols-[2fr,1fr]">
                    <div>
                        <div class="mb-3 text-sm font-bold text-white">
                            7-Day Event Trend
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-[#111b31] p-4">
                            <div class="grid grid-cols-7 gap-2">
                                <div
                                    v-for="point in trend"
                                    :key="point.day"
                                    class="flex flex-col items-center justify-end gap-2"
                                >
                                    <div class="flex h-28 w-full items-end">
                                        <div
                                            class="w-full rounded-md bg-indigo-500/80"
                                            :style="{ height: `${barHeight(point.total, maxTrendValue)}%` }"
                                        />
                                    </div>
                                    <div class="text-[11px] font-semibold text-slate-400">
                                        {{ point.label }}
                                    </div>
                                    <div class="text-xs font-bold text-white">
                                        {{ point.total }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="mb-3 text-sm font-bold text-white">
                            Top Actions
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-[#111b31] p-4">
                            <div v-if="topActions.length" class="space-y-3">
                                <div v-for="item in topActions" :key="item.action" class="space-y-1">
                                    <div class="flex items-center justify-between gap-2 text-xs text-slate-300">
                                        <span class="truncate">{{ item.action }}</span>
                                        <span class="font-bold text-white">{{ item.total }}</span>
                                    </div>

                                    <div class="h-2 rounded bg-white/10">
                                        <div
                                            class="h-2 rounded bg-cyan-400"
                                            :style="{ width: `${barWidth(item.total, maxTopActionValue)}%` }"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div v-else class="text-sm text-slate-500">
                                No action data for the selected filter set.
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="rounded-3xl border border-white/10 bg-[#0f172a] shadow-xl shadow-black/20">
                <div class="border-b border-white/10 px-5 py-5 sm:px-6">
                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-6">
                        <div class="xl:col-span-2">
                            <label class="mb-2 block text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">
                                Search
                            </label>
                            <input
                                v-model="form.search"
                                type="text"
                                placeholder="Action, actor, tenant, IP"
                                class="w-full rounded-2xl border border-white/10 bg-black/30 px-4 py-3 text-sm text-white outline-none transition placeholder:text-slate-500 focus:border-indigo-500"
                                @keyup.enter="applyFilters"
                            >
                        </div>

                        <div>
                            <label class="mb-2 block text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">
                                Category
                            </label>
                            <select
                                v-model="form.category"
                                class="w-full rounded-2xl border border-white/10 bg-black/30 px-4 py-3 text-sm text-white outline-none transition focus:border-indigo-500"
                            >
                                <option value="">All</option>
                                <option
                                    v-for="item in categoryOptions"
                                    :key="item"
                                    :value="item"
                                >
                                    {{ item }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">
                                Action
                            </label>
                            <input
                                v-model="form.action"
                                type="text"
                                placeholder="AUTH.LOGIN_SUCCESS"
                                class="w-full rounded-2xl border border-white/10 bg-black/30 px-4 py-3 text-sm text-white outline-none transition placeholder:text-slate-500 focus:border-indigo-500"
                                @keyup.enter="applyFilters"
                            >
                        </div>

                        <div>
                            <label class="mb-2 block text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">
                                From
                            </label>
                            <input
                                v-model="form.date_from"
                                type="date"
                                class="w-full rounded-2xl border border-white/10 bg-black/30 px-4 py-3 text-sm text-white outline-none transition focus:border-indigo-500"
                            >
                        </div>

                        <div>
                            <label class="mb-2 block text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">
                                To
                            </label>
                            <input
                                v-model="form.date_to"
                                type="date"
                                class="w-full rounded-2xl border border-white/10 bg-black/30 px-4 py-3 text-sm text-white outline-none transition focus:border-indigo-500"
                            >
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        <button
                            type="button"
                            class="rounded-2xl border border-white/10 bg-white/5 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/10"
                            @click="applyFilters"
                        >
                            Apply
                        </button>
                        <button
                            type="button"
                            class="rounded-2xl border border-white/10 px-4 py-2 text-sm font-semibold text-slate-300 transition hover:bg-white/5 hover:text-white"
                            @click="resetFilters"
                        >
                            Reset
                        </button>
                    </div>
                </div>

                <div v-if="logs.data.length" class="divide-y divide-white/10">
                    <div
                        v-for="log in logs.data"
                        :key="log.id"
                        class="px-5 py-5 sm:px-6"
                    >
                        <div class="flex flex-col gap-3 xl:flex-row xl:items-start xl:justify-between">
                            <div class="min-w-0 space-y-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-xl border px-2 py-1 text-[11px] font-bold uppercase tracking-[0.18em]" :class="segmentPillClass(log.segment)">
                                        {{ log.segment }}
                                    </span>
                                    <span class="text-sm font-bold text-white">
                                        {{ log.action }}
                                    </span>
                                    <span class="text-xs text-slate-500">
                                        {{ log.category }}
                                    </span>
                                </div>

                                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-400">
                                    <span>{{ log.created_at }}</span>
                                    <span>Actor: {{ log.actor.name || 'N/A' }}</span>
                                    <span>Tenant: {{ log.tenant.name || 'N/A' }}</span>
                                    <span>IP: {{ log.ip_address || 'N/A' }}</span>
                                </div>

                                <div class="text-xs text-slate-500">
                                    Resource: {{ log.resource.type || 'N/A' }} <span v-if="log.resource.id">#{{ log.resource.id }}</span>
                                </div>
                            </div>

                            <details class="w-full xl:w-[36rem] rounded-2xl border border-white/10 bg-[#111b31] p-3">
                                <summary class="cursor-pointer text-sm font-semibold text-slate-200">
                                    View Payload
                                </summary>
                                <div class="mt-3 grid gap-3 md:grid-cols-2">
                                    <div>
                                        <div class="mb-1 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">
                                            New Values
                                        </div>
                                        <pre class="max-h-56 overflow-auto rounded-xl bg-black/30 p-3 text-xs text-slate-300">{{ pretty(log.new_values) }}</pre>
                                    </div>
                                    <div>
                                        <div class="mb-1 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">
                                            Old Values
                                        </div>
                                        <pre class="max-h-56 overflow-auto rounded-xl bg-black/30 p-3 text-xs text-slate-300">{{ pretty(log.old_values) }}</pre>
                                    </div>
                                </div>
                            </details>
                        </div>
                    </div>
                </div>

                <div
                    v-else
                    class="px-6 py-16 text-center text-sm text-slate-500"
                >
                    No audit events found for the current filter set.
                </div>
            </section>

            <div v-if="logs.links?.length" class="flex flex-wrap items-center gap-2">
                <button
                    v-for="link in logs.links"
                    :key="`${link.label}-${link.url}`"
                    type="button"
                    class="rounded-xl px-3 py-2 text-sm transition"
                    :class="link.active
                        ? 'bg-indigo-600 font-semibold text-white'
                        : 'border border-white/10 bg-[#0f172a] text-slate-300 hover:bg-white/5'"
                    :disabled="!link.url"
                    @click="visitLink(link.url)"
                    v-html="link.label"
                />
            </div>
        </div>
    </SystemLayout>
</template>

<script setup>
import { computed, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import SystemLayout from '@/Layouts/SystemLayout.vue';

const props = defineProps({
    logs: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
    metrics: {
        type: Object,
        required: true,
    },
    segmentCounts: {
        type: Object,
        required: true,
    },
    topActions: {
        type: Array,
        default: () => [],
    },
    trend: {
        type: Array,
        default: () => [],
    },
    segmentOptions: {
        type: Array,
        default: () => [],
    },
    categoryOptions: {
        type: Array,
        default: () => [],
    },
});

const form = reactive({
    search: props.filters.search ?? '',
    segment: props.filters.segment ?? '',
    category: props.filters.category ?? '',
    action: props.filters.action ?? '',
    date_from: props.filters.date_from ?? '',
    date_to: props.filters.date_to ?? '',
});

const maxTopActionValue = computed(() => {
    const values = props.topActions.map((item) => Number(item.total) || 0);
    return Math.max(1, ...values, 1);
});

const maxTrendValue = computed(() => {
    const values = props.trend.map((item) => Number(item.total) || 0);
    return Math.max(1, ...values, 1);
});

const queryPayload = () => ({
    search: form.search || undefined,
    segment: form.segment || undefined,
    category: form.category || undefined,
    action: form.action || undefined,
    date_from: form.date_from || undefined,
    date_to: form.date_to || undefined,
});

const applyFilters = () => {
    router.get('/audit-logs', queryPayload(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const resetFilters = () => {
    form.search = '';
    form.segment = '';
    form.category = '';
    form.action = '';
    form.date_from = '';
    form.date_to = '';
    applyFilters();
};

const toggleSegment = (segment) => {
    form.segment = form.segment === segment ? '' : segment;
    applyFilters();
};

const visitLink = (url) => {
    if (!url) {
        return;
    }

    router.visit(url, {
        preserveState: true,
        preserveScroll: true,
    });
};

const barWidth = (value, max) => Math.max(4, Math.round((Number(value) / max) * 100));
const barHeight = (value, max) => Math.max(6, Math.round((Number(value) / max) * 100));

const pretty = (payload) => {
    try {
        return JSON.stringify(payload ?? {}, null, 2);
    } catch (error) {
        return '{}';
    }
};

const activeSegmentClass = (segment) => {
    if (form.segment === segment) {
        return 'border-indigo-500 bg-indigo-500/15 text-white';
    }

    return 'border-white/10 bg-[#111b31] text-slate-300 hover:border-indigo-500/40 hover:bg-white/5';
};

const segmentPillClass = (segment) => {
    const map = {
        AUTH: 'border-cyan-400/40 bg-cyan-500/15 text-cyan-200',
        ACCESS: 'border-rose-400/40 bg-rose-500/15 text-rose-200',
        USER_ADMIN: 'border-amber-400/40 bg-amber-500/15 text-amber-200',
        DATA: 'border-emerald-400/40 bg-emerald-500/15 text-emerald-200',
        SYSTEM: 'border-slate-400/40 bg-slate-500/15 text-slate-200',
    };

    return map[segment] ?? map.SYSTEM;
};

const MetricCard = {
    props: {
        label: {
            type: String,
            required: true,
        },
        value: {
            type: [Number, String],
            required: true,
        },
    },
    template: `
        <div class="rounded-2xl border border-white/10 bg-[#111b31] p-5">
            <div class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">{{ label }}</div>
            <div class="mt-3 text-4xl font-black text-white">{{ value }}</div>
        </div>
    `,
};
</script>
