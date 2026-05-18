<template>
    <SystemLayout>
        <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
            <div>
                <Link href="/blueprints" class="mb-2 flex items-center gap-2 text-sm text-indigo-400 transition hover:text-white">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Back to Forge
                </Link>
                <h1 class="text-3xl font-bold text-white">
                    {{ isEditing ? `${form.service_name || form.display_name || 'Schema Vector'} Blueprint` : 'Forge New Blueprint' }}
                </h1>
                <p class="mt-2 max-w-3xl text-sm text-gray-400">
                    Blueprint Forge is the master schema-vector authoring surface. Use it to define structure, validation, pricing units, and document output before tenants consume the published version.
                </p>
            </div>

            <button
                @click="attemptSave"
                :disabled="form.processing"
                class="flex items-center gap-2 rounded-xl bg-indigo-600 px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-indigo-500/20 transition hover:bg-indigo-500 disabled:opacity-50"
                :class="{ 'animate-shake bg-red-600 hover:bg-red-600': isShaking }"
            >
                <svg v-if="!form.processing" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                <svg v-else class="h-4 w-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                {{ isEditing ? 'Publish Updated Vector' : 'Publish Master Vector' }}
            </button>
        </div>

        <div class="mb-8 rounded-2xl border border-indigo-500/20 bg-indigo-500/5 px-6 py-4 text-sm text-indigo-100">
            <div class="font-bold text-white">Govern centrally, consume safely.</div>
            <div class="mt-1 text-indigo-100/80">
                Tenant workspaces only review and consume published vectors. Structural edits, versions, and lifecycle changes belong here.
            </div>
        </div>

        <div class="grid grid-cols-1 items-start gap-8 pb-20 xl:grid-cols-12">
            <div class="space-y-6 xl:col-span-4">
                <div class="overflow-hidden rounded-2xl border border-white/10 bg-[#1e293b] p-6 shadow-xl">
                    <h2 class="mb-1 font-bold text-white">1. Vector Identity</h2>
                    <p class="mb-4 border-b border-white/10 pb-3 text-xs text-gray-400">Name the vector, confirm its key, and set lifecycle defaults.</p>

                    <div class="space-y-4">
                        <div>
                            <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-gray-400">Service Name <span class="text-red-400">*</span></label>
                            <input
                                v-model="form.service_name"
                                @input="generateSystemKey"
                                type="text"
                                class="w-full rounded-lg border border-white/10 bg-black/20 px-4 py-2 text-sm text-white focus:border-indigo-500"
                                placeholder="e.g. Airport Transfer"
                            >
                            <p v-if="form.errors.service_name || form.errors.display_name" class="mt-1 text-xs text-red-400">
                                {{ form.errors.service_name || form.errors.display_name }}
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-gray-400">Target Industry</label>
                                <select
                                    v-model="form.industry"
                                    @change="generateSystemKey"
                                    class="w-full rounded-lg border border-white/10 bg-black/20 px-4 py-2 text-sm text-white focus:border-indigo-500"
                                >
                                    <option value="travel">Travel & Aviation</option>
                                    <option value="medical">Medical & Clinics</option>
                                    <option value="logistics">Logistics</option>
                                    <option value="general">General Cross-Platform</option>
                                </select>
                                <p v-if="form.errors.industry" class="mt-1 text-xs text-red-400">{{ form.errors.industry }}</p>
                            </div>

                            <div>
                                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-gray-400">Version</label>
                                <input
                                    v-model.number="form.version"
                                    type="number"
                                    min="1"
                                    class="w-full rounded-lg border border-white/10 bg-black/20 px-4 py-2 text-sm text-white focus:border-indigo-500"
                                >
                                <p v-if="form.errors.version" class="mt-1 text-xs text-red-400">{{ form.errors.version }}</p>
                            </div>
                        </div>

                        <div>
                            <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-gray-400">Database Key (Unique) <span class="text-red-400">*</span></label>
                            <input
                                v-model="form.service_code"
                                @input="serviceCodeManuallyEdited = true"
                                type="text"
                                class="w-full rounded-lg border border-white/10 bg-black/20 px-4 py-2 font-mono text-sm text-indigo-300 focus:border-indigo-500"
                                placeholder="travel.airport_transfer"
                            >
                            <p class="mt-1 text-[10px] uppercase tracking-[0.18em] text-gray-500">
                                Auto-generated from the service name until manually edited.
                            </p>
                            <p v-if="form.errors.service_code" class="mt-1 text-xs text-red-400">{{ form.errors.service_code }}</p>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-gray-400">Lifecycle Status</label>
                                <select
                                    v-model="form.status"
                                    class="w-full rounded-lg border border-white/10 bg-black/20 px-4 py-2 text-sm text-white focus:border-indigo-500"
                                >
                                    <option value="draft">Draft</option>
                                    <option value="active">Active</option>
                                    <option value="deprecated">Deprecated</option>
                                    <option value="archived">Archived</option>
                                </select>
                                <p v-if="form.errors.status" class="mt-1 text-xs text-red-400">{{ form.errors.status }}</p>
                            </div>

                            <div class="rounded-xl border border-white/10 bg-black/20 px-4 py-3">
                                <div class="mb-2 text-[10px] font-bold uppercase tracking-wider text-gray-400">Default Vector</div>
                                <label class="flex items-center gap-2 text-sm text-gray-200">
                                    <input v-model="form.is_default" type="checkbox" class="rounded border-white/20 bg-black/20 text-indigo-500">
                                    Mark as primary active version
                                </label>
                            </div>
                        </div>

                        <div class="rounded-xl border border-white/10 bg-black/20 p-4">
                            <label class="mb-2 block text-[10px] font-bold uppercase tracking-wider text-gray-400">Pricing Units</label>

                            <div class="mb-3 flex items-center gap-2">
                                <input
                                    v-model="newUnit"
                                    @keydown.enter.prevent="addUnit"
                                    type="text"
                                    class="flex-1 rounded-lg border border-white/10 bg-[#0f172a] px-3 py-2 text-xs text-white focus:border-indigo-500"
                                    placeholder="Type unit and press Enter, e.g. pax, room"
                                >
                                <button @click.prevent="addUnit" class="rounded-lg bg-white/10 px-3 py-2 text-xs font-bold text-white transition hover:bg-white/20">Add</button>
                            </div>

                            <div class="flex min-h-[40px] flex-wrap items-center gap-2 rounded-lg border border-white/10 bg-[#0f172a] p-3">
                                <span v-if="form.pricing_units.length === 0" class="text-[10px] italic text-gray-500">No units added yet.</span>
                                <span
                                    v-for="(unit, index) in form.pricing_units"
                                    :key="`${unit}-${index}`"
                                    class="group flex items-center gap-1.5 rounded-full border border-indigo-500/20 bg-indigo-500/10 px-2.5 py-1"
                                >
                                    <span class="text-[10px] font-bold uppercase tracking-[0.18em] text-indigo-200">{{ unit }}</span>
                                    <button @click.prevent="removeUnit(index)" class="text-indigo-300 opacity-60 transition hover:text-red-300 group-hover:opacity-100">
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <VisualDocumentEditor v-model="form.document_output" :schema-fields="form.schema_payload" surface="dark" />
            </div>

            <div class="xl:col-span-5">
                <AttributeManager v-model="form.schema_payload" surface="dark" title="2. Schema Fields" />
            </div>

            <div class="xl:col-span-3">
                <SchemaPreview :fields="form.schema_payload" :compiled-json="compiledJson" surface="dark" />
            </div>
        </div>
    </SystemLayout>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import SystemLayout from '../../../../Layouts/SystemLayout.vue';
import AttributeManager from '../../../Admin/Schemas/Components/AttributeManager.vue';
import SchemaPreview from '../../../Admin/Schemas/Components/SchemaPreview.vue';
import VisualDocumentEditor from '../../../Admin/Schemas/Components/VisualDocumentEditor.vue';
import { useSchemaCompiler } from '../../../Admin/Schemas/Composables/useSchemaCompiler';

const props = defineProps({
    schema: {
        type: Object,
        default: null,
    },
});

const isEditing = computed(() => Boolean(props.schema));
const isShaking = ref(false);
const newUnit = ref('');
const serviceCodeManuallyEdited = ref(Boolean(props.schema?.service_code));

const parsePayload = (schemaPayload) => {
    if (!schemaPayload) {
        return {};
    }

    return typeof schemaPayload === 'string' ? JSON.parse(schemaPayload) : schemaPayload;
};

const getInitialPayload = () => {
    const parsed = parsePayload(props.schema?.schema_payload);
    const fields = Array.isArray(parsed?.fields) ? parsed.fields : [];

    return fields.map((field, index) => ({
        ...field,
        order: field.order ?? index,
        _show_advanced: false,
        _is_minimized: true,
        _key_manually_edited: true,
    }));
};

const getInitialPayloadValue = (key, fallback) => {
    const parsed = parsePayload(props.schema?.schema_payload);
    return parsed?.[key] ?? fallback;
};

const form = useForm({
    display_name: props.schema?.display_name || props.schema?.service_name || '',
    service_name: props.schema?.service_name || props.schema?.display_name || '',
    service_type: props.schema?.service_type || props.schema?.service_code || '',
    service_code: props.schema?.service_code || props.schema?.service_type || '',
    version: props.schema?.version || 1,
    status: props.schema?.status || 'active',
    is_default: props.schema?.is_default ?? true,
    industry: props.schema?.industry || 'travel',
    schema_payload: getInitialPayload(),
    document_output: getInitialPayloadValue('document_output', ''),
    pricing_units: getInitialPayloadValue('pricing_units', []),
});

const { compiledJson, hasGlobalDuplicates } = useSchemaCompiler(form);

const normalizeSystemKey = (value) => {
    return value
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '_')
        .replace(/(^_|_$)/g, '');
};

const generateSystemKey = () => {
    const normalized = normalizeSystemKey(form.service_name || form.display_name || '');
    form.display_name = form.service_name;
    form.service_type = normalized;

    if (!serviceCodeManuallyEdited.value) {
        form.service_code = normalized ? `${form.industry}.${normalized}` : '';
    }
};

const addUnit = () => {
    const unit = newUnit.value.trim().toLowerCase();
    if (unit !== '' && !form.pricing_units.includes(unit)) {
        form.pricing_units.push(unit);
    }
    newUnit.value = '';
};

const removeUnit = (index) => {
    form.pricing_units.splice(index, 1);
};

const attemptSave = () => {
    if (hasGlobalDuplicates.value) {
        isShaking.value = true;
        setTimeout(() => {
            isShaking.value = false;
        }, 600);
        return;
    }

    const payloadToSave = {
        ...form.data(),
        display_name: form.service_name,
        service_name: form.service_name,
        service_code: form.service_code,
        service_type: form.service_type || form.service_code,
        schema_payload: JSON.parse(compiledJson.value),
    };

    const method = isEditing.value ? 'put' : 'post';
    const url = isEditing.value
        ? `/blueprints/${props.schema.id}`
        : '/blueprints';

    form.transform(() => payloadToSave)[method](url);
};
</script>

<style scoped>
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

.animate-shake {
    animation: shake 0.2s ease-in-out 0s 2;
}
</style>
