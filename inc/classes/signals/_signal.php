<?php

/**
 * Class _signal
 */
abstract class _signal {

    /**
     * @param array $data
     *
     * @return bool
     */
    abstract public static function isValidSignal(array $data): bool;
}