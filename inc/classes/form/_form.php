<?php

namespace form;

use form\fields\field;

/**
 * Class form
 * @package form
 */
abstract class _form {

    public $friendly_name = null;
    public $submit_label = 'Submit';
    public $error_message_prefix = 'There was an error with your submission: ';

    protected $errors = null;

    private $fields = null;
    private $name = null;
    private $form_hash = null;
    private $form_submitted = null;

    /**
     * form constructor.
     *
     * @param array $fields
     */
    public function __construct(array $fields) {
        $this->setFields($fields);
        $this->setName();
    }

    /**
     * @return bool|null
     */
    protected function isSubmitted() {
        if ($this->form_submitted === null) {
            if (isset($_POST[$this->getFormHash()])) {
                /**@var field $field */
                foreach ($this->fields as $field) {
                    if (!isset($_POST[$field->getName()])) {
                        $this->form_submitted = false;
                        break;
                    }
                }

                if ($this->form_submitted === null) {
                    $this->form_submitted = true;
                }
            }
        }

        return $this->form_submitted;
    }

    /**
     * @return string
     */
    private function getFormHash() {
        if ($this->form_hash === null) {
            /**@var field $field */
            $form_fields_string = '';
            foreach ($this->fields as $field) {
                $form_fields_string .= '_' . $field->getName();
            }

            $this->form_hash = md5($this->name . ip . $form_fields_string);
        }

        return $this->form_hash;
    }

    /**
     * @param array $fields
     *
     * @return \form\bool
     */
    private function setFields(array $fields): bool {
        if ($fields === []) {
            return false;
        }

        if ($this->fields === null) {
            /**@var field $field*/
            foreach ($fields as $field) {
                $this->fields[$field->getName()] = $field;
            }

            $submitted = $this->isSubmitted();
            foreach ($this->fields as &$field) {
                $field->submitted = $submitted;
            }
        }

        return true;
    }

    /**
     *
     */
    private function setName() {
        $class = get_called_class();

        $this->name = 'form_' . str_replace([' ', '-'], '_', $class);
        if ($this->friendly_name === null) {
            $this->friendly_name = ucfirst(strtolower(str_replace(['_', '-'], ' ', $class)));
        }
    }

    /**
     * @return \form\string
     */
    protected function isValid(): string {
        if ($this->errors === null) {
            $this->errors = [];

            if ($this->isSubmitted()) {
                /**@var field $field */
                foreach ($this->fields as $field) {
                    if ($field_errors = $field->getErrors()) {
                        foreach ($field_errors as $error) {
                            $this->errors[] = '\'' . $field->getLabel() . '\' ' . $error;
                        }
                    }
                }

                // We also include a special hidden field on all our forms to try and detect bots auto filling the form
                // these fields are hidden from visibility in the form so should be impossible for a user to fill in under
                // normal website usage.
                if (!empty($_POST[$this->getFormHash()])) {
                    $this->errors[] = 'this form submission has been detected as being automated and as such, your form submission has been blocked.';
                }
            }
        }

        return empty($this->errors);
    }

    /**
     * @return \form\string
     */
    protected function getErrorsString(): string {
        if (!$this->isValid()) {
            return $this->error_message_prefix . \get::array_to_sentence_list($this->errors);
        }

        return '';
    }

    /**
     * @param \form\string $field_name
     *
     * @return bool|field
     */
    public function &getField(string $field_name) {
        if (isset($this->fields[$field_name])) {
            return $this->fields[$field_name];
        }

        return false;
    }

    /**
     * @return \form\string
     */
    public function getHtml(): string {
        if ($this->isSubmitted() && $this->isValid()) {
            return $this->doSubmit();
        } else {
            $html = '';
            if ($this->isSubmitted() && !$this->isValid()) {
                $html .= '<div class="alert alert-danger" role="alert">
                ' . $this->getErrorsString() . '
            </div>';
            }

            $html .= '<form name="' . $this->name . ' id="' . $this->name . ' method="post" action="' . uri . '">' . "\n";
            $i = 0;
            /**@var \form\fields\field $field */
            foreach ($this->fields as $field) {
                $field_attributes = [];
                $attrs = [
                    'class' => ['form-group']
                ];
                if (!$field->isValid()) {
                    $i++;
                    $attrs['class'][] = 'has-error';
                    if ($i == 1) {
                        $field_attributes['autofocus'] = 'autofocus';
                    }
                    $field_attributes['data-tooltip'] = 'This ' . \get::array_to_sentence_list($field->getErrors());
                }

                $html .= '<div' . \get::attrs($attrs) . '>' . "\n";
                $html .= $field->getHtml($field_attributes);
                $html .= '</div>' . "\n";
            }
            $html .= '<input type="text" name="' . $this->getFormHash() . '" id="' . $this->getFormHash() . '" value="" hidden="hidden" />' . "\n";
            $html .= '<button type="submit" class="btn btn-default">' . $this->submit_label . '</button>' . "\n";
            $html .= '</form>' . "\n";

            return $html;
        }
    }

    /**
     * @return mixed
     */
    abstract protected function doSubmit(): string;
}