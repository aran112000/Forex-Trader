<?php

/**
 * Class clean
 */
class clean {

    /**
     * @param $email
     *
     * @return mixed
     */
    public static function email(string $email) {
        return filter_var(str_replace([
            ',',
            ' '
        ], [
            '.',
            ''
        ], strtolower(trim($email))), FILTER_SANITIZE_EMAIL);
    }
}