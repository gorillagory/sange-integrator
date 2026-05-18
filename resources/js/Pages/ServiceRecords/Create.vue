<template>
    <TenantLayout>
        <template #breadcrumbs>
            <Breadcrumbs
                :items="[
                    { label: 'Service Records', url: '/service-records' },
                    { label: 'Create Service Record', url: null },
                ]"
            />
        </template>

        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Create Service Record</h1>
            <p class="mt-1 text-sm text-gray-500">
                Capture routing, schema-vector payloads, and row-level finance in one service record.
            </p>
        </div>

        <form
            class="grid grid-cols-1 items-start gap-8 pb-20 xl:grid-cols-12"
            @submit.prevent="submitServiceRecord"
        >
            <div class="space-y-6 xl:col-span-8">
                <CorporateRouting
                    :clients="clients"
                    v-model:clientId="form.client_id"
                    v-model:contractNo="form.contract_no"
                />

                <div
                    class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm"
                    :class="{ 'pointer-events-none opacity-50': !form.contract_no }"
                >
                    <div class="mb-6 flex items-center gap-2">
                        <div class="flex h-6 w-6 items-center justify-center rounded-full bg-[var(--brand-100)] text-xs font-bold text-[var(--brand-700)]">
                            2
                        </div>
                        <h3 class="font-bold text-gray-900">Schema Vector Rows & Commercial Values</h3>
                    </div>

                    <div class="mb-6">
                        <label class="mb-2 block text-xs font-bold uppercase text-gray-600">
                            Select Schema Vector
                        </label>

                        <div class="flex gap-2">
                            <select
                                v-model="selectedSchemaVectorId"
                                class="flex-1 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 transition-colors focus:border-[var(--brand-500)] focus:bg-white"
                            >
                                <option :value="null" disabled>
                                    Choose a vector to add...
                                </option>
                                <option
                                    v-for="schemaVector in vectors"
                                    :key="schemaVector.id"
                                    :value="schemaVector.id"
                                >
                                    {{ schemaVector.service_name || schemaVector.display_name }} · v{{ schemaVector.version || 1 }}
                                </option>
                            </select>

                            <button
                                type="button"
                                class="flex shrink-0 items-center gap-2 rounded-xl bg-[var(--brand-600)] px-6 py-3 text-sm font-bold text-white shadow-lg shadow-brand-500/20 transition hover:bg-[var(--brand-500)] disabled:opacity-50 disabled:shadow-none"
                                :disabled="!selectedSchemaVectorId"
                                @click.prevent="addRow"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Add Row
                            </button>
                        </div>

                        <p v-if="form.errors.rows" class="mt-2 text-sm text-red-600">
                            {{ form.errors.rows }}
                        </p>
                    </div>

                    <div v-if="form.rows.length" class="space-y-4">
                        <ServiceRow
                            v-for="(item, index) in form.rows"
                            :key="item.__uuid"
                            :item="item"
                            :schema="findSchemaForRow(item)"
                            @remove="removeRow(index)"
                        />
                    </div>

                    <div
                        v-else
                        class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 px-6 py-10 text-center text-sm text-gray-500"
                    >
                        Add at least one schema vector row to begin building the service record.
                    </div>
                </div>
            </div>

            <div class="sticky top-8 z-10 xl:col-span-4">
                <CartSummary
                    :totals="totals"
                    :item-count="form.rows.length"
                    :is-ready="isReadyToSubmit"
                    :processing="form.processing"
                    @submit="submitServiceRecord"
                />
            </div>
        </form>
    </TenantLayout>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import TenantLayout from '../../Layouts/TenantLayout.vue';
import Breadcrumbs from '../../Components/UI/Breadcrumbs.vue';
import CorporateRouting from './Components/CorporateRouting.vue';
import ServiceRow from './Components/ServiceRow.vue';
import CartSummary from './Components/CartSummary.vue';

const props = defineProps({
    schemaVectors: {
        type: Array,
        default: () => [],
    },
    schemas: {
        type: Array,
        default: () => [],
    },
    clients: {
        type: Array,
        default: () => [],
    },
    serviceGroup: {
        type: Object,
        default: () => ({}),
    },
    handler: {
        type: Object,
        default: () => ({}),
    },
});

const vectors = computed(() => props.schemaVectors.length ? props.schemaVectors : props.schemas);
const selectedSchemaVectorId = ref(null);

const form = useForm({
    service_group_key: props.serviceGroup?.service_group_key || props.serviceGroup?.handler_key || props.handler?.handler_key || null,
    client_id: null,
    contract_no: null,
    rows: [],
});

const isReadyToSubmit = computed(() => {
    return !!form.client_id
        && !!form.contract_no
        && form.rows.length > 0
        && !form.processing;
});

watch(
    () => form.client_id,
    () => {
        form.contract_no = null;
        form.rows = [];
        selectedSchemaVectorId.value = null;
    },
);

function normalizeFields(payload) {
    const fieldsArray = Array.isArray(payload)
        ? payload
        : Array.isArray(payload?.fields)
            ? payload.fields
            : [];

    return fieldsArray.map((field, index) => {
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
}

function createServiceDetails(schemaVector) {
    const payload = typeof schemaVector?.schema_payload === 'string'
        ? safelyParseJson(schemaVector.schema_payload)
        : (schemaVector?.schema_payload || {});

    const details = {};

    normalizeFields(payload).forEach((field) => {
        details[field.key] = field.is_array ? [''] : defaultValueForField(field);
    });

    return details;
}

function defaultValueForField(field) {
    if (field.ui_component === 'file') {
        return null;
    }

    if (field.type === 'number' || field.type === 'integer') {
        return '';
    }

    if (field.type === 'boolean') {
        return false;
    }

    return '';
}

function safelyParseJson(value) {
    try {
        return JSON.parse(value);
    } catch {
        return {};
    }
}

function addRow() {
    const schemaVector = vectors.value.find((item) => item.id === selectedSchemaVectorId.value);

    if (!schemaVector) {
        return;
    }

    form.rows.push({
        __uuid: `${schemaVector.service_code}_${Date.now()}_${Math.random().toString(36).slice(2, 8)}`,
        schema_vector_id: schemaVector.id,
        service_code: schemaVector.service_code,
        service_details: createServiceDetails(schemaVector),
        service_details_extra: {},
        unit_name: resolveUnitName(schemaVector),
        qty: 1,
        base_cost: 0,
        supplier_cost: null,
        discount_type: 'RM',
        discount_value: 0,
        tax_type: 'RM',
        tax_value: 0,
        sell_price: 0,
    });

    selectedSchemaVectorId.value = null;
}

function removeRow(index) {
    form.rows.splice(index, 1);
}

function findSchemaForRow(row) {
    if (!row) {
        return null;
    }

    if (row.schema_vector_id) {
        const foundById = vectors.value.find((schemaVector) => schemaVector.id === row.schema_vector_id);
        if (foundById) {
            return foundById;
        }
    }

    if (row.service_code) {
        const foundByCode = vectors.value.find((schemaVector) => schemaVector.service_code === row.service_code);

        if (foundByCode) {
            return foundByCode;
        }
    }

    return null;
}

const totals = computed(() => {
    let base = 0;
    let discount = 0;
    let tax = 0;
    let markup = 0;
    let grand = 0;

    form.rows.forEach((item) => {
        const qty = Number(item.qty || 0);
        const baseCost = Number(item.base_cost || 0);
        const taxValue = Number(item.tax_value || 0);
        const discountValue = Number(item.discount_value || 0);
        const sellPrice = Number(item.sell_price || 0);

        const perUnitDiscount = item.discount_type === '%'
            ? sellPrice * (discountValue / 100)
            : discountValue;

        const taxableUnit = Math.max(sellPrice - perUnitDiscount, 0);

        const perUnitTax = item.tax_type === '%'
            ? taxableUnit * (taxValue / 100)
            : taxValue;

        const perUnitMarkup = taxableUnit - baseCost;

        base += baseCost * qty;
        discount += perUnitDiscount * qty;
        tax += perUnitTax * qty;
        markup += perUnitMarkup * qty;
        grand += (taxableUnit + perUnitTax) * qty;
    });

    return { base, discount, tax, markup, grand };
});

function submitServiceRecord() {
    form.transform((data) => ({
        service_group_key: data.service_group_key,
        client_id: data.client_id,
        contract_no: data.contract_no,
        rows: data.rows.map((row) => ({
            schema_vector_id: row.schema_vector_id ?? null,
            service_code: row.service_code,
            service_details: sanitizeServiceDetails(row.service_details),
            service_details_extra: row.service_details_extra || {},
            qty: Number(row.qty || 1),
            unit_name: row.unit_name || null,
            base_cost: Number(row.base_cost || 0),
            supplier_cost: row.supplier_cost === null || row.supplier_cost === '' ? null : Number(row.supplier_cost),
            discount_type: row.discount_type || 'RM',
            discount_value: Number(row.discount_value || 0),
            tax_type: row.tax_type || 'RM',
            tax_value: Number(row.tax_value || 0),
            sell_price: Number(row.sell_price || 0),
        })),
    })).post('/service-records');
}

function sanitizeServiceDetails(details) {
    const normalized = {};

    Object.entries(details || {}).forEach(([key, value]) => {
        if (Array.isArray(value)) {
            normalized[key] = value.filter((item) => item !== null && item !== undefined && String(item).trim() !== '');
            return;
        }

        normalized[key] = value;
    });

    return normalized;
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

function resolveUnitName(schemaVector) {
    const payload = typeof schemaVector?.schema_payload === 'string'
        ? safelyParseJson(schemaVector.schema_payload)
        : (schemaVector?.schema_payload || {});

    return payload?.commercial?.unit || payload?.unit || '';
}
</script>
