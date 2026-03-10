.PHONY: build up down restart logs shell migrate fresh test

build:
	docker compose build

up:
	docker compose up -d

down:
	docker compose down

restart:
	docker compose restart

logs:
	docker compose logs -f

shell:
	docker compose exec php sh

shell-worker:
	docker compose exec php-worker sh

migrate:
	docker compose exec php php artisan migrate --force

fresh:
	docker compose exec php php artisan migrate:fresh --force

install:
	cp -n .env.example .env 2>/dev/null || true
	docker compose up -d
	docker compose exec php composer install --no-interaction
	docker compose exec php php artisan key:generate --no-interaction
	docker compose exec php php artisan storage:link
	$(MAKE) migrate

test:
	docker compose exec php php artisan test
