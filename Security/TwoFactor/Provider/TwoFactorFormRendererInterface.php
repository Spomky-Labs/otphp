<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Provider;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface TwoFactorFormRendererInterface
{
    /**
     * Render the authentication form of a two-factor provider.
     *
     * @param Request $request
     * @param array   $templateVars
     *
     * @return Response
     */
    public function renderForm(Request $request, array $templateVars): Response;
}
