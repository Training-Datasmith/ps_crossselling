#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
NETWORK="ps-crossselling-test"
MYSQL_IMAGE="mysql@sha256:4bc6bc963e6d8443453676cae56536f4b8156d78bae03c0145cbe47c2aad73bb"
PHP_IMAGE_TAG="ps-crossselling-php56"
PHAR_HOST="/tmp/phpunit-5.7.27.phar"
MYSQL_CONTAINER="ps-crossselling-mysql"
DEADLINE_SECONDS=180

export PS_TEST_DB_DSN="mysql:host=127.0.0.1;dbname=ps_crossselling_test"
export PS_TEST_DB_USER="root"
export PS_TEST_DB_PASSWORD="ps_crossselling"

if ! command -v docker >/dev/null 2>&1; then
  echo "docker is required" >&2
  exit 1
fi

"${ROOT}/tests/verify-phpunit.sh"

docker network inspect "$NETWORK" >/dev/null 2>&1 || docker network create "$NETWORK"
docker rm -f "$MYSQL_CONTAINER" >/dev/null 2>&1 || true

docker run -d --name "$MYSQL_CONTAINER" --network "$NETWORK" --network-alias mysql \
  -e MYSQL_ROOT_PASSWORD="$PS_TEST_DB_PASSWORD" \
  -e MYSQL_DATABASE=ps_crossselling_test \
  "$MYSQL_IMAGE"

docker build -t "$PHP_IMAGE_TAG" -f "${ROOT}/tests/docker/Dockerfile.php56" "${ROOT}/tests/docker"

end=$(( $(date +%s) + DEADLINE_SECONDS ))
ready=0
while [ "$(date +%s)" -lt "$end" ]; do
  if docker run --rm --network "container:${MYSQL_CONTAINER}" "$PHP_IMAGE_TAG" php -r "
    try {
      new PDO('mysql:host=127.0.0.1;dbname=ps_crossselling_test', '${PS_TEST_DB_USER}', '${PS_TEST_DB_PASSWORD}');
      exit(0);
    } catch (Exception \$e) {
      exit(1);
    }
  "; then
    ready=1
    break
  fi
  sleep 2
done

if [ "$ready" -ne 1 ]; then
  echo "MySQL did not become ready within ${DEADLINE_SECONDS}s" >&2
  docker logs "$MYSQL_CONTAINER" || true
  exit 1
fi

run_phpunit() {
  local version="$1"
  local assembler="$2"
  local config="$3"
  if [[ "$config" == "${ROOT}/tests/"* ]]; then
    config="tests/$(basename "$config")"
  elif [[ "$config" != */* ]]; then
    config="tests/${config}"
  fi
  docker run --rm --network "container:${MYSQL_CONTAINER}" \
    -v "${ROOT}:/src" \
    -v "${PHAR_HOST}:/tmp/phpunit-5.7.27.phar:ro" \
    -w /src \
    -e PS_TEST_DB_DSN \
    -e PS_TEST_DB_USER \
    -e PS_TEST_DB_PASSWORD \
    -e "PS_TEST_VERSION=${version}" \
    -e "PS_TEST_ASSEMBLER=${assembler}" \
    "$PHP_IMAGE_TAG" \
    php /tmp/phpunit-5.7.27.phar --configuration "${config}"
}

default_config="phpunit.xml"
shuffle_config="${ROOT}/tests/phpunit-shuffle.xml"
test_files=(
  PsCrosssellingConstructorTest.php
  PsCrosssellingInstallTest.php
  PsCrosssellingGetContentTest.php
  PsCrosssellingConfigFormTest.php
  PsCrosssellingCacheAndHookTest.php
  PsCrosssellingWidgetTest.php
  PsCrosssellingOrderProductsTest.php
  PsCrosssellingPresenterTest.php
)

write_shuffle_config() {
  local seed="$1"
  docker run --rm "$PHP_IMAGE_TAG" php -r "
    srand(${seed});
    \$files = array(
      'PsCrosssellingConstructorTest.php',
      'PsCrosssellingInstallTest.php',
      'PsCrosssellingGetContentTest.php',
      'PsCrosssellingConfigFormTest.php',
      'PsCrosssellingCacheAndHookTest.php',
      'PsCrosssellingWidgetTest.php',
      'PsCrosssellingOrderProductsTest.php',
      'PsCrosssellingPresenterTest.php'
    );
    shuffle(\$files);
    foreach (\$files as \$f) { echo \$f, PHP_EOL; }
  " > "${ROOT}/tests/.shuffled-tests.txt"

  {
    echo '<?xml version="1.0" encoding="UTF-8"?>'
    echo '<phpunit bootstrap="bootstrap.php" colors="true" processIsolation="true" backupGlobals="false">'
    echo '  <testsuites><testsuite name="shuffled">'
    while read -r file; do
      echo "    <file>${file}</file>"
    done < "${ROOT}/tests/.shuffled-tests.txt"
    echo '  </testsuite></testsuites>'
    echo '</phpunit>'
  } > "${shuffle_config}"
}

modes=(
  "1.7.5.1:bulk"
  "1.7.4.4:single"
)

for mode in "${modes[@]}"; do
  version="${mode%%:*}"
  assembler="${mode##*:}"
  run_phpunit "$version" "$assembler" "$default_config"
  run_phpunit "$version" "$assembler" "$default_config"
  write_shuffle_config 20261008
  run_phpunit "$version" "$assembler" "$shuffle_config"
  write_shuffle_config 20261008
  run_phpunit "$version" "$assembler" "$shuffle_config"
done

"${ROOT}/tests/mutation-check.sh"

docker rm -f "$MYSQL_CONTAINER" >/dev/null 2>&1 || true
