<template>
    <SystemLayout>
        <div class="space-y-6">
            <UserToolbar
                v-model:search="search"
                :user-count="users.total"
                :company-count="companies.length"
                :global-role-count="globalRoles.length"
                :tenant-role-count="tenantRoles.length"
                @apply-search="applySearch"
                @reset-search="resetSearch"
                @create-user="openCreate"
            />

            <UserTable
                :users="users"
                @edit-user="openEdit"
                @visit-link="visitLink"
            />

            <UserFormModal
                :open="modalOpen"
                :mode="modalMode"
                :user="selectedUser"
                :companies="companies"
                :global-roles="globalRoles"
                :tenant-roles="tenantRoles"
                @close="closeModal"
            />
        </div>
    </SystemLayout>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import SystemLayout from '@/Layouts/SystemLayout.vue';
import UserFormModal from './Components/UserFormModal.vue';
import UserTable from './Components/UserTable.vue';
import UserToolbar from './Components/UserToolbar.vue';

const props = defineProps({
    users: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    companies: {
        type: Array,
        default: () => [],
    },
    globalRoles: {
        type: Array,
        default: () => [],
    },
    tenantRoles: {
        type: Array,
        default: () => [],
    },
});

const search = ref(props.filters.search ?? '');
const modalOpen = ref(false);
const modalMode = ref('create');
const selectedUser = ref(null);

watch(
    () => props.filters.search,
    (value) => {
        search.value = value ?? '';
    }
);

const users = computed(() => props.users);

const applySearch = () => {
    router.get(
        '/users',
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
    modalMode.value = 'create';
    selectedUser.value = null;
    modalOpen.value = true;
};

const openEdit = (user) => {
    modalMode.value = 'edit';
    selectedUser.value = user;
    modalOpen.value = true;
};

const closeModal = () => {
    modalOpen.value = false;
    selectedUser.value = null;
};
</script>
