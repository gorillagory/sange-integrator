<template>
    <div class="w-full bg-white border-l border-gray-200 h-full flex flex-col shrink-0 relative z-10 shadow-[-4px_0_15px_-3px_rgba(0,0,0,0.05)]">

        <div class="px-4 py-3 border-b border-gray-200 flex justify-between items-center bg-gray-50">
            <h3 class="text-xs font-black text-gray-800 uppercase tracking-wider flex items-center gap-2 truncate">
                <svg class="w-4 h-4 text-[var(--brand-500)] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                <span>Inspector</span>
            </h3>
            <span v-if="activeNode && !activeNode.isPage" class="text-[9px] bg-gray-200 text-gray-700 px-2 py-0.5 rounded font-mono uppercase font-bold">{{ activeNode.type }}</span>
            <span v-else-if="activeNode?.isPage" class="text-[9px] bg-[var(--brand-100)] text-[var(--brand-700)] px-2 py-0.5 rounded font-mono uppercase font-bold">Document Page</span>
        </div>

        <div v-if="!activeNode" class="flex-1 flex flex-col items-center justify-center p-6 text-center">
            <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center mb-3">
                <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path></svg>
            </div>
            <p class="text-xs font-bold text-gray-500">No Element Selected</p>
            <p class="text-[10px] text-gray-400 mt-1">Click an element on the canvas to configure its properties.</p>
        </div>

        <template v-else>
            <div class="flex border-b border-gray-200">
                <button
                    @click="activeTab = 'design'"
                    :class="activeTab === 'design' ? 'border-[var(--brand-500)] text-[var(--brand-600)]' : 'border-transparent text-gray-500 hover:text-gray-700 bg-gray-50'"
                    class="flex-1 py-2.5 text-[10px] font-bold uppercase tracking-wider border-b-2 transition"
                >
                    Design & Layout
                </button>
                <button
                    v-if="activeNode.type === 'text'"
                    @click="activeTab = 'data'"
                    :class="activeTab === 'data' ? 'border-[var(--brand-500)] text-[var(--brand-600)]' : 'border-transparent text-gray-500 hover:text-gray-700 bg-gray-50'"
                    class="flex-1 py-2.5 text-[10px] font-bold uppercase tracking-wider border-b-2 transition"
                >
                    Data Tokens
                </button>
            </div>

            <div class="flex-1 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-200">
                <DesignTab
                    v-if="activeTab === 'design'"
                    :activeNode="activeNode"
                    @update="$emit('update')"
                />

                <DataTab
                    v-if="activeTab === 'data' && activeNode.type === 'text'"
                    :activeNode="activeNode"
                    :documentType="documentType"
                    @update="$emit('update')"
                />
            </div>
        </template>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import DesignTab from './Tabs/DesignTab.vue';
import DataTab from './Tabs/DataTab.vue';

const props = defineProps({
    activeNode: { type: Object, default: null },
    documentType: { type: String, default: 'invoice' }
});
const emit = defineEmits(['update']);

const activeTab = ref('design');

// Auto-switch back to Design tab if a non-text element is selected
watch(() => props.activeNode, (newNode) => {
    if (newNode?.type !== 'text' && activeTab.value === 'data') {
        activeTab.value = 'design';
    }
});
</script>
