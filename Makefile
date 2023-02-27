.PHONY: help
.DEFAULT_GOAL = help

dc = docker-compose
composer = $(dc) exec php memory_limit=1 /usr/local/bin/composer

## —— Docker 🐳  ———————————————————————————————————————————————————————————————
.PHONY: install
install:	## Installation du projet
	$(dc) up -d
	$(dc) exec php bash -c 'composer install'
	$(dc) exec php bash -c 'yarn install'

.PHONY: build
build:	## Lancer les containers docker au start du projet
	$(dc) up -d
	$(dc) exec php bash -c 'composer install'
	$(dc) exec php bash -c 'yarn install && yarn run build'
# make db-install
# $(dc) exec php bash -c 'bin/console d:m:m && bin/console d:f:l'

.PHONY: up
up:	## start container
	$(dc) up -d

.PHONY: down
down:	## delete container
	$(dc) down --remove-orphans
	rm -rf var/uploads/ var/scraping/ var/screenshots/ var/error-screenshots/

.PHONY: watch
watch:	## watch du projet
	$(dc) up -d
	$(dc) exec php bash -c 'yarn run watch'

.PHONY: bash
bash:	## connection container php
	$(dc) exec php bash

.PHONY: delete
delete:	## delete container
	make down
	$(dc) kill
	$(dc) rm

.PHONY: db-install
db-install: ## Install database
	make db-drop
	make db-create
	make db-migration

.PHONY: db-create
db-create: ## Create database
	$(dc) up -d
	$(dc) exec php bash -c 'bin/console doctrine:database:create --if-not-exists || true'
	$(dc) exec php bash -c 'php bin/console --env=test doctrine:database:create --if-not-exists || true'

.PHONY: db-migration
db-migration: ## Load doctrine data fixtures
	$(dc) up -d
	$(dc) exec php bash -c 'bin/console doctrine:migrations:migrate -n'
	$(dc) exec php bash -c 'bin/console doctrine:fixtures:load -n --append'
	$(dc) exec php bash -c 'php bin/console --env=test doctrine:migrations:migrate -n'

.PHONY: db-drop
db-drop: ## Remove database
	$(dc) up -d
	$(dc) exec php bash -c 'bin/console doctrine:database:drop --force || true'
	$(dc) exec php bash -c 'bin/console  --env=test doctrine:database:drop --force || true'

.PHONY: reset-all
reset-all: ## Reset app
	make delete
	make install
	make db-install
	make watch

.PHONY: logs
logs: ## Show containers logs
	$(dc) logs -f --tail=0 | ccze -m ansi

## —— Others 🛠️️ ———————————————————————————————————————————————————————————————
help: ## listing command
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'
