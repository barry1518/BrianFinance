<?php

use Techbanx\Project;

/**

 * Define directory

 */

define('BASE_DIR', dirname(__DIR__));

define('DIR', [

    'application' => BASE_DIR . '/App',

    'log' => BASE_DIR . '/Log',

    'service' => BASE_DIR . '/App/Components',

]);

/**

 * Activate log

 */

ini_set('display_errors', 0);

ini_set('error_reporting', -1);

ini_set('log_errors', 1);

ini_set('error_log', DIR['log'].'/php-error.log');

/*

 * Bootstrap application

 */

require_once DIR['application'].'/bootstrap.php';