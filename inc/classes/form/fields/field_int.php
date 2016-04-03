<?php
namespace form\fields;

/**
 * Class field_int
 * @package form\fields
 */
class field_int extends field {

    protected $filter_validate_error_message = 'is not a valid number';

    protected $filter_validate_constant = 'FILTER_VALIDATE_INT'; // http://php.net/manual/en/filter.filters.validate.php
    protected $filter_sanitise_constant = 'FILTER_SANITIZE_NUMBER_INT'; // http://php.net/manual/en/filter.filters.sanitize.php

}