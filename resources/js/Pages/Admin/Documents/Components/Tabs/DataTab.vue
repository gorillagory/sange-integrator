<template>
    <div class="space-y-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-4">
            <h4 class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">
                Smart Mapping
            </h4>

            <div class="mt-2 text-sm text-slate-600">
                <template v-if="activeNode?.type === 'text'">
                    Click a variable to insert it into the selected text block.
                </template>

                <template v-else-if="activeNode?.type === 'image'">
                    Click an image-capable variable to bind the selected image block dynamically.
                </template>

                <template v-else-if="activeNode?.type === 'table' || activeNode?.type === 'list'">
                    Click an array variable to assign it as the data source.
                </template>

                <template v-else>
                    Select a text, image, table, or list block to map dynamic data.
                </template>
            </div>
        </div>

        <div
            v-for="(group, groupName) in dictionary"
            :key="groupName"
            class="rounded-2xl border border-slate-200 bg-white p-4"
        >
            <div class="text-[11px] font-bold uppercase tracking-[0.18em] text-[var(--brand-700)]">
                {{ groupName }}
            </div>

            <div class="mt-3 space-y-3">
                <div
                    v-for="variable in group"
                    :key="variable.key"
                    class="rounded-2xl border border-slate-200 bg-slate-50 p-3"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="font-mono text-[12px] font-semibold text-slate-900">
                                {{ variable.key }}
                            </div>
                            <div class="mt-1 text-xs leading-5 text-slate-500">
                                {{ variable.description }}
                            </div>
                            <div
                                v-if="variable.children?.length"
                                class="mt-2 flex flex-wrap gap-2"
                            >
                                <span
                                    v-for="child in variable.children"
                                    :key="`${variable.key}-${child.key}`"
                                    class="rounded-full bg-white px-2 py-1 font-mono text-[11px] text-slate-600"
                                >
                                    {{ child.key }}
                                </span>
                            </div>
                        </div>

                        <div class="flex shrink-0 flex-col gap-2">
                            <button
                                v-if="canInsertText"
                                type="button"
                                class="action-btn"
                                @click="insertTextVariable(variable.key)"
                            >
                                Insert
                            </button>

                            <button
                                v-if="canAssignArray && variable.is_array"
                                type="button"
                                class="action-btn"
                                @click="assignArrayVariable(variable.key)"
                            >
                                Use as Source
                            </button>

                            <button
                                v-if="canAssignImage"
                                type="button"
                                class="action-btn"
                                @click="assignImageVariable(variable.key)"
                            >
                                Use as Image
                            </button>

                            <button
                                type="button"
                                class="action-btn-secondary"
                                @click="copyVariable(variable.key)"
                            >
                                Copy Key
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    activeNode: {
        type: Object,
        default: null,
    },
    dictionary: {
        type: Object,
        default: () => ({}),
    },
});

const emit = defineEmits(['update']);

const canInsertText = computed(() => props.activeNode?.type === 'text');
const canAssignArray = computed(() => ['table', 'list'].includes(props.activeNode?.type));
const canAssignImage = computed(() => props.activeNode?.type === 'image');

const insertTextVariable = (token) => {
    if (!props.activeNode) {
        return;
    }

    props.activeNode.content ||= '';
    props.activeNode.content += `${props.activeNode.content ? ' ' : ''}{{ ${token} }}`;
    emit('update');
};

const assignArrayVariable = (key) => {
    if (!props.activeNode) {
        return;
    }

    props.activeNode.data_key = key;
    emit('update');
};

const assignImageVariable = (key) => {
    if (!props.activeNode) {
        return;
    }

    props.activeNode.source_mode = 'dynamic';
    props.activeNode.data_key = key;
    emit('update');
};

const copyVariable = async (key) => {
    try {
        await navigator.clipboard.writeText(key);
    } catch {
        // noop
    }
};
</script>

<style scoped>
.action-btn {
    border-radius: 0.8rem;
    background: var(--brand-600);
    color: white;
    padding: 0.45rem 0.8rem;
    font-size: 0.75rem;
    font-weight: 700;
    transition: 0.15s ease;
}

.action-btn:hover {
    opacity: 0.9;
}

.action-btn-secondary {
    border-radius: 0.8rem;
    border: 1px solid rgb(226 232 240);
    background: white;
    color: rgb(51 65 85);
    padding: 0.45rem 0.8rem;
    font-size: 0.75rem;
    font-weight: 700;
    transition: 0.15s ease;
}

.action-btn-secondary:hover {
    background: rgb(248 250 252);
}
</style>
