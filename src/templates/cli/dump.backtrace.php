<?php
echo "------------------------------\nBACKTRACE\n";
foreach ($backtrace as $call) {
    if (isset($call['file'])) {
        echo $call['file'] ,':', $call['line'] ,"\n";
    }
}
