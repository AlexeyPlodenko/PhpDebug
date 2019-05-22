<?php
namespace AlexeyPlodenko\PhpDebug\Client\Html;

use AlexeyPlodenko\PhpDebug\AbstractDebugFunctions;
use Exception;
use InvalidArgumentException;
use Qazd\TextDiff;

class DebugFunctions extends AbstractDebugFunctions
{
    /**
     * @var bool
     */
    protected $cli = false;

    /**
     * @var string
     */
    protected $eol = '<br>';

    /**
     * @var string
     */
    protected $verticalSpace = '<br><br>';

    /**
     * _dumpOutputBufferPrepare
     */
    protected function _dumpOutputBufferPrepare()
    {
        ob_start();
        if (!headers_sent()) {
            // CORS, to allow JS access to this domain from any domain
            header('Access-Control-Allow-Origin: *');

            $httpProtocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP 1.0');
            header(
                "$httpProtocol 500 Internal Server Error",
                true,
                500
            );
        }
        ob_start();
    }

    /**
     *
     */
    protected function _dumpOpen()
    {
        ?><pre style="white-space: pre-wrap; line-height: 22px;"><?php
    }

    /**
     * @param mixed $var
     */
    protected function _dumpTypeTable($var)
    {
        $this->renderTpl('dump', 'table', array('var' => $var));
    }

    /**
     * _dumpClose
     */
    protected function _dumpClose()
    {
        echo '</pre>';
    }

    /**
     * _dumpVariable
     */
    protected function _dumpVariable()
    {
        $output = ob_get_clean();
        $this->renderTpl('dump', 'variable', array('output' => $output));
    }

    /**
     * @param mixed $var
     */
    protected function _dumpMethods($var)
    {
        $this->renderTpl('dump', 'methods', array('var' => $var));
    }

    /**
     * @param mixed $var
     */
    protected function _dumpProperties($var)
    {
        $this->renderTpl('dump', 'properties', array('var' => $var));
    }

    /**
     * _dumpOutputLineByLine
     */
    protected function _dumpOutputLineByLine()
    {
    }

    /**
     * _dumpFinalize
     */
    protected function _dumpFinalize()
    {
    }

    /**
     * @param mixed $var
     */
    protected function _dumpTypeAny($var)
    {
        $res = print_r($var, true);
        if (strlen($res) > 99999) {
            $res = substr($res, 0, 99999);
        }

        $res = html_entity_decode($res);
        if (strpos($res, '>') !== false && strpos($res, '<') !== false) {
            $res = htmlspecialchars($res);
        }

        echo $res;
    }

    /**
     * @param string|int|float $a
     * @param string|int|float $b
     */
    public function dumpDiff($a, $b)
    {
        if (!is_scalar($a)) {
            throw new InvalidArgumentException('1st argument must be a string');
        }
        if (!is_scalar($b)) {
            throw new InvalidArgumentException('2nd argument must be a string');
        }

        $cssNormalization = 'table.diff td, table.diff th {padding: 0; line-height: normal; font: inherit;}';

        require 'vendor/autoload.php';
        $res = TextDiff::render($a, $b);
        $css = file_get_contents(__DIR__ .'/../vendor/qazd/text-diff/css/style.css');
        $res = "<style>{$css}{$cssNormalization}</style>{$res}";
        $this->dump($res, array('type' => static::TYPE_HTML));
    }

    /**
     * @param array $backtrace
     * @param int $linesToShowBefore
     * @param int $linesToShowAfter
     */
    public function printBacktrace(array $backtrace, $linesToShowBefore = 10, $linesToShowAfter  = 10)
    {
        foreach ($backtrace as &$call) {
            if (isset($call['file'], $call['line']) && is_file($call['file']) && is_readable($call['file'])) {
                $code_str = file_get_contents($call['file']);
                $code_str = highlight_string($code_str, true);
                $code     = explode('<br />', $code_str);
                unset($code_str);

                $our_line   = $call['line'];
                $start_line = $call['line'] - $linesToShowBefore;
                if ($start_line < 0) {
                    $start_line = 0;
                }
                $our_line -= 1;

                $code = array_slice($code, $start_line, $linesToShowBefore + $linesToShowAfter, true);

                // getting last line number
                end($code);
                $last_line_no     = key($code);
                $last_line_no_len = strlen($last_line_no);

                foreach ($code as $line_no => &$line) {
                    // adding line numbers to the code string
                    $line_no_len = strlen($line_no);
                    $line_no     = str_repeat('&nbsp;&nbsp;', $last_line_no_len - $line_no_len) . $line_no;
                    $line        = $line_no . '.&nbsp;' . $line;
                    if ($line_no != $our_line) {
                        $line = '&nbsp;&nbsp;' . $line;
                    }
                }
                unset($line);
                $code[$our_line] = '<b>>'. $code[$our_line] .'</b>';
                $call['code']    = implode('<br>', $code);
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
                    <td style="text-align: right; vertical-align: top;"><?php echo ($i + 1) ?></td>
                    <td style="white-space: nowrap; vertical-align: top;"><?php echo (isset($call['class']) ? $call['class'] . $call['type'] : null), (isset($call['function']) ? $call['function'] : '_NO_FUNC_'); ?></td>
                    <td style="vertical-align: top; width: 70%;"><var
                            onclick="toggle('<?php echo $idPrefix, $i ?>');"><?php echo (isset($call['file']) ? $call['file'] : '_NO_FILE_') , ' (', (isset($call['line']) ? $call['line'] : '_NO_LINE_') ,')'; ?></var>
                    </td>
                    <td style="vertical-align: top; width: 30%;">
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
                                        <div id="args<?php echo $i; ?>" style="display:none;"><pre><?php echo $args; ?></pre></div>
                                    <?php endif*/ ?>
                    </td>
                </tr>
                <?php if (isset($call['code']) && $call['code']) : ?>
                    <tr id="<?php echo $idPrefix, $i ?>"<?php echo ($i ? ' style="display: none;"' : null) ?>>
                        <td colspan="4">
                            <?php echo $call['code'] ?>
                        </td>
                    </tr>
                <?php endif ?>
            <?php endforeach ?>
        </table><?php
    }
}