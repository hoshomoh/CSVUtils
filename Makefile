UID ?= $(shell id -u)
GID ?= $(shell id -g)

help:
	@echo "\e[32m Usage make [target] "
	@echo
	@echo "\e[1m targets:"
	@egrep '^(.+)\:\ ##\ (.+)' ${MAKEFILE_LIST} | column -t -c 2 -s ':#'

clean: ## Clean everything
clean: clean-docker clean-logs clean-cache clean-dependencies
.PHONY: clean

clean-docker: ## Remove images, volumes, containers
	# Not implemented
.PHONY: clean-docker

clean-logs: ## Clean all log files
	# Not implemented
.PHONY: clean-logs

clean-cache: ## Clean local caches
	# Not implemented
.PHONY: clean-cache

clean-dependencies: ## Clean dev dependencies
	# Not implemented
.PHONY: clean-dependencies

build-php7: ## Build PHP7 container
	# Hint: force a rebuild by passing --no-cache
	@UID=$(UID) GID=$(GID) docker-compose build --no-cache php7
.PHONY: install-web

stop: ## Stop running containers
	@UID=$(UID) GID=$(GID) docker-compose stop
.PHONY: stop

shell-php7: ## Start an interactive shell session for PHP7 container
	# Hint: adjust UID and GID to 0 if you want to use the shell as root
	@UID=$(UID) GID=$(GID) docker-compose run --rm -w /var/www/html -e SHELL_VERBOSITY=1 php7 bash
.PHONY: shell

test: ## Run all unit tests
test: php7-tests
.PHONY: test

test-php7: ## Run php unit tests
	# Not implemented
.PHONY: php7-tests

watch-logs: ## Open a tail on all the logs
	@UID=$(UID) GID=$(GID) docker-compose logs -f -t
.PHONY: watch-logs

.DEFAULT_GOAL := help
