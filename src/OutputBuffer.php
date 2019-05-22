<?php

//use BadMethodCallException;
//use Exception;
//use InvalidArgumentException;
//use SplTempFileObject;

/**
 * @TODO not used
 * @link http://stackoverflow.com/questions/5446647/how-can-i-use-var-dump-output-buffering-without-memory-errors/
 */
//class OutputBuffer
//{
//    /**
//     * @var int
//     */
//    private $chunkSize;
//
//    /**
//     * @var bool
//     */
//    private $started;
//
//    /**
//     * @var SplFileObject
//     */
//    private $store;
//
//    /**
//     * @var bool Set Verbosity to true to output analysis data to stderr
//     */
//    private $verbose = true;
//
//    public function __construct($chunkSize = 1024)
//    {
//        $this->chunkSize = $chunkSize;
//        $this->store     = new SplTempFileObject();
//    }
//
//    public function start()
//    {
//        if ($this->started) {
//            throw new BadMethodCallException('Buffering already started, can not start again.');
//        }
//        $this->started = true;
//        $result        = ob_start(array($this, 'bufferCallback'), $this->chunkSize);
//        $this->verbose && file_put_contents('php://stderr',
//            sprintf("Starting Buffering: %d; Level %d\n", $result, ob_get_level()));
//
//        return $result;
//    }
//
//    public function flush()
//    {
//        $this->started && ob_flush();
//    }
//
//    public function stop()
//    {
//        if ($this->started) {
//            ob_flush();
//            $result        = ob_end_flush();
//            $this->started = false;
//            $this->verbose && file_put_contents('php://stderr',
//                sprintf("Buffering stopped: %d; Level %d\n", $result, ob_get_level()));
//        }
//    }
//
//    private function bufferCallback($chunk, $flags)
//    {
//
//        $chunkSize = strlen($chunk);
//
//        if ($this->verbose) {
//            $level     = ob_get_level();
//            $constants = array(
//                'PHP_OUTPUT_HANDLER_START',
//                'PHP_OUTPUT_HANDLER_WRITE',
//                'PHP_OUTPUT_HANDLER_FLUSH',
//                'PHP_OUTPUT_HANDLER_CLEAN',
//                'PHP_OUTPUT_HANDLER_FINAL'
//            );
//            $flagsText = '';
//            foreach ($constants as $i => $constant) {
//                if ($flags & ($value = constant($constant)) || $value == $flags) {
//                    $flagsText .= (strlen($flagsText) ? ' | ' : '') . $constant . "[$value]";
//                }
//            }
//
//            file_put_contents('php://stderr',
//                "Buffer Callback: Chunk Size $chunkSize; Flags $flags ($flagsText); Level $level\n");
//        }
//
//        if ($flags & PHP_OUTPUT_HANDLER_FINAL) {
//            return true;
//        }
//
//        if ($flags & PHP_OUTPUT_HANDLER_START) {
//            $this->store->fseek(0, SEEK_END);
//        }
//
//        $chunkSize && $this->store->fwrite($chunk);
//
//        if ($flags & PHP_OUTPUT_HANDLER_FLUSH) {
//            // there is nothing to d
//        }
//
//        if ($flags & PHP_OUTPUT_HANDLER_CLEAN) {
//            $this->store->ftruncate(0);
//        }
//
//        return "";
//    }
//
//    public function getSize()
//    {
//        $this->store->fseek(0, SEEK_END);
//
//        return $this->store->ftell();
//    }
//
//    public function getBufferFile()
//    {
//        return $this->store;
//    }
//
//    public function getBuffer()
//    {
//        $array = iterator_to_array($this->store);
//
//        return implode('', $array);
//    }
//
//    public function __toString()
//    {
//        return $this->getBuffer();
//    }
//
//    public function endClean()
//    {
//        return ob_end_clean();
//    }
//}