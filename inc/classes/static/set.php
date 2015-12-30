<?php

/**
 * Class set
 */
final class set {

    /**
     * @param string $key
     * @param string $value
     *
     * @return bool
     */
    public static function define(string $key, string $value): bool {
        if (!defined($key)) {
            define($key, $value);

            return true;
        }

        return false;
    }

    /**
     *
     */
    public static function flushableBuffer() {
        if (!cli) {
            ob_implicit_flush(true);
            ini_set('zlib.output_compression', 'Off');
            ini_set('output_buffering', 'Off');
            ini_set('output_handler', '');
        }
    }
}