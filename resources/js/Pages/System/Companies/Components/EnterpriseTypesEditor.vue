<template>
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-300">
                {{ title }}
            </h3>

            <button
                type="button"
                class="rounded-xl border border-indigo-500/30 bg-indigo-500/10 px-4 py-2 text-sm font-semibold text-indigo-300 transition hover:bg-indigo-500/20"
                @click="addType"
            >
                Add Type
            </button>
        </div>

        <div v-if="!modelValue.length" class="rounded-2xl border border-dashed border-white/10 px-4 py-8 text-center text-sm text-slate-500">
            No enterprise types added yet.
        </div>

        <div
            v-for="(type, index) in modelValue"
            :key="`${index}_${type}`"
            class="flex gap-3 rounded-2xl border border-white/10 bg-slate-950 p-4"
        >
            <input
                :value="type"
                type="text"
                class="w-full rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white outline-none transition focus:border-indigo-500"
                placeholder="Holding, Hospitality, Travel, Medical, Catering"
                @input="updateType(index, $event.target.value)"
            >

            <button
                type="button"
                class="rounded-xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-sm font-semibold text-rose-300 transition hover:bg-rose-500/20"
                @click="removeType(index)"
            >
                Remove
            </button>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    modelValue: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['update:modelValue']);

const addType = () => {
    emit('update:modelValue', [...props.modelValue, '']);
};

const updateType = (index, value) => {
    const next = props.modelValue.map((item, itemIndex) => {
        if (itemIndex !== index) {
            return item;
        }

        return value;
    });

    emit('update:modelValue', next);
};

const removeType = (index) => {
    emit('update:modelValue', props.modelValue.filter((_, itemIndex) => itemIndex !== index));
};
</script>
