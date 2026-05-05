<template>
    <div class="flex h-full flex-col bg-white">
        <div class="border-b border-slate-200 p-4">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <div class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">
                        Inspector
                    </div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">
                        {{ title }}
                    </div>
                </div>

                <button
                    v-if="canDelete"
                    type="button"
                    class="rounded-xl border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-600 transition hover:bg-rose-50"
                    @click="$emit('delete-node')"
                >
                    Delete
                </button>
            </div>
        </div>

        <div v-if="!activeNode" class="flex flex-1 items-center justify-center p-6 text-center">
            <div>
                <div class="text-sm font-semibold text-slate-700">
                    No element selected
                </div>
                <div class="mt-1 text-xs text-slate-500">
                    Click a block in the preview or drag a new one in from the left.
                </div>
            </div>
        </div>

        <div v-else class="min-h-0 flex-1 overflow-y-auto p-4">
            <div class="space-y-6">
                <section v-if="isPage" class="space-y-4">
                    <div>
                        <label class="field-label">Page Size</label>
                        <select v-model="activeNode.size" class="control" @change="$emit('update')">
                            <option value="A4">A4</option>
                            <option value="Letter">Letter</option>
                            <option value="Legal">Legal</option>
                        </select>
                    </div>

                    <div>
                        <label class="field-label">Orientation</label>
                        <select v-model="activeNode.orientation" class="control" @change="$emit('update')">
                            <option value="portrait">Portrait</option>
                            <option value="landscape">Landscape</option>
                        </select>
                    </div>

                    <div>
                        <label class="field-label">Margins</label>
                        <input
                            v-model="activeNode.margins"
                            type="text"
                            class="control"
                            placeholder="10mm"
                            @input="$emit('update')"
                        >
                    </div>

                    <div>
                        <label class="field-label">Background Color</label>
                        <div class="flex items-center gap-2">
                            <input
                                :value="safeHex(activeNode.backgroundColor, '#ffffff')"
                                type="color"
                                class="h-10 w-12 rounded border border-slate-200"
                                @input="activeNode.backgroundColor = $event.target.value; $emit('update')"
                            >
                            <input
                                v-model="activeNode.backgroundColor"
                                type="text"
                                class="control"
                                @input="$emit('update')"
                            >
                        </div>
                    </div>

                    <div>
                        <label class="field-label">Watermark Text</label>
                        <input
                            v-model="activeNode.watermarkText"
                            type="text"
                            class="control"
                            placeholder="Optional"
                            @input="$emit('update')"
                        >
                    </div>
                </section>

                <section v-else class="space-y-4">
                    <div>
                        <label class="field-label">Block Type</label>
                        <input
                            :value="activeNode.type"
                            type="text"
                            class="control bg-slate-50"
                            readonly
                        >
                    </div>

                    <div v-if="activeNode.type === 'text'">
                        <label class="field-label">Content</label>
                        <textarea
                            v-model="activeNode.content"
                            rows="5"
                            class="control"
                            placeholder="Enter text or insert tokens from Data tab"
                            @input="$emit('update')"
                        />
                    </div>

                    <div v-if="activeNode.type === 'text'">
                        <label class="field-label">Direct Data Key</label>
                        <input
                            v-model="activeNode.data_key"
                            type="text"
                            class="control"
                            placeholder="Optional direct key, e.g. invoice.number"
                            @input="$emit('update')"
                        >
                    </div>

                    <template v-if="activeNode.type === 'image'">
                        <div>
                            <label class="field-label">Image Source Mode</label>
                            <div class="grid grid-cols-2 gap-2">
                                <button
                                    type="button"
                                    class="mode-btn"
                                    :class="activeNode.source_mode !== 'dynamic' ? 'mode-btn-active' : 'mode-btn-idle'"
                                    @click="activeNode.source_mode = 'static'; $emit('update')"
                                >
                                    Static
                                </button>

                                <button
                                    type="button"
                                    class="mode-btn"
                                    :class="activeNode.source_mode === 'dynamic' ? 'mode-btn-active' : 'mode-btn-idle'"
                                    @click="activeNode.source_mode = 'dynamic'; $emit('update')"
                                >
                                    Dynamic
                                </button>
                            </div>
                        </div>

                        <div v-if="activeNode.source_mode === 'dynamic'">
                            <label class="field-label">Dynamic Image Key</label>
                            <input
                                v-model="activeNode.data_key"
                                type="text"
                                class="control"
                                placeholder="company.logo_url or main_group.logo_url"
                                @input="$emit('update')"
                            >
                        </div>

                        <div v-else>
                            <label class="field-label">Asset Path</label>
                            <input
                                v-model="activeNode.asset_path"
                                type="text"
                                class="control"
                                placeholder="/storage/logos/company.png"
                                @input="$emit('update')"
                            >
                        </div>

                        <div v-if="activeNode.source_mode !== 'dynamic'">
                            <label class="field-label">Fallback URL / Data URI</label>
                            <textarea
                                v-model="activeNode.url"
                                rows="3"
                                class="control"
                                placeholder="Use only when needed"
                                @input="$emit('update')"
                            />
                        </div>
                    </template>

                    <div v-if="activeNode.type === 'list'">
                        <label class="field-label">List Data Source</label>
                        <select
                            v-model="activeNode.data_key"
                            class="control"
                            @change="$emit('update')"
                        >
                            <option value="">Select loop source</option>
                            <option
                                v-for="option in arrayVariables"
                                :key="option.key"
                                :value="option.key"
                            >
                                {{ option.key }}
                            </option>
                        </select>
                    </div>

                    <template v-if="activeNode.type === 'table'">
                        <div>
                            <label class="field-label">Table Data Source</label>
                            <select
                                v-model="activeNode.data_key"
                                class="control"
                                @change="$emit('update')"
                            >
                                <option value="">Select loop source</option>
                                <option
                                    v-for="option in arrayVariables"
                                    :key="option.key"
                                    :value="option.key"
                                >
                                    {{ option.key }}
                                </option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <div class="field-label !mb-0">Table Columns</div>
                                <button
                                    type="button"
                                    class="rounded-lg border border-slate-200 px-2 py-1 text-[11px] font-semibold text-slate-700 transition hover:bg-slate-50"
                                    @click="addTableColumn"
                                >
                                    Add Column
                                </button>
                            </div>

                            <div
                                v-for="(column, index) in activeNode.columns"
                                :key="`${column.label}-${index}`"
                                class="rounded-2xl border border-slate-200 p-3"
                            >
                                <div class="grid gap-2">
                                    <input
                                        v-model="column.label"
                                        type="text"
                                        class="control"
                                        placeholder="Column label"
                                        @input="$emit('update')"
                                    >
                                    <input
                                        v-model="column.key"
                                        type="text"
                                        class="control font-mono"
                                        placeholder="description"
                                        @input="$emit('update')"
                                    >
                                </div>

                                <div class="mt-2 flex justify-end">
                                    <button
                                        type="button"
                                        class="rounded-lg border border-rose-200 px-2 py-1 text-[11px] font-semibold text-rose-600 transition hover:bg-rose-50"
                                        @click="removeTableColumn(index)"
                                    >
                                        Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template v-if="activeNode.type === 'row'">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="field-label !mb-1">
                                        Row Layout
                                    </div>
                                    <p class="text-xs leading-5 text-slate-500">
                                        Pick a column split, then drag blocks or nested rows into each column.
                                    </p>
                                </div>

                                <button
                                    type="button"
                                    class="rounded-lg border border-slate-200 bg-white px-2 py-1 text-[11px] font-semibold text-slate-700 transition hover:bg-slate-50"
                                    @click="redistributeColumns"
                                >
                                    Normalize
                                </button>
                            </div>

                            <div class="mt-4 grid grid-cols-2 gap-2">
                                <button
                                    v-for="preset in rowPresets"
                                    :key="preset.label"
                                    type="button"
                                    class="layout-preset"
                                    @click="applyRowPreset(preset.spans)"
                                >
                                    <span>{{ preset.label }}</span>
                                    <span class="font-mono text-[10px] text-slate-400">
                                        {{ preset.spans.join('/') }}
                                    </span>
                                </button>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <div class="field-label !mb-0">Columns</div>
                                <button
                                    type="button"
                                    class="rounded-lg border border-slate-200 px-2 py-1 text-[11px] font-semibold text-slate-700 transition hover:bg-slate-50"
                                    @click="addRowColumn"
                                >
                                    Add Column
                                </button>
                            </div>

                            <div
                                v-for="(column, index) in activeNode.columns"
                                :key="column.id || index"
                                class="rounded-2xl border border-slate-200 p-3"
                            >
                                <div class="flex items-center justify-between gap-3">
                                    <div class="text-xs font-bold text-slate-700">
                                        Column {{ index + 1 }}
                                    </div>

                                    <button
                                        v-if="activeNode.columns.length > 1"
                                        type="button"
                                        class="rounded-lg border border-rose-200 px-2 py-1 text-[11px] font-semibold text-rose-600 transition hover:bg-rose-50"
                                        @click="removeRowColumn(index)"
                                    >
                                        Remove
                                    </button>
                                </div>

                                <div class="mt-3">
                                    <label class="mb-1 block text-[11px] font-semibold text-slate-600">
                                        Span / 12
                                    </label>
                                    <input
                                        v-model.number="column.span"
                                        type="number"
                                        min="1"
                                        max="12"
                                        class="control"
                                        @input="normalizeColumnSpan(column); $emit('update')"
                                    >
                                </div>

                                <div class="mt-2 text-[11px] text-slate-500">
                                    Contains {{ column.blocks?.length || 0 }} block(s). You can drag text, image, table, or another row into this column.
                                </div>
                            </div>
                        </div>
                    </template>

                    <section class="space-y-3 border-t border-slate-200 pt-4">
                        <div class="field-label !mb-0">
                            Appearance
                        </div>

                        <div class="grid gap-3">
                            <div>
                                <label class="field-label">Margin</label>
                                <input
                                    v-model="activeNode.styles.margin"
                                    type="text"
                                    class="control"
                                    placeholder="0px"
                                    @input="$emit('update')"
                                >
                            </div>

                            <div>
                                <label class="field-label">Padding</label>
                                <input
                                    v-model="activeNode.styles.padding"
                                    type="text"
                                    class="control"
                                    placeholder="0px"
                                    @input="$emit('update')"
                                >
                            </div>

                            <div v-if="supportsTextStyles">
                                <label class="field-label">Font Size</label>
                                <input
                                    v-model="activeNode.styles.fontSize"
                                    type="text"
                                    class="control"
                                    placeholder="12px"
                                    @input="$emit('update')"
                                >
                            </div>

                            <div v-if="supportsTextStyles">
                                <label class="field-label">Font Weight</label>
                                <select
                                    v-model="activeNode.styles.fontWeight"
                                    class="control"
                                    @change="$emit('update')"
                                >
                                    <option value="normal">normal</option>
                                    <option value="bold">bold</option>
                                    <option value="600">600</option>
                                    <option value="700">700</option>
                                    <option value="800">800</option>
                                    <option value="900">900</option>
                                </select>
                            </div>

                            <div v-if="supportsTextStyles">
                                <label class="field-label">Text Align</label>
                                <select
                                    v-model="activeNode.styles.textAlign"
                                    class="control"
                                    @change="$emit('update')"
                                >
                                    <option value="left">left</option>
                                    <option value="center">center</option>
                                    <option value="right">right</option>
                                    <option value="justify">justify</option>
                                </select>
                            </div>

                            <div v-if="supportsTextStyles">
                                <label class="field-label">Text Color</label>
                                <div class="flex items-center gap-2">
                                    <input
                                        :value="safeHex(activeNode.styles.color, '#1f2937')"
                                        type="color"
                                        class="h-10 w-12 rounded border border-slate-200"
                                        @input="activeNode.styles.color = $event.target.value; $emit('update')"
                                    >
                                    <input
                                        v-model="activeNode.styles.color"
                                        type="text"
                                        class="control"
                                        @input="$emit('update')"
                                    >
                                </div>
                            </div>

                            <div>
                                <label class="field-label">Background Color</label>
                                <div class="flex items-center gap-2">
                                    <input
                                        :value="safeHex(activeNode.styles.backgroundColor, '#ffffff')"
                                        type="color"
                                        class="h-10 w-12 rounded border border-slate-200"
                                        @input="activeNode.styles.backgroundColor = $event.target.value; $emit('update')"
                                    >
                                    <input
                                        v-model="activeNode.styles.backgroundColor"
                                        type="text"
                                        class="control"
                                        @input="$emit('update')"
                                    >
                                </div>
                            </div>

                            <div>
                                <label class="field-label">Border Radius</label>
                                <input
                                    v-model="activeNode.styles.borderRadius"
                                    type="text"
                                    class="control"
                                    placeholder="0px"
                                    @input="$emit('update')"
                                >
                            </div>

                            <div>
                                <label class="field-label">Border</label>
                                <input
                                    v-model="activeNode.styles.border"
                                    type="text"
                                    class="control"
                                    placeholder="0px solid #e5e7eb"
                                    @input="$emit('update')"
                                >
                            </div>

                            <div v-if="supportsHeight">
                                <label class="field-label">Height</label>
                                <input
                                    v-model="activeNode.styles.height"
                                    type="text"
                                    class="control"
                                    placeholder="24px"
                                    @input="$emit('update')"
                                >
                            </div>

                            <div v-if="activeNode.type === 'image'">
                                <label class="field-label">Width</label>
                                <input
                                    v-model="activeNode.styles.width"
                                    type="text"
                                    class="control"
                                    placeholder="180px"
                                    @input="$emit('update')"
                                >
                            </div>
                        </div>
                    </section>
                </section>
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
    documentType: {
        type: String,
        default: 'invoice',
    },
    dictionary: {
        type: Object,
        default: () => ({}),
    },
    selectedNodeIds: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['update', 'delete-node']);

const rowPresets = [
    { label: 'Full', spans: [12] },
    { label: 'Half / Half', spans: [6, 6] },
    { label: 'One Third / Two Thirds', spans: [4, 8] },
    { label: 'Two Thirds / One Third', spans: [8, 4] },
    { label: 'Three Columns', spans: [4, 4, 4] },
    { label: 'Four Columns', spans: [3, 3, 3, 3] },
    { label: 'Sidebar / Main', spans: [3, 9] },
    { label: 'Main / Sidebar', spans: [9, 3] },
];

const isPage = computed(() => !!props.activeNode?.isPage);
const canDelete = computed(() => !isPage.value && !!props.activeNode);

const title = computed(() => {
    if (!props.activeNode) {
        return 'Nothing Selected';
    }

    if (isPage.value) {
        return 'Document Page';
    }

    return `${String(props.activeNode.type || 'block').toUpperCase()} Block`;
});

const supportsTextStyles = computed(() => ['text', 'list'].includes(props.activeNode?.type));
const supportsHeight = computed(() => ['spacer', 'divider'].includes(props.activeNode?.type));

const arrayVariables = computed(() => {
    const groups = Object.values(props.dictionary || {}).flat();

    return groups.filter((item) => item?.is_array);
});

const addTableColumn = () => {
    if (!Array.isArray(props.activeNode.columns)) {
        props.activeNode.columns = [];
    }

    props.activeNode.columns.push({
        label: 'Column',
        key: '',
    });

    emit('update');
};

const removeTableColumn = (index) => {
    props.activeNode.columns.splice(index, 1);
    emit('update');
};

const applyRowPreset = (spans) => {
    if (!props.activeNode || props.activeNode.type !== 'row') {
        return;
    }

    const oldColumns = Array.isArray(props.activeNode.columns)
        ? props.activeNode.columns
        : [];

    const nextColumns = spans.map((span, index) => {
        const oldColumn = oldColumns[index];

        return {
            id: oldColumn?.id || `col_${Date.now()}_${index}`,
            span,
            blocks: Array.isArray(oldColumn?.blocks) ? oldColumn.blocks : [],
        };
    });

    if (oldColumns.length > spans.length) {
        const lastColumn = nextColumns[nextColumns.length - 1];

        oldColumns.slice(spans.length).forEach((column) => {
            if (Array.isArray(column.blocks)) {
                lastColumn.blocks.push(...column.blocks);
            }
        });
    }

    props.activeNode.columns = nextColumns;
    props.activeNode.layout = `row_${spans.join('_')}`;

    emit('update');
};

const addRowColumn = () => {
    if (!Array.isArray(props.activeNode.columns)) {
        props.activeNode.columns = [];
    }

    props.activeNode.columns.push({
        id: `col_${Date.now()}_${props.activeNode.columns.length}`,
        span: 12,
        blocks: [],
    });

    redistributeColumns();
};

const removeRowColumn = (index) => {
    if (!Array.isArray(props.activeNode.columns) || props.activeNode.columns.length <= 1) {
        return;
    }

    const [removedColumn] = props.activeNode.columns.splice(index, 1);

    if (removedColumn?.blocks?.length) {
        const targetIndex = Math.max(0, index - 1);
        props.activeNode.columns[targetIndex].blocks ||= [];
        props.activeNode.columns[targetIndex].blocks.push(...removedColumn.blocks);
    }

    redistributeColumns();
};

const redistributeColumns = () => {
    if (!Array.isArray(props.activeNode.columns) || props.activeNode.columns.length === 0) {
        return;
    }

    const count = props.activeNode.columns.length;
    const base = Math.floor(12 / count);
    let remainder = 12 - (base * count);

    props.activeNode.columns.forEach((column) => {
        column.span = base + (remainder > 0 ? 1 : 0);
        remainder -= 1;
    });

    props.activeNode.layout = `row_${props.activeNode.columns.map((column) => column.span).join('_')}`;

    emit('update');
};

const normalizeColumnSpan = (column) => {
    column.span = Math.max(1, Math.min(12, Number(column.span || 12)));
};

const safeHex = (value, fallback) => {
    const candidate = String(value || '').trim();

    if (/^#[0-9a-fA-F]{6}$/.test(candidate)) {
        return candidate;
    }

    if (/^#[0-9a-fA-F]{3}$/.test(candidate)) {
        return `#${candidate[1]}${candidate[1]}${candidate[2]}${candidate[2]}${candidate[3]}${candidate[3]}`;
    }

    return fallback;
};
</script>

<style scoped>
.control {
    width: 100%;
    border-radius: 1rem;
    border: 1px solid rgb(226 232 240);
    padding: 0.625rem 0.75rem;
    font-size: 0.875rem;
    outline: none;
    transition: border-color 0.15s ease;
}

.control:focus {
    border-color: var(--brand-500);
}

.field-label {
    margin-bottom: 0.35rem;
    display: block;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.18em;
    color: rgb(100 116 139);
}

.mode-btn {
    border-radius: 1rem;
    padding: 0.6rem 0.75rem;
    font-size: 0.8rem;
    font-weight: 700;
    transition: 0.15s ease;
}

.mode-btn-active {
    background: var(--brand-600);
    color: white;
}

.mode-btn-idle {
    border: 1px solid rgb(226 232 240);
    background: white;
    color: rgb(51 65 85);
}

.layout-preset {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
    border-radius: 1rem;
    border: 1px solid rgb(226 232 240);
    background: white;
    padding: 0.75rem;
    text-align: left;
    font-size: 0.78rem;
    font-weight: 700;
    color: rgb(15 23 42);
    transition: 0.15s ease;
}

.layout-preset:hover {
    border-color: var(--brand-500);
    background: var(--brand-50);
}
</style>
