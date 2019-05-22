<style>
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

    <?php if (isset($themeConfig['css'])) : ?>
    <?php echo is_array($themeConfig['css']) ? implode(null, $themeConfig['css']) : $themeConfig['css'] ?>
    <?php endif ?>
</style>