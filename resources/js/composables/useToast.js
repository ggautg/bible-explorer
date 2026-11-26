import { ref } from 'vue';

/**
 * Cola simple de notificaciones breves para acciones dentro de la interfaz.
 */
const toasts = ref([]);
let nextId = 0;

/**
 * Composable para mostrar y consultar mensajes temporales.
 */
export function useToast() {
    /**
     * Muestra un mensaje temporal en pantalla.
     */
    function showToast(message, duration = 2600) {
        const id = nextId++;
        toasts.value.push({ id, message });

        setTimeout(() => {
            toasts.value = toasts.value.filter((t) => t.id !== id);
        }, duration);
    }

    return { toasts, showToast };
}
