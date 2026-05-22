import { useState, useEffect } from 'react';
import { usePage } from '@inertiajs/react';

const ICONS = {
    success: (
        <svg className="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
    ),
    error: (
        <svg className="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
    ),
    warning: (
        <svg className="w-5 h-5 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
        </svg>
    ),
    info: (
        <svg className="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
    ),
};

const BG_COLORS = {
    success: 'bg-green-50 border-green-200',
    error: 'bg-red-50 border-red-200',
    warning: 'bg-yellow-50 border-yellow-200',
    info: 'bg-blue-50 border-blue-200',
};

function ToastItem({ type, message, onClose }) {
    const [visible, setVisible] = useState(false);

    useEffect(() => {
        // Animate in
        requestAnimationFrame(() => setVisible(true));

        // Auto-dismiss after 4 seconds
        const timer = setTimeout(() => {
            setVisible(false);
            setTimeout(onClose, 300);
        }, 4000);

        return () => clearTimeout(timer);
    }, []);

    return (
        <div
            className={`flex items-center gap-3 px-4 py-3 rounded-lg border shadow-lg transition-all duration-300 ${BG_COLORS[type] || BG_COLORS.info} ${
                visible ? 'translate-x-0 opacity-100' : 'translate-x-full opacity-0'
            }`}
        >
            {ICONS[type] || ICONS.info}
            <p className="text-sm font-medium text-gray-800 flex-1">{message}</p>
            <button onClick={() => { setVisible(false); setTimeout(onClose, 300); }} className="text-gray-400 hover:text-gray-600">
                <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    );
}

export default function Toast() {
    const { flash } = usePage().props;
    const [toasts, setToasts] = useState([]);

    useEffect(() => {
        const newToasts = [];
        if (flash?.success) newToasts.push({ id: Date.now(), type: 'success', message: flash.success });
        if (flash?.error) newToasts.push({ id: Date.now() + 1, type: 'error', message: flash.error });
        if (flash?.warning) newToasts.push({ id: Date.now() + 2, type: 'warning', message: flash.warning });
        if (flash?.info) newToasts.push({ id: Date.now() + 3, type: 'info', message: flash.info });

        if (newToasts.length > 0) {
            setToasts((prev) => [...prev, ...newToasts]);
        }
    }, [flash?.success, flash?.error, flash?.warning, flash?.info]);

    function removeToast(id) {
        setToasts((prev) => prev.filter((t) => t.id !== id));
    }

    if (toasts.length === 0) return null;

    return (
        <div className="fixed top-4 right-4 z-50 space-y-2 max-w-sm w-full">
            {toasts.map((toast) => (
                <ToastItem key={toast.id} type={toast.type} message={toast.message} onClose={() => removeToast(toast.id)} />
            ))}
        </div>
    );
}
