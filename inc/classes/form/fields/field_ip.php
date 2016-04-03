<?php
namespace form\fields;

/**
 * Class field_ip
 * @package form\fields
 */
class field_ip extends field {

    protected $filter_validate_error_message = 'is not a valid IP address';

    protected $filter_validate_constant = 'FILTER_VALIDATE_IP'; // http://php.net/manual/en/filter.filters.validate.php

}