<?php

namespace Scheb\TwoFactorBundle\Tests\Model\Persister;

use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\MockObject\MockObject;
use Scheb\TwoFactorBundle\Model\Persister\DoctrinePersister;
use Scheb\TwoFactorBundle\Tests\TestCase;

class DoctrinePersisterTest extends TestCase
{
    /**
     * @var MockObject|EntityManager
     */
    private $em;

    /**
     * @var \Scheb\TwoFactorBundle\Model\Persister\DoctrinePersister
     */
    private $persister;

    protected function setUp()
    {
        // Although we use Doctrine's generic ObjectManager as an interface, for testing we will use Doctrine2 ORM's EntityManager
        $this->em = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['persist', 'flush'])
            ->getMock();

        $this->persister = new DoctrinePersister($this->em);
    }

    /**
     * @test
     */
    public function persist_persistObject_callPersistAndFlush()
    {
        $user = new \stdClass(); //Some user object

        //Mock the EntityManager
        $this->em
            ->expects($this->once())
            ->method('persist')
            ->with($user);
        $this->em
            ->expects($this->once())
            ->method('flush');

        $this->persister->persist($user);
    }
}
