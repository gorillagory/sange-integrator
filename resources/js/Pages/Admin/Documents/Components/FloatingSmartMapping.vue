<template>
    <div class="pointer-events-none fixed bottom-5 right-5 z-[90] flex max-w-[calc(100vw-1.5rem)] flex-col items-end gap-3">
        <button
            v-if="!open"
            type="button"
            class="pointer-events-auto inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white/95 px-4 py-3 text-sm font-semibold text-slate-700 shadow-xl backdrop-blur transition hover:bg-white"
            @click="$emit('update:open', true)"
        >
            <span class="text-[11px] font-black uppercase tracking-[0.18em] text-[var(--brand-700)]">Smart Mapping</span>
            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] text-slate-500">{{ variableCount }}</span>
        </button>

        <div
            v-else
            class="pointer-events-auto flex w-[380px] max-w-[calc(100vw-1.5rem)] flex-col overflow-hidden rounded-[28px] border border-slate-200 bg-white/95 shadow-2xl backdrop-blur"
        >
            <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
                <div>
                    <div class="text-[11px] font-black uppercase tracking-[0.2em] text-[var(--brand-700)]">
                        Smart Mapping
                    </div>
                    <div class="mt-1 text-sm text-slate-600">
                        Keep keys close while you build. Minimize it when you want the sheet clear.
                    </div>
                </div>

                <button
                    type="button"
                    class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-50"
                    @click="$emit('update:open', false)"
                >
                    Minimize
                </button>
            </div>

            <div class="max-h-[65vh] overflow-y-auto p-4">
                <DataTab
                    :active-node="activeNode"
                    :dictionary="dictionary"
                    @update="$emit('update')"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import DataTab from './Tabs/DataTab.vue';

const props = defineProps({
    open: {
        type: Boolean,
        default: true,
    },
    activeNode: {
        type: Object,
        default: null,
    },
    dictionary: {
        type: Object,
        default: () => ({}),
    },
});

defineEmits(['update:open', 'update']);

const variableCount = computed(() => {
    return Object.values(props.dictionary || {}).reduce((sum, group) => {
        return sum + (Array.isArray(group) ? group.length : 0);
    }, 0);
});
</script>
