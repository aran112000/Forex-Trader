<?php

/**
 * Class user
 */
class user {

    /**
     * @var array|null
     */
    private static $user = null;
    private static $login_from_session_checked = false;

    /**
     * @return bool
     */
    public static function isLoggedIn(): bool {
        self::setLoggedInFromSession();

        return (self::$user !== null);
    }

    /**
     * @return bool
     */
    public static function setLoggedInFromSession(): bool {
        if (self::$login_from_session_checked) {
            return (self::$user !== null);
        }
        self::$login_from_session_checked = true;

        if ($user_session = session::get('user_details')) {
            if (isset($user_session['uid'])) {
                // Session is present, lets verify it quickly
                if ($res = db::query('SELECT * FROM user WHERE uid=:uid AND last_login_ip=:last_login_ip AND last_login_ua=:last_login_ua LIMIT 1', [
                    'uid' => $user_session['uid'],
                    'last_login_ip' => ip,
                    'last_login_ua' => $_SERVER['HTTP_USER_AGENT'],
                ])) {
                    if (db::num($res) === 1 && ip === $user_session['ip'] && $_SERVER['HTTP_USER_AGENT'] === $user_session['ua']) {
                        if ($row = db::fetch($res)) {
                            self::$user = $row;

                            return true;
                        }
                    }
                }
            }

            session::unset('user_details');
        }

        return false;
    }

    /**
     * @param array $user_row
     *
     * @return bool
     */
    public static function setLoggedInUser(array $user_row): bool {
        if (!isset($user_row['uid'])) {
            return false;
        }

        if (db::query('UPDATE user SET last_login_ip=:last_login_ip, last_login_time=NOW(), last_login_ua=:last_login_ua WHERE uid=:uid LIMIT 1', [
            'uid' => (int) $user_row['uid'],
            'last_login_ip' => ip,
            'last_login_ua' => $_SERVER['HTTP_USER_AGENT'],
        ])) {
            self::$user = $user_row;

            session::set('user_details', [
                'uid' => self::$user['uid'],
                'ip' => ip,
                'ua' => $_SERVER['HTTP_USER_AGENT'],
            ]);

            return true;
        }


        return false;
    }

    /**
     * @return array
     */
    public static function get(): array {
        if (self::$user === null) {
            return [];
        }

        return self::$user;
    }
}