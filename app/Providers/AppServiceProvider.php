<?php

namespace App\Providers;

use App\Auth\PatchedSessionGuard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        Vite::prefetch(concurrency: 3);

        $this->registerPatchedSessionGuard();
    }

    /**
     * Replace the built-in "session" guard with one that survives a stale
     * remember me cookie. Remove once laravel/framework patches v12.69.0.
     *
     * Mirrors Illuminate\Auth\AuthManager::createSessionDriver().
     *
     * @see \App\Auth\PatchedSessionGuard
     */
    protected function registerPatchedSessionGuard(): void
    {
        Auth::extend('session', function ($app, $name, array $config) {
            $guard = new PatchedSessionGuard(
                $name,
                Auth::createUserProvider($config['provider'] ?? null),
                $app['session.store'],
                rehashOnLogin: $app['config']->get('hashing.rehash_on_login', true),
                timeboxDuration: $app['config']->get('auth.timebox_duration', 200000),
                hashKey: $app['config']->get('app.key'),
            );

            $guard->setCookieJar($app['cookie']);
            $guard->setDispatcher($app['events']);

            // Upstream calls setRequest() unconditionally, which fatals when no
            // request is bound yet (console commands, queue workers). The
            // rebinding callback still fires once one is bound.
            if ($request = $app->refresh('request', $guard, 'setRequest')) {
                $guard->setRequest($request);
            }

            if (isset($config['remember'])) {
                $guard->setRememberDuration($config['remember']);
            }

            return $guard;
        });
    }
}
