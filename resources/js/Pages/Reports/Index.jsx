import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';

function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);
}

function BarChart({ data, maxValue }) {
    return (
        <div className="space-y-2">
            {data.map((item, i) => (
                <div key={i} className="flex items-center gap-3">
                    <span className="text-xs text-gray-500 w-16">{item.label}</span>
                    <div className="flex-1 flex gap-1">
                        <div className="h-5 bg-green-400 rounded" style={{ width: `${maxValue > 0 ? (item.income / maxValue) * 100 : 0}%` }} title={`Income: ${formatCurrency(item.income)}`} />
                        <div className="h-5 bg-red-400 rounded" style={{ width: `${maxValue > 0 ? (item.expense / maxValue) * 100 : 0}%` }} title={`Expense: ${formatCurrency(item.expense)}`} />
                    </div>
                </div>
            ))}
        </div>
    );
}

export default function Index({ auth, expenseByCategory, incomeVsExpense, cashFlow, startDate, endDate }) {
    const [tab, setTab] = useState('overview');

    const maxMonthly = cashFlow.reduce((max, item) => Math.max(max, item.income, item.expense), 0);

    const totalExpense = expenseByCategory.reduce((sum, item) => sum + item.total, 0);

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Laporan</h2>}
        >
            <Head title="Laporan" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {/* Tabs */}
                    <div className="flex gap-2 mb-6">
                        {[['overview', 'Overview'], ['categories', 'Kategori'], ['cashflow', 'Cash Flow']].map(([key, label]) => (
                            <button key={key} onClick={() => setTab(key)}
                                className={`px-4 py-2 rounded-md text-sm font-medium ${tab === key ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'}`}>
                                {label}
                            </button>
                        ))}
                    </div>

                    {/* Overview Tab */}
                    {tab === 'overview' && (
                        <div className="bg-white shadow-sm sm:rounded-lg p-6">
                            <h3 className="text-lg font-semibold mb-4">Pemasukan vs Pengeluaran</h3>
                            {cashFlow.length > 0 ? (
                                <>
                                    <div className="flex gap-4 mb-4 text-sm">
                                        <span className="flex items-center gap-1"><span className="w-3 h-3 bg-green-400 rounded" /> Pemasukan</span>
                                        <span className="flex items-center gap-1"><span className="w-3 h-3 bg-red-400 rounded" /> Pengeluaran</span>
                                    </div>
                                    <BarChart
                                        data={cashFlow.map(item => ({ label: item.month, income: item.income, expense: item.expense }))}
                                        maxValue={maxMonthly}
                                    />
                                </>
                            ) : (
                                <p className="text-gray-500">Belum ada data.</p>
                            )}
                        </div>
                    )}

                    {/* Categories Tab */}
                    {tab === 'categories' && (
                        <div className="bg-white shadow-sm sm:rounded-lg p-6">
                            <h3 className="text-lg font-semibold mb-4">Pengeluaran per Kategori</h3>
                            {expenseByCategory.length > 0 ? (
                                <div className="space-y-3">
                                    {expenseByCategory.map((item, i) => {
                                        const pct = totalExpense > 0 ? Math.round((item.total / totalExpense) * 100) : 0;
                                        return (
                                            <div key={i}>
                                                <div className="flex justify-between text-sm mb-1">
                                                    <span>{item.category?.name || 'Tanpa Kategori'}</span>
                                                    <span>{formatCurrency(item.total)} ({pct}%)</span>
                                                </div>
                                                <div className="w-full bg-gray-200 rounded-full h-3">
                                                    <div className="h-3 rounded-full bg-indigo-500" style={{ width: `${pct}%` }} />
                                                </div>
                                            </div>
                                        );
                                    })}
                                    <div className="pt-3 border-t">
                                        <p className="font-semibold">Total: {formatCurrency(totalExpense)}</p>
                                    </div>
                                </div>
                            ) : (
                                <p className="text-gray-500">Belum ada data pengeluaran.</p>
                            )}
                        </div>
                    )}

                    {/* Cash Flow Tab */}
                    {tab === 'cashflow' && (
                        <div className="bg-white shadow-sm sm:rounded-lg p-6">
                            <h3 className="text-lg font-semibold mb-4">Cash Flow</h3>
                            {cashFlow.length > 0 ? (
                                <table className="min-w-full">
                                    <thead>
                                        <tr className="border-b">
                                            <th className="text-left py-2 text-sm font-medium text-gray-500">Bulan</th>
                                            <th className="text-right py-2 text-sm font-medium text-gray-500">Pemasukan</th>
                                            <th className="text-right py-2 text-sm font-medium text-gray-500">Pengeluaran</th>
                                            <th className="text-right py-2 text-sm font-medium text-gray-500">Net</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {cashFlow.map((item, i) => (
                                            <tr key={i} className="border-b last:border-0">
                                                <td className="py-2 text-sm">{item.month}</td>
                                                <td className="py-2 text-sm text-right text-green-600">{formatCurrency(item.income)}</td>
                                                <td className="py-2 text-sm text-right text-red-600">{formatCurrency(item.expense)}</td>
                                                <td className={`py-2 text-sm text-right font-medium ${item.net >= 0 ? 'text-green-600' : 'text-red-600'}`}>
                                                    {formatCurrency(item.net)}
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            ) : (
                                <p className="text-gray-500">Belum ada data.</p>
                            )}
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
