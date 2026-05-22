import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);
}

export default function Index({ auth, transactions, accounts, categories, filters }) {
    function handleFilter(key, value) {
        router.get(route('transactions.index'), { ...filters, [key]: value || undefined }, { preserveState: true });
    }

    function handleDelete(id) {
        if (confirm('Hapus transaksi ini?')) {
            router.delete(route('transactions.destroy', id));
        }
    }

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Transaksi</h2>}
        >
            <Head title="Transaksi" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {/* Actions */}
                    <div className="flex justify-between items-center mb-6">
                        <div className="flex gap-4 flex-wrap">
                            <select value={filters.type || ''} onChange={e => handleFilter('type', e.target.value)} className="rounded-md border-gray-300 text-sm">
                                <option value="">Semua Tipe</option>
                                <option value="income">Pemasukan</option>
                                <option value="expense">Pengeluaran</option>
                                <option value="transfer">Transfer</option>
                            </select>
                            <select value={filters.account_id || ''} onChange={e => handleFilter('account_id', e.target.value)} className="rounded-md border-gray-300 text-sm">
                                <option value="">Semua Akun</option>
                                {accounts.map(a => <option key={a.id} value={a.id}>{a.name}</option>)}
                            </select>
                            <input type="date" value={filters.start_date || ''} onChange={e => handleFilter('start_date', e.target.value)} className="rounded-md border-gray-300 text-sm" />
                            <input type="date" value={filters.end_date || ''} onChange={e => handleFilter('end_date', e.target.value)} className="rounded-md border-gray-300 text-sm" />
                        </div>
                        <div className="flex gap-2">
                            <a href={route('export.transactions', filters)} className="px-4 py-2 bg-green-600 text-white rounded-md text-sm hover:bg-green-700">Export CSV</a>
                            <Link href={route('transactions.create')} className="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">Tambah</Link>
                        </div>
                    </div>

                    {/* Table */}
                    <div className="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Deskripsi</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kategori</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Akun</th>
                                    <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                                    <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody className="bg-white divide-y divide-gray-200">
                                {transactions.data.map(t => (
                                    <tr key={t.id}>
                                        <td className="px-6 py-4 text-sm text-gray-900">{t.date}</td>
                                        <td className="px-6 py-4 text-sm text-gray-900">{t.description || '-'}</td>
                                        <td className="px-6 py-4 text-sm text-gray-500">{t.category?.name || (t.type === 'transfer' ? 'Transfer' : '-')}</td>
                                        <td className="px-6 py-4 text-sm text-gray-500">
                                            {t.account?.name}
                                            {t.transfer_to_account && <span> → {t.transfer_to_account.name}</span>}
                                        </td>
                                        <td className={`px-6 py-4 text-sm text-right font-medium ${t.type === 'expense' ? 'text-red-600' : t.type === 'income' ? 'text-green-600' : 'text-blue-600'}`}>
                                            {t.type === 'expense' ? '-' : t.type === 'transfer' ? '↔' : '+'}{formatCurrency(t.amount)}
                                        </td>
                                        <td className="px-6 py-4 text-sm text-right">
                                            <button onClick={() => handleDelete(t.id)} className="text-red-600 hover:text-red-800">Hapus</button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>

                        {transactions.data.length === 0 && (
                            <div className="p-12 text-center text-gray-500">Belum ada transaksi.</div>
                        )}
                    </div>

                    {/* Pagination */}
                    {transactions.last_page > 1 && (
                        <div className="flex justify-center gap-2 mt-6">
                            {transactions.links.map((link, i) => (
                                <button
                                    key={i}
                                    disabled={!link.url}
                                    onClick={() => link.url && router.get(link.url)}
                                    className={`px-3 py-1 rounded text-sm ${link.active ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'}`}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
