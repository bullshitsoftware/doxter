.PHONY: serve-debug
serve-debug:
	bin/debug -S localhost:8000 -t public

.PHONY: prepare-tests tests cover
prepare-tests:
	rm -rf var/test_data.db
	bin/console --env=test doctrine:database:create 
	bin/console --env=test doctrine:migrations:migrate --no-interaction
	bin/console --env=test doctrine:fixtures:load --no-interaction

tests: prepare-tests
	bin/phpunit

cover: prepare-tests
	XDEBUG_MODE=coverage bin/phpunit --coverage-clover cover.xml

.PHONY: build-php
build-php:
	docker buildx build --push -f build/php/Dockerfile \
		-t ghcr.io/bullshitsoftware/doxter-php:${VER} -t ghcr.io/bullshitsoftware/doxter-php:latest .

.PHONY: build-devcontainer
build-devcontainer:
	docker buildx build --push -f build/devcontainer/Dockerfile \
		-t ghcr.io/bullshitsoftware/doxter-devcontainer:${VER} -t ghcr.io/bullshitsoftware/doxter-devcontainer:latest .
