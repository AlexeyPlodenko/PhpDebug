<br>
<fieldset>
    <legend>
        <?php if (isset($objectBacktrace)) : ?>
            <var onclick="toggle('dump-backtrace'); toggleOff('dump-object-backtrace');">Backtrace</var> |
            <var onclick="toggleOff('dump-backtrace'); toggle('dump-object-backtrace');">Object Backtrace</var>
        <?php else : ?>
            <var onclick="toggle('dump-backtrace');">Backtrace</var>
        <?php endif ?>
    </legend>
    <div id="dump-backtrace">
        <?php $self->printBacktrace($backtrace); ?>
    </div>
    <?php if (isset($objectBacktrace)) : ?>
        <div id="dump-object-backtrace" style="display: none;">
            <?php $self->printBacktrace($objectBacktrace); ?>
        </div>
    <?php endif ?>
</fieldset>

<script>
    /**
     * @param {string} id
     */
    function toggle(id) {
        if (document.getElementById(id).style.display === 'none') {
            toggleOn(id);
        } else {
            toggleOff(id);
        }
    }

    /**
     * @param {string} id
     */
    function toggleOn(id) {
        var $o = document.getElementById(id);
        $o.style.display = ($o.tagName === 'TR' ? 'table-row' : 'block');
    }

    /**
     * @param {string} id
     */
    function toggleOff(id) {
        document.getElementById(id).style.display = 'none';
    }
</script>