<?php

namespace Scheb\TwoFactorBundle\Tests\Model;

use Scheb\TwoFactorBundle\Model\DoctrinePersister;
use Scheb\TwoFactorBundle\Tests\TestCase;

class DoctrinePersisterTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $em;

    /**
     * @var DoctrinePersister
     */
    private $persister;

    public function setUp()
    {
        // Although we use Doctrine's generic ObjectManager as an interface, for testing we will use Doctrine2 ORM's EntityManager
        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->setMethods(array('persist', 'flush'))
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
