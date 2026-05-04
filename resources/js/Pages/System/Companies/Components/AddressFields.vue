<template>
    <div class="space-y-4">
        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-300">
            {{ title }}
        </h3>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-400">Line 1</label>
                <input
                    :value="modelValue.line1"
                    type="text"
                    class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-white outline-none transition focus:border-indigo-500"
                    @input="updateField('line1', $event.target.value)"
                >
            </div>

            <div class="md:col-span-2">
                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-400">Line 2</label>
                <input
                    :value="modelValue.line2"
                    type="text"
                    class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-white outline-none transition focus:border-indigo-500"
                    @input="updateField('line2', $event.target.value)"
                >
            </div>

            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-400">City</label>
                <input
                    :value="modelValue.city"
                    type="text"
                    class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-white outline-none transition focus:border-indigo-500"
                    @input="updateField('city', $event.target.value)"
                >
            </div>

            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-400">State</label>
                <input
                    :value="modelValue.state"
                    type="text"
                    class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-white outline-none transition focus:border-indigo-500"
                    @input="updateField('state', $event.target.value)"
                >
            </div>

            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-400">Postcode</label>
                <input
                    :value="modelValue.postcode"
                    type="text"
                    class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-white outline-none transition focus:border-indigo-500"
                    @input="updateField('postcode', $event.target.value)"
                >
            </div>

            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-400">Country</label>
                <input
                    :value="modelValue.country"
                    type="text"
                    class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-white outline-none transition focus:border-indigo-500"
                    @input="updateField('country', $event.target.value)"
                >
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
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['update:modelValue']);

const updateField = (field, value) => {
    emit('update:modelValue', {
        ...props.modelValue,
        [field]: value,
    });
};
</script>
