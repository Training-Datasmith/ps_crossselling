# PHP floor for module tests

`composer.json` requires PHP `>=5.4`, but the official `php:5.4-cli` image manifest is no longer pullable in current Docker environments.

The suite runs on the known-good floor image:

`php@sha256:6ce95208609dc66df163ab936c970b3b34cd901b85c747102c5999f08ade9143` (PHP 5.6.40), which satisfies PrestaShop 1.7.2+ and PHPUnit 5.7.27.

No host PHP is used. PHPUnit is executed from a verified PHAR mounted at `/tmp/phpunit-5.7.27.phar` inside the container.
