<!-- resources/js/Pages/Admin/Documents/Components/canvas/CanvasRowBlock.vue -->
<template>
    <div
        class="rounded-2xl border border-dashed border-slate-200/70 bg-transparent p-2"
        :style="rowStyle"
    >
        <div class="mb-2 flex items-center justify-between gap-3">
            <div class="text-[11px] font-semibold text-slate-500">
                {{ columnCount }} column{{ columnCount === 1 ? '' : 's' }}
            </div>

            <div class="text-[11px] font-mono text-slate-400">
                {{ spansLabel }}
            </div>
        </div>

        <div class="grid gap-3" :style="gridStyle">
            <CanvasColumnDropZone
                v-for="(column, index) in node.columns || []"
                :key="column.id || index"
                :row-id="node.id"
                :column="column"
                :column-index="index"
                :is-preview-mode="isPreviewMode"
            />
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import CanvasColumnDropZone from './CanvasColumnDropZone.vue';

const props = defineProps({
    node: {
        type: Object,
        required: true,
    },
    isPreviewMode: {
        type: Boolean,
        default: false,
    },
});

const columnCount = computed(() => (props.node.columns || []).length);

const spansLabel = computed(() => {
    return (props.node.columns || [])
        .map((column) => column.span || 12)
        .join(' / ');
});

const gridStyle = computed(() => {
    const columns = (props.node.columns || []).map((column) => {
        const span = Number(column.span || 12);
        return `minmax(0, ${span}fr)`;
    });

    return {
        gridTemplateColumns: columns.join(' '),
        gap: props.node?.styles?.gap || '16px',
        alignItems: props.node?.styles?.alignItems || 'start',
    };
});

const rowStyle = computed(() => ({
    padding: props.node?.styles?.padding || '0px',
    margin: props.node?.styles?.margin || '0px',
    backgroundColor: props.node?.styles?.backgroundColor || 'transparent',
    borderRadius: props.node?.styles?.borderRadius || '0px',
    border: props.node?.styles?.border || '0px solid transparent',
}));
</script>
