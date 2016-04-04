<?php

namespace page;

/**
 * Class login
 */
class login extends _page {

    protected $requires_login = false;

    /**
     * @return string
     */
    function getBody(): string {
        if (!\user::isLoggedIn()) {
            $login_form = new \login_form();

            return $login_form->getHtml();
        } else {
            return 'Welcome back ' . user::get()['first_name'];
        }
    }
}