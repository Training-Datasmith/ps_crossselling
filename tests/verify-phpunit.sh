#!/usr/bin/env bash
set -euo pipefail

PHAR_URL="https://phar.phpunit.de/phpunit-5.7.27.phar"
ASC_URL="https://phar.phpunit.de/phpunit-5.7.27.phar.asc"
PHAR_PATH="/tmp/phpunit-5.7.27.phar"
ASC_PATH="/tmp/phpunit-5.7.27.phar.asc"
EXPECTED_SHA="f0300a13f2653bee10b0ce24dbc8d94e65dc9be8966d822b9e455b6b294be16e"
KEY_FPR="D8406D0D82947747293778314AA394086372C20A"
GPG_STATUS="$(mktemp)"

curl -fsSL "$PHAR_URL" -o "$PHAR_PATH"
curl -fsSL "$ASC_URL" -o "$ASC_PATH"

echo "${EXPECTED_SHA}  ${PHAR_PATH}" | sha256sum -c -

gpg --batch --keyserver hkps://keyserver.ubuntu.com --recv-keys "$KEY_FPR"
gpg --batch --status-fd 3 --verify "$ASC_PATH" "$PHAR_PATH" 3>"$GPG_STATUS"

if ! grep -q "^\\[GNUPG:\\] VALIDSIG ${KEY_FPR} " "$GPG_STATUS"; then
  echo "PHPUnit PHAR signature missing VALIDSIG for ${KEY_FPR}" >&2
  cat "$GPG_STATUS" >&2
  rm -f "$GPG_STATUS"
  exit 1
fi

rm -f "$GPG_STATUS"
echo "PHPUnit PHAR verified at ${PHAR_PATH}"
