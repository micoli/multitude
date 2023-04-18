test-unit:
	vendor/bin/phpunit

test-static:
	vendor/bin/phpstan
	vendor/bin/psalm
	XDEBUG_MODE=off PHAN_DISABLE_XDEBUG_WARN=1 vendor/bin/phan --allow-polyfill-parser

test-coding-standard:
	vendor/bin/php-cs-fixer fix --verbose

update-doc:
	php ./update-doc.php README.md

tests-all: test-coding-standard test-unit test-static update-doc
