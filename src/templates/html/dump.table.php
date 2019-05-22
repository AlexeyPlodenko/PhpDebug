<?php
$colWidth = floor(100 / count($var));
?><table style="border-collapse: collapse; width: 100%;"><tr>
    <?php foreach ($var as $col) : ?>
        <td style="vertical-align: top; border-style: groove; border-color: threedface; padding: 0.75em; width: <?php echo $colWidth ?>%;"><pre style="white-space: pre-wrap; line-height: 22px;"><?php print_r($col) ?></pre></td>
    <?php endforeach ?>
</tr></table>