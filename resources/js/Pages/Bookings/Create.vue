<!-- resources/js/Pages/Bookings/Create.vue -->
<template>
    <TenantLayout>
        <template #breadcrumbs>
            <Breadcrumbs
                :items="[
                    { label: 'Operations', url: null },
                    { label: 'Master Bookings', url: '/bookings' },
                    { label: 'Initiate Booking', url: null },
                ]"
            />
        </template>

        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Initiate Master Booking</h1>
            <p class="mt-1 text-sm text-gray-500">
                Define corporate routing, compile dynamic services, and construct pricing.
            </p>
        </div>

        <form
            class="grid grid-cols-1 items-start gap-8 pb-20 xl:grid-cols-12"
            @submit.prevent="submitMasterBooking"
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
                        <h3 class="font-bold text-gray-900">Operational Payload & Margins</h3>
                    </div>

                    <div class="mb-6">
                        <label class="mb-2 block text-xs font-bold uppercase text-gray-600">
                            Select Service Module
                        </label>

                        <div class="flex gap-2">
                            <select
                                v-model="selectedSchemaId"
                                class="flex-1 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 transition-colors focus:border-[var(--brand-500)] focus:bg-white"
                            >
                                <option :value="null" disabled>
                                    Choose a service to add to cart...
                                </option>
                                <option
                                    v-for="schema in schemas"
                                    :key="schema.id"
                                    :value="schema.id"
                                >
                                    {{ schema.display_name }}
                                </option>
                            </select>

                            <button
                                type="button"
                                class="flex shrink-0 items-center gap-2 rounded-xl bg-[var(--brand-600)] px-6 py-3 text-sm font-bold text-white shadow-lg shadow-brand-500/20 transition hover:bg-[var(--brand-500)] disabled:opacity-50 disabled:shadow-none"
                                :disabled="!selectedSchemaId"
                                @click.prevent="addService"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Add Service
                            </button>
                        </div>

                        <p
                            v-if="form.errors.services"
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ form.errors.services }}
                        </p>
                    </div>

                    <div
                        v-if="form.services.length"
                        class="space-y-4"
                    >
                        <ServiceRow
                            v-for="(item, index) in form.services"
                            :key="item.__uuid"
                            :item="item"
                            :schema="findSchemaByServiceType(item.service_type)"
                            @remove="removeService(index)"
                        />
                    </div>

                    <div
                        v-else
                        class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 px-6 py-10 text-center text-sm text-gray-500"
                    >
                        Add at least one service module to begin building the booking payload.
                    </div>
                </div>
            </div>

            <div class="sticky top-8 z-10 xl:col-span-4">
                <CartSummary
                    :totals="totals"
                    :item-count="form.services.length"
                    :is-ready="isReadyToSubmit"
                    :processing="form.processing"
                    @submit="submitMasterBooking"
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
    schemas: {
        type: Array,
        default: () => [],
    },
    clients: {
        type: Array,
        default: () => [],
    },
});

const selectedSchemaId = ref(null);

const form = useForm({
    client_id: null,
    contract_no: null,
    services: [],
    passengers: [],
    passenger_details: [],
});

const isReadyToSubmit = computed(() => {
    return !!form.client_id
        && !!form.contract_no
        && form.services.length > 0
        && !form.processing;
});

watch(
    () => form.client_id,
    () => {
        form.contract_no = null;
        form.services = [];
        form.passengers = [];
        form.passenger_details = [];
        selectedSchemaId.value = null;
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

function createServiceDetails(schema) {
    const payload = typeof schema?.schema_payload === 'string'
        ? safelyParseJson(schema.schema_payload)
        : (schema?.schema_payload || {});

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

function addService() {
    const schema = props.schemas.find((item) => item.id === selectedSchemaId.value);

    if (!schema) {
        return;
    }

    form.services.push({
        __uuid: `${schema.service_type}_${Date.now()}_${Math.random().toString(36).slice(2, 8)}`,
        service_type: schema.service_type,
        service_details: createServiceDetails(schema),
        qty: 1,
        unit_fare: 0,
        tax_type: 'RM',
        tax_value: 0,
        client_price: 0,
    });

    selectedSchemaId.value = null;
}

function removeService(index) {
    form.services.splice(index, 1);
}

function findSchemaByServiceType(serviceType) {
    return props.schemas.find((schema) => schema.service_type === serviceType) || null;
}

const totals = computed(() => {
    let base = 0;
    let tax = 0;
    let markup = 0;
    let grand = 0;

    form.services.forEach((item) => {
        const qty = Number(item.qty || 0);
        const unitFare = Number(item.unit_fare || 0);
        const taxValue = Number(item.tax_value || 0);
        const clientPrice = Number(item.client_price || 0);

        const perUnitTax = item.tax_type === '%'
            ? unitFare * (taxValue / 100)
            : taxValue;

        const perUnitMarkup = clientPrice - unitFare - perUnitTax;

        base += unitFare * qty;
        tax += perUnitTax * qty;
        markup += perUnitMarkup * qty;
        grand += clientPrice * qty;
    });

    return { base, tax, markup, grand };
});

function submitMasterBooking() {
    form.transform((data) => ({
        client_id: data.client_id,
        contract_no: data.contract_no,
        services: data.services.map((service) => ({
            service_type: service.service_type,
            service_details: sanitizeServiceDetails(service.service_details),
            qty: Number(service.qty || 1),
            unit_fare: Number(service.unit_fare || 0),
            tax_type: service.tax_type || 'RM',
            tax_value: Number(service.tax_value || 0),
            client_price: Number(service.client_price || 0),
        })),
        passengers: data.passengers || [],
        passenger_details: data.passenger_details || [],
    })).post('/bookings');
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
</script>
