<!-- resources/js/Pages/Admin/Documents/Builder.vue -->
<template>
    <TenantLayout>
        <div class="relative flex h-[calc(100vh-8rem)] min-h-[780px] flex-col gap-4 overflow-hidden">
            <BuilderHeader
                :form="form"
                :is-editing="isEditing"
                :is-preview-mode="isPreviewMode"
                :can-undo="canUndo"
                :can-redo="canRedo"
                :is-dirty="form.isDirty"
                :document-types="documentTypes"
                :first-layout-error="firstLayoutError"
                :template-id="props.template?.id"
                :pretty-type="prettyType"
                @undo="undo"
                @redo="redo"
                @toggle-preview-mode="togglePreviewMode"
                @open-preview="openPreview"
                @save="saveTemplate"
                @save-and-exit="saveTemplateAndExit"
                @update-code="updateCode"
            />

            <div class="grid min-h-0 flex-1 grid-cols-1 gap-4 xl:grid-cols-[380px_minmax(0,_1fr)] xl:items-stretch">
                <BuilderSidebar
                    v-model:active-tab="activeRailTab"
                    :document-type="form.document_type"
                    :font-options="fontOptions"
                    :dictionaries="dictionaries"
                    :active-dictionary="activeDictionary"
                    :active-node="activeNode"
                    :selected-node-ids="selectedNodeIds"
                    @add-block="addBlock"
                    @add-row="addRow"
                    @start-drag-block="startDragBlock"
                    @start-drag-row="startDragRow"
                    @touch="touch"
                    @delete-selection="deleteActiveSelection"
                />

                <BuilderPreviewPane
                    :layout-vector="form.layout_vector"
                    :font-options="fontOptions"
                    :zoom-level="zoomLevel"
                    :is-preview-mode="isPreviewMode"
                    :preview-payload="previewPayload"
                    :compiled-preview-html="compiledPreviewHtml"
                    :preview-loading="previewLoading"
                    :preview-error="previewError"
                    @select-page="selectPage"
                    @zoom-in="zoomIn"
                    @zoom-out="zoomOut"
                />
            </div>

            <FloatingSmartMapping
                v-model:open="smartMappingOpen"
                :active-node="activeNode"
                :dictionary="activeDictionary"
                @update="touch"
            />
        </div>

        <GlobalToast />
    </TenantLayout>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3';
import GlobalToast from '@/Components/GlobalToast.vue';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import BuilderHeader from './Components/BuilderHeader.vue';
import BuilderPreviewPane from './Components/BuilderPreviewPane.vue';
import BuilderSidebar from './Components/BuilderSidebar.vue';
import FloatingSmartMapping from './Components/FloatingSmartMapping.vue';
import { useBuilderPage } from './Composables/useBuilderPage';
import { ref } from 'vue';

const page = usePage();

const props = defineProps({
    template: {
        type: Object,
        default: null,
    },
    dictionaries: {
        type: Object,
        default: () => ({}),
    },
    documentTypes: {
        type: Array,
        default: () => ['invoice', 'receipt', 'quote', 'itinerary'],
    },
    fontOptions: {
        type: Array,
        default: () => [],
    },
    defaultLayoutVector: {
        type: Object,
        required: true,
    },
});

const smartMappingOpen = ref(true);

const {
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

    selectedNodeIds,
    isPreviewMode,
    zoomLevel,
    zoomIn,
    zoomOut,
    canUndo,
    canRedo,
    undo,
    redo,
    activeNode,
    selectPage,
    addBlock,
    addRow,
    startDragBlock,
    startDragRow,
    touch,
} = useBuilderPage({
    props,
    page,
});
</script>
