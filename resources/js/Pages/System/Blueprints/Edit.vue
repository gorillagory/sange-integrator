<template>
    <BlueprintForgeForm :schema="schema" />
</template>

<script setup>
import BlueprintForgeForm from './Components/BlueprintForgeForm.vue';

defineProps({
    schema: {
        type: Object,
        required: true,
    },
});
</script>
