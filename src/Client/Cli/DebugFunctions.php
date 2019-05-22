<?php
namespace AlexeyPlodenko\PhpDebug\Client\Cli;

use AlexeyPlodenko\PhpDebug\AbstractDebugFunctions;

class DebugFunctions extends AbstractDebugFunctions
{
    /**
     * @var bool
     */
    protected $cli = true;

    /**
     * @var string
     */
    protected $eol = "\n";

    /**
     * @var string
     */
    protected $verticalSpace = "\n\n";

    /**
     * _dumpOutputBufferPrepare
     */
    protected function _dumpOutputBufferPrepare()
    {
        ob_start();
        ob_implicit_flush(true);
        ob_start();
    }

    /**
     *
     */
    protected function _dumpOpen()
    {
        echo "\n>>>>>>\n";
    }

    /**
     * @param mixed $var
     */
    protected function _dumpTypeTable($var)
    {
        echo 'Not applicable in the CLI mode.';
    }

    /**
     * @param mixed $var
     */
    protected function _dumpTypeHtml($var)
    {
        echo 'Not applicable in the CLI mode.';
    }

    /**
     * _dumpClose
     */
    protected function _dumpClose()
    {
    }

    /**
     * @param mixed $var
     */
    protected function _dumpMethods($var)
    {
    }

    /**
     * @param mixed $var
     */
    protected function _dumpProperties($var)
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
        echo $res;
    }

    /**
     * _dumpOutputLineByLine
     */
    protected function _dumpOutputLineByLine()
    {
        $output     = ob_get_clean();
        $output     = explode("\n", $output);
        $outputSize = count($output);
        // how many rows should be in result set before the user interaction is needed
        $rowsAmountToAutoOutput = 20;
        $lineByLine = ($outputSize > $rowsAmountToAutoOutput);

        if ($lineByLine) {
            $this->renderTpl('dump', 'line-by-line', array('outputSize' => $outputSize));
            ob_flush();
            flush();
        }

        foreach ($output as $line) {
            echo $line, "\n";

            if ($lineByLine) {
                ob_flush();
                flush();

                // read user input...
                $input = $this->cliGetUserInput();
                if ($input[0] === 'a' || $input[0] === 'A') {
                    // and cancel output if A key was pressed

                    $lineByLine = false;
                }
            }
        }
    }

    /**
     * _dumpVariable
     */
    protected function _dumpVariable()
    {
    }

    /**
     * _dumpFinalize
     */
    protected function _dumpFinalize()
    {
        ob_flush();
        flush();
    }
}
