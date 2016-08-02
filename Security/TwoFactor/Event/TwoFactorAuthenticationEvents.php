<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Event;

class TwoFactorAuthenticationEvents
{
    const SUCCESS = 'scheb_two_factor.authentication.success';
    const FAILURE = 'scheb_two_factor.authentication.failure';
}
