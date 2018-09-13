<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2018 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace OTPHP;

interface HOTPInterface extends OTPInterface
{
    /**
     * @return int The initial counter (a positive integer)
     */
    public function getCounter();
}
