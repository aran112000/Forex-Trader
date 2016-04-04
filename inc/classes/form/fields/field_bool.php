<?php
namespace form\fields;

/**
 * Class field_bool
 *
 * @package form\fields
 */
class field_bool extends field {

    /**
     * @return mixed
     */
    public function getErrors(): array {
        if ($this->errors === null) {
            $this->errors = [];
            if ($this->submitted) {

                $value = $this->getValue(false);

                if ($value != 1 && $value != 0) {
                    $this->errors[] = 'is not a valid boolean';
                }
            }
        }

        return $this->errors;
    }

    /**
     * @param array $field_attributes
     *
     * @return string
     */
    public function getHtml(array $field_attributes = []): string {
        $name = $this->getName();
        $field_value = $this->getValue();

        $return = '<label for="' . $name . '" class="control-label">' . $this->label . ($this->required ? ' <span class="required_field">*</span>' : '') . '</label>'."\n";

        $return .= '<div class="radio">
        <label>
            <input type="radio" name="' . $name . '" id="' . $name . '_yes" value="1"' . ($field_value == 1 ? ' checked="checked"' : '') . '>
            Yes
        </label>
        </div>' . "\n";

        $return .= '<div class="radio">
        <label>
            <input type="radio" name="' . $name . '" id="' . $name . '_no" value="0"' . ($field_value == 0 ? ' checked="checked"' : '') . '>
            No
        </label>
        </div>' . "\n";

        return $return;
    }
}