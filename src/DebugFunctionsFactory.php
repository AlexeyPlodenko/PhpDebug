<?php
namespace AlexeyPlodenko\PhpDebug;

use AlexeyPlodenko\PhpDebug\Client\Cli\DebugFunctions as CliDebugFunctions;
use AlexeyPlodenko\PhpDebug\Client\Html\DebugFunctions as HtmlDebugFunctions;

class DebugFunctionsFactory
{
    /**
     * @var CliDebugFunctions|HtmlDebugFunctions
     */
    protected static $instance;

    /**
     * @return CliDebugFunctions|HtmlDebugFunctions
     */
    public static function makeInstance()
    {
        if (!isset(static::$instance)) {
            $client = (php_sapi_name() === 'cli' ? 'Cli' : 'Html');
            $className = "\\AlexeyPlodenko\\PhpDebug\\Client\\$client\\DebugFunctions";
            static::$instance = new $className();
        }

        return static::$instance;
    }
}
