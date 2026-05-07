<!-- resources/js/Pages/Bookings/Components/ServiceRow.vue -->
<template>
    <div
        class="rounded-2xl border shadow-sm transition-all duration-300"
        :class="isEditing ? 'border-[var(--brand-200)] bg-white ring-4 ring-blue-50/50' : 'border-gray-200 bg-gray-50/50 hover:border-gray-300 hover:shadow-md'"
    >
        <div
            v-if="isEditing"
            class="relative p-6"
        >
            <button
                type="button"
                class="absolute right-6 top-6 text-gray-400 transition hover:text-red-500"
                title="Remove Service"
                @click.prevent="$emit('remove')"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </button>

            <h4 class="mb-6 mr-8 flex items-center gap-2 border-b border-gray-100 pb-3 text-sm font-bold uppercase text-[var(--brand-700)]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                {{ schemaName }}
            </h4>

            <div class="mb-8 grid grid-cols-1 gap-5 md:grid-cols-2">
                <div
                    v-for="field in fields"
                    :key="field.key"
                    :class="field.grid_span === 2 || field.ui_component === 'textarea' ? 'md:col-span-2' : 'md:col-span-1'"
                >
                    <label class="mb-1.5 flex items-center justify-between text-[10px] font-bold uppercase text-gray-500">
                        <span>
                            {{ field.label }}
                            <span
                                v-if="field.rules.includes('required')"
                                class="text-red-500"
                            >
                                *
                            </span>
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
                            :required="field.rules.includes('required')"
                            class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm transition-colors focus:border-[var(--brand-500)] focus:bg-white focus:ring-[var(--brand-500)]"
                            :style="{ textTransform: field.text_transform === 'none' ? 'none' : field.text_transform }"
                            :placeholder="field.placeholder || 'Enter details...'"
                        />

                        <select
                            v-else-if="field.ui_component === 'select' && field.options.length"
                            v-model="item.service_details[field.key]"
                            :required="field.rules.includes('required')"
                            class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm transition-colors focus:border-[var(--brand-500)] focus:bg-white focus:ring-[var(--brand-500)]"
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
                            :required="field.rules.includes('required')"
                            class="block w-full cursor-pointer rounded-lg border border-gray-200 bg-gray-50 text-sm text-gray-500 transition-colors file:mr-4 file:rounded-lg file:border-0 file:bg-blue-50 file:px-4 file:py-2.5 file:text-xs file:font-bold file:text-[var(--brand-700)] hover:file:bg-[var(--brand-100)]"
                            @change="onFileChange($event, field.key)"
                        >

                        <input
                            v-else
                            v-model="item.service_details[field.key]"
                            :type="getHtmlInputType(field.type, field.ui_component)"
                            :required="field.rules.includes('required')"
                            class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm transition-colors focus:border-[var(--brand-500)] focus:bg-white focus:ring-[var(--brand-500)]"
                            :style="{ textTransform: field.text_transform === 'none' ? 'none' : field.text_transform }"
                            :placeholder="field.placeholder || `Enter ${field.label}...`"
                        >
                    </template>

                    <template v-else>
                        <div class="space-y-2 rounded-xl border border-gray-200 bg-gray-50 p-3">
                            <div
                                v-for="(line, lineIndex) in item.service_details[field.key]"
                                :key="lineIndex"
                                class="relative flex gap-2"
                            >
                                <textarea
                                    v-if="field.ui_component === 'textarea'"
                                    v-model="item.service_details[field.key][lineIndex]"
                                    rows="2"
                                    :required="field.rules.includes('required') && lineIndex === 0"
                                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-[var(--brand-500)] focus:ring-[var(--brand-500)]"
                                    :style="{ textTransform: field.text_transform === 'none' ? 'none' : field.text_transform }"
                                    :placeholder="`${field.placeholder || field.label} ${lineIndex + 1}`"
                                />

                                <input
                                    v-else
                                    v-model="item.service_details[field.key][lineIndex]"
                                    :type="getHtmlInputType(field.type, field.ui_component)"
                                    :required="field.rules.includes('required') && lineIndex === 0"
                                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-[var(--brand-500)] focus:ring-[var(--brand-500)]"
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
                </div>
            </div>

            <div class="mb-6 grid grid-cols-1 items-start gap-5 rounded-xl border border-gray-200 bg-gray-50 p-5 md:grid-cols-12">
                <div class="md:col-span-2">
                    <label class="mb-1.5 block text-[10px] font-bold uppercase text-gray-500">
                        Qty
                    </label>
                    <input
                        v-model.number="item.qty"
                        type="number"
                        min="1"
                        class="w-full rounded-lg border border-gray-300 bg-white px-2 py-2.5 text-center text-sm font-bold focus:border-[var(--brand-500)]"
                    >
                </div>

                <div class="md:col-span-3">
                    <label class="mb-1.5 block text-[10px] font-bold uppercase text-gray-500">
                        Supplier Cost (Unit)
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-bold text-gray-500">RM</span>
                        <input
                            v-model.number="item.unit_fare"
                            type="number"
                            step="0.01"
                            class="w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-9 pr-3 text-sm font-bold focus:border-[var(--brand-500)]"
                            placeholder="0.00"
                        >
                    </div>
                </div>

                <div class="md:col-span-3">
                    <label class="mb-1.5 block text-[10px] font-bold uppercase text-gray-500">
                        Tax (Unit)
                    </label>
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
                            class="w-full rounded-r-lg border border-l-0 border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-[var(--brand-500)]"
                            placeholder="0.00"
                        >
                    </div>
                    <div class="ml-1 mt-1.5 text-[10px] font-medium text-gray-500">
                        Est: RM {{ formatNumber(calculatedTax) }}
                    </div>
                </div>

                <div class="md:col-span-4">
                    <label class="mb-1.5 block text-[10px] font-black uppercase text-[var(--brand-600)]">
                        Total Charged to Client (Unit)
                    </label>
                    <div class="relative shadow-sm">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-black text-[var(--brand-700)]">RM</span>
                        <input
                            v-model.number="item.client_price"
                            type="number"
                            step="0.01"
                            class="w-full rounded-lg border border-[var(--brand-300)] bg-blue-50 py-2.5 pl-10 text-sm font-black text-[var(--brand-800)] focus:border-[var(--brand-500)] focus:ring-[var(--brand-500)]"
                            placeholder="0.00"
                        >
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 rounded-xl bg-gradient-to-r from-gray-900 via-gray-800 to-gray-900 p-4 text-white md:grid-cols-4">
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-wide text-gray-400">
                        Base Cost
                    </div>
                    <div class="mt-1 text-lg font-black">
                        RM {{ formatNumber(totalBaseCost) }}
                    </div>
                </div>

                <div>
                    <div class="text-[10px] font-bold uppercase tracking-wide text-gray-400">
                        Tax
                    </div>
                    <div class="mt-1 text-lg font-black text-amber-300">
                        RM {{ formatNumber(totalTax) }}
                    </div>
                </div>

                <div>
                    <div class="text-[10px] font-bold uppercase tracking-wide text-gray-400">
                        Margin
                    </div>
                    <div
                        class="mt-1 text-lg font-black"
                        :class="totalMargin < 0 ? 'text-rose-300' : 'text-emerald-300'"
                    >
                        RM {{ formatNumber(totalMargin) }}
                    </div>
                </div>

                <div>
                    <div class="text-[10px] font-bold uppercase tracking-wide text-gray-400">
                        Line Total
                    </div>
                    <div class="mt-1 text-xl font-black text-white">
                        RM {{ formatNumber(lineTotal) }}
                    </div>
                </div>
            </div>
        </div>

        <div
            v-else
            class="flex items-center justify-between p-5"
        >
            <div>
                <div class="text-xs font-bold uppercase tracking-wide text-gray-500">
                    {{ schemaName }}
                </div>
                <div class="mt-1 text-sm text-gray-600">
                    {{ fields.length }} field{{ fields.length === 1 ? '' : 's' }} configured
                </div>
            </div>

            <div class="text-right">
                <div class="text-xs font-semibold uppercase text-gray-400">
                    Line Total
                </div>
                <div class="mt-1 text-lg font-black text-gray-900">
                    RM {{ formatNumber(lineTotal) }}
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
    item: {
        type: Object,
        required: true,
    },
    schema: {
        type: Object,
        default: null,
    },
});

defineEmits(['remove']);

const isEditing = ref(true);

const schemaName = computed(() => props.schema?.display_name || humanize(props.item.service_type));

const fields = computed(() => {
    const payload = typeof props.schema?.schema_payload === 'string'
        ? safelyParseJson(props.schema.schema_payload)
        : (props.schema?.schema_payload || {});

    const source = Array.isArray(payload)
        ? payload
        : Array.isArray(payload?.fields)
            ? payload.fields
            : [];

    return source.map((field, index) => {
        if (typeof field === 'string') {
            return {
                key: field,
                label: humanize(field),
                type: 'string',
                ui_component: 'input',
                is_array: false,
                rules: [],
                grid_span: 1,
                order: index,
                placeholder: '',
                text_transform: 'none',
                options: [],
            };
        }

        return {
            key: field?.key || `field_${index + 1}`,
            label: field?.label || humanize(field?.key || `Field ${index + 1}`),
            type: field?.type || 'string',
            ui_component: field?.ui_component || field?.component || defaultUiComponentForType(field?.type),
            is_array: Boolean(field?.is_array),
            rules: Array.isArray(field?.rules) ? field.rules : [],
            grid_span: Number(field?.grid_span || 1),
            order: Number(field?.order || index),
            placeholder: field?.placeholder || '',
            text_transform: field?.text_transform || 'none',
            options: Array.isArray(field?.options) ? field.options : [],
        };
    }).sort((a, b) => a.order - b.order);
});

const calculatedTax = computed(() => {
    const base = Number(props.item.unit_fare || 0);
    const taxValue = Number(props.item.tax_value || 0);

    return props.item.tax_type === '%'
        ? base * (taxValue / 100)
        : taxValue;
});

const totalBaseCost = computed(() => {
    return Number(props.item.unit_fare || 0) * Number(props.item.qty || 0);
});

const totalTax = computed(() => {
    return calculatedTax.value * Number(props.item.qty || 0);
});

const totalMargin = computed(() => {
    const perUnitMargin = Number(props.item.client_price || 0)
        - Number(props.item.unit_fare || 0)
        - calculatedTax.value;

    return perUnitMargin * Number(props.item.qty || 0);
});

const lineTotal = computed(() => {
    return Number(props.item.client_price || 0) * Number(props.item.qty || 0);
});

function getHtmlInputType(type, uiComponent) {
    if (uiComponent === 'date' || type === 'date') {
        return 'date';
    }

    if (uiComponent === 'email' || type === 'email') {
        return 'email';
    }

    if (uiComponent === 'number' || type === 'number' || type === 'integer' || type === 'decimal') {
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

function safelyParseJson(value) {
    try {
        return JSON.parse(value);
    } catch {
        return {};
    }
}

function formatNumber(value) {
    return Number(value || 0).toFixed(2);
}

function humanize(value) {
    return String(value || '')
        .replace(/_/g, ' ')
        .replace(/\b\w/g, (char) => char.toUpperCase());
}

function defaultUiComponentForType(type) {
    if (type === 'text') {
        return 'textarea';
    }

    if (type === 'date') {
        return 'date';
    }

    if (type === 'file') {
        return 'file';
    }

    return 'input';
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
