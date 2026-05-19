<template>
    <div
        class="rounded-2xl border shadow-sm transition-all duration-300"
        :class="cardClasses"
    >
        <div v-if="isEditing" class="relative p-6">
            <div class="absolute right-6 top-6 flex items-center gap-2">
                <span
                    class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]"
                    :class="hasErrors ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-700'"
                >
                    {{ hasErrors ? 'Needs Attention' : 'Ready' }}
                </span>

                <button
                    type="button"
                    class="text-gray-400 transition hover:text-slate-600"
                    title="Save and minimize"
                    @click.prevent="saveAndMinimize"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                    </svg>
                </button>

                <button
                    type="button"
                    class="text-gray-400 transition hover:text-red-500"
                    title="Remove Service Row"
                    @click.prevent="$emit('remove')"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </div>

            <div class="mb-6 mr-24 flex flex-wrap items-start gap-3 border-b border-gray-100 pb-3">
                <div>
                    <h4 class="flex items-center gap-2 text-sm font-bold uppercase text-[var(--brand-700)]">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        {{ schemaName }}
                    </h4>
                    <div class="mt-1 flex flex-wrap gap-2 text-[11px] text-gray-500">
                        <span class="rounded-full bg-gray-100 px-2 py-0.5 font-semibold uppercase tracking-wide text-gray-600">
                            {{ item.service_code }}
                        </span>
                        <span v-if="pricingUnits.length" class="rounded-full bg-blue-50 px-2 py-0.5 font-semibold text-blue-700">
                            {{ pricingUnits.length }} unit option<span v-if="pricingUnits.length !== 1">s</span>
                        </span>
                    </div>
                </div>

                <div
                    v-if="hasErrors"
                    class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900"
                >
                    {{ validationCount }} field<span v-if="validationCount !== 1">s</span> still need attention.
                </div>
            </div>

            <div
                v-if="resolutionError"
                class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800"
            >
                <div class="font-bold">Row source needs refresh</div>
                <div class="mt-1">{{ resolutionError }}</div>
            </div>

            <div
                v-if="filteredErrorList.length"
                class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900"
            >
                <div class="font-bold">Finish these before you submit:</div>
                <ul class="mt-2 space-y-1">
                    <li v-for="error in filteredErrorList" :key="error">• {{ error }}</li>
                </ul>
            </div>

            <div class="mb-8 grid grid-cols-1 gap-5 md:grid-cols-2">
                <div
                    v-for="field in fields"
                    :key="field.key"
                    :class="field.grid_span === 2 || field.ui_component === 'textarea' ? 'md:col-span-2' : 'md:col-span-1'"
                >
                    <label class="mb-1.5 flex items-center justify-between text-[10px] font-bold uppercase text-gray-500">
                        <span>
                            {{ field.label }}
                            <span v-if="fieldIsRequired(field)" class="text-red-500">*</span>
                        </span>

                        <span
                            v-if="field.is_array"
                            class="rounded border border-[var(--brand-200)] bg-[var(--brand-50)] px-1.5 py-0.5 text-[9px] text-[var(--brand-500)]"
                        >
                            Repeatable List
                        </span>
                    </label>

                    <template v-if="!field.is_array">
                        <textarea
                            v-if="field.ui_component === 'textarea'"
                            v-model="item.service_details[field.key]"
                            rows="3"
                            :required="fieldIsRequired(field)"
                            class="w-full rounded-lg px-4 py-2.5 text-sm transition-colors focus:bg-white"
                            :class="inputClasses(fieldError(field.key))"
                            :style="{ textTransform: field.text_transform === 'none' ? 'none' : field.text_transform }"
                            :placeholder="field.placeholder || 'Enter details...'"
                        />

                        <select
                            v-else-if="field.ui_component === 'select' && field.options.length"
                            v-model="item.service_details[field.key]"
                            :required="fieldIsRequired(field)"
                            class="w-full rounded-lg px-4 py-2.5 text-sm transition-colors focus:bg-white"
                            :class="inputClasses(fieldError(field.key))"
                        >
                            <option :value="''" disabled>
                                Select {{ field.label.toLowerCase() }}...
                            </option>
                            <option
                                v-for="option in field.options"
                                :key="normalizeOptionValue(option)"
                                :value="normalizeOptionValue(option)"
                            >
                                {{ normalizeOptionLabel(option) }}
                            </option>
                        </select>

                        <input
                            v-else-if="field.ui_component === 'file'"
                            type="file"
                            :required="fieldIsRequired(field)"
                            class="block w-full cursor-pointer rounded-lg border bg-gray-50 text-sm text-gray-500 transition-colors file:mr-4 file:rounded-lg file:border-0 file:bg-blue-50 file:px-4 file:py-2.5 file:text-xs file:font-bold file:text-[var(--brand-700)] hover:file:bg-[var(--brand-100)]"
                            :class="fieldError(field.key) ? 'border-amber-300 bg-amber-50/70' : 'border-gray-200'"
                            @change="onFileChange($event, field.key)"
                        >

                        <input
                            v-else
                            v-model="item.service_details[field.key]"
                            :type="getHtmlInputType(field.type, field.ui_component)"
                            :required="fieldIsRequired(field)"
                            class="w-full rounded-lg px-4 py-2.5 text-sm transition-colors focus:bg-white"
                            :class="inputClasses(fieldError(field.key))"
                            :style="{ textTransform: field.text_transform === 'none' ? 'none' : field.text_transform }"
                            :placeholder="field.placeholder || `Enter ${field.label}...`"
                        >
                    </template>

                    <template v-else>
                        <div class="space-y-2 rounded-xl border p-3" :class="fieldError(field.key) ? 'border-amber-300 bg-amber-50/70' : 'border-gray-200 bg-gray-50'">
                            <div
                                v-for="(line, lineIndex) in item.service_details[field.key]"
                                :key="lineIndex"
                                class="relative flex gap-2"
                            >
                                <textarea
                                    v-if="field.ui_component === 'textarea'"
                                    v-model="item.service_details[field.key][lineIndex]"
                                    rows="2"
                                    :required="fieldIsRequired(field) && lineIndex === 0"
                                    class="w-full rounded-lg border bg-white px-3 py-2 text-sm shadow-sm"
                                    :class="inputClasses(fieldError(field.key))"
                                    :style="{ textTransform: field.text_transform === 'none' ? 'none' : field.text_transform }"
                                    :placeholder="`${field.placeholder || field.label} ${lineIndex + 1}`"
                                />

                                <input
                                    v-else
                                    v-model="item.service_details[field.key][lineIndex]"
                                    :type="getHtmlInputType(field.type, field.ui_component)"
                                    :required="fieldIsRequired(field) && lineIndex === 0"
                                    class="w-full rounded-lg border bg-white px-3 py-2 text-sm shadow-sm"
                                    :class="inputClasses(fieldError(field.key))"
                                    :style="{ textTransform: field.text_transform === 'none' ? 'none' : field.text_transform }"
                                    :placeholder="`${field.placeholder || field.label} ${lineIndex + 1}`"
                                >

                                <button
                                    v-if="item.service_details[field.key].length > 1"
                                    type="button"
                                    class="flex w-10 shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-red-50 hover:text-red-500"
                                    title="Remove line"
                                    @click.prevent="item.service_details[field.key].splice(lineIndex, 1)"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <button
                                type="button"
                                class="mt-2 flex items-center gap-1 text-xs font-bold text-[var(--brand-600)] transition-colors hover:text-[var(--brand-800)]"
                                @click.prevent="item.service_details[field.key].push('')"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Add another {{ field.label }}
                            </button>
                        </div>
                    </template>

                    <p v-if="fieldError(field.key)" class="mt-1.5 text-xs font-medium text-amber-700">
                        {{ fieldError(field.key) }}
                    </p>
                </div>
            </div>

            <div class="mb-6 grid grid-cols-1 items-start gap-5 rounded-xl border border-gray-200 bg-gray-50 p-5 md:grid-cols-12">
                <div class="md:col-span-2">
                    <label class="mb-1.5 block text-[10px] font-bold uppercase text-gray-500">Qty</label>
                    <input
                        v-model.number="item.qty"
                        type="number"
                        min="1"
                        class="w-full rounded-lg px-2 py-2.5 text-center text-sm font-bold"
                        :class="inputClasses(commercialError('qty'))"
                    >
                    <p v-if="commercialError('qty')" class="mt-1.5 text-xs font-medium text-amber-700">{{ commercialError('qty') }}</p>
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1.5 block text-[10px] font-bold uppercase text-gray-500">Unit</label>
                    <div class="relative">
                        <input
                            v-model="item.unit_name"
                            type="text"
                            class="w-full rounded-lg px-3 py-2.5 text-sm font-medium"
                            :class="inputClasses(commercialError('unit_name'))"
                            :placeholder="pricingUnits.length ? 'Type to search schema units...' : 'ticket, room, pax'"
                            @focus="unitSuggestionsOpen = true"
                            @input="unitSuggestionsOpen = true"
                            @blur="closeUnitSuggestions"
                        >

                        <div
                            v-if="unitSuggestionsOpen && filteredUnitSuggestions.length"
                            class="absolute z-20 mt-2 max-h-48 w-full overflow-y-auto rounded-xl border border-gray-200 bg-white p-2 shadow-xl"
                        >
                            <button
                                v-for="unit in filteredUnitSuggestions"
                                :key="unit"
                                type="button"
                                class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-sm text-gray-700 transition hover:bg-[var(--brand-50)] hover:text-[var(--brand-700)]"
                                @mousedown.prevent="selectUnitSuggestion(unit)"
                            >
                                <span class="font-semibold">{{ unit }}</span>
                                <span class="text-[10px] font-bold uppercase tracking-wide text-gray-400">Suggested</span>
                            </button>
                        </div>
                    </div>

                    <p v-if="pricingUnits.length" class="mt-1.5 text-[10px] font-medium text-gray-500">
                        Suggested by schema vector: {{ pricingUnits.join(', ') }}
                    </p>
                    <p v-if="commercialError('unit_name')" class="mt-1.5 text-xs font-medium text-amber-700">{{ commercialError('unit_name') }}</p>
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1.5 block text-[10px] font-bold uppercase text-gray-500">Base Cost (Unit)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-bold text-gray-500">RM</span>
                        <input
                            v-model.number="item.base_cost"
                            type="number"
                            step="0.01"
                            class="w-full rounded-lg py-2.5 pl-9 pr-3 text-sm font-bold"
                            :class="inputClasses(commercialError('base_cost'))"
                            placeholder="0.00"
                        >
                    </div>
                    <p v-if="commercialError('base_cost')" class="mt-1.5 text-xs font-medium text-amber-700">{{ commercialError('base_cost') }}</p>
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1.5 block text-[10px] font-bold uppercase text-gray-500">Supplier Cost (Unit)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-bold text-gray-500">RM</span>
                        <input
                            v-model.number="item.supplier_cost"
                            type="number"
                            step="0.01"
                            class="w-full rounded-lg py-2.5 pl-9 pr-3 text-sm font-bold"
                            :class="inputClasses(commercialError('supplier_cost'))"
                            placeholder="0.00"
                        >
                    </div>
                    <p v-if="commercialError('supplier_cost')" class="mt-1.5 text-xs font-medium text-amber-700">{{ commercialError('supplier_cost') }}</p>
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1.5 block text-[10px] font-bold uppercase text-gray-500">Discount (Unit)</label>
                    <div class="relative flex">
                        <select
                            v-model="item.discount_type"
                            class="w-20 rounded-l-lg border border-gray-300 bg-gray-100 text-xs font-bold focus:z-10 focus:border-[var(--brand-500)]"
                        >
                            <option value="RM">RM</option>
                            <option value="%">%</option>
                        </select>
                        <input
                            v-model.number="item.discount_value"
                            type="number"
                            step="0.01"
                            class="w-full rounded-r-lg border border-l-0 px-3 py-2.5 text-sm"
                            :class="commercialError('discount_value') ? 'border-amber-300 bg-amber-50/70' : 'border-gray-300 bg-white focus:border-[var(--brand-500)]'"
                            placeholder="0.00"
                        >
                    </div>
                    <div class="ml-1 mt-1.5 text-[10px] font-medium text-gray-500">
                        Est: RM {{ formatNumber(calculatedDiscount) }}
                    </div>
                    <p v-if="commercialError('discount_value')" class="mt-1.5 text-xs font-medium text-amber-700">{{ commercialError('discount_value') }}</p>
                </div>

                <div class="md:col-span-3">
                    <label class="mb-1.5 block text-[10px] font-bold uppercase text-gray-500">Tax (Unit)</label>
                    <div class="relative flex">
                        <select
                            v-model="item.tax_type"
                            class="w-20 rounded-l-lg border border-gray-300 bg-gray-100 text-xs font-bold focus:z-10 focus:border-[var(--brand-500)]"
                        >
                            <option value="RM">RM</option>
                            <option value="%">%</option>
                        </select>
                        <input
                            v-model.number="item.tax_value"
                            type="number"
                            step="0.01"
                            class="w-full rounded-r-lg border border-l-0 px-3 py-2.5 text-sm"
                            :class="commercialError('tax_value') ? 'border-amber-300 bg-amber-50/70' : 'border-gray-300 bg-white focus:border-[var(--brand-500)]'"
                            placeholder="0.00"
                        >
                    </div>
                    <div class="ml-1 mt-1.5 text-[10px] font-medium text-gray-500">
                        Est: RM {{ formatNumber(calculatedTax) }}
                    </div>
                    <p v-if="commercialError('tax_value')" class="mt-1.5 text-xs font-medium text-amber-700">{{ commercialError('tax_value') }}</p>
                </div>

                <div class="md:col-span-3">
                    <label class="mb-1.5 block text-[10px] font-black uppercase text-[var(--brand-600)]">Sell Price (Unit)</label>
                    <div class="relative shadow-sm">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-black text-[var(--brand-700)]">RM</span>
                        <input
                            v-model.number="item.sell_price"
                            type="number"
                            step="0.01"
                            class="w-full rounded-lg py-2.5 pl-10 text-sm font-black text-[var(--brand-800)]"
                            :class="commercialError('sell_price') ? 'border border-amber-300 bg-amber-50/70' : 'border border-[var(--brand-300)] bg-blue-50 focus:border-[var(--brand-500)] focus:ring-[var(--brand-500)]'"
                            placeholder="0.00"
                        >
                    </div>
                    <p v-if="commercialError('sell_price')" class="mt-1.5 text-xs font-medium text-amber-700">{{ commercialError('sell_price') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 rounded-xl bg-gradient-to-r from-gray-900 via-gray-800 to-gray-900 p-4 text-white md:grid-cols-5">
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-wide text-gray-400">Base Cost</div>
                    <div class="mt-1 text-lg font-black">RM {{ formatNumber(totalBaseCost) }}</div>
                </div>

                <div>
                    <div class="text-[10px] font-bold uppercase tracking-wide text-gray-400">Discount</div>
                    <div class="mt-1 text-lg font-black text-rose-300">RM {{ formatNumber(totalDiscount) }}</div>
                </div>

                <div>
                    <div class="text-[10px] font-bold uppercase tracking-wide text-gray-400">Tax</div>
                    <div class="mt-1 text-lg font-black text-amber-300">RM {{ formatNumber(totalTax) }}</div>
                </div>

                <div>
                    <div class="text-[10px] font-bold uppercase tracking-wide text-gray-400">Margin</div>
                    <div class="mt-1 text-lg font-black" :class="totalMargin < 0 ? 'text-rose-300' : 'text-emerald-300'">RM {{ formatNumber(totalMargin) }}</div>
                </div>

                <div>
                    <div class="text-[10px] font-bold uppercase tracking-wide text-gray-400">Line Total</div>
                    <div class="mt-1 text-xl font-black text-white">RM {{ formatNumber(lineTotal) }}</div>
                </div>
            </div>

            <div class="mt-5 flex justify-end gap-3 border-t border-gray-100 pt-4">
                <button
                    type="button"
                    class="rounded-lg border border-gray-200 px-4 py-2 text-xs font-bold text-gray-600 transition hover:bg-gray-50"
                    @click.prevent="saveAndMinimize"
                >
                    Save & Minimize
                </button>
            </div>
        </div>

        <div v-else class="flex items-center justify-between gap-4 p-5">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-sm font-bold text-gray-900">{{ schemaName }}</span>
                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-gray-600">{{ item.service_code }}</span>
                    <span
                        class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide"
                        :class="hasErrors ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-700'"
                    >
                        {{ hasErrors ? `${validationCount} left` : 'Ready' }}
                    </span>
                </div>
                <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-gray-500">
                    <span>{{ filledFieldCount }}/{{ fields.length }} schema fields</span>
                    <span>•</span>
                    <span>{{ item.qty }} x {{ item.unit_name || 'unit' }}</span>
                    <span>•</span>
                    <span>RM {{ formatNumber(lineTotal) }}</span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    class="rounded-lg border border-gray-200 px-3 py-1.5 text-[10px] font-bold text-[var(--brand-600)] transition hover:bg-[var(--brand-50)]"
                    @click.prevent="isEditing = true"
                >
                    Edit
                </button>
                <button
                    type="button"
                    class="rounded-lg p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-500"
                    @click.prevent="$emit('remove')"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import {
    fieldIsRequired,
    getPricingUnits,
    humanize,
    normalizeSchemaFields,
} from '../serviceRecordSchema';

const props = defineProps({
    item: {
        type: Object,
        required: true,
    },
    schema: {
        type: Object,
        default: null,
    },
    rowErrors: {
        type: Object,
        default: () => ({}),
    },
    submitAttempted: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['remove', 'validation-failed']);

const isEditing = ref(true);
const unitSuggestionsOpen = ref(false);

const schemaName = computed(() => props.schema?.service_name || props.schema?.display_name || humanize(props.item.service_code));
const fields = computed(() => normalizeSchemaFields(props.schema));
const pricingUnits = computed(() => getPricingUnits(props.schema));
const filteredUnitSuggestions = computed(() => {
    const query = String(props.item.unit_name || '').trim().toLowerCase();

    return pricingUnits.value.filter((unit) => {
        if (!query) {
            return true;
        }

        return unit.toLowerCase().includes(query);
    });
});
const hasErrors = computed(() => Object.keys(props.rowErrors || {}).length > 0);
const validationCount = computed(() => Object.keys(props.rowErrors || {}).length);
const errorList = computed(() => Object.values(props.rowErrors || {}));
const resolutionError = computed(() => props.rowErrors?.schema_vector_id || props.rowErrors?.service_code || '');
const filteredErrorList = computed(() => errorList.value.filter((error) => error !== resolutionError.value));
const filledFieldCount = computed(() => {
    return fields.value.filter((field) => {
        const value = props.item.service_details?.[field.key];

        if (Array.isArray(value)) {
            return value.some((entry) => String(entry ?? '').trim() !== '');
        }

        if (typeof value === 'boolean') {
            return value;
        }

        return String(value ?? '').trim() !== '';
    }).length;
});

const calculatedDiscount = computed(() => {
    const sellPrice = Number(props.item.sell_price || 0);
    const discountValue = Number(props.item.discount_value || 0);

    return props.item.discount_type === '%'
        ? sellPrice * (discountValue / 100)
        : discountValue;
});

const calculatedTax = computed(() => {
    const taxableBase = Math.max(Number(props.item.sell_price || 0) - calculatedDiscount.value, 0);
    const taxValue = Number(props.item.tax_value || 0);

    return props.item.tax_type === '%'
        ? taxableBase * (taxValue / 100)
        : taxValue;
});

const totalBaseCost = computed(() => Number(props.item.base_cost || 0) * Number(props.item.qty || 0));
const totalDiscount = computed(() => calculatedDiscount.value * Number(props.item.qty || 0));
const totalTax = computed(() => calculatedTax.value * Number(props.item.qty || 0));
const totalMargin = computed(() => {
    const perUnitMargin = Math.max(Number(props.item.sell_price || 0) - calculatedDiscount.value, 0) - Number(props.item.base_cost || 0);
    return perUnitMargin * Number(props.item.qty || 0);
});
const lineTotal = computed(() => (Math.max(Number(props.item.sell_price || 0) - calculatedDiscount.value, 0) + calculatedTax.value) * Number(props.item.qty || 0));

const cardClasses = computed(() => {
    if (hasErrors.value) {
        return 'border-amber-300 bg-amber-50/30 ring-2 ring-amber-100';
    }

    return isEditing.value
        ? 'border-[var(--brand-200)] bg-white ring-4 ring-blue-50/50'
        : 'border-gray-200 bg-gray-50/50 hover:border-gray-300 hover:shadow-md';
});

watch(
    () => props.submitAttempted,
    (attempted) => {
        if (attempted && hasErrors.value) {
            isEditing.value = true;
        }
    }
);

watch(
    () => props.rowErrors,
    (errors) => {
        if (Object.keys(errors || {}).length > 0) {
            isEditing.value = true;
        }
    },
    { deep: true }
);

function fieldError(fieldKey) {
    return props.rowErrors?.[`service_details.${fieldKey}`] || '';
}

function commercialError(fieldKey) {
    return props.rowErrors?.[fieldKey] || '';
}

function saveAndMinimize() {
    if (hasErrors.value) {
        emit('validation-failed', {
            schemaName: schemaName.value,
            count: validationCount.value,
        });
    }

    isEditing.value = false;
}

function selectUnitSuggestion(unit) {
    props.item.unit_name = unit;
    unitSuggestionsOpen.value = false;
}

function closeUnitSuggestions() {
    window.setTimeout(() => {
        unitSuggestionsOpen.value = false;
    }, 120);
}

function inputClasses(hasErrorMessage) {
    return hasErrorMessage
        ? 'border-amber-300 bg-amber-50/70 focus:border-amber-400 focus:ring-amber-200'
        : 'border-gray-200 bg-gray-50 focus:border-[var(--brand-500)] focus:ring-[var(--brand-500)]';
}

function getHtmlInputType(type, uiComponent) {
    if (uiComponent === 'date' || type === 'date') {
        return 'date';
    }

    if (uiComponent === 'datetime-local' || type === 'datetime') {
        return 'datetime-local';
    }

    if (uiComponent === 'time' || type === 'time') {
        return 'time';
    }

    if (uiComponent === 'email' || type === 'email') {
        return 'email';
    }

    if (uiComponent === 'number' || ['number', 'integer', 'decimal', 'float'].includes(type)) {
        return 'number';
    }

    if (uiComponent === 'tel' || type === 'tel' || type === 'phone') {
        return 'tel';
    }

    return 'text';
}

function onFileChange(event, key) {
    props.item.service_details[key] = event.target.files?.[0] || null;
}

function formatNumber(value) {
    return Number(value || 0).toFixed(2);
}

function normalizeOptionValue(option) {
    return typeof option === 'object'
        ? (option.value ?? option.label ?? '')
        : option;
}

function normalizeOptionLabel(option) {
    return typeof option === 'object'
        ? (option.label ?? option.value ?? '')
        : option;
}
</script>
