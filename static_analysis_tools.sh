#!/usr/bin/bash
pretty-php --space=2 .
#php-cs-fixer fix .
#phpcs .
#phpcbf .
phpmd . text cleancode
phpcpd .
#phpstan analyse . --level=7

