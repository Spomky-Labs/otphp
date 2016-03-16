<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2016 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace OTPHP;

use Assert\Assertion;
use Base32\Base32;

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
    private $issuer_included_as_parameter = false;

    /**
     * @return bool Return true is it must be included as parameter, else false
     */
    protected function issuerAsParameter()
    {
        if (null !== $this->getIssuer() && $this->isIssuerIncludedAsParameter() === true) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function getSecret()
    {
        return $this->getParameter('secret');
    }

    /**
     * @param string $secret
     */
    private function setSecret($secret)
    {
        Assertion::string($secret, 'Secret must be a string.');

        $this->parameters['secret'] = $secret;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    private function setLabel($label)
    {
        Assertion::string($label, 'Label must be a string.');
        Assertion::false($this->hasSemicolon($label), 'Label must not contain a semi-colon.');

        $this->label = $label;
    }

    /**
     * {@inheritdoc}
     */
    public function getIssuer()
    {
        return $this->issuer;
    }

    /**
     * {@inheritdoc}
     */
    public function setIssuer($issuer)
    {
        Assertion::string($issuer, 'Issuer must be a string.');
        Assertion::false($this->hasSemicolon($issuer), 'Issuer must not contain a semi-colon.');

        $this->issuer = $issuer;
    }

    /**
     * {@inheritdoc}
     */
    public function isIssuerIncludedAsParameter()
    {
        return $this->issuer_included_as_parameter;
    }

    /**
     * {@inheritdoc}
     */
    public function setIssuerIncludedAsParameter($issuer_included_as_parameter)
    {
        Assertion::boolean($issuer_included_as_parameter, 'A boolean is expected.');
        $this->issuer_included_as_parameter = $issuer_included_as_parameter;
    }

    /**
     * {@inheritdoc}
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
        Assertion::greaterThan((int)$digits, 0, 'Digits must be at least 1.');

        $this->parameters['digits'] = (int)$digits;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function hasParameter($parameter)
    {
        return array_key_exists($parameter, $this->parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getParameter($parameter)
    {
        if ($this->hasParameter($parameter)) {
            return $this->getParameters()[$parameter];
        }

        throw new \InvalidArgumentException(sprintf('Parameter "%s" does not exist', $parameter));
    }

    /**
     * {@inheritdoc}
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
    private function hasSemicolon($value)
    {
        $semicolons = [':', '%3A', '%3a'];
        foreach ($semicolons as $semicolon) {
            if (false !== strpos($value, $semicolon)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string
     */
    private function getDecodedSecret()
    {
        $secret = Base32::decode($this->getSecret());

        return $secret;
    }
}
