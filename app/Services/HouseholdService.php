<?php

namespace App\Services;

use App\Models\Household;
use App\Models\HouseholdMember;
use App\Models\User;
use App\Repositories\Contracts\HouseholdRepositoryInterface;

class HouseholdService
{
    public function __construct(private HouseholdRepositoryInterface $householdRepository) {}

    public function createWithOwner(User $user, string $name): Household
    {
        $household = $this->householdRepository->create(['name' => $name]);

        HouseholdMember::create([
            'household_id' => $household->id,
            'user_id' => $user->id,
            'role' => 'admin',
        ]);

        return $household;
    }

    public function inviteMember(Household $household, string $email): ?HouseholdMember
    {
        $user = User::where('email', $email)->first();

        if (! $user) {
            return null;
        }

        if ($household->members()->where('user_id', $user->id)->exists()) {
            return null;
        }

        return HouseholdMember::create([
            'household_id' => $household->id,
            'user_id' => $user->id,
            'role' => 'member',
        ]);
    }

    public function removeMember(Household $household, int $userId): bool
    {
        return $household->householdMembers()
            ->where('user_id', $userId)
            ->where('role', '!=', 'admin')
            ->delete() > 0;
    }

    public function changeRole(HouseholdMember $member, string $role): HouseholdMember
    {
        $member->update(['role' => $role]);

        return $member->fresh();
    }
}
