import { ref } from 'vue';

// Global state outside the function means all components share the SAME toast list
const toasts = ref([]);

export function useToast() {
    const addToast = (message, type = 'success', duration = 5000) => {
        const id = Date.now() + Math.random();
        toasts.value.push({ id, message, type });

        // Auto-kill the toast after the duration
        setTimeout(() => removeToast(id), duration);
    };

    const removeToast = (id) => {
        toasts.value = toasts.value.filter(toast => toast.id !== id);
    };

    return { toasts, addToast, removeToast };
}
