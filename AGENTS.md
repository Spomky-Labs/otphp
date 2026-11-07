# AGENTS.md

This file provides guidance to AI coding agents when working with code in this repository.

## Project

`spomky-labs/otphp` is a PHP 8.1+ library implementing HOTP (RFC 4226) and TOTP (RFC 6238), compatible with Google Authenticator. It is published on Packagist and has no runtime dependencies beyond `paragonie/constant_time_encoding`, `psr/clock`, and `symfony/deprecation-contracts`.

## Task runner (Castor)

All QA tasks are defined in `castor.php` and dispatched through a dockerized `ghcr.io/spomky-labs/phpqa:<PHP_VERSION>` image (auto-pulled). They run natively if Docker is unavailable or already inside a container. Override the PHP version with `PHP_VERSION=8.4 castor ...`.

```bash
castor phpunit              # PHPUnit with coverage (config: .ci-tools/phpunit.xml.dist)
castor phpunit -- --filter TOTPTest::testGenerateOTPAt   # Run a single test (args after `--`)
castor phpstan              # PHPStan at max level (config: .ci-tools/phpstan.neon)
castor phpstan-baseline     # Regenerate .ci-tools/phpstan-baseline.neon
castor ecs                  # Check coding standards
castor ecs-fix              # Auto-fix coding standards
castor rector               # Rector dry-run
castor rector-fix           # Apply Rector changes
castor deptrac              # Verify layer dependencies (.ci-tools/deptrac.yaml)
castor lint                 # parallel-lint over src/ and tests/
castor infect               # Mutation testing (Infection)
castor check-licenses       # Validate dependency licenses
castor prepare-pr           # ecs-fix + rector-fix + phpstan-baseline + deptrac + lint
```

CI (`.github/workflows/ci.yml`) runs the test matrix across PHP 8.2, 8.3, 8.4 (and 8.5 experimentally), plus a `--prefer-lowest` job on 8.2. Mutation testing runs only on `*.x` branch pushes.

## Architecture

The public surface is tiny: `OTPInterface` / `HOTPInterface` / `TOTPInterface` / `FactoryInterface`, with concrete `OTP` (abstract), `HOTP`, `TOTP`, and `Factory` classes. All live in the `OTPHP\` namespace under `src/` (PSR-4).

- **`OTP` (abstract)** holds shared state in a `$parameters` array, indexed by string keys (`secret`, `algorithm`, `digits`, `label`, `issuer`, plus subclass-specific `period`/`epoch`/`counter`). `setParameter()` dispatches to a per-key validator returned by `getParameterMap()`; subclasses extend the map via `[...parent::getParameterMap(), ...]`. The actual OTP arithmetic (HMAC truncation per RFC 4226 §5.3) lives in `OTP::generateOTP()`.
- **`TOTP`** holds a `Psr\Clock\ClockInterface` injected via constructor. Passing `null` triggers a deprecation and falls back to `InternalClock` — **the clock becomes mandatory in v12.0** (`UPGRADE_v11-v12.md`). Always thread an explicit clock through new code paths.
- **`HOTP`** is the only class **not** marked `@readonly`: its `verify()` intentionally mutates the counter on success (via `updateCounter()`) to prevent OTP reuse. Do not change this without considering the security implication.
- **Mutable vs immutable API:** every setter has both `setX()` (mutating, deprecated since v11.4) and `withX()` (returns a clone). New code should prefer `with*`. Both must remain functional until v12.
- **`Factory`** parses `otpauth://` provisioning URIs into OTP objects using the internal `Url` value object. It enforces Google Authenticator's label rules (issuer-in-label vs issuer-as-query-parameter precedence).
- **Exception hierarchy:** all library-thrown exceptions implement the `OTPHP\Exception\OTPExceptionInterface` marker and extend a stdlib type (`InvalidArgumentException` or `RuntimeException`) for backwards compatibility. New error paths should throw one of the existing typed exceptions (`InvalidParameterException`, `InvalidLabelException`, `InvalidProvisioningUriException`, `ParameterNotFoundException`, `SecretDecodingException`) rather than raw stdlib exceptions — see `doc/Exceptions.md`.

### Architectural constraint (enforced by Deptrac)

Code in `OTPHP\` may only depend on `ParagonIE\` and `Psr\Clock\`. Pulling in any other vendor namespace from `src/` will fail CI — add a new layer to `.ci-tools/deptrac.yaml` if a dependency is genuinely required.

### Label validation

The non-trivial logic in `OTP::validateLabel()` implements the Google Authenticator label grammar: a label is either a bare `accountname`, or `issuer ":" *"%20" accountname` where neither side may contain its own colon. Both literal `:` and URL-encoded `%3A`/`%3a` count as separators. Bugs in label handling should be traced through this method and `buildProvisioningUriLabel()`.

## Repository conventions

- `declare(strict_types=1);` everywhere; PSR-12 + extensive PHPStan/ECS/Rector rule sets (see `.ci-tools/ecs.php` and `.ci-tools/phpstan.neon` — phpstan level `max` with bleeding-edge rules and several third-party rule sets).
- Branch naming uses `<major>.<minor>.x` (current: `11.4.x`). The active development branch is also the PR base.
- PHPDoc types use `non-empty-string`, `positive-int`, and `0|positive-int` extensively; preserve these when editing — PHPStan will catch regressions.
- `tests/` uses the `OTPHP\Test\` namespace and PHPUnit 11.
