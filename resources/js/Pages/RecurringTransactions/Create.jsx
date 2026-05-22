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
        frequency: 'monthly',
        next_due_date: new Date().toISOString().split('T')[0],
    });

    const filteredCategories = categories[values.type] || [];

    function handleSubmit(e) {
        e.preventDefault();
        router.post(route('recurring-transactions.store'), values);
    }

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Tambah Transaksi Berulang</h2>}
        >
            <Head title="Tambah Transaksi Berulang" />

            <div className="py-12">
                <div className="max-w-2xl mx-auto sm:px-6 lg:px-8">
                    <form onSubmit={handleSubmit} className="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                        <div className="flex gap-2">
                            {['expense', 'income'].map(type => (
                                <button key={type} type="button" onClick={() => setValues({ ...values, type, category_id: '' })}
                                    className={`px-4 py-2 rounded-md text-sm font-medium ${values.type === type ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700'}`}>
                                    {type === 'expense' ? 'Pengeluaran' : 'Pemasukan'}
                                </button>
                            ))}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700">Jumlah (Rp)</label>
                            <input type="number" value={values.amount} onChange={e => setValues({...values, amount: e.target.value})} className="mt-1 block w-full rounded-md border-gray-300" required min="1" />
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700">Akun</label>
                            <select value={values.account_id} onChange={e => setValues({...values, account_id: e.target.value})} className="mt-1 block w-full rounded-md border-gray-300" required>
                                {accounts.map(a => <option key={a.id} value={a.id}>{a.name}</option>)}
                            </select>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700">Kategori</label>
                            <select value={values.category_id} onChange={e => setValues({...values, category_id: e.target.value})} className="mt-1 block w-full rounded-md border-gray-300" required>
                                <option value="">Pilih Kategori</option>
                                {filteredCategories.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
                            </select>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700">Frekuensi</label>
                            <select value={values.frequency} onChange={e => setValues({...values, frequency: e.target.value})} className="mt-1 block w-full rounded-md border-gray-300">
                                <option value="daily">Harian</option>
                                <option value="weekly">Mingguan</option>
                                <option value="monthly">Bulanan</option>
                                <option value="yearly">Tahunan</option>
                            </select>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                            <input type="date" value={values.next_due_date} onChange={e => setValues({...values, next_due_date: e.target.value})} className="mt-1 block w-full rounded-md border-gray-300" required />
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700">Deskripsi</label>
                            <input type="text" value={values.description} onChange={e => setValues({...values, description: e.target.value})} className="mt-1 block w-full rounded-md border-gray-300" />
                        </div>

                        <button type="submit" className="w-full px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700">Simpan</button>
                    </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
