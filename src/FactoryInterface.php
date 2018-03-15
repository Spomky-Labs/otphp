<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2018 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace OTPHP;

interface FactoryInterface
{
    /**
     * This method is the unique public method of the class.
     * It can load a provisioning Uri and convert it into an OTP object.
     *
     * @param string $uri
     *
     * @throws \InvalidArgumentException
     *
     * @return OTPInterface
     */
    public static function loadFromProvisioningUri(string $uri): OTPInterface;
}
