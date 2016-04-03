<?php
namespace form\fields;

/**
 * Class field_textarea
 * @package form\fields
 */
class field_textarea extends field {

    public $height = 4;

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
            'rows' => $this->height,
            'aria-required' => ($this->required ? 'true' : 'false'),
        ];
        if (!$this->isValid()) {
            $attributes['aria-invalid'] = 'true';
        }
        if (!empty($field_attributes)) {
            $attributes = array_merge($attributes, $field_attributes);
        }

        return '<label for="' . $name . '">' . $this->label . ($this->required ? ' <span class="required_field">*</span>' : '') . '</label>
<textarea' . \get::attrs($attributes) . '>' . $this->getValue() . '</textarea>' . "\n";
    }
}