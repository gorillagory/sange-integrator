<!-- resources/js/Pages/Admin/Documents/Components/BuilderPreviewPane.vue -->
<template>
    <div class="min-h-0 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div v-if="isPreviewMode" class="flex h-full min-h-0 flex-col">
            <div class="border-b border-slate-200 bg-white px-4 py-3">
                <div class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">
                    Print Preview
                </div>
                <div class="mt-1 text-sm font-semibold text-slate-900">
                    This sheet uses the same compiled HTML contract as the generated PDF.
                </div>
            </div>

            <div class="min-h-0 flex-1 overflow-auto bg-slate-900/85 p-6 lg:p-8">
                <div v-if="previewLoading" class="flex min-h-full items-center justify-center text-sm font-semibold text-white/80">
                    Compiling live preview...
                </div>

                <div v-else-if="previewError" class="mx-auto max-w-2xl rounded-3xl border border-rose-200 bg-white p-6 text-sm text-rose-700 shadow-xl">
                    {{ previewError }}
                </div>

                <div v-else class="mx-auto w-full max-w-[1200px]">
                    <iframe
                        class="min-h-[1200px] w-full rounded-3xl bg-white shadow-2xl"
                        :srcdoc="compiledPreviewHtml || fallbackPreviewHtml"
                        title="Compiled document preview"
                    />
                </div>
            </div>
        </div>

        <Canvas
            v-else
            :layout-vector="layoutVector"
            :font-options="fontOptions"
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
import { computed } from 'vue';
import Canvas from './Canvas.vue';

const props = defineProps({
    layoutVector: {
        type: Object,
        required: true,
    },
    fontOptions: {
        type: Array,
        default: () => [],
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
    compiledPreviewHtml: {
        type: String,
        default: '',
    },
    previewLoading: {
        type: Boolean,
        default: false,
    },
    previewError: {
        type: String,
        default: '',
    },
});

defineEmits(['select-page', 'zoom-in', 'zoom-out']);

const fallbackPreviewHtml = computed(() => {
    const background = props.layoutVector?.page?.backgroundColor || '#ffffff';
    const fontFamily = props.fontOptions.find((option) => option.value === props.layoutVector?.page?.fontFamily)?.css_family || 'Helvetica, Arial, sans-serif';

    return `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <style>
        body { margin: 0; padding: 32px; background: #111827; font-family: ${fontFamily}; }
        .sheet { min-height: 1000px; background: ${background}; border-radius: 24px; box-shadow: 0 20px 60px rgba(15, 23, 42, 0.3); }
    </style>
</head>
<body>
    <div class="sheet"></div>
</body>
</html>`;
});
</script>
