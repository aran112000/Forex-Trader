<?php

/**
 * Class session
 **/
final class session {

    public static $session_lifetime = (60 * 60 * 24 * 365 * 10); // 10y
    public static $session_valid_path = '/';

    private static $session_started = false;

    const SESSION_NAME = 'forextrader';
    const SECURE_SESSION = true;
    const HTTP_SESSION = true;

    /**
     * @return bool
     */
    public static function start(): bool {
        if (self::$session_started) {
            return true;
        }

        self::$session_lifetime = (!empty(self::$session_lifetime) ? self::$session_lifetime : (60 * 60 * 24 * 365));

        // Set the cookie settings and start the session
        $https = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? self::SECURE_SESSION : false);
        session_set_cookie_params(self::$session_lifetime, self::$session_valid_path, '.' . host, $https, self::HTTP_SESSION);
        if (!session_start()) {
            trigger_error('Failed to save session');
            return false;
        }

        echo '<p><pre>' . print_r($_SESSION, true) . '</pre></p>';

        self::checkSessionHijacked();
        self::$session_started = true;

        return true;
    }

    /**
     *
     */
    private static function setHijackDetection() {
        if (!isset($_SESSION[self::SESSION_NAME]['session_details'])) {
            $_SESSION[self::SESSION_NAME]['session_details'] = [];
            $_SESSION[self::SESSION_NAME]['session_details']['ip_address'] = ip;
            $_SESSION[self::SESSION_NAME]['session_details']['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        }
    }

    /**
     * This is used to validate the current session - Data will be used to detect hijacks
     *
     * @return bool
     */
    protected static function checkSessionHijacked() {
        self::setHijackDetection();

        if ($_SESSION[self::SESSION_NAME]['session_details']['ip_address'] != ip || $_SESSION[self::SESSION_NAME]['session_details']['user_agent'] != $_SERVER['HTTP_USER_AGENT']) {
            self::regenerateId();
        }
    }

    /**
     * @param bool $remove_old_session
     */
    public static function regenerateId(bool $remove_old_session = true) {
        session_regenerate_id($remove_old_session);

        self::setHijackDetection();
    }

    /**
     * @param string $key
     *
     * @return null
     */
    public static function get(string $key) {
        if (self::start()) {
            if (isset($_SESSION[self::SESSION_NAME][$key])) {
                return $_SESSION[self::SESSION_NAME][$key];
            }
        }

        return null;
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return bool
     */
    public static function set(string $key, $value): bool {
        if (self::start()) {
            $_SESSION[self::SESSION_NAME][$key] = $value;

            return true;
        }

        return false;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public static function unset(string $key): bool {
        if (self::start()) {
            unset($_SESSION[self::SESSION_NAME][$key]);

            return true;
        }

        return false;
    }
}