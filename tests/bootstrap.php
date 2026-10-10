<?php

error_reporting(-1);

if (PHP_VERSION_ID >= 70000) {
    fwrite(STDERR, "Refusing to run module tests on PHP 7+; use the pinned PHP 5.6 container.\n");
    exit(1);
}

if (!getenv('PS_TEST_VERSION')) {
    putenv('PS_TEST_VERSION=1.7.5.1');
}
if (!getenv('PS_TEST_ASSEMBLER')) {
    putenv('PS_TEST_ASSEMBLER=bulk');
}

define('_PS_VERSION_', getenv('PS_TEST_VERSION'));
define('_DB_PREFIX_', 'ps_');
define('_PS_USE_SQL_SLAVE_', false);

require_once __DIR__ . '/stubs/prestashop_stubs.php';
require_once __DIR__ . '/stubs/prestashop_namespaced.php';
