#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
MODULE="${ROOT}/ps_crossselling.php"
BACKUP="$(mktemp)"
MYSQL_CONTAINER="ps-crossselling-mysql"
PHP_IMAGE_TAG="ps-crossselling-php56"
PHAR_HOST="/tmp/phpunit-5.7.27.phar"

export PS_TEST_DB_DSN="mysql:host=127.0.0.1;dbname=ps_crossselling_test"
export PS_TEST_DB_USER="root"
export PS_TEST_DB_PASSWORD="ps_crossselling"

restore_module() {
  if [ -f "$BACKUP" ]; then
    cp "$BACKUP" "$MODULE"
  fi
}

trap restore_module EXIT

cp "$MODULE" "$BACKUP"

run_phpunit_filter() {
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
  return "$code"
}

run_expect_pass() {
  local test="$1"
  cp "$BACKUP" "$MODULE"
  if ! run_phpunit_filter "$test"; then
    echo "Mutation pre-check failed: ${test} did not pass on clean module" >&2
    cat /tmp/mutation.out
    exit 1
  fi
}

run_expect_fail() {
  local test="$1"
  if run_phpunit_filter "$test"; then
    echo "Mutation check failed: ${test} still passed" >&2
    cat /tmp/mutation.out
    exit 1
  fi
}

mutate() {
  cp "$BACKUP" "$MODULE"
  "$@"
}

run_expect_pass "testRejectsDisplayPriceOutsideZeroAndOne"
mutate sed -i "s/0 != Tools::getValue('CROSSSELLING_DISPLAY_PRICE')/0 != Tools::getValue('displayPrice')/" "$MODULE"
run_expect_fail "testRejectsDisplayPriceOutsideZeroAndOne"

run_expect_pass "testRejectsNegativeProductCount"
mutate sed -i 's/(int) \$product_nbr < 1/0 === (int) $product_nbr/' "$MODULE"
run_expect_fail "testRejectsNegativeProductCount"

run_expect_pass "testDirtyProductIdIsSanitizedInCacheKeyAndQuery"
mutate perl -0pi -e 's/\n        \$productIds = array_map\('\''intval'\'', \$productIds\);\n\n        return array_unique/\n\n        return array_unique/s' "$MODULE"
run_expect_fail "testDirtyProductIdIsSanitizedInCacheKeyAndQuery"

run_expect_pass "testGetOrderProductsCastsIdsBeforeQuery"
mutate perl -0pi -e 's/\n        \$productIds = array_map\('\''intval'\'', \$productIds\);\n\n        \$q_orders/\n\n        \$q_orders/s' "$MODULE"
run_expect_fail "testGetOrderProductsCastsIdsBeforeQuery"

run_expect_pass "testExcludesSourceProductAndInvalidOrders"
mutate sed -i 's/AND od.product_id NOT IN/AND od.product_id IN/' "$MODULE"
run_expect_fail "testExcludesSourceProductAndInvalidOrders"

run_expect_pass "testFiltersVisibilityAndActiveFlag"
mutate sed -i 's/AND product_shop.active = 1/AND product_shop.active = 0/' "$MODULE"
run_expect_fail "testFiltersVisibilityAndActiveFlag"

run_expect_pass "testInstallWritesDefaultsAndRegistersHooks"
mutate sed -i '/Configuration::updateValue('\''CROSSSELLING_NBR'\'', 8)/d' "$MODULE"
run_expect_fail "testInstallWritesDefaultsAndRegistersHooks"

run_expect_pass "testGetContentWithoutSubmitLeavesConfigUntouched"
mutate sed -i 's/if (Tools::isSubmit('\''submitCross'\'')) {/if (true) {/' "$MODULE"
run_expect_fail "testGetContentWithoutSubmitLeavesConfigUntouched"

run_expect_pass "testDbQueriesUseSlaveFlagFalse"
mutate sed -i 's/Db::getInstance((bool) _PS_USE_SQL_SLAVE_)/Db::getInstance()/' "$MODULE"
run_expect_fail "testDbQueriesUseSlaveFlagFalse"

cp "$BACKUP" "$MODULE"
rm -f "$BACKUP"
trap - EXIT
echo "Mutation checks passed"
