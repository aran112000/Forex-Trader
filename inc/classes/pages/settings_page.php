<?php

/**
 * Class settings_page
 */
class settings_page extends _page {

    /**
     * @return string
     */
    function getBody(): string {
        $settings = new settings_form();

        return $settings->getHtml();
    }
}