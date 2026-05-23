<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface TransactionRepositoryInterface extends BaseRepositoryInterface
{
    public function getByHouseholdFiltered(int $householdId, array $filters = []): LengthAwarePaginator;

    public function sumByType(int $householdId, string $type, string $startDate, string $endDate): int;

    public function sumByCategoryForPeriod(int $householdId, string $startDate, string $endDate): Collection;

    public function getRecentByHousehold(int $householdId, int $limit = 5): Collection;

    public function sumByMonthForRange(int $householdId, string $startDate, string $endDate): Collection;

    public function countByPeriod(int $householdId, string $startDate, string $endDate): int;

    public function getDeletedByHousehold(int $householdId, int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator;

    public function restore(int $id): bool;

    public function forceDelete(int $id): bool;
}
