<?php
namespace form\fields;

/**
 * Class field_email
 * @package form\fields
 */
class field_email extends field {

    protected $filter_validate_error_message = 'is not a valid email address';
    protected $filter_validate_constant = 'FILTER_VALIDATE_EMAIL'; // http://php.net/manual/en/filter.filters.validate.php

    /**
     * @param \form\fields\bool $allow_default_value
     *
     * @return \form\fields\string
     */
    public function getValue(bool $allow_default_value = true): string {
        $field_name = $this->getName();
        if (isset($_POST[$field_name]) && !empty($_POST[$field_name])) {
            return \clean::email($_POST[$field_name]);
        } else if ($allow_default_value && $this->value !== null) {
            return \clean::email($this->value);
        }

        return '';
    }
}