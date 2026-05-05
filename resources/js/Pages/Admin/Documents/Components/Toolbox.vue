<template>
    <div class="flex h-full flex-col bg-transparent">
        <div class="border-b border-slate-200 bg-white p-4">
            <h3 class="text-sm font-black uppercase tracking-[0.22em] text-slate-900">
                Block Library
            </h3>
            <p class="mt-1 text-xs text-slate-500">
                Click to insert into body, or drag into preview zones and row columns.
            </p>
        </div>

        <div class="min-h-0 flex-1 overflow-y-auto p-4">
            <div class="space-y-6">
                <section>
                    <div class="mb-2 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">
                        Row Presets
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" class="preset-btn" @click="$emit('add-row-preset', [12])">12</button>
                        <button type="button" class="preset-btn" @click="$emit('add-row-preset', [6, 6])">6 / 6</button>
                        <button type="button" class="preset-btn" @click="$emit('add-row-preset', [4, 8])">4 / 8</button>
                        <button type="button" class="preset-btn" @click="$emit('add-row-preset', [8, 4])">8 / 4</button>
                        <button type="button" class="preset-btn col-span-2" @click="$emit('add-row-preset', [4, 4, 4])">4 / 4 / 4</button>
                    </div>
                </section>

                <section>
                    <div class="mb-2 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">
                        Layout
                    </div>

                    <div class="grid gap-2">
                        <div
                            draggable="true"
                            class="tool-card cursor-grab active:cursor-grabbing"
                            @click="$emit('add-row')"
                            @dragstart="emitRowDrag"
                        >
                            <div class="tool-title">Row</div>
                            <div class="tool-desc">12 column layout container. Drop into any zone.</div>
                        </div>

                        <div
                            draggable="true"
                            class="tool-card cursor-grab active:cursor-grabbing"
                            @click="$emit('add-block', 'spacer')"
                            @dragstart="emitBlockDrag($event, 'spacer')"
                        >
                            <div class="tool-title">Spacer</div>
                            <div class="tool-desc">Vertical breathing room.</div>
                        </div>

                        <div
                            draggable="true"
                            class="tool-card cursor-grab active:cursor-grabbing"
                            @click="$emit('add-block', 'divider')"
                            @dragstart="emitBlockDrag($event, 'divider')"
                        >
                            <div class="tool-title">Divider</div>
                            <div class="tool-desc">Visual separator line.</div>
                        </div>

                        <div
                            draggable="true"
                            class="tool-card cursor-grab active:cursor-grabbing"
                            @click="$emit('add-block', 'page_break')"
                            @dragstart="emitBlockDrag($event, 'page_break')"
                        >
                            <div class="tool-title">Page Break</div>
                            <div class="tool-desc">Force next page in rendered PDF.</div>
                        </div>
                    </div>
                </section>

                <section>
                    <div class="mb-2 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">
                        Content
                    </div>

                    <div class="grid gap-2">
                        <div
                            draggable="true"
                            class="tool-card cursor-grab active:cursor-grabbing"
                            @click="$emit('add-block', 'text')"
                            @dragstart="emitBlockDrag($event, 'text')"
                        >
                            <div class="tool-title">Text</div>
                            <div class="tool-desc">Static content or token-based text output.</div>
                        </div>

                        <div
                            draggable="true"
                            class="tool-card cursor-grab active:cursor-grabbing"
                            @click="$emit('add-block', 'image')"
                            @dragstart="emitBlockDrag($event, 'image')"
                        >
                            <div class="tool-title">Image</div>
                            <div class="tool-desc">Static asset or dynamic image token like company logo.</div>
                        </div>
                    </div>
                </section>

                <section>
                    <div class="mb-2 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">
                        Dynamic Data
                    </div>

                    <div class="grid gap-2">
                        <div
                            draggable="true"
                            class="tool-card cursor-grab active:cursor-grabbing"
                            @click="$emit('add-block', 'table')"
                            @dragstart="emitBlockDrag($event, 'table')"
                        >
                            <div class="tool-title">Table</div>
                            <div class="tool-desc">Loop arrays like <span class="font-mono">invoice.line_items</span>.</div>
                        </div>

                        <div
                            draggable="true"
                            class="tool-card cursor-grab active:cursor-grabbing"
                            @click="$emit('add-block', 'list')"
                            @dragstart="emitBlockDrag($event, 'list')"
                        >
                            <div class="tool-title">List</div>
                            <div class="tool-desc">Render bullet list from an array key.</div>
                        </div>
                    </div>
                </section>

                <section>
                    <div class="mb-2 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">
                        High Value Variables
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <div class="space-y-3">
                            <div
                                v-for="(group, groupName) in dictionariesForType"
                                :key="groupName"
                                class="rounded-xl bg-slate-50 p-3"
                            >
                                <div class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">
                                    {{ groupName }}
                                </div>

                                <div class="mt-2 flex flex-wrap gap-2">
                                    <span
                                        v-for="variable in group.slice(0, 6)"
                                        :key="variable.key"
                                        class="rounded-full border border-slate-200 bg-white px-2 py-1 font-mono text-[11px] text-slate-700"
                                    >
                                        {{ variable.key }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    documentType: {
        type: String,
        required: true,
    },
    dictionaries: {
        type: Object,
        default: () => ({}),
    },
});

const emit = defineEmits([
    'add-block',
    'add-row',
    'add-row-preset',
    'start-drag-block',
    'start-drag-row',
]);

const dictionariesForType = computed(() => {
    return props.dictionaries?.[props.documentType] ?? {};
});

const emitBlockDrag = (event, type) => {
    if (event.dataTransfer) {
        event.dataTransfer.effectAllowed = 'copy';
        event.dataTransfer.setData('text/plain', type);
    }

    emit('start-drag-block', type, event);
};

const emitRowDrag = (event) => {
    if (event.dataTransfer) {
        event.dataTransfer.effectAllowed = 'copy';
        event.dataTransfer.setData('text/plain', 'row');
    }

    emit('start-drag-row', event);
};
</script>

<style scoped>
.tool-card {
    border: 1px solid rgb(226 232 240);
    background: white;
    border-radius: 1rem;
    padding: 0.875rem 1rem;
    text-align: left;
    transition: 0.15s ease;
}

.tool-card:hover {
    border-color: var(--brand-500);
    background: var(--brand-50);
}

.tool-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: rgb(15 23 42);
}

.tool-desc {
    margin-top: 0.25rem;
    font-size: 0.75rem;
    color: rgb(100 116 139);
    line-height: 1.4;
}

.preset-btn {
    border: 1px solid rgb(226 232 240);
    background: white;
    color: rgb(15 23 42);
    border-radius: 0.9rem;
    padding: 0.7rem 0.9rem;
    font-size: 0.8rem;
    font-weight: 800;
    transition: 0.15s ease;
}

.preset-btn:hover {
    border-color: var(--brand-500);
    background: var(--brand-50);
}
</style>
