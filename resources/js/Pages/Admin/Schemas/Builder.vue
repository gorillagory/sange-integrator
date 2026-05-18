<template>
    <TenantLayout>
        <template #breadcrumbs>
            <Breadcrumbs :items="breadcrumbItems" />
        </template>

        <div v-if="!isEditing" class="mx-auto max-w-4xl pb-16">
            <div class="rounded-3xl border border-gray-200 bg-white p-10 shadow-sm">
                <div class="inline-flex rounded-full border border-[var(--brand-200)] bg-[var(--brand-50)] px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-[var(--brand-700)]">
                    Central Governance
                </div>
                <h1 class="mt-5 text-3xl font-bold text-gray-900">Schema authoring is handled in Blueprint Forge</h1>
                <p class="mt-3 max-w-3xl text-sm leading-6 text-gray-600">
                    Tenant workspaces no longer carry a second full schema editor. This keeps the platform simpler:
                    blueprint design, versioning, and structural changes stay in the central forge, while tenant teams review the published vectors that are already safe to use.
                </p>

                <div class="mt-8 grid gap-4 md:grid-cols-3">
                    <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5">
                        <div class="text-xs font-bold uppercase tracking-[0.18em] text-gray-400">Blueprint Forge Owns</div>
                        <div class="mt-3 text-sm font-semibold text-gray-900">Structure, validation, versioning, and lifecycle.</div>
                    </div>
                    <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5">
                        <div class="text-xs font-bold uppercase tracking-[0.18em] text-gray-400">Tenant Side Owns</div>
                        <div class="mt-3 text-sm font-semibold text-gray-900">Review, understanding, and operational use of published vectors.</div>
                    </div>
                    <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5">
                        <div class="text-xs font-bold uppercase tracking-[0.18em] text-gray-400">Why This Matters</div>
                        <div class="mt-3 text-sm font-semibold text-gray-900">Less duplication, less drift, and a cleaner mental model for teams.</div>
                    </div>
                </div>

                <div class="mt-8 flex flex-wrap gap-3">
                    <Link
                        href="/admin/schemas"
                        class="inline-flex items-center rounded-xl bg-[var(--brand-600)] px-5 py-2.5 text-sm font-bold text-white transition hover:bg-[var(--brand-500)]"
                    >
                        Back to Schema Manager
                    </Link>
                </div>
            </div>
        </div>

        <div v-else class="pb-16">
            <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-900">
                <div class="font-bold">Review mode only.</div>
                <div class="mt-1 text-amber-800">
                    This tenant screen is intentionally read-only for structure. If this vector needs new fields, rules, attachments, or a new version,
                    update it centrally in Blueprint Forge and then publish the next approved revision.
                </div>
            </div>

            <div class="mb-8 flex flex-wrap items-start justify-between gap-4">
                <div>
                    <div class="inline-flex rounded-full border border-gray-200 bg-white px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-gray-500">
                        Published Vector Review
                    </div>
                    <h1 class="mt-3 text-3xl font-bold text-gray-900">{{ schema.service_name || schema.display_name }}</h1>
                    <p class="mt-2 text-sm text-gray-500">
                        Review the contract your operations team will use inside service records.
                    </p>
                </div>
                <Link
                    href="/admin/schemas"
                    class="inline-flex items-center rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-bold text-gray-700 transition hover:border-[var(--brand-300)] hover:bg-[var(--brand-50)] hover:text-[var(--brand-700)]"
                >
                    Back to Manager
                </Link>
            </div>

            <div class="grid grid-cols-1 gap-8 xl:grid-cols-12">
                <div class="space-y-6 xl:col-span-4">
                    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                        <h2 class="text-sm font-bold uppercase tracking-[0.18em] text-gray-400">Identity</h2>
                        <dl class="mt-4 space-y-4">
                            <div>
                                <dt class="text-[11px] font-bold uppercase tracking-[0.18em] text-gray-400">Service Name</dt>
                                <dd class="mt-1 text-base font-semibold text-gray-900">{{ schema.service_name || schema.display_name }}</dd>
                            </div>
                            <div>
                                <dt class="text-[11px] font-bold uppercase tracking-[0.18em] text-gray-400">Service Code</dt>
                                <dd class="mt-1 font-mono text-sm text-gray-700">{{ schema.service_code || schema.service_type }}</dd>
                            </div>
                            <div>
                                <dt class="text-[11px] font-bold uppercase tracking-[0.18em] text-gray-400">Industry</dt>
                                <dd class="mt-1 text-sm font-semibold text-gray-900">{{ schema.industry || industry }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                            <div class="text-[10px] font-bold uppercase tracking-[0.18em] text-gray-400">Version</div>
                            <div class="mt-2 text-2xl font-black text-[var(--brand-700)]">v{{ schema.version || 1 }}</div>
                        </div>
                        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                            <div class="text-[10px] font-bold uppercase tracking-[0.18em] text-gray-400">Field Count</div>
                            <div class="mt-2 text-2xl font-black text-gray-900">{{ fields.length }}</div>
                        </div>
                        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                            <div class="text-[10px] font-bold uppercase tracking-[0.18em] text-gray-400">Lifecycle</div>
                            <div class="mt-2 inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold uppercase tracking-[0.18em]" :class="statusClasses(schema.status)">
                                {{ schema.status || 'draft' }}
                            </div>
                        </div>
                        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                            <div class="text-[10px] font-bold uppercase tracking-[0.18em] text-gray-400">Default</div>
                            <div class="mt-2 text-sm font-bold" :class="schema.is_default ? 'text-emerald-700' : 'text-gray-500'">
                                {{ schema.is_default ? 'Primary Published Vector' : 'Secondary Published Vector' }}
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                        <h2 class="text-sm font-bold uppercase tracking-[0.18em] text-gray-400">Operational Notes</h2>
                        <ul class="mt-4 space-y-3 text-sm text-gray-600">
                            <li>Use this vector as a governed capture contract inside service records.</li>
                            <li>Every service record row should point to the exact published version used at capture time.</li>
                            <li>Structure changes should create a new approved version centrally rather than mutating this contract in tenant space.</li>
                        </ul>
                    </div>

                    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                        <h2 class="text-sm font-bold uppercase tracking-[0.18em] text-gray-400">Pricing Units</h2>
                        <div v-if="pricingUnits.length > 0" class="mt-4 flex flex-wrap gap-2">
                            <span
                                v-for="unit in pricingUnits"
                                :key="unit"
                                class="rounded-full border border-[var(--brand-200)] bg-[var(--brand-50)] px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-[var(--brand-700)]"
                            >
                                {{ unit }}
                            </span>
                        </div>
                        <p v-else class="mt-4 text-sm text-gray-500">No explicit pricing units were configured on this vector.</p>
                    </div>
                </div>

                <div class="space-y-6 xl:col-span-5">
                    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <h2 class="text-sm font-bold uppercase tracking-[0.18em] text-gray-400">Field Contract</h2>
                                <p class="mt-1 text-sm text-gray-500">This is the payload structure operations will fill.</p>
                            </div>
                        </div>

                        <div v-if="fields.length === 0" class="mt-6 rounded-2xl border border-dashed border-gray-300 bg-gray-50 px-6 py-10 text-center text-sm text-gray-500">
                            No fields were defined on this vector.
                        </div>

                        <div v-else class="mt-6 space-y-4">
                            <div
                                v-for="(field, index) in fields"
                                :key="field.key || index"
                                class="rounded-2xl border border-gray-200 bg-gray-50 p-4"
                            >
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <div class="text-sm font-bold text-gray-900">{{ field.label || 'Untitled Field' }}</div>
                                        <div class="mt-1 font-mono text-[11px] text-gray-500">{{ field.key || 'missing_key' }}</div>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        <span class="rounded-full bg-white px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-gray-500 border border-gray-200">
                                            {{ prettyComponent(field.ui_component) }}
                                        </span>
                                        <span class="rounded-full bg-white px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-gray-500 border border-gray-200">
                                            {{ Number(field.grid_span) === 2 ? 'Full Width' : 'Half Width' }}
                                        </span>
                                    </div>
                                </div>

                                <div class="mt-3 grid gap-3 md:grid-cols-2">
                                    <div>
                                        <div class="text-[10px] font-bold uppercase tracking-[0.18em] text-gray-400">Placeholder</div>
                                        <div class="mt-1 text-sm text-gray-600">{{ field.placeholder || 'No placeholder provided' }}</div>
                                    </div>
                                    <div>
                                        <div class="text-[10px] font-bold uppercase tracking-[0.18em] text-gray-400">Rules</div>
                                        <div class="mt-1 text-sm text-gray-600">{{ formatRules(field.rules) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="documentOutput" class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                        <h2 class="text-sm font-bold uppercase tracking-[0.18em] text-gray-400">Document Output Notes</h2>
                        <pre class="mt-4 overflow-x-auto rounded-2xl bg-gray-950 px-4 py-4 text-xs leading-6 text-emerald-300">{{ documentOutput }}</pre>
                    </div>
                </div>

                <div class="xl:col-span-3">
                    <div class="sticky top-6 rounded-2xl border border-gray-200 bg-white shadow-sm">
                        <div class="border-b border-gray-100 bg-gray-50 px-5 py-4">
                            <div class="text-sm font-bold text-gray-800">Operational Preview</div>
                            <div class="mt-1 text-xs text-gray-500">How this vector feels to an operator during capture.</div>
                        </div>

                        <div class="p-5">
                            <div class="grid grid-cols-1 gap-4 rounded-2xl border border-blue-100 bg-blue-50/60 p-4">
                                <div v-if="fields.length === 0" class="text-sm text-gray-400">
                                    Preview appears once fields exist on the vector.
                                </div>

                                <div
                                    v-for="(field, index) in fields"
                                    :key="`${field.key || 'field'}-preview-${index}`"
                                    :class="Number(field.grid_span) === 2 ? 'md:col-span-2' : ''"
                                >
                                    <label class="mb-1 block text-xs font-semibold uppercase text-blue-900">
                                        {{ field.label || 'Untitled Field' }}
                                    </label>

                                    <input
                                        v-if="field.ui_component === 'text_input'"
                                        type="text"
                                        :placeholder="field.placeholder || 'Text input'"
                                        class="w-full rounded-lg border-gray-300 bg-white text-sm text-gray-900 shadow-sm"
                                        disabled
                                    >

                                    <input
                                        v-else-if="field.ui_component === 'date_picker'"
                                        type="date"
                                        class="w-full rounded-lg border-gray-300 bg-white text-sm text-gray-900 shadow-sm"
                                        disabled
                                    >

                                    <select
                                        v-else-if="field.ui_component === 'select_dropdown'"
                                        class="w-full rounded-lg border-gray-300 bg-white text-sm text-gray-900 shadow-sm"
                                        disabled
                                    >
                                        <option>{{ field.placeholder || 'Select an option' }}</option>
                                    </select>

                                    <textarea
                                        v-else
                                        rows="2"
                                        :placeholder="field.placeholder || 'Structured input'"
                                        class="w-full rounded-lg border-gray-300 bg-white text-sm text-gray-900 shadow-sm"
                                        disabled
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </TenantLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import TenantLayout from '../../../Layouts/TenantLayout.vue';
import Breadcrumbs from '../../../Components/UI/Breadcrumbs.vue';

const props = defineProps({
    schema: {
        type: Object,
        default: null,
    },
    industry: {
        type: String,
        default: 'travel',
    },
});

const isEditing = computed(() => Boolean(props.schema));

const payload = computed(() => {
    if (!props.schema?.schema_payload) {
        return {};
    }

    return typeof props.schema.schema_payload === 'string'
        ? JSON.parse(props.schema.schema_payload)
        : props.schema.schema_payload;
});

const fields = computed(() => Array.isArray(payload.value?.fields) ? payload.value.fields : []);
const pricingUnits = computed(() => Array.isArray(payload.value?.pricing_units) ? payload.value.pricing_units : []);
const documentOutput = computed(() => payload.value?.document_output || '');

const breadcrumbItems = computed(() => {
    if (!isEditing.value) {
        return [
            { label: 'Admin Settings', url: null },
            { label: 'Schema Manager', url: '/admin/schemas' },
            { label: 'Centralized Authoring', url: null },
        ];
    }

    return [
        { label: 'Admin Settings', url: null },
        { label: 'Schema Manager', url: '/admin/schemas' },
        { label: props.schema.service_name || props.schema.display_name || 'Vector Review', url: null },
    ];
});

const prettyComponent = (component) => {
    switch (component) {
        case 'text_input':
            return 'Text Input';
        case 'date_picker':
            return 'Date Picker';
        case 'select_dropdown':
            return 'Dropdown';
        default:
            return component || 'Field';
    }
};

const formatRules = (rules) => {
    if (!Array.isArray(rules) || rules.length === 0) {
        return 'No explicit validation rules';
    }

    return rules.join(', ');
};

const statusClasses = (status) => {
    switch ((status || '').toLowerCase()) {
        case 'active':
            return 'border border-emerald-200 bg-emerald-50 text-emerald-700';
        case 'deprecated':
            return 'border border-amber-200 bg-amber-50 text-amber-700';
        case 'archived':
            return 'border border-gray-200 bg-gray-100 text-gray-600';
        default:
            return 'border border-sky-200 bg-sky-50 text-sky-700';
    }
};
</script>
