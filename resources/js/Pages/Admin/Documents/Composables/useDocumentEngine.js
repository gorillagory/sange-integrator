import { ref, computed, provide, onMounted, onUnmounted, watch } from 'vue';

export function useDocumentEngine(form) {
    // ==========================================
    // STATE & ZOOM
    // ==========================================
    const selectedNodeIds = ref(['page']);
    const clipboard = ref([]);
    const isPreviewMode = ref(false);
    const zoomLevel = ref(1);

    const zoomIn = () => { if (zoomLevel.value < 2) zoomLevel.value += 0.1; };
    const zoomOut = () => { if (zoomLevel.value > 0.4) zoomLevel.value -= 0.1; };

    // ==========================================
    // RECURSIVE NODE HELPERS
    // ==========================================
    const findNodeById = (id, nodesArray) => {
        for (let node of nodesArray) {
            if (node.id === id) return node;
            if (node.type === 'row') {
                for (let col of node.columns) {
                    const found = findNodeById(id, col.blocks);
                    if (found) return found;
                }
            }
        }
        return null;
    };

    const getActiveNodeData = (id) => {
        let found = findNodeById(id, form.layout_vector.header);
        if (!found) found = findNodeById(id, form.layout_vector.body);
        if (!found) found = findNodeById(id, form.layout_vector.footer);
        return found;
    };

    const findNodeContainer = (nodesArray, targetId) => {
        if (nodesArray.some(n => n.id === targetId)) return nodesArray;
        for (let node of nodesArray) {
            if (node.type === 'row') {
                for (let col of node.columns) {
                    const found = findNodeContainer(col.blocks, targetId);
                    if (found) return found;
                }
            }
        }
        return null;
    };

    const getActiveContainer = (id) => {
        let container = findNodeContainer(form.layout_vector.header, id);
        if (!container) container = findNodeContainer(form.layout_vector.body, id);
        if (!container) container = findNodeContainer(form.layout_vector.footer, id);
        return container;
    };

    const removeNodeById = (nodesArray, targetId) => {
        for (let i = 0; i < nodesArray.length; i++) {
            if (nodesArray[i].id === targetId) { nodesArray.splice(i, 1); return true; }
            if (nodesArray[i].type === 'row') {
                for (let col of nodesArray[i].columns) {
                    if (removeNodeById(col.blocks, targetId)) return true;
                }
            }
        }
        return false;
    };

    // ==========================================
    // HISTORY ENGINE (UNDO / REDO)
    // ==========================================
    const history = ref([JSON.stringify(form.layout_vector)]);
    const historyIndex = ref(0);
    const isTraversingTime = ref(false);

    const canUndo = computed(() => historyIndex.value > 0);
    const canRedo = computed(() => historyIndex.value < history.value.length - 1);

    const recordHistory = () => {
        if (isTraversingTime.value) return;
        if (historyIndex.value < history.value.length - 1) history.value = history.value.slice(0, historyIndex.value + 1);
        history.value.push(JSON.stringify(form.layout_vector));
        if (history.value.length > 50) history.value.shift();
        else historyIndex.value++;
    };

    const undo = () => {
        if (!canUndo.value) return;
        isTraversingTime.value = true;
        historyIndex.value--;
        form.layout_vector = JSON.parse(history.value[historyIndex.value]);
        setTimeout(() => { isTraversingTime.value = false; }, 50);
    };

    const redo = () => {
        if (!canRedo.value) return;
        isTraversingTime.value = true;
        historyIndex.value++;
        form.layout_vector = JSON.parse(history.value[historyIndex.value]);
        setTimeout(() => { isTraversingTime.value = false; }, 50);
    };

    // ==========================================
    // CLIPBOARD ENGINE
    // ==========================================
    const regenerateIds = (item) => {
        const clone = JSON.parse(JSON.stringify(item));
        clone.id = clone.type + '_' + Date.now() + Math.floor(Math.random() * 10000);
        if (clone.type === 'row') {
            clone.columns.forEach((col, i) => {
                col.id = 'col_' + Date.now() + '_' + i;
                if (col.blocks) col.blocks = col.blocks.map(b => regenerateIds(b));
            });
        }
        return clone;
    };

    const copySelection = () => {
        const nodesToCopy = [];
        selectedNodeIds.value.forEach(id => {
            if (id !== 'page') {
                const node = getActiveNodeData(id);
                if (node) nodesToCopy.push(JSON.parse(JSON.stringify(node)));
            }
        });
        clipboard.value = nodesToCopy;
    };

    const deleteSelection = () => {
        selectedNodeIds.value.forEach(id => {
            if (id !== 'page') {
                removeNodeById(form.layout_vector.header, id);
                removeNodeById(form.layout_vector.body, id);
                removeNodeById(form.layout_vector.footer, id);
            }
        });
        selectedNodeIds.value = ['page'];
        recordHistory();
    };

    const cutSelection = () => { copySelection(); deleteSelection(); };

    const pasteClipboard = () => {
        if (!clipboard.value.length) return;
        let targetArray = form.layout_vector.body;
        if (selectedNodeIds.value.length > 0 && selectedNodeIds.value[0] !== 'page') {
            const container = getActiveContainer(selectedNodeIds.value[0]);
            if (container) targetArray = container;
        }
        const newIds = [];
        clipboard.value.forEach(item => {
            const regenerated = regenerateIds(item);
            targetArray.push(regenerated);
            newIds.push(regenerated.id);
        });
        selectedNodeIds.value = newIds;
        recordHistory();
    };

    // ==========================================
    // KEYBOARD SHORTCUTS
    // ==========================================
    const handleKeydown = (e) => {
        if (['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)) return;
        if ((e.ctrlKey || e.metaKey) && e.key === 'z') { e.preventDefault(); undo(); }
        if ((e.ctrlKey || e.metaKey) && e.key === 'y') { e.preventDefault(); redo(); }
        if ((e.ctrlKey || e.metaKey) && e.key === 'c') { e.preventDefault(); copySelection(); }
        if ((e.ctrlKey || e.metaKey) && e.key === 'x') { e.preventDefault(); cutSelection(); }
        if ((e.ctrlKey || e.metaKey) && e.key === 'v') { e.preventDefault(); pasteClipboard(); }
        if (e.key === 'Delete' || e.key === 'Backspace') { e.preventDefault(); deleteSelection(); }
    };

    onMounted(() => { window.addEventListener('keydown', handleKeydown); });
    onUnmounted(() => { window.removeEventListener('keydown', handleKeydown); });

    // ==========================================
    // EXPORTS & PROVIDERS
    // ==========================================
    const selectPage = () => { selectedNodeIds.value = ['page']; };
    watch(isPreviewMode, (val) => { if (val) selectedNodeIds.value = []; });

    const activeNode = computed(() => {
        if (selectedNodeIds.value.length === 0) return null;
        const lastId = selectedNodeIds.value[selectedNodeIds.value.length - 1];
        if (lastId === 'page') return form.layout_vector.page;
        return getActiveNodeData(lastId);
    });

    provide('documentBuilder', {
        selectedNodeIds,
        isPreviewMode,
        setActiveNode: (node, multi = false) => {
            if (isPreviewMode.value) return;
            const id = node.isPage ? 'page' : node.id;
            if (multi) {
                if (selectedNodeIds.value.includes(id)) selectedNodeIds.value = selectedNodeIds.value.filter(nId => nId !== id);
                else selectedNodeIds.value.push(id);
            } else {
                selectedNodeIds.value = [id];
            }
        },
        removeNode: (id) => {
            removeNodeById(form.layout_vector.header, id);
            removeNodeById(form.layout_vector.body, id);
            removeNodeById(form.layout_vector.footer, id);
            selectedNodeIds.value = selectedNodeIds.value.filter(nId => nId !== id);
            if (selectedNodeIds.value.length === 0) selectedNodeIds.value = ['page'];
            recordHistory();
        },
        forceUpdate: () => recordHistory()
    });

    return {
        selectedNodeIds, isPreviewMode, zoomLevel, zoomIn, zoomOut,
        canUndo, canRedo, undo, redo, recordHistory, activeNode, selectPage
    };
}
