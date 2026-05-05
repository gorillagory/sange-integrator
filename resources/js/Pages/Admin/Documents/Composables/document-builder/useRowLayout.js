// resources/js/Pages/Admin/Documents/Composables/document-builder/useRowLayout.js
import {
    createColumns,
    flattenRowBlocks,
    makeRow,
    normalizeSpan,
} from './layoutHelpers';

export function useRowLayout({ selectedNodeIds, touch }) {
    function normalizeRowSpans(spans = [12]) {
        if (!Array.isArray(spans) || !spans.length) {
            return [12];
        }

        return spans.map((span) => normalizeSpan(span));
    }

    function createRow(spans = [12]) {
        return makeRow(normalizeRowSpans(spans));
    }

    function applyPresetToRow(row, spans = [12]) {
        if (!row || row.type !== 'row') {
            return false;
        }

        const normalizedSpans = normalizeRowSpans(spans);
        const existingBlocks = flattenRowBlocks(row);

        row.columns = createColumns(normalizedSpans);

        if (existingBlocks.length && row.columns.length) {
            row.columns[0].blocks = existingBlocks;
        }

        selectedNodeIds.value = [row.id];
        touch();

        return true;
    }

    function createNestedRowInColumn(column, spans = [12]) {
        if (!column) {
            return null;
        }

        column.blocks ||= [];

        const row = createRow(spans);
        column.blocks.push(row);
        selectedNodeIds.value = [row.id];
        touch();

        return row;
    }

    return {
        normalizeRowSpans,
        createRow,
        applyPresetToRow,
        createNestedRowInColumn,
    };
}
