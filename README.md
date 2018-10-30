# PhpDebug
PHP debug scripts for CLI and browser.

No dependencies.

# Installation
Go to your php.ini and change auto_prepend_file and auto_append_file with full path, pointing to the php.ini-prepend.php and php.ini-append.php file.

# Usage
Put dump(); any where in the code to stop execution on this line and receive the stack trace.

Pass a parameter to the dump('EXAMPLE'); function, to output it.

Add any data to the super global array $GLOBALS['dumpAll'] to output after the scripts execution.
