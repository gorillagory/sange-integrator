// resources/js/Pages/Admin/Documents/Composables/document-builder/dragTypes.js
export const DRAG_KIND_BLOCK = 'block';
export const DRAG_KIND_ROW = 'row';
export const DRAG_KIND_EXISTING = 'existing';

export function makeBlockDragPayload(type) {
    return {
        kind: DRAG_KIND_BLOCK,
        type,
    };
}

export function makeRowDragPayload() {
    return {
        kind: DRAG_KIND_ROW,
    };
}

export function makeExistingNodeDragPayload(nodeId) {
    return {
        kind: DRAG_KIND_EXISTING,
        nodeId,
    };
}

export function makeZoneDragTarget(zone) {
    return `zone:${zone}`;
}

export function makeColumnDragTarget(rowId, columnId) {
    return `column:${rowId}:${columnId}`;
}
