<?php

namespace form\fields;

/**
 * Class field
 * @package form\fields
 */
abstract class field {

    public $name = null;
    public $label = null;
    public $pre_text = null;
    public $post_text = null;
    public $value = null;

    public $required = true;
    public $hidden = false;
    public $show_label = true;
    public $submitted = false;

    protected $errors = null;

    protected $input_type = 'text';

    protected $filter_validate_error_message = 'is not a valid value';
    protected $filter_validate_constant = null; // http://php.net/manual/en/filter.filters.validate.php
    protected $filter_sanitise_constant = 'FILTER_SANITIZE_STRING'; // http://php.net/manual/en/filter.filters.sanitize.php

    /**
     * field constructor.
     *
     * @param \form\fields\string      $field_name
     * @param \form\fields\string|null $field_label
     *
     * @throws \Exception
     */
    public function __construct(string $field_name, string $field_label = null) {
        $this->name = $field_name;
        $this->setLabel($field_name, $field_label);
    }

    /**
     * @param \form\fields\string $field_name
     * @param \form\fields\string $field_label
     *
     * @return \form\fields\bool
     */
    private function setLabel(string $field_name, $field_label = null): bool {
        if ($this->label === null && $this->show_label) {
            if ($field_label === null) {
                $this->label = ucfirst(trim(str_ireplace(['-', '_'], ' ', $field_name)));
            } else {
                $this->label = $field_label;
            }
        }

        return true;
    }

    /**
     * @return \form\fields\string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return \form\fields\string
     */
    public function getLabel(): string {
        return $this->label;
    }

    /**
     * @param \form\fields\bool $allow_default_value
     *
     * @return \form\fields\string
     */
    public function getValue(bool $allow_default_value = true): string {
        $field_name = $this->getName();
        if (isset($_POST[$field_name]) && !empty($_POST[$field_name])) {
            if ($this->filter_validate_constant !== null && filter_input(INPUT_POST, $field_name, constant($this->filter_validate_constant))) {
                return filter_input(INPUT_POST, $field_name, constant($this->filter_sanitise_constant));
            } else {
                return filter_input(INPUT_POST, $field_name, FILTER_SANITIZE_STRING);
            }
        } else if ($allow_default_value && $this->value !== null) {
            return $this->value;
        }

        return '';
    }

    /**
     * @return mixed
     */
    public function getErrors(): array {
        if ($this->errors === null) {
            $errors = [];

            if ($this->submitted) {
                $value = $this->getValue(false);
                if ($this->required && empty($value)) {
                    $errors[] = 'is a required field';
                } else if ($this->filter_validate_constant !== null && !filter_var($value, constant($this->filter_validate_constant))) {
                    $errors[] = $this->filter_validate_error_message;
                }
            }

            $this->errors = $errors;
        }

        return $this->errors;
    }

    /**
     * @return \form\fields\bool
     */
    public function isValid(): bool {
        return empty($this->getErrors());
    }

    /**
     * @param array $field_attributes
     *
     * @return \form\fields\string
     */
    public function getHtml(array $field_attributes = []): string {
        $name = $this->getName();

        $attributes = [
            'value' => $this->getValue(),
            'name' => $name,
            'class' => 'form-control',
            'id' => $name,
            'type' => $this->input_type,
            'aria-required' => ($this->required ? 'true' : 'false'),
            'placeholder' => $this->label,
        ];
        if (!$this->isValid()) {
            $attributes['aria-invalid'] = 'true';
        }
        if ($this->hidden) {
            $attributes['hidden'] = 'hidden';
        }
        if (!empty($field_attributes)) {
            $attributes = array_merge($attributes, $field_attributes);
        }

        return '<label for="' . $name . '" class="control-label">' . $this->label . ($this->required ? ' <span class="required_field">*</span>' : '') . '</label>
<input' . \get::attrs($attributes) . '/>' . "\n";
    }
}