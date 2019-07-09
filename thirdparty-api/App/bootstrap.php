<?php
use Phalcon\Di;
use Techbanx\Core;
/**
 * Welcome to Zephir Techbanx Framework
 */
 $params = [
     'env'              => 'dev',
     'debug'            => true,
     'verify_https'     => false,
     'verify_host'      => false,
     'verify_auth'      => false,
     'benchmark_log'    => true,
     'real_http_status' => true,
     'request_method'   => ['GET', 'POST', 'OPTIONS'],
     'redis_server'  => [
         'host'     => 'redis',
         'auth' 	=> 'Ping&Pong2017;',
		 'index' 	=> 14 //change this to use a different db : 0 - 15
     ]
 ];
 $di = new Di();
 $bootstrap = new Core($di, $params);
 $bootstrap->run();