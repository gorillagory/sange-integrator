<!-- resources/js/Pages/Admin/Documents/Components/canvas/CanvasColumnDropZone.vue -->
<template>
    <div
        class="rounded-2xl border border-dashed p-3 transition"
        :class="columnClass"
        @dragover.prevent="builder?.onDragOverColumn?.(rowId, column.id)"
        @dragleave="builder?.onDragLeaveColumn?.(rowId, column.id)"
        @drop.prevent="builder?.handleDropToColumn?.(rowId, column.id)"
    >
        <div class="mb-2 flex items-center justify-between gap-2">
            <div class="text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400">
                Column {{ columnIndex + 1 }}
            </div>
            <div class="text-[11px] font-mono text-slate-500">
                {{ column.span }}/12
            </div>
        </div>

        <div v-if="column.blocks?.length" class="space-y-2">
            <CanvasNodeTree
                v-for="child in column.blocks"
                :key="child.id"
                :node="child"
                :is-preview-mode="isPreviewMode"
            />
        </div>

        <div v-else class="rounded-xl bg-white/80 px-3 py-4 text-center text-xs text-slate-400">
            Drop block or nested row here
        </div>
    </div>
</template>

<script setup>
import { computed, inject } from 'vue';
import CanvasNodeTree from './CanvasNodeTree.vue';

const props = defineProps({
    rowId: {
        type: String,
        required: true,
    },
    column: {
        type: Object,
        required: true,
    },
    columnIndex: {
        type: Number,
        required: true,
    },
    isPreviewMode: {
        type: Boolean,
        default: false,
    },
});

const builder = inject('documentBuilder', null);

const columnClass = computed(() => {
    const active = builder?.dragOverTarget?.value === `column:${props.rowId}:${props.column.id}`;

    return active
        ? 'border-[var(--brand-500)] bg-[rgba(239,68,68,0.06)]'
        : 'border-slate-300 bg-slate-50';
});
</script>
