import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';

function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);
}

export default function Index({ auth, goals }) {
    const [showForm, setShowForm] = useState(false);
    const [values, setValues] = useState({ name: '', target_amount: '', current_amount: 0, deadline: '' });
    const [updatingId, setUpdatingId] = useState(null);
    const [progressAmount, setProgressAmount] = useState('');

    function handleSubmit(e) {
        e.preventDefault();
        router.post(route('goals.store'), values, { onSuccess: () => { setShowForm(false); setValues({ name: '', target_amount: '', current_amount: 0, deadline: '' }); } });
    }

    function handleUpdateProgress(id) {
        router.patch(route('goals.update-progress', id), { current_amount: parseInt(progressAmount) }, { onSuccess: () => { setUpdatingId(null); setProgressAmount(''); } });
    }

    function handleDelete(id) {
        if (confirm('Hapus goal ini?')) {
            router.delete(route('goals.destroy', id));
        }
    }

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Goals</h2>}
        >
            <Head title="Goals" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="flex justify-end mb-4">
                        <button onClick={() => setShowForm(!showForm)} className="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">
                            {showForm ? 'Batal' : 'Tambah Goal'}
                        </button>
                    </div>

                    {showForm && (
                        <form onSubmit={handleSubmit} className="bg-white shadow-sm sm:rounded-lg p-6 mb-6 space-y-4">
                            <input type="text" placeholder="Nama goal" value={values.name} onChange={e => setValues({...values, name: e.target.value})} className="block w-full rounded-md border-gray-300" required />
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <input type="number" placeholder="Target (Rp)" value={values.target_amount} onChange={e => setValues({...values, target_amount: e.target.value})} className="rounded-md border-gray-300" required min="1" />
                                <input type="date" value={values.deadline} onChange={e => setValues({...values, deadline: e.target.value})} className="rounded-md border-gray-300" />
                            </div>
                            <button type="submit" className="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">Simpan</button>
                        </form>
                    )}

                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        {goals.map(goal => {
                            const percentage = goal.target_amount > 0 ? Math.min(100, Math.round((goal.current_amount / goal.target_amount) * 100)) : 0;
                            return (
                                <div key={goal.id} className="bg-white shadow-sm sm:rounded-lg p-6">
                                    <div className="flex justify-between items-start mb-2">
                                        <h4 className="font-semibold text-gray-900">{goal.name}</h4>
                                        <button onClick={() => handleDelete(goal.id)} className="text-sm text-red-600 hover:text-red-800">Hapus</button>
                                    </div>
                                    <p className="text-sm text-gray-500 mb-3">
                                        {formatCurrency(goal.current_amount)} / {formatCurrency(goal.target_amount)}
                                    </p>
                                    <div className="w-full bg-gray-200 rounded-full h-3 mb-2">
                                        <div className="h-3 rounded-full bg-indigo-500 transition-all" style={{ width: `${percentage}%` }} />
                                    </div>
                                    <div className="flex justify-between text-sm">
                                        <span className="text-indigo-600 font-medium">{percentage}%</span>
                                        {goal.deadline && <span className="text-gray-500">Deadline: {goal.deadline}</span>}
                                    </div>

                                    {updatingId === goal.id ? (
                                        <div className="mt-3 flex gap-2">
                                            <input type="number" value={progressAmount} onChange={e => setProgressAmount(e.target.value)} placeholder="Jumlah baru" className="flex-1 rounded-md border-gray-300 text-sm" />
                                            <button onClick={() => handleUpdateProgress(goal.id)} className="px-3 py-1 bg-indigo-600 text-white rounded-md text-sm">OK</button>
                                            <button onClick={() => setUpdatingId(null)} className="px-3 py-1 bg-gray-200 rounded-md text-sm">X</button>
                                        </div>
                                    ) : (
                                        <button onClick={() => { setUpdatingId(goal.id); setProgressAmount(goal.current_amount); }} className="mt-3 text-sm text-indigo-600 hover:text-indigo-800">
                                            Update Progress
                                        </button>
                                    )}
                                </div>
                            );
                        })}
                    </div>

                    {goals.length === 0 && (
                        <div className="bg-white shadow-sm sm:rounded-lg p-12 text-center text-gray-500">
                            Belum ada goals. Buat target tabungan pertama Anda!
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
