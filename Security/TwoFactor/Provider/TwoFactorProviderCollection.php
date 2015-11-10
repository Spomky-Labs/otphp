<?php
namespace Scheb\TwoFactorBundle\Security\TwoFactor\Provider;

use Symfony\Component\DependencyInjection\Reference;

/**
 * Class TwoFactorProviderCollection
 */
class TwoFactorProviderCollection
{
    /**
     * @var array
     **/
    protected $providers = array();

    /**
     * addProvider
     * @param string                                          $name
     * @param Symfony\Component\DependencyInjection\Reference $provider
     * @return void
     **/
    public function addProvider($name, Reference $provider)
    {
        $this->providers[$name] = $provider;
    }

    /**
     * getProviders
     * @return array
     **/
    public function getProviders()
    {
        return $this->providers;
    }
}
