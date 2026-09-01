<?php

namespace App\Auth;

use Illuminate\Auth\SessionGuard;

/**
 * Workaround for a regression in laravel/framework v12.69.0.
 *
 * That release started binding the "remember me" cookie to a HMAC of the user's
 * password hash, but SessionGuard::userFromRecaller() passes the password hash
 * straight into hash_equals() without checking that a user was actually found:
 *
 *     $userPassword = $this->viaRemember ? $user->getAuthPassword() : null;
 *     ...
 *     || hash_equals($userPassword, $recallerHash)   // TypeError when null
 *
 * So any request carrying a stale remember_web_* cookie — one whose token no
 * longer matches the users table — throws
 * "hash_equals(): Argument #1 ($known_string) must be of type string, null given"
 * and returns a 500 instead of simply treating the cookie as invalid.
 *
 * This subclass restores the guard clause while keeping the v12.69.0 security
 * behaviour intact. Delete this class and its Auth::extend() registration in
 * AppServiceProvider once upstream ships a fix.
 *
 * @see https://github.com/laravel/framework/blob/v12.69.0/src/Illuminate/Auth/SessionGuard.php
 */
class PatchedSessionGuard extends SessionGuard
{
    /**
     * Pull a user from the repository by its "remember me" cookie token.
     *
     * @param  \Illuminate\Auth\Recaller  $recaller
     * @return mixed
     */
    protected function userFromRecaller($recaller)
    {
        if (! $recaller->valid() || $this->recallAttempted) {
            return;
        }

        // If the user is null, but we decrypt a "recaller" cookie we can attempt to
        // pull the user data on that cookie which serves as a remember cookie on
        // the application. Once we have a user we can return it to the caller.
        $this->recallAttempted = true;

        $this->viaRemember = ! is_null($user = $this->provider->retrieveByToken(
            $recaller->id(), $recaller->token()
        ));

        // The cookie points at a user that no longer exists, or its token has since
        // been rotated. There is nothing to compare against, so treat the cookie as
        // invalid rather than handing a null password hash to hash_equals().
        if (! $this->viaRemember) {
            return null;
        }

        // Users created without a password (social logins, for example) would also
        // put null into hash_equals(), so fall back to an empty string, which can
        // never match a real recaller hash.
        $userPassword = $user->getAuthPassword() ?? '';

        $recallerHash = $recaller->hash();

        if (hash_equals($this->hashPasswordForCookie($userPassword), $recallerHash)
            || hash_equals($userPassword, $recallerHash)) {
            return $user;
        }

        $this->viaRemember = false;

        return null;
    }
}
