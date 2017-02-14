<?php

namespace Scheb\TwoFactorBundle\Model;

use Doctrine\Common\Persistence\ObjectManager;

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
    public function persist($user)
    {
        $this->om->persist($user);
        $this->om->flush();
    }
}
