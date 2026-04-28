<template>
    <SystemLayout>

        <div class="flex justify-between items-center mb-8">
            <div>
                <Link href="/blueprints" class="text-indigo-400 hover:text-white text-sm flex items-center gap-2 mb-2 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Back to Forge
                </Link>
                <h2 class="text-3xl font-bold text-white flex items-center gap-3">
                    {{ schema.display_name }} Blueprint
                    <span class="px-2.5 py-1 text-xs font-bold bg-indigo-500/10 text-indigo-400 rounded-md border border-indigo-500/20 font-mono">{{ schema.service_type }}</span>
                </h2>
            </div>

            <button @click="saveBlueprint" :disabled="form.processing" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-bold rounded-xl transition shadow-lg shadow-indigo-500/20 disabled:opacity-50">
                Deploy to Tenant Vaults
            </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

            <div class="lg:col-span-7 space-y-6">
                <div class="bg-[#1e293b] rounded-2xl border border-white/10 shadow-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-white/10 bg-black/20 flex justify-between items-center">
                        <h3 class="font-bold text-white">Schema Nodes (Input Fields)</h3>
                        <button @click="addField" class="text-xs font-bold text-indigo-400 hover:text-indigo-300 transition">+ Add Node</button>
                    </div>

                    <div class="p-6 space-y-4 max-h-[600px] overflow-y-auto">
                        <div v-if="form.schema_payload.fields.length === 0" class="text-center text-gray-500 py-10 text-sm">
                            No nodes found. Add a node to build the form.
                        </div>

                        <div v-for="(field, index) in form.schema_payload.fields" :key="index" class="bg-black/20 border border-white/5 rounded-xl p-5 relative group transition-all hover:border-indigo-500/30">

                            <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition flex gap-2">
                                <button @click.prevent="moveUp(index)" :disabled="index === 0" class="text-gray-400 hover:text-white disabled:opacity-20"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg></button>
                                <button @click.prevent="moveDown(index)" :disabled="index === form.schema_payload.fields.length - 1" class="text-gray-400 hover:text-white disabled:opacity-20"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg></button>
                                <button @click.prevent="removeField(index)" class="text-red-400 hover:text-red-300 ml-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Display Label</label>
                                    <input v-model="field.label" type="text" class="w-full bg-[#0f172a] border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:border-indigo-500" placeholder="e.g. Passenger Name">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">JSON Key (Database)</label>
                                    <input v-model="field.key" type="text" class="w-full bg-[#0f172a] border border-white/10 rounded-lg px-3 py-2 text-sm text-indigo-400 font-mono focus:border-indigo-500" placeholder="e.g. passenger_name">
                                </div>

                                <div>
                                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">UI Component</label>
                                    <select v-model="field.ui_component" class="w-full bg-[#0f172a] border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:border-indigo-500">
                                        <option value="text_input">Text Input</option>
                                        <option value="date_picker">Date Picker</option>
                                        <option value="select_dropdown">Dropdown</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Grid Span</label>
                                    <select v-model="field.grid_span" class="w-full bg-[#0f172a] border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:border-indigo-500">
                                        <option :value="1">Half Width (1 Col)</option>
                                        <option :value="2">Full Width (2 Cols)</option>
                                    </select>
                                </div>

                                <div class="col-span-2">
                                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Placeholder / Hint</label>
                                    <input v-model="field.placeholder" type="text" class="w-full bg-[#0f172a] border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:border-indigo-500">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-5 space-y-6">

                <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <h3 class="font-bold text-gray-800 text-sm">Tenant POS Preview</h3>
                        </div>
                        <span class="text-xs text-gray-400">Light Theme Simulation</span>
                    </div>

                    <div class="p-6 bg-white">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-5 bg-blue-50/50 rounded-xl border border-blue-100">

                            <div v-for="field in form.schema_payload.fields" :key="field.key" :class="field.grid_span === 2 ? 'md:col-span-2' : ''">
                                <label class="block text-xs font-semibold text-blue-900 uppercase mb-1">
                                    {{ field.label || 'Untitled Label' }}
                                </label>

                                <input v-if="field.ui_component === 'text_input'" type="text" :placeholder="field.placeholder" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm bg-white text-gray-900" disabled>

                                <input v-if="field.ui_component === 'date_picker'" type="date" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm bg-white text-gray-900" disabled>

                                <select v-if="field.ui_component === 'select_dropdown'" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm bg-white text-gray-900" disabled>
                                    <option>{{ field.placeholder || 'Select an option' }}</option>
                                </select>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="bg-black/40 rounded-xl p-4 border border-white/5">
                    <h4 class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2">Generated JSON Payload</h4>
                    <pre class="text-xs text-emerald-400 font-mono overflow-x-auto">{{ form.schema_payload }}</pre>
                </div>

            </div>
        </div>

    </SystemLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import SystemLayout from '../../../Layouts/SystemLayout.vue';

const props = defineProps({
    schema: Object
});

// Initialize form with existing schema payload, or default empty array
const form = useForm({
    schema_payload: props.schema.schema_payload || { fields: [] }
});

// Array Manipulation Methods
const addField = () => {
    form.schema_payload.fields.push({
        key: `field_${Date.now().toString().slice(-4)}`,
        label: 'New Field',
        ui_component: 'text_input',
        grid_span: 1,
        placeholder: '',
        rules: ['nullable']
    });
};

const removeField = (index) => {
    form.schema_payload.fields.splice(index, 1);
};

const moveUp = (index) => {
    if (index > 0) {
        const item = form.schema_payload.fields.splice(index, 1)[0];
        form.schema_payload.fields.splice(index - 1, 0, item);
    }
};

const moveDown = (index) => {
    if (index < form.schema_payload.fields.length - 1) {
        const item = form.schema_payload.fields.splice(index, 1)[0];
        form.schema_payload.fields.splice(index + 1, 0, item);
    }
};

const saveBlueprint = () => {
    form.put(route('system.blueprints.update', props.schema.id));
};
</script>
