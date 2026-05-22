<?php

namespace App\Providers;

use App\Repositories\AccountRepository;
use App\Repositories\BudgetRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\Contracts\AccountRepositoryInterface;
use App\Repositories\Contracts\BudgetRepositoryInterface;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\GoalRepositoryInterface;
use App\Repositories\Contracts\HouseholdRepositoryInterface;
use App\Repositories\Contracts\RecurringTransactionRepositoryInterface;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use App\Repositories\GoalRepository;
use App\Repositories\HouseholdRepository;
use App\Repositories\RecurringTransactionRepository;
use App\Repositories\TransactionRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    protected array $repositories = [
        HouseholdRepositoryInterface::class => HouseholdRepository::class,
        AccountRepositoryInterface::class => AccountRepository::class,
        CategoryRepositoryInterface::class => CategoryRepository::class,
        TransactionRepositoryInterface::class => TransactionRepository::class,
        BudgetRepositoryInterface::class => BudgetRepository::class,
        GoalRepositoryInterface::class => GoalRepository::class,
        RecurringTransactionRepositoryInterface::class => RecurringTransactionRepository::class,
    ];

    public function register(): void
    {
        foreach ($this->repositories as $interface => $implementation) {
            $this->app->bind($interface, $implementation);
        }
    }

    public function boot(): void {}
}
