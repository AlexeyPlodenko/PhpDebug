<?php
if (is_object($var)) :
    $properties = get_object_vars($var);
    $properties = array_keys($properties);
    sort($properties);

    ?><br>
    <fieldset>
        <legend><var onclick="toggle('dump-properties');">Properties</var></legend>
        <div id="dump-properties" style="display: none;"><?php echo implode('<br>', $properties) ?></div>
    </fieldset><?php
endif;
