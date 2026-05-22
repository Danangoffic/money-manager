import { PieChart, Pie, Cell, ResponsiveContainer, Tooltip } from 'recharts';

const COLORS = ['#6366f1', '#f59e0b', '#10b981', '#ef4444', '#8b5cf6', '#06b6d4', '#ec4899', '#f97316'];

function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);
}

function CustomTooltip({ active, payload }) {
    if (!active || !payload || payload.length === 0) return null;

    const data = payload[0].payload;
    return (
        <div className="bg-white border border-gray-200 rounded-lg shadow-lg p-3">
            <p className="text-sm font-medium text-gray-700">{data.name}</p>
            <p className="text-sm text-gray-600">{formatCurrency(data.value)}</p>
            <p className="text-xs text-gray-400">{data.percentage}%</p>
        </div>
    );
}

export default function TopCategoriesChart({ data = [] }) {
    // Transform backend data: [{category_id, total, category: {name}}]
    const totalExpense = data.reduce((sum, item) => sum + item.total, 0);

    const chartData = data
        .sort((a, b) => b.total - a.total)
        .slice(0, 6)
        .map((item) => ({
            name: item.category?.name || 'Tanpa Kategori',
            value: item.total,
            percentage: totalExpense > 0 ? ((item.total / totalExpense) * 100).toFixed(1) : 0,
        }));

    if (chartData.length === 0) {
        return (
            <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 className="text-lg font-semibold text-gray-900 mb-4">Top Kategori Pengeluaran</h3>
                <div className="flex items-center justify-center h-64 text-gray-400">
                    <p>Belum ada pengeluaran bulan ini.</p>
                </div>
            </div>
        );
    }

    return (
        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 className="text-lg font-semibold text-gray-900 mb-4">Top Kategori Pengeluaran</h3>
            <div className="flex items-center gap-4 h-64">
                {/* Pie Chart */}
                <div className="w-1/2 h-full">
                    <ResponsiveContainer width="100%" height="100%">
                        <PieChart>
                            <Pie
                                data={chartData}
                                cx="50%"
                                cy="50%"
                                innerRadius={50}
                                outerRadius={80}
                                paddingAngle={3}
                                dataKey="value"
                            >
                                {chartData.map((entry, index) => (
                                    <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                                ))}
                            </Pie>
                            <Tooltip content={<CustomTooltip />} />
                        </PieChart>
                    </ResponsiveContainer>
                </div>

                {/* Legend */}
                <div className="w-1/2 space-y-2">
                    {chartData.map((item, index) => (
                        <div key={index} className="flex items-center justify-between">
                            <div className="flex items-center gap-2">
                                <div
                                    className="w-3 h-3 rounded-full flex-shrink-0"
                                    style={{ backgroundColor: COLORS[index % COLORS.length] }}
                                />
                                <span className="text-sm text-gray-700 truncate max-w-[100px]">{item.name}</span>
                            </div>
                            <span className="text-sm font-medium text-gray-900 ml-2">{item.percentage}%</span>
                        </div>
                    ))}
                </div>
            </div>
        </div>
    );
}
