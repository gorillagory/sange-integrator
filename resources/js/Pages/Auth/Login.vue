<template>
    <div class="min-h-screen flex items-center justify-center bg-[#0f172a]">
        <div class="bg-[#1e293b] p-8 rounded-2xl shadow-xl w-full max-w-md border border-white/10">
            <h2 class="text-2xl font-bold text-white mb-6 text-center">System Login</h2>

            <div
                v-if="form.errors.email || form.errors.password"
                class="mb-4 rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300"
            >
                {{ form.errors.email || form.errors.password }}
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Email</label>
                    <input
                        v-model="form.email"
                        type="email"
                        class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-2 text-white focus:border-indigo-500"
                        required
                    >
                    <span v-if="form.errors.email" class="text-red-400 text-xs mt-1 block">
                        {{ form.errors.email }}
                    </span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Password</label>
                    <input
                        v-model="form.password"
                        type="password"
                        class="w-full bg-black/20 border border-white/10 rounded-lg px-4 py-2 text-white focus:border-indigo-500"
                        required
                    >
                    <span v-if="form.errors.password" class="text-red-400 text-xs mt-1 block">
                        {{ form.errors.password }}
                    </span>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full py-3 mt-4 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl transition shadow-lg shadow-indigo-500/20 disabled:opacity-50"
                >
                    {{ form.processing ? 'Authenticating...' : 'Authenticate' }}
                </button>
            </form>
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    email: 'admin@bayam.test',
    password: 'password',
    remember: false,
});

const submit = () => {
    form.post('/login');
};
</script>
