<?php

namespace App\Http\Middleware;

use App\Models\WebSetting;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class EnsureSiteIsNotInMaintenance
{
    public function handle(Request $request, Closure $next): Response
    {
        $settings = WebSetting::first();

        if (! $settings?->maintenance_mode || $this->canBypass($request)) {
            return $next($request);
        }

        return Inertia::render('Errors/Maintenance', [
            'status' => 503,
            'title' => $settings->maintenance_title ?: 'We are polishing the shop',
            'message' => $settings->maintenance_message ?: 'SweetChocholate is temporarily unavailable while we make a few improvements.',
        ])->toResponse($request)->setStatusCode(503);
    }

    private function canBypass(Request $request): bool
    {
        if ($request->is('admin/login') || $request->is('login') || $request->is('logout') || $request->is('up')) {
            return true;
        }

        if (! $request->is('admin') && ! $request->is('admin/*') && ! $request->is('dashboard')) {
            return false;
        }

        $user = $request->user();

        return $user && $user->hasAnyRole(['Super Admin', 'Administrator']);
    }
}
