<!-- resources/js/Pages/Operations/Components/CartSummary.vue -->
<template>
    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
        <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4">
            <div class="font-bold text-gray-900">Operation Control Summary</div>
            <div class="mt-1 text-xs text-gray-500">
                {{ itemCount }} service line<span v-if="itemCount !== 1">s</span> ready
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

                <div class="rounded-xl border border-[var(--brand-200)] bg-[var(--brand-50)] p-4">
                    <div class="text-[10px] font-bold uppercase tracking-wide text-[var(--brand-600)]">
                        Grand Total
                    </div>
                    <div class="mt-2 text-xl font-black text-[var(--brand-800)]">
                        RM {{ money(totals.grand) }}
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-dashed border-gray-200 bg-gray-50 p-4 text-xs text-gray-600">
                The operation payload will be created only when routing is complete and at least one service line exists.
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
                {{ processing ? 'Submitting...' : 'Create Operation' }}
            </button>
        </div>
    </div>
</template>

<script setup>
defineProps({
    totals: {
        type: Object,
        required: true,
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
});

defineEmits(['submit']);

function money(value) {
    return Number(value || 0).toFixed(2);
}
</script>
