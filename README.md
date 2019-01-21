# PhpDebug
PHP debug scripts for CLI and browser.

No dependencies.

Provides some basic functionality required to debug PHP scripts.

# Installation
Go to your php.ini and change auto_prepend_file and auto_append_file with full path, pointing to the php.ini-prepend.php and php.ini-append.php file.

Provided functions:
dump([$myVar]); - outputs the passed parameter, the stack trace and stops further execution.
dumpBacktrace(); - outputs the stack trace and stops further execution.
dumpClearOutput(); - clears the output buffer

# Usage
Put `dump();` anywhere in the code to stop execution on this line and receive the stack trace.

Pass a parameter to the `dump($myVar);` function, to output it for further investigation.

Add any data to the super global array `$GLOBALS['dumpAll']` and it contents will be outputted after the script would finish.

For example, this fragment would output an array with 3 added timestamps after the script would finish.:
```php
$GLOBALS['dumpAll'] = [];
$GLOBALS['dumpAll'][] = microtime(true);
...
$GLOBALS['dumpAll'][] = microtime(true);
...
$GLOBALS['dumpAll'][] = microtime(true);
```
