<template>
    <div class="space-y-6 sticky top-8">

        <div class="bg-gray-50 border border-gray-200 rounded-2xl overflow-hidden shadow-inner">
            <div class="bg-gray-200 px-4 py-2 border-b border-gray-300">
                <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Agent UI Preview</span>
            </div>
            <div class="p-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div v-for="field in sortedFields" :key="field.key" :class="field.grid_span === 2 || field.ui_component === 'textarea' ? 'md:col-span-2' : 'md:col-span-1'">
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1 flex justify-between items-center">
                            <span>
                                {{ field.label || 'Label' }}
                                <span v-if="field.rules.includes('required')" class="text-red-500">*</span>
                            </span>
                            <div class="flex gap-1">
                                <span v-if="field.api_endpoint" class="text-[9px] text-purple-600 bg-purple-100 px-1.5 py-0.5 rounded" title="Connected to API">API</span>
                                <span v-if="field.is_array" class="text-[9px] text-[var(--brand-500)] bg-[var(--brand-50)] px-1.5 py-0.5 rounded">List</span>
                            </div>
                        </label>

                        <div v-if="field.ui_component === 'file'" class="w-full bg-gray-100 border border-gray-300 border-dashed rounded-lg px-3 py-4 text-xs text-gray-400 text-center flex flex-col items-center justify-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                            Drag & Drop {{ field.file_max_count > 1 ? 'Files' : 'File' }}
                        </div>
                        <textarea v-else-if="field.ui_component === 'textarea'" rows="2" disabled class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm opacity-70" :placeholder="field.placeholder || '...'"></textarea>

                        <input v-else
                               :type="getNativeInputType(field.ui_component)"
                               disabled
                               class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm opacity-70"
                               :class="{'uppercase': field.text_transform === 'uppercase', 'lowercase': field.text_transform === 'lowercase'}"
                               :placeholder="field.placeholder || '...'">
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-gray-900 rounded-2xl border border-gray-700 shadow-xl overflow-hidden">
            <div class="bg-black/50 px-4 py-2 border-b border-gray-800 flex justify-between items-center">
                <span class="text-[10px] font-bold text-emerald-400 uppercase tracking-wider">Compiled JSON Vector</span>
            </div>
            <pre class="p-4 text-xs text-gray-300 font-mono overflow-x-auto max-h-96 scrollbar-thin scrollbar-thumb-gray-700">{{ compiledJson }}</pre>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    fields: { type: Array, required: true },
    compiledJson: { type: String, required: true }
});

const sortedFields = computed(() => {
    return [...props.fields].sort((a, b) => a.order - b.order);
});

// Maps custom UI Component tags to Native HTML5 Input Types
const getNativeInputType = (uiComponent) => {
    if (uiComponent === 'date') return 'date';
    if (uiComponent === 'datetime') return 'datetime-local';
    if (uiComponent === 'time') return 'time';
    return 'text';
};
</script>
