<?php

namespace Scheb\TwoFactorBundle\Tests\Model\Persister;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Scheb\TwoFactorBundle\Model\Persister\DoctrinePersister;
use Scheb\TwoFactorBundle\Model\Persister\DoctrinePersisterFactory;
use Scheb\TwoFactorBundle\Tests\TestCase;

class DoctrinePersisterFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function construct_noManagerRegistry_throwInvalidArgumentException()
    {
        $this->expectException(\InvalidArgumentException::class);
        new DoctrinePersisterFactory(null, 'managerName');
    }

    /**
     * @test
     */
    public function getPersister_managerExists_returnDoctrinePersister()
    {
        $managerRegister = $this->createMock(ManagerRegistry::class);
        $managerRegister
            ->expects($this->once())
            ->method('getManager')
            ->with('managerName')
            ->willReturn($this->createMock(ObjectManager::class));

        $persisterFactory = new DoctrinePersisterFactory($managerRegister, 'managerName');
        $returnValue = $persisterFactory->getPersister();

        $this->assertInstanceOf(DoctrinePersister::class, $returnValue);
    }
}
