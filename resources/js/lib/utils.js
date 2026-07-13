import { clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs) {
    return twMerge(clsx(inputs));
}

export function formatDate(dateStr) {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('en-KE', {
        year: 'numeric', month: 'short', day: 'numeric',
    });
}

export function formatDateTime(dateStr) {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleString('en-KE', {
        year: 'numeric', month: 'short', day: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
}

export function formatTime(dateStr) {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleTimeString('en-KE', {
        hour: '2-digit', minute: '2-digit',
    });
}

export const statusColors = {
    expected: 'bg-blue-100 text-blue-800',
    checked_in: 'bg-green-100 text-green-800',
    checked_out: 'bg-gray-100 text-gray-800',
    denied: 'bg-red-100 text-red-800',
    cancelled: 'bg-yellow-100 text-yellow-800',
};
