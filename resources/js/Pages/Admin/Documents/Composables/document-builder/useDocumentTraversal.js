// resources/js/Pages/Admin/Documents/Composables/document-builder/useDocumentTraversal.js
export function useDocumentTraversal(getLayoutVector) {
    const ZONES = ['header', 'body', 'footer'];

    function layout() {
        return getLayoutVector();
    }

    function zoneNodes(zone) {
        return layout()?.[zone] || [];
    }

    function allZones() {
        return ZONES.flatMap((zone) => zoneNodes(zone));
    }

    function findNodeById(targetId, nodes = allZones()) {
        for (const node of nodes) {
            if (node.id === targetId) {
                return node;
            }

            if (node.type === 'row') {
                for (const column of node.columns || []) {
                    const found = findNodeById(targetId, column.blocks || []);
                    if (found) {
                        return found;
                    }
                }
            }
        }

        return null;
    }

    function searchContainer(nodes, targetId) {
        if (nodes.some((node) => node.id === targetId)) {
            return nodes;
        }

        for (const node of nodes) {
            if (node.type === 'row') {
                for (const column of node.columns || []) {
                    const found = searchContainer(column.blocks || [], targetId);
                    if (found) {
                        return found;
                    }
                }
            }
        }

        return null;
    }

    function findNodeContainer(targetId) {
        for (const zone of ZONES) {
            const found = searchContainer(zoneNodes(zone), targetId);

            if (found) {
                return found;
            }
        }

        return null;
    }

    function detachFromNodes(nodes, targetId) {
        for (let index = 0; index < nodes.length; index += 1) {
            if (nodes[index].id === targetId) {
                const [removed] = nodes.splice(index, 1);
                return removed;
            }

            if (nodes[index].type === 'row') {
                for (const column of nodes[index].columns || []) {
                    const removed = detachFromNodes(column.blocks || [], targetId);

                    if (removed) {
                        return removed;
                    }
                }
            }
        }

        return null;
    }

    function detachNodeById(targetId) {
        for (const zone of ZONES) {
            const removed = detachFromNodes(zoneNodes(zone), targetId);

            if (removed) {
                return removed;
            }
        }

        return null;
    }

    function removeNodeById(targetId) {
        return !!detachNodeById(targetId);
    }

    function collectNestedBlocks(node) {
        const blocks = [];

        if (node?.type === 'row') {
            for (const column of node.columns || []) {
                for (const block of column.blocks || []) {
                    blocks.push(block);

                    if (block.type === 'row') {
                        blocks.push(...collectNestedBlocks(block));
                    }
                }
            }
        }

        return blocks;
    }

    function isDescendantTarget(sourceNodeId, targetRowId) {
        if (sourceNodeId === targetRowId) {
            return true;
        }

        const sourceNode = findNodeById(sourceNodeId);

        if (!sourceNode || sourceNode.type !== 'row') {
            return false;
        }

        return !!findNodeById(targetRowId, collectNestedBlocks(sourceNode));
    }

    return {
        ZONES,
        allZones,
        findNodeById,
        findNodeContainer,
        detachNodeById,
        removeNodeById,
        collectNestedBlocks,
        isDescendantTarget,
    };
}
