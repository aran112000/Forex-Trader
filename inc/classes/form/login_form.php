<?php

/**
 * Class login_form
 */
class login_form extends \form\_form {

    /**
     * login_form constructor.
     */
    public function __construct() {
        parent::__construct([
            new \form\fields\field_email('email'),
            new \form\fields\field_password('password'),
        ]);
    }

    /**
     * @return string
     */
    public function isValid(): string {
        $ok = parent::isValid();

        if ($ok) {
            if (!$this->isValidLogin()) {
                $this->errors[] = 'Please check your email and password and try again';
            }
        }

        return empty($this->errors);
    }

    /**
     * @return mixed
     */
    function doSubmit(): string {
        $redirect_url = '/';
        if (isset($_REQUEST['r'])) {
            $redirect_url = urldecode($_REQUEST['r']);
        }

        header('location: ' . $redirect_url);
    }

    /**
     * @return bool
     */
    private function isValidLogin() {
        if ($res = db::query('SELECT * FROM user WHERE email=:email AND password=:password LIMIT 1', ['email' => $this->getField('email')->getValue(), 'password' => get::hash($this->getField('password')->getValue())])) {
            if (db::num($res) === 1) {
                if ($row = db::fetch($res)) {
                    user::setLoggedInUser($row);

                    return true;
                }
            }
        }

        return false;
    }
}