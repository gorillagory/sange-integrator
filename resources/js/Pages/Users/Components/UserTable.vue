<template>
    <div class="overflow-hidden rounded-2xl border border-white/10 bg-slate-900">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-white/10">
                <thead class="bg-slate-950/80">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">User</th>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">Global Roles</th>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">Memberships</th>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">Created</th>
                    <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-slate-400">Action</th>
                </tr>
                </thead>

                <tbody class="divide-y divide-white/5">
                <tr v-if="!users.data.length">
                    <td colspan="5" class="px-6 py-10 text-center text-sm text-slate-500">
                        No users found.
                    </td>
                </tr>

                <UserTableRow
                    v-for="user in users.data"
                    :key="user.id"
                    :user="user"
                    @edit="$emit('edit-user', user)"
                />
                </tbody>
            </table>
        </div>

        <div class="flex flex-col gap-4 border-t border-white/10 px-6 py-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="text-sm text-slate-400">
                Showing {{ users.from ?? 0 }} to {{ users.to ?? 0 }} of {{ users.total }} users
            </div>

            <div class="flex flex-wrap gap-2">
                <button
                    v-for="link in users.links"
                    :key="`${link.label}_${link.url}`"
                    type="button"
                    class="rounded-xl px-4 py-2 text-sm font-semibold transition"
                    :class="link.active
                        ? 'bg-indigo-600 text-white'
                        : 'border border-white/10 text-slate-300 hover:bg-white/5'"
                    :disabled="!link.url"
                    @click="$emit('visit-link', link.url)"
                    v-html="link.label"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import UserTableRow from './UserTableRow.vue';

defineProps({
    users: {
        type: Object,
        required: true,
    },
});

defineEmits([
    'edit-user',
    'visit-link',
]);
</script>
