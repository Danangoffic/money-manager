import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';

export default function Create({ auth, accounts, categories }) {
    const [values, setValues] = useState({
        type: 'expense',
        account_id: accounts[0]?.id || '',
        category_id: '',
        amount: '',
        description: '',
        date: new Date().toISOString().split('T')[0],
        transfer_to_account_id: '',
    });

    const filteredCategories = values.type === 'transfer' ? [] : (categories[values.type] || []);

    function handleSubmit(e) {
        e.preventDefault();
        router.post(route('transactions.store'), values);
    }

    function handleChange(e) {
        setValues({ ...values, [e.target.id]: e.target.value });
    }

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Tambah Transaksi</h2>}
        >
            <Head title="Tambah Transaksi" />

            <div className="py-12">
                <div className="max-w-2xl mx-auto sm:px-6 lg:px-8">
                    <form onSubmit={handleSubmit} className="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                        {/* Type */}
                        <div className="flex gap-2">
                            {['expense', 'income', 'transfer'].map(type => (
                                <button
                                    key={type}
                                    type="button"
                                    onClick={() => setValues({ ...values, type, category_id: '', transfer_to_account_id: '' })}
                                    className={`px-4 py-2 rounded-md text-sm font-medium ${values.type === type ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'}`}
                                >
                                    {type === 'expense' ? 'Pengeluaran' : type === 'income' ? 'Pemasukan' : 'Transfer'}
                                </button>
                            ))}
                        </div>

                        {/* Amount */}
                        <div>
                            <label htmlFor="amount" className="block text-sm font-medium text-gray-700">Jumlah (Rp)</label>
                            <input id="amount" type="number" value={values.amount} onChange={handleChange} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required min="1" />
                        </div>

                        {/* Account */}
                        <div>
                            <label htmlFor="account_id" className="block text-sm font-medium text-gray-700">
                                {values.type === 'transfer' ? 'Dari Akun' : 'Akun'}
                            </label>
                            <select id="account_id" value={values.account_id} onChange={handleChange} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">Pilih Akun</option>
                                {accounts.map(a => <option key={a.id} value={a.id}>{a.name}</option>)}
                            </select>
                        </div>

                        {/* Transfer To Account */}
                        {values.type === 'transfer' && (
                            <div>
                                <label htmlFor="transfer_to_account_id" className="block text-sm font-medium text-gray-700">Ke Akun</label>
                                <select id="transfer_to_account_id" value={values.transfer_to_account_id} onChange={handleChange} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="">Pilih Akun Tujuan</option>
                                    {accounts.filter(a => a.id != values.account_id).map(a => <option key={a.id} value={a.id}>{a.name}</option>)}
                                </select>
                            </div>
                        )}

                        {/* Category */}
                        {values.type !== 'transfer' && (
                            <div>
                                <label htmlFor="category_id" className="block text-sm font-medium text-gray-700">Kategori</label>
                                <select id="category_id" value={values.category_id} onChange={handleChange} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Pilih Kategori</option>
                                    {filteredCategories.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
                                </select>
                            </div>
                        )}

                        {/* Date */}
                        <div>
                            <label htmlFor="date" className="block text-sm font-medium text-gray-700">Tanggal</label>
                            <input id="date" type="date" value={values.date} onChange={handleChange} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
                        </div>

                        {/* Description */}
                        <div>
                            <label htmlFor="description" className="block text-sm font-medium text-gray-700">Deskripsi</label>
                            <input id="description" type="text" value={values.description} onChange={handleChange} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                        </div>

                        <button type="submit" className="w-full px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700">
                            Simpan Transaksi
                        </button>
                    </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
