<?php

use AlexeyPlodenko\PhpDebug\DebugFunctionsFactory;

require_once __DIR__ .'/vendor/autoload.php';

// starting output buffer, to be able to dump() anytime and anywhere, even after everything has rendered
ob_start();

// replaces current host env. with this. Uncomment to enable
//$host = 'www.example.com';

// replaces current path and query. Uncomment to enable
//$_SERVER['REQUEST_URI'] = '/en/article?id=123';

// put anything into this super global var. to output after execution will finish. Assign something during the runtime to it
//$GLOBALS['phpDebugDump'] = array();

if (!function_exists('http_build_url')) {
    require_once __DIR__ .'/src/http_build_url.php';
}

if (!function_exists('dump')) {
    /**
     * Output...
     *
     * @param mixed $var
     * @param array $config
     */
    function dump($var, array $config = array())
    {
        DebugFunctionsFactory::makeInstance()->dump($var, $config);
    }
}

if (!function_exists('dumpDiff')) {
    /**
     * Output...
     *
     * @param string|int|float $a
     * @param string|int|float $b
     */
    function dumpDiff($a, $b)
    {
        DebugFunctionsFactory::makeInstance()->dumpDiff($a, $b);
    }
}

if (!function_exists('dumpStacktrace')) {
    /**
     * Output...
     *
     * @param array $backtrace
     */
    function dumpStacktrace(array $backtrace = null)
    {
        DebugFunctionsFactory::makeInstance()->dumpStacktrace($backtrace);
    }
}

//// switch env. to the one we want
//if (isset($host) && $host) {
//    $_SERVER['HTTP_HOST'] = $_SERVER['SERVER_NAME'] = $host;
//    if (isset($_SERVER['HTTP_REFERER'])) {
//        $referrer = parse_url($_SERVER['HTTP_REFERER']);
//        if (isset($referrer['host'])) {
//            $referrer['host']        = $host;
//            $_SERVER['HTTP_REFERER'] = http_build_url(null, $referrer);
//        }
//    }
//}
