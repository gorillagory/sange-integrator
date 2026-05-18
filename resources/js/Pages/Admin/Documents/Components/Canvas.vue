<!-- resources/js/Pages/Admin/Documents/Components/Canvas.vue -->
<template>
    <div class="flex h-full min-h-0 flex-col">
        <div class="border-b border-slate-200 bg-white px-4 py-3">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <div class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">
                        Design Canvas
                    </div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">
                        Arrange blocks, rows, and sections before switching to print preview
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50"
                        @click="$emit('zoom-out')"
                    >
                        −
                    </button>

                    <div class="min-w-[60px] text-center text-xs font-semibold text-slate-600">
                        {{ Math.round(zoomLevel * 100) }}%
                    </div>

                    <button
                        type="button"
                        class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50"
                        @click="$emit('zoom-in')"
                    >
                        +
                    </button>
                </div>
            </div>
        </div>

        <div class="min-h-0 flex-1 overflow-auto bg-slate-100 p-6 lg:p-8">
            <div class="flex min-h-full items-start justify-center">
                <div
                    class="origin-top overflow-hidden rounded-2xl border border-slate-300 bg-white shadow-xl"
                    :style="pageShellStyle"
                    @click.stop="$emit('select-page')"
                >
                    <div class="h-full w-full" :style="sheetContentStyle">
                        <CanvasZone
                            label="Header"
                            zone="header"
                            :nodes="layoutVector?.header || []"
                            :is-preview-mode="isPreviewMode"
                        />

                        <CanvasZone
                            label="Body"
                            zone="body"
                            :nodes="layoutVector?.body || []"
                            :is-preview-mode="isPreviewMode"
                            with-top-border
                            empty-label="Drag blocks here to begin"
                        />

                        <CanvasZone
                            label="Footer"
                            zone="footer"
                            :nodes="layoutVector?.footer || []"
                            :is-preview-mode="isPreviewMode"
                            with-top-border
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { resolveDocumentFontCss } from '../Composables/document-builder/fontPresets';
import CanvasZone from './canvas/CanvasZone.vue';

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
});

defineEmits(['select-page', 'zoom-in', 'zoom-out']);

const pageShellStyle = computed(() => {
    const page = props.layoutVector?.page ?? {};
    const portrait = page.orientation !== 'landscape';
    const pagePresets = {
        A5: { width: 559, height: 794 },
        A4: { width: 794, height: 1123 },
        Letter: { width: 816, height: 1056 },
        Legal: { width: 816, height: 1344 },
    };
    const preset = pagePresets[page.size] || pagePresets.A4;
    const width = portrait ? preset.width : preset.height;
    const height = portrait ? preset.height : preset.width;

    return {
        width: `${width}px`,
        minHeight: `${height}px`,
        backgroundColor: page.backgroundColor || '#ffffff',
        transform: `scale(${props.zoomLevel})`,
        transformOrigin: 'top center',
    };
});

const sheetContentStyle = computed(() => ({
    padding: props.layoutVector?.page?.margins || '10mm',
    fontFamily: resolveDocumentFontCss(
        props.layoutVector?.page?.fontFamily,
        props.fontOptions.map((option) => ({
            value: option.value,
            cssFamily: option.css_family,
        })),
    ),
}));
</script>
