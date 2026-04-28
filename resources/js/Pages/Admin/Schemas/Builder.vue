<template>
    <TenantLayout>
        <template #breadcrumbs>
            <Breadcrumbs :items="[
                { label: 'Admin Settings', url: null },
                { label: 'Service Vectors', url: '/admin/schemas' },
                { label: 'Vector Builder', url: null }
            ]" />
        </template>

        <div class="mb-8 flex justify-between items-end">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Schema Vector Builder</h1>
                <p class="text-sm text-gray-500 mt-1">Design dynamic operational payloads and UI rules for master bookings.</p>
            </div>
            <button @click="saveSchema" :disabled="form.processing" class="px-6 py-2.5 bg-[var(--brand-600)] hover:bg-[var(--brand-500)] text-white text-sm font-bold rounded-xl transition shadow-lg flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                Deploy Schema to Production
            </button>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-8 items-start pb-20">

            <div class="xl:col-span-3 space-y-6">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h3 class="font-bold text-gray-900 mb-4 pb-2 border-b border-gray-100">1. Core Definition</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Display Name <span class="text-red-500">*</span></label>
                            <input v-model="form.display_name" @input="generateSystemKey" type="text" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:bg-white focus:border-[var(--brand-500)]" placeholder="e.g. Flight Ticketing">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">System Key (Immutable) <span class="text-red-500">*</span></label>
                            <input v-model="form.service_type" type="text" class="w-full bg-gray-100 border border-gray-200 rounded-lg px-3 py-2 text-sm font-mono text-gray-600" placeholder="flight_ticketing" :readonly="isEditing">
                            <p class="text-[9px] text-gray-400 mt-1">Used internally for database mapping.</p>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Target Industry</label>
                            <select v-model="form.industry" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:bg-white focus:border-[var(--brand-500)]">
                                <option value="travel">Travel & Tourism</option>
                                <option value="logistics">Logistics</option>
                                <option value="events">Event Management</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="xl:col-span-5 space-y-4">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="font-bold text-gray-900">2. Payload Attributes ({{ form.schema_payload.length }})</h3>
                    <button @click.prevent="addField" class="text-xs font-bold text-[var(--brand-600)] hover:text-[var(--brand-800)] flex items-center gap-1">
                        + Add Attribute
                    </button>
                </div>

                <div v-if="form.schema_payload.length === 0" class="p-8 border-2 border-dashed border-gray-200 rounded-2xl text-center text-gray-500 text-sm font-medium bg-gray-50">
                    No attributes defined. Click '+ Add Attribute' to begin building the schema vector.
                </div>

                <div v-for="(field, index) in form.schema_payload" :key="index" class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm relative group transition hover:border-[var(--brand-300)]">
                    <button @click.prevent="removeField(index)" class="absolute top-4 right-4 text-gray-300 hover:text-red-500 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>

                    <div class="grid grid-cols-2 gap-4 mb-4 pr-6">
                        <div>
                            <label class="block text-[10px] font-bold text-[var(--brand-600)] uppercase mb-1">UI Label</label>
                            <input v-model="field.label" @input="generateFieldKey(field)" type="text" class="w-full bg-gray-50 border border-gray-200 rounded-md px-3 py-1.5 text-sm" placeholder="e.g. Passenger Name">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">JSON Key</label>
                            <input v-model="field.key" type="text" class="w-full bg-gray-100 border border-gray-200 rounded-md px-3 py-1.5 text-sm font-mono text-gray-600" placeholder="passenger_name">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 pt-4 border-t border-gray-100">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Component</label>
                            <select v-model="field.ui_component" class="w-full bg-gray-50 border border-gray-200 rounded-md px-2 py-1.5 text-xs">
                                <option value="text_input">Text Input</option>
                                <option value="textarea">Textarea (Large)</option>
                                <option value="file">File Upload</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Grid Width</label>
                            <select v-model="field.grid_span" class="w-full bg-gray-50 border border-gray-200 rounded-md px-2 py-1.5 text-xs">
                                <option :value="1">Half Width (1 Col)</option>
                                <option :value="2">Full Width (2 Col)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Requirement</label>
                            <div class="flex items-center h-8 gap-2">
                                <input type="checkbox" :checked="field.rules.includes('required')" @change="toggleRequired(field, $event.target.checked)" class="text-[var(--brand-600)] rounded border-gray-300 focus:ring-[var(--brand-500)]">
                                <span class="text-xs text-gray-600 font-medium">Required</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-[var(--brand-600)] uppercase mb-1">Data Structure</label>
                            <div class="flex items-center h-8 gap-2">
                                <input type="checkbox" v-model="field.is_array" class="text-[var(--brand-600)] rounded border-gray-300 focus:ring-[var(--brand-500)]">
                                <span class="text-xs text-gray-600 font-medium" :class="{'text-[var(--brand-600)] font-bold': field.is_array}">Repeatable List</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="xl:col-span-4 space-y-6 sticky top-8">

                <div class="bg-gray-50 border border-gray-200 rounded-2xl overflow-hidden shadow-inner">
                    <div class="bg-gray-200 px-4 py-2 border-b border-gray-300">
                        <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Agent UI Preview</span>
                    </div>
                    <div class="p-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-for="field in form.schema_payload" :key="field.key" :class="field.grid_span === 2 || field.ui_component === 'textarea' ? 'md:col-span-2' : 'md:col-span-1'">
                                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1 flex justify-between items-center">
                                    <span>{{ field.label || 'Label' }} <span v-if="field.rules.includes('required')" class="text-red-500">*</span></span>
                                    <span v-if="field.is_array" class="text-[9px] text-[var(--brand-500)] bg-[var(--brand-50)] px-1.5 py-0.5 rounded">List</span>
                                </label>

                                <div v-if="field.is_array" class="space-y-2">
                                    <input v-if="field.ui_component === 'text_input' || !field.ui_component" type="text" disabled class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm opacity-70" :placeholder="(field.placeholder || '...') + ' 1'">
                                    <textarea v-else-if="field.ui_component === 'textarea'" rows="2" disabled class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm opacity-70" placeholder="..."></textarea>
                                    <button disabled class="text-[10px] font-bold text-[var(--brand-600)] flex items-center gap-1 mt-1 opacity-50">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                        Add another
                                    </button>
                                </div>
                                <div v-else>
                                    <input v-if="field.ui_component === 'text_input' || !field.ui_component" type="text" disabled class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm opacity-70" :placeholder="field.placeholder || '...'">
                                    <textarea v-else-if="field.ui_component === 'textarea'" rows="2" disabled class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm opacity-70" placeholder="..."></textarea>
                                    <div v-else-if="field.ui_component === 'file'" class="w-full bg-gray-100 border border-gray-300 border-dashed rounded-lg px-3 py-2 text-xs text-gray-400 text-center">Attachment Placeholder</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-900 rounded-2xl border border-gray-700 shadow-xl overflow-hidden">
                    <div class="bg-black/50 px-4 py-2 border-b border-gray-800 flex justify-between items-center">
                        <span class="text-[10px] font-bold text-emerald-400 uppercase tracking-wider">Generated JSON Vector</span>
                    </div>
                    <pre class="p-4 text-xs text-gray-300 font-mono overflow-x-auto max-h-96 scrollbar-thin scrollbar-thumb-gray-700">{{ compiledJson }}</pre>
                </div>
            </div>

        </div>
    </TenantLayout>
</template>

<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import TenantLayout from '../../../Layouts/TenantLayout.vue';
import Breadcrumbs from '../../../Components/UI/Breadcrumbs.vue';

const props = defineProps({
    schema: {
        type: Object,
        default: null
    }
});

const isEditing = computed(() => !!props.schema);

const form = useForm({
    display_name: props.schema?.display_name || '',
    service_type: props.schema?.service_type || '',
    industry: props.schema?.industry || 'travel',
    schema_payload: props.schema?.schema_payload ? JSON.parse(props.schema.schema_payload) : []
});

const generateSystemKey = () => {
    if (isEditing.value) return;
    form.service_type = form.display_name.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/(^_|_$)/g, '');
};

const generateFieldKey = (field) => {
    if (!field.key || field.key === '') {
        field.key = field.label.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/(^_|_$)/g, '');
    }
};

const addField = () => {
    form.schema_payload.push({
        key: '',
        label: '',
        type: 'string',
        ui_component: 'text_input',
        grid_span: 1,
        placeholder: '',
        rules: [],
        is_array: false // Initialize boolean
    });
};

const removeField = (index) => {
    form.schema_payload.splice(index, 1);
};

const toggleRequired = (field, isRequired) => {
    if (isRequired) {
        if (!field.rules.includes('required')) field.rules.push('required');
    } else {
        field.rules = field.rules.filter(r => r !== 'required');
    }
};

const compiledJson = computed(() => {
    const payload = {
        fields: form.schema_payload.map(f => ({
            key: f.key,
            type: f.type,
            label: f.label,
            rules: f.rules,
            grid_span: f.grid_span,
            ui_component: f.ui_component,
            is_array: f.is_array || false // Map boolean to payload
        }))
    };
    return JSON.stringify(payload, null, 4);
});

const saveSchema = () => {
    if (isEditing.value) {
        form.put(`/admin/schemas/${props.schema.id}`);
    } else {
        form.post('/admin/schemas');
    }
};
</script>
