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
            if ($res = db::query('SELECT `key`, `value`, field_type FROM settings')) {
                while ($row = db::fetch($res)) {
                    $this->settings[$row['key']] = [
                        'value' => $row['value'],
                        'field_type' => $row['field_type'],
                    ];
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
        foreach ($this->getSettings() as $key => $details) {
            $field_type = '\form\fields\field_' . $details['field_type'];
            $field = new $field_type($key);
            $field->value = $details['value'];

            $fields[] = $field;
        }

        return $fields;
    }

    /**
     *
     */
    private function doUpdateSettings() {
        foreach ($this->getSettings() as $field_name => $value) {
            bulk_db::add_query('settings', [
                'key' => $field_name,
                'value' => $this->getField($field_name)->getValue(false),
            ]);
        }
        bulk_db::do_process_queries();

        return true;
    }

    /**
     * @return mixed
     */
    protected function doSubmit(): string {
        $this->doUpdateSettings();

        \ajax::addUpdateHtml('<div class="alert alert-success" role="alert">Your changes have been saved</div>' . $this->getHtml());
        \ajax::doServe();
    }
}