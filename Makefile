.PHONY: tests
tests:
	rm -rf var/test_data.db
	bin/console --env=test doctrine:database:create 
	bin/console --env=test doctrine:schema:create 
	bin/console --env=test doctrine:fixtures:load --no-interaction
	bin/phpunit
