<?php
namespace Scheb\TwoFactorBundle\Model;

use Doctrine\ORM\EntityManager;

class DoctrinePersister implements PersisterInterface
{

    /**
     *
     * @var \Doctrine\ORM\EntityManager $em
     */
    private $em;

    /**
     * Initialize a persistor for doctrine entities
     *
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Persist the user entity
     *
     * @param object $user
     */
    public function persist($user)
    {
        $this->em->persist($user);
        $this->em->flush();
    }
}
