<?php

declare(strict_types=1);

namespace OTPHP;

use Exception;
use InvalidArgumentException;
use ParagonIE\ConstantTime\Base32;
use RuntimeException;
use function array_key_exists;
use function assert;
use function chr;
use function count;
use function in_array;
use function is_int;
use function is_string;
use function sprintf;
use const STR_PAD_LEFT;

/**
 * @readonly
 */
abstract class OTP implements OTPInterface
{
    private const DEFAULT_SECRET_SIZE = 64;

    /**
     * @var array<non-empty-string, mixed>
     */
    private array $parameters = [];

    /**
     * @var non-empty-string|null
     */
    private null|string $issuer = null;

    /**
     * @var non-empty-string|null
     */
    private null|string $label = null;

    private bool $issuer_included_as_parameter = true;

    /**
     * @param non-empty-string $secret
     */
    protected function __construct(string $secret)
    {
        $this->setSecret($secret);
    }

    public function getQrCodeUri(string $uri, string $placeholder): string
    {
        $provisioning_uri = urlencode($this->getProvisioningUri());

        return str_replace($placeholder, $provisioning_uri, $uri);
    }

    /**
     * @param 0|positive-int $input
     */
    public function at(int $input): string
    {
        return $this->generateOTP($input);
    }

    /**
     * @return array<non-empty-string, mixed>
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
        (is_string($value) && $value !== '') || throw new InvalidArgumentException('Invalid "secret" parameter.');

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

    public function withLabel(string $label): self
    {
        $otp = clone $this;
        $otp->setParameter('label', $label);

        return $otp;
    }

    public function getIssuer(): null|string
    {
        return $this->issuer;
    }

    public function setIssuer(string $issuer): void
    {
        $this->setParameter('issuer', $issuer);
    }

    public function withIssuer(string $issuer): self
    {
        $otp = clone $this;
        $otp->setParameter('issuer', $issuer);

        return $otp;
    }

    public function isIssuerIncludedAsParameter(): bool
    {
        return $this->issuer_included_as_parameter;
    }

    public function setIssuerIncludedAsParameter(bool $issuer_included_as_parameter): void
    {
        $this->issuer_included_as_parameter = $issuer_included_as_parameter;
    }

    public function withIssuerIncludedAsParameter(bool $issuer_included_as_parameter): self
    {
        $otp = clone $this;
        $otp->issuer_included_as_parameter = $issuer_included_as_parameter;

        return $otp;
    }

    public function getDigits(): int
    {
        $value = $this->getParameter('digits');
        (is_int($value) && $value > 0) || throw new InvalidArgumentException('Invalid "digits" parameter.');

        return $value;
    }

    public function getDigest(): string
    {
        $value = $this->getParameter('algorithm');
        (is_string($value) && $value !== '') || throw new InvalidArgumentException('Invalid "algorithm" parameter.');

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

    public function withParameter(string $parameter, mixed $value): self
    {
        $otp = clone $this;
        $otp->setParameter($parameter, $value);

        return $otp;
    }

    public function setSecret(string $secret): void
    {
        $this->setParameter('secret', $secret);
    }

    public function withSecret(string $secret): self
    {
        $otp = clone $this;
        $otp->setParameter('secret', $secret);

        return $otp;
    }

    public function setDigits(int $digits): void
    {
        $this->setParameter('digits', $digits);
    }

    public function withDigits(int $digits): self
    {
        $otp = clone $this;
        $otp->setParameter('digits', $digits);

        return $otp;
    }

    public function setDigest(string $digest): void
    {
        $this->setParameter('algorithm', $digest);
    }

    public function withDigest(string $digest): self
    {
        $otp = clone $this;
        $otp->setParameter('algorithm', $digest);

        return $otp;
    }

    /**
     * @return non-empty-string
     */
    final protected static function generateSecret(): string
    {
        return Base32::encodeUpper(random_bytes(self::DEFAULT_SECRET_SIZE));
    }

    /**
     * The OTP at the specified input.
     *
     * @param 0|positive-int $input
     *
     * @return non-empty-string
     */
    protected function generateOTP(int $input): string
    {
        $hash = hash_hmac($this->getDigest(), $this->intToByteString($input), $this->getDecodedSecret(), true);
        $unpacked = unpack('C*', $hash);
        $unpacked !== false || throw new InvalidArgumentException('Invalid data.');
        $hmac = array_values($unpacked);

        $offset = ($hmac[count($hmac) - 1] & 0xF);
        $code = ($hmac[$offset] & 0x7F) << 24 | ($hmac[$offset + 1] & 0xFF) << 16 | ($hmac[$offset + 2] & 0xFF) << 8 | ($hmac[$offset + 3] & 0xFF);
        $otp = $code % (10 ** $this->getDigits());

        return str_pad((string) $otp, $this->getDigits(), '0', STR_PAD_LEFT);
    }

    /**
     * @param array<non-empty-string, mixed> $options
     */
    protected function filterOptions(array &$options): void
    {
        foreach ([
            'algorithm' => 'sha1',
            'period' => 30,
            'digits' => 6,
        ] as $key => $default) {
            if (isset($options[$key]) && $default === $options[$key]) {
                unset($options[$key]);
            }
        }

        ksort($options);
    }

    /**
     * @param non-empty-string $type
     * @param array<non-empty-string, mixed> $options
     *
     * @return non-empty-string
     */
    protected function generateURI(string $type, array $options): string
    {
        $options = [...$options, ...$this->getParameters()];
        $this->filterOptions($options);
        $params = str_replace(['+', '%7E'], ['%20', '~'], http_build_query($options, '', '&'));

        return sprintf(
            'otpauth://%s/%s?%s',
            $type,
            rawurlencode($this->buildProvisioningUriLabel()),
            $params
        );
    }

    /**
     * @param non-empty-string $safe
     * @param non-empty-string $user
     */
    protected function compareOTP(string $safe, string $user): bool
    {
        return hash_equals($safe, $user);
    }

    /**
     * @return array<non-empty-string, callable>
     */
    protected function getParameterMap(): array
    {
        return [
            'label' => function (string $value): string {
                assert($value !== '');
                $this->hasColon($value) === false || throw new InvalidArgumentException(
                    'Label must not contain a colon.'
                );

                return $value;
            },
            'secret' => static fn (string $value): string => mb_strtoupper(mb_trim($value, '=')),
            'algorithm' => static function (string $value): string {
                $value = mb_strtolower($value);
                in_array($value, hash_algos(), true) || throw new InvalidArgumentException(sprintf(
                    'The "%s" digest is not supported.',
                    $value
                ));

                return $value;
            },
            'digits' => static function ($value): int {
                $value > 0 || throw new InvalidArgumentException('Digits must be at least 1.');

                return (int) $value;
            },
            'issuer' => function (string $value): string {
                assert($value !== '');
                $this->hasColon($value) === false || throw new InvalidArgumentException(
                    'Issuer must not contain a colon.'
                );

                return $value;
            },
        ];
    }

    /**
     * @return non-empty-string
     */
    private function getDecodedSecret(): string
    {
        try {
            $decoded = Base32::decodeUpper($this->getSecret());
        } catch (Exception) {
            throw new RuntimeException('Unable to decode the secret. Is it correctly base32 encoded?');
        }
        assert($decoded !== '');

        return $decoded;
    }

    private function intToByteString(int $int): string
    {
        $result = [];
        while ($int !== 0) {
            $result[] = chr($int & 0xFF);
            $int >>= 8;
        }

        return str_pad(implode('', array_reverse($result)), 8, "\000", STR_PAD_LEFT);
    }

    /**
     * @return non-empty-string
     */
    private function buildProvisioningUriLabel(): string
    {
        $issuer = $this->getIssuer();
        $label = $this->getLabel();

        return match (true) {
            $issuer === null && $label === null => throw new InvalidArgumentException(
                'The label is not set. Either label or issuer must be set.'
            ),
            $label !== null && $this->hasColon($label) => throw new InvalidArgumentException(
                'Label must not contain a colon.'
            ),
            $issuer !== null && $label !== null => $issuer . ':' . $label,
            $issuer !== null => $issuer,
            default => $label,
        };
    }

    /**
     * @param non-empty-string $value
     */
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
