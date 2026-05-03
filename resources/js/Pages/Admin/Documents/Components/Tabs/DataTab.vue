<template>
    <div class="p-4 flex flex-col h-full space-y-4">
        <div>
            <h4 class="text-[10px] font-bold text-gray-900 uppercase tracking-wider mb-1">Data Dictionary</h4>
            <p class="text-[9px] text-gray-500 leading-relaxed">
                Click a token below to insert it into the selected text block. The PDF Compiler will replace these with live database values.
            </p>
        </div>

        <div class="flex-1 overflow-y-auto pr-1">
            <div v-for="(group, groupName) in dictionary" :key="groupName" class="mb-4">
                <h5 class="text-[9px] font-bold text-[var(--brand-600)] uppercase tracking-wider mb-2 border-b border-[var(--brand-100)] pb-1">{{ groupName }}</h5>
                <div class="flex flex-wrap gap-1.5">
                    <button
                        v-for="token in group"
                        :key="token"
                        @click="insertToken(token)"
                        class="text-[9px] bg-gray-50 hover:bg-[var(--brand-50)] text-gray-700 hover:text-[var(--brand-700)] px-2 py-1 rounded border border-gray-200 hover:border-[var(--brand-200)] font-mono transition shadow-sm text-left"
                    >
                        &lbrace;&lbrace; {{ token }} &rbrace;&rbrace;
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    activeNode: { type: Object, required: true },
    documentType: { type: String, required: true }
});
const emit = defineEmits(['update']);

// This dictionary simulates what the PdfCompilerService expects based on the DocumentPayloadTransformer
const dictionary = computed(() => {
    const base = {
        'Global Settings': ['company_name', 'company_email', 'company_phone', 'company_address'],
        'Client Details': ['client_name', 'client_address_1', 'client_address_2', 'client_address_3', 'client_referral'],
    };

    if (props.documentType === 'invoice') {
        base['Invoice Info'] = ['invoice_number', 'invoice_date', 'invoice_term', 'invoice_personnel'];
        base['Financials'] = ['subtotal', 'tax_amount', 'grand_total', 'amount_paid', 'balance_due'];
    } else if (props.documentType === 'receipt') {
        base['Receipt Info'] = ['receipt_number', 'receipt_date', 'payment_method', 'transaction_ref'];
        base['Financials'] = ['amount_paid', 'balance_remaining'];
    }

    return base;
});

const insertToken = (token) => {
    if (!props.activeNode.content) props.activeNode.content = '';
    // Append the token with a space (This is safe in the script tag!)
    props.activeNode.content += (props.activeNode.content.length > 0 ? ' ' : '') + `{{ ${token} }}`;
    emit('update');
};
</script>
