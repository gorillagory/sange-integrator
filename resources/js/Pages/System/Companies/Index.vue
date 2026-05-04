<template>
    <SystemLayout>
        <div class="space-y-6">
            <CompanyToolbar
                v-model:search="search"
                :company-count="companies.total"
                :group-count="mainGroupCompanies.length"
                @apply-search="applySearch"
                @reset-search="resetSearch"
                @create-company="openCreate"
            />

            <CompanyTable
                :companies="companies"
                @visit-link="visitLink"
            />

            <CompanyFormModal
                :open="modalOpen"
                :main-group-companies="mainGroupCompanies"
                :industries="industries"
                @close="closeModal"
            />
        </div>
    </SystemLayout>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import SystemLayout from '@/Layouts/SystemLayout.vue';
import CompanyToolbar from './Components/CompanyToolbar.vue';
import CompanyTable from './Components/CompanyTable.vue';
import CompanyFormModal from './Components/CompanyFormModal.vue';

const props = defineProps({
    companies: {
        type: Object,
        required: true,
    },
    mainGroupCompanies: {
        type: Array,
        default: () => [],
    },
    industries: {
        type: Array,
        default: () => [],
    },
});

const search = ref('');
const modalOpen = ref(false);

watch(
    () => props.companies,
    () => {},
    { immediate: true }
);

const companies = computed(() => props.companies);

const applySearch = () => {
    router.get(
        '/companies',
        { search: search.value || undefined },
        {
            preserveState: true,
            replace: true,
            preserveScroll: true,
        }
    );
};

const resetSearch = () => {
    search.value = '';
    applySearch();
};

const visitLink = (url) => {
    if (!url) {
        return;
    }

    router.visit(url, {
        preserveScroll: true,
        preserveState: true,
    });
};

const openCreate = () => {
    modalOpen.value = true;
};

const closeModal = () => {
    modalOpen.value = false;
};
</script>
