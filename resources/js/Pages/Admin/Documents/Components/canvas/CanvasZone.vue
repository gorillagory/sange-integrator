<!-- resources/js/Pages/Admin/Documents/Components/canvas/CanvasZone.vue -->
<template>
    <div>
        <div
            class="zone-label"
            :class="{ 'border-t border-dashed border-slate-200': withTopBorder }"
        >
            {{ label }}
        </div>

        <div
            class="zone-area"
            :class="zoneClass"
            @dragover.prevent="builder?.onDragOverZone(zone)"
            @dragleave="builder?.onDragLeaveZone(zone)"
            @drop.prevent="builder?.handleDropToZone(zone)"
        >
            <div v-if="nodes.length" class="space-y-2">
                <CanvasNodeTree
                    v-for="node in nodes"
                    :key="node.id"
                    :node="node"
                    :is-preview-mode="isPreviewMode"
                />
            </div>

            <div v-else class="empty-zone">
                {{ emptyLabel }}
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, inject } from 'vue';
import CanvasNodeTree from './CanvasNodeTree.vue';

const props = defineProps({
    label: {
        type: String,
        required: true,
    },
    zone: {
        type: String,
        required: true,
    },
    nodes: {
        type: Array,
        default: () => [],
    },
    isPreviewMode: {
        type: Boolean,
        default: false,
    },
    withTopBorder: {
        type: Boolean,
        default: false,
    },
    emptyLabel: {
        type: String,
        default: 'Drop blocks here',
    },
});

const builder = inject('documentBuilder', null);

const zoneClass = computed(() => {
    if (!builder?.dragOverTarget?.value) {
        return '';
    }

    return builder.dragOverTarget.value === `zone:${props.zone}`
        ? 'zone-active'
        : '';
});
</script>

<style scoped>
.zone-label {
    padding: 0.85rem 1rem;
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.18em;
    color: rgb(148 163 184);
    border-bottom: 1px dashed rgb(226 232 240);
}

.zone-area {
    padding: 1rem;
    transition: 0.15s ease;
}

.zone-active {
    background: rgba(239, 68, 68, 0.06);
    outline: 2px dashed var(--brand-500);
    outline-offset: -6px;
}

.empty-zone {
    border: 1px dashed rgb(203 213 225);
    border-radius: 1rem;
    background: rgb(248 250 252);
    padding: 1rem;
    text-align: center;
    font-size: 0.8rem;
    color: rgb(148 163 184);
}
</style>
