<?php

namespace App\Repositories;

use App\Models\Transaction;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TransactionRepository extends BaseRepository implements TransactionRepositoryInterface
{
    public function __construct(Transaction $model)
    {
        parent::__construct($model);
    }

    public function getByHouseholdFiltered(int $householdId, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model
            ->where('household_id', $householdId)
            ->with(['account', 'category', 'user', 'transferToAccount'])
            ->orderByDesc('date')
            ->orderByDesc('id');

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['account_id'])) {
            $query->where('account_id', $filters['account_id']);
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (! empty($filters['start_date'])) {
            $query->where('date', '>=', $filters['start_date']);
        }

        if (! empty($filters['end_date'])) {
            $query->where('date', '<=', $filters['end_date']);
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }

    public function sumByType(int $householdId, string $type, string $startDate, string $endDate): int
    {
        return (int) $this->model
            ->where('household_id', $householdId)
            ->where('type', $type)
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('amount');
    }

    public function sumByCategoryForPeriod(int $householdId, string $startDate, string $endDate): Collection
    {
        return $this->model
            ->where('household_id', $householdId)
            ->where('type', 'expense')
            ->whereBetween('date', [$startDate, $endDate])
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->with('category')
            ->get();
    }

    public function getRecentByHousehold(int $householdId, int $limit = 5): Collection
    {
        return $this->model
            ->where('household_id', $householdId)
            ->with(['account', 'category', 'user'])
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    public function sumByMonthForRange(int $householdId, string $startDate, string $endDate): Collection
    {
        return $this->model
            ->where('household_id', $householdId)
            ->whereIn('type', ['income', 'expense'])
            ->whereBetween('date', [$startDate, $endDate])
            ->selectRaw("strftime('%Y-%m', date) as month, type, SUM(amount) as total")
            ->groupBy('month', 'type')
            ->orderBy('month')
            ->get();
    }
}
