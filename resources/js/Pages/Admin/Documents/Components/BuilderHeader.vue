<!-- resources/js/Pages/Admin/Documents/Components/BuilderHeader.vue -->
<template>
    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 px-5 py-4 lg:px-6">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                <div class="min-w-0">
                    <div class="text-[11px] font-bold uppercase tracking-[0.24em] text-slate-400">
                        Document Forge
                    </div>

                    <div class="mt-1 flex flex-wrap items-center gap-3">
                        <h1 class="truncate text-2xl font-black tracking-tight text-slate-900">
                            {{ isEditing ? (form.name || 'Edit Template') : 'New Document Template' }}
                        </h1>

                        <span class="rounded-full bg-[var(--brand-50)] px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-[var(--brand-700)]">
                            {{ prettyType(form.document_type) }}
                        </span>
                    </div>

                    <p class="mt-1 text-sm text-slate-500">
                        Left card for tools. Right card for live preview.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40"
                        :disabled="!canUndo"
                        @click="$emit('undo')"
                    >
                        Undo
                    </button>

                    <button
                        type="button"
                        class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40"
                        :disabled="!canRedo"
                        @click="$emit('redo')"
                    >
                        Redo
                    </button>

                    <button
                        type="button"
                        class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                        @click="$emit('toggle-preview-mode')"
                    >
                        {{ isPreviewMode ? 'Exit Preview' : 'Preview Mode' }}
                    </button>

                    <button
                        type="button"
                        class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40"
                        :disabled="!templateId"
                        @click="$emit('open-preview')"
                    >
                        Open PDF Preview
                    </button>

                    <button
                        type="button"
                        class="rounded-2xl bg-[var(--brand-600)] px-4 py-2 text-sm font-semibold text-white transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="form.processing"
                        @click="$emit('save')"
                    >
                        {{ form.processing ? 'Saving...' : (isEditing ? 'Save Template' : 'Create Template') }}
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-3 xl:grid-cols-[1.4fr_1fr_0.9fr]">
                <div>
                    <label class="mb-1 block text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">
                        Template Name
                    </label>
                    <input
                        v-model="form.name"
                        type="text"
                        class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm outline-none transition focus:border-[var(--brand-500)]"
                        placeholder="Bayam Travel Invoice"
                    >
                    <div v-if="form.errors.name" class="mt-1 text-xs font-medium text-rose-600">
                        {{ form.errors.name }}
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">
                        Template Code
                    </label>
                    <input
                        :value="form.code"
                        type="text"
                        class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 font-mono text-sm outline-none transition focus:border-[var(--brand-500)]"
                        placeholder="master_bt_1"
                        @input="$emit('update-code', $event.target.value)"
                    >
                    <div v-if="form.errors.code" class="mt-1 text-xs font-medium text-rose-600">
                        {{ form.errors.code }}
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">
                        Document Type
                    </label>
                    <select
                        v-model="form.document_type"
                        class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm outline-none transition focus:border-[var(--brand-500)]"
                    >
                        <option
                            v-for="type in documentTypes"
                            :key="type"
                            :value="type"
                        >
                            {{ prettyType(type) }}
                        </option>
                    </select>
                    <div v-if="form.errors.document_type" class="mt-1 text-xs font-medium text-rose-600">
                        {{ form.errors.document_type }}
                    </div>
                </div>
            </div>

            <div
                v-if="firstLayoutError"
                class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"
            >
                {{ firstLayoutError }}
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    form: {
        type: Object,
        required: true,
    },
    isEditing: {
        type: Boolean,
        default: false,
    },
    isPreviewMode: {
        type: Boolean,
        default: false,
    },
    canUndo: {
        type: Boolean,
        default: false,
    },
    canRedo: {
        type: Boolean,
        default: false,
    },
    documentTypes: {
        type: Array,
        default: () => [],
    },
    firstLayoutError: {
        type: String,
        default: '',
    },
    templateId: {
        type: [String, Number, null],
        default: null,
    },
    prettyType: {
        type: Function,
        required: true,
    },
});

defineEmits([
    'undo',
    'redo',
    'toggle-preview-mode',
    'open-preview',
    'save',
    'update-code',
]);
</script>
