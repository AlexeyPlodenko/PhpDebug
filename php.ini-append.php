<?php

use AlexeyPlodenko\PhpDebug\DebugFunctionsFactory;

if (isset($GLOBALS['phpDebugDump']) && $GLOBALS['phpDebugDump']) {
    DebugFunctionsFactory::makeInstance()->dump($GLOBALS['phpDebugDump']);
}
