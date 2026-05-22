import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';

function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);
}

const ACCOUNT_TYPES = [
    { value: 'cash', label: 'Cash' },
    { value: 'bank', label: 'Bank' },
    { value: 'e-wallet', label: 'E-Wallet' },
    { value: 'credit-card', label: 'Credit Card' },
];

export default function Index({ auth, accounts, totalBalance }) {
    const [showForm, setShowForm] = useState(false);
    const [editingId, setEditingId] = useState(null);
    const [values, setValues] = useState({ name: '', type: 'cash', balance: 0, icon: '' });

    function handleSubmit(e) {
        e.preventDefault();
        if (editingId) {
            router.put(route('accounts.update', editingId), values, { onSuccess: () => resetForm() });
        } else {
            router.post(route('accounts.store'), values, { onSuccess: () => resetForm() });
        }
    }

    function resetForm() {
        setValues({ name: '', type: 'cash', balance: 0, icon: '' });
        setShowForm(false);
        setEditingId(null);
    }

    function startEdit(account) {
        setValues({ name: account.name, type: account.type, balance: account.balance, icon: account.icon || '' });
        setEditingId(account.id);
        setShowForm(true);
    }

    function handleDelete(id) {
        if (confirm('Hapus akun ini?')) {
            router.delete(route('accounts.destroy', id));
        }
    }

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Akun</h2>}
        >
            <Head title="Akun" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white shadow-sm sm:rounded-lg p-6 mb-6">
                        <p className="text-sm text-gray-500">Total Saldo</p>
                        <p className="text-3xl font-bold text-gray-900">{formatCurrency(totalBalance)}</p>
                    </div>

                    <div className="flex justify-between items-center mb-4">
                        <h3 className="text-lg font-semibold">Daftar Akun</h3>
                        <button onClick={() => setShowForm(!showForm)} className="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">
                            {showForm ? 'Batal' : 'Tambah Akun'}
                        </button>
                    </div>

                    {showForm && (
                        <form onSubmit={handleSubmit} className="bg-white shadow-sm sm:rounded-lg p-6 mb-6 space-y-4">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Nama</label>
                                    <input type="text" value={values.name} onChange={e => setValues({...values, name: e.target.value})} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Tipe</label>
                                    <select value={values.type} onChange={e => setValues({...values, type: e.target.value})} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        {ACCOUNT_TYPES.map(t => <option key={t.value} value={t.value}>{t.label}</option>)}
                                    </select>
                                </div>
                                {!editingId && (
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700">Saldo Awal</label>
                                        <input type="number" value={values.balance} onChange={e => setValues({...values, balance: parseInt(e.target.value) || 0})} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                    </div>
                                )}
                            </div>
                            <button type="submit" className="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">
                                {editingId ? 'Update' : 'Simpan'}
                            </button>
                        </form>
                    )}

                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        {accounts.map(account => (
                            <div key={account.id} className="bg-white shadow-sm sm:rounded-lg p-6">
                                <div className="flex justify-between items-start">
                                    <div>
                                        <p className="font-semibold text-gray-900">{account.name}</p>
                                        <p className="text-xs text-gray-500 uppercase">{account.type}</p>
                                    </div>
                                    <div className="flex gap-2">
                                        <button onClick={() => startEdit(account)} className="text-sm text-indigo-600 hover:text-indigo-800">Edit</button>
                                        <button onClick={() => handleDelete(account.id)} className="text-sm text-red-600 hover:text-red-800">Hapus</button>
                                    </div>
                                </div>
                                <p className="text-xl font-bold mt-2">{formatCurrency(account.balance)}</p>
                            </div>
                        ))}
                    </div>

                    {accounts.length === 0 && (
                        <div className="bg-white shadow-sm sm:rounded-lg p-12 text-center text-gray-500">
                            Belum ada akun. Tambahkan akun pertama Anda.
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
