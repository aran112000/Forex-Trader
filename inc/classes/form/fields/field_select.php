<?php
namespace form\fields;

/**
 * Class field_select
 * @package form\fields
 */
class field_select extends field {

    public $sort_values_alphabetically = true;

    protected $options = [];
    protected $default_value = 'Please Select';

    /**
     * field_select constructor.
     *
     * @param \form\fields\string      $field_name
     * @param array                    $values
     * @param \form\fields\string|null $field_label
     */
    public function __construct(string $field_name, array $values, string $field_label = null) {
        parent::__construct($field_name, $field_label);

        $this->setOptions($values);
    }

    /**
     * @param $values
     */
    protected function setOptions($values) {
        $set_value_as_key = false;
        if ($this->sort_values_alphabetically) {
            asort($values);
        }
        foreach ($values as $key => $value) {
            if ($key === 0) {
                // This appears to be a non-associative array
                $set_value_as_key = true;
            }

            $this->options[($set_value_as_key ? $value : $key)] = $value;
        }
    }

    /**
     * @param array $field_attributes
     *
     * @return \form\fields\string
     */
    public function getHtml(array $field_attributes = []): string {
        $name = $this->getName();

        $attributes = [
            'name' => $name,
            'id' => $name,
            'aria-required' => ($this->required ? 'true' : 'false'),
        ];
        if (!$this->isValid()) {
            $attributes['aria-invalid'] = 'true';
        }
        if (!empty($field_attributes)) {
            $attributes = array_merge($attributes, $field_attributes);
        }

        $field_value = $this->getValue();

        $return = '<label for="' . $name . '">' . $this->label . ($this->required ? ' <span class="required_field">*</span>' : '') . '</label>'."\n";
        $return .= '<select' . \get::attrs($attributes) . '>'."\n";
        if ($this->default_value !== null) {
            $return .= '<option value="">' . $this->default_value . '</option>';
        }
        foreach ($this->options as $key => $value) {
            $attrs = ['value' => $key];
            if ($key == $field_value) {
                $attrs['selected'] = 'selected';
            }
            $return .= '<option' . \get::attrs($attrs) . '>' . $value . '</option>';
        }
        $return .= '</select>' . "\n";

        return $return;
    }
}