<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Event;

class TwoFactorAuthenticationEvents
{
    /**
     * When two-factor authentication is attempted, dispatched before the code is checked.
     */
    const ATTEMPT = 'scheb_two_factor.authentication.attempt';

    /**
     * When two-factor authentication was successful (code was valid) for a single provider.
     */
    const SUCCESS = 'scheb_two_factor.authentication.success';

    /**
     * When two-factor authentication failed (code was invalid) for a single provider.
     */
    const FAILURE = 'scheb_two_factor.authentication.failure';

    /**
     * When the entire two-factor authentication process was completed successfully, that means two-factor authentication
     * was successful for all providers and the user is now fully authenticated.
     */
    const COMPLETE = 'scheb_two_factor.authentication.complete';
}
