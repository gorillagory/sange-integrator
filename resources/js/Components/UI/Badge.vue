<template>
    <span
        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border"
        :class="colorClasses"
    >
        <slot />
    </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    type: {
        type: String,
        default: 'gray', // success, danger, warning, brand, gray
    }
});

// Dynamic dictionary for clean, readable states
const colorClasses = computed(() => {
    const types = {
        success: 'bg-green-50 text-green-700 border-green-200',
        danger: 'bg-red-50 text-red-700 border-red-200',
        warning: 'bg-amber-50 text-amber-700 border-amber-200',
        brand: 'bg-[var(--brand-50)] text-[var(--brand-700)] border-[var(--brand-200)]',
        gray: 'bg-gray-50 text-gray-700 border-gray-200'
    };
    return types[props.type] || types.gray;
});
</script>
