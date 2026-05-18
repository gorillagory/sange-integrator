<template>
    <div class="space-y-3">
        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-300">
            {{ title }}
        </h3>

        <div class="flex items-center gap-4 rounded-2xl border border-white/10 bg-slate-950 p-4">
            <div class="h-20 w-20 overflow-hidden rounded-2xl border border-white/10 bg-slate-900">
                <img
                    v-if="previewUrl"
                    :src="previewUrl"
                    alt="Logo preview"
                    class="h-full w-full object-cover"
                >
                <div v-else class="flex h-full w-full items-center justify-center text-xs font-bold text-slate-500">
                    No Logo
                </div>
            </div>

            <div class="flex-1">
                <input
                    type="file"
                    accept="image/*"
                    class="block w-full text-sm text-slate-300 file:mr-4 file:rounded-xl file:border-0 file:bg-indigo-600 file:px-4 file:py-2 file:font-semibold file:text-white hover:file:bg-indigo-500"
                    @change="handleFileChange"
                >
                <p class="mt-2 text-xs text-slate-500">
                    Store logo as public disk file path.
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    modelValue: {
        type: [File, Object, null],
        default: null,
    },
    existingPath: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['update:modelValue']);

const previewUrl = computed(() => {
    if (props.modelValue instanceof File) {
        return URL.createObjectURL(props.modelValue);
    }

    if (props.existingPath) {
        if (String(props.existingPath).startsWith('http://') || String(props.existingPath).startsWith('https://')) {
            return props.existingPath;
        }

        return String(props.existingPath).startsWith('/storage/')
            ? props.existingPath
            : `/storage/${String(props.existingPath).replace(/^storage\//, '')}`;
    }

    return null;
});

const handleFileChange = (event) => {
    const file = event.target.files?.[0] ?? null;
    emit('update:modelValue', file);
};
</script>
