function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);
}

function StatCard({ title, value, icon, color = 'text-gray-900', bgColor = 'bg-gray-100' }) {
    return (
        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-5">
            <div className="flex items-center gap-3">
                <div className={`p-2 rounded-lg ${bgColor}`}>
                    {icon}
                </div>
                <div className="min-w-0 flex-1">
                    <p className="text-sm text-gray-500 truncate">{title}</p>
                    <p className={`text-xl font-bold ${color} truncate`}>{value}</p>
                </div>
            </div>
        </div>
    );
}

export default function StatsGrid({ totalBalance = 0, incomeThisMonth = 0, expenseThisMonth = 0, dailyAverage = 0, transactionCount = 0 }) {
    return (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <StatCard
                title="Total Saldo"
                value={formatCurrency(totalBalance)}
                bgColor="bg-indigo-50"
                color="text-indigo-700"
                icon={
                    <svg className="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                }
            />
            <StatCard
                title="Pemasukan Bulan Ini"
                value={formatCurrency(incomeThisMonth)}
                bgColor="bg-green-50"
                color="text-green-700"
                icon={
                    <svg className="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 11l5-5m0 0l5 5m-5-5v12" />
                    </svg>
                }
            />
            <StatCard
                title="Pengeluaran Bulan Ini"
                value={formatCurrency(expenseThisMonth)}
                bgColor="bg-red-50"
                color="text-red-700"
                icon={
                    <svg className="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 13l-5 5m0 0l-5-5m5 5V6" />
                    </svg>
                }
            />
            <StatCard
                title="Transaksi Bulan Ini"
                value={`${transactionCount} transaksi`}
                bgColor="bg-amber-50"
                color="text-amber-700"
                icon={
                    <svg className="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                }
            />
        </div>
    );
}
