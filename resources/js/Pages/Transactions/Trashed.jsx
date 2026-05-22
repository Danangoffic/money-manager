import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);
}

export default function Trashed({ auth, transactions }) {
    function handleRestore(id) {
        if (confirm('Pulihkan transaksi ini?')) {
            router.patch(route('transactions.restore', id));
        }
    }

    function handleForceDelete(id) {
        if (confirm('Hapus permanen transaksi ini? Aksi ini tidak dapat dibatalkan.')) {
            router.delete(route('transactions.force-delete', id));
        }
    }

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <div className="flex items-center justify-between">
                    <h2 className="font-semibold text-xl text-gray-800 leading-tight">Transaksi Terhapus</h2>
                    <Link href={route('transactions.index')} className="px-4 py-2 bg-gray-600 text-white rounded-md text-sm hover:bg-gray-700">
                        Kembali
                    </Link>
                </div>
            }
        >
            <Head title="Transaksi Terhapus" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
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
                                    <tr key={t.id} className="bg-red-50">
                                        <td className="px-6 py-4 text-sm text-gray-900">{t.date}</td>
                                        <td className="px-6 py-4 text-sm text-gray-900">{t.description || '-'}</td>
                                        <td className="px-6 py-4 text-sm text-gray-500">{t.category?.name || (t.type === 'transfer' ? 'Transfer' : '-')}</td>
                                        <td className="px-6 py-4 text-sm text-gray-500">{t.account?.name}</td>
                                        <td className={`px-6 py-4 text-sm text-right font-medium ${t.type === 'expense' ? 'text-red-600' : t.type === 'income' ? 'text-green-600' : 'text-blue-600'}`}>
                                            {t.type === 'expense' ? '-' : '+'}{formatCurrency(t.amount)}
                                        </td>
                                        <td className="px-6 py-4 text-sm text-right space-x-2">
                                            <button onClick={() => handleRestore(t.id)} className="text-green-600 hover:text-green-800 font-medium">Pulihkan</button>
                                            <button onClick={() => handleForceDelete(t.id)} className="text-red-600 hover:text-red-800 font-medium">Hapus Permanen</button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>

                        {transactions.data.length === 0 && (
                            <div className="p-12 text-center text-gray-500">Tidak ada transaksi yang terhapus.</div>
                        )}
                    </div>

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
