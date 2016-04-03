<?php

/**
 * Class login
 */
class login extends _page {

    /**
     * @return string
     */
    function getBody(): string {
        $login_form = new login_form();
        return $login_form->getHtml();
    }
}