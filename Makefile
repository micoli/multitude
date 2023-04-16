test-unit:
	vendor/bin/phpunit

test-static:
	vendor/bin/phpstan
	vendor/bin/psalm

test-coding-standard:
	vendor/bin/php-cs-fixer fix --verbose

update-doc:
	php ./update-doc.php README.md

tests-all: test-coding-standard test-unit test-static update-doc
