import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';

function getActionBadge(action) {
    const map = {
        created: { label: 'Dibuat', color: 'bg-green-100 text-green-800' },
        updated: { label: 'Diperbarui', color: 'bg-blue-100 text-blue-800' },
        deleted: { label: 'Dihapus', color: 'bg-red-100 text-red-800' },
        restored: { label: 'Dipulihkan', color: 'bg-yellow-100 text-yellow-800' },
    };
    return map[action] || { label: action, color: 'bg-gray-100 text-gray-800' };
}

function formatDate(dateStr) {
    return new Date(dateStr).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

export default function Index({ auth, logs }) {
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Activity Log</h2>}
        >
            <Head title="Activity Log" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Deskripsi</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Model</th>
                                </tr>
                            </thead>
                            <tbody className="bg-white divide-y divide-gray-200">
                                {logs.data.map((log) => {
                                    const badge = getActionBadge(log.action);
                                    return (
                                        <tr key={log.id}>
                                            <td className="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                                {formatDate(log.created_at)}
                                            </td>
                                            <td className="px-6 py-4 text-sm text-gray-900">
                                                {log.user?.name || 'System'}
                                            </td>
                                            <td className="px-6 py-4">
                                                <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${badge.color}`}>
                                                    {badge.label}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 text-sm text-gray-700">
                                                {log.description}
                                            </td>
                                            <td className="px-6 py-4 text-sm text-gray-500">
                                                {log.model_type ? log.model_type.split('\\').pop() : '-'} #{log.model_id}
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>

                        {logs.data.length === 0 && (
                            <div className="p-12 text-center text-gray-500">Belum ada aktivitas tercatat.</div>
                        )}
                    </div>

                    {/* Pagination */}
                    {logs.last_page > 1 && (
                        <div className="flex justify-center gap-2 mt-6">
                            {logs.links.map((link, i) => (
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
