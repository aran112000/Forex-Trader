<?php
namespace form\fields;

/**
 * Class field_url
 * @package form\fields
 */
class field_url extends field {

    protected $filter_validate_error_message = 'is not a valid website';

    protected $filter_validate_constant = 'FILTER_VALIDATE_URL'; // http://php.net/manual/en/filter.filters.validate.php
    protected $filter_sanitise_constant = 'FILTER_SANITIZE_URL'; // http://php.net/manual/en/filter.filters.validate.php

    /**
     * @param \form\fields\bool $allow_default_value
     *
     * @return \form\fields\string
     */
    public function getValue(bool $allow_default_value = true): string {
        $value = parent::getValue($allow_default_value);
        $field_name = $this->getName();

        if (isset($_REQUEST[$field_name]) && !empty($_REQUEST[$field_name])) {
            if (!strstr($value, 'https://') && !strstr($value, 'http://')) {
                $value = 'http://' . $value;
            }
        }

        return $value;
    }
}