<template>
    <div
        class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm"
        :class="{ 'pointer-events-none opacity-50': disabled }"
    >
        <div class="mb-6 flex items-center gap-2">
            <div class="flex h-6 w-6 items-center justify-center rounded-full bg-[var(--brand-100)] text-xs font-bold text-[var(--brand-700)]">
                2
            </div>
            <div>
                <h3 class="font-bold text-gray-900">Client Remarks</h3>
                <p class="mt-1 text-xs text-gray-500">
                    Reuse client-specific guidance, then keep an editable snapshot on this service record.
                </p>
            </div>
        </div>

        <div v-if="!client" class="rounded-xl border border-dashed border-gray-300 bg-gray-50 px-5 py-6 text-sm text-gray-500">
            Select a corporate entity first to load its saved remarks and operational notes.
        </div>

        <div v-else class="space-y-5">
            <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end">
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-gray-600">Saved Client Remark</label>
                    <select
                        :value="modelValue.presetId ?? ''"
                        class="w-full rounded-xl px-4 py-3 text-sm text-gray-900"
                        :class="presetError ? 'border border-amber-300 bg-amber-50/70' : 'border border-gray-200 bg-gray-50 focus:border-[var(--brand-500)] focus:bg-white'"
                        @change="$emit('update:preset-id', normalizeNullable($event.target.value))"
                    >
                        <option :value="''">No preset selected</option>
                        <option
                            v-for="preset in presets"
                            :key="preset.id"
                            :value="preset.id"
                        >
                            {{ preset.title }}
                        </option>
                    </select>
                    <p v-if="presetError" class="mt-1.5 text-xs font-medium text-amber-700">{{ presetError }}</p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <button
                        type="button"
                        class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-xs font-bold text-gray-700 transition hover:border-[var(--brand-200)] hover:bg-[var(--brand-50)] hover:text-[var(--brand-700)]"
                        @click="$emit('create-preset')"
                    >
                        New Preset
                    </button>
                    <button
                        type="button"
                        class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-xs font-bold text-gray-700 transition hover:border-[var(--brand-200)] hover:bg-[var(--brand-50)] hover:text-[var(--brand-700)] disabled:cursor-not-allowed disabled:opacity-40"
                        :disabled="!activePreset"
                        @click="$emit('edit-preset', activePreset)"
                    >
                        Edit Preset
                    </button>
                    <button
                        type="button"
                        class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-xs font-bold text-rose-700 transition hover:bg-rose-100 disabled:cursor-not-allowed disabled:opacity-40"
                        :disabled="!activePreset"
                        @click="$emit('delete-preset', activePreset)"
                    >
                        Delete Preset
                    </button>
                </div>
            </div>

            <div v-if="activePreset" class="rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-xs text-blue-900">
                <div class="font-bold">{{ activePreset.title }}</div>
                <div class="mt-1 whitespace-pre-line text-blue-800">{{ activePreset.content }}</div>
                <button
                    type="button"
                    class="mt-3 rounded-lg border border-blue-200 bg-white px-3 py-1.5 text-[11px] font-bold text-blue-700 transition hover:bg-blue-100"
                    @click="$emit('apply-preset', activePreset)"
                >
                    Use This Preset In Record
                </button>
            </div>

            <div
                v-if="editor.open"
                class="rounded-2xl border border-[var(--brand-200)] bg-[var(--brand-50)] p-5"
            >
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-sm font-bold text-[var(--brand-800)]">
                            {{ editor.mode === 'create' ? 'Create client remark preset' : 'Edit client remark preset' }}
                        </div>
                        <div class="mt-1 text-xs text-[var(--brand-700)]">
                            Presets stay tied to {{ client.name }} and can be reused in future service records.
                        </div>
                    </div>
                    <button
                        type="button"
                        class="rounded-lg p-1.5 text-gray-400 transition hover:bg-white/80 hover:text-gray-700"
                        @click="$emit('cancel-editor')"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="mt-4 space-y-4">
                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase text-[var(--brand-700)]">Preset Title</label>
                        <input
                            :value="editor.title"
                            type="text"
                            class="w-full rounded-xl border border-[var(--brand-200)] bg-white px-4 py-2.5 text-sm text-gray-900"
                            @input="$emit('update-editor', { title: $event.target.value })"
                        >
                        <p v-if="editor.errors.title" class="mt-1.5 text-xs font-medium text-amber-700">{{ editor.errors.title }}</p>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase text-[var(--brand-700)]">Preset Content</label>
                        <textarea
                            :value="editor.content"
                            rows="5"
                            class="w-full rounded-xl border border-[var(--brand-200)] bg-white px-4 py-3 text-sm text-gray-900"
                            placeholder="Patient EID:&#10;-&#10;Travel code:&#10;-&#10;Special billing note:&#10;-"
                            @input="$emit('update-editor', { content: $event.target.value })"
                        />
                        <p v-if="editor.errors.content" class="mt-1.5 text-xs font-medium text-amber-700">{{ editor.errors.content }}</p>
                    </div>

                    <div class="flex justify-end gap-2">
                        <button
                            type="button"
                            class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-xs font-bold text-gray-700 transition hover:bg-gray-50"
                            @click="$emit('cancel-editor')"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            class="rounded-xl bg-[var(--brand-600)] px-4 py-2.5 text-xs font-bold text-white transition hover:bg-[var(--brand-500)]"
                            @click="$emit('save-preset')"
                        >
                            {{ editor.mode === 'create' ? 'Save Preset' : 'Update Preset' }}
                        </button>
                    </div>
                </div>
            </div>

            <div>
                <label class="mb-2 block text-xs font-bold uppercase text-gray-600">Service Record Remarks</label>
                <textarea
                    :value="modelValue.remarks"
                    rows="6"
                    class="w-full rounded-2xl px-4 py-3 text-sm text-gray-900"
                    :class="remarksError ? 'border border-amber-300 bg-amber-50/70' : 'border border-gray-200 bg-gray-50 focus:border-[var(--brand-500)] focus:bg-white'"
                    placeholder="Add client-specific instructions, patient identifiers, travel codes, or operational notes for this record."
                    @input="$emit('update:remarks', $event.target.value)"
                />
                <p v-if="remarksError" class="mt-1.5 text-xs font-medium text-amber-700">{{ remarksError }}</p>
                <p class="mt-2 text-[11px] text-gray-500">
                    This text is stored directly on the service record as a snapshot, even if the original preset changes later.
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    disabled: {
        type: Boolean,
        default: false,
    },
    client: {
        type: Object,
        default: null,
    },
    presets: {
        type: Array,
        default: () => [],
    },
    modelValue: {
        type: Object,
        required: true,
    },
    editor: {
        type: Object,
        default: () => ({
            open: false,
            mode: 'create',
            title: '',
            content: '',
            errors: {},
        }),
    },
    presetError: {
        type: String,
        default: '',
    },
    remarksError: {
        type: String,
        default: '',
    },
});

defineEmits([
    'update:preset-id',
    'update:remarks',
    'create-preset',
    'edit-preset',
    'delete-preset',
    'apply-preset',
    'save-preset',
    'cancel-editor',
    'update-editor',
]);

const activePreset = computed(() => {
    return props.presets.find((preset) => String(preset.id) === String(props.modelValue.presetId)) || null;
});

function normalizeNullable(value) {
    return value === '' ? null : Number(value);
}
</script>
