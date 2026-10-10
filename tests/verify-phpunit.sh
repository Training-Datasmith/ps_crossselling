#!/usr/bin/env bash
set -euo pipefail

PHAR_URL="https://phar.phpunit.de/phpunit-5.7.27.phar"
ASC_URL="https://phar.phpunit.de/phpunit-5.7.27.phar.asc"
PHAR_PATH="/tmp/phpunit-5.7.27.phar"
ASC_PATH="/tmp/phpunit-5.7.27.phar.asc"
EXPECTED_SHA="f0300a13f2653bee10b0ce24dbc8d94e65dc9be8966d822b9e455b6b294be16e"
KEY_FPR="D8406D0D82947747293778314AA394086372C20A"

curl -fsSL "$PHAR_URL" -o "$PHAR_PATH"
curl -fsSL "$ASC_URL" -o "$ASC_PATH"

echo "${EXPECTED_SHA}  ${PHAR_PATH}" | sha256sum -c -

gpg --batch --keyserver hkps://keyserver.ubuntu.com --recv-keys "$KEY_FPR"
gpg --batch --verify "$ASC_PATH" "$PHAR_PATH"

echo "PHPUnit PHAR verified at ${PHAR_PATH}"
