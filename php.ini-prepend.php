<?php  /*!ap delete file */
// fix to be able to dump() anytime anywhere, even after everything is rendered
ob_start();

// replaces current host env. with this, comment out to disable
//$host = 'www.example.com';

// replaces current path and query
//$_SERVER['REQUEST_URI'] = '/en/article?id=123';

// configures PHP
//ini_set("display_startup_errors", "1");
//ini_set("display_errors", "1");
//ini_set('html_errors', 0);
//error_reporting(E_ALL ^ E_NOTICE);
//ini_set('memory_limit', '1G');

//$cli = (php_sapi_name() === 'cli');
//$time_limit = ($cli ? 360 : 30);
//set_time_limit($time_limit);

// put anything into this global var. to output after everything will be finished
$GLOBALS['dumpAll'] = array();

if (!function_exists('http_build_url'))
{
    define('HTTP_URL_REPLACE', 1);              // Replace every part of the first URL when there's one of the second URL
    define('HTTP_URL_JOIN_PATH', 2);            // Join relative paths
    define('HTTP_URL_JOIN_QUERY', 4);           // Join query strings
    define('HTTP_URL_STRIP_USER', 8);           // Strip any user authentication information
    define('HTTP_URL_STRIP_PASS', 16);          // Strip any password authentication information
    define('HTTP_URL_STRIP_AUTH', 32);          // Strip any authentication information
    define('HTTP_URL_STRIP_PORT', 64);          // Strip explicit port numbers
    define('HTTP_URL_STRIP_PATH', 128);         // Strip complete path
    define('HTTP_URL_STRIP_QUERY', 256);        // Strip query string
    define('HTTP_URL_STRIP_FRAGMENT', 512);     // Strip any fragments (#identifier)
    define('HTTP_URL_STRIP_ALL', 1024);         // Strip anything but scheme and host

    // Build an URL
    // The parts of the second URL will be merged into the first according to the flags argument.
    //
    // @param   mixed           (Part(s) of) an URL in form of a string or associative array like parse_url() returns
    // @param   mixed           Same as the first argument
    // @param   int             A bitmask of binary or'ed HTTP_URL constants (Optional)HTTP_URL_REPLACE is the default
    // @param   array           If set, it will be filled with the parts of the composed url like parse_url() would return
    function http_build_url($url, $parts=array(), $flags=HTTP_URL_REPLACE, &$new_url=false)
    {
        $keys = array('user','pass','port','path','query','fragment');

        // HTTP_URL_STRIP_ALL becomes all the HTTP_URL_STRIP_Xs
        if ($flags & HTTP_URL_STRIP_ALL)
        {
            $flags |= HTTP_URL_STRIP_USER;
            $flags |= HTTP_URL_STRIP_PASS;
            $flags |= HTTP_URL_STRIP_PORT;
            $flags |= HTTP_URL_STRIP_PATH;
            $flags |= HTTP_URL_STRIP_QUERY;
            $flags |= HTTP_URL_STRIP_FRAGMENT;
        }
        // HTTP_URL_STRIP_AUTH becomes HTTP_URL_STRIP_USER and HTTP_URL_STRIP_PASS
        elseif ($flags & HTTP_URL_STRIP_AUTH)
        {
            $flags |= HTTP_URL_STRIP_USER;
            $flags |= HTTP_URL_STRIP_PASS;
        }

        // Parse the original URL
        $parse_url = parse_url($url);

        // Scheme and Host are always replaced
        if (isset($parts['scheme']))
            $parse_url['scheme'] = $parts['scheme'];
        if (isset($parts['host']))
            $parse_url['host'] = $parts['host'];

        // (If applicable) Replace the original URL with it's new parts
        if ($flags & HTTP_URL_REPLACE)
        {
            foreach ($keys as $key)
            {
                if (isset($parts[$key]))
                    $parse_url[$key] = $parts[$key];
            }
        }
        else
        {
            // Join the original URL path with the new path
            if (isset($parts['path']) && ($flags & HTTP_URL_JOIN_PATH))
            {
                if (isset($parse_url['path']))
                    $parse_url['path'] = rtrim(str_replace(basename($parse_url['path']), '', $parse_url['path']), '/') . '/' . ltrim($parts['path'], '/');
                else
                    $parse_url['path'] = $parts['path'];
            }

            // Join the original query string with the new query string
            if (isset($parts['query']) && ($flags & HTTP_URL_JOIN_QUERY))
            {
                if (isset($parse_url['query']))
                    $parse_url['query'] .= '&' . $parts['query'];
                else
                    $parse_url['query'] = $parts['query'];
            }
        }

        // Strips all the applicable sections of the URL
        // Note: Scheme and Host are never stripped
        foreach ($keys as $key)
        {
            if ($flags & (int)constant('HTTP_URL_STRIP_' . strtoupper($key)))
                unset($parse_url[$key]);
        }


        $new_url = $parse_url;

        return
             ((isset($parse_url['scheme'])) ? $parse_url['scheme'] . '://' : '')
            .((isset($parse_url['user'])) ? $parse_url['user'] . ((isset($parse_url['pass'])) ? ':' . $parse_url['pass'] : '') .'@' : '')
            .((isset($parse_url['host'])) ? $parse_url['host'] : '')
            .((isset($parse_url['port'])) ? ':' . $parse_url['port'] : '')
            .((isset($parse_url['path'])) ? $parse_url['path'] : '')
            .((isset($parse_url['query'])) ? '?' . $parse_url['query'] : '')
            .((isset($parse_url['fragment'])) ? '#' . $parse_url['fragment'] : '')
        ;
    }
}

// switch env. to the one we want
if (isset($host) && $host) {
	$_SERVER['HTTP_HOST'] = $_SERVER['SERVER_NAME'] = $host;
	if (isset($_SERVER['HTTP_REFERER'])) {
		$referrer = parse_url($_SERVER['HTTP_REFERER']);
		if (isset($referrer['host'])) {
			$referrer['host'] = $host;
			$_SERVER['HTTP_REFERER'] = http_build_url(null, $referrer);
		}
	}
}

function dumpBacktrace(array $backtrace = null) {
    if (!$backtrace) {
        $backtrace = debug_backtrace();
        array_shift($backtrace);
    }

    dumpClearOutput();
    dumpPrintBacktraceSimple($backtrace);
    exit;
}

function dumpClearOutput() {
    while (ob_get_level()) {
		ob_end_clean();
	}
}

function dumpAsIs($var = null) {
    set_time_limit(360);
    ini_set('memory_limit', '2G');

    dumpClearOutput();

    echo $var;

    exit;
}

function dump($var = null) {
    set_time_limit(360);
    ini_set('memory_limit', '2G');

	$cli = (php_sapi_name() === 'cli');

    dumpClearOutput();
    ob_start();
    if (!$cli) {
        if (!headers_sent()) {
            // CORS, to allow JS access to this domain from any domain
            header('Access-Control-Allow-Origin: *');
            header(
                (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP 1.0') .' 500 Internal Server Error',
                true,
                500
            );
        }
    } else {
        ob_implicit_flush(true);
    }

    ob_start();

	if ($cli) : ?>
		<?php echo "\n>>>>>>\n"; ?>
	<?php else :
		?><pre style="white-space: pre-wrap; line-height: 22px;"><?php
	endif;

	if ($var === null) {
		echo 'NULL';

    } elseif (is_bool($var)) {
		echo ($var ? 'true' : 'false');

    } elseif (is_object($var) && $var instanceof Exception) {
        /* @var $var Exception */
//        $objectBacktrace = (array)$var->getTrace();
        $objectBacktrace = [];
//        $publicProps = get_object_vars($var);
        $code = $var->getCode();
        $file = $var->getFile();
        $line = $var->getLine();
        $message = $var->getMessage();

        echo 'Exception '. get_class($var) .' #'. $code .' with message "'. $message .'" in file "'. $file .'" on line '. $line;
        echo ($cli ? "\n\n" : '<br><br>');

    } else {
        ini_set('memory_limit', '1G');
		$res = print_r($var, true);
        if (strlen($res) > 99999) {
            $res = substr($res, 0, 99999);
        }
        if (!$cli) {
            $res = html_entity_decode($res);
            if (strpos($res, '>') !== false && strpos($res, '<') !== false) {
                $res = htmlspecialchars($res);
            }
        }
        echo $res;

    }

	if (!$cli) :
        ?></pre><?php
    endif;

    if ($cli) {
        $output = ob_get_clean();
        $output = explode("\n", $output);
        $outputSize = count($output);
        // how many rows should be in result set before the user interaction is needed
        $rowsAmountToAutoOutput = 20;
        $line_by_line = ($outputSize > $rowsAmountToAutoOutput);
$line_by_line = false;

        if ($line_by_line) {
            echo "\n";
            echo "PRESS [ENTER] TO OUTPUT NEXT LINE\n";
            echo "PRESS [a], [ENTER] TO OUTPUT EVERYTHING\n";
            echo "PRESS TWICE [CTRL]+[C] TO STOP\n";
            echo "LINES TO GO: ". $outputSize ."\n";
            echo "\n";
            ob_flush();
            flush();
        }

        foreach ($output as &$line) {
            echo $line ,"\n";

            if ($line_by_line) {
                ob_flush();
                flush();
                $input = _cliGetUserInput();

                if ($input[0] === 'a' || $input[0] === 'A') {
                    $line_by_line = false;
                }
            }

//            $input_len = strlen($input);
//            $keycodes = array();
//            for ($i = 0; $i < $input_len; $i++) {
//                $keycodes[] = ord($input[$i]);
//            }
//            print_r(array($input, $keycodes)); ob_flush(); exit;
        }
        unset($line);

    } else {
        $output = ob_get_clean();

        ?><fieldset><legend><var onclick="toggle('dump-output');">Variable</var></legend><div id="dump-output"><?= $output ?></div></fieldset><?php

        if (is_object($var)) :
            $methods = get_class_methods($var);
            sort($methods);

            $properties = get_object_vars($var);
            $properties = array_keys($properties);
            sort($properties);

            ?><fieldset><legend><var onclick="toggle('dump-methods');">Methods</var></legend><div id="dump-methods" style="display:none;"><?= implode('<br>', $methods) ?></div></fieldset>
            <fieldset><legend><var onclick="toggle('dump-properties');">Properties</var></legend><div id="dump-properties" style="display:none;"><?= implode('<br>', $properties) ?></div></fieldset><?php
        endif;
    }

    if (!isset($backtrace)) {
        $backtrace = debug_backtrace();
    }

	$dark = true;
	
    if ($cli) :
		echo "------------------------------\nBACKTRACE\n";
		foreach ($backtrace as $call) {
			if (isset($call['file'])) {
				echo $call['file'] ,':', $call['line'] ,"\n";
			}
		}

	else : ?>
        <style>
            * {
                font: 16px Arial;
            }
            var {
                cursor: pointer;
                border-bottom: 1px dashed #000;
            }
            .backtrace {
                border-collapse: collapse;
                border-width: 0;
                width: 100%;
            }
            .backtrace, .backtrace td, .backtrace th {
                border-color: #ccc;
            }
            .backtrace th {
                border-top-width: 0;
            }
            .backtrace td:nth-child(1), .backtrace th {
                border-left-width: 0;
            }
            .backtrace td:nth-child(4), .backtrace th:nth-child(4) {
                border-right-width: 0;
                width: 99%;
            }
            .backtrace td, .backtrace th {
                padding: 7px 5px;
            }
            .backtrace th {
                font-weight: bold;
                text-align: left;
            }
			<?php if ($dark) : ?>
				BODY {
					background: #232525;
				}
				* {
					color: #A9B7C6;
				}
			<?php endif ?>
        </style>
        <fieldset>
            <legend>
                <?php if (isset($objectBacktrace)) : ?>
                    <var onclick="toggle('dump-backtrace'); toggleOff('dump-object-backtrace');">Backtrace</var> | <var onclick="toggleOff('dump-backtrace'); toggle('dump-object-backtrace');">Object Backtrace</var>
                <?php else : ?>
                    Backtrace
                <?php endif ?>
            </legend>
            <div id="dump-backtrace">
                <?php dumpPrintBacktrace($backtrace); ?>
            </div>
            <?php if (isset($objectBacktrace)) : ?>
                <div id="dump-object-backtrace" style="display: none;">
                    <?php dumpPrintBacktrace($objectBacktrace); ?>
                </div>
            <?php endif ?>
        </fieldset>
        <script>
            function toggle(id) {
                var $o = document.getElementById(id);
                if ($o.style.display === 'none') {
                    toggleOn(id);
                } else {
                    toggleOff(id);
                }
            }
            function toggleOn(id) {
                var $o = document.getElementById(id);

                var display;
                switch ($o.tagName) {
                    case 'TR':
                        display = 'table-row';
                    break;
                    default:
                        display = 'block';
                    break;
                }
                $o.style.display = display;
            }
            function toggleOff(id) {
                var $o = document.getElementById(id);

                $o.style.display = 'none';
            }
        </script>
	<?php endif;

    if ($cli) {
        ob_flush();
        flush();
    }

	exit;
}

/**
 * @link http://stackoverflow.com/questions/5446647/how-can-i-use-var-dump-output-buffering-without-memory-errors/
 */
class OutputBuffer
{
    /**
     * @var int
     */
    private $chunkSize;

    /**
     * @var bool
     */
    private $started;

    /**
     * @var SplFileObject
     */
    private $store;

    /**
     * @var bool Set Verbosity to true to output analysis data to stderr
     */
    private $verbose = true;

    public function __construct($chunkSize = 1024) {
        $this->chunkSize = $chunkSize;
        $this->store     = new SplTempFileObject();
    }

    public function start() {
        if ($this->started) {
            throw new BadMethodCallException('Buffering already started, can not start again.');
        }
        $this->started = true;
        $result = ob_start(array($this, 'bufferCallback'), $this->chunkSize);
        $this->verbose && file_put_contents('php://stderr', sprintf("Starting Buffering: %d; Level %d\n", $result, ob_get_level()));
        return $result;
    }

    public function flush() {
        $this->started && ob_flush();
    }

    public function stop() {
        if ($this->started) {
            ob_flush();
            $result = ob_end_flush();
            $this->started = false;
            $this->verbose && file_put_contents('php://stderr', sprintf("Buffering stopped: %d; Level %d\n", $result, ob_get_level()));
        }
    }

    private function bufferCallback($chunk, $flags) {

        $chunkSize = strlen($chunk);

        if ($this->verbose) {
            $level     = ob_get_level();
            $constants = ['PHP_OUTPUT_HANDLER_START', 'PHP_OUTPUT_HANDLER_WRITE', 'PHP_OUTPUT_HANDLER_FLUSH', 'PHP_OUTPUT_HANDLER_CLEAN', 'PHP_OUTPUT_HANDLER_FINAL'];
            $flagsText = '';
            foreach ($constants as $i => $constant) {
                if ($flags & ($value = constant($constant)) || $value == $flags) {
                    $flagsText .= (strlen($flagsText) ? ' | ' : '') . $constant . "[$value]";
                }
            }

            file_put_contents('php://stderr', "Buffer Callback: Chunk Size $chunkSize; Flags $flags ($flagsText); Level $level\n");
        }

        if ($flags & PHP_OUTPUT_HANDLER_FINAL) {
            return TRUE;
        }

        if ($flags & PHP_OUTPUT_HANDLER_START) {
            $this->store->fseek(0, SEEK_END);
        }

        $chunkSize && $this->store->fwrite($chunk);

        if ($flags & PHP_OUTPUT_HANDLER_FLUSH) {
            // there is nothing to d
        }

        if ($flags & PHP_OUTPUT_HANDLER_CLEAN) {
            $this->store->ftruncate(0);
        }

        return "";
    }

    public function getSize() {
        $this->store->fseek(0, SEEK_END);
        return $this->store->ftell();
    }

    public function getBufferFile() {
        return $this->store;
    }

    public function getBuffer() {
        $array = iterator_to_array($this->store);
        return implode('', $array);
    }

    public function __toString() {
        return $this->getBuffer();
    }

    public function endClean() {
        return ob_end_clean();
    }
}

function dumpPrintBacktraceSimple($backtrace) {
    $cli = (php_sapi_name() === 'cli');

    foreach ($backtrace as $call) {
        echo (isset($call['class']) ? $call['class'] . $call['type'] : null);
        echo (isset($call['function']) ? $call['function'] .'()' : '_NO_FUNC_') ."\n\t";
        echo (isset($call['file']) ? $call['file'] : '_NO_FILE_') .':';
        echo (isset($call['line']) ? $call['line'] : '_NO_LINE_');
        if ($cli) {
            echo "\n\n";
        } else {
            echo '<br>';
        }
    }
}

function dumpPrintBacktrace($backtrace) {
    $lines_to_show_before = 10;
    $lines_to_show_after = 10;
    foreach ($backtrace as &$call) {
        if (isset($call['file'], $call['line']) && is_file($call['file']) && is_readable($call['file'])) {
            $code_str = file_get_contents($call['file']);
            $code_str = highlight_string($code_str, true);
            $code = explode('<br />', $code_str);
            unset($code_str);

            $our_line = $call['line'];
            $start_line = $call['line'] - $lines_to_show_before;
            if ($start_line < 0) {
                $start_line = 0;
            }
            $our_line -= 1;

            $code = array_slice($code, $start_line, $lines_to_show_before + $lines_to_show_after, true);

            // getting last line number
            end($code);
            $last_line_no = key($code);
            $last_line_no_len = strlen($last_line_no);

            foreach ($code as $line_no => &$line) {
                // adding line numbers to the code string
                $line_no_len = strlen($line_no);
                $line_no = str_repeat('&nbsp;&nbsp;', $last_line_no_len - $line_no_len) . $line_no;
                $line = $line_no .'.&nbsp;'. $line;
                if ($line_no != $our_line) {
                    $line = '&nbsp;&nbsp;'. $line;
                }
            }
            unset($line);
            $code[$our_line] = '<b>>'. $code[$our_line] .'</b>';
            $call['code'] = implode('<br>', $code);
        }
    }
    unset($call);

    $idPrefix = 'code'. uniqid();

    ?><table border="1" class="backtrace">
        <tr>
            <th>#</th>
            <th>Call</th>
            <th>File</th>
            <th>Arguments</th>
        </tr>
        <?php foreach ($backtrace as $i => $call) : ?>
            <tr>
                <td align="right" valign="top"><?php echo ($i + 1); ?></td>
                <td valign="top"><?php echo (isset($call['class']) ? $call['class'] . $call['type'] : null), (isset($call['function']) ? $call['function'] : '_NO_FUNC_'); ?></td>
                <td valign="top"><var onclick="toggle('<?= $idPrefix , $i ?>');"><?php echo (isset($call['file']) ? $call['file'] : '_NO_FILE_') .' ('. (isset($call['line']) ? $call['line'] : '_NO_LINE_') .')'; ?></var></td>
                <td valign="top">
                    <?php /*if (isset($call['args'])) :
                        $args = array();
                        $allowed_classes = array('RuntimeException');
                        foreach ($call['args'] as $arg_i => &$arg) {
                            if (is_object($arg)) {
                                $class_name = get_class($arg);
                                if (!in_array($class_name, $allowed_classes)) {
                                    $class_json = json_encode($arg);
                                    $args[$class_name] = json_decode($class_json);
                                } else {
                                    $args[$class_name] = $arg;
                                }
                            } else {
                                $args[$arg_i] = $arg;
                            }
                        }
                        unset($arg);
//                            if (is_object($call['args'])) {
//                                $params_json = json_encode($call['args']);
//                                $params = json_decode($params_json);
//                                print_r($params); exit;
//                            }
                        $args = print_r($args, true);
                        if (strlen($args) > 9999) {
                            $args = substr($args, 0, 9999);
                            $args .= '...';
                        } ?>
                        <a href="" onclick="toggle('args<?php echo $i; ?>'); return false;">Show</a>
                        <div id="args<?php echo $i; ?>" style="display:none;"><pre><?= $args; ?></pre></div>
                    <?php endif*/ ?>
                </td>
            </tr>
            <?php if (isset($call['code']) && $call['code']) : ?>
                <tr id="<?= $idPrefix , $i ?>"<?= ($i ? ' style="display:none;"' : null) ?>>
                    <td colspan="4">
                        <?= $call['code'] ?>
                    </td>
                </tr>
            <?php endif ?>
        <?php endforeach ?>
    </table><?php
}

/**
 * @return string
 */
function _cliGetUserInput() {
    if (!defined('STDIN')) {
        define('STDIN', fopen('php://stdin', 'r'));

        if (function_exists('readline_callback_handler_install')) {
            readline_callback_handler_install('', function() {});
        }
    }

    // get anything the user will type
    // CLI returns everything what user will type after he will press ENTER
    $input = fread(STDIN, 4096);

    return $input;

    // dont close stdin, otherwise the output to CLI stops
    // fclose(STDIN);
}

