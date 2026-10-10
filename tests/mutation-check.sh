#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
MODULE="${ROOT}/ps_crossselling.php"
BACKUP="${ROOT}/.mutation-backup.php"
NETWORK="ps-crossselling-test"
MYSQL_CONTAINER="ps-crossselling-mysql"
PHP_IMAGE_TAG="ps-crossselling-php56"
PHAR_HOST="/tmp/phpunit-5.7.27.phar"

export PS_TEST_DB_DSN="mysql:host=127.0.0.1;dbname=ps_crossselling_test"
export PS_TEST_DB_USER="root"
export PS_TEST_DB_PASSWORD="ps_crossselling"

cp "$MODULE" "$BACKUP"

run_expect_fail() {
  local test="$1"
  set +e
  docker run --rm --network "container:${MYSQL_CONTAINER}" \
    -v "${ROOT}:/src" \
    -v "${PHAR_HOST}:/tmp/phpunit-5.7.27.phar:ro" \
    -w /src \
    -e PS_TEST_DB_DSN -e PS_TEST_DB_USER -e PS_TEST_DB_PASSWORD \
    -e PS_TEST_VERSION=1.7.5.1 -e PS_TEST_ASSEMBLER=bulk \
    "$PHP_IMAGE_TAG" \
    php /tmp/phpunit-5.7.27.phar --configuration tests/phpunit.xml --filter "$test" >/tmp/mutation.out 2>&1
  local code=$?
  set -e
  if [ "$code" -eq 0 ]; then
    echo "Mutation check failed: ${test} still passed" >&2
    cat /tmp/mutation.out
    cp "$BACKUP" "$MODULE"
    exit 1
  fi
}

mutate() {
  cp "$BACKUP" "$MODULE"
  "$@"
}

mutate sed -i "s/Tools::getValue('CROSSSELLING_DISPLAY_PRICE')/Tools::getValue('displayPrice')/g" "$MODULE"
run_expect_fail "testRejectsDisplayPriceOutsideZeroAndOne"

mutate sed -i 's/(int) \$product_nbr < 1/0 === (int) $product_nbr/' "$MODULE"
run_expect_fail "testRejectsNegativeProductCount"

mutate perl -0pi -e 's/\n        \$productIds = array_map\('\''intval'\'', \$productIds\);\n//g' "$MODULE"
run_expect_fail "testDirtyProductIdIsSanitizedInCacheKeyAndQuery"

mutate sed -i 's/AND od.product_id NOT IN/AND od.product_id IN/' "$MODULE"
run_expect_fail "testExcludesSourceProductAndInvalidOrders"

mutate sed -i 's/AND product_shop.active = 1/AND product_shop.active = 0/' "$MODULE"
run_expect_fail "testFiltersVisibilityAndActiveFlag"

cp "$BACKUP" "$MODULE"
rm -f "$BACKUP"
echo "Mutation checks passed"
