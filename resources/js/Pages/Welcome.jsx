import { Head, Link } from '@inertiajs/react';

function FeatureCard({ icon, title, description }) {
    return (
        <div className="bg-white rounded-xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
            <div className="w-12 h-12 bg-indigo-50 rounded-lg flex items-center justify-center mb-4 text-2xl">
                {icon}
            </div>
            <h3 className="font-semibold text-gray-900 mb-2">{title}</h3>
            <p className="text-sm text-gray-500 leading-relaxed">{description}</p>
        </div>
    );
}

const FEATURES = [
    { icon: '💳', title: 'Multi Akun', description: 'Kelola akun cash, bank, e-wallet, dan kartu kredit dalam satu tempat.' },
    { icon: '📊', title: 'Laporan Visual', description: 'Grafik income vs expense, pie chart kategori, dan analisis cash flow bulanan.' },
    { icon: '🎯', title: 'Budget & Goals', description: 'Set budget per kategori dan pantau progress target tabungan kamu.' },
    { icon: '🔄', title: 'Transaksi Berulang', description: 'Otomatis catat tagihan atau pemasukan rutin sesuai jadwal.' },
    { icon: '👨‍👩‍👧', title: 'Multi User', description: 'Undang anggota keluarga untuk mengelola keuangan bersama dalam satu household.' },
    { icon: '📤', title: 'Export Data', description: 'Export riwayat transaksi ke CSV kapan saja.' },
];

export default function Welcome({ auth, canLogin, canRegister }) {
    return (
        <>
            <Head title="Money Manager — Kelola Keuangan dengan Mudah" />

            <div className="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50">
                {/* Nav */}
                <nav className="flex items-center justify-between px-6 py-4 max-w-6xl mx-auto">
                    <div className="flex items-center gap-2">
                        <span className="text-2xl">💰</span>
                        <span className="font-bold text-gray-900 text-lg">MoneyManager</span>
                    </div>
                    <div className="flex items-center gap-4">
                        {auth.user ? (
                            <Link href={route('dashboard')} className="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                                Buka Dashboard
                            </Link>
                        ) : (
                            <>
                                {canLogin && (
                                    <Link href={route('login')} className="text-sm font-medium text-gray-600 hover:text-gray-900">
                                        Masuk
                                    </Link>
                                )}
                                {canRegister && (
                                    <Link href={route('register')} className="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                                        Daftar Gratis
                                    </Link>
                                )}
                            </>
                        )}
                    </div>
                </nav>

                {/* Hero */}
                <section className="text-center px-6 py-20 max-w-4xl mx-auto">
                    <div className="inline-flex items-center gap-2 bg-indigo-100 text-indigo-700 text-xs font-medium px-3 py-1 rounded-full mb-6">
                        ✨ Gratis & Open Source
                    </div>
                    <h1 className="text-5xl font-bold text-gray-900 leading-tight mb-6">
                        Kelola Keuangan<br />
                        <span className="text-indigo-600">Lebih Cerdas</span>
                    </h1>
                    <p className="text-lg text-gray-500 mb-10 max-w-2xl mx-auto leading-relaxed">
                        Catat pemasukan & pengeluaran, pantau budget, raih target tabungan, dan lihat laporan keuangan — semua dalam satu aplikasi.
                    </p>
                    <div className="flex items-center justify-center gap-4">
                        {auth.user ? (
                            <Link href={route('dashboard')} className="px-8 py-3 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition-colors text-lg">
                                Buka Dashboard →
                            </Link>
                        ) : (
                            <>
                                {canRegister && (
                                    <Link href={route('register')} className="px-8 py-3 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition-colors text-lg">
                                        Mulai Sekarang →
                                    </Link>
                                )}
                                {canLogin && (
                                    <Link href={route('login')} className="px-8 py-3 border border-gray-300 text-gray-700 font-semibold rounded-xl hover:bg-gray-50 transition-colors text-lg">
                                        Masuk
                                    </Link>
                                )}
                            </>
                        )}
                    </div>
                </section>

                {/* Stats */}
                <section className="max-w-4xl mx-auto px-6 mb-16">
                    <div className="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 grid grid-cols-3 gap-8 text-center">
                        {[
                            ['Multi Akun', 'Cash, Bank, E-Wallet'],
                            ['Real-time', 'Update saldo otomatis'],
                            ['Multi User', 'Kelola bersama keluarga'],
                        ].map(([title, sub]) => (
                            <div key={title}>
                                <p className="font-bold text-2xl text-indigo-600 mb-1">{title}</p>
                                <p className="text-sm text-gray-500">{sub}</p>
                            </div>
                        ))}
                    </div>
                </section>

                {/* Features */}
                <section className="max-w-6xl mx-auto px-6 pb-20">
                    <h2 className="text-3xl font-bold text-center text-gray-900 mb-3">Semua yang Kamu Butuhkan</h2>
                    <p className="text-center text-gray-500 mb-10">Fitur lengkap untuk manajemen keuangan personal maupun keluarga</p>
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        {FEATURES.map((f) => (
                            <FeatureCard key={f.title} {...f} />
                        ))}
                    </div>
                </section>

                {/* CTA */}
                {!auth.user && canRegister && (
                    <section className="bg-indigo-600 text-white text-center py-16 px-6">
                        <h2 className="text-3xl font-bold mb-4">Mulai Gratis Sekarang</h2>
                        <p className="text-indigo-200 mb-8">Tidak perlu kartu kredit. Daftar dalam 30 detik.</p>
                        <Link href={route('register')} className="px-8 py-3 bg-white text-indigo-600 font-semibold rounded-xl hover:bg-indigo-50 transition-colors text-lg">
                            Buat Akun Gratis →
                        </Link>
                    </section>
                )}

                {/* Footer */}
                <footer className="text-center py-8 text-sm text-gray-400">
                    Built with Laravel + Inertia + React
                </footer>
            </div>
        </>
    );
}
