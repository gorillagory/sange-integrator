<template>
    <TenantLayout>
        <template #breadcrumbs>
            <Breadcrumbs
                :items="[
                    { label: 'Service Records', url: '/service-records' },
                    { label: isEditMode ? 'Edit Service Record' : 'Create Service Record', url: null },
                ]"
            />
        </template>

        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">{{ isEditMode ? `Edit ${recordReference}` : 'Create Service Record' }}</h1>
            <p class="mt-1 text-sm text-gray-500">
                {{ isEditMode
                    ? 'Refine the draft payload, commercial values, and client notes before locking the final document.'
                    : 'Capture routing, schema-vector payloads, and row-level finance in one service record.' }}
            </p>
        </div>

        <div
            v-if="showValidationHighlights && validationSummary.items.length"
            class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-900"
        >
            <div class="font-bold">Some required information is still missing.</div>
            <ul class="mt-2 space-y-1">
                <li v-for="item in validationSummary.items" :key="item">• {{ item }}</li>
            </ul>
        </div>

        <form
            class="grid grid-cols-1 items-start gap-8 pb-20 xl:grid-cols-12"
            @submit.prevent="submitServiceRecord"
        >
            <div class="space-y-6 xl:col-span-8">
                <CorporateRouting
                    :clients="clientsState"
                    :client-error="fieldError('client_id')"
                    :contract-error="fieldError('contract_no')"
                    v-model:clientId="form.client_id"
                    v-model:contractNo="form.contract_no"
                />

                <RemarksCard
                    :client="selectedClient"
                    :presets="remarkPresets"
                    :disabled="!form.client_id"
                    :model-value="{ presetId: form.client_remark_preset_id, remarks: form.remarks }"
                    :editor="remarkEditor"
                    :preset-error="fieldError('client_remark_preset_id')"
                    :remarks-error="fieldError('remarks')"
                    @update:preset-id="selectRemarkPreset"
                    @update:remarks="(value) => { form.remarks = value; }"
                    @create-preset="openCreateRemarkPreset"
                    @edit-preset="openEditRemarkPreset"
                    @delete-preset="deleteRemarkPreset"
                    @apply-preset="applyRemarkPreset"
                    @save-preset="saveRemarkPreset"
                    @cancel-editor="closeRemarkEditor"
                    @update-editor="updateRemarkEditor"
                />

                <div
                    class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm"
                    :class="{ 'pointer-events-none opacity-50': !form.contract_no }"
                >
                    <div class="mb-6 flex items-center gap-2">
                        <div class="flex h-6 w-6 items-center justify-center rounded-full bg-[var(--brand-100)] text-xs font-bold text-[var(--brand-700)]">
                            3
                        </div>
                        <h3 class="font-bold text-gray-900">Schema Vector Rows & Commercial Values</h3>
                    </div>

                    <div
                        v-if="fieldError('rows')"
                        class="mb-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900"
                    >
                        {{ fieldError('rows') }}
                    </div>

                    <div class="mb-6">
                        <label class="mb-2 block text-xs font-bold uppercase text-gray-600">
                            Select Schema Vector
                        </label>

                        <div class="flex gap-2">
                            <select
                                v-model="selectedSchemaVectorId"
                                class="flex-1 rounded-xl px-4 py-3 text-sm text-gray-900 transition-colors"
                                :class="fieldError('selectedSchemaVectorId') ? 'border border-amber-300 bg-amber-50/70 focus:border-amber-400' : 'border border-gray-200 bg-gray-50 focus:border-[var(--brand-500)] focus:bg-white'"
                            >
                                <option :value="null" disabled>
                                    Choose a vector to add...
                                </option>
                                <option
                                    v-for="schemaVector in vectors"
                                    :key="schemaVector.id"
                                    :value="schemaVector.id"
                                >
                                    {{ schemaVector.service_name || schemaVector.display_name }} · {{ schemaVector.service_code }} · v{{ schemaVector.version || 1 }}
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

                        <p v-if="fieldError('selectedSchemaVectorId')" class="mt-2 text-sm text-amber-700">
                            {{ fieldError('selectedSchemaVectorId') }}
                        </p>
                    </div>

                    <div v-if="form.rows.length" class="space-y-4">
                        <ServiceRow
                            v-for="(item, index) in form.rows"
                            :key="item.__uuid"
                            :item="item"
                            :schema="findSchemaForRow(item)"
                            :row-errors="mergedRowErrors[index] || {}"
                            :submit-attempted="showValidationHighlights"
                            @remove="removeRow(index)"
                            @validation-failed="handleRowValidationFailed"
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
                    :rows="servicePreviewRows"
                    :item-count="form.rows.length"
                    :is-ready="validationSummary.isValid && !form.processing"
                    :processing="form.processing"
                    :validation-state="validationSummary"
                    @submit="submitServiceRecord"
                />
            </div>
        </form>
    </TenantLayout>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useToast } from '@/Composables/useToast';
import TenantLayout from '../../Layouts/TenantLayout.vue';
import Breadcrumbs from '../../Components/UI/Breadcrumbs.vue';
import CorporateRouting from './Components/CorporateRouting.vue';
import RemarksCard from './Components/RemarksCard.vue';
import ServiceRow from './Components/ServiceRow.vue';
import CartSummary from './Components/CartSummary.vue';
import {
    createServiceDetails,
    fieldIsRequired,
    humanize,
    normalizeSchemaFields,
    resolveDefaultUnitName,
} from './serviceRecordSchema';

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
    serviceRecord: {
        type: Object,
        default: null,
    },
    mode: {
        type: String,
        default: 'create',
    },
});

const { addToast } = useToast();
const isEditMode = computed(() => props.mode === 'edit' && !!props.serviceRecord);
const recordReference = computed(() => props.serviceRecord?.reference_no || 'service record');
const vectors = computed(() => props.schemaVectors.length ? props.schemaVectors : props.schemas);
const selectedSchemaVectorId = ref(null);
const showValidationHighlights = ref(false);
const hasShownServerValidationToast = ref(false);
const clientsState = ref(cloneClients(props.clients));
const remarkEditor = reactive({
    open: false,
    mode: 'create',
    presetId: null,
    title: '',
    content: '',
    errors: {},
});

const form = useForm({
    service_group_key: props.serviceGroup?.service_group_key || props.serviceGroup?.handler_key || props.handler?.handler_key || null,
    client_id: props.serviceRecord?.client_id || null,
    contract_no: props.serviceRecord?.contract_no || null,
    client_remark_preset_id: props.serviceRecord?.client_remark_preset_id || null,
    remarks: props.serviceRecord?.remarks || '',
    rows: props.serviceRecord ? hydrateRows(props.serviceRecord.rows || []) : [],
});

const selectedClient = computed(() => {
    return clientsState.value.find((client) => String(client.id) === String(form.client_id)) || null;
});

const remarkPresets = computed(() => {
    return Array.isArray(selectedClient.value?.remark_presets)
        ? selectedClient.value.remark_presets
        : [];
});

watch(
    () => form.client_id,
    () => {
        form.contract_no = null;
        form.client_remark_preset_id = null;
        form.remarks = '';
        form.rows = [];
        selectedSchemaVectorId.value = null;
        form.clearErrors();
        hasShownServerValidationToast.value = false;
        closeRemarkEditor();
    },
);

watch(
    () => form.errors,
    (errors) => {
        if (!showValidationHighlights.value) {
            return;
        }

        const count = Object.keys(errors || {}).length;

        if (count > 0 && !hasShownServerValidationToast.value) {
            addToast(`We still have ${count} validation item${count === 1 ? '' : 's'} to fix before submission.`, 'error');
            hasShownServerValidationToast.value = true;
        }

        if (count === 0) {
            hasShownServerValidationToast.value = false;
        }
    },
    { deep: true }
);

const mergedRowErrors = computed(() => {
    const clientMap = buildClientRowErrorMap();
    const serverMap = buildServerRowErrorMap();
    const merged = {};
    const totalRows = Math.max(form.rows.length, Object.keys(clientMap).length, Object.keys(serverMap).length);

    for (let index = 0; index < totalRows; index += 1) {
        merged[index] = {
            ...(clientMap[index] || {}),
            ...(serverMap[index] || {}),
        };
    }

    return merged;
});

const globalErrors = computed(() => {
    const errors = {};

    if (!form.client_id) {
        errors.client_id = 'Choose the corporate entity before adding services.';
    }

    if (!form.contract_no) {
        errors.contract_no = 'Select the active contract for this service record.';
    }

    if (!form.rows.length) {
        errors.rows = 'Add at least one schema vector row before creating the service record.';
        errors.selectedSchemaVectorId = 'Pick a schema vector and add it to the record.';
    }

    Object.entries(form.errors || {}).forEach(([key, value]) => {
        if (!key.startsWith('rows.')) {
            errors[key] = value;
        }
    });

    return errors;
});

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

const servicePreviewRows = computed(() => {
    return form.rows.map((row, index) => ({
        id: row.__uuid,
        name: findSchemaForRow(row)?.service_name || findSchemaForRow(row)?.display_name || humanize(row.service_code),
        serviceCode: row.service_code,
        qty: Number(row.qty || 0),
        unitName: row.unit_name,
        lineTotal: lineTotalForRow(row),
        requiredPending: Object.keys(mergedRowErrors.value[index] || {}).length,
    }));
});

const validationSummary = computed(() => {
    const items = [];
    const topLevelErrors = globalErrors.value;

    if (topLevelErrors.client_id) {
        items.push('Choose a corporate entity.');
    }

    if (topLevelErrors.contract_no) {
        items.push('Select an active contract.');
    }

    if (topLevelErrors.rows) {
        items.push('Add at least one schema vector row.');
    }

    form.rows.forEach((row, index) => {
        const rowErrors = mergedRowErrors.value[index] || {};
        const pending = Object.keys(rowErrors).length;

        if (pending > 0) {
            const schema = findSchemaForRow(row);
            items.push(`${schema?.service_name || schema?.display_name || `Row ${index + 1}`} needs ${pending} more fix${pending === 1 ? '' : 'es'}.`);
        }
    });

    return {
        isValid: items.length === 0,
        message: items.length === 0
            ? 'Routing, schema-vector fields, and commercial values are all ready.'
            : 'Finish the remaining required items below so operators can submit cleanly.',
        items,
    };
});

function fieldError(key) {
    if (form.errors?.[key]) {
        return form.errors[key];
    }

    return showValidationHighlights.value ? (globalErrors.value[key] || '') : '';
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

function addRow() {
    const schemaVector = vectors.value.find((item) => item.id === selectedSchemaVectorId.value);

    if (!schemaVector) {
        addToast('Choose a schema vector before adding a row.', 'error');
        return;
    }

    form.rows.push({
        __uuid: `${schemaVector.service_code}_${Date.now()}_${Math.random().toString(36).slice(2, 8)}`,
        schema_vector_id: schemaVector.id,
        service_code: schemaVector.service_code,
        service_details: createServiceDetails(schemaVector),
        service_details_extra: {},
        unit_name: resolveDefaultUnitName(schemaVector),
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
    showValidationHighlights.value = false;
    addToast(`Added ${schemaVector.service_name || schemaVector.display_name} to the record.`, 'success');
}

function removeRow(index) {
    const removed = form.rows[index];
    form.rows.splice(index, 1);
    addToast(`Removed ${findSchemaForRow(removed)?.service_name || 'service row'}.`, 'success');
}

function submitServiceRecord() {
    showValidationHighlights.value = true;

    if (!validationSummary.value.isValid) {
        addToast(`Complete ${validationSummary.value.items.length} remaining item${validationSummary.value.items.length === 1 ? '' : 's'} before submitting.`, 'error');
        return;
    }

    hasShownServerValidationToast.value = false;

    const payload = form.transform((data) => ({
        service_group_key: data.service_group_key,
        client_id: data.client_id,
        contract_no: data.contract_no,
        client_remark_preset_id: data.client_remark_preset_id,
        remarks: data.remarks || null,
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
    }));

    const options = {
        preserveScroll: true,
        onError: () => {
            addToast('The record still has validation issues. Review the highlighted fields.', 'error');
        },
    };

    if (isEditMode.value) {
        payload.put(`/service-records/${props.serviceRecord.id}`, options);
        return;
    }

    payload.post('/service-records', options);
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

function buildClientRowErrorMap() {
    const errors = {};

    form.rows.forEach((row, index) => {
        const rowErrors = {};
        const schema = findSchemaForRow(row);
        const fields = normalizeSchemaFields(schema);

        if (!row.schema_vector_id) {
            rowErrors.schema_vector_id = 'Schema vector is required.';
        }

        if (!row.service_code) {
            rowErrors.service_code = 'Service code is required.';
        }

        fields.forEach((field) => {
            if (!fieldIsRequired(field)) {
                return;
            }

            const value = row.service_details?.[field.key];

            if (field.is_array) {
                const filled = Array.isArray(value)
                    ? value.filter((entry) => String(entry ?? '').trim() !== '')
                    : [];

                if (filled.length === 0) {
                    rowErrors[`service_details.${field.key}`] = `${field.label} is required.`;
                }

                return;
            }

            if (field.ui_component === 'file') {
                if (!value) {
                    rowErrors[`service_details.${field.key}`] = `${field.label} is required.`;
                }

                return;
            }

            if (typeof value === 'boolean') {
                if (!value) {
                    rowErrors[`service_details.${field.key}`] = `${field.label} is required.`;
                }

                return;
            }

            if (String(value ?? '').trim() === '') {
                rowErrors[`service_details.${field.key}`] = `${field.label} is required.`;
            }
        });

        if (!row.unit_name || String(row.unit_name).trim() === '') {
            rowErrors.unit_name = 'Unit is required so the service line stays readable.';
        }

        if (!Number.isInteger(Number(row.qty)) || Number(row.qty) < 1) {
            rowErrors.qty = 'Qty must be at least 1.';
        }

        ['base_cost', 'discount_value', 'tax_value', 'sell_price'].forEach((fieldKey) => {
            if (Number(row[fieldKey] ?? 0) < 0) {
                rowErrors[fieldKey] = `${humanize(fieldKey)} cannot be negative.`;
            }
        });

        if (row.supplier_cost !== null && row.supplier_cost !== '' && Number(row.supplier_cost) < 0) {
            rowErrors.supplier_cost = 'Supplier cost cannot be negative.';
        }

        if (Object.keys(rowErrors).length > 0) {
            errors[index] = rowErrors;
        }
    });

    return errors;
}

function buildServerRowErrorMap() {
    const map = {};

    Object.entries(form.errors || {}).forEach(([key, message]) => {
        if (!key.startsWith('rows.')) {
            return;
        }

        const parts = key.split('.');
        const index = Number(parts[1]);
        const fieldPath = parts.slice(2).join('.');

        if (!Number.isInteger(index)) {
            return;
        }

        if (!map[index]) {
            map[index] = {};
        }

        map[index][fieldPath || '_row'] = message;
    });

    return map;
}

function lineTotalForRow(row) {
    const qty = Number(row.qty || 0);
    const sellPrice = Number(row.sell_price || 0);
    const discountValue = Number(row.discount_value || 0);
    const taxValue = Number(row.tax_value || 0);

    const discount = row.discount_type === '%'
        ? sellPrice * (discountValue / 100)
        : discountValue;
    const taxableUnit = Math.max(sellPrice - discount, 0);
    const tax = row.tax_type === '%'
        ? taxableUnit * (taxValue / 100)
        : taxValue;

    return (taxableUnit + tax) * qty;
}

function handleRowValidationFailed(payload) {
    showValidationHighlights.value = true;
    addToast(`${payload.schemaName} still has ${payload.count} validation item${payload.count === 1 ? '' : 's'} to fix.`, 'error');
}

function selectRemarkPreset(presetId) {
    form.client_remark_preset_id = presetId;

    const preset = remarkPresets.value.find((item) => String(item.id) === String(presetId));

    if (preset) {
        form.remarks = preset.content;
    }
}

function applyRemarkPreset(preset) {
    form.client_remark_preset_id = preset.id;
    form.remarks = preset.content;
    addToast(`Applied "${preset.title}" to this service record.`, 'success');
}

function openCreateRemarkPreset() {
    if (!selectedClient.value) {
        addToast('Choose a client before creating a remark preset.', 'error');
        return;
    }

    Object.assign(remarkEditor, {
        open: true,
        mode: 'create',
        presetId: null,
        title: '',
        content: form.remarks || '',
        errors: {},
    });
}

function openEditRemarkPreset(preset) {
    if (!preset) {
        return;
    }

    Object.assign(remarkEditor, {
        open: true,
        mode: 'edit',
        presetId: preset.id,
        title: preset.title,
        content: preset.content,
        errors: {},
    });
}

function closeRemarkEditor() {
    Object.assign(remarkEditor, {
        open: false,
        mode: 'create',
        presetId: null,
        title: '',
        content: '',
        errors: {},
    });
}

function updateRemarkEditor(payload) {
    Object.assign(remarkEditor, payload);

    if (payload.title !== undefined && remarkEditor.errors.title) {
        delete remarkEditor.errors.title;
    }

    if (payload.content !== undefined && remarkEditor.errors.content) {
        delete remarkEditor.errors.content;
    }
}

async function saveRemarkPreset() {
    if (!selectedClient.value) {
        return;
    }

    remarkEditor.errors = {};

    if (!String(remarkEditor.title || '').trim()) {
        remarkEditor.errors.title = 'Preset title is required.';
    }

    if (!String(remarkEditor.content || '').trim()) {
        remarkEditor.errors.content = 'Preset content is required.';
    }

    if (Object.keys(remarkEditor.errors).length > 0) {
        addToast('Complete the preset title and content before saving.', 'error');
        return;
    }

    try {
        const endpoint = remarkEditor.mode === 'create'
            ? `/clients/${selectedClient.value.id}/remark-presets`
            : `/clients/${selectedClient.value.id}/remark-presets/${remarkEditor.presetId}`;
        const method = remarkEditor.mode === 'create' ? 'post' : 'put';
        const response = await window.axios[method](endpoint, {
            title: remarkEditor.title,
            content: remarkEditor.content,
            is_active: true,
        });
        const preset = response.data?.preset;

        if (!preset) {
            throw new Error('Preset response missing.');
        }

        upsertClientPreset(selectedClient.value.id, preset);
        form.client_remark_preset_id = preset.id;
        form.remarks = preset.content;
        closeRemarkEditor();
        addToast(
            remarkEditor.mode === 'create'
                ? `Saved "${preset.title}" for ${selectedClient.value.name}.`
                : `Updated "${preset.title}".`,
            'success'
        );
    } catch (error) {
        const errors = error?.response?.data?.errors || {};
        remarkEditor.errors = {
            title: errors.title?.[0] || '',
            content: errors.content?.[0] || '',
        };
        addToast(error?.response?.data?.message || 'Could not save the client remark preset right now.', 'error');
    }
}

async function deleteRemarkPreset(preset) {
    if (!preset) {
        return;
    }

    if (!window.confirm(`Delete "${preset.title}" from ${selectedClient.value?.name}?`)) {
        return;
    }

    try {
        await window.axios.delete(`/clients/${selectedClient.value.id}/remark-presets/${preset.id}`);
        removeClientPreset(selectedClient.value.id, preset.id);

        if (String(form.client_remark_preset_id) === String(preset.id)) {
            form.client_remark_preset_id = null;
        }

        closeRemarkEditor();
        addToast(`Deleted "${preset.title}".`, 'success');
    } catch (error) {
        addToast(error?.response?.data?.message || 'Could not delete the client remark preset right now.', 'error');
    }
}

function upsertClientPreset(clientId, preset) {
    const client = clientsState.value.find((item) => String(item.id) === String(clientId));

    if (!client) {
        return;
    }

    const presets = Array.isArray(client.remark_presets) ? [...client.remark_presets] : [];
    const index = presets.findIndex((item) => String(item.id) === String(preset.id));

    if (index >= 0) {
        presets[index] = preset;
    } else {
        presets.push(preset);
    }

    client.remark_presets = presets.sort((left, right) => left.title.localeCompare(right.title));
}

function removeClientPreset(clientId, presetId) {
    const client = clientsState.value.find((item) => String(item.id) === String(clientId));

    if (!client) {
        return;
    }

    client.remark_presets = (client.remark_presets || []).filter((item) => String(item.id) !== String(presetId));
}

function cloneClients(clients) {
    return JSON.parse(JSON.stringify(clients || []));
}

function hydrateRows(rows) {
    return (rows || []).map((row) => ({
        __uuid: row.id ? `existing_${row.id}` : `${row.service_code}_${Date.now()}_${Math.random().toString(36).slice(2, 8)}`,
        schema_vector_id: row.schema_vector_id ?? row.service_schema_id ?? row.schema?.id ?? null,
        service_code: row.service_code,
        service_details: row.service_details || {},
        service_details_extra: row.service_details_extra || {},
        unit_name: row.unit_name || '',
        qty: Number(row.qty || 1),
        base_cost: Number(row.base_cost || row.unit_fare || 0),
        supplier_cost: row.supplier_cost === null || row.supplier_cost === undefined ? null : Number(row.supplier_cost),
        discount_type: row.discount_type || 'RM',
        discount_value: Number(row.discount_value || 0),
        tax_type: row.tax_type || 'RM',
        tax_value: Number(row.tax_value || 0),
        sell_price: Number(row.sell_price || row.client_price || 0),
    }));
}
</script>
