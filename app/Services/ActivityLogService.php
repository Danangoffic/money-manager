<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityLogService
{
    public function log(
        string $action,
        Model $model,
        ?string $description = null,
        ?array $oldValues = null,
        ?array $newValues = null,
    ): ActivityLog {
        $householdId = $model->household_id ?? Auth::user()?->household_id;

        return ActivityLog::create([
            'household_id' => $householdId,
            'user_id' => Auth::id(),
            'action' => $action,
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'description' => $description ?? $this->generateDescription($action, $model),
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ]);
    }

    public function getByHousehold(int $householdId, int $perPage = 20): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return ActivityLog::where('household_id', $householdId)
            ->with('user')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    private function generateDescription(string $action, Model $model): string
    {
        $modelName = class_basename($model);
        $userName = Auth::user()?->name ?? 'System';

        $actionLabels = [
            'created' => 'membuat',
            'updated' => 'memperbarui',
            'deleted' => 'menghapus',
            'restored' => 'memulihkan',
        ];

        $actionLabel = $actionLabels[$action] ?? $action;

        return "{$userName} {$actionLabel} {$modelName}";
    }
}
