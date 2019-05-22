<?php
namespace AlexeyPlodenko\PhpDebug;

use Exception;
use InvalidArgumentException;
use Qazd\TextDiff;

abstract class AbstractDebugFunctions
{
    const TYPE_HTML = 'HTML';
    const TYPE_TABLE = 'TABLE';

    /**
     * @var string[]
     */
    protected $templates = array();

    /**
     * @var bool
     */
    protected $cli = true;

    /**
     * @var string
     */
    protected $eol;

    /**
     * @var string
     */
    protected $verticalSpace;

    /**
     * @var array
     */
    protected $themeConfig;

    /**
     * @var array
     */
    protected $stacktrace;

    /**
     * @var int
     */
    protected $stackTraceOffset;

    /**
     * AbstractHelperFunctions constructor.
     */
    public function __construct()
    {
        $this->initTheme();
    }

    /**
     * _dumpOutputBufferPrepare
     */
    abstract protected function _dumpOutputBufferPrepare();

    /**
     * _dumpOpen
     */
    abstract protected function _dumpOpen();

    /**
     * @param mixed $var
     */
    abstract protected function _dumpTypeTable($var);

    /**
     * @param mixed $var
     */
    protected function _dumpTypeHtml($var)
    {
        echo $var;
    }

    /**
     * _dumpTypeNull
     */
    protected function _dumpTypeNull()
    {
        echo 'NULL';
    }

    /**
     * @param mixed $var
     */
    protected function _dumpTypeBool($var)
    {
        echo ($var ? 'true' : 'false');
    }

    /**
     * @param array $trace
     */
    protected function setStackTrace(array $trace)
    {
        $this->stacktrace = $trace;
    }

    /**
     * @param Exception $var
     */
    protected function _dumpTypeObject($var)
    {
        $code = $var->getCode();
        $file = $var->getFile();
        $line = $var->getLine();
        $message = $var->getMessage();
        $varClass = get_class($var);

        $stackTrace = $var->getTrace();
        // adding the place of exception to the stack trace, so its source code will be shown
        array_unshift($stackTrace, array(
            'class' => $varClass,
            'type' => '->',
            'function' => '__construct',
            'file' => $file,
            'line' => $line,
            'code' => $code
        ));
        $this->setStackTrace($stackTrace);

        echo "Exception $varClass #$code with message \"$message\" in file \"$file\" on line $line";
        echo $this->verticalSpace;
    }

    /**
     * @param mixed $var
     */
    abstract protected function _dumpTypeAny($var);

    /**
     * _dumpVariable
     */
    abstract protected function _dumpVariable();

    /**
     * _dumpClose
     */
    abstract protected function _dumpClose();

    /**
     * @param int $offset
     */
    public function setStackTraceOffset($offset)
    {
        $this->stackTraceOffset = $offset;
    }

    /**
     * _dumpBacktrace
     */
    protected function _dumpBacktrace()
    {
        if (isset($this->stacktrace)) {
            $backtrace = $this->stacktrace;
        } else {
            $backtrace = debug_backtrace();
            $backtrace = array_slice($backtrace, (int)$this->stackTraceOffset);
        }

        $this->renderTpl('dump', 'css', array('themeConfig' => $this->themeConfig));
        $this->renderTpl('dump', 'backtrace', array(
            'objectBacktrace' => isset($objectBacktrace) ? $objectBacktrace : null,
            'backtrace' => $backtrace
        ));
    }

    /**
     * _dumpFinalize
     */
    abstract protected function _dumpFinalize();

    /**
     * @param mixed $var
     */
    abstract protected function _dumpMethods($var);

    /**
     * @param mixed $var
     */
    abstract protected function _dumpProperties($var);

    /**
     * _dumpOutputLineByLine
     */
    abstract protected function _dumpOutputLineByLine();

    /**
     * configureRuntime
     */
    protected function configureRuntime()
    {
        // increase time and memory limits, since debug information rendering may take some time
        set_time_limit(360);
        ini_set('memory_limit', '2G');
    }

    /**
     * @param mixed $var
     * @param array $config [type => HelpersFunctions::TYPE_*]
     */
    public function dump($var = null, array $config = array())
    {
        $this->configureRuntime();
        $this->clearOutput();
        $this->_dumpOutputBufferPrepare();
        $this->_dumpOpen();
        if (isset($config['type']) && $config['type'] === static::TYPE_TABLE) {
            $this->_dumpTypeTable($var);
        } elseif (isset($config['type']) && $config['type'] === static::TYPE_HTML) {
            $this->_dumpTypeHtml($var);
        } elseif ($var === null) {
            $this->_dumpTypeNull();
        } elseif (is_bool($var)) {
            $this->_dumpTypeBool($var);
        } elseif (is_object($var) && $var instanceof Exception) {
            $this->_dumpTypeObject($var);
        } else {
            $this->_dumpTypeAny($var);
        }
        $this->_dumpClose();
        $this->_dumpOutputLineByLine();
        $this->_dumpVariable();
        $this->_dumpBacktrace();
        $this->_dumpMethods($var);
        $this->_dumpProperties($var);
        $this->_dumpFinalize();

        exit;
    }

    /**
     * @param string $funcName
     * @param string $fragmentName
     * @param array $vars
     */
    protected function renderTpl($funcName, $fragmentName, array $vars = array())
    {
        $name = "$funcName.$fragmentName";
        $typeDir = ($this->isCli() ? 'cli' : 'html');

        // passing current context to the template
        $vars['self'] = $this;

        $context = function(array $vars, $path) {
            extract($vars);
            require $path;
        };
        $path = __DIR__ ."/templates/$typeDir/$name.php";
        $context($vars, $path);
    }

    /**
     * initTheme
     */
    protected function initTheme()
    {
        $defaultTheme = 'dark';
        $defaultThemeConfig = file_get_contents(__DIR__ . '/../themes/' . $defaultTheme . '.json');
        $defaultThemeConfig = json_decode($defaultThemeConfig, true);

        $theme = null; // @TODO read it from /data/config.json

        if ($theme && $theme !== $defaultTheme) {
            $this->themeConfig = file_get_contents(__DIR__ . '/../themes/' . $defaultTheme . '.json');
            $this->themeConfig = json_decode($this->themeConfig, true);

            $this->themeConfig = array_merge_recursive($defaultThemeConfig, $this->themeConfig);
        } else {
            $this->themeConfig = $defaultThemeConfig;
        }

        // configuring highlight_string() function with the current theme
        ini_set('highlight.comment', $this->themeConfig['highlight']['comment']);
        ini_set('highlight.default', $this->themeConfig['highlight']['default']);
        ini_set('highlight.html', $this->themeConfig['highlight']['html']);
        ini_set('highlight.keyword', $this->themeConfig['highlight']['keyword']);
        ini_set('highlight.string', $this->themeConfig['highlight']['string']);
    }

    /**
     * @param array|null $backtrace
     */
    public function dumpStacktrace(array $backtrace = null)
    {
        if (!$backtrace) {
            $backtrace = debug_backtrace();
        }
        $backtrace = array_slice($backtrace, (int)$this->stackTraceOffset);

        $this->clearOutput();
        $this->printBacktraceSimple($backtrace);

        exit;
    }

    /**
     * clearOutput
     */
    public function clearOutput()
    {
        while (ob_get_level()) {
            ob_end_clean();
        }
    }

    /**
     * @param mixed $var
     */
    public function dumpAsIs($var = null)
    {
        $this->clearOutput();

        echo $var;

        exit;
    }

    /**
     * @param mixed $var
     */
    public function dumpTable(array $var)
    {
        $this->dump($var, array('type' => static::TYPE_TABLE));
    }

    /**
     * @param array $backtrace
     */
    public function printBacktraceSimple(array $backtrace)
    {
        foreach ($backtrace as $call) {
            echo (isset($call['class']) ? $call['class'] . $call['type'] : null);
            echo (isset($call['function']) ? $call['function'] . '()' : '_NO_FUNC_') . "\n\t";
            echo (isset($call['file']) ? $call['file'] : '_NO_FILE_') . ':';
            echo (isset($call['line']) ? $call['line'] : '_NO_LINE_');
            echo $this->verticalSpace;
        }
    }

    /**
     * @return bool
     */
    protected function isCli()
    {
        return $this->cli;
    }

    /**
     * @return bool|string
     */
    protected function cliGetUserInput()
    {
        if (!defined('STDIN')) {
            define('STDIN', fopen('php://stdin', 'r'));

            if (function_exists('readline_callback_handler_install')) {
                readline_callback_handler_install('', function () {});
            }
        }

        // read user input, after the user would hit ENTER
        return fread(STDIN, 4096);

        // dont close stdin, otherwise the output to CLI would stop
        // fclose(STDIN);
    }
}
