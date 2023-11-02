########################
#      Everyday        #
########################

.PHONY: mu
mu: vendor ## Mutation tests
	XDEBUG_MODE=coverage vendor/bin/infection -s --threads=$$(nproc) --min-msi=30 --min-covered-msi=50

.PHONY: tests
tests: vendor ## Run all tests
	vendor/bin/phpunit  --color

.PHONY: cc
cc: vendor ## Show test coverage rates (HTML)
	vendor/bin/phpunit --coverage-html ./build

.PHONY: cs
cs: vendor ## Fix all files using defined ECS rules
	vendor/bin/ecs check --fix

.PHONY: tu
tu: vendor ## Run only unit tests
	vendor/bin/phpunit --color --group Unit

.PHONY: ti
ti: vendor ## Run only integration tests
	vendor/bin/phpunit --color --group Integration

.PHONY: tf
tf: vendor ## Run only functional tests
	vendor/bin/phpunit --color --group Functional

.PHONY: st
st: vendor ## Run static analyse
	XDEBUG_MODE=off vendor/bin/phpstan analyse


########################
#         CI/CD        #
########################

.PHONY: ci-mu
ci-mu: vendor ## Mutation tests (for CI/CD only)
	XDEBUG_MODE=coverage vendor/bin/infection --logger-github -s --threads=$$(nproc) --min-msi=30 --min-covered-msi=50

.PHONY: ci-cc
ci-cc: vendor ## Show test coverage rates (for CI/CD only)
	XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-text

.PHONY: ci-cs
ci-cs: vendor ## Check all files using defined ECS rules (for CI/CD only)
	XDEBUG_MODE=off vendor/bin/ecs check

########################
#        Others        #
########################

.PHONY: rector
rector: vendor ## Check all files using Rector
	XDEBUG_MODE=off vendor/bin/rector process --ansi --dry-run --xdebug

vendor: composer.json
	composer validate
	composer install

.DEFAULT_GOAL := help
help:
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'
.PHONY: help
