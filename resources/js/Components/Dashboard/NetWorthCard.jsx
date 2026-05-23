function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);
}

export default function NetWorthCard({ netWorth = {} }) {
    const { total = 0, net_this_month = 0, net_last_month = 0, change = 0 } = netWorth;

    const isPositive = net_this_month >= 0;
    const changeIsPositive = change >= 0;

    return (
        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 className="text-lg font-semibold text-gray-900 mb-3">Net Worth</h3>

            <div className="mb-4">
                <p className="text-3xl font-bold text-gray-900">{formatCurrency(total)}</p>
                <p className="text-sm text-gray-500 mt-1">Total saldo semua akun</p>
            </div>

            <div className="border-t pt-4 space-y-3">
                {/* Net this month */}
                <div className="flex items-center justify-between">
                    <span className="text-sm text-gray-600">Net bulan ini</span>
                    <span className={`text-sm font-semibold ${isPositive ? 'text-green-600' : 'text-red-600'}`}>
                        {isPositive ? '+' : ''}{formatCurrency(net_this_month)}
                    </span>
                </div>

                {/* Net last month */}
                <div className="flex items-center justify-between">
                    <span className="text-sm text-gray-600">Net bulan lalu</span>
                    <span className={`text-sm font-medium ${net_last_month >= 0 ? 'text-green-600' : 'text-red-600'}`}>
                        {net_last_month >= 0 ? '+' : ''}{formatCurrency(net_last_month)}
                    </span>
                </div>

                {/* Change indicator */}
                <div className="flex items-center justify-between pt-2 border-t">
                    <span className="text-sm text-gray-600">Perubahan</span>
                    <div className={`flex items-center gap-1 ${changeIsPositive ? 'text-green-600' : 'text-red-600'}`}>
                        <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            {changeIsPositive ? (
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 11l5-5m0 0l5 5m-5-5v12" />
                            ) : (
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 13l-5 5m0 0l-5-5m5 5V6" />
                            )}
                        </svg>
                        <span className="text-sm font-semibold">{Math.abs(change)}%</span>
                    </div>
                </div>
            </div>
        </div>
    );
}
