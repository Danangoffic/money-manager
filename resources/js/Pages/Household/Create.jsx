import { Head, router } from '@inertiajs/react';
import { useState } from 'react';

export default function Create() {
    const [name, setName] = useState('');

    function handleSubmit(e) {
        e.preventDefault();
        router.post(route('household.store'), { name });
    }

    return (
        <div className="min-h-screen flex items-center justify-center bg-gray-100">
            <Head title="Buat Household" />
            <div className="max-w-md w-full bg-white rounded-lg shadow-sm p-8">
                <h1 className="text-2xl font-bold text-gray-900 mb-2">Buat Household</h1>
                <p className="text-gray-500 mb-6">Beri nama household Anda untuk mulai mengelola keuangan.</p>
                <form onSubmit={handleSubmit} className="space-y-4">
                    <input
                        type="text"
                        value={name}
                        onChange={e => setName(e.target.value)}
                        placeholder="Contoh: Keluarga Budi"
                        className="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        required
                    />
                    <button type="submit" className="w-full px-4 py-2 bg-indigo-600 text-white rounded-md font-medium hover:bg-indigo-700">
                        Buat Household
                    </button>
                </form>
            </div>
        </div>
    );
}
