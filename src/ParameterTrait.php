<?php

declare(strict_types=1);

namespace OTPHP;

use function array_key_exists;
use Assert\Assertion;
use InvalidArgumentException;
use ParagonIE\ConstantTime\Base32;

trait ParameterTrait
{
    /**
     * @var array<string, mixed>
     */
    private array $parameters = [];

    private null|string $issuer = null;

    private null|string $label = null;

    private bool $issuer_included_as_parameter = true;

    /**
     * @return array<string, mixed>
     */
    public function getParameters(): array
    {
        $parameters = $this->parameters;

        if ($this->getIssuer() !== null && $this->isIssuerIncludedAsParameter() === true) {
            $parameters['issuer'] = $this->getIssuer();
        }

        return $parameters;
    }

    public function getSecret(): string
    {
        $value = $this->getParameter('secret');
        Assertion::string($value, 'Invalid "secret" parameter.');

        return $value;
    }

    public function getLabel(): null|string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->setParameter('label', $label);
    }

    public function getIssuer(): null|string
    {
        return $this->issuer;
    }

    public function setIssuer(string $issuer): void
    {
        $this->setParameter('issuer', $issuer);
    }

    public function isIssuerIncludedAsParameter(): bool
    {
        return $this->issuer_included_as_parameter;
    }

    public function setIssuerIncludedAsParameter(bool $issuer_included_as_parameter): void
    {
        $this->issuer_included_as_parameter = $issuer_included_as_parameter;
    }

    public function getDigits(): int
    {
        $value = $this->getParameter('digits');
        Assertion::integer($value, 'Invalid "digits" parameter.');

        return $value;
    }

    public function getDigest(): string
    {
        $value = $this->getParameter('algorithm');
        Assertion::string($value, 'Invalid "algorithm" parameter.');

        return $value;
    }

    public function hasParameter(string $parameter): bool
    {
        return array_key_exists($parameter, $this->parameters);
    }

    public function getParameter(string $parameter): mixed
    {
        if ($this->hasParameter($parameter)) {
            return $this->getParameters()[$parameter];
        }

        throw new InvalidArgumentException(sprintf('Parameter "%s" does not exist', $parameter));
    }

    public function setParameter(string $parameter, mixed $value): void
    {
        $map = $this->getParameterMap();

        if (array_key_exists($parameter, $map) === true) {
            $callback = $map[$parameter];
            $value = $callback($value);
        }

        if (property_exists($this, $parameter)) {
            $this->{$parameter} = $value;
        } else {
            $this->parameters[$parameter] = $value;
        }
    }

    /**
     * @return array<string, callable>
     */
    protected function getParameterMap(): array
    {
        return [
            'label' => function ($value) {
                Assertion::false($this->hasColon($value), 'Label must not contain a colon.');

                return $value;
            },
            'secret' => static function ($value): string {
                if ($value === null) {
                    $value = Base32::encodeUpper(random_bytes(64));
                }

                return mb_strtoupper(trim($value, '='));
            },
            'algorithm' => static function ($value): string {
                $value = mb_strtolower($value);
                Assertion::inArray($value, hash_algos(), sprintf('The "%s" digest is not supported.', $value));

                return $value;
            },
            'digits' => static function ($value): int {
                Assertion::greaterThan($value, 0, 'Digits must be at least 1.');

                return (int) $value;
            },
            'issuer' => function ($value) {
                Assertion::false($this->hasColon($value), 'Issuer must not contain a colon.');

                return $value;
            },
        ];
    }

    private function setSecret(null|string $secret): void
    {
        $this->setParameter('secret', $secret);
    }

    private function setDigits(int $digits): void
    {
        $this->setParameter('digits', $digits);
    }

    private function setDigest(string $digest): void
    {
        $this->setParameter('algorithm', $digest);
    }

    private function hasColon(string $value): bool
    {
        $colons = [':', '%3A', '%3a'];
        foreach ($colons as $colon) {
            if (str_contains($value, $colon)) {
                return true;
            }
        }

        return false;
    }
}
