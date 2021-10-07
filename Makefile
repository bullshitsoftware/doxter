.PHONY: tests
tests:
	rm -rf var/test_data.db
	bin/console --env=test doctrine:database:create 
	bin/console --env=test doctrine:schema:create 
	bin/console --env=test doctrine:fixtures:load --no-interaction
	bin/phpunit

.PHONY: build-php
build-php:
	docker buildx build --push -f build/php/Dockerfile \
		-t ghcr.io/bullshitsoftware/doxter-php:${VER} -t ghcr.io/bullshitsoftware/doxter-php:latest .

.PHONY: build-devcontainer
build-devcontainer:
	docker buildx build --push -f build/devcontainer/Dockerfile \
		-t ghcr.io/bullshitsoftware/doxter-devcontainer:${VER} -t ghcr.io/bullshitsoftware/doxter-devcontainer:latest .
