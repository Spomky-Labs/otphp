<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\Trusted;

use Scheb\TwoFactorBundle\Model\TrustedComputerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class UserInterfaceWithTrustedComputerInterface implements UserInterface, TrustedComputerInterface
{

}
