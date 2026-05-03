<template>
    <div
        class="relative group/node transition-all rounded w-full"
        :class="[
            !isPreview && isActive ? 'ring-2 ring-[var(--brand-400)] z-40 shadow-sm' : '',
            !isPreview && !isActive ? 'hover:ring-1 hover:ring-blue-200 hover:z-30' : '',
            !isPreview && node.type === 'row' ? 'border border-dashed border-gray-300' : ''
        ]"
        :style="{
            padding: node.styles?.padding,
            margin: node.styles?.margin,
            marginTop: node.styles?.marginTop,
            marginBottom: node.styles?.marginBottom,
            backgroundColor: node.styles?.backgroundColor,
            borderRadius: node.styles?.borderRadius,
            border: node.styles?.border
        }"
        @click.stop="selectNode($event)"
    >
        <div v-if="!isPreview && isActive" class="absolute -top-3 -right-3 bg-[var(--brand-600)] text-white text-[10px] flex rounded-lg shadow-xl z-50 overflow-hidden">
            <div class="px-2 py-1 font-bold cursor-move handle uppercase tracking-wider">{{ node.type === 'row' ? 'Row' : node.type }}</div>
            <button @click.stop="removeNode" class="px-2 py-1 bg-red-500 hover:bg-red-400 border-l border-white/20 transition">Del</button>
        </div>

        <div v-if="node.type === 'row'" class="grid grid-cols-12 w-full" :style="{ gap: node.styles.gap, alignItems: node.styles.alignItems }">

            <div
                v-for="col in node.columns"
                :key="col.id"
                class="flex flex-col min-h-[40px] transition-colors rounded relative"
                :class="[
                    getColClass(col.span),
                    !isPreview ? 'border border-dashed border-gray-200/50 hover:bg-blue-50/20' : ''
                ]"
            >
                <div v-if="col.blocks.length === 0 && !isPreview" class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <span class="text-[9px] font-bold text-gray-300 uppercase tracking-widest bg-white/50 px-1 rounded">Col {{ col.span }}/12</span>
                </div>

                <VueDraggableNext :list="col.blocks" group="blocks" :disabled="isPreview" class="flex-1 w-full min-h-[40px] p-1 z-10 flex flex-col" @change="forceUpdate">
                    <CanvasNode v-for="child in col.blocks" :key="child.id" :node="child" />
                </VueDraggableNext>
            </div>
        </div>

        <div v-else class="w-full break-words outline-none" :style="node.styles">

            <div v-if="node.type === 'text'" :style="{ textAlign: node.styles.textAlign, color: node.styles.color, fontSize: node.styles.fontSize, fontWeight: node.styles.fontWeight }">
                <span v-if="node.data_key && !isPreview" class="bg-blue-100 text-blue-800 px-1 rounded inline-block font-mono">{{ '{ ' + node.data_key + ' }' }}</span>
                <span v-else-if="node.data_key && isPreview">{{ node.content || '{ ' + node.data_key + ' }' }}</span>
                <span v-else>{{ node.content || (isPreview ? '' : 'Empty Text') }}</span>
            </div>

            <div v-else-if="node.type === 'list'" :style="{ color: node.styles.color, fontSize: node.styles.fontSize }">
                <ul :style="{ listStyleType: node.styles.listStyleType, paddingLeft: node.styles.paddingLeft }">
                    <li v-if="!isPreview"><span class="bg-blue-100 text-blue-800 px-1 rounded inline-block font-mono text-[10px]">{{ '{ loop: ' + node.data_key + ' }' }}</span></li>
                    <li v-else>{{ '{ loop: ' + node.data_key + ' }' }}</li>
                    <li>{{ node.content || 'Item 2' }}</li>
                    <li>{{ node.content || 'Item 3' }}</li>
                </ul>
            </div>

            <div v-else-if="node.type === 'divider'" class="w-full" :style="{ height: node.styles.height, backgroundColor: node.styles.backgroundColor }"></div>

            <div v-else-if="node.type === 'image'" class="flex w-full" :style="{ justifyContent: node.styles.textAlign === 'center' ? 'center' : (node.styles.textAlign === 'right' ? 'flex-end' : 'flex-start') }">
                <div :class="!isPreview && !node.url ? 'bg-gray-100 border border-gray-200 flex items-center justify-center text-gray-400' : ''" :style="{ width: node.styles.width, height: node.url ? 'auto' : (node.styles.height || '100px') }">
                    <img v-if="node.url" :src="node.url" class="w-full h-full object-contain" />
                    <span v-else-if="!isPreview" class="text-[10px] font-bold uppercase">Image Placeholder</span>
                </div>
            </div>

            <div v-else-if="node.type === 'table'" class="w-full border text-[10px] text-gray-500 font-bold uppercase flex justify-between" :class="isPreview ? 'border-transparent bg-transparent' : 'border-gray-200 bg-gray-50 p-2'">
                <span>Table Loop: {{ node.data_key }}</span>
                <span>{{ node.columns.length }} Cols</span>
            </div>

            <div v-else-if="node.type === 'spacer'" class="w-full relative" :style="{ height: node.styles.height }">
                <div v-if="!isPreview && isActive" class="absolute inset-0 bg-blue-500/10 border border-blue-500/30 flex items-center justify-center text-[10px] text-blue-500 font-bold uppercase tracking-widest">Spacer ({{ node.styles.height }})</div>
            </div>

            <div v-else-if="node.type === 'page_break'" class="w-full relative flex items-center justify-center py-4" :class="isPreview ? 'opacity-0' : 'opacity-50'">
                <div class="w-full border-t-2 border-dashed border-red-400"></div>
                <div class="absolute bg-white px-2 text-[9px] font-black text-red-500 uppercase tracking-widest flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    Page Break
                </div>
            </div>

        </div>
    </div>
</template>

<script setup>
import { inject, computed } from 'vue';
import { VueDraggableNext } from 'vue-draggable-next';
import CanvasNode from './CanvasNode.vue';

const props = defineProps({ node: Object });
const builder = inject('documentBuilder');

const isPreview = builder.isPreviewMode;

// FIXED: Now checks if this node's ID is inside the selectedNodeIds array
const isActive = computed(() => builder.selectedNodeIds.value.includes(props.node.id));

// Passes the raw event payload so we can track the Shift key
const selectNode = (event) => builder.setActiveNode(props.node, event.shiftKey);
const removeNode = () => builder.removeNode(props.node.id);
const forceUpdate = () => builder.forceUpdate();

const getColClass = (span) => {
    const spanMap = {
        1: 'col-span-1', 2: 'col-span-2', 3: 'col-span-3', 4: 'col-span-4',
        5: 'col-span-5', 6: 'col-span-6', 7: 'col-span-7', 8: 'col-span-8',
        9: 'col-span-9', 10: 'col-span-10', 11: 'col-span-11', 12: 'col-span-12',
    };
    return spanMap[span] || 'col-span-12';
};
</script>
