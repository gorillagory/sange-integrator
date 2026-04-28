<template>
    <TenantLayout>
        <template #breadcrumbs>
            <span class="text-gray-400">System</span>
            <svg class="w-4 h-4 mx-2 text-gray-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
            <span class="text-[var(--brand-600)] font-semibold">User Management</span>
        </template>

        <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">System Users</h1>
                <p class="text-sm text-gray-500 mt-1">Manage personnel access and roles within the vault.</p>
            </div>

            <PrimaryButton @click="openCreateModal">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                New User
            </PrimaryButton>
        </div>

        <DataCard>
            <template #header>
                <div class="font-medium text-gray-900">Active Personnel</div>

                <div class="w-full sm:w-72">
                    <TextInput
                        v-model="searchQuery"
                        placeholder="Search by name, email, or ID..."
                        class="!py-2 !text-sm"
                    >
                        <template #icon>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </template>
                    </TextInput>
                </div>
            </template>

            <table class="w-full text-left border-collapse">
                <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-xs uppercase tracking-wider text-gray-500">
                    <th class="px-6 py-4 font-medium">User Profile</th>
                    <th class="px-6 py-4 font-medium">Role</th>
                    <th class="px-6 py-4 font-medium">Status</th>
                    <th class="px-6 py-4 font-medium text-right">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                <tr class="hover:bg-gray-50/50 transition-colors group">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-full flex items-center justify-center font-bold border bg-[var(--brand-50)] text-[var(--brand-600)] border-[var(--brand-100)]">
                                AD
                            </div>
                            <div>
                                <div class="font-medium text-gray-900">Admin Director</div>
                                <div class="text-xs text-gray-500">admin@bayam.test</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">Super Administrator</td>
                    <td class="px-6 py-4">
                        <Badge type="success">Active</Badge>
                    </td>
                    <td class="px-6 py-4 text-right text-sm font-medium">
                        <button class="text-[var(--brand-600)] hover:text-[var(--brand-900)] transition-colors">Edit</button>
                    </td>
                </tr>
                </tbody>
            </table>

            <template #footer>
                <div class="text-xs text-gray-500 flex justify-between items-center">
                    <span>Showing 1 to 1 of 1 users</span>
                    <div class="flex gap-2">
                        <button class="px-3 py-1 border border-gray-200 rounded-lg hover:bg-gray-50 disabled:opacity-50">Prev</button>
                        <button class="px-3 py-1 border border-gray-200 rounded-lg hover:bg-gray-50 disabled:opacity-50">Next</button>
                    </div>
                </div>
            </template>
        </DataCard>
    </TenantLayout>
</template>

<script setup>
import { ref } from 'vue';
import TenantLayout from '../../Layouts/TenantLayout.vue';
import PrimaryButton from '../../Components/Form/PrimaryButton.vue';
import TextInput from '../../Components/Form/TextInput.vue';
import DataCard from '../../Components/UI/DataCard.vue';
import Badge from '../../Components/UI/Badge.vue';

const searchQuery = ref('');

const openCreateModal = () => {
    // We will wire this to a modal or page navigation shortly
    alert("Trigger Create User Protocol");
};
</script>
