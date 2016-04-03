<?php

/**
 * Class user
 */
class user {

    /**
     * @var array|null
     */
    private static $user = null;

    /**
     * @param array $user_row
     *
     * @return bool
     */
    public static function setLoggedInUser(array $user_row): bool {
        if (!isset($user_row['uid'])) {
            return false;
        }

        self::$user = $user_row;

        return true;
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