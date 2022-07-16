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
	vendor/bin/phpunit --color tests

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

mu: vendor ## Mutation tests
	vendor/bin/infection -s --threads=$(nproc) --min-msi=70 --min-covered-msi=50 --test-framework-options="--exclude-group=Performance"

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
