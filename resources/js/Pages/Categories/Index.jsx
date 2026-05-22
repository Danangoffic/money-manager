import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';

export default function Index({ auth, categories }) {
    const [showForm, setShowForm] = useState(false);
    const [values, setValues] = useState({ name: '', type: 'expense', icon: '', color: '#6366f1' });

    function handleSubmit(e) {
        e.preventDefault();
        router.post(route('categories.store'), values, { onSuccess: () => { setValues({ name: '', type: 'expense', icon: '', color: '#6366f1' }); setShowForm(false); } });
    }

    function handleDelete(id) {
        if (confirm('Hapus kategori ini?')) {
            router.delete(route('categories.destroy', id));
        }
    }

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Kategori</h2>}
        >
            <Head title="Kategori" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="flex justify-end mb-4">
                        <button onClick={() => setShowForm(!showForm)} className="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">
                            {showForm ? 'Batal' : 'Tambah Kategori'}
                        </button>
                    </div>

                    {showForm && (
                        <form onSubmit={handleSubmit} className="bg-white shadow-sm sm:rounded-lg p-6 mb-6">
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <input type="text" placeholder="Nama kategori" value={values.name} onChange={e => setValues({...values, name: e.target.value})} className="rounded-md border-gray-300" required />
                                <select value={values.type} onChange={e => setValues({...values, type: e.target.value})} className="rounded-md border-gray-300">
                                    <option value="expense">Pengeluaran</option>
                                    <option value="income">Pemasukan</option>
                                </select>
                                <input type="color" value={values.color} onChange={e => setValues({...values, color: e.target.value})} className="h-10 w-20 rounded-md border-gray-300" />
                            </div>
                            <button type="submit" className="mt-4 px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">Simpan</button>
                        </form>
                    )}

                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {/* Income */}
                        <div className="bg-white shadow-sm sm:rounded-lg p-6">
                            <h3 className="text-lg font-semibold text-green-700 mb-4">Pemasukan</h3>
                            <div className="space-y-2">
                                {categories.income?.map(cat => (
                                    <div key={cat.id} className="flex items-center justify-between py-2 border-b last:border-0">
                                        <div className="flex items-center gap-2">
                                            <div className="w-3 h-3 rounded-full" style={{ backgroundColor: cat.color || '#10b981' }} />
                                            <span>{cat.name}</span>
                                        </div>
                                        <button onClick={() => handleDelete(cat.id)} className="text-sm text-red-600 hover:text-red-800">Hapus</button>
                                    </div>
                                ))}
                                {!categories.income?.length && <p className="text-gray-500 text-sm">Belum ada kategori pemasukan.</p>}
                            </div>
                        </div>

                        {/* Expense */}
                        <div className="bg-white shadow-sm sm:rounded-lg p-6">
                            <h3 className="text-lg font-semibold text-red-700 mb-4">Pengeluaran</h3>
                            <div className="space-y-2">
                                {categories.expense?.map(cat => (
                                    <div key={cat.id} className="flex items-center justify-between py-2 border-b last:border-0">
                                        <div className="flex items-center gap-2">
                                            <div className="w-3 h-3 rounded-full" style={{ backgroundColor: cat.color || '#ef4444' }} />
                                            <span>{cat.name}</span>
                                        </div>
                                        <button onClick={() => handleDelete(cat.id)} className="text-sm text-red-600 hover:text-red-800">Hapus</button>
                                    </div>
                                ))}
                                {!categories.expense?.length && <p className="text-gray-500 text-sm">Belum ada kategori pengeluaran.</p>}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
