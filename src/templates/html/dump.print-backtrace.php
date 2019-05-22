<table border="1" class="backtrace">
    <tr>
        <th>#</th>
        <th>Call</th>
        <th>File</th>
        <th>Arguments</th>
    </tr>
    <?php foreach ($backtrace as $i => $call) : ?>
        <tr>
            <td style="text-align: right; vertical-align: top;"><?php echo ($i + 1) ?></td>
            <td style="white-space: nowrap; vertical-align: top;">
                <var onclick="toggle('<?php echo $idPrefix, $i ?>');">
                    <?php echo (isset($call['class']) ? $call['class'] . $call['type'] : null), (isset($call['function']) ? $call['function'] : '_NO_FUNC_'); ?>
                </var>
            </td>
            <td style="vertical-align: top; width: 70%;">
                <var onclick="toggle('<?php echo $idPrefix, $i ?>');">
                    <?php echo (isset($call['file']) ? $call['file'] : '_NO_FILE_') , ' (', (isset($call['line']) ? $call['line'] : '_NO_LINE_') ,')'; ?>
                </var>
            </td>
            <td style="vertical-align: top; width: 30%;">
                <?php /*if (isset($call['args'])) : // @TODO fix, this is causing performance problems ATM
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
</table>