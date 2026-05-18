import { computed, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useToast } from '@/Composables/useToast';
import { useDocumentEngine } from './document-builder/useDocumentEngine';
import { makeBuilderPreviewPayload } from './document-builder/builderPreviewData';
import {
    cloneLayout,
    documentPreviewHtmlUrl,
    documentPreviewUrl,
    documentStoreUrl,
    documentUpdateUrl,
    normalizeCode,
    prettyType,
} from './document-builder/builderPageHelpers';

export function useBuilderPage({ props, page }) {
    const activeRailTab = ref('blocks');
    const codeTouched = ref(hasManualCode(props.template));
    const compiledPreviewHtml = ref('');
    const previewLoading = ref(false);
    const previewError = ref('');
    const { addToast } = useToast();
    let previewDebounce = null;
    let previewRequestId = 0;

    const isEditing = computed(() => !!props.template?.id);

    const form = useForm({
        name: props.template?.name ?? '',
        code: props.template?.code ?? '',
        document_type: props.template?.document_type ?? 'invoice',
        layout_vector: cloneLayout(props.template?.layout_vector ?? props.defaultLayoutVector),
        preview_payload: {},
        exit_after_save: false,
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

    watch(() => form.name, (value) => {
        if (!codeTouched.value) {
            form.code = normalizeCode(value);
        }
    }, { immediate: true });

    watch(
        () => JSON.stringify({
            name: form.name,
            code: form.code,
            document_type: form.document_type,
            layout_vector: form.layout_vector,
        }),
        () => {
            if (engine.isPreviewMode.value) {
                scheduleCompiledPreview();
            }
        },
    );

    watch(engine.isPreviewMode, (value) => {
        if (value) {
            scheduleCompiledPreview(true);
            return;
        }

        previewError.value = '';
    });

    function saveTemplate(exitAfterSave = false) {
        if (!codeTouched.value) {
            form.code = normalizeCode(form.name);
        }

        form.exit_after_save = exitAfterSave;

        const options = {
            preserveScroll: true,
            onSuccess: () => {
                engine.recordHistory(true);

                if (!exitAfterSave) {
                    addToast(
                        isEditing.value ? 'Template saved successfully.' : 'Template created successfully.',
                        'success',
                        3000,
                    );
                }
            },
            onError: () => {
                addToast('Could not save this template yet. Please review the highlighted fields.', 'error', 4000);
            },
            onFinish: () => {
                form.exit_after_save = false;
            },
        };

        if (isEditing.value) {
            form.put(documentUpdateUrl(props.template.id), options);
            return;
        }

        form.post(documentStoreUrl(), options);
    }

    function saveTemplateAndExit() {
        saveTemplate(true);
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
        codeTouched.value = true;
        form.code = normalizeCode(value);
    }

    function scheduleCompiledPreview(immediate = false) {
        clearTimeout(previewDebounce);
        previewDebounce = setTimeout(() => {
            requestCompiledPreview();
        }, immediate ? 0 : 300);
    }

    async function requestCompiledPreview() {
        const requestId = ++previewRequestId;

        previewLoading.value = true;
        previewError.value = '';

        try {
            const response = await window.axios.post(documentPreviewHtmlUrl(), {
                name: form.name,
                code: form.code,
                document_type: form.document_type,
                layout_vector: form.layout_vector,
            });

            if (requestId !== previewRequestId) {
                return;
            }

            compiledPreviewHtml.value = response.data;
        } catch (error) {
            if (requestId !== previewRequestId) {
                return;
            }

            compiledPreviewHtml.value = '';
            previewError.value = error?.response?.data?.message || 'Could not compile the printable preview right now.';
        } finally {
            if (requestId === previewRequestId) {
                previewLoading.value = false;
            }
        }
    }

    return {
        activeRailTab,
        isEditing,
        form,
        activeDictionary,
        firstLayoutError,
        previewPayload,
        compiledPreviewHtml,
        previewLoading,
        previewError,
        saveTemplate,
        saveTemplateAndExit,
        openPreview,
        togglePreviewMode,
        deleteActiveSelection,
        updateCode,
        prettyType,
        ...engine,
    };
}

function hasManualCode(template) {
    if (!template) {
        return false;
    }

    const normalizedName = normalizeCode(template.name || '');
    const currentCode = normalizeCode(template.code || '');

    if (!currentCode) {
        return false;
    }

    return currentCode !== normalizedName;
}
