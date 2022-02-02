########################
#         CI/CD        #
########################

ci-cs: vendor ## Check all files using defined rules (CI/CD)
	vendor/bin/ecs check

ci-st: vendor ## Run static analyse (CI/CD)
	vendor/bin/phpstan analyse --error-format=checkstyle | cs2pr

ci-rector: vendor ## Check all files using Rector (CI/CD)
	vendor/bin/rector process --ansi --dry-run

ci-mu: vendor ## Mutation tests (CI/CD)
	vendor/bin/infection --logger-github --git-diff-filter=AM -s --threads=$(nproc) --min-msi=70 --min-covered-msi=50 --test-framework-options="--exclude-group=Performance"

########################
#      Everyday        #
########################

all: vendor ## Run all tests
	vendor/bin/phpunit --color

tu: vendor ## Run only unit tests
	vendor/bin/phpunit --color tests/Unit

ti: vendor ## Run only integration tests
	vendor/bin/phpunit --color tests/Integration

tf: vendor ## Run only functional tests
	vendor/bin/phpunit --color tests/Functional

st: vendor ## Run static analyse
	vendor/bin/phpstan analyse


########################
#      Every PR        #
########################

cs: vendor ## Fix all files using defined rules
	vendor/bin/ecs check --fix

rector: vendor ## Check all files using Rector
	vendor/bin/rector process


########################
#        Others        #
########################

twig-lint: vendor ## All Twig template checks
	bin/console lint:twig templates/

mu: vendor ## Mutation tests
	vendor/bin/infection -s --threads=$(nproc) --min-msi=70 --min-covered-msi=50 --test-framework-options="--exclude-group=Performance"

db: vendor ## Create the database (should only be used in local env
	bin/console doctrine:database:drop --env=test --force
	bin/console doctrine:database:create --env=test
	bin/console doctrine:schema:create --env=test

clean: vendor ## Cleanup the var folder
	rm -rf var

cc: vendor ## Show test coverage rates (HTML)
	vendor/bin/phpunit --coverage-html ./build

vendor: composer.json composer.lock
	composer validate
	composer install


########################
#      Default         #
########################

.DEFAULT_GOAL := help
help:
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'
.PHONY: help
