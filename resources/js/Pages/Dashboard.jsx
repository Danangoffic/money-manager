import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Modal from '@/Components/Modal';
import StatsGrid from '@/Components/Dashboard/StatsGrid';
import IncomeExpenseChart from '@/Components/Dashboard/IncomeExpenseChart';
import TopCategoriesChart from '@/Components/Dashboard/TopCategoriesChart';
import NetWorthCard from '@/Components/Dashboard/NetWorthCard';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);
}

function TransactionItem({ transaction }) {
    const isExpense = transaction.type === 'expense';
    const isTransfer = transaction.type === 'transfer';

    return (
        <div className="flex items-center justify-between py-3 border-b last:border-0">
            <div>
                <p className="font-medium text-gray-900">{transaction.description || transaction.category?.name || 'Transfer'}</p>
                <p className="text-sm text-gray-500">{transaction.account?.name} · {transaction.date}</p>
            </div>
            <p className={`font-semibold ${isExpense ? 'text-red-600' : isTransfer ? 'text-blue-600' : 'text-green-600'}`}>
                {isExpense ? '-' : isTransfer ? '↔' : '+'}{formatCurrency(transaction.amount)}
            </p>
        </div>
    );
}

const EMPTY_FORM = {
    type: 'expense',
    account_id: '',
    category_id: '',
    amount: '',
    description: '',
    date: new Date().toISOString().split('T')[0],
    transfer_to_account_id: '',
};

function QuickTransactionForm({ accounts, categories, onClose }) {
    const [values, setValues] = useState({ ...EMPTY_FORM, account_id: accounts[0]?.id || '' });
    const [errors, setErrors] = useState({});

    const filteredCategories = values.type === 'transfer' ? [] : (categories[values.type] || []);

    function handleSubmit(e) {
        e.preventDefault();
        router.post(route('transactions.store'), values, {
            onSuccess: () => {
                onClose();
                router.reload();
            },
            onError: (errs) => setErrors(errs),
        });
    }

    function set(key, value) {
        setValues((prev) => ({ ...prev, [key]: value }));
    }

    return (
        <form onSubmit={handleSubmit} className="p-6 space-y-4">
            <div className="flex items-center justify-between mb-2">
                <h3 className="text-lg font-semibold text-gray-900">Catat Transaksi</h3>
                <button type="button" onClick={onClose} className="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
            </div>

            {/* Type */}
            <div className="flex gap-2">
                {[['expense', 'Pengeluaran'], ['income', 'Pemasukan'], ['transfer', 'Transfer']].map(([type, label]) => (
                    <button key={type} type="button"
                        onClick={() => setValues({ ...values, type, category_id: '', transfer_to_account_id: '' })}
                        className={`flex-1 py-2 rounded-md text-sm font-medium transition-colors ${values.type === type ? (type === 'expense' ? 'bg-red-500 text-white' : type === 'income' ? 'bg-green-500 text-white' : 'bg-blue-500 text-white') : 'bg-gray-100 text-gray-700 hover:bg-gray-200'}`}>
                        {label}
                    </button>
                ))}
            </div>

            {/* Amount */}
            <div>
                <input type="number" value={values.amount} onChange={e => set('amount', e.target.value)}
                    placeholder="Jumlah (Rp)" min="1" required
                    className="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-lg" />
                {errors.amount && <p className="text-red-500 text-xs mt-1">{errors.amount}</p>}
            </div>

            {/* Account */}
            <div className="grid grid-cols-2 gap-3">
                <div>
                    <label className="block text-xs font-medium text-gray-600 mb-1">{values.type === 'transfer' ? 'Dari Akun' : 'Akun'}</label>
                    <select value={values.account_id} onChange={e => set('account_id', e.target.value)}
                        className="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" required>
                        <option value="">Pilih akun</option>
                        {accounts.map(a => <option key={a.id} value={a.id}>{a.name}</option>)}
                    </select>
                </div>

                {values.type === 'transfer' ? (
                    <div>
                        <label className="block text-xs font-medium text-gray-600 mb-1">Ke Akun</label>
                        <select value={values.transfer_to_account_id} onChange={e => set('transfer_to_account_id', e.target.value)}
                            className="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" required>
                            <option value="">Pilih akun</option>
                            {accounts.filter(a => a.id != values.account_id).map(a => <option key={a.id} value={a.id}>{a.name}</option>)}
                        </select>
                    </div>
                ) : (
                    <div>
                        <label className="block text-xs font-medium text-gray-600 mb-1">Kategori</label>
                        <select value={values.category_id} onChange={e => set('category_id', e.target.value)}
                            className="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">Tanpa kategori</option>
                            {filteredCategories.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
                        </select>
                    </div>
                )}
            </div>

            {/* Date & Description */}
            <div className="grid grid-cols-2 gap-3">
                <div>
                    <input type="date" value={values.date} onChange={e => set('date', e.target.value)}
                        className="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" required />
                </div>
                <div>
                    <input type="text" value={values.description} onChange={e => set('description', e.target.value)}
                        placeholder="Deskripsi (opsional)"
                        className="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" />
                </div>
            </div>

            <button type="submit"
                className="w-full py-2 bg-indigo-600 text-white rounded-md font-medium hover:bg-indigo-700 transition-colors">
                Simpan
            </button>
        </form>
    );
}

export default function Dashboard({ auth, summary, accounts, categories }) {
    const [showModal, setShowModal] = useState(false);
    const {
        total_balance,
        income_this_month,
        expense_this_month,
        recent_transactions,
        budget_alerts,
        goals,
        monthly_chart,
        top_categories,
        net_worth,
        daily_average_expense,
        transaction_count,
    } = summary || {};

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <div className="flex items-center justify-between">
                    <h2 className="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
                    <button onClick={() => setShowModal(true)}
                        className="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 transition-colors">
                        + Catat Transaksi
                    </button>
                </div>
            }
        >
            <Head title="Dashboard" />

            <Modal show={showModal} maxWidth="md" onClose={() => setShowModal(false)}>
                <QuickTransactionForm
                    accounts={accounts || []}
                    categories={categories || {}}
                    onClose={() => setShowModal(false)}
                />
            </Modal>

            <div className="py-8">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    {/* Stats Grid */}
                    <StatsGrid
                        totalBalance={total_balance || 0}
                        incomeThisMonth={income_this_month || 0}
                        expenseThisMonth={expense_this_month || 0}
                        dailyAverage={daily_average_expense || 0}
                        transactionCount={transaction_count || 0}
                    />

                    {/* Charts Row: Income vs Expense + Top Categories */}
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <IncomeExpenseChart data={monthly_chart || []} />
                        <TopCategoriesChart data={top_categories || []} />
                    </div>

                    {/* Net Worth + Budget Alerts */}
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <NetWorthCard netWorth={net_worth || {}} />

                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <h3 className="text-lg font-semibold text-gray-900 mb-4">Peringatan Budget</h3>
                            {budget_alerts?.length > 0 ? (
                                <div className="space-y-3">
                                    {budget_alerts.map((alert, i) => (
                                        <div key={i}>
                                            <div className="flex justify-between text-sm mb-1">
                                                <span>{alert.category?.name}</span>
                                                <span className={alert.percentage >= 100 ? 'text-red-600 font-semibold' : alert.percentage >= 80 ? 'text-yellow-600 font-medium' : 'text-gray-600'}>
                                                    {alert.percentage}%
                                                </span>
                                            </div>
                                            <div className="w-full bg-gray-200 rounded-full h-2">
                                                <div className={`h-2 rounded-full ${alert.percentage >= 100 ? 'bg-red-500' : alert.percentage >= 80 ? 'bg-yellow-500' : 'bg-green-500'}`}
                                                    style={{ width: `${Math.min(100, alert.percentage)}%` }} />
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <p className="text-gray-500 text-sm">Semua budget dalam batas aman.</p>
                            )}
                        </div>
                    </div>

                    {/* Recent Transactions + Goals */}
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {/* Recent Transactions */}
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <div className="flex items-center justify-between mb-4">
                                <h3 className="text-lg font-semibold text-gray-900">Transaksi Terakhir</h3>
                                <Link href={route('transactions.index')} className="text-sm text-indigo-600 hover:text-indigo-800">
                                    Lihat Semua
                                </Link>
                            </div>
                            {recent_transactions?.length > 0 ? (
                                <div>
                                    {recent_transactions.map((t) => (
                                        <TransactionItem key={t.id} transaction={t} />
                                    ))}
                                </div>
                            ) : (
                                <p className="text-gray-500 text-sm">Belum ada transaksi.</p>
                            )}
                        </div>

                        {/* Goals */}
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <div className="flex items-center justify-between mb-4">
                                <h3 className="text-lg font-semibold text-gray-900">Goals</h3>
                                <Link href={route('goals.index')} className="text-sm text-indigo-600 hover:text-indigo-800">Lihat Semua</Link>
                            </div>
                            {goals?.length > 0 ? (
                                <div className="space-y-3">
                                    {goals.slice(0, 4).map((goal) => (
                                        <div key={goal.id}>
                                            <div className="flex justify-between text-sm mb-1">
                                                <span className="font-medium text-gray-700">{goal.name}</span>
                                                <span className="text-gray-500">{formatCurrency(goal.current_amount)} / {formatCurrency(goal.target_amount)}</span>
                                            </div>
                                            <div className="w-full bg-gray-200 rounded-full h-2">
                                                <div className="h-2 rounded-full bg-indigo-500"
                                                    style={{ width: `${Math.min(100, (goal.current_amount / goal.target_amount) * 100)}%` }} />
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <p className="text-gray-500 text-sm">Belum ada goals.</p>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
