# PhpDebug
PHP debug scripts for CLI and browser.

Provides some basic functionality required to debug PHP scripts.

# Installation
Go to your php.ini and change auto_prepend_file and auto_append_file with full path, pointing to the php.ini-prepend.php and php.ini-append.php file.

In your favourite console, go to the directory and run `composer install` to install required libraries.

Provided functions:
dump([$myVar]); - outputs the passed parameter, the stack trace and stops further execution.
dumpDiff($string1, $string2); - outputs the visual difference between 2 strings, the stack trace and stops further execution. 
dumpStacktrace(); - outputs the stack trace of the current line and stops further execution.

# Usage
Put `dump();` anywhere in the code to stop execution on this line and receive the stack trace.

Pass a parameter to the `dump($myVar);` function, to output it for further investigation.

Use `dumpDiff('string one', 'string two')` to receive a visual difference between 2 strings as shown on this page https://packagist.org/packages/qazd/text-diff . Useful for code comparison.

Add any data to the super global array `$GLOBALS['phpDebugDump']` and it contents will be outputted after the script would finish.

For example, this fragment would output an array with 3 added timestamps after the script would finish.:
```php
$GLOBALS['phpDebugDump'] = [];
$GLOBALS['phpDebugDump'][] = microtime(true);
...
$GLOBALS['phpDebugDump'][] = microtime(true);
...
$GLOBALS['phpDebugDump'][] = microtime(true);
```
