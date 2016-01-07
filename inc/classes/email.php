<?php

/**
 * Class email
 */
class email {

    const FROM_EMAIL = 'cdtreeks@gmail.com';

    /**
     * @param string $subject
     * @param string $message
     * @param string $to_email
     *
     * @return bool
     */
    public static function send(string $subject, string $message, string $to_email = 'cdtreeks@gmail.com'): bool {
        return mail($to_email, $subject, $message, self::getHeaders());
    }

    /**
     * @return string
     */
    private static function getHeaders() {
        $headers = "From: Aran Reeks <" . self::FROM_EMAIL . ">\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        return $headers;
    }
}