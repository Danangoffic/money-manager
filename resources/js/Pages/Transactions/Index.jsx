import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState, useCallback } from 'react';

function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);
}

function debounce(fn, delay) {
    let timer;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn(...args), delay);
    };
}

export default function Index({ auth, transactions, accounts, categories, filters }) {
    const [search, setSearch] = useState(filters.search || '');

    const debouncedSearch = useCallback(
        debounce((value) => {
            router.get(route('transactions.index'), { ...filters, search: value || undefined, page: 1 }, { preserveState: true });
        }, 400),
        [filters]
    );

    function handleSearchChange(e) {
        const value = e.target.value;
        setSearch(value);
        debouncedSearch(value);
    }

    function handleFilter(key, value) {
        router.get(route('transactions.index'), { ...filters, [key]: value || undefined, page: 1 }, { preserveState: true });
    }

    function handleReset() {
        setSearch('');
        router.get(route('transactions.index'), {}, { preserveState: true });
    }

    function handlePerPage(value) {
        router.get(route('transactions.index'), { ...filters, per_page: value, page: 1 }, { preserveState: true });
    }

    function handleDelete(id) {
        if (confirm('Hapus transaksi ini?')) {
            router.delete(route('transactions.destroy', id));
        }
    }

    const hasActiveFilters = filters.type || filters.account_id || filters.category_id || filters.start_date || filters.end_date || filters.search;

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Transaksi</h2>}
        >
            <Head title="Transaksi" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {/* Top Actions */}
                    <div className="flex justify-between items-center mb-4">
                        <div className="flex gap-2">
                            <Link href={route('transactions.trashed')} className="px-4 py-2 bg-gray-100 text-gray-700 rounded-md text-sm hover:bg-gray-200 border border-gray-300">
                                Terhapus
                            </Link>
                        </div>
                        <div className="flex gap-2">
                            <a href={route('export.transactions', filters)} className="px-4 py-2 bg-green-600 text-white rounded-md text-sm hover:bg-green-700">Export CSV</a>
                            <Link href={route('transactions.create')} className="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">+ Tambah</Link>
                        </div>
                    </div>

                    {/* Filters */}
                    <div className="bg-white shadow-sm sm:rounded-lg p-4 mb-6">
                        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                            {/* Search */}
                            <div>
                                <label className="block text-xs font-medium text-gray-600 mb-1">Cari Deskripsi</label>
                                <input
                                    type="text"
                                    value={search}
                                    onChange={handleSearchChange}
                                    placeholder="Cari transaksi..."
                                    className="block w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                            </div>

                            {/* Type */}
                            <div>
                                <label className="block text-xs font-medium text-gray-600 mb-1">Tipe</label>
                                <select value={filters.type || ''} onChange={e => handleFilter('type', e.target.value)} className="block w-full rounded-md border-gray-300 text-sm">
                                    <option value="">Semua Tipe</option>
                                    <option value="income">Pemasukan</option>
                                    <option value="expense">Pengeluaran</option>
                                    <option value="transfer">Transfer</option>
                                </select>
                            </div>

                            {/* Account */}
                            <div>
                                <label className="block text-xs font-medium text-gray-600 mb-1">Akun</label>
                                <select value={filters.account_id || ''} onChange={e => handleFilter('account_id', e.target.value)} className="block w-full rounded-md border-gray-300 text-sm">
                                    <option value="">Semua Akun</option>
                                    {accounts.map(a => <option key={a.id} value={a.id}>{a.name}</option>)}
                                </select>
                            </div>

                            {/* Category */}
                            <div>
                                <label className="block text-xs font-medium text-gray-600 mb-1">Kategori</label>
                                <select value={filters.category_id || ''} onChange={e => handleFilter('category_id', e.target.value)} className="block w-full rounded-md border-gray-300 text-sm">
                                    <option value="">Semua Kategori</option>
                                    {categories.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
                                </select>
                            </div>

                            {/* Start Date */}
                            <div>
                                <label className="block text-xs font-medium text-gray-600 mb-1">Dari Tanggal</label>
                                <input type="date" value={filters.start_date || ''} onChange={e => handleFilter('start_date', e.target.value)} className="block w-full rounded-md border-gray-300 text-sm" />
                            </div>

                            {/* End Date */}
                            <div>
                                <label className="block text-xs font-medium text-gray-600 mb-1">Sampai Tanggal</label>
                                <input type="date" value={filters.end_date || ''} onChange={e => handleFilter('end_date', e.target.value)} className="block w-full rounded-md border-gray-300 text-sm" />
                            </div>

                            {/* Per Page */}
                            <div>
                                <label className="block text-xs font-medium text-gray-600 mb-1">Per Halaman</label>
                                <select value={filters.per_page || 15} onChange={e => handlePerPage(e.target.value)} className="block w-full rounded-md border-gray-300 text-sm">
                                    <option value="10">10</option>
                                    <option value="15">15</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                </select>
                            </div>

                            {/* Reset */}
                            <div className="flex items-end">
                                {hasActiveFilters && (
                                    <button onClick={handleReset} className="w-full px-4 py-2 bg-red-50 text-red-700 border border-red-200 rounded-md text-sm hover:bg-red-100 font-medium">
                                        Reset Filter
                                    </button>
                                )}
                            </div>
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
                                    <tr key={t.id} className="hover:bg-gray-50">
                                        <td className="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">{t.date}</td>
                                        <td className="px-6 py-4 text-sm text-gray-900">{t.description || '-'}</td>
                                        <td className="px-6 py-4 text-sm text-gray-500">{t.category?.name || (t.type === 'transfer' ? 'Transfer' : '-')}</td>
                                        <td className="px-6 py-4 text-sm text-gray-500">
                                            {t.account?.name}
                                            {t.transfer_to_account && <span className="text-blue-600"> → {t.transfer_to_account.name}</span>}
                                        </td>
                                        <td className={`px-6 py-4 text-sm text-right font-medium ${t.type === 'expense' ? 'text-red-600' : t.type === 'income' ? 'text-green-600' : 'text-blue-600'}`}>
                                            {t.type === 'expense' ? '-' : t.type === 'transfer' ? '↔' : '+'}{formatCurrency(t.amount)}
                                        </td>
                                        <td className="px-6 py-4 text-sm text-right space-x-2">
                                            <Link href={route('transactions.edit', t.id)} className="text-indigo-600 hover:text-indigo-800">Edit</Link>
                                            <button onClick={() => handleDelete(t.id)} className="text-red-600 hover:text-red-800">Hapus</button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>

                        {transactions.data.length === 0 && (
                            <div className="p-12 text-center text-gray-500">
                                {hasActiveFilters ? 'Tidak ada transaksi yang cocok dengan filter.' : 'Belum ada transaksi.'}
                            </div>
                        )}
                    </div>

                    {/* Pagination Info + Controls */}
                    {transactions.total > 0 && (
                        <div className="flex flex-col sm:flex-row justify-between items-center mt-4 gap-4">
                            <p className="text-sm text-gray-600">
                                Menampilkan {transactions.from} - {transactions.to} dari {transactions.total} transaksi
                            </p>

                            {transactions.last_page > 1 && (
                                <div className="flex items-center gap-1">
                                    {transactions.links.map((link, i) => (
                                        <button
                                            key={i}
                                            disabled={!link.url}
                                            onClick={() => link.url && router.get(link.url, {}, { preserveState: true })}
                                            className={`px-3 py-1.5 rounded text-sm border transition-colors ${
                                                link.active
                                                    ? 'bg-indigo-600 text-white border-indigo-600'
                                                    : link.url
                                                        ? 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
                                                        : 'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed'
                                            }`}
                                            dangerouslySetInnerHTML={{ __html: link.label }}
                                        />
                                    ))}
                                </div>
                            )}
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
