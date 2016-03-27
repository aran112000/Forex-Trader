<?php

/**
 * Class _signal
 */
abstract class _signal {

    /**
     * @param array  $data
     * @param string $direction
     *
     * @return bool
     */
    abstract public static function isValidSignal(array $data, string $direction): bool;
}