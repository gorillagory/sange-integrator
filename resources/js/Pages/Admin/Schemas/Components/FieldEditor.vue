<template>
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm transition hover:border-[var(--brand-300)]" :class="field._is_minimized ? 'p-3' : 'p-5 relative group'">

        <div v-if="!field._is_minimized">
            <div class="absolute top-4 right-4 flex gap-2">
                <button @click.prevent="field._show_advanced = !field._show_advanced" class="text-gray-400 hover:text-[var(--brand-600)]" title="Advanced Settings">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                </button>
                <button @click.prevent="$emit('remove')" class="text-gray-400 hover:text-red-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>
            </div>

            <div class="grid grid-cols-12 gap-4 mb-4 pr-12">
                <div class="col-span-1 flex items-center justify-center cursor-move handle opacity-30 hover:opacity-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path></svg>
                </div>
                <div class="col-span-4">
                    <label class="block text-[10px] font-bold text-[var(--brand-600)] uppercase mb-1">UI Label</label>
                    <input v-model="field.label" @input="generateFieldKey" type="text" class="w-full bg-gray-50 border border-gray-200 rounded-md px-3 py-1.5 text-sm" placeholder="e.g. Passport Number">
                </div>
                <div class="col-span-4">
                    <label class="block text-[10px] font-bold uppercase mb-1" :class="isDuplicateKey ? 'text-red-500' : 'text-gray-500'">JSON Key</label>
                    <input v-model="field.key" @input="field._key_manually_edited = true" type="text" class="w-full font-mono rounded-md px-3 py-1.5 text-sm transition" :class="isDuplicateKey ? 'bg-red-50 border border-red-300 text-red-600' : 'bg-gray-100 border border-gray-200 text-gray-600'" placeholder="passport_number">
                    <p v-if="isDuplicateKey" class="text-[9px] text-red-500 mt-1 font-bold">Error: Duplicate Key</p>
                </div>
                <div class="col-span-3">
                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Data Type</label>
                    <select v-model="field.type" @change="syncUIComponent" class="w-full bg-gray-50 border border-gray-200 rounded-md px-2 py-1.5 text-xs">
                        <option value="string">String (Text)</option>
                        <option value="number">Number (Int)</option>
                        <option value="float">Float (Decimal)</option>
                        <option value="email">Email</option>
                        <option value="date">Date</option>
                        <option value="datetime">Date & Time</option>
                        <option value="time">Time</option>
                        <option value="file">File Upload</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 pt-4 border-t border-gray-100 pl-12">
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">UI Component</label>
                    <select v-model="field.ui_component" class="w-full bg-gray-50 border border-gray-200 rounded-md px-2 py-1.5 text-xs">
                        <option value="text_input">Text Input</option>
                        <option value="textarea">Textarea</option>
                        <option value="date">Date Picker</option>
                        <option value="datetime">Datetime Picker</option>
                        <option value="time">Time Picker</option>
                        <option value="file">File Upload</option>
                        <option value="typeahead">Typeahead API</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Grid Width</label>
                    <select v-model="field.grid_span" class="w-full bg-gray-50 border border-gray-200 rounded-md px-2 py-1.5 text-xs">
                        <option :value="1">Half Width (1)</option>
                        <option :value="2">Full Width (2)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Requirement</label>
                    <div class="flex items-center h-8 gap-2">
                        <input type="checkbox" :checked="field.rules.includes('required')" @change="toggleRequired($event.target.checked)" class="text-[var(--brand-600)] rounded border-gray-300">
                        <span class="text-xs text-gray-600 font-medium">Required</span>
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-[var(--brand-600)] uppercase mb-1">Structure</label>
                    <div class="flex items-center h-8 gap-2">
                        <input type="checkbox" v-model="field.is_array" class="text-[var(--brand-600)] rounded border-gray-300">
                        <span class="text-xs text-gray-600 font-medium" :class="{'text-[var(--brand-600)] font-bold': field.is_array}">List Array</span>
                    </div>
                </div>
            </div>

            <div v-if="field._show_advanced" class="mt-4 pt-4 border-t border-gray-200 bg-gray-50 rounded-lg p-4 pl-12 space-y-4">
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-[9px] font-bold text-gray-500 uppercase mb-1">Placeholder Text</label>
                        <input v-model="field.placeholder" type="text" class="w-full bg-white border border-gray-200 rounded text-xs px-2 py-1" placeholder="Enter value...">
                    </div>
                    <div>
                        <label class="block text-[9px] font-bold text-gray-500 uppercase mb-1">Text Transform</label>
                        <select v-model="field.text_transform" class="w-full bg-white border border-gray-200 rounded text-xs px-2 py-1">
                            <option value="none">Normal</option>
                            <option value="uppercase">ALL CAPS</option>
                            <option value="lowercase">lowercase</option>
                            <option value="capitalize">Capitalize Words</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[9px] font-bold text-gray-500 uppercase mb-1">Sort Order</label>
                        <input v-model.number="field.order" type="number" class="w-full bg-white border border-gray-200 rounded text-xs px-2 py-1">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[9px] font-bold text-gray-500 uppercase mb-1">API Endpoint (Typeahead)</label>
                        <input v-model="field.api_endpoint" type="text" class="w-full bg-white border border-gray-200 rounded text-xs font-mono px-2 py-1" placeholder="/api/v1/search/clients">
                    </div>
                    <div>
                        <label class="block text-[9px] font-bold text-gray-500 uppercase mb-1">Cascade From (Parent Key)</label>
                        <select v-model="field.cascade_parent" class="w-full bg-white border border-gray-200 rounded text-xs px-2 py-1">
                            <option value="">None</option>
                            <option v-for="opt in availableParents" :key="opt" :value="opt">{{ opt }}</option>
                        </select>
                    </div>
                </div>

                <div v-if="field.type === 'file'" class="grid grid-cols-4 gap-4 p-3 bg-blue-50 border border-blue-100 rounded">
                    <div>
                        <label class="block text-[9px] font-bold text-blue-700 uppercase mb-1">Max Size (MB)</label>
                        <input v-model.number="field.file_max_size" type="number" class="w-full bg-white border border-blue-200 rounded text-xs px-2 py-1">
                    </div>
                    <div>
                        <label class="block text-[9px] font-bold text-blue-700 uppercase mb-1">Max Files</label>
                        <input v-model.number="field.file_max_count" type="number" class="w-full bg-white border border-blue-200 rounded text-xs px-2 py-1">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-[9px] font-bold text-blue-700 uppercase mb-1">Allowed Types (MIME)</label>
                        <input v-model="field.file_types" type="text" class="w-full bg-white border border-blue-200 rounded text-xs font-mono px-2 py-1" placeholder="image/jpeg, application/pdf">
                    </div>
                    <div class="col-span-4 flex items-center gap-2 mt-1">
                        <input type="checkbox" v-model="field.file_preview" class="text-blue-600 rounded border-blue-300">
                        <span class="text-xs text-blue-800 font-medium">Enable In-Browser File Preview</span>
                    </div>
                </div>
            </div>

            <div class="mt-5 pt-4 border-t border-gray-100 flex justify-end">
                <button
                    @click.prevent="field._is_minimized = true"
                    :disabled="!field.label || !field.key || isDuplicateKey"
                    class="px-5 py-2 bg-gray-900 text-white text-xs font-bold rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-800 transition flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Confirm & Minimize
                </button>
            </div>
        </div>

        <div v-else class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="cursor-move handle opacity-30 hover:opacity-100 px-2 py-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path></svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-bold text-gray-900">{{ field.label || 'Unnamed Attribute' }}</span>
                        <span class="text-[10px] font-mono px-1.5 py-0.5 rounded" :class="isDuplicateKey ? 'bg-red-100 text-red-600 font-bold' : 'bg-gray-100 text-gray-400'">{{ field.key }}</span>
                        <span v-if="isDuplicateKey" class="text-[9px] px-1.5 py-0.5 rounded bg-red-600 text-white font-bold uppercase">DUPLICATE</span>
                    </div>
                    <div class="flex items-center gap-1.5 mt-1">
                        <span class="text-[9px] px-1.5 py-0.5 rounded border border-gray-200 text-gray-500 font-medium uppercase">{{ field.type }}</span>
                        <span class="text-[9px] px-1.5 py-0.5 rounded border border-gray-200 text-gray-500 font-medium uppercase">{{ field.ui_component.replace('_', ' ') }}</span>
                        <span v-if="field.rules.includes('required')" class="text-[9px] px-1.5 py-0.5 rounded bg-red-50 border border-red-100 text-red-600 font-bold uppercase">Required</span>
                        <span v-if="field.is_array" class="text-[9px] px-1.5 py-0.5 rounded bg-[var(--brand-50)] border border-[var(--brand-100)] text-[var(--brand-600)] font-bold uppercase">List</span>
                        <span v-if="field.api_endpoint" class="text-[9px] px-1.5 py-0.5 rounded bg-purple-50 border border-purple-100 text-purple-600 font-bold uppercase">API</span>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-1 pr-2">
                <button @click.prevent="field._is_minimized = false" class="text-[10px] font-bold text-[var(--brand-600)] hover:bg-[var(--brand-50)] px-3 py-1.5 rounded-lg transition border border-transparent hover:border-[var(--brand-100)]">
                    Edit
                </button>
                <button @click.prevent="$emit('remove')" class="text-gray-400 hover:text-red-500 hover:bg-red-50 p-1.5 rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>
            </div>
        </div>

    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: { type: Object, required: true },
    availableParents: { type: Array, default: () => [] },
    otherKeys: { type: Array, default: () => [] }
});

const emit = defineEmits(['update:modelValue', 'remove']);

const field = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val)
});

const isDuplicateKey = computed(() => {
    return field.value.key && props.otherKeys.includes(field.value.key);
});

const generateFieldKey = () => {
    if (!field.value._key_manually_edited) {
        let baseKey = field.value.label.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/(^_|_$)/g, '');

        if (!baseKey) {
            field.value.key = '';
            return;
        }

        let finalKey = baseKey;
        let counter = 1;

        while (props.otherKeys.includes(finalKey)) {
            finalKey = `${baseKey}_${counter}`;
            counter++;
        }

        field.value.key = finalKey;
    }
};

const syncUIComponent = () => {
    if (field.value.type === 'file') field.value.ui_component = 'file';
    if (field.value.type === 'date') field.value.ui_component = 'date';
    if (field.value.type === 'datetime') field.value.ui_component = 'datetime';
    if (field.value.type === 'time') field.value.ui_component = 'time';
};

const toggleRequired = (isRequired) => {
    if (isRequired) {
        if (!field.value.rules.includes('required')) field.value.rules.push('required');
    } else {
        field.value.rules = field.value.rules.filter(r => r !== 'required');
    }
};
</script>
