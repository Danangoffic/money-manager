<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogController extends Controller
{
    public function __construct(private ActivityLogService $activityLogService) {}

    public function index(Request $request): Response
    {
        $householdId = $request->user()->household_id;

        return Inertia::render('ActivityLogs/Index', [
            'logs' => $this->activityLogService->getByHousehold($householdId),
        ]);
    }
}
