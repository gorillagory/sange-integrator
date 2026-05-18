// resources/js/Pages/Admin/Documents/Composables/document-builder/useDocumentEngine.js
import { computed, onMounted, onUnmounted, provide, ref, watch } from 'vue';
import { ensureLayoutIntegrity, regenerateIds } from './layoutHelpers';
import { useBlockBindings } from './useBlockBindings';
import { useDocumentDrag } from './useDocumentDrag';
import { useDocumentHistory } from './useDocumentHistory';
import { useDocumentMutations } from './useDocumentMutations';
import { useDocumentTraversal } from './useDocumentTraversal';

export function useDocumentEngine(form) {
    ensureLayoutIntegrity(form.layout_vector);

    const selectedNodeIds = ref(['page']);
    const clipboard = ref([]);
    const isPreviewMode = ref(false);
    const zoomLevel = ref(1);

    const traversal = useDocumentTraversal(() => form.layout_vector);
    const bindings = useBlockBindings({ form });

    const {
        canUndo,
        canRedo,
        undo,
        redo,
        recordHistory,
        touch,
    } = useDocumentHistory({
        form,
    });

    const mutations = useDocumentMutations({
        form,
        selectedNodeIds,
        traversal,
        touch,
    });

    const {
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
    } = useDocumentDrag({
        isPreviewMode,
        selectedNodeIds,
        addBlock: mutations.addBlock,
        addRow: mutations.addRow,
        moveExistingNodeToTarget: mutations.moveExistingNodeToTarget,
    });

    const activeNode = computed(() => {
        if (!selectedNodeIds.value.length) {
            return null;
        }

        const activeId = selectedNodeIds.value[selectedNodeIds.value.length - 1];

        if (activeId === 'page') {
            return form.layout_vector.page;
        }

        return traversal.findNodeById(activeId, traversal.allZones());
    });

    const zoomIn = () => {
        if (zoomLevel.value < 2) {
            zoomLevel.value = Number((zoomLevel.value + 0.1).toFixed(2));
        }
    };

    const zoomOut = () => {
        if (zoomLevel.value > 0.4) {
            zoomLevel.value = Number((zoomLevel.value - 0.1).toFixed(2));
        }
    };

    const selectPage = () => {
        selectedNodeIds.value = ['page'];
    };

    const setActiveNode = (node, multi = false) => {
        if (isPreviewMode.value) {
            return;
        }

        const id = node?.isPage ? 'page' : node?.id;

        if (!id) {
            return;
        }

        if (!multi) {
            selectedNodeIds.value = [id];
            return;
        }

        if (selectedNodeIds.value.includes(id)) {
            selectedNodeIds.value = selectedNodeIds.value.filter((value) => value !== id);

            if (!selectedNodeIds.value.length) {
                selectedNodeIds.value = ['page'];
            }

            return;
        }

        selectedNodeIds.value.push(id);
    };

    const deleteSelection = () => {
        const ids = selectedNodeIds.value.filter((id) => id !== 'page');

        if (!ids.length) {
            return;
        }

        ids.forEach((id) => traversal.removeNodeById(id));

        selectedNodeIds.value = ['page'];
        touch();
    };

    const copySelection = () => {
        clipboard.value = selectedNodeIds.value
            .filter((id) => id !== 'page')
            .map((id) => traversal.findNodeById(id, traversal.allZones()))
            .filter(Boolean)
            .map((node) => JSON.parse(JSON.stringify(node)));
    };

    const cutSelection = () => {
        copySelection();
        deleteSelection();
    };

    const pasteClipboard = () => {
        if (!clipboard.value.length) {
            return;
        }

        const container = mutations.resolveTargetContainer();
        const newIds = [];

        clipboard.value.forEach((item) => {
            const cloned = regenerateIds(JSON.parse(JSON.stringify(item)));
            container.push(cloned);
            newIds.push(cloned.id);
        });

        selectedNodeIds.value = newIds.length ? newIds : ['page'];
        touch();
    };

    const handleKeydown = (event) => {
        if (['INPUT', 'TEXTAREA', 'SELECT'].includes(event.target.tagName)) {
            return;
        }

        if ((event.ctrlKey || event.metaKey) && event.key === 'z') {
            event.preventDefault();
            undo();
        }

        if ((event.ctrlKey || event.metaKey) && event.key === 'y') {
            event.preventDefault();
            redo();
        }

        if ((event.ctrlKey || event.metaKey) && event.key === 'c') {
            event.preventDefault();
            copySelection();
        }

        if ((event.ctrlKey || event.metaKey) && event.key === 'x') {
            event.preventDefault();
            cutSelection();
        }

        if ((event.ctrlKey || event.metaKey) && event.key === 'v') {
            event.preventDefault();
            pasteClipboard();
        }

        if (event.key === 'Delete' || event.key === 'Backspace') {
            event.preventDefault();
            deleteSelection();
        }
    };

    onMounted(() => {
        window.addEventListener('keydown', handleKeydown);
        window.addEventListener('dragend', clearDragState);
    });

    onUnmounted(() => {
        window.removeEventListener('keydown', handleKeydown);
        window.removeEventListener('dragend', clearDragState);
    });

    watch(isPreviewMode, (value) => {
        if (value) {
            selectedNodeIds.value = [];
        } else if (!selectedNodeIds.value.length) {
            selectedNodeIds.value = ['page'];
        }
    });

    provide('documentBuilder', {
        selectedNodeIds,
        isPreviewMode,
        dragPayload,
        dragOverTarget,
        setActiveNode,
        startDragExistingNode,
        onDragOverZone,
        onDragLeaveZone,
        onDragOverColumn,
        onDragLeaveColumn,
        handleDropToZone,
        handleDropToColumn,
        moveNodeUp: mutations.moveNodeUp,
        moveNodeDown: mutations.moveNodeDown,
        duplicateNode: mutations.duplicateNode,
        deleteNode: mutations.deleteNode,
        addBlockToColumn: mutations.addBlockToColumn,
        addNestedRowToColumn: mutations.addNestedRowToColumn,
        applyRowPreset: mutations.applyRowPreset,
        resolveDataPath: bindings.resolveDataPath,
        resolveTextContent: bindings.resolveTextContent,
        resolveListItems: bindings.resolveListItems,
        resolveTableRows: bindings.resolveTableRows,
        resolveTableColumns: bindings.resolveTableColumns,
        resolveTableSummary: bindings.resolveTableSummary,
        resolveImage: bindings.resolveImage,
        resolveBlockValue: bindings.resolveBlockValue,
    });

    return {
        selectedNodeIds,
        isPreviewMode,
        zoomLevel,
        zoomIn,
        zoomOut,
        canUndo,
        canRedo,
        undo,
        redo,
        recordHistory,
        activeNode,
        selectPage,
        addBlock: mutations.addBlock,
        addRow: mutations.addRow,
        applyRowPreset: mutations.applyRowPreset,
        startDragBlock,
        startDragRow,
        deleteSelection,
        touch,
        resolveDataPath: bindings.resolveDataPath,
        resolveTextContent: bindings.resolveTextContent,
        resolveListItems: bindings.resolveListItems,
        resolveTableRows: bindings.resolveTableRows,
        resolveTableColumns: bindings.resolveTableColumns,
        resolveTableSummary: bindings.resolveTableSummary,
        resolveImage: bindings.resolveImage,
        resolveBlockValue: bindings.resolveBlockValue,
    };
}
