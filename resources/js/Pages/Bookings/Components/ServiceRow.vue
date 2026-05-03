<template>
    <div class="border rounded-2xl transition-all duration-300 shadow-sm" :class="isEditing ? 'bg-white border-[var(--brand-200)] ring-4 ring-blue-50/50' : 'bg-gray-50/50 border-gray-200 hover:border-gray-300 hover:shadow-md'">

        <div v-if="isEditing" class="p-6 relative">
            <button @click.prevent="$emit('remove')" class="absolute top-6 right-6 text-gray-400 hover:text-red-500 transition" title="Remove Service">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            </button>
            <h4 class="font-bold text-[var(--brand-700)] uppercase text-sm mb-6 pb-3 border-b border-gray-100 mr-8 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                {{ schemaName }}
            </h4>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-8">
                <div v-for="field in fields" :key="field.key" :class="field.grid_span === 2 || field.ui_component === 'textarea' ? 'md:col-span-2' : 'md:col-span-1'">
                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1.5 flex justify-between items-center">
                        <span>{{ field.label }} <span v-if="field.rules.includes('required')" class="text-red-500">*</span></span>
                        <span v-if="field.is_array" class="text-[9px] text-[var(--brand-500)] bg-[var(--brand-50)] px-1.5 py-0.5 rounded border border-[var(--brand-200)]">Repeatable List</span>
                    </label>

                    <template v-if="!field.is_array">

                        <textarea v-if="field.ui_component === 'textarea'"
                                  v-model="item.service_details[field.key]"
                                  rows="3"
                                  :required="field.rules.includes('required')"
                                  class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:bg-white focus:border-[var(--brand-500)] focus:ring-[var(--brand-500)] transition-colors"
                                  :style="{ textTransform: field.text_transform === 'none' ? 'none' : field.text_transform }"
                                  :placeholder="field.placeholder || 'Enter details...'"></textarea>

                        <input v-else-if="field.ui_component === 'file'"
                               type="file"
                               :required="field.rules.includes('required')"
                               @change="e => item.service_details[field.key] = e.target.files[0]"
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-[var(--brand-700)] hover:file:bg-[var(--brand-100)] border border-gray-200 rounded-lg bg-gray-50 cursor-pointer transition-colors">

                        <input v-else
                               :type="getHtmlInputType(field.type, field.ui_component)"
                               v-model="item.service_details[field.key]"
                               :required="field.rules.includes('required')"
                               class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:bg-white focus:border-[var(--brand-500)] focus:ring-[var(--brand-500)] transition-colors"
                               :style="{ textTransform: field.text_transform === 'none' ? 'none' : field.text_transform }"
                               :placeholder="field.placeholder || `Enter ${field.label}...`">
                    </template>

                    <template v-else>
                        <div class="space-y-2 p-3 bg-gray-50 border border-gray-200 rounded-xl">
                            <div v-for="(line, lineIndex) in item.service_details[field.key]" :key="lineIndex" class="flex gap-2 relative group/line">

                                <textarea v-if="field.ui_component === 'textarea'"
                                          v-model="item.service_details[field.key][lineIndex]"
                                          rows="2"
                                          :required="field.rules.includes('required') && lineIndex === 0"
                                          class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-[var(--brand-500)] focus:ring-[var(--brand-500)] shadow-sm"
                                          :style="{ textTransform: field.text_transform === 'none' ? 'none' : field.text_transform }"
                                          :placeholder="`${field.placeholder || field.label} ${lineIndex + 1}`"></textarea>

                                <input v-else
                                       :type="getHtmlInputType(field.type, field.ui_component)"
                                       v-model="item.service_details[field.key][lineIndex]"
                                       :required="field.rules.includes('required') && lineIndex === 0"
                                       class="w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-[var(--brand-500)] focus:ring-[var(--brand-500)] shadow-sm"
                                       :style="{ textTransform: field.text_transform === 'none' ? 'none' : field.text_transform }"
                                       :placeholder="`${field.placeholder || field.label} ${lineIndex + 1}`">

                                <button v-if="item.service_details[field.key].length > 1"
                                        @click.prevent="item.service_details[field.key].splice(lineIndex, 1)"
                                        class="shrink-0 w-10 flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition" title="Remove line">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>
                            <button @click.prevent="item.service_details[field.key].push('')" class="text-xs font-bold text-[var(--brand-600)] hover:text-[var(--brand-800)] flex items-center gap-1 mt-2 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                Add another {{ field.label }}
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            <div class="bg-gray-50 p-5 rounded-xl border border-gray-200 grid grid-cols-1 md:grid-cols-12 gap-5 items-start mb-6">
                <div class="md:col-span-2">
                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1.5">QTY</label>
                    <input v-model.number="item.qty" type="number" min="1" class="w-full px-2 py-2.5 bg-white border border-gray-300 rounded-lg text-sm font-bold focus:border-[var(--brand-500)] text-center">
                </div>

                <div class="md:col-span-3">
                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1.5">Supplier Cost (Unit)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm font-bold">RM</span>
                        <input v-model.number="item.unit_fare" type="number" step="0.01" class="w-full pl-9 pr-3 py-2.5 bg-white border border-gray-300 rounded-lg text-sm font-bold focus:border-[var(--brand-500)]" placeholder="0.00">
                    </div>
                </div>

                <div class="md:col-span-3">
                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1.5">Tax (Unit)</label>
                    <div class="flex relative">
                        <select v-model="item.tax_type" class="w-20 bg-gray-100 border border-gray-300 rounded-l-lg text-xs font-bold focus:z-10 focus:border-[var(--brand-500)]">
                            <option value="RM">RM</option>
                            <option value="%">%</option>
                        </select>
                        <input v-model.number="item.tax_value" type="number" step="0.01" class="w-full border border-l-0 border-gray-300 bg-white rounded-r-lg px-3 py-2.5 text-sm focus:border-[var(--brand-500)]" placeholder="0.00">
                    </div>
                    <div class="text-[10px] text-gray-500 font-medium mt-1.5 ml-1">Est: RM {{ formatNumber(calculatedTax) }}</div>
                </div>

                <div class="md:col-span-4">
                    <label class="block text-[10px] font-black text-[var(--brand-600)] uppercase mb-1.5">Total Charged to Client (Unit)</label>
                    <div class="relative shadow-sm">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[var(--brand-700)] text-sm font-black">RM</span>
                        <input v-model.number="item.client_price" type="number" step="0.01" class="w-full pl-10 border border-[var(--brand-300)] bg-blue-50 text-[var(--brand-800)] font-black rounded-lg py-2.5 text-sm focus:border-[var(--brand-500)] focus:ring-[var(--brand-500)]" placeholder="0.00">
                    </div>
                    <div class="text-[10px] text-[var(--brand-500)] font-bold mt-1.5 ml-1">Est. Profit: RM {{ formatNumber(calculatedProfit) }}</div>
                </div>
            </div>

            <div class="flex justify-end border-t border-gray-100 pt-5">
                <button @click.prevent="isEditing = false" class="px-6 py-2.5 bg-gray-900 hover:bg-black text-white text-sm font-bold rounded-xl transition shadow-lg flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Confirm & Minimize Row
                </button>
            </div>
        </div>

        <div v-else class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 cursor-pointer" @click="isEditing = true">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <div>
                    <h4 class="font-bold text-gray-900 text-sm">{{ schemaName }}</h4>
                    <p class="text-xs text-gray-500 mt-0.5 truncate max-w-md">{{ summarySnippet || 'No operational details provided.' }}</p>
                </div>
            </div>

            <div class="flex items-center gap-6 sm:ml-auto border-t sm:border-t-0 border-gray-200 pt-3 sm:pt-0">
                <div class="text-right">
                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wide">Line Total (Qty: {{ item.qty || 1 }})</div>
                    <div class="font-bold text-gray-900">RM {{ formatNumber(lineTotal) }}</div>
                </div>
                <button @click.stop="isEditing = true" class="px-4 py-2 bg-white border border-gray-200 hover:border-gray-300 text-gray-700 text-xs font-bold rounded-lg transition shadow-sm flex items-center gap-2">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    Edit
                </button>
                <button @click.stop="$emit('remove')" class="text-gray-400 hover:text-red-500 transition" title="Remove">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    item: Object,
    schemas: Array
});
defineEmits(['remove']);

const isEditing = ref(true);

// 🟢 THE FIX: Checks BOTH `type` and `ui_component` to find the correct HTML tag!
const getHtmlInputType = (type, uiComponent) => {
    const effectiveType = type || uiComponent || 'text';
    if (effectiveType === 'datetime') return 'datetime-local';
    if (['date', 'time', 'number', 'email', 'tel', 'color', 'password'].includes(effectiveType)) return effectiveType;
    return 'text'; // Fallback for typeahead, string, text_input
};

const formatNumber = (value) => {
    return (parseFloat(value) || 0).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
};

const schemaName = computed(() => {
    return props.schemas.find(s => s.service_type === props.item.service_type)?.display_name || props.item.service_type;
});

const fields = computed(() => {
    const schema = props.schemas.find(s => s.service_type === props.item.service_type);
    if (!schema) return [];

    let payload = schema.schema_payload;
    if (typeof payload === 'string') { try { payload = JSON.parse(payload); } catch (e) { payload = []; } }

    let fieldsArray = Array.isArray(payload) ? payload : (payload && Array.isArray(payload.fields) ? payload.fields : []);

    return fieldsArray.map(f => {
        if (typeof f === 'string') {
            return { key: f, label: f.replace(/_/g, ' '), type: 'string', ui_component: 'text_input', grid_span: 1, rules: [], is_array: false, text_transform: 'none' };
        }
        return {
            key: f.key || 'unknown_field',
            label: f.label || (f.key ? f.key.replace(/_/g, ' ') : 'Detail'),
            type: f.type || 'string',
            ui_component: f.ui_component || 'text_input',
            grid_span: f.grid_span || 1,
            placeholder: f.placeholder || '',
            rules: f.rules || [],
            is_array: f.is_array || false,
            text_transform: f.text_transform || 'none' // 🟢 Pulling the uppercase/capitalize rules
        };
    });
});

const calculatedTax = computed(() => {
    const base = parseFloat(props.item.unit_fare) || 0;
    const val = parseFloat(props.item.tax_value) || 0;
    return props.item.tax_type === '%' ? base * (val / 100) : val;
});

const calculatedProfit = computed(() => {
    const base = parseFloat(props.item.unit_fare) || 0;
    const clientPrice = parseFloat(props.item.client_price) || 0;
    return clientPrice - base - calculatedTax.value;
});

const lineTotal = computed(() => {
    const cp = parseFloat(props.item.client_price) || 0;
    const q = parseInt(props.item.qty) || 1;
    return cp * q;
});

const summarySnippet = computed(() => {
    if (!props.item.service_details) return '';
    const values = [];
    Object.values(props.item.service_details).forEach(val => {
        if (Array.isArray(val)) {
            val.forEach(v => { if (typeof v === 'string' && v.trim() !== '') values.push(v); });
        } else if (typeof val === 'string' && val.trim() !== '') {
            values.push(val);
        }
    });
    return values.slice(0, 3).join(' • ') + (values.length > 3 ? '...' : '');
});
</script>
