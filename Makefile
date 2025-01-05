.DEFAULT_GOAL := help

help:
	@echo "Available commands:"
	@echo "  - run-tests:          Run tests"
	@echo "  - run-infection:      Runs Infection mutation testing"
	@echo "  - coverage-text:      Runs coverage text"
	@echo "  - coverage-html:      Runs coverage html"
	@echo "  - all:                Runs CS-Fixer, CS-Checker, Static Analyser and Tests"
	@echo "  - shell:              Run shell"

run-tests:
	@echo "Running tests"
	docker compose run --rm --no-deps php composer test

run-infection:
	@echo "Running infection mutation testing"
	docker compose run --rm --no-deps php composer infection

coverage-text:
	@echo "Running coverage text"
	docker compose run --rm --no-deps php composer test-coverage

coverage-html:
	@echo "Running coverage HTML"
	docker compose run --rm --no-deps php composer test-coverage-html

all:
	@echo "Running CS-Checker, Static Analyser and Tests"
	docker compose run --rm --no-deps -T php composer all

shell:
	@echo "Running shell"
	docker compose run --rm --no-deps --service-ports --entrypoint /bin/bash php
