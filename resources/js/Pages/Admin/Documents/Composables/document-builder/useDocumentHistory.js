// resources/js/Pages/Admin/Documents/Composables/document-builder/useDocumentHistory.js
import { computed, ref } from 'vue';
import { ensureLayoutIntegrity, snapshot } from './layoutHelpers';

export function useDocumentHistory({ form, limit = 50 }) {
    const history = ref([snapshot(form.layout_vector)]);
    const historyIndex = ref(0);
    const traversingHistory = ref(false);

    const canUndo = computed(() => historyIndex.value > 0);
    const canRedo = computed(() => historyIndex.value < history.value.length - 1);

    const recordHistory = (force = false) => {
        if (traversingHistory.value && !force) {
            return;
        }

        const current = snapshot(form.layout_vector);

        if (history.value[historyIndex.value] === current && !force) {
            return;
        }

        if (historyIndex.value < history.value.length - 1) {
            history.value = history.value.slice(0, historyIndex.value + 1);
        }

        history.value.push(current);

        if (history.value.length > limit) {
            history.value.shift();
        } else {
            historyIndex.value += 1;
        }
    };

    const undo = () => {
        if (!canUndo.value) {
            return;
        }

        traversingHistory.value = true;
        historyIndex.value -= 1;
        form.layout_vector = JSON.parse(history.value[historyIndex.value]);
        ensureLayoutIntegrity(form.layout_vector);

        setTimeout(() => {
            traversingHistory.value = false;
        }, 0);
    };

    const redo = () => {
        if (!canRedo.value) {
            return;
        }

        traversingHistory.value = true;
        historyIndex.value += 1;
        form.layout_vector = JSON.parse(history.value[historyIndex.value]);
        ensureLayoutIntegrity(form.layout_vector);

        setTimeout(() => {
            traversingHistory.value = false;
        }, 0);
    };

    const touch = () => {
        ensureLayoutIntegrity(form.layout_vector);
        recordHistory();
    };

    return {
        history,
        historyIndex,
        traversingHistory,
        canUndo,
        canRedo,
        recordHistory,
        undo,
        redo,
        touch,
    };
}
