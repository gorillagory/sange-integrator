<!-- resources/js/Pages/Admin/Documents/Components/canvas/CanvasNodeTree.vue -->
<template>
    <div
        class="node-shell"
        :class="shellClass"
        draggable="true"
        @click.stop="selectNode"
        @dragstart="builder?.startDragExistingNode?.(node.id, $event)"
    >
        <div class="node-toolbar">
            <div class="truncate text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">
                {{ node.type }}
            </div>

            <div v-if="!isPreviewMode" class="flex items-center gap-1">
                <button
                    type="button"
                    class="node-tool-btn"
                    title="Move up"
                    @click.stop="builder?.moveNodeUp?.(node.id)"
                >
                    ↑
                </button>

                <button
                    type="button"
                    class="node-tool-btn"
                    title="Move down"
                    @click.stop="builder?.moveNodeDown?.(node.id)"
                >
                    ↓
                </button>

                <button
                    type="button"
                    class="node-tool-btn"
                    title="Duplicate"
                    @click.stop="builder?.duplicateNode?.(node.id)"
                >
                    ⧉
                </button>

                <button
                    type="button"
                    class="node-tool-btn node-tool-btn-danger"
                    title="Delete"
                    @click.stop="builder?.deleteNode?.(node.id)"
                >
                    ✕
                </button>
            </div>
        </div>

        <CanvasRowBlock
            v-if="node.type === 'row'"
            :node="node"
            :is-preview-mode="isPreviewMode"
        />

        <CanvasBlockContent
            v-else
            :node="node"
        />
    </div>
</template>

<script setup>
import { computed, inject } from 'vue';
import CanvasBlockContent from './CanvasBlockContent.vue';
import CanvasRowBlock from './CanvasRowBlock.vue';

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

const builder = inject('documentBuilder', null);

const shellClass = computed(() => {
    const classes = [];

    if (builder?.selectedNodeIds?.value?.includes(props.node.id)) {
        classes.push('node-selected');
    }

    return classes;
});

function selectNode(event) {
    builder?.setActiveNode?.(props.node, event.metaKey || event.ctrlKey);
}
</script>

<style scoped>
.node-shell {
    position: relative;
    border: 1px solid transparent;
    border-radius: 0.9rem;
    background: transparent;
    padding: 0.35rem;
    transition: 0.15s ease;
}

.node-shell:hover {
    border-color: rgba(148, 163, 184, 0.5);
    background: rgba(255, 255, 255, 0.45);
}

.node-selected {
    border-color: var(--brand-500);
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.12);
    background: rgba(255, 255, 255, 0.78);
}

.node-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    margin-bottom: 0.45rem;
}

.node-tool-btn {
    border: 1px solid rgb(226 232 240);
    background: white;
    color: rgb(71 85 105);
    border-radius: 0.5rem;
    padding: 0.2rem 0.45rem;
    font-size: 11px;
    font-weight: 700;
    transition: 0.15s ease;
}

.node-tool-btn:hover {
    background: rgb(248 250 252);
}

.node-tool-btn-danger {
    border-color: rgb(254 205 211);
    color: rgb(225 29 72);
}
</style>
