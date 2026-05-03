<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\Analytics\AnalyticsService;
use App\Services\Audit\AuditLogService;
use App\Services\FeatureFlagService;
use App\Services\Impersonation\ImpersonationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SuperAdminController extends Controller
{
    public function __construct(
        private AnalyticsService    $analytics,
        private ImpersonationService $impersonation,
        private FeatureFlagService  $flags,
        private AuditLogService     $auditLogs,
    ) {}

    // ── Analytics ──────────────────────────────────────────

    public function overview(): JsonResponse
    {
        return response()->json([
            'status'  => true,
            'message' => 'Platform overview.',
            'data'    => $this->analytics->platformOverview(),
        ]);
    }

    public function companyBreakdown(Request $request): JsonResponse
    {
        $sortBy = $request->get('sort_by', 'revenue');

        return response()->json([
            'status'  => true,
            'message' => 'Company breakdown.',
            'data'    => $this->analytics->companyBreakdown($sortBy),
        ]);
    }

    public function revenueTrend(Request $request): JsonResponse
    {
        $days = (int) $request->get('days', 30);

        return response()->json([
            'status'  => true,
            'message' => 'Revenue trend.',
            'data'    => $this->analytics->revenueTrend(min($days, 365)),
        ]);
    }

    public function topMenuItems(Request $request): JsonResponse
    {
        return response()->json([
            'status'  => true,
            'message' => 'Top menu items.',
            'data'    => $this->analytics->topMenuItems((int) $request->get('limit', 10)),
        ]);
    }

    // ── Impersonation ───────────────────────────────────────

    public function impersonate(Request $request, int $userId): JsonResponse
    {
        $request->validate([]);

        $result = $this->impersonation->impersonate($request->user(), $userId);

        return response()->json([
            'status'  => true,
            'message' => "Impersonating {$result['target']['name']}. Token expires at {$result['expires_at']}.",
            'data'    => $result,
        ]);
    }

    public function leaveImpersonation(Request $request): JsonResponse
    {
        $this->impersonation->leave($request->user());

        return response()->json([
            'status'  => true,
            'message' => 'Impersonation session ended.',
            'data'    => null,
        ]);
    }

    // ── Feature Flags ───────────────────────────────────────

    public function featureFlags(int $companyId): JsonResponse
    {
        return response()->json([
            'status'  => true,
            'message' => 'Feature flags retrieved.',
            'data'    => $this->flags->forCompany($companyId),
        ]);
    }

    public function toggleFlag(int $companyId, string $key): JsonResponse
    {
        $flag = $this->flags->toggle($companyId, $key);

        return response()->json([
            'status'  => true,
            'message' => "Feature '{$key}' " . ($flag->enabled ? 'enabled' : 'disabled') . '.',
            'data'    => $flag,
        ]);
    }

    public function bulkUpdateFlags(Request $request, int $companyId): JsonResponse
    {
        $request->validate([
            'flags'   => ['required', 'array'],
            'flags.*' => ['boolean'],
        ]);

        $results = $this->flags->bulkUpdate($companyId, $request->flags);

        return response()->json([
            'status'  => true,
            'message' => 'Feature flags updated.',
            'data'    => $results,
        ]);
    }

    // ── Audit Logs ──────────────────────────────────────────

    public function auditLogs(Request $request): JsonResponse
    {
        $filters = $request->only(['company_id', 'user_id', 'action', 'from', 'to']);
        $logs    = $this->auditLogs->query($filters, (int) $request->get('per_page', 30));

        return response()->json([
            'status'  => true,
            'message' => 'Audit logs retrieved.',
            'data'    => $logs,
        ]);
    }

    public function recentActions(): JsonResponse
    {
        return response()->json([
            'status'  => true,
            'message' => 'Recent actions.',
            'data'    => $this->auditLogs->recentActions(),
        ]);
    }
}
