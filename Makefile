##
## OTPHP
## -----
##

install: ## Install the dependencies (dev included)
install: vendor

##
## Tests
## -----
##

test: ## Run unit and functional test
test: vendor
	vendor/bin/phpunit

.PHONY: install tests help
.DEFAULT_GOAL := help
help:
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'



composer.lock: composer.json
	composer update

vendor: composer.lock
	composer install
