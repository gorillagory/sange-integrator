<template>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden sticky top-8 drop-shadow-2xl z-10">

        <div class="bg-gray-900 px-6 py-4 flex justify-between items-center">
            <h3 class="font-bold text-white uppercase text-sm tracking-wider">Cart Summary</h3>
            <span class="text-xs text-gray-400 font-medium">{{ itemCount }} Item(s)</span>
        </div>

        <div class="p-6 space-y-4">
            <div class="flex justify-between items-center text-sm">
                <span class="text-gray-500 font-medium">Total Base Supplier Cost</span>
                <span class="font-bold text-gray-700">RM {{ formatNumber(totals.base) }}</span>
            </div>

            <div class="flex justify-between items-center text-sm">
                <span class="text-gray-500 font-medium">Total Operational Tax</span>
                <span class="font-bold text-gray-700">RM {{ formatNumber(totals.tax) }}</span>
            </div>

            <div class="flex justify-between items-center text-sm pb-5 border-b border-gray-200">
                <span class="text-[var(--brand-600)] font-bold">Total Profit Margin</span>
                <span class="font-bold text-[var(--brand-600)]">+ RM {{ formatNumber(totals.markup) }}</span>
            </div>

            <div class="flex justify-between items-end pt-2">
                <div>
                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Gross Invoice Value</div>
                    <div class="text-3xl font-black text-gray-900 tracking-tight">RM {{ formatNumber(totals.grand) }}</div>
                </div>
            </div>

            <button
                @click.prevent="$emit('submit')"
                :disabled="!isReady"
                class="w-full mt-6 py-4 bg-[var(--brand-600)] hover:bg-[var(--brand-500)] text-white text-sm font-bold rounded-xl transition-all shadow-lg shadow-brand-500/30 disabled:opacity-50 disabled:shadow-none disabled:cursor-not-allowed flex justify-center items-center gap-2"
            >
                Secure Routing & Continue &rarr;
            </button>

            <div v-if="!isReady" class="text-[10px] text-center text-gray-400 font-medium mt-2">
                Ensure a Corporate Client, Contract, and at least one Service are selected to proceed.
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    totals: {
        type: Object,
        required: true,
        default: () => ({ base: 0, tax: 0, markup: 0, grand: 0 })
    },
    itemCount: {
        type: Number,
        default: 0
    },
    isReady: {
        type: Boolean,
        default: false
    }
});

defineEmits(['submit']);

// 🛡️ Accounting Number Formatter
const formatNumber = (value) => {
    return (parseFloat(value) || 0).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
};
</script>
