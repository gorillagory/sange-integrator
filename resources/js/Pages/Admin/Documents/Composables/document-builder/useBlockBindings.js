// resources/js/Pages/Admin/Documents/Composables/document-builder/useBlockBindings.js
import { computed } from 'vue';
import { resolveImageSource, resolvePath } from './imageHelpers';

export function useBlockBindings({ form }) {
    const previewPayload = computed(() => form.preview_payload ?? {});

    function resolveDataPath(path, payload = null) {
        return resolvePath(payload ?? previewPayload.value, path);
    }

    function hasDataPath(path, payload = null) {
        const value = resolveDataPath(path, payload);
        return value !== null && value !== undefined && value !== '';
    }

    function resolveTextContent(node, payload = null) {
        if (!node || node.type !== 'text') {
            return '';
        }

        if (node.data_key) {
            const bound = resolveDataPath(node.data_key, payload);

            if (bound !== null && bound !== undefined && bound !== '') {
                return String(bound);
            }
        }

        return node.content ?? '';
    }

    function resolveListItems(node, payload = null) {
        if (!node || node.type !== 'list') {
            return [];
        }

        if (node.data_key) {
            const bound = resolveDataPath(node.data_key, payload);

            if (Array.isArray(bound)) {
                return bound;
            }

            if (typeof bound === 'string') {
                return bound
                    .split('\n')
                    .map((item) => item.trim())
                    .filter(Boolean);
            }
        }

        return String(node.content || '')
            .split('\n')
            .map((item) => item.trim())
            .filter(Boolean);
    }

    function resolveTableRows(node, payload = null) {
        if (!node || node.type !== 'table') {
            return [];
        }

        const bound = node.data_key ? resolveDataPath(node.data_key, payload) : null;

        if (Array.isArray(bound)) {
            return bound;
        }

        return [];
    }

    function resolveTableColumns(node) {
        if (!node || node.type !== 'table') {
            return [];
        }

        return Array.isArray(node.columns) ? node.columns : [];
    }

    function resolveTableSummary(node, payload = null) {
        if (!node || node.type !== 'table') {
            return {
                subtotal: '',
                tax_total: '',
                grand_total: '',
            };
        }

        const activePayload = payload ?? previewPayload.value;
        const dataKey = String(node.data_key || '');
        const rootPath = dataKey.endsWith('.line_items')
            ? dataKey.slice(0, -1 * '.line_items'.length)
            : '';

        const subtotal = rootPath
            ? resolveDataPath(`${rootPath}.subtotal`, activePayload)
            : null;
        const taxTotal = rootPath
            ? resolveDataPath(`${rootPath}.tax_total`, activePayload)
            : null;
        const grandTotal = rootPath
            ? resolveDataPath(`${rootPath}.grand_total`, activePayload)
            : null;

        return {
            subtotal: subtotal ?? resolveDataPath('finance.formatted_subtotal', activePayload) ?? '',
            tax_total: taxTotal ?? resolveDataPath('finance.formatted_tax_total', activePayload) ?? '',
            grand_total: grandTotal ?? resolveDataPath('finance.formatted_grand_total', activePayload) ?? '',
        };
    }

    function resolveImage(node, payload = null) {
        return resolveImageSource(node, payload ?? previewPayload.value);
    }

    function resolveBlockValue(node, payload = null) {
        if (!node) {
            return null;
        }

        switch (node.type) {
            case 'text':
                return resolveTextContent(node, payload);

            case 'list':
                return resolveListItems(node, payload);

            case 'table':
                return resolveTableRows(node, payload);

            case 'image':
                return resolveImage(node, payload);

            default:
                return node;
        }
    }

    return {
        previewPayload,
        resolveDataPath,
        hasDataPath,
        resolveTextContent,
        resolveListItems,
        resolveTableRows,
        resolveTableColumns,
        resolveTableSummary,
        resolveImage,
        resolveBlockValue,
    };
}
