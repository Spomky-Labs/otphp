<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2018 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

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
