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
            <div class="overflow-hidden rounded-xl">
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
            <div
                v-if="isItemizedInvoiceTable"
                class="rounded-xl bg-white"
                :style="tableWrapperStyle"
            >
                <table class="min-w-full table-fixed text-sm">
                    <thead>
                        <tr class="border-b border-slate-300">
                            <th
                                v-for="(column, index) in tableColumns"
                                :key="`${column.key}-${index}`"
                                class="pb-3 text-left text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500"
                                :class="cellClass(column.key, true)"
                            >
                                {{ column.label || `Column ${index + 1}` }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!tableRows.length">
                            <td
                                :colspan="Math.max(tableColumns.length, 1)"
                                class="py-4 text-center text-xs text-slate-400"
                            >
                                No items yet
                            </td>
                        </tr>

                        <tr
                            v-for="(row, rowIndex) in tableRows"
                            :key="rowIndex"
                            class="border-b border-slate-200 last:border-b-0"
                        >
                            <td
                                v-for="(column, columnIndex) in tableColumns"
                                :key="`${rowIndex}-${column.key}-${columnIndex}`"
                                class="py-3 align-top text-slate-700"
                                :class="cellClass(column.key, false)"
                            >
                                {{ resolveCell(row, column.key) }}
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="mt-4 flex justify-end">
                    <div class="w-full max-w-[260px] border-t border-slate-300 pt-3">
                        <div class="flex items-center justify-between py-1 text-sm text-slate-600">
                            <span>Subtotal</span>
                            <span class="font-medium text-slate-900">{{ tableSummary.subtotal || '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between py-1 text-sm text-slate-600">
                            <span>Tax</span>
                            <span class="font-medium text-slate-900">{{ tableSummary.tax_total || '-' }}</span>
                        </div>
                        <div class="mt-1 flex items-center justify-between py-2 text-base font-bold text-slate-950">
                            <span>Grand Total</span>
                            <span>{{ tableSummary.grand_total || '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="overflow-x-auto rounded-xl border border-slate-200" :style="tableWrapperStyle">
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
import { resolveDocumentFontCss } from '../../Composables/document-builder/fontPresets';

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
const tableSummary = computed(() => builder?.resolveTableSummary?.(props.node) ?? {});
const imageSource = computed(() => builder?.resolveImage?.(props.node) ?? props.node.asset_path ?? props.node.url ?? '');
const isItemizedInvoiceTable = computed(() => props.node?.preset === 'invoice_line_items');

const textStyle = computed(() => ({
    fontFamily: props.node?.styles?.fontFamily ? resolveDocumentFontCss(props.node.styles.fontFamily) : 'inherit',
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
    fontFamily: props.node?.styles?.fontFamily ? resolveDocumentFontCss(props.node.styles.fontFamily) : 'inherit',
    listStyleType: props.node?.styles?.listStyleType || 'disc',
    paddingLeft: props.node?.styles?.paddingLeft || '20px',
    fontSize: props.node?.styles?.fontSize || '14px',
    color: props.node?.styles?.color || '#374151',
    margin: props.node?.styles?.margin || '10px 0px',
}));

const imageStyle = computed(() => ({
    width: props.node?.styles?.width || '180px',
    maxWidth: '100%',
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

const tableWrapperStyle = computed(() => ({
    fontFamily: props.node?.styles?.fontFamily ? resolveDocumentFontCss(props.node.styles.fontFamily) : 'inherit',
    fontSize: props.node?.styles?.fontSize || '12px',
    color: props.node?.styles?.color || '#0f172a',
    margin: props.node?.styles?.margin || '0px',
    padding: props.node?.styles?.padding || '0px',
    backgroundColor: props.node?.styles?.backgroundColor || 'transparent',
    borderRadius: props.node?.styles?.borderRadius || '0px',
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

function cellClass(key, isHeader) {
    const classes = [];

    if (key === 'description') {
        classes.push('pr-4', 'w-[42%]');
    }

    if (key === 'unit') {
        classes.push('w-[12%]');
    }

    if (['quantity', 'unit_price', 'total'].includes(key)) {
        classes.push('text-right');
    }

    if (!isHeader && key === 'description') {
        classes.push('font-semibold', 'text-slate-900');
    }

    return classes.join(' ');
}
</script>
