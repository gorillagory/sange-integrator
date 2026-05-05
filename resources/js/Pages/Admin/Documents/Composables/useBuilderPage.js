import { computed, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useDocumentEngine } from './document-builder/useDocumentEngine';
import { makeBuilderPreviewPayload } from './document-builder/builderPreviewData';
import {
    cloneLayout,
    documentPreviewUrl,
    documentStoreUrl,
    documentUpdateUrl,
    normalizeCode,
    prettyType,
} from './document-builder/builderPageHelpers';

export function useBuilderPage({ props, page }) {
    const activeRailTab = ref('blocks');

    const isEditing = computed(() => !!props.template?.id);

    const form = useForm({
        name: props.template?.name ?? '',
        code: props.template?.code ?? '',
        document_type: props.template?.document_type ?? 'invoice',
        layout_vector: cloneLayout(props.template?.layout_vector ?? props.defaultLayoutVector),
        preview_payload: {},
    });

    const engine = useDocumentEngine(form);

    const activeDictionary = computed(() => props.dictionaries?.[form.document_type] ?? {});

    const firstLayoutError = computed(() => {
        const value = form.errors.layout_vector;

        if (Array.isArray(value)) {
            return value[0] ?? '';
        }

        return value ?? '';
    });

    const previewPayload = computed(() => {
        const payload = makeBuilderPreviewPayload({
            pageProps: page.props,
            documentType: form.document_type,
        });

        form.preview_payload = payload;

        return payload;
    });

    watch(engine.activeNode, (node) => {
        if (node && activeRailTab.value !== 'inspector' && !node.isPage) {
            activeRailTab.value = 'inspector';
        }
    });

    function saveTemplate() {
        const options = {
            preserveScroll: true,
            onSuccess: () => {
                engine.recordHistory(true);
            },
        };

        if (isEditing.value) {
            form.put(documentUpdateUrl(props.template.id), options);
            return;
        }

        form.post(documentStoreUrl(), options);
    }

    function openPreview() {
        if (!props.template?.id) {
            return;
        }

        window.open(
            documentPreviewUrl({
                pageProps: page.props,
                templateId: props.template.id,
            }),
            '_blank',
            'noopener,noreferrer',
        );
    }

    function togglePreviewMode() {
        engine.isPreviewMode.value = !engine.isPreviewMode.value;
    }

    function deleteActiveSelection() {
        engine.deleteSelection();
    }

    function updateCode(value) {
        form.code = normalizeCode(value);
    }

    return {
        activeRailTab,
        isEditing,
        form,
        activeDictionary,
        firstLayoutError,
        previewPayload,
        saveTemplate,
        openPreview,
        togglePreviewMode,
        deleteActiveSelection,
        updateCode,
        prettyType,
        ...engine,
    };
}
