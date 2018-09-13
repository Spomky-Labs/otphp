<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2018 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace OTPHP;

use Assert\Assertion;
use ParagonIE\ConstantTime\Base32;

trait ParameterTrait
{
    /**
     * @var array
     */
    private $parameters = [];

    /**
     * @var string|null
     */
    private $issuer = null;

    /**
     * @var string|null
     */
    private $label = null;

    /**
     * @var bool
     */
    private $issuer_included_as_parameter = true;

    /**
     * @return array
     */
    public function getParameters()
    {
        $parameters = $this->parameters;

        if (null !== $this->getIssuer() && $this->isIssuerIncludedAsParameter() === true) {
            $parameters['issuer'] = $this->getIssuer();
        }

        return $parameters;
    }

    /**
     * @return string
     */
    public function getSecret()
    {
        return $this->getParameter('secret');
    }

    /**
     * @param string|null $secret
     */
    private function setSecret($secret)
    {
        Assertion::nullOrString($secret, 'The secret must be a string or null.');
        if (null === $secret) {
            $secret = trim(Base32::encodeUpper(random_bytes(32)), '=');
        }
        $secret = strtoupper($secret);

        $this->parameters['secret'] = $secret;
    }

    /**
     * @return string|null
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string|null $label
     */
    private function setLabel($label)
    {
        Assertion::nullOrString($label, 'Label must be null or a string.');

        $this->label = $label;
    }

    /**
     * @return string|null
     */
    public function getIssuer()
    {
        return $this->issuer;
    }

    /**
     * @param string $issuer
     */
    public function setIssuer($issuer)
    {
        Assertion::string($issuer, 'Issuer must be a string.');
        Assertion::false($this->hasColon($issuer), 'Issuer must not contain a colon.');

        $this->issuer = $issuer;
    }

    /**
     * @return bool
     */
    public function isIssuerIncludedAsParameter()
    {
        return $this->issuer_included_as_parameter;
    }

    /**
     * @param bool $issuer_included_as_parameter
     */
    public function setIssuerIncludedAsParameter($issuer_included_as_parameter)
    {
        Assertion::boolean($issuer_included_as_parameter, 'A boolean is expected.');
        $this->issuer_included_as_parameter = $issuer_included_as_parameter;
    }

    /**
     * @return int
     */
    public function getDigits()
    {
        return $this->getParameter('digits');
    }

    /**
     * @param int $digits
     */
    private function setDigits($digits)
    {
        Assertion::greaterThan((int) $digits, 0, 'Digits must be at least 1.');

        $this->parameters['digits'] = (int) $digits;
    }

    /**
     * @return string
     */
    public function getDigest()
    {
        return $this->getParameter('algorithm');
    }

    /**
     * @param string $digest
     */
    private function setDigest($digest)
    {
        Assertion::string($digest, 'Digest must be a string.');
        Assertion::inArray($digest, hash_algos(), sprintf('The "%s" digest is not supported.', $digest));

        $this->parameters['algorithm'] = $digest;
    }

    /**
     * @param string $parameter
     *
     * @return bool
     */
    public function hasParameter($parameter)
    {
        return array_key_exists($parameter, $this->parameters);
    }

    /**
     * @param string $parameter
     */
    public function getParameter($parameter)
    {
        if ($this->hasParameter($parameter)) {
            return $this->getParameters()[$parameter];
        }

        throw new \InvalidArgumentException(sprintf('Parameter "%s" does not exist', $parameter));
    }

    /**
     * @param string $parameter
     * @param int    $value
     */
    public function setParameter($parameter, $value)
    {
        Assertion::string($parameter, 'Parameter index must be a string.');
        $map = $this->getParameterMap();

        if (true === array_key_exists($parameter, $map)) {
            $method = $map[$parameter];
            $this->$method($value);
        } else {
            $this->parameters[$parameter] = $value;
        }
    }

    /**
     * @return array
     */
    private function getParameterMap()
    {
        return [
            'label'  => 'setLabel',
            'secret' => 'setSecret',
            'digest' => 'setDigest',
            'digits' => 'setDigits',
            'issuer' => 'setIssuer',
        ];
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    private function hasColon($value)
    {
        $colons = [':', '%3A', '%3a'];
        foreach ($colons as $colon) {
            if (false !== strpos($value, $colon)) {
                return true;
            }
        }

        return false;
    }
}
