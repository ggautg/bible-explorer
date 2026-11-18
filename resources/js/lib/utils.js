import { clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

/**
 * Combina clases CSS condicionales y resuelve conflictos de Tailwind.
 */
export function cn(...inputs) {
    return twMerge(clsx(inputs));
}

/**
 * Normaliza una URL proveniente de Inertia a cadena de texto.
 */
export function toUrl(href) {
    return typeof href === 'string' ? href : href?.url;
}
