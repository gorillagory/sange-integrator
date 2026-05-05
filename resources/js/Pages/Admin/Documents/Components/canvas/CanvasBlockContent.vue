<!-- resources/js/Pages/Admin/Documents/Components/canvas/CanvasBlockContent.vue -->
<template>
    <div>
        <div
            v-if="node.type === 'text'"
            class="whitespace-pre-wrap break-words"
            :style="textStyle"
        >
            {{ textValue || 'Text block' }}
        </div>

        <template v-else-if="node.type === 'image'">
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-slate-50 p-3">
                <img
                    v-if="imageSource"
                    :src="imageSource"
                    alt=""
                    class="max-w-full"
                    :style="imageStyle"
                >
                <div
                    v-else
                    class="flex min-h-[120px] items-center justify-center rounded-lg border border-dashed border-slate-300 bg-white text-xs text-slate-400"
                >
                    No image source
                </div>
            </div>
        </template>

        <template v-else-if="node.type === 'list'">
            <ul
                class="pl-5"
                :style="listStyle"
            >
                <li
                    v-for="(item, index) in listItems"
                    :key="index"
                    class="break-words"
                >
                    {{ formatListItem(item) }}
                </li>
                <li v-if="!listItems.length" class="text-slate-400">
                    Empty list
                </li>
            </ul>
        </template>

        <template v-else-if="node.type === 'divider'">
            <div :style="dividerStyle" />
        </template>

        <template v-else-if="node.type === 'spacer'">
            <div :style="spacerStyle" />
        </template>

        <template v-else-if="node.type === 'table'">
            <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50">
                    <tr>
                        <th
                            v-for="(column, index) in tableColumns"
                            :key="`${column.key}-${index}`"
                            class="border-b border-slate-200 px-3 py-2 text-left text-xs font-bold uppercase tracking-[0.12em] text-slate-500"
                        >
                            {{ column.label || `Column ${index + 1}` }}
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-if="!tableRows.length">
                        <td
                            :colspan="Math.max(tableColumns.length, 1)"
                            class="px-3 py-4 text-center text-xs text-slate-400"
                        >
                            No rows
                        </td>
                    </tr>

                    <tr
                        v-for="(row, rowIndex) in tableRows"
                        :key="rowIndex"
                        class="border-t border-slate-100"
                    >
                        <td
                            v-for="(column, columnIndex) in tableColumns"
                            :key="`${rowIndex}-${column.key}-${columnIndex}`"
                            class="px-3 py-2 align-top text-slate-700"
                        >
                            {{ resolveCell(row, column.key) }}
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </template>

        <template v-else-if="node.type === 'page_break'">
            <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-3 py-4 text-center text-xs font-bold uppercase tracking-[0.16em] text-slate-400">
                Page Break
            </div>
        </template>

        <template v-else>
            <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-3 py-4 text-center text-xs text-slate-400">
                Unsupported block
            </div>
        </template>
    </div>
</template>

<script setup>
import { computed, inject } from 'vue';

const props = defineProps({
    node: {
        type: Object,
        required: true,
    },
});

const builder = inject('documentBuilder', null);

const textValue = computed(() => builder?.resolveTextContent?.(props.node) ?? props.node.content ?? '');
const listItems = computed(() => builder?.resolveListItems?.(props.node) ?? []);
const tableRows = computed(() => builder?.resolveTableRows?.(props.node) ?? []);
const tableColumns = computed(() => builder?.resolveTableColumns?.(props.node) ?? props.node.columns ?? []);
const imageSource = computed(() => builder?.resolveImage?.(props.node) ?? props.node.asset_path ?? props.node.url ?? '');

const textStyle = computed(() => ({
    fontSize: props.node?.styles?.fontSize || '12px',
    fontWeight: props.node?.styles?.fontWeight || 'normal',
    color: props.node?.styles?.color || '#1f2937',
    textAlign: props.node?.styles?.textAlign || 'left',
    margin: props.node?.styles?.margin || '0px',
    backgroundColor: props.node?.styles?.backgroundColor || 'transparent',
    borderRadius: props.node?.styles?.borderRadius || '0px',
    padding: props.node?.styles?.padding || '0px',
}));

const listStyle = computed(() => ({
    listStyleType: props.node?.styles?.listStyleType || 'disc',
    paddingLeft: props.node?.styles?.paddingLeft || '20px',
    fontSize: props.node?.styles?.fontSize || '14px',
    color: props.node?.styles?.color || '#374151',
    margin: props.node?.styles?.margin || '10px 0px',
}));

const imageStyle = computed(() => ({
    width: props.node?.styles?.width || '180px',
    objectFit: props.node?.styles?.objectFit || 'contain',
    padding: props.node?.styles?.padding || '0px',
    margin: props.node?.styles?.margin || '0px',
    display: props.node?.styles?.display || 'block',
}));

const dividerStyle = computed(() => ({
    height: props.node?.styles?.height || '1px',
    backgroundColor: props.node?.styles?.backgroundColor || '#e5e7eb',
    margin: props.node?.styles?.margin || '6px 0px',
}));

const spacerStyle = computed(() => ({
    height: props.node?.styles?.height || '24px',
}));

function formatListItem(item) {
    if (item == null) {
        return '';
    }

    if (typeof item === 'object') {
        return Object.values(item).filter(Boolean).join(' • ');
    }

    return String(item);
}

function resolveCell(row, key) {
    if (!key) {
        return '';
    }

    return String(
        String(key)
            .split('.')
            .filter(Boolean)
            .reduce((carry, segment) => carry?.[segment], row) ?? '',
    );
}
</script>
