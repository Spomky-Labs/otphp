<?php

declare(strict_types=1);

namespace OTPHP;

use function array_key_exists;
use InvalidArgumentException;
use function is_string;

/**
 * @internal
 */
final class Url
{
    /**
     * @param non-empty-string $secret
     */
    public function __construct(
        private readonly string $scheme,
        private readonly string $host,
        private readonly string $path,
        private readonly string $secret,
        /** @var array<string, mixed> $query */
        private readonly array $query
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

    /**
     * @return non-empty-string
     */
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
        $parsed_url !== false || throw new InvalidArgumentException('Invalid URI.');
        foreach (['scheme', 'host', 'path', 'query'] as $key) {
            array_key_exists($key, $parsed_url) || throw new InvalidArgumentException(
                'Not a valid OTP provisioning URI'
            );
            is_string($parsed_url[$key]) || throw new InvalidArgumentException('Not a valid OTP provisioning URI');
        }
        $scheme = $parsed_url['scheme'] ?? null;
        $host = $parsed_url['host'] ?? null;
        $path = $parsed_url['path'] ?? null;
        $query = $parsed_url['query'] ?? null;
        $scheme === 'otpauth' || throw new InvalidArgumentException('Not a valid OTP provisioning URI');
        is_string($host) || throw new InvalidArgumentException('Invalid URI.');
        is_string($path) || throw new InvalidArgumentException('Invalid URI.');
        is_string($query) || throw new InvalidArgumentException('Invalid URI.');
        $parsedQuery = [];
        parse_str($query, $parsedQuery);
        array_key_exists('secret', $parsedQuery) || throw new InvalidArgumentException(
            'Not a valid OTP provisioning URI'
        );
        $secret = $parsedQuery['secret'];
        unset($parsedQuery['secret']);

        return new self($scheme, $host, $path, $secret, $parsedQuery);
    }
}
