<template>
    <div class="space-y-4">
        <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4">
                <div class="font-bold text-gray-900">Service Record Summary</div>
                <div class="mt-1 text-xs text-gray-500">
                    {{ itemCount }} service row<span v-if="itemCount !== 1">s</span> ready
                </div>
            </div>

            <div class="space-y-5 p-6">
                <div class="grid grid-cols-2 gap-4">
                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                        <div class="text-[10px] font-bold uppercase tracking-wide text-gray-500">
                            Base Cost
                        </div>
                        <div class="mt-2 text-lg font-black text-gray-900">
                            RM {{ money(totals.base) }}
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                        <div class="text-[10px] font-bold uppercase tracking-wide text-gray-500">
                            Discount
                        </div>
                        <div class="mt-2 text-lg font-black text-rose-600">
                            RM {{ money(totals.discount) }}
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                        <div class="text-[10px] font-bold uppercase tracking-wide text-gray-500">
                            Tax
                        </div>
                        <div class="mt-2 text-lg font-black text-amber-600">
                            RM {{ money(totals.tax) }}
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                        <div class="text-[10px] font-bold uppercase tracking-wide text-gray-500">
                            Margin
                        </div>
                        <div
                            class="mt-2 text-lg font-black"
                            :class="Number(totals.markup || 0) < 0 ? 'text-rose-600' : 'text-emerald-600'"
                        >
                            RM {{ money(totals.markup) }}
                        </div>
                    </div>

                    <div class="col-span-2 rounded-xl border border-[var(--brand-200)] bg-[var(--brand-50)] p-4">
                        <div class="text-[10px] font-bold uppercase tracking-wide text-[var(--brand-600)]">
                            Grand Total
                        </div>
                        <div class="mt-2 text-xl font-black text-[var(--brand-800)]">
                            RM {{ money(totals.grand) }}
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-xl border p-4 text-xs"
                    :class="validationState.isValid ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-amber-200 bg-amber-50 text-amber-900'"
                >
                    <div class="font-bold">
                        {{ validationState.isValid ? 'Ready to submit' : 'Action still required' }}
                    </div>
                    <div class="mt-1">
                        {{ validationState.message }}
                    </div>
                    <ul v-if="validationState.items.length" class="mt-3 space-y-1.5">
                        <li
                            v-for="item in validationState.items"
                            :key="item"
                            class="flex items-start gap-2"
                        >
                            <span class="mt-0.5 h-1.5 w-1.5 rounded-full bg-current opacity-70" />
                            <span>{{ item }}</span>
                        </li>
                    </ul>
                </div>

                <button
                    type="button"
                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-[var(--brand-600)] px-4 py-3 text-sm font-bold text-white shadow-lg shadow-brand-500/20 transition hover:bg-[var(--brand-500)] disabled:cursor-not-allowed disabled:opacity-50 disabled:shadow-none"
                    :disabled="!isReady || processing"
                    @click="$emit('submit')"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ processing ? 'Submitting...' : 'Create Service Record' }}
                </button>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
            <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4">
                <div class="font-bold text-gray-900">Service Lines Preview</div>
                <div class="mt-1 text-xs text-gray-500">
                    A quick operational read of every row before submission.
                </div>
            </div>

            <div v-if="rows.length" class="divide-y divide-gray-100">
                <div
                    v-for="row in rows"
                    :key="row.id"
                    class="px-6 py-4"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <div class="truncate text-sm font-bold text-gray-900">
                                {{ row.name }}
                            </div>
                            <div class="mt-1 flex flex-wrap items-center gap-2 text-[11px] text-gray-500">
                                <span class="rounded-full bg-gray-100 px-2 py-0.5 font-semibold uppercase tracking-wide text-gray-600">
                                    {{ row.serviceCode }}
                                </span>
                                <span>{{ row.qty }} x {{ row.unitName || 'unit' }}</span>
                                <span v-if="row.requiredPending > 0" class="rounded-full bg-amber-100 px-2 py-0.5 font-semibold text-amber-800">
                                    {{ row.requiredPending }} required left
                                </span>
                                <span v-else class="rounded-full bg-emerald-100 px-2 py-0.5 font-semibold text-emerald-700">
                                    Complete
                                </span>
                            </div>
                        </div>

                        <div class="text-right">
                            <div class="text-[10px] font-bold uppercase tracking-wide text-gray-400">Line Total</div>
                            <div class="mt-1 text-sm font-black text-gray-900">RM {{ money(row.lineTotal) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div
                v-else
                class="px-6 py-8 text-sm text-gray-500"
            >
                Add schema vector rows to build a live preview list here.
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    totals: {
        type: Object,
        required: true,
    },
    rows: {
        type: Array,
        default: () => [],
    },
    itemCount: {
        type: Number,
        default: 0,
    },
    isReady: {
        type: Boolean,
        default: false,
    },
    processing: {
        type: Boolean,
        default: false,
    },
    validationState: {
        type: Object,
        default: () => ({
            isValid: false,
            message: '',
            items: [],
        }),
    },
});

defineEmits(['submit']);

function money(value) {
    return Number(value || 0).toFixed(2);
}
</script>
