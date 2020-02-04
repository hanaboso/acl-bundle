.PHONY: init-dev test

DC=docker-compose
DE=docker-compose exec -T app
DEC=docker-compose exec -T  app composer
DM=docker-compose exec -T mongo
DMY=docker-compose exec -T mariadb

.env:
	sed -e "s/{DEV_UID}/$(shell id -u)/g" \
		-e "s/{DEV_GID}/$(shell id -u)/g" \
		-e "s/{SSH_AUTH}/$(shell if [ "$(shell uname)" = "Linux" ]; then echo "\/tmp\/.ssh-auth-sock"; else echo '\/tmp\/.nope'; fi)/g" \
		.env.dist >> .env; \

# Docker
docker-up-force: .env
	$(DC) pull
	$(DC) up -d --force-recreate --remove-orphans

docker-down-clean: .env
	$(DC) down -v

# Composer
composer-install:
	$(DE) composer install --no-suggest

composer-update:
	$(DE) composer update --no-suggest

composer-outdated:
	$(DE) composer outdated

# Console
clear-cache:
	$(DE) rm -rf var/log
	$(DE) php tests/testApp/bin/console cache:clear --env=test
	$(DE) php tests/testApp/bin/console cache:warmup --env=test

database-create:
	$(DMY) /bin/bash -c 'while ! mysql -uroot -proot <<< "DROP DATABASE IF EXISTS acl;" > /dev/null 2>&1; do sleep 1; done'
	$(DE) php tests/testApp/bin/console doctrine:database:drop --force || true --env=test
	$(DE) php tests/testApp/bin/console doctrine:database:create --env=test
	$(DE) php tests/testApp/bin/console doctrine:schema:create --env=test
	$(DM) /bin/bash -c "mongo <<< 'use acl;'" ; \
	for i in 1 2 3 4; do \
		$(DM) /bin/bash -c "mongo <<< 'use acl$$i;'" ; \
		$(DMY) /bin/bash -c "mysql -uroot -proot <<< 'DROP DATABASE IF EXISTS acl$$i;'" ; \
		$(DMY) /bin/bash -c "mysql -uroot -proot <<< 'CREATE DATABASE acl$$i CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;'" ; \
		$(DMY) /bin/bash -c "mysqldump -uroot -proot acl | mysql -uroot -proot acl$$i" ; \
	done

# App dev
init-dev: docker-up-force composer-install

phpcodesniffer:
	$(DE) ./vendor/bin/phpcs --standard=./ruleset.xml --colors -p src tests

phpstan:
	$(DE) ./vendor/bin/phpstan analyse -c ./phpstan.neon -l 8 src tests

phpunit:
	$(DE) ./vendor/bin/paratest -c ./vendor/hanaboso/php-check-utils/phpunit.xml.dist -p 4 --runner=WrapperRunner tests/Unit

phpintegration: database-create
	$(DE) ./vendor/bin/paratest -c ./vendor/hanaboso/php-check-utils/phpunit.xml.dist -p 4 --runner=WrapperRunner tests/Integration

phpcontroller:
	$(DE) ./vendor/bin/paratest -c ./vendor/hanaboso/php-check-utils/phpunit.xml.dist -p 4 --runner=WrapperRunner tests/Controller

phpcoverage:
	$(DE) ./vendor/bin/paratest -c ./vendor/hanaboso/php-check-utils/phpunit.xml.dist -p 4 --coverage-html var/coverage --whitelist src tests

phpcoverage-ci:
	$(DE) ./vendor/hanaboso/php-check-utils/bin/coverage.sh -p 4

test: docker-up-force composer-install fasttest

fasttest: clear-cache phpcodesniffer phpstan phpunit phpintegration phpcontroller phpcoverage-ci
