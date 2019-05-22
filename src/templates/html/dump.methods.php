<?php
if (is_object($var)) :
    $methods = get_class_methods($var);
    sort($methods);

    ?><br>
    <fieldset>
        <legend><var onclick="toggle('dump-methods');">Methods</var></legend>
        <div id="dump-methods" style="display: none;"><?php echo implode('<br>', $methods) ?></div>
    </fieldset><?php
endif;
