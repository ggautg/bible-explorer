import { router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';

const ALLOWED_TYPES = ['success', 'error', 'info', 'warning'];

export function initializeFlashToast() {
    router.on('flash', (event) => {
        const flash = event.detail?.flash;
        const data = flash?.toast;

        if (!data) {
            return;
        }

        const type = ALLOWED_TYPES.includes(data.type) ? data.type : 'info';

        toast[type](data.message);
    });
}
