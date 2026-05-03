<template>
    <TenantLayout>
        <template #breadcrumbs>
            <Breadcrumbs :items="[
                { label: 'Admin Settings', url: null },
                { label: 'Document Forge', url: null }
            ]" />
        </template>

        <div class="mb-8 flex justify-between items-end">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Document Forge</h1>
                <p class="text-sm text-gray-500 mt-1">Manage dynamic layout templates for all system PDFs.</p>
            </div>
            <Link href="/admin/documents/create" class="px-5 py-2.5 bg-gray-900 text-white text-sm font-bold rounded-xl shadow hover:bg-black transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Create New Template
            </Link>
        </div>

        <div v-if="!templates || templates.length === 0" class="bg-white rounded-3xl border border-gray-100 p-20 text-center shadow-sm">
            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900">No Templates Forged</h3>
            <p class="text-gray-500 text-sm mb-6 mt-1">Design your first document layout to start generating PDFs.</p>
            <Link href="/admin/documents/create" class="text-blue-600 text-sm font-bold hover:text-blue-800">Start Building &rarr;</Link>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 pb-12">
            <div v-for="template in templates" :key="template.id" class="bg-white border border-gray-100 rounded-3xl p-6 shadow-sm hover:shadow-xl transition group relative overflow-hidden">

                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-gray-700 to-black opacity-0 group-hover:opacity-100 transition"></div>

                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h3 class="font-bold text-lg text-gray-900">{{ template.name }}</h3>
                        <span class="text-[10px] font-mono text-gray-400 uppercase tracking-tighter">{{ template.code }}</span>
                    </div>
                    <span class="px-2 py-1 bg-blue-50 text-blue-600 text-[10px] font-black rounded uppercase border border-blue-100">{{ template.document_type }}</span>
                </div>

                <div class="bg-gray-50 border border-gray-100 rounded-xl p-4 mb-6 flex justify-between items-center">
                    <span class="text-xs font-bold text-gray-500 uppercase">Layout Blocks</span>
                    <span class="text-sm font-black text-gray-900">{{ getBlockCount(template.layout_vector) }} Nodes</span>
                </div>

                <div class="flex gap-2">
                    <Link :href="`/admin/documents/${template.id}/edit`" class="flex-1 text-center py-2 bg-gray-50 text-gray-700 text-xs font-bold rounded-xl hover:bg-gray-100 transition border border-gray-200">
                        Edit Layout
                    </Link>
                    <button @click="confirmDeletion(template)" class="px-4 py-2 bg-red-50 text-red-500 rounded-xl hover:bg-red-100 transition border border-red-100 hover:border-red-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </div>
            </div>
        </div>

        <ConfirmationModal
            :show="deleteState.isOpen"
            title="Shatter Document Template?"
            :message="`Are you sure you want to delete '${deleteState.activeTemplate?.name}'? This action cannot be undone.`"
            @close="deleteState.isOpen = false"
            @confirm="performDelete"
        />

        <GlobalToast />
    </TenantLayout>
</template>

<script setup>
import { reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import TenantLayout from '../../../Layouts/TenantLayout.vue';
import Breadcrumbs from '../../../Components/UI/Breadcrumbs.vue';
import ConfirmationModal from '../../../Components/UI/ConfirmationModal.vue';
import GlobalToast from '../../../Components/GlobalToast.vue';

defineProps({
    templates: {
        type: Array,
        default: () => []
    }
});

const deleteState = reactive({
    isOpen: false,
    activeTemplate: null
});

// Safely count how many logical blocks are in the layout vector
const getBlockCount = (payload) => {
    try {
        const parsed = typeof payload === 'string' ? JSON.parse(payload) : payload;
        return parsed.nodes?.length || 0;
    } catch (e) {
        return 0;
    }
};

const confirmDeletion = (template) => {
    deleteState.activeTemplate = template;
    deleteState.isOpen = true;
};

const performDelete = () => {
    router.delete(`/admin/documents/${deleteState.activeTemplate.id}`, {
        onSuccess: () => { deleteState.isOpen = false; }
    });
};
</script>
