<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor;

use Scheb\TwoFactorBundle\DependencyInjection\Factory\Security\TwoFactorFactory;

class TwoFactorFirewallConfig
{
    /**
     * @var array
     */
    private $options;

    public function __construct(array $options)
    {
        $this->options = $options;
    }

    public function isMultiFactor(): bool
    {
        return $this->options['multi_factor'] ?? TwoFactorFactory::DEFAULT_MULTI_FACTOR;
    }

    public function getAuthCodeParameterName(): string
    {
        return $this->options['auth_code_parameter_name'] ?? TwoFactorFactory::DEFAULT_AUTH_CODE_PARAMETER_NAME;
    }

    public function getTrustedParameterName(): string
    {
        return $this->options['trusted_parameter_name'] ?? TwoFactorFactory::DEFAULT_TRUSTED_PARAMETER_NAME;
    }
}
