<template>
    <TenantLayout>
        <template #breadcrumbs>
            <Breadcrumbs :items="[
                { label: 'Admin Settings', url: null },
                { label: 'Service Vectors', url: '/admin/schemas' },
                { label: isEditing ? `Edit: ${form.display_name}` : 'Create New Vector', url: null }
            ]" />
        </template>

        <div class="mb-8 flex justify-between items-end">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ isEditing ? 'Edit Schema Vector' : 'Create New Vector' }}</h1>
                <p class="text-sm text-gray-500 mt-1">{{ isEditing ? `Modifying ${form.display_name}.` : 'Design operational payloads for master bookings.' }}</p>
            </div>
            <button
                @click="attemptSave"
                :disabled="form.processing"
                class="px-6 py-2.5 bg-[var(--brand-600)] hover:bg-[var(--brand-500)] text-white text-sm font-bold rounded-xl transition shadow-lg flex items-center gap-2"
                :class="{'animate-shake bg-red-600': isShaking}"
            >
                <svg v-if="!form.processing" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                <svg v-else class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                {{ isEditing ? 'Update & Sync Vector' : 'Deploy Schema to Production' }}
            </button>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-8 items-start pb-20">
            <div class="xl:col-span-4 space-y-6">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h3 class="font-bold text-gray-900 mb-4 pb-2 border-b border-gray-100">1. Core Definition</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Display Name <span class="text-red-500">*</span></label>
                            <input v-model="form.display_name" @input="generateSystemKey" type="text" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:bg-white focus:border-[var(--brand-500)]" placeholder="e.g. Hotel Accommodation">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">System Key (Immutable) <span class="text-red-500">*</span></label>
                            <input v-model="form.service_type" type="text" class="w-full bg-gray-100 border border-gray-200 rounded-lg px-3 py-2 text-sm font-mono text-gray-600" placeholder="hotel_accommodation" :readonly="isEditing">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Target Industry</label>
                            <select v-model="form.industry" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:bg-white">
                                <option value="travel">Travel & Tourism</option>
                                <option value="logistics">Logistics</option>
                                <option value="events">Event Management</option>
                            </select>
                        </div>

                        <div class="pt-3 border-t border-gray-100">
                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2">Pricing Units</label>

                            <div class="flex items-center gap-2 mb-3">
                                <input
                                    v-model="newUnit"
                                    @keydown.enter.prevent="addUnit"
                                    type="text"
                                    class="flex-1 bg-gray-50 border border-gray-200 rounded-lg px-3 py-1.5 text-xs focus:bg-white focus:border-[var(--brand-500)]"
                                    placeholder="Type unit and press Enter (e.g., Pax, Room)"
                                >
                                <button @click.prevent="addUnit" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded-lg text-xs font-bold transition">Add</button>
                            </div>

                            <div class="flex flex-wrap gap-2 min-h-[36px] p-2 bg-gray-50 border border-gray-100 rounded-lg items-center">
                                <span v-if="form.pricing_units.length === 0" class="text-[10px] text-gray-400 italic">No units added. Default calculation is 1.</span>

                                <span
                                    v-for="(unit, index) in form.pricing_units"
                                    :key="index"
                                    class="flex items-center gap-1.5 bg-white border border-[var(--brand-200)] px-2 py-1 rounded shadow-sm group"
                                >
                                    <span class="text-[10px] font-bold text-[var(--brand-700)] uppercase tracking-wider">{{ unit }}</span>
                                    <button @click.prevent="removeUnit(index)" class="text-[var(--brand-400)] hover:text-red-500 focus:outline-none transition opacity-50 group-hover:opacity-100" title="Remove unit">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                </span>
                            </div>
                            <p class="text-[8px] text-gray-400 mt-2 uppercase tracking-widest">Calculates as: Base Price × {{ form.pricing_units.length > 0 ? form.pricing_units.join(' × ') : '1' }}</p>
                        </div>
                    </div>
                </div>

                <VisualDocumentEditor
                    v-model="form.document_output"
                    :schema-fields="form.schema_payload"
                />
            </div>

            <div class="xl:col-span-5">
                <AttributeManager v-model="form.schema_payload" />
            </div>

            <div class="xl:col-span-3 sticky top-6">
                <SchemaPreview :fields="form.schema_payload" :compiledJson="compiledJson" />
            </div>
        </div>

        <GlobalToast ref="toastRef" />
    </TenantLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

// Internal Components
import TenantLayout from '../../../Layouts/TenantLayout.vue';
import Breadcrumbs from '../../../Components/UI/Breadcrumbs.vue';
import SchemaPreview from './Components/SchemaPreview.vue';
import GlobalToast from '../../../Components/GlobalToast.vue';
import VisualDocumentEditor from './Components/VisualDocumentEditor.vue';
import AttributeManager from './Components/AttributeManager.vue';

// Composables
import { useSchemaCompiler } from './Composables/useSchemaCompiler';

const props = defineProps({ schema: { type: Object, default: null } });
const isEditing = computed(() => !!props.schema);
const toastRef = ref(null);
const isShaking = ref(false);

// Extract Data
const getInitialPayload = () => {
    if (!props.schema?.schema_payload) return [];
    const parsed = typeof props.schema.schema_payload === 'string' ? JSON.parse(props.schema.schema_payload) : props.schema.schema_payload;
    return (parsed.fields || []).map((f, i) => ({ ...f, order: f.order ?? i, _show_advanced: false, _is_minimized: true, _key_manually_edited: true }));
};

const getInitialData = (key, fallback) => {
    if (!props.schema?.schema_payload) return fallback;
    const parsed = typeof props.schema.schema_payload === 'string' ? JSON.parse(props.schema.schema_payload) : props.schema.schema_payload;
    return parsed[key] || fallback;
};

// Form State
const form = useForm({
    display_name: props.schema?.display_name || '',
    service_type: props.schema?.service_type || '',
    industry: props.schema?.industry || 'travel',
    schema_payload: getInitialPayload(),
    document_output: getInitialData('document_output', ''),
    pricing_units: getInitialData('pricing_units', [])
});

// JSON Compilation Magic via Composable
const { compiledJson, hasGlobalDuplicates } = useSchemaCompiler(form);

// Dynamic Units
const newUnit = ref('');
const addUnit = () => {
    const val = newUnit.value.trim().toLowerCase();
    if (val && !form.pricing_units.includes(val)) form.pricing_units.push(val);
    newUnit.value = '';
};
const removeUnit = (index) => form.pricing_units.splice(index, 1);

// Generate Key
const generateSystemKey = () => {
    if (isEditing.value) return;
    form.service_type = form.display_name.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/(^_|_$)/g, '');
};

// Save Logic
const attemptSave = () => {
    if (hasGlobalDuplicates.value) {
        isShaking.value = true;
        toastRef.value.addToast('Cannot deploy: Resolve duplicate keys first!', 'error');
        setTimeout(() => { isShaking.value = false; }, 600);
        return;
    }

    const payloadToSave = { ...form.data(), schema_payload: JSON.parse(compiledJson.value) };
    const method = isEditing.value ? 'put' : 'post';
    const url = isEditing.value ? `/admin/schemas/${props.schema.id}` : '/admin/schemas';

    form.transform(() => payloadToSave)[method](url);
};
</script>

<style scoped>
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}
.animate-shake { animation: shake 0.2s ease-in-out 0s 2; }
</style>
