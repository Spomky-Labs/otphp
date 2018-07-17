<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2018 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace Scheb\TwoFactorBundle\Model\Persister;

use Doctrine\Common\Persistence\ObjectManager;
use Scheb\TwoFactorBundle\Model\PersisterInterface;

class DoctrinePersister implements PersisterInterface
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * Initialize a persister for doctrine entities.
     *
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * Persist the user entity.
     *
     * @param object $user
     */
    public function persist($user): void
    {
        $this->om->persist($user);
        $this->om->flush();
    }
}
