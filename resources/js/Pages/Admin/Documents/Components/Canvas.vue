<template>
    <div class="flex-1 bg-gray-200/80 p-8 overflow-y-auto h-[800px] flex justify-center items-start overflow-x-auto relative">

        <div :style="{ transform: `scale(${zoom})`, transformOrigin: 'top center', transition: 'transform 0.2s ease' }" class="pb-32 w-full flex justify-center">

            <div
                class="transition-all duration-300 relative flex flex-col overflow-hidden"
                :class="[pageDimensions, isPreview ? 'shadow-sm' : 'shadow-2xl']"
                :style="{ padding: page.margins, backgroundColor: page.backgroundColor || '#ffffff' }"
                @click.self="$emit('selectPage')"
            >
                <div v-if="activeNodeId === 'page' && !isPreview" class="absolute inset-0 border-4 border-[var(--brand-500)] pointer-events-none z-50"></div>

                <div
                    v-if="page.watermarkText"
                    class="absolute inset-0 flex items-center justify-center pointer-events-none z-0"
                    :style="{ opacity: page.watermarkOpacity || 0.1 }"
                >
                    <span
                        class="font-black uppercase tracking-widest transform -rotate-45 whitespace-nowrap"
                        :style="{ fontSize: '8rem', color: page.watermarkColor || '#e5e7eb' }"
                    >
                        {{ page.watermarkText }}
                    </span>
                </div>

                <div class="flex-1 flex flex-col relative z-10 w-full h-full gap-2">

                    <div class="relative w-full min-h-[60px] group/zone">
                        <div v-if="!isPreview && header.length === 0" class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <span class="text-[10px] font-bold text-blue-400/50 uppercase tracking-widest">Header Zone (Repeats Every Page)</span>
                        </div>
                        <VueDraggableNext :list="header" group="blocks" :disabled="isPreview" class="w-full min-h-[60px]" :class="!isPreview ? 'border-2 border-dashed border-blue-200 hover:bg-blue-50/30 transition' : ''" @change="$emit('update')">
                            <CanvasNode v-for="node in header" :key="node.id" :node="node" />
                        </VueDraggableNext>
                    </div>

                    <div class="relative w-full flex-1 group/zone">
                        <div v-if="!isPreview && body.length === 0" class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <span class="text-[10px] font-bold text-emerald-400/50 uppercase tracking-widest">Body Zone (Flows downwards)</span>
                        </div>
                        <VueDraggableNext :list="body" group="blocks" :disabled="isPreview" class="w-full h-full min-h-[200px]" :class="!isPreview ? 'border-2 border-dashed border-emerald-200 hover:bg-emerald-50/30 transition' : ''" @change="$emit('update')">
                            <CanvasNode v-for="node in body" :key="node.id" :node="node" />
                        </VueDraggableNext>
                    </div>

                    <div class="relative w-full min-h-[60px] group/zone mt-auto">
                        <div v-if="!isPreview && footer.length === 0" class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <span class="text-[10px] font-bold text-purple-400/50 uppercase tracking-widest">Footer Zone (Repeats Every Page)</span>
                        </div>
                        <VueDraggableNext :list="footer" group="blocks" :disabled="isPreview" class="w-full min-h-[60px]" :class="!isPreview ? 'border-2 border-dashed border-purple-200 hover:bg-purple-50/30 transition' : ''" @change="$emit('update')">
                            <CanvasNode v-for="node in footer" :key="node.id" :node="node" />
                        </VueDraggableNext>
                    </div>

                </div>

            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { VueDraggableNext } from 'vue-draggable-next';
import CanvasNode from './CanvasNode.vue';

const props = defineProps({
    page: { type: Object, required: true },
    // NEW: Accepts the 3 specific zone arrays
    header: { type: Array, required: true },
    body: { type: Array, required: true },
    footer: { type: Array, required: true },
    activeNodeId: { type: [String, Number], default: null },
    zoom: { type: Number, default: 1 },
    isPreview: { type: Boolean, default: false }
});

defineEmits(['update', 'selectPage']);

// Dynamically calculates the Tailwind classes for proper mm printing dimensions
const pageDimensions = computed(() => {
    const s = props.page.size || 'A4';
    const o = props.page.orientation || 'portrait';

    if (s === 'A4' && o === 'portrait') return 'w-[210mm] min-h-[297mm]';
    if (s === 'A4' && o === 'landscape') return 'w-[297mm] min-h-[210mm]';

    if (s === 'A5' && o === 'portrait') return 'w-[148mm] min-h-[210mm]';
    if (s === 'A5' && o === 'landscape') return 'w-[210mm] min-h-[148mm]';

    if (s === 'Letter' && o === 'portrait') return 'w-[216mm] min-h-[279mm]';
    if (s === 'Letter' && o === 'landscape') return 'w-[279mm] min-h-[216mm]';

    return 'w-[210mm] min-h-[297mm]';
});
</script>
