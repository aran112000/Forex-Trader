<?php
namespace form\fields;

class field_float extends field {

    protected $filter_validate_error_message = 'is not a valid decimal';

    protected $filter_validate_constant = 'FILTER_VALIDATE_FLOAT'; // http://php.net/manual/en/filter.filters.validate.php
    protected $filter_sanitise_constant = 'FILTER_SANITIZE_NUMBER_FLOAT'; // http://php.net/manual/en/filter.filters.sanitize.php

}