<!-- resources/js/Pages/Admin/Documents/Components/BuilderSidebar.vue -->
<template>
    <div class="flex min-h-0 flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 bg-white px-3 py-3">
            <div class="grid grid-cols-2 gap-2">
                <button
                    type="button"
                    class="rounded-2xl px-3 py-2 text-sm font-semibold transition"
                    :class="activeTab === 'blocks'
                        ? 'bg-[var(--brand-600)] text-white'
                        : 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-100'"
                    @click="$emit('update:active-tab', 'blocks')"
                >
                    Blocks
                </button>

                <button
                    type="button"
                    class="rounded-2xl px-3 py-2 text-sm font-semibold transition"
                    :class="activeTab === 'inspector'
                        ? 'bg-[var(--brand-600)] text-white'
                        : 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-100'"
                    @click="$emit('update:active-tab', 'inspector')"
                >
                    Inspector
                </button>
            </div>
        </div>

        <div class="flex min-h-0 flex-1 flex-col overflow-hidden">
            <div class="min-h-0 flex-1 overflow-hidden">
                <div v-show="activeTab === 'blocks'" class="h-full">
                    <Toolbox
                        :document-type="documentType"
                        :dictionaries="dictionaries"
                        @add-block="emitAddBlock"
                        @add-row="emitAddRow"
                        @start-drag-block="emitStartDragBlock"
                        @start-drag-row="emitStartDragRow"
                    />
                </div>

                <div v-show="activeTab === 'inspector'" class="h-full">
                    <Inspector
                        :active-node="activeNode"
                        :document-type="documentType"
                        :font-options="fontOptions"
                        :dictionary="activeDictionary"
                        :selected-node-ids="selectedNodeIds"
                        @update="$emit('touch')"
                        @delete-node="$emit('delete-selection')"
                    />
                </div>
            </div>

        </div>
    </div>
</template>

<script setup>
import Inspector from './Inspector.vue';
import Toolbox from './Toolbox.vue';

defineProps({
    activeTab: {
        type: String,
        default: 'blocks',
    },
    documentType: {
        type: String,
        default: 'invoice',
    },
    fontOptions: {
        type: Array,
        default: () => [],
    },
    dictionaries: {
        type: Object,
        default: () => ({}),
    },
    activeDictionary: {
        type: Object,
        default: () => ({}),
    },
    activeNode: {
        type: Object,
        default: null,
    },
    selectedNodeIds: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits([
    'update:active-tab',
    'add-block',
    'add-row',
    'start-drag-block',
    'start-drag-row',
    'touch',
    'delete-selection',
]);

function emitAddBlock(...args) {
    emit('add-block', ...args);
}

function emitAddRow(...args) {
    emit('add-row', ...args);
}

function emitStartDragBlock(...args) {
    emit('start-drag-block', ...args);
}

function emitStartDragRow(...args) {
    emit('start-drag-row', ...args);
}
</script>
