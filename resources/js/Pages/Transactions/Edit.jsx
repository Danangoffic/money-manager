import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';

export default function Edit({ auth, transaction, accounts, categories }) {
    const [values, setValues] = useState({
        type: transaction.type,
        account_id: transaction.account_id,
        category_id: transaction.category_id || '',
        amount: transaction.amount,
        description: transaction.description || '',
        date: transaction.date,
        transfer_to_account_id: transaction.transfer_to_account_id || '',
    });

    const filteredCategories = values.type === 'transfer' ? [] : (categories[values.type] || []);

    function handleSubmit(e) {
        e.preventDefault();
        router.put(route('transactions.update', transaction.id), values);
    }

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Edit Transaksi</h2>}
        >
            <Head title="Edit Transaksi" />

            <div className="py-12">
                <div className="max-w-2xl mx-auto sm:px-6 lg:px-8">
                    <form onSubmit={handleSubmit} className="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                        <div className="flex gap-2">
                            {['expense', 'income', 'transfer'].map(type => (
                                <button key={type} type="button"
                                    onClick={() => setValues({ ...values, type, category_id: '', transfer_to_account_id: '' })}
                                    className={`px-4 py-2 rounded-md text-sm font-medium ${values.type === type ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700'}`}>
                                    {type === 'expense' ? 'Pengeluaran' : type === 'income' ? 'Pemasukan' : 'Transfer'}
                                </button>
                            ))}
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700">Jumlah (Rp)</label>
                            <input type="number" value={values.amount} onChange={e => setValues({...values, amount: e.target.value})} className="mt-1 block w-full rounded-md border-gray-300" required min="1" />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700">{values.type === 'transfer' ? 'Dari Akun' : 'Akun'}</label>
                            <select value={values.account_id} onChange={e => setValues({...values, account_id: e.target.value})} className="mt-1 block w-full rounded-md border-gray-300" required>
                                {accounts.map(a => <option key={a.id} value={a.id}>{a.name}</option>)}
                            </select>
                        </div>
                        {values.type === 'transfer' && (
                            <div>
                                <label className="block text-sm font-medium text-gray-700">Ke Akun</label>
                                <select value={values.transfer_to_account_id} onChange={e => setValues({...values, transfer_to_account_id: e.target.value})} className="mt-1 block w-full rounded-md border-gray-300" required>
                                    <option value="">Pilih Akun Tujuan</option>
                                    {accounts.filter(a => a.id != values.account_id).map(a => <option key={a.id} value={a.id}>{a.name}</option>)}
                                </select>
                            </div>
                        )}
                        {values.type !== 'transfer' && (
                            <div>
                                <label className="block text-sm font-medium text-gray-700">Kategori</label>
                                <select value={values.category_id} onChange={e => setValues({...values, category_id: e.target.value})} className="mt-1 block w-full rounded-md border-gray-300">
                                    <option value="">Pilih Kategori</option>
                                    {filteredCategories.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
                                </select>
                            </div>
                        )}
                        <div>
                            <label className="block text-sm font-medium text-gray-700">Tanggal</label>
                            <input type="date" value={values.date} onChange={e => setValues({...values, date: e.target.value})} className="mt-1 block w-full rounded-md border-gray-300" required />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700">Deskripsi</label>
                            <input type="text" value={values.description} onChange={e => setValues({...values, description: e.target.value})} className="mt-1 block w-full rounded-md border-gray-300" />
                        </div>
                        <button type="submit" className="w-full px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700">
                            Update Transaksi
                        </button>
                    </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
