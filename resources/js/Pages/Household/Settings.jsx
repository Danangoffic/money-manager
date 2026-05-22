import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';

export default function Settings({ auth, household, members, isAdmin }) {
    const [name, setName] = useState(household.name);
    const [email, setEmail] = useState('');

    function handleUpdateName(e) {
        e.preventDefault();
        router.patch(route('household.update'), { name });
    }

    function handleInvite(e) {
        e.preventDefault();
        router.post(route('household.invite'), { email }, { onSuccess: () => setEmail('') });
    }

    function handleRemove(userId) {
        if (confirm('Hapus anggota ini?')) {
            router.delete(route('household.remove-member', userId));
        }
    }

    function handleChangeRole(memberId, role) {
        router.patch(route('household.change-role', memberId), { role });
    }

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Pengaturan Household</h2>}
        >
            <Head title="Household Settings" />

            <div className="py-12">
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    {/* Name */}
                    <div className="bg-white shadow-sm sm:rounded-lg p-6">
                        <h3 className="text-lg font-semibold mb-4">Nama Household</h3>
                        <form onSubmit={handleUpdateName} className="flex gap-4">
                            <input type="text" value={name} onChange={e => setName(e.target.value)} className="flex-1 rounded-md border-gray-300" required />
                            {isAdmin && <button type="submit" className="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">Simpan</button>}
                        </form>
                    </div>

                    {/* Members */}
                    <div className="bg-white shadow-sm sm:rounded-lg p-6">
                        <h3 className="text-lg font-semibold mb-4">Anggota</h3>

                        {isAdmin && (
                            <form onSubmit={handleInvite} className="flex gap-4 mb-6">
                                <input type="email" value={email} onChange={e => setEmail(e.target.value)} placeholder="Email anggota baru" className="flex-1 rounded-md border-gray-300" required />
                                <button type="submit" className="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">Invite</button>
                            </form>
                        )}

                        <div className="space-y-3">
                            {members.map(member => (
                                <div key={member.id} className="flex items-center justify-between py-2 border-b last:border-0">
                                    <div>
                                        <p className="font-medium">{member.name}</p>
                                        <p className="text-sm text-gray-500">{member.email}</p>
                                    </div>
                                    <div className="flex items-center gap-3">
                                        <span className={`text-xs px-2 py-1 rounded ${member.pivot.role === 'admin' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-700'}`}>
                                            {member.pivot.role}
                                        </span>
                                        {isAdmin && member.id !== auth.user.id && (
                                            <>
                                                <select value={member.pivot.role} onChange={e => handleChangeRole(member.pivot.id, e.target.value)} className="text-sm rounded-md border-gray-300">
                                                    <option value="admin">Admin</option>
                                                    <option value="member">Member</option>
                                                </select>
                                                <button onClick={() => handleRemove(member.id)} className="text-sm text-red-600 hover:text-red-800">Hapus</button>
                                            </>
                                        )}
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
