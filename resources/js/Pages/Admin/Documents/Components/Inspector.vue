<template>
    <div class="flex h-full min-h-0 flex-col bg-white">
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
                            <option value="A5">A5</option>
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
                        <input v-model="activeNode.margins" type="text" class="control" placeholder="10mm" @input="$emit('update')">
                    </div>

                    <div>
                        <label class="field-label">Document Font</label>
                        <select v-model="activeNode.fontFamily" class="control" @change="$emit('update')">
                            <option
                                v-for="option in fontOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </option>
                        </select>
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
                            <input v-model="activeNode.backgroundColor" type="text" class="control" @input="$emit('update')">
                        </div>
                    </div>

                    <div>
                        <label class="field-label">Watermark Text</label>
                        <input v-model="activeNode.watermarkText" type="text" class="control" placeholder="Optional" @input="$emit('update')">
                    </div>
                </section>

                <section v-else class="space-y-4">
                    <div>
                        <label class="field-label">Block Type</label>
                        <input :value="activeNode.type" type="text" class="control bg-slate-50" readonly>
                    </div>

                    <template v-if="activeNode.type === 'row'">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                            <div class="field-label !mb-2">Quick Layout Presets</div>

                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" class="preset-btn" @click="applyPreset([12])">12</button>
                                <button type="button" class="preset-btn" @click="applyPreset([6, 6])">6 / 6</button>
                                <button type="button" class="preset-btn" @click="applyPreset([4, 8])">4 / 8</button>
                                <button type="button" class="preset-btn" @click="applyPreset([8, 4])">8 / 4</button>
                                <button type="button" class="preset-btn col-span-2" @click="applyPreset([4, 4, 4])">4 / 4 / 4</button>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <div class="field-label !mb-0">Row Columns</div>
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
                                <label class="mb-1 block text-[11px] font-semibold text-slate-600">
                                    Column {{ index + 1 }} Span
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
                        </div>
                    </template>

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
                        <select v-model="activeNode.data_key" class="control" @change="$emit('update')">
                            <option value="">Select loop source</option>
                            <option v-for="option in arrayVariables" :key="option.key" :value="option.key">
                                {{ option.key }}
                            </option>
                        </select>
                    </div>

                    <template v-if="activeNode.type === 'table'">
                        <div>
                            <label class="field-label">Table Data Source</label>
                            <select v-model="activeNode.data_key" class="control" @change="$emit('update')">
                                <option value="">Select loop source</option>
                                <option v-for="option in arrayVariables" :key="option.key" :value="option.key">
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

                    <section class="space-y-3 border-t border-slate-200 pt-4">
                        <div class="field-label !mb-0">
                            Appearance
                        </div>

                        <div class="grid gap-3">
                            <div>
                                <label class="field-label">Margin</label>
                                <input v-model="activeNode.styles.margin" type="text" class="control" placeholder="0px" @input="$emit('update')">
                            </div>

                            <div>
                                <label class="field-label">Padding</label>
                                <input v-model="activeNode.styles.padding" type="text" class="control" placeholder="0px" @input="$emit('update')">
                            </div>

                            <div v-if="supportsTextStyles">
                                <label class="field-label">Font Family</label>
                                <select v-model="activeNode.styles.fontFamily" class="control" @change="$emit('update')">
                                    <option value="">Use document default</option>
                                    <option
                                        v-for="option in fontOptions"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>
                            </div>

                            <div v-if="supportsTextStyles">
                                <label class="field-label">Font Size</label>
                                <input v-model="activeNode.styles.fontSize" type="text" class="control" placeholder="12px" @input="$emit('update')">
                            </div>

                            <div v-if="supportsTextStyles">
                                <label class="field-label">Font Weight</label>
                                <select v-model="activeNode.styles.fontWeight" class="control" @change="$emit('update')">
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
                                <select v-model="activeNode.styles.textAlign" class="control" @change="$emit('update')">
                                    <option value="left">left</option>
                                    <option value="center">center</option>
                                    <option value="right">right</option>
                                    <option value="justify">justify</option>
                                </select>
                            </div>

                            <div v-if="supportsTextStyles">
                                <label class="field-label">Case Transform</label>
                                <select v-model="activeNode.styles.textTransform" class="control" @change="$emit('update')">
                                    <option value="none">Normal</option>
                                    <option value="uppercase">ALL CAPS</option>
                                    <option value="lowercase">lowercase</option>
                                    <option value="capitalize">Capitalize Words</option>
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
                                    <input v-model="activeNode.styles.color" type="text" class="control" @input="$emit('update')">
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
                                    <input v-model="activeNode.styles.backgroundColor" type="text" class="control" @input="$emit('update')">
                                </div>
                            </div>

                            <div>
                                <label class="field-label">Border Radius</label>
                                <input v-model="activeNode.styles.borderRadius" type="text" class="control" placeholder="0px" @input="$emit('update')">
                            </div>

                            <div>
                                <label class="field-label">Border</label>
                                <input v-model="activeNode.styles.border" type="text" class="control" placeholder="0px solid #e5e7eb" @input="$emit('update')">
                            </div>

                            <div v-if="supportsHeight">
                                <label class="field-label">Height</label>
                                <input v-model="activeNode.styles.height" type="text" class="control" placeholder="24px" @input="$emit('update')">
                            </div>

                            <div v-if="activeNode.type === 'image'">
                                <label class="field-label">Width</label>
                                <input v-model="activeNode.styles.width" type="text" class="control" placeholder="180px" @input="$emit('update')">
                            </div>

                            <div v-if="activeNode.type === 'image'">
                                <label class="field-label">Image Align</label>
                                <select v-model="activeNode.styles.textAlign" class="control" @change="$emit('update')">
                                    <option value="left">left</option>
                                    <option value="center">center</option>
                                    <option value="right">right</option>
                                </select>
                            </div>

                            <div v-if="activeNode.type === 'image'">
                                <label class="field-label">Image Fit</label>
                                <select v-model="activeNode.styles.objectFit" class="control" @change="$emit('update')">
                                    <option value="contain">contain</option>
                                    <option value="cover">cover</option>
                                    <option value="fill">fill</option>
                                    <option value="none">none</option>
                                    <option value="scale-down">scale-down</option>
                                </select>
                            </div>
                        </div>
                    </section>
                </section>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, inject } from 'vue';

const props = defineProps({
    activeNode: {
        type: Object,
        default: null,
    },
    documentType: {
        type: String,
        default: 'invoice',
    },
    fontOptions: {
        type: Array,
        default: () => [],
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

defineEmits(['update', 'delete-node']);

const builder = inject('documentBuilder', null);

const isPage = computed(() => !!props.activeNode?.isPage);
const canDelete = computed(() => !isPage.value && !!props.activeNode);
const title = computed(() => {
    if (!props.activeNode) {
        return 'Nothing Selected';
    }

    if (isPage.value) {
        return 'Document Sheet';
    }

    return `${String(props.activeNode.type || 'block').toUpperCase()} Block`;
});

const supportsTextStyles = computed(() => ['text', 'list', 'table'].includes(props.activeNode?.type));
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
};

const removeTableColumn = (index) => {
    props.activeNode.columns.splice(index, 1);
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
};

const normalizeColumnSpan = (column) => {
    column.span = Math.max(1, Math.min(12, Number(column.span || 12)));
};

const applyPreset = (spans) => {
    if (!props.activeNode?.id || props.activeNode?.type !== 'row') {
        return;
    }

    builder?.applyRowPreset?.(props.activeNode.id, spans);
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

.preset-btn {
    border: 1px solid rgb(226 232 240);
    background: white;
    color: rgb(15 23 42);
    border-radius: 0.85rem;
    padding: 0.6rem 0.75rem;
    font-size: 0.78rem;
    font-weight: 800;
    transition: 0.15s ease;
}

.preset-btn:hover {
    border-color: var(--brand-500);
    background: var(--brand-50);
}
</style>
