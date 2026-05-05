---
name: test-and-fix
description: Run full test suite, fix PHP errors, lint code
---

# Testing and Fixing it

steps:
- run: php -dxdebug.mode=coverage ./vendor/bin/pest -c ./phpunit.xml --coverage
- fix: all PHP errors in changed files
- run: vendor/bin/phpstan analyse src
- run: vendor/bin/pint src
- summarize: what broke, what was fixed, remaining issues
