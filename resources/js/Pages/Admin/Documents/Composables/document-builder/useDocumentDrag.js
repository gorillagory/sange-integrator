// resources/js/Pages/Admin/Documents/Composables/document-builder/useDocumentDrag.js
import { ref } from 'vue';
import {
    DRAG_KIND_EXISTING,
    DRAG_KIND_ROW,
    makeBlockDragPayload,
    makeColumnDragTarget,
    makeExistingNodeDragPayload,
    makeRowDragPayload,
    makeZoneDragTarget,
} from './dragTypes';

export function useDocumentDrag({
                                    isPreviewMode,
                                    selectedNodeIds,
                                    addBlock,
                                    addRow,
                                    moveExistingNodeToTarget,
                                }) {
    const dragPayload = ref(null);
    const dragOverTarget = ref(null);

    const startDragBlock = (type, event = null) => {
        dragPayload.value = makeBlockDragPayload(type);
        hydrateDataTransfer(event, `block:${type}`);
    };

    const startDragRow = (event = null) => {
        dragPayload.value = makeRowDragPayload();
        hydrateDataTransfer(event, 'row');
    };

    const startDragExistingNode = (nodeId, event = null) => {
        if (!nodeId || isPreviewMode.value) {
            return;
        }

        dragPayload.value = makeExistingNodeDragPayload(nodeId);
        selectedNodeIds.value = [nodeId];
        hydrateDataTransfer(event, `existing:${nodeId}`);
    };

    const onDragOverZone = (zone) => {
        dragOverTarget.value = makeZoneDragTarget(zone);
    };

    const onDragLeaveZone = (zone) => {
        const key = makeZoneDragTarget(zone);

        if (dragOverTarget.value === key) {
            dragOverTarget.value = null;
        }
    };

    const onDragOverColumn = (rowId, columnId) => {
        dragOverTarget.value = makeColumnDragTarget(rowId, columnId);
    };

    const onDragLeaveColumn = (rowId, columnId) => {
        const key = makeColumnDragTarget(rowId, columnId);

        if (dragOverTarget.value === key) {
            dragOverTarget.value = null;
        }
    };

    const handleDropToZone = (zone) => {
        if (!dragPayload.value) {
            return;
        }

        const target = { zone };

        if (dragPayload.value.kind === DRAG_KIND_EXISTING) {
            moveExistingNodeToTarget(dragPayload.value.nodeId, target);
            clearDragState();
            return;
        }

        if (dragPayload.value.kind === DRAG_KIND_ROW) {
            addRow(target);
        } else {
            addBlock(dragPayload.value.type, target);
        }

        clearDragState();
    };

    const handleDropToColumn = (rowId, columnId) => {
        if (!dragPayload.value) {
            return;
        }

        const target = { rowId, columnId };

        if (dragPayload.value.kind === DRAG_KIND_EXISTING) {
            moveExistingNodeToTarget(dragPayload.value.nodeId, target);
            clearDragState();
            return;
        }

        if (dragPayload.value.kind === DRAG_KIND_ROW) {
            addRow(target);
        } else {
            addBlock(dragPayload.value.type, target);
        }

        clearDragState();
    };

    const clearDragState = () => {
        dragPayload.value = null;
        dragOverTarget.value = null;
    };

    return {
        dragPayload,
        dragOverTarget,
        startDragBlock,
        startDragRow,
        startDragExistingNode,
        onDragOverZone,
        onDragLeaveZone,
        onDragOverColumn,
        onDragLeaveColumn,
        handleDropToZone,
        handleDropToColumn,
        clearDragState,
    };
}

function hydrateDataTransfer(event, payload) {
    if (!event?.dataTransfer) {
        return;
    }

    event.dataTransfer.effectAllowed = 'move';
    event.dataTransfer.setData('text/plain', payload);
}
