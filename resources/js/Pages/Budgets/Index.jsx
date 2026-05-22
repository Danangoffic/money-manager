import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';

function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);
}

export default function Index({ auth, budgets, categories, month, isAdmin }) {
    const [showForm, setShowForm] = useState(false);
    const [values, setValues] = useState({ category_id: '', amount: '', month });

    function handleSubmit(e) {
        e.preventDefault();
        router.post(route('budgets.store'), values, { onSuccess: () => { setShowForm(false); setValues({ ...values, category_id: '', amount: '' }); } });
    }

    function handleMonthChange(direction) {
        const d = new Date(month + '-01');
        d.setMonth(d.getMonth() + direction);
        const newMonth = d.toISOString().slice(0, 7);
        router.get(route('budgets.index'), { month: newMonth }, { preserveState: true });
    }

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Budget</h2>}
        >
            <Head title="Budget" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {/* Month Navigation */}
                    <div className="flex items-center justify-between mb-6">
                        <button onClick={() => handleMonthChange(-1)} className="px-3 py-1 bg-white rounded-md shadow-sm text-sm hover:bg-gray-50">← Prev</button>
                        <h3 className="text-lg font-semibold">{new Date(month + '-01').toLocaleDateString('id-ID', { month: 'long', year: 'numeric' })}</h3>
                        <button onClick={() => handleMonthChange(1)} className="px-3 py-1 bg-white rounded-md shadow-sm text-sm hover:bg-gray-50">Next →</button>
                    </div>

                    {isAdmin && (
                        <div className="flex justify-end mb-4">
                            <button onClick={() => setShowForm(!showForm)} className="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">
                                {showForm ? 'Batal' : 'Set Budget'}
                            </button>
                        </div>
                    )}

                    {showForm && (
                        <form onSubmit={handleSubmit} className="bg-white shadow-sm sm:rounded-lg p-6 mb-6">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <select value={values.category_id} onChange={e => setValues({...values, category_id: e.target.value})} className="rounded-md border-gray-300" required>
                                    <option value="">Pilih Kategori</option>
                                    {categories.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
                                </select>
                                <input type="number" placeholder="Jumlah budget" value={values.amount} onChange={e => setValues({...values, amount: e.target.value})} className="rounded-md border-gray-300" required min="1" />
                            </div>
                            <button type="submit" className="mt-4 px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">Simpan</button>
                        </form>
                    )}

                    {/* Budget List */}
                    <div className="space-y-4">
                        {budgets.map((budget, i) => (
                            <div key={i} className="bg-white shadow-sm sm:rounded-lg p-6">
                                <div className="flex justify-between items-center mb-2">
                                    <span className="font-medium">{budget.category?.name}</span>
                                    <span className="text-sm text-gray-500">
                                        {formatCurrency(budget.spent)} / {formatCurrency(budget.amount)}
                                    </span>
                                </div>
                                <div className="w-full bg-gray-200 rounded-full h-3">
                                    <div
                                        className={`h-3 rounded-full transition-all ${budget.percentage >= 100 ? 'bg-red-500' : budget.percentage >= 80 ? 'bg-yellow-500' : 'bg-green-500'}`}
                                        style={{ width: `${Math.min(100, budget.percentage)}%` }}
                                    />
                                </div>
                                <p className={`text-sm mt-1 ${budget.percentage >= 100 ? 'text-red-600' : budget.percentage >= 80 ? 'text-yellow-600' : 'text-green-600'}`}>
                                    {budget.percentage}% terpakai
                                </p>
                            </div>
                        ))}

                        {budgets.length === 0 && (
                            <div className="bg-white shadow-sm sm:rounded-lg p-12 text-center text-gray-500">
                                Belum ada budget untuk bulan ini.
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
