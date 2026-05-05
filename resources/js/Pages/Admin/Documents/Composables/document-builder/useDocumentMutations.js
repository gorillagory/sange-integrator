// resources/js/Pages/Admin/Documents/Composables/document-builder/useDocumentMutations.js
import {
    SUPPORTED_BLOCK_TYPES,
    ensureLayoutIntegrity,
    makeBlock,
    regenerateIds,
} from './layoutHelpers';
import { useRowLayout } from './useRowLayout';

export function useDocumentMutations({
                                         form,
                                         selectedNodeIds,
                                         traversal,
                                         touch,
                                     }) {
    const rowLayout = useRowLayout({
        selectedNodeIds,
        touch,
    });

    function resolveTargetContainer(target = null) {
        ensureLayoutIntegrity(form.layout_vector);

        if (target?.zone && traversal.ZONES.includes(target.zone)) {
            return form.layout_vector[target.zone];
        }

        if (target?.rowId && target?.columnId) {
            const row = traversal.findNodeById(target.rowId, traversal.allZones());

            if (row?.type === 'row') {
                const column = (row.columns || []).find((item) => item.id === target.columnId);

                if (column) {
                    column.blocks ||= [];
                    return column.blocks;
                }
            }
        }

        const activeId = selectedNodeIds.value[selectedNodeIds.value.length - 1];

        if (!activeId || activeId === 'page') {
            return form.layout_vector.body;
        }

        const directNode = traversal.findNodeById(activeId, traversal.allZones());

        if (directNode?.type === 'row' && Array.isArray(directNode.columns) && directNode.columns.length) {
            directNode.columns[0].blocks ||= [];
            return directNode.columns[0].blocks;
        }

        return traversal.findNodeContainer(activeId) || form.layout_vector.body;
    }

    function addBlock(type, target = null) {
        if (!SUPPORTED_BLOCK_TYPES.includes(type) || type === 'row') {
            return;
        }

        const container = resolveTargetContainer(target);
        const block = makeBlock(type);

        container.push(block);
        selectedNodeIds.value = [block.id];
        touch();
    }

    function addRow(target = null, spans = [12]) {
        const container = resolveTargetContainer(target);
        const row = rowLayout.createRow(spans);

        container.push(row);
        selectedNodeIds.value = [row.id];
        touch();
    }

    function applyRowPreset(rowId, spans) {
        const row = traversal.findNodeById(rowId, traversal.allZones());

        if (!row || row.type !== 'row' || !Array.isArray(spans) || !spans.length) {
            return;
        }

        rowLayout.applyPresetToRow(row, spans);
    }

    function addBlockToColumn(rowId, columnId, type) {
        addBlock(type, { rowId, columnId });
    }

    function addNestedRowToColumn(rowId, columnId, spans = [12]) {
        const row = traversal.findNodeById(rowId, traversal.allZones());

        if (!row || row.type !== 'row') {
            return;
        }

        const column = (row.columns || []).find((item) => item.id === columnId);

        if (!column) {
            return;
        }

        rowLayout.createNestedRowInColumn(column, spans);
    }

    function moveExistingNodeToTarget(nodeId, target) {
        if (!nodeId) {
            return;
        }

        const node = traversal.findNodeById(nodeId, traversal.allZones());

        if (!node) {
            return;
        }

        if (target?.rowId && traversal.isDescendantTarget(nodeId, target.rowId)) {
            return;
        }

        const targetContainer = resolveTargetContainer(target);
        const removed = traversal.detachNodeById(nodeId);

        if (!removed) {
            return;
        }

        targetContainer.push(removed);
        selectedNodeIds.value = [removed.id];
        touch();
    }

    function moveNodeUp(nodeId) {
        const container = traversal.findNodeContainer(nodeId);

        if (!container) {
            return;
        }

        const index = container.findIndex((node) => node.id === nodeId);

        if (index <= 0) {
            return;
        }

        const [node] = container.splice(index, 1);
        container.splice(index - 1, 0, node);
        selectedNodeIds.value = [nodeId];
        touch();
    }

    function moveNodeDown(nodeId) {
        const container = traversal.findNodeContainer(nodeId);

        if (!container) {
            return;
        }

        const index = container.findIndex((node) => node.id === nodeId);

        if (index < 0 || index >= container.length - 1) {
            return;
        }

        const [node] = container.splice(index, 1);
        container.splice(index + 1, 0, node);
        selectedNodeIds.value = [nodeId];
        touch();
    }

    function duplicateNode(nodeId) {
        const container = traversal.findNodeContainer(nodeId);

        if (!container) {
            return;
        }

        const index = container.findIndex((node) => node.id === nodeId);

        if (index < 0) {
            return;
        }

        const cloned = regenerateIds(JSON.parse(JSON.stringify(container[index])));
        container.splice(index + 1, 0, cloned);
        selectedNodeIds.value = [cloned.id];
        touch();
    }

    function deleteNode(nodeId) {
        if (!nodeId || nodeId === 'page') {
            return;
        }

        traversal.removeNodeById(nodeId);
        selectedNodeIds.value = ['page'];
        touch();
    }

    return {
        resolveTargetContainer,
        addBlock,
        addRow,
        applyRowPreset,
        addBlockToColumn,
        addNestedRowToColumn,
        moveExistingNodeToTarget,
        moveNodeUp,
        moveNodeDown,
        duplicateNode,
        deleteNode,
    };
}
