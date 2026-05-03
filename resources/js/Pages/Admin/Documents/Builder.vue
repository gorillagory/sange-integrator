<template>
    <TenantLayout>
        <template #breadcrumbs>
            <Breadcrumbs :items="[
                { label: 'Admin Settings', url: null },
                { label: 'Document Forge', url: '/admin/documents' },
                { label: isEditing ? `Edit: ${form.name}` : 'New Enterprise Template', url: null }
            ]" />
        </template>

        <div class="mb-4 flex justify-between items-start bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex items-start gap-6">
                <div>
                    <label class="block text-[9px] font-bold text-gray-500 uppercase mb-1">Template Name</label>
                    <input v-model="form.name" type="text" class="border-b border-gray-300 focus:border-[var(--brand-500)] focus:ring-0 px-0 py-1 text-lg font-bold text-gray-900 bg-transparent w-64" placeholder="e.g. Master Enterprise">
                    <p v-if="form.errors.name" class="text-red-500 text-[10px] mt-1 font-bold">{{ form.errors.name }}</p>
                </div>
                <div>
                    <label class="block text-[9px] font-bold text-gray-500 uppercase mb-1">System Code</label>
                    <input v-model="form.code" :readonly="isEditing" type="text" class="border-b border-gray-200 px-0 py-1 text-sm font-mono text-gray-500 bg-transparent w-40 outline-none" :class="isEditing ? 'opacity-50 cursor-not-allowed' : 'focus:border-[var(--brand-500)]'" placeholder="master_01">
                    <p v-if="form.errors.code" class="text-red-500 text-[10px] mt-1 font-bold">{{ form.errors.code }}</p>
                </div>
                <div>
                    <label class="block text-[9px] font-bold text-gray-500 uppercase mb-1">Type</label>
                    <select v-model="form.document_type" class="border-b border-gray-200 px-0 py-1 text-sm font-bold text-[var(--brand-600)] bg-transparent outline-none cursor-pointer">
                        <option value="invoice">Invoice</option>
                        <option value="receipt">Receipt</option>
                        <option value="quote">Quotation</option>
                        <option value="itinerary">Itinerary</option>
                    </select>
                </div>
            </div>

            <div class="flex flex-col items-end">
                <div class="flex items-center gap-3">
                    <button @click="showVectorModal = true" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-bold rounded-xl shadow-sm transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                        View JSON
                    </button>
                    <button @click="saveTemplate" :disabled="form.processing" class="px-8 py-2.5 bg-[var(--brand-600)] hover:bg-[var(--brand-500)] text-white text-sm font-bold rounded-xl shadow flex items-center gap-2 transition disabled:opacity-50">
                        <svg v-if="!form.processing" class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <svg v-else class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        {{ form.processing ? 'Syncing Forge...' : 'Sync Document Vector' }}
                    </button>
                </div>
                <p v-if="form.recentlySuccessful" class="text-emerald-500 text-[10px] font-bold mt-2 uppercase tracking-widest">Vector Synced Successfully!</p>
                <p v-if="Object.keys(form.errors).length > 0" class="text-red-500 text-[10px] font-bold mt-2 uppercase tracking-widest">Validation Failed</p>
            </div>
        </div>

        <div class="mb-2 flex justify-center items-center gap-4 bg-white py-2 px-4 rounded-xl shadow-sm border border-gray-100 w-max mx-auto">
            <div class="flex items-center gap-1 border-r border-gray-200 pr-4 mr-2">
                <button @click="undo" :disabled="!canUndo" class="p-1.5 rounded text-gray-500 hover:bg-gray-100 disabled:opacity-30 transition" title="Undo (Ctrl+Z)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                </button>
                <button @click="redo" :disabled="!canRedo" class="p-1.5 rounded text-gray-500 hover:bg-gray-100 disabled:opacity-30 transition" title="Redo (Ctrl+Y)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10h-10a8 8 0 00-8 8v2M21 10l-6 6m6-6l-6-6"></path></svg>
                </button>
            </div>
            <div class="flex items-center gap-2 bg-gray-50 rounded-lg p-1 border border-gray-200">
                <button @click="zoomOut" class="w-6 h-6 flex items-center justify-center text-gray-500 hover:bg-white rounded shadow-sm">-</button>
                <span class="text-xs font-bold text-gray-700 font-mono w-12 text-center">{{ Math.round(zoomLevel * 100) }}%</span>
                <button @click="zoomIn" class="w-6 h-6 flex items-center justify-center text-gray-500 hover:bg-white rounded shadow-sm">+</button>
                <button @click="zoomLevel = 1" class="text-[9px] font-bold uppercase text-gray-400 hover:text-gray-600 px-2">Reset</button>
            </div>
            <div class="h-4 w-px bg-gray-200"></div>
            <button @click="isPreviewMode = !isPreviewMode" class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-bold transition-colors border" :class="isPreviewMode ? 'bg-[var(--brand-500)] text-white border-[var(--brand-600)]' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                {{ isPreviewMode ? 'Exit Preview' : 'Preview PDF' }}
            </button>
        </div>

        <div class="rounded-3xl overflow-hidden shadow-xl border border-gray-200 bg-gray-200 relative h-[800px] flex">

            <div class="absolute left-0 top-0 bottom-0 z-30 group flex">
                <div class="w-12 group-hover:w-80 transition-all duration-300 ease-in-out h-full overflow-hidden bg-white border-r border-gray-200 shadow-[8px_0_20px_-5px_rgba(0,0,0,0.1)]">
                    <div class="w-80 h-full">
                        <Toolbox v-show="!isPreviewMode" />
                    </div>
                </div>
            </div>

            <div class="flex-1 w-full h-full overflow-auto pt-8 pb-12 px-20 flex justify-center items-start">
                <Canvas
                    :page="form.layout_vector.page"
                    :header="form.layout_vector.header"
                    :body="form.layout_vector.body"
                    :footer="form.layout_vector.footer"
                    :zoom="zoomLevel"
                    :isPreview="isPreviewMode"
                    @selectPage="selectPage"
                    @update="recordHistory"
                />
            </div>

            <div class="absolute right-0 top-0 bottom-0 z-30 group flex justify-end">
                <div class="w-4 h-full bg-transparent"></div>

                <div class="w-12 group-hover:w-80 transition-all duration-300 ease-in-out h-full overflow-hidden bg-white border-l border-gray-200 shadow-[-8px_0_20px_-5px_rgba(0,0,0,0.1)]">
                    <div class="w-80 h-full">
                        <Inspector
                            v-show="!isPreviewMode"
                            :activeNode="activeNode"
                            :documentType="form.document_type"
                            @update="recordHistory"
                        />
                    </div>
                </div>
            </div>

        </div>

        <div v-if="showVectorModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden border border-gray-200">
                <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h3 class="font-black text-gray-800 uppercase tracking-wider text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                        Raw Layout Vector
                    </h3>
                    <div class="flex gap-2">
                        <button @click="copyVector" class="px-4 py-2 bg-purple-50 text-purple-600 text-xs font-bold rounded-lg hover:bg-purple-100 transition shadow-sm border border-purple-100 flex items-center gap-2">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                            Copy JSON
                        </button>
                        <button @click="showVectorModal = false" class="px-4 py-2 bg-gray-200 text-gray-700 text-xs font-bold rounded-lg hover:bg-gray-300 transition shadow-sm border border-gray-300">
                            Close
                        </button>
                    </div>
                </div>
                <div class="p-4 overflow-y-auto flex-1 bg-gray-900 text-emerald-400 font-mono text-xs relative">
                    <pre class="whitespace-pre-wrap break-all">{{ JSON.stringify(form.layout_vector, null, 2) }}</pre>
                </div>
            </div>
        </div>
    </TenantLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

import TenantLayout from '../../../Layouts/TenantLayout.vue';
import Breadcrumbs from '../../../Components/UI/Breadcrumbs.vue';
import Toolbox from './Components/Toolbox.vue';
import Canvas from './Components/Canvas.vue';
import Inspector from './Components/Inspector.vue';

// Use the newly extracted Document Engine Composable
import { useDocumentEngine } from './Composables/useDocumentEngine';

const props = defineProps({ template: { type: Object, default: null } });
const isEditing = computed(() => !!props.template);

// Initialization Defaults
const ensurePageDefaults = (pageVector) => ({
    isPage: true, size: 'A4', orientation: 'portrait', margins: '20mm',
    backgroundColor: '#ffffff', watermarkText: '', watermarkOpacity: 0.1, watermarkColor: '#e5e7eb', ...pageVector
});

const defaultVector = { page: ensurePageDefaults({}), header: [], body: [], footer: [] };

const initialVector = props.template?.layout_vector
    ? (typeof props.template.layout_vector === 'string' ? JSON.parse(props.template.layout_vector) : props.template.layout_vector)
    : defaultVector;

initialVector.page = ensurePageDefaults(initialVector.page);

// Legacy migration support
if (initialVector.nodes && !initialVector.body) {
    initialVector.body = initialVector.nodes;
    initialVector.header = [];
    initialVector.footer = [];
    delete initialVector.nodes;
}

const form = useForm({
    name: props.template?.name || '',
    code: props.template?.code || '',
    document_type: props.template?.document_type || 'invoice',
    layout_vector: JSON.parse(JSON.stringify(initialVector))
});

// Bind the Engine Logic
const {
    isPreviewMode, zoomLevel, zoomIn, zoomOut,
    canUndo, canRedo, undo, redo, recordHistory,
    activeNode, selectPage
} = useDocumentEngine(form);

// Modal and Form Submission
const showVectorModal = ref(false);

const copyVector = () => {
    const text = JSON.stringify(form.layout_vector, null, 2);
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text);
        alert('JSON Vector copied to clipboard!');
    } else {
        const textArea = document.createElement("textarea");
        textArea.value = text;
        textArea.style.position = "fixed"; textArea.style.left = "-9999px";
        document.body.appendChild(textArea);
        textArea.focus(); textArea.select();
        try { document.execCommand('copy'); alert('JSON Vector copied to clipboard (fallback)!'); }
        catch (err) { console.error('Fallback copy failed', err); }
        document.body.removeChild(textArea);
    }
};

const saveTemplate = () => {
    selectPage(); // Reset selection before saving
    if (isEditing.value) {
        form.put(`/admin/documents/${props.template.id}`, { preserveScroll: true });
    } else {
        form.post('/admin/documents');
    }
};
</script>
