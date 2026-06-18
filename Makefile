.DEFAULT_GOAL := help

help:
	@echo "Available commands:"
	@echo "  - run-tests:          Run tests"
	@echo "  - run-infection:      Runs Infection mutation testing"
	@echo "  - coverage-text:      Runs coverage text"
	@echo "  - coverage-html:      Runs coverage html"
	@echo "  - all:                Runs CS-Fixer, CS-Checker, Static Analyser and Tests"
	@echo "  - shell:              Run shell"

ensure-up:
	@docker compose ps php --status running | grep -q php || docker compose up -d php

install: ensure-up
	docker compose exec php composer install --no-interaction

run-tests: install
	@echo "Running tests"
	docker compose exec php composer test

run-infection: ensure-up
	@echo "Running infection mutation testing"
	docker compose exec php composer infection

coverage-text: ensure-up
	@echo "Running coverage text"
	docker compose exec php composer test-coverage

coverage-html: ensure-up
	@echo "Running coverage HTML"
	docker compose exec php composer test-coverage-html

all: ensure-up
	@echo "Running CS-Checker, Static Analyser and Tests"
	docker compose exec -T php composer all

shell: ensure-up
	@echo "Running shell"
	docker compose exec php /bin/bash
