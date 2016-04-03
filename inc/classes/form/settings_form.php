<?php

/**
 * Class settings_form
 */
class settings_form extends \form\_form {

    private $settings = null;

    /**
     * settings_form constructor.
     */
    public function __construct() {
        parent::__construct($this->getSettingFields());
    }

    /**
     * @return array
     */
    private function getSettings(): array {
        if ($this->settings === null) {
            $this->settings = [];
            if ($res = db::query('SELECT `key`, `value` FROM settings LIMIT 1')) {
                while ($row = db::fetch($res)) {
                    $this->settings[$row['key']] = $row['value'];
                }
            }
        }

        return $this->settings;
    }

    /**
     * @return array
     */
    private function getSettingFields(): array {
        $fields = [];
        foreach ($this->getSettings() as $key => $value) {
            $fields[] = new \form\fields\field_string($key);
        }

        return $fields;
    }

    /**
     *
     */
    private function doUpdateSettings() {
        foreach ($this->getSettings() as $field) {

        }
    }

    /**
     * @return mixed
     */
    protected function doSubmit(): string {
        $this->doUpdateSettings();

        return '<div class="alert alert-success" role="alert">Your changes have been saved</div>' . $this->getHtml();
    }
}