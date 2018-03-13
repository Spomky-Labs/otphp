<?php

namespace Scheb\TwoFactorBundle\Model\Persister;

use Doctrine\Common\Persistence\ManagerRegistry;
use Scheb\TwoFactorBundle\Model\PersisterInterface;

class DoctrinePersisterFactory
{
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    /**
     * @var string
     */
    private $objectManagerName;

    public function __construct(?ManagerRegistry $managerRegistry, ?string $objectManagerName)
    {
        if (!$managerRegistry) {
            $msg = 'scheb/two-factor-bundle requires Doctrine to manage the user entity. If you don\'t want something else ';
            $msg .= 'for persistence, implement your own persister service and configure it in scheb_two_factor.persister.';
            throw new \InvalidArgumentException($msg);
        }

        $this->managerRegistry = $managerRegistry;
        $this->objectManagerName = $objectManagerName;
    }

    public function getPersister(): PersisterInterface
    {
        return new DoctrinePersister($this->managerRegistry->getManager($this->objectManagerName));
    }
}
