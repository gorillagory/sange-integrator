<template>
    <div class="space-y-4">
        <div class="flex justify-between items-center mb-2">
            <h3 :class="headingClass" class="font-bold">{{ title }}</h3>
            <button @click.prevent="addField" :class="buttonClass" class="text-xs font-bold flex items-center gap-1 transition">+ Add Field</button>
        </div>

        <div v-if="modelValue.length === 0" :class="emptyStateClass" class="p-8 border-2 border-dashed rounded-2xl text-center text-sm font-medium">
            No fields added yet.
        </div>

        <VueDraggableNext :list="modelValue" handle=".handle" class="space-y-3" @change="recalculateOrder">
            <FieldEditor
                v-for="(field, index) in modelValue"
                :key="index"
                v-model="modelValue[index]"
                :availableParents="availableParentKeys(index)"
                :otherKeys="getOtherKeys(index)"
                :surface="surface"
                @remove="removeField(index)"
            />
        </VueDraggableNext>
    </div>
</template>

<script setup>
import { VueDraggableNext } from 'vue-draggable-next';
import FieldEditor from './FieldEditor.vue';

const props = defineProps({
    modelValue: { type: Array, required: true },
    surface: { type: String, default: 'light' },
    title: { type: String, default: '2. Schema Fields' },
});

const headingClass = props.surface === 'dark' ? 'text-white' : 'text-gray-900';
const buttonClass = props.surface === 'dark'
    ? 'text-indigo-300 hover:text-white'
    : 'text-[var(--brand-600)] hover:text-[var(--brand-700)]';
const emptyStateClass = props.surface === 'dark'
    ? 'border-white/10 bg-[#131d31] text-gray-400'
    : 'border-gray-200 bg-gray-50 text-gray-500';

const recalculateOrder = () => {
    props.modelValue.forEach((f, i) => { f.order = i; });
};

const addField = () => {
    props.modelValue.push({
        key: '', label: '', type: 'string', ui_component: 'text_input', grid_span: 1, rules: [],
        is_array: false, placeholder: '', text_transform: 'none', order: props.modelValue.length,
        api_endpoint: '', cascade_parent: '', file_max_size: 5, file_max_count: 1, file_types: '',
        file_preview: false, _show_advanced: false, _is_minimized: false, _key_manually_edited: false
    });
};

const removeField = (index) => {
    props.modelValue.splice(index, 1);
    recalculateOrder();
};

const getOtherKeys = (currentIndex) => props.modelValue.filter((_, i) => i !== currentIndex).map(f => f.key).filter(k => k);
const availableParentKeys = (currentIndex) => getOtherKeys(currentIndex);
</script>
