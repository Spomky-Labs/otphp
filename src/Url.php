<?php

declare(strict_types=1);

namespace OTPHP;

use Assert\Assertion;
use function Safe\parse_url;

/**
 * @internal
 */
final class Url
{
    public function __construct(
        private string $scheme,
        private string $host,
        private string $path,
        private string $secret,
        /** @var array<string, mixed> $query */
        private array $query
    ) {
    }

    public function getScheme(): string
    {
        return $this->scheme;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getSecret(): string
    {
        return $this->secret;
    }

    /**
     * @return array<string, mixed>
     */
    public function getQuery(): array
    {
        return $this->query;
    }

    public static function fromString(string $uri): self
    {
        $parsed_url = parse_url($uri);
        Assertion::isArray($parsed_url, 'Not a valid OTP provisioning URI');
        foreach (['scheme', 'host', 'path', 'query'] as $key) {
            Assertion::keyExists($parsed_url, $key, 'Not a valid OTP provisioning URI');
            Assertion::string($parsed_url[$key], 'Not a valid OTP provisioning URI');
        }
        $scheme = $parsed_url['scheme'];
        Assertion::eq('otpauth', $scheme, 'Not a valid OTP provisioning URI');
        $host = $parsed_url['host'];
        $path = $parsed_url['path'];
        $query = $parsed_url['query'];
        $parsedQuery = [];
        parse_str($query, $parsedQuery);
        Assertion::keyExists($parsedQuery, 'secret', 'Not a valid OTP provisioning URI');
        $secret = $parsedQuery['secret'];
        unset($parsedQuery['secret']);

        return new self($scheme, $host, $path, $secret, $parsedQuery);
    }
}
