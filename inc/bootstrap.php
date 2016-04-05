<?php
define('APP_NAME', 'Forex Trader');
setPaths();
date_default_timezone_set('Europe/London');
require('autoloader.php');

$live = false;
if (strstr($_SERVER['HTTP_HOST'], 'aran.me.uk')) {
    $live = true;
}
set::define('live', $live);
set::define('ip', get::ip());
set::define('uri', $_SERVER['REQUEST_URI']);
set::define('host', $_SERVER['HTTP_HOST']);
set::define('cli', (php_sapi_name() === "cli"));
set::define('ajax', (isset($_REQUEST['action']) && isset($_REQUEST['module'])));

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (!cli) {
    set::flushableBuffer();
}

function setPaths(): bool {
    if (!defined('__SCRIPT__') || !defined('__CWD__')) {
        $script_path = (isset($_SERVER['PATH_TRANSLATED']) ? $_SERVER['PATH_TRANSLATED'] : (isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : (isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : null)));
        if ($script_path === null) {
            trigger_error('Failed to find the current script path. Please ensure you\'re running PHP from the command line.');

            return false;
        }

        if (strstr($script_path, DIRECTORY_SEPARATOR)) {
            $script_path_parts = explode(DIRECTORY_SEPARATOR, $script_path);
            $script = array_pop($script_path_parts);
            $cwd = rtrim(implode(DIRECTORY_SEPARATOR, $script_path_parts), DIRECTORY_SEPARATOR);
        } else {
            $script = $script_path;
            $cwd = getcwd();
        }
        if (isset($_SERVER['DOCUMENT_ROOT']) && !empty($_SERVER['DOCUMENT_ROOT'])) {
            $cwd = $_SERVER['DOCUMENT_ROOT'];
        }

        define('__SCRIPT__', $script);
        define('__CWD__', $cwd);
        define('root', __CWD__);
    }

    return true;
}