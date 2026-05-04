<template>
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-300">
                {{ title }}
            </h3>

            <button
                type="button"
                class="rounded-xl border border-indigo-500/30 bg-indigo-500/10 px-4 py-2 text-sm font-semibold text-indigo-300 transition hover:bg-indigo-500/20"
                @click="addPhone"
            >
                Add Phone
            </button>
        </div>

        <div v-if="!modelValue.length" class="rounded-2xl border border-dashed border-white/10 px-4 py-8 text-center text-sm text-slate-500">
            No phones added yet.
        </div>

        <div
            v-for="(phone, index) in modelValue"
            :key="phone.uid"
            class="grid gap-4 rounded-2xl border border-white/10 bg-slate-950 p-4 md:grid-cols-4"
        >
            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-400">Label</label>
                <input
                    :value="phone.label"
                    type="text"
                    class="w-full rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white outline-none transition focus:border-indigo-500"
                    @input="updatePhone(index, 'label', $event.target.value)"
                >
            </div>

            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-400">Type</label>
                <input
                    :value="phone.type"
                    type="text"
                    class="w-full rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white outline-none transition focus:border-indigo-500"
                    placeholder="office / mobile / billing"
                    @input="updatePhone(index, 'type', $event.target.value)"
                >
            </div>

            <div class="md:col-span-2">
                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-400">Number</label>
                <div class="flex gap-3">
                    <input
                        :value="phone.number"
                        type="text"
                        class="w-full rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-white outline-none transition focus:border-indigo-500"
                        @input="updatePhone(index, 'number', $event.target.value)"
                    >

                    <button
                        type="button"
                        class="rounded-xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-sm font-semibold text-rose-300 transition hover:bg-rose-500/20"
                        @click="removePhone(index)"
                    >
                        Remove
                    </button>
                </div>
            </div>
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

const blankPhone = () => ({
    uid: `${Date.now()}_${Math.random().toString(36).slice(2, 8)}`,
    label: '',
    type: '',
    number: '',
});

const addPhone = () => {
    emit('update:modelValue', [...props.modelValue, blankPhone()]);
};

const updatePhone = (index, field, value) => {
    const next = props.modelValue.map((phone, phoneIndex) => {
        if (phoneIndex !== index) {
            return phone;
        }

        return {
            ...phone,
            [field]: value,
        };
    });

    emit('update:modelValue', next);
};

const removePhone = (index) => {
    emit('update:modelValue', props.modelValue.filter((_, phoneIndex) => phoneIndex !== index));
};
</script>
