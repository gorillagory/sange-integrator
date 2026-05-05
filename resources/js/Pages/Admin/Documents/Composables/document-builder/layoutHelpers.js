// resources/js/Pages/Admin/Documents/Composables/document-builder/layoutHelpers.js
export const SUPPORTED_BLOCK_TYPES = [
    'row',
    'text',
    'image',
    'list',
    'divider',
    'spacer',
    'table',
    'page_break',
];

export function defaultPageSettings() {
    return {
        isPage: true,
        size: 'A4',
        orientation: 'portrait',
        margins: '10mm',
        backgroundColor: '#ffffff',
        watermarkText: null,
        watermarkOpacity: 0.1,
        watermarkColor: '#e5e7eb',
    };
}

export function defaultStyles() {
    return {
        fontSize: '12px',
        fontWeight: 'normal',
        color: '#1f2937',
        textAlign: 'left',
        margin: '0px',
        backgroundColor: 'transparent',
        borderRadius: '0px',
        padding: '0px',
    };
}

export function makeId(prefix) {
    return `${prefix}_${Date.now()}_${Math.floor(Math.random() * 100000)}`;
}

export function makeColumnId(index = 0) {
    return `col_${Date.now()}_${index}_${Math.floor(Math.random() * 1000)}`;
}

export function normalizeSpan(span) {
    return Math.max(1, Math.min(12, Number(span || 12)));
}

export function normalizeSpans(spans = [12]) {
    if (!Array.isArray(spans) || !spans.length) {
        return [12];
    }

    return spans.map((span) => normalizeSpan(span));
}

export function createColumn(span = 12, index = 0, blocks = []) {
    return {
        id: makeColumnId(index),
        span: normalizeSpan(span),
        blocks: Array.isArray(blocks) ? blocks : [],
    };
}

export function createColumns(spans = [12]) {
    return normalizeSpans(spans).map((span, index) => createColumn(span, index));
}

export function makeBlock(type) {
    switch (type) {
        case 'text':
            return {
                id: makeId('text'),
                type: 'text',
                label: 'Text Node',
                content: '',
                data_key: null,
                styles: defaultStyles(),
            };

        case 'image':
            return {
                id: makeId('image'),
                type: 'image',
                label: 'Image',
                source_mode: 'static',
                data_key: '',
                url: '',
                asset_path: '',
                styles: {
                    width: '180px',
                    objectFit: 'contain',
                    padding: '0px',
                    margin: '0px',
                    display: 'block',
                },
            };

        case 'list':
            return {
                id: makeId('list'),
                type: 'list',
                label: 'List',
                content: '',
                data_key: '',
                styles: {
                    listStyleType: 'disc',
                    paddingLeft: '20px',
                    fontSize: '14px',
                    color: '#374151',
                    margin: '10px 0px',
                },
            };

        case 'divider':
            return {
                id: makeId('divider'),
                type: 'divider',
                label: 'Divider',
                styles: {
                    height: '1px',
                    backgroundColor: '#e5e7eb',
                    margin: '6px 0px',
                },
            };

        case 'spacer':
            return {
                id: makeId('spacer'),
                type: 'spacer',
                label: 'Spacer',
                styles: {
                    height: '24px',
                },
            };

        case 'table':
            return {
                id: makeId('table'),
                type: 'table',
                label: 'Data Table',
                data_key: '',
                columns: [
                    { label: 'Column 1', key: '' },
                ],
                styles: {
                    width: '100%',
                    marginTop: '10px',
                },
            };

        case 'page_break':
            return {
                id: makeId('page_break'),
                type: 'page_break',
                label: 'Page Break',
                styles: {},
            };

        default:
            return {
                id: makeId(type),
                type,
                styles: defaultStyles(),
            };
    }
}

export function makeRow(spans = [12]) {
    return {
        id: makeId('row'),
        type: 'row',
        layout: 'row_12',
        styles: {
            padding: '0px',
            margin: '0px 0px 16px 0px',
            gap: '16px',
            alignItems: 'start',
            justifyContent: 'space-between',
            backgroundColor: 'transparent',
            borderRadius: '0px',
            border: '0px solid #e5e7eb',
        },
        columns: createColumns(spans),
    };
}

export function ensureLayoutIntegrity(layoutVector) {
    layoutVector.version ??= 1;
    layoutVector.page ??= defaultPageSettings();
    layoutVector.header ??= [];
    layoutVector.body ??= [];
    layoutVector.footer ??= [];
    return layoutVector;
}

export function snapshot(value) {
    return JSON.stringify(value);
}

export function flattenRowBlocks(row) {
    return (row?.columns || []).flatMap((column) => column.blocks || []);
}

export function rebuildRowColumns(spans = [12], existingBlocks = []) {
    const columns = createColumns(spans);

    if (existingBlocks.length && columns.length) {
        columns[0].blocks = existingBlocks;
    }

    return columns;
}

export function regenerateIds(node) {
    const clonedNode = {
        ...node,
        id: makeId(node.type),
    };

    if (clonedNode.type === 'row') {
        clonedNode.columns = (clonedNode.columns || []).map((column, index) => ({
            ...column,
            id: makeColumnId(index),
            blocks: (column.blocks || []).map((block) => regenerateIds(block)),
        }));
    }

    return clonedNode;
}
