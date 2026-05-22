import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);
}

const FREQ_LABELS = { daily: 'Harian', weekly: 'Mingguan', monthly: 'Bulanan', yearly: 'Tahunan' };

export default function Index({ auth, recurringTransactions }) {
    function handleToggle(id) {
        router.patch(route('recurring-transactions.toggle', id));
    }

    function handleDelete(id) {
        if (confirm('Hapus recurring transaction ini?')) {
            router.delete(route('recurring-transactions.destroy', id));
        }
    }

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Transaksi Berulang</h2>}
        >
            <Head title="Transaksi Berulang" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="flex justify-end mb-4">
                        <Link href={route('recurring-transactions.create')} className="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">
                            Tambah
                        </Link>
                    </div>

                    <div className="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Deskripsi</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kategori</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Frekuensi</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Berikutnya</th>
                                    <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                                    <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200">
                                {recurringTransactions.map(rt => (
                                    <tr key={rt.id} className={!rt.is_active ? 'opacity-50' : ''}>
                                        <td className="px-6 py-4 text-sm">{rt.description || '-'}</td>
                                        <td className="px-6 py-4 text-sm text-gray-500">{rt.category?.name}</td>
                                        <td className="px-6 py-4 text-sm">{FREQ_LABELS[rt.frequency]}</td>
                                        <td className="px-6 py-4 text-sm">{rt.next_due_date}</td>
                                        <td className={`px-6 py-4 text-sm text-right font-medium ${rt.type === 'expense' ? 'text-red-600' : 'text-green-600'}`}>
                                            {formatCurrency(rt.amount)}
                                        </td>
                                        <td className="px-6 py-4 text-sm text-right space-x-2">
                                            <button onClick={() => handleToggle(rt.id)} className="text-indigo-600 hover:text-indigo-800">
                                                {rt.is_active ? 'Pause' : 'Aktifkan'}
                                            </button>
                                            <button onClick={() => handleDelete(rt.id)} className="text-red-600 hover:text-red-800">Hapus</button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>

                        {recurringTransactions.length === 0 && (
                            <div className="p-12 text-center text-gray-500">Belum ada transaksi berulang.</div>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
