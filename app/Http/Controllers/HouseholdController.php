<?php

namespace App\Http\Controllers;

use App\Http\Requests\Household\InviteMemberRequest;
use App\Services\HouseholdService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HouseholdController extends Controller
{
    public function __construct(private HouseholdService $householdService) {}

    public function create(): Response
    {
        return Inertia::render('Household/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['name' => ['required', 'string', 'max:255']]);

        $this->householdService->createWithOwner($request->user(), $request->name);

        return redirect()->route('dashboard')->with('success', 'Household berhasil dibuat.');
    }

    public function settings(Request $request): Response
    {
        $household = $request->user()->household;

        return Inertia::render('Household/Settings', [
            'household' => $household,
            'members' => $household->members,
            'isAdmin' => $request->user()->isHouseholdAdmin(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate(['name' => ['required', 'string', 'max:255']]);

        $household = $request->user()->household;
        $household->update(['name' => $request->name]);

        return back()->with('success', 'Nama household berhasil diperbarui.');
    }

    public function inviteMember(InviteMemberRequest $request): RedirectResponse
    {
        $household = $request->user()->household;
        $member = $this->householdService->inviteMember($household, $request->email);

        if (! $member) {
            return back()->with('error', 'User sudah menjadi anggota atau tidak ditemukan.');
        }

        return back()->with('success', 'Anggota berhasil diundang.');
    }

    public function removeMember(Request $request, int $userId): RedirectResponse
    {
        $household = $request->user()->household;
        $this->householdService->removeMember($household, $userId);

        return back()->with('success', 'Anggota berhasil dihapus.');
    }

    public function changeRole(Request $request, int $memberId): RedirectResponse
    {
        $request->validate(['role' => ['required', 'in:admin,member']]);

        $member = $request->user()->household->householdMembers()->findOrFail($memberId);
        $this->householdService->changeRole($member, $request->role);

        return back()->with('success', 'Role anggota berhasil diubah.');
    }
}
