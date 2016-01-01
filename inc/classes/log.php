<?php

/**
 * Class log
 */
class log {

    const LOG_DIRECTORY = '/var/log/forex';

    // Log levels
    const INFO = 1;
    const DEBUG = 2;
    const WARNING = 3;
    const ERROR = 4;

    /**
     * Higher severity errors will cascade into the more granular log level files
     */
    const ERROR_CODES = [
        1 => 'info',
        2 => 'debug',
        3 => 'warning',
        4 => 'error',
    ];

    /**
     * @param string $message
     * @param int    $severity
     */
    public static function write($message, int $severity) {
        self::writeToLogs($message, $severity);
    }

    /**
     * @param string $str
     * @param string $filename
     */
    private static function appendToFile(string $str, string $filename) {
        if ($file = fopen($filename, 'a')) {
            fwrite($file, '[' . date('d/m/Y H:i:s') . '] ' . trim($str, "\n")."\n--------------------------------------------------------------------------------------------------------------------\n");
            fclose($file);
        } else {
            trigger_error('Failed to open file: ' . $filename);
        }
    }

    /**
     * @param string $message
     * @param int    $severity
     */
    private static function writeToLogs(string $message, int $severity) {
        $log_level = $severity;
        while ($log_level > 0) {
            self::appendToFile($message, self::getLogFilename($log_level));
            $log_level--;
        }
    }

    /**
     * @param int $severity
     *
     * @return string
     */
    private static function getLogFilename(int $severity): string {
        return self::LOG_DIRECTORY . '/' . self::ERROR_CODES[$severity] . '.log';
    }
}