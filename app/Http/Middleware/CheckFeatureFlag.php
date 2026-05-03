<?php

namespace App\Http\Middleware;

use App\Models\FeatureFlag;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFeatureFlag
{
    public function handle(Request $request, Closure $next, string $flag): mixed
    {
        $user = $request->user();

        // Super admin always passes
        if ($user?->isSuperAdmin()) {
            return $next($request);
        }

        if (!$user?->company_id || !FeatureFlag::isEnabled($user->company_id, $flag)) {
            return response()->json([
                'status'  => false,
                'message' => "Feature '{$flag}' is not enabled for your company.",
                'data'    => null,
            ], 403);
        }

        return $next($request);
    }
}
