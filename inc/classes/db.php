<?php

/**
 * Class db
 */
class db {

    /**
     * @var null|mysqli
     */
    private static $connection = null;

    /**
     *
     */
    public static function connect() {
        if (self::$connection === null) {
            self::$connection = new mysqli("localhost", "root", "Issj598*", "forex");

            if (mysqli_connect_errno()) {
                printf("Connect failed: %s\n", mysqli_connect_error());
                exit();
            }
        }
    }

    /**
     * @param string $sql
     *
     * @return mixed
     */
    public static function query(string $sql) {
        if (self::$connection === null) {
            self::connect();
        }

        if (!$res = self::$connection->query($sql)) {
            printf("Error: %s\n", self::$connection->error);
        }

        return $res;
    }

    /**
     * @param string $string
     *
     * @return string
     */
    public static function esc(string $string): string {
        if (self::$connection === null) {
            self::connect();
        }

        return self::$connection->real_escape_string($string);
    }

    /**
     * @param mysqli_result $res
     *
     * @return array|null
     */
    public static function fetch($res) {
        return $res->fetch_assoc();
    }

    /**
     * @param mysqli_result $res
     *
     * @return mixed
     */
    public static function num($res): int {
        return $res->num_rows;
    }
}