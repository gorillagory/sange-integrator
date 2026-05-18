<template>
    <div class="space-y-6 sticky top-8">

        <div :class="previewCardClass" class="rounded-2xl overflow-hidden shadow-inner">
            <div :class="previewHeaderClass" class="px-4 py-2 border-b">
                <span :class="previewHeaderTextClass" class="text-[10px] font-bold uppercase tracking-wider">Operator Form Preview</span>
            </div>
            <div class="p-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div v-for="field in sortedFields" :key="field.key" :class="field.grid_span === 2 || field.ui_component === 'textarea' ? 'md:col-span-2' : 'md:col-span-1'">
                        <label :class="fieldLabelClass" class="block text-[10px] font-bold uppercase mb-1 flex justify-between items-center">
                            <span>
                                {{ field.label || 'Label' }}
                                <span v-if="field.rules.includes('required')" class="text-red-500">*</span>
                            </span>
                            <div class="flex gap-1">
                                <span v-if="field.api_endpoint" :class="apiBadgeClass" class="text-[9px] px-1.5 py-0.5 rounded" title="Connected to API">API</span>
                                <span v-if="field.is_array" :class="listBadgeClass" class="text-[9px] px-1.5 py-0.5 rounded">List</span>
                            </div>
                        </label>

                        <div v-if="field.ui_component === 'file'" :class="fileDropClass" class="w-full border border-dashed rounded-lg px-3 py-4 text-xs text-center flex flex-col items-center justify-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                            Drag & Drop {{ field.file_max_count > 1 ? 'Files' : 'File' }}
                        </div>
                        <textarea v-else-if="field.ui_component === 'textarea'" rows="2" disabled :class="previewInputClass" class="w-full rounded-lg px-3 py-2 text-sm opacity-70" :placeholder="field.placeholder || '...'"></textarea>

                        <input v-else
                               :type="getNativeInputType(field.ui_component)"
                               disabled
                               class="w-full rounded-lg px-3 py-2 text-sm opacity-70"
                               :class="[previewInputClass, { uppercase: field.text_transform === 'uppercase', lowercase: field.text_transform === 'lowercase' }]"
                               :placeholder="field.placeholder || '...'">
                    </div>
                </div>
            </div>
        </div>

        <div :class="jsonCardClass" class="rounded-2xl shadow-xl overflow-hidden">
            <div :class="jsonHeaderClass" class="px-4 py-2 border-b flex justify-between items-center">
                <span class="text-[10px] font-bold uppercase tracking-wider" :class="jsonHeaderTextClass">Compiled Vector JSON</span>
            </div>
            <pre :class="jsonBodyClass" class="p-4 text-xs font-mono overflow-x-auto max-h-96 scrollbar-thin">{{ compiledJson }}</pre>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    fields: { type: Array, required: true },
    compiledJson: { type: String, required: true },
    surface: { type: String, default: 'light' },
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

const isDark = computed(() => props.surface === 'dark');
const previewCardClass = computed(() => isDark.value ? 'bg-[#111827] border border-white/10' : 'bg-gray-50 border border-gray-200');
const previewHeaderClass = computed(() => isDark.value ? 'bg-white/5 border-white/10' : 'bg-gray-200 border-gray-300');
const previewHeaderTextClass = computed(() => isDark.value ? 'text-gray-300' : 'text-gray-500');
const fieldLabelClass = computed(() => isDark.value ? 'text-gray-400' : 'text-gray-500');
const apiBadgeClass = computed(() => isDark.value ? 'text-purple-200 bg-purple-500/15' : 'text-purple-600 bg-purple-100');
const listBadgeClass = computed(() => isDark.value ? 'text-indigo-200 bg-indigo-500/15' : 'text-[var(--brand-500)] bg-[var(--brand-50)]');
const fileDropClass = computed(() => isDark.value ? 'bg-[#0f172a] border-white/10 text-gray-500' : 'bg-gray-100 border-gray-300 text-gray-400');
const previewInputClass = computed(() => isDark.value ? 'bg-[#0f172a] border border-white/10 text-gray-100' : 'bg-white border border-gray-300 text-gray-900');
const jsonCardClass = computed(() => isDark.value ? 'bg-[#0b1220] border border-white/10' : 'bg-gray-900 border border-gray-700');
const jsonHeaderClass = computed(() => isDark.value ? 'bg-black/30 border-white/10' : 'bg-black/50 border-gray-800');
const jsonHeaderTextClass = computed(() => 'text-emerald-400');
const jsonBodyClass = computed(() => isDark.value ? 'text-gray-300 scrollbar-thumb-white/10' : 'text-gray-300 scrollbar-thumb-gray-700');
</script>
