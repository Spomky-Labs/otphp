<?php
namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\Provider;

use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderCollection;

class TwoFactorProviderCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function addProvider_getProviders()
    {
        $collection = new TwoFactorProviderCollection();
        $provider = $this->getMock("Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderInterface");

        $collection->addProvider('test', $provider);

        $providers = $collection->getProviders();

        $this->assertEquals(array('test' => $provider), $providers);
    }

    /**
     * @test
     */
    public function noProvider_getProviders()
    {
        $collection = new TwoFactorProviderCollection();

        $providers = $collection->getProviders();

        $this->assertEquals(array(), $providers);
    }
}
