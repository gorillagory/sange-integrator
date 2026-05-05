<!-- resources/js/Pages/Admin/Documents/Components/BuilderPreviewPane.vue -->
<template>
    <div class="min-h-0 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <Canvas
            :layout-vector="layoutVector"
            :zoom-level="zoomLevel"
            :is-preview-mode="isPreviewMode"
            :preview-payload="previewPayload"
            @select-page="$emit('select-page')"
            @zoom-in="$emit('zoom-in')"
            @zoom-out="$emit('zoom-out')"
        />
    </div>
</template>

<script setup>
import Canvas from './Canvas.vue';

defineProps({
    layoutVector: {
        type: Object,
        required: true,
    },
    zoomLevel: {
        type: Number,
        default: 1,
    },
    isPreviewMode: {
        type: Boolean,
        default: false,
    },
    previewPayload: {
        type: Object,
        default: () => ({}),
    },
});

defineEmits(['select-page', 'zoom-in', 'zoom-out']);
</script>
